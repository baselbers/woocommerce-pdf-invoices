<?php
/**
 * Document Class for different types of (invoice) documents.
 *
 * @author      Bas Elbers
 * @category    Abstract Class
 * @package     BE_WooCommerce_PDF_Invoices/Abstracts
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BEWPI_Abstract_Document' ) ) {
	/**
	 * Class BEWPI_Abstract_Document.
	 */
	abstract class BEWPI_Abstract_Document {
		/**
		 * Document filename.
		 *
		 * @var string.
		 */
		protected $filename;

		/**
		 * Full path to document.
		 *
		 * @var string
		 */
		protected $full_path;

		/**
		 * General options.
		 *
		 * @var array
		 */
		protected $general_options = array();

		/**
		 * Template options.
		 *
		 * @var array
		 */
		protected $template_options = array();

		/**
		 * BEWPI_Abstract_Document constructor.
		 */
		public function __construct() {
			$this->general_options = get_option( 'bewpi_general_settings' );
			$this->template_options = get_option( 'bewpi_template_settings' );
		}

		/**
		 * Generate document.
		 *
		 * @param array  $html_sections Html output.
		 * @param string $dest Destination shortcode for file.
		 * @param bool   $is_paid WooCommerce order paid status.
		 */
		protected function generate( $html_sections, $dest, $is_paid ) {
			require_once BEWPI_DIR . 'lib/mpdf/mpdf.php';
			require_once BEWPI_DIR . 'lib/mpdf/vendor/autoload.php';

			$mpdf_params = apply_filters( 'bewpi_mpdf_options', array(
				'mode'              => '',
				'format'            => '',
				'default_font_size' => 0,
				'default_font'      => 'opensans',
				'margin_left'       => 14,
				'margin_right'      => 14,
				'margin_top'        => 14,
				'margin_bottom'     => 0,
				'margin_header'     => 14,
				'margin_footer'     => 6,
				'orientation'       => 'P',
			) );
			$mpdf         = new mPDF(
				$mpdf_params['mode'],
				$mpdf_params['format'],
				$mpdf_params['default_font_size'],
				$mpdf_params['default_font'],
				$mpdf_params['margin_left'],
				$mpdf_params['margin_right'],
				$mpdf_params['margin_top'],
				$mpdf_params['margin_bottom'],
				$mpdf_params['margin_header'],
				$mpdf_params['margin_footer'],
				$mpdf_params['orientation']
			);

			// add company logo image as a variable.
			$wp_upload_dir = wp_upload_dir();
			$image_url     = $this->template_options['bewpi_company_logo'];
			if ( ! empty( $image_url ) ) {
				// use absolute path due to probability of (local)host misconfiguration.
				// problems with shared hosting when one ip is configured to multiple users/environments.
				$image_path         = str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir['basedir'], $image_url );
				$mpdf->company_logo = file_get_contents( $image_path );
			}

			// show paid watermark.
			if ( (bool) $this->template_options['bewpi_show_payment_status'] && $is_paid ) {
				$mpdf->SetWatermarkText( __( 'Paid', 'woocommerce-pdf-invoices' ) );
				$mpdf->showWatermarkText  = true;
				$mpdf->watermarkTextAlpha = '0.2';
				$mpdf->watermarkImgBehind = false;
			}

			// debug.
			if ( (bool) $this->general_options['bewpi_mpdf_debug'] ) {
				$mpdf->debug           = true;
				$mpdf->showImageErrors = true;
			}

			$mpdf->SetDisplayMode( 'fullpage' );
			$mpdf->autoScriptToLang    = true;
			$mpdf->autoLangToFont      = true;
			$mpdf->setAutoTopMargin    = 'stretch';
			$mpdf->setAutoBottomMargin = 'stretch';
			$mpdf->autoMarginPadding   = 10;
			$mpdf->useOnlyCoreFonts    = false;
			$mpdf->useSubstitutions    = true;

			if ( ! empty( $html_sections['header'] ) ) {
				$mpdf->SetHTMLHeader( $html_sections['header'] );
			}

			if ( ! empty( $html_sections['footer'] ) ) {
				$mpdf->SetHTMLFooter( $html_sections['footer'] );
			}

			$mpdf->WriteHTML( $html_sections['style'] . $html_sections['body'] );

			$mpdf     = apply_filters( 'bewpi_mpdf', $mpdf );
			$filename = ( 'F' === $dest ) ? $this->full_path : $this->filename;

			$mpdf->Output( $filename, $dest );
		}

		/**
		 * View document.
		 */
		public function view() {
			if ( 'browser' === $this->general_options['bewpi_view_pdf'] ) {
				header( 'Content-type: application/pdf' );
				header( 'Content-Disposition: inline; filename = "' . $this->filename . '"' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Length: ' . filesize( $this->full_path ) );
				header( 'Accept-Ranges: bytes' );
			} else {
				header( 'Content-type: application / pdf' );
				header( 'Content-Disposition: attachment; filename="' . $this->filename . '"' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Length: ' . filesize( $this->full_path ) );
				header( 'Accept-Ranges: bytes' );
			}

			readfile( $this->full_path );
			exit;
		}

		/**
		 * Delete document.
		 */
		public function delete() {
			wp_delete_file( $this->full_path );
		}

		/**
		 * Check if document exists.
		 *
		 * @param string $full_path Full path to document.
		 *
		 * @return bool
		 */
		public static function exists( $full_path ) {
			return file_exists( $full_path );
		}

		/**
		 * Get full path to document.
		 *
		 * @return string full path of document.
		 */
		public function get_full_path() {
			return $this->full_path;
		}
	}
}
