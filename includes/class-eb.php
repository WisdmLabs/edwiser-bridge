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
 *
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
class EdwiserBridge {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      EB_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     *
     *
     * @var EDW The single instance of the class
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;


    /**
     * Main EDW Instance
     *
     * Ensures only one instance of EDW is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see EB()
     * @return EDW - Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since   1.0.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'eb-textdomain' ), '1.0.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since   1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'eb-textdomain' ), '1.0.0' );
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
    public function __construct() {
        $this->plugin_name = 'edwiserbridge';
        $this->version     = '1.0.2';
        $this->define_constants();
        $this->load_dependencies();
        $this->set_locale();
        $this->define_plugin_hooks();
    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.0.0
     * @return void
     */
    private function define_constants() {

        $upload_dir = wp_upload_dir();

        //get connection settings
        $connection_options = get_option( 'eb_connection' );

        $eb_moodle_url   = isset( $connection_options['eb_url'] )?$connection_options['eb_url']:'';
        $eb_moodle_token = isset( $connection_options['eb_access_token'] )?$connection_options['eb_access_token']:'';

        // Plugin version
        if ( !defined( 'EB_VERSION' ) ) {
            define( 'EB_VERSION', $this->version );
        }

        // Plugin Folder URL
        if ( !defined( 'EB_PLUGIN_URL' ) ) {
            define( 'EB_PLUGIN_URL', plugin_dir_url( dirname( __FILE__ ) ) );
        }

        // Plugin Folder Path
        if ( !defined( 'EB_PLUGIN_DIR' ) ) {
            define( 'EB_PLUGIN_DIR', plugin_dir_path( dirname( __FILE__ ) ) );
        }

        // Templates Path ( In case one wants to override templates in child themes )
        if ( !defined( 'EB_TEMPLATE_PATH' ) ) {
            define( 'EB_TEMPLATE_PATH', 'edwiserbridge/' );
        }

        // Moodle Access Token
        if ( !defined( 'EB_ACCESS_TOKEN' ) ) {
            define( 'EB_ACCESS_TOKEN', $eb_moodle_token );
        }

        // Moodle Access URL
        if ( !defined( 'EB_ACCESS_URL' ) ) {
            define( 'EB_ACCESS_URL', $eb_moodle_url );
        }

        // Debug Log Directory
        if ( !defined( 'EB_LOG_DIR' ) ) {
            define( 'EB_LOG_DIR', $upload_dir['basedir'] . '/eb-logs/' );
        }
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - EB_Loader. Orchestrates the hooks of the plugin.
     * - EB_i18n. Defines internationalization functionality.
     * - EB_Admin. Defines all hooks for the admin area.
     * - EB_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        // load admin & public facing files conditionally
        if ( is_admin() ) {
            $this->admin_dependencies();
        } elseif ( !is_admin() ) {
            $this->frontend_dependencies();
        }

        /**
         * The core class to manage debug log on the plugin.
         */
        require_once EB_PLUGIN_DIR . 'includes/class-eb-logger.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once EB_PLUGIN_DIR . 'includes/class-eb-loader.php';

        /**
         * The class responsible for managing emails sent to user on purchase.
         */
        require_once EB_PLUGIN_DIR . 'includes/emails/class-eb-emailer.php';

        /**
         * The class responsible for defining post types and meta boxes
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR . 'includes/class-eb-post-types.php';

        /**
         * The class responsible for defining course synchronization & management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR . 'includes/class-eb-course-manager.php';

        /**
         * The class responsible for defining order management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR . 'includes/class-eb-order-manager.php';

        /**
         * The class responsible for defining user management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR . 'includes/class-eb-user-manager.php';

        /**
         * The class responsible for defining enrollment management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR . 'includes/class-eb-enrollment-manager.php';

        /**
         * The class responsible for defining connection management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR . 'includes/api/class-eb-connection-helper.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR . 'includes/class-eb-i18n.php';

        /**
         * The core class to manage payments on the site.
         */
        require_once EB_PLUGIN_DIR . 'includes/payments/class-eb-payment-manager.php';

        // core functions
        require_once EB_PLUGIN_DIR . 'includes/eb-core-functions.php';
        require_once EB_PLUGIN_DIR . 'includes/eb-formatting-functions.php';

        $this->loader = new EB_Loader();
    }

    /**
     * admin facing code
     *
     * @since    1.0.0
     * @access   private
     */
    private function admin_dependencies() {
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once EB_PLUGIN_DIR . 'admin/class-eb-admin.php';
        require_once EB_PLUGIN_DIR . 'admin/class-eb-welcome.php';
        require_once EB_PLUGIN_DIR . 'admin/class-eb-extensions.php';

        /**
         * The core classes that initiates settings module
         */
        require_once EB_PLUGIN_DIR . 'admin/class-eb-admin-menus.php';
        require_once EB_PLUGIN_DIR . 'admin/class-eb-admin-settings.php';

        /**
         * The core class to handle custom events events on settings page
         *
         * Used in:
         *
         * Test Connection
         * Courses & User Data Synchronization
         *
         */
        require_once EB_PLUGIN_DIR . 'admin/class-eb-settings-ajax-initiater.php';
    }

