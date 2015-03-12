<?php

class WPI_Template_Settings {

    private $settings_key = 'template_settings';

    private $defaults = array(
        'template_id' => 1,
        'company_name' => '',
        'company_logo' => '',
        'company_address' => '',
        'company_details' => '',
        'terms' => '',
        'show_discount' => 0,
        'show_subtotal' => 0,
        'show_tax' => 0,
        'show_shipping' => 0,
        'show_customer_notes' => 0,
        'show_sku' => 0,
        'next_invoice_number' => 1,
        'invoice_number_digits' => 3,
        'invoice_prefix' => '',
        'invoice_suffix' => '',
        'invoice_format' => '[prefix]-[number]-[suffix]',
        'reset_invoice_number' => 0,
        'invoice_date_format' => 'F jS Y',
        'last_invoiced_year' => '',
        'invoice_number' => 1,
        'intro_text' => 'Many thanks for your purchase. If you have any questions about your invoice, please feel free to contact us at your conveniance. We will reply as soon as we get your message.'
    );

    public $settings;

    private $templates = array(
        array(
            'id' => 1,
            'name' => 'Micro',
            'filename' => 'invoice-micro.php'
        )
    );

    function __construct() {
        add_action('init', array(&$this, 'load_settings'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    function load_settings() {
        $this->settings = (array) get_option( $this->settings_key );
        $this->settings = array_merge( $this->defaults, $this->settings );

        if( $this->settings['template_id'] != "" ) {
            $this->settings['template_filename'] = $this->get_template_filename( $this->settings['template_id'] );
        }

        update_option( $this->settings_key, $this->settings );
    }

    function register_settings() {
        register_setting( $this->settings_key, $this->settings_key, array(&$this, 'validate') );
        add_settings_section( 'section_template', 'Template Settings', array( &$this, 'section_template_desc' ), $this->settings_key );
        add_settings_field( 'template_id', 'Template', array( &$this, 'template_id_option' ), $this->settings_key, 'section_template', array('templates' => $this->templates));
        add_settings_field( 'company_name', 'Company name', array( &$this, 'company_name_option' ), $this->settings_key, 'section_template');
        add_settings_field( 'company_logo', 'Company logo', array( &$this, 'company_logo_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'intro_text', 'Intro text', array( &$this, 'intro_text_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'company_address', 'Company address', array( &$this, 'company_address_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'company_details', 'Company details', array( &$this, 'company_details_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'terms', 'Terms & conditions, policies etc.', array( &$this, 'terms_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'next_invoice_number', 'Next invoice number', array( &$this, 'next_invoice_number_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'invoice_number_digits', 'Number of digits', array( &$this, 'invoice_number_digits_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'invoice_prefix', 'Invoice number prefix', array( &$this, 'invoice_prefix_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'invoice_suffix', 'Invoice number suffix', array( &$this, 'invoice_suffix_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'invoice_format', 'Invoice number format', array( &$this, 'invoice_format_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'reset_invoice_number', 'Reset on 1st January', array( &$this, 'reset_invoice_number_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'invoice_date_format', 'Invoice date format', array( &$this, 'invoice_date_format_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'show_sku', 'Show SKU', array( &$this, 'show_sku_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'show_discount', 'Show discount', array( &$this, 'show_discount_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'show_subtotal', 'Show subtotal', array( &$this, 'show_subtotal_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'show_tax', 'Show tax', array( &$this, 'show_tax_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'show_shipping', 'Show shipping', array( &$this, 'show_shipping_option' ), $this->settings_key, 'section_template' );
        add_settings_field( 'show_customer_notes', 'Show customer notes', array( &$this, 'show_customer_notes_option' ), $this->settings_key, 'section_template' );
//        add_settings_field( 'preview_invoice', 'Preview invoice', array( &$this, 'preview_invoice_option' ), $this->settings_key, 'section_template' );
    }

    function section_template_desc() { echo 'Template section description goes here.'; }

    function template_id_option( $args ) {
        ?>
        <select id="template-type-option" name="<?php echo $this->settings_key; ?>[template_id]">
            <?php
            foreach ($args['templates'] as $template) {
                ?>
                <option value="<?php echo $template['id']; ?>"   <?php selected( $this->settings['template_id'], $template['id'] ); ?>><?php echo $template['name']; ?></option>
                <?php
            }
            ?>
        </select>
    <?php
    }

    function company_name_option() {
        ?>
        <input  type="text"
                name="<?php echo $this->settings_key; ?>[company_name]"
                value="<?php echo $this->settings['company_name']; ?>" />
        <?php
    }

    function company_logo_option() {
        ?>
        <input id="upload-file" type="file" name="company_logo" accept="image/*" />
        <input type="hidden" id="company-logo-value" name="company_logo" value="<?php echo esc_attr( $this->settings['company_logo'] ); ?>" />
        <?php
        if ($this->settings['company_logo'] != "") {
            ?>
            <div id="company-logo-wrapper">
                <img id="company-logo" src="<?php echo esc_attr( $this->settings['company_logo'] ); ?>" />
                <img id="delete" src="<?php echo WPI_URL . '/assets/img/delete-icon.png'; ?>" onclick="Settings.removeCompanyLogo()" />
            </div>
        <?php
        }
        ?>
    <?php
    }

    function intro_text_option() {
        ?>
        <textarea name="<?php echo $this->settings_key; ?>[intro_text]" rows="5" cols="50"><?php echo $this->settings['intro_text']; ?></textarea>
    <?php
    }

    function company_address_option() {
        ?>
        <textarea name="<?php echo $this->settings_key; ?>[company_address]" rows="5" cols="50"><?php echo $this->settings['company_address']; ?></textarea>
    <?php
    }

    function company_details_option() {
        ?>
        <textarea name="<?php echo $this->settings_key; ?>[company_details]" rows="5" cols="50"><?php echo $this->settings['company_details']; ?></textarea>
        <?php
    }

    function terms_option() {
        ?>
        <textarea name="<?php echo $this->settings_key; ?>[terms]" rows="5" cols="50"><?php echo $this->settings['terms']; ?></textarea>
        <?php
    }

    function show_subtotal_option() {
        ?>
        <input  type="checkbox"
                name="<?php echo $this->settings_key; ?>[show_subtotal]"
                value="1"
            <?php checked( $this->settings['show_subtotal'] ); ?> />
    <?php
    }

    function show_tax_option() {
        ?>
        <input  type="checkbox"
                name="<?php echo $this->settings_key; ?>[show_tax]"
                value="1"
                <?php checked( $this->settings['show_tax'] ); ?> />
        <?php
    }

    function show_discount_option() {
        ?>
        <input  type="checkbox"
                name="<?php echo $this->settings_key; ?>[show_discount]"
                value="1"
            <?php checked( $this->settings['show_discount'] ); ?> />
    <?php
    }

    function show_shipping_option() {
        ?>
        <input  type="checkbox"
                name="<?php echo $this->settings_key; ?>[show_shipping]"
                value="1"
            <?php checked( $this->settings['show_shipping'] ); ?> />
    <?php
    }

    function show_customer_notes_option() {
        ?>
        <input  type="checkbox"
                name="<?php echo $this->settings_key; ?>[show_customer_notes]"
                value="1"
            <?php checked( $this->settings['show_customer_notes'] ); ?> />
        <?php
    }

    function show_sku_option() {
        ?>
        <input  type="checkbox"
                name="<?php echo $this->settings_key; ?>[show_sku]"
                value="1"
            <?php checked( $this->settings['show_sku'] ); ?> />
    <?php
    }

    function next_invoice_number_option() {
        ?>
        <input  type="text"
                name="<?php echo $this->settings_key; ?>[next_invoice_number]"
                value="<?php echo $this->settings['next_invoice_number']; ?>" />
        <?php
    }

    function invoice_number_digits_option() {
        ?>
        <input  type="number"
                name="<?php echo $this->settings_key; ?>[invoice_number_digits]"
                value="<?php echo $this->settings['invoice_number_digits']; ?>"
                min="3"
                max="6"
            />
        <?php
    }

    function invoice_prefix_option() {
        ?>
        <input  type="text"
                name="<?php echo $this->settings_key; ?>[invoice_prefix]"
                value="<?php echo $this->settings['invoice_prefix']; ?>" />
        <?php
    }

    function invoice_suffix_option() {
        ?>
        <input  type="text"
                name="<?php echo $this->settings_key; ?>[invoice_suffix]"
                value="<?php echo $this->settings['invoice_suffix']; ?>" />
        <?php
    }

    function invoice_format_option() {
        ?>
        <input  type="text"
                name="<?php echo $this->settings_key; ?>[invoice_format]"
                value="<?php echo $this->settings['invoice_format']; ?>" />
        <?php
    }

    function reset_invoice_number_option() {
        ?>
        <input  type="checkbox"
                name="<?php echo $this->settings_key; ?>[reset_invoice_number]"
                value="1"
                <?php checked( $this->settings['reset_invoice_number'] ); ?> />
        <?php
    }

    function invoice_date_format_option() {
        ?>
        <input  type="text"
                name="<?php echo $this->settings_key; ?>[invoice_date_format]"
                value="<?php echo $this->settings['invoice_date_format']; ?>" />
        <?php
    }

    function show_notes_option() {
        ?>
        <input  type="checkbox"
                name="<?php echo $this->settings_key; ?>[show_notes]"
                value="<?php echo $this->settings['show_notes']; ?>"
            <?php checked( $this->settings['show_notes'] ); ?> />
        <?php
    }

    function preview_invoice_option() {
        ?>
        <a href="<?php echo admin_url('admin-ajax.php'); ?>?action=wpi_preview_invoice&security=<?php echo wp_create_nonce('wpi_preview_invoice'); ?>" target="_blank">Preview</a>
    <?php
    }

    public function get_template_filename($template_id) {
        foreach ($this->templates as $template) {
            if ($template['id'] == $template_id) {
                return $template['filename'];
            }
        }
    }

    public function validate( $input ) {
        $input['company_logo'] = $this->upload_file();
        return $input;
    }

    private function upload_file() {
        if( $_FILES['company_logo']['error'] == 0 ) {
            $file = $_FILES['company_logo'];
            if ($file['size'] <= 200000) {
                $override = array('test_form' => false);
                $company_logo = wp_handle_upload($file, $override);
                $validate_file_code = validate_file($company_logo['url']);
                if ($validate_file_code == 0) {
                    return $company_logo['url'];
                } else {
                    switch ($validate_file_code) {
                        case 1:
                            $this->add_settings_error('File is invalid and contains either \'..\' or \'./\'.');
                            break;
                        case 2:
                            $this->add_settings_error('File is invalid and contains \':\' after the first character.');
                            break;
                    }
                }
            } else {
                $this->add_settings_error('Please upload image with extension jpg, jpeg or png.');
            }
        } else if( empty( $_POST['company_logo'] ) ) {
            return "";
        } else {
            return $_POST['company_logo'];
        }
    }

    function add_settings_error( $error_message ) {
        add_settings_error(
            'wpi_notices',
            esc_attr( 'settings_updated' ),
            __( $error_message ),
            'error'
        );
    }
}