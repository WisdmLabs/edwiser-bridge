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

        public function outPut()
        {
            $listTable = new EBCustomListTable();
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
                    $listTable->display();
                    ?>
                </form>
                <?php do_action("eb_after_manage_user_enrollment_table"); ?>
            </div>
            <?php
        }

        public function unenrollUser()
        {
            $responce = "Failed unenroll user";
            if (isset($_POST['user_id']) && isset($_POST['course_id']) && isset($_POST['action']) && $_POST['action'] == 'wdm_eb_user_manage_unenroll_unenroll_user') {
                $enrollmentManager = EBEnrollmentManager::instance($this->plugin_name, $this->version);
                $courseId = $_POST['course_id'];
                $userId = $_POST['user_id'];
                $args = array(
                    'user_id' => $userId,
                    'role_id' => 5,
                    'courses' => array($courseId),
                    'unenroll' => 1,
                    'suspend' => 0,
                );
                $enrollmentManager->updateUserCourseEnrollment($args);
                $courseName = get_the_title($courseId);
                $user = get_userdata($userId);
                $responce = ucfirst($user->user_login) . " has been unenrolled from the $courseName course";
                wp_send_json_success($responce);
            } else {
                wp_send_json_error($responce);
            }
        }
    }
}
