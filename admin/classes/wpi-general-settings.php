<?php

class WPI_General_Settings extends WPI_Settings {
    protected $settings_key = 'general_settings';
    public $settings;
    private $defaults = array(
        'email_type' => '3',
        'new_order' => false
    );

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
                        'id' => 1,
                        'name' => 'Processing order'
                    ),
                    array(
                        'id' => 2,
                        'name' => 'Completed order'
                    ),
                    array(
                        'id' => 3,
                        'name' => 'Customer invoice'
                    )
                )
            )
        );
        add_settings_field( 'new_order_option', 'Attach to New order Email', array( &$this, 'new_order_option' ), $this->settings_key, 'section_general' );
    }

    function email_type_option( $args ) {
        ?>
        <select id="email-type-option" name="<?php echo $this->settings_key; ?>[email_type]">
            <!--<option selected hidden>-- Select --</option>-->
            <?php
            foreach ($args['emails'] as $email) {
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
        <?php
    }
}