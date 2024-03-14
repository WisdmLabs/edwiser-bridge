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
	 * Calls connection_test_helper() from Eb_Connection_Helper class.
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

		$connection_helper = new Eb_Connection_Helper( $this->plugin_name, $this->version );
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
		$connection_helper = new Eb_Connection_Helper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connect_moodle_with_args_helper( 'edwiserbridge_local_get_mandatory_settings', array() );

		if ( 403 === $response['status_code'] ) {
			$mdl_settings_link = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=settings';
			$response_array    = array(
				'status'  => 'error',
				'message' => '<div class="alert alert-error">' . __( 'REST Protocol and Web Services should be enabled in moodle. Check Test Connection first and try again', 'edwiser-bridge' ) . '</div>',
				'html'    => '<a target="_blank" href="' . $mdl_settings_link . '">' . __( 'Enable REST Protocol and Web Services', 'edwiser-bridge' ) . '</a>',
			);
		} elseif ( ! empty( $response['response_data'] ) ) {
			$data = $response['response_data'];

			$general_settings = get_option( 'eb_general' );
			$language         = isset( $general_settings['eb_language_code'] ) ? $general_settings['eb_language_code'] : 'en';
			$role_id          = isset( $general_settings['eb_moodle_role_id'] ) ? $general_settings['eb_moodle_role_id'] : 5;
			$msg              = '';
			$flag             = false;
			if ( 1 != $data->allow_extended_char ) { // @codingStandardsIgnoreLine
				$flag = true;
				$msg .= '<div class="alert alert-error">' . __( 'Extended character in username should be enabled', 'edwiser-bridge' ) . '</div>';
			}
			if ( 0 != $data->password_policy ) { // @codingStandardsIgnoreLine
				$flag = true;
				$msg .= '<div class="alert alert-error">' . __( 'Password Policy should be disabled', 'edwiser-bridge' ) . '</div>';
			}
			if ( $language !== $data->lang_code ) {
				$flag = true;
				$msg .= '<div class="alert alert-error">' . __( 'Language code in edwiser settings should be same as in moodle', 'edwiser-bridge' ) . '</div>';
			}
			if ( $role_id != $data->student_role_id ) { // @codingStandardsIgnoreLine
				$flag = true;
				$msg .= '<div class="alert alert-error">' . __( 'Default student role in edwiser settings should be same as in moodle', 'edwiser-bridge' ) . '</div>';
			}

			if ( $flag ) {
				$response_array = array(
					'status'  => 'error',
					'message' => $msg,
					'html'    => '<buton id="btn_set_mandatory" class="button button-secondary">' . __( 'Update mandatory settings & Continue', 'edwiser-bridge' ) . '</button>',
				);
			} else {
				$response_array = array(
					'status'  => 'success',
					'message' => '<div class="alert alert-success">' . __( 'All Mandatory settings are up to mark', 'edwiser-bridge' ) . '</div>',
				);
			}
		} else {
			$response_array = array(
				'status'  => 'error',
				'message' => '<div class="alert alert-error">' . __( 'Something went wrong. Try Test Connection. ERROR : ', 'edwiser-bridge' ) . $response['response_message'] . '</div>',
			);
			if ( strpos( $response['response_message'], 'external_functions' ) !== false ) {
				global $eb_plugin_data;
				$response_array['message'] = '<div class="alert alert-error">' . __( 'Something went wrong. Probably Moodle Edwiser Bridge plugin is not updated or installed properly. Please update the plugin and try again.', 'edwiser-bridge' ) . '</div>';
				$response_array['html']    = '<a target="_blank" href="' . $eb_plugin_data['mdl_plugin_url'] . '">' . __( 'Download latest plugin file', 'edwiser-bridge' ) . '</a>';
			}
			if ( \app\wisdmlabs\edwiserBridge\is_access_exception( $response ) ) {
				$mdl_settings_link      = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=service';
				$response_array['html'] = '<a target="_blank" href="' . $mdl_settings_link . '">' . __( 'Update webservice', 'edwiser-bridge' ) . '</a>' . __( ' OR ', 'edwiser-bridge' ) . '<a target="_blank" href="' . admin_url( '/admin.php?page=eb-settings&tab=connection' ) . '">' . __( 'Try test connection', 'edwiser-bridge' ) . '</a>';
			}
		}

		echo wp_json_encode( $response_array );
		die();
	}
	/**
	 * Checks if the course is published and its tye is closed.
	 */
	public function check_course_options() {
		$pro_module_option = get_option( 'eb_pro_modules_data' );
		$flag              = false;
		$msg               = '';

		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}
		$course_id = isset( $_POST['course_id'] ) ? sanitize_text_field( wp_unslash( $_POST['course_id'] ) ) : 0;
		if ( isset( $pro_module_option['woo_integration'] ) && 'active' === $pro_module_option['woo_integration'] ) {
			$course_options = get_post_meta( $course_id, 'eb_course_options', true );
			if ( isset( $course_options['course_price_type'] ) && 'closed' !== $course_options['course_price_type'] ) {
				$flag      = true;
				$post_link = get_edit_post_link( $course_id );
				$msg      .= '<div class="alert alert-warning"><span class="dashicons dashicons-warning" style="padding: 2px 6px 2px 0px;font-size: 22px;margin-left: -2px;"></span>' . __( 'Course Price type is not set to closed. It is recomended to be closed for woocommerce products.', 'edwiser-bridge' ) . ' <a target="_blank" href="' . $post_link . '">' . __( 'Configure course price type', 'edwiser-bridge' ) . '</a></div>';
			}

			global $wpdb;
			// $query = 'SELECT `product_id` FROM ' . $wpdb->prefix . "eb_moodle_course_products WHERE `moodle_post_id` = '" . $course_id . "'";
			$query = $wpdb->prepare( "SELECT `product_id` FROM {$wpdb->prefix}eb_moodle_course_products WHERE `moodle_post_id` = %d", $course_id );

			$product_id = $wpdb->get_var( $query ); // @codingStandardsIgnoreLine

			// check if product is virtual and downloadable.
			if ( $product_id ) {
				$product = wc_get_product( $product_id );
				if ( ! $product->is_virtual() ) {
					$flag      = true;
					$post_link = get_edit_post_link( $product_id );
					$msg      .= '<div class="alert alert-warning"><span class="dashicons dashicons-warning" style="padding: 2px 6px 2px 0px;font-size: 22px;margin-left: -2px;"></span>' . __( 'Product is not virtual. It is recomended to be virtual for woocommerce products.', 'edwiser-bridge' ) . ' <a target="_blank" href="' . $post_link . '">' . __( 'Configure product', 'edwiser-bridge' ) . '</a></div>';
				}
				if ( ! $product->is_downloadable() ) {
					$flag      = true;
					$post_link = get_edit_post_link( $product_id );
					$msg      .= '<div class="alert alert-warning"><span class="dashicons dashicons-warning" style="padding: 2px 6px 2px 0px;font-size: 22px;margin-left: -2px;"></span>' . __( 'Product is not downloadable. It is recomended to be downloadable for woocommerce products.', 'edwiser-bridge' ) . ' <a target="_blank" href="' . $post_link . '">' . __( 'Configure product', 'edwiser-bridge' ) . '</a></div>';
				}
			}
		}
		if ( 'publish' !== get_post_status( $course_id ) ) {
			$flag = true;
			$msg .= '<div class="alert alert-error">' . __( 'Course should be published', 'edwiser-bridge' ) . '</div>';
		}

		if ( $flag ) {
			$response_array = array(
				'status'  => 'error',
				'message' => $msg,
				'html'    => '<buton id="btn_set_course_price_type" class="button button-secondary">' . __( 'Continue without change', 'edwiser-bridge' ) . '</button>',
			);
		} else {
			$response_array = array(
				'status'  => 'success',
				'message' => '<div class="alert alert-success">' . __( 'All post options are up to mark', 'edwiser-bridge' ) . '</div>',
			);
		}

		echo wp_json_encode( $response_array );
		die();
	}

	/**
	 * Checks If Manual enrollment is enabled.
	 *
	 * @since    1.0.0
	 */
	public function check_manual_enrollment() {
		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}

		$response = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper( 'edwiserbridge_local_get_course_enrollment_method', array() );

		$course_id        = isset( $_POST['course_id'] ) ? sanitize_text_field( wp_unslash( $_POST['course_id'] ) ) : 0;
		$moodle_course_id = get_post_meta( $course_id, 'moodle_course_id', true );

		if ( 0 === $response['success'] ) {
			$response_array ['status'] = 'error';
			$response_array['message'] = '<div class="alert alert-error">' . __( 'Manual Enrollment method check failed. ERROR : ', 'edwiser-bridge' ) . $response['response_message'] . '</div>';
			if ( \app\wisdmlabs\edwiserBridge\is_access_exception( $response ) ) {
				$mdl_settings_link      = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=service';
				$response_array['html'] = '<a target="_blank" href="' . $mdl_settings_link . '">' . __( 'Update webservice', 'edwiser-bridge' ) . '</a>' . __( ' OR ', 'edwiser-bridge' ) . '<a target="_blank" href="' . admin_url( '/admin.php?page=eb-settings&tab=connection' ) . '">' . __( 'Try test connection', 'edwiser-bridge' ) . '</a>';
			} else {
				$response_array = array(
					'status'  => 'error',
					'message' => '<div class="alert alert-error">' . __( 'Manual Enrollment method not enabled on Moodle Site', 'edwiser-bridge' ) . '</div>',
					'html'    => '<buton id="btn_set_manual_enrol" class="button button-secondary">' . __( 'Enable & Continue', 'edwiser-bridge' ) . '</button>',
				);
			}
		} elseif ( isset( $response['response_data'] ) ) {
			foreach ( $response['response_data'] as $course ) {
				if ( $course->courseid == $moodle_course_id ) { // @codingStandardsIgnoreLine
					update_post_meta( $course_id, 'eb_course_manual_enrolment_enabled', 1 );
					$response_array = array(
						'status'  => 'success',
						'message' => '<div class="alert alert-success">' . __( 'Manual Enrollment method enabled', 'edwiser-bridge' ) . '</div>',
					);
					break;
				} else {
					$response_array = array(
						'status'  => 'error',
						'message' => '<div class="alert alert-error">' . __( 'Manual Enrollment method not enabled', 'edwiser-bridge' ) . '</div>',
						'html'    => '<buton id="btn_set_manual_enrol" class="button button-secondary">' . __( 'Enable & Continue', 'edwiser-bridge' ) . '</button>',
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
	 * @since    1.0.0
	 */
	public function enable_manual_enrollment() {
		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}

		$course_id        = isset( $_POST['course_id'] ) ? sanitize_text_field( wp_unslash( $_POST['course_id'] ) ) : 0;
		$moodle_course_id = get_post_meta( $course_id, 'moodle_course_id', true );
		$course_array     = array(
			'courseid' => array( $moodle_course_id ),
		);
		$response         = edwiser_bridge_instance()->course_manager()->edwiserbridge_local_update_course_enrollment_method( $course_array );

		if ( isset( $response['success'] ) && 0 === $response['success'] ) {
			$response_array = array(
				'status'  => 'error',
				'message' => '<div class="alert alert-error">' . __( 'Enabling Manual Enrollment method failed. ERROR: ', 'edwiser-bridge' ) . $response['response_message'] . '</div>',
			);
			if ( \app\wisdmlabs\edwiserBridge\is_access_exception( $response ) ) {
				$mdl_settings_link      = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=service';
				$response_array['html'] = '<a target="_blank" href="' . $mdl_settings_link . '">' . __( 'Update webservice', 'edwiser-bridge' ) . '</a>' . __( ' OR ', 'edwiser-bridge' ) . '<a target="_blank" href="' . admin_url( '/admin.php?page=eb-settings&tab=connection' ) . '">' . __( 'Try test connection', 'edwiser-bridge' ) . '</a>';
			}
			if ( 'plugin_not_installed' === $response['response_message'] ) {
				$mdl_settings_link          = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/admin/settings.php?section=manageenrols';
				$response_array['message'] .= '<div class="alert alert-error">' . __( 'Please enable manual enrolment plugin on Moodle Site', 'edwiser-bridge' ) . ' </div>';
				$response_array['html']     = '<a target="_blank" href="' . $mdl_settings_link . '">' . __( 'Enable Manual Enrollment plugin', 'edwiser-bridge' ) . '</a>';
			}
		} else {
			$course_data = $response[0];
			if ( isset( $course_data->message ) && 'plugin_not_installed' === $course_data->message ) {
				$mdl_settings_link = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/admin/settings.php?section=manageenrols';
				$response_array    = array(
					'status'  => 'error',
					'message' => '<div class="alert alert-error">' . __( 'Please enable manual enrolment plugin on Moodle Site', 'edwiser-bridge' ) . ' </div>',
					'html'    => '<a target="_blank" href="' . $mdl_settings_link . '">' . __( 'Enable Manual Enrollment plugin', 'edwiser-bridge' ) . '</a>',
				);
			} else {
				update_post_meta( $course_id, 'eb_course_manual_enrolment_enabled', 1 );
				$response_array = array(
					'status'  => 'success',
					'message' => '<div class="alert alert-success">' . __( 'Manual Enrollment method enabled', 'edwiser-bridge' ) . '</div>',
				);
			}
		}

		echo wp_json_encode( $response_array );
		die();
	}

	/**
	 * Checks If Manual enrollment is enabled.
	 *
	 * @since    1.0.0
	 */
	public function enable_mandatory_settings() {
		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			die( 'Busted!' );
		}

		$course_id         = isset( $_POST['course_id'] ) ? sanitize_text_field( wp_unslash( $_POST['course_id'] ) ) : 0;
		$course_array      = array(
			'courseid' => array( $course_id ),
		);
		$connection_helper = new Eb_Connection_Helper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connect_moodle_with_args_helper( 'edwiserbridge_local_enable_plugin_settings', array() );
		$general_settings  = get_option( 'eb_general' );

		if ( ! empty( $response['response_data'] ) ) {
			$general_settings['eb_language_code']  = $response['response_data']->lang_code;
			$general_settings['eb_moodle_role_id'] = $response['response_data']->student_role_id;
			update_option( 'eb_general', $general_settings );
		}
		if ( isset( $response['success'] ) && 0 === $response['success'] ) {
			$response_array = array(
				'status'  => 'error',
				'message' => '<div class="alert alert-error">' . __( 'Enabling Mandatory Settings failed. Try Test Connection first. ERROR: ', 'edwiser-bridge' ) . '' . $response['response_message'] . '</div>',
			);
			if ( \app\wisdmlabs\edwiserBridge\is_access_exception( $response ) ) {
				$mdl_settings_link      = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=service';
				$response_array['html'] = '<a target="_blank" href="' . $mdl_settings_link . '">' . __( 'Update webservice', 'edwiser-bridge' ) . '</a>' . __( ' OR ', 'edwiser-bridge' ) . '<a target="_blank" href="' . admin_url( '/admin.php?page=eb-settings&tab=connection' ) . '">' . __( 'Try test connection', 'edwiser-bridge' ) . '</a>';
			}
		} else {
			$response_array = array(
				'status'  => 'success',
				'message' => '<div class="alert alert-success">' . __( 'Mandatory Settings are up to mark', 'edwiser-bridge' ) . '</div>',
			);
		}
		echo wp_json_encode( $response_array );
		die();
	}

	/**
	 * Ajax callback to get error log data for given id
	 */
	public function eb_get_log_data() {
		$response = esc_html__( 'Error log not found', 'edwiser-bridge' );
		if ( isset( $_POST['key'] ) && isset( $_POST['action'] ) && 'wdm_eb_get_log_data' === $_POST['action'] && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {

			$key      = sanitize_text_field( wp_unslash( $_POST['key'] ) );
			$log_file = wdm_edwiser_bridge_plugin_log_dir() . 'log.json';
			$logs     = file_get_contents( $log_file ); // @codingStandardsIgnoreLine
			$logs     = json_decode( $logs, true );

			if ( ! is_array( $logs ) ) {
				wp_send_json_error( $response );
			} else {
				if ( isset( $logs[ $key ] ) ) {
					$response = $logs[ $key ];
					wp_send_json_success( $response );
				} else {
					wp_send_json_error( $response );
				}
			}
		} else {
			wp_send_json_error( $response );
		}
	}

	/**
	 * Ajax callback to mark error log resolved
	 */
	public function eb_log_resolved() {
		$response = esc_html__( 'Error log not found', 'edwiser-bridge' );
		if ( isset( $_POST['key'] ) && isset( $_POST['action'] ) && 'wdm_eb_mark_log_resolved' === $_POST['action'] && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {

			// get error log file.
			$key      = sanitize_text_field( wp_unslash( $_POST['key'] ) );
			$log_file = wdm_edwiser_bridge_plugin_log_dir() . 'log.json';
			$logs     = file_get_contents( $log_file ); // @codingStandardsIgnoreLine
			$logs     = json_decode( $logs, true );

			// get resolved error log file for this month.
			$resolved_log_file = wdm_edwiser_bridge_plugin_log_dir() . 'log-' . date( 'm-y' ) . '.json'; // @codingStandardsIgnoreLine
			if ( file_exists( $resolved_log_file ) ) {
				$resolved_logs = file_get_contents( $resolved_log_file ); // @codingStandardsIgnoreLine
				$resolved_logs = json_decode( $resolved_logs, true );
			} else {
				$resolved_logs = array();
			}

			if ( ! is_array( $logs ) ) {
				wp_send_json_error( $response );
			} else {
				$logs[ $key ]['status'] = 'RESOLVED';

				if ( ! is_array( $resolved_logs ) ) {
					$resolved_logs = array();
				}
				$resolved_logs[] = $logs[ $key ];
				unset( $logs[ $key ] );

				$logs          = wp_json_encode( $logs );
				$resolved_logs = wp_json_encode( $resolved_logs );
				file_put_contents( $log_file, $logs ); // @codingStandardsIgnoreLine
				file_put_contents( $resolved_log_file, $resolved_logs ); // @codingStandardsIgnoreLine
				wp_send_json_success();
			}
		} else {
			wp_send_json_error( $response );
		}
	}

	/**
	 * Ajax callback to delete error log
	 */
	public function eb_send_log_to_support() {
		$response = esc_html__( 'Failed', 'edwiser-bridge' );
		if ( isset( $_POST['key'] ) && isset( $_POST['action'] ) && 'send_log_to_support' === $_POST['action'] && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {

			$key = sanitize_text_field( wp_unslash( $_POST['key'] ) );
			if ( isset( $_POST['email'] ) ) {
				$email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
			} else {
				$email = get_option( 'admin_email' );
			}
			$email    = sanitize_text_field( wp_unslash( $_POST['email'] ) );
			$log_file = wdm_edwiser_bridge_plugin_log_dir() . 'log.json';
			$logs     = file_get_contents( $log_file ); // @codingStandardsIgnoreLine
			$logs     = json_decode( $logs, true );

			if ( ! is_array( $logs ) ) {
				wp_send_json_error( $response );
			} else {
				$log_data = $logs[ $key ]['data'];
				// send mail to support.
				// get site name and url for subject.
				$site_name = get_option( 'blogname' );
				$site_url  = get_option( 'siteurl' );
				$subject   = 'Error Log From : ' . $site_name . ' - ' . $site_url;
				$message   = '<p>' . esc_html__( 'Error log details', 'edwiser-bridge' ) . '</p>';
				if ( isset( $email ) ) {
					$message .= '<p>' . esc_html__( 'Support Email', 'edwiser-bridge' ) . ' : ' . $email . '</p>';
				}
				$message .= '<p>' . esc_html__( 'Error log message', 'edwiser-bridge' ) . ': ' . $log_data['message'] . '</p>';
				$message .= '<p>' . esc_html__( 'URL', 'edwiser-bridge' ) . ': ' . $log_data['url'] . '</p>';
				$message .= '<p>' . esc_html__( 'HTTP Response Code', 'edwiser-bridge' ) . ': ' . $log_data['responsecode'] . '</p>';
				$message .= '<p>' . esc_html__( 'User', 'edwiser-bridge' ) . ': ' . $log_data['user'] . '</p>';
				$message .= '<p>' . esc_html__( 'Exception', 'edwiser-bridge' ) . ': ' . $log_data['exception'] . '</p>';
				$message .= '<p>' . esc_html__( 'Error Code', 'edwiser-bridge' ) . ': ' . $log_data['errorcode'] . '</p>';
				if ( isset( $log_data['debuginfo'] ) ) {
					$message .= '<p>' . esc_html__( 'Debug Info', 'edwiser-bridge' ) . ': ' . $log_data['debuginfo'] . '</p>';
				}
				if ( isset( $log_data['backtrace'] ) ) {
					$message .= '<pre>' . esc_html__( 'Backtrace', 'edwiser-bridge' ) . ': ' . print_r( $log_data['backtrace'], true ) . '</pre>'; // @codingStandardsIgnoreLine
				}

				$headers       = array( 'Content-Type: text/html; charset=UTF-8' );
				$support_email = 'edwiser@wisdmlabs.com';

				$mail_sent = wp_mail( $support_email, $subject, $message, $headers );
				if ( $mail_sent ) {
					$response = esc_html__( 'Mail sent successfully', 'edwiser-bridge' );

					// add status in error log file.
					$logs[ $key ]['status'] = 'SENT TO SUPPORT';

					$logs = wp_json_encode( $logs );
					file_put_contents( $log_file, $logs ); // @codingStandardsIgnoreLine

					wp_send_json_success( $response );
				} else {
					wp_send_json_error( $response );
				}
			}
		} else {
			wp_send_json_error( $response );
		}
	}
}
