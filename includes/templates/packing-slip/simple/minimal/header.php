<?php
/**
 * PDF packing slip header template that will be visible on every page.
 *
 * This template can be overridden by copying it to youruploadsfolder/woocommerce-pdf-invoices/templates/packing-slip/simple/yourtemplatename/header.php.
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
$document   = $templater->packing_slip;
$order      = $templater->order;
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
			printf( __( 'Order Date: %s', 'woocommerce-pdf-invoices' ), $document->get_formatted_order_date() );
			printf( '<br />' );
			printf( __( 'Order Number: %s', 'woocommerce-pdf-invoices' ), $order->get_order_number() );

			$shipping_method = $order->get_shipping_method();
			if ( $shipping_method ) {
				printf( '<br />' );
				printf( __( 'Shipping Method: %s', 'woocommerce-pdf-invoices' ), $shipping_method );
			}
			?>
		</td>
	</tr>
</table>
