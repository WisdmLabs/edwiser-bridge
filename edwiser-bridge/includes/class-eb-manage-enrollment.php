<?php

namespace app\wisdmlabs\edwiserBridge;

if (!class_exists('\WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if (!class_exists('\app\wisdmlabs\edwiserBridge\EBManageUserEnrollment')) {

    class EBManageUserEnrollment
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
         * @var EB_Course_Manager The single instance of the class
         *
         * @since 1.0.0
         */
        protected static $instance = null;

        /**
         * Main EBOrderManager Instance.
         *
         * Ensures only one instance of EBOrderManager is loaded or can be loaded.
         *
         * @since 1.0.0
         * @static
         *
         * @see EBOrderManager()
         *
         * @return EBOrderManager - Main instance
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
         * Displays the manage email tempalte page output
         */
        public function outPut()
        {
            $listTable = new EBCustomListTable();
            $currentAction = $listTable->current_action();
            $this->handleBulkAction($currentAction);
            $listTable->prepare_items();
            ?>
            <div class="eb-manage-user-enrol-wrap">

                <!-- Display the proccessing popup start. -->
                <div id="loading-div-background">
                    <div id="loading-div" class="ui-corner-all" >
                        <img style="height:40px;margin:40px;" src="images/loading.gif" alt="Loading.."/>
                        <h2 style="color:gray;font-weight:normal;">
                            <?php _e("Please wait processing request ....", 'eb-textdomain'); ?>
                        </h2>
                    </div>
                </div>
                <!-- Display the proccessing popup end. -->

                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

                <div class="eb-notices" id="eb-notices"><!-- Add custom notices inside this. --></div>
                <?php do_action("eb_before_manage_user_enrollment_table"); ?>
                <form id="eb-manage-user-enrollment-filter" method="post">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <?php
                    wp_nonce_field('eb-manage-user-enrol', 'eb-manage-user-enrol');

                    //will add search box in next update.
                    // $listTable->search_box(__('Search Users / course', 'eb-textdomain'), 'eb_manage_enroll_search');

                    $listTable->display();
                    ?>
                </form>
                <?php do_action("eb_after_manage_user_enrollment_table"); ?>
            </div>
            <?php
        }

        /**
         * Callback to handle the bulk or individul action applied on the list
         * table row from the manage user enrolment page
         * @param type $action bulk action
         */
        private function handleBulkAction($action)
        {
            switch ($action) {
                case "unenroll":
                    $this->multipalUnenrollByRecId($_POST);
                    break;
                default:
                    break;
            }
        }

        /**
         * Provides the functionality to unenroll multipal users from the course
         * @param type $data bulk action data to unenroll users
         * @return type
         */
        private function multipalUnenrollByRecId($data)
        {
            if (!isset($data['enrollment'])) {
                return;
            }
            $users = $data['enrollment'];
            global $wpdb;
            $enrollTbl = $wpdb->prefix . 'moodle_enrollment';
            $stmt = "select user_id,course_id from $enrollTbl where id in('" . implode("','", $users) . "')";
            $results = $wpdb->get_results($stmt, ARRAY_A);
            $cnt = 0;
            foreach ($results as $rec) {
                if ($this->unenrollUser($rec['course_id'], $rec['user_id'])) {
                    $cnt++;
                }
            }
            if ($cnt > 0) {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong>
                            <?php _e(sprintf("%s users has been unenrolled successfully.", $cnt), 'eb-textdomain'); ?>
                        </strong>
                    </p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e('Dismiss this notice', "eb-textdomain");
                        ?>.</span>
                    </button>
                </div>
                <?php
            } else {
                ?>
                <div class="error notice">
                    <p>
                        <strong>
                            <?php _e('No users has been unenrolled', 'eb-textdomain'); ?>
                        </strong>
                    </p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e('Dismiss this notice', "eb-textdomain");
                        ?>.</span>
                    </button>
                </div>
                <?php
            }
        }

        /**
         * Ajax callback to unenroo the users from the database
         */
        public function unenrollUserAjaxHandler()
        {
            $responce = "Failed unenroll user";
            if (isset($_POST['user_id']) && isset($_POST['course_id']) && isset($_POST['action']) && $_POST['action'] == 'wdm_eb_user_manage_unenroll_unenroll_user') {
                $courseId = $_POST['course_id'];
                $userId = $_POST['user_id'];
                $res = $this->unenrollUser($courseId, $userId);
                if ($res) {
                    $courseName = get_the_title($courseId);
                    $user = get_userdata($userId);
                    $responce = ucfirst($user->user_login) . " has been unenrolled from the $courseName course";
                    wp_send_json_success($responce);
                } else {
                    wp_send_json_error($responce);
                }
            } else {
                wp_send_json_error($responce);
            }
        }

        /**
         * Provides the functionality to unenroll the user from the course
         * @param type $courseId
         * @param type $userId
         * @return bolean returns ture if the user is unenrolled from the course
         * othrewise returns false.
         */
        private function unenrollUser($courseId, $userId)
        {
            /**
             * This is commented due to the error Avoid using static access to class
             * This doesn't allow the class to call other class statically
             */
            // $enrollmentManager = EBEnrollmentManager::instance($this->plugin_name, $this->version);

            $enrollmentManager = new EBEnrollmentManager($this->plugin_name, $this->version);

            $args = array(
                'user_id'           => $userId,
                'role_id'           => 5,
                'courses'           => array($courseId),
                'unenroll'          => 1,
                'suspend'           => 0,
                'complete_unenroll' => 1
            );
            return $enrollmentManager->updateUserCourseEnrollment($args);
        }

        /**
         * @return [type] [description]
         */
       /* public function processEnrollmentOnLogin($user_login, $user)
        {
            global $wpdb;
            $userId = $user->ID;
            $moodleUserId = get_user_meta($userId, "moodle_user_id", true);

            if (trim($moodleUserId) == "") {
                 $enrolledCourses = edwiserBridgeInstance()->courseManager()->getMoodleCourses($moodleUserId);

                if (!isset($enrolledCourses["success"]) || !$enrolledCourses["success"]) {
                    return;
                }

                $moodleCourseData = array();
                foreach ($enrolledCourses["response_data"] as $value) {
                    $moodleCourseId = $value->id;
                    $wordpressCourseId = $this->getWPPostID($moodleCourseId);
                    if ($wordpressCourseId) {
                        $moodleCourseData[$wordpressCourseId] = $moodleCourseId;
                    }
                }

                $result = $wpdb->get_results(
                    "SELECT course_id
                    FROM {$wpdb->prefix}moodle_enrollment
                    WHERE user_id={$userId};",
                    ARRAY_N
                );


                $wordpressCourseData = array();

                foreach ($result as $value) {
                    $moodleCourseId = get_post_meta($value[0], "moodle_course_id", true);
                    $wordpressCourseId = $value[0];
                    if ($wordpressCourseId) {
                        $wordpressCourseData[$wordpressCourseId] = $moodleCourseId;
                    }
                }

                $newEnrolledCourses = array_diff($moodleCourseData, $wordpressCourseData);
                $unEnrolledCOurses = array_diff($wordpressCourseData, $moodleCourseData);


                $args = array(
                    'user_id' => $userId,
                    'role_id' => 5,
                    'courses' => array_keys($newEnrolledCourses),
                    'unenroll' => 0,
                    'suspend' => 0,
                );

                edwiserBridgeInstance()->enrollmentManager()->updateEnrollmentRecordWordpress($args);


                $args = array(
                    'user_id' => $userId,
                    'role_id' => 5,
                    'courses' => array_keys($unEnrolledCOurses),
                    'unenroll' => 1,
                    'suspend' => 0,
                );

                edwiserBridgeInstance()->enrollmentManager()->updateEnrollmentRecordWordpress($args);
            }
        }*/


        public function getWPPostID($moodleCourseId)
        {
            global $wpdb;
            $result = $wpdb->get_var("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_value={$moodleCourseId} AND meta_key = 'moodle_course_id'");

            return $result;
        }
    }
}
