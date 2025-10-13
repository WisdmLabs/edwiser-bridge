<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
/**
 * Activator.
 */
class Eb_Activator
{

	/**
	 * Network_wide tells if the plugin was activated for the entire network or just for single site.
	 *
	 * @var string network_wide.
	 * @since    1.1.1
	 */
	private static $network_wide = false;

	/**
	 * Activation function.
	 *
	 * @param string $network_wide network_wide.
	 * @since    1.0.0
	 */
	public static function activate($network_wide)
	{
		/**
		 * Deactivates legacy extensions.
		 */
		self::$network_wide = $network_wide;

		self::deactivate_legacy_extensions();

		// create database tables & Pages.
		self::check_single_or_multi_site();

		// create required files & directories.
		self::create_files();

		// rename translation files.
		require_once WP_PLUGIN_DIR . '/edwiser-bridge/includes/class-eb-i18n.php';
		$plugin_i18n = new Eb_I18n();
		$plugin_i18n->rename_langauge_files();

		// check if moodle plugin update is available.
		require_once WP_PLUGIN_DIR . '/edwiser-bridge/includes/class-eb-admin-notice-handler.php';
		$notice_handler = new Eb_Admin_Notice_Handler();
		$notice_handler->eb_check_mdl_plugin_update();

		// redirect to welcome screen.
		$current_version = get_option('eb_current_version');
		if (! $current_version) {
			set_transient('_eb_activation_redirect', 1, 30);
		}
		set_transient('edwiser_bridge_admin_feedback_notice', 'eb_admin_feedback_notice', 60 * 60 * 24 * 15);

		// eb pro consolidated plugin notice.
		$is_pro          = eb_is_legacy_pro();
		$pro_plugin_path = 'edwiser-bridge-pro/edwiser-bridge-pro.php';
		if ($is_pro && ! is_plugin_active($pro_plugin_path)) {
			delete_option('eb_pro_consolidated_plugin_notice_dismissed');
			set_transient('_eb_pro_consolidated_plugin_notice', 1, 30);
		}
	}

