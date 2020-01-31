<?php
/**
 * Template settings
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BEWPI_Template_Settings.
 */
class BEWPI_Template_Settings extends BEWPI_Abstract_Settings {

	/**
	 * BEWPI_Template_Settings constructor.
	 */
	public function __construct() {
		$this->id           = 'template';
		$this->settings_key = 'bewpi_template_settings';
		$this->settings_tab = __( 'Template', 'woocommerce-pdf-invoices' );
		$this->fields       = $this->get_fields();
		$this->sections     = $this->get_sections();
		$this->defaults     = $this->get_defaults();

		parent::__construct();

		$this->fix_deleted_custom_template();
	}

	/**
	 * Get all sections.
	 *
	 * @return array.
	 */
	private function get_sections() {
		$sections = apply_filters( 'wpi_template_sections', array(
			'general'        => array(
				'title'       => __( 'General Options', 'woocommerce-pdf-invoices' ),
				'description' => sprintf( __( 'Want to customize the template? The <a href="%s">FAQ</a> will give you a brief description.', 'woocommerce-pdf-invoices' ), 'https://wordpress.org/plugins/woocommerce-pdf-invoices' ),
			),
			'invoice_number' => array(
				'title' => __( 'Invoice Number Options', 'woocommerce-pdf-invoices' ),
			),
			'packing_slips'  => array(
				'title'       => __( 'Packing Slips Options', 'woocommerce-pdf-invoices' ),
				'description' => __( 'Packing slips are <strong>only available</strong> when using minimal template.', 'woocommerce-pdf-invoices' ),
			),
			'header'         => array(
				'title'       => __( 'Header Options', 'woocommerce-pdf-invoices' ),
				'description' => __( 'The header will be visible on every page.', 'woocommerce-pdf-invoices' ),
			),
			'body'           => array(
				'title'       => __( 'Body Options', 'woocommerce-pdf-invoices' ),
				'description' => __( 'Configuration options for the body of the template.', 'woocommerce-pdf-invoices' ),
			),
			'footer'         => array(
				'title'       => __( 'Footer Options', 'woocommerce-pdf-invoices' ),
				'description' => __( 'The footer will be visible on every page.', 'woocommerce-pdf-invoices' ),
			),
		) );

		return $sections;
	}

	/**
	 * Get templates for options.
	 *
	 * @return array
	 */
	private function get_template_options() {
		$templates = array();

		foreach ( array_map( 'basename', WPI()->templater()->get_templates() ) as $template ) {
			$templates[ $template ] = strtolower( $template );
		}

		return $templates;
	}

