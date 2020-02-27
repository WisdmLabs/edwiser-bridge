<?php
/**
* 
*/
namespace app\wisdmlabs\edwiserBridge;

class EdwiserBridgeLoadCommonHooks
{
    
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     *
     * @var EBLoader Maintains and registers all hooks for the plugin.
     */
    protected $loader;


    public function __construct($loader)
    {
        $this->loader = $loader;
    }


    public function loadHooks()
    {
        $this->defineUserHooks();
        $this->defineSystemHooks();
        $this->defineEmailHooks();
    }


/**
     * Register all of the hooks related to the user profile & actions related with user.
     *
     * @since    1.0.0
     */
    private function defineUserHooks()
    {
        // $manageEnrollment = new EBManageUserEnrollment($this->plugin_name, $this->version);

        //@since 1.3.5
/*        $this->loader->addAction(
            'wp_login',
            $manageEnrollment,
            'processEnrollmentOnLogin',
            100,
            2
        );*/

        // display bulk action to unlink moodle account
        // On users page in dashboard.
        $this->loader->addAction(
            'admin_print_scripts',
            $this->userManager(),
            'linkUserBulkActions',
            100
        ); //add unlink action
        $this->loader->addAction(
            'load-users.php',
            $this->userManager(),
            'linkUserBulkActionsHandler'
        ); // handle unlink action
        $this->loader->addAction(
            'admin_notices',
            $this->userManager(),
            'linkUserBulkActionsNotices'
        ); // display unlink notices

        // display enroll to course dropdown in user profile
        $this->loader->addAction(
            'show_user_profile',
            $this->userManager(),
            'displayUsersEnrolledCourses'
        );
        $this->loader->addAction(
            'edit_user_profile',
            $this->userManager(),
            'displayUsersEnrolledCourses'
        );

        // enroll user to course on user profile update
        $this->loader->addAction(
            'personal_options_update',
            $this->userManager(),
            'updateCoursesOnProfileUpdate'
        );
        $this->loader->addAction(
            'edit_user_profile_update',
            $this->userManager(),
            'updateCoursesOnProfileUpdate'
        );

        $this->loader->addAction(
            'wp_ajax_moodleLinkUnlinkUser',
            $this->userManager(),
            'moodleLinkUnlinkUser'
        );


        $this->loader->addAction(
            'admin_notices',
            $this->userManager(),
            'moodleLinkUnlinkUserNotices'
        );

        // password sync with moodle on profile update & password reset
        $this->loader->addAction('profile_update', $this->userManager(), 'passwordUpdate', 10, 2);
        $this->loader->addAction('password_reset', $this->userManager(), 'passwordReset', 10, 2);

        /*
         * In case a user is permanentaly deleted from wordpress,
         * update course enrollment table appropriately by deleting records for user being deleted.
         */
        $this->loader->addAction('delete_user', $this->userManager(), 'deleteEnrollmentRecordsOnUserDeletion');

        $this->loader->addAction("eb_before_single_course", $this->userManager(), "unenrollOnCourseAccessExpire");
        $this->loader->addAction("eb_before_my_courses_wrapper", $this->userManager(), "unenrollOnCourseAccessExpire");
    }

    /**
     * Register all of the hooks related to the internal functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function defineSystemHooks()
    {

        // Registers core post types, taxonomies and metaboxes.
        $plugin_post_types = new EBPostTypes($this->getPluginName(), $this->getVersion());

        /*************/
        $apiEndPointHandler = new EBExternalApiEndpoint();
        $this->loader->addAction('rest_api_init', $apiEndPointHandler, "apiRegistration");
        /*************/


        $this->loader->addAction('init', $plugin_post_types, 'registerTaxonomies');
        $this->loader->addAction('init', $plugin_post_types, 'registerPostTypes');
        $this->loader->addFilter(
            'post_updated_messages',
            $plugin_post_types,
            'customPostTypeUpdateMessages'
        ); // change post updated messages
        $this->loader->addAction(
            'add_meta_boxes',
            $plugin_post_types,
            'registerMetaBoxes'
        );

        $this->loader->addAction(
            'save_post',
            $plugin_post_types,
            'handlePostOptionsSave',
            15
        ); //this hook must be run after update_order_status() function added below.

