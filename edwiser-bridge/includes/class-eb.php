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
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

/**
 * Edwiser Bridge.
 */
class EdwiserBridge {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var Eb_Loader Maintains and registers all hooks for the plugin.
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
	 * Instance.
	 *
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
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
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
		$this->version     = '2.0.9';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_plugin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Eb_Loader. Orchestrates the hooks of the plugin.
	 * - Eb_I18n. Defines internationalization functionality.
	 * - EbAdmin. Defines all hooks for the admin area.
	 * - Eb_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {

		$plugin_path = plugin_dir_path( __DIR__ );

		// load admin & public facing files conditionally.
		if ( is_admin() ) {
			$this->admin_dependencies();
		} else {
			$this->frontend_dependencies();
		}

		/*
		* Adding this function because of is_plugin_active function not found error is given
		*/
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		/*
		 * Usage tracking file.
		 */
		require_once $plugin_path . 'includes/class-eb-usage-tracking.php';

		/**
		 * The core class to manage debug log on the plugin.
		 */
		require_once $plugin_path . 'includes/api/class-eb-external-api-endpoint.php';

		/**
		 * The core class to manage debug log on the plugin.
		 */
		require_once $plugin_path . 'includes/class-eb-logger.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once $plugin_path . 'includes/class-eb-loader.php';

		/**
		 * The class responsible for managing emails sent to user on purchase.
		 */
		require_once $plugin_path . 'includes/emails/class-eb-emailer.php';

		/**
		 * The class responsible for defining post types and meta boxes
		 * of the plugin.
		 */
		require_once $plugin_path . 'includes/class-eb-post-types.php';

		/**
		 * The class responsible for defining course synchronization & management functionality
		 * of the plugin.
		 */
		require_once $plugin_path . 'includes/class-eb-course-manager.php';

		/**
		 * The class responsible for defining order management functionality
		 * of the plugin.
		 */
		require_once $plugin_path . 'includes/class-eb-order-manager.php';

		/**
		 * The class responsible for defining user management functionality
		 * of the plugin.
		 */
		require_once $plugin_path . 'includes/class-eb-user-manager.php';

		/**
		 * The class responsible for defining enrollment management functionality
		 * of the plugin.
		 */
		require_once $plugin_path . 'includes/class-eb-enrollment-manager.php';

		/**
		 * The class responsible for defining connection management functionality
		 * of the plugin.
		 */
		require_once $plugin_path . 'includes/api/class-eb-connection-helper.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once $plugin_path . 'includes/class-eb-i18n.php';

		/**
		 * The core class to manage payments on the site.
		 */
		require_once $plugin_path . 'includes/payments/class-eb-payment-manager.php';

		/*
		 * loading refund dependencies.
		 */
		require_once $plugin_path . 'includes/payments/class-eb-refund-manager.php';

		// core functions.
		require_once $plugin_path . 'includes/eb-core-functions.php';

		require_once $plugin_path . 'includes/eb-formatting-functions.php';

		// To handle addition of new blog (for multisite installations).
		require_once $plugin_path . 'includes/class-eb-activator.php';

		// To handel the email template modification.
		require_once $plugin_path . 'admin/class-eb-email-template.php';

		require_once $plugin_path . 'includes/class-eb-email-template-parser.php';

		require_once $plugin_path . 'includes/class-eb-default-email-templates.php';

		// handles theme compatibility.
		require_once $plugin_path . 'public/class-eb-theme-compatibility.php';

		/*
		 * loading refund dependencies.
		 * @since      1.3.3
		 */
		require_once $plugin_path . 'includes/class-eb-gdpr-compatibility.php';

		$this->loader = new Eb_Loader();
	}

