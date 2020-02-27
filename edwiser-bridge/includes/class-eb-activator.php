<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EBActivator
{

    /**
     * networkWide tells if the plugin was activated for the entire network or just for single site.
     * @since    1.1.1
     */
    private static $networkWide = false;

    /**
     * activation function.
     * @since    1.0.0
     */
    public static function activate($networkWide)
    {
        /**
         * deactivates legacy extensions
         */
        self::$networkWide = $networkWide;

        self::deactivateLegacyExtensions();

        // create database tables & Pages
        self::checkSingleOrMultiSite();

        // create required files & directories
        self::createFiles();

        // redirect to welcome screen
        set_transient('_eb_activation_redirect', 1, 30);
        set_transient("edwiser_bridge_admin_feedback_notice", "eb_admin_feedback_notice", 60*60*24*15);
    }

    /**
     * deactivates legacy extensions
     *
     * @since 1.1
     */
    public static function deactivateLegacyExtensions()
    {
        // prepare extensions array
        $extensions = array(
            'selective_sync'          => array('selective-synchronization/selective-synchronization.php', '1.0.0'),
            'woocommerce_integration' => array('woocommerce-integration/bridge-woocommerce.php', '1.0.4'),
            'single_signon'           => array('edwiser-bridge-sso/sso.php', '1.0.0')
        );

        // deactive legacy extensions
        foreach ($extensions as $extension) {
            if (is_plugin_active($extension[0])) {
                $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $extension[0]);

                if (isset($plugin_data['Version'])) {
                    if (version_compare($plugin_data['Version'], $extension[1]) <= 0) {
                        deactivate_plugins($extension[0]);
                    }
                }
            }
        }
    }

    /**
     * checks if the plugin is activated on a SIngle site or Network wide
     *
     * @since    1.1.1
     */
    public static function checkSingleOrMultiSite()
    {
        global $wpdb;

        if (is_multisite()) {
            // print_r(is_plugin_active_for_network('edwiser-bridge/edwiser-bridge.php')); die();

            if (self::$networkWide) {
                $allSites = wp_get_sites();


                foreach ($allSites as $blog) {
                    switch_to_blog($blog['blog_id']);

                    self::createMoodleDBTables();
                    self::createPages();
                    self::createDefaultEmailTempaltes();
                    restore_current_blog();
                }
            } else {
                switch_to_blog($wpdb->blogid);
                self::createMoodleDBTables();
                self::createPages();
                self::createDefaultEmailTempaltes();
                restore_current_blog();
            }
        } else {
            self::createMoodleDBTables();
            self::createPages();
            self::createDefaultEmailTempaltes();
        }
    }

    /**
     * Create DB tables
     *
     * @since  1.0.0
     */
    public static function createMoodleDBTables()
    {
        global $wpdb;

        $charset_collate     = $wpdb->get_charset_collate();
        $enrollment_tbl_name = $wpdb->prefix . 'moodle_enrollment';

        $enrollment_table = "CREATE TABLE IF NOT EXISTS $enrollment_tbl_name (
            id            mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id       int(11) NOT NULL,
            course_id     int(11) NOT NULL,
            role_id       int(11) NOT NULL,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            expire_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            act_cnt int(5) DEFAULT '1' NOT NULL,
            PRIMARY KEY id (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($enrollment_table);
        self::alterTable();
    }

    public static function alterTable()
    {
        global $wpdb;
        $enrollment_tbl_name = $wpdb->prefix . 'moodle_enrollment';
        $newCol              = array(
            "expire_time" => array("type" => "datetime", "default" => "0000-00-00 00:00:00"),
            "act_cnt"     => array("type" => "int(5)", "default" => "1"),
        );
        foreach ($newCol as $col => $val) {
            $query  = "SHOW COLUMNS FROM `$enrollment_tbl_name` LIKE '$col';";
            $exists = $wpdb->query($query);
            /**
             * Alter table if the expire_time column is not exisit in the plugin.
             */
            if (!$exists) {
                //$dType      = $val['type'];
                $defaultVal = $val['default'];
                $type = $val['type'];
                $query      = "ALTER TABLE `$enrollment_tbl_name` ADD COLUMN (`$col` $type DEFAULT '$defaultVal' NOT NULL);";
                $wpdb->query($query);
            }
        }
    }

    /**
     * handles addtion of new blog
     *
     * @since  1.1.1
     */
    public static function handleNewBlog($blog_id)
    {
        switch_to_blog($blog_id);
        self::createMoodleDBTables();
        self::createPages();
        restore_current_blog();
    }

    /**
     * Create files/directories.
     *
     * @since  1.0.0
     */
    private static function createFiles()
    {
        // Install files and folders for uploading files and prevent hotlinking
        $upload_dir = wp_upload_dir();

        $files = array(
            array(
                'base'    => $upload_dir['basedir'] . '/eb-logs/',
                'file'    => '.htaccess',
                'content' => 'deny from all',
            ),
        );

        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                $file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w');
                if ($file_handle) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }

    /**
     * Create default pages with shortcodes.
     *
     * Create pages that the plugin relies on, storing page id's in variables.
     *
     *  @since  1.0.0
     */
    public static function createPages()
    {
        include_once 'eb-core-functions.php';

        $page_content = getShortcodePageContent();

        $pages = apply_filters(
            'eb_create_default_pages',
            array(
            'thankyou'    => array(
                'name'       => _x('thank-you-for-purchase', 'Page slug', 'eb-textdomain'),
                'title'      => _x('Thank You for Purchase', 'Page title', 'eb-textdomain'),
                'content'    => __('Thanks for purchasing the course, your order will be processed shortly.', 'eb-textdomain'),
                'option_key' => '',
            ),
            'useraccount' => array(
                'name'       => _x('user-account', 'Page slug', 'eb-textdomain'),
                'title'      => _x('User Account', 'Page title', 'eb-textdomain'),
                'content'    => '[' . apply_filters('eb_user_account_shortcode_tag', 'eb_user_account') . ']',
                'option_key' => 'eb_useraccount_page_id',
            ),
            'mycourses'   => array(
                'name'       => _x('eb-my-courses', 'Page slug', 'eb-textdomain'),
                'title'      => _x('My Courses', 'Page title', 'eb-textdomain'),
                'content'    => $page_content['eb_my_courses'],
                'option_key' => 'eb_my_courses_page_id',
            ),
            'courses'     => array(
                'name'       => _x('eb-courses', 'Page slug', 'eb-textdomain'),
                'title'      => _x('Courses', 'Page title', 'eb-textdomain'),
                'content'    => $page_content['eb_courses'],
                'option_key' => '',
            ),
                )
        );

        foreach ($pages as $key => $page) {
            $key;
            wdmCreatePage(esc_sql($page['name']), $page['option_key'], $page['title'], $page['content']);
        }
    }

    public static function createDefaultEmailTempaltes()
    {
        $defaultTmpl = new EBDefaultEmailTemplate();
        self::updateTemplateData("eb_emailtmpl_create_user", $defaultTmpl->newUserAcoount("eb_emailtmpl_create_user"));

        self::updateTemplateData("eb_emailtmpl_refund_completion_notifier_to_user", $defaultTmpl->notifyUserOnOrderRefund("eb_emailtmpl_refund_completion_notifier_to_user"));
        self::updateTemplateData("eb_emailtmpl_refund_completion_notifier_to_admin", $defaultTmpl->notifyAdminOnOrderRefund("eb_emailtmpl_refund_completion_notifier_to_admin"));

        self::updateTemplateData("eb_emailtmpl_linked_existing_wp_user", $defaultTmpl->linkWPMoodleAccount("eb_emailtmpl_linked_existing_wp_user"));
        self::updateTemplateData("eb_emailtmpl_linked_existing_wp_new_moodle_user", $defaultTmpl->linkNewMoodleAccount("eb_emailtmpl_linked_existing_wp_new_moodle_user"));
        self::updateTemplateData("eb_emailtmpl_order_completed", $defaultTmpl->orderComplete("eb_emailtmpl_order_completed"));
        self::updateTemplateData("eb_emailtmpl_course_access_expir", $defaultTmpl->courseAccessExpired("eb_emailtmpl_course_access_expir"));



/******  Two way synch Moodle triggered emails   ******/

        self::updateTemplateData("eb_emailtmpl_mdl_enrollment_trigger", $defaultTmpl->moodleEnrollmentTrigger("eb_emailtmpl_mdl_enrollment_trigger"));
        self::updateTemplateData("eb_emailtmpl_mdl_un_enrollment_trigger", $defaultTmpl->moodleUnenrollmentTrigger("eb_emailtmpl_mdl_un_enrollment_trigger"));
        self::updateTemplateData("eb_emailtmpl_mdl_user_deletion_trigger", $defaultTmpl->userDeletionTrigger("eb_emailtmpl_mdl_user_deletion_trigger"));

/************************************/





        self::updateAllowMailSendData("eb_emailtmpl_refund_completion_notifier_to_user_notify_allow", "ON");
        self::updateAllowMailSendData("eb_emailtmpl_refund_completion_notifier_to_admin_notify_allow", "ON");

        self::updateAllowMailSendData("eb_emailtmpl_create_user_notify_allow", "ON");
        self::updateAllowMailSendData("eb_emailtmpl_linked_existing_wp_user_notify_allow", "ON");
        self::updateAllowMailSendData("eb_emailtmpl_linked_existing_wp_new_moodle_user_notify_allow", "ON");
        self::updateAllowMailSendData("eb_emailtmpl_order_completed_notify_allow", "ON");
        self::updateAllowMailSendData("eb_emailtmpl_course_access_expir_notify_allow", "ON");



/******  Two way synch Moodle triggered emails   ******/

        self::updateAllowMailSendData("eb_emailtmpl_mdl_enrollment_trigger_notify_allow", "ON");
        self::updateAllowMailSendData("eb_emailtmpl_mdl_un_enrollment_trigger_notify_allow", "ON");
        self::updateAllowMailSendData("eb_emailtmpl_mdl_user_deletion_trigger_notify_allow", "ON");
    }

    private static function updateTemplateData($key, $value)
    {
        if (get_option($key) == false) {
            update_option($key, $value);
        }
    }

    private static function updateAllowMailSendData($key, $value)
    {
        $data = get_option($key);

        if ($data == false) {
            update_option($key, $value);
        }
    }
}
