<?php
/**
 * Activation admin notice
 *
 * Link to settings page.
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

$settings_url = add_query_arg( array( 'page' => WPI()->get_plugin_slug() ), admin_url( 'admin.php' ) );
?>
<div class="updated notice notice-success is-dismissible" data-dismissible="activation-forever">
	<p>
		<?php printf( __( 'The settings of WooCommerce PDF Invoices are available <a href="%1$s">on this page</a>.', 'woocommerce-pdf-invoices' ), esc_url( $settings_url ) ); ?>
	</p>
</div>
