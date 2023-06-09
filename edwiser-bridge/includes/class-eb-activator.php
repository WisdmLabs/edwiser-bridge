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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Activator.
 */
class Eb_Activator {

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
	public static function activate( $network_wide ) {
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
		$current_version = get_option( 'eb_current_version' );
		if ( ! $current_version ) {
			set_transient( '_eb_activation_redirect', 1, 30 );
		}
		set_transient( 'edwiser_bridge_admin_feedback_notice', 'eb_admin_feedback_notice', 60 * 60 * 24 * 15 );

		// eb pro consolidated plugin notice.
		$is_pro          = eb_is_legacy_pro();
		$pro_plugin_path = 'edwiser-bridge-pro/edwiser-bridge-pro.php';
		if ( $is_pro && ! is_plugin_active( $pro_plugin_path ) ) {
			delete_option( 'eb_pro_consolidated_plugin_notice_dismissed' );
			set_transient( '_eb_pro_consolidated_plugin_notice', 1, 30 );
		}
	}

	/**
	 * Deactivates legacy extensions.
	 *
	 * @since 1.1
	 */
	public static function deactivate_legacy_extensions() {
		// prepare extensions array.
		$extensions = array(
			'selective_sync'          => array( 'selective-synchronization/selective-synchronization.php', '1.0.0' ),
			'woocommerce_integration' => array( 'woocommerce-integration/bridge-woocommerce.php', '1.0.4' ),
			'single_signon'           => array(
				'edwiser-bridge-sso/sso.php',
				'1.0.0',
			),
		);

		// deactive legacy extensions.
		foreach ( $extensions as $extension ) {
			if ( is_plugin_active( $extension[0] ) ) {
				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $extension[0] );

				if ( isset( $plugin_data['Version'] ) && version_compare( $plugin_data['Version'], $extension[1] ) <= 0 ) {
						deactivate_plugins( $extension[0] );
				}
			}
		}
	}

	/**
	 * Checks if the plugin is activated on a SIngle site or Network wide.
	 *
	 * @since    1.1.1
	 */
	public static function check_single_or_multi_site() {
		global $wpdb;

		if ( is_multisite() ) {

			if ( self::$network_wide ) {
				$all_sites = get_sites();

				foreach ( $all_sites as $blog ) {
					$blog_id = is_array( $blog ) ? $blog['blog_id'] : $blog->blog_id;
					switch_to_blog( $blog_id );
					self::create_moodle_db_tables();
					self::create_pages();
					self::create_default_email_tempaltes();
					restore_current_blog();
				}
			} else {
				switch_to_blog( $wpdb->blogid );
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
	public static function create_moodle_db_tables() {
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
		dbDelta( $enrollment_table );
		self::alter_table();
	}

	/**
	 * Alter table.
	 */
	public static function alter_table() {
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

		if ( ! $wpdb->query( "SHOW COLUMNS FROM `{$wpdb->prefix}moodle_enrollment` LIKE 'expire_time';" ) ) { // @codingStandardsIgnoreLine
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}moodle_enrollment ADD COLUMN (`expire_time` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL);" ); // @codingStandardsIgnoreLine
		}

		if ( ! $wpdb->query( "SHOW COLUMNS FROM `{$wpdb->prefix}moodle_enrollment` LIKE 'act_cnt';" ) ) { // @codingStandardsIgnoreLine
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}moodle_enrollment ADD COLUMN (`act_cnt` int(5) DEFAULT 1 NOT NULL);" ); // @codingStandardsIgnoreLine
		}

		if ( ! $wpdb->query( "SHOW COLUMNS FROM `{$wpdb->prefix}moodle_enrollment` LIKE 'suspended';" ) ) { // @codingStandardsIgnoreLine
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}moodle_enrollment ADD COLUMN (`suspended` int(5) DEFAULT 0 NOT NULL);" ); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Handles addtion of new blog.
	 *
	 * @param text $blog_id blog_id.
	 * @since  1.1.1
	 */
	public static function handle_new_blog( $blog_id ) {
		switch_to_blog( $blog_id );
		self::create_moodle_db_tables();
		self::create_pages();
		restore_current_blog();
	}

	/**
	 * Create files/directories.
	 *
	 * @since  1.0.0
	 */
	private static function create_files() {
		// Install files and folders for uploading files and prevent hotlinking.
		$upload_dir = wp_upload_dir();

		$files = array(
			array(
				'base'    => $upload_dir['basedir'] . '/eb-logs/',
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ); // @codingStandardsIgnoreLine
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] ); // @codingStandardsIgnoreLine
					fclose( $file_handle ); // @codingStandardsIgnoreLine
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
	public static function create_pages() {
		include_once 'eb-core-functions.php';

		$page_content = \app\wisdmlabs\edwiserBridge\wdm_eb_get_shortcode_page_content();

		$pages = apply_filters(
			'eb_create_default_pages',
			array(
				'thankyou'    => array(
					'name'       => esc_html_x( 'thank-you-for-purchase', 'Page slug', 'edwiser-bridge' ),
					'title'      => esc_html_x( 'Thank You for Purchase', 'Page title', 'edwiser-bridge' ),
					'content'    => esc_html__( 'Thanks for purchasing the course, your order will be processed shortly.', 'edwiser-bridge' ),
					'option_key' => '',
				),
				'useraccount' => array(
					'name'       => esc_html_x( 'user-account', 'Page slug', 'edwiser-bridge' ),
					'title'      => esc_html_x( 'User Account', 'Page title', 'edwiser-bridge' ),
					'content'    => '[' . apply_filters( 'eb_user_account_shortcode_tag', 'eb_user_account' ) . ']',
					'option_key' => 'eb_useraccount_page_id',
				),
				'mycourses'   => array(
					'name'       => esc_html_x( 'eb-my-courses', 'Page slug', 'edwiser-bridge' ),
					'title'      => esc_html_x( 'My Courses', 'Page title', 'edwiser-bridge' ),
					'content'    => $page_content['eb_my_courses'],
					'option_key' => 'eb_my_courses_page_id',
				),
				'courses'     => array(
					'name'       => esc_html_x( 'eb-courses', 'Page slug', 'edwiser-bridge' ),
					'title'      => esc_html_x( 'Courses', 'Page title', 'edwiser-bridge' ),
					'content'    => $page_content['eb_courses'],
					'option_key' => 'eb_courses_page_id',
				),
			)
		);

		foreach ( $pages as $key => $page ) {
			$key;
			\app\wisdmlabs\edwiserBridge\wdm_eb_create_page( esc_sql( $page['name'] ), $page['option_key'], $page['title'], $page['content'] );
		}
	}

	/**
	 * Default email tempalate.
	 */
	public static function create_default_email_tempaltes() {
		$default_tmpl = new Eb_Default_Email_Templates();
		self::update_template_data( 'eb_emailtmpl_create_user', $default_tmpl->new_user_acoount( 'eb_emailtmpl_create_user' ) );

		self::update_template_data( 'eb_emailtmpl_refund_completion_notifier_to_user', $default_tmpl->notify_user_on_order_refund( 'eb_emailtmpl_refund_completion_notifier_to_user' ) );
		self::update_template_data( 'eb_emailtmpl_refund_completion_notifier_to_admin', $default_tmpl->notify_admin_on_order_refund( 'eb_emailtmpl_refund_completion_notifier_to_admin' ) );

		self::update_template_data( 'eb_emailtmpl_linked_existing_wp_user', $default_tmpl->link_wp_moodle_account( 'eb_emailtmpl_linked_existing_wp_user' ) );
		self::update_template_data( 'eb_emailtmpl_linked_existing_wp_new_moodle_user', $default_tmpl->link_new_moodle_account( 'eb_emailtmpl_linked_existing_wp_new_moodle_user' ) );
		self::update_template_data( 'eb_emailtmpl_order_completed', $default_tmpl->order_complete( 'eb_emailtmpl_order_completed' ) );
		self::update_template_data( 'eb_emailtmpl_course_access_expir', $default_tmpl->course_access_expired( 'eb_emailtmpl_course_access_expir' ) );

		self::update_template_data( 'eb_emailtmpl_mdl_enrollment_trigger', $default_tmpl->moodle_enrollment_trigger( 'eb_emailtmpl_mdl_enrollment_trigger' ) );
		self::update_template_data( 'eb_emailtmpl_mdl_un_enrollment_trigger', $default_tmpl->moodle_unenrollment_trigger( 'eb_emailtmpl_mdl_un_enrollment_trigger' ) );
		self::update_template_data( 'eb_emailtmpl_mdl_user_deletion_trigger', $default_tmpl->user_deletion_trigger( 'eb_emailtmpl_mdl_user_deletion_trigger' ) );
		self::update_template_data( 'eb_emailtmpl_new_user_email_verification', $default_tmpl->new_user_email_verification( 'eb_emailtmpl_new_user_email_verification' ) );

		self::update_allow_mail_send_data( 'eb_emailtmpl_refund_completion_notifier_to_user_notify_allow', 'ON' );
		self::update_allow_mail_send_data( 'eb_emailtmpl_refund_completion_notifier_to_admin_notify_allow', 'ON' );

		self::update_allow_mail_send_data( 'eb_emailtmpl_create_user_notify_allow', 'ON' );
		self::update_allow_mail_send_data( 'eb_emailtmpl_linked_existing_wp_user_notify_allow', 'ON' );
		self::update_allow_mail_send_data( 'eb_emailtmpl_linked_existing_wp_new_moodle_user_notify_allow', 'ON' );
		self::update_allow_mail_send_data( 'eb_emailtmpl_order_completed_notify_allow', 'ON' );
		self::update_allow_mail_send_data( 'eb_emailtmpl_course_access_expir_notify_allow', 'ON' );

		self::update_allow_mail_send_data( 'eb_emailtmpl_mdl_enrollment_trigger_notify_allow', 'ON' );
		self::update_allow_mail_send_data( 'eb_emailtmpl_mdl_un_enrollment_trigger_notify_allow', 'ON' );
		self::update_allow_mail_send_data( 'eb_emailtmpl_mdl_user_deletion_trigger_notify_allow', 'ON' );
		self::update_allow_mail_send_data( 'eb_emailtmpl_new_user_email_verification_notify_allow', 'ON' );
	}

	/**
	 * Upate template.
	 *
	 * @param text $key key.
	 * @param text $value value.
	 */
	private static function update_template_data( $key, $value ) {
		if ( get_option( $key ) === false ) {
			update_option( $key, $value );
		}
	}

	/**
	 * Update allow send email data.
	 *
	 * @param text $key key.
	 * @param text $value value.
	 */
	private static function update_allow_mail_send_data( $key, $value ) {
		$data = get_option( $key );

		if ( false === $data ) {
			update_option( $key, $value );
		}
	}
}
