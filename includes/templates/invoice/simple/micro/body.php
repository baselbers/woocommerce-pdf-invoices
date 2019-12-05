<?php
$templater              = WPI()->templater();
$invoice                = $templater->invoice;
$columns                = $invoice->get_columns();
$theme_color_background = WPI()->get_option( 'template', 'color_theme_background' );
$theme_color_text       = WPI()->get_option( 'template', 'color_theme_text' );

// Paid watermark.
if ( WPI()->get_option( 'template', 'show_payment_status' ) && $invoice->order->is_paid() ) {
	$this->mpdf->SetWatermarkText( __( 'Paid', 'woocommerce-pdf-invoices' ) );
	$this->mpdf->showWatermarkText  = true;
	$this->mpdf->watermarkTextAlpha = '0.2';
	$this->mpdf->watermarkImgBehind = false;
}

// Header and footer margin and padding.
$this->mpdf->setAutoTopMargin    = 'stretch';
$this->mpdf->setAutoBottomMargin = 'stretch';
$this->mpdf->autoMarginPadding   = 25; // mm.
?>

<table>
	<tbody>
	<tr>
		<td class="invoice-to">
			<b><?php _e( 'Invoice to:', 'woocommerce-pdf-invoices' ); ?></b><br>
			<?php
			echo $invoice->order->get_formatted_billing_address() . '<br>';

			do_action( 'wpi_after_formatted_billing_address', $invoice );
			?>
		</td>
		<?php
		if ( WPI()->get_option( 'template', 'show_ship_to' ) && ! WPI()->has_only_virtual_products( $invoice->order ) && ! empty( $formatted_shipping_address ) ) {
			printf( '<strong>%s</strong><br />', esc_html__( 'Ship to:', 'woocommerce-pdf-invoices' ) );
			echo $formatted_shipping_address;

			do_action( 'wpi_after_formatted_shipping_address', $invoice );
		}
		?>
	</tr>
	</tbody>
</table>

<table>
	<tbody>
	<tr>
		<td class="invoice-details">
			<h1 class="title"><?php echo esc_html( WPI()->get_option( 'template', 'title' ) ); ?></h1>
			<span class="number"
			      style="color:<?php echo esc_attr( $theme_color_background ); ?>;"><?php echo esc_html( $invoice->get_formatted_number() ); ?></span><br/>
			<span><?php echo esc_html( $this->get_formatted_invoice_date() ); ?></span><br/>
			<span><?php printf( esc_html__( 'Order #%s', 'woocommerce-pdf-invoices' ), esc_html( $invoice->order->get_order_number() ) ); ?></span>
		</td>
		<td class="total-amount"
		    bgcolor="<?php echo esc_attr( $theme_color_background ); ?>" style="color:<?php echo esc_attr( $theme_color_text ); ?>">
			<h1 class="amount"><?php echo wc_price( $invoice->order->get_total() - $invoice->order->get_total_refunded(), array( 'currency' => $invoice->order->get_currency() ) ); ?></h1>
			<p><?php echo WPI()->get_option( 'template', 'intro_text' ); ?></p>
		</td>
	</tr>
	</tbody>
</table>

<div class="product-lines">
	<table class="products">
		<thead>
		<tr class="heading" style="background-color:<?php echo esc_attr( $theme_color_background ); ?>;">
			<?php
			foreach ( $columns as $key => $data ) {
				if ( is_array( $data ) ) {
					foreach ( $data as $k => $d ) {
						printf( '<th class="%1$s" style="color:%2$s;">%3$s</th>', esc_attr( $k ), esc_attr( $theme_color_text ), $d );
					}

					continue;
				}

				printf( '<th class="%1$s" style="color:%2$s;">%3$s</th>', esc_attr( $key ), esc_attr( $theme_color_text ), $data );
			}
			?>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $invoice->get_columns_data() as $index => $row ) {
			echo '<tr class="item">';
			foreach ( $row as $key => $data ) {
				if ( is_array( $data ) ) {
					foreach ( $data as $k => $d ) {
						printf( '<td class="%1$s">%2$s</td>', esc_attr( $key ), $d );
					}

					continue;
				}

				printf( '<td class="%1$s">%2$s</td>', esc_attr( $key ), $data );
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
			<td class="border">
				<?php
				// Customer notes.
				if ( WPI()->get_option( 'template', 'show_customer_notes' ) ) {
					// Note added by customer.
					$customer_note = esc_html( BEWPI_WC_Order_Compatibility::get_customer_note( $invoice->order ) );
					if ( $customer_note ) {
						echo '<p><strong>' . __( 'Note from customer: ', 'woocommerce-pdf-invoices' ) . '</strong>' . nl2br( $customer_note ) . '</p>';
					}

					// Notes added by administrator on 'Edit Order' page.
					foreach ( $invoice->order->get_customer_order_notes() as $custom_order_note ) {
						echo '<p><strong>' . __( 'Note to customer:', 'woocommerce-pdf-invoices' ) . '</strong> ' . nl2br( $custom_order_note->comment_content ) . '</p>';
					}
				}
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php
				// Zero Rated VAT message.
				if ( 'true' === WPI()->get_meta( $invoice->order, '_vat_number_is_valid' ) && count( $invoice->order->get_tax_totals() ) === 0 ) {
					echo '<p>' . esc_html__( 'Zero rated for VAT as customer has supplied EU VAT number', 'woocommerce-pdf-invoices' ) . '</p>';
				}
				?>
			</td>
		</tr>
	</table>
</div>