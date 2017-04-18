<?php
/**
 * Simple logging class that writes to wp-content/debug.log file.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     0.0.1
 */

/**
 * Class BEWPI_Debug_Log.
 */
class BEWPI_Debug_Log {

	/** Main instance.
	 *
	 * @var BEWPI_Debug_Log The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Detailed debug information
	 */
	const DEBUG = 100;

	/**
	 * Interesting events
	 *
	 * Examples: Visitor subscribed
	 */
	const INFO = 200;

	/**
	 * Exceptional occurrences that are not errors
	 *
	 * Examples: User already subscribed
	 */
	const WARNING = 300;

	/**
	 * Runtime errors
	 */
	const ERROR = 400;

	/**
	 * Logging levels from syslog protocol defined in RFC 5424
	 *
	 * @var array $levels Logging levels
	 */
	protected static $levels = array(
		self::DEBUG     => 'DEBUG',
		self::INFO      => 'INFO',
		self::WARNING   => 'WARNING',
		self::ERROR     => 'ERROR',
	);

	/**
	 * Main BEWPI_Debug_Log Instance.
	 *
	 * Ensures only one instance of BEWPI_Debug_Log is loaded or can be loaded.
	 *
	 * @since 2.7.2
	 * @static
	 * @return BEWPI_Debug_Log Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Log to wp-content/debug.log file.
	 *
	 * @param string $level Log level.
	 * @param string $message Log message.
	 *
	 * @return bool true on success and false on failure.
	 */
	private function log( $level, $message ) {
		$level = self::to_level( $level );

		// Generate line.
		$level_name = self::get_level_name( $level );
		$message = sprintf( 'WooCommerce PDF Invoices %s: %s', $level_name, $message );

		return error_log( $message );
	}

	/**
	 * Log warning.
	 *
	 * @param string $message Log message.
	 *
	 * @return bool true on success and false on failure.
	 */
	public function warning( $message ) {
		return $this->log( self::WARNING, $message );
	}

	/**
	 * Log information.
	 *
	 * @param string $message Log message.
	 *
	 * @return bool true on success and false on failure.
	 */
	public function info( $message ) {
		return $this->log( self::INFO, $message );
	}

	/**
	 * Log error.
	 *
	 * @param string $message Log message.
	 *
	 * @return bool true on success and false on failure.
	 */
	public function error( $message ) {
		return $this->log( self::ERROR, $message );
	}

	/**
	 * Log debug information.
	 *
	 * @param string $message Log message.
	 *
	 * @return bool true on success and false on failure.
	 */
	public function debug( $message ) {
		return $this->log( self::DEBUG, $message );
	}

	/**
	 * Converts PSR-3 levels to local ones if necessary.
	 *
	 * @param string|int $level Level number or name (PSR-3).
	 *
	 * @return int
	 */
	public static function to_level( $level ) {

		if ( is_string( $level ) ) {

			$level = strtoupper( $level );
			if ( defined( __CLASS__ . '::' . $level ) ) {
				return constant( __CLASS__ . '::' . $level );
			}

			throw new InvalidArgumentException( 'Level "' . $level . '" is not defined, use one of: ' . implode( ', ', array_keys( self::$levels ) ) );
		}

		return $level;
	}

	/**
	 * Gets the name of the logging level.
	 *
	 * @param  int $level Log level.
	 *
	 * @return string
	 */
	public static function get_level_name( $level ) {

		if ( ! isset( self::$levels[ $level ] ) ) {
			throw new InvalidArgumentException( 'Level "' . $level . '" is not defined, use one of: ' . implode( ', ', array_keys( self::$levels ) ) );
		}

		return self::$levels[ $level ];
	}
}
