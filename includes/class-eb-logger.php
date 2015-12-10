<?php

/**
 * Allows log files to be written to for debugging purposes.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

class EB_Logger {

	/**
	 *
	 *
	 * @var array Stores open file _handles.
	 * @access private
	 */
	private $_handles;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 *
	 *
	 * @var EB_Course_Manager The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main EB_Logger Instance
	 *
	 * Ensures only one instance of EB_Logger is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see EB_Logger()
	 * @return EB_Logger - Main instance
	 */
	public static function instance( $plugin_name, $version ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $plugin_name, $version );
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since   1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'eb-textdomain' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since   1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'eb-textdomain' ), '1.0.0' );
	}

	/**
	 * Constructor for the logger.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->_handles = array();
	}


	/**
	 * Destructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		foreach ( $this->_handles as $handle ) {
			@fclose( escapeshellarg( $handle ) );
		}
	}


	/**
	 * Open log file for writing.
	 *
	 * @access private
	 * @param mixed   $handle
	 * @return bool success
	 */
	private function open( $handle ) {

		if ( isset( $this->_handles[ $handle ] ) ) {
			return true;
		}

		if ( $this->_handles[ $handle ] = @fopen( wdm_log_file_path( $handle ), 'a' ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Add a log entry to chosen file.
	 *
	 * @access public
	 * @param string  $handle
	 * @param string  $message
	 * @return void
	 */
	public function add( $handle, $message ) {
		if ( $this->open( $handle ) && is_resource( $this->_handles[ $handle ] ) ) {
			$time = date_i18n( 'm-d-Y @ H:i:s -' ); // Grab Time
			@fwrite( $this->_handles[ $handle ], $time . " " . $message . "\n" );
		}
	}


	/**
	 * Clear entries from chosen file.
	 *
	 * @access public
	 * @param mixed   $handle
	 * @return void
	 */
	public function clear( $handle ) {
		if ( $this->open( $handle ) && is_resource( $this->_handles[ $handle ] ) ) {
			@ftruncate( $this->_handles[ $handle ], 0 );
		}
	}

}
