<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EdwiserBridge
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

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * @var EDW The single instance of the class
     *
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     *
     * @var string The current version of the plugin.
     */
    protected $version;

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
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
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

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->plugin_name = 'edwiserbridge';
        $this->version = '1.0.2';
        $this->defineConstants();
        $this->loadDependencies();
        $this->setLocale();
        $this->definePluginHooks();
    }

    private function checkConstantDefined($key, $value)
    {
        if (!defined($key)) {
            define($key, $value);
        }
    }
    /**
     * Setup plugin constants.
     *
     * @since 1.0.0
     */
    private function defineConstants()
    {
        $upload_dir = wp_upload_dir();

        //get connection settings
        $connection_options = get_option('eb_connection');

        $eb_moodle_url = '';
        if (isset($connection_options['eb_url'])) {
            $eb_moodle_url = $connection_options['eb_url'];
        }
        $eb_moodle_token = '';
        if (isset($connection_options['eb_access_token'])) {
            $eb_moodle_token = $connection_options['eb_access_token'];
        }

        // Plugin version
        // if (!defined('EB_VERSION')) {
        //     define('EB_VERSION', $this->version);
        // }
        $this->checkConstantDefined('EB_VERSION', $this->version);

        // Plugin Folder URL
        // if (!defined('EB_PLUGIN_URL')) {
        //     define('EB_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));
        // }
        $this->checkConstantDefined('EB_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));

        // Plugin Folder Path
        // if (!defined('EB_PLUGIN_DIR')) {
        //     define('EB_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));
        // }
        $this->checkConstantDefined('EB_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));

        // Templates Path ( In case one wants to override templates in child themes )
        // if (!defined('EB_TEMPLATE_PATH')) {
        //     define('EB_TEMPLATE_PATH', 'edwiserbridge/');
        // }
        $this->checkConstantDefined('EB_TEMPLATE_PATH', 'edwiserBridge/');

        // Moodle Access Token
        // if (!defined('EB_ACCESS_TOKEN')) {
        //     define('EB_ACCESS_TOKEN', $eb_moodle_token);
        // }
        $this->checkConstantDefined('EB_ACCESS_TOKEN', $eb_moodle_token);

        // Moodle Access URL
        // if (!defined('EB_ACCESS_URL')) {
        //     define('EB_ACCESS_URL', $eb_moodle_url);
        // }
        $this->checkConstantDefined('EB_ACCESS_URL', $eb_moodle_url);

        // Debug Log Directory
        // if (!defined('EB_LOG_DIR')) {
        //     define('EB_LOG_DIR', $upload_dir['basedir'] . '/eb-logs/');
        // }
        $this->checkConstantDefined('EB_LOG_DIR', $upload_dir['basedir'].'/eb-logs/');
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - EBLoader. Orchestrates the hooks of the plugin.
     * - EBI18n. Defines internationalization functionality.
     * - EbAdmin. Defines all hooks for the admin area.
     * - EbPublic. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function loadDependencies()
    {

        // load admin & public facing files conditionally
        if (is_admin()) {
            $this->adminDependencies();
        } else {
            $this->frontendDependencies();
        }

        /**
         * The core class to manage debug log on the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-logger.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-loader.php';

        /**
         * The class responsible for managing emails sent to user on purchase.
         */
        require_once EB_PLUGIN_DIR.'includes/emails/class-eb-emailer.php';

        /**
         * The class responsible for defining post types and meta boxes
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-post-types.php';

        /**
         * The class responsible for defining course synchronization & management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-course-manager.php';

        /**
         * The class responsible for defining order management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-order-manager.php';

        /**
         * The class responsible for defining user management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-user-manager.php';

        /**
         * The class responsible for defining enrollment management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-enrollment-manager.php';

        /**
         * The class responsible for defining connection management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/api/class-eb-connection-helper.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-i18n.php';

        /**
         * The core class to manage payments on the site.
         */
        require_once EB_PLUGIN_DIR.'includes/payments/class-eb-payment-manager.php';

        // core functions
        require_once EB_PLUGIN_DIR.'includes/eb-core-functions.php';
        require_once EB_PLUGIN_DIR.'includes/eb-formatting-functions.php';

         // To handle addition of new blog (for multisite installations)

         require_once EB_PLUGIN_DIR.'includes/class-eb-activator.php';

        $this->loader = new EBLoader();
    }

    /**
     * admin facing code.
     *
     * @since    1.0.0
     */
    private function adminDependencies()
    {
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once EB_PLUGIN_DIR.'admin/class-eb-admin.php';
        require_once EB_PLUGIN_DIR.'admin/class-eb-welcome.php';
        require_once EB_PLUGIN_DIR.'admin/class-eb-extensions.php';

        /**
         * The core classes that initiates settings module.
         */
        require_once EB_PLUGIN_DIR.'admin/class-eb-admin-menus.php';
        require_once EB_PLUGIN_DIR.'admin/class-eb-admin-settings.php';

        /**
         * The core class to handle custom events events on settings page.
         *
         * Used in:
         *
         * Test Connection
         * Courses & User Data Synchronization
         */
        require_once EB_PLUGIN_DIR.'admin/class-eb-settings-ajax-initiater.php';
    }

    /**
     * public facing code.
     *
     * @since    1.0.0
     */
    private function frontendDependencies()
    {
        /**
         * The classes responsible for defining and handling all actions that occur in the public-facing
         * side of the site.
         */
        require_once EB_PLUGIN_DIR.'public/class-eb-public.php';
        /**
         * Tha classes responsible for defining shortcodes.
         */
        require_once EB_PLUGIN_DIR.'public/class-eb-shortcodes.php';
        require_once EB_PLUGIN_DIR.'public/shortcodes/class-eb-shortcode-user-account.php';
        require_once EB_PLUGIN_DIR.'public/shortcodes/class-eb-shortcode-user-profile.php';

        /**
         * The class responsible for handling frontend forms, specifically login & registration forms.
         */
        require_once EB_PLUGIN_DIR.'public/class-eb-frontend-form-handler.php';
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WPmoodle_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function setLocale()
    {
        $plugin_i18n = new EBI18n();
        $plugin_i18n->setDomain('eb-textdomain');

        $this->loader->addAction('plugins_loaded', $plugin_i18n, 'loadPluginTextdomain');
    }

    /**
     * Get User Manager class.
     *
     * @since    1.0.0
     *
     * @return EBUserManager
     */
    public function userManager()
    {
        return EBUserManager::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Course Manager class.
     *
     * @since    1.0.0
     *
     * @return EBCourseManager
     */
    public function courseManager()
    {
        return EBCourseManager::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Enrollment Manager class.
     *
     * @since    1.0.0
     *
     * @return EBEnrollmentManager
     */
    public function enrollmentManager()
    {
        return EBEnrollmentManager::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Order Manager class.
     *
     * @since    1.0.0
     *
     * @return EBOrderManager
     */
    public function orderManager()
    {
        return EBOrderManager::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Connection Helper class.
     *
     * @since    1.0.0
     *
     * @return EBConnectionHelper
     */
    public function connectionHelper()
    {
        return EBConnectionHelper::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Logger class.
     *
     * @since    1.0.0
     *
     * @return EBLogger
     */
    public function logger()
    {
        return EBLogger::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Register all plugin hooks.
     *
     * @since   1.0.0
     */
    private function definePluginHooks()
    {
        $this->defineUserHooks();

        $this->defineSystemHooks();

        $this->defineEmailHooks();

        if (is_admin()) {
            $this->defineAdminHooks();
        } else {
            $this->definePublicHooks();
        }
    }

    /**
     * Register all of the hooks related to the admin area & admin settings area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function defineAdminHooks()
    {
        //if (is_admin()) {
        $plugin_admin = new EbAdmin($this->getPluginName(), $this->getVersion());
        $this->loader->addAction('admin_enqueue_scripts', $plugin_admin, 'adminEnqueueStyles');
        $this->loader->addAction('admin_enqueue_scripts', $plugin_admin, 'adminEnqueueScripts');

            /*
             * Handling custom button events on settings page
             * Responsible for initiating ajax requests made by custom buttons placed in settings pages.
             * Specifically 'Synchronization Request' & 'Test Connection Request' on Moodle settings page.
             */
            $admin_settings_init = new EBSettingsAjaxInitiater($this->getPluginName(), $this->getVersion());
        $this->loader->addAction(
            'wp_ajax_handleCourseSynchronization',
            $admin_settings_init,
            'courseSynchronizationInitiater'
        );
        $this->loader->addAction(
            'wp_ajax_handleUserCourseSynchronization',
            $admin_settings_init,
            'userDataSynchronizationInitiater'
        );
        $this->loader->addAction(
            'wp_ajax_handleConnectionTest',
            $admin_settings_init,
            'connectionTestInitiater'
        );
        //}
    }

    /**
     * Register all of the hooks related to the user profile & actions related with user.
     *
     * @since    1.0.0
     */
    private function defineUserHooks()
    {

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

        // password sync with moodle on profile update & password reset
        $this->loader->addAction('profile_update', $this->userManager(), 'passwordUpdate', 10, 2);
        $this->loader->addAction('password_reset', $this->userManager(), 'passwordReset', 10, 2);

        /*
         * In case a user is permanentaly deleted from wordpress,
         * update course enrollment table appropriately by deleting records for user being deleted.
         */
        $this->loader->addAction('delete_user', $this->userManager(), 'deleteEnrollmentRecordsOnUserDeletion');
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
        $this->loader->addAction('init', $plugin_post_types, 'registerTaxonomies');
        $this->loader->addAction('init', $plugin_post_types, 'registerPostTypes');
        $this->loader->addFilter(
            'post_updated_messages',
            $plugin_post_types,
            'customPostTypeUpdateMessages'
        ); // change post updated messages
        $this->loader->addAction('add_meta_boxes', $plugin_post_types, 'registerMetaBoxes');
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
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function definePublicHooks()
    {
        $plugin_public = new EbPublic($this->getPluginName(), $this->getVersion());
        $template_loader = new EbTemplateLoader($this->getPluginName(), $this->getVersion());

        $this->loader->addAction('wp_enqueue_scripts', $plugin_public, 'publicEnqueueStyles');
        $this->loader->addAction('wp_enqueue_scripts', $plugin_public, 'publicEnqueueScripts');

        // Template loader hooks
        $this->loader->addFilter('template_include', $template_loader, 'templateLoader');

        // Initiate our shortcodes class on init hook
        $this->loader->addAction('init', 'app\wisdmlabs\edwiserBridge\EbShortcodes', 'init');

        // Frontend form handler hooks to handle user login & registration
        $this->loader->addAction('wp_loaded', 'app\wisdmlabs\edwiserBridge\EbFrontendFormHandler', 'processLogin', 20);
        $this->loader->addAction('wp_loaded', 'app\wisdmlabs\edwiserBridge\EbFrontendFormHandler', 'processRegistration', 20);
        // process course join request for free courses
        $this->loader->addAction('wp_loaded', 'app\wisdmlabs\edwiserBridge\EbFrontendFormHandler', 'processFreeCourseJoinRequest');
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
            'eb_order_status_completed',
            $plugin_emailer,
            'sendOrderCompletionEmail',
            10
        ); // email on order status completed
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     *
     * @return string The name of the plugin.
     */
    public function getPluginName()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     *
     * @return EBLoader Orchestrates the hooks of the plugin.
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     *
     * @return string The version number of the plugin.
     */
    public function getVersion()
    {
        return $this->version;
    }
}

/**
 * Returns the main instance of EDW to prevent the need to use globals.
 *
 * @since  1.0.0
 *
 * @return EDW
 */
function edwiserBridgeInstance()
{
    return EdwiserBridge::instance();
}
