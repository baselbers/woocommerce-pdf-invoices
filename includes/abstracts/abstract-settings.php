<?php
/**
 * Settings class to return options.
 *
 * @author      Bas Elbers
 * @category    Abstract Class
 * @package     BE_WooCommerce_PDF_Invoices/Abstracts
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BEWPI_Abstract_Settings.
 */
abstract class BEWPI_Abstract_Settings {
	/**
	 * Setting ID.
	 *
	 * @var string
	 */
	protected $id = 'general';

	/**
	 * Option name prefix.
	 *
	 * @var string
	 */
	protected $prefix = 'bewpi_';

	/**
	 * Option key.
	 *
	 * @var string.
	 */
	public $settings_key;

	/**
	 * Page tab.
	 *
	 * @var string.
	 */
	public $settings_tab;

	/**
	 * Option sections.
	 *
	 * @var array.
	 */
	protected $sections = array();

	/**
	 * Option fields.
	 *
	 * @var array.
	 */
	protected $fields = array();

	/**
	 * Field defaults.
	 *
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * Submit button text.
	 *
	 * @var null
	 */
	private $submit_button_text = null;

	/**
	 * Setting tab config data.
	 *
	 * @var array
	 */
	public static $setting_tabs = array();

	/**
	 * Setting object.
	 *
	 * @var BEWPI_Abstract_Settings
	 */
	public static $setting = null;

	/**
	 * Current active tab.
	 *
	 * @var string
	 */
	private static $current_tab = 'general';

	/**
	 * BEWPI_Abstract_Settings constructor.
	 */
	public function __construct() {
		$this->add_sections();
		$this->add_fields();
		$this->set_defaults();

		register_setting( $this->settings_key, $this->settings_key, array( $this, 'sanitize' ) );
	}

	/**
	 * Initialize hooks.
	 */
	public static function init_hooks() {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_wc_submenu_options_page' ) );
		add_action( 'admin_notices', array( __CLASS__, 'display_settings_errors' ) );
	}

	/**
	 * Admin init.
	 */
	public static function admin_init() {
		// Only load settings on settings saved or page load.
		if ( isset( $_GET['key'] ) && md5( WPI()->get_plugin_slug() ) === $_GET['key'] || isset( $_GET['page'] ) && WPI()->get_plugin_slug() === $_GET['page'] ) {
			self::load_setting_tabs();

			self::$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : self::$current_tab;

			/**
			 * Setting annotation.
			 *
			 * @var BEWPI_Abstract_Settings $current_setting setting object.
			 */
			if ( isset( self::$setting_tabs[ self::$current_tab ] ) ) {
				self::$setting = new self::$setting_tabs[ self::$current_tab ]['class']();
			}
		}
	}

	/**
	 * Load settings.
	 */
	public static function load_setting_tabs() {
		$setting_tabs['general']  = array(
			'class' => 'BEWPI_General_Settings',
			'label' => __( 'General', 'woocommerce-pdf-invoices' ),
		);
		$setting_tabs['template'] = array(
			'class' => 'BEWPI_Template_Settings',
			'label' => __( 'Template', 'woocommerce-pdf-invoices' ),
		);

		self::$setting_tabs = apply_filters( 'wpi_setting_tabs', $setting_tabs );

		self::$setting_tabs['debug'] = array(
			'class' => 'BEWPI_Debug_Settings',
			'label' => __( 'Debug', 'woocommerce-pdf-invoices' ),
		);
	}

	/**
	 * Add setting error.
	 *
	 * @param object $error setting error object.
	 */
	protected function add_error( $error ) {
		add_settings_error( $this->settings_key, 'error', $error->message, $error->type );
	}

	/**
	 * Get all settings errors.
	 *
	 * @return array
	 */
	protected function get_errors() {
		return get_settings_errors( $this->settings_key );
	}

	/**
	 * Add submenu to WooCommerce menu and display options page.
	 */
	public static function add_wc_submenu_options_page() {
		add_submenu_page( 'woocommerce', __( 'Invoices', 'woocommerce-pdf-invoices' ), __( 'Invoices', 'woocommerce-pdf-invoices' ), self::settings_capability(), WPI()->get_plugin_slug(), array(
			__CLASS__,
			'display_options_page',
		) );
	}

