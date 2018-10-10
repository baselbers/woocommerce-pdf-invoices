<?php
/**
 * Invoice class.
 *
 * Handling invoice specific functionality.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     2.5.4
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'BEWPI_Invoice' ) ) {
	/**
	 * Class BEWPI_Invoice.
	 */
	class BEWPI_Invoice extends BEWPI_Abstract_Invoice {
		/**
		 * BEWPI_Invoice constructor.
		 *
		 * @param int $order_id WooCommerce Order ID.
		 */
		public function __construct( $order_id ) {
			$this->order = wc_get_order( $order_id );
			$this->type  = 'invoice/simple';
			WPI()->templater()->set_invoice( $this );
			parent::__construct( $order_id );
		}

		/**
		 * Initialize hooks.
		 */
		public static function init_hooks() {
			if ( is_admin() ) {
				self::admin_init_hooks();
			}
		}

		/**
		 * Initialize admin hooks.
		 */
		private static function admin_init_hooks() {
			// Delete PDF invoice file and data when deleting order.
			add_action( 'wp_trash_post', array( __CLASS__, 'delete' ) );
			add_action( 'before_delete_post', array( __CLASS__, 'delete' ) );
		}

		/**
		 * Delete invoice file and data to prevent invoice number conflicts.
		 *
		 * @param int $post_id WordPress post ID.
		 */
		public static function delete( $post_id ) {
			if ( 'shop_order' !== get_post_type( $post_id ) ) {
				return;
			}

			parent::delete( $post_id );
		}

		/**
		 * Check if PDF invoice is sent to client.
		 *
		 * @since 2.9.4
		 * @return bool
		 */
		public function is_sent() {
			$order_id = BEWPI_WC_Order_Compatibility::get_id( $this->order );
			$is_sent  = get_post_meta( $order_id, 'bewpi_pdf_invoice_sent', true );

			// Backporting.
			if ( false === $is_sent ) {
				return true;
			}

			return 1 === absint( $is_sent ) ? true : false;
		}

		/**
		 * Get invoice number type.
		 *
		 * @return string
		 */
		public static function get_number_type() {
			$number_type = WPI()->get_option( 'template', 'invoice_number_type' );

			return (string) $number_type;
		}

		/**
		 * Get invoice details.
		 */
		public function get_invoice_info() {

			return apply_filters( 'wpi_invoice_information_meta', array(
				'invoice_number'  => array(
					'title' => __( 'Invoice #:', 'woocommerce-pdf-invoices' ),
					'value' => $this->get_formatted_number(),
				),
				'invoice_date'    => array(
					'title' => __( 'Invoice Date:', 'woocommerce-pdf-invoices' ),
					'value' => $this->get_formatted_date(),
				),
				'order_date'      => array(
					'title' => __( 'Order Date:', 'woocommerce-pdf-invoices' ),
					'value' => $this->get_formatted_order_date(),
				),
				'order_number'    => array(
					'title' => __( 'Order Number:', 'woocommerce-pdf-invoices' ),
					'value' => $this->order->get_order_number(),
				),
				'payment_method'  => array(
					'title' => __( 'Payment Method:', 'woocommerce-pdf-invoices' ),
					'value' => $this->order->get_payment_method_title(),
				),
				'shipping_method' => array(
					'title' => __( 'Shipping Method:', 'woocommerce-pdf-invoices' ),
					'value' => $this->order->get_shipping_method(),
				),
			), $this );
		}

		/**
		 * Formatted custom order subtotal.
		 * Shipping including or excluding tax.
		 *
		 * @deprecated No longer used within template files. Custom templates should be replaced.
		 *
		 * @return string
		 */
		public function get_formatted_subtotal() {
			$subtotal = $this->order->get_subtotal();

			// add shipping to subtotal if shipping is taxable.
			if ( (bool) $this->template_options['bewpi_shipping_taxable'] ) {
				$subtotal += $this->order->get_total_shipping();
			}

			$subtotal -= $this->order->get_total_discount();

			return wc_price( $subtotal, array( 'currency' => $this->order->get_currency() ) );
		}

		/**
		 * Formatted custom order total.
		 *
		 * @deprecated No longer used within template files. Custom templates should be replaced.
		 *
		 * @return string
		 */
		public function get_formatted_total() {
			if ( $this->order->get_total_refunded() > 0 ) {
				return '<del class="total-without-refund">' . wc_price( $this->order->get_total(), array( 'currency' => $this->order->get_currency() ) ) . '</del> <ins>' . wc_price( $this->order->get_total() - $this->order->get_total_refunded(), array( 'currency' => $this->order->get_currency() ) ) . '</ins>';
			}

			return $this->order->get_formatted_order_total();
		}

		/**
		 * Custom order total.
		 *
		 * @deprecated No longer used within template files. Custom templates should be replaced.
		 * @return string
		 */
		public function get_total() {
			if ( $this->order->get_total_refunded() > 0 ) {
				$total_after_refund = $this->order->get_total() - $this->order->get_total_refunded();

				return '<del class="total-without-refund">' . wc_price( $this->order->get_total(), array( 'currency' => $this->order->get_currency() ) ) . '</del> <ins>' . wc_price( $total_after_refund, array( 'currency' => $this->order->get_currency() ) ) . '</ins>';
			}

			return $this->order->get_formatted_order_total();
		}

		/**
		 * Custom order subtotal.
		 *
		 * @deprecated No longer used within template files. Custom templates should be replaced.
		 * @return float|mixed|void
		 */
		public function get_subtotal() {
			$subtotal = $this->order->get_subtotal();

			if ( (bool) $this->template_options['bewpi_shipping_taxable'] ) {
				$subtotal += $this->order->get_total_shipping();
			}

			$subtotal -= $this->order->get_total_discount();

			return $subtotal;
		}
	}
}
