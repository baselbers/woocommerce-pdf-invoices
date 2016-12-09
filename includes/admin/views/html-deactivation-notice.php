<?php
/**
 * Deactivation admin notice
 *
 * Link to settings page.
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

$reason_deactivation_url = 'http://wcpdfinvoices.com/what-made-you-deactivate';
global $status, $page, $s;
$deactive_url = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . BEWPI_PLUGIN_FILE . '&amp;plugin_status=' . $status . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . BEWPI_PLUGIN_FILE );
?>
<div id="bewpi-deactivation-notice" class="notice notice-warning">
	<p><?php printf( __( 'Before we deactivate WooCommerce PDF Invoices, would you care to <a href="%1$s" target="_blank">let us know why</a> so we can improve it for you? <a href="%2$s">No, deactivate now</a>.', 'woocommerce-pdf-invoices' ), esc_url( $reason_deactivation_url ), esc_url( $deactive_url ) ); // WPCS: XSS OK. ?></p>
</div>