	/**
	 * Admin facing code.
	 *
	 * @since    1.0.0
	 */
	private function admin_dependencies() {

		$plugin_path = plugin_dir_path( __DIR__ );

		/*
		 *Class responsible to show admin notices
		 */
		require_once $plugin_path . 'includes/class-eb-admin-notice-handler.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once $plugin_path . 'admin/class-eb-admin.php';

		require_once $plugin_path . 'admin/class-eb-welcome.php';

		/**
		*The class used to add Moodle account column on users page frontend
		*/
		require_once $plugin_path . 'admin/class-eb-moodle-link-unlink.php';

		require_once $plugin_path . 'includes/class-eb-custom-list-table.php';

		require_once $plugin_path . 'includes/class-eb-manage-enrollment.php';

		/**
		 * The core classes that initiates settings module.
		 */
		require_once $plugin_path . 'admin/class-eb-admin-menus.php';

		require_once $plugin_path . 'admin/class-eb-admin-settings.php';

		/**
		 * The core class to handle custom events events on settings page.
		 *
		 * Used in:
		 *
		 * Test Connection
		 * Courses & User Data Synchronization
		 */
		require_once $plugin_path . 'admin/class-eb-settings-ajax-initiater.php';

		/**
		 * Add order meta boxes.
		 */
		require_once $plugin_path . 'includes/class-eb-order-meta.php';

		require_once $plugin_path . 'includes/class-eb-order-status.php';

		require_once $plugin_path . 'includes/class-eb-order-history-meta.php';

		require_once $plugin_path . 'includes/class-eb-manage-order-refund.php';

	}

	/**
	 * Public facing code.
	 *
	 * @since    1.0.0
	 */
	private function frontend_dependencies() {

		$plugin_path = plugin_dir_path( __DIR__ );

		/*
		 * inlcuding course progress file
		 * @since 1.4
		 */
		require_once $plugin_path . 'includes/class-eb-course-progress.php';

		/**
		 * The classes responsible for defining and handling all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once $plugin_path . 'public/class-eb-public.php';

		/**
		 * Tha classes responsible for defining shortcodes.
		 */
		require_once $plugin_path . 'includes/class-eb-manage-enrollment.php';

		require_once $plugin_path . 'public/class-eb-shortcodes.php';

		require_once $plugin_path . 'public/shortcodes/class-eb-shortcode-user-account.php';

		require_once $plugin_path . 'public/shortcodes/class-eb-shortcode-user-profile.php';

		require_once $plugin_path . 'public/shortcodes/class-eb-shortcode-courses.php';

		require_once $plugin_path . 'public/shortcodes/class-eb-shortcode-course.php';

		require_once $plugin_path . 'public/shortcodes/class-eb-shortcode-my-courses.php';

		/**
		 * The class responsible for handling frontend forms, specifically login & registration forms.
		 */
		require_once $plugin_path . 'public/class-eb-frontend-form-handler.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WPmoodle_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function set_locale() {
		$plugin_i18n = new Eb_I18n();
		$plugin_i18n->set_domain( 'eb-textdomain' );

		$this->loader->eb_add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}



	/**
	 * DEPRECATED FUNCTION
	 * Get User Manager class.
	 *
	 * @since    1.0.0
	 * @deprecated since 2.0.1 use user_manager() insted.
	 * @return EBUserManager
	 */
	public function userManager() {
		return EBUserManager::instance( $this->get_plugin_name(), $this->get_version() );
	}


