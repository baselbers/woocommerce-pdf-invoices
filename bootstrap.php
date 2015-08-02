<?php

/**
 * @version           2.2.9
 * @package           WooCommerce PDF Invoices PRO
 * @author            baaaaas
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce PDF Invoices PRO
 * Plugin URI:
 * Description:       Automatically or manually create and send PDF Invoices for WooCommerce orders and connect with Dropbox, Google Drive, OneDrive or Egnyte.
 * Version:           2.2.9
 * Author:            baaaaas
 * Author URI:
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       be-woocommerce-pdf-invoices
 * Domain Path:       /lang
 */

if ( ! defined( 'ABSPATH' ) )
    die( 'Access denied.' );

if ( ! defined( 'BEWPI_VERSION' ) )
	define( 'BEWPI_VERSION', '2.2.9' );

if ( ! defined( 'BEWPI_URL' ) )
    define( 'BEWPI_URL', plugins_url( '', __FILE__ ) . '/' );

if ( ! defined( 'BEWPI_DIR' ) )
	define( 'BEWPI_DIR', plugin_dir_path( __FILE__ ) . '/' );

if ( ! defined( 'BEWPI_LANG_DIR' ) )
    define( 'BEWPI_LANG_DIR', basename( dirname( __FILE__ ) ) . '/lang' );

if ( ! defined( 'BEWPI_TEMPLATES_DIR' ) )
    define( 'BEWPI_TEMPLATES_DIR', plugin_dir_path( __FILE__ ) . 'includes/templates/' );

if ( ! defined( 'BEWPI_TEMPLATES_INVOICES_DIR' ) )
	define( 'BEWPI_TEMPLATES_INVOICES_DIR', plugin_dir_path( __FILE__ ) . 'includes/templates/invoices/' );

if ( ! defined( 'BEWPI_INVOICES_DIR' ) ) {
	$wp_upload_dir = wp_upload_dir();
	define( 'BEWPI_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-invoices/' );
}

if ( ! defined( 'BEWPI_LIB_DIR' ) )
	define( 'BEWPI_LIB_DIR', plugin_dir_path( __FILE__ ) . '/lib/' );

require_once( BEWPI_DIR . 'functions.php' );
// require abstract classes
require_once( BEWPI_DIR . 'includes/abstracts/abstract-bewpi-document.php' );
require_once( BEWPI_DIR . 'includes/abstracts/abstract-bewpi-invoice.php' );
require_once( BEWPI_DIR . 'includes/abstracts/abstract-bewpi-setting.php' );
// require settings classes
require_once( BEWPI_DIR . 'includes/admin/settings/class-bewpi-admin-settings-general.php' );
require_once( BEWPI_DIR . 'includes/admin/settings/class-bewpi-admin-settings-template.php' );
// require invoice classes
require_once( BEWPI_DIR . 'includes/class-bewpi-invoice.php' );
// woocommerce pdf invoices pro
if ( file_exists( BEWPI_DIR . 'includes/class-bewpipro-invoice-global.php' ) ) {
	require_once( BEWPI_DIR . 'includes/class-bewpipro-invoice-global.php' );
}

// require main class
require_once( BEWPI_DIR . 'includes/be-woocommerce-pdf-invoices.php' );

if ( class_exists( 'BE_WooCommerce_PDF_Invoices' ) )
    $GLOBALS['bewpi'] = new BE_WooCommerce_PDF_Invoices();
