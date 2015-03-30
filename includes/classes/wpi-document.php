<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPI_Document' ) ) {

    class WPI_Document{

        /**
         * @var
         */
        protected $order;

        /**
         * Textdomain from the plugin.
         * @var
         */
        protected $textdomain = 'be-woocommerce-pdf-invoices';

        /**
         * All settings from general tab.
         * @var array
         */
        protected $general_settings;

        /**
         * All settings from template tab.
         * @var array
         */
        protected $template_settings;

        /**
         * Path to invoice in tmp dir.
         * @var
         */
        protected $file;

        /**
         * @param $order
         */
        public function __construct( $order ) {

            $this->order = $order;

            $this->general_settings = (array)get_option('general_settings');

            $this->template_settings = (array)get_option('template_settings');

        }

        /**
         * Generates the invoice with MPDF lib.
         * @param $dest
         * @return string
         */
        public function generate($dest) {
            if( !$this->exists() ) {

                $this->delete_all_post_meta();

                $last_invoice_number = $this->template_settings['last_invoice_number'];

                // Get the up following invoice number
                $next_invoice_number = $this->get_next_invoice_number($last_invoice_number);

                if ($this->template_settings['invoice_number_type'] == 'sequential_number') {

                    // Create new invoice number and insert into database.
                    $this->create_invoice_number($next_invoice_number);

                    // Get the new invoice number from db.
                    $this->number = $this->get_invoice_number();

                } else {

                    $this->number = $this->order->id;

                }

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
            }
            $this->delete_all_post_meta();
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
         * Gets the file path.
         * @return mixed
         */
        public function get_file() {
            return $this->file;
        }

        /**
         * Gets the template from template dir.
         * @return string
         */
        private function get_template() {
            return WPI_TEMPLATES_DIR . $this->template_settings['template'];
        }
    }
}