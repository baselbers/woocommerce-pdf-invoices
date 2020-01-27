<?php
/**
 * PDF packing slip body template that will be visible on every page.
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

$packing_slip               = WPI()->templater()->packing_slip;
$formatted_shipping_address = $packing_slip->order->get_formatted_shipping_address();
$theme_color_background     = WPI()->get_option( 'template', 'color_theme_background' );
$theme_color_text           = WPI()->get_option( 'template', 'color_theme_text' );

// Header and footer margin and padding.
$this->mpdf->setAutoTopMargin    = 'stretch';
$this->mpdf->setAutoBottomMargin = 'stretch';
$this->mpdf->autoMarginPadding   = 25; // mm.
?>
<table class="customer-addresses a4-iso">
	<tbody>
	<tr>
		<td class="invoice-to">
			<?php
			printf( '<strong>%s</strong><br>', esc_html__( 'Invoice to:', 'woocommerce-pdf-invoices' ) );
			echo $packing_slip->order->get_formatted_billing_address();

			do_action( 'wpi_after_formatted_billing_address', $packing_slip );
			?>
		</td>
		<td class="ship-to">
			<?php
			if ( WPI()->get_option( 'template', 'show_ship_to' ) && ! WPI()->has_only_virtual_products( $packing_slip->order ) && ! empty( $formatted_shipping_address ) ) {
				printf( '<strong>%s</strong><br>', esc_html__( 'Ship to:', 'woocommerce-pdf-invoices' ) );
				echo $formatted_shipping_address;

				do_action( 'wpi_after_formatted_shipping_address', $packing_slip );
			}
			?>
		</td>
	</tr>
	</tbody>
</table>

<table class="information a4-iso">
	<tbody>
	<tr>
		<td class="invoice-details">
			<h1 class="title"><?php esc_attr_e( 'Packing Slip', 'woocommerce-pdf-invoices' ); ?></h1>
			<span style="color:<?php echo esc_attr( $theme_color_background ); ?>;" class="number"><?php printf( esc_html__( 'Order #%s', 'woocommerce-pdf-invoices' ), esc_html( $packing_slip->order->get_order_number() ) ); ?></span><br>
			<span><?php echo esc_html( $packing_slip->get_formatted_order_date() ); ?></span>
		</td>
	</tr>
	</tbody>
</table>

<table class="line-items a4-iso">
	<thead>
	<tr class="heading">
		<th>
			<?php esc_html_e( 'SKU', 'woocommerce-pdf-invoices' ); ?>
		</th>

		<th>
			<?php esc_html_e( 'Product', 'woocommerce-pdf-invoices' ); ?>
		</th>

		<th>
			<?php esc_html_e( 'Quantity', 'woocommerce-pdf-invoices' ); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $packing_slip->order->get_items( 'line_item' ) as $item_id => $item ) {
		/** @var WC_Product $product */
		$product = $item->get_product();
		?>

		<tr class="item">
			<td width="20%">
				<?php echo $product && $product->get_sku() ? esc_html( $product->get_sku() ) : '-'; ?>
			</td>

			<td width="65%">
				<?php
				echo esc_html( $item['name'] );

				do_action( 'wpi_order_item_meta_start', $item, $this->order );
				do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $this->order );

				WPI()->templater()->display_item_meta( $item );

				do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $this->order );
				?>
			</td>

			<td width="15%">
				<?php echo esc_html( $item['qty'] ); ?>
			</td>
		</tr>

	<?php } ?>
	</tbody>
</table>

<table class="notes a4-iso">
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