    /**
     * public facing code
     *
     * @since    1.0.0
     * @access   private
     */
    private function frontend_dependencies() {
        /**
         * The classes responsible for defining and handling all actions that occur in the public-facing
         * side of the site.
         */
        require_once EB_PLUGIN_DIR . 'public/class-eb-public.php';
        /**
         * Tha classes responsible for defining shortcodes
         */
        require_once EB_PLUGIN_DIR . 'public/class-eb-shortcodes.php';
        require_once EB_PLUGIN_DIR . 'public/shortcodes/class-eb-shortcode-user-account.php';
        require_once EB_PLUGIN_DIR . 'public/shortcodes/class-eb-shortcode-user-profile.php';

        /**
         * The class responsible for handling frontend forms, specifically login & registration forms.
         */
        require_once EB_PLUGIN_DIR . 'public/class-eb-frontend-form-handler.php';
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WPmoodle_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new EB_i18n();
        $plugin_i18n->set_domain( 'eb-textdomain' );

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Get User Manager class.
     *
     * @since    1.0.0
     * @return EB_User_Manager
     */
    public function user_manager() {
        return EB_User_Manager::instance( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Get Course Manager class.
     *
     * @since    1.0.0
     * @return EB_Course_Manager
     */
    public function course_manager() {
        return EB_Course_Manager::instance( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Get Enrollment Manager class.
     *
     * @since    1.0.0
     * @return EB_Enrollment_Manager
     */
    public function enrollment_manager() {
        return EB_Enrollment_Manager::instance( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Get Order Manager class.
     *
     * @since    1.0.0
     * @return EB_Order_Manager
     */
    public function order_manager() {
        return EB_Order_Manager::instance( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Get Connection Helper class.
     *
     * @since    1.0.0
     * @return EB_Connection_Helper
     */
    public function connection_helper() {
        return EB_Connection_Helper::instance( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Get Logger class.
     *
     * @since    1.0.0
     * @return EB_Logger
     */
    public function logger() {
        return EB_Logger::instance( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Register all plugin hooks
     *
     * @since   1.0.0
     * @access  private
     */
    private function define_plugin_hooks() {
        $this->define_user_hooks();

        $this->define_system_hooks();

        $this->define_email_hooks();

        if ( is_admin() ) {
            $this->define_admin_hooks();
        } else {
            $this->define_public_hooks();
        }
    }

    /**
     * Register all of the hooks related to the admin area & admin settings area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        if ( is_admin() ) {

            $plugin_admin = new EB_Admin( $this->get_plugin_name(), $this->get_version() );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'admin_enqueue_styles' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'admin_enqueue_scripts' );

            /**
             * Handling custom button events on settings page
             * Responsible for initiating ajax requests made by custom buttons placed in settings pages.
             * Specifically 'Synchronization Request' & 'Test Connection Request' on Moodle settings page.
             */
            $plugin_admin_settings_initiater = new EB_Settings_Ajax_Initiater( $this->get_plugin_name(), $this->get_version() );
            $this->loader->add_action( 'wp_ajax_handle_course_synchronization', $plugin_admin_settings_initiater, 'course_synchronization_initiater' );
            $this->loader->add_action( 'wp_ajax_handle_user_course_synchronization', $plugin_admin_settings_initiater, 'user_data_synchronization_initiater' );
            $this->loader->add_action( 'wp_ajax_handle_connection_test', $plugin_admin_settings_initiater, 'connection_test_initiater' );

        }
    }

    /**
     * Register all of the hooks related to the user profile & actions related with user
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_user_hooks() {

        // display bulk action to unlink moodle account
        // On users page in dashboard.
        $this->loader->add_action( 'admin_print_scripts', $this->user_manager(), 'link_user_bulk_actions', 100 ); //add unlink action
        $this->loader->add_action( 'load-users.php', $this->user_manager(), 'link_user_bulk_actions_handler' ); // handle unlink action
        $this->loader->add_action( 'admin_notices', $this->user_manager(), 'link_user_bulk_actions_notices' ); // display unlink notices

        // display enroll to course dropdown in user profile
        $this->loader->add_action( 'show_user_profile', $this->user_manager(), 'display_users_enrolled_courses' );
        $this->loader->add_action( 'edit_user_profile', $this->user_manager(), 'display_users_enrolled_courses' );

        // enroll user to course on user profile update
        $this->loader->add_action( 'personal_options_update', $this->user_manager(), 'update_courses_on_profile_update' );
        $this->loader->add_action( 'edit_user_profile_update', $this->user_manager(), 'update_courses_on_profile_update' );

        // password sync with moodle on profile update & password reset
        $this->loader->add_action( 'profile_update', $this->user_manager(), 'password_update', 10, 2 );
        $this->loader->add_action( 'password_reset', $this->user_manager(), 'password_reset', 10, 2 );

        /**
         * In case a user is permanentaly deleted from wordpress,
         * update course enrollment table appropriately by deleting records for user being deleted.
         */
        $this->loader->add_action( 'delete_user', $this->user_manager(), 'delete_enrollment_records_on_user_deletion' );
    }

    /**
     * Register all of the hooks related to the internal functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_system_hooks() {

        // Registers core post types, taxonomies and metaboxes.
        $plugin_post_types = new EB_Post_Types( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'init', $plugin_post_types, 'register_taxonomies' );
        $this->loader->add_action( 'init', $plugin_post_types, 'register_post_types' );
        $this->loader->add_filter( 'post_updated_messages', $plugin_post_types, 'custom_post_type_update_messages' ); // change post updated messages
        $this->loader->add_action( 'add_meta_boxes', $plugin_post_types, 'register_meta_boxes' );
        $this->loader->add_action( 'save_post', $plugin_post_types, 'handle_post_options_save', 15 ); //this hook must be run after update_order_status() function added below.

        // hooks related to order manager class
        // should be called before handle_post_options_save() function to determine previous order status on order status change.
        $this->loader->add_action( 'save_post_eb_order', $this->order_manager(), 'update_order_status_on_order_save', 10 );
        $this->loader->add_action( 'eb_order_status_completed', $this->order_manager(), 'enroll_to_course_on_order_complete', 10 );
        $this->loader->add_action( 'wp_ajax_create_new_order_ajax_wrapper', $this->order_manager(), 'create_new_order_ajax_wrapper', 10 );
        $this->loader->add_action( 'manage_eb_order_posts_columns', $this->order_manager(), 'add_order_status_column', 10, 1 );
        $this->loader->add_action( 'manage_eb_order_posts_custom_column', $this->order_manager(), 'add_order_status_column_content', 10, 2 );


        // hooks related to payment management
        $plugin_payment_manager = new EB_Payment_Manager( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'generate_rewrite_rules', $plugin_payment_manager, 'paypal_rewrite_rules' );
        $this->loader->add_filter( 'query_vars', $plugin_payment_manager, 'add_query_vars' );
        $this->loader->add_action( 'parse_request', $plugin_payment_manager, 'parse_ipn_request' );

        /**
         * In case a course is permanentaly deleted from moodle course list,
         * update course enrollment table appropriately by deleting records for course being deleted.
         */
        $this->loader->add_action( 'before_delete_post', $this->course_manager(), 'delete_enrollment_records_on_course_deletion' );
        $this->loader->add_action( 'manage_eb_course_posts_columns', $this->course_manager(), 'add_course_price_type_column', 10, 1 );
        $this->loader->add_action( 'manage_eb_course_posts_custom_column', $this->course_manager(), 'add_course_price_type_column_content', 10, 2 );

        
        // wp_remote_post() has default timeout set as 5 seconds, increase it to remove timeout problem
        $this->loader->add_filter( 'http_request_timeout', $this->connection_helper(), 'connection_timeout_extender' );
        
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new EB_Public( $this->get_plugin_name(), $this->get_version() );
        $plugin_template_loader = new EB_Template_Loader( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'public_enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'public_enqueue_scripts' );

        // Template loader hooks
        $this->loader->add_filter( 'template_include', $plugin_template_loader, 'template_loader' );

        // Initiate our shortcodes class on init hook
        $this->loader->add_action( 'init', 'EB_Shortcodes', 'init' );

        // Frontend form handler hooks to handle user login & registration
        $this->loader->add_action( 'wp_loaded', 'EB_Frontend_Form_Handler', 'process_login', 20 );
        $this->loader->add_action( 'wp_loaded', 'EB_Frontend_Form_Handler', 'process_registration', 20 );
        // process course join request for free courses
        $this->loader->add_action( 'wp_loaded', 'EB_Frontend_Form_Handler', 'process_free_course_join_request' );
    }

    /**
     * Register all of the hooks related to sending emails
     * of various activities.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_email_hooks() {
        $plugin_emailer = new EB_Emailer( $this->get_plugin_name(), $this->get_version() );

        // send emails on various system events as specified
        $this->loader->add_action( 'eb_email_header', $plugin_emailer, 'get_email_header', 10, 1 ); // Get email header template.
        $this->loader->add_action( 'eb_email_footer', $plugin_emailer, 'get_email_footer', 10 ); // Get email footer template.
        $this->loader->add_action( 'eb_created_user', $plugin_emailer, 'send_new_user_email', 10 ); // email on new user registration
        $this->loader->add_action( 'eb_linked_to_existing_wordpress_user', $plugin_emailer, 'send_existing_user_moodle_account_email', 10 ); // email on moodle user link to existing wordpress user
        $this->loader->add_action( 'eb_order_status_completed', $plugin_emailer, 'send_order_completion_email', 10 ); // email on order status completed
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }


    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return string The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return EB_Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return string The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}

/**
 * Returns the main instance of EDW to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return EDW
 */
function EB() {
    return EdwiserBridge::instance();
}
