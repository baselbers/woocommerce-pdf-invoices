<?php
/**
 * PDF invoice template body.
 *
 * This template can be overridden by copying it to youruploadsfolder/woocommerce-pdf-invoices/templates/invoice/simple/yourtemplatename/body.php.
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

$templater                      = BEWPI()->templater();
$order                          = $templater->order;
$invoice                        = $templater->invoice;
$formatted_shipping_address     = $order->get_formatted_shipping_address();
$formatted_billing_address      = $order->get_formatted_billing_address();
$line_items                     = $order->get_items( 'line_item' );
$color                          = $templater->get_option( 'bewpi_color_theme' );
$terms                          = $templater->get_option( 'bewpi_terms' );
?>

<div class="title">
	<div>
		<h2><?php echo $templater->get_option( 'bewpi_title' ); ?></h2>
	</div>
	<div class="watermark">
		<?php
		if ( $templater->get_option( 'bewpi_show_payment_status' ) && $order->is_paid() ) {
			printf( '<h2 class="rubber-stamp">%s</h2>', __( 'Paid', 'woocommerce-pdf-invoices' ) );
		}
		?>
	</div>
</div>
<table cellpadding="0" cellspacing="0">
	<tr class="information">
		<td width="50%">
			<?php echo nl2br( $templater->get_option( 'bewpi_company_address' ) ); ?>
		</td>

		<td>
			<?php
			if ( $templater->get_option( 'bewpi_show_ship_to' ) && ! empty( $formatted_shipping_address ) && $formatted_shipping_address !== $formatted_billing_address && ! $templater->has_only_virtual_products( $line_items ) ) {
				printf( '<strong>%s</strong><br />', __( 'Ship to:', 'woocommerce-pdf-invoices' ) );
				echo $formatted_shipping_address;
			}
			?>
		</td>

		<td>
			<?php echo $formatted_billing_address; ?>
		</td>
	</tr>
</table>
<table cellpadding="0" cellspacing="0">
	<thead>
		<tr class="heading" bgcolor="<?php echo $color; ?>;">
			<th>
				<?php _e( 'Product', 'woocommerce-pdf-invoices' ); ?>
			</th>

			<th>
				<?php _e( 'Qty', 'woocommerce-pdf-invoices' ); ?>
			</th>

			<th>
				<?php _e( 'Price', 'woocommerce-pdf-invoices' ); ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $line_items as $item_id => $item ) {
		$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
		?>

		<tr class="item">
			<td width="50%">
				<?php
				$is_visible        = $product && $product->is_visible();

				echo $item['name'];

				do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );

				$order->display_item_meta( $item );
				$order->display_item_downloads( $item );

				do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );
				?>
			</td>

			<td width="25%">
				<?php echo $item['qty']; ?>
			</td>

			<td width="25%">
				<?php echo $order->get_formatted_line_subtotal( $item ); ?>
			</td>
		</tr>

	<?php } ?>

	<tr class="spacer">
		<td></td>
	</tr>

	<?php
	foreach ( $order->get_order_item_totals() as $key => $total ) {
		$class = str_replace( '_', '-', $key );

		if ( ! $templater->get_option( 'bewpi_show_subtotal' ) && 'cart_subtotal' === $key ) {
			continue;
		}

		if ( 'payment_method' === $key ) {
			continue;
		}
		?>

		<tr class="total">
			<td></td>
			<td class="<?php echo $class; ?>"><?php echo $total['label']; ?></td>
			<td class="<?php echo $class; ?>"><?php echo $total['value']; ?></td>
		</tr>

	<?php } ?>
	</tbody>
</table>

<table class="notes" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<?php
			// Customer notes.
			if ( $templater->get_option( 'bewpi_show_customer_notes' ) ) {
				// Note added by customer.
				$customer_note = method_exists( 'WC_Order', 'get_customer_note' ) ? $order->get_customer_note() : $order->customer_note;
				if ( $customer_note ) {
					printf( '<strong>' . __( 'Note from customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br />', nl2br( $customer_note ) );
				}

				// Notes added by administrator on 'Edit Order' page.
				foreach ( $order->get_customer_order_notes() as $custom_order_note ) {
					printf( '<strong>' . __( 'Note to customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br />', nl2br( $custom_order_note->comment_content ) );
				}
			}
			?>
		</td>
	</tr>

	<tr>
		<td>
			<?php
			// Zero Rated VAT message.
			if ( $templater->get_meta( '_vat_number_is_valid' ) && count( $order->get_tax_totals() ) === 0 ) {
				_e( 'Zero rated for VAT as customer has supplied EU VAT number', 'woocommerce-pdf-invoices' );
				printf( '<br />' );
			}
			?>
		</td>
	</tr>
</table>

<?php if ( $terms ) { ?>
	<div class="terms">
		<table>
			<tr>
				<td style="border: 1px solid #000;">
					<?php echo nl2br( $terms ); ?>
				</td>
			</tr>
		</table>
	</div>
<?php }
