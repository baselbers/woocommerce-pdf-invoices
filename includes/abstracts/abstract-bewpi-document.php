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
		 * Type of document like invoice, packing slip or credit note.
		 *
		 * @var string type of document.
		 */
		protected $type;

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
		 * Array containing all template files.
		 *
		 * @var array
		 */
		protected $template = array();

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
			$templater    = BEWPI()->templater();
			$templater->set_order( $this->order );
			$this->template         = $templater->get_template( $this->type );
			$this->general_options  = get_option( 'bewpi_general_settings' ); // @todo remove.
			$this->template_options = get_option( 'bewpi_template_settings' ); // @todo remove and use 'templater()'.
		}

		/**
		 * Generate document.
		 *
		 * @param string $destination Destination mode for file.
		 */
		public function generate( $destination = 'F' ) {
			$order_id = bewpi_get_id( $this->order );

			do_action( 'bewpi_before_invoice_content', $order_id );

			// Only use default font with version 2.6.2- because we defining font in template.
			$default_font    = ( version_compare( WPI_VERSION, '2.6.2' ) <= 0 ) ? 'opensans' : '';
			$is_new_template = 'minimal' === $this->template_options['bewpi_template_name'];

			$mpdf_params = apply_filters( 'bewpi_mpdf_options', array(
				'mode'              => '',
				'format'            => '',
				'default_font_size' => 0,
				'default_font'      => $default_font,
				'margin_left'       => ( $is_new_template ) ? 0 : 14,
				'margin_right'      => ( $is_new_template ) ? 0 : 14,
				'margin_top'        => ( $is_new_template ) ? 0 : 14,
				'margin_bottom'     => 0,
				'margin_header'     => ( $is_new_template ) ? 0 : 14,
				'margin_footer'     => ( $is_new_template ) ? 0 : 6,
				'orientation'       => 'P',
			) );
			$mpdf        = new mPDF(
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

			$mpdf = apply_filters( 'bewpi_mpdf', $mpdf );

			// add company logo image as a variable.
			$wp_upload_dir = wp_upload_dir();
			$image_url     = $this->template_options['bewpi_company_logo'];
			if ( ! empty( $image_url ) ) {
				// use absolute path due to probability of (local)host misconfiguration.
				// problems with shared hosting when one ip is configured to multiple users/environments.
				$image_path         = str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir['basedir'], $image_url );
				$mpdf->company_logo = file_get_contents( $image_path );
			}

			// Show legacy paid watermark.
			if ( strpos( $this->template_options['bewpi_template_name'], 'micro' ) !== false && $this->template_options['bewpi_show_payment_status'] && strpos( $this->type, 'invoice' ) !== false && $this->order->is_paid() ) {
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
			$mpdf->autoMarginPadding   = ( $is_new_template ) ? 20 : 10;
			$mpdf->useOnlyCoreFonts    = false;
			$mpdf->useSubstitutions    = true;

			$html = $this->get_html();
			if ( count( $html ) === 0 ) {
				BEWPI()->logger()->error( sprintf( 'PDF generation aborted. No HTML for PDF in %1$s:%2$s', __FILE__,  __LINE__ ) );
				return;
			}

			if ( ! empty( $html['header'] ) ) {
				$mpdf->SetHTMLHeader( $html['header'] );
			}

			if ( ! empty( $html['footer'] ) ) {
				$mpdf->SetHTMLFooter( $html['footer'] );
			}

			$mpdf->WriteHTML( $html['style'] . $html['body'] );

			if ( 'F' === $destination ) {
				$name = $this->full_path;
			} else {
				$name = $this->filename;
			}

			do_action( 'bewpi_after_invoice_content', $order_id );

			$mpdf->Output( $name, $destination );
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
			$html = array();
			foreach ( $this->template as $section => $full_path ) {
				if ( 'style' === $section ) {
					$html[ $section ] = '<style>' . $this->buffer( $full_path ) . '</style>';
					continue;
				}

				$html[ $section ] = $this->buffer( $full_path );
			}

			return $html;
		}

		/**
		 * View PDF file.
		 *
		 * @param string $full_path absolute path to PDF file.
		 */
		public static function view( $full_path ) {
			$general_options = get_option( 'bewpi_general_settings' );
			$type            = ( 'browser' === $general_options['bewpi_view_pdf'] ) ? 'inline' : 'attachment';

			header( 'Content-type: application/pdf' );
			header( 'Content-Disposition: ' . $type . '; filename="' . basename( $full_path ) . '"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Content-Length: ' . filesize( $full_path ) );
			header( 'Accept-Ranges: bytes' );

			readfile( $full_path );
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
		 * Date format from user option or get default WordPress date format.
		 *
		 * @return string
		 */
		public function get_date_format() {
			$date_format = $this->template_options['bewpi_date_format'];
			if ( ! empty( $date_format ) ) {
				return (string) $date_format;
			}

			return (string) get_option( 'date_format' );
		}

		/**
		 * Order date formatted with user option format and localized.
		 *
		 * @return string
		 */
		public function get_formatted_order_date() {
			// WC backwards compatibility.
			$order_date = method_exists( 'WC_Order', 'get_date_created' ) ? $this->order->get_date_created() : $this->order->order_date;

			return date_i18n( $this->get_date_format(), strtotime( $order_date ) );
		}

		/**
		 * Check if pdf exists within uploads folder.
		 *
		 * @param string $full_path to pdf file.
		 *
		 * @return bool/string false when pdf does not exist else full path to pdf.
		 */
		public static function exists( $full_path ) {
			if ( ! file_exists( $full_path ) ) {
				return false;
			}

			return $full_path;
		}
	}
}
