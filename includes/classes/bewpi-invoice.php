<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_Invoice' ) ) {

    /**
     * Makes the invoice.
     * Class BEWPI_Invoice
     */
    class BEWPI_Invoice extends BEWPI_Document{

        /*
         * WooCommerce order
         */
        public $order;

        /**
         * Invoice number
         * @var
         */
        protected $number;

        /**
         * Formatted invoice number with prefix and/or suffix
         * @var
         */
        protected $formatted_number;

        /**
         * Creation date.
         * @var
         */
        protected $date;

	    protected $footer;

        /**
         * Initialize invoice with WooCommerce order and plugin textdomain.
         * @param string $order
         * @param $textdomain
         */
        public function __construct( $order_id ) {
            parent::__construct( $order_id );
	        $this->order                = wc_get_order( $order_id );
	        $this->formatted_number     = get_post_meta( $this->order->id, '_bewpi_formatted_invoice_number', true );

	        // Check if the invoice already exists.
	        if( !empty( $this->formatted_number ) )
		        $this->init();
        }

        /**
         * Gets all the existing invoice data from database or creates new invoice number.
         */
        private function init() {

            if ( $this->template_options['bewpi_invoice_number_type'] === 'sequential_number' ) :
                $this->number           = get_post_meta( $this->order->id, '_bewpi_invoice_number', true );
            else :
	            $this->number           = $this->order->id;
            endif;

            $this->date                 = get_post_meta( $this->order->id, '_bewpi_invoice_date', true );
	        $this->footer               = $this->get_footer();
        }

	    public function save() {

		    // Delete all data from db
		    $this->delete_all_post_meta();

		    if ( $this->template_options['bewpi_invoice_number_type'] === "sequential_number" ) :
			    if ( !$this->reset_counter() && !$this->new_year_reset() ) :
				    // User resetted counter
				    $last_invoice_number = $this->template_options['bewpi_last_invoice_number'];

				    if ( $this->insert_invoice_number( $last_invoice_number ) ) :
					    // Get the latest invoice number from db
					    $this->number = $this->get_invoice_number();
				    endif;

			    endif;

			    // Set new last invoice number
			    $this->template_options['last_invoice_number'] = $this->number;
			    update_option( 'bewpi_template_options', $this->template_options );

		    else :

			    $this->number = $this->order->get_order_number();

		    endif;

		    // Format invoice number
		    $this->number = $this->get_formatted_number( true );

		    $this->generate( "F", $this );
	    }

	    private function reset_counter() {
		    // Check if the user resetted the invoice counter and set the number.
		    if ( $this->template_options['bewpi_reset_counter'] ) {
			    if ( $this->template_options['bewpi_next_invoice_number'] > 0 ) {
				    $this->number = $this->template_options['bewpi_next_invoice_number'];
				    return true;
			    }
		    }
		    return false;
        }

	    private function new_year_reset() {
		    if ( $this->template_options['bewpi_reset_counter_yearly'] ) {
			    $last_year = $this->template_options['bewpi_last_invoiced_year'];

			    if ( !empty( $last_year ) && is_numeric( $last_year ) ) {
				    $date = getdate();
				    $current_year = $date['year'];
				    if ($last_year < $current_year) {
					    // Set new year as last invoiced year and reset invoice number
					    $this->number = 1;
					    return true;
				    }
			    }
		    }
		    return false;
	    }

	    /**
	     * Creates new invoice number with SQL MAX CAST.
	     * @param $order_id
	     * @param $number
	     */
	    protected function insert_invoice_number( $next_number ) {
		    global $wpdb;
		    // attempt the query up to 3 times for a much higher success rate if it fails (due to Deadlock)
		    $success = false;
		    for ($i = 0; $i < 3 && !$success; $i++) {
			    // this seems to me like the safest way to avoid order number clashes
			    $query = $wpdb->prepare(
				    "
                    INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
                    SELECT %d, %s, IF( MAX( CAST( meta_value as UNSIGNED ) ) IS NULL, %d, MAX( CAST( meta_value as UNSIGNED ) ) + 1 )
                    FROM {$wpdb->postmeta}
                    WHERE meta_key = %s
                	",
				    $this->order->id,
				    $this->invoice_number_meta_key,
				    $next_number,
				    $this->invoice_number_meta_key
			    );
			    $success = $wpdb->query( $query );
		    }
		    return $success;
	    }

	    /**
	     * Format the invoice number with prefix and/or suffix.
	     * @return mixed
	     */
	    protected function get_formatted_number( $insert = false ) {
		    $invoice_number_format = $this->template_options['bewpi_invoice_format'];
		    // Format number with the number of digits
		    $digit_str = "%0" . $this->template_options['bewpi_invoice_number_digits'] . "s";
		    $digitized_invoice_number = sprintf( $digit_str, $this->number );
		    $year = date('Y');
		    $y = date('y');

		    // Format invoice number
		    $formatted_invoice_number = str_replace(
			    array( '[prefix]', '[suffix]', '[number]', '[Y]', '[y]' ),
			    array( $this->template_options['bewpi_invoice_number_prefix'], $this->template_options['bewpi_invoice_number_suffix'], $digitized_invoice_number, $year, $y ),
			    $invoice_number_format );

		    // Insert formatted invoicenumber into db
		    if ( $insert )
			    add_post_meta( $this->order->id, '_bewpi_formatted_invoice_number', $formatted_invoice_number );

		    return $formatted_invoice_number;
	    }

        /**
         * Create invoice date
         * @return bool|string
         */
        public function get_formatted_invoice_date( $insert = false ) {
            $date_format = $this->template_options['bewpi_date_format'];
	        ( !empty( $date_format ) ) ? $this->date = date( $date_format ) : $this->date = date('d-m-Y');
            if( $insert ) add_post_meta($this->order->id, '_bewpi_invoice_date', $this->date);
            return $this->date;
        }

        /*
         * Format the order date and return
         */
        public function get_formatted_order_date() {
            $order_date = $date = DateTime::createFromFormat('Y-m-d H:i:s', $this->order->order_date);
            if ( !empty ( $this->template_options['bewpi_date_format'] ) ) {
                $date_format = $this->template_options['bewpi_date_format'];
                $formatted_date = $order_date->format($date_format);
            } else {
                $formatted_date = $order_date->format($order_date, "d-m-Y");
            }
            return $formatted_date;
        }

        /**
         * Returns MPDF footer.
         * @return string
         */
        protected function get_footer() {
            ob_start(); ?>

            <table class="foot">
                <tbody>
                <tr>
                    <td class="border" colspan="2">
                        <?php echo $this->template_options['terms']; ?>
                        <br/>
                        <?php
                        $customer_order_notes = $this->order->get_customer_order_notes();
                        if ( count( $customer_order_notes ) > 0 ) { ?>
                            <p>
                                <strong><?php _e('Customer note', $this->textdomain); ?> </strong><?php echo $customer_order_notes[0]->comment_content; ?>
                            </p>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td class="company-details">
                        <p>
                            <?php echo nl2br($this->template_options['company_details']); ?>
                        </p>
                    </td>
                    <td class="payment">
                        <p>
                            <?php printf( __( '%sPayment%s via', $this->textdomain ), '<b>', '</b>' ); ?>  <?php echo $this->order->payment_method_title; ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php $html = ob_get_contents();

            ob_end_clean();

            return $html;
        }

        /**
         * Get's the invoice number from db.
         * @param $order_id
         * @return mixed
         */
        public function get_invoice_number() {
            global $wpdb;

            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT meta_value
                    FROM $wpdb->postmeta
                    WHERE post_id = %d
                    AND meta_key = %s
                    ", $this->order->id, '_bewpi_invoice_number'
                )
            );

            if ( count( $results ) == 1 ) {
	            return $results[0]->meta_value;
            } else {
	            return "";
            }
        }

	    public function get_colspan() {
		    $colspan = 2;
		    if ( $this->template_options['show_sku'] ) $colspan ++;
		    if ( $this->template_options['show_sku'] ) $colspan ++;
		    return $colspan;
	    }

	    /**
	     * When an invoice gets generated again then the post meta needs to get deleted.
	     */
	    protected function delete_all_post_meta() {
		    delete_post_meta( $this->order->id, '_bewpi_invoice_number' );
		    delete_post_meta( $this->order->id, '_bewpi_formatted_invoice_number' );
		    delete_post_meta( $this->order->id, '_bewpi_invoice_date' );
	    }
    }
}