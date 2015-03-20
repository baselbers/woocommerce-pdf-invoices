<?php

abstract class WPI_Settings {

    public $textdomain = 'be-woocommerce-pdf-invoices';

    /**
     * For <textarea>.
     * @var array
     */
    private $allowed_tags = ['<b>', '<i>', '<br>', '<br/>'];

    protected function validate_email( $email ) {
        return is_email( sanitize_email( $email ) ) ? true : false;
    }

    protected function is_valid_str( $str ) {
        return is_string( sanitize_text_field( $str ) );
    }

    protected function is_valid_int( $int ) {
        return intval( $int ) && absint( $int );
    }

    protected function validate_textarea( $str ) {
        $str = preg_replace( "/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $str ); // Removes the attributes in the HTML tags
        return is_string( strip_tags( $str, '<b><i><br><br/>' ) );
    }

    protected function validate_checkbox( $int ) {
        return $int == 1 || $int == 0;
    }

    /**
     * Check for a valid hex color string like '#c1c2b4'
     * @param $hex
     */
    protected function is_valid_hex_color( $hex ) {
        $valid = false;
        if( preg_match('/^#[a-f0-9]{6}$/i', $hex ) ) {
            return true;
        } else if( preg_match('/^[a-f0-9]{6}$/i', $hex ) ) { // Check for a hex color string without hash like 'c1c2b4'
            return '#' . $hex;
        }
        return false;
    }

    protected function get_allowed_tags_str() {
        $str = __( 'Allowed tags: ', $this->textdomain );
        foreach( $this->allowed_tags as $i => $tag ) {
            ( $i == count($this->allowed_tags) - 1 ) ? $str .= sprintf( '%s.', htmlspecialchars( $tag ) ) : $str .= sprintf( '%s', htmlspecialchars( $tag ) );
        }
        return $str;
    }
}