<?php
/**
 * Debug settings
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BEWPI_Debug_Settings.
 */
class BEWPI_Debug_Settings extends BEWPI_Abstract_Settings {

	/**
	 * BEWPI_Debug_Settings constructor.
	 */
	public function __construct() {
		$this->id           = 'debug';
		$this->settings_key = 'bewpi_debug_settings';
		$this->settings_tab = __( 'Debug', 'woocommerce-pdf-invoices' );
		$this->fields       = $this->get_fields();
		$this->sections     = $this->get_sections();
		$this->defaults     = $this->get_defaults();

		parent::__construct();
	}

	/**
	 * Get all sections.
	 *
	 * @return array.
	 */
	private function get_sections() {
		$sections = apply_filters( 'wpi_debug_sections', array(
				'general' => array(
					'title'       => __( 'General Options', 'woocommerce-pdf-invoices' ),
					'description' => '',
				),
				'debug'   => array(
					'title'       => __( 'Plugin Configuration', 'woocommerce-pdf-invoices' ),
					'description' => '',
				),
			)
		);

		return $sections;
	}

	/**
	 * Settings configuration.
	 *
	 * @return array
	 */
	private function get_fields() {
		$settings = array(
			array(
				'id'       => 'bewpi-mpdf-debug',
				'name'     => $this->prefix . 'mpdf_debug',
				'title'    => '',
				'callback' => array( $this, 'input_callback' ),
				'page'     => $this->settings_key,
				'section'  => 'general',
				'type'     => 'checkbox',
				'desc'     => __( 'Enable mPDF debugging', 'woocommerce-pdf-invoices' )
				              . '<br/><div class="bewpi-notes">' . __( 'Enable if you aren\'t able to create an invoice.', 'woocommerce-pdf-invoices' ) . '</div>',
				'class'    => 'bewpi-checkbox-option-title',
				'default'  => (bool) WPI()->get_option( 'general', 'mpdf_debug' ) ? 1 : 0,
			),
		);

		return apply_filters( 'wpi_debug_settings', $settings, $this );
	}

	public function display_custom_settings() {
		echo '<pre>';
		foreach ( parent::$setting_tabs as $setting_tab ) {
			$class = new $setting_tab['class'];
			echo print_r( get_option( $class->settings_key ), true );
		}
		echo '</pre>';
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input form settings.
	 *
	 * @return mixed|void
	 */
	public function sanitize( $input ) {
		$output = get_option( $this->settings_key );

		foreach ( $output as $key => $value ) {
			if ( ! isset( $input[ $key ] ) ) {
				$output[ $key ] = is_array( $output[ $key ] ) ? array() : '';
				continue;
			}

			if ( is_array( $output[ $key ] ) ) {
				$output[ $key ] = $input[ $key ];
				continue;
			}

			// strip all html and php tags and properly handle quoted strings.
			$output[ $key ] = $this->strip_str( stripslashes( $input[ $key ] ) );
		}

		return apply_filters( 'bewpi_sanitized_' . $this->settings_key, $output, $input );
	}
}
