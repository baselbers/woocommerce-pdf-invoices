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

$templater             = WPI()->templater();
$invoice               = $templater->invoice;
$company_phone         = WPI()->get_option( 'template', 'company_phone' );
$company_email_address = WPI()->get_option( 'template', 'company_email_address' );
$company_vat_id        = WPI()->get_option( 'template', 'company_vat_id' );
?>

<table cellpadding="0" cellspacing="0">
	<tr class="top">
		<td>
			<?php
			if ( WPI()->get_option( 'template', 'company_logo' ) ) {
				printf( '<img src="var:company_logo" style="max-height:100px;"/>' );
			} else {
				printf( '<h2>%s</h2>', esc_html( WPI()->get_option( 'template', 'company_name' ) ) );
			}
			?>
		</td>

		<td>
			<?php
			if ( BEWPI_WC_Core_Compatibility::is_wc_version_gte_3_0() ) {
				echo $invoice->get_formatted_base_address();
			} else {
				echo nl2br( WPI()->get_option( 'template', 'company_address' ) ) . '<br>';
			}

			if ( ! empty( $company_phone ) ) {
				echo sprintf( __( 'Phone: %s', 'woocommerce-pdf-invoices' ), $company_phone ) . '<br>';
			}

			if ( ! empty( $company_email_address ) ) {
				echo sprintf( __( 'Email: %s', 'woocommerce-pdf-invoices' ), $company_email_address ) . '<br>';
			}

			if ( ! empty( $company_vat_id ) ) {
				printf( __( 'VAT ID: %s', 'woocommerce-pdf-invoices' ), $company_vat_id );
			}
			?>
		</td>
	</tr>
</table>
