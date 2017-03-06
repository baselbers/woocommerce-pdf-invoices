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
		 * WooCommerce Order associated with invoice.
		 *
		 * @var WC_Order
		 */
		public $order;

		/**
		 * Output destination mode of mPDF.
		 *
		 * @var string
		 */
		protected $destination;

		/**
		 * Array containing all HTML to generate as PDF.
		 *
		 * @var array
		 */
		protected $html_templates = array();

		/**
		 * Full path to document.
		 *
		 * @var string
		 */
		protected $full_path;

		/**
		 * Document filename.
		 *
		 * @var string.
		 */
		protected $filename;

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
		 * @param string $destination Destination mode for file.
		 * @param bool   $is_paid WooCommerce order paid status.
		 */
		protected function generate( $destination, $is_paid ) {
			// only use default font with version 2.6.2- because we defining font in template.
			$default_font = ( version_compare( BEWPI_VERSION, '2.6.2' ) <= 0 ) ? 'opensans' : '';

			$mpdf_params = apply_filters( 'bewpi_mpdf_options', array(
				'mode'              => '',
				'format'            => '',
				'default_font_size' => 0,
				'default_font'      => $default_font,
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

			$this->select_custom_font( $mpdf );

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

			$html = $this->get_html();
			if ( count( $html ) === 0 ) {
				return;
			}

			if ( ! empty( $html['header'] ) ) {
				$mpdf->SetHTMLHeader( $html['header'] );
			}

			if ( ! empty( $html['footer'] ) ) {
				$mpdf->SetHTMLFooter( $html['footer'] );
			}

			$mpdf = apply_filters( 'bewpi_mpdf', $mpdf );

			$mpdf->WriteHTML( $html['style'] . $html['body'] );

			if ( 'F' === $destination ) {
				$name = $this->full_path;
			} else {
				$name = $this->filename;
			}

			$mpdf->Output( $name, $destination );
		}

		/**
		 * Convert the mPDF fonts path to a relative path that points to our custom uploads directory using '..' parent directory command.
		 *
		 * @param string $fonts_path Path to the mPDF default fonts.
		 *
		 * @return string relative path to the custom fonts directory.
		 */
		private function make_fonts_path_relative( $fonts_path ) {
			$fonts_path_relative = '';

			$dirs = substr( $fonts_path, strpos( $fonts_path, basename( WP_CONTENT_DIR ) ) );
			$dirs_up_count = count( array_filter( explode( '/', $dirs ) ) );
			for ( $i = 0; $i < $dirs_up_count - 1; $i++ ) {
				$fonts_path_relative .= '../';
			}

			return $fonts_path_relative . 'uploads/woocommerce-pdf-invoices/fonts/';
		}

		/**
		 * Select a custom font from custom fonts directory.
		 *
		 * @param mPDF $mpdf mPDF library object.
		 */
		private function select_custom_font( $mpdf ) {
			$fonts_path_relative = $this->make_fonts_path_relative( _MPDF_TTFONTPATH );
			if ( empty( $fonts_path_relative ) ) {
				return;
			}

			$mpdf->fontdata['opensans'] = array(
				'R' => $fonts_path_relative . 'OpenSans-Regular.ttf',
				'B' => $fonts_path_relative . 'OpenSans-Bold.ttf',
				'I' => $fonts_path_relative . 'OpenSans-Italic.ttf',
			);

			foreach ( $mpdf->fontdata as $font_style => $font ) {
				$mpdf->fontdata[ $font_style ] = $font;

				// add to available fonts array.
				$font_styles = array( 'R', 'B', 'I', 'BI' );
				foreach ( $font_styles as $style ) {
					if ( isset( $font[ $style ] ) && $font[ $style ] ) {
						// no suffix for regular style.
						$mpdf->available_unifonts[] = $font_style . trim( $style, 'R' );
					}
				}
			}

			$mpdf->default_available_fonts = $mpdf->available_unifonts;
		}

		/**
		 * Output HTML file to buffer.
		 *
		 * @param string $full_path to html file.
		 *
		 * @return string
		 */
		private function buffer( $full_path ) {
			ob_start();
			require $full_path;
			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		}

		/**
		 * Get PDF html.
		 *
		 * @return array
		 */
		private function get_html() {
			do_action( 'bewpi_before_output_template_to_buffer', array( 'order_id' => $this->order->id ) );

			$html = array();
			foreach ( $this->html_templates as $section => $full_path ) {
				if ( 'style' === $section ) {
					$html[ $section ] = '<style>' . $this->buffer( $full_path ) . '</style>';
					continue;
				}

				$html[ $section ] = $this->buffer( $full_path );
			}

			do_action( 'bewpi_after_output_template_to_buffer' );

			return $html;
		}

		/**
		 * View pdf file.
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
		 * Delete pdf document.
		 *
		 * @param string $full_path absolute path to pdf document.
		 */
		protected static function delete( $full_path ) {
			if ( file_exists( $full_path ) ) {
				wp_delete_file( $full_path );
			}
		}

		/**
		 * Full path to pdf invoice.
		 *
		 * @return string full path to pdf invoice.
		 */
		public function get_full_path() {
			return $this->full_path;
		}

		/**
		 * Check if pdf exists within uploads folder.
		 *
		 * @param string $full_path to pdf file.
		 *
		 * @return bool/string false when pdf does not exist else full path to pdf.
		 */
		public static function exists( $full_path ) {
			// pdf file exists?
			if ( ! file_exists( $full_path ) ) {
				return false;
			}

			return $full_path;
		}
	}
}