	/**
	 * Capabilities needed for managing the settings of this plugin.
	 */
	public static function settings_capability() {
		return apply_filters( 'bewpi_settings_capability', 'manage_options' );
	}

	/**
	 * Load settings based on active tab.
	 *
	 * @throws Exception Cannot find settings class.
	 */
	public static function display_options_page() {
		$sidebar_path = apply_filters( 'wpi_sidebar_path', WPI_DIR . '/includes/admin/views/html-sidebar.php' );
		$width        = sprintf( 'style="width: %s;"', $sidebar_path ? '75%' : '100%' );
		?>

		<div class="wrap wpi">

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( self::$setting_tabs as $id => $tab ) {
					$active = self::$current_tab === $id ? 'nav-tab-active' : '';
					printf( '<a class="nav-tab %1$s" href="%2$s">%3$s</a>',
						esc_attr( $active ),
						add_query_arg( array(
							'page' => WPI()->get_plugin_slug(),
							'tab'  => $id,
						), '' ),
						esc_html( $tab['label'] )
					);
				}
				?>
			</h2>
			<form method="post"
			      action="options.php?tab=<?php echo self::$current_tab; ?>&key=<?php echo md5( WPI()->get_plugin_slug() ); ?>"
			      enctype="multipart/form-data" <?php echo esc_html( $width ); ?>>
				<?php
				settings_fields( self::$setting->settings_key );
				do_settings_sections( self::$setting->settings_key );

				self::$setting->display_custom_settings();

				submit_button( self::$setting->get_submit_button_text() );
				?>
			</form>

			<?php
			if ( $sidebar_path ) {
				include $sidebar_path;
			}
			?>

		</div>

		<?php
		// Add rate plugin text in footer.
		add_filter( 'admin_footer_text', array( __CLASS__, 'plugin_review_text' ), 50 );
		add_filter( 'update_footer', array( __CLASS__, 'plugin_version' ), 50 );
	}

	/**
	 *
	 */
	public static function display_settings_errors() {
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : self::$current_tab;
		settings_errors( $current_tab );
	}

	/**
	 * Add rate plugin text to footer of settings page.
	 *
	 * @return string
	 */
	public static function plugin_review_text() {
		return sprintf( __( 'If you like <strong>WooCommerce PDF Invoices</strong> please leave us a <a href="%s">★★★★★</a> rating. A huge thank you in advance!', 'woocommerce-pdf-invoices' ), 'https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform' );
	}

	/**
	 * Plugin version text in footer of settings page.
	 *
	 * @return string
	 */
	public static function plugin_version() {
		return sprintf( __( 'Version %s', 'woocommerce-pdf-invoices' ), WPI_VERSION );
	}

	/**
	 * Adds all the different settings sections
	 */
	private function add_sections() {
		foreach ( $this->sections as $id => $section ) {
			add_settings_section( $id, $section['title'], function () use ( $section ) {
				if ( isset( $section['description'] ) ) {
					echo $section['description'];
				}
			}, $this->settings_key );
		}
	}

	/**
	 * Show all settings notices.
	 *
	 * @param string $settings_key Settings key.
	 */
	public static function display_settings_notices( $settings_key ) {
		settings_errors( $settings_key );
	}

	/**
	 * Adds settings fields
	 */
	protected function add_fields() {
		foreach ( $this->fields as $field ) {
			add_settings_field( $field['name'], $field['title'], $field['callback'], $field['page'], $field['section'], $field );
		};
	}

	/**
	 * Format all available invoice number placeholders.
	 *
	 * @return string
	 */
	protected static function formatted_number_placeholders() {
		$placeholders = array( '[number]', '[order-number]', '[order-date]', '[m]', '[Y]', '[y]' );

		return '<code>' . join( '</code>, <code>', $placeholders ) . '</code>';
	}