	/**
	 * Settings configuration.
	 *
	 * @return array
	 */
	private function get_fields() {
		$ex_tax_or_vat  = WC()->countries->ex_tax_or_vat();
		$inc_tax_or_vat = WC()->countries->inc_tax_or_vat();

		$settings = array(
			array(
				'id'       => 'bewpi-template-name',
				'name'     => $this->prefix . 'template_name',
				'title'    => __( 'Template', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'select_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Create a custom template by copying it from %1$s to %2$s.', 'woocommerce-pdf-invoices' ), '<code>plugins/woocommerce-pdf-invoices/includes/templates/invoice/simple</code>', '<code>uploads/woocommerce-pdf-invoices/templates/invoice/simple</code>' ),
				'options'  => $this->get_template_options(),
				'default'  => 'minimal',
				'priority' => 0,
			),
			array(
				'id'       => 'bewpi-color-theme-background',
				'name'     => $this->prefix . 'color_theme_background',
				'title'    => __( 'Color theme background', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'color',
				'desc'     => '',
				'default'  => '#000000',
				'priority' => 1,
			),
			array(
				'id'       => 'bewpi-color-theme-text',
				'name'     => $this->prefix . 'color_theme_text',
				'title'    => __( 'Color theme text', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'color',
				'desc'     => '',
				'default'  => '#FFFFFF',
				'priority' => 2,
			),
			array(
				'id'          => 'bewpi-date-format',
				'name'        => $this->prefix . 'date_format',
				'title'       => __( 'Date format', 'woocommerce-pdf-invoices' ),
				'callback'    => array( $this, 'input_callback' ),
				'page'        => $this->settings_key,
				'section'     => 'general',
				'type'        => 'text',
				'placeholder' => 'd-m-Y',
				'desc'        => '',
				'default'     => 'Y-m-d H:i:s',
				'attrs'       => array( 'required' ),
				'priority'    => 3,
			),
			array(
				'id'       => 'bewpi-show-payment-status',
				'name'     => $this->prefix . 'show_payment_status',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'checkbox',
				'label'    => __( 'Mark invoice as paid', 'woocommerce-pdf-invoices' ),
				'desc'     => __( 'Invoice will be watermarked when order has been paid.', 'woocommerce-pdf-invoices' ),
				'default'  => 0,
				'priority' => 4,
			),
			array(
				'id'       => 'bewpi-invoice-number-type',
				'name'     => $this->prefix . 'invoice_number_type',
				'title'    => __( 'Type', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'select_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'text',
				'desc'     => '',
				'options'  => array(
					'woocommerce_order_number' => __( 'WooCommerce order number', 'woocommerce-pdf-invoices' ),
					'sequential_number'        => __( 'Sequential number', 'woocommerce-pdf-invoices' ),
				),
				'default'  => 'sequential_number',
				'priority' => 0,
			),
			array(
				'id'       => 'bewpi-reset-counter',
				'name'     => $this->prefix . 'reset_counter',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'checkbox',
				'label'    => __( 'Reset invoice counter', 'woocommerce-pdf-invoices' ),
				'desc'     => __( 'Enable and determine next invoice number by changing the Next field below.', 'woocommerce-pdf-invoices' ),
				'default'  => false,
				'attrs'    => array( 'onchange="bewpi.setting.enableDisableNextInvoiceNumbering(this)"' ),
				'priority' => 1,
			),
			array(
				'id'       => 'bewpi-next-invoice-number',
				'name'     => $this->prefix . 'next_invoice_number',
				'title'    => __( 'Next', 'woocommerce-pdf-invoices' ) . wc_help_tip( __( 'The next invoice number when resetting the counter. Only available for Sequential numbering. All PDF invoices with invoice number greater then next invoice number will be deleted.', 'woocommerce-pdf-invoices' ) ),
				'callback' => array( $this, 'next_invoice_number_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'number',
				'desc'     => '',
				'default'  => 1,
				'attrs'    => array(
					'readonly',
					'min="1"',
				),
				'priority' => 2,
			),
			array(
				'id'       => 'bewpi-invoice-number-digits',
				'name'     => $this->prefix . 'invoice_number_digits',
				'title'    => __( 'Digits', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'number',
				'desc'     => '',
				'default'  => 5,
				'attrs'    => array(
					'min="3"',
					'max="20"',
					'required',
				),
				'priority' => 3,
			),
			array(
				'id'       => 'bewpi-invoice-number-prefix',
				'name'     => $this->prefix . 'invoice_number_prefix',
				'title'    => __( 'Prefix', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 4,
			),
			array(
				'id'       => 'bewpi-invoice-number-suffix',
				'name'     => $this->prefix . 'invoice_number_suffix',
				'title'    => __( 'Suffix', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 5,
			),
			array(
				'id'         => 'bewpi-invoice-number-format',
				'name'       => $this->prefix . 'invoice_number_format',
				'title'      => __( 'Format', 'woocommerce-pdf-invoices' ) . wc_help_tip( sprintf( __( 'The available placeholders are %s', 'woocommerce-pdf-invoices' ), self::formatted_number_placeholders() ) ),
				'callback'   => array( $this, 'input_callback' ),
				'page'       => $this->settings_key,
				'section'    => 'invoice_number',
				'type'       => 'text',
				'placholder' => '[number]',
				'desc'       => '',
				'default'    => '[number]-[Y]',
				'attrs'      => array( 'required' ),
				'priority'   => 6,
			),
			array(
				'id'       => 'bewpi-reset-counter-yearly',
				'name'     => $this->prefix . 'reset_counter_yearly',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'checkbox',
				'label'    => __( 'Reset yearly', 'woocommerce-pdf-invoices' ),
				'desc'     => __( 'Automatically reset invoice numbers on New Year\'s Day. All invoices have to be generated again when changing this option.', 'woocommerce-pdf-invoices' ),
				'default'  => 1,
				'priority' => 7,
			),
			array(
				'id'       => 'bewpi-packing-slips',
				'name'     => $this->prefix . 'disable_packing_slips',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'packing_slips',
				'type'     => 'checkbox',
				'label'    => __( 'Disable Packing Slips', 'woocommerce-pdf-invoices' ),
				'desc'     => '',
				'default'  => 0,
				'priority' => 0,
			),
			array(
				'id'       => 'bewpi-company-logo',
				'name'     => $this->prefix . 'company_logo',
				'title'    => __( 'Company logo', 'woocommerce-pdf-invoices' ) . wc_help_tip( __( 'Supports image file extensions JPEG, JPG, PNG. The image will have a maximum height of 150 pixels.', 'woocommerce-pdf-invoices' ) ),
				'callback' => array( $this, 'upload_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 0,
			),
			array(
				'id'       => 'bewpi-company-name',
				'name'     => $this->prefix . 'company_name',
				'title'    => __( 'Company name', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => '',
				'default'  => get_bloginfo(),
				'priority' => 1,
			),
			array(
				'id'       => 'bewpi-company-address',
				'name'     => $this->prefix . 'company_address',
				'title'    => __( 'Company address', 'woocommerce-pdf-invoices' ) . wc_help_tip( __( 'Supports HTML markup.', 'woocommerce-pdf-invoices' ) ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => '',
				'default'  => BEWPI_WC_Core_Compatibility::is_wc_version_gte_3_0() ? WPI()->get_formatted_base_address() : '',
				'priority' => 2,
			),
			array(
				'id'       => 'bewpi-company-phone',
				'name'     => $this->prefix . 'company_phone',
				'title'    => __( 'Company phone', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 3,
			),
			array(
				'id'       => 'bewpi-company-email-address',
				'name'     => $this->prefix . 'company_email_address',
				'title'    => __( 'Company email address', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 4,
			),
			array(
				'id'       => 'bewpi-company-registration-number',
				'name'     => $this->prefix . 'company_registration_number',
				'title'    => __( 'Company Registration Number', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 5,
			),
			array(
				'id'       => 'bewpi-company-vat-id',
				'name'     => $this->prefix . 'company_vat_id',
				'title'    => __( 'Company VAT ID', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 6,
			),
			array(
				'id'       => 'bewpi-title',
				'name'     => $this->prefix . 'title',
				'title'    => __( 'Title', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'text',
				'desc'     => __( 'Change the name of the invoice.', 'woocommerce-pdf-invoices' ),
				'default'  => __( 'Invoice', 'woocommerce-pdf-invoices' ),
				'priority' => 0,
			),
			array(
				'id'       => 'bewpi-show-ship-to',
				'name'     => $this->prefix . 'show_ship_to',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'checkbox',
				'label'    => __( 'Show customers shipping address', 'woocommerce-pdf-invoices' ),
				'desc'     => __( 'The customers shipping address will not be visible when order has only virtual/digital products.', 'woocommerce-pdf-invoices' ),
				'default'  => 1,
				'priority' => 2,
			),
			array(
				'id'       => 'bewpi-show-sku-meta',
				'name'     => 'bewpi_show_sku_meta',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'checkbox',
				'label'    => __( 'Show SKU as meta data', 'woocommerce-pdf-invoices' ),
				'desc'     => '',
				'default'  => 1,
				'priority' => 3,
			),
			array(
				'id'       => 'bewpi-columns',
				'name'     => 'bewpi_columns',
				'title'    => __( 'Line item columns', 'woocommerce-pdf-invoices' ) . wc_help_tip( __( 'Select and sort all desired invoice columns. Try dragging the elements.', 'woocommerce-pdf-invoices' ) ),
				'callback' => array( $this, 'multi_select_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'multiple_select',
				'desc'     => '',
				'class'    => 'bewpi-columns',
				'options'  => apply_filters( 'wpi_body_columns_options', array(
					'description' => array(
						'name'    => __( 'Description', 'woocommerce-pdf-invoices' ),
						'value'   => 'description',
						'default' => 1,
					),
					'quantity'    => array(
						'name'    => __( 'Quantity', 'woocommerce-pdf-invoices' ),
						'value'   => 'quantity',
						'default' => 1,
					),
					'total'       => array(
						'name'    => __( 'Total', 'woocommerce-pdf-invoices' ),
						'value'   => 'total_ex_vat',
						'default' => 1,
					),
				), $this ),
				'priority' => 5,
			),
			array(
				'id'       => 'bewpi-totals',
				'name'     => 'bewpi_totals',
				'title'    => __( 'Total rows', 'woocommerce-pdf-invoices' ) . wc_help_tip( __( 'Select and sort all desired invoice totals. Try dragging the elements.', 'woocommerce-pdf-invoices' ) ),
				'callback' => array( $this, 'multi_select_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'multiple_select',
				'desc'     => '',
				'class'    => 'bewpi-totals',
				'options'  => apply_filters( 'wpi_body_totals_options', array(
					'shipping_ex_vat' => array(
						'name'    => __( 'Shipping', 'woocommerce-pdf-invoices' ),
						'value'   => 'shipping_ex_vat',
						'default' => 1,
					),
					'fee_ex_vat'      => array(
						'name'    => __( 'Fee', 'woocommerce-pdf-invoices' ),
						'value'   => 'fee_ex_vat',
						'default' => 1,
					),
					'vat'             => array(
						'name'    => WC()->countries->tax_or_vat(),
						'value'   => 'vat',
						'default' => 1,
					),
					'total_incl_vat'  => array(
						'name'    => __( 'Total', 'woocommerce-pdf-invoices' ),
						'value'   => 'total_incl_vat',
						'default' => 1,
					),
				), $this ),
				'priority' => 6,
			),
			array(
				'id'       => 'bewpi-show-customer-notes',
				'name'     => $this->prefix . 'show_customer_notes',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'checkbox',
				'label'    => __( 'Show customer notes', 'woocommerce-pdf-invoices' ),
				'desc'     => '',
				'default'  => 1,
				'priority' => 8,
			),
			array(
				'id'       => 'bewpi-terms',
				'name'     => $this->prefix . 'terms',
				'title'    => __( 'Terms & Conditions', 'woocommerce-pdf-invoices' ) . wc_help_tip( __( 'Supports HTML markup.', 'woocommerce-pdf-invoices' ) ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 9,
			),
			array(
				'id'       => 'bewpi-left-footer-column',
				'name'     => $this->prefix . 'left_footer_column',
				'title'    => __( 'Left footer column.', 'woocommerce-pdf-invoices' ) . wc_help_tip( __( 'Supports HTML markup.', 'woocommerce-pdf-invoices' ) ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'footer',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 0,
			),
			array(
				'id'       => 'bewpi-right-footer-column',
				'name'     => $this->prefix . 'right_footer_column',
				'title'    => __( 'Right footer column.', 'woocommerce-pdf-invoices' ) . wc_help_tip( __( 'Supports HTML markup.', 'woocommerce-pdf-invoices' ) ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'footer',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
				'priority' => 1,
			),
		);

		$settings = apply_filters( 'wpi_template_settings', $settings, $this );

		usort( $settings, function ( $item1, $item2 ) {
			if ( $item1['priority'] === $item2['priority'] ) {
				return 0;
			}

			return $item1['priority'] < $item2['priority'] ? - 1 : 1;
		} );

		return $settings;
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input form settings.
	 *
	 * @return mixed|void
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

			// strip all html and php tags and properly handle quoted strings.
			$output[ $key ] = $this->strip_str( stripslashes( $input[ $key ] ) );
		}

		if ( isset( $input['bewpi_reset_counter'] ) && $input['bewpi_reset_counter'] ) {
			set_transient( 'bewpi_next_invoice_number', intval( $input['bewpi_next_invoice_number'] ) );
		}

		if ( ! isset( $input['bewpi_invoice_number_format'] ) || false === strpos( $input['bewpi_invoice_number_format'], '[number]' ) ) {
			$error          = new stdClass();
			$error->message = __( 'Invoice number format field must contain at least the placeholder: [number].', 'woocommerce-pdf-invoices' );
			$error->type    = 'error';
			$this->add_error( $error );
		} else {
			$output['bewpi_invoice_number_format'] = sanitize_text_field( $input['bewpi_invoice_number_format'] );
		}

		return apply_filters( 'bewpi_sanitized_' . $this->settings_key, $output, $input );
	}

	/**
	 * Sets template to default template when custom template has been deleted.
	 */
	private function fix_deleted_custom_template() {
		$options   = get_option( $this->settings_key );
		$templates = array_map( 'basename', WPI()->templater()->get_templates() );

		// Check for deleted custom template.
		if ( in_array( $options['bewpi_template_name'], $templates, true ) ) {
			return;
		}

		$defaults                       = $this->get_defaults();
		$options['bewpi_template_name'] = $defaults['bewpi_template_name'];
		update_option( $this->settings_key, $options );
	}
}
