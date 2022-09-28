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

		echo wp_json_encode( $response );
		die();
	}


	/**
	 * Test Enrolment between for a course with dummy user.
	 *
	 * .
	 *
	 * @since    1.0.0
	 */
	public function check_mandatory_settings() {
		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}
		$connection_helper = new EBConnectionHelper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connect_moodle_with_args_helper( 'edwiserbridge_local_get_mandatory_settings', array() );

		if( 403 === $response['status_code'] ) {
			$mdl_settings_link = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/local/edwiserbridge/edwiserbridge.php?tab=settings';
			$response_array = array(
				'status' => 'error',
				'message' => '<div class="alert alert-error">REST Protocol and Web Services should be enabled in moodle. Check Test Connection first and try again</div>',
				'html' => '<a target="_blank" href="' . $mdl_settings_link . '">Enable REST Protocol and Web Services</a>'
			);
		} elseif ( ! empty( $response['response_data'] ) ) {
			$data = $response['response_data'];
			error_log( print_r( $data, true ) );
			$general_settings = get_option( 'eb_general' );
			$language         = isset( $general_settings['eb_language_code'] ) ? $general_settings['eb_language_code'] : 'en';
			$msg = '';
			$flag = false;
			if ( 1 != $data->allow_extended_char ) {
				$flag = true;
				$msg .= '<div class="alert alert-error">Extended character in username should be enabled</div>';
			}
			if ( 0 != $data->password_policy ) {
				$flag = true;
				$msg .= '<div class="alert alert-error">Password Policy should be disabled</div>';
			}
			if ( $language !== $data->lang_code ){
				$flag = true;
				$msg .= '<div class="alert alert-error">Language code in edwiser settings should be same as in moodle</div>';
			}


			if ( $flag ) {
				$response_array = array(
					'status' => 'error',
					'message' => $msg,
					'html' => '<buton id="btn_set_mandatory" class="button button-secondary">Update mandatory settings & Continue</button>',
				);
			} else {
				$response_array = array(
					'status' => 'success',
					'message' => '<div class="alert alert-success">All Mandatory settings are up to mark</div>',
				);
			}
		} else {
			$response_array = array(
				'status' => 'error',
				'message' => '<div class="alert alert-error">Something went wrong. Try Test Connection. ERROR : ' . $response['response_message'] . '</div>',
			);
		}

		echo wp_json_encode( $response_array );
		die();
	}
	/**
	 * checks if the course is published and its tye is closed
	 */
	public function check_course_options() {
		$woo_integration_path = 'woocommerce-integration/bridge-woocommerce.php';
		$flag = false;
		$msg = '';
		$course_id = isset( $_POST['course_id'] ) ? sanitize_text_field( wp_unslash( $_POST['course_id'] ) ) : 0;
		if ( is_plugin_active( $woo_integration_path ) ) {
			
			$course_options = get_post_meta( $course_id, 'eb_course_options', true ) ;
			if ( isset( $course_options['course_price_type'] ) && 'closed' !== $course_options['course_price_type'] ) {
				$flag = true;
				$post_link = get_edit_post_link( $course_id );
				$msg .= '<div class="alert alert-warning"><span class="dashicons dashicons-warning" style="padding: 2px 6px 2px 0px;font-size: 22px;margin-left: -2px;"></span>Course Price type is not set to closed. It is recomended to be closed for woocommerce products. <a target="_blank" href="' . $post_link . '">Configure course price type</a></div>';
			}
		}
		if ( 'publish' !== get_post_status( $course_id ) ) {
			$flag = true;
			$msg .= '<div class="alert alert-error">Course should be published</div>';
		}

		if ( $flag ) {
			$response_array = array(
				'status' => 'error',
				'message' => $msg,
				'html' => '<buton id="btn_set_course_price_type" class="button button-secondary">Continue without chnage</button>',
			);
		} else {
			$response_array = array(
				'status' => 'success',
				'message' => '<div class="alert alert-success">All post options are up to mark</div>',
			);
		}

		echo wp_json_encode( $response_array );
		die();
	}

	/**
	 * Checks If Manual enrollment is enabled.
	 *
	 * @return JSON array
	 *
	 * @since    1.0.0
	 */
	public function check_manual_enrollment() {
		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}

		$response = edwiser_bridge_instance()->course_manager()->sync_course_enrollment_method();

		$course_id = isset( $_POST['course_id'] ) ? sanitize_text_field( wp_unslash( $_POST['course_id'] ) ) : 0;
		$moodle_course_id = get_post_meta( $course_id, 'moodle_course_id', true );

		if( ! isset( $response ) ){
			$response_array = array(
				'status' => 'error',
				'message' => '<div class="alert alert-error">Manual Enrollment method not enabled on Moodle Site</div>',
				'html' => '<buton id="btn_set_manual_enrol" class="button button-secondary">Enable & Continue</button>',
			);
		} else {
			foreach ( $response as $course ) {
				if ( $course->courseid == $moodle_course_id ) {
					$response_array = array(
						'status' => 'success',
						'message' => '<div class="alert alert-success">Manual Enrollment method enabled</div>',
					);
					break;
				} else {
					update_post_meta( $course_id, 'eb_course_manual_enrolment_enabled', 0 );
					$response_array = array(
						'status' => 'error',
						'message' => '<div class="alert alert-error">Manual Enrollment method not enabled</div>',
						'html' => '<buton id="btn_set_manual_enrol" class="button button-secondary">Enable & Continue</button>',
					);
				}
			}
		}
	
		echo wp_json_encode( $response_array );
		die();
	}


	/**
	 * Enables Manual enrollment.
	 *
	 * @return JSON array
	 *
	 * @since    1.0.0
	 */
	public function enable_manual_enrollment() {
		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}

		$course_id = isset( $_POST['course_id'] ) ? sanitize_text_field( wp_unslash( $_POST['course_id'] ) ) : 0;
		$moodle_course_id = get_post_meta( $course_id, 'moodle_course_id', true );
		$course_array = array(
			'courseid' => array( $moodle_course_id ),
		);
		$response = edwiser_bridge_instance()->course_manager()->edwiserbridge_local_update_course_enrollment_method( $course_array );

		if( isset( $response['success'] ) && 0 === $response['success'] ) {
			$response_array = array(
				'status' => 'error',
				'message' => '<div class="alert alert-error">Enabling Manual Enrollment method failed. ERROR: ' . $response['response_message'] . '</div>',
			);
			if( "Class 'enrol_manual_plugin' not found" === $response['response_message'] ) {
				$mdl_settings_link = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/admin/settings.php?section=manageenrols';
				$response_array['message'] .= '<div class="alert alert-error">Please enable manual enrolment plugin on Moodle Site </div>';
				$response_array['html'] = '<a target="_blank" href="' . $mdl_settings_link . '">Enable Manual Enrollment plugin</a>';
			}
		} else {
			update_post_meta( $course_id, 'eb_course_manual_enrolment_enabled', 1 );
			$response_array = array(
				'status' => 'success',
				'message' => '<div class="alert alert-success">Manual Enrollment method enabled</div>',
			);
		}

		echo wp_json_encode( $response_array );
		die();
	}

	/**
	 * Checks If Manual enrollment is enabled.
	 *
	 * @return JSON array
	 *
	 * @since    1.0.0
	 */
	public function enable_mandatory_settings() {
		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}

		$course_id = isset( $_POST['course_id'] ) ? sanitize_text_field( wp_unslash( $_POST['course_id'] ) ) : 0;
		$course_array = array(
			'courseid' => array( $course_id ),
		);
		$connection_helper = new EBConnectionHelper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connect_moodle_with_args_helper( 'edwiserbridge_local_enable_plugin_settings', array() );
		$general_settings = get_option( 'eb_general' );

		if ( ! empty( $response['response_data'] ) ) {
			$general_settings['eb_language_code'] = $response['response_data']->lang_code;
			update_option( 'eb_general', $general_settings );
		}
		if( isset( $response['success'] ) && 0 === $response['success'] ) {
			$response_array = array(
				'status' => 'error',
				'message' => '<div class="alert alert-error">Enabling Mandatory Settings failed. Try Test Connection first. ERROR: ' . $response['response_message'] . '</div>',
			);
		} else {
			$response_array = array(
				'status' => 'success',
				'message' => '<div class="alert alert-success">Mandatory Settings are up to mark</div>',
			);
		}
	
		echo wp_json_encode( $response_array );
		die();
	}
}
