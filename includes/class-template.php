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
	 * String placeholders.
	 *
	 * @var array.
	 */
	private static $placeholders = array( '[payment_method]', '[shipping_method]' );
	/**
	 * WooCommerce order.
	 *
	 * @var WC_Order.
	 */
	public $order;
	/**
	 * WooCommerce PDF Invoices invoice.
	 *
	 * @var BEWPI_Abstract_Invoice.
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
	 * BEWPI_Template constructor.
	 */
	private function __construct() {
		$upload_dir        = wp_upload_dir();
		$this->directories = apply_filters( 'bewpi_template_directories', array(
			$upload_dir['basedir'] . '/bewpi-templates', // Old custom templates directory.
			WPI_TEMPLATES_DIR, // uploads/woocommerce-pdf-invoices/templates.
			WPI_DIR . '/includes/templates',
		) );
	}

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
	 * Get template files.
	 *
	 * @param string $type Document type.
	 *
	 * @return array
	 */
	public function get_template( $type ) {
		$template = array();
		$order_id = BEWPI_WC_Order_Compatibility::get_id( WPI()->templater()->get_order() );

		// get template name from template options.
		$name = apply_filters( 'wpi_template_name', WPI()->get_option( 'template', 'template_name' ), $type, $order_id );

		// first check custom directory, second plugin directory.
		foreach ( $this->directories as $directory ) {
			$template_path = $directory . '/' . $type . '/' . $name;
			if ( ! file_exists( $template_path ) ) {
				continue;
			}

			$files = array_merge( glob( $template_path . '/*.php' ), glob( $template_path . '/*.css' ) );
			foreach ( $files as $full_path ) {
				$file                          = pathinfo( $full_path );
				$template[ $file['filename'] ] = $full_path;
			}

			break;
		}

		if ( count( $template ) === 0 ) {
			WPI()->logger()->error( sprintf( 'PDF generation aborted. Template not found in %1$s:%2$s', __FILE__, __LINE__ ) );
		}

		return $template;
	}

	/**
	 * Get order object.
	 *
	 * @return WC_Order $order order object.
	 */
	public function get_order() {
		return $this->order;
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
	 * Add dir.
	 *
	 * @param string $directory dir.
	 */
	public function add_directory( $directory ) {
		$this->directories[] = $directory;
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
	 * Check if order has only virtual products.
	 *
	 * @param array $items WooCommerce products.
	 *
	 * @return bool
	 * @since 2.5.3
	 * @deprecated Use 'WPI()->has_only_virtual_product( $order )' instead.
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
	 * Order item meta.
	 *
	 * @param WC_Order_Item $item Order item meta.
	 */
	public function display_item_meta( $item ) {
		echo '<ul class="wc-item-meta">';
		foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
			echo '<li><strong class="wc-item-meta-label">' . esc_html( wp_filter_nohtml_kses( $meta->display_key ) ) . ':</strong> ' . esc_html( wp_filter_nohtml_kses( $meta->display_value ) ) . '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Order item meta port.
	 *
	 * @param WC_Order_Item $item Order item meta.
	 * @param bool          $inline Strip <b> and <p> tags to avoid breaks.
	 *
	 * @deprecated Make sure to remove it from your custom template since this will be removed in future releases.
	 */
	public function wc_display_item_meta( $item, $inline = false ) {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices 2.9.13', 'WPI()->templater()->display_item_meta( $item )' );
		if ( function_exists( 'wc_display_item_meta' ) ) {

			if ( $inline ) {
				echo $this->strip_non_inline_tags( wc_display_item_meta( $item, array( 'echo' => false ) ) );

				return;
			}

			wc_display_item_meta( $item );

		} else {
			$this->order->display_item_meta( (array) $item );
		}
	}

	/**
	 * Strip some non-inline elements.
	 *
	 * @param string $string Value.
	 *
	 * @deprecated Make sure to remove it from your custom template since this will be removed in future releases.
	 *
	 * @return mixed
	 */
	public function strip_non_inline_tags( $string ) {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices 2.9.13', 'wp_filter_nohtml_kses()' );

		return str_replace( array( '<p>', '</p>', '<br>', '</br>' ), '', $string );
	}

	/**
	 * Order item downloads meta.
	 *
	 * @param object $item Order item.
	 * @param bool   $inline Strip <b> and <p> tags to avoid breaks.
	 *
	 * @deprecated No longer used within the template files since it is triggering a Fatal Error and does not get used anyway. Make sure to remove it from your custom template since this will be removed in future releases.
	 */
	public function wc_display_item_downloads( $item, $inline = false ) {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices 2.9.13', 'wc_display_item_downloads( $item )' );
		if ( function_exists( 'wc_display_item_downloads' ) ) {

			if ( $inline ) {
				echo $this->strip_non_inline_tags( wc_display_item_downloads( $item, array( 'echo' => false ) ) );

				return;
			}

			wc_display_item_meta( $item );

		} else {
			$this->order->display_item_downloads( $item );
		}
	}

	/**
	 * Display column recursively.
	 *
	 * @param string $key Column ID.
	 * @param array  $data Column or column label.
	 */
	public function display_header_recursive( $key, $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $k => $d ) {
				$this->display_header_recursive( $k, $d );
			}

			return;
		}

		printf( '<th class="%1$s">%2$s</th>', esc_attr( $key ), $data );
	}

	/**
	 * Display row recursively.
	 *
	 * @param string $key Column ID.
	 * @param array  $data Column or column label.
	 */
	public function display_data_recursive( $key, $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $k => $d ) {
				$this->display_data_recursive( $k, $d );
			}

			return;
		}

		printf( '<td class="%1$s">%2$s</td>', esc_attr( $key ), $data );
	}

	/**
	 * Template needs to show sku as meta data.
	 *
	 * @return bool.
	 */
	public function has_sku_as_meta_data() {
		// Only available for minimal template.
		if ( false === strpos( $this->get_active_template(), 'minimal' ) ) {
			return false;
		}

		return $this->has_advanced_table_content() && (bool) WPI()->get_option( 'template', 'show_sku_meta' );
	}

	/**
	 * Get current active template name.
	 *
	 * @return string.
	 */
	public function get_active_template() {
		return (string) WPI()->get_option( 'template', 'template_name' );
	}

	/**
	 * Template uses advanced table content?
	 *
	 * @return bool.
	 */
	public function has_advanced_table_content() {
		return (bool) WPI()->get_option( 'template', 'enable_advanced_table_content' );
	}

	/**
	 * Get the company logo URL.
	 *
	 * @deprecated use 'esc_url_raw( WPI()->get_option( 'template', 'company_logo' )' instead.
	 *
	 * @return string The actual url from the Media Library.
	 */
	public function get_logo_url() {
		return esc_url_raw( $this->get_option( 'bewpi_company_logo' ) );
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
		$value    = apply_filters( $name, $template_options[ $name ], $name, $order_id );

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
	 * Get custom post meta data.
	 *
	 * @param string $meta_key The post meta key.
	 *
	 * @return string
	 */
	public function get_meta( $meta_key ) {
		$order_id = BEWPI_WC_Order_Compatibility::get_id( $this->order );

		return get_post_meta( $order_id, $meta_key, true );
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
	 * Get two letter ISO language code.
	 */
	public function get_two_letter_iso_code() {
		return substr( get_locale(), 0, 2 );
	}

	/**
	 * Set invoice.
	 *
	 * @param BEWPI_Abstract_Invoice $invoice invoice object.
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
