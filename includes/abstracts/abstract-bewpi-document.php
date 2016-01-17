<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'BEWPI_Abstract_Document' ) ) {

    abstract class BEWPI_Abstract_Document {

	    /**
	     * @var string
	     */
	    protected $filename;

	    /**
	     * @var string
	     */
	    protected $full_path;

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
         * Constructor
         */
        public function __construct() {
            $this->general_options      = get_option( 'bewpi_general_settings' );
            $this->template_options     = get_option( 'bewpi_template_settings' );
        }

        /**
         * Generates the invoice with MPDF lib.
         * @param string $dest
         * @return string
         */
        protected function generate( $html_sections, $dest, $paid ) {
	        set_time_limit(0);
	        include_once BEWPI_LIB_DIR . 'mpdf/mpdf.php';

	        $mpdf_options = $this->get_mpdf_options();
	        $mpdf = new mPDF(
		        $mpdf_options['mode'],               // mode
		        $mpdf_options['format'],             // format
		        $mpdf_options['default_font_size'],  // default_font_size
		        $mpdf_options['default_font'],       // default_font
		        $mpdf_options['margin_left'],        // margin_left
		        $mpdf_options['margin_right'],       // margin_right
		        $mpdf_options['margin_top'],         // margin_top
		        $mpdf_options['margin_bottom'],      // margin_bottom
		        $mpdf_options['margin_header'],      // margin_header
		        $mpdf_options['margin_footer'],      // margin_footer
		        $mpdf_options['orientation']         // orientation
	        );

	        // show paid watermark
	        if ( (bool)$this->template_options[ 'bewpi_show_payment_status' ] && $paid ) {
		        $mpdf->SetWatermarkText( __( 'Paid', 'woocommerce-pdf-invoices' ) );
		        $mpdf->showWatermarkText = true;
	        }

	        // debugging
	        if ( (bool) $this->general_options[ 'bewpi_mpdf_debug' ] ) {
		        $mpdf->debug = true;
		        $mpdf->showImageErrors = true;
	        }

	        $mpdf->SetDisplayMode( 'fullpage' );
	        $mpdf->autoScriptToLang = true;
	        $mpdf->autoLangToFont = true;
	        $mpdf->setAutoTopMargin = 'stretch';
	        $mpdf->setAutoBottomMargin = 'stretch';
	        $mpdf->autoMarginPadding = 10;
	        $mpdf->useOnlyCoreFonts = false;

	        if ( ! empty ( $html_sections['header'] ) )
		        $mpdf->SetHTMLHeader( $html_sections['header'] );

	        if ( ! empty( $html_sections['footer'] ) )
		        $mpdf->SetHTMLFooter( $html_sections['footer'] );

	        $mpdf->WriteHTML( $html_sections['style'] . $html_sections['body'] );

	        $mpdf       = apply_filters( 'bewpi_mpdf', $mpdf );
			$filename   = ( $dest === 'F' ) ? $this->full_path : $this->filename;

	        $mpdf->Output( $filename, $dest );
        }

        /**
         * Get the invoice if exist and show.
         */
        public function view() {
            if ( $this->general_options[ 'bewpi_view_pdf' ] === 'browser' ) {
	            header( 'Content-type: application/pdf' );
	            header( 'Content-Disposition: inline; filename = "' . $this->filename . '"' );
	            header( 'Content-Transfer-Encoding: binary' );
	            header( 'Content-Length: ' . filesize( $this->full_path ) );
	            header( 'Accept-Ranges: bytes' );
	        } else {
	            header('Content-type: application / pdf');
	            header('Content-Disposition: attachment; filename="' . $this->filename . '"');
	            header('Content-Transfer-Encoding: binary');
	            header('Content-Length: ' . filesize( $this->full_path ));
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

	    private function get_mpdf_options() {
		    return apply_filters( 'bewpi_mpdf_options', array(
			    'mode' => '',
			    'format' => '',
			    'default_font_size' => 0,
			    'default_font' => 'opensans',
			    'margin_left' => 14,
			    'margin_right' => 14,
			    'margin_top' => 14,
			    'margin_bottom' => 0,
			    'margin_header' => 14,
			    'margin_footer' => 6,
			    'orientation' => 'P'
		    ));
	    }
    }
}