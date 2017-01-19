<?php
/**
 * Plugin Name:       WooCommerce PDF Invoices
 * Plugin URI:        https://wordpress.org/plugins/woocommerce-pdf-invoices
 * Description:       Automatically generate and attach customizable PDF Invoices to WooCommerce emails and connect with Dropbox, Google Drive, OneDrive or Egnyte.
 * Version:           2.5.6
 * Author:            Bas Elbers
 * Author URI:        http://wcpdfinvoices.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-pdf-invoices
 * Domain Path:       /lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BEWPI_VERSION', '2.5.6' );

/**
 * Load WooCommerce PDF Invoices plugin.
 */
function _bewpi_load_plugin() {

	define( 'BEWPI_FILE', __FILE__ );
	define( 'BEWPI_DIR', plugin_dir_path( __FILE__ ) );
	define( 'BEWPI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

	require_once BEWPI_DIR . 'includes/be-woocommerce-pdf-invoices.php';

	_bewpi_on_plugin_update();
}
add_action( 'plugins_loaded', '_bewpi_load_plugin', 10 );

/**
 * "Attach to Email" and "Attach to new order email" options changed to multi-checkbox, so update settings accordingly.
 *
 * @since 2.5.0
 */
function _bewpi_on_plugin_update() {
	if ( get_site_option( 'bewpi_version' ) !== BEWPI_VERSION ) {
		// plugin is updated.
		$general_options = get_option( 'bewpi_general_settings' );
		// check if we need to add and/or remove options.
		if ( isset( $general_options['bewpi_email_type'] ) ) {
			$email_type = $general_options['bewpi_email_type'];
			if ( ! empty( $email_type ) ) {
				// set new email type option.
				$general_options[ $email_type ] = 1;
			}
			// delete old option.
			unset( $general_options['bewpi_email_type'] );
		}

		if ( isset( $general_options['bewpi_new_order'] ) ) {
			$email_type = $general_options['bewpi_new_order'];
			if ( $email_type ) {
				// set invoice attach to new order email option.
				$general_options['new_order'] = 1;
			}
			// delete old option.
			unset( $general_options['bewpi_new_order'] );
		}

		update_option( 'bewpi_general_settings', $general_options );
		update_site_option( 'bewpi_version', BEWPI_VERSION );
	}
}

/**
 * Save install date, plugin version to db and set transient to show activation notice.
 *
 * @since 2.5.0
 */
function _bewpi_on_plugin_activation() {
	// save install date for plugin activation admin notice.
	$now = new DateTime();
	update_site_option( 'bewpi-install-date', $now->format( 'Y-m-d' ) );

	// use transient to display activation admin notice.
	set_transient( 'bewpi-admin-notice-activation', true, 30 );

	// save plugin version for update function.
	update_site_option( 'bewpi_version', BEWPI_VERSION );
}

register_activation_hook( __FILE__, '_bewpi_on_plugin_activation' );