	/**
	 * Get User Manager class.
	 *
	 * @since    1.0.0
	 *
	 * @return EBUserManager
	 */
	public function user_manager() {
		return EBUserManager::instance( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * DEPRECATED FUNCTION
	 *
	 * Get Course Manager class.
	 *
	 * @since    1.0.0
	 *
	 * @deprecated since 2.0.1 use course_manager() insted.
	 * @return Eb_Course_Manager
	 */
	public function courseManager() {
		return Eb_Course_Manager::instance( $this->get_plugin_name(), $this->get_version() );
	}


	/**
	 * Get Course Manager class.
	 *
	 * @since    1.0.0
	 *
	 * @return Eb_Course_Manager
	 */
	public function course_manager() {
		return Eb_Course_Manager::instance( $this->get_plugin_name(), $this->get_version() );
	}


	/**
	 * DEPRECATED FUNCTION
	 *
	 * Get Enrollment Manager class.
	 *
	 * @since    1.0.0
	 *
	 * @deprecated since 2.0.1 use enrollment_manager() insted.
	 * @return Eb_Enrollment_Manager
	 */
	public function enrollmentManager() {
		return Eb_Enrollment_Manager::instance( $this->get_plugin_name(), $this->get_version() );
	}


	/**
	 * Get Enrollment Manager class.
	 *
	 * @since    1.0.0
	 *
	 * @return Eb_Enrollment_Manager
	 */
	public function enrollment_manager() {
		return Eb_Enrollment_Manager::instance( $this->get_plugin_name(), $this->get_version() );
	}



	/**
	 * DEPREACATED FUNCTION
	 *
	 * Get Order Manager class.
	 *
	 * @since    1.0.0
	 *
	 * @deprecated since 2.0.1 use order_manager() insted.
	 * @return Eb_Order_Manager
	 */
	public function orderManager() {
		return Eb_Order_Manager::instance( $this->get_plugin_name(), $this->get_version() );
	}


	/**
	 * Get Order Manager class.
	 *
	 * @since    1.0.0
	 *
	 * @return Eb_Order_Manager
	 */
	public function order_manager() {
		return Eb_Order_Manager::instance( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * DEPRECATED FUNCTION
	 *
	 * Get Connection Helper class.
	 *
	 * @since    1.0.0
	 *
	 * @deprecated since 2.0.1 use connection_helper() insted.
	 * @return EBConnectionHelper
	 */
	public function connectionHelper() {
		return EBConnectionHelper::instance( $this->get_plugin_name(), $this->get_version() );
	}



	/**
	 * Get Connection Helper class.
	 *
	 * @since    1.0.0
	 *
	 * @return EBConnectionHelper
	 */
	public function connection_helper() {
		return EBConnectionHelper::instance( $this->get_plugin_name(), $this->get_version() );
	}


	/**
	 * Get Logger class.
	 *
	 * @since    1.0.0
	 *
	 * @return Eb_Logger
	 */
	public function logger() {
		return Eb_Logger::instance( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Register all plugin hooks.
	 *
	 * @since   1.0.0
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
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Eb_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->eb_add_action( 'admin_enqueue_scripts', $plugin_admin, 'admin_enqueue_styles' );
		$this->loader->eb_add_action( 'admin_enqueue_scripts', $plugin_admin, 'admin_enqueue_scripts' );

		/**
		 * Add action to add the meta boxes in backend for the order
		 */
		$order_meta            = new Eb_Order_Meta( $this->plugin_name, $this->version );
		$save_order_meta       = new Eb_Order_Status( $this->plugin_name, $this->version );
		$paypal_refund_manager = new Eb_Paypal_Refund_Manager( $this->plugin_name, $this->version );

		$this->loader->eb_add_action(
			'eb_order_refund_init',
			$paypal_refund_manager,
			'refund',
			10,
			5
		);

		$this->loader->eb_add_action(
			'add_meta_boxes',
			$order_meta,
			'add_eb_order_meta_boxes'
		);
		$this->loader->eb_add_action(
			'save_post_eb_order',
			$save_order_meta,
			'save_status_update_meta',
			05
		);
		$this->loader->eb_add_action(
			'eb_order_created',
			$save_order_meta,
			'save_new_order_place_note'
		);

		// Nonce is already added.
		$this->loader->eb_add_action(
			'wp_ajax_wdm_eb_order_refund',
			$save_order_meta,
			'init_eb_order_refund'
		);

		$admin_notice_handler = new Eb_Admin_Notice_Handler();

		$this->loader->eb_add_action(
			'admin_notices',
			$admin_notice_handler,
			'eb_admin_update_moodle_plugin_notice'
		);

		$this->loader->eb_add_action(
			'admin_init',
			$admin_notice_handler,
			'eb_admin_update_notice_dismiss_handler'
		);

		$this->loader->eb_add_action(
			'admin_init',
			$admin_notice_handler,
			'eb_admin_notice_dismiss_handler'
		);

		$hook = 'in_plugin_update_message-edwiser-bridge/edwiser-bridge.php';
		$this->loader->eb_add_action(
			$hook,
			$admin_notice_handler,
			'eb_show_inline_plugin_update_notification',
			10,
			2
		);

		/*
		 * Handling custom button events on settings page
		 * Responsible for initiating ajax requests made by custom buttons placed in settings pages.
		 * Specifically 'Synchronization Request' & 'Test Connection Request' on Moodle settings page.
		 */
		$admin_settings_init = new Eb_Settings_Ajax_Initiater( $this->get_plugin_name(), $this->get_version() );

		/**
		 * Email template editor ajax start
		 */
		$email_tmpl_editor = new EBAdminEmailTemplate();
		$manage_enrollment = new Eb_Manage_User_Enrollment( $this->plugin_name, $this->version );

		$this->loader->eb_add_action(
			'wp_ajax_wdm_eb_get_email_template',
			$email_tmpl_editor,
			'get_template_data_ajax_call_back'
		);
		$this->loader->eb_add_action(
			'wp_ajax_nopriv_wdm_eb_get_email_template',
			$email_tmpl_editor,
			'get_template_data_ajax_call_back'
		);
		$this->loader->eb_add_action(
			'wp_ajax_wdm_eb_send_test_email',
			$email_tmpl_editor,
			'send_test_email'
		);
		$this->loader->eb_add_action(
			'wp_ajax_nopriv_wdm_eb_send_test_email',
			$email_tmpl_editor,
			'send_test_email'
		);
		$this->loader->eb_add_action(
			'wp_ajax_nopriv_wdm_eb_user_manage_unenroll_unenroll_user',
			$email_tmpl_editor,
			'unenroll_user_ajax_handler'
		);
		$this->loader->eb_add_action(
			'wp_ajax_nopriv_wdm_eb_email_tmpl_restore_content',
			$email_tmpl_editor,
			'reset_email_template_content'
		);

		$this->loader->eb_add_action(
			'wp_ajax_handleCourseSynchronization',
			$admin_settings_init,
			'course_synchronization_initiater'
		);
		$this->loader->eb_add_action(
			'wp_ajax_handleUserCourseSynchronization',
			$admin_settings_init,
			'user_data_synchronization_initiater'
		);
		$this->loader->eb_add_action(
			'wp_ajax_handleUserLinkToMoodle',
			$admin_settings_init,
			'users_link_to_moodle_synchronization'
		);
		$this->loader->eb_add_action(
			'wp_ajax_handleConnectionTest',
			$admin_settings_init,
			'connection_test_initiater'
		);
		$this->loader->eb_add_action(
			'wp_ajax_wdm_eb_user_manage_unenroll_unenroll_user',
			$manage_enrollment,
			'unenroll_user_ajax_handler'
		);
		$this->loader->eb_add_action(
			'wp_ajax_wdm_eb_email_tmpl_restore_content',
			$email_tmpl_editor,
			'reset_email_template_content'
		);

		$gdpr_compatible = new Eb_Gdpr_Compatibility();
		/**
		 * Used to add eb personal while exporting personal data.
		 *
		 *@since  1.3.2
		 */
		$this->loader->eb_add_action(
			'wp_privacy_personal_data_exporters',
			$gdpr_compatible,
			'eb_register_my_plugin_exporter'
		);

		/**
		 * Used to add eb personal while exporting personal data
		 *
		 *@since  1.3.2
		 */
		$this->loader->eb_add_action(
			'wp_privacy_personal_data_erasers',
			$gdpr_compatible,
			'eb_register_plugin_eraser'
		);

		$this->loader->eb_add_action(
			'admin_init',
			$gdpr_compatible,
			'eb_privacy_policy_page_data'
		);
	}

	/**
	 * Register all of the hooks related to the user profile & actions related with user.
	 *
	 * @since    1.0.0
	 */
	private function define_user_hooks() {

		// display bulk action to unlink moodle account
		// On users page in dashboard.
		$this->loader->eb_add_action(
			'admin_print_scripts',
			$this->user_manager(),
			'link_user_bulk_actions',
			100
		); // add unlink action.
		$this->loader->eb_add_action(
			'load-users.php',
			$this->user_manager(),
			'link_user_bulk_actions_handler'
		); // handle unlink action.
		$this->loader->eb_add_action(
			'admin_notices',
			$this->user_manager(),
			'link_user_bulk_actions_notices'
		); // display unlink notices.

		// display enroll to course dropdown in user profile.
		$this->loader->eb_add_action(
			'show_user_profile',
			$this->user_manager(),
			'display_users_enrolled_courses'
		);
		$this->loader->eb_add_action(
			'edit_user_profile',
			$this->user_manager(),
			'display_users_enrolled_courses'
		);

		// enroll user to course on user profile update.
		$this->loader->eb_add_action(
			'personal_options_update',
			$this->user_manager(),
			'update_courses_on_profile_update'
		);
		$this->loader->eb_add_action(
			'edit_user_profile_update',
			$this->user_manager(),
			'update_courses_on_profile_update'
		);

		$this->loader->eb_add_action(
			'wp_ajax_moodleLinkUnlinkUser',
			$this->user_manager(),
			'moodle_link_unlink_user'
		);

		$this->loader->eb_add_action(
			'admin_notices',
			$this->user_manager(),
			'moodle_link_unlink_user_notices'
		);

		// password sync with moodle on profile update & password reset.
		$this->loader->eb_add_action( 'profile_update', $this->user_manager(), 'password_update', 10, 2 );
		$this->loader->eb_add_action( 'password_reset', $this->user_manager(), 'password_reset', 10, 2 );

		/*
		 * In case a user is permanentaly deleted from WordPress,
		 * update course enrollment table appropriately by deleting records for user being deleted.
		 */
		$this->loader->eb_add_action( 'delete_user', $this->user_manager(), 'delete_enrollment_records_on_user_deletion' );

		$this->loader->eb_add_action( 'eb_before_single_course', $this->user_manager(), 'unenroll_on_course_access_expire' );
	}

	/**
	 * Register all of the hooks related to the internal functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_system_hooks() {
		// Registers core post types, taxonomies and metaboxes.
		$plugin_post_types = new Eb_Post_Types( $this->get_plugin_name(), $this->get_version() );

		$api_end_point_handler = new Eb_External_Api_Endpoint();
		$this->loader->eb_add_action( 'rest_api_init', $api_end_point_handler, 'api_registration' );

		/*
		* Usage Tracking hook.
		*/
		$usage_tracking = new EB_Usage_Tracking();
		$this->loader->eb_add_action( 'admin_init', $usage_tracking, 'usage_tracking_cron' );
		$this->loader->eb_add_action( 'eb_monthly_usage_tracking', $usage_tracking, 'send_usage_analytics' );

		$this->loader->eb_add_action( 'init', $plugin_post_types, 'register_taxonomies' );
		$this->loader->eb_add_action( 'init', $plugin_post_types, 'register_post_types' );
		$this->loader->eb_add_filter(
			'post_updated_messages',
			$plugin_post_types,
			'custom_post_type_update_messages'
		); // change post updated messages.
		$this->loader->eb_add_action(
			'add_meta_boxes',
			$plugin_post_types,
			'register_meta_boxes'
		);

		$this->loader->eb_add_action(
			'save_post',
			$plugin_post_types,
			'handle_post_options_save',
			15
		); // this hook must be run after update_order_status() function added below.

		// hooks related to order manager class
		// should be called before handle_post_options_save() function
		// to determine previous order status on order status change.
		$this->loader->eb_add_action(
			'save_post_eb_order',
			$this->order_manager(),
			'update_order_status_on_order_save',
			10
		);
		$this->loader->eb_add_action(
			'eb_order_status_completed',
			$this->order_manager(),
			'enroll_to_course_on_order_complete',
			10
		);
		$this->loader->eb_add_action(
			'wp_ajax_createNewOrderAjaxWrapper',
			$this->order_manager(),
			'create_new_order_ajax_wrapper',
			10
		);
		$this->loader->eb_add_action(
			'manage_eb_order_posts_columns',
			$this->order_manager(),
			'add_order_status_column',
			10,
			1
		);
		$this->loader->eb_add_action(
			'manage_eb_order_posts_custom_column',
			$this->order_manager(),
			'add_order_status_column_content',
			10,
			2
		);

		// hooks related to payment management.
		$payment_mgr = new Eb_Payment_Manager( $this->get_plugin_name(), $this->get_version() );
		$this->loader->eb_add_action( 'generate_rewrite_rules', $payment_mgr, 'paypal_rewrite_rules' );
		$this->loader->eb_add_filter( 'query_vars', $payment_mgr, 'add_query_vars' );
		$this->loader->eb_add_action( 'parse_request', $payment_mgr, 'parse_ipn_request' );

		/*
		 * In case a course is permanentaly deleted from moodle course list,
		 * update course enrollment table appropriately by deleting records for course being deleted.
		 */
		$this->loader->eb_add_action(
			'before_delete_post',
			$this->course_manager(),
			'delete_enrollment_records_on_course_deletion'
		);
		$this->loader->eb_add_action(
			'manage_eb_course_posts_columns',
			$this->course_manager(),
			'add_course_price_type_column',
			10,
			1
		);
		$this->loader->eb_add_action(
			'manage_eb_course_posts_custom_column',
			$this->course_manager(),
			'add_course_price_type_column_content',
			10,
			2
		);
		$this->loader->eb_add_action(
			'post_row_actions',
			$this->course_manager(),
			'view_moodle_course_link',
			10,
			2
		);

		// handles addtion of new blog.

		$this->loader->eb_add_action(
			'wpmu_new_blog',
			'app\wisdmlabs\edwiserBridge\Eb_Activator',
			'handle_new_blog',
			10,
			1
		);

		// wp_remote_post() has default timeout set as 5 seconds, increase it to remove timeout problem.
		$this->loader->eb_add_filter( 'http_request_timeout', $this->connection_helper(), 'connection_timeout_extender' );

		// Adding theme compatibility hooks here.
		$theme_compatibility = new Eb_Theme_Compatibility();
		$this->loader->eb_add_action(
			'eb_archive_before_content',
			$theme_compatibility,
			'eb_content_start_theme_compatibility',
			10,
			2
		);

		$this->loader->eb_add_action(
			'eb_archive_after_content',
			$theme_compatibility,
			'eb_content_end_theme_compatibility',
			10,
			2
		);

		$this->loader->eb_add_action(
			'eb_before_single_course',
			$theme_compatibility,
			'eb_content_start_theme_compatibility',
			10,
			2
		);

		$this->loader->eb_add_action(
			'eb_after_single_course',
			$theme_compatibility,
			'eb_content_end_theme_compatibility',
			10,
			2
		);

		$this->loader->eb_add_action(
			'eb_archive_before_sidebar',
			$theme_compatibility,
			'eb_sidebar_start_theme_compatibility',
			10,
			2
		);

		$this->loader->eb_add_action(
			'eb_archive_after_sidebar',
			$theme_compatibility,
			'eb_sidebar_end_theme_compatibility',
			10,
			2
		);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_public_hooks() {
		$plugin_public   = new Eb_Public( $this->get_plugin_name(), $this->get_version() );
		$template_loader = new EbTemplateLoader( $this->get_plugin_name(), $this->get_version() );

		$this->loader->eb_add_action( 'wp_enqueue_scripts', $plugin_public, 'public_enqueue_styles' );
		$this->loader->eb_add_action( 'wp_enqueue_scripts', $plugin_public, 'public_enqueue_scripts' );

		// Template loader hooks.
		$this->loader->eb_add_filter( 'template_include', $template_loader, 'template_loader', 10 );

		// Initiate our shortcodes class on init hook.
		$this->loader->eb_add_action( 'init', '\app\wisdmlabs\edwiserBridge\Eb_Shortcodes', 'init' );

		// Frontend form handler hooks to handle user login & registration.
		$this->loader->eb_add_action( 'wp_loaded', '\app\wisdmlabs\edwiserBridge\Eb_Frontend_Form_Handler', 'process_login', 20 );
		$this->loader->eb_add_action( 'wp_loaded', '\app\wisdmlabs\edwiserBridge\Eb_Frontend_Form_Handler', 'process_registration', 20 );
		// process course join request for free courses.
		$this->loader->eb_add_action( 'wp_loaded', '\app\wisdmlabs\edwiserBridge\Eb_Frontend_Form_Handler', 'process_free_course_join_request' );

		$this->loader->eb_add_action( 'after_setup_theme', $plugin_public, 'after_setup_theme' );
		add_action( 'template_redirect', array( '\app\wisdmlabs\edwiserBridge\Eb_Shortcode_User_Account', 'save_account_details' ) );
	}

	/**
	 * Register all of the hooks related to sending emails
	 * of various activities.
	 *
	 * @since    1.0.0
	 */
	private function define_email_hooks() {
		$plugin_emailer = new Eb_Emailer( $this->get_plugin_name(), $this->get_version() );

		// send emails on various system events as specified.
		$this->loader->eb_add_action(
			'eb_email_header',
			$plugin_emailer,
			'get_email_header',
			10,
			1
		); // Get email header template.
		$this->loader->eb_add_action(
			'eb_email_footer',
			$plugin_emailer,
			'get_email_footer',
			10
		); // Get email footer template.
		$this->loader->eb_add_action(
			'eb_created_user',
			$plugin_emailer,
			'send_new_user_email',
			10
		); // email on new user registration.
		$this->loader->eb_add_action(
			'eb_linked_to_existing_wordpress_user',
			$plugin_emailer,
			'send_existing_user_moodle_account_email',
			10
		); // email on moodle user link to existing WordPress user.
		$this->loader->eb_add_action(
			'eb_linked_to_existing_wordpress_to_new_user',
			$plugin_emailer,
			'send_existing_wp_user_new_moodle_account_email',
			10
		); // email on moodle user link to existing WordPress user.
		$this->loader->eb_add_action(
			'eb_order_status_completed',
			$plugin_emailer,
			'send_order_completion_email',
			10
		); // email on order status completed.
		$this->loader->eb_add_action(
			'eb_course_access_expire_alert',
			$plugin_emailer,
			'send_course_access_expire_email',
			10
		); // email on order status completed.

		$this->loader->eb_add_action(
			'eb_refund_completion',
			$plugin_emailer,
			'refund_completion_email',
			10
		); // email on successful refund.

		/********  Two way synch  */

		$this->loader->eb_add_action(
			'eb_mdl_enrollment_trigger',
			$plugin_emailer,
			'send_mdl_triggered_enrollment_email',
			10
		); // email on trigger of the Moodle course enrollment.

		$this->loader->eb_add_action(
			'eb_mdl_un_enrollment_trigger',
			$plugin_emailer,
			'send_mdl_triggered_unenrollment_email',
			10
		); // email on trigger of the Moodle course Un enrollment.

		$this->loader->eb_add_action(
			'eb_mdl_user_deletion_trigger',
			$plugin_emailer,
			'send_mdl_triggered_user_deletion_email',
			10
		); // email on trigger of the Moodle User Deletion.

		// Hook to get bcc field set for the email.
		$this->loader->eb_add_action(
			'eb_email_custom_args',
			$plugin_emailer,
			'set_bcc_field_in_email_header',
			10,
			2
		);
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
	 * DEPRECATED FUNCTION
	 *
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 *
	 * @deprecated since 2.0.1 use get_plugin_name() insted.
	 * @return string The name of the plugin.
	 */
	public function getPluginName() {
		return $this->plugin_name;
	}


	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}


	/**
	 * NOT USED FUNCTION
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 *
	 * @deprecated since 2.0.1 use get_loader() insted.
	 * @return Eb_Loader Orchestrates the hooks of the plugin.
	 */
	public function getLoader() {
		return $this->loader;
	}


	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 *
	 * @return Eb_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}



	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 *
	 * @deprecated since 2.0.1 use get_version() insted.
	 * @return string The version number of the plugin.
	 */
	public function getVersion() {
		return $this->version;
	}




	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 *
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
 * @deprecated since 2.0.1 use edwiser_bridge_instance() insted.
 * @return EDW
 */
function edwiserBridgeInstance() {
	return EdwiserBridge::instance();
}

/**
 * Returns the main instance of EDW to prevent the need to use globals.
 *
 * @since  1.0.0
 *
 * @return EDW
 */
function edwiser_bridge_instance() {
	return EdwiserBridge::instance();
}
