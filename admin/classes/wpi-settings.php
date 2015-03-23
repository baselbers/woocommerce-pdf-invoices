<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPI_Settings' ) ) {

    /**
     * Abstract class with validation functions to validate all the template and general settings.
     * Class WPI_Settings
     */
    abstract class WPI_Settings {

        /**
         * The textdomain
         * @var string
         */
        public $textdomain = 'be-woocommerce-pdf-invoices';

        /**
         * For <textarea>.
         * @var array
         */
        private $allowed_tags = ['<b>', '<i>', '<br>', '<br/>'];

        /**
         * Validates an email.
         * @param $email
         * @return bool
         */
        protected function validate_email($email) {
            return is_email(sanitize_email($email)) ? true : false;
        }

        /**
         * Validates a string.
         * @param $str
         * @return bool
         */
        protected function is_valid_str($str) {
            return is_string(sanitize_text_field($str));
        }

        /**
         * Validates an integer.
         * @param $int
         * @return bool
         */
        protected function is_valid_int($int) {
            return intval($int) && absint($int);
        }

        /**
         * Validates a textarea.
         * @param $str
         * @return bool
         */
        protected function validate_textarea($str) {
            $str = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $str); // Removes the attributes in the HTML tags
            return is_string(strip_tags($str, '<b><i><br><br/>'));
        }

        /**
         * Validates a checkbox
         * @param $int
         * @return bool
         */
        protected function validate_checkbox($int) {
            return $int == 1 || $int == 0;
        }

        /**
         * Check for a valid hex color string like '#c1c2b4'
         * @param $hex
         */
        protected function is_valid_hex_color($hex)
        {
            $valid = false;
            if (preg_match('/^#[a-f0-9]{6}$/i', $hex)) {
                return true;
            } else if (preg_match('/^[a-f0-9]{6}$/i', $hex)) { // Check for a hex color string without hash like 'c1c2b4'
                return '#' . $hex;
            }
            return false;
        }

        /**
         * Gets all the tags that are allowed to use for the textarea's.
         * @return string|void
         */
        protected function get_allowed_tags_str() {
            ( count( $this->allowed_tags ) > 0 ) ? $str = __('Allowed tags: ', $this->textdomain) : $str = '';
            foreach ($this->allowed_tags as $i => $tag) {
                ($i == count($this->allowed_tags) - 1) ? $str .= sprintf('%s.', htmlspecialchars($tag)) : $str .= sprintf('%s', htmlspecialchars($tag));
            }
            return $str;
        }
    }
}