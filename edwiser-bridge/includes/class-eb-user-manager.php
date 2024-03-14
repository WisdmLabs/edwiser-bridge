<?php
/**
 * This class defines all code necessary to manage user's moodle & WP account'.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwioser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * User manager.
 */
class Eb_User_Manager {

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
	 * The version of this plugin.
	 *
	 * @var EDW The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main EDW Instance.
	 *
	 * Ensures only one instance of EDW is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
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
	 * Contsruct.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Login a user (set auth cookie and set global user object).
	 *
	 * @param int $user_id user id.
	 */
	public function set_user_auth_cookie( $user_id ) {
		global $current_user;

		wp_set_auth_cookie( $user_id, true );
	}

	/**
	 * Initiate the user courses synchronization process, get user's all courses from moodle
	 * and enroll him to the same courses on WordPress end.
	 *
	 * Called by user_data_synchronization_initiater() from class Eb_Settings_Ajax_Initiater
	 *
	 * @param array $sync_options    user sync options.
	 * @param int   $user_id_to_sync to only sync courses of an individual user on registration.
	 * @param int   $offset LIMIT query offset.
	 *
	 * @since   1.0.0
	 *
	 * @return array $response     array containing status & response message
	 */
	public function user_course_synchronization_handler( $sync_options = array(), $user_id_to_sync = '', $offset = 0 ) {
		global $wpdb;
		// checking if moodle connection is working properly.

		$response_array['connection_response'] = 1; // add connection response in response array.
		$wp_users_count                        = 1;
		// get all WordPress users having an associated moodle account.
		if ( is_numeric( $user_id_to_sync ) ) {
			$all_users = $wpdb->get_results( // @codingStandardsIgnoreLine
				$wpdb->prepare(
					"SELECT user_id, meta_value AS moodle_user_id
					FROM {$wpdb->base_prefix}usermeta
					WHERE user_id =%d AND meta_key = 'moodle_user_id' AND meta_value IS NOT NULL",
					$user_id_to_sync
				),
				ARRAY_A
			);
		} else {
			// query to get all WordPress users having an associated moodle account so that we can synchronize the course enrollment
			// added limit for get users in chunk.
			$all_users = $wpdb->get_results( // @codingStandardsIgnoreLine
				$wpdb->prepare(
					"SELECT user_id, meta_value AS moodle_user_id
					FROM {$wpdb->base_prefix}usermeta
					WHERE meta_key = 'moodle_user_id'
					AND meta_value IS NOT NULL
					ORDER BY user_id ASC
					LIMIT  %d , 20",
					$offset
				),
				ARRAY_A
			);
			// used to get all users count.
			$users_count    = $wpdb->get_results( // @codingStandardsIgnoreLine
				"SELECT COUNT(user_id) AS users_count
				FROM {$wpdb->base_prefix}usermeta
				WHERE meta_key = 'moodle_user_id'
				AND meta_value IS NOT NULL"
			);
			$wp_users_count = $users_count[0]->users_count;
		}

		// get courses of each user having a moodle a/c assosiated.
		foreach ( $all_users as $key => $value ) {
			$key;

			// sync users courses only if checkbox is checked.
			if ( isset( $sync_options['eb_synchronize_user_courses'] ) &&
					1 === (int) $sync_options['eb_synchronize_user_courses'] ) {
				// get user's enrolled courses from moodle.
				$moodle_user_courses = edwiser_bridge_instance()->course_manager()->get_moodle_courses( $value['moodle_user_id'] );

				$enrolled_courses = array(); // push user's all enrolled courses id in array.
				// enrol user to courses based on recieved data.
				if ( 1 === $moodle_user_courses['success'] ) {
					foreach ( $moodle_user_courses['response_data'] as $course_data ) {
						// get WordPress id of course.
						$existing_course_id = edwiser_bridge_instance()->course_manager()->is_course_presynced( $course_data->id );

						// enroll user to course if course exist on WordPress ( synchronized on WordPress ).
						if ( is_numeric( $existing_course_id ) ) {
							// add enrolled courses id in array.
							$enrolled_courses[] = $existing_course_id;

							// define args.
							$args = array(
								'user_id' => $value['user_id'],
								'courses' => array( $existing_course_id ),
								'sync'    => true,
							);
							// update enrollment records.
							edwiser_bridge_instance()->enrollment_manager()->update_enrollment_record_wordpress( $args );

							edwiser_bridge_instance()->logger()->add(
								'user',
								'New course enrolled,
								User ID: ' . $value['user_id'] . ' Course ID: ' . $existing_course_id
							); // add user log.
						}
					}
				} else {
					// Push user's id to separate array,
					// if there is a problem in fetching his/her courses from moodle.
					$response_array['user_with_error'][]  = $value['user_id'];
					$response_array['user_with_error'][] .= '<strong>' . esc_html__( 'User ID:', 'edwiser-bridge' ) . ' </strong>' . $value['user_id'];
					$response_array['user_with_error'][] .= '</p><br/>';
				}

				/*
					* In this block we are unenrolling user course if a user is unenrolled from those course on moodle
					*/
				$old_enrolled_courses = $wpdb->get_results( // @codingStandardsIgnoreLine
					$wpdb->prepare(
						"SELECT course_id
						FROM {$wpdb->prefix}moodle_enrollment
						WHERE user_id = %d",
						$value['user_id']
					),
					ARRAY_A
				);

				// get user's existing enrollment record from WordPress DB.
				$notenrolled_courses = array();

				foreach ( $old_enrolled_courses as $existing_course ) {
					if ( ! in_array( trim( $existing_course['course_id'] ), $enrolled_courses, true ) ) {
						$notenrolled_courses[] = $existing_course['course_id'];
					}
				}

				if ( is_array( $notenrolled_courses ) && ! empty( $notenrolled_courses ) ) {
					// define args.
					$args = array(
						'user_id'  => $value['user_id'],
						'courses'  => $notenrolled_courses,
						'unenroll' => 1,
					);
					edwiser_bridge_instance()->enrollment_manager()->update_enrollment_record_wordpress( $args );

				}
				/* Unenrollment part completed * */
			}

			/*
				* hook that can be used when a single users data sync completes
				* total courses in which user is enrolled after sync is given as an argument with user id
				* we are passing users WordPress id and course id (as on WordPress)
				*/
			do_action( 'eb_user_synchronization_complete_single', $value['user_id'], $sync_options );
		}
		// these two properties are used to track, how many user's data have beedn updated.
		$response_array['users_count']    = count( $all_users );
		$response_array['wp_users_count'] = $wp_users_count;

		/*
			* hook to be run on user data sync total completion
			* we are passing all user ids for which sync is performed
			*/
		do_action( 'eb_user_synchronization_complete', $all_users, $sync_options );

		return $response_array;
	}


