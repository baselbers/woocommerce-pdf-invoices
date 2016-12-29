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
		const SETTINGS_KEY = self::PREFIX . 'template_settings';

		/**
		 * BEWPI_Template_Settings constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_notices', array( $this, 'show_settings_notices' ) );
		}

		/**
		 * Get path to templates.
		 *
		 * @return array
		 */
		private function get_templates() {
			$scanned_templates = array();
			$templates         = array();

			if ( file_exists( BEWPI_TEMPLATES_DIR . 'invoices' ) ) {
				$scanned_templates = array_merge( $scanned_templates, scandir( BEWPI_TEMPLATES_DIR . 'invoices/simple/' ) );
			}

			if ( file_exists( BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR ) ) {
				$scanned_templates = array_merge( $scanned_templates, scandir( BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . 'simple/' ) );
			}

			foreach ( $scanned_templates as $i => $template_name ) {
				if ( '..' !== $template_name && '.' !== $template_name ) {
					$templates[] = array(
						'id'    => $i,
						'name'  => ucfirst( $template_name ),
						'value' => $template_name,
					);
				}
			}

			return $templates;
		}

		/**
		 * Settings configuration.
		 *
		 * @return array
		 */
		private function the_settings() {
			$templates = $this->get_templates();

			$settings = array(
				array(
					'id'       => 'bewpi-template-name',
					'name'     => self::PREFIX . 'template_name',
					'title'    => __( 'Template', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'select_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'general',
					'type'     => 'text',
					'desc'     => '',
					'options'  => $templates,
					'default'  => $templates[0]['value'],
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
					'default'  => '#11a7e7',
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
					'default'  => 'F j, Y',
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
					'callback' => array( $this, 'logo_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'header',
					'type'     => 'file',
					'desc'     => __( 'Supported extensions are GIF, JPG/JPEG and PNG.<br/><b>Note:</b> JPG/JPEG are recommended for best performance.', 'woocommerce-pdf-invoices' ),
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-company-address',
					'name'     => self::PREFIX . 'company_address',
					'title'    => __( 'Company address', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'textarea_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'header',
					'type'     => 'text',
					'desc'     => __( 'Displayed in upper-right corner near logo.', 'woocommerce-pdf-invoices' ),
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
					'desc'     => __( 'Displayed below company address.', 'woocommerce-pdf-invoices' ),
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
					'desc'     => __( 'Displayed in big colored bar directly after invoice total.', 'woocommerce-pdf-invoices' ),
					'default'  => __( 'Thank you for your purchase!', 'woocommerce-pdf-invoices' ),
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
					'desc'     => sprintf( __( 'Displayed below customer notes and above footer. Want to attach additional pages to the invoice? Take a look at the <a href="%s">Premium</a> plugin.', 'woocommerce-pdf-invoices' ), 'http://wcpdfinvoices.com' ),
					'default'  => __( 'Items will be shipped within 2 days.', 'woocommerce-pdf-invoices' ),
				),
				array(
					'id'       => 'bewpi-left-footer-column',
					'name'     => self::PREFIX . 'left_footer_column',
					'title'    => __( 'Left footer column.', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'textarea_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'footer',
					'type'     => 'text',
					'desc'     => '',
					'default'  => sprintf( __( '<b>Payment method</b> %s', 'woocommerce-pdf-invoices' ), '[payment_method]' ),
				),
				array(
					'id'       => 'bewpi-right-footer-column',
					'name'     => self::PREFIX . 'right_footer_column',
					'title'    => __( 'Right footer column.', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'textarea_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'footer',
					'type'     => 'text',
					'desc'     => __( 'Leave empty to show page numbering.', 'woocommerce-pdf-invoices' ),
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
							'name'  => __( 'WooCommerce order number', 'woocommerce-pdf-invoices' ),
							'value' => 'woocommerce_order_number',
						),
						array(
							'name'  => __( 'Sequential number', 'woocommerce-pdf-invoices' ),
							'value' => 'sequential_number',
						),
					),
					'default'  => 'sequential_number',
				),
				array(
					'id'       => 'bewpi-reset-counter',
					'name'     => self::PREFIX . 'reset_counter',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
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
					'callback' => array( $this, 'input_callback' ),
					'page'     => self::SETTINGS_KEY,
					'section'  => 'invoice_number',
					'type'     => 'number',
					'desc'     => __( 'Reset the invoice counter and start counting from given invoice number.<br/><b>Note:</b> Only available for Sequential numbering. All PDF invoices will be deleted and need to be manually created again! Value will be editable by selecting checkbox.', 'woocommerce-pdf-invoices' ),
					'default'  => 1,
					'attrs'    => array(
						'disabled',
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
					'default'  => 3,
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
					'desc'     => sprintf( __( 'Allowed placeholders: <code>%1$s</code> <code>%2$s</code> <code>%3$s</code> <code>%4$s</code> <code>%5$s</code> <code>%6$s</code>.<br/><b>Note:</b> <code>%3$s</code> is required and slashes aren\'t supported.', 'woocommerce-pdf-invoices' ), '[prefix]', '[suffix]', '[number]', '[m]', '[Y]', '[y]' ),
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
					'desc'     => __( 'Reset on 1st of january', 'woocommerce-pdf-invoices' ),
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
			$defaults = $this->get_defaults();
			$options  = get_option( self::SETTINGS_KEY );
			$options  = array_merge( $defaults, $options );
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
			echo __( 'The footer will be visible on every page.', 'woocommerce-pdf-invoices' ) // WPCS: XSS OK.
			     . '<br/>'
			     . $this->allowed_tags_text()
			     . '<br/>'
			     . sprintf( __( '<b>Hint</b>: Use <code>%1$s</code> placeholder to display the order payment method or <code>%2$s</code> to display shipping method.', 'woocommerce-pdf-invoices' ), '[payment_method]', '[shipping_method]' );
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
			$output           = array();
			$template_options = get_option( self::SETTINGS_KEY );

			// strip strings.
			foreach ( $input as $key => $value ) {
				if ( isset( $input[ $key ] ) ) {
					// strip all html and php tags and properly handle quoted strings.
					$output[ $key ] = $this->strip_str( stripslashes( $input[ $key ] ) );
				}
			}

			// company logo file upload.
			if ( isset( $input['bewpi_company_logo'] ) ) {
				$output['bewpi_company_logo'] = $input['bewpi_company_logo'];
			}

			if ( isset( $_FILES['bewpi_company_logo'] ) && 0 === $_FILES['bewpi_company_logo']['error'] ) { // Input var okay.
				$file = wp_unslash( $_FILES['bewpi_company_logo'] );
				if ( $file['size'] <= 2000000 ) {
					$override           = array( 'test_form' => false );
					$company_logo       = wp_handle_upload( $file, $override );
					$validate_file_code = validate_file( $company_logo['url'] );
					if ( 0 === $validate_file_code ) {
						$output['bewpi_company_logo'] = $company_logo['url'];
					} else {
						switch ( $validate_file_code ) {
							case 1:
								add_settings_error(
									esc_attr( self::SETTINGS_KEY ),
									'file-invalid-2',
									__( 'File is invalid and contains either \'..\' or \'./\'.', 'woocommerce-pdf-invoices' )
								);
								break;
							case 2:
								add_settings_error(
									esc_attr( self::SETTINGS_KEY ),
									'file-invalid-3',
									__( 'File is invalid and contains \':\' after the first character.', 'woocommerce-pdf-invoices' )
								);
								break;
						}
					}
				} else {
					add_settings_error(
						esc_attr( self::SETTINGS_KEY ),
						'file-invalid-1',
						__( 'File should be less then 2MB.', 'woocommerce-pdf-invoices' )
					);
				}
			} elseif ( isset( $_POST['bewpi_company_logo'] ) && ! empty( $_POST['bewpi_company_logo'] ) ) { // Input var okay.
				$output['bewpi_company_logo'] = $_POST['bewpi_company_logo'];
			}

			// invoice number.
			if ( ! isset( $input['bewpi_next_invoice_number'] ) ) {
				// reset the next invoice number so it's visible in the disabled input field.
				$output['bewpi_next_invoice_number'] = $template_options['bewpi_next_invoice_number'];
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
