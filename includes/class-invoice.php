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

defined( 'ABSPATH' ) || exit;

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
			$this->order  = wc_get_order( $order_id );
			$this->type   = 'invoice/simple';
			$this->number = $this->calculate_number();
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
		 * @return bool
		 * @since 2.9.4
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
		 * Delete invoice PDF files.
		 *
		 * @param int $from_number Invoice number where to start from.
		 */
		private function delete_pdf_invoices( $from_number = 0 ) {
			global $wpdb;

			if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
				// get formatted numbers by year and greater then given invoice number.
				$files = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm3.meta_value AS pdf_path FROM wp_postmeta pm1
						INNER JOIN wp_postmeta pm2 ON pm1.post_id = pm2.post_id
  						INNER JOIN wp_postmeta pm3 ON pm1.post_id = pm3.post_id
					WHERE (pm1.meta_key = '_bewpi_invoice_date' AND YEAR(pm1.meta_value) = %d)
      						AND (pm2.meta_key = '_bewpi_invoice_number' AND pm2.meta_value >= %d)
      						AND (pm3.meta_key = '_bewpi_invoice_pdf_path')",
					(int) $this->year,
					$from_number
				) ); // db call ok; no-cache ok.
			} else {
				// get formatted numbers greater then given invoice number.
				$files = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm2.meta_value AS pdf_path FROM wp_postmeta pm1
						INNER JOIN wp_postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE (pm1.meta_key = '_bewpi_invoice_number' AND pm1.meta_value >= %d)
      						AND (pm2.meta_key = '_bewpi_invoice_pdf_path')",
					$from_number
				) ); // db call ok; no-cache ok.
			}

			// delete pdf files.
			foreach ( $files as $pdf_path ) {
				parent::delete( WPI_ATTACHMENTS_DIR . '/' . $pdf_path );
			}
		}

		/**
		 * Delete invoice post meta information.
		 *
		 * @param int $from_number Invoice number from which to delete.
		 *
		 * @return false|int
		 */
		private function delete_invoice_meta( $from_number = 0 ) {
			global $wpdb;

			if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
				// delete by year and greater then given invoice number.
				$query = $wpdb->prepare(
					"DELETE pm1, pm2, pm3 FROM $wpdb->postmeta pm1
  						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
  						INNER JOIN $wpdb->postmeta pm3 ON pm1.post_id = pm3.post_id
					WHERE (pm1.meta_key = %s AND YEAR(pm1.meta_value) = %d)
      						AND (pm2.meta_key = %s AND pm2.meta_value >= %d)
      						AND (pm3.meta_key = %s)",
					'_bewpi_invoice_date',
					(int) $this->year,
					'_bewpi_invoice_number',
					$from_number,
					'_bewpi_invoice_pdf_path'
				);
			} else {
				// delete by greater then given invoice number.
				$query = $wpdb->prepare(
					"DELETE pm1, pm2 FROM $wpdb->postmeta pm1
						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE (pm1.meta_key = %s AND pm1.meta_value >= %d)
							AND (pm2.meta_key = %s OR pm2.meta_key = %s)",
					'_bewpi_invoice_number',
					$from_number,
					'_bewpi_invoice_date',
					'_bewpi_invoice_pdf_path'
				);
			}

			return $wpdb->query( $query ); // db call ok; no-cache ok. WPCS: unprepared SQL OK.
		}

		/**
		 * Counter reset.
		 */
		private function do_counter_reset() {
			$next_number = get_transient( 'bewpi_next_invoice_number' );
			if ( false !== $next_number ) {
				$this->delete_pdf_invoices( $next_number );
				$this->delete_invoice_meta( $next_number );
				delete_transient( 'bewpi_next_invoice_number' );
			}

			return (int) $next_number;
		}

		/**
		 * Calculate invoice number.
		 *
		 * $return int
		 */
		public function calculate_number() {
			// Using order number as the invoice number?
			if ( 'woocommerce_order_number' === WPI()->get_option( 'template', 'invoice_number_type' ) ) {
				return $this->order->get_order_number();
			}

			// User did invoice number reset?
			if ( true === (bool) WPI()->get_option( 'template', 'reset_counter' ) ) {
				$number = $this->do_counter_reset();
				if ( 0 === $number ) {
					throw new RuntimeException( 'Could not reset invoice number counter.' );
				}

				return $number;
			}

			// Increment invoice number.
			$number = (int) get_option( 'bewpi_invoice_number' );
			if ( false === (bool) $number ) {
				$number = 1;
			}

			$number++;

			return $number;
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
		 * @return string
		 * @deprecated No longer used within template files. Custom templates should be replaced.
		 *
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
		 * @return string
		 * @deprecated No longer used within template files. Custom templates should be replaced.
		 *
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
		 * @return string
		 * @deprecated No longer used within template files. Custom templates should be replaced.
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
		 * @return float|mixed|void
		 * @deprecated No longer used within template files. Custom templates should be replaced.
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
