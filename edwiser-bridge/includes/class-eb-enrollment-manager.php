<?php
/**
 * This class defines all code necessary manage user course enrollment.
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
 * Enollment manager.
 */
class Eb_Enrollment_Manager {

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
	 * Manager.
	 *
	 * @var Eb_Enrollment_Manager The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main Eb_Enrollment_Manager Instance.
	 *
	 * Ensures only one instance of Eb_Enrollment_Manager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @param text $plugin_name name.
	 * @param text $version version.
	 *
	 * @see Eb_Enrollment_Manager()
	 *
	 * @return Eb_Enrollment_Manager - Main instance
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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'eb-textdomain' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since   1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'eb-textdomain' ), '1.0.0' );
	}

	/**
	 * COnstructor.
	 *
	 * Ensures only one instance of Eb_Enrollment_Manager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @param text $plugin_name name.
	 * @param text $version version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * DEPRECATED FUNCTION.
	 * Used to enroll user to course(s)
	 * Enrolls user to course on moodle on course purchase as well as update enrollment data on WordPress.
	 *
	 * @deprecated since 2.0.1 use update_user_course_enrollment( $args, $role_id ) insted.
	 * @param array $args arguments array.
	 * @param bool  $role_id false.
	 *
	 * @return bool true / false
	 */
	public function updateUserCourseEnrollment( $args, $role_id = '5' ) {
		return $this->update_user_course_enrollment( $args, $role_id );
	}


	/**
	 * Used to enroll user to course(s)
	 * Enrolls user to course on moodle on course purchase as well as update enrollment data on WordPress.
	 *
	 * @param array $args arguments array.
	 * @param bool  $role_id false.
	 */
	public function update_user_course_enrollment( $args, $role_id = '5' ) {
		$defaults = array(
			'user_id'           => 0,
			'role_id'           => $role_id,
			'courses'           => array(),
			'unenroll'          => 0,
			'suspend'           => 0,
			'complete_unenroll' => 0,
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults.
		 */
		$args = wp_parse_args( $args, $defaults );
		// get moodle user id of user.
		$moodle_user_id = get_user_meta( $args['user_id'], 'moodle_user_id', true );
		$msg            = '';
		if ( $moodle_user_id ) {
			$msg = 'Yes, Moodle user ID is: ' . $moodle_user_id;
		} else {
			$msg = 'No, Exiting!!!';
		}

		edwiser_bridge_instance()->logger()->add(
			'user',
			'Associated moodle a/c found? :' . ( $msg )
		); // add user log.

		// exit if no associated moodle user found.
		if ( ! is_numeric( $moodle_user_id ) ) {
			return false;
		}

		// get moodle course id of each course.
		// we are fetching course id  of each course as on moodle, to send enrollment request on moodle.
		$moodle_courses_raw = array_map(
			array( edwiser_bridge_instance()->course_manager(), 'get_moodle_wp_course_id_pair' ),
			$args['courses']
		);
		$moodle_courses     = array();
		foreach ( $moodle_courses_raw as $course ) {
			$moodle_courses[ key( $course ) ] = reset( $course );
		}

		edwiser_bridge_instance()->logger()->add( 'user', 'Course IDs: ' . serialize( $args['courses'] ) ); // @codingStandardsIgnoreLine
		// add user log.
		edwiser_bridge_instance()->logger()->add( 'user', 'Respective moodle course IDs: ' . serialize( $moodle_courses ) ); // @codingStandardsIgnoreLine
		// add user log.
		edwiser_bridge_instance()->logger()->add( 'user', "\n" );

		$enrolments          = array();
		$role_id             = $args['role_id']; // the role id 5 denotes student role on moodle.
		$webservice_function = $this->get_moodle_web_service_function( $args['unenroll'] );

		// prepare course array.
		foreach ( $moodle_courses as $wp_course_id => $moodle_course_id ) {
			// first we check if a moodle course id exists.
			if ( '' !== $moodle_course_id ) {
				$expire_date                 = $this->calc_course_acess_expiry_date( $wp_course_id );
				$enrolments[ $wp_course_id ] = array(
					'roleid'   => $role_id,
					'userid'   => $moodle_user_id,
					'courseid' => $moodle_course_id,
				);
				if ( 'enrol_manual_enrol_users' === $webservice_function && '0000-00-00 00:00:00' !== $expire_date ) {
					$enrolments[ $wp_course_id ]['timestart'] = strtotime( gmdate( 'Y-m-d H:i:s' ) );
					$enrolments[ $wp_course_id ]['timeend']   = strtotime( $expire_date );
				}

				// we only add suspend parameter when we are enrolling or suspending a user.
				// in case user is being unenrolled, no suspend parameter is expected in webservice function.
				if ( 0 === $args['unenroll'] ) {
					$enrolments[ $wp_course_id ]['suspend'] = $args['suspend'];
				}
			}
		}

		$response = array();
		// If enrolling is enabled then process Moodle request if unenrollment triggered then first check the count and then process request.

		if ( 1 !== $args['unenroll'] ) {

			// prepare request data.
			$request_data = array( 'enrolments' => $enrolments );
			$response     = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper(
				$webservice_function,
				$request_data
			);
		} elseif ( 1 === $args['unenroll'] ) {

			foreach ( $args['courses'] as $key => $course_id ) {

				// Get User Course access Count.
				$act_cnt = $this->get_user_course_access_count( $args['user_id'], $course_id );

				// decrease the count value.

				if ( $act_cnt <= 1 || $args['complete_unenroll'] ) {

					// update decreased count value.
					$request_data = array( 'enrolments' => $enrolments );
					$response     = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper(
						$webservice_function,
						$request_data
					);
				} elseif ( $act_cnt > 1 && ! $args['complete_unenroll'] ) {

					// delete row if count equals zero.

					// Process Moodle unenrollment.
					// prepare request data.
					$this->update_user_course_access_count( $args['user_id'], $course_id, $act_cnt-- );
					$response['success'] = 1;
				}
			}
				// Trigger email.
		}

		// update enrollment details on WordPress enrollment table.
		if ( isset( $response['success'] ) && $response['success'] ) {
			// define args.
			$args = array(
				'user_id'           => $args['user_id'],
				'role_id'           => $args['role_id'],
				'courses'           => $args['courses'],
				'unenroll'          => $args['unenroll'],
				'suspend'           => $args['suspend'],
				'complete_unenroll' => $args['complete_unenroll'],

			);

			$this->update_enrollment_record_wordpress( $args );
		}

		/*
		 * hook to execute custom function on user course enrollment update.
		 * $courses is passed as argument containing courses for which user is enrolled.
		 * response is passed to know if request is successful or not
		 */
		do_action( 'eb_user_courses_updated', $args['user_id'], isset( $response['success'] ) ? $response['success'] : 0, $args['courses'] );

		return isset( $response['success'] ) ? $response['success'] : 0;
	}



	/**
	 * Enroll count.
	 *
	 * @param text $args args.
	 * @param text $wp_course_id wp_course_id.
	 */
	public function check_enroll_count( $args, $wp_course_id ) {
		global $wpdb;

		if ( isset( $args['complete_unenroll'] ) && ! $args['complete_unenroll'] ) {

			$result = $wpdb->get_var( $wpdb->prepare( "SELECT act_cnt FROM {$wpdb->prefix}moodle_enrollment WHERE course_id=%d AND user_id=%d;", $wp_course_id, $args['user_id'] ) ); // @codingStandardsIgnoreLine

			if ( $result > 1 ) {
				return 0;
			}
		}

		return 1;
	}

	/**
	 * Service function.
	 *
	 * @param text $unenroll unenroll.
	 */
	private function get_moodle_web_service_function( $unenroll ) {
		if ( 0 === $unenroll ) {
			return 'enrol_manual_enrol_users';
		} elseif ( 1 === $unenroll ) {
			return 'enrol_manual_unenrol_users';
		}
	}

	/**
	 * We have to update our enrollment table on WordPress everytime a user is enrolled.
	 * or unenrolled from moodle.
	 *
	 * @since  1.0.0
	 *
	 * @deprecated since 2.0.1 use update_enrollment_record_wordpress($args, $role_id) insted
	 * @param array $args arguments array.
	 * @param array $role_id role_id array.
	 */
	public function updateEnrollmentRecordWordpress( $args, $role_id = '5' ) {
		$this->update_enrollment_record_wordpress( $args, $role_id );
	}

	/**
	 * We have to update our enrollment table on WordPress everytime a user is enrolled
	 * or unenrolled from moodle.
	 *
	 * @since  1.0.0
	 * @m
	 *
	 * @param array $args arguments array.
	 * @param array $role_id role_id array.
	 */
	public function update_enrollment_record_wordpress( $args, $role_id = '5' ) {
		global $wpdb;

		// default args.
		$defaults = array(
			'user_id'           => 0,
			'role_id'           => $role_id,
			'courses'           => array(),
			'unenroll'          => 0,
			'suspend'           => 0,
			'complete_unenroll' => 0,
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults.
		 */
		$args = wp_parse_args( $args, $defaults );

		$role_id = $args['role_id']; // the role id 5 denotes student role on moodle
		// add enrollment record in DB conditionally
		// We are using user's WordPress ID and course's WordPress ID while saving record in enrollment table.
		if ( 0 === $args['unenroll'] && 0 === $args['suspend'] ) {
			foreach ( $args['courses'] as $key => $course_id ) {
				// Get User Course Access Count.
				$act_cnt = $this->get_user_course_access_count( $args['user_id'], $course_id );

				// If not enrolled to any of the coursers.
				if ( '' !== edwiser_bridge_instance()->course_manager()->get_moodle_course_id( $course_id ) &&
						! $this->user_has_course_access( $args['user_id'], $course_id ) ) {
					// Set timezone.

					// New code for time.
					$expire_date = $this->calc_course_acess_expiry_date( $course_id );

					$wpdb->insert( // @codingStandardsIgnoreLine
						$wpdb->prefix . 'moodle_enrollment',
						array(
							'user_id'     => $args['user_id'],
							'course_id'   => $course_id,
							'role_id'     => $role_id,
							'time'        => gmdate( 'Y-m-d H:i:s' ),
							'expire_time' => $expire_date,
							'act_cnt'     => 1,
						),
						array(
							'%d',
							'%d',
							'%d',
							'%s',
							'%s',
							'%d',
						)
					);

				} elseif ( $this->user_has_course_access( $args['user_id'], $course_id ) && false !== $act_cnt ) {
					// increase the count value.
					$act_cnt = ++$act_cnt;
					// update increased count value.
					$this->update_user_course_access_count( $args['user_id'], $course_id, $act_cnt );
				}
			}
			// Trigger Email.
		} elseif ( 1 === (int) trim( $args['unenroll'] ) ) {
			foreach ( $args['courses'] as $key => $course_id ) {
				// Get User Course Access Count.
				$act_cnt = $this->get_user_course_access_count( $args['user_id'], $course_id );

				// decrease the count value.
				$act_cnt = --$act_cnt;

				if ( $act_cnt <= 0 || $args['complete_unenroll'] ) {
					// delete row if count equals zero.
					$this->delete_user_enrollment_record( $args['user_id'], $course_id );
				} elseif ( 0 !== $act_cnt && ! $args['complete_unenroll'] ) {
					// update decreased count value.
					$this->update_user_course_access_count( $args['user_id'], $course_id, $act_cnt );
				}
			}
			// Trigger email.
		} else {
			// Handle suspend action.
			foreach ( $args['courses'] as $key => $course_id ) {
				$this->update_user_course_suspend_status( $args['user_id'], $course_id );
			}
			// update only DB column suspended as 1.
		}
	}



	/**
	 * DEPRECATED FUNCTION
	 * used to update the count of users access to a course.
	 *
	 * @since  1.2.5
	 *
	 * @deprecated since 2.0.1 use update_user_course_access_count( $user_id, $course_id, $count ) insted.
	 * @param int $user_id   WordPress user id of a user.
	 * @param int $course_id WordPress course id of a course.
	 * @param int $count WordPress course id of a course.
	 */
	public function updateUserCourseAccessCount( $user_id, $course_id, $count ) {
		$this->update_user_course_access_count( $user_id, $course_id, $count );
	}





	/**
	 * Used to update the count of users access to a course.
	 *
	 * @since  1.2.5
	 *
	 * @param int $user_id   WordPress user id of a user.
	 * @param int $course_id WordPress course id of a course.
	 * @param int $count WordPress course id of a course.
	 */
	public function update_user_course_access_count( $user_id, $course_id, $count ) {
		global $wpdb;
		$wpdb->update( // @codingStandardsIgnoreLine
			$wpdb->prefix . 'moodle_enrollment',
			array(
				'act_cnt'   => $count,   // increase OR decrease count value.
				'suspended' => 0,
			),
			array(
				'user_id'   => $user_id,
				'course_id' => $course_id,
			),
			array(
				'%d',
			),
			array(
				'%d',
				'%d',
			)
		);
	}


	/**
	 * Used to update user suspend action.
	 *
	 * @since  1.2.5
	 *
	 * @param int $user_id   WordPress user id of a user.
	 * @param int $course_id WordPress course id of a course.
	 */
	public function update_user_course_suspend_status( $user_id, $course_id ) {
		global $wpdb;
		$wpdb->update( // @codingStandardsIgnoreLine
			$wpdb->prefix . 'moodle_enrollment',
			array(
				'suspended' => 1,   // increase OR decrease count value.
			),
			array(
				'user_id'   => $user_id,
				'course_id' => $course_id,
			),
			array(
				'%d',
			),
			array(
				'%d',
				'%d',
			)
		);
	}


	/**
	 * Expiry date.
	 *
	 * @param int $course_id WordPress course id of a course.
	 */
	public function calc_course_acess_expiry_date( $course_id ) {
		$course_meta      = get_post_meta( $course_id, 'eb_course_options', true );
		$expiry_date_time = '0000-00-00 00:00:00';
		if ( isset( $course_meta['course_expirey'] ) && 'yes' === $course_meta['course_expirey'] ) {
			$expiry_date_time = gmdate( 'Y-m-d H:i:s', strtotime( '+' . $course_meta['num_days_course_access'] . ' days' ) );
		}
		return $expiry_date_time;
	}

	/**
	 * Right now executes on user course synchronization action.
	 *
	 * This function just removes enrollment entry from enrollment table on WordPress,
	 * only if a user has been unenrolled from a course on moodle
	 *
	 * @since  1.0.0
	 *
	 * @param int $user_id   WordPress user id of a user.
	 * @param int $course_id WordPress course id of a course.
	 */
	public function delete_user_enrollment_record( $user_id, $course_id ) {
		global $wpdb;

		// removing user enrolled courses from plugin db.
		$deleted = $wpdb->delete( // @codingStandardsIgnoreLine
			$wpdb->prefix . 'moodle_enrollment',
			array(
				'user_id'   => $user_id,
				'course_id' => $course_id,
			),
			array(
				'%d',
				'%d',
			)
		);

		if ( $deleted ) {
			$user = get_userdata( $user_id );
			$args = array(
				'username'   => $user->user_login,
				'first_name' => $user->user_firstname,
				'last_name'  => $user->user_lastname,
				'user_email' => $user->user_email,
				'course_id'  => $course_id,
			);
			do_action( 'eb_course_access_expire_alert', $args );
			edwiser_bridge_instance()->logger()->add( 'user', "Unenrolled user: {$user_id} from course {$course_id}" );  // add user log.
		}
	}



	/**
	 * DEPRECATED FUNCTION
	 * Right now executes on user course synchronization action.
	 *
	 * This function just removes enrollment entry from enrollment table on WordPress,
	 * only if a user has been unenrolled from a course on moodle
	 *
	 * @since  1.0.0
	 *
	 * @deprecated since 2.0.1 use delete_user_enrollment_record( $user_id, $course_id )  insted.
	 * @param int $user_id   WordPress user id of a user.
	 * @param int $course_id WordPress course id of a course.
	 */
	public function deleteUserEnrollmentRecord( $user_id, $course_id ) {
		$this->delete_user_enrollment_record( $user_id, $course_id );
	}


	/**
	 * Used to check if a user has access to a course.
	 *
	 * @since  1.0.0
	 *
	 * @deprecated since 2.0.1 use user_has_course_access( $user_id, $course_id ); insted.
	 * @param int $user_id   WordPress user id of a user.
	 * @param int $course_id WordPress course id of a course.
	 *
	 * @return bool true / false
	 */
	public function userHasCourseAccess( $user_id, $course_id ) {
		return $this->user_has_course_access( $user_id, $course_id );
	}




	/**
	 * Used to check if a user has access to a course.
	 *
	 * @since  1.0.0
	 *
	 * @param int $user_id   WordPress user id of a user.
	 * @param int $course_id WordPress course id of a course.
	 */
	public function user_has_course_access( $user_id, $course_id ) {

		global $wpdb;
		$has_access = false;

		if ( '' === $user_id || '' === $course_id ) {
			return $has_access;
		}

		$result = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}moodle_enrollment WHERE course_id=%d AND user_id=%d;", $course_id, $user_id ) ); // @codingStandardsIgnoreLine

		if ( trim( $result ) === trim( $user_id ) ) {
			$has_access = true;
		}

		return $has_access;
	}


	/**
	 * Used to get the count of users access to a course.
	 *
	 * @since  1.2.5
	 *
	 * @param int $user_id   WordPress user id of a user.
	 * @param int $course_id WordPress course id of a course.
	 *
	 * @return bool/int (true/ false)/ account access count
	 */
	public function get_user_course_access_count( $user_id, $course_id ) {
		global $wpdb;
		$act_cnt = false;

		if ( '' === $user_id || '' === $course_id ) {
			return $act_cnt;
		}

		$act_cnt = $wpdb->get_var( $wpdb->prepare( "SELECT act_cnt FROM {$wpdb->prefix}moodle_enrollment WHERE course_id=%d AND user_id=%d;", $course_id, $user_id ) ); // @codingStandardsIgnoreLine
		return $act_cnt;
	}

	/**
	 * Access remaining.
	 *
	 * @since  1.2.5
	 *
	 * @param int $user_id   WordPress user id of a user.
	 * @param int $course_id WordPress course id of a course.
	 */
	public static function access_remianing( $user_id, $course_id ) {
		global $wpdb;
		$curr_date   = new \DateTime( ( gmdate( 'Y-m-d H:i:s' ) ) );
		$expire_date = new \DateTime( ( $wpdb->get_var( $wpdb->prepare( "SELECT expire_time	FROM {$wpdb->prefix}moodle_enrollment WHERE course_id=%d AND user_id=%d;", $course_id, $user_id ) ) ) ); // @codingStandardsIgnoreLine

		return $curr_date->diff( $expire_date )->format( '%a' );
	}
}
