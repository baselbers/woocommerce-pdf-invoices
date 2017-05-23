<?php
/**
 * Settings class to return options.
 *
 * @author      Bas Elbers
 * @category    Abstract Class
 * @package     BE_WooCommerce_PDF_Invoices/Abstracts
 * @version     1.0.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Class BEWPI_Abstract_Settings.
 */
abstract class BEWPI_Abstract_Settings {

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
	 * Settings classes.
	 *
	 * @var array
	 */
	private static $settings = array();

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
		add_action( 'admin_init', array( __CLASS__, 'load_settings' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_wc_submenu_options_page' ) );
		add_action( 'admin_notices', array( __CLASS__, 'display_settings_errors' ) );
	}

	/**
	 * Load all settings classes.
	 */
	public static function load_settings() {
		global $pagenow;

		// Only load settings on settings page. @todo Only load settings for specific tab.
		if ( isset( $_GET['page'] ) && 'bewpi-invoices' === $_GET['page'] || 'options.php' === $pagenow ) {
			$settings[] = new BEWPI_General_Settings();
			$settings[] = new BEWPI_Template_Settings();
			self::$settings = apply_filters( 'bewpi_settings', $settings );
		}
	}

	/**
	 * Add submenu to WooCommerce menu and display options page.
	 */
	public static function add_wc_submenu_options_page() {
		add_submenu_page( 'woocommerce', __( 'Invoices', 'woocommerce-pdf-invoices' ), __( 'Invoices', 'woocommerce-pdf-invoices' ), 'manage_options', 'bewpi-invoices', array( __CLASS__, 'display_options_page' ) );
	}

	/**
	 * WooCommerce PDF Invoices settings page.
	 */
	public static function display_options_page() {
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'bewpi_general_settings';
		$sidebar_path = apply_filters( 'bewpi_sidebar_path', WPI_DIR . '/includes/admin/views/html-sidebar.php' );
		$width = sprintf( 'style="width: %s;"', $sidebar_path ? '75%' : '100%' );
		?>

		<div class="wrap wpi">

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( self::$settings as $setting ) {
					$active = $current_tab === $setting->settings_key ? 'nav-tab-active' : '';
					printf( '<a class="nav-tab %1$s" href="?page=bewpi-invoices&tab=%2$s">%3$s</a>', $active, $setting->settings_key, $setting->settings_tab );
				}

				// Backwards compatibility.
				$tabs = apply_filters( 'bewpi_settings_tabs', array() );
				foreach ( $tabs as $settings_key => $settings_tab ) {
					$active = $current_tab === $settings_key ? 'nav-tab-active' : '';
					printf( '<a class="nav-tab %1$s" href="?page=bewpi-invoices&tab=%2$s">%3$s</a>', $active, $settings_key, $settings_tab );
				}
				?>
			</h2>
			<form method="post" action="options.php" enctype="multipart/form-data" <?php echo $width; ?>>
				<?php
				settings_fields( $current_tab );
				do_settings_sections( $current_tab );
				submit_button();
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
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'bewpi_general_settings';
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
			add_settings_section( $id, $section['title'], function() use ( $section ) {
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
			foreach ( $args['options'] as $option ) :
				?>
				<option
					value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $options[ $args['name'] ], $option['value'] ); ?>><?php echo esc_html( $option['id'] ); ?></option>
				<?php
			endforeach;
			?>
		</select>
		<div class="bewpi-notes"><?php echo $args['desc']; ?></div>
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
		$options             = get_option( $args['page'] );
		$class               = isset( $args['class'] ) ? $args['class'] : 'bewpi-notes';
		$is_checkbox         = 'checkbox' === $args['type'];
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
			<label for="<?php echo $args['id']; ?>"
			       class="<?php echo $class; ?>"><?php echo $args['desc']; ?></label>
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
		return wp_list_pluck( $this->fields, 'default', 'name' );
	}

	/**
	 * Initialize field defaults.
	 *
	 * @return bool
	 */
	protected function set_defaults() {
		$options = array_merge( $this->defaults, (array) get_option( $this->settings_key ) );

		return update_option( $this->settings_key, $options );
	}

	/**
	 * @param $input
	 *
	 * @return mixed
	 */
	public abstract function sanitize( $input );
}
