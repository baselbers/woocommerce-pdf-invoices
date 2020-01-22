<?php
/**
 * PDF packing slip template body.
 *
 * This template can be overridden by copying it to youruploadsfolder/woocommerce-pdf-invoices/templates/packing-slip/simple/yourtemplatename/body.php.
 *
 * HOWEVER, on occasion WooCommerce PDF Invoices will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  Bas Elbers
 * @package WooCommerce_PDF_Invoices/Templates
 * @version 0.0.1
 */

$packing_slip           = WPI()->templater()->packing_slip;
$theme_color_text       = WPI()->get_option( 'template', 'color_theme_text' );
$theme_color_background = WPI()->get_option( 'template', 'color_theme_background' );
$line_items             = $packing_slip->order->get_items( 'line_item' );

// Header and footer margin and padding.
$this->mpdf->setAutoTopMargin    = 'stretch';
$this->mpdf->setAutoBottomMargin = 'stretch';
$this->mpdf->autoMarginPadding   = 25; // mm.
?>

<div class="title">
	<h2><?php esc_attr_e( 'Packing Slip', 'woocommerce-pdf-invoices' ); ?></h2>
</div>
<table>
	<tr class="information">
		<td class="primary">
			<?php
			printf( __( 'Order Date: %s', 'woocommerce-pdf-invoices' ), $packing_slip->get_formatted_order_date() );
			echo '<br>';
			printf( __( 'Order Number: %s', 'woocommerce-pdf-invoices' ), $packing_slip->order->get_order_number() );

			$shipping_method = $packing_slip->order->get_shipping_method();
			if ( $shipping_method ) {
				echo '<br>';
				printf( __( 'Shipping Method: %s', 'woocommerce-pdf-invoices' ), $shipping_method );
			}

			$payment_method = $packing_slip->order->get_payment_method_title();
			if ( $payment_method ) {
				echo '<br>';
				printf( __( 'Payment Method: %s', 'woocommerce-pdf-invoices' ), $payment_method );
			}
			?>
		</td>

		<td class="bill-to">
			<?php
			printf( '<strong>%s</strong><br>', esc_html__( 'Bill to:', 'woocommerce-pdf-invoices' ) );
			echo $packing_slip->order->get_formatted_billing_address();

			do_action( 'wpi_after_formatted_billing_address', $packing_slip );
			?>
		</td>

		<td class="ship-to">
			<?php
			$formatted_shipping_address = $packing_slip->order->get_formatted_shipping_address();
			if ( WPI()->get_option( 'template', 'show_ship_to' ) && ! WPI()->has_only_virtual_products( $packing_slip->order ) && ! empty( $formatted_shipping_address ) ) {
				printf( '<strong>%s</strong><br>', esc_html__( 'Ship to:', 'woocommerce-pdf-invoices' ) );
				echo $formatted_shipping_address;

				do_action( 'wpi_after_formatted_shipping_address', $packing_slip );
			}
			?>
		</td>
	</tr>
</table>

<table>
	<thead>
	<tr class="heading" style="background-color:<?php echo esc_attr( $theme_color_background ); ?>;">
		<th style="color:<?php echo esc_attr( $theme_color_text ); ?>;">
			<?php esc_html_e( 'SKU', 'woocommerce-pdf-invoices' ); ?>
		</th>

		<th style="color:<?php echo esc_attr( $theme_color_text ); ?>;">
			<?php esc_html_e( 'Product', 'woocommerce-pdf-invoices' ); ?>
		</th>

		<th style="color:<?php echo esc_attr( $theme_color_text ); ?>;">
			<?php esc_html_e( 'Quantity', 'woocommerce-pdf-invoices' ); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	/**
	 * Item annotation.
	 *
	 * @var WC_Order_Item_Product $item
	 */
	foreach ( $line_items as $item_id => $item ) {
		/**
		 * Product annotation.
		 *
		 * @var WC_Product $product
		 */
		$product = $item->get_product();
		?>

		<tr class="item">
			<td>
				<?php echo $product && $product->get_sku() ? esc_html( $product->get_sku() ) : '-'; ?>
			</td>

			<td>
				<?php
				echo esc_html( $item['name'] );

				do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $this->order );
				WPI()->templater()->display_item_meta( $item );
				do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $this->order );
				?>
			</td>

			<td>
				<?php echo esc_html( $item['qty'] ); ?>
			</td>
		</tr>

	<?php } ?>
	</tbody>
</table>

<table class="notes">
	<tr>
		<td>
			<?php
			// Customer notes.
			if ( WPI()->get_option( 'template', 'show_customer_notes' ) ) {
				// Note added by customer.
				$customer_note = BEWPI_WC_Order_Compatibility::get_customer_note( $packing_slip->order );
				if ( $customer_note ) {
					printf( '<strong>' . __( 'Note from customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br>', nl2br( $customer_note ) );
				}

				// Notes added by administrator on 'Edit Order' page.
				foreach ( $packing_slip->order->get_customer_order_notes() as $custom_order_note ) {
					printf( '<strong>' . __( 'Note to customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br>', nl2br( $custom_order_note->comment_content ) );
				}
			}
			?>
		</td>
	</tr>
</table>
