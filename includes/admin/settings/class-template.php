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
			'general'         => array(
				'title'       => __( 'General Options', 'woocommerce-pdf-invoices' ),
				'description' => sprintf( __( 'Want to customize the template? The <a href="%s">FAQ</a> will give you a brief description.', 'woocommerce-pdf-invoices' ), 'https://wordpress.org/plugins/woocommerce-pdf-invoices' ),
			),
			'invoice_number'  => array(
				'title' => __( 'Invoice Number Options', 'woocommerce-pdf-invoices' ),
			),
			'packing_slips'   => array(
				'title'       => __( 'Packing Slips Options', 'woocommerce-pdf-invoices' ),
				'description' => __( 'Packing slips are <strong>only available</strong> when using minimal template.', 'woocommerce-pdf-invoices' ),
			),
			'header'          => array(
				'title'       => __( 'Header Options', 'woocommerce-pdf-invoices' ),
				'description' => __( 'The header will be visible on every page.', 'woocommerce-pdf-invoices' ),
			),
			'body'            => array(
				'title'       => __( 'Body Options', 'woocommerce-pdf-invoices' ),
				'description' => __( 'Configuration options for the body of the template. .', 'woocommerce-pdf-invoices' ),
			),
			'footer'          => array(
				'title'       => __( 'Footer Options', 'woocommerce-pdf-invoices' ),
				'description' => __( 'The footer will be visible on every page.', 'woocommerce-pdf-invoices' ),
			),
			'visible_columns' => array(
				'title'       => __( 'Table Content', 'woocommerce-pdf-invoices' ),
				'description' => __( 'Enable or disable the columns.', 'woocommerce-pdf-invoices' ),
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
		$company_logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'thumbnail' );

		$settings = array(
			array(
				'id'       => 'bewpi-template-name',
				'name'     => $this->prefix . 'template_name',
				'title'    => __( 'Template', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'select_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Create a custom template by copying it from %1$s to %2$s.', 'woocommerce-pdf-invoices' ), '<code>plugins/woocommerce-pdf-invoices/includes/templates/invoice/simple</code>', '<code>uploads/woocommerce-pdf-invoices/templates/invoice/simple</code>' )
				              . '<br><div class="bewpi-notes">'
				              . sprintf( __( '<strong>Note:</strong> The %1$s template will probably no longer be supported in future versions, consider using the %2$s template.', 'woocommerce-pdf-invoices' ), '<strong>micro</strong>', '<strong>minimal</strong>' )
				              . '</div>',
				'options'  => $this->get_template_options(),
				'default'  => 'minimal',
			),
			array(
				'id'       => 'bewpi-color-theme',
				'name'     => $this->prefix . 'color_theme',
				'title'    => __( 'Color theme', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'color',
				'desc'     => '',
				'default'  => '#000000',
			),
			array(
				'id'       => 'bewpi-theme-text-black',
				'name'     => $this->prefix . 'theme_text_black',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'checkbox',
				'desc'     => __( 'Display theme text in black color', 'woocommerce-pdf-invoices' )
				              . '<br/><div class="bewpi-notes">'
				              . __( 'Enable if you\'ve set the color theme to white or some other light color.', 'woocommerce-pdf-invoices' )
				              . '</div>',
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 0,
			),
			array(
				'id'       => 'bewpi-date-format',
				'name'     => $this->prefix . 'date_format',
				'title'    => __( 'Date format', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'text',
				'desc'     => sprintf( __( '<a href="%s">Format</a> of invoice date and order date.', 'woocommerce-pdf-invoices' ), 'http://php.net/manual/en/datetime.formats.date.php' ),
				'default'  => 'Y-m-d H:i:s',
				'attrs'    => array( 'required' ),
			),
			array(
				'id'       => 'bewpi-display-prices-incl-tax',
				'name'     => $this->prefix . 'display_prices_incl_tax',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'checkbox',
				'desc'     => __( 'Display prices including tax', 'woocommerce-pdf-invoices' )
				              . '<br/><div class="bewpi-notes">'
				              . __( 'Line item totals will be including tax. <br/><b>Note</b>: Subtotal will still be excluding tax, so disable it within the visible columns section.', 'woocommerce-pdf-invoices' )
				              . '</div>',
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 0,
			),
			array(
				'id'       => 'bewpi-shipping-taxable',
				'name'     => $this->prefix . 'shipping_taxable',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'checkbox',
				'desc'     => __( 'Shipping taxable', 'woocommerce-pdf-invoices' )
				              . '<br/><div class="bewpi-notes">'
				              . __( 'Enable to display subtotal including shipping.', 'woocommerce-pdf-invoices' )
				              . '</div>',
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 0,
			),
			array(
				'id'       => 'bewpi-show-payment-status',
				'name'     => $this->prefix . 'show_payment_status',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'checkbox',
				'desc'     => __( 'Mark invoice as paid', 'woocommerce-pdf-invoices' )
				              . '<br/><div class="bewpi-notes">'
				              . __( 'Invoice will be watermarked when order has been paid.', 'woocommerce-pdf-invoices' )
				              . '</div>',
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 0,
			),
			array(
				'id'       => 'bewpi-packing-slips',
				'name'     => $this->prefix . 'disable_packing_slips',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'packing_slips',
				'type'     => 'checkbox',
				'desc'     => __( 'Disable Packing Slips', 'woocommerce-pdf-invoices' ),
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 0,
			),
			array(
				'id'       => 'bewpi-company-logo',
				'name'     => $this->prefix . 'company_logo',
				'title'    => __( 'Company logo', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Use the <a href="%1$s">Media Library</a> to <a href="%2$s">upload</a> or choose a .jpg, .jpeg, .gif or .png file and copy and paste the <a href="%3$s" target="_blank">File URL</a>.', 'woocommerce-pdf-invoices' ), 'media-new.php', 'upload.php', 'https://codex.wordpress.org/Media_Library_Screen#Attachment_Details' ),
				'default'  => ( is_array( $company_logo ) ) ? $company_logo[0] : '',
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
			),
			array(
				'id'       => 'bewpi-company-address',
				'name'     => $this->prefix . 'company_address',
				'title'    => __( 'Company address', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Allowed HTML tags: %s. Since WooCommerce +3.0 this setting is ignored and the WooCommerce store address is used.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ),
				'default'  => BEWPI_WC_Core_Compatibility::is_wc_version_gte_3_0() ? WPI()->get_formatted_base_address() : '',
			),
			array(
				'id'       => 'bewpi-company-details',
				'name'     => $this->prefix . 'company_details',
				'title'    => __( 'Company details', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ),
				'default'  => '',
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
			),
			array(
				'id'       => 'bewpi-company-email_address',
				'name'     => $this->prefix . 'company_email_address',
				'title'    => __( 'Company email address', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => '',
				'default'  => '',
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
			),
			array(
				'id'       => 'bewpi-intro-text',
				'name'     => $this->prefix . 'intro_text',
				'title'    => __( 'Thank you text', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'header',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ) . ' '
				              . __( 'Visible in big colored bar directly after invoice total.', 'woocommerce-pdf-invoices' ),
				'default'  => '',
			),
			array(
				'id'       => 'bewpi-show-ship-to',
				'name'     => $this->prefix . 'show_ship_to',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'checkbox',
				'desc'     => __( 'Show customers shipping address', 'woocommerce-pdf-invoices' )
				              . '<br/><div class="bewpi-notes">'
				              . __( 'Customers shipping address won\'t be visible when order has only virtual products.', 'woocommerce-pdf-invoices' )
				              . '</div>',
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 1,
			),
			array(
				'id'       => 'bewpi-show-customer-notes',
				'name'     => $this->prefix . 'show_customer_notes',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'checkbox',
				'desc'     => __( 'Show customer notes', 'woocommerce-pdf-invoices' ),
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 1,
			),
			array(
				'id'       => 'bewpi-terms',
				'name'     => $this->prefix . 'terms',
				'title'    => __( 'Terms & conditions, policies etc.', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'body',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ) . ' '
				              . sprintf( __( 'Visible below customer notes and above footer. Want to attach additional pages to the invoice? Take a look at <a href="%1$s">%2$s</a> plugin.', 'woocommerce-pdf-invoices' ), 'http://wcpdfinvoices.com', 'WooCommerce PDF Invoices Premium' ),
				'default'  => '',
			),
			array(
				'id'       => 'bewpi-left-footer-column',
				'name'     => $this->prefix . 'left_footer_column',
				'title'    => __( 'Left footer column.', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'footer',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ),
				'default'  => '',
			),
			array(
				'id'       => 'bewpi-right-footer-column',
				'name'     => $this->prefix . 'right_footer_column',
				'title'    => __( 'Right footer column.', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'textarea_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'footer',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ),
				'default'  => '',
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
			),
			array(
				'id'       => 'bewpi-reset-counter',
				'name'     => $this->prefix . 'reset_counter',
				'title'    => '',
				'callback' => array( $this, 'reset_counter_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'checkbox',
				'desc'     => __( 'Reset invoice counter', 'woocommerce-pdf-invoices' ),
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 0,
				'attrs'    => array( 'onchange="bewpi.setting.enableDisableNextInvoiceNumbering(this)"' ),
			),
			array(
				'id'       => 'bewpi-next-invoice-number',
				'name'     => $this->prefix . 'next_invoice_number',
				'title'    => __( 'Next', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'next_invoice_number_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'number',
				'desc'     => __( 'Next invoice number when resetting counter.', 'woocommerce-pdf-invoices' )
				              . '<br/>'
				              . __( '<b>Note:</b> Only available for Sequential numbering. All PDF invoices with invoice number greater then next invoice number will be deleted!', 'woocommerce-pdf-invoices' ),
				'default'  => 1,
				'attrs'    => array(
					'readonly',
					'min="1"',
				),
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
			),
			array(
				'id'       => 'bewpi-invoice-number-format',
				'name'     => $this->prefix . 'invoice_number_format',
				'title'    => __( 'Format', 'woocommerce-pdf-invoices' ),
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'text',
				'desc'     => sprintf( __( 'Available placeholders: %s.', 'woocommerce-pdf-invoices' ), self::formatted_number_placeholders() )
				              . '<br>'
				              . sprintf( __( '<b>Note:</b> %s is required and slashes aren\'t supported.', 'woocommerce-pdf-invoices' ), '<code>[number]</code>' ),
				'default'  => '[number]-[Y]',
				'attrs'    => array( 'required' ),
			),
			array(
				'id'       => 'bewpi-reset-counter-yearly',
				'name'     => $this->prefix . 'reset_counter_yearly',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'invoice_number',
				'type'     => 'checkbox',
				'desc'     => __( 'Reset yearly', 'woocommerce-pdf-invoices' )
				              . '<br/><div class="bewpi-notes">'
				              . __( 'Automatically reset invoice numbers on new year\'s day.', 'woocommerce-pdf-invoices' )
				              . '<br/>'
				              . __( '<b>Note</b>: You will have to generate all invoices again when changing option.', 'woocommerce-pdf-invoices' )
				              . '</div>',
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 1,
			),
			array(
				'id'       => 'bewpi-show-sku',
				'name'     => $this->prefix . 'show_sku',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'visible_columns',
				'type'     => 'checkbox',
				'desc'     => __( 'SKU', 'woocommerce-pdf-invoices' ),
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 0,
			),
			array(
				'id'       => 'bewpi-show-subtotal',
				'name'     => $this->prefix . 'show_subtotal',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'visible_columns',
				'type'     => 'checkbox',
				'desc'     => __( 'Subtotal', 'woocommerce-pdf-invoices' ),
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 1,
			),
			array(
				'id'       => 'bewpi-show-tax',
				'name'     => $this->prefix . 'show_tax',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'visible_columns',
				'type'     => 'checkbox',
				'desc'     => __( 'Tax (item)', 'woocommerce-pdf-invoices' ),
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 0,
			),
			array(
				'id'       => 'bewpi-show-tax-row',
				'name'     => $this->prefix . 'show_tax_total',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'visible_columns',
				'type'     => 'checkbox',
				'desc'     => __( 'Tax (total)', 'woocommerce-pdf-invoices' ),
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 1,
			),
			array(
				'id'       => 'bewpi-show-discount',
				'name'     => $this->prefix . 'show_discount',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'visible_columns',
				'type'     => 'checkbox',
				'desc'     => __( 'Discount', 'woocommerce-pdf-invoices' ),
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 1,
			),
			array(
				'id'       => 'bewpi-show-shipping',
				'name'     => $this->prefix . 'show_shipping',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'visible_columns',
				'type'     => 'checkbox',
				'desc'     => __( 'Shipping', 'woocommerce-pdf-invoices' ),
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => 1,
			),
		);

		return apply_filters( 'wpi_template_settings', $settings, $this );
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

			if ( 'bewpi_company_logo' === $key ) {
				continue;
			}

			// strip all html and php tags and properly handle quoted strings.
			$output[ $key ] = $this->strip_str( stripslashes( $input[ $key ] ) );
		}

		if ( isset( $input['bewpi_company_logo'] ) ) {
			if ( ! empty( $input['bewpi_company_logo'] ) ) {
				$image_url = $this->validate_image( $input['bewpi_company_logo'] );
				if ( $image_url ) {
					$output['bewpi_company_logo'] = $image_url;
				} else {
					add_settings_error(
						esc_attr( $this->settings_key ),
						'file-not-found',
						__( 'Company logo not found. Upload the image to the Media Library and try again.', 'woocommerce-pdf-invoices' )
					);
				}
			} else {
				$output['bewpi_company_logo'] = '';
			}
		}

		if ( isset( $input['bewpi_reset_counter'] ) && $input['bewpi_reset_counter'] ) {
			set_transient( 'bewpi_next_invoice_number', intval( $input['bewpi_next_invoice_number'] ) );
		}

		return apply_filters( 'bewpi_sanitized_' . $this->settings_key, $output, $input );
	}

	/**
	 * Validate image against modified urls and check for extension.
	 *
	 * @param string $image_url source url of the image.
	 *
	 * @return bool|string false or image url.
	 */
	public function validate_image( $image_url ) {
		$image_url = esc_url_raw( $image_url, array( 'http', 'https' ) );
		$query     = array(
			'post_type'  => 'attachment',
			'fields'     => 'ids',
			'meta_query' => array(
				array(
					'key'     => '_wp_attached_file',
					'value'   => basename( $image_url ),
					'compare' => 'LIKE',
				),
			)
		);

		$ids = get_posts( $query );
		if ( count( $ids ) === 0 ) {
			WPI()->logger()->error( sprintf( 'Image %s not found in post table.', basename( $image_url ) ) );

			return false;
		}

		return wp_get_attachment_image_url( $ids[0], 'full' );
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
