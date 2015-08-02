<?php
if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly

if ( ! class_exists( 'BEWPI_Global_Invoice' ) ) {

	class BEWPIPRO_Invoice_Global extends BEWPI_Abstract_Invoice {

		public $orders = array();

		private $subtotal, $subtotal_incl_tax, $item_line_total_tax, $total_discount, $total_shipping, $total, $total_refunded, $total_refunded_tax = 0.00;

		public $customer_notes = array();

		public $order_notes = array();

		private $taxes = array();

		private $currency;

		protected $type = 'global';

		private $template_dir_name;

		private $fees = array();

		public function __construct( $orders_ids ) {
			$this->order = wc_create_order();

			$taxes = array();
			foreach ( $orders_ids as $i => $order_id ) {
				$order                      = wc_get_order( $order_id );
				$this->orders[]             = $order;
				$this->total_discount       += $order->get_total_discount();
				$this->total_shipping       += $order->get_total_shipping();
				$this->total                += $order->get_total();
				$this->total_refunded_tax   += $order->get_total_tax_refunded();
				$this->total_refunded       += $order->get_total_refunded();

				// add products to order
				foreach ( $order->get_items( 'line_item ' ) as $item_id => $item ) {
					$this->subtotal += $item['line_total'];
					$this->item_line_total_tax += $item['line_tax'];

					$product = $order->get_product_from_item( $item );
					$this->order->add_product( $product, $item['qty'] );
				}

				// add fees to order
				foreach ( $order->get_fees() as $fee_key => $fee ) {
					$this->order->add_fee( $fee );
				}

				// unique tax
				foreach ( $order->get_taxes() as $tax ) {

					// only add tax when unique
					if ( ! in_array ( $tax['rate_id'], $taxes ) ) {
						$this->taxes[] = $tax;
					} else {
						$this->update_tax( $tax );
					}

					$taxes[] = $tax['rate_id'];
				}

				// sort taxes by tax rate id
				usort( $this->taxes, array( $this, "sort_taxes" ) );

				// only need to set once from the first order
				if ( $i === 0 ) {
					$this->order->set_address( array(
						'first_name' => $order->shipping_first_name,
						'last_name'  => $order->shipping_last_name,
						'company'    => $order->shipping_company,
						'address_1'  => $order->shipping_address_1,
						'address_2'  => $order->shipping_address_2,
						'city'       => $order->shipping_city,
						'state'      => $order->shipping_state,
						'postcode'   => $order->shipping_postcode,
						'country'    => $order->shipping_country
					), 'shipping' );

					$this->order->set_address( array(
						'first_name' => $order->billing_first_name,
						'last_name'  => $order->billing_last_name,
						'company'    => $order->billing_company,
						'address_1'  => $order->billing_address_1,
						'address_2'  => $order->billing_address_2,
						'city'       => $order->billing_city,
						'state'      => $order->billing_state,
						'postcode'   => $order->billing_postcode,
						'country'    => $order->billing_country
					), 'billing' );

					//$this->payment_method   = $order->payment_method_title;
					$this->currency         = $order->get_order_currency();
				}

				// add billing email
				if ( $this->order->billing_email !== "" && get_post_meta( $this->order->id, '_billing_email', true ) !== "" )
					update_post_meta( $order->id, '_billing_email', $this->order->billing_email );

				// add billing phone
				if ( $this->order->billing_phone !== "" && get_post_meta( $this->order->id, '_billing_phone', true ) !== "" )
					update_post_meta( $order->id, '_billing_phone', $this->order->billing_phone );
			}

			$this->subtotal -= $this->total_discount;
			$this->subtotal -= $this->total_shipping;
			$this->subtotal_incl_tax += $this->item_line_total_tax;

			$this->total -= $this->total_discount;

			// update order values
			update_post_meta( $this->order->id, '_cart_discount', $this->total_discount );
			update_post_meta( $this->order->id, '_order_shipping_tax', $this->total_shipping );
			$this->order->set_total( $this->total );

			$taxes_count = count( $this->get_taxes() );
			parent::__construct( $this->order->id, $this->type, $taxes_count );
		}

		protected function sort_taxes( $a, $b ) {
			return $a['rate_id'] > $b['rate_id'];
		}

		public function save( $dest, $html_templates = array() ) {
			$template_name = apply_filters( 'bewpi_invoice_template_name', $this->template_name, $this->type );
			$this->template_dir_name    = BEWPI_TEMPLATES_INVOICES_DIR . $this->type . '/' . $template_name . '/';

			$html_templates     = array(
				"header"    => $this->template_dir_name . 'header.php',
				"footer"    => $this->template_dir_name . 'footer.php',
				"body"      => $this->template_dir_name . 'body.php',
				"style"     => $this->template_dir_name . 'style.css'
			);

			parent::save( $dest, $html_templates );
		}

		public function get_subtotal( $incl_tax = false ) {
			return ( ! $incl_tax ) ? $this->subtotal : $this->subtotal_incl_tax;
		}

		public function get_currency() {
			return $this->currency;
		}

		/**
		 * Get the total amount with or without refunds
		 * @return string
		 */
		public function get_total() {
			if ( $this->total_refunded > 0 ) {
				$total_before_refund = $this->total + $this->total_refunded;
				return '<del class="total-without-refund">' . strip_tags( wc_price( $total_before_refund, array( 'currency' => $this->order->get_order_currency() ) ) ) . '</del> <ins>' . wc_price( $this->total, array( 'currency' => $this->order->get_order_currency() ) ) . '</ins>';
			}

			return $this->total;
		}

		public function get_total_after_refunded() {
			return $this->total;
		}

		public function get_total_discount() {
			return $this->total_discount;
		}

		public function get_total_shipping() {
			return $this->total_shipping;
		}

		public function get_taxes() {
			return $this->taxes;
		}

		public function get_order_currency() {
			return $this->currency;
		}

		public function get_fees() {
			return $this->fees;
		}

		public function get_total_refunded() {
			return $this->total_refunded;
		}

		private function update_tax( $tax ) {
			foreach ( $this->taxes as $i => $t ) {
				if ( $t['rate_id'] === $tax['rate_id'] ) {
					$this->taxes[$i]['tax_amount'] += $tax['tax_amount'];
					$this->taxes[$i]['shipping_tax_amount'] += $tax['shipping_tax_amount'];
				}
			}
		}
	}
}