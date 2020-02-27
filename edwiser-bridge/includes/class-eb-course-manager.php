<?php

/**
 * This class defines all code necessary for moodle course synchronization.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EBCourseManager
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
     * @var EBCourseManager The single instance of the class
     *
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * Main EBCourseManager Instance.
     *
     * Ensures only one instance of EBCourseManager is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     *
     * @see EBCourseManager()
     *
     * @return EBCourseManager - Main instance
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
     * Initiate the synchronization process.
     * Called by courseSynchronizationInitiater() from class EBSettingsAjaxInitiater.
     *
     * @param array $sync_options course sync options
     *
     * @since   1.0.0
     *
     * @return array $response     array containing status & response message
     */
    public function courseSynchronizationHandler($sync_options = array())
    {
        edwiserBridgeInstance()->logger()->add('user', 'Initiating course & category sync process....'); // add course log

        $moodle_course_resp = array(); // contains course response from moodle
        $moodle_category_resp = array(); // contains category response from moodle
        $response_array = array(); // contains response message to be displayed to user.
        $courses_updated = array(); // store updated course ids ( wordpress course ids )
        $courses_created = array(); // store newely created course ids ( wordpress course ids )
        //$category_created   = array(); // array of categories created / synced from moodle

        // checking if moodle connection is working properly
        $connected = edwiserBridgeInstance()->connectionHelper()->connectionTestHelper(EB_ACCESS_URL, EB_ACCESS_TOKEN);

        $response_array['connection_response'] = $connected['success']; // add connection response in response array

        if ($connected['success'] == 1) {
            /*
             * sync moodle course categories to wordpress conditionally.
             * executes only if user chooses to sync categories.
             */
            if (isset($sync_options['eb_synchronize_categories']) && $sync_options['eb_synchronize_categories'] == 1) {
                $moodle_category_resp = $this->getMoodleCourseCategories(); // get categories from moodle

                // creating categories based on recieved data
                if ($moodle_category_resp['success'] == 1) {
                    $this->createCourseCategoriesOnWordpress($moodle_category_resp['response_data']);
                }

                // push category response in array
                $response_array['category_success'] = $moodle_category_resp['success'];
                $response_array['category_response_message'] = $moodle_category_resp['response_message'];
            }

            /*
             * sync moodle courses to wordpress.
             */
            $moodle_course_resp = $this->getMoodleCourses(); // get courses from moodle



            if ((isset($sync_options["eb_synchronize_draft"])/* && $sync_options['eb_synchronize_draft'] == 1*/) || (isset($sync_options["eb_synchronize_previous"]) && $sync_options['eb_synchronize_previous'] == 1)) {
                // creating courses based on recieved data
                if ($moodle_course_resp['success'] == 1) {
                    foreach ($moodle_course_resp['response_data'] as $course_data) {
                        /*
                         * moodle always returns moodle frontpage as first course,
                         * below step is to avoid the frontpage to be added as a course.
                         */
                        if ($course_data->id == 1) {
                            continue;
                        }

                        // check if course is previously synced
                        $existing_course_id = $this->isCoursePresynced($course_data->id);

                        // creates new course or updates previously synced course conditionally
                        if (!is_numeric($existing_course_id) /*&& $sync_options["eb_synchronize_draft"]*/) {
                            $course_id = $this->createCourseOnWordpress($course_data, $sync_options);
                            $courses_created[] = $course_id; // push course id in courses created array
                        } elseif (is_numeric($existing_course_id) &&
                                isset($sync_options['eb_synchronize_previous']) &&
                                $sync_options['eb_synchronize_previous'] == 1) {
                            $course_id = $this->updateCourseOnWordpress(
                                $existing_course_id,
                                $course_data,
                                $sync_options
                            );
                            $courses_updated[] = $course_id; // push course id in courses updated array
                        }
                    }
                }
                $response_array['course_success'] = $moodle_course_resp['success'];
                // push course response in array
                $response_array['course_response_message'] = $moodle_course_resp['response_message'];
            }

            /*
             * hook to be run on course completion
             * we are passing all new created and updated courses as arg
             */
            do_action('eb_course_synchronization_complete', $courses_created, $courses_updated, $sync_options);
        } else {
            edwiserBridgeInstance()->logger()->add(
                'course',
                'Connection problem in synchronization, Response:'.print_r($connected, true)
            ); // add connection log
        }

        return $response_array;
    }

    /**
     * fetches the courses from moodle ( all courses or courses of a specfic user ).
     *
     * uses connect_moodle_helper() and connect_moodle_with_args_helper()
     *
     * @param int     moodle user_id of a wordpress user passed to connection helper.
     *
     * @return array stores moodle web service response.
     */
    public function getMoodleCourses($moodle_user_id = null)
    {
        $response = '';

        if (!empty($moodle_user_id)) {
            $webservice_function = 'core_enrol_get_users_courses'; // get a users enrolled courses from moodle
            $request_data = array('userid' => $moodle_user_id); // prepare request data array

            $response = edwiserBridgeInstance()->connectionHelper()->connectMoodleWithArgsHelper(
                $webservice_function,
                $request_data
            );

            edwiserBridgeInstance()->logger()->add('course', 'User course response: '.serialize($response)); // add course log
        } elseif (empty($moodle_user_id)) {
            $webservice_function = 'core_course_get_courses'; // get all courses from moodle
            $response = edwiserBridgeInstance()->connectionHelper()->connectMoodleHelper($webservice_function);

            edwiserBridgeInstance()->logger()->add('course', 'Response: '.serialize($response)); // add course log
        }

        return $response;
    }

    /**
     * fetches the courses categories from moodle.
     * uses connect_moodle_helper().
     *
     * @param string $webservice_function the webservice function passed to connection helper.
     *
     * @return array stores moodle web service response.
     */
    public function getMoodleCourseCategories($webservice_function = null)
    {
        if ($webservice_function == null) {
            $webservice_function = 'core_course_get_categories';
        }

        $response = edwiserBridgeInstance()->connectionHelper()->connectMoodleHelper($webservice_function);
        edwiserBridgeInstance()->logger()->add('course', serialize($response));

        return $response;
    }

    /**
     * checks if a course is previously synced from moodle.
     *
     * @param int $course_id_on_moodle the id of course as on moodle
     *
     * @return bool returns respective course id on wordpress if exist else returns null
     */
    public function isCoursePresynced($course_id_on_moodle)
    {
        global $wpdb;

        //get id of course on wordpress based on id on moodle $course_id =
        $course_id = $wpdb->get_var(
            "SELECT post_id
            FROM {$wpdb->prefix}postmeta
            WHERE meta_key = 'moodle_course_id'
            AND meta_value = '".$course_id_on_moodle."'"
        );

        return $course_id;
    }

    /**
     * return the moodle id of a course using its wordpress id.
     *
     * @param int $course_id_on_wp the id of course synced on wordpress
     *
     * @return int returns respective course id on moodle
     */
    public function getMoodleCourseId($course_id_on_wp)
    {
        return get_post_meta($course_id_on_wp, 'moodle_course_id', true);
    }

    /**
     * return the moodle id of a course using its wordpress id.
     *
     * @param int $course_id_on_wp the id of course synced on wordpress
     *
     * @return int returns respective course id on moodle
     */
    public function getMoodleWPCourseIdPair($course_id_on_wp)
    {
        return array("$course_id_on_wp" => get_post_meta($course_id_on_wp, 'moodle_course_id', true));
    }

    /**
     * create course on wordpress.
     *
     * @param array $course_data course data recieved from initiate_course_sync_process()
     *
     * @return int returns id of course
     */
    public function createCourseOnWordpress($course_data, $sync_options = array())
    {
        global $wpdb;

        $status = (isset($sync_options['eb_synchronize_draft']) &&
                $sync_options['eb_synchronize_draft'] == 1) ? 'draft' : 'publish'; // manage course status

        $course_args = array(
            'post_title' => $course_data->fullname,
            'post_content' => $course_data->summary,
            'post_status' => $status,
            'post_type' => 'eb_course',
        );

        $wp_course_id = wp_insert_post($course_args); // create a course on wordpress

        // get term id on wordpress to which course is associated on moodle
        /*$term_id = $wpdb->get_var(
            "SELECT term_id
            FROM {$wpdb->prefix}term_taxonomy
            WHERE taxonomy = 'eb_course_cat'
            AND description = ".$course_data->categoryid
        );*/


        $term_id = $wpdb->get_var(
            "SELECT term_id
            FROM {$wpdb->prefix}termmeta
            WHERE meta_key = 'eb_moodle_cat_id'
            AND meta_value = ".$course_data->categoryid
        );

        // $term_id = get_option( "eb_course_cat_".$course_data->categoryid );

        // set course terms
        if ($term_id > 0) {
            wp_set_post_terms($wp_course_id, $term_id, 'eb_course_cat');
        }

        // add course id on moodle in corse meta on WP
        $eb_course_options = array('moodle_course_id' => $course_data->id);
        add_post_meta($wp_course_id, 'moodle_course_id', $course_data->id);
        add_post_meta($wp_course_id, 'eb_course_options', $eb_course_options);

        /*
         * execute your own action on course creation on WorPress
         * we are passing newly created course id as well as its respective moodle id in arguments
         *
         * sync_options are also passed as it can be used in a custom action on hook.
         */
        do_action('eb_course_created_wp', $wp_course_id, $course_data, $sync_options);

        return $wp_course_id;
    }

    /**
     * update previous synced course on wordpress.
     *
     * @param int   $wp_course_id existing id of course on wordpress
     * @param array $course_data  course data recieved from initiate_course_sync_process()
     *
     * @return int returns id of course
     */
    public function updateCourseOnWordpress($wp_course_id, $course_data, $sync_options)
    {
        global $wpdb;
        $course_args = array(
            'ID' => $wp_course_id,
            'post_title' => $course_data->fullname,
            'post_content' => $course_data->summary,
        );

        // updater course on wordpress
        wp_update_post($course_args);

        // get term id on wordpress to which course is associated on moodle
        /*$term_id = $wpdb->get_var(
            "SELECT term_id
            FROM {$wpdb->prefix}term_taxonomy
            WHERE taxonomy = 'eb_course_cat'
            AND description = ".$course_data->categoryid
        );*/


        $term_id = $wpdb->get_var(
            "SELECT term_id
            FROM {$wpdb->prefix}termmeta
            WHERE meta_key = 'eb_moodle_cat_id'
            AND meta_value = ".$course_data->categoryid
        );

        // $term_id = get_option( "eb_course_cat_".$course_data->categoryid );

        // set course terms
        if ($term_id > 0) {
            wp_set_post_terms($wp_course_id, $term_id, 'eb_course_cat');
        }

        /*
         * execute your own action on course updation on WordPress
         * we are passing newly created course id as well as its respective moodle id in arguments
         *
         * sync_options are also passed as it can be used in a custom action on hook.
         */
        do_action('eb_course_updated_wp', $wp_course_id, $course_data, $sync_options);

        return $wp_course_id;
    }

    /**
     * In case a course is permanentaly deleted from moodle course list,
     * update course enrollment table appropriately by deleting records for course being deleted.
     *
     * @since  1.0.0
     *
     * @param int $course_id
     */
    public function deleteEnrollmentRecordsOnCourseDeletion($course_id)
    {
        global $wpdb;

        if (get_post_type($course_id) == 'eb_course') {
            // removing course from enrollment table
            $wpdb->delete($wpdb->prefix.'moodle_enrollment', array('course_id' => $course_id), array('%d'));
        }
    }

    /**
     * uses the response recieved from get_eb_course_categories() function.
     * craetes terms of eb_course_cat taxonomy.
     *
     * @param array $category_response accepts categories fetched from moodle
     */
    public function createCourseCategoriesOnWordpress($category_response)
    {
        global $wpdb;

        //$term_id_array          = array();
        //$moodle_category_array  = array();
        //$i                      = 0;

        // sort category response by id in incremental order
        usort($category_response, 'usortNumericCallback');

        foreach ($category_response as $category) {
            $cat_name_clean = preg_replace('/\s*/', '', $category->name);
            $cat_name_lower = strtolower($cat_name_clean);
            $parent = ($category->parent == 0 ? 0 : $category->parent);

            //$term_id = '';
            //$term_description = '';

            if ($parent > 0) {
                // get parent term if exists
                /*$parent_term = $wpdb->get_var(
                    "SELECT term_id
                    FROM {$wpdb->prefix}term_taxonomy
                    WHERE taxonomy = 'eb_course_cat'
                    AND description = ".$category->parent
                );*/

                $parent_term = $wpdb->get_var(
                    "SELECT term_id
                    FROM {$wpdb->prefix}termmeta
                    WHERE meta_key = 'eb_moodle_cat_id'
                    AND meta_value = ".$category->parent
                );

                // $parent_term = get_option( "eb_course_cat_".$category->parent );
                if ($parent_term && !term_exists($cat_name_lower, 'eb_course_cat', $parent_term)) {
                    $created_term = wp_insert_term(
                        $category->name,
                        'eb_course_cat',
                        array(
                        'slug' => $cat_name_lower,
                        'parent' => $parent_term,
                        'description' => $category->description,
                        )
                    );
                    update_term_meta($created_term['term_id'], "eb_moodle_cat_id", $category->id);

                    // Save the moodle id of category in options
                    // update_option( "eb_course_cat_".$category->id, $created_term['term_id'] );
                }
            } else {
                if (!term_exists($cat_name_lower, 'eb_course_cat')) {
                    $created_term = wp_insert_term(
                        $category->name,
                        'eb_course_cat',
                        array(
                        'slug' => $cat_name_lower,
                        'description' => $category->description,
                            )
                    );
                    update_term_meta($created_term['term_id'], "eb_moodle_cat_id", $category->id);

                    // Save the moodle id of category in options
                    // update_option( "eb_course_cat_".$category->id, $created_term['term_id'] );
                }
            }
        }
    }

    /**
     * add a new column price type to courses table in admin.
     *
     * @since  1.0.0
     *
     * @param array $columns default columns array
     *
     * @return array $new_columns   updated columns array
     */
    public function addCoursePriceTypeColumn($columns)
    {
        $new_columns = array(); // new columns array

        foreach ($columns as $k => $value) {
            if ($k === 'title') {
                $new_columns[$k] = __('Course Title', 'eb-textdomain');
            } else {
                $new_columns[$k] = $value;
            }

            if ($k === 'title') {
                $new_columns['course_type'] = __('Course Type', 'eb-textdomain');
            }
        }

        return $new_columns;
    }

    /**
     * add content to course price type column.
     *
     * @since  1.0.0
     *
     * @param array $columns name of a column
     */
    public function addCoursePriceTypeColumnContent($column_name, $post_ID)
    {
        if ($column_name == 'course_type') {
            $status = EBPostTypes::getPostOptions($post_ID, 'course_price_type', 'eb_course');
            $options = array(
                        'free' => __('Free', 'eb-textdomain'),
                        'paid' => __('Paid', 'eb-textdomain'),
                        'closed' => __('Closed', 'eb-textdomain'),
                    );
            echo isset($options[$status]) ? $options[$status] : ucfirst($status);
        }
    }
}