	/**
	 * Initiate the process to link users to moodle, get user's who have not linked to moodle
	 * and link them to moodle
	 *
	 * Called by users_link_to_moodle_synchronization() from class Eb_Settings_Ajax_Initiater
	 *
	 * @param array $sync_options    user sync options.
	 * @param int   $offset LIMIT query offset for getting the resluts in chunk.
	 *
	 * @since   1.4.1
	 *
	 * @return array $response     array containing status & response message
	 */
	public function user_link_to_moodle_handler( $sync_options = array(), $offset = 0 ) {
		global $wpdb;
		// checking if moodle connection is working properly.
		$eb_access_token = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();
		$eb_access_url   = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();
		$connected       = edwiser_bridge_instance()->connection_helper()->connection_test_helper( $eb_access_url, $eb_access_token );

		$response_array['connection_response'] = $connected['success']; // add connection response in response array.
		$link_users_count                      = 0;

		if ( 1 === (int) $connected['success'] ) {
			if ( isset( $sync_options['eb_link_users_to_moodle'] ) && 1 === (int) $sync_options['eb_link_users_to_moodle'] ) {
				// query to get list of users who have not linked to moodle with limit.
				$unlinked_users = $wpdb->get_results( // @codingStandardsIgnoreLine
					$wpdb->prepare(
						"SELECT DISTINCT(user_id)
						FROM {$wpdb->base_prefix}usermeta
						WHERE user_id NOT IN (SELECT DISTINCT(user_id) from {$wpdb->base_prefix}usermeta WHERE meta_key = 'moodle_user_id' && meta_value IS NOT NULL)
						ORDER BY user_id ASC
						LIMIT %d , 20",
						$offset
					),
					ARRAY_A
				);

				if ( ! empty( $unlinked_users ) ) {
					foreach ( $unlinked_users as $key => $value ) {
						if ( 0 == $value['user_id'] ) continue; // @codingStandardsIgnoreLine
						$user_object = get_userdata( $value['user_id'] );
						$flag        = $this->link_moodle_user( $user_object );
						// If user not linked then add it in unlinked users array.
						if ( ! $flag ) {
							$user                                = get_userdata( $value['user_id'] );
							$response_array['user_with_error'][] = '<tr><td>' . $value['user_id'] . '</td><td> ' . $user->user_login . '</td></tr>';
						} else {
							++$link_users_count;
						}
					}
				}
				// used to get all unlinked users count.
				$users_count = $wpdb->get_results( // @codingStandardsIgnoreLine
					"SELECT COUNT(DISTINCT(user_id)) as users_count
					FROM {$wpdb->base_prefix}usermeta
					WHERE user_id NOT IN (SELECT DISTINCT(user_id) from {$wpdb->base_prefix}usermeta WHERE meta_key = 'moodle_user_id' && meta_value IS NOT NULL)"
				);

				$users_count = $users_count[0]->users_count;
			}
			// these properties are used to track, how many user's have linked.
			$response_array['unlinked_users_count'] = count( $unlinked_users );
			$response_array['users_count']          = $users_count;
			$response_array['linked_users_count']   = $link_users_count;
		} else {
			edwiser_bridge_instance()->logger()->add(
				'user',
				'Connection problem in synchronization, Response:' . print_r( $connected, true ) // @codingStandardsIgnoreLine
			); // add connection log.
		}
		return $response_array;
	}



	/**
	 * DEPRECATED FUNCTION
	 * Initiate the process to link users to moodle, get user's who have not linked to moodle
	 * and link them to moodle
	 *
	 * Called by users_link_to_moodle_synchronization() from class Eb_Settings_Ajax_Initiater
	 *
	 * @deprecated since 2.0.1 use user_link_to_moodle_handler( $sync_options, $offset ) insted.
	 * @param array $sync_options    user sync options.
	 * @param int   $offset LIMIT query offset for getting the resluts in chunk.
	 *
	 * @since   1.4.1
	 *
	 * @return array $response     array containing status & response message
	 */
	public function userLinkToMoodlenHandler( $sync_options = array(), $offset = 0 ) {
		return $this->user_link_to_moodle_handler( $sync_options, $offset );
	}




	/**
	 * DEPRECATED FUNATION.
	 *
	 * Create a new WordPress user.
	 *
	 * @deprecated since 2.0.1 use create_wordpress_user( $email, $firstname, $lastname, $role ) insted.
	 * @param string $email email.
	 * @param string $firstname firstname name.
	 * @param string $lastname lastname.
	 * @param string $role role.
	 *
	 * @return int|WP_Error on failure, Int (user ID) on success
	 */
	public function createWordpressUser( $email, $firstname, $lastname, $role = '' ) {
		return $this->create_wordpress_user( $email, $firstname, $lastname, $role );
	}


