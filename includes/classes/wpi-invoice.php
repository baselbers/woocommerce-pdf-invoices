<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPI_Invoice' ) ) {

    /**
     * Makes the invoice.
     * Class WPI_Invoice
     */
    class WPI_Invoice {

        /**
         * WooCommerce order
         * @var string
         */
        private $order;

        /**
         * Textdomain from the plugin.
         * @var
         */
        private $textdomain;

        /**
         * All settings from general tab.
         * @var array
         */
        private $general_settings;

        /**
         * All settings from template tab.
         * @var array
         */
        private $template_settings;

        /**
         * Invoice number
         * @var
         */
        private $number;

        /**
         * Formatted invoice number with prefix and/or suffix
         * @var
         */
        private $formatted_number;

        /**
         * Invoice number database meta key
         * @var string
         */
        private $invoice_number_meta_key = '_bewpi_invoice_number';

        /**
         * Path to invoice in tmp dir.
         * @var
         */
        private $file;

        /**
         * Creation date.
         * @var
         */
        private $date;

        /**
         * Initialize invoice with WooCommerce order and plugin textdomain.
         * @param string $order
         * @param $textdomain
         */
        public function __construct($order = '', $textdomain) {
            $this->order = $order;
            $this->textdomain = $textdomain;
            $this->general_settings = (array)get_option('general_settings');
            $this->template_settings = (array)get_option('template_settings');

            $this->init();
        }

        /**
         * Gets all the existing invoice data from database or creates new invoice number.
         */
        private function init() {
            $this->number = get_post_meta($this->order->id, '_bewpi_invoice_number', true);
            $this->formatted_number = get_post_meta($this->order->id, '_bewpi_formatted_invoice_number', true);
            $this->date = get_post_meta($this->order->id, '_bewpi_invoice_date', true);
        }

        /**
         * Gets next invoice number based on the user input.
         * @param $order_id
         */
        function get_next_invoice_number($last_invoice_number) {
            // Check if it has been the first of january.
            if ($this->template_settings['reset_invoice_number']) {
                $last_year = $this->template_settings['last_invoiced_year'];

                if ( !empty( $last_year ) && is_numeric($last_year)) {
                    $current_year = getdate()['year'];
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
        public function create_formatted_date() {
            $date_format = $this->template_settings['invoice_date_format'];
            //$date = DateTime::createFromFormat('Y-m-d H:i:s', $this->order->order_date);
            //$date = date( $date_format );

            if ($date_format != "") {
                //$formatted_date = $date->format($date_format);
                $formatted_date = date($date_format);
            } else {
                //$formatted_date = $date->format($date, "d-m-Y");
                $formatted_date = date('d-m-Y');
            }

            add_post_meta($this->order->id, '_bewpi_invoice_date', $formatted_date);

            return $formatted_date;
        }

        /**
         * Creates new invoice number with SQL MAX CAST.
         * @param $order_id
         * @param $number
         */
        function create_invoice_number($next_number) {
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
        private function format_invoice_number() {
            $invoice_number_format = $this->template_settings['invoice_format'];
            $digit_str = "%0" . $this->template_settings['invoice_number_digits'] . "s";
            $this->number = sprintf($digit_str, $this->number);

            $invoice_number_format = str_replace(
                array('[prefix]', '[suffix]', '[number]'),
                array($this->template_settings['invoice_prefix'], $this->template_settings['invoice_suffix'], $this->number),
                $invoice_number_format);

            add_post_meta($this->order->id, '_bewpi_formatted_invoice_number', $invoice_number_format);

            return $invoice_number_format;
        }

        /**
         * When an invoice gets generated again then the post meta needs to get deleted.
         */
        private function delete_all_post_meta() {
            delete_post_meta( $this->order->id, '_bewpi_invoice_number' );
            delete_post_meta( $this->order->id, '_bewpi_formatted_invoice_number' );
            delete_post_meta( $this->order->id, '_bewpi_invoice_date' );
        }

        /**
         * Generates the invoice with MPDF lib.
         * @param $dest
         * @return string
         */
        public function generate($dest) {
            if( !$this->exists() ) {

                $last_invoice_number = $this->template_settings['last_invoice_number'];

                // Get the up following invoice number
                $next_invoice_number = $this->get_next_invoice_number($last_invoice_number);

                // Create new invoice number and insert into database.
                $invoice_number_created = $this->create_invoice_number($next_invoice_number);

                if( $invoice_number_created ) {
                    // Set the current year as the last invoiced.
                    $this->template_settings['last_invoiced_year'] = getdate()['year'];

                    // Get the new invoice number from db.
                    $this->number = $this->get_invoice_number();
                    $this->template_settings['last_invoice_number'] = $this->number;

                    $this->formatted_number = $this->format_invoice_number();

                    update_option('template_settings', $this->template_settings);

                    $this->date = $this->create_formatted_date();

                    // Go generate
                    set_time_limit(0);
                    include WPI_DIR . "lib/mpdf/mpdf.php";

                    $mpdf = new mPDF('', 'A4', 0, '', 17, 17, 20, 50, 0, 0, '');
                    $mpdf->useOnlyCoreFonts = true;    // false is default
                    $mpdf->SetTitle(($this->template_settings['company_name'] != "") ? $this->template_settings['company_name'] . " - Invoice" : "Invoice");
                    $mpdf->SetAuthor(($this->template_settings['company_name'] != "") ? $this->template_settings['company_name'] : "");
                    $mpdf->showWatermarkText = false;
                    $mpdf->SetDisplayMode('fullpage');
                    $mpdf->useSubstitutions = false;

                    ob_start();

                    require_once $this->get_template();

                    $html = ob_get_contents();

                    ob_end_clean();

                    $footer = $this->get_footer();

                    $mpdf->SetHTMLFooter($footer);

                    $mpdf->WriteHTML($html);

                    $file = WPI_TMP_DIR . $this->formatted_number . ".pdf";

                    $mpdf->Output($file, $dest);

                    return $file;
                }
            } else {
                die('Invoice already exists.');
            }
        }

        /**
         * Get the invoice if exist and show.
         * @param $download
         */
        public function view_invoice($download) {
            if ($this->exists()) {
                $file = WPI_TMP_DIR . $this->formatted_number . ".pdf";
                $filename = $this->formatted_number . ".pdf";

                if ($download) {
                    header('Content-type: application / pdf');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Transfer-Encoding: binary');
                    header('Content-Length: ' . filesize($file));
                    header('Accept-Ranges: bytes');
                } else {
                    header('Content-type: application/pdf');
                    header('Content-Disposition: inline; filename="' . $filename . '"');
                    header('Content-Transfer-Encoding: binary');
                    header('Accept-Ranges: bytes');
                }

                @readfile($file);
                exit;

            } else {
                die('No invoice found.');
            }
        }

        /**
         * Delete invoice from tmp dir.
         */
        public function delete() {
            if ($this->exists()) {
                unlink($this->file);
                $this->delete_all_post_meta();
            }
        }

        /**
         * Checks if the invoice exists.
         * @return bool
         */
        public function exists() {
            $this->file = WPI_TMP_DIR . $this->get_formatted_invoice_number() . ".pdf";
            return file_exists($this->file);
        }

        /**
         * Returns MPDF footer.
         * @return string
         */
        function get_footer() {
            ob_start(); ?>

            <table class="foot">
                <tbody>
                <tr>
                    <td class="border" colspan="2">
                        <?php echo $this->template_settings['terms']; ?><br/>
                        <?php if (count($this->order->get_customer_order_notes()) > 0) { ?>
                            <p>
                                <strong><?php _e('Customer note', $this->textdomain); ?> </strong><?php echo $this->order->get_customer_order_notes()[0]->comment_content; ?>
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
                            <strong>Payment</strong> via <?php echo $this->order->payment_method_title; ?>
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
        function get_invoice_number() {
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
         * Getter for formatted date.
         * @return mixed
         */
        public function get_formatted_date() {
            return $this->date;
        }

        /**
         * Gets the year from the WooCommerce order date.
         * @return bool|string
         */
        public function get_formatted_order_year() {
            return date("Y", strtotime($this->order->order_date));
        }

        /**
         * Gets the template from template dir.
         * @return string
         */
        private function get_template() {
            return WPI_TEMPLATES_DIR . $this->template_settings['template_filename'];
        }

        /**
         * Gets the file path.
         * @return mixed
         */
        public function get_file() {
            return $this->file;
        }
    }
}