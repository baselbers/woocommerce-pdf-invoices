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

        /**
         * @var WC_Order
         */
        public $order;

        /**
         * Invoice number
         * @var integer
         */
        protected $number;

        /**
         * Formatted invoice number with prefix and/or suffix
         * @var string
         */
        protected $formatted_number;

        /**
         * Creation date.
         * @var datetime
         */
        protected $date;

	    /**
	     * Creation year
	     * @var datetime
	     */
	    protected $year;

	    /**
	     * Invoice footer
	     * @var string
	     */
	    protected $footer;

        /**
         * Number of columns for the products table
         * @var integer
         */
        protected $number_of_columns;

        /**
         * Colspan data for product table cells
         * @var array
         */
        protected $colspan;

        /**
         * Width of the description cell of the product table
         * @var string
         */
        protected $desc_cell_width;

        /**
         * Initialize invoice with WooCommerce order
         * @param string $order
         */
        public function __construct( $order_id ) {
            parent::__construct();
	        $this->order = wc_get_order( $order_id );
	        $this->template_filename    = BEWPI_TEMPLATES_DIR . $this->template_options['bewpi_template_filename'];
	        $this->footer               = $this->get_footer();
	        $this->formatted_number     = get_post_meta( $this->order->id, '_bewpi_formatted_invoice_number', true );

	        // Check if the invoice already exists.
	        if( ! empty( $this->formatted_number ) )
		        $this->init();
        }

        /**
         * Gets all the existing invoice data from database or creates new invoice number.
         */
        private function init() {
	        $this->number               = get_post_meta( $this->order->id, '_bewpi_invoice_number', true );
	        $this->year                 = get_post_meta( $this->order->id, '_bewpi_invoice_year', true );
	        $this->file                 = $this->formatted_number . '.pdf';
	        $this->filename             = BEWPI_INVOICES_DIR . (string)$this->year . '/' . $this->file;
	        $this->date                 = get_post_meta( $this->order->id, '_bewpi_invoice_date', true );
        }

	    /**
	     * Format the invoice number with prefix and/or suffix.
	     * @return mixed
	     */
	    public function get_formatted_number( $insert = false ) {
		    $invoice_number_format = $this->template_options['bewpi_invoice_number_format'];
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
         * Format date
         * @param bool $insert
         * @return bool|datetime|string
         */
        public function get_formatted_invoice_date( $insert = false ) {
            $date_format = $this->template_options['bewpi_date_format'];
	        ( !empty( $date_format ) ) ? $this->date = date_i18n( $date_format, date( $date_format ) ) : $this->date = date_i18n( "d-m-Y", date( 'd-m-Y' ) );
            if( $insert ) add_post_meta($this->order->id, '_bewpi_invoice_date', $this->date);
            return $this->date;
        }

        /*
         * Format the order date and return
         */
        public function get_formatted_order_date() {
            $order_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->order->order_date );
            if ( ! empty ( $this->template_options['bewpi_date_format'] ) ) {
                $date_format = $this->template_options['bewpi_date_format'];
                $formatted_date = $order_date->format( $date_format );
                return date_i18n( $date_format, $formatted_date );

            } else {
                $formatted_date = $order_date->format( $order_date, "d-m-Y" );
                return date_i18n( "d-m-Y", $formatted_date );
            }
        }

        /**
         * The footer for the invoice.
         * @return string
         */
        protected function get_footer() {
            ob_start(); ?>
            <table class="foot small-font">
                <tbody>
                <tr>
                    <td class="border" colspan="2">
                        <?php echo $this->template_options['bewpi_terms']; ?><br/>
                        <?php
                        if ( $this->template_options['bewpi_show_customer_notes'] && $this->order->post->post_excerpt != "" ) :
                            // Note added by customer.
                            echo '<p><strong>' . __( 'Customer note', $this->textdomain ) . '</strong> ' . $this->order->post->post_excerpt . '</p>';
                            // Notes added administrator on order details page.
                            $customer_order_notes = $this->order->get_customer_order_notes();
                            if ( count( $customer_order_notes ) > 0 ) {
                                echo '<p><strong>' . __('Customer note', $this->textdomain) . '</strong>' . $customer_order_notes[0]->comment_content . '</p>';
                            }
                        endif;
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="company-details"><p><?php echo nl2br( $this->template_options['bewpi_company_details'] ); ?></p></td>
                    <td class="payment"><p><?php printf( __( '%sPayment%s via', $this->textdomain ), '<b>', '</b>' ); ?>  <?php echo $this->order->payment_method_title; ?></p></td>
                </tr>
                </tbody>
            </table>
            <?php $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }

        /**
         * Number of table columns that will be displayed
         * @return int
         */
	    public function get_number_of_columns() {
	        $number_of_columns = 4;
		    if ( $this->template_options['bewpi_show_sku'] ) $number_of_columns++;
		    $order_taxes    = $this->order->get_taxes();
		    if ( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() && empty( $legacy_order ) && ! empty( $order_taxes ) ) :
			    foreach ( $order_taxes as $tax_id => $tax_item ) :
				    $number_of_columns++;
			    endforeach;
		    endif;
		    return $number_of_columns;
        }

        /**
         * Calculates colspan for table footer cells
         * @return array
         */
	    public function get_colspan() {
	        $colspan = array();
		    $number_of_columns = $this->get_number_of_columns();
            $number_of_left_half_columns = 3;
            $this->desc_cell_width = '30%';

		    // The product table will be split into 2 where on the right 5 columns are the max
            if ( $number_of_columns <= 4 ) :
                $number_of_left_half_columns = 1;
                $this->desc_cell_width = '50%';
		    elseif ( $number_of_columns <= 6 ) :
			    $number_of_left_half_columns = 2;
                $this->desc_cell_width = '37.50%';
		    endif;

		    $colspan['left'] = $number_of_left_half_columns;
		    $colspan['right'] = $number_of_columns - $number_of_left_half_columns;
		    $colspan['right_left'] = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_DOWN );
		    $colspan['right_right'] = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_UP );

		    return $colspan;
	    }

	    /**
	     * Reset invoice number counter if user did check the checkbox.
	     * @return bool
	     */
	    private function reset_counter() {
		    // Check if the user resetted the invoice counter and set the number.
		    if ( $this->template_options['bewpi_reset_counter'] ) {
			    if ( $this->template_options['bewpi_next_invoice_number'] > 0 ) {
				    $this->number = $this->template_options['bewpi_next_invoice_number'];
				    $this->template_options['bewpi_reset_counter'] = 0;
				    return true;
			    }
		    }
		    return false;
	    }

	    /**
	     * Reset the invoice number counter if user did check the checkbox.
	     * @return bool
	     */
	    private function new_year_reset() {
		    if ( $this->template_options['bewpi_reset_counter_yearly'] ) {
			    $last_year = ( isset( $this->template_options['bewpi_last_invoiced_year'] ) ) ? $this->template_options['bewpi_last_invoiced_year'] : '';

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
	     * Generates and saves the invoice to the uploads folder.
	     * @param $dest
	     * @return string
	     */
	    public function save( $dest ) {
		    if ( $this->exists() ) die( 'Invoice already exists. First delete invoice.' );

		    // If the invoice is manually deleted from dir, delete data from database.
		    $this->delete();

		    if ( $this->template_options['bewpi_invoice_number_type'] === "sequential_number" ) :
			    if ( !$this->reset_counter() && !$this->new_year_reset() ) :
				    $this->number = $this->template_options['bewpi_last_invoice_number'] + 1;
			    endif;
		    else :
			    $this->number = $this->order->get_order_number();
		    endif;

            $this->number_of_columns    = $this->get_number_of_columns();
            $this->colspan              = $this->get_colspan();
		    $this->formatted_number     = $this->get_formatted_number( true );
		    $this->year                 = date( 'Y' );
		    $this->filename             = BEWPI_INVOICES_DIR . (string)$this->year . '/' . $this->formatted_number . '.pdf';

		    add_post_meta( $this->order->id, '_bewpi_invoice_number', $this->number );
		    add_post_meta( $this->order->id, '_bewpi_invoice_year', $this->year );

		    $this->template_options['bewpi_last_invoice_number']    = $this->number;
		    $this->template_options['bewpi_last_invoiced_year']     = $this->year;
		    update_option( 'bewpi_template_settings', $this->template_options );

		    parent::generate( $dest, $this );

		    return $this->filename;
	    }

	    /**
	     * View or download the invoice.
	     * @param $download
	     */
	    public function view( $download ) {
		    if ( !$this->exists() ) die( 'No invoice found. First create invoice.' );
		    parent::view( $download );
	    }

	    /**
	     * Delete all invoice data from database and the file.
	     */
	    public function delete() {
		    delete_post_meta( $this->order->id, '_bewpi_invoice_number' );
		    delete_post_meta( $this->order->id, '_bewpi_formatted_invoice_number' );
		    delete_post_meta( $this->order->id, '_bewpi_invoice_date' );
		    delete_post_meta( $this->order->id, '_bewpi_invoice_year' );

		    if ( $this->exists() )
		        parent::delete();
	    }

	    /**
	     * @param $order_status
	     * Customer is only allowed to download invoice if the status of the order matches the email type option.
	     * @return bool
	     */
	    public function is_download_allowed( $order_status ) {
		    $allowed = false;
		    if ( $this->general_options['bewpi_email_type'] === "customer_processing_order"
		        && $order_status === "wc-processing" || $order_status === "wc-completed" ) {
			    $allowed = true;
		    }
		    return $allowed;
	    }

	    public function get_total() {
		    $total = "";
		    if ( $this->order->get_total_refunded() > 0 ) :
			    $total_after_refund = $this->order->get_total() - $this->order->get_total_refunded();
			    $total = '<del class="total-without-refund">' . strip_tags( $this->order->get_formatted_order_total() ) . '</del> <ins>' . wc_price( $total_after_refund, array( 'currency' => $this->order->get_order_currency() ) ) . '</ins>';
		    else :
			    $total = $this->order->get_formatted_order_total();
		    endif;
		    return $total;
	    }
    }
}