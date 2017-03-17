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
		$this->directories = array(
			WPI_TEMPLATES_DIR, // uploads/woocommerce-pdf-invoices/templates.
			WPI_DIR . '/includes/templates',
		);
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

		$value = apply_filters( 'bewpi_template_option-' . $name, $template_options[ $name ], $name, $order_id );

		if ( ! is_null( $order_id ) ) {
			$value = self::replace_placeholders( $value, $order_id );
		}

		return $value;
	}

	/**
	 * Display company logo or name
	 */
	public static function print_logo() {
		$logo_url = self::get_option( 'bewpi_company_logo' );
		if ( ! empty( $logo_url ) ) {
			// mPDF' stablest method to display an image is to use their 'Image data as a Variable' (https://mpdf.github.io/what-else-can-i-do/images.html) option.
			$src = apply_filters( 'bewpi_company_logo_url', 'var:company_logo' );
			printf( '<img class="company-logo" src="%s"/>', esc_attr( $src ) );
		} else {
			// show company name if company logo does not exist.
			$company_name = self::get_option( 'bewpi_company_name' );
			printf( '<h1 class="company-logo">%s</h1>', esc_html( $company_name ) );
		}
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
			array( $order->payment_method_title, $order->get_shipping_method() ),
			$value
		);

		return $value;
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
