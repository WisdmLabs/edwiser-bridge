<?php
/**
 * This class defines all code necessary to manage user's moodle & WP account'.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EBUserManager
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
     * @see edwiserBridgeInstance()
     *
     * @return EDW - Main instance
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
     * Login a user (set auth cookie and set global user object).
     *
     * @param int $user_id
     */
    public function setUserAuthCookie($user_id)
    {
        global $current_user;

        $current_user = get_user_by('id', $user_id);

        wp_set_auth_cookie($user_id, true);
    }

    /**
     * Initiate the user courses synchronization process, get user's all courses from moodle
     * and enroll him to the same courses on wordpress end.
     *
     * Called by user_data_synchronization_initiater() from class EBSettingsAjaxInitiater
     *
     * @param array $sync_options    user sync options
     * @param int   $user_id_to_sync to only sync courses of an individual user on registration
     * @param int  $offset LIMIT query offset
     *
     * @since   1.0.0
     *
     * @return array $response     array containing status & response message
     */
    public function userCourseSynchronizationHandler($sync_options = array(), $user_id_to_sync = '', $offset = 0)
    {
        global $wpdb;
        // $response_array['process_completed'] = 0;
        // checking if moodle connection is working properly
        $connected = edwiserBridgeInstance()->connectionHelper()->connectionTestHelper(EB_ACCESS_URL, EB_ACCESS_TOKEN);

        $response_array['connection_response'] = $connected['success']; // add connection response in response array
        $wp_users_count = 1;
        if ($connected['success'] == 1) {
            // get all wordpress users having an associated moodle account
            if (is_numeric($user_id_to_sync)) {
                $all_users = $wpdb->get_results(
                    "SELECT user_id, meta_value AS moodle_user_id
                    FROM {$wpdb->base_prefix}usermeta
                    WHERE user_id = ".$user_id_to_sync." AND meta_key = 'moodle_user_id' AND meta_value IS NOT NULL",
                    ARRAY_A
                );
            } else {
                 // query to get all wordpress users having an associated moodle account so that we can synchronize the course enrollment
                // added limit for get users in chunk
                $all_users = $wpdb->get_results(
                    "SELECT user_id, meta_value AS moodle_user_id
                    FROM {$wpdb->base_prefix}usermeta
                    WHERE meta_key = 'moodle_user_id'
                    AND meta_value IS NOT NULL
                    ORDER BY user_id ASC
                    LIMIT ".$offset.", 20",
                    ARRAY_A
                );
                // used to get all users count
                $users_count = $wpdb->get_results(
                    "SELECT COUNT(user_id) AS users_count
                    FROM {$wpdb->base_prefix}usermeta
                    WHERE meta_key = 'moodle_user_id'
                    AND meta_value IS NOT NULL"
                );
                $wp_users_count = $users_count[0]->users_count;
            }

            // get courses of each user having a moodle a/c assosiated
            foreach ($all_users as $key => $value) {
                $key;
                // sync users courses only if checkbox is checked
                if (isset($sync_options['eb_synchronize_user_courses']) &&
                        $sync_options['eb_synchronize_user_courses'] == 1) {
                    // get user's enrolled courses from moodle
                    $moodle_user_courses = edwiserBridgeInstance()->courseManager()->getMoodleCourses($value['moodle_user_id']);

                    $enrolled_courses = array(); // push user's all enrolled courses id in array
                    // enrol user to courses based on recieved data
                    if ($moodle_user_courses['success'] == 1) {
                        foreach ($moodle_user_courses['response_data'] as $course_data) {
                            // get wordpress id of course
                            $existing_course_id = edwiserBridgeInstance()->courseManager()->isCoursePresynced($course_data->id);

                            // enroll user to course if course exist on wordpress ( synchronized on wordpress )
                            if (is_numeric($existing_course_id)) {
                                // add enrolled courses id in array
                                $enrolled_courses[] = $existing_course_id;

                                // define args
                                $args = array(
                                    'user_id' => $value['user_id'],
                                    'courses' => array($existing_course_id),
                                );
                                // update enrollment records
                                edwiserBridgeInstance()->enrollmentManager()->updateEnrollmentRecordWordpress($args);
                                edwiserBridgeInstance()->logger()->add(
                                    'user',
                                    'New course enrolled,
                                    User ID: '.$value['user_id'].' Course ID: '.$existing_course_id
                                ); // add user log
                            }
                        }
                    } else {
                        // Push user's id to separate array,
                        // if there is a problem in fetching his/her courses from moodle.
                        $response_array['user_with_error'][] = $value['user_id'];
                        $response_array['user_with_error'][] .= '<strong>' . __('User ID:', 'eb-textdomain') . ' </strong>'.$value['user_id'];
                        $response_array['user_with_error'][] .= '</p><br/>';
                    }

                    /*
                     * In this block we are unenrolling user course if a user is unenrolled from those course on moodle
                     */
                    $old_enrolled_courses = $wpdb->get_results(
                        "SELECT course_id
                        FROM {$wpdb->prefix}moodle_enrollment
                        WHERE user_id = ".$value['user_id'],
                        ARRAY_A
                    );

                    // get user's existing enrollment record from wordpress DB
                    $notenrolled_courses = array();

                    foreach ($old_enrolled_courses as $existing_course) {
                        if (!in_array($existing_course['course_id'], $enrolled_courses)) {
                            $notenrolled_courses[] = $existing_course['course_id'];
                        }
                    }

                    if (is_array($notenrolled_courses) && !empty($notenrolled_courses)) {
                        // define args
                        $args = array(
                            'user_id' => $value['user_id'],
                            'courses' => $notenrolled_courses,
                            'unenroll' => 1,
                        );
                        edwiserBridgeInstance()->enrollmentManager()->updateEnrollmentRecordWordpress($args);
                    }
                    /* Unenrollment part completed * */
                }

                /*
                 * hook that can be used when a single users data sync completes
                 * total courses in which user is enrolled after sync is given as an argument with user id
                 * we are passing users wordpress id and course id (as on wordpress)
                 */
                do_action('eb_user_synchronization_complete_single', $value['user_id'], $sync_options);
            }
            // these two properties are used to track, how many user's data have beedn updated.
            $response_array['users_count'] = count($all_users);
            $response_array['wp_users_count'] = $wp_users_count;

            /*
             * hook to be run on user data sync total completion
             * we are passing all user ids for which sync is performed
             */
            do_action('eb_user_synchronization_complete', $all_users, $sync_options);
        } else {
            edwiserBridgeInstance()->logger()->add(
                'user',
                'Connection problem in synchronization, Response:'.print_r($connected, true)
            ); // add connection log
        }

        return $response_array;
    }
    /**
     * Initiate the process to link users to moodle, get user's who have not linked to moodle
     * and link them to moodle
     *
     * Called by usersLinkToMoodleSynchronization() from class EBSettingsAjaxInitiater
     *
     * @param array $sync_options    user sync options
     * @param int  $offset LIMIT query offset for getting the resluts in chunk
     *
     * @since   1.4.1
     *
     * @return array $response     array containing status & response message
     */
    public function userLinkToMoodlenHandler($sync_options = array(), $offset = 0)
    {
        global $wpdb;
        // checking if moodle connection is working properly
        $connected = edwiserBridgeInstance()->connectionHelper()->connectionTestHelper(EB_ACCESS_URL, EB_ACCESS_TOKEN);

        $response_array['connection_response'] = $connected['success']; // add connection response in response array
        $link_users_count = 0;
        if ($connected['success'] == 1) {
            if ((isset($sync_options["eb_link_users_to_moodle"]) && $sync_options['eb_link_users_to_moodle'] == 1)) {
                // query to get list of users who have not linked to moodle with limit
                $unlinked_users = $wpdb->get_results(
                    "SELECT DISTINCT(user_id)
                    FROM {$wpdb->base_prefix}usermeta
                    WHERE user_id NOT IN (SELECT DISTINCT(user_id) from {$wpdb->base_prefix}usermeta WHERE meta_key = 'moodle_user_id' && meta_value IS NOT NULL)
                    ORDER BY user_id ASC
                    LIMIT ".$offset.", 20",
                    ARRAY_A
                );
                if (!empty($unlinked_users)) {
                    foreach ($unlinked_users as $key => $value) {
                        $user_object = get_userdata($value['user_id']);
                        $flag = $this->linkMoodleUser($user_object);
                        // if user not linked then add it in unlinked users array
                        if (!$flag) {
                            $user = get_userdata($value['user_id']);
                            $response_array['user_with_error'][] = '<tr><td>'.$value['user_id'].'</td><td> '.$user->user_login.'</td></tr>';
                        } else {
                            $link_users_count++;
                        }
                    }
                }
                // used to get all unlinked users count
                $users_count = $wpdb->get_results(
                    "SELECT COUNT(DISTINCT(user_id)) as users_count
                    FROM {$wpdb->base_prefix}usermeta
                    WHERE user_id NOT IN (SELECT DISTINCT(user_id) from {$wpdb->base_prefix}usermeta WHERE meta_key = 'moodle_user_id' && meta_value IS NOT NULL)"
                );
                $users_count = $users_count[0]->users_count;
            }
            // these properties are used to track, how many user's have linked.
            $response_array['unlinked_users_count'] = count($unlinked_users);
            $response_array['users_count'] = $users_count;
            $response_array['linked_users_count'] = $link_users_count;
        } else {
            edwiserBridgeInstance()->logger()->add(
                'user',
                'Connection problem in synchronization, Response:'.print_r($connected, true)
            ); // add connection log
        }
        return $response_array;
    }

    /**
     * Create a new wordpress user.
     *
     * @param string $email
     * @param string $username
     * @param string $password
     *
     * @return int|WP_Error on failure, Int (user ID) on success
     */
    public function createWordpressUser($email, $firstname, $lastname, $role = "")
    {

        // Check the e-mail address
        if (empty($email) || !is_email($email)) {
            return new \WP_Error('registration-error', __('Please provide a valid email address.', 'eb-textdomain'));
        }

        if (email_exists($email)) {
            return new \WP_Error(
                'registration-error',
                __('An account is already registered with your email address. Please login.', 'eb-textdomain'),
                'eb_email_exists'
            );
        }

        if (empty($firstname)) {
            $firstname = $_POST['firstname'];
        }

        if (empty($lastname)) {
            $lastname = $_POST['lastname'];
        }

        $username = sanitize_user(current(explode('@', $email)), true);

        // Ensure username is unique
        $append = 1;
        $o_username = $username;

        while (username_exists($username)) {
            $username = $o_username.$append;
            ++$append;
        }

        // Handle password creation
        $password = wp_generate_password();
        //$password_generated = true;
        // WP Validation
        $validation_errors = new \WP_Error();

        do_action('eb_register_post', $username, $email, $validation_errors);

        $validation_errors = apply_filters('eb_registration_errors', $validation_errors, $username, $email);

        if ($validation_errors->get_error_code()) {
            return $validation_errors;
        }

        //Added after 1.3.4
        if ($role == "") {
            $role = get_option("default_role");
        }


        $wp_user_data = apply_filters(
            'eb_new_user_data',
            array(
                'user_login' => $username,
                'user_pass' => $password,
                'user_email' => $email,
                'role' => $role,
            )
        );

        $user_id = wp_insert_user($wp_user_data);

        if (is_wp_error($user_id)) {
            return new \WP_Error(
                'registration-error',
                '<strong>'.__('ERROR', 'eb-textdomain').'</strong>: '.
                    __(
                        'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.',
                        'eb-textdomain'
                    )
            );
        }

        //update firstname, lastname
        update_user_meta($user_id, 'first_name', $firstname);
        update_user_meta($user_id, 'last_name', $lastname);

        // check if a user exists on moodle with same email
        $moodle_user = $this->getMoodleUser($wp_user_data['user_email']);

        if (isset($moodle_user['user_exists']) && $moodle_user['user_exists'] == 1 && is_object($moodle_user['user_data'])) {
            update_user_meta($user_id, 'moodle_user_id', $moodle_user['user_data']->id);

            // sync courses of an individual user when an existing moodle user is linked with a wordpress account.
            $this->userCourseSynchronizationHandler(array('eb_synchronize_user_courses' => 1), $user_id);
        } else {
            $general_settings = get_option('eb_general');
            $language = 'en';
            if (isset($general_settings['eb_language_code'])) {
                $language = $general_settings['eb_language_code'];
            }
            $user_data = array(
                'username' => $username,
                'password' => $password,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'auth' => 'manual',
                'lang' => $language,
            );

            // create a moodle user with above details
            if (EB_ACCESS_TOKEN != '' && EB_ACCESS_URL != '') {
                $moodle_user = $this->createMoodleUser($user_data);
                if (isset($moodle_user['user_created']) && $moodle_user['user_created'] == 1 && is_object($moodle_user['user_data'])) {
                    update_user_meta($user_id, 'moodle_user_id', $moodle_user['user_data']->id);
                }
            }
        }

        $args = array(
            'user_email' => $email,
            'username' => $username,
            'first_name' => $firstname,
            'last_name' => $lastname,
            'password' => $password,
        );
        do_action('eb_created_user', $args);

        // send another email if moodle user account created has a different username then wordpress
        // in case the username was already registered on moodle, so our system generates a new username automatically.
        //
        // In this case we need to send another mail with moodle account credentials
        $created = 0;
        if (isset($moodle_user['user_created'])) {
            $created = $moodle_user['user_created'];
        }
        if ($created && strtolower($username) != strtolower($moodle_user['user_data']->username)) {
            $args = array(
                'user_email' => $email,
                'username' => $moodle_user['user_data']->username,
                'first_name' => $firstname,
                'last_name' => $lastname,
                'password' => $password,
            );
            // create a new action hook with user details as argument.
            do_action('eb_linked_to_existing_wordpress_user', $args);
        }

        return $user_id;
    }

    /**
     * Will be used to check if a username is available on moodle
     * Used while creating a moodle account for a user.
     *
     * @param string $username username to be checked
     *
     * @return bool returns true / false  [ return false in case of connection failure ]
     */
    public function isMoodleUsernameAvailable($username)
    {
        //global $wpdb;

        edwiserBridgeInstance()->logger()->add('user', 'Checking if username exists....'); // add to user log

        $username = sanitize_user($username); // get sanitized username
        //$user       = array();
        $webservice_function = 'core_user_get_users_by_field';

        // prepare request data array
        $request_data = array('field' => 'username', 'values' => array($username));
        $response = edwiserBridgeInstance()->connectionHelper()->connectMoodleWithArgsHelper($webservice_function, $request_data);

        // return true only if username is available
        if ($response['success'] == 1 && empty($response['response_data'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get a moodle user by email, search if a user exists on moodle with same email id and
     * return user's moodle id and username.
     *
     * proper response is returned on completion
     *
     * @since  1.0.0
     *
     * @param string $user_email email to be checked on moodle
     *
     * @return array
     */
    public function getMoodleUser($user_email)
    {
        //global $wpdb;

        $user_email = filter_var($user_email, FILTER_VALIDATE_EMAIL);
        $user = array();
        $webservice_function = 'core_user_get_users_by_field';

        if (!is_email($user_email)) {
            return $user;
        }

        // prepare request data array
        $request_data = array('field' => 'email', 'values' => array($user_email));
        $response = edwiserBridgeInstance()->connectionHelper()->connectMoodleWithArgsHelper($webservice_function, $request_data);






        // create response array based on response recieved from api helper class
        if ($response['success'] == 1 && empty($response['response_data'])) {
            $user = array('user_exists' => 0, 'user_data' => '');
        } elseif ($response['success'] == 1 &&
                is_array($response['response_data']) &&
                !empty($response['response_data'])) {
            $user = array('user_exists' => 1, 'user_data' => $response['response_data'][0]);
        } elseif ($response['success'] == 0) {
            $user = array('user_created' => 0, 'user_data' => $response['response_message']);
        }

        return $user;
    }

    /**
     * create a new user on moodle with user data passed to it.
     * update an existing user whose moodle id is passed to the function.
     *
     * @param array $user_data the user data used to create a new account or update existing one.
     *                         user data array format
     *
     * $user_data = array(
     *           'username'  => $username,
     *           'password'  => $password,
     *           'firstname' => $firstname,
     *           'lastname'  => $lastname,
     *           'email'     => $email,
     *           'auth'      => 'manual', // this is always manual
     *           'lang'      => $language // get language from settings
     *       );
     * @param int $update set update = 1 if you want to update an existing user on moodle.
     *
     * @return int returns id of new user created, on error returns false.
     */
    public function createMoodleUser($user_data, $update = 0)
    {
        $user = array(); // to store user creation/updation response
        $users = array();
        edwiserBridgeInstance()->logger()->add('user', 'Start creating/updating moodle user, Updating: '.$update); // add user log
        // set webservice function according to update parameter
        if ($update == 1) {
            $webservice_function = 'core_user_update_users';
        } else {
            $webservice_function = 'core_user_create_users';
        }

        /**
         * to lowercase the username for moodle
         * @since  1.2.2
         */

        // confirm that username is in lowercase always
        if (isset($user_data['username'])) {
            $user_data['username'] = strtolower($user_data['username']);
        }

        // Ensure username is unique, when creating a new user on moodle
        if ($update == 0) {
            $append = 1;
            if (!empty($user_data['username'])) {
                $o_username = $user_data['username'];

                // we will check if the username is vailable on moodle before creating a user
                while (!$this->isMoodleUsernameAvailable($user_data['username'])) {
                    $user_data['username'] = $o_username.$append;
                    ++$append;
                }

                // apply custom filter on username generated
                $user_data['username'] = apply_filters('eb_unique_moodle_username', $user_data['username']);
            }
        }

        /*
         * apply custom filter on userdata that is used to create or update moodle account
         * used to add additional user profile fields value that is passed to moodle
         */
        $user_data = apply_filters('eb_moodle_user_profile_details', $user_data, $update);
        // prepare user data array
        foreach ($user_data as $key => $value) {
            $users[0][$key] = $value;
        }
        // prepare request data
        $request_data = array('users' => $users);
        $response = edwiserBridgeInstance()->connectionHelper()->connectMoodleWithArgsHelper(
            $webservice_function,
            $request_data
        );
        // handle response recived from moodle and creates response array accordingly
        if ($update == 0) { // when user is created
            if ($response['success'] == 1 && empty($response['response_data'])) {
                $user = array('user_created' => 0, 'user_data' => '');
            } elseif ($response['success'] == 1 &&
                    is_array($response['response_data']) &&
                    !empty($response['response_data'])) {
                $user = array('user_created' => 1, 'user_data' => $response['response_data'][0]);
            } elseif ($response['success'] == 0) {
                $user = array('user_created' => 0, 'user_data' => $response['response_message']);
            }
        } elseif ($update == 1) { // when updating profile details of an existing user on moodle
            if ($response['success'] == 1 && empty($response['response_data'])) {
                $user = array('user_updated' => 1);
            } else {
                $user = array('user_updated' => 0);
            }
        }

        // sync courses of an individual user when user is created or updated on moodle
        // get wordpress user id by wordpress user email
        if ($update == 0 && isset($user_data['email'])) {
            $wp_user = get_user_by('email', $user_data['email']);
            $this->userCourseSynchronizationHandler(array('eb_synchronize_user_courses' => 1), $wp_user->ID);
        }
        do_action('eb_after_moodle_user_creation', $user);

        return $user;
    }

    /**
     * Checks if a moodle account is already linked, or create account on moodle and links to wordpress.
     * Can also be executed on wp_login hook.
     *
     * a do_action is added that can be used to execute custom action if a new user is created on moodle
     * and linked to wordpress.
     *
     * @param object $user wordpress user object
     */
    public function linkMoodleUser($user)
    {
        // check if a moodle user account is already linked
        $moodle_user_id = get_user_meta($user->ID, 'moodle_user_id', true);
        $created = 0;
        $linked = 0;
        $user_data = array();

        if (empty($moodle_user_id)) {
            /*
             * get user's id from moodle and add in wordpress usermeta.
             *
             * first checks if user exists on moodle,
             * creates a new user account on moodle with same user details including password.
             */
            $moodle_user = $this->getMoodleUser($user->user_email);

            if (isset($moodle_user['user_exists']) && $moodle_user['user_exists'] == 1 && is_object($moodle_user['user_data'])) {
                update_user_meta($user->ID, 'moodle_user_id', $moodle_user['user_data']->id);
                $linked = 1;

                // sync courses of an individual user
                // when an exisintg moodle user is linked to wordpress account with same email
                $this->userCourseSynchronizationHandler(
                    array(
                    'eb_synchronize_user_courses' => 1,
                        ),
                    $user->ID
                );
            } elseif (isset($moodle_user['user_exists']) && $moodle_user['user_exists'] == 0) {
                $general_settings = get_option('eb_general');
                $language = 'en';
                if (isset($general_settings['eb_language_code'])) {
                    $language = $general_settings['eb_language_code'];
                }

                //generate random password for moodle account, as user is already registered on wordpress.
                $password = apply_filters('eb_filter_moodle_password', wp_generate_password());

                $user_data = array(
                    'username' => $user->user_login,
                    'password' => $password,
                    'firstname' => $user->first_name,
                    'lastname' => $user->last_name,
                    'email' => $user->user_email,
                    'auth' => 'manual',
                    'lang' => $language,
                );

                $moodle_user = $this->createMoodleUser($user_data);
                if (isset($moodle_user['user_created']) && $moodle_user['user_created'] == 1 && is_object($moodle_user['user_data'])) {
                    update_user_meta($user->ID, 'moodle_user_id', $moodle_user['user_data']->id);
                    $created = 1;
                    $linked = 1;
                }
            }
        }

        // add a dynamic hook only if a new user is created on moodle and linked to wordpress account
        if (!$created && $linked) {
            $args = array(
                'user_email' => $user->user_email,
                'username' => $moodle_user['user_data']->username,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            );
            //create a new action hook with user details as argument.
            do_action('eb_linked_to_existing_wordpress_user', $args);
        } else if ($created && $linked) {
            $args = array(
                'user_email' => $user_data['email'],
                'username' => $moodle_user['user_data']->username,
                'first_name' => $user_data['firstname'],
                'last_name' => $user_data['lastname'],
                'password' => $user_data['password'],
            );
            //create a new action hook with user details as argument.
            do_action('eb_linked_to_existing_wordpress_to_new_user', $args);
        }

        return $linked;
    }

    /**
     * Custom bulk action to link or unlink user's moodle account.
     *
     * One use case is: User account is deleted from moodle, then one should unlink it from wordpress too.
     * Other can be if admin manually wants to link a user's wordpress account with moodle account.
     *
     * @since  1.0.0
     */
    public function linkUserBulkActions()
    {
        $current_screen = get_current_screen(); // get current screen object
        // enqueue js only if current screen is users
        if ($current_screen->base == 'users') {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('<option>').val('link_moodle')
                            .text('<?php _e('Link Moodle Account', 'eb-textdomain') ?>')
                            .appendTo("select[name='action']");
                    jQuery('<option>').val('link_moodle')
                            .text('<?php _e('Link Moodle Account', 'eb-textdomain') ?>')
                            .appendTo("select[name='action2']");
                    jQuery('<option>').val('unlink_moodle')
                            .text('<?php _e('Unlink Moodle Account', 'eb-textdomain') ?>')
                            .appendTo("select[name='action']");
                    jQuery('<option>').val('unlink_moodle')
                            .text('<?php _e('Unlink Moodle Account', 'eb-textdomain') ?>')
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
     * links or unlinks moodle and wordpress accounts of a user.
     *
     * @since  1.0.0
     */
    public function linkUserBulkActionsHandler()
    {

        // get the action
        $wp_user_table = _get_list_table('WP_Users_List_Table');
        $action = $wp_user_table->current_action();

        // perform our unlink action
        switch ($action) {
            case 'link_moodle':
                $linked = 0;

                // get all selected users
                $users = isset($_REQUEST['users']) ? $_REQUEST['users'] : array();

                if (is_array($users)) {
                    foreach ($users as $user) {
                        $user_object = get_userdata($user);
                        if ($this->linkMoodleUser($user_object)) {
                            ++$linked;
                        }
                    }

                    // build the redirect url
                    $sendback = add_query_arg(array('linked' => $linked), $_SERVER['HTTP_REFERER']);
                }

                break;
            case 'unlink_moodle':
                $unlinked = 0;

                // get all selected users
                $users = isset($_REQUEST['users']) ? $_REQUEST['users'] : array();

                if (is_array($users)) {
                    foreach ($users as $user) {
                        $deleted = (delete_user_meta($user, 'moodle_user_id'));
                        delete_user_meta($user, 'eb_user_password');
                        if ($deleted) {
                            $unlinked++;
                        }
                    }

                    // build the redirect url
                    $sendback = add_query_arg(array('unlinked' => $unlinked), $_SERVER['HTTP_REFERER']);
                }

                break;
            default:
                return;
        }

        wp_safe_redirect($sendback);
        exit();
    }

    /**
     * display a message to admin on user link or unlink bulk actions.
     *
     * @since  1.0.0
     *
     * @return string link or unlink message
     */
    public function linkUserBulkActionsNotices()
    {
        global $pagenow;

        if ($pagenow == 'users.php') {
            if (isset($_REQUEST['unlinked']) && $_REQUEST['unlinked'] == 1) {
                $message = sprintf(__('%s User Unlinked.', 'eb-textdomain'), number_format_i18n($_REQUEST['unlinked']));
            } elseif (isset($_REQUEST['unlinked']) && $_REQUEST['unlinked'] > 1) {
                $message = sprintf(
                    __('%s Users Unlinked.', 'eb-textdomain'),
                    number_format_i18n($_REQUEST['unlinked'])
                );
            } elseif (isset($_REQUEST['linked']) && $_REQUEST['linked'] == 1) {
                $message = sprintf(__('%s User Linked.', 'eb-textdomain'), number_format_i18n($_REQUEST['linked']));
            } elseif (isset($_REQUEST['linked']) && $_REQUEST['linked'] > 1) {
                $message = sprintf(__('%s Users Linked.', 'eb-textdomain'), number_format_i18n($_REQUEST['linked']));
            }

            if (isset($message)) {
                echo "<div class='updated'><p>{$message}</p></div>";
            }
        }
    }

    /**
     * change moodle password when wordpress password change event occurs.
     *
     * @since 1.0.0
     *
     * @param int    $user_id user id of the profile being updated
     * @param object $user    previous user object
     */
    public function passwordUpdate($user_id/* , $user */)
    {

        // get new password entered by user
        // works for wordpress profile & woocommerce my account edit profile page
        if (isset($_POST['password_1']) && !empty($_POST['password_1'])) {
            $new_password = $_POST['password_1'];
        } elseif (isset($_POST['pass1']) && !empty($_POST['pass1'])) {
            $new_password = $_POST['pass1'];
        } else {
            return;
        }

        edwiserBridgeInstance()->logger()->add('user', 'Password update initiated..... '); // add user log

        $moodle_user_id = get_user_meta($user_id, 'moodle_user_id', true); // get moodle user id

        if (!is_numeric($moodle_user_id)) {
            edwiserBridgeInstance()->logger()->add('user', 'A moodle user id is not associated.... Exiting!!!'); // add user log
            return;
        }

        if (empty($new_password)) {
            return; // stop further execution of function if password was not entered.
        }

        $user_data = array(
            'id' => $moodle_user_id, // moodle user id
            //'user_id'   => $user_id, // wordpress user id
            'password' => $new_password,
        );

        $moodle_user = $this->createMoodleUser($user_data, 1);
        if (isset($moodle_user['user_updated']) && $moodle_user['user_updated'] == 1) {
            //edwiserBridgeInstance()->logger()->add( 'user', 'Password updated successfully on moodle.' ); // add user log
        } else {
            edwiserBridgeInstance()->logger()->add('user', 'There is a problem in updating password..... Exiting!!!'); // add user log
        }
    }

    /**
     * When reseting password in wp-login.
     *
     * @since  1.0.0
     *
     * @param object $user current user object
     * @param string $pass new password entered by user
     */
    public function passwordReset($user, $pass)
    {
        $moodle_user_id = get_user_meta($user->ID, 'moodle_user_id', true); // get moodle user id

        if (!is_numeric($moodle_user_id)) {
            return;
        }

        if (isset($pass) && !empty($pass)) {
            $new_password = $pass; // get new password entered by user

            $user_data = array(
                'id' => $moodle_user_id,
                //'user_id'   => $user_id, // wordpress user id
                'password' => $new_password,
            );

            $moodle_user = $this->createMoodleUser($user_data, 1);
            if (isset($moodle_user['user_updated']) && $moodle_user['user_updated'] == 1) {
                //edwiserBridgeInstance()->logger()->add( 'user', 'Password reset successfully on moodle.' ); // add user log
            } else {
                edwiserBridgeInstance()->logger()->add('user', 'There is a problem in resetting password..... Exiting!!!'); // add user log
            }
        }
    }

    /**
     * display a dropdown in wordpress user profile from where admin can enroll user to any course directly.
     *
     * @param object $user current user object
     *
     * @return int returns true
     */
    public function displayUsersEnrolledCourses($user)
    {

        // check if a moodle user account is already linked
        $moodle_user_id = get_user_meta($user->ID, 'moodle_user_id', true);

        if (is_numeric($moodle_user_id)) {
            global $profileuser;
            $user_id = $profileuser->ID;
            $enrolled_courses = array();
            $notenrolled_courses = array();

            $course_args = array(
                'post_type' => 'eb_course',
                'post_status' => 'publish',
                'posts_per_page' => -1,
            );
            $courses = get_posts($course_args);
            ?>
            <table class="form-table">
                <tr>
                    <th><h3><?php _e('Enrolled Courses', 'eb-textdomain');?></h3></th>
                    <td>
                        <ol>
                            <?php
                            foreach ($courses as $course) {
                                $has_access = edwiserBridgeInstance()->enrollmentManager()->userHasCourseAccess($user_id, $course->ID);
                                if ($has_access) {
                                    $enrolled_courses[] = $course;
                                    echo "<li><a href='".get_permalink($course->ID)."'>".$course->post_title.'</a></li>';
                                } else {
                                    $notenrolled_courses[] = $course;
                                }
                            }
                            ?>
                        </ol>
                    </td>
                </tr>
                <?php
                if (current_user_can('manage_options')) {
                    ?>
                    <tr>
                        <th><h3><?php _e('Enroll a Course', 'eb-textdomain');?></h3></th>
                        <td>
                            <select name="enroll_course">
                                <option value=''><?php _e('-- Select a Course --', 'eb-textdomain'); ?></option>
                                <?php
                                foreach ($notenrolled_courses as $course) {
                                    echo "<option value='".$course->ID."'>".$course->post_title.'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><h3><?php _e('Unenroll a Course', 'eb-textdomain');?></h3></th>
                        <td>
                            <select name="unenroll_course">
                                <option value=''><?php _e('-- Select a Course --', 'eb-textdomain'); ?></option>
                                <?php
                                foreach ($enrolled_courses as $course) {
                                    echo "<option value='".$course->ID."'>".$course->post_title.'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>

            <?php
        }

        return true;
    }

    /**
     * enroll or unenroll user courses on profile update.
     * works on 'edit_user_profile_update' & 'personal_options_update' hook.
     *
     * @param int $user_id id of the user whose profile is updated.
     *
     * @return bool true
     */
    public function updateCoursesOnProfileUpdate($user_id)
    {

        if (!current_user_can('manage_options')) {
            return false;
        }

        $user = get_userdata($user_id);

        //check if a moodle user account is already linked
        $moodle_user_id = get_user_meta($user->ID, 'moodle_user_id', true);

        if (is_numeric($moodle_user_id)) {
            $enroll_course = '';
            if (isset($_POST['enroll_course'])) {
                $enroll_course = $_POST['enroll_course'];
            }

            $unenroll_course = '';
            if (isset($_POST['unenroll_course'])) {
                $unenroll_course = $_POST['unenroll_course'];
            }

            if (is_numeric($enroll_course)) {
                // define args
                $args = array(
                    'user_id'           => $user->ID,
                    'courses'           => array($enroll_course),
                    'complete_unenroll' => 0
                );

                // enroll user to course
                edwiserBridgeInstance()->enrollmentManager()->updateUserCourseEnrollment($args);
            }



            if (is_numeric($unenroll_course)) {
                // define args
                $args = array(
                    'user_id'           => $user->ID,
                    'courses'           => array($unenroll_course),
                    'unenroll'          => 1,
                    'complete_unenroll' => 1
                );

                // enroll user to course
                edwiserBridgeInstance()->enrollmentManager()->updateUserCourseEnrollment($args);
            }
        }

        return true;
    }

    /**
     * delete users enrollment records when user is permanently deleted from wordpress.
     *
     * @param int $user_id id of the user whose profile is updated.
     *
     * @return bool true
     */
    public function deleteEnrollmentRecordsOnUserDeletion($user_id)
    {
        global $wpdb;

        // removing user's records from enrollment table
        $wpdb->delete($wpdb->prefix.'moodle_enrollment', array('user_id' => $user_id), array('%d'));

        edwiserBridgeInstance()->logger()->add('user', "Enrollment records of user ID: {$user_id} are deleted.");  // add user log
    }

    public function unenrollOnCourseAccessExpire()
    {
        global $wpdb, $post;
        $curUser = get_current_user_id();
        $stmt = "SELECT * FROM {$wpdb->prefix}moodle_enrollment WHERE  expire_time!='0000-00-00 00:00:00' AND expire_time<NOW();";
        $enrollData = $wpdb->get_results($stmt);
        $enrollMentManager = EBEnrollmentManager::instance($this->plugin_name, $this->version);

        //Added for the bulk purchase plugin expiration functionality
        $enrollData = apply_filters("eb_user_list_on_course_expiration", $enrollData);

        foreach ($enrollData as $courseEnrollData) {
            $args = array(
                'user_id' => $courseEnrollData->user_id,
                'courses' => array($courseEnrollData->course_id),
                'unenroll' => 1,
            );
            $enrollMentManager->updateUserCourseEnrollment($args);
        }
    }

    public function moodleLinkUnlinkUser()
    {
        $responce=array("code"=>"failed");
        if (isset($_POST['user_id']) && isset($_POST['link_user'])) {
            $user_object = get_userdata($_POST['user_id']);
            if ($_POST['link_user']) {
                $flag=$this->linkMoodleUser($user_object);
                if (!$flag) {
                    $responce["msg"]=__("Failed to process the request.", "eb-textdomain");
                } else {
                    $responce["code"]="success";
                    $responce["msg"]=sprintf(__("%s's account has been linked successfully.", 'eb-textdomain'), $user_object->user_login);
                }
            } else {
                $deleted = (delete_user_meta($_POST['user_id'], 'moodle_user_id'));
                delete_user_meta($_POST['user_id'], 'eb_user_password');
                $responce["code"]="success";
                $responce["msg"]=sprintf(__("%s's account has been unlinked successfully.", 'eb-textdomain'), $user_object->user_login);
            }
        } else {
            $responce["msg"]=__("Invalid ajax request.", "eb-textdomain");
        }
        echo json_encode($responce);
        die();
    }

    public function moodleLinkUnlinkUserNotices()
    {
        echo "<div id='moodleLinkUnlinkUserNotices' class='updated'>
                 <p></p>
              </div>";
    }
}
