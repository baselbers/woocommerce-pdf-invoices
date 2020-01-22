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

$invoice                = WPI()->templater()->invoice;
$theme_color_text       = WPI()->get_option( 'template', 'color_theme_text' );
$theme_color_background = WPI()->get_option( 'template', 'color_theme_background' );

// Header and footer margin and padding.
$this->mpdf->setAutoTopMargin    = 'stretch';
$this->mpdf->setAutoBottomMargin = 'stretch';
$this->mpdf->autoMarginPadding   = 25; // mm.
?>

<div class="title">
	<div>
		<h2><?php echo esc_html( WPI()->get_option( 'template', 'title' ) ); ?></h2>
	</div>
	<div class="watermark">
		<?php
		if ( WPI()->get_option( 'template', 'show_payment_status' ) && $invoice->order->is_paid() ) {
			printf( '<h2 class="green">%s</h2>', esc_html__( 'Paid', 'woocommerce-pdf-invoices' ) );
		}

		do_action( 'wpi_watermark_end', $invoice->order, $invoice );
		?>
	</div>
</div>
<table>
	<tr class="information">
		<td class="primary">
			<?php
			foreach ( $invoice->get_invoice_info() as $info_id => $info ) {
				if ( empty( $info['value'] ) ) {
					continue;
				}

				printf( '<span class="%1$s">%2$s %3$s</span>', esc_attr( $info_id ), esc_html( $info['title'] ), esc_html( $info['value'] ) );
				echo '<br>';
			}
			?>
		</td>

		<td class="bill-to">
			<?php
			printf( '<strong>%s</strong><br />', esc_html__( 'Bill to:', 'woocommerce-pdf-invoices' ) );
			echo $invoice->order->get_formatted_billing_address();

			do_action( 'wpi_after_formatted_billing_address', $invoice );
			?>
		</td>

		<td class="ship-to">
			<?php
			$formatted_shipping_address = $invoice->order->get_formatted_shipping_address();
			if ( WPI()->get_option( 'template', 'show_ship_to' ) && ! WPI()->has_only_virtual_products( $invoice->order ) && ! empty( $formatted_shipping_address ) ) {
				printf( '<strong>%s</strong><br />', esc_html__( 'Ship to:', 'woocommerce-pdf-invoices' ) );
				echo $formatted_shipping_address;

				do_action( 'wpi_after_formatted_shipping_address', $invoice );
			}
			?>
		</td>
	</tr>
</table>

<table>
	<thead>
	<tr class="heading" style="background-color:<?php echo esc_attr( $theme_color_background ); ?>;">
		<?php
		foreach ( $invoice->get_columns() as $key => $data ) {
			$class = str_replace( '_', '-', $key );

			if ( is_array( $data ) ) {
				foreach ( $data as $k => $d ) {
					printf( '<th class="%1$s" style="color:%2$s;">%3$s</th>', esc_attr( $class ), esc_attr( $theme_color_text ), $d );
				}

				continue;
			}

			printf( '<th class="%1$s" style="color:%2$s;">%3$s</th>', esc_attr( $class ), esc_attr( $theme_color_text ), $data );
		}
		?>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $invoice->get_columns_data() as $index => $row ) {
		echo '<tr class="item">';
		foreach ( $row as $key => $data ) {
			$class = str_replace( '_', '-', $key );

			if ( is_array( $data ) ) {
				foreach ( $data as $k => $d ) {
					printf( '<td class="%1$s">%2$s</td>', esc_attr( $class ), $d );
				}

				continue;
			}

			printf( '<td class="%1$s">%2$s</td>', esc_attr( $class ), $data );
		}

		echo '</tr>';
	}
	?>

	<tr class="spacer">
		<td></td>
	</tr>

	</tbody>
</table>

<table>
	<tbody>

	<?php
	$order_item_totals = $invoice->get_order_item_totals();
	$rowspan           = count( $order_item_totals );
	foreach ( $order_item_totals as $key => $total ) {
		$class = str_replace( '_', '-', $key );
		?>

		<tr class="total">
			<?php
			// Only display row for first element and use rowspan.
			if ( $rowspan > 0 ) {
				?>
				<td class="custom-text" rowspan="<?php echo esc_attr( $rowspan ); ?>">
					<?php do_action( 'wpi_order_item_totals_left', $key, $invoice ); ?>
				</td>
				<?php
				$rowspan = 0;
			}
			?>

			<td class="<?php echo esc_attr( $class ); ?>">
				<?php echo $total['label']; ?>
			</td>

			<td class="<?php echo esc_attr( $class ); ?>">
				<?php echo str_replace( '&nbsp;', '', $total['value'] ); ?>
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
				$customer_note = BEWPI_WC_Order_Compatibility::get_customer_note( $invoice->order );
				if ( $customer_note ) {
					printf( '<strong>' . __( 'Note from customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br>', nl2br( $customer_note ) );
				}

				// Notes added by administrator on 'Edit Order' page.
				foreach ( $invoice->order->get_customer_order_notes() as $custom_order_note ) {
					printf( '<strong>' . __( 'Note to customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br>', nl2br( $custom_order_note->comment_content ) );
				}
			}
			?>
		</td>
	</tr>

	<tr>
		<td>
			<?php
			// Zero Rated VAT message.
			if ( $invoice->is_vat_exempt() ) {
				_e( 'Zero rated for VAT as customer has supplied EU VAT number', 'woocommerce-pdf-invoices' );
				echo '<br>';
			}
			?>
		</td>
	</tr>
</table>

<?php
$terms = WPI()->get_option( 'template', 'terms' );
if ( $terms ) {
	?>
	<div class="terms">
		<table>
			<tr>
				<td>
					<?php echo nl2br( $terms ); ?>
				</td>
			</tr>
		</table>
	</div>
<?php } ?>
