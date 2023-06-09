<?php
/**
 * This class works as a connection helper to connect with Moodle webservice API.
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
 * Esternal API.
 */
class Eb_External_Api_Endpoint {

	/**
	 * This method registers the webservice endpointr.
	 */
	public function api_registration() {
		register_rest_route(
			'edwiser-bridge',
			'/wisdmlabs/',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'external_api_endpoint_def' ),
				'permission_callback' => '__return_true',
			)
		);
	}


	/**
	 * Functionality to validate the secret key from Moodle with WP.
	 *
	 * @param  text $request_data request Data.
	 */
	public function eb_validate_api_key( $request_data ) {
		$wp_token  = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();
		$valid_key = false;
		if ( isset( $request_data['secret_key'] ) && ! empty( $request_data['secret_key'] ) && $wp_token === $request_data['secret_key'] ) {
			$valid_key = true;
		}
		return $valid_key;
	}



	/**
	 * This function parse the request coming from
	 *
	 * @param  text $request_data request Data.
	 */
	public function external_api_endpoint_def( $request_data ) {
		$response_data = array();

		// CHeck if key is valid.
		if ( isset( $request_data['action'] ) && ! empty( $request_data['action'] ) && $this->eb_validate_api_key( $request_data ) ) {
			$action = sanitize_text_field( wp_unslash( $request_data['action'] ) );

			switch ( $action ) {
				case 'test_connection':
					$response_data = $this->eb_test_connection( $request_data );
					break;

				case 'course_enrollment':
					$response_data = $this->eb_course_enrollment( $request_data, 0 );
					break;

				case 'course_un_enrollment':
					$response_data = $this->eb_course_enrollment( $request_data, 1 );
					break;

				case 'user_creation':
					$response_data = $this->eb_trigger_user_creation( $request_data );
					break;

				case 'user_deletion':
					$response_data = $this->eb_trigger_user_delete( $request_data );
					break;

				case 'user_updated':
					$response_data = $this->eb_trigger_user_update( $request_data );
					break;

				case 'course_created':
					$response_data = $this->eb_trigger_course_creation( $request_data );
					break;

				case 'course_deleted':
					$response_data = $this->eb_trigger_course_delete( $request_data );
					break;

				default:
					// Apply filter here for more default options.
					break;
			}
		} elseif ( ! $this->eb_validate_api_key( $request_data ) ) {
			$response_data = array(
				'status' => 0,
				'msg'    => 'Invalid token please check token',
			);
		}
		return $response_data;
	}


	/**
	 * Function to test connection for the request from Moodle.
	 *
	 * @param  data $data data.
	 */
	protected function eb_test_connection( $data ) {
		$status = 0;

		if ( isset( $data['secret_key'] ) ) {
			$settings = maybe_unserialize( get_option( 'eb_connection' ) );
			if ( ! isset( $settings['eb_access_token'] ) ) {
				$msg = 'Please save connection settings on Worpdress';
			} elseif ( $settings['eb_access_token'] !== $data['secret_key'] ) {
				$msg    = 'Invalid token please check token';
				$status = 0;
			} else {
				$msg    = 'Test connection successful';
				$status = 1;
			}
		}
		return array(
			'status' => $status,
			'msg'    => $msg,
		);
	}


	/**
	 * Function to enroll or unenroll from the course for the request coming from Moodle
	 *
	 * @param  [type] $data     data.
	 * @param  [type] $un_enroll un_enroll.
	 * @return [type]           [description]
	 */
	protected function eb_course_enrollment( $data, $un_enroll ) {
		if ( isset( $data['user_id'] ) && isset( $data['course_id'] ) ) {
			$mdl_course_id = $data['course_id'];
			$wp_course_id  = \app\wisdmlabs\edwiserBridge\wdm_eb_get_wp_course_id_from_moodle_course_id( $data['course_id'] );

			if ( $wp_course_id ) {
				$mdl_user_id = $data['user_id'];
				$wp_user_id  = \app\wisdmlabs\edwiserBridge\wdm_eb_get_wp_user_id_from_moodle_id( $data['user_id'] );
				if ( ! $wp_user_id && empty( $wp_user_id ) && 0 === $un_enroll ) {
					$role = \app\wisdmlabs\edwiserBridge\wdm_eb_default_registration_role();

					// Check if user is available with same email address.
					$eb_user = get_user_by( 'email', $data['email'] );

					if ( $eb_user && isset( $eb_user->ID ) ) {
						$wp_user_id = $eb_user->ID;
					} else {
						$wp_user_id = $this->create_only_wp_user( $data['user_name'], $data['email'], $data['first_name'], $data['last_name'], $role );
					}
					update_user_meta( $wp_user_id, 'moodle_user_id', $mdl_user_id );
				}

				if ( $wp_user_id ) {
					$user = get_user_by( 'ID', $wp_user_id );

					$args = array(
						'user_id'  => $wp_user_id,
						'role_id'  => 5,
						'courses'  => array( $wp_course_id ),
						'unenroll' => $un_enroll,
						'suspend'  => 0,
					);

					// check if there any pending enrollments for the given course then don't enroll user.
					$user_enrollment_meta = get_user_meta( $wp_user_id, 'eb_pending_enrollment', 1 );

					if ( is_array( $user_enrollment_meta ) && in_array( $wp_course_id, $user_enrollment_meta ) ) { // @codingStandardsIgnoreLine
						return;
					}

					$args['complete_unenroll'] = 0;
					if ( $un_enroll ) {
						$args['complete_unenroll'] = 1;
					}

					edwiser_bridge_instance()->enrollment_manager()->update_enrollment_record_wordpress( $args );

					$args = array(
						'user_email' => $user->user_email,
						'username'   => $user->user_login,
						'first_name' => $user->first_name,
						'last_name'  => $user->last_name,
						'course_id'  => $wp_course_id,
					);
					if ( $un_enroll ) {
						do_action( 'eb_mdl_un_enrollment_trigger', $args );
					} else {

						do_action( 'eb_mdl_enrollment_trigger', $args );
					}
				}
			}
		}
	}



	/**
	 * Function to create user for the user creation request coming from Moodle.
	 *
	 * @param text $data data.
	 */
	public function eb_trigger_user_creation( $data ) {
		if ( isset( $data['user_name'] ) && isset( $data['email'] ) ) {
			$role            = \app\wisdmlabs\edwiserBridge\wdm_eb_default_registration_role();
			$eb_access_token = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();
			$user_p          = '';
			if ( isset( $data['password'] ) && ! empty( $data['password'] ) ) {

				$enc_method = 'AES-128-CTR';
				$enc_iv     = substr( hash( 'sha256', $eb_access_token ), 0, 16 );
				$enc_key    = openssl_digest( $eb_access_token, 'SHA256', true );
				$user_p     = openssl_decrypt( $data['password'], $enc_method, $enc_key, 0, $enc_iv );
			}

			$wp_user_id = $this->create_only_wp_user( $data['user_name'], $data['email'], $data['first_name'], $data['last_name'], $role, $user_p );
			if ( $wp_user_id ) {
				update_user_meta( $wp_user_id, 'moodle_user_id', $data['user_id'] );

				// user creation hook.
				do_action( 'eb_user_created_from_moodle', $wp_user_id, $data );
			}
		}
	}


	/**
	 * Function to delete user for the user deletion request coming from Moodle.
	 *
	 * @param  text $data data.
	 */
	public function eb_trigger_user_delete( $data ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
		if ( isset( $data['user_id'] ) ) {
			$wp_user_id = \app\wisdmlabs\edwiserBridge\wdm_eb_get_wp_user_id_from_moodle_id( $data['user_id'] );
			if ( $wp_user_id ) {
				$deleted = wp_delete_user( $wp_user_id );
			}
		}
	}



	/**
	 * Functinality to create only WordPress user.
	 *
	 * @param  text   $username  username.
	 * @param  text   $email     email.
	 * @param  text   $firstname firstname.
	 * @param  text   $lastname  lastname.
	 * @param  text   $role  role.
	 * @param  string $user_p    password.
	 */
	public function create_only_wp_user( $username, $email, $firstname, $lastname, $role = '', $user_p = '' ) {
		$uc_status = new \WP_Error(
			'registration-error',
			esc_html__( 'An account is already registered with your email address. Please login.', 'edwiser-bridge' ),
			'eb_email_exists'
		);
		if ( email_exists( $email ) ) {
			$uc_status = new \WP_Error(
				'registration-error',
				esc_html__( 'An account is already registered with your email address. Please login.', 'edwiser-bridge' ),
				'eb_email_exists'
			);
		} else {

			// Ensure username is unique.
			$append     = 1;
			$o_username = $username;

			while ( username_exists( $username ) ) {
				$username = $o_username . $append;
				++$append;
			}

			if ( empty( $user_p ) ) {
				// Handle password creation.
				$user_p = wp_generate_password();
			}

			// WP Validation.
			$validation_errors = new \WP_Error();

			if ( $validation_errors->get_error_code() ) {
				$uc_status = $validation_errors;
			} else {

				// Added after 1.3.4.
				if ( '' === $role ) {
					$role = get_option( 'default_role' );
				}

				$wp_user_data = apply_filters(
					'eb_new_user_data',
					array(
						'user_login' => $username,
						'user_pass'  => $user_p,
						'user_email' => $email,
						'role'       => $role,
					)
				);

				$user_id = wp_insert_user( $wp_user_data );

				if ( is_wp_error( $user_id ) ) {
					$uc_status = new \WP_Error(
						'registration-error',
						'<strong>' . esc_html__( 'ERROR', 'edwiser-bridge' ) . '</strong>: ' .
						esc_html__(
							'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.',
							'edwiser-bridge'
						)
					);
				} else {

					// update firstname, lastname.
					update_user_meta( $user_id, 'first_name', $firstname );
					update_user_meta( $user_id, 'last_name', $lastname );

					$args = array(
						'user_email' => $email,
						'username'   => $username,
						'first_name' => $firstname,
						'last_name'  => $lastname,
						'password'   => $user_p,
					);
					do_action( 'eb_created_user', $args );
					$uc_status = $user_id;
				}
			}
		}
		return $uc_status;
	}
	/**
	 * Trigger course creation.
	 *
	 * @param text $data data.
	 */
	public function eb_trigger_course_creation( $data ) {

		if ( isset( $data['course_id'] ) ) {
			// Create course data.
			$course_data             = new \stdClass();
			$course_data->id         = $data['course_id'];
			$course_data->fullname   = $data['fullname'];
			$course_data->summary    = $data['summary'];
			$course_data->categoryid = $data['cat'];

			// Create WP course from Moodle course id info.
			edwiser_bridge_instance()->course_manager()->create_course_on_wordpress(
				$course_data,
				array(
					'eb_synchronize_draft' => '1',
				)
			);
		}
	}



	/**
	 * Trigger Delete.
	 *
	 * @param text $data data.
	 */
	public function eb_trigger_course_delete( $data ) {

		if ( isset( $data['course_id'] ) ) {
			// get WP course id from moodle course id.
			$wp_course_id = \app\wisdmlabs\edwiserBridge\wdm_eb_get_wp_course_id_from_moodle_course_id( $data['course_id'] );

			if ( $wp_course_id ) {
				// Update course meta to delete.
				// mdl_course_deleted.
				$course_meta = get_post_meta( $wp_course_id, 'eb_course_options', 1 );

				$course_meta['mdl_course_deleted'] = 1;

				// To add eb_course in WordPress to draft if course is deleted from moodle.
				$post_status = array(
					'ID'          => $wp_course_id,
					'post_type'   => 'eb_course',
					'post_status' => 'draft',
				);
				wp_update_post( $post_status );

				update_post_meta( $wp_course_id, 'eb_course_options', $course_meta );
			}
		}
	}


	/**
	 * User update.
	 *
	 * @param text $data data.
	 */
	public function eb_trigger_user_update( $data ) {
		// get WP User id if present then process.
		$wp_user_id      = \app\wisdmlabs\edwiserBridge\wdm_eb_get_wp_user_id_from_moodle_id( $data['user_id'] );
		$eb_access_token = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();

		if ( ! empty( $wp_user_id ) ) {
			// get fields.
			$user_update_array = array(
				'ID' => $wp_user_id,
			);

			// CHecking this as when user updates password through preferences only password will be sent to the WordPress, so if anyone of the field is empty we need to only update password.
			if ( isset( $data['first_name'] ) && ! empty( $data['first_name'] ) ) {
				$user_update_array['first_name'] = $data['first_name'];
				$user_update_array['last_name']  = $data['last_name'];
			}

			// if password is present then decode with key.
			if ( isset( $data['password'] ) && ! empty( $data['password'] ) ) {
				$enc_method = 'AES-128-CTR';
				$enc_iv     = substr( hash( 'sha256', $eb_access_token ), 0, 16 );

				$enc_key                        = openssl_digest( $eb_access_token, 'SHA256', true );
				$user_p                         = openssl_decrypt( $data['password'], $enc_method, $enc_key, 0, $enc_iv );
				$user_update_array['user_pass'] = $user_p;
			}

			$user_update_array = apply_filters( 'eb_mdl_user_update_trigger_data', $user_update_array );

			// Update password and fields.
			wp_update_user( $user_update_array );

			do_action( 'eb_user_updated_from_moodle', $wp_user_id, $data );
		}
	}
}
