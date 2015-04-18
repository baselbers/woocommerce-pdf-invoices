<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_Document' ) ) {

    class BEWPI_Document{

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
        protected $general_options;

        /**
         * All settings from template tab.
         * @var array
         */
        protected $template_options;

        /**
         * Path to invoice in tmp dir.
         * @var
         */
        protected $file;

	    protected $title;

	    protected $author;

        /**
         * @param $order
         */
        public function __construct( $order_id ) {
            $this->order                = wc_get_order( $order_id );
            $this->general_options      = (array) get_option( 'bewpi_general_settings' );
            $this->template_options     = (array) get_option( 'bewpi_template_settings' );
	        $this->title                = $this->template_options['bewpi_company_name'] . " - Invoice";
	        $this->author               = $this->template_options['bewpi_company_name'];
        }

        /**
         * Generates the invoice with MPDF lib.
         * @param $dest
         * @return string
         */
        public function generate( $dest, $document ) {
            if ( $this->exists() ) die( 'Invoice already exists.' );

	        // Go generate
	        set_time_limit(0);
	        include BEWPI_LIB_DIR . "mpdf/mpdf.php";
	        $mpdf = new mPDF('', 'A4', 0, '', 17, 17, 20, 50, 0, 0, '');
	        $mpdf->useOnlyCoreFonts = true;    // false is default
	        $mpdf->SetTitle( $this->title );
	        $mpdf->SetAuthor( $this->author );
	        $mpdf->showWatermarkText = false;
	        $mpdf->SetDisplayMode('fullpage');
	        $mpdf->useSubstitutions = false;
	        ob_start();
		        require_once $this->get_template();
	        $html = ob_get_contents();
	        ob_end_clean();
	        $mpdf->SetHTMLFooter( $document->footer );
	        $mpdf->WriteHTML( $html );
	        $file = BEWPI_INVOICES_DIR . $document->formatted_number . ".pdf";
	        $mpdf->Output( $file, $dest );
	        return $file;
        }

        /**
         * Get the invoice if exist and show.
         * @param $download
         */
        public function view_invoice($download) {
            if ($this->exists()) {
                $file = BEWPI_INVOICES_DIR . $this->formatted_number . ".pdf";
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
            $this->file = BEWPI_TEMPLATES_DIR . $this->get_formatted_invoice_number() . ".pdf";
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
            return BEWPI_TEMPLATES_DIR . $this->template_settings['template_filename'];
        }
    }
}