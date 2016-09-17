<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_General_Settings' ) ) {

    /**
     * Implements the template settings.
     */
    class BEWPI_General_Settings extends BEWPI_Abstract_Setting {

        /**
         * Constant template settings key
         * @var string
         */
        private $settings_key = 'bewpi_general_settings';

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
	        $options = (array) get_option( $this->settings_key );
	        $options = array_merge( $defaults, $options );

	        //update_option( $this->settings_key, serialize( $options ) ); todo
	        update_option( $this->settings_key, $options );
        }

	    /**
	     * Get all default values from the settings array.
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
	     * Settings array
	     * @return array
	     */
	    private function the_settings() {
		    $settings = array(
			    // General section
			    array(
				    'id' => 'bewpi-email-type',
				    'name' => $this->prefix . 'email_type',
				    'title' => __( 'Attach to Email', 'woocommerce-pdf-invoices' ),
				    'callback' => array( &$this, 'select_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'email',
				    'type' => 'text',
				    'desc' => '',
				    'options' => array(
					    array(
						    'name' => __( 'Order on-hold', 'woocommerce-pdf-invoices' ),
						    'value' => 'customer_on_hold_order'
					    ),
					    array(
						    'name' => __( 'Processing order', 'woocommerce-pdf-invoices' ),
						    'value' => 'customer_processing_order'
					    ),
					    array(
						    'name' => __( 'Completed order', 'woocommerce-pdf-invoices' ),
						    'value' => 'customer_completed_order'
					    ),
					    array(
						    'name' => __( 'Customer invoice', 'woocommerce-pdf-invoices' ),
						    'value' => 'customer_invoice'
					    ),
					    array(
						    'name' => __( 'Do not attach', 'woocommerce-pdf-invoices' ),
						    'value' => ''
					    )
				    ),
				    'default' => 'customer_completed_order'
			    ),
			    array(
				    'id' => 'bewpi-new-order',
				    'name' => $this->prefix . 'new_order',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'email',
				    'type' => 'checkbox',
				    'desc' => __( 'Attach to New order Email', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 0
			    ),
			    array(
				    'id' => 'bewpi-view-pdf',
				    'name' => $this->prefix . 'view_pdf',
				    'title' => __( 'View PDF', 'woocommerce-pdf-invoices' ),
				    'callback' => array( &$this, 'select_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'download',
				    'type' => 'text',
				    'desc' => '',
				    'options' => array(
					    array(
						    'name' => __( 'Download', 'woocommerce-pdf-invoices' ),
						    'value' => 'download'
					    ),
					    array(
						    'name' => __( 'Open in new browser tab/window', 'woocommerce-pdf-invoices' ),
						    'value' => 'browser'
					    )
				    ),
				    'default' => 'download'
			    ),
			    array(
				    'id' => 'bewpi-download-invoice-account',
				    'name' => $this->prefix . 'download_invoice_account',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'download',
				    'type' => 'checkbox',
				    'desc' => __( 'Enable download from account', 'woocommerce-pdf-invoices' )
			                    . __( '<br/><div class="bewpi-notes">Let customers download invoice from account page.</div>', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 1
			    ),
			    array(
				    'id' => 'bewpi-email-it-in',
				    'name' => $this->prefix . 'email_it_in',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'cloud_storage',
				    'type' => 'checkbox',
					'desc' => __( 'Enable Email It In', 'woocommerce-pdf-invoices' ),
				    'class' => 'bewpi-checkbox-option-title',
		            'default' => 0
			    ),
			    // Header section
		        array(
			        'id' =>  'bewpi-email-it-in-account',
			        'name' => $this->prefix . 'email_it_in_account',
				    'title' => __( 'Email It In account', 'woocommerce-pdf-invoices' ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'cloud_storage',
				    'type' => 'text',
					'desc' => sprintf( __( 'Get your account from your Email It In %suser account%s.', 'woocommerce-pdf-invoices' ), '<a href="https://www.emailitin.com/user_account">', '</a>' ),
			        'default' => ''
		        ),
			    array(
				    'id' =>  'bewpi-mpdf-debug',
				    'name' => $this->prefix . 'mpdf_debug',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'debug',
				    'type' => 'checkbox',
				    'desc' => __( 'Enable mPDF debugging' )
				                  . '<br/><div class="bewpi-notes">' . __( 'Enable mPDF debugging if you aren\'t able to create an invoice.', 'woocommerce-pdf-invoices' ) . '</div>',
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 0
			    )
		    );

		    return apply_filters( 'bewpi_general_settings', $settings );
	    }

	    /**
	     * Adds all the different settings sections
	     */
	    private function add_settings_sections() {
		    add_settings_section(
			    'email',
			    __( 'Email Options', 'woocommerce-pdf-invoices' ),
			    array( &$this, 'email_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'download',
			    __( 'Download Options', 'woocommerce-pdf-invoices' ),
			    array( &$this, 'download_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'cloud_storage',
			    __( 'Cloud Storage Options', 'woocommerce-pdf-invoices' ),
			    array( &$this, 'cloud_storage_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'debug',
			    __( 'Debug Options', 'woocommerce-pdf-invoices' ),
			    array( &$this, 'debug_desc_callback' ),
			    $this->settings_key
		    );
	    }

	    public function email_desc_callback() { }
	    public function download_desc_callback() {}
	    public function cloud_storage_desc_callback() { printf( __( 'Signup at %s to send invoices to your Dropbox, OneDrive, Google Drive or Egnyte and enter your account below.', 'woocommerce-pdf-invoices' ), '<a href="https://emailitin.com">Email It In</a>' ); }
	    public function debug_desc_callback() {}

	    /**
	     * Adds settings fields
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
	     * Validates all settings
	     * @param $input
	     * @return mixed|void
	     */
	    public function validate_input( $input ) {
		    $output             = array();
		    $the_settings       = $this->the_settings();

		    foreach ( $input as $key => $value ) {
			    if ( isset( $input[$key] ) )
				    $output[$key] = stripslashes( $input[ $key ] ); // Strip all HTML and PHP tags and properly handle quoted strings
		    }

		    // Uncheck checkboxes
		    foreach ( $the_settings as $setting ) {
			    if ( $setting[ 'type' ] === 'checkbox' && ! isset( $input[ $setting[ 'name' ] ] ) )
				    $output[ $setting['name'] ] = 0;
		    }

		    // Sanitize Email
		    if ( isset( $input['email_it_in_account'] ) )
			    $output['email_it_in_account'] = sanitize_email( $input['email_it_in_account'] );

		    return apply_filters( 'validate_input', $output, $input );
	    }
    }
}