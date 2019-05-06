<?php
/**
 * Admin notices.
 *
 * Show and dismiss admin notices.
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BEWPI_Admin_Notices.
 */
class BEWPI_Admin_Notices {

	/**
	 * Constructor.
	 */
	public static function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
		add_action( 'admin_init', array( __CLASS__, 'dismiss_notice_rate' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notice_rate' ) );
		add_action( 'wp_ajax_dismiss-notice', array( __CLASS__, 'dismiss_notice' ) );
		add_action( 'wp_ajax_deactivation-notice', array( __CLASS__, 'admin_notice_deactivation' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notice_activation' ) );
	}

	/**
	 * Load admin scripts.
	 */
	public function admin_scripts() {
		global $pagenow;

		wp_register_script( 'wpi_deactivate_js', WPI_URL . '/assets/js/deactivate.js', array(), WPI_VERSION, true );
		wp_localize_script( 'wpi_deactivate_js', 'WPI_DEACTIVATE', array( 'nonce' => wp_create_nonce( 'deactivation-notice' ) ) );

		if ( 'plugins.php' === $pagenow ) {
			wp_enqueue_script( 'wpi_deactivate_js' );
		}
	}

	/**
	 * Dismiss notices.
	 */
	public static function dismiss_notice_rate() {
		if ( ! isset( $_POST['wpi_action'] ) || ! isset( $_POST['nonce'] ) ) {
			return;
		}

		$action = sanitize_key( $_POST['wpi_action'] );
		if ( 'dismiss_notice_rate' !== $action ) {
			return;
		}

		if ( wp_verify_nonce( sanitize_key( $_POST['nonce'] ), $action ) ) {
			set_site_transient( WPI()->get_plugin_prefix() . $action, 1 );
		}
	}

	/**
	 * Admin notice for administrator to rate plugin on wordpress.org.
	 */
	public static function admin_notice_rate() {
		if ( get_site_transient( 'wpi_dismiss_notice_rate' ) ) {
			return;
		}

		// user needs to be an administrator.
		if ( false === current_user_can( 'manage_options' ) || false === current_user_can( 'install_plugins' ) ) {
			return;
		}

		// install date should be valid.
		$install_date = WPI()->get_install_date();
		if ( false === $install_date ) {
			return;
		}

		// at least 10 days should be past to display notice.
		if ( new DateTime( '10 days ago' ) >= $install_date ) {
			include WPI_DIR . '/includes/admin/views/html-rate-notice.php';
		}
	}

	/**
	 * Handles Ajax request to persist notices dismissal.
	 * Uses check_ajax_referer to verify nonce.
	 */
	public static function dismiss_notice() {
		$option_name        = sanitize_text_field( wp_unslash( $_POST['option_name'] ) );
		$dismissible_length = sanitize_text_field( wp_unslash( $_POST['dismissible_length'] ) );
		$transient          = 0;

		if ( 'forever' !== $dismissible_length ) {
			// If $dismissible_length is not an integer default to 1.
			$dismissible_length = ( 0 === absint( $dismissible_length ) ) ? 1 : $dismissible_length;
			$transient          = absint( $dismissible_length ) * DAY_IN_SECONDS;
			$dismissible_length = strtotime( absint( $dismissible_length ) . ' days' );
		}

		check_ajax_referer( 'dismiss-notice', 'nonce' );
		set_site_transient( $option_name, $dismissible_length, $transient );
		wp_die();
	}

	/**
	 * Is admin notice active?
	 *
	 * @param string $arg data-dismissible content of notice.
	 *
	 * @return bool
	 */
	public static function is_admin_notice_active( $arg ) {
		$array = explode( '-', $arg );
		array_pop( $array );
		$option_name = implode( '-', $array );
		$db_record   = get_site_transient( $option_name );

		if ( 'forever' === $db_record ) {
			return false;
		} elseif ( absint( $db_record ) >= time() ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Admin notice to configure plugin when activated.
	 */
	public static function admin_notice_activation() {
		// notice needs to be inactive.
		if ( ! self::is_admin_notice_active( 'activation-forever' ) ) {
			return;
		}

		// check if plugin has been activated by checking transient.
		if ( ! get_transient( 'bewpi-admin-notice-activation' ) ) {
			return;
		}

		include( WPI_DIR . '/includes/admin/views/html-activation-notice.php' );
		delete_transient( 'bewpi-admin-notice-activation' );
	}

	/**
	 * AJAX backend deactivation notice.
	 */
	public static function admin_notice_deactivation() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'deactivation-notice' ) ) { // Input var okay.
			die( 0 );
		}

		ob_start();
		include WPI_DIR . '/includes/admin/views/html-deactivation-notice.php';
		$content = ob_get_clean();
		die( $content ); // WPCS: XSS OK.
	}
}
