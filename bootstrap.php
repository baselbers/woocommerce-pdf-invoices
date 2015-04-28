<?php

/**
 * @version           2.2.2
 * @package           WooCommerce PDF Invoices
 * @author            baaaaas
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce PDF Invoices
 * Plugin URI:
 * Description:       Automatically or manually create and send PDF Invoices for WooCommerce orders and connect with Dropbox, Google Drive, OneDrive or Egnyte.
 * Version:           2.2.2
 * Author:            baaaaas
 * Author URI:
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       be-woocommerce-pdf-invoices
 * Domain Path:       /lang
 */

if ( ! defined( 'ABSPATH' ) )
    die( 'Access denied.' );

if( !defined( 'BEWPI_VERSION' ) )
	define( 'BEWPI_VERSION', '2.2.2' );

if( !defined( 'BEWPI_URL' ) )
    define( 'BEWPI_URL', plugins_url( '', __FILE__ ) . '/' );

if( !defined( 'BEWPI_DIR' ) )
	define( 'BEWPI_DIR', plugin_dir_path( __FILE__ ) . '/' );

if( !defined( 'BEWPI_TEMPLATES_DIR' ) )
    define( 'BEWPI_TEMPLATES_DIR', plugin_dir_path( __FILE__ ) . '/includes/views/templates/' );

if( !defined( 'BEWPI_LANG_DIR' ) )
    define( 'BEWPI_LANG_DIR', basename( dirname( __FILE__ ) ) . '/lang' );

$wp_upload_dir = wp_upload_dir();

if ( !defined( 'BEWPI_INVOICES_DIR' ) )
    define( 'BEWPI_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-invoices/' );

if ( !defined( 'BEWPI_LIB_DIR' ) )
	define( 'BEWPI_LIB_DIR', plugin_dir_path( __FILE__ ) . '/lib/' );

require_once( BEWPI_DIR . 'functions.php' );
require_once( BEWPI_DIR . 'admin/classes/bewpi-settings.php' );
require_once( BEWPI_DIR . 'admin/classes/bewpi-general-settings.php' );
require_once( BEWPI_DIR . 'admin/classes/bewpi-template-settings.php' );
require_once( BEWPI_DIR . 'includes/classes/bewpi-document.php' );
require_once( BEWPI_DIR . 'includes/classes/bewpi-invoice.php' );
require_once( BEWPI_DIR . 'admin/classes/be-woocommerce-pdf-invoices.php' );

if ( class_exists( 'BE_WooCommerce_PDF_Invoices' ) ) {
    new BE_WooCommerce_PDF_Invoices();
    //add_action( 'plugins_loaded', create_function( '', 'new BE_WooCommerce_PDF_Invoices();' ) );
    //register_activation_hook( __FILE__, array( 'BE_WooCommerce_PDF_Invoices', 'plugin_activation' ) );
}