	/**
	 * Gets all the tags that are allowed.
	 *
	 * @return string|void
	 */
	protected static function formatted_html_tags() {
		$html_tags = array_map( 'htmlspecialchars', array( '<b>', '<i>', '<br>' ) );

		return '<code>' . join( '</code>, <code>', $html_tags ) . '</code>';
	}

	/**
	 * String validation.
	 *
	 * @param string $str the string to validate.
	 *
	 * @return bool
	 */
	protected function strip_str( $str ) {
		$str = preg_replace( '/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i', '<$1$2>', $str );

		return strip_tags( $str, '<b><i><br><br/>' );
	}

	/**
	 * Multiple checkbox fields.
	 *
	 * @param array $args option arguments.
	 */
	public function multiple_checkbox_callback( $args ) {
		include WPI_DIR . '/includes/admin/views/html-multiple-checkbox-setting.php';
	}

	/**
	 * Select field.
	 *
	 * @param array $args Field arguments.
	 */
	public function select_callback( $args ) {
		$options = get_option( $args['page'] );
		?>
		<select id="<?php echo $args['id']; ?>" name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>">
			<?php
			foreach ( $args['options'] as $key => $label ) :
				?>
				<option
					value="<?php echo esc_attr( $key ); ?>" <?php selected( $options[ $args['name'] ], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php
			endforeach;
			?>
		</select>
		<div class="bewpi-notes"><?php echo $args['desc']; ?></div>
		<?php
	}

	/**
	 * Multiple select box.
	 *
	 * @param array $args field arguments.
	 */
	public function multi_select_callback( $args ) {
		$page_options = get_option( $args['page'] );
		$selections   = (array) $page_options[ $args['name'] ];
		$options      = array_merge( array_flip( $selections ), $args['options'] );
		?>
		<select multiple="multiple"
		        name="<?php echo esc_attr( $args['page'] . '[' . $args['name'] . '][]' ); ?>"
		        title="<?php echo esc_attr( $args['title'] ); ?>"
		        data-placeholder="<?php esc_attr_e( 'Choose&hellip;', 'woocommerce-pdf-invoices' ); ?>"
		        aria-label="<?php esc_attr_e( 'Column', 'woocommerce-pdf-invoices' ) ?>"
		        class="wc-enhanced-select">
			<?php
			foreach ( $options as $id => $option ) {
				echo '<option value="' . esc_attr( $option['value'] ) . '" ' . selected( in_array( $id, $selections, true ), true, false ) . '>' . $option['name'] . '</option>';
			}
			?>
		</select>
		<?php echo ( $args['desc'] ) ? $args['desc'] : ''; ?>
		<a class="select_all button" href="#"><?php _e( 'Select all', 'woocommerce-pdf-invoices' ); ?></a> <a
			class="select_none button" href="#"><?php _e( 'Select none', 'woocommerce-pdf-invoices' ); ?></a>
		<?php
	}

	/**
	 * Reset counter field.
	 *
	 * @param string $args Field arguments.
	 */
	public function reset_counter_callback( $args ) {
		$class = isset( $args['class'] ) ? $args['class'] : 'bewpi-notes';
		?>
		<input type="hidden" name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>" value="0"/>
		<input id="<?php echo $args['id']; ?>"
		       name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>"
		       type="<?php echo $args['type']; ?>"
		       value="1"
			<?php
			checked( (bool) get_transient( 'bewpi_next_invoice_number' ) );

			if ( isset( $args['attrs'] ) ) {
				foreach ( $args['attrs'] as $attr ) {
					echo $attr . ' ';
				}
			}
			?>
		/>
		<label for="<?php echo $args['id']; ?>" class="<?php echo $class; ?>">
			<?php echo $args['desc']; ?>
		</label>
		<?php
	}

	/**
	 * Next invoice number field.
	 *
	 * @param array $args Field arguments.
	 */
	public function next_invoice_number_callback( $args ) {
		$class               = isset( $args['class'] ) ? $args['class'] : 'bewpi-notes';
		$next_invoice_number = get_transient( 'bewpi_next_invoice_number' );
		?>
		<input id="<?php echo $args['id']; ?>"
		       name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>"
		       type="<?php echo $args['type']; ?>"
		       value="<?php echo esc_attr( ( false !== $next_invoice_number ) ? $next_invoice_number : BEWPI_Abstract_Invoice::get_max_invoice_number() + 1 ); ?>"
			<?php
			if ( isset( $args['attrs'] ) ) {
				foreach ( $args['attrs'] as $attr ) {
					echo $attr . ' ';
				}
			}
			?>
		/>
		<div class="<?php echo $class; ?>"><?php echo $args['desc']; ?></div>
		<?php
	}

	/**
	 * Input field.
	 *
	 * @param array $args Field arguments.
	 */
	public function input_callback( $args ) {
		$options     = get_option( $args['page'] );
		$class       = isset( $args['class'] ) ? $args['class'] : 'bewpi-notes';
		$is_checkbox = 'checkbox' === $args['type'];
		if ( $is_checkbox ) { ?>
			<input type="hidden" name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>" value="0"/>
		<?php } ?>
		<input id="<?php echo $args['id']; ?>"
		       name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>"
		       type="<?php echo $args['type']; ?>"
		       value="<?php echo $is_checkbox ? 1 : esc_attr( $options[ $args['name'] ] ); ?>"

			<?php
			if ( $is_checkbox ) {
				checked( $options[ $args['name'] ] );
			}

			if ( isset( $args['attrs'] ) ) {
				foreach ( $args['attrs'] as $attr ) {
					echo $attr . ' ';
				}
			}
			?>
		/>
		<?php if ( $is_checkbox ) { ?>
			<label for="<?php echo $args['id']; ?>" class="<?php echo $class; ?>"><?php echo $args['desc']; ?></label>
		<?php } else { ?>
			<div class="<?php echo $class; ?>"><?php echo $args['desc']; ?></div>
		<?php } ?>
		<?php
	}

	/**
	 * Textarea field.
	 *
	 * @param array $args Field arguments.
	 */
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

	/**
	 * Gets all default settings values from the settings array.
	 *
	 * @return array
	 */
	protected function get_defaults() {
		$fields   = $this->fields;
		$defaults = array();

		// Remove multiple checkbox types from settings.
		foreach ( $fields as $index => $field ) {
			if ( isset( $field['type'] ) && in_array( $field['type'], array(
					'multiple_checkbox',
					'multiple_select'
				) )
			) {
				// Add options defaults.
				$defaults[ $field['name'] ] = array_keys( array_filter( wp_list_pluck( $field['options'], 'default', 'value' ) ) );
				unset( $fields[ $index ] );
			}
		}

		$defaults = wp_parse_args( $defaults, wp_list_pluck( $fields, 'default', 'name' ) );

		return $defaults;
	}

	/**
	 * Initialize field defaults.
	 *
	 * @return bool
	 */
	protected function set_defaults() {
		$options = get_option( $this->settings_key );

		// Initialize options.
		if ( false === $options ) {
			return add_option( $this->settings_key, $this->defaults );
		}

		// Recursive merge options with defaults.
		foreach ( $this->defaults as $key => $value ) {

			if ( ! isset( $options[ $key ] ) ) {
				continue;
			}

			$this->defaults[ $key ] = $options[ $key ];
		}

		return update_option( $this->settings_key, $this->defaults );
	}

	/**
	 * Get the formatted html setting id.
	 *
	 * @param string $id String to format.
	 *
	 * @return string.
	 */
	public static function get_formatted_setting_id( $id ) {
		return str_replace( '_', '-', WPI()->get_prefix() . $id );
	}

	/**
	 * @param $input
	 *
	 * @return mixed
	 */
	public abstract function sanitize( $input );

	/**
	 * Set submit button text.
	 *
	 * @param string $text
	 */
	public function set_submit_button_text( $text ) {
		$this->submit_button_text = $text;
	}

	/**
	 * Get submit button text.
	 *
	 * @return null
	 */
	public function get_submit_button_text() {
		return $this->submit_button_text;
	}

	/**
	 * Display additional custom settings.
	 *
	 * @return mixed
	 */
	protected function display_custom_settings() {}
}
