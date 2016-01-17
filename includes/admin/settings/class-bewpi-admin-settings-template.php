<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_Template_Settings' ) ) {

    /**
     * Implements the template settings.
     */
    class BEWPI_Template_Settings extends BEWPI_Abstract_Setting {

        /**
         * Constant template settings key
         * @var string
         */
        private $settings_key = 'bewpi_template_settings';

        /**
         * Initializes the template settings.
         */
        public function __construct() {
            /**
             * Loads all the template settings.
             */
            add_action( 'admin_init', array( $this, 'load_settings' ) );

            /**
             * Register all template settings.
             */
            add_action( 'admin_init', array( $this, 'create_settings' ) );

            /**
             * Displays all messages registered to 'template_settings'
             */
            add_action( 'admin_notices', array( $this, 'show_settings_notices' ) );
        }

        /**
         * Load all settings into settings var and merge with defaults.
         */
        public function load_settings() {
			$defaults = $this->get_defaults();
	        $options = (array) get_option( $this->settings_key );
	        $options = array_merge( $defaults, $options );

	        //update_option( $this->settings_key, serialize( $options ) ); todo
	        update_option( $this->settings_key, $options );
        }

	    /**
	     * Gets all default settings values from the settings array.
	     * @return array
	     */
	    private function get_defaults() {
		    $defaults = wp_list_pluck( $this->the_settings(), 'default', 'name' );
		    return $defaults;
	    }

        /**
         * Register all settings fields.
         */
        public function create_settings() {
	        $this->add_settings_sections();
	        register_setting(
		        $this->settings_key,
		        $this->settings_key,
		        array( $this, 'validate_input' )
	        );
	        $this->add_settings_fields();
        }

	    /**
	     * The settings array.
	     * @return array
	     */
	    private function the_settings() {
		    $templates = $this->get_templates();

		    $settings = array(
			    // General section
			    array(
				    'id' => 'bewpi-template-name',
				    'name' => $this->prefix . 'template_name',
				    'title' => __( 'Template', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'select_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'general',
				    'type' => 'text',
				    'desc' => '',
				    'options' => $templates,
				    'default' => $templates[0]['value']
			    ),
			    array(
				    'id' => 'bewpi-color-theme',
				    'name' => $this->prefix . 'color_theme',
				    'title' => __( 'Color theme', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'general',
				    'type' => 'color',
				    'desc' => '',
				    'default' => '#000000'
			    ),
			    array(
				    'id' => 'bewpi-date-format',
				    'name' => $this->prefix . 'date_format',
				    'title' => __( 'Date format', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'general',
				    'type' => 'text',
				    'desc' => sprintf( __( '%sFormat%s of invoice date and order date.', 'woocommerce-pdf-invoices' ),
					    '<a href="http://php.net/manual/en/datetime.formats.date.php">', '</a>' ), // F jS Y or d.m.y or d-m-Y
				    'default' => 'F j, Y',
				    'attrs' => array(
					    'required'
				    )
			    ),
                array(
                    'id' => 'bewpi-display-prices-incl-tax',
                    'name' => $this->prefix . 'display_prices_incl_tax',
                    'title' => '',
                    'callback' => array( $this, 'input_callback' ),
                    'page' => $this->settings_key,
                    'section' => 'general',
                    'type' => 'checkbox',
                    'desc' => __( 'Display prices including tax', 'woocommerce-pdf-invoices' )
                              . "<br/><div class='bewpi-notes'>" . __( 'Line item totals will be including tax. <br/><b>Note</b>: Subtotal will still be excluding tax, so disable it within the visible columns section.', 'woocommerce-pdf-invoices' ) . "</div>",
                    'class' => 'bewpi-checkbox-option-title',
                    'default' => 0
                ),
			    array(
				    'id' => 'bewpi-shipping-taxable',
				    'name' => $this->prefix . 'shipping_taxable',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'general',
				    'type' => 'checkbox',
				    'desc' => __( 'Shipping taxable', 'woocommerce-pdf-invoices' )
				              . "<br/><div class='bewpi-notes'>" . __( 'Enable to display subtotal including shipping.', 'woocommerce-pdf-invoices' ) . "</div>",
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 0
			    ),
			    array(
				    'id' => 'bewpi-show-payment-status',
				    'name' => $this->prefix . 'show_payment_status',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'general',
				    'type' => 'checkbox',
				    'desc' => __( 'Mark invoice as paid', 'woocommerce-pdf-invoices' )
				              . "<br/><div class='bewpi-notes'>" . __( 'Invoice will be watermarked when order has been paid.', 'woocommerce-pdf-invoices' ) . "</div>",
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 0
			    ),
			    // Header section
		        array(
			        'id' =>  'bewpi-company-name',
			        'name' => $this->prefix . 'company_name',
				    'title' => __( 'Company name', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'text',
					'desc' => '',
			        'default' => get_bloginfo()
			    ),
			    array(
				    'id' => 'bewpi-company-logo',
				    'name' => $this->prefix . 'company_logo',
				    'title' => __( 'Company logo', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'logo_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'file',
			        'desc' => '',
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-company-address',
				    'name' => $this->prefix . 'company_address',
				    'title' => __( 'Company address', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'text',
				    'desc' => __( 'Displayed in upper-right corner near logo.', 'woocommerce-pdf-invoices' ),
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-company-details',
				    'name' => $this->prefix . 'company_details',
				    'title' => __( 'Company details', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'text',
				    'desc' => __( 'Displayed below company address.', 'woocommerce-pdf-invoices' ),
				    'default' => ''
			    ),
			    // Body
			    array(
				    'id' => 'bewpi-intro-text',
				    'name' => $this->prefix . 'intro_text',
				    'title' => __( 'Thank you text', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'text',
				    'desc' => __( 'Displayed in big colored bar directly after invoice total.', 'woocommerce-pdf-invoices' ),
				    'default' => __( 'Thank you for your purchase!', 'woocommerce-pdf-invoices' )
			    ),
			    array(
				    'id' => 'bewpi-show-customer-notes',
				    'name' => $this->prefix . 'show_customer_notes',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'body',
				    'type' => 'checkbox',
				    'desc' => __( 'Show customer notes', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 1
			    ),
			    array(
				    'id' => 'bewpi-terms',
				    'name' => $this->prefix . 'terms',
				    'title' => __( 'Terms & conditions, policies etc.', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'body',
				    'type' => 'text',
				    'desc' => sprintf( __( 'Displayed below customer notes and above footer. Want to attach additional pages to the invoice? Take a look at the <a href="%s">Premium</a> plugin.', 'woocommerce-pdf-invoices' ), 'http://wcpdfinvoices.com' ),
				    'default' => __( 'Items will be shipped within 2 days.', 'woocommerce-pdf-invoices' )
			    ),
			    // Footer
			    array(
				    'id' => 'bewpi-left-footer-column',
				    'name' => $this->prefix . 'left_footer_column',
				    'title' => __( 'Left footer column.', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'footer',
				    'type' => 'text',
				    'desc' => '',
				    'default' => sprintf( __( '<b>Payment method</b> %s', 'woocommerce-pdf-invoices' ), '[payment_method]' )
			    ),
			    array(
				    'id' => 'bewpi-right-footer-column',
				    'name' => $this->prefix . 'right_footer_column',
				    'title' => __( 'Right footer column.', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'footer',
				    'type' => 'text',
				    'desc' => __( 'Leave empty to show page numbering.', 'woocommerce-pdf-invoices' ),
				    'default' => ''
			    ),
			    // Invoice number section
			    array(
				    'id' => 'bewpi-invoice-number-type',
				    'name' => $this->prefix . 'invoice_number_type',
				    'title' => __( 'Type', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'select_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'text',
				    'desc' => '',
				    'options' => array(
					    array(
						    'name' => __( 'WooCommerce order number', 'woocommerce-pdf-invoices' ),
						    'value' => 'woocommerce_order_number'
					    ),
					    array(
						    'name' => __( 'Sequential number', 'woocommerce-pdf-invoices' ),
						    'value' => 'sequential_number'
					    )
				    ),
				    'default' => 'sequential_number'
			    ),
			    array(
				    'id' => 'bewpi-reset-counter',
				    'name' => $this->prefix . 'reset_counter',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'checkbox',
				    'desc' => __( 'Reset invoice counter', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 0,
				    'attrs' => array(
					    'onchange="BEWPI.Settings.enableDisableNextInvoiceNumbering(this)"'
				    )
			    ),
			    array(
				    'id' => 'bewpi-next-invoice-number',
				    'name' => $this->prefix . 'next_invoice_number',
				    'title' => __( 'Next', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'number',
				    'desc' => __( 'Reset the invoice counter and start counting from given invoice number.<br/><b>Note:</b> Only available for Sequential numbering and value will be editable by selecting checkbox. Next number needs to be lower then highest existing invoice number or delete invoices first.', 'woocommerce-pdf-invoices' ),
				    'default' => 1,
				    'attrs' => array(
					    'disabled',
					    'min="1"'
				    )
			    ),
			    array(
				    'id' => 'bewpi-invoice-number-digits',
				    'name' => $this->prefix . 'invoice_number_digits',
				    'title' => __( 'Digits', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'number',
				    'desc' => '',
				    'default' => 3,
				    'attrs' => array(
					    'min="3"',
					    'max="20"',
					    'required'
				    )
			    ),
			    array(
				    'id' => 'bewpi-invoice-number-prefix',
				    'name' => $this->prefix . 'invoice_number_prefix',
				    'title' => __( '[prefix]', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'text',
				    'desc' => '',
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-invoice-number-suffix',
				    'name' => $this->prefix . 'invoice_number_suffix',
				    'title' => __( '[suffix]', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'text',
				    'desc' => '',
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-invoice-number-format',
				    'name' => $this->prefix . 'invoice_number_format',
				    'title' => __( 'Format', 'woocommerce-pdf-invoices' ),
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'text',
					'desc' => sprintf( __( 'Allowed placeholders: %s.<br/><strong>Note:</strong> %s is required and slashes aren\'t supported.', 'woocommerce-pdf-invoices' ), '<code>[prefix]</code> <code>[suffix]</code> <code>[number]</code> <code>[m]</code> <code>[Y]</code> <code>[y]</code>', '<code>[number]</code>' ),
				    'default' => '[number]-[Y]',
				    'attrs' => array(
			            'required'
		            )
			    ),
			    array(
				    'id' => 'bewpi-reset-counter-yearly',
				    'name' => $this->prefix . 'reset_counter_yearly',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'checkbox',
				    'desc' => sprintf( __( 'Reset on %s', 'woocommerce-pdf-invoices' ), date_i18n( 'F n', strtotime( '2015/1/1' )) ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 1
			    ),
			    // Visible columns section
			    array(
				    'id' => 'bewpi-show-sku',
				    'name' => $this->prefix . 'show_sku',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => __( 'SKU', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 0
			    ),
			    array(
				    'id' => 'bewpi-show-subtotal',
				    'name' => $this->prefix . 'show_subtotal',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => __( 'Subtotal', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 1
			    ),
			    array(
				    'id' => 'bewpi-show-tax',
				    'name' => $this->prefix . 'show_tax',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => __( 'Tax (item)', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 0
			    ),
			    array(
				    'id' => 'bewpi-show-tax-row',
				    'name' => $this->prefix . 'show_tax_total',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => __( 'Tax (total)', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 1
			    ),
			    array(
				    'id' => 'bewpi-show-discount',
				    'name' => $this->prefix . 'show_discount',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => __( 'Discount', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 1
			    ),
			    array(
				    'id' => 'bewpi-show-shipping',
				    'name' => $this->prefix . 'show_shipping',
				    'title' => '',
				    'callback' => array( $this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => __( 'Shipping', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 1
			    )
		    );
		    return $settings;
	    }

	    /**
	     * Adds all the different settings sections.
	     */
	    private function add_settings_sections() {
		    add_settings_section(
			    'general',
			    __( 'General Options', 'woocommerce-pdf-invoices' ),
			    array( $this, 'general_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'invoice_number',
			    __( 'Invoice Number Options', 'woocommerce-pdf-invoices' ),
			    array( $this, 'invoice_number_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'header',
			    __( 'Header Options', 'woocommerce-pdf-invoices' ),
			    array( $this, 'header_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'body',
			    __( 'Body Options', 'woocommerce-pdf-invoices' ),
			    array( $this, 'body_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'footer',
			    __( 'Footer Options', 'woocommerce-pdf-invoices' ),
			    array( $this, 'footer_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'visible_columns',
			    __( 'Visible Columns', 'woocommerce-pdf-invoices' ),
			    array( $this, 'visible_columns_desc_callback' ),
			    $this->settings_key
		    );
	    }

	    public function general_desc_callback() {
		    echo __( 'These are the general template options.', 'woocommerce-pdf-invoices' );
	    }

	    public function invoice_number_desc_callback() {
		    echo __( 'These are the invoice number options.', 'woocommerce-pdf-invoices' );
	    }

	    public function header_desc_callback() {
		    echo __( 'The header will be visible on every page.', 'woocommerce-pdf-invoices' );
		    echo $this->get_allowed_tags_str();
	    }

	    public function body_desc_callback() { }

	    public function footer_desc_callback() {
		    echo __( 'The footer will be visible on every page.', 'woocommerce-pdf-invoices' ) . '<br/>' . $this->get_allowed_tags_str() . '<br/>' . __( '<b>Hint</b>: Use <code>[payment_method]</code> placeholder to display the order payment method.', 'woocommerce-pdf-invoices' );
	    }

	    public function visible_columns_desc_callback() {
		    echo __( 'Enable or disable the columns.', 'woocommerce-pdf-invoices' );
	    }

	    /**
	     * Adds all settings fields.
	     */
	    private function add_settings_fields() {
		    $the_settings = $this->the_settings();
		    foreach ( $the_settings as $setting ) {
			    add_settings_field(
				    $setting['name'],
				    $setting['title'],
				    $setting['callback'],
				    $setting['page'],
				    $setting['section'],
				    $setting
			    );
		    }
	    }

	    /**
	     * Show all settings notices.
	     */
	    public function show_settings_notices() {
		    settings_errors( $this->settings_key );
	    }

	    /**
	     * @param $input
	     * Validate all settings
	     * @return mixed|void
	     */
	    public function validate_input( $input ) {
		    $output             = array();
            $template_options   = get_option( $this->settings_key );
            $the_settings       = $this->the_settings();

            // Uncheck checkboxes
            foreach ( $the_settings as $setting ) {
	            if ( $setting['type'] === 'checkbox' && ! isset( $input[ $setting['name'] ] ) ) {
		            // Checkbox is unchecked
		            $output[ $setting['name'] ] = 0;
	            }
            }

            // Strip strings
		    foreach( $input as $key => $value ) {
			    if ( isset( $input[ $key ] ) ) {
				    // Strip all HTML and PHP tags and properly handle quoted strings
				    $output[ $key ] = $this->strip_str( stripslashes( $input[ $key ] ) );
			    }
		    }

		    // File upload -- Company logo
            if ( isset( $input['bewpi_company_logo'] ) ) {
	            $output['bewpi_company_logo'] = $input['bewpi_company_logo'];
            }

		    if ( isset( $_FILES['bewpi_company_logo'] ) && $_FILES['bewpi_company_logo']['error'] == 0 ) {
			    $file = $_FILES['bewpi_company_logo'];
			    if ( $file['size'] <= 2000000 ) {
				    $override = array( 'test_form' => false );
				    $company_logo = wp_handle_upload( $file, $override );
				    $validate_file_code = validate_file( $company_logo['url'] );
				    if ( $validate_file_code === 0 ) {
					    $output['bewpi_company_logo'] = $company_logo['url'];
				    } else {
					    switch ( $validate_file_code ) {
						    case 1:
							    add_settings_error(
								    esc_attr( $this->settings_key ),
								    'file-invalid-2',
								    __( 'File is invalid and contains either \'..\' or \'./\'.', 'woocommerce-pdf-invoices' )
							    );
							    break;
						    case 2:
							    add_settings_error(
								    esc_attr( $this->settings_key ),
								    'file-invalid-3',
								    __( 'File is invalid and contains \':\' after the first character.', 'woocommerce-pdf-invoices' )
							    );
							    break;
					    }
				    }
			    } else {
				    add_settings_error(
					    esc_attr( $this->settings_key ),
					    'file-invalid-1',
					    __( 'File should be less then 2MB.', 'woocommerce-pdf-invoices' )
				    );
			    }
		    } else if ( isset( $_POST['bewpi_company_logo'] ) && !empty( $_POST['bewpi_company_logo'] ) ) {
			    $output['bewpi_company_logo'] = $_POST['bewpi_company_logo'];
		    }

		    // Invoice number
		    if ( ! isset( $input['bewpi_next_invoice_number'] ) ) {
			    // Reset the next invoice number so it's visible in the disabled input field.
			    $output['bewpi_next_invoice_number'] = $template_options['bewpi_next_invoice_number'];
		    }

		    // Return the array processing any additional functions filtered by this action
		    return apply_filters( 'validate_input', $output, $input );
	    }

	    private function get_templates() {
	    	$scanned_templates = array();
		    $templates = array();

		    if ( file_exists( BEWPI_TEMPLATES_INVOICES_DIR ) ) {
			    $scanned_templates = array_merge( $scanned_templates, scandir( BEWPI_TEMPLATES_INVOICES_DIR . 'simple/' ) );
		    }

		    if ( file_exists( BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR ) ) {
			    $scanned_templates = array_merge( $scanned_templates, scandir( BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . 'simple/' ) );
		    }

		    foreach( $scanned_templates as $i => $template_name ) {
			    if( $template_name !== '..' && $template_name !== '.' ) {
				    $templates[] = array(
					    'id'    => $i,
					    'name'  => ucfirst( $template_name ),
					    'value' => $template_name
				    );
			    }
		    }

		    return $templates;
	    }
    }
}