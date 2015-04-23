<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_Template_Settings' ) ) {

    /**
     * Implements the template settings.
     */
    class BEWPI_Template_Settings extends BEWPI_Settings {

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
            add_action( 'init', array( &$this, 'load_settings' ) );

            /**
             * Register all template settings.
             */
            add_action( 'admin_init', array( &$this, 'create_settings' ) );

            /**
             * Displays all messages registered to 'template_settings'
             */
            add_action( 'admin_notices', array( &$this, 'show_settings_notices' ) );
        }

        /**
         * Load all settings into settings var and merge with defaults.
         */
        public function load_settings() {
			$defaults = $this->get_defaults();
	        $defaults['bewpi_settings_key'] = $this->settings_key;
	        $defaults['bewpi_last_invoice_number'] = 1;
	        $options = (array) get_option( $this->settings_key );
	        $options = array_merge( $defaults, $options );
	        update_option( $this->settings_key, $options );
        }

	    /**
	     * Gets all default settings values from the settings array.
	     * @return array
	     */
	    private function get_defaults() {
		    $defaults = array();
		    foreach ( $this->the_settings() as $setting ) :
			    $defaults[ $setting['name'] ] = $setting['default'];
		    endforeach;
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
		        array( &$this, 'validate_input' )
	        );
	        $this->add_settings_fields();
        }

	    /**
	     * The settings array.
	     * @return array
	     */
	    private function the_settings() {
		    $settings = array(
			    // General section
			    array(
				    'id' => 'bewpi-template-filename',
				    'name' => $this->prefix . 'template_filename',
				    'title' => __( 'Template', $this->textdomain ),
				    'callback' => array( &$this, 'select_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'general',
				    'type' => 'text',
				    'desc' => '',
				    'options' => array(
					    array(
						    'id' => 1,
						    'name' => 'Micro',
						    'value' => 'invoice-micro.php'
					    )
				    ),
				    'default' => 'invoice-micro.php'
			    ),
			    array(
				    'id' => 'bewpi-color-theme',
				    'name' => $this->prefix . 'color_theme',
				    'title' => __( 'Color theme', $this->textdomain ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'general',
				    'type' => 'color',
				    'desc' => '',
				    'default' => '#11B0E7'
			    ),
			    array(
				    'id' => 'bewpi-date-format',
				    'name' => $this->prefix . 'date_format',
				    'title' => __( 'Date format', $this->textdomain ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'general',
				    'type' => 'text',
				    'desc' => sprintf( __( '%sFormat%s of invoice date and order date.', $this->textdomain ),
					    '<a href="http://php.net/manual/en/datetime.formats.date.php">', '</a>' ), // F jS Y or d.m.y or d-m-Y
				    'default' => 'd-m-Y',
				    'attrs' => array(
					    'required'
				    )
			    ),
			    // Header section
		        array(
			        'id' =>  'bewpi-company-name',
			        'name' => $this->prefix . 'company_name',
				    'title' => __( 'Company name', $this->textdomain ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'text',
					'desc' => '',
			        'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-company-logo',
				    'name' => $this->prefix . 'company_logo',
				    'title' => __( 'Company logo', $this->textdomain ),
				    'callback' => array( &$this, 'logo_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'file',
				    'desc' => '',
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-company-address',
				    'name' => $this->prefix . 'company_address',
				    'title' => __( 'Company address', $this->textdomain ),
				    'callback' => array( &$this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'text',
				    'desc' => '',
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-company-details',
				    'name' => $this->prefix . 'company_details',
				    'title' => __( 'Company details', $this->textdomain ),
				    'callback' => array( &$this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'text',
				    'desc' => '',
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-intro-text',
				    'name' => $this->prefix . 'intro_text',
				    'title' => __( 'Intro text', $this->textdomain ),
				    'callback' => array( &$this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'header',
				    'type' => 'text',
				    'desc' => '',
				    'default' => ''
			    ),
			    // Footer section
			    array(
				    'id' => 'bewpi-terms',
				    'name' => $this->prefix . 'terms',
				    'title' => __( 'Terms & conditions, policies etc.', $this->textdomain ),
				    'callback' => array( &$this, 'textarea_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'footer',
				    'type' => 'text',
				    'desc' => '',
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-show-customer-notes',
				    'name' => $this->prefix . 'show_customer_notes',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'footer',
				    'type' => 'checkbox',
				    'desc' => 'Show customer notes',
				    'class' => 'bewpi-customer-notes-option-title',
				    'default' => 1
			    ),
			    // Invoice number section
			    array(
				    'id' => 'bewpi-invoice-number-type',
				    'name' => $this->prefix . 'invoice_number_type',
				    'title' => __( 'Type', $this->textdomain ),
				    'callback' => array( &$this, 'select_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'text',
				    'desc' => '',
				    'options' => array(
					    array(
						    'name' => __( 'WooCommerce order number', $this->textdomain ),
						    'value' => 'woocommerce_order_number'
					    ),
					    array(
						    'name' => __( 'Sequential number', $this->textdomain ),
						    'value' => 'sequential_number'
					    )
				    ),
				    'default' => 'woocommerce_order_number'
			    ),
			    array(
				    'id' => 'bewpi-reset-counter',
				    'name' => $this->prefix . 'reset_counter',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'checkbox',
				    'desc' => 'Reset invoice counter',
				    'class' => 'bewpi-reset-counter-option-title',
				    'default' => 0,
				    'attrs' => array(
					    'onchange="Settings.enableDisableNextInvoiceNumbering(this)"'
				    )
			    ),
			    array(
				    'id' => 'bewpi-next-invoice-number',
				    'name' => $this->prefix . 'next_invoice_number',
				    'title' => __( 'Next', $this->textdomain ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'number',
				    'desc' => sprintf( __( 'Reset the invoice counter and start with next invoice number. %s %sNote:%s Only available with sequential numbering type and you need to check the checkbox to actually reset the value.', $this->textdomain ), '<br/>', '<b>', '</b>' ),
				    'default' => '',
				    'attrs' => array(
					    'disabled'
				    )
			    ),
			    array(
				    'id' => 'bewpi-invoice-number-digits',
				    'name' => $this->prefix . 'invoice_number_digits',
				    'title' => __( 'Digits', $this->textdomain ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'number',
				    'desc' => '',
				    'default' => 3,
				    'attrs' => array(
					    'min="3"',
					    'max="6"',
					    'required'
				    )
			    ),
			    array(
				    'id' => 'bewpi-invoice-number-prefix',
				    'name' => $this->prefix . 'invoice_number_prefix',
				    'title' => __( '[prefix]', $this->textdomain ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'text',
				    'desc' => '',
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-invoice-number-suffix',
				    'name' => $this->prefix . 'invoice_number_suffix',
				    'title' => __( '[suffix]', $this->textdomain ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'text',
				    'desc' => '',
				    'default' => ''
			    ),
			    array(
				    'id' => 'bewpi-invoice-number-format',
				    'name' => $this->prefix . 'invoice_number_format',
				    'title' => __( 'Format', $this->textdomain ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'text',
				    'desc' => sprintf( __( 'Feel free to use the placeholders %s %s %s %s and %s. %s %sNote:%s %s is required.', $this->textdomain ), '<b>[prefix]</b>', '<b>[suffix]</b>', '<b>[number]</b>', '<b>[Y]</b>', '<b>[y]</b>', '<br/>', '<b>', '</b>', '<b>[number]</b>' ),
				    'default' => '[number]-[Y]',
				    'attrs' => array(
			            'required'
		            )
			    ),
			    array(
				    'id' => 'bewpi-reset-counter-yearly',
				    'name' => $this->prefix . 'reset_counter_yearly',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'invoice_number',
				    'type' => 'checkbox',
				    'desc' => 'Reset on 1st of january',
				    'class' => 'bewpi-reset-counter-yearly-option-title',
				    'default' => 1
			    ),
			    // Visible columns section
			    array(
				    'id' => 'bewpi-show-sku',
				    'name' => $this->prefix . 'show_sku',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => 'SKU',
				    'class' => 'bewpi-visible-columns-option-title',
				    'default' => 0
			    ),
			    array(
				    'id' => 'bewpi-show-subtotal',
				    'name' => $this->prefix . 'show_subtotal',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => 'Subtotal',
				    'class' => 'bewpi-visible-columns-option-title',
				    'default' => 1
			    ),
			    array(
				    'id' => 'bewpi-show-tax',
				    'name' => $this->prefix . 'show_tax',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => 'Tax',
				    'class' => 'bewpi-visible-columns-option-title',
				    'default' => 1
			    ),
			    array(
				    'id' => 'bewpi-show-discount',
				    'name' => $this->prefix . 'show_discount',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => 'Discount',
				    'class' => 'bewpi-visible-columns-option-title',
				    'default' => 1
			    ),
			    array(
				    'id' => 'bewpi-show-shipping',
				    'name' => $this->prefix . 'show_shipping',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'visible_columns',
				    'type' => 'checkbox',
				    'desc' => 'Shipping',
				    'class' => 'bewpi-visible-columns-option-title',
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
			    __( 'General Options', $this->textdomain ),
			    array( &$this, 'general_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'invoice_number',
			    __( 'Invoice Number Options', $this->textdomain ),
			    array( &$this, 'invoice_number_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'header',
			    __( 'Header Options', $this->textdomain ),
			    array( &$this, 'header_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'footer',
			    __( 'Footer Options', $this->textdomain ),
			    array( &$this, 'footer_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'visible_columns',
			    __( 'Visible Columns', $this->textdomain ),
			    array( &$this, 'visible_columns_desc_callback' ),
			    $this->settings_key
		    );
	    }

	    public function general_desc_callback() { _e( 'These are the general template options.', $this->textdomain ); }
	    public function invoice_number_desc_callback() { _e( 'These are the invoice number options.', $this->textdomain ); }
	    public function header_desc_callback() { _e( 'The header will be visible on every page. ' . $this->get_allowed_tags_str(), $this->textdomain ); }
	    public function footer_desc_callback() { _e( 'The footer will be visible on every page. ' . $this->get_allowed_tags_str(), $this->textdomain ); }
	    public function visible_columns_desc_callback() { _e( 'Enable or disable the columns.', $this->textdomain ); }

	    /**
	     * Adds all settings fields.
	     */
	    private function add_settings_fields() {
		    $the_settings = $this->the_settings();
		    foreach ( $the_settings as $setting ) :
			    add_settings_field(
				    $setting['name'],
				    $setting['title'],
				    $setting['callback'],
				    $setting['page'],
				    $setting['section'],
				    $setting
			    );
		    endforeach;
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
            foreach ( $the_settings as $setting ) :
                if ( $setting['type'] === 'checkbox' && ! isset( $input[ $setting['name'] ] ) ) :
                    // Checkbox is unchecked
                    $output[ $setting['name'] ] = 0;
                endif;
            endforeach;

            // Strip strings
		    foreach( $input as $key => $value ) :
			    if( isset( $input[$key] ) ) :
				    // Strip all HTML and PHP tags and properly handle quoted strings
				    $output[$key] = $this->strip_str( stripslashes( $input[ $key ] ) );
			    endif;
		    endforeach;

		    // File upload -- Company logo
            if ( isset( $input['bewpi_company_logo'] ) )
                $output['bewpi_company_logo'] = $input['bewpi_company_logo'];

		    if ( isset( $_FILES['bewpi_company_logo'] ) && $_FILES['bewpi_company_logo']['error'] == 0 ) {
			    $file = $_FILES['bewpi_company_logo'];
			    if ( $file['size'] <= 200000 ) {
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
								    __( 'File is invalid and contains either \'..\' or \'./\'.', $this->textdomain )
							    );
							    break;
						    case 2:
							    add_settings_error(
								    esc_attr( $this->settings_key ),
								    'file-invalid-3',
								    __( 'File is invalid and contains \':\' after the first character.', $this->textdomain )
							    );
							    break;
					    }
				    }
			    } else {
				    add_settings_error(
					    esc_attr( $this->settings_key ),
					    'file-invalid-1',
					    __( 'File should be less then 2MB.', $this->textdomain )
				    );
			    }
		    } else if ( isset( $_POST['bewpi_company_logo'] ) && !empty( $_POST['bewpi_company_logo'] ) ) {
			    $output['bewpi_company_logo'] = $_POST['bewpi_company_logo'];
		    }

		    // Invoice number
		    if ( !isset( $input['bewpi_next_invoice_number'] ) ) {
			    // Reset the next invoice number so it's visible in the disabled input field.
			    $output['bewpi_next_invoice_number'] = $template_options['bewpi_next_invoice_number'];
		    }

            // We don't want to loose the invoice counter value
            if ( ! empty( $template_options['bewpi_last_invoice_number'] ) ) {
                $output['bewpi_last_invoice_number'] = $template_options['bewpi_last_invoice_number'];
            }

		    // Return the array processing any additional functions filtered by this action
		    return apply_filters( 'validate_input', $output, $input );
	    }
    }
}