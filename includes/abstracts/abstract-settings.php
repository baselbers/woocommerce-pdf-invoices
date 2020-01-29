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
				enctype="multipart/form-data" <?php echo $width; ?>>
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
		$name    = $args['page'] . '[' . $args['name'] . ']';
		$value   = $options[ $args['name'] ];

		echo wc_help_tip( $args['desc'] );
		printf( '<select id="%s" name="%s">', esc_attr( $args['id'] ), esc_attr( $name ) );

		foreach ( $args['options'] as $key => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_html( $label ) );
		}

		printf( '</select>' );
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
			aria-label="<?php esc_attr_e( 'Column', 'woocommerce-pdf-invoices' ); ?>"
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
	 * @param array $args Field arguments.
	 */
	public function reset_counter_callback( $args ) {
		$options    = get_option( $args['page'] );
		$name       = $args['page'] . '[' . $args['name'] . ']';
		$value      = $options[ $args['name'] ];
		$attributes = isset( $args['attrs'] ) ? implode( ' ', $args['attrs'] ) : '';
		$label      = isset( $args['label'] ) ? $args['label'] : '';
		$checkbox   = sprintf( '<input type="checkbox" id="%s" name="%s" value="%s" %s %s/>', $args['id'], $name, true, checked( true, (int) $value, false ), $attributes );
		$hidden     = sprintf( '<input type="hidden" name="%s" value="%s" />', esc_html( $name ), false );

		printf( '<label for="%s">%s</label>', esc_attr( $args['id'] ), $hidden . $checkbox . esc_html( $label ) );

		if ( isset( $args['desc'] ) ) {
			printf( '<p class="description">%s</p>', $args['desc'] );
		}
	}

	/**
	 * Next invoice number field.
	 *
	 * @param array $args Field arguments.
	 */
	public function next_invoice_number_callback( $args ) {
		$name                = $args['page'] . '[' . $args['name'] . ']';
		$attributes          = isset( $args['attrs'] ) ? implode( ' ', $args['attrs'] ) : '';
		$next_invoice_number = get_transient( 'bewpi_next_invoice_number' );
		$year                = (int) date_i18n( 'Y', current_time( 'timestamp' ) );
		$value               = false === $next_invoice_number ? BEWPI_Abstract_Invoice::get_max_invoice_number( $year ) + 1 : $next_invoice_number;

		echo wc_help_tip( $args['desc'] );
		printf( '<input id="%s" name="%s" type="%s" value="%d" %s />', esc_attr( $args['id'] ), esc_attr( $name ), esc_attr( $args['type'] ), esc_attr( $value ), $attributes );
	}

	/**
	 * Input field.
	 *
	 * @param array $args Field arguments.
	 */
	public function input_callback( $args ) {
		$options    = get_option( $args['page'] );
		$name       = $args['page'] . '[' . $args['name'] . ']';
		$value      = $options[ $args['name'] ];
		$attributes = isset( $args['attrs'] ) ? implode( ' ', $args['attrs'] ) : '';

		if ( 'checkbox' === $args['type'] ) {
			$label    = isset( $args['label'] ) ? $args['label'] : '';
			$checkbox = sprintf( '<input type="checkbox" id="%s" name="%s" value="%s" %s %s/>', $args['id'], $name, true, checked( true, (int) $value, false ), $attributes );
			$hidden   = sprintf( '<input type="hidden" name="%s" value="%s" />', esc_html( $name ), false );
			printf( '<label for="%s">%s</label>', esc_attr( $args['id'] ), $hidden . $checkbox . esc_html( $label ) );

			if ( isset( $args['desc'] ) ) {
				printf( '<p class="description">%s</p>', $args['desc'] );
			}
		} else {
			echo wc_help_tip( $args['desc'] );
			printf( '<input id="%s" name="%s" type="%s" value="%s" />', esc_attr( $args['id'] ), esc_attr( $name ), esc_attr( $args['type'] ), esc_attr( $value ) );
		}
	}

	/**
	 * Textarea field.
	 *
	 * @param array $args Field arguments.
	 */
	public function textarea_callback( $args ) {
		$options = get_option( $args['page'] );
		$name    = $args['page'] . '[' . $args['name'] . ']';
		$value   = $options[ $args['name'] ];

		echo wc_help_tip( $args['desc'] );
		printf( '<textarea id="%s" name="%s" rows="%d">%s</textarea>', esc_attr( $args['id'] ), esc_attr( $name ), 5, esc_textarea( $value ) );
	}

	/**
	 * Add additional file option callback.
	 *
	 * @param array $args Field arguments.
	 */
	public function upload_callback( $args ) {
		$options       = get_option( $args['page'] );
		$attachment_id = $options[ $args['name'] ];
		$file_url      = wp_get_attachment_url( $attachment_id );
		?>
		<p class="form-field">
			<input type="hidden" class="file_id"
				name="<?php echo esc_attr( $args['page'] . '[' . $args['name'] . ']' ); ?>"
				value="<?php echo esc_attr( $attachment_id ); ?>"/>
			<input type="<?php echo esc_attr( $args['type'] ); ?>" class="file_url"
				placeholder="<?php echo esc_attr( $file_url ); ?>" value="<?php echo esc_attr( $file_url ); ?>"/>
			<button class="button upload_image_button"
				data-uploader_button_text="<?php _e( 'Use file', 'woocommerce-pdf-invoices' ); ?>"><?php _e( 'Upload', 'woocommerce-pdf-invoices' ); ?></button>
		</p>
		<script type="text/javascript">
			// Uploading files
			var file_frame;
			var file_target_input;
			var file_id_input;

			jQuery('.upload_image_button').live('click', function (event) {

				event.preventDefault();

				file_target_input = jQuery(this).closest('.form-field').find('.file_url');
				file_id_input = jQuery(this).closest('.form-field').find('.file_id');

				// If the media frame already exists, reopen it.
				if (file_frame) {
					file_frame.open();
					return;
				}

				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: jQuery(this).data('uploader_title'),
					button: {
						text: jQuery(this).data('uploader_button_text')
					},
					multiple: false  // Set to true to allow multiple files to be selected,
				});

				// When an image is selected, run a callback.
				file_frame.on('select', function () {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();

					jQuery(file_target_input).val(attachment.url);
					jQuery(file_id_input).val(attachment.id);
				});

				// Finally, open the modal
				file_frame.open();
			});

			jQuery('.upload_image_button').closest('.form-field').find('.file_url').on('change', function (event) {
				file_id_input = jQuery(this).closest('.form-field').find('.file_id');
				jQuery(file_id_input).val('');
			});
		</script>
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
				), true ) ) {
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
	 * Sanitize settings.
	 *
	 * @param array $input Settings.
	 *
	 * @return array
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
	protected function display_custom_settings() {
	}
}
