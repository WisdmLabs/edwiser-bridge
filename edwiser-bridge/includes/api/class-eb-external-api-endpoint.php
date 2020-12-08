<?php
/**
 * This class works as a connection helper to connect with Moodle webservice API.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

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
	 * This function parse the request coming from
	 *
	 * @param  text $request_data request Data.
	 */
	public function external_api_endpoint_def( $request_data ) {

		$data = isset( $request_data['data'] ) ? sanitize_text_field( wp_unslash( $request_data['data'] ) ) : '';
		$data = unserialize( $data );

		$response_data = array();

		if ( isset( $request_data['action'] ) && ! empty( $request_data['action'] ) ) {
			$action = sanitize_text_field( wp_unslash( $request_data['action'] ) );

			switch ( $action ) {
				case 'test_connection':
					$response_data = $this->eb_test_connection( $data );
					break;

				case 'course_enrollment':
					$response_data = $this->eb_course_enrollment( $data, 0 );
					break;

				case 'course_un_enrollment':
					$response_data = $this->eb_course_enrollment( $data, 1 );
					break;

				case 'user_creation':
					$response_data = $this->eb_trigger_user_creation( $data );
					break;

				case 'user_deletion':
					$response_data = $this->eb_trigger_user_delete( $data );
					break;

				case 'user_updated':
					$response_data = $this->eb_trigger_user_update( $data );
					break;

				case 'course_deleted':
					$response_data = $this->eb_trigger_course_delete( $data );
					break;

				default:
					break;
			}
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
		$msg    = 'Invalid token please check token';

		if ( isset( $data['token'] ) ) {
			$settings = maybe_unserialize( get_option( 'eb_connection' ) );
			if ( ! isset( $settings['eb_access_token'] ) ) {
				$msg = 'Please save connection settings on Worpdress';
			} elseif ( $settings['eb_access_token'] === $data['token'] ) {
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
			$wp_course_id  = get_wp_course_id_from_moodle_course_id( $data['course_id'] );

			if ( $wp_course_id ) {
				$mdl_user_id = $data['user_id'];
				$wp_user_id  = get_wp_user_id_from_moodle_id( $data['user_id'] );
				if ( ! $wp_user_id && empty( $wp_user_id ) && 0 == $un_enroll ) {
					$role       = eb_default_registration_role();
					$wp_user_id = $this->create_only_wp_user( $data['user_name'], $data['email'], $data['first_name'], $data['last_name'], $role );
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

					if ( in_array( $wp_course_id, $user_enrollment_meta ) ) {
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
			$role = eb_default_registration_role();

			$password = '';
			if ( isset( $data['password'] ) && ! empty( $data['password'] ) ) {

				$enc_method = 'AES-128-CTR';
				$enc_iv     = '1234567891011121';

				$enc_key  = openssl_digest( EDWISER_ACCESS_TOKEN, 'SHA256', true );
				$password = openssl_decrypt( $data['password'], $enc_method, $enc_key, 0, $enc_iv );
			}

			$wp_user_id = $this->create_only_wp_user( $data['user_name'], $data['email'], $data['first_name'], $data['last_name'], $role, $password );
			if ( $wp_user_id ) {
				update_user_meta( $wp_user_id, 'moodle_user_id', $data['user_id'] );
			}
		}
	}


	/**
	 * Function to delete user for the user deletion request coming from Moodle.
	 *
	 * @param  text $data data.
	 */
	public function eb_trigger_user_delete( $data ) {
		require_once( ABSPATH . 'wp-admin/includes/user.php' );
		if ( isset( $data['user_id'] ) ) {
			$wp_user_id = get_wp_user_id_from_moodle_id( $data['user_id'] );
			if ( $wp_user_id ) {
				$user    = get_user_by( 'ID', $wp_user_id );
				$args    = array(
					'user_email' => $user->user_email,
					'username'   => $user->user_login,
					'first_name' => $user->first_name,
					'last_name'  => $user->last_name,
				);
				$deleted = wp_delete_user( $wp_user_id );
				if ( $deleted ) {
					do_action( 'eb_mdl_user_deletion_trigger', $args );
				}
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
	 * @param  string $password    password.
	 */
	public function create_only_wp_user( $username, $email, $firstname, $lastname, $role = '', $password = '' ) {
		if ( email_exists( $email ) ) {
			return new \WP_Error(
				'registration-error',
				esc_html__( 'An account is already registered with your email address. Please login.', 'eb-textdomain' ),
				'eb_email_exists'
			);
		}

		// Ensure username is unique.
		$append     = 1;
		$o_username = $username;

		while ( username_exists( $username ) ) {
			$username = $o_username . $append;
			++$append;
		}

		if ( empty( $password ) ) {
			// Handle password creation.
			$password = wp_generate_password();
		}

		// WP Validation.
		$validation_errors = new \WP_Error();

		if ( $validation_errors->get_error_code() ) {
			return $validation_errors;
		}

		// Added after 1.3.4.
		if ( '' === $role ) {
			$role = get_option( 'default_role' );
		}

		$wp_user_data = apply_filters(
			'eb_new_user_data',
			array(
				'user_login' => $username,
				'user_pass'  => $password,
				'user_email' => $email,
				'role'       => $role,
			)
		);

		$user_id = wp_insert_user( $wp_user_data );

		if ( is_wp_error( $user_id ) ) {
			return new \WP_Error(
				'registration-error',
				'<strong>' . esc_html__( 'ERROR', 'eb-textdomain' ) . '</strong>: ' .
				esc_html__(
					'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.',
					'eb-textdomain'
				)
			);
		}

		// update firstname, lastname.
		update_user_meta( $user_id, 'first_name', $firstname );
		update_user_meta( $user_id, 'last_name', $lastname );

		$args = array(
			'user_email' => $email,
			'username'   => $username,
			'first_name' => $firstname,
			'last_name'  => $lastname,
			'password'   => $password,
		);
		do_action( 'eb_created_user', $args );
		return $user_id;
	}


	/**
	 * Trigger Delete.
	 *
	 * @param text $data data.
	 */
	public function eb_trigger_course_delete( $data ) {

		if ( isset( $data['course_id'] ) ) {
			// get WP course id from moodle course id.
			$wp_course_id = get_wp_course_id_from_moodle_course_id( $data['course_id'] );

			if ( $wp_course_id ) {
				// Update course meta to delete.
				// mdl_course_deleted.
				$course_meta = get_post_meta( $wp_course_id, 'eb_course_options', 1 );

				$course_meta['mdl_course_deleted'] = 1;

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
		$wp_user_id = get_wp_user_id_from_moodle_id( $data['user_id'] );

		if ( ! empty( $wp_user_id ) ) {
			// get fields.
			$user_update_array = array(
				'ID'         => $wp_user_id,
				'first_name' => $data['first_name'],
				'last_name'  => $data['last_name'],
			);

			// if password is present then decode with key.

			if ( isset( $data['password'] ) && ! empty( $data['password'] ) ) {

				$enc_method = 'AES-128-CTR';
				$enc_iv     = '1234567891011121';

				$enc_key                        = openssl_digest( EDWISER_ACCESS_TOKEN, 'SHA256', true );
				$password                       = openssl_decrypt( $data['password'], $enc_method, $enc_key, 0, $enc_iv );
				$user_update_array['user_pass'] = $password;
			}

			$user_update_array = apply_filters( 'eb_mdl_user_update_trigger_data', $user_update_array );

			// Update password and fields.
			wp_update_user( $user_update_array );
		}
	}
}
