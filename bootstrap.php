<?php
/**
 * Plugin Name:       WooCommerce PDF Invoices
 * Plugin URI:        https://wordpress.org/plugins/woocommerce-pdf-invoices
 * Description:       Automatically generate and attach customizable PDF Invoices to WooCommerce emails and connect with Dropbox, Google Drive, OneDrive or Egnyte.
 * Version:           2.6.0
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

define( 'BEWPI_VERSION', '2.6.0' );

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
 * On plugin update.
 *
 * @since 2.5.0
 */
function _bewpi_on_plugin_update() {
	if ( get_site_option( 'bewpi_version' ) !== BEWPI_VERSION ) {

		// update attach to email options.
		update_email_type_options();

		// create pdf path postmeta for all shop orders.
		create_pdf_path_postmeta();

		// format date postmeta to mysql date.
		update_date_format_postmeta();

		update_site_option( 'bewpi_version', BEWPI_VERSION );
	}
}

/**
 * "Attach to Email" and "Attach to new order email" options changed to multi-checkbox, so update settings accordingly.
 *
 * @since 2.5.0
 */
function update_email_type_options() {
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
}

/**
 * Create full path postmeta for all orders that have a pdf invoice generated.
 *
 * @since 2.6.0
 */
function create_pdf_path_postmeta() {
	$template_options = get_option( 'bewpi_template_settings' );
	$posts = get_posts( array(
		'numberposts' => -1,
		'post_type'   => 'shop_order',
		'post_status' => array_keys( wc_get_order_statuses() ),
	) );

	foreach ( $posts as $post ) {
		$pdf_path = get_post_meta( $post->ID, '_bewpi_invoice_pdf_path', true );
		if ( $pdf_path ) {
			continue;
		}

		$invoice_number = get_post_meta( $post->ID, '_bewpi_invoice_number', true );
		if ( ! $invoice_number ) {
			continue;
		}

		$date_format = $template_options['bewpi_date_format'];
		if ( empty( $date_format ) ) {
			$date_format = (string) get_option( 'date_format' );
		}

		$digitized_invoice_number = sprintf( '%0' . $template_options['bewpi_invoice_number_digits'] . 's', $invoice_number );
		$formatted_invoice_number = str_replace(
			array( '[prefix]', '[suffix]', '[number]', '[order-date]', '[order-number]', '[Y]', '[y]', '[m]' ),
			array(
				$template_options['bewpi_invoice_number_prefix'],
				$template_options['bewpi_invoice_number_suffix'],
				$digitized_invoice_number,
				date_i18n( $date_format, strtotime( $post->post_date ) ),
				$post->ID,
				date_i18n( 'Y', strtotime( $post->post_date ) ),
				date_i18n( 'y', strtotime( $post->post_date ) ),
				date_i18n( 'm', strtotime( $post->post_date ) ),
			),
			$template_options['bewpi_invoice_number_format']
		);

		// one folder for all invoices.
		$new_pdf_path = $formatted_invoice_number . '.pdf';
		if ( (bool) $template_options['bewpi_reset_counter_yearly'] ) {
			// yearly sub-folders.
			$invoice_year = get_post_meta( $post->ID, '_bewpi_invoice_year', true );
			if ( $invoice_year ) {
				$new_pdf_path = $invoice_year . '/' . $formatted_invoice_number . '.pdf';
			}
		}

		if ( file_exists( BEWPI_INVOICES_DIR . $new_pdf_path ) ) {
			update_post_meta( $post->ID, '_bewpi_invoice_pdf_path', $new_pdf_path );
		}
	}
}

/**
 * Format date postmeta to mysql date.
 *
 * @since 2.6.0
 */
function update_date_format_postmeta() {
	$template_options = get_option( 'bewpi_template_settings' );
	$posts = get_posts( array(
		'numberposts' => -1,
		'post_type'   => 'shop_order',
		'post_status' => array_keys( wc_get_order_statuses() ),
	) );

	foreach ( $posts as $post ) {
		$invoice_date = get_post_meta( $post->ID, '_bewpi_invoice_date', true );
		if ( ! $invoice_date ) {
			continue;
		}

		$date_format = $template_options['bewpi_date_format'];
		if ( empty( $date_format ) ) {
			$date_format = (string) get_option( 'date_format' );
		}

		$date = DateTime::createFromFormat( $date_format, $invoice_date );
		if ( ! $date ) {
			continue;
		}

		update_post_meta( $post->ID, '_bewpi_invoice_date', $date->format( 'Y-m-d H:i:s' ) );
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
