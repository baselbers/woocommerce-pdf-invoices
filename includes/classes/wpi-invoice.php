<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPI_Invoice' ) ) {

    /**
     * Makes the invoice.
     * Class WPI_Invoice
     */
    class WPI_Invoice extends WPI_Document{

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
         * Invoice number database meta key
         * @var string
         */
        protected $invoice_number_meta_key = '_bewpi_invoice_number';

        /**
         * Creation date.
         * @var
         */
        protected $date;

        /**
         * Initialize invoice with WooCommerce order and plugin textdomain.
         * @param string $order
         * @param $textdomain
         */
        public function __construct( $order ) {

            parent::__construct( $order );

            // Init if the invoice already exists.
            if( get_post_meta( $this->order->id, '_bewpi_invoice_date', true ) === '' )
                return;

            $this->init();
        }

        /**
         * Gets all the existing invoice data from database or creates new invoice number.
         */
        private function init() {
            ( $this->template_settings['invoice_number_type'] === 'sequential_number' )
                ? $this->number = get_post_meta($this->order->id, '_bewpi_invoice_number', true)
                : $this->number = $this->order->id;
            $this->formatted_number = get_post_meta($this->order->id, '_bewpi_formatted_invoice_number', true);
            $this->date = get_post_meta( $this->order->id, '_bewpi_invoice_date', true );
        }

        /**
         * Gets next invoice number based on the user input.
         * @param $order_id
         */
        protected function get_next_invoice_number( $last_invoice_number ) {
            // Check if it has been the first of january.
            if ($this->template_settings['reset_invoice_number']) {
                $last_year = $this->template_settings['last_invoiced_year'];

                if ( !empty( $last_year ) && is_numeric($last_year)) {
                    $date = getdate();
                    $current_year = $date['year'];
                    if ($last_year < $current_year) {
                        // Set new year as last invoiced year and reset invoice number
                        return 1;
                    }
                }
            }

            // Check if the next invoice number should be used.
            $next_invoice_number = $this->template_settings['next_invoice_number'];
            if ( !empty( $next_invoice_number )
                && empty( $last_invoice_number )
                || $next_invoice_number > $last_invoice_number) {
                return $next_invoice_number;
            }

            return $last_invoice_number;
        }

        /**
         * Create invoice date
         * @return bool|string
         */
        public function get_formatted_invoice_date( $add_post_meta = false ) {
            $date_format = $this->template_settings['invoice_date_format'];
            //$date = DateTime::createFromFormat('Y-m-d H:i:s', $this->order->order_date);
            //$date = date( $date_format );

            if ($date_format != "") {
                //$formatted_date = $date->format($date_format);
                $this->date = date($date_format);
            } else {
                //$formatted_date = $date->format($date, "d-m-Y");
                $this->date = date('d-m-Y');
            }

            if( $add_post_meta )
                add_post_meta($this->order->id, '_bewpi_invoice_date', $this->date);

            return $this->date;
        }

        /*
         * Format the order date and return
         */
        public function get_formatted_order_date() {
            $order_date = $date = DateTime::createFromFormat('Y-m-d H:i:s', $this->order->order_date);

            if ( !empty ( $this->template_settings['order_date_format'] ) ) {
                $date_format = $this->template_settings['order_date_format'];
                $formatted_date = $order_date->format($date_format);
            } else {
                $formatted_date = $order_date->format($order_date, "d-m-Y");
            }

            return $formatted_date;
        }

        /**
         * Creates new invoice number with SQL MAX CAST.
         * @param $order_id
         * @param $number
         */
        protected function create_invoice_number($next_number) {
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
                    $this->order->id, $this->invoice_number_meta_key, $next_number, $this->invoice_number_meta_key
                );
                $success = $wpdb->query($query);
            }

            return $success;
        }

        /**
         * Format the invoice number with prefix and/or suffix.
         * @return mixed
         */
        protected function format_invoice_number() {
            $invoice_number_format = $this->template_settings['invoice_format'];
            $digit_str = "%0" . $this->template_settings['invoice_number_digits'] . "s";
            $this->number = sprintf($digit_str, $this->number);
            $year = date('Y');
            $y = date('y');

            $invoice_number_format = str_replace(
                array( '[prefix]', '[suffix]', '[number]', '[Y]', '[y]' ),
                array( $this->template_settings['invoice_prefix'], $this->template_settings['invoice_suffix'], $this->number, $year, $y ),
                $invoice_number_format );

            add_post_meta($this->order->id, '_bewpi_formatted_invoice_number', $invoice_number_format);

            return $invoice_number_format;
        }

        /**
         * When an invoice gets generated again then the post meta needs to get deleted.
         */
        protected function delete_all_post_meta() {
            delete_post_meta( $this->order->id, '_bewpi_invoice_number' );
            delete_post_meta( $this->order->id, '_bewpi_formatted_invoice_number' );
            delete_post_meta( $this->order->id, '_bewpi_invoice_date' );
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
                        <?php echo $this->template_settings['terms']; ?>
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
                            <?php echo nl2br($this->template_settings['company_details']); ?>
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
                    ", $this->order->id, $this->invoice_number_meta_key
                )
            );

            if (count($results) == 1) {
                return $results[0]->meta_value;
            }
        }

        /**
         * Getter for formatted invoice number.
         * @return mixed
         */
        public function get_formatted_invoice_number() {
            return $this->formatted_number;
        }

        /**
         * Gets the year from the WooCommerce order date.
         * @return bool|string
         */
        public function get_formatted_order_year() {
            return date("Y", strtotime($this->order->order_date));
        }

        /**
         * Get total with or without refunds
         */
        public function get_formatted_total() {
            if( $this->order->get_total_refunded() > 0 ) {
                $total = wc_price( $this->order->get_total() - $this->order->get_total_refunded() );
            }
        }
    }
}