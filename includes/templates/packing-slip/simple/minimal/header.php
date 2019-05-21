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
?>

<table>
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
			<?php echo WPI()->get_formatted_company_address(); ?>
		</td>
	</tr>
</table>
