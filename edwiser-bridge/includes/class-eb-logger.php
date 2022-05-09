<?php
/**
 * Allows log files to be written to for debugging purposes.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Eb logger.
 */
class Eb_Logger {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The current version of this plugin.
	 */
	private $version;

	/**
	 * Instance.
	 *
	 * @var EB_Course_Manager The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main Eb_Logger Instance.
	 *
	 * Ensures only one instance of Eb_Logger is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @param text $plugin_name plugin_name.
	 * @param text $version version.
	 * @see Eb_Logger()
	 *
	 * @return Eb_Logger - Main instance
	 */
	public static function instance( $plugin_name, $version ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $plugin_name, $version );
		}

		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since   1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'edwiser-bridge' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since   1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'edwiser-bridge' ), '1.0.0' );
	}

	/**
	 * Constructor for the logger.
	 *
	 * @param text $plugin_name plugin_name.
	 * @param text $version version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->_handles    = array();
	}

	/**
	 * Destructor.
	 */
	public function __destruct() {
		foreach ( $this->_handles as $handle ) {
			if ( is_string( $handle ) ) {
				fclose( escapeshellarg( $handle ) ); // @codingStandardsIgnoreLine
			}
		}
	}

	/**
	 * Open log file for writing.
	 *
	 * @param mixed $handle handle.
	 *
	 * @return bool success
	 */
	private function open( $handle ) {
		if ( isset( $this->_handles[ $handle ] ) ) {
			return true;
		}

		$this->_handles[ $handle ] = fopen( \app\wisdmlabs\edwiserBridge\wdm_eb_log_file_path( $handle ), 'a' ); // @codingStandardsIgnoreLine

		if ( $this->_handles[ $handle ] ) {
			return true;
		}

		return false;
	}

	/**
	 * Add a log entry to chosen file.
	 *
	 * @param string $handle handle.
	 * @param string $message message.
	 */
	public function add( $handle, $message ) {
		if ( $this->open( $handle ) && is_resource( $this->_handles[ $handle ] ) ) {
			$time = date_i18n( 'm-d-Y @ H:i:s -' ); // Grab Time.
			fwrite( $this->_handles[ $handle ], $time . ' ' . $message . "\n" ); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Clear entries from chosen file.
	 *
	 * @param mixed $handle handle.
	 */
	public function clear( $handle ) {
		if ( $this->open( $handle ) && is_resource( $this->_handles[ $handle ] ) ) {
			ftruncate( $this->_handles[ $handle ], 0 );
		}
	}
}
