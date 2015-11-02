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
				    'title' => __( 'Attach to Email', $this->textdomain ),
				    'callback' => array( &$this, 'select_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'email',
				    'type' => 'text',
				    'desc' => '',
				    'options' => array(
					    array(
						    'name' => __( 'Processing order', $this->textdomain ),
						    'value' => 'customer_processing_order'
					    ),
					    array(
						    'name' => __( 'Completed order', $this->textdomain ),
						    'value' => 'customer_completed_order'
					    ),
					    array(
						    'name' => __( 'Customer invoice', $this->textdomain ),
						    'value' => 'customer_invoice'
					    ),
					    array(
						    'name' => __( 'Do not attach', $this->textdomain ),
						    'value' => ''
					    )
				    ),
				    'default' => 'customer_invoice'
			    ),
			    array(
				    'id' => 'bewpi-new-order',
				    'name' => $this->prefix . 'new_order',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'email',
				    'type' => 'checkbox',
				    'desc' => __( 'Attach to New order Email', $this->textdomain ),
				    'class' => 'bewpi-checkbox-option-title',
				    'default' => 0
			    ),
			    array(
				    'id' => 'bewpi-email-it-in',
				    'name' => $this->prefix . 'email_it_in',
				    'title' => '',
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'cloud_storage',
				    'type' => 'checkbox',
					'desc' => __( 'Enable Email It In', $this->textdomain ),
				    'class' => 'bewpi-checkbox-option-title',
		            'default' => 0
			    ),
			    // Header section
		        array(
			        'id' =>  'bewpi-email-it-in-account',
			        'name' => $this->prefix . 'email_it_in_account',
				    'title' => __( 'Email It In account', $this->textdomain ),
				    'callback' => array( &$this, 'input_callback' ),
				    'page' => $this->settings_key,
				    'section' => 'cloud_storage',
				    'type' => 'text',
					'desc' => sprintf( __( 'Get your account from your Email It In %suser account%s.', $this->textdomain ), '<a href="https://www.emailitin.com/user_account">', '</a>' ),
			        'default' => ''
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
			    __( 'Email Options', $this->textdomain ),
			    array( &$this, 'email_desc_callback' ),
			    $this->settings_key
		    );
		    add_settings_section(
			    'cloud_storage',
			    __( 'Cloud Storage Options', $this->textdomain ),
			    array( &$this, 'cloud_storage_desc_callback' ),
			    $this->settings_key
		    );
	    }

	    public function email_desc_callback() { }
	    public function cloud_storage_desc_callback() { printf( __( 'Signup at %s to send invoices to your Dropbox, OneDrive, Google Drive or Egnyte and enter your account below.', $this->textdomain ), '<a href="https://emailitin.com">Email It In</a>' ); }

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
		    $output = array();
		    foreach ( $input as $key => $value ) :
			    if ( isset( $input[$key] ) ) :
				    // Strip all HTML and PHP tags and properly handle quoted strings
				    $output[$key] = stripslashes( $input[ $key ] );
			    endif;
		    endforeach;

		    // Sanitize Email
		    if ( isset( $input['email_it_in_account'] ) ) :
			    $output['email_it_in_account'] = sanitize_email( $input['email_it_in_account'] );
		    endif;

		    return apply_filters( 'validate_input', $output, $input );
	    }
    }
}