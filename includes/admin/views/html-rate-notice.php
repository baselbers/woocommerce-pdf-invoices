<?php
/**
 * Rate notice
 *
 * Ask user to rate the plugin on wordpress.org
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

$current_user = wp_get_current_user();
$user_firstname = '';
if ( ! empty( $current_user->user_firstname ) ) {
	$user_firstname = ' ' . $current_user->user_firstname;
}
$rate_url = 'https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform';
?>
<div class="updated notice notice-success is-dismissible" data-dismissible="rate-forever">
	<p><?php printf( __( 'Hi%1$s! You\'re using <b>WooCommerce PDF Invoices</b> for some time now and we would appreciate your <a href="%2$s" target="_blank">★★★★★</a> rating. It will support future development big-time.', 'woocommerce-pdf-invoices' ), esc_html( $user_firstname ), $rate_url ); ?></p>
</div>
