<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPI_Template_Settings' ) ) {

    /**
     * Implements the template settings.
     */
    class WPI_Template_Settings extends WPI_Settings {

        /**
         * Constant template settings key
         * @var string
         */
        private $settings_key = 'template_settings';

        /**
         * Default template settings.
         * @var array
         */
        private $defaults = array(
            'template_id' => 1,
            'color_theme' => '#11B0E7',
            'company_name' => '',
            'company_logo' => '',
            'intro_text' => '',
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
            'invoice_number' => 1
        );

        /**
         * All the template settings.
         * @var array
         */
        public $settings = array();

        /**
         * All the different templates.
         * @var array
         */
        private $templates = array(
            array(
                'id' => 1,
                'name' => 'Micro',
                'filename' => 'invoice-micro.php'
            )
        );

        /**
         * Initializes the template settings.
         */
        public function __construct() {

            /**
             * Loads all the template settings.
             */
            add_action('init', array(&$this, 'load_settings'));

            /**
             * Register all template settings.
             */
            add_action('admin_init', array($this, 'register_settings'));

            /**
             * Displays all messages registered to 'template_settings'
             */
            add_action('admin_notices', array(&$this, 'show_settings_notices'));
        }

        /**
         * Load all settings into settings var and merge with defaults.
         */
        public function load_settings() {
            $this->settings = (array)get_option($this->settings_key);
            $this->settings = array_merge($this->defaults, $this->settings);

            if ($this->settings['template_id'] != "") {
                $this->settings['template_filename'] = $this->get_template($this->settings['template_id'])['filename'];
            }

            update_option($this->settings_key, $this->settings);
        }

        /**
         * Register all settings fields.
         */
        public function register_settings()
        {
            register_setting($this->settings_key, $this->settings_key, array(&$this, 'validate'));
            add_settings_section('section_template', __('Template Settings', $this->textdomain), '', $this->settings_key);
            add_settings_field('template_id', __('Template', $this->textdomain), array(&$this, 'template_id_option'), $this->settings_key, 'section_template', $this->templates);
            add_settings_field('color_theme', __('Color theme', $this->textdomain), array(&$this, 'color_theme_option'), $this->settings_key, 'section_template');
            add_settings_field('company_name', __('Company name', $this->textdomain), array(&$this, 'company_name_option'), $this->settings_key, 'section_template');
            add_settings_field('company_logo', __('Company logo', $this->textdomain), array(&$this, 'company_logo_option'), $this->settings_key, 'section_template');
            add_settings_field('intro_text', __('Intro text', $this->textdomain), array(&$this, 'intro_text_option'), $this->settings_key, 'section_template');
            add_settings_field('company_address', __('Company address', $this->textdomain), array(&$this, 'company_address_option'), $this->settings_key, 'section_template');
            add_settings_field('company_details', __('Company details', $this->textdomain), array(&$this, 'company_details_option'), $this->settings_key, 'section_template');
            add_settings_field('terms', __('Terms & conditions, policies etc.', $this->textdomain), array(&$this, 'terms_option'), $this->settings_key, 'section_template');
            add_settings_field('next_invoice_number', __('Next invoice number', $this->textdomain), array(&$this, 'next_invoice_number_option'), $this->settings_key, 'section_template');
            add_settings_field('invoice_number_digits', __('Number of digits', $this->textdomain), array(&$this, 'invoice_number_digits_option'), $this->settings_key, 'section_template');
            add_settings_field('invoice_prefix', __('Invoice number prefix', $this->textdomain), array(&$this, 'invoice_prefix_option'), $this->settings_key, 'section_template');
            add_settings_field('invoice_suffix', __('Invoice number suffix', $this->textdomain), array(&$this, 'invoice_suffix_option'), $this->settings_key, 'section_template');
            add_settings_field('invoice_format', __('Invoice number format', $this->textdomain), array(&$this, 'invoice_format_option'), $this->settings_key, 'section_template');
            add_settings_field('reset_invoice_number', __('Reset on 1st January', $this->textdomain), array(&$this, 'reset_invoice_number_option'), $this->settings_key, 'section_template');
            add_settings_field('invoice_date_format', __('Invoice date format', $this->textdomain), array(&$this, 'invoice_date_format_option'), $this->settings_key, 'section_template');
            add_settings_field('show_sku', __('Show SKU', $this->textdomain), array(&$this, 'show_sku_option'), $this->settings_key, 'section_template');
            add_settings_field('show_discount', __('Show discount', $this->textdomain), array(&$this, 'show_discount_option'), $this->settings_key, 'section_template');
            add_settings_field('show_subtotal', __('Show subtotal', $this->textdomain), array(&$this, 'show_subtotal_option'), $this->settings_key, 'section_template');
            add_settings_field('show_tax', __('Show tax', $this->textdomain), array(&$this, 'show_tax_option'), $this->settings_key, 'section_template');
            add_settings_field('show_shipping', __('Show shipping', $this->textdomain), array(&$this, 'show_shipping_option'), $this->settings_key, 'section_template');
            add_settings_field('show_customer_notes', __('Show customer notes', $this->textdomain), array(&$this, 'show_customer_notes_option'), $this->settings_key, 'section_template');
            //add_settings_field( 'preview_invoice', 'Preview invoice', array( &$this, 'preview_invoice_option' ), $this->settings_key, 'section_template' );
        }

        /**
         * Show all settings notices.
         */
        public function show_settings_notices() {
            settings_errors($this->settings_key);
        }

        /**
         * @param $args
         */
        public function template_id_option($args)
        {
            ?>
            <select id="template-type-option" name="<?php echo $this->settings_key; ?>[template_id]">
                <?php
                foreach ($args as $template) {
                    ?>
                    <option
                        value="<?php echo $template['id']; ?>"   <?php selected($this->settings['template_id'], $template['id']); ?>><?php echo $template['name']; ?></option>
                <?php
                }
                ?>
            </select>
        <?php
        }

        /**
         * @param $args
         */
        public function color_theme_option($args)
        {
            ?>
            <input id="color-picker" type="color" name="<?php echo $this->settings_key; ?>[color_theme]"
                   value="<?php echo $this->settings['color_theme']; ?>"/>
            <div class="notes"><?php _e('Color theme of the invoice.', $this->textdomain); ?></div>
        <?php
        }

        /**
         *
         */
        public function company_name_option()
        {
            ?>
            <input type="text"
                   name="<?php echo $this->settings_key; ?>[company_name]"
                   value="<?php echo $this->settings['company_name']; ?>"/>
        <?php
        }

        /**
         *
         */
        public function company_logo_option()
        {
            ?>
            <div
                class="notes"><?php _e('Please upload an image less then 200Kb and make sure it\'s a jpeg, jpg or png.', $this->textdomain); ?></div>
            <br/>
            <input id="upload-file" type="file" name="company_logo" accept="image/*"/>
            <input type="hidden" id="company-logo-value" name="company_logo"
                   value="<?php echo esc_attr($this->settings['company_logo']); ?>"/>
            <?php
            if ($this->settings['company_logo'] != "") {
                ?>
                <div id="company-logo-wrapper">
                    <img id="company-logo" src="<?php echo esc_attr($this->settings['company_logo']); ?>"/>
                    <img id="delete" src="<?php echo WPI_URL . '/assets/img/delete-icon.png'; ?>"
                         onclick="Settings.removeCompanyLogo()" title="<?php _e('Remove logo', $this->textdomain); ?>"/>
                </div>
            <?php
            }
            ?>
        <?php
        }

        /**
         *
         */
        public function intro_text_option()
        {
            ?>
            <div class="notes block"><?php echo $this->get_allowed_tags_str(); ?></div>
            <textarea name="<?php echo $this->settings_key; ?>[intro_text]" rows="5"
                      cols="50"><?php _e(esc_textarea($this->settings['intro_text'], $this->textdomain)); ?></textarea>
        <?php
        }

        /**
         *
         */
        public function company_address_option()
        {
            ?>
            <div class="notes block"><?php echo $this->get_allowed_tags_str(); ?></div>
            <textarea name="<?php echo $this->settings_key; ?>[company_address]" rows="5"
                      cols="50"><?php echo esc_textarea($this->settings['company_address']); ?></textarea>
        <?php
        }

        /**
         *
         */
        public function company_details_option()
        {
            ?>
            <div class="notes block"><?php echo $this->get_allowed_tags_str(); ?></div>
            <textarea name="<?php echo $this->settings_key; ?>[company_details]" rows="5"
                      cols="50"><?php echo esc_textarea($this->settings['company_details']); ?></textarea>
        <?php
        }

        /**
         *
         */
        public function terms_option()
        {
            ?>
            <div class="notes block"><?php echo $this->get_allowed_tags_str(); ?></div>
            <textarea name="<?php echo $this->settings_key; ?>[terms]" rows="5"
                      cols="50"><?php _e(esc_textarea($this->settings['terms'], $this->textdomain)); ?></textarea>
        <?php
        }

        /**
         *
         */
        public function show_subtotal_option()
        {
            ?>
            <input type="checkbox"
                   name="<?php echo $this->settings_key; ?>[show_subtotal]"
                   value="1"
                <?php checked($this->settings['show_subtotal']); ?> />
        <?php
        }

        /**
         *
         */
        public function show_tax_option()
        {
            ?>
            <input type="checkbox"
                   name="<?php echo $this->settings_key; ?>[show_tax]"
                   value="1"
                <?php checked($this->settings['show_tax']); ?> />
        <?php
        }

        /**
         *
         */
        public function show_discount_option()
        {
            ?>
            <input type="checkbox"
                   name="<?php echo $this->settings_key; ?>[show_discount]"
                   value="1"
                <?php checked($this->settings['show_discount']); ?> />
        <?php
        }

        /**
         *
         */
        public function show_shipping_option()
        {
            ?>
            <input type="checkbox"
                   name="<?php echo $this->settings_key; ?>[show_shipping]"
                   value="1"
                <?php checked($this->settings['show_shipping']); ?> />
        <?php
        }

        /**
         *
         */
        public function show_customer_notes_option()
        {
            ?>
            <input type="checkbox"
                   name="<?php echo $this->settings_key; ?>[show_customer_notes]"
                   value="1"
                <?php checked($this->settings['show_customer_notes']); ?> />
        <?php
        }

        /**
         *
         */
        public function show_sku_option()
        {
            ?>
            <input type="checkbox"
                   name="<?php echo $this->settings_key; ?>[show_sku]"
                   value="1"
                <?php checked($this->settings['show_sku']); ?> />
        <?php
        }

        /**
         *
         */
        public function next_invoice_number_option()
        {
            ?>
            <input type="text"
                   name="<?php echo $this->settings_key; ?>[next_invoice_number]"
                   value="<?php echo $this->settings['next_invoice_number']; ?>"/>
            <div class="notes"><?php _e('Invoice number to use for next invoice.', $this->textdomain); ?></div>
        <?php
        }

        /**
         *
         */
        public function invoice_number_digits_option()
        {
            ?>
            <input type="number"
                   name="<?php echo $this->settings_key; ?>[invoice_number_digits]"
                   value="<?php echo $this->settings['invoice_number_digits']; ?>"
                   min="3"
                   max="6"
                />
            <div class="notes"><?php _e('Number of zero digits.', $this->textdomain); ?></div>
        <?php
        }

        /**
         *
         */
        public function invoice_prefix_option()
        {
            ?>
            <input type="text"
                   name="<?php echo $this->settings_key; ?>[invoice_prefix]"
                   value="<?php echo $this->settings['invoice_prefix']; ?>"/>
            <div
                class="notes"><?php _e('Prefix text for the invoice number. Not required.', $this->textdomain); ?></div>
        <?php
        }

        /**
         *
         */
        public function invoice_suffix_option()
        {
            ?>
            <input type="text"
                   name="<?php echo $this->settings_key; ?>[invoice_suffix]"
                   value="<?php echo $this->settings['invoice_suffix']; ?>"/>
            <div
                class="notes"><?php _e('Suffix text for the invoice number. Not required.', $this->textdomain); ?></div>
        <?php
        }

        /**
         *
         */
        public function invoice_format_option()
        {
            ?>
            <input type="text"
                   name="<?php echo $this->settings_key; ?>[invoice_format]"
                   value="<?php echo $this->settings['invoice_format']; ?>"/>
            <div
                class="notes"><?php _e('Use [prefix], [suffix] and [number] as placeholders. [number] is required.', $this->textdomain); ?></div>
        <?php
        }

        /**
         *
         */
        public function reset_invoice_number_option()
        {
            ?>
            <input type="checkbox"
                   name="<?php echo $this->settings_key; ?>[reset_invoice_number]"
                   value="1"
                <?php checked($this->settings['reset_invoice_number']); ?> />
            <div class="notes"><?php _e('Reset on the first of January.', $this->textdomain); ?></div>
        <?php
        }

        /**
         *
         */
        public function invoice_date_format_option()
        {
            ?>
            <input type="text"
                   name="<?php echo $this->settings_key; ?>[invoice_date_format]"
                   value="<?php echo $this->settings['invoice_date_format']; ?>"/>
            <div
                class="notes"><?php printf(__('%sFormat%s of the date. Examples: %s or %s.', $this->textdomain), '<a href="http://php.net/manual/en/datetime.formats.date.php">', '</a>', '"m.d.y"', '"F jS Y"'); ?></div>
        <?php
        }

        /**
         *
         */
        /*function preview_invoice_option() {
            ?>
            <a href="<?php echo admin_url('admin-ajax.php'); ?>?action=wpi_preview_invoice&security=<?php echo wp_create_nonce('wpi_preview_invoice'); ?>" target="_blank">Preview</a>
        <?php
        }*/

        /**
         * Gets a template from the templates array by id.
         * @param $template_id
         * @return string
         */
        public function get_template($template_id)
        {
            $template = "";
            foreach ($this->templates as $template) {
                if ($template['id'] == $template_id) {
                    return $template;
                }
            }
            return $template;
        }

        /**
         * Validates all the settings values.
         *
         * @param $input
         * @return array
         */
        public function validate($input)
        {
            $output = array();

            // Validate template id
            if ($this->is_valid_int($input['template_id'])) {
                $output['template_id'] = $input['template_id'];
            } else {
                add_settings_error(
                    esc_attr($this->settings_key),
                    'invalid-template-value',
                    __('Invalid template.', $this->textdomain)
                );
            }

            // Validate color theme.
            if (is_string($this->is_valid_hex_color($input['color_theme']))) {
                $output['color_theme'] = $this->is_valid_hex_color($input['color_theme']);
            } else if ($this->is_valid_hex_color($input['color_theme'])) {
                $output['color_theme'] = $input['color_theme'];
            } else {
                add_settings_error(
                    esc_attr($this->settings_key),
                    'invalid-color-hex',
                    __('Invalid color theme code.', $this->textdomain)
                );
            }

            // Validate company name
            if ($this->is_valid_str($input['company_name'])) {
                $output['company_name'] = $input['company_name'];
            } else {
                add_settings_error(
                    esc_attr($this->settings_key),
                    'invalid-company-name',
                    __('Invalid company name.', $this->textdomain)
                );
            }

            // Validate company logo
            $output['company_logo'] = $this->upload_file();

            // Validate textarea's
            $ta_errors = 0;
            $textarea_values = array('intro_text' => $input['intro_text'], 'company_address' => $input['company_address'], 'company_details' => $input['company_details'], 'terms' => $input['terms']);
            foreach ($textarea_values as $key => $value) {
                ($this->validate_textarea($value)) ? $output[$key] = $value : $ta_errors += 1;
            }

            if ($ta_errors > 0) {
                add_settings_error(
                    esc_attr($this->settings_key),
                    'invalid_textarea_value',
                    __('Invalid input into one of the textarea\'s.', $this->textdomain)
                );
            }

            // Validate next invoice number
            if ($this->is_valid_int($input['next_invoice_number'])) {
                $output['next_invoice_number'] = $input['next_invoice_number'];
            } else {
                add_settings_error(
                    esc_attr($this->settings_key),
                    'invalid_next_invoice_number',
                    __('Invalid (next) invoice number.', $this->textdomain)
                );
            }

            // Validate zero digits
            $ind_errors = 0;
            if ($this->is_valid_int($input['invoice_number_digits'])) {
                ($input['invoice_number_digits'] >= 3 && $input['invoice_number_digits'] <= 6)
                    ? $output['invoice_number_digits'] = $input['invoice_number_digits']
                    : $ind_errors += 1;
            } else {
                $ind_errors += 1;
            }

            if ($ind_errors > 0) {
                add_settings_error(
                    esc_attr($this->settings_key),
                    'invalid_invoice_number_digits',
                    __('Invalid invoice number digits.', $this->textdomain)
                );
            }

            // Validate invoice number prefix and suffix.
            $output['invoice_prefix'] = esc_html($input['invoice_prefix']);
            $output['invoice_suffix'] = esc_html($input['invoice_suffix']);

            // Validate invoice number format
            if ($this->is_valid_str($input['invoice_format'])) {
                if (strpos($input['invoice_format'], '[number]') !== false) {
                    $output['invoice_format'] = $input['invoice_format'];
                } else {
                    add_settings_error(
                        esc_attr($this->settings_key),
                        'invalid_invoice_format-1',
                        __('The [number] placeholder is required as invoice number format.', $this->textdomain)
                    );
                }
            } else {
                add_settings_error(
                    esc_attr($this->settings_key),
                    'invalid_invoice_format-2',
                    __('Invalid invoice number format.', $this->textdomain)
                );
            }

            // Validate all checkboxes
            $cb_errors = 0;
            $checkbox_values = array(
                'reset_invoice_number' => $input['reset_invoice_number'],
                'show_sku' => $input['show_sku'],
                'show_discount' => $input['show_discount'],
                'show_subtotal' => $input['show_subtotal'],
                'show_tax' => $input['show_tax'],
                'show_shipping' => $input['show_shipping'],
                'show_customer_notes' => $input['show_customer_notes']
            );

            foreach ($checkbox_values as $key => $value) {
                ($this->validate_checkbox($value)) ? $output[$key] = $value : $output[$key] = 0;
            }

            if ($cb_errors > 0) {
                add_settings_error(
                    esc_attr($this->settings_key),
                    'invalid-checkbox-value',
                    __('Please don\'t try to change the values.', $this->textdomain)
                );
            }

            if ($this->is_valid_str($input['invoice_date_format'])) {
                $output['invoice_date_format'] = $input['invoice_date_format'];
            } else {
                add_settings_error(
                    esc_attr($this->settings_key),
                    'invalid-date-format',
                    __('Invalid date format.', $this->textdomain)
                );
            }

            return $output;
        }

        /**
         * Checks if the company logo has changed and uploads new logo's.
         * @return string
         */
        private function upload_file() {
            $return = "";

            if ($_FILES['company_logo']['error'] == 0) {
                $file = $_FILES['company_logo'];
                if ($file['size'] <= 200000) {
                    $override = array('test_form' => false);
                    $company_logo = wp_handle_upload($file, $override);
                    $validate_file_code = validate_file($company_logo['url']);
                    if ($validate_file_code == 0) {
                        $return = $company_logo['url'];
                    } else {
                        switch ($validate_file_code) {
                            case 1:
                                add_settings_error(
                                    esc_attr($this->settings_key),
                                    'file-invalid-1',
                                    __('File is invalid and contains either \'..\' or \'./\'.', $this->textdomain)
                                );
                                break;
                            case 2:
                                add_settings_error(
                                    esc_attr($this->settings_key),
                                    'file-invalid-2',
                                    __('File is invalid and contains \':\' after the first character.', $this->textdomain)
                                );
                                break;
                        }
                    }
                } else {
                    add_settings_error(
                        esc_attr($this->settings_key),
                        'file-invalid-3',
                        __('Please upload image with extension jpg, jpeg or png.', $this->textdomain)
                    );
                }
            } else if (!empty($_POST['company_logo'])) {
                $return = $_POST['company_logo'];
            }

            return $return;
        }
    }
}