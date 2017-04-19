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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			$this->order        = wc_get_order( $order_id );
			$this->type         = 'invoice/simple';
			BEWPI()->templater()->set_invoice( $this );
			parent::__construct( $order_id );
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
			return wc_price( $subtotal, array( 'currency' => $this->order->get_order_currency() ) );
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
				return '<del class="total-without-refund">' . wc_price( $this->order->get_total(), array( 'currency' => $this->order->get_order_currency() ) ) . '</del> <ins>' . wc_price( $this->order->get_total() - $this->order->get_total_refunded(), array( 'currency' => $this->order->get_order_currency() ) ) . '</ins>';
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
				return '<del class="total-without-refund">' . wc_price( $this->order->get_total(), array( 'currency' => $this->order->get_order_currency() ) ) . '</del> <ins>' . wc_price( $total_after_refund, array( 'currency' => $this->order->get_order_currency() ) ) . '</ins>';
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
