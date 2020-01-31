<?php
/**
 * General settings class.
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BEWPI_General_Settings.
 */
class BEWPI_General_Settings extends BEWPI_Abstract_Settings {

	/**
	 * BEWPI_General_Settings constructor.
	 */
	public function __construct() {
		$this->id           = 'general';
		$this->settings_key = 'bewpi_general_settings';
		$this->settings_tab = __( 'General', 'woocommerce-pdf-invoices' );
		$this->fields       = $this->get_fields();
		$this->sections     = $this->get_sections();
		$this->defaults     = $this->get_defaults();

		parent::__construct();
	}

	/**
	 * Get all sections.
	 *
	 * @return array.
	 */
	private function get_sections() {
		$sections = array(
			'email'         => array(
				'title'       => __( 'Email Options', 'woocommerce-pdf-invoices' ),
				'description' => sprintf( __( 'The PDF invoice will be generated when WooCommerce sends the corresponding email. The email should be <a href="%s">enabled</a> in order to automatically generate the PDF invoice.', 'woocommerce-pdf-invoices' ), 'admin.php?page=wc-settings&tab=email' ),
			),
			'download'      => array(
				'title' => __( 'Download Options', 'woocommerce-pdf-invoices' ),
			),
			'cloud_storage' => array(
				'title'       => __( 'Cloud Storage Options', 'woocommerce-pdf-invoices' ),
				'description' => sprintf( __( 'Sign-up at <a href="%1$s">Email It In</a> to send invoices to your Dropbox, OneDrive, Google Drive or Egnyte and enter your account below.', 'woocommerce-pdf-invoices' ), 'https://emailitin.com' ),
			),
			'interface'     => array(
				'title' => __( 'Interface Options', 'woocommerce-pdf-invoices' ),
			),
		);

		return $sections;
	}

	/**
	 * Settings fields.
	 *
	 * @return array
	 */
	private function get_fields() {
		$settings = array(
			array(
				'id'       => 'bewpi-email-types',
				'name'     => $this->prefix . 'email_types',
				'title'    => __( 'Attach to Emails', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'multi_select_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'email',
				'type'     => 'multiple_select',
				'desc'     => '',
				'options'  => apply_filters( 'wpi_email_types', array(
					'new_order'                 => array(
						'name'    => __( 'New order', 'woocommerce-pdf-invoices' ),
						'value'   => 'new_order',
						'default' => 1,
					),
					'customer_on_hold_order'    => array(
						'name'    => __( 'Order on-hold', 'woocommerce-pdf-invoices' ),
						'value'   => 'customer_on_hold_order',
						'default' => 0,
					),
					'customer_processing_order' => array(
						'name'    => __( 'Processing order', 'woocommerce-pdf-invoices' ),
						'value'   => 'customer_processing_order',
						'default' => 0,
					),
					'customer_completed_order'  => array(
						'name'    => __( 'Completed order', 'woocommerce-pdf-invoices' ),
						'value'   => 'customer_completed_order',
						'default' => 1,
					),
					'customer_invoice'          => array(
						'name'    => __( 'Customer invoice', 'woocommerce-pdf-invoices' ),
						'value'   => 'customer_invoice',
						'default' => 0,
					),
				) ),
				'priority' => 0,
			),
			array(
				'id'       => 'bewpi-disable-free-products',
				'name'     => $this->prefix . 'disable_free_products',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'email',
				'type'     => 'checkbox',
				'label'    => __( 'Disable for free products', 'woocommerce-pdf-invoices' ),
				'desc'     => __( 'Skip automatic PDF invoice generation for orders containing only free products.', 'woocommerce-pdf-invoices' ),
				'default'  => 0,
				'priority' => 1,
			),
			array(
				'id'       => 'bewpi-view-pdf',
				'name'     => $this->prefix . 'view_pdf',
				'title'    => __( 'View PDF', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'select_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'download',
				'type'     => 'text',
				'desc'     => '',
				'options'  => array(
					'download' => __( 'Download', 'woocommerce-pdf-invoices' ),
					'browser'  => __( 'Open in new browser tab/window', 'woocommerce-pdf-invoices' ),
				),
				'default'  => 'browser',
				'priority' => 0,
			),
			array(
				'id'       => 'bewpi-download-invoice-account',
				'name'     => $this->prefix . 'download_invoice_account',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'download',
				'type'     => 'checkbox',
				'label'    => __( 'Enable download from my account', 'woocommerce-pdf-invoices' ),
				'desc'     => __( 'By default PDF is only downloadable when order has been paid, so order status should be Processing or Completed.', 'woocommerce-pdf-invoices' ),
				'default'  => 1,
				'priority' => 1,
			),
			array(
				'id'       => 'bewpi-email-it-in',
				'name'     => $this->prefix . 'email_it_in',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'cloud_storage',
				'type'     => 'checkbox',
				'label'    => __( 'Enable Email It In', 'woocommerce-pdf-invoices' ),
				'desc'     => '',
				'default'  => 0,
				'priority' => 0,
			),
			array(
				'id'       => 'bewpi-email-it-in-account',
				'name'     => $this->prefix . 'email_it_in_account',
				'title'    => __( 'Email It In account', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'cloud_storage',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 1,
			),
			array(
				'id'       => 'bewpi-invoice-number-column',
				'name'     => $this->prefix . 'invoice_number_column',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'interface',
				'type'     => 'checkbox',
				'label'    => __( 'Enable Invoice Number column', 'woocommerce-pdf-invoices' ),
				'desc'     => __( 'Display invoice numbers on Shop Order page.', 'woocommerce-pdf-invoices' ),
				'default'  => 0,
				'priority' => 0,
			),
		);

		return apply_filters( 'wpi_general_settings', $settings, $this );
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input settings.
	 *
	 * @return mixed
	 */
	public function sanitize( $input ) {
		$output = get_option( $this->settings_key );

		foreach ( $output as $key => $value ) {
			if ( ! isset( $input[ $key ] ) ) {
				$output[ $key ] = is_array( $output[ $key ] ) ? array() : '';
				continue;
			}

			if ( is_array( $output[ $key ] ) ) {
				$output[ $key ] = $input[ $key ];
				continue;
			}

			// Strip all html and properly handle quoted strings.
			$output[ $key ] = stripslashes( $input[ $key ] );
		}

		return apply_filters( 'bewpi_sanitized_' . $this->settings_key, $output, $input );
	}
}
