<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'BEWPI_Abstract_Document' ) ) {

    abstract class BEWPI_Abstract_Document {

	    protected $filename;

	    protected $full_path;

	    protected $textdomain = 'be-woocommerce-pdf-invoices';

        /**
         * All options from general tab.
         * @var array
         */
        protected $general_options;

        /**
         * All options from template tab.
         * @var array
         */
        protected $template_options;

	    /**
	     * @var string
	     */
	    protected $header_filename = '';

	    /**
	     * @var string
	     */
        protected $footer_filename = '';

	    /**
	     * @var
	     */
        protected $body_filename;

	    /**
	     * @var
	     */
        protected $style_filename;

	    /**
	     * @var string
	     */
	    protected $header_html = '';

	    /**
	     * @var string
	     */
	    protected $footer_html = '';

	    /**
	     * @var
	     */
	    protected $body_html;

	    /**
	     * @var
	     */
	    protected $style_html;

	    /**
         * @param $order
         */
        public function __construct() {
            $this->general_options      = get_option( 'bewpi_general_settings' );
            $this->template_options     = get_option( 'bewpi_template_settings' );
        }

        /**
         * Generates the invoice with MPDF lib.
         * @param $dest
         * @return string
         */
        protected function generate( $html_sections, $dest ) {
	        set_time_limit(0);
            $mpdf_filename = BEWPI_LIB_DIR . 'mpdf/mpdf.php';
	        include $mpdf_filename;
	        $mpdf = new mPDF('', 'A4', 0, 'opensans', 17, 17, 150, 30, 17, 0, '');
	        $mpdf->useOnlyCoreFonts = false;    // false is default
	        $mpdf->showWatermarkText = false;
	        $mpdf->SetDisplayMode( 'fullpage' );
	        $mpdf->useSubstitutions = true;

	        if ( ! empty ( $html_sections['header'] ) )
		        $mpdf->SetHTMLHeader( $html_sections['header'] );

	        if ( ! empty( $html_sections['footer'] ) )
		        $mpdf->SetHTMLFooter( $html_sections['footer'] );

	        $mpdf->WriteHTML( $html_sections['style'] . $html_sections['body'] );

	        $mpdf->Output(
		        ( $dest === 'F' ) ? $this->full_path : $this->filename,
		        $dest
	        );
        }

        /**
         * Get the invoice if exist and show.
         * @param $download
         */
        public function view( $download ) {
            if ( $download ) {
		        header('Content-type: application / pdf');
		        header('Content-Disposition: attachment; filename="' . $this->filename . '"');
		        header('Content-Transfer-Encoding: binary');
		        header('Content-Length: ' . filesize( $this->filename ));
		        header('Accept-Ranges: bytes');
	        } else {
		        header('Content-type: application/pdf');
		        header('Content-Disposition: inline; filename="' . $this->filename . '"');
		        header('Content-Transfer-Encoding: binary');
		        header('Accept-Ranges: bytes');
	        }
	        @readfile( $this->full_path );
	        exit;
        }

        /**
         * Delete invoice from tmp dir.
         */
        public function delete() {
	        return unlink( $this->full_path );
        }

        /**
         * Checks if the invoice exists.
         * @return bool
         */
        public function exists() {
            return file_exists( $this->full_path );
        }
    }
}