<?php
/**
 * This class contains functionality to handle actions of custom buttons implemented in settings page
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Ajax initiater.
 */
class Eb_Settings_Ajax_Initiater {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Contsructor.
	 *
	 * @param text $plugin_name plugin_name.
	 * @param text $version version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Initiate course synchronization process.
	 *
	 * @since    1.0.0
	 */
	public function course_synchronization_initiater() {

		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}

		$sync_options = isset( $_POST['sync_options'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_options'] ) ) : array();
		$sync_options = (array) json_decode( $sync_options );

		// start working on request.
		$response = edwiser_bridge_instance()->course_manager()->course_synchronization_handler( $sync_options );

		echo wp_json_encode( $response );
		die();
	}

	/**
	 * Initiate user data synchronization process.
	 *
	 * @since    1.0.0
	 */
	public function user_data_synchronization_initiater() {

		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}
		// Added offset for user get limit.
		$offset = isset( $_POST['offset'] ) ? sanitize_text_field( wp_unslash( $_POST['offset'] ) ) : 0;

		$sync_options = isset( $_POST['sync_options'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_options'] ) ) : array();
		$sync_options = (array) json_decode( $sync_options );

		$response = edwiser_bridge_instance()->user_manager()->user_course_synchronization_handler( $sync_options, false, $offset );

		echo wp_json_encode( $response );
		die();
	}
	/**
	 * Initiate user link to moodle synchronization process.
	 *
	 * @since    1.4.1
	 */
	public function users_link_to_moodle_synchronization() {

		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}
		// Added offset for user get limit.
		$offset = isset( $_POST['offset'] ) ? sanitize_text_field( wp_unslash( $_POST['offset'] ) ) : 0;
		// get sync options.
		$sync_options = isset( $_POST['sync_options'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_options'] ) ) : array();
		$sync_options = (array) json_decode( $sync_options );

		$response = edwiser_bridge_instance()->user_manager()->user_link_to_moodle_handler( $sync_options, $offset );

		echo wp_json_encode( $response );
		die();
	}

	/**
	 * Test connection between WordPress and moodle.
	 *
	 * Calls connection_test_helper() from EBConnectionHelper class.
	 *
	 * @since    1.0.0
	 */
	public function connection_test_initiater() {

		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}

		// start working on request.
		$url   = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

		$connection_helper = new EBConnectionHelper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connection_test_helper( $url, $token );

		if ( 0 === $response['success'] ) {
			$response['response_message'] .= esc_html__( ' : to know more about this error', 'eb-textdomain' ) . "<a href='https://edwiser.helpscoutdocs.com/collection/85-edwiser-bridge-plugin' target='_blank'>" . esc_html__( ' click here', 'eb-textdomain' ) . '</a>';
		}

		echo wp_json_encode( $response );
		die();
	}
}
