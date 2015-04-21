<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_Document' ) ) {

    class BEWPI_Document{

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
        protected $filename;

	    protected $title;

	    protected $author;

	    protected $file;

        /**
         * @param $order
         */
        public function __construct() {
            $this->general_options      = get_option( 'bewpi_general_settings' );
            $this->template_options     = get_option( 'bewpi_template_settings' );
	        $this->title                = $this->template_options['bewpi_company_name'] . " - Invoice";
	        $this->author               = $this->template_options['bewpi_company_name'];
        }

        /**
         * Generates the invoice with MPDF lib.
         * @param $dest
         * @return string
         */
        protected function generate( $dest, $document ) {
	        set_time_limit(0);
	        include BEWPI_LIB_DIR . 'mpdf/mpdf.php';
	        $mpdf = new mPDF('', 'A4', 0, '', 17, 17, 20, 50, 0, 0, '');
	        $mpdf->useOnlyCoreFonts = true;    // false is default
	        $mpdf->SetTitle( $this->title );
	        $mpdf->SetAuthor( $this->author );
	        $mpdf->showWatermarkText = false;
	        $mpdf->SetDisplayMode('fullpage');
	        $mpdf->useSubstitutions = false;
	        ob_start();
		        require_once $document->template_filename;
	        $html = ob_get_contents();
	        ob_end_clean();
	        $mpdf->SetHTMLFooter( $document->footer );
	        $mpdf->WriteHTML( $html );
	        $mpdf->Output( $document->filename, $dest );
        }

        /**
         * Get the invoice if exist and show.
         * @param $download
         */
        public function view( $download ) {
            if ( $download ) {
		        header('Content-type: application / pdf');
		        header('Content-Disposition: attachment; filename="' . $this->file . '"');
		        header('Content-Transfer-Encoding: binary');
		        header('Content-Length: ' . filesize( $this->filename ));
		        header('Accept-Ranges: bytes');
	        } else {
		        header('Content-type: application/pdf');
		        header('Content-Disposition: inline; filename="' . $this->file . '"');
		        header('Content-Transfer-Encoding: binary');
		        header('Accept-Ranges: bytes');
	        }

	        @readfile( $this->filename );
	        exit;
        }

        /**
         * Delete invoice from tmp dir.
         */
        public function delete() {
	        return unlink( $this->filename );
        }

        /**
         * Checks if the invoice exists.
         * @return bool
         */
        public function exists() {
            return file_exists( $this->filename );
        }

        /**
         * Gets the file path.
         * @return mixed
         */
        public function get_filename() {
            return $this->filename;
        }
    }
}