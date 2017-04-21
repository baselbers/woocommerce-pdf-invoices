<?php
/**
 * Templater class to populate templates.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     0.0.1
 */

defined( 'ABSPATH' ) or exit;

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
	private $directories = array();

	/**
	 * String placeholders.
	 *
	 * @var array.
	 */
	private static $placeholders = array( '[payment_method]', '[shipping_method]' );

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

		$order_id = BEWPI_WC_Order_Compatibility::get_id( $this->order );
		$value = apply_filters( $name, $template_options[ $name ], $name, $order_id );

		if ( self::has_placeholder( $value ) ) {
			$value = $this->replace_placeholders( $value );
		}

		return $value;
	}

	/**
	 * Checks if string has placeholders.
	 *
	 * @param string $value Text value.
	 *
	 * @return bool
	 */
	private static function has_placeholder( $value ) {
		foreach ( self::$placeholders as $placeholder ) {
			if ( strpos( $value, $placeholder ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Replace template placeholder within string.
	 *
	 * @param string $value string to format.
	 *
	 * @return string
	 */
	private function replace_placeholders( $value ) {
		$payment_gateway = wc_get_payment_gateway_by_order( $this->order );

		$value = str_replace(
			self::$placeholders,
			array(
				( $payment_gateway ) ? $payment_gateway->get_title() : $value,
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
	 * Order item meta port.
	 *
	 * @param object $item Order item meta.
	 */
	public function wc_display_item_meta( $item ) {
		// WooCommerce v3.
		if ( function_exists( 'wc_display_item_meta' ) ) {
			wc_display_item_meta( $item );
		} else {
			$this->order->display_item_meta( $item );
		}
	}

	/**
	 * Order item downloads meta.
	 *
	 * @param object $item Order item.
	 */
	public function wc_display_item_downloads( $item ) {
		if ( function_exists( 'wc_display_item_downloads' ) ) {
			// WooCommerce v3.
			wc_display_item_downloads( $item );
		} else {
			$this->order->display_item_downloads( $item );
		}
	}

	/**
	 * Get tax total for item to display.
	 *
	 * @param object $item Order item.
	 *
	 * @return array Tax totals.
	 */
	public function get_tax_totals( $item ) {
		$line_tax_data         = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
		$tax_data              = maybe_unserialize( $line_tax_data );
		$tax_totals            = array();

		foreach ( $this->order->get_taxes() as $tax_item ) {
			$tax_item_id = $tax_item['rate_id'];
			$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';

			if ( ! empty( $tax_item_total ) ) {
				$order_currency = ( method_exists( 'WC_Order', 'get_currency' ) ) ? $this->order->get_currency() : $this->order->get_order_currency();
				$tax_totals[] = wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order_currency ) );
			} else {
				$tax_totals[] = '&ndash;';
			}
		}

		return $tax_totals;
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
		$order_id = BEWPI_WC_Order_Compatibility::get_id( $this->order );

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
