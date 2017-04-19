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
	 * WooCommerce order.
	 *
	 * @var WC_Order.
	 */
	public $order;

	/**
	 * WooCommerce PDF Invoices invoice.
	 *
	 * @var BEWPI_Invoice.
	 */
	public $invoice;

	/**
	 * WooCommerce PDF Invoices packing slip.
	 *
	 * @var BEWPI_Packing_Slip.
	 */
	public $packing_slip;

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
	 * @since 2.7.0
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
		$upload_dir = wp_upload_dir();
		$this->directories = apply_filters( 'bewpi_template_directories', array(
			$upload_dir['basedir'] . '/bewpi-templates', // Old custom templates directory.
			WPI_TEMPLATES_DIR, // uploads/woocommerce-pdf-invoices/templates.
			WPI_DIR . '/includes/templates',
		) );
	}

	/**
	 * Get template files.
	 *
	 * @param string $type Document type.
	 *
	 * @return array
	 */
	public function get_template( $type ) {
		$template = array();

		// get template name from template options.
		$name = $this->get_option( 'bewpi_template_name' );

		// first check custom directory, second plugin directory.
		foreach ( $this->directories as $directory ) {
			$template_path = $directory . '/' . $type . '/' . $name;
			if ( ! file_exists( $template_path ) ) {
				continue;
			}

			$files = array_merge( glob( $template_path . '/*.php' ), glob( $template_path . '/*.css' ) );
			foreach ( $files as $full_path ) {
				$file = pathinfo( $full_path );
				$template[ $file['filename'] ] = $full_path;
			}

			break;
		}

		if ( count( $template ) === 0 ) {
			BEWPI()->logger()->error( sprintf( 'PDF generation aborted. Template not found in %1$s:%2$s', __FILE__,  __LINE__ ) );
		}

		return $template;
	}

	/**
	 * Get absolute paths of all invoice/simple templates.
	 *
	 * @return array
	 */
	public function get_templates() {
		$templates = array();

		foreach ( $this->directories as $directory ) {
			$templates = array_merge( $templates, glob( $directory . '/invoice/simple/*', GLOB_ONLYDIR ) );
		}

		return $templates;
	}

	/**
	 * Get template options by key.
	 *
	 * @param string $name the option key.
	 *
	 * @return string
	 */
	public function get_option( $name ) {
		$template_options = get_option( 'bewpi_template_settings' );

		$order_id = bewpi_get_id( $this->order );
		$value = apply_filters( $name, $template_options[ $name ], $name, $order_id );
		$value = $this->replace_placeholders( $value );

		return $value;
	}

	/**
	 * Replace template placeholder within string.
	 *
	 * @param string $value string to format.
	 *
	 * @return string
	 */
	private function replace_placeholders( $value ) {
		$value = str_replace(
			array( '[payment_method]', '[shipping_method]' ),
			array(
				apply_filters( 'bewpi_payment_method_title', method_exists( 'WC_Order', 'get_payment_method_title' ) ? $this->order->get_payment_method_title() : $this->order->payment_method_title ),
				$this->order->get_shipping_method(),
			),
			$value
		);

		return $value;
	}

	/**
	 * Check if order has only virtual products.
	 *
	 * @param array $items WooCommerce products.
	 *
	 * @return bool
	 * @since 2.5.3
	 */
	public function has_only_virtual_products( $items ) {
		foreach ( $items as $item ) {
			$product = $this->order->get_product_from_item( $item );
			if ( ! $product || ! $product->is_virtual() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the company logo URL.
	 *
	 * @return string The actual url from the Media Library.
	 */
	public function get_logo_url() {
		return esc_url_raw( $this->get_option( 'bewpi_company_logo' ) );
	}

	/**
	 * Get custom post meta data.
	 *
	 * @param string $meta_key The post meta key.
	 *
	 * @return string
	 */
	public function get_meta( $meta_key ) {
		$order_id = bewpi_get_id( $this->order );

		return (string) get_post_meta( $order_id, $meta_key, true );
	}

	/**
	 * Get template directories.
	 *
	 * @return array
	 */
	public function get_directories() {
		return $this->directories;
	}

	/**
	 * Set order.
	 *
	 * @param WC_Order $order WooCommerce Order object.
	 */
	public function set_order( $order ) {
		$this->order = $order;
	}

	/**
	 * Set invoice.
	 *
	 * @param BEWPI_Invoice $invoice WooCommerce PDF Invoices invoice object.
	 */
	public function set_invoice( $invoice ) {
		$this->invoice = $invoice;
	}

	/**
	 * Set invoice.
	 *
	 * @param BEWPI_Packing_Slip $packing_slip WooCommerce PDF Invoices invoice object.
	 */
	public function set_packing_slip( $packing_slip ) {
		$this->packing_slip = $packing_slip;
	}
}
