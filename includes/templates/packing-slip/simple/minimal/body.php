<?php
$templater           = WPI()->templater();
$packing_slip        = $templater->packing_slip;
$theme_color         = WPI()->get_option( 'template', 'color_theme' );
$is_theme_text_black = WPI()->get_option( 'template', 'theme_text_black' );
$line_items          = $packing_slip->order->get_items( 'line_item' );

// Header and footer margin and padding.
$this->mpdf->setAutoTopMargin    = 'stretch';
$this->mpdf->setAutoBottomMargin = 'stretch';
$this->mpdf->autoMarginPadding   = 25; // mm.
?>
<div class="body">
	<table class="customer-address">
		<tbody>
		<tr>

			<td class="title-information">
				<h1 class="title"><?php esc_attr_e( 'Packing Slip', 'woocommerce-pdf-invoices' ); ?></h1>
				<?php
				printf( __( 'Order Date: %s', 'woocommerce-pdf-invoices' ), $packing_slip->get_formatted_order_date() );
				printf( '<br />' );
				printf( __( 'Order Number: %s', 'woocommerce-pdf-invoices' ), $packing_slip->order->get_order_number() );

				$shipping_method = $packing_slip->order->get_shipping_method();
				if ( $shipping_method ) {
					printf( '<br />' );
					printf( __( 'Shipping Method: %s', 'woocommerce-pdf-invoices' ), $shipping_method );
				}

				$payment_method = $packing_slip->order->get_payment_method_title();
				if ( $payment_method ) {
					printf( '<br />' );
					printf( __( 'Payment Method: %s', 'woocommerce-pdf-invoices' ), $payment_method );
				}
				?>
			</td>
			<td class="bill-to">
				<?php
				printf( '<strong>%s</strong><br />', esc_html__( 'Bill to:', 'woocommerce-pdf-invoices' ) );
				echo $packing_slip->order->get_formatted_billing_address();

				do_action( 'wpi_after_formatted_billing_address', $packing_slip );
				?>
			</td>
			<td class="ship-to">
				<?php
				printf( '<strong>%s</strong><br />', esc_html__( 'Ship to:', 'woocommerce-pdf-invoices' ) );
				echo $packing_slip->order->get_formatted_shipping_address();

				do_action( 'wpi_after_formatted_shipping_address', $packing_slip );
				?>
			</td>
		</tr>
		</tbody>
	</table>
	<table class="products">
		<thead>
		<tr class="heading" style="background-color: <?php echo esc_attr( $theme_color ); ?>">
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
		foreach ( $line_items as $item_id => $item ) {
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

	<table class="customer-notes">
		<!-- Notes & terms -->
		<tr>
			<td class="border">
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
</div>