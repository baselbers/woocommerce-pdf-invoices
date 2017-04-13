<?php
/**
 * PDF invoice header template that will be visible on every page.
 *
 * This template can be overridden by copying it to youruploadsfolder/woocommerce-pdf-invoices/templates/invoice/simple/yourtemplatename/header.php.
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

$templater  = BEWPI()->templater();
$order      = $templater->order;
$invoice    = $templater->invoice;
?>

<table cellpadding="0" cellspacing="0">
	<tr class="top">
		<td>
			<?php
			if ( $templater->get_logo_url() ) {
				printf( '<img src="var:company_logo" style="max-height:100px;"/>' );
			} else {
				printf( '<h2>%s</h2>', esc_html( $templater->get_option( 'bewpi_company_name' ) ) );
			}
			?>
		</td>

		<td>
			<?php
			printf( __( 'Invoice #: %s', 'woocommerce-pdf-invoices' ), $invoice->get_formatted_number() );
			printf( '<br />' );
			printf( __( 'Invoice Date: %s', 'woocommerce-pdf-invoices' ), $invoice->get_formatted_invoice_date() );
			printf( '<br />' );
			printf( __( 'Order Date: %s', 'woocommerce-pdf-invoices' ), $invoice->get_formatted_order_date() );
			printf( '<br />' );
			printf( __( 'Order Number: %s', 'woocommerce-pdf-invoices' ), $order->get_order_number() );

			// WC backwards compatibility.
			$payment_method = method_exists( 'WC_Order', 'get_payment_method' ) ? $order->get_payment_method() : $order->payment_method;
			// Get PO Number from 'WooCommerce Purchase Order Gateway' plugin.
			if ( isset( $payment_method ) ) {

				printf( '<br />' );
				// WC backwards compatibility.
				$payment_method_title = method_exists( 'WC_Order', 'get_payment_method_title' ) ? $order->get_payment_method_title() : $order->payment_method_title;
				printf( __( 'Payment Method: %s', 'woocommerce-pdf-invoices' ), $payment_method_title );

				if ( 'woocommerce_gateway_purchase_order' === $payment_method ) {
					$po_number = $templater->get_meta( '_po_number' );
					if ( $po_number ) {
						printf( '<br />' );
						printf( __( 'Purchase Order Number: %s', 'woocommerce-pdf-invoices' ), $po_number );
					}
				}
			}

			// Get VAT Number from 'WooCommerce EU VAT Number' plugin.
			$vat_number = $templater->get_meta( '_vat_number' );
			if ( $vat_number ) {
				printf( '<br />' );
				printf( __( 'VAT Number: %s', 'woocommerce-pdf-invoices' ), $vat_number );
			}
			?>
		</td>
	</tr>
</table>
