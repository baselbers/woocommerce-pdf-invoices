<?php
$templater           = WPI()->templater();
$invoice             = $templater->invoice;
$columns             = $invoice->get_columns();
$theme_color         = $this->template_options['bewpi_color_theme'];
$is_theme_text_black = $this->template_options['bewpi_theme_text_black'];
$columns_count       = 4;

if ( WPI()->get_option( 'template', 'show_payment_status' ) && $invoice->order->is_paid() ) {
	$this->packing_slip->showWatermarkText  = true;
	$this->packing_slip->watermarkTextAlpha = '0.2';
	$this->packing_slip->watermarkImgBehind = false;
}
?>

<table>
	<tbody>
	<tr>
		<td class="invoice-to">
			<b><?php _e( 'Invoice to:', 'woocommerce-pdf-invoices' ); ?></b><br/>
			<?php
			echo $invoice->order->get_formatted_billing_address() . '<br/>';
			// Billing phone.
			$billing_phone = method_exists( 'WC_Order', 'get_billing_phone' ) ? $invoice->order->get_billing_phone() : $invoice->order->billing_phone;
			echo $billing_phone ? sprintf( __( 'Phone: %s', 'woocommerce-pdf-invoices' ), $billing_phone ) : '';
			?>
		</td>
		<?php
		$formatted_shipping_address = $invoice->order->get_formatted_shipping_address();
		if ( $this->template_options['bewpi_show_ship_to'] && ! empty( $formatted_shipping_address ) && ! $this->has_only_virtual_products() ) { ?>
			<td class="ship-to">
				<b><?php _e( 'Ship to:', 'woocommerce-pdf-invoices' ); ?></b><br/>
				<?php echo $formatted_shipping_address; ?>
			</td>
		<?php } ?>
	</tr>
	</tbody>
</table>

<table>
	<tbody>
	<tr>
		<td class="invoice-details">
			<h1 class="title"><?php echo WPI()->templater()->get_option( 'bewpi_title' ); ?></h1>
			<span class="number"
			      style="color: <?php echo ( $is_theme_text_black ) ? 'black' : $theme_color; ?>;"><?php echo $this->get_formatted_number(); ?></span><br/>
			<span><?php echo $this->get_formatted_invoice_date(); ?></span><br/>
			<span><?php echo "Order #" . $invoice->get_number(); ?></span>
		</td>
		<td class="total-amount" bgcolor="<?php echo $theme_color; ?>" <?php if ( $is_theme_text_black ) {
			echo 'style="color: black;"';
		} ?>>
			<h1 class="amount"><?php echo wc_price( $invoice->order->get_total() - $invoice->order->get_total_refunded(), array( 'currency' => $invoice->order->get_currency() ) ); ?></h1>
			<p><?php echo WPI()->templater()->get_option( 'bewpi_intro_text' ); ?></p>
		</td>
	</tr>
	</tbody>
</table>

<div class="product-lines">
	<table class="products">
		<thead>
		<tr class="heading" bgcolor="<?php echo esc_attr( $color ); ?>;">
			<?php
			foreach ( $columns as $key => $data ) {
				$templater->display_header_recursive( $key, $data );
			}
			?>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $invoice->get_columns_data() as $index => $row ) {
			echo '<tr class="item">';

			// Display row data.
			foreach ( $row as $column_key => $data ) {
				$templater->display_data_recursive( $column_key, $data );
			}

			echo '</tr>';
			echo '<tr class="item">';

			// Display row data.
			foreach ( $row as $column_key => $data ) {
				$templater->display_data_recursive( $column_key, $data );
			}

			echo '</tr>';
		}
		?>

		<tr class="spacer">
			<td></td>
		</tr>

		</tbody>
	</table>

	<table style="page-break-inside: avoid">
		<tbody>
		<?php
		$order_item_totals = $invoice->get_order_item_totals();
		$rowspan           = count( $order_item_totals );
		foreach ( $order_item_totals as $key => $total ) {
			$class = str_replace( '_', '-', $key );
			?>

			<tr class="total">
				<td>
					<?php do_action( 'wpi_order_item_totals_left', $key, $invoice ); ?>
				</td>

				<td class="border line-spacing <?php echo esc_attr( $class ); ?>">
					<?php echo $total['label']; ?>
				</td>

				<td class="border line-spacing <?php echo esc_attr( $class ); ?>">
					<?php echo str_replace( '&nbsp;', '', $total['value'] ); ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>

<div class="extra-info">
	<table id="terms-notes">
		<tr>
			<td><?php echo nl2br( WPI()->templater()->get_option( 'bewpi_terms' ) ); ?></td>
			<td class="border" colspan="3">

				<?php
				if ( $this->template_options['bewpi_show_customer_notes'] ) :
					// Note added by customer.
					$customer_note = method_exists( 'WC_Order', 'get_customer_note' ) ? $invoice->order->get_customer_note() : $invoice->order->customer_note;
					if ( $customer_note ) {
						echo '<p><strong>' . __( 'Customer note', 'woocommerce-pdf-invoices' ) . ' </strong> ' . $customer_note . '</p>';
					}
					// Notes added by administrator on order details page.
					$customer_order_notes = $invoice->order->get_customer_order_notes();
					if ( count( $customer_order_notes ) > 0 ) {
						echo '<p><strong>' . __( 'Customer note', 'woocommerce-pdf-invoices' ) . ' </strong>' . $customer_order_notes[0]->comment_content . '</p>';
					}
				endif;
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php
				// Zero Rated VAT message.
				if ( 'true' === WPI()->get_meta( $invoice, '_vat_number_is_valid' ) && count( $invoice->get_tax_totals() ) === 0 ) {
					echo esc_html__( 'Zero rated for VAT as customer has supplied EU VAT number', 'woocommerce-pdf-invoices' ) . '<br>';
				}
				?>
			</td>
		</tr>
	</table>
</div>