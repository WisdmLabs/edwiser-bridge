<?php

/**
 * This class defines all code necessary manage user course enrollment.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EBEnrollmentManager
{
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
     * @var EBEnrollmentManager The single instance of the class
     *
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * Main EBEnrollmentManager Instance.
     *
     * Ensures only one instance of EBEnrollmentManager is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     *
     * @see EBEnrollmentManager()
     *
     * @return EBEnrollmentManager - Main instance
     */
    public static function instance($plugin_name, $version)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($plugin_name, $version);
        }

        return self::$instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since   1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'eb-textdomain'), '1.0.0');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since   1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'eb-textdomain'), '1.0.0');
    }

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Used to enroll user to course(s)
     * Enrolls user to course on moodle on course purchase as well as update enrollment data on WordPress.
     *
     * @param array $args arguments array
     * @param bool false
     *
     * @return bool true / false
     */
    public function updateUserCourseEnrollment($args)
    {
        //global $wpdb;

        // default args
        $defaults = array(
            'user_id' => 0,
            'role_id' => 5,
            'courses' => array(),
            'unenroll' => 0,
            'suspend' => 0,
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults.
         */
        $args = wp_parse_args($args, $defaults);

        // get moodle user id of user
        $moodle_user_id = get_user_meta($args['user_id'], 'moodle_user_id', true);
        $msg = '';
        if ($moodle_user_id) {
            $msg = 'Yes, Moodle user ID is: '.$moodle_user_id;
        } else {
            $msg = 'No, Exiting!!!';
        }

        edwiserBridgeInstance()->logger()->add(
            'user',
            'Associated moodle a/c found? :'.($msg)
        ); // add user log

        // exit if no associated moodle user found.
        if (!is_numeric($moodle_user_id)) {
            return;
        }

        // get moodle course id of each course
        // we are fetching course id  of each course as on moodle, to send enrollment request on moodle.
        $moodle_courses = array_map(
            array(edwiserBridgeInstance()->courseManager(), 'getMoodleCourseId'),
            $args['courses']
        );

        // logging
        // add user log
        edwiserBridgeInstance()->logger()->add('user', 'Course IDs: '.serialize($args['courses']));
        // add user log
        edwiserBridgeInstance()->logger()->add('user', 'Respective moodle course IDs: '.serialize($moodle_courses));
        // add user log
        edwiserBridgeInstance()->logger()->add('user', "\n");

        $enrolments = array();
        $role_id = $args['role_id']; // the role id 5 denotes student role on moodle

        // define moodle webservice function to use
        if ($args['unenroll'] == 0) {
            $webservice_function = 'enrol_manual_enrol_users';
        } elseif ($args['unenroll'] == 1) {
            $webservice_function = 'enrol_manual_unenrol_users';
        }

        // prepare course array
        foreach ($moodle_courses as $key => $moodle_course) {
            // first we check if a moodle course id exists
            if ($moodle_course != '') {
                $enrolments[$key] = array(
                    'roleid' => $role_id,
                    'userid' => $moodle_user_id,
                    'courseid' => $moodle_course,
                );

                // we only add suspend parameter when we are enrolling or suspending a user.
                // in case user is being unenrolled, no suspend parameter is expected in webservice function.
                if ($args['unenroll'] == 0) {
                    $enrolments[$key]['suspend'] = $args['suspend'];
                }
            }
        }

        // prepare request data
        $request_data = array('enrolments' => $enrolments);
        $response = edwiserBridgeInstance()->connectionHelper()->connectMoodleWithArgsHelper(
            $webservice_function,
            $request_data
        );
        
        // update enrollment details on wordpress enrollment table
        if ($response['success']) {
            // define args
            $args = array(
                'user_id' => $args['user_id'],
                'role_id' => $args['role_id'],
                'courses' => $args['courses'],
                'unenroll' => $args['unenroll'],
                'suspend' => $args['suspend'],
            );

            $this->updateEnrollmentRecordWordpress($args);
        }

        /*
         * hook to execute custom function on user course enrollment update.
         * $courses is passed as argument containing courses for which user is enrolled.
         * response is passed to know if request is successful or not
         */
        do_action('eb_user_courses_updated', $args['user_id'], $response['success'], $args['courses']);

        return $response['success'];
    }

    /**
     * We have to update our enrollment table on wordpress everytime a user is enrolled
     * or unenrolled from moodle.
     *
     * @since  1.0.0
     * @m
     *
     * @param array $args arguments array
     *
     * @return bool true
     */
    public function updateEnrollmentRecordWordpress($args)
    {
        global $wpdb;

        // default args
        $defaults = array(
            'user_id' => 0,
            'role_id' => 5,
            'courses' => array(),
            'unenroll' => 0,
            'suspend' => 0,
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults.
         */
        $args = wp_parse_args($args, $defaults);
        $role_id = $args['role_id']; // the role id 5 denotes student role on moodle

        // add enrollment record in DB conditionally
        // We are using user's wordpress ID and course's wordpress ID while saving record in enrollment table.
        if ($args['unenroll'] == 0 && $args['suspend'] == 0) {
            foreach ($args['courses'] as $key => $course_id) {
                if (edwiserBridgeInstance()->courseManager()->getMoodleCourseId($course_id) != '' &&
                    !$this->userHasCourseAccess($args['user_id'], $course_id)) {
                    $wpdb->insert(
                        $wpdb->prefix.'moodle_enrollment',
                        array(
                            'user_id' => $args['user_id'],
                            'course_id' => $course_id,
                            'role_id' => $role_id,
                            'time' => date('Y-m-d H:i:s'),
                        ),
                        array(
                            '%d',
                            '%d',
                            '%d',
                            '%s',
                        )
                    );
                }
            }
        } elseif ($args['unenroll'] == 1 || $args['suspend'] == 1) {
            foreach ($args['courses'] as $key => $course_id) {
                $this->deleteUserEnrollmentRecord($args['user_id'], $course_id);
            }
        }
    }

    /**
     * Right now executes on user course synchronization action.
     *
     * This function just removes enrollment entry from enrollment table on wordpress,
     * only if a user has been unenrolled from a course on moodle
     *
     * @since  1.0.0
     *
     * @param int $user_id   WordPress user id of a user
     * @param int $course_id WordPress course id of a course
     *
     * @return bool true
     */
    public function deleteUserEnrollmentRecord($user_id, $course_id)
    {
        global $wpdb;

        // removing user enrolled courses from plugin db
        $deleted = $wpdb->delete(
            $wpdb->prefix.'moodle_enrollment',
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
            ),
            array(
                '%d',
                '%d',
            )
        );

        if ($deleted) {
            edwiserBridgeInstance()->logger()->add('user', "Unenrolled user: {$user_id} from course {$course_id}");  // add user log
        }
    }

    /**
     * used to check if a user has access to a course.
     *
     * @since  1.0.0
     *
     * @param int $user_id   WordPress user id of a user
     * @param int $course_id WordPress course id of a course
     *
     * @return bool true / false
     */
    public function userHasCourseAccess($user_id, $course_id)
    {
        global $wpdb;
        $has_access = false;

        if ($user_id == '' || $course_id == '') {
            return $has_access;
        }

        //check if user has access to course
        $result = $wpdb->get_var(
            "SELECT user_id
            FROM {$wpdb->prefix}moodle_enrollment
            WHERE course_id={$course_id}
            AND user_id={$user_id};"
        );

        if ($result == $user_id) {
            $has_access = true;
        }

        return $has_access;
    }
}
