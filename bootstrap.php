<?php
/**
 * Plugin Name:             WooCommerce PDF Invoices
 * Plugin URI:              https://wordpress.org/plugins/woocommerce-pdf-invoices
 * Description:             Automatically generate and attach customizable PDF Invoices to WooCommerce emails and connect with Dropbox, Google Drive, OneDrive or Egnyte.
 * Version:                 3.0.5
 * Author:                  Bas Elbers
 * Author URI:              http://wcpdfinvoices.com
 * License:                 GPL-2.0+
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:             woocommerce-pdf-invoices
 * Domain Path:             /lang
 * WC requires at least:    3.0.0
 * WC tested up to:         3.6.4
 */

defined( 'ABSPATH' ) || exit;

define( 'WPI_VERSION', '3.0.5' );

/**
 * Load WooCommerce PDF Invoices plugin.
 */
function _bewpi_load_plugin() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	if ( ! defined( 'WPI_FILE' ) ) {
		define( 'WPI_FILE', __FILE__ );
	}

	if ( ! defined( 'WPI_DIR' ) ) {
		define( 'WPI_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	}

	if ( file_exists( WPI_DIR . '/vendor/autoload.php' ) ) {
		require_once WPI_DIR . '/vendor/autoload.php';
	}

	/**
	 * Main instance of BE_WooCommerce_PDF_Invoices.
	 *
	 * @since  2.9.1
	 * @return BE_WooCommerce_PDF_Invoices
	 */
	function WPI() {
		return BE_WooCommerce_PDF_Invoices::instance();
	}

	WPI();

	if ( is_admin() ) {
		add_action( 'admin_init', '_bewpi_on_plugin_update' );
	}
}

add_action( 'plugins_loaded', '_bewpi_load_plugin', 10 );

/**
 * On plugin update.
 *
 * @since 2.5.0
 */
function _bewpi_on_plugin_update() {
	if ( WPI_VERSION !== get_site_option( 'bewpi_version' ) ) {
		WPI()->setup_directories();
		WPI()->setup_options();

		update_site_option( 'bewpi_version', WPI_VERSION );
	}
}

/**
 * Save install date, plugin version to db and set transient to show activation notice.
 *
 * @since 2.5.0
 */
function _bewpi_on_plugin_activation() {
	add_site_option( 'bewpi_install_date', current_time( 'mysql' ) );
	set_transient( 'bewpi-admin-notice-activation', true, 30 );
}

register_activation_hook( __FILE__, '_bewpi_on_plugin_activation' );
