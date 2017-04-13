<?php
/**
 * Template settings
 *
 * Handling template settings.
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BEWPI_Template_Settings' ) ) {
	/**
	 * Class BEWPI_Template_Settings.
	 */
	class BEWPI_Template_Settings extends BEWPI_Abstract_Setting {
		/**
		 * Settings key constant.
		 */
		const SETTINGS_KEY = 'bewpi_template_settings';

		/**
		 * BEWPI_Template_Settings constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_notices', array( $this, 'show_settings_notices' ) );
		}

		/**
		 * Settings configuration.
		 *
		 * @return array
		 */
		private function the_settings() {
			$templater = BEWPI()->templater();
			$templates = array();
			foreach ( array_map( 'basename', $templater->get_templates() ) as $template ) {
				$templates[] = array(
					'id'    => $template,
					'value' => strtolower( $template ),
				);
			}
			$company_logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'thumbnail' );

			$settings = array(
				array(
					'id'       => 'bewpi-template-name',
					'name'     => self::PREFIX . 'template_name',
					'title'    => __( 'Template', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'select_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'general',
					'type'     => 'text',
					'desc'     => sprintf( __( 'Create a custom template by copying it from %1$s to %2$s.', 'woocommerce-pdf-invoices' ), '<code>plugins/woocommerce-pdf-invoices/includes/templates/invoice/simple</code>', '<code>uploads/woocommerce-pdf-invoices/templates/invoice/simple</code>' ),
					'options'  => $templates,
					'default'  => 'minimal',
				),
				array(
					'id'       => 'bewpi-color-theme',
					'name'     => self::PREFIX . 'color_theme',
					'title'    => __( 'Color theme', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'general',
					'type'     => 'color',
					'desc'     => '',
					'default'  => '#000000',
				),
				array(
					'id'       => 'bewpi-theme-text-black',
					'name'     => self::PREFIX . 'theme_text_black',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
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
					'name'     => self::PREFIX . 'date_format',
					'title'    => __( 'Date format', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'general',
					'type'     => 'text',
					'desc'     => __( '<a href="http://php.net/manual/en/datetime.formats.date.php">Format</a> of invoice date and order date.', 'woocommerce-pdf-invoices' ),
					'default'  => get_option( 'date_format' ),
					'attrs'    => array( 'required' ),
				),
				array(
					'id'       => 'bewpi-display-prices-incl-tax',
					'name'     => self::PREFIX . 'display_prices_incl_tax',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
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
					'name'     => self::PREFIX . 'shipping_taxable',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
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
					'name'     => self::PREFIX . 'show_payment_status',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
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
					'id'       => 'bewpi-company-name',
					'name'     => self::PREFIX . 'company_name',
					'title'    => __( 'Company name', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'header',
					'type'     => 'text',
					'desc'     => '',
					'default'  => get_bloginfo(),
				),
				array(
					'id'       => 'bewpi-company-logo',
					'name'     => self::PREFIX . 'company_logo',
					'title'    => __( 'Company logo', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'header',
					'type'     => 'text',
					'desc'     => sprintf( __( 'Use the <a href="%1$s">Media Library</a> to <a href="%2$s">upload</a> or choose a .jpg, .jpeg, .gif or .png file and copy and paste the <a href="%3$s" target="_blank">URL</a>.', 'woocommerce-pdf-invoices' ), 'media-new.php', 'upload.php', 'https://codex.wordpress.org/Media_Library_Screen#Attachment_Details' ),
					'default'  => ( is_array( $company_logo ) ) ? $company_logo[0] : '',
				),
				array(
					'id'       => 'bewpi-company-address',
					'name'     => self::PREFIX . 'company_address',
					'title'    => __( 'Company address', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'textarea_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'header',
					'type'     => 'text',
					'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ),
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-company-details',
					'name'     => self::PREFIX . 'company_details',
					'title'    => __( 'Company details', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'textarea_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'header',
					'type'     => 'text',
					'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ),
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-title',
					'name'     => self::PREFIX . 'title',
					'title'    => __( 'Title', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'body',
					'type'     => 'text',
					'desc'     => __( 'Change the name of the invoice.', 'woocommerce-pdf-invoices' ),
					'default'  => __( 'Invoice', 'woocommerce-pdf-invoices' ),
				),
				array(
					'id'       => 'bewpi-intro-text',
					'name'     => self::PREFIX . 'intro_text',
					'title'    => __( 'Thank you text', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'textarea_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'header',
					'type'     => 'text',
					'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ) . ' '
					              . __( 'Visible in big colored bar directly after invoice total.', 'woocommerce-pdf-invoices' ),
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-show-ship-to',
					'name'     => self::PREFIX . 'show_ship_to',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'body',
					'type'     => 'checkbox',
					'desc'     => __( 'Show customers shipping address<br/><div class="bewpi-notes">Customers shipping address won\'t be visible when order has only virtual products.</div>', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
				array(
					'id'       => 'bewpi-show-customer-notes',
					'name'     => self::PREFIX . 'show_customer_notes',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'body',
					'type'     => 'checkbox',
					'desc'     => __( 'Show customer notes', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
				array(
					'id'       => 'bewpi-terms',
					'name'     => self::PREFIX . 'terms',
					'title'    => __( 'Terms & conditions, policies etc.', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'textarea_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'body',
					'type'     => 'text',
					'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ) . ' '
					              . sprintf( __( 'Visible below customer notes and above footer. Want to attach additional pages to the invoice? Take a look at <a href="%s">WooCommerce PDF Invoices Premium</a> plugin.', 'woocommerce-pdf-invoices' ), 'http://wcpdfinvoices.com' ),
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-left-footer-column',
					'name'     => self::PREFIX . 'left_footer_column',
					'title'    => __( 'Left footer column.', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'textarea_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'footer',
					'type'     => 'text',
					'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ),
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-right-footer-column',
					'name'     => self::PREFIX . 'right_footer_column',
					'title'    => __( 'Right footer column.', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'textarea_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'footer',
					'type'     => 'text',
					'desc'     => sprintf( __( 'Allowed HTML tags: %s.', 'woocommerce-pdf-invoices' ), self::formatted_html_tags() ),
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-invoice-number-type',
					'name'     => self::PREFIX . 'invoice_number_type',
					'title'    => __( 'Type', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'select_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'invoice_number',
					'type'     => 'text',
					'desc'     => '',
					'options'  => array(
						array(
							'id'  => __( 'WooCommerce order number', 'woocommerce-pdf-invoices' ),
							'value' => 'woocommerce_order_number',
						),
						array(
							'id'  => __( 'Sequential number', 'woocommerce-pdf-invoices' ),
							'value' => 'sequential_number',
						),
					),
					'default'  => 'sequential_number',
				),
				array(
					'id'       => 'bewpi-reset-counter',
					'name'     => self::PREFIX . 'reset_counter',
					'title'    => '',
					'callback' => array( $this, 'reset_counter_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'invoice_number',
					'type'     => 'checkbox',
					'desc'     => __( 'Reset invoice counter', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 0,
					'attrs'    => array( 'onchange="bewpi.setting.enableDisableNextInvoiceNumbering(this)"' ),
				),
				array(
					'id'       => 'bewpi-next-invoice-number',
					'name'     => self::PREFIX . 'next_invoice_number',
					'title'    => __( 'Next', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'next_invoice_number_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'invoice_number',
					'type'     => 'number',
					'desc'     => __( 'Next invoice number when resetting counter.<br/><b>Note:</b> Only available for Sequential numbering. All PDF invoices with invoice number greater then next invoice number will be deleted.', 'woocommerce-pdf-invoices' ),
					'default'  => 1,
					'attrs'    => array(
						'readonly',
						'min="1"',
					),
				),
				array(
					'id'       => 'bewpi-invoice-number-digits',
					'name'     => self::PREFIX . 'invoice_number_digits',
					'title'    => __( 'Digits', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
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
					'name'     => self::PREFIX . 'invoice_number_prefix',
					'title'    => __( '[prefix]', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'invoice_number',
					'type'     => 'text',
					'desc'     => '',
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-invoice-number-suffix',
					'name'     => self::PREFIX . 'invoice_number_suffix',
					'title'    => __( '[suffix]', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'invoice_number',
					'type'     => 'text',
					'desc'     => '',
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-invoice-number-format',
					'name'     => self::PREFIX . 'invoice_number_format',
					'title'    => __( 'Format', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
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
					'name'     => self::PREFIX . 'reset_counter_yearly',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'invoice_number',
					'type'     => 'checkbox',
					'desc'     => __( 'Reset yearly', 'woocommerce-pdf-invoices' )
								. '<br/><div class="bewpi-notes">'
								. __( 'Automatically reset invoice numbers on new year\'s day. <br/><b>Note</b>: You will have to generate all invoices again when changing option.', 'woocommerce-pdf-invoices' )
								. '</div>',
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
				array(
					'id'       => 'bewpi-show-sku',
					'name'     => self::PREFIX . 'show_sku',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'visible_columns',
					'type'     => 'checkbox',
					'desc'     => __( 'SKU', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 0,
				),
				array(
					'id'       => 'bewpi-show-subtotal',
					'name'     => self::PREFIX . 'show_subtotal',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'visible_columns',
					'type'     => 'checkbox',
					'desc'     => __( 'Subtotal', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
				array(
					'id'       => 'bewpi-show-tax',
					'name'     => self::PREFIX . 'show_tax',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'visible_columns',
					'type'     => 'checkbox',
					'desc'     => __( 'Tax (item)', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 0,
				),
				array(
					'id'       => 'bewpi-show-tax-row',
					'name'     => self::PREFIX . 'show_tax_total',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'visible_columns',
					'type'     => 'checkbox',
					'desc'     => __( 'Tax (total)', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
				array(
					'id'       => 'bewpi-show-discount',
					'name'     => self::PREFIX . 'show_discount',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'visible_columns',
					'type'     => 'checkbox',
					'desc'     => __( 'Discount', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
				array(
					'id'       => 'bewpi-show-shipping',
					'name'     => self::PREFIX . 'show_shipping',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'visible_columns',
					'type'     => 'checkbox',
					'desc'     => __( 'Shipping', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
			);

			return $settings;
		}

		/**
		 * Gets all default settings values from the settings array.
		 *
		 * @return array
		 */
		private function get_defaults() {
			$defaults = wp_list_pluck( $this->the_settings(), 'default', 'name' );
			return $defaults;
		}

		/**
		 * Load (default) settings.
		 */
		public function load_settings() {
			// merge defaults with options.
			$defaults = $this->get_defaults();
			$options  = (array) get_option( self::SETTINGS_KEY );
			$options  = array_merge( $defaults, $options );

			// check for deleted custom template.
			$templater = BEWPI()->templater();
			$templates = array_map( 'basename', $templater->get_templates() );
			if ( ! in_array( $options['bewpi_template_name'], $templates, true ) ) {
				$options['bewpi_template_name'] = $defaults['bewpi_template_name'];
			}

			update_option( self::SETTINGS_KEY, $options );
		}

		/**
		 * General settings section information.
		 */
		public function general_desc_callback() {
			_e( 'These are the general template options.', 'woocommerce-pdf-invoices' ); // WPCS: XSS OK.
		}

		/**
		 * Invoice number section information.
		 */
		public function invoice_number_desc_callback() {
			_e( 'These are the invoice number options.', 'woocommerce-pdf-invoices' ); // WPCS: XSS OK.
		}

		/**
		 * Invoice header section information.
		 */
		public function header_desc_callback() {
			_e( 'The header will be visible on every page.', 'woocommerce-pdf-invoices' ); // WPCS: XSS OK.
		}

		/**
		 * Invoice header section information.
		 */
		public function footer_desc_callback() {
			echo __( 'The footer will be visible on every page.', 'woocommerce-pdf-invoices' );
		}

		/**
		 * Visible columns section information.
		 */
		public function visible_columns_desc_callback() {
			_e( 'Enable or disable the columns.', 'woocommerce-pdf-invoices' ); // WPCSS: XSS OK.
		}

		/**
		 * Adds all the different settings sections.
		 */
		private function add_settings_sections() {
			add_settings_section( 'general', __( 'General Options', 'woocommerce-pdf-invoices' ), array(
				$this,
				'general_desc_callback',
			), self::SETTINGS_KEY );
			add_settings_section( 'invoice_number', __( 'Invoice Number Options', 'woocommerce-pdf-invoices' ), array(
				$this,
				'invoice_number_desc_callback',
			), self::SETTINGS_KEY );
			add_settings_section( 'header', __( 'Header Options', 'woocommerce-pdf-invoices' ), array(
				$this,
				'header_desc_callback',
			), self::SETTINGS_KEY );
			add_settings_section( 'body', __( 'Body Options', 'woocommerce-pdf-invoices' ), null, self::SETTINGS_KEY );
			add_settings_section( 'footer', __( 'Footer Options', 'woocommerce-pdf-invoices' ), array(
				$this,
				'footer_desc_callback',
			), self::SETTINGS_KEY );
			add_settings_section( 'visible_columns', __( 'Visible Columns', 'woocommerce-pdf-invoices' ), array(
				$this,
				'visible_columns_desc_callback',
			), self::SETTINGS_KEY );
		}

		/**
		 * Validate input of settings.
		 *
		 * @param array $input form settings.
		 *
		 * @return mixed|void
		 */
		public function validate_input( $input ) {
			$output = array();

			// strip strings.
			foreach ( $input as $key => $value ) {
				if ( ! isset( $input[ $key ] ) ) {
					continue;
				}

				if ( 'bewpi_company_logo' === $key ) {
					$output[ $key ] = '';
					continue;
				}

				// strip all html and php tags and properly handle quoted strings.
				$output[ $key ] = $this->strip_str( stripslashes( $input[ $key ] ) );
			}

			if ( isset( $input['bewpi_company_logo'] ) && ! empty( $input['bewpi_company_logo'] ) ) {
				$image_url = $this->validate_image( $input['bewpi_company_logo'] );
				if ( $image_url ) {
					$output['bewpi_company_logo'] = $image_url;
				} else {
					add_settings_error(
						esc_attr( self::SETTINGS_KEY ),
						'file-not-found',
						__( 'Company logo not found. Upload the image to the Media Library and try again.', 'woocommerce-pdf-invoices' )
					);
				}
			}

			if ( isset( $input['bewpi_reset_counter'] ) && $input['bewpi_reset_counter'] ) {
				set_transient( 'bewpi_next_invoice_number', intval( $input['bewpi_next_invoice_number'] ) );
			}

			// return the array processing any additional functions filtered by this action.
			return apply_filters( 'validate_input', $output, $input );
		}

		/**
		 * Adds all settings fields.
		 */
		private function add_settings_fields() {
			$the_settings = $this->the_settings();
			foreach ( $the_settings as $setting ) {
				add_settings_field( $setting['name'], $setting['title'], $setting['callback'], $setting['page'], $setting['section'], $setting );
			}
		}

		/**
		 * Register settings.
		 */
		public function create_settings() {
			$this->add_settings_sections();
			register_setting( self::SETTINGS_KEY, self::SETTINGS_KEY, array( $this, 'validate_input' ) );
			$this->add_settings_fields();
		}

		/**
		 * Initialize admin.
		 */
		public function admin_init() {
			$this->load_settings();
			$this->create_settings();
		}

		/**
		 * Show all settings notices.
		 */
		public function show_settings_notices() {
			settings_errors( self::SETTINGS_KEY );
		}
	}

	new BEWPI_Template_Settings();
}