        // hooks related to order manager class
        // should be called before handle_post_options_save() function
        // to determine previous order status on order status change.
        $this->loader->addAction(
            'save_post_eb_order',
            $this->orderManager(),
            'updateOrderStatusOnOrderSave',
            10
        );
        $this->loader->addAction(
            'eb_order_status_completed',
            $this->orderManager(),
            'enrollToCourseOnOrderComplete',
            10
        );
        $this->loader->addAction(
            'wp_ajax_createNewOrderAjaxWrapper',
            $this->orderManager(),
            'createNewOrderAjaxWrapper',
            10
        );
        $this->loader->addAction(
            'manage_eb_order_posts_columns',
            $this->orderManager(),
            'addOrderStatusColumn',
            10,
            1
        );
        $this->loader->addAction(
            'manage_eb_order_posts_custom_column',
            $this->orderManager(),
            'addOrderStatusColumnContent',
            10,
            2
        );

        // hooks related to payment management
        $payment_mgr = new EBPaymentManager($this->getPluginName(), $this->getVersion());
        $this->loader->addAction('generate_rewrite_rules', $payment_mgr, 'paypalRewriteRules');
        $this->loader->addFilter('query_vars', $payment_mgr, 'addQueryVars');
        $this->loader->addAction('parse_request', $payment_mgr, 'parseIpnRequest');

        /*
         * In case a course is permanentaly deleted from moodle course list,
         * update course enrollment table appropriately by deleting records for course being deleted.
         */
        $this->loader->addAction(
            'before_delete_post',
            $this->courseManager(),
            'deleteEnrollmentRecordsOnCourseDeletion'
        );
        $this->loader->addAction(
            'manage_eb_course_posts_columns',
            $this->courseManager(),
            'addCoursePriceTypeColumn',
            10,
            1
        );
        $this->loader->addAction(
            'manage_eb_course_posts_custom_column',
            $this->courseManager(),
            'addCoursePriceTypeColumnContent',
            10,
            2
        );


        // handles addtion of new blog

        $this->loader->addAction(
            'wpmu_new_blog',
            'app\wisdmlabs\edwiserBridge\EBActivator',
            'handleNewBlog',
            10,
            1
        );

        // wp_remote_post() has default timeout set as 5 seconds, increase it to remove timeout problem
        $this->loader->addFilter('http_request_timeout', $this->connectionHelper(), 'connectionTimeoutExtender');
    }


        /**
     * Register all of the hooks related to sending emails
     * of various activities.
     *
     * @since    1.0.0
     */
    private function defineEmailHooks()
    {
        $plugin_emailer = new EBEmailer($this->getPluginName(), $this->getVersion());

        // send emails on various system events as specified
        $this->loader->addAction(
            'eb_email_header',
            $plugin_emailer,
            'getEmailHeader',
            10,
            1
        ); // Get email header template.
        $this->loader->addAction(
            'eb_email_footer',
            $plugin_emailer,
            'getEmailFooter',
            10
        ); // Get email footer template.
        $this->loader->addAction(
            'eb_created_user',
            $plugin_emailer,
            'sendNewUserEmail',
            10
        ); // email on new user registration
        $this->loader->addAction(
            'eb_linked_to_existing_wordpress_user',
            $plugin_emailer,
            'sendExistingUserMoodleAccountEmail',
            10
        ); // email on moodle user link to existing wordpress user
        $this->loader->addAction(
            'eb_linked_to_existing_wordpress_to_new_user',
            $plugin_emailer,
            'sendExistingWpUserNewMoodleAccountEmail',
            10
        ); // email on moodle user link to existing wordpress user
        $this->loader->addAction(
            'eb_order_status_completed',
            $plugin_emailer,
            'sendOrderCompletionEmail',
            10
        ); // email on order status completed
        $this->loader->addAction(
            'eb_course_access_expire_alert',
            $plugin_emailer,
            'sendCourseAccessExpireEmail',
            10
        ); // email on order status completed

        $this->loader->addAction(
            'eb_refund_completion',
            $plugin_emailer,
            'refundCompletionEmail',
            10
        ); // email on successful refund


        /********  Two way synch  ******/

        $this->loader->addAction(
            'eb_mdl_enrollment_trigger',
            $plugin_emailer,
            'sendMdlTriggeredEnrollmentEmail',
            10
        ); // email on trigger of the Moodle course enrollment


        $this->loader->addAction(
            'eb_mdl_un_enrollment_trigger',
            $plugin_emailer,
            'sendMdlTriggeredUnenrollmentEmail',
            10
        ); // email on trigger of the Moodle course Un enrollment

        $this->loader->addAction(
            'eb_mdl_user_deletion_trigger',
            $plugin_emailer,
            'sendMdlTriggeredUserDeletionEmail',
            10
        ); // email on trigger of the Moodle User Deletion
        /**************/
    }
}
