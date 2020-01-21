<?php
/**
 * Document Class for different types of (invoice) documents.
 *
 * @author      Bas Elbers
 * @category    Abstract Class
 * @package     BE_WooCommerce_PDF_Invoices/Abstracts
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BEWPI_Abstract_Document' ) ) {
	/**
	 * Class BEWPI_Abstract_Document.
	 */
	abstract class BEWPI_Abstract_Document {

		/**
		 * ID of document.
		 *
		 * @var int.
		 */
		protected $id;

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
		 * PDF library object.
		 *
		 * @var mPDF $mpdf .
		 */
		private $mpdf;

		/**
		 * BEWPI_Abstract_Document constructor.
		 */
		public function __construct() {
			$templater = WPI()->templater();
			$templater->set_order( $this->order );
			$this->template         = $templater->get_template( $this->type );
			$this->general_options  = get_option( 'bewpi_general_settings' ); // @todo remove.
			$this->template_options = get_option( 'bewpi_template_settings' ); // @todo remove and use 'templater()'.
		}

		/**
		 * Generate document.
		 *
		 * @param string $destination Destination mode for file.
		 *
		 * @throws MpdfException
		 */
		public function generate( $destination = 'F' ) {
			// Use ttfontdata from uploads folder.
			define( '_MPDF_TTFONTDATAPATH', WPI_UPLOADS_DIR . '/mpdf/ttfontdata/' );

			do_action( 'bewpi_before_invoice_content', $this->order->get_id() );

			$mpdf_params = apply_filters( 'bewpi_mpdf_options', array(
				'mode'              => 'utf-8',
				'format'            => 'A4',
				'default_font_size' => 0,
				'default_font'      => '',
				'margin_left'       => 0,
				'margin_right'      => 0,
				'margin_top'        => 0,
				'margin_bottom'     => 0,
				'margin_header'     => 0,
				'margin_footer'     => 0,
				'orientation'       => 'P',
			) );
			$this->mpdf  = new mPDF(
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

			// Add company logo image as a variable.
			// As of 3.0.9 we've improved the media upload settings feature and so bewpi_company_logo contains the id of the attachment.
			$attachment = WPI()->get_option( 'template', 'company_logo' );
			if ( ! empty( $attachment ) ) {
				// use absolute path due to probability of (local)host misconfiguration.
				// problems with shared hosting when one ip is configured to multiple users/environments.
				$attachment_path = get_attached_file( $attachment );
				if ( false !== $attachment_path ) {
					$this->mpdf->company_logo = file_get_contents( $attachment_path );
				}
			}

			// mPDF debugging.
			if ( WPI()->get_option( 'general', 'debug' ) ) {
				$this->mpdf->debug           = true;
				$this->mpdf->showImageErrors = true;
			}

			// Font.
			$this->mpdf->autoScriptToLang = true;
			$this->mpdf->autoLangToFont   = true;
			$this->mpdf->baseScript       = 1;
			$this->mpdf->autoVietnamese   = true;
			$this->mpdf->autoArabic       = true;
			$this->mpdf->useSubstitutions = true;

			// Template.
			$html = $this->get_html();
			if ( count( $html ) === 0 ) {
				WPI()->logger()->error( sprintf( 'PDF generation aborted. No HTML for PDF in %1$s:%2$s', __FILE__, __LINE__ ) );

				return;
			}

			if ( ! empty( $html['header'] ) ) {
				$this->mpdf->SetHTMLHeader( $html['header'] );
			}

			if ( ! empty( $html['footer'] ) ) {
				$this->mpdf->SetHTMLFooter( $html['footer'] );
			}

			$this->mpdf = apply_filters( 'bewpi_mpdf', $this->mpdf, $this );

			$this->mpdf->WriteHTML( $html['style'] . $html['body'] );

			do_action( 'bewpi_after_invoice_content', $this->order->get_id() );

			$this->mpdf = apply_filters( 'bewpi_mpdf_after_write', $this->mpdf, $this );

			if ( 'F' === $destination ) {
				$name = $this->full_path;
			} else {
				$name = $this->filename;
			}

			$this->mpdf->Output( $name, $destination );

			if ( 'F' !== $destination ) {
				exit;
			}
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
			$type = 'browser' === (string) WPI()->get_option( 'general', 'view_pdf' ) ? 'inline' : 'attachment';

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
			$order_date = BEWPI_WC_Order_Compatibility::get_date_created( $this->order );

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

		/**
		 * Get document type.
		 *
		 * @return string.
		 */
		public function get_type() {
			return $this->type;
		}

		/**
		 * Get filename.
		 *
		 * @return string
		 */
		public function get_filename() {
			return $this->filename;
		}
	}
}
