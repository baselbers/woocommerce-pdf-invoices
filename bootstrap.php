<?php

/**
 * @version           2.0.0
 * @package           WooCommerce PDF Invoices
 * @author            Bas Elbers
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce PDF Invoices
 * Plugin URI:
 * Description:       Generates customized PDF invoices and automatically attaches it to a WooCommerce email type of your choice. Now sending invoices to your Google Drive, Egnyte, Dropbox or OneDrive and it's all FREE!
 * Version:           2.0.0
 * Author:            Bas Elbers
 * Author URI:
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       be-woocommerce-pdf-invoices
 * Domain Path:       /lang
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Access denied.' );
}

define( 'WPI_NAME', 'WooCommerce PDF Invoices' );
define( 'WPI_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPI_URL', plugins_url( '', __FILE__ ) );
define( 'WPI_TEMPLATES_DIR', plugin_dir_path( __FILE__ ) . 'includes/views/templates/' );
define( 'WPI_TMP_DIR', plugin_dir_path( __FILE__ ) . 'tmp/' );
define( 'WPI_LANG_DIR', basename( dirname( __FILE__ ) ) . '/lang' );

require_once( WPI_DIR . 'admin/classes/woocommerce-pdf-invoices.php' );
require_once( WPI_DIR . 'admin/classes/wpi-settings.php' );
require_once( WPI_DIR . 'admin/classes/wpi-general-settings.php' );
require_once( WPI_DIR . 'admin/classes/wpi-template-settings.php' );
require_once( WPI_DIR . 'includes/classes/wpi-invoice.php' );

if ( class_exists( 'BE_WooCommerce_PDF_Invoices' ) ) {
    new BE_WooCommerce_PDF_Invoices(new WPI_General_Settings(), new WPI_Template_Settings());
}