	/**
	 * Create a new WordPress user.
	 *
	 * @param string $email email.
	 * @param string $firstname firstname name.
	 * @param string $lastname lastname.
	 * @param string $role role.
	 * @param string $user_p user account password.
	 * @param string $redirect_to the redirect to url to redirect the user ro the login page with redirect to url so that the track wont be loose.
	 *
	 * @return int|WP_Error on failure, Int (user ID) on success
	 */
	public function create_wordpress_user( $email, $firstname, $lastname, $role = '', $user_p = '', $redirect_to = '' ) {
		$uc_status = '';
		// Check the e-mail address.
		if ( ! empty( $email ) && is_email( $email ) ) {
			$uc_status = new \WP_Error( 'registration-error', esc_html__( 'Please provide a valid email address.', 'edwiser-bridge' ) );
			if ( email_exists( $email ) ) {
				$login_link = '<a href="' . esc_url( \app\wisdmlabs\edwiserBridge\wdm_eb_user_account_url( array( $redirect_to ) ) ) . '">Please login</a>';
				$uc_status  = new \WP_Error(
					'registration-error',
					/* translators: %s: $login_link Login link. */
					sprintf( __( 'An account is already registered with your email address, %s.', 'edwiser-bridge' ), $login_link ),
					'eb_email_exists'
				);
			} else {
				$username = sanitize_user( current( explode( '@', $email ) ), true );

				// Ensure username is unique.
				$append     = 1;
				$o_username = $username;

				while ( username_exists( $username ) ) {
					$username = $o_username . $append;
					++$append;
				}

				// Handle password creation.
				if ( empty( $user_p ) ) {
					$user_p = wp_generate_password();
				}
				// WP Validation.
				$validation_errors = new \WP_Error();

				do_action( 'eb_register_post', $username, $email, $validation_errors );

				$validation_errors = apply_filters( 'eb_registration_errors', $validation_errors, $username, $email );

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

						// check if a user exists on moodle with same email.
						$moodle_user = $this->get_moodle_user( $wp_user_data['user_email'] );

						if ( isset( $moodle_user['user_exists'] ) && 1 === $moodle_user['user_exists'] && is_object( $moodle_user['user_data'] ) ) {
							update_user_meta( $user_id, 'moodle_user_id', $moodle_user['user_data']->id );

							// sync courses of an individual user when an existing moodle user is linked with a WordPress account.
							$this->user_course_synchronization_handler( array( 'eb_synchronize_user_courses' => 1 ), $user_id );
						} else {
							$general_settings = get_option( 'eb_general' );
							$language         = 'en';
							if ( isset( $general_settings['eb_language_code'] ) ) {
								$language = $general_settings['eb_language_code'];
							}
							$user_data = array(
								'username'  => $username,
								'password'  => $user_p,
								'firstname' => $firstname,
								'lastname'  => $lastname,
								'email'     => $email,
								'auth'      => 'manual',
								'lang'      => $language,
							);

							$eb_access_token = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();
							$eb_access_url   = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();

							// create a moodle user with above details.
							if ( '' !== $eb_access_token && '' !== $eb_access_url ) {
								$moodle_user = $this->create_moodle_user( $user_data );
								if ( isset( $moodle_user['user_created'] ) && 1 === $moodle_user['user_created'] && is_object( $moodle_user['user_data'] ) ) {
									update_user_meta( $user_id, 'moodle_user_id', $moodle_user['user_data']->id );
								}
							}
						}

						$args = array(
							'user_email' => $email,
							'username'   => $username,
							'first_name' => $firstname,
							'last_name'  => $lastname,
							'password'   => $user_p,
						);
						do_action( 'eb_created_user', $args );

						// send another email if moodle user account created has a different username then WordPress
						// in case the username was already registered on moodle, so our system generates a new username automatically.
						//
						// In this case we need to send another mail with moodle account credentials.
						$created = 0;
						if ( isset( $moodle_user['user_created'] ) ) {
							$created = $moodle_user['user_created'];
						}
						if ( $created && strtolower( $username ) !== strtolower( $moodle_user['user_data']->username ) ) {
							$args = array(
								'user_email' => $email,
								'username'   => $moodle_user['user_data']->username,
								'first_name' => $firstname,
								'last_name'  => $lastname,
								'password'   => $user_p,
							);
							// create a new action hook with user details as argument.
							do_action( 'eb_linked_to_existing_wordpress_user', $args );
						}
						$uc_status = $user_id;
					}
				}
			}
		}
		return $uc_status;
	}


	/**
	 * DEPRECATED FUNCTION.
	 * Will be used to check if a username is available on moodle
	 * Used while creating a moodle account for a user.
	 *
	 * @deprecated since 2.0.1 use is_moodle_username_available( $username ) insted.
	 * @param string $username username to be checked.
	 *
	 * @return bool returns true / false  [ return false in case of connection failure ]
	 */
	public function isMoodleUsernameAvailable( $username ) {
		return $this->is_moodle_username_available( $username );
	}




	/**
	 * Will be used to check if a username is available on moodle
	 * Used while creating a moodle account for a user.
	 *
	 * @param string $username username to be checked.
	 *
	 * @return bool returns true / false  [ return false in case of connection failure ]
	 */
	public function is_moodle_username_available( $username ) {

		edwiser_bridge_instance()->logger()->add( 'user', 'Checking if username exists....' ); // add to user log.

		$username            = sanitize_user( $username ); // get sanitized username.
		$webservice_function = 'core_user_get_users_by_field';

		// prepare request data array.
		$request_data = array(
			'field'  => 'username',
			'values' => array( $username ),
		);
		$response     = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper( $webservice_function, $request_data );

		// return true only if username is available.
		if ( 1 === $response['success'] && empty( $response['response_data'] ) ) {
			return true;
		} else {
			return false;
		}
	}





	/**
	 * Get a moodle user by email, search if a user exists on moodle with same email id and
	 * return user's moodle id and username.
	 *
	 * Proper response is returned on completion
	 *
	 * @since  1.0.0
	 *
	 * @param string $user_email email to be checked on moodle.
	 *
	 * @return array
	 */
	public function get_moodle_user( $user_email ) {

		$user_email          = filter_var( $user_email, FILTER_VALIDATE_EMAIL );
		$user                = array();
		$webservice_function = 'core_user_get_users_by_field';

		if ( ! is_email( $user_email ) ) {
			return $user;
		}

		// prepare request data array.
		$request_data = array(
			'field'  => 'email',
			'values' => array( $user_email ),
		);
		$response     = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper( $webservice_function, $request_data );

		// create response array based on response recieved from api helper class.
		if ( 1 === $response['success'] && empty( $response['response_data'] ) ) {
			$user = array(
				'user_exists' => 0,
				'user_data'   => '',
			);
		} elseif ( 1 === $response['success'] &&
				is_array( $response['response_data'] ) &&
				! empty( $response['response_data'] ) ) {
			$user = array(
				'user_exists' => 1,
				'user_data'   => $response['response_data'][0],
			);
		} elseif ( 0 === $response['success'] ) {
			$user = array(
				'user_created' => 0,
				'user_data'    => $response['response_message'],
			);
		}

		return $user;
	}

	/**
	 * DEPRECATED FUNCTION
	 *
	 * Create a new user on moodle with user data passed to it.
	 * update an existing user whose moodle id is passed to the function.
	 *
	 * @deprecated since 2.0.1 use create_moodle_user( $user_data, $update ) insted.
	 * @param array $user_data the user data used to create a new account or update existing one.
	 * @param int   $update set update = 1 if you want to update an existing user on moodle.
	 *
	 * @return int returns id of new user created, on error returns false.
	 */
	public function createMoodleUser( $user_data, $update = 0 ) {
		return $this->create_moodle_user( $user_data, $update );
	}



	/**
	 * Create a new user on moodle with user data passed to it.
	 * update an existing user whose moodle id is passed to the function.
	 *
	 * @param array $user_data the user data used to create a new account or update existing one.
	 * @param int   $update set update = 1 if you want to update an existing user on moodle.
	 *
	 * @return int returns id of new user created, on error returns false.
	 */
	public function create_moodle_user( $user_data, $update = 0 ) {
		$user  = array(); // to store user creation/updation response.
		$users = array();
		edwiser_bridge_instance()->logger()->add( 'user', 'Start creating/updating moodle user, Updating: ' . $update ); // add user log
		// set webservice function according to update parameter.
		if ( 1 === $update ) {
			$webservice_function = 'core_user_update_users';
		} else {
			$webservice_function = 'core_user_create_users';

			$eb_general_settings = get_option( 'eb_general' );
			if ( isset( $_GET['action'] ) && 'eb_register' === $_GET['action'] && isset( $eb_general_settings['eb_email_verification'] ) && 'yes' === $eb_general_settings['eb_email_verification'] ) { // @codingStandardsIgnoreLine
				// get wp user id from email.
				$wp_user_id  = email_exists( $user_data['email'] );
				$is_verified = get_user_meta( $wp_user_id, 'eb_user_email_verified', true );
				if ( 1 != $is_verified ) { // @codingStandardsIgnoreLine
					$user = array(
						'user_created' => 0,
						'user_data'    => __( 'Email not verified', 'edwiser-bridge' ),
					);
					return $user;
				}
			}
		}

		/**
		 * To lowercase the username for moodle.
		 *
		 * @since  1.2.2
		 */
		// confirm that username is in lowercase always.
		if ( isset( $user_data['username'] ) ) {
			$user_data['username'] = strtolower( $user_data['username'] );
		}

		// Ensure username is unique, when creating a new user on moodle.
		if ( 0 === $update ) {
			$append = 1;
			if ( ! empty( $user_data['username'] ) ) {
				$o_username = $user_data['username'];

				// we will check if the username is vailable on moodle before creating a user.
				while ( ! $this->is_moodle_username_available( $user_data['username'] ) ) {
					$user_data['username'] = $o_username . $append;
					++$append;
				}

				// apply custom filter on username generated.
				$user_data['username'] = apply_filters( 'eb_unique_moodle_username', $user_data['username'] );
			}
		}

		/*
		 * apply custom filter on userdata that is used to create or update moodle account
		 * used to add additional user profile fields value that is passed to moodle
		 */
		$user_data = apply_filters( 'eb_moodle_user_profile_details', $user_data, $update );
		// prepare user data array.
		foreach ( $user_data as $key => $value ) {
			$users[0][ $key ] = $value;
		}
		// prepare request data.
		$request_data = array( 'users' => $users );
		$response     = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper(
			$webservice_function,
			$request_data
		);
		// handle response recived from moodle and creates response array accordingly.
		if ( 0 === $update ) { // when user is created.
			if ( 1 === $response['success'] && empty( $response['response_data'] ) ) {
				$user = array(
					'user_created' => 0,
					'user_data'    => '',
				);
			} elseif ( 1 === $response['success'] &&
					is_array( $response['response_data'] ) &&
					! empty( $response['response_data'] ) ) {
				$user = array(
					'user_created' => 1,
					'user_data'    => $response['response_data'][0],
				);
			} elseif ( 0 === $response['success'] ) {
				$user = array(
					'user_created' => 0,
					'user_data'    => $response['response_message'],
				);
			}
		} elseif ( 1 === $update ) { // when updating profile details of an existing user on moodle.
			if ( 1 === $response['success'] && ( empty( $response['response_data'] ) || empty( $response['response_data']->warnings ) ) ) {
				$user = array( 'user_updated' => 1 );
			} else {
				$user = array( 'user_updated' => 0 );
			}
		}

		// sync courses of an individual user when user is created or updated on moodle.
		// get WordPress user id by WordPress user email.
		if ( 0 === $update && isset( $user_data['email'] ) ) {
			$wp_user = get_user_by( 'email', $user_data['email'] );
			$this->user_course_synchronization_handler( array( 'eb_synchronize_user_courses' => 1 ), $wp_user->ID );
		}

		do_action( 'eb_after_moodle_user_creation', $user );
		return $user;
	}





	/**
	 * DEPRECATED FUNCTION
	 *
	 * Checks if a moodle account is already linked, or create account on moodle and links to WordPress.
	 * Can also be executed on wp_login hook.
	 *
	 * @deprecated since 2.0.1 use link_moodle_user( $user ) insted.
	 * a do_action is added that can be used to execute custom action if a new user is created on moodle
	 * and linked to WordPress.
	 *
	 * @param object $user WordPress user object.
	 */
	public function linkMoodleUser( $user ) {
		return $this->link_moodle_user( $user );
	}





	/**
	 * Checks if a moodle account is already linked, or create account on moodle and links to WordPress.
	 * Can also be executed on wp_login hook.
	 *
	 * A do_action is added that can be used to execute custom action if a new user is created on moodle
	 * and linked to WordPress.
	 *
	 * @param object $user WordPress user object.
	 */
	public function link_moodle_user( $user ) {
		// check if a moodle user account is already linked.
		$moodle_user_id = get_user_meta( $user->ID, 'moodle_user_id', true );
		$created        = 0;
		$linked         = 0;
		$user_data      = array();

		if ( empty( $moodle_user_id ) ) {
			/*
			 * get user's id from moodle and add in WordPress usermeta.
			 *
			 * first checks if user exists on moodle,
			 * creates a new user account on moodle with same user details including password.
			 */
			$moodle_user = $this->get_moodle_user( $user->user_email );

			if ( isset( $moodle_user['user_exists'] ) && 1 === $moodle_user['user_exists'] && is_object( $moodle_user['user_data'] ) ) {
				update_user_meta( $user->ID, 'moodle_user_id', $moodle_user['user_data']->id );
				$linked = 1;

				// sync courses of an individual user.
				// when an exisintg moodle user is linked to WordPress account with same email.
				$this->user_course_synchronization_handler(
					array(
						'eb_synchronize_user_courses' => 1,
					),
					$user->ID
				);
			} elseif ( isset( $moodle_user['user_exists'] ) && 0 === $moodle_user['user_exists'] ) {
				$general_settings = get_option( 'eb_general' );
				$language         = 'en';

				if ( isset( $general_settings['eb_language_code'] ) ) {
					$language = $general_settings['eb_language_code'];
				}

				// generate random password for moodle account, as user is already registered on WordPress.
				$user_p = wp_unslash( apply_filters( 'eb_filter_moodle_password', wp_generate_password() ) );

				$user_data = array(
					'username'  => $user->user_login,
					'password'  => $user_p,
					'firstname' => $user->first_name,
					'lastname'  => $user->last_name,
					'email'     => $user->user_email,
					'auth'      => 'manual',
					'lang'      => $language,
				);

				$moodle_user = $this->create_moodle_user( $user_data );
				if ( isset( $moodle_user['user_created'] ) && 1 === $moodle_user['user_created'] && is_object( $moodle_user['user_data'] ) ) {
					update_user_meta( $user->ID, 'moodle_user_id', $moodle_user['user_data']->id );
					$created = 1;
					$linked  = 1;
				}
			}
		}

		$send_user_creation_email = 1;
		$send_user_creation_email = apply_filters( 'eb_send_new_user_email_on_user_sync', $send_user_creation_email );

		// add a dynamic hook only if a new user is created on moodle and linked to WordPress account.
		if ( ! $created && $linked ) {
			$args = array(
				'user_email' => $user->user_email,
				'username'   => $moodle_user['user_data']->username,
				'first_name' => $user->first_name,
				'last_name'  => $user->last_name,
			);
			// create a new action hook with user details as argument.
			do_action( 'eb_linked_to_existing_wordpress_user', $args );
		} elseif ( $send_user_creation_email && $created && $linked ) {
			$args = array(
				'user_email' => $user_data['email'],
				'username'   => $moodle_user['user_data']->username,
				'first_name' => $user_data['firstname'],
				'last_name'  => $user_data['lastname'],
				'password'   => $user_data['password'],
			);
			// create a new action hook with user details as argument.
			do_action( 'eb_linked_to_existing_wordpress_to_new_user', $args );
		}

		return $linked;
	}

	/**
	 * Custom bulk action to link or unlink user's moodle account.
	 *
	 * One use case is: User account is deleted from moodle, then one should unlink it from WordPress too.
	 * Other can be if admin manually wants to link a user's WordPress account with moodle account.
	 *
	 * @since  1.0.0
	 */
	public function link_user_bulk_actions() {
		$current_screen = get_current_screen(); // get current screen object.
		// enqueue js only if current screen is users.
		if ( isset( $current_screen->base ) && 'users' === $current_screen->base ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery('<option>').val('link_moodle')
							.text('<?php esc_html_e( 'Link Moodle Account', 'edwiser-bridge' ); ?>')
							.appendTo("select[name='action']");
					jQuery('<option>').val('link_moodle')
							.text('<?php esc_html_e( 'Link Moodle Account', 'edwiser-bridge' ); ?>')
							.appendTo("select[name='action2']");
					jQuery('<option>').val('unlink_moodle')
							.text('<?php esc_html_e( 'Unlink Moodle Account', 'edwiser-bridge' ); ?>')
							.appendTo("select[name='action']");
					jQuery('<option>').val('unlink_moodle')
							.text('<?php esc_html_e( 'Unlink Moodle Account', 'edwiser-bridge' ); ?>')
							.appendTo("select[name='action2']");
				});
			</script>
			<?php
		}
	}

	/**
	 * Determine the link / unlink moodle account action
	 * perform security checks
	 * perform the action.
	 *
	 * This does not delete users account from moodle on unlink,
	 * but it can create a new moodle account on 'link account' action.
	 * In case a new account is created on moodle, the password is sent to user automatically as email.
	 * links or unlinks moodle and WordPress accounts of a user.
	 *
	 * @since  1.0.0
	 */
	public function link_user_bulk_actions_handler() {
		// get the action.
		$wp_user_table = _get_list_table( 'WP_Users_List_Table' );
		$action        = $wp_user_table->current_action();

		// perform our unlink action.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-users' ) ) {
			return;
		}

		$eb_bulk_user_nonce = wp_create_nonce( 'eb_bulk_users_nonce' );

		$users = isset( $_REQUEST['users'] ) ? \app\wisdmlabs\edwiserBridge\wdm_eb_edwiser_sanitize_array( $_REQUEST['users'] ) : array(); //@codingStandardsIgnoreLine

		// get all selected users.
		$request_refer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		$request_refer = strtok( $request_refer, '?' );

		switch ( $action ) {
			case 'link_moodle':
				$linked = 0;

				if ( is_array( $users ) ) {
					foreach ( $users as $user ) {
						$user_object = get_userdata( $user );
						if ( $this->link_moodle_user( $user_object ) ) {
							++$linked;
						}
					}

					// build the redirect url.
					$sendback = add_query_arg(
						array(
							'linked'             => $linked,
							'eb_bulk_user_nonce' => $eb_bulk_user_nonce,
						),
						$request_refer
					);

				}

				break;
			case 'unlink_moodle':
				$unlinked = 0;

				// get all selected users.
				if ( is_array( $users ) ) {
					foreach ( $users as $user ) {
						$deleted = ( delete_user_meta( $user, 'moodle_user_id' ) );
						delete_user_meta( $user, 'eb_user_password' );
						if ( $deleted ) {
							$unlinked++;
						}
					}

					// build the redirect url.
					$sendback = add_query_arg(
						array(
							'unlinked'           => $unlinked,
							'eb_bulk_user_nonce' => $eb_bulk_user_nonce,
						),
						sanitize_text_field(
							wp_unslash(
								$request_refer
							)
						)
					);
				}

				break;
			default:
				return;
		}

		wp_safe_redirect( $sendback );
		exit();
	}

	/**
	 * Display a message to admin on user link or unlink bulk actions.
	 *
	 * @since  1.0.0
	 */
	public function link_user_bulk_actions_notices() {
		global $pagenow;

		if ( ! isset( $_REQUEST['eb_bulk_user_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['eb_bulk_user_nonce'] ) ), 'eb_bulk_users_nonce' ) ) {
			return;
		}

		if ( 'users.php' === $pagenow ) {
			if ( isset( $_REQUEST['unlinked'] ) && 1 === (int) trim( sanitize_text_field( wp_unslash( $_REQUEST['unlinked'] ) ) ) ) {
				$message = sprintf( '%s' . esc_html__( ' User Unlinked.', 'edwiser-bridge' ), number_format_i18n( sanitize_text_field( wp_unslash( $_REQUEST['unlinked'] ) ) ) );
			} elseif ( isset( $_REQUEST['unlinked'] ) && (int) $_REQUEST['unlinked'] > 1 ) {
				$message = sprintf(
					'%s' . esc_html__( ' Users Unlinked.', 'edwiser-bridge' ),
					number_format_i18n( sanitize_text_field( wp_unslash( $_REQUEST['unlinked'] ) ) )
				);
			} elseif ( isset( $_REQUEST['linked'] ) && 1 === (int) trim( sanitize_text_field( wp_unslash( $_REQUEST['linked'] ) ) ) ) {
				$message = sprintf( '%s' . esc_html__( 'User Linked.', 'edwiser-bridge' ), number_format_i18n( sanitize_text_field( wp_unslash( $_REQUEST['linked'] ) ) ) );
			} elseif ( isset( $_REQUEST['linked'] ) && (int) $_REQUEST['linked'] > 1 ) {
				$message = sprintf( '%s ' . esc_html__( 'Users Linked.', 'edwiser-bridge' ), number_format_i18n( sanitize_text_field( wp_unslash( $_REQUEST['linked'] ) ) ) );
			}

			if ( isset( $message ) ) {
				echo "<div class='updated'><p>" . esc_html( $message ) . '</p></div>';
			}
		}
	}

	/**
	 * Change moodle password when WordPress password change event occurs.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id user id of the profile being updated.
	 */
	public function password_update( $user_id ) {
		// Proceed if nonce is verified.
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-update-user' ) ) {

			// Get new password entered by user.
			// Works for WordPress profile & woocommerce my account edit profile page.
			if ( isset( $_POST['password_1'] ) && ! empty( $_POST['password_1'] ) ) {
				$new_p = sanitize_text_field( wp_unslash( $_POST['password_1'] ) );
			} elseif ( isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
				$new_p = sanitize_text_field( wp_unslash( $_POST['pass1'] ) );
			} else {
				return;
			}

			edwiser_bridge_instance()->logger()->add( 'user', 'Password update initiated..... ' ); // add user log.

			$moodle_user_id = get_user_meta( $user_id, 'moodle_user_id', true ); // get moodle user id.

			if ( ! is_numeric( $moodle_user_id ) ) {
				edwiser_bridge_instance()->logger()->add( 'user', 'A moodle user id is not associated.... Exiting!!!' ); // add user log.
				return;
			}

			if ( empty( $new_p ) ) {
				return; // stop further execution of function if password was not entered.
			}

			$user_data = array(
				'id'       => $moodle_user_id, // moodle user id.
				'password' => $new_p,
			);

			$moodle_user = $this->create_moodle_user( $user_data, 1 );
			if ( isset( $moodle_user['user_updated'] ) && 1 !== $moodle_user['user_updated'] ) {
				edwiser_bridge_instance()->logger()->add( 'user', 'There is a problem in updating password..... Exiting!!!' ); // add user log.
			}
		}
	}

	/**
	 * When reseting password in wp-login.
	 *
	 * @since  1.0.0
	 *
	 * @param object $user current user object.
	 * @param string $pass new password entered by user.
	 */
	public function password_reset( $user, $pass ) {
		$moodle_user_id = get_user_meta( $user->ID, 'moodle_user_id', true ); // get moodle user id.

		if ( ! is_numeric( $moodle_user_id ) ) {
			return;
		}

		if ( isset( $pass ) && ! empty( $pass ) ) {
			$new_p = $pass; // get new password entered by user.

			$user_data = array(
				'id'       => (int) $moodle_user_id,
				'password' => $new_p,
			);

			$moodle_user = $this->create_moodle_user( $user_data, 1 );
			if ( isset( $moodle_user['user_updated'] ) && 1 !== $moodle_user['user_updated'] ) {
				edwiser_bridge_instance()->logger()->add( 'user', 'There is a problem in resetting password..... Exiting!!!' ); // add user log.
			}
		}
	}

	/**
	 * Display a dropdown in WordPress user profile from where admin can enroll user to any course directly.
	 *
	 * @param object $user current user object.
	 *
	 * @return int returns true
	 */
	public function display_users_enrolled_courses( $user ) {

		// Check if a moodle user account is already linked.
		$moodle_user_id = get_user_meta( $user->ID, 'moodle_user_id', true );

		if ( is_numeric( $moodle_user_id ) ) {
			global $profileuser;
			$user_id             = $user->ID;
			$enrolled_courses    = array();
			$notenrolled_courses = array();

			$course_args = array(
				'post_type'      => 'eb_course',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$courses     = get_posts( $course_args );

			$user_enrolled_courses = eb_get_user_enrolled_courses( $user_id );
			// make sure all the courses are in int.
			$user_enrolled_courses = array_map( 'intval', $user_enrolled_courses );
			?>
			<table>
				<tr>
				<?php
				wp_nonce_field( 'eb_mdl_course_enrollment', 'eb_mdl_course_enrollment' );
				?>
				<h3><?php esc_html_e( 'Enrolled Courses', 'edwiser-bridge' ); ?></h3>
				</tr>
				<tr>
					<td class="eb-profile-all-courses">
						<input type="text" id="eb-search-all-courses" placeholder="Search all courses">
						<select name="eb-all-courses" multiple="multiple" id="eb-all-courses">
							<?php
							foreach ( $courses as $course ) {
								if ( in_array( $course->ID, $user_enrolled_courses, true ) ) {
									continue;
								}
								echo "<option value='" . esc_html( $course->ID ) . "'>" . esc_html( $course->post_title ) . '</option>';
							}
							?>
						</select>
						<datalist id="eb-all-courses-list">
							<?php
							foreach ( $courses as $course ) {
								if ( in_array( $course->ID, $user_enrolled_courses, true ) ) {
									continue;
								}
								echo "<option value='" . esc_html( $course->ID ) . "'>" . esc_html( $course->post_title ) . '</option>';
							}
							?>
						</datalist>
					</td>
					<td class="eb-profile-enroll-icons">
						<a href="#" id="eb-profile-course-add">
							<span class="dashicons dashicons-arrow-right-alt"></span>
						</a>
						<a href="#" id="eb-profile-course-remove">
							<span class="dashicons dashicons-arrow-left-alt"></span>
						</a>
					</td>
					<td class="eb-profile-enroll-courses">
						<input type="hidden" name="eb_enroll_courses[]" value="<?php echo esc_html( wp_json_encode( $user_enrolled_courses ) ); ?>" id="eb_enroll_courses">
						<input type="text" id="eb-search-enrolled-courses" placeholder="Search enrolled courses">
						<select name="eb-enrolled-courses" multiple="multiple" id="eb-enrolled-courses">
							<?php
							foreach ( $courses as $course ) {
								if ( in_array( $course->ID, $user_enrolled_courses, true ) ) {
									echo "<option value='" . esc_html( $course->ID ) . "'>" . esc_html( $course->post_title ) . '</option>';
								}
							}
							?>
						</select>
						<datalist id="eb-enrolled-courses-list">
							<?php
							foreach ( $courses as $course ) {
								if ( in_array( $course->ID, $user_enrolled_courses, true ) ) {
									echo "<option value='" . esc_html( $course->ID ) . "'>" . esc_html( $course->post_title ) . '</option>';
								}
							}
							?>
						</datalist>
					</td>
				</tr>
			</table>

			<?php
		}

		return true;
	}

	/**
	 * Enroll or unenroll user courses on profile update.
	 * works on 'edit_user_profile_update' & 'personal_options_update' hook.
	 *
	 * @param int $user_id id of the user whose profile is updated.
	 *
	 * @return bool true
	 */
	public function update_courses_on_profile_update( $user_id ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Proceed if nonce is verified.
		if ( isset( $_POST['eb_mdl_course_enrollment'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eb_mdl_course_enrollment'] ) ), 'eb_mdl_course_enrollment' ) ) {

			$user = get_userdata( $user_id );

			// check if a moodle user account is already linked.
			$moodle_user_id = get_user_meta( $user->ID, 'moodle_user_id', true );

			if ( is_numeric( $moodle_user_id ) ) {

				$enroll_courses = array();
				if ( isset( $_POST['eb_enroll_courses'] ) ) {
					$enroll_courses = wp_unslash( (array) $_POST['eb_enroll_courses'] );
				}
				$enroll_courses = json_decode( $enroll_courses[0], true );

				$user_enrolled_courses = eb_get_user_enrolled_courses( $user_id );

				// enroll user to courses.
				$to_enroll   = array_diff( $enroll_courses, $user_enrolled_courses );
				$to_unenroll = array_diff( $user_enrolled_courses, $enroll_courses );

				if ( is_array( $to_enroll ) ) {
					// define args.
					$args = array(
						'user_id'           => $user->ID,
						'courses'           => $to_enroll,
						'complete_unenroll' => 0,
					);

					// enroll user to course.
					edwiser_bridge_instance()->enrollment_manager()->update_user_course_enrollment( $args );

					foreach ( $to_enroll as $course_id ) {
						$course = get_post( $course_id );
						// send email to user.
						$args = array(
							'user_email' => $user->user_email,
							'username'   => $user->user_login,
							'first_name' => $user->first_name,
							'last_name'  => $user->last_name,
							'course_id'  => $course_id,
						);

						do_action( 'eb_mdl_enrollment_trigger', $args );
					}
				}

				if ( is_array( $to_unenroll ) ) {
					// define args.
					$args = array(
						'user_id'           => $user->ID,
						'courses'           => $to_unenroll,
						'unenroll'          => 1,
						'complete_unenroll' => 1,
					);

					// enroll user to course.
					edwiser_bridge_instance()->enrollment_manager()->update_user_course_enrollment( $args );
				}
			}
		}
		return true;
	}

	/**
	 * Delete users enrollment records when user is permanently deleted from WordPress.
	 *
	 * @param int $user_id id of the user whose profile is updated.
	 */
	public function delete_enrollment_records_on_user_deletion( $user_id ) {
		global $wpdb;

		// removing user's records from enrollment table.
		$wpdb->delete( $wpdb->prefix . 'moodle_enrollment', array( 'user_id' => $user_id ), array( '%d' ) ); // @codingStandardsIgnoreLine

		edwiser_bridge_instance()->logger()->add( 'user', "Enrollment records of user ID: {$user_id} are deleted." );  // add user log.

		// send email to user.
		$user = get_userdata( $user_id );

		$args = array(
			'user_email' => $user->user_email,
			'username'   => $user->user_login,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		);
		do_action( 'eb_mdl_user_deletion_trigger', $args );
	}

	/**
	 * Unenroll on expire.
	 */
	public function unenroll_on_course_access_expire() {
		global $wpdb, $post;
		$cur_user    = get_current_user_id();
		$enroll_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moodle_enrollment WHERE  expire_time!='0000-00-00 00:00:00' AND expire_time<%s;", gmdate( 'Y-m-d H:i:s' ) ) ); // @codingStandardsIgnoreLine

		$enrollment_manager = Eb_Enrollment_Manager::instance( $this->plugin_name, $this->version );

		// Added for the bulk purchase plugin expiration functionality.
		$enroll_data = apply_filters( 'eb_user_list_on_course_expiration', $enroll_data );

		foreach ( $enroll_data as $course_enroll_data ) {

			$course_options = get_post_meta( $course_enroll_data->course_id, 'eb_course_options', true );

			$args = array(
				'user_id' => $course_enroll_data->user_id,
				'courses' => array( $course_enroll_data->course_id ),
			);
			// get expiration action.
			if ( isset( $course_options['course_expiry_action'] ) && 'suspend' === $course_options['course_expiry_action'] ) {

				$args['suspend'] = 1;
			} elseif ( isset( $course_options['course_expiry_action'] ) && 'do-nothing' === $course_options['course_expiry_action'] ) {

				continue;
			} else {

				$args['unenroll'] = 1;
			}

			$enrollment_manager->update_user_course_enrollment( $args );

		}
	}

	/**
	 * Link unlink user.
	 */
	public function moodle_link_unlink_user() {
		$responce = array( 'code' => 'failed' );

		// Proceed if nonce is verified.
		if ( isset( $_POST['user_id'] ) && isset( $_POST['link_user'] ) && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {

			$user_object = get_userdata( sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) );
			if ( sanitize_text_field( wp_unslash( $_POST['link_user'] ) ) ) {
				$flag = $this->link_moodle_user( $user_object );
				if ( ! $flag ) {
					$responce['msg'] = esc_html__( 'Failed to process the request.', 'edwiser-bridge' );
				} else {
					$responce['code'] = 'success';
					$responce['msg']  = sprintf( "%s'" . esc_html__( 's account has been linked successfully.', 'edwiser-bridge' ), $user_object->user_login );
				}
			} else {
				$deleted = ( delete_user_meta( sanitize_text_field( wp_unslash( $_POST['user_id'] ) ), 'moodle_user_id' ) );
				delete_user_meta( sanitize_text_field( wp_unslash( $_POST['user_id'] ) ), 'eb_user_password' );
				$responce['code'] = 'success';
				$responce['msg']  = sprintf( "%s'" . esc_html__( 's account has been unlinked successfully.', 'edwiser-bridge' ), $user_object->user_login );
			}
		} else {
			$responce['msg'] = esc_html__( 'Invalid ajax request.', 'edwiser-bridge' );
		}
		echo wp_json_encode( $responce );
		die();
	}

	/**
	 * Notices.
	 */
	public function moodle_link_unlink_user_notices() {
		echo "<div id='moodleLinkUnlinkUserNotices' class='updated'>
				 <p></p>
			  </div>";
	}

	/**
	 * Create dummy user. Used for Enrollment test functionality.
	 *
	 * @return int $user_id id of the dummy user created.
	 *
	 * @since 2.2.1
	 */
	public function create_dummy_user() {
		$response_array      = array(
			'status' => 'error',
		);
		$username            = 'ebdummyuser';
		$wp_user_created     = 0;
		$moodle_user_created = 0;
		// check if user already exists.
		$user = get_user_by( 'login', $username );
		if ( ! $user ) {
			// create dummy user.
			$user_data = array(
				'user_login' => $username,
				'user_pass'  => 'ebdummyuser',
				'user_email' => 'ebdummyuser@wdm.com',
				'role'       => get_option( 'default_role' ),
			);

			$user_id = wp_insert_user( $user_data );
			if ( is_wp_error( $user_id ) ) {
				$response_array['wp_message'] = '<div class="alert alert-error">' . __( 'WordPress User creation failed. ERROR : ', 'edwiser-bridge' ) . $user_id->get_error_message() . '</div>';
				return $response_array;
			} else {
				$user_id                      = $user_id;
				$wp_user_created              = 1;
				$response_array['wp_message'] = '<div class="alert alert-success">' . __( 'WordPress User created successfully', 'edwiser-bridge' ) . '</div>';
			}
		} else {
			$user_id                      = $user->ID;
			$wp_user_created              = 1;
			$response_array['wp_message'] = '<div class="alert alert-success">' . __( 'WordPress User already exists', 'edwiser-bridge' ) . '</div>';
		}

		// create moodle user.
		if ( $this->is_moodle_username_available( $username ) ) {
			$general_settings = get_option( 'eb_general' );
			$language         = 'en';
			if ( isset( $general_settings['eb_language_code'] ) ) {
				$language = $general_settings['eb_language_code'];
			}
			$moodle_user_data[0] = array(
				'username'  => $username,
				'password'  => 'ebdummyuser',
				'email'     => 'ebdummyuser@wdm.com',
				'firstname' => 'ebdummyuser',
				'lastname'  => 'ebdummyuser',
				'auth'      => 'manual',
				'lang'      => $language,
			);

			$webservice_function = 'core_user_create_users';
			$request_data        = array( 'users' => $moodle_user_data );
			$response            = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper(
				$webservice_function,
				$request_data
			);

			if ( 1 === $response['success'] && empty( $response['response_data'] ) ) {
				$response_array['moodle_message'] = '<div class="alert alert-error">Moodle User creation failed</div>';
			} elseif ( 1 === $response['success'] && is_array( $response['response_data'] ) && ! empty( $response['response_data'] ) ) {
				$moodle_user_id = $response['response_data'][0]->id;
				update_user_meta( $user_id, 'moodle_user_id', $moodle_user_id );
				$moodle_user_created              = 1;
				$response_array['moodle_message'] = '<div class="alert alert-success">' . __( 'Moodle User created successfully', 'edwiser-bridge' ) . '</div>';
			} elseif ( 0 === $response['success'] ) {
				$response_array['moodle_message'] = '<div class="alert alert-error">' . __( 'Moodle User creation failed. ERROR : ', 'edwiser-bridge' ) . '' . $response['response_message'] . '</div>';
				if ( \app\wisdmlabs\edwiserBridge\is_access_exception( $response ) ) {
					$mdl_settings_link      = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=service';
					$response_array['html'] = '<a target="_blank" href="' . $mdl_settings_link . '">' . __( 'Update webservice', 'edwiser-bridge' ) . '</a>' . __( ' OR ', 'edwiser-bridge' ) . '<a target="_blank" href="' . admin_url( '/admin.php?page=eb-settings&tab=connection' ) . '">' . __( 'Try test connection', 'edwiser-bridge' ) . '</a>';
				}
			}
		} else {
			$moodle_user = $this->get_moodle_user( 'ebdummyuser@wdm.com' );

			if ( isset( $moodle_user['user_exists'] ) && 1 === $moodle_user['user_exists'] && is_object( $moodle_user['user_data'] ) ) {
				update_user_meta( $user_id, 'moodle_user_id', $moodle_user['user_data']->id );
			}

			$moodle_user_created              = 1;
			$response_array['moodle_message'] = '<div class="alert alert-success">' . __( 'Moodle User already exists', 'edwiser-bridge' ) . '</div>';
		}

		if ( 1 === $wp_user_created && 1 === $moodle_user_created ) {
			$response_array['status'] = 'success';
		}

		echo wp_json_encode( $response_array );
		die();
	}

	/**
	 * Set user meta for email verification.
	 *
	 * @param  int $user_id user id.
	 */
	public function eb_user_email_verification_set_meta( $user_id ) {
		$eb_general_settings = get_option( 'eb_general' );
		// check if user is registered from edwiser bridge registration form.
		if ( isset( $_GET['action'] ) && 'eb_register' === $_GET['action'] && isset( $eb_general_settings['eb_email_verification'] ) && 'yes' === $eb_general_settings['eb_email_verification'] ) { // @codingStandardsIgnoreLine
			update_user_meta( $user_id, 'eb_user_email_verified', 0 );

			$this->eb_send_email_verification_link( $user_id );
		}
	}

	/**
	 * Redirect user to login page after registration.
	 *
	 * @param  string $redirect redirect url.
	 * @param  object $user     user object.
	 */
	public function eb_verify_registration_redirect( $redirect, $user = null ) {
		wp_logout();
		$query_args = add_query_arg(
			array(
				'eb_user_email_verification' => 1,
			),
			get_permalink()
		);
		return $query_args;
	}

	/**
	 * Authentication check during login.
	 *
	 * @param  object $user     user object.
	 * @param  string $username username.
	 * @param  string $password password.
	 */
	public function eb_user_authentication_check( $user, $username, $password ) {
		$eb_general_settings = get_option( 'eb_general' );
		if ( isset( $eb_general_settings['eb_email_verification'] ) && 'yes' === $eb_general_settings['eb_email_verification'] ) {
			// check the username against the email and username if user exist.
			$userdata = get_user_by( 'email', $username );
			if ( ! $userdata ) {
				$userdata = get_user_by( 'login', $username );
			}

			if ( ! $userdata ) {
				return $user;
			}

			$eb_user_email_verified = get_user_meta( $userdata->ID, 'eb_user_email_verified', true );
			$moodle_user_id         = get_user_meta( $userdata->ID, 'moodle_user_id', true );
			$resend_link            = add_query_arg(
				array(
					'action'                        => 'eb_user_verification_resend',
					'eb_user_email_verification_id' => $userdata->ID,
				)
			);
			$resend_link            = '<a href="' . $resend_link . '">' . __( 'Resend Verification Email', 'edwiser-bridge' ) . '</a>';

			if ( '' !== $eb_user_email_verified && 1 != $eb_user_email_verified ) { // @codingStandardsIgnoreLine
				$user = new \WP_Error( 'eb_user_email_verification', __( 'Your email is not verified. Please verify your email.', 'edwiser-bridge' ) . ' ' . $resend_link );
			}
		}
		return $user;
	}

	/**
	 * Verify user email.
	 */
	public function eb_user_email_verify() {
		$verification_key = isset( $_GET['eb_user_email_verification_key'] ) ? sanitize_text_field( $_GET['eb_user_email_verification_key'] ) : ''; // @codingStandardsIgnoreLine
		$verification_id  = isset( $_GET['eb_user_email_verification_id'] ) ? sanitize_text_field( $_GET['eb_user_email_verification_id'] ) : ''; // @codingStandardsIgnoreLine
		$action		      = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : ''; // @codingStandardsIgnoreLine

		if ( 'eb_user_email_verification' === $action ) {
			$eb_user_email_verification_key = get_user_meta( $verification_id, 'eb_user_email_verification_key', true );
			if ( $verification_key === $eb_user_email_verification_key ) {
				update_user_meta( $verification_id, 'eb_user_email_verified', 1 );
				$message = __( 'Your email is verified successfully.', 'edwiser-bridge' );
				// create moodle user.
				$user             = get_user_by( 'id', $verification_id );
				$general_settings = get_option( 'eb_general' );
				$language         = 'en';

				if ( isset( $general_settings['eb_language_code'] ) ) {
					$language = $general_settings['eb_language_code'];
				}

				$user_data = array(
					'username'  => $user->user_login,
					'password'  => wp_generate_password( 12, false ),
					'firstname' => $user->first_name,
					'lastname'  => $user->last_name,
					'email'     => $user->user_email,
					'auth'      => 'manual',
					'lang'      => $language,
				);

				$moodle_user = $this->create_moodle_user( $user_data );
				if ( isset( $moodle_user['user_created'] ) && 1 === $moodle_user['user_created'] && is_object( $moodle_user['user_data'] ) ) {
					update_user_meta( $verification_id, 'moodle_user_id', $moodle_user['user_data']->id );
				}

				do_action( 'eb_user_email_verified', $user->ID );

				// login user.
				wp_clear_auth_cookie();
				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID );
				do_action( 'wp_login', $user->user_login, $user );

			} else {
				$message = __( 'Your email verification link is invalid.', 'edwiser-bridge' );
			}
			// register and localize script.
			wp_register_script( 'eb-user-email-verification', false ); // @codingStandardsIgnoreLine
			wp_enqueue_script( 'eb-user-email-verification' );
			wp_localize_script(
				'eb-user-email-verification',
				'eb_user_email_verification',
				array(
					'message' => $message,
				)
			);
		} elseif ( 'eb_user_verification_resend' === $action ) {

			$eb_user_email_verified = get_user_meta( $verification_id, 'eb_user_email_verified', true );
			if ( '' !== $eb_user_email_verified && 1 != $eb_user_email_verified ) { // @codingStandardsIgnoreLine
				$this->eb_send_email_verification_link( $verification_id );
				$message = __( 'Verification email has been sent to your email address.', 'edwiser-bridge' );
				// register and localize script.
				wp_register_script( 'eb-user-email-verification', false ); // @codingStandardsIgnoreLine
				wp_enqueue_script( 'eb-user-email-verification' );
				wp_localize_script(
					'eb-user-email-verification',
					'eb_user_email_verification',
					array(
						'message' => $message,
					)
				);
			}
		}
	}

	/**
	 * Send email verification link to user.
	 *
	 * @param object $user_id user id.
	 */
	public function eb_send_email_verification_link( $user_id ) {
		// generate verification code.
		$verification_key = wp_generate_password( 20, false );
		update_user_meta( $user_id, 'eb_user_email_verification_key', $verification_key );
		// generate verification link.
		$verification_link = add_query_arg(
			array(
				'action'                         => 'eb_user_email_verification',
				'eb_user_email_verification_key' => $verification_key,
				'eb_user_email_verification_id'  => $user_id,
			),
			get_site_url()
		);
		$verification_link = "<a href='$verification_link'>Verify</a>";
		// send verification email.
		$user       = get_user_by( 'id', $user_id );
		$first_name = isset( $_POST['firstname'] ) ? sanitize_text_field( $_POST['firstname'] ) : $user->first_name; // @codingStandardsIgnoreLine
		$last_name  = isset( $_POST['lastname'] ) ? sanitize_text_field( $_POST['lastname'] ) : $user->last_name; // @codingStandardsIgnoreLine
		$args       = array(
			'user_email' => $user->user_email,
			'username'   => $user->user_login,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'verify_url' => $verification_link,
		);
		do_action( 'eb_new_user_email_verification_trigger', $args );
	}
}
