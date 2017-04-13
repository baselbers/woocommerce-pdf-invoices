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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BEWPI_Admin_Notices.
 */
class BEWPI_Admin_Notices {

	/**
	 * Constructor.
	 */
	public static function init() {
		add_action( 'wp_ajax_dismiss-notice', array( __CLASS__, 'dismiss_notice' ) );
		add_action( 'wp_ajax_deactivation-notice', array( __CLASS__, 'admin_notice_deactivation' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notice_rate' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notice_activation' ) );
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
	 * Get plugin install date.
	 *
	 * @return DateTime|bool
	 */
	private static function get_install_date() {
		if ( version_compare( WPI_VERSION, '2.6.1' ) >= 0 ) {
			// since 2.6.1+ option name changed and date has mysql format.
			return DateTime::createFromFormat( 'Y-m-d H:i:s', get_site_option( 'bewpi_install_date' ) );
		}

		return DateTime::createFromFormat( 'Y-m-d', get_site_option( 'bewpi-install-date' ) );
	}

	/**
	 * Admin notice for administrator to rate plugin on wordpress.org.
	 */
	public static function admin_notice_rate() {
		// notice needs to be inactive.
		if ( ! self::is_admin_notice_active( 'rate-forever' ) ) {
			return;
		}

		// user needs to be an administrator.
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// install date should be valid.
		$install_date = self::get_install_date();
		if ( false === $install_date ) {
			return;
		}

		// at least 10 days should be past to display notice.
		if ( new \DateTime( '10 days ago' ) >= $install_date ) {
			include( BEWPI_DIR . 'includes/admin/views/html-rate-notice.php' );
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

		include( BEWPI_DIR . 'includes/admin/views/html-activation-notice.php' );
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
		include BEWPI_DIR . 'includes/admin/views/html-deactivation-notice.php';
		$content = ob_get_clean();
		die( $content ); // WPCS: XSS OK.
	}
}

BEWPI_Admin_Notices::init();
