<?php

class WPI_General_Settings extends WPI_Settings{

    private $settings_key = 'general_settings';

    private $defaults = array(
        'email_type' => 'customer_invoice',
        'new_order' => 0,
        'email_it_in' => 0,
        'email_it_in_account' => ''
    );

    public $settings;

    public function __construct() {
        add_action( 'init', array( &$this, 'load_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_settings' ) );

        /**
         * Displays all messages registered to 'template_settings'
         */
        add_action( 'admin_notices', array( &$this, 'show_settings_notices' ) );
    }

    public function load_settings() {
        $this->settings = (array) get_option( $this->settings_key ); // Get all settings from database
        $this->settings = array_merge( $this->defaults, $this->settings ); // Merge defaults with settings
        update_option( $this->settings_key, $this->settings );
    }

    public function register_settings() {
        register_setting( $this->settings_key, $this->settings_key, array( &$this, 'validate' ) );
        add_settings_section( 'section_general', __( 'General Settings', $this->textdomain ), '', $this->settings_key );
        add_settings_field( 'email_type_option', __( 'Attach to Email', $this->textdomain ), array( &$this, 'email_type_option' ), $this->settings_key, 'section_general',
            array(
                array(
                    'id' => 'customer_processing_order',
                    'name' => __( 'Processing order', $this->textdomain )
                ),
                array(
                    'id' => 'customer_completed_order',
                    'name' => __( 'Completed order', $this->textdomain )
                ),
                array(
                    'id' => 'customer_invoice',
                    'name' => __( 'Customer invoice', $this->textdomain )
                )
            )
        );
        add_settings_field( 'new_order', __( 'Attach to New order Email', $this->textdomain ), array( &$this, 'new_order_option' ), $this->settings_key, 'section_general' );
        add_settings_field( 'email_it_in', __( 'Automatically send invoice to Google Drive, Egnyte, Dropbox or OneDrive', $this->textdomain ), array( &$this, 'email_it_in_option' ), $this->settings_key, 'section_general' );
        add_settings_field( 'email_it_in_account', __( 'Email It In account', $this->textdomain ), array( &$this, 'email_it_in_account_option' ), $this->settings_key, 'section_general' );
    }

    //public function section_general_desc() { echo 'General section description goes here.'; }

    public function show_settings_notices() {
        settings_errors( $this->settings_key );
    }

    public function email_type_option( $args ) {
        ?>
        <select id="email-type-option" name="<?php echo $this->settings_key; ?>[email_type]">
            <!--<option selected hidden>-- Select --</option>-->
            <?php
            foreach( $args as $email ) {
                ?>
                <option value="<?php echo $email['id']; ?>"   <?php selected( $this->settings['email_type'], $email['id'] ); ?>><?php echo $email['name']; ?></option>
            <?php
            }
            ?>
        </select>
    <?php
    }

    public function new_order_option() {
        ?>
        <input type="checkbox" name="<?php echo $this->settings_key; ?>[new_order]" value="1" <?php checked( $this->settings['new_order'] ); ?>/>
        <div class="notes"><?php _e( 'For bookkeeping purposes.', $this->textdomain ); ?></div>
        <?php
    }

    public function email_it_in_option() {
        ?>
        <input type="checkbox" name="<?php echo $this->settings_key; ?>[email_it_in]" value="1" <?php checked( $this->settings['email_it_in'] ); ?>/>
        <div class="notes"><?php printf( __( 'Signup at %s and enter your account beyond.', $this->textdomain ), '<a href="https://emailitin.com">emailitin.com</a>' ); ?></div>
    <?php
    }

    public function email_it_in_account_option() {
        ?>
        <input type="text" name="<?php echo $this->settings_key; ?>[email_it_in_account]" value="<?php echo $this->settings['email_it_in_account']; ?>"/>
        <div class="notes">
            <?php printf( __( 'Enter your %s account.', $this->textdomain ), 'Email It In' ); ?>
        </div>
    <?php
    }

    public function validate( $input ) {
        $output = array();

        // Validate email type
        if( $this->is_valid_str( $input['email_type'] ) ) {
            $output['email_type'] = $input['email_type'];
        } else {
            add_settings_error(
                esc_attr( $this->settings_key ),
                'invalid-email-type',
                __( 'Invalid type of Email.', $this->textdomain )
            );
        }

        // Validate new order email
        if( $this->is_valid_int( $input['new_order'] ) ) {
            $output['new_order'] = $input['new_order'];
        } else {
            add_settings_error(
                esc_attr( $this->settings_key ),
                'invalid-new-order-email-value',
                __( 'Please don\'t try to change the values.', $this->textdomain )
            );
        }

        // Validate new order email
        if( $this->validate_checkbox( $input['email_it_in'] ) ) {
            $output['email_it_in'] = $input['email_it_in'];
        } else {
            add_settings_error(
                esc_attr( $this->settings_key ),
                'invalid-email-it-in-value',
                __( 'Please don\'t try to change the values.', $this->textdomain )
            );
        }

        // Validate Email
        if( is_email( sanitize_email( $input['email_it_in_account'] ) ) ) {
            $output['email_it_in_account'] = $input['email_it_in_account'];
        } else {
            add_settings_error(
                esc_attr( $this->settings_key ),
                'invalid-email',
                __( 'Invalid Email address.', $this->textdomain )
            );
        }

        return $output;
    }
}