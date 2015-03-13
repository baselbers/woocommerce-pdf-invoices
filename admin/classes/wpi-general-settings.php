<?php

class WPI_General_Settings {

    private $settings_key = 'general_settings';

    private $defaults = array(
        'email_type' => 'customer_invoice',
        'new_order' => 0,
        'email_it_in' => 0,
        'email_it_in_account' => ''
    );

    public $settings;

    function __construct() {
        add_action('init', array(&$this, 'load_settings'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    function load_settings() {
        $this->settings = (array) get_option( $this->settings_key ); // Get all settings from database
        $this->settings = array_merge( $this->defaults, $this->settings ); // Merge defaults with settings
        update_option( $this->settings_key, $this->settings );
    }

    function register_settings() {
        register_setting( $this->settings_key, $this->settings_key ); // Register settings
        add_settings_section( 'section_general', 'General Settings', array( &$this, 'section_general_desc' ), $this->settings_key );
        $this->register_fields();
    }

    function section_general_desc() { echo 'General section description goes here.'; }

    function register_fields() {
        add_settings_field(
            'email_type_option',
            'Attach to Email',
            array( &$this, 'email_type_option' ),
            $this->settings_key,
            'section_general',
            array(
                'emails' => array(
                    array(
                        'id' => 'customer_processing_order',
                        'name' => 'Processing order'
                    ),
                    array(
                        'id' => 'customer_completed_order',
                        'name' => 'Completed order'
                    ),
                    array(
                        'id' => 'customer_invoice',
                        'name' => 'Customer invoice'
                    )
                )
            )
        );
        add_settings_field( 'new_order', 'Attach to New order Email', array( &$this, 'new_order_option' ), $this->settings_key, 'section_general' );
        add_settings_field( 'email_it_in', 'Automatically send invoice to Google Drive, Egnyte, Dropbox or OneDrive', array( &$this, 'email_it_in_option' ), $this->settings_key, 'section_general' );
        add_settings_field( 'email_it_in_account', 'Email It In account', array( &$this, 'email_it_in_account_option' ), $this->settings_key, 'section_general' );
    }

    function email_type_option( $args ) {
        ?>
        <select id="email-type-option" name="<?php echo $this->settings_key; ?>[email_type]">
            <!--<option selected hidden>-- Select --</option>-->
            <?php
            foreach( $args['emails'] as $email ) {
                ?>
                <option value="<?php echo $email['id']; ?>"   <?php selected( $this->settings['email_type'], $email['id'] ); ?>><?php echo $email['name']; ?></option>
            <?php
            }
            ?>
        </select>
    <?php
    }

    function new_order_option() {
        ?>
        <input type="checkbox" name="<?php echo $this->settings_key; ?>[new_order]" value="1" <?php checked( $this->settings['new_order'] ); ?>/>
        <div class="notes">For bookkeeping purposes.</div>
        <?php
    }

    function email_it_in_option() {
        ?>
        <input type="checkbox" name="<?php echo $this->settings_key; ?>[email_it_in]" value="1" <?php checked( $this->settings['email_it_in'] ); ?>/>
        <div class="notes">Signup at <a href="https://emailitin.com">emailitin.com</a> and enter your account beyond.</div>
    <?php
    }

    function email_it_in_account_option() {
        ?>
        <input type="text" name="<?php echo $this->settings_key; ?>[email_it_in_account]" value="<?php echo $this->settings['email_it_in_account']; ?>"/>
        <div class="notes">Enter your Email It In account.</div>
    <?php
    }
}