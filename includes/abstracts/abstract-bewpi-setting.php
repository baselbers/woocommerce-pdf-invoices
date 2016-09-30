<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'BEWPI_Abstract_Setting' ) ) {

    /**
     * Abstract class with validation functions to validate all the template and general settings.
     * Class BEWPI_Settings
     */
    abstract class BEWPI_Abstract_Setting {

	    /**
	     * Options and settings prefix
	     * @var string
	     */
	    public $prefix = 'bewpi_';

	    /**
	     * For <textarea>.
	     * @var array
	     */
	    private $allowed_tags = array( '<b>', '<i>', '<br>', '<br/>' );

	    /**
	     * Validates an email.
	     *
	     * @param $email
	     *
	     * @return bool
	     */
	    protected function validate_email( $email ) {
		    return is_email( sanitize_email( $email ) ) ? true : false;
	    }

	    /**
	     * Validates a string.
	     *
	     * @param $str
	     *
	     * @return bool
	     */
	    protected function is_valid_str( $str ) {
		    return is_string( sanitize_text_field( $str ) );
	    }

	    /**
	     * Validates an integer.
	     *
	     * @param $int
	     *
	     * @return bool
	     */
	    protected function is_valid_int( $int ) {
		    return intval( $int ) && absint( $int );
	    }

	    /**
	     * Validates a textarea.
	     *
	     * @param $str
	     *
	     * @return bool
	     */
	    protected function strip_str( $str ) {
		    $str = preg_replace( "/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $str ); // Removes the attributes in the HTML tags
		    return strip_tags( $str, '<b><i><br><br/>' );
	    }

	    /**
	     * Check for a valid hex color string like '#c1c2b4'
	     *
	     * @param $hex
	     */
	    protected function is_valid_hex_color( $hex ) {
		    $valid = false;
		    if ( preg_match( '/^#[a-f0-9]{6}$/i', $hex ) ) {
			    return true;
		    } else if ( preg_match( '/^[a-f0-9]{6}$/i', $hex ) ) { // Check for a hex color string without hash like 'c1c2b4'
			    return '#' . $hex;
		    }

		    return false;
	    }

	    /**
	     * Gets all the tags that are allowed to use for the textarea's.
	     * @return string|void
	     */
	    protected function get_allowed_tags_str() {

		    if( empty( $this->allowed_tags ) ) {
			    return '';
		    }

		    $encoded_tags = array_map( 'htmlspecialchars', $this->allowed_tags );
		    $tags_string = '<code>' . join( '</code>, <code>', $encoded_tags ) . '</code>';

			return __( 'Allowed HTML tags: ', 'woocommerce-pdf-invoices' ) . $tags_string . '.';
	    }

	    public function select_callback( $args ) {
		    $options = get_option( $args['page'] );
		    ?>
		    <select id="<?php echo $args['id']; ?>" name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>" <?php
			    if ( isset ( $args['attrs'] ) ) :
				    foreach ( $args['attrs'] as $attr ) :
					    echo $attr . ' ';
				    endforeach;
			    endif;
			    ?> >
			    <?php
			    foreach ( $args['options'] as $option ) :
				    ?>
				    <option
					    value="<?php echo $option['value']; ?>" <?php selected( $options[ $args['name'] ], $option['value'] ); ?>><?php echo $option['name']; ?></option>
			    <?php
			    endforeach;
			    ?>
		    </select>
		    <div class="bewpi-notes"><?php echo $args['desc']; ?></div>
	    <?php
	    }

	    public function input_callback( $args ) {
		    $options = get_option( $args['page'] );
		    $class   = ( isset( $args['class'] ) ) ? $args['class'] : "bewpi-notes";
		    ?>
		    <input id="<?php echo $args['id']; ?>"
		           name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>"
		           type="<?php echo $args['type']; ?>"
		           value="<?php if ( $args['type'] === "checkbox" ) {
			           echo 1;
		           } else {
			           echo $options[ $args['name'] ];
		           } ?>"
			    <?php if ( $args['type'] === "checkbox" ) {
				    checked( $options[ $args['name'] ] );
			    } ?>
			    <?php
			    if ( isset ( $args['attrs'] ) ) :
				    foreach ( $args['attrs'] as $attr ) :
					    echo $attr . ' ';
				    endforeach;
			    endif;
			    ?>
			    />
		    <?php if ( $args['type'] === "checkbox" ) { ?>
			    <label class="<?php echo $class; ?>"><?php echo $args['desc']; ?></label>
		    <?php } else { ?>
			    <div class="<?php echo $class; ?>"><?php echo $args['desc']; ?></div>
		    <?php } ?>
	    <?php
	    }

	    public function logo_callback( $args ) {
		    $options = get_option( $args['page'] );
		    ?>
		    <input id="<?php echo $args['id']; ?>"
		           name="<?php echo $args['name']; ?>"
		           type="<?php echo $args['type']; ?>"
		           accept="image/*"
			    />
		    <div class="bewpi-notes"><?php echo $args['desc']; ?></div>
		    <input id="<?php echo $args['id'] . '-value'; ?>"
		           name="<?php echo $args['name']; ?>"
		           type="hidden"
		           value="<?php echo $options[ $args['name'] ]; ?>"
			    />

		    <?php
		    if ( ! empty( $options[ $args['name'] ] ) ) :
			    ?>
			    <div id="<?php echo $args['id'] . '-wrapper'; ?>">
				    <img id="<?php echo $args['id'] . '-image'; ?>"
				         src="<?php echo esc_attr( $options[ $args['name'] ] ); ?>"/>
				    <img id="<?php echo $args['id'] . '-delete'; ?>"
				         src="<?php echo BEWPI_URL . '/assets/images/delete-icon.png'; ?>"
				         onclick="Settings.removeCompanyLogo()"
				         title="<?php _e( 'Remove logo', 'woocommerce-pdf-invoices' ); ?>"/>
			    </div>
		    <?php
		    endif;
	    }

	    public function textarea_callback( $args ) {
		    $options = get_option( $args['page'] );
		    ?>
		    <textarea id="<?php echo $args['id']; ?>"
		              name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>"
		              rows="5"
			    ><?php echo esc_textarea( $options[ $args['name'] ] ); ?></textarea>
		    <div class="bewpi-notes"><?php echo $args['desc']; ?></div>
	    <?php
	    }
    }
}