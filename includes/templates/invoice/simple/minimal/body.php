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

$templater                      = WPI()->templater();
$order                          = $templater->order;
$invoice                        = $templater->invoice;
$formatted_shipping_address     = $order->get_formatted_shipping_address();
$formatted_billing_address      = $order->get_formatted_billing_address();
$headers                        = $invoice->get_line_item_column_header_data();
$headers_count                  = apply_filters( 'wpi_invoice_headers_count', count( $headers ), $headers );
$color                          = $templater->get_option( 'bewpi_color_theme' );
$terms                          = $templater->get_option( 'bewpi_terms' );
?>

<div class="title">
	<div>
		<h2><?php echo esc_html( $templater->get_option( 'bewpi_title' ) ); ?></h2>
	</div>
	<div class="watermark">
		<?php
		if ( $templater->get_option( 'bewpi_show_payment_status' ) && $order->is_paid() ) {
			printf( '<h2 class="green">%s</h2>', esc_html__( 'Paid', 'woocommerce-pdf-invoices' ) );
		}

		do_action( 'wpi_watermark_end', $order, $invoice );
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
				printf( '<strong>%s</strong><br />', esc_html__( 'Ship to:', 'woocommerce-pdf-invoices' ) );
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
		<tr class="heading" bgcolor="<?php echo esc_attr( $color ); ?>;">
			<?php
			foreach ( $headers as $id => $value ) {
				// Calculate table cell width for headers on right half of the table.
				$width = ( 'description' === $key ) ? 50 : 50 / ( $headers_count - 1 );

				if ( is_array( $value ) ) {
					foreach ( $value as $val ) {
						printf( '<th class="%s">%s</th>', $id, $val );
					}
					continue;
				}

				printf( '<th class="%s">%s</th>', $id, $value );
			}
			?>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $invoice->get_line_items() as $index => $line_item ) {
		echo '<tr class="item">';

		foreach ( $headers as $header_id => $header_label ) {

			if ( isset( $line_item[ $header_id ] ) ) {
				$value = $line_item[ $header_id ];

				if ( is_array( $value ) ) {
					foreach ( $value as $val ) {
						printf( '<td class="%s">%s</td>', $header_id, $val );
					}
					continue;
				}

				printf( '<td class="%s">%s</td>', $header_id, $value );
			}

		}

		echo '</tr>';
	} // End foreach().
	?>

	<tr class="spacer">
		<td></td>
	</tr>

	</tbody>
</table>

<table cellpadding="0" cellspacing="0">
	<tbody>

	<?php
	foreach ( $invoice->get_order_item_totals() as $key => $total ) {
		$class = str_replace( '_', '-', $key );
		?>

		<tr class="total">
			<td width="50%"></td>
			<td width="25%" align="left" class="border <?php echo esc_attr( $class ); ?>"><?php echo esc_html( $total['label'] ); ?></td>
			<td width="25%" align="right" class="border <?php echo esc_attr( $class ); ?>"><?php echo str_replace( '&nbsp;', '', $total['value'] ); ?></td>
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
				$customer_note = BEWPI_WC_Order_Compatibility::get_customer_note( $order );
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
			if ( 'true' === $templater->get_meta( '_vat_number_is_valid' ) && count( $order->get_tax_totals() ) === 0 ) {
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
