<?php
/**
 * BEWPI_Packing_Slip class.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     0.0.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BEWPI_Packing_Slip' ) ) {

	/**
	 * Class BEWPI_Packing_Slip.
	 */
	class BEWPI_Packing_Slip extends BEWPI_Abstract_Document {
		/**
		 * BEWPI_Packing_Slip constructor.
		 *
		 * @param int $order_id WooCommerce Order ID.
		 */
		public function __construct( $order_id ) {
			$this->order    = wc_get_order( $order_id );
			$this->type     = 'packing-slip/simple';
			$this->filename = apply_filters( 'bewpi_pdf_packing_slip_filename', sprintf( 'packing-slip-%s.pdf', BEWPI_WC_Order_Compatibility::get_id( $this->order ) ), $this );
			WPI()->templater()->set_packing_slip( $this );
			parent::__construct();
		}

		/**
		 * Initialize packing slips hooks.
		 */
		public static function init_hooks() {
			add_action( 'admin_init', array( __CLASS__, 'admin_init_hooks' ) );
		}

		/**
		 * Initialize admin hooks.
		 */
		public static function admin_init_hooks() {
			add_action( 'woocommerce_admin_order_actions_end', array( __CLASS__, 'add_packing_slip_pdf' ) );
		}

		/**
		 * Add packing slip link to 'Shop Order' page.
		 *
		 * @param WC_Order $order WooCommerce order object.
		 */
		public static function add_packing_slip_pdf( $order ) {
			if ( ! self::packing_slips_enabled() ) {
				return;
			}

			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );

			// View Packing Slip.
			$action = 'view_packing_slip';
			$url    = wp_nonce_url( add_query_arg( array(
				'post'         => $order_id,
				'action'       => 'edit',
				'bewpi_action' => $action,
			), admin_url( 'post.php' ) ), $action, 'nonce' );

			$url = apply_filters( 'bewpi_pdf_packing_slip_url', $url, $order_id, $action );

			printf( '<a href="%1$s" title="%2$s" class="button shop-order-action packing-slip wpi" target="_blank">%2$s</a>', $url, __( 'View packing slip', 'woocommerce-pdf-invoices' ) );
		}

		/**
		 * Determine whether to show packing slip PDF icon on Shop Order page.
		 *
		 * @return bool
		 */
		private static function packing_slips_enabled() {
			if ( WPI()->get_option( 'template', 'disable_packing_slips' ) ) {
				return false;
			}

			// There is no packing slip available for micro template.
			$template_name = WPI()->get_option( 'template', 'template_name' );
			if ( strpos( $template_name, 'micro' ) !== false ) {
				return false;
			}

			return true;
		}

		/**
		 * Get path for Packing Slip PDF.
		 *
		 * @return string
		 */
		public function get_pdf_path() {
			// Yearly sub-folders.
			if ( WPI()->get_option( 'template', 'reset_counter_yearly' ) ) {
				$year     = date_i18n( 'Y', current_time( 'timestamp' ) );
				$pdf_path = $year . '/' . $this->filename;
			} else {
				// One folder for all invoices.
				$pdf_path = $this->filename;
			}

			return $pdf_path;
		}


		/**
		 * Generate Packing Slip.
		 *
		 * @param string $destination PDF generation mode.
		 *
		 * @return string
		 */
		public function generate( $destination = 'F' ) {

			if ( 'F' === $destination ) {
				$pdf_path        = $this->get_pdf_path();
				$this->full_path = WPI_ATTACHMENTS_DIR . '/' . $pdf_path;

				if ( parent::exists( $this->full_path ) ) {
					parent::delete( $this->full_path );
				}
			}

			do_action( 'bewpi_before_document_generation', $this->type, BEWPI_WC_Order_Compatibility::get_id( $this->order ) );

			parent::generate( $destination );

			return $this->full_path;
		}
	}
}
