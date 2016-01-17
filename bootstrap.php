<?php
/**
 * Plugin Name:       WooCommerce PDF Invoices
 * Plugin URI:        https://wordpress.org/plugins/woocommerce-pdf-invoices
 * Description:       Automatically generate and attach customizable PDF Invoices to WooCommerce emails and connect with Dropbox, Google Drive, OneDrive or Egnyte.
 * Version:           2.4.11
 * Author:            Bas Elbers
 * Author URI:        http://wcpdfinvoices.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-pdf-invoices
 * Domain Path:       /lang
 */

function bewpi_plugins_loaded() {

	if ( ! defined( 'ABSPATH' ) )
		die( 'Access denied.' );

	$wp_upload_dir = wp_upload_dir();

	define( 'BEWPI_VERSION', '2.4.11' );
	define( 'BEWPI_URL', plugins_url( '', __FILE__ ) . '/' );
	define( 'BEWPI_DIR', plugin_dir_path( __FILE__ ) . '/' );
	define( 'BEWPI_TEMPLATES_DIR', plugin_dir_path( __FILE__ ) . 'includes/templates/' );
	define( 'BEWPI_TEMPLATES_INVOICES_DIR', plugin_dir_path( __FILE__ ) . 'includes/templates/invoices/' );
	define( 'BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-templates/invoices/' );
	define( 'BEWPI_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-invoices/' );
	define( 'BEWPI_LANG_DIR', basename( dirname( __FILE__ ) ) . '/lang' );
	define( 'BEWPI_LIB_DIR', plugin_dir_path( __FILE__ ) . '/lib/' );
	define( 'BEWPI_WP_REPO_URL', 'https://wordpress.org/plugins/woocommerce-pdf-invoices/' );
	define( 'BEWPI_WEBSITE_URL', 'http://wcpdfinvoices.com' );

	require_once( BEWPI_DIR . 'functions.php' );
	require_once( BEWPI_DIR . 'includes/abstracts/abstract-bewpi-document.php' );
	require_once( BEWPI_DIR . 'includes/abstracts/abstract-bewpi-invoice.php' );
	require_once( BEWPI_DIR . 'includes/abstracts/abstract-bewpi-setting.php' );
	require_once( BEWPI_DIR . 'includes/admin/settings/class-bewpi-admin-settings-general.php' );
	require_once( BEWPI_DIR . 'includes/admin/settings/class-bewpi-admin-settings-template.php' );
	require_once( BEWPI_DIR . 'includes/class-bewpi-invoice.php' );
	require_once( BEWPI_DIR . 'includes/be-woocommerce-pdf-invoices.php' );

	new BE_WooCommerce_PDF_Invoices();
}
add_action( 'plugins_loaded', 'bewpi_plugins_loaded', 11 );

if ( is_admin() ) {
	require_once( dirname( __FILE__ ) . '/includes/be-woocommerce-pdf-invoices.php' );
	register_activation_hook( __FILE__, array( 'BE_WooCommerce_PDF_Invoices', 'plugin_activation' ) );
}