<?php
$templater           = WPI()->templater();
$packing_slip        = $templater->packing_slip;
$theme_color         = WPI()->get_option( 'template', 'color_theme' );
$is_theme_text_black = WPI()->get_option( 'template', 'theme_text_black' );
$line_items          = $packing_slip->order->get_items( 'line_item' );
$intro_text          = WPI()->get_option( 'template', 'intro_text' );

?>
<table class="two-column customer">
	<tbody>
	<tr>
		<td class="address small-font" width="50%">
			<?php
			printf( '<strong>%s</strong><br />', __( 'Bill to:', 'woocommerce-pdf-invoices' ) );
			echo $packing_slip->order->get_formatted_shipping_address();

			do_action( 'wpi_after_formatted_billing_address', $packing_slip );
			?>
		</td>
		<td>
			<?php
			printf( '<strong>%s</strong><br />', __( 'Ship to:', 'woocommerce-pdf-invoices' ) );
			echo $packing_slip->order->get_formatted_billing_address();

			do_action( 'wpi_after_formatted_shipping_address', $packing_slip );
			?>
		</td>
	</tr>
	</tbody>
</table>
<table class="invoice-head">
	<tbody>
	<tr>
		<td class="invoice-details">
			<h1 class="title"><?php _e( 'Packing Slip', 'woocommerce-pdf-invoices' ); ?></h1>
			<span class="number"
			      style="color: <?php echo $is_theme_text_black ? 'black' : $theme_color; ?>;"><?php echo $packing_slip->get_formatted_order_date(); ?></span><br/>
			<span><?php echo $packing_slip->order->get_order_number(); ?></span>
		</td>

		<?php if ( ! empty( $intro_text ) ) { ?>
			<td class="total-amount" bgcolor="<?php echo esc_attr( $theme_color ); ?>">
				<p><?php echo WPI()->get_option( 'template', 'intro_text' ); ?></p>
			</td>
		<?php } ?>

	</tr>
	</tbody>
</table>
<table class="products small-font">
	<thead>
	<tr class="heading">
		<th>
			<?php _e( 'SKU', 'woocommerce-pdf-invoices' ); ?>
		</th>

		<th>
			<?php _e( 'Product', 'woocommerce-pdf-invoices' ); ?>
		</th>

		<th>
			<?php _e( 'Qty', 'woocommerce-pdf-invoices' ); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $line_items as $item_id => $item ) {
		/** @var WC_Product $product */
		$product = $item->get_product();
		?>

		<tr class="item">
			<td width="10%">
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

			<td width="25%">
				<?php echo esc_html( $item['qty'] ); ?>
			</td>
		</tr>

	<?php } ?>
	</tbody>
</table>

<table id="terms-notes">
	<!-- Notes & terms -->
	<tr>
		<td class="border">
			<?php echo nl2br( WPI()->get_option( 'template', 'terms' ) ); ?><br/>
			<?php
			if ( WPI()->get_option( 'template', 'show_customer_notes' ) ) {
				// Note added by customer.
				$customer_note = method_exists( 'WC_Order', 'get_customer_note' ) ? $this->order->get_customer_note() : $this->order->customer_note;
				if ( $customer_note ) {
					echo '<p><strong>' . __( 'Customer note', 'woocommerce-pdf-invoices' ) . ' </strong> ' . $customer_note . '</p>';
				}
				// Notes added by administrator on order details page.
				$customer_order_notes = $this->order->get_customer_order_notes();
				if ( count( $customer_order_notes ) > 0 ) {
					echo '<p><strong>' . __( 'Customer note', 'woocommerce-pdf-invoices' ) . ' </strong>' . $customer_order_notes[0]->comment_content . '</p>';
				}
			}
			?>
		</td>
	</tr>
</table>
