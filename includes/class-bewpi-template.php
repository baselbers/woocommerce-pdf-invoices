<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class BEWPI_Template.
 */
class BEWPI_Template {

	/** Main instance.
	 *
	 * @var BEWPI_Template The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Template directories.
	 *
	 * @var array.
	 */
	private $directories;

	/**
	 * Main BEWPI_Template Instance.
	 *
	 * Ensures only one instance of BEWPI_Template is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @return BEWPI_Template Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * BEWPI_Template constructor.
	 */
	private function __construct() {
		$this->directories = apply_filters( 'bewpi_template_directories', array(
			WPI_TEMPLATES_DIR, // uploads/woocommerce-pdf-invoices/templates.
			WPI_DIR . '/includes/templates',
		) );
	}

	/**
	 * Get path to templates.
	 *
	 * @return array
	 */
	public function get_templates() {
		$templates = array();

		// uploads/bewpi-templates/invoices.
		$templates = array_merge( $templates, glob( BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . '/simple/*', GLOB_ONLYDIR ) );

		foreach ( $this->directories as $directory ) {
			$templates = array_merge( $templates, glob( $directory . '/invoice/simple/*', GLOB_ONLYDIR ) );
		}

		return $templates;
	}

	/**
	 * Get template options by key.
	 *
	 * @param string $name the option key.
	 * @param int    $order_id the WooCommerce Order ID is needed to replace template placeholders.
	 *
	 * @return string
	 */
	public static function get_option( $name, $order_id = null ) {
		$template_options = get_option( 'bewpi_template_settings' );

		$value = apply_filters( $name, $template_options[ $name ], $name, $order_id );

		if ( ! is_null( $order_id ) ) {
			$value = self::replace_placeholders( $value, $order_id );
		}

		return $value;
	}

	/**
	 * Replace template placeholder within string.
	 *
	 * @param string $value string to format.
	 * @param int    $order_id WC_Order ID.
	 *
	 * @return string
	 */
	private static function replace_placeholders( $value, $order_id ) {
		$order = wc_get_order( $order_id );

		$value = str_replace(
			array( '[payment_method]', '[shipping_method]' ),
			array(
				apply_filters( 'bewpi_payment_method_title', $order->payment_method_title ),
				$order->get_shipping_method(),
			),
			$value
		);

		return $value;
	}

	/**
	 * Get the company logo URL.
	 *
	 * @return string The actual url from the Media Library.
	 */
	public static function get_logo_url() {
		return esc_url_raw( self::get_option( 'bewpi_company_logo' ) );
	}

	/**
	 * Get template directories.
	 *
	 * @return array
	 */
	public function get_directories() {
		return $this->directories;
	}
}
