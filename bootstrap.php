<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce PDF Invoices
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress dashboard.
 * Version:           1.0.0
 * Author:            Bas Elbers
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Access denied.' );
}

define( 'WPI_NAME', 'WooCommerce PDF Invoices' );
define( 'WPI_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPI_URL', plugins_url( '', __FILE__ ) );
define( 'WPI_TEMPLATES_DIR', plugin_dir_path( __FILE__ ) . 'includes/views/templates/' );

require_once( WPI_DIR . 'admin/classes/woocommerce-pdf-invoices.php' );
require_once( WPI_DIR . 'admin/classes/wpi-general-settings.php' );
require_once( WPI_DIR . 'admin/classes/wpi-template-settings.php' );
require_once( WPI_DIR . 'includes/classes/wpi-invoice.php' );

if ( class_exists( 'BE_WooCommerce_PDF_Invoices' ) ) {
    new BE_WooCommerce_PDF_Invoices(new WPI_General_Settings(), new WPI_Template_Settings());
}