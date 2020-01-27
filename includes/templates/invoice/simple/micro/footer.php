<?php
/**
 * PDF invoice body template that will be visible on every page.
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

$theme_color_background = WPI()->get_option( 'template', 'color_theme_background' );
?>
<table class="foot" style="border-top: 6px solid <?php echo esc_attr( $theme_color_background ); ?>;">
	<tr>
		<td class="left">
			<p><?php echo nl2br( WPI()->get_option( 'template', 'left_footer_column' ) ); ?></p>
		</td>

		<td class="mid">
			<p><?php printf( __( '%1$s of %2$s', 'woocommerce-pdf-invoices' ), '{PAGENO}', '{nbpg}' ); ?></p>
		</td>

		<td class="right">
			<p><?php echo nl2br( WPI()->get_option( 'template', 'right_footer_column' ) ); ?></p>
		</td>
	</tr>
</table>