	/**
	 * Deactivates legacy extensions.
	 *
	 * @since 1.1
	 */
	public static function deactivate_legacy_extensions()
	{
		// prepare extensions array.
		$extensions = array(
			'selective_sync'          => array('selective-synchronization/selective-synchronization.php', '1.0.0'),
			'woocommerce_integration' => array('woocommerce-integration/bridge-woocommerce.php', '1.0.4'),
			'single_signon'           => array(
				'edwiser-bridge-sso/sso.php',
				'1.0.0',
			),
		);

		// deactive legacy extensions.
		foreach ($extensions as $extension) {
			if (is_plugin_active($extension[0])) {
				$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $extension[0]);

				if (isset($plugin_data['Version']) && version_compare($plugin_data['Version'], $extension[1]) <= 0) {
					deactivate_plugins($extension[0]);
				}
			}
		}
	}

	/**
	 * Checks if the plugin is activated on a SIngle site or Network wide.
	 *
	 * @since    1.1.1
	 */
	public static function check_single_or_multi_site()
	{
		global $wpdb;

		if (is_multisite()) {

			if (self::$network_wide) {
				$all_sites = get_sites();

				foreach ($all_sites as $blog) {
					$blog_id = is_array($blog) ? $blog['blog_id'] : $blog->blog_id;
					switch_to_blog($blog_id);
					self::create_moodle_db_tables();
					self::create_pages();
					self::create_default_email_tempaltes();
					restore_current_blog();
				}
			} else {
				switch_to_blog($wpdb->blogid);
				self::create_moodle_db_tables();
				self::create_pages();
				self::create_default_email_tempaltes();
				restore_current_blog();
			}
		} else {
			self::create_moodle_db_tables();
			self::create_pages();
			self::create_default_email_tempaltes();
		}
	}

	/**
	 * Create DB tables
	 *
	 * @since  1.0.0
	 */
	public static function create_moodle_db_tables()
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
		self::alter_table();
	}

	/**
	 * Alter table.
	 */
	public static function alter_table()
	{
		global $wpdb;
		$enrollment_tbl_name = $wpdb->prefix . 'moodle_enrollment';
		$new_col             = array(
			'expire_time' => array(
				'type'    => 'datetime',
				'default' => '0000-00-00 00:00:00',
			),
			'act_cnt'     => array(
				'type'    => 'int(5)',
				'default' => '1',
			),
			'suspended'   => array(
				'type'    => 'int(5)',
				'default' => '0',
			),
		);

		if (! $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}moodle_enrollment` LIKE 'expire_time';")) { // @codingStandardsIgnoreLine
			$wpdb->query("ALTER TABLE {$wpdb->prefix}moodle_enrollment ADD COLUMN (`expire_time` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL);"); // @codingStandardsIgnoreLine
		}

		if (! $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}moodle_enrollment` LIKE 'act_cnt';")) { // @codingStandardsIgnoreLine
			$wpdb->query("ALTER TABLE {$wpdb->prefix}moodle_enrollment ADD COLUMN (`act_cnt` int(5) DEFAULT 1 NOT NULL);"); // @codingStandardsIgnoreLine
		}

		if (! $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}moodle_enrollment` LIKE 'suspended';")) { // @codingStandardsIgnoreLine
			$wpdb->query("ALTER TABLE {$wpdb->prefix}moodle_enrollment ADD COLUMN (`suspended` int(5) DEFAULT 0 NOT NULL);"); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Handles addtion of new blog.
	 *
	 * @param text $blog_id blog_id.
	 * @since  1.1.1
	 */
	public static function handle_new_blog($blog_id)
	{
		switch_to_blog($blog_id);
		self::create_moodle_db_tables();
		self::create_pages();
		restore_current_blog();
	}

	/**
	 * Create files/directories.
	 *
	 * @since  1.0.0
	 */
	private static function create_files()
	{
		// Install files and folders for uploading files and prevent hotlinking.
		$upload_dir = wp_upload_dir();

		$files = array(
			array(
				'base'    => $upload_dir['basedir'] . '/eb-logs/',
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
		);

		foreach ($files as $file) {
			if (wp_mkdir_p($file['base']) && ! file_exists(trailingslashit($file['base']) . $file['file'])) {
				$file_handle = fopen(trailingslashit($file['base']) . $file['file'], 'w'); // @codingStandardsIgnoreLine
				if ($file_handle) {
					fwrite($file_handle, $file['content']); // @codingStandardsIgnoreLine
					fclose($file_handle); // @codingStandardsIgnoreLine
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
	public static function create_pages()
	{
		include_once 'eb-core-functions.php';

		$page_content = \app\wisdmlabs\edwiserBridge\wdm_eb_get_shortcode_page_content();

		$pages = apply_filters(
			'eb_create_default_pages',
			array(
				'thankyou'    => array(
					'name'       => esc_html_x('thank-you-for-purchase', 'Page slug', 'edwiser-bridge'),
					'title'      => esc_html_x('Thank You for Purchase', 'Page title', 'edwiser-bridge'),
					'content'    => esc_html__('Thanks for purchasing the course, your order will be processed shortly.', 'edwiser-bridge'),
					'option_key' => '',
				),
				'useraccount' => array(
					'name'       => esc_html_x('user-account', 'Page slug', 'edwiser-bridge'),
					'title'      => esc_html_x('User Account', 'Page title', 'edwiser-bridge'),
					'content'    => '[' . apply_filters('eb_user_account_shortcode_tag', 'eb_user_account') . ']',
					'option_key' => 'eb_useraccount_page_id',
				),
				'mycourses'   => array(
					'name'       => esc_html_x('eb-my-courses', 'Page slug', 'edwiser-bridge'),
					'title'      => esc_html_x('My Courses', 'Page title', 'edwiser-bridge'),
					'content'    => $page_content['eb_my_courses'],
					'option_key' => 'eb_my_courses_page_id',
				),
				'courses'     => array(
					'name'       => esc_html_x('eb-courses', 'Page slug', 'edwiser-bridge'),
					'title'      => esc_html_x('Courses', 'Page title', 'edwiser-bridge'),
					'content'    => $page_content['eb_courses'],
					'option_key' => 'eb_courses_page_id',
				),
			)
		);

		foreach ($pages as $key => $page) {
			$key;
			\app\wisdmlabs\edwiserBridge\wdm_eb_create_page(esc_sql($page['name']), $page['option_key'], $page['title'], $page['content']);
		}
		self::create_gutenberg_pages();
	}

	public static function create_gutenberg_pages()
	{
		$gutenberg_pages_settings = get_option('eb_gutenberg_pages', array());

		$courses = get_posts(array(
			'post_type'      => 'eb_course',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		));

		if (!empty($courses)) {
			$course_id = $courses[0]->ID;
		} else {
			$course_id = 0;
		}

		$gutenbergpages = apply_filters('eb_create_gutenberg_pages', array(
			'all_courses' => array(
				'name' => 'eb-all-courses',
				'title' => 'All Courses',
				'content' => '<!-- wp:edwiser-bridge/courses {"categories":""} -->
								<div class="wp-block-edwiser-bridge-courses"><div id="eb-courses" data-page-title="Courses" data-hide-title="false" data-hide-filters="false" data-courses-per-page="9" data-categories="" data-group-by-category="false" data-category-per-page="3" data-horizontal-scroll="false"></div></div>
							<!-- /wp:edwiser-bridge/courses -->',
				'option_key' => 'eb_courses_page_id_new',
			),
			'single_course' => array(
				'name' => 'eb-single-course',
				'title' => 'Single Course',
				'content' => '<!-- wp:edwiser-bridge/course-description -->
								<div class="wp-block-edwiser-bridge-course-description"><div id="eb-course-description" data-show-recommended-courses="true"></div></div>
							<!-- /wp:edwiser-bridge/course-description -->',
				'option_key' => 'eb_single_course_page_id_new',
			),
			'user_account' => array(
				'name' => 'user-account-new',
				'title' => 'User Account - New',
				'content' => '<!-- wp:edwiser-bridge/user-account-v2 {"tabLabelsArray":["Dashboard","Profile","Orders","My Courses"],"tabIconsArray":["layout-dashboard","user","package","graduation-cap"],"tabClassnamesArray":["","","",""]} -->
<div class="wp-block-edwiser-bridge-user-account-v2"><div class="eb-user-account-v2__wrapper"><div class="eb-user-account-v2__tabs"><div class="eb-user-account-v2__tabs-title-wrapper"><h3 class="eb-user-account-v2__tabs-title">User Account</h3><button class="eb-user-account-v2__tabs-toggle" data-active="false"><span class="tabs-toggle__menu"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu-icon lucide-menu"><path d="M4 5h16"></path><path d="M4 12h16"></path><path d="M4 19h16"></path></svg></span><span class="tabs-toggle__close"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></span></button></div><div class="eb-user-account-v2__tabs-list" data-visible="false"><div class="eb-user-account-v2__tab active dashboard " role="tab" aria-selected="true" aria-controls="dashboard" data-tab-index="0"><span class="eb-user-account-v2__tab-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg></span><span class="eb-user-account-v2__tab-label">Dashboard</span></div><div class="eb-user-account-v2__tab profile " role="tab" aria-selected="false" aria-controls="profile" data-tab-index="1"><span class="eb-user-account-v2__tab-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"></circle><path d="M20 21a8 8 0 0 0-16 0"></path></svg></span><span class="eb-user-account-v2__tab-label">Profile</span></div><div class="eb-user-account-v2__tab orders " role="tab" aria-selected="false" aria-controls="orders" data-tab-index="2"><span class="eb-user-account-v2__tab-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-icon lucide-package"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg></span><span class="eb-user-account-v2__tab-label">Orders</span></div><div class="eb-user-account-v2__tab my-courses " role="tab" aria-selected="false" aria-controls="my-courses" data-tab-index="3"><span class="eb-user-account-v2__tab-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap-icon lucide-graduation-cap"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"></path><path d="M22 10v6"></path><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path></svg></span><span class="eb-user-account-v2__tab-label">My Courses</span></div></div></div><div class="eb-user-account-v2__tabs-content"><!-- wp:edwiser-bridge/user-account-v2-tab {"tabLabel":"Dashboard"} -->
<div class="wp-block-edwiser-bridge-user-account-v2-tab eb-user-account-v2__tab-panel dashboard" data-tab-index="0" data-tab-name="dashboard"><!-- wp:edwiser-bridge/dashboard -->
<div class="wp-block-edwiser-bridge-dashboard"><div id="eb-dashboard" data-page-title="Dashboard" data-hide-page-title="false"></div></div>
<!-- /wp:edwiser-bridge/dashboard --></div>
<!-- /wp:edwiser-bridge/user-account-v2-tab -->

<!-- wp:edwiser-bridge/user-account-v2-tab {"tabLabel":"Profile","tabIcon":"user","tabIndex":1} -->
<div class="wp-block-edwiser-bridge-user-account-v2-tab eb-user-account-v2__tab-panel profile" data-tab-index="1" data-tab-name="profile"><!-- wp:edwiser-bridge/profile -->
<div class="wp-block-edwiser-bridge-profile"><div id="eb-profile" data-page-title="Profile" data-hide-page-title="false"></div></div>
<!-- /wp:edwiser-bridge/profile --></div>
<!-- /wp:edwiser-bridge/user-account-v2-tab -->

<!-- wp:edwiser-bridge/user-account-v2-tab {"tabLabel":"Orders","tabIcon":"package","tabIndex":2} -->
<div class="wp-block-edwiser-bridge-user-account-v2-tab eb-user-account-v2__tab-panel orders" data-tab-index="2" data-tab-name="orders"><!-- wp:edwiser-bridge/orders -->
<div class="wp-block-edwiser-bridge-orders"><div id="eb-orders-undefined" data-page-title="Orders" data-hide-page-title="false" data-enable-edwiser-orders="true" data-enable-woo-commerce-orders="false" data-default-tab="eb-orders"></div></div>
<!-- /wp:edwiser-bridge/orders --></div>
<!-- /wp:edwiser-bridge/user-account-v2-tab -->

<!-- wp:edwiser-bridge/user-account-v2-tab {"tabLabel":"My Courses","tabIcon":"graduation-cap","tabIndex":3} -->
<div class="wp-block-edwiser-bridge-user-account-v2-tab eb-user-account-v2__tab-panel my-courses" data-tab-index="3" data-tab-name="my-courses"><!-- wp:edwiser-bridge/my-courses -->
<div class="wp-block-edwiser-bridge-my-courses"><div id="eb-my-courses" data-page-title="My Courses" data-recommended-courses-title="Recommended Courses" data-recommended-courses-count="3" data-show-course-progress="true" data-show-recommended-courses="true" data-hide-page-title="false"></div></div>
<!-- /wp:edwiser-bridge/my-courses --></div>
<!-- /wp:edwiser-bridge/user-account-v2-tab --></div></div></div>
<!-- /wp:edwiser-bridge/user-account-v2 -->',
				'option_key' => 'eb_user_account_page_id',
			),
			'my_courses' => array(
				'name' => 'my-courses-new',
				'title' => 'My Courses - New',
				'content' => '<!-- wp:edwiser-bridge/my-courses -->
<div class="wp-block-edwiser-bridge-my-courses"><div id="eb-my-courses" data-page-title="My Courses" data-recommended-courses-title="Recommended Courses" data-recommended-courses-count="3" data-show-course-progress="true" data-show-recommended-courses="true" data-hide-page-title="false"></div></div>
<!-- /wp:edwiser-bridge/my-courses -->',
				'option_key' => 'eb_my_courses_page_id',
			),
		));
		foreach ($gutenbergpages as $key => $page) {
			if (!isset($gutenberg_pages_settings[$key]) || empty($gutenberg_pages_settings[$key])) {
				$page_id = wp_insert_post(array(
					'post_type' => 'page',
					'post_title' => $page['title'],
					'post_content' => $page['content'],
					'post_status' => 'publish',
					'post_name' => $page['name'],
				));
				$gutenberg_pages_settings[$key] = $page_id;

				if ($key === 'single_course') {
					update_post_meta($page_id, 'courseId', $course_id);
				}

				if ($key === 'user_account' || $key === 'my_courses') {
					update_post_meta($page_id, '_eb_page_state', 'Gutenberg');
				}
			}
		}
		$gutenberg_pages_settings['single_course_block_id'] = $course_id;
		update_option('eb_gutenberg_pages', $gutenberg_pages_settings);
	}

	/**
	 * Default email tempalate.
	 */
	public static function create_default_email_tempaltes()
	{
		$default_tmpl = new Eb_Default_Email_Templates();
		self::update_template_data('eb_emailtmpl_create_user', $default_tmpl->new_user_acoount('eb_emailtmpl_create_user'));

		self::update_template_data('eb_emailtmpl_refund_completion_notifier_to_user', $default_tmpl->notify_user_on_order_refund('eb_emailtmpl_refund_completion_notifier_to_user'));
		self::update_template_data('eb_emailtmpl_refund_completion_notifier_to_admin', $default_tmpl->notify_admin_on_order_refund('eb_emailtmpl_refund_completion_notifier_to_admin'));

		self::update_template_data('eb_emailtmpl_linked_existing_wp_user', $default_tmpl->link_wp_moodle_account('eb_emailtmpl_linked_existing_wp_user'));
		self::update_template_data('eb_emailtmpl_linked_existing_wp_new_moodle_user', $default_tmpl->link_new_moodle_account('eb_emailtmpl_linked_existing_wp_new_moodle_user'));
		self::update_template_data('eb_emailtmpl_order_completed', $default_tmpl->order_complete('eb_emailtmpl_order_completed'));
		self::update_template_data('eb_emailtmpl_course_access_expir', $default_tmpl->course_access_expired('eb_emailtmpl_course_access_expir'));

		self::update_template_data('eb_emailtmpl_mdl_enrollment_trigger', $default_tmpl->moodle_enrollment_trigger('eb_emailtmpl_mdl_enrollment_trigger'));
		self::update_template_data('eb_emailtmpl_mdl_un_enrollment_trigger', $default_tmpl->moodle_unenrollment_trigger('eb_emailtmpl_mdl_un_enrollment_trigger'));
		self::update_template_data('eb_emailtmpl_mdl_user_deletion_trigger', $default_tmpl->user_deletion_trigger('eb_emailtmpl_mdl_user_deletion_trigger'));
		self::update_template_data('eb_emailtmpl_new_user_email_verification', $default_tmpl->new_user_email_verification('eb_emailtmpl_new_user_email_verification'));

		self::update_allow_mail_send_data('eb_emailtmpl_refund_completion_notifier_to_user_notify_allow', 'ON');
		self::update_allow_mail_send_data('eb_emailtmpl_refund_completion_notifier_to_admin_notify_allow', 'ON');

		self::update_allow_mail_send_data('eb_emailtmpl_create_user_notify_allow', 'ON');
		self::update_allow_mail_send_data('eb_emailtmpl_linked_existing_wp_user_notify_allow', 'ON');
		self::update_allow_mail_send_data('eb_emailtmpl_linked_existing_wp_new_moodle_user_notify_allow', 'ON');
		self::update_allow_mail_send_data('eb_emailtmpl_order_completed_notify_allow', 'ON');
		self::update_allow_mail_send_data('eb_emailtmpl_course_access_expir_notify_allow', 'ON');

		self::update_allow_mail_send_data('eb_emailtmpl_mdl_enrollment_trigger_notify_allow', 'ON');
		self::update_allow_mail_send_data('eb_emailtmpl_mdl_un_enrollment_trigger_notify_allow', 'ON');
		self::update_allow_mail_send_data('eb_emailtmpl_mdl_user_deletion_trigger_notify_allow', 'ON');
		self::update_allow_mail_send_data('eb_emailtmpl_new_user_email_verification_notify_allow', 'ON');
	}

	/**
	 * Upate template.
	 *
	 * @param text $key key.
	 * @param text $value value.
	 */
	private static function update_template_data($key, $value)
	{
		if (get_option($key) === false) {
			update_option($key, $value);
		}
	}

	/**
	 * Update allow send email data.
	 *
	 * @param text $key key.
	 * @param text $value value.
	 */
	private static function update_allow_mail_send_data($key, $value)
	{
		$data = get_option($key);

		if (false === $data) {
			update_option($key, $value);
		}
	}
}
