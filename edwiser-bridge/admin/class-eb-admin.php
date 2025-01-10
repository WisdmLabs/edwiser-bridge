<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
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
 * Eb Admin.
 */
class Eb_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_enqueue_styles() {
		/*
		 * An instance of this class should be passed to the run() function
		 * defined in EB_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The EB_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		wp_enqueue_style( 'dashicons' );

		wp_enqueue_style(
			$this->plugin_name . '_font_awesome',
			$eb_plugin_url . 'public/assets/css/font-awesome-4.4.0/css/font-awesome.min.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_style(
			$this->plugin_name,
			$eb_plugin_url . 'admin/assets/css/eb-admin.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_style(
			'jquery-tiptip-css',
			$eb_plugin_url . 'admin/assets/css/tipTip.css',
			array(),
			$this->version,
			'all'
		);
		wp_enqueue_style(
			'eb-select2-css',
			$eb_plugin_url . 'admin/assets/css/select2.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_style(
			'eb-jquery-ui-css',
			$eb_plugin_url . 'admin/assets/css/jquery-ui.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Eb_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();
		$sync_nonce    = wp_create_nonce( 'check_sync_action' );
		$admin_nonce   = wp_create_nonce( 'eb_admin_nonce' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );

		wp_enqueue_script( 'iris' );

		wp_enqueue_script(
			$this->plugin_name,
			$eb_plugin_url . 'admin/assets/js/eb-admin.js',
			array( 'jquery', 'jquery-ui-dialog', 'jquery-ui-accordion', 'iris' ),
			$this->version,
			false
		);

		wp_enqueue_script(
			'eb-select2-js',
			$eb_plugin_url . 'admin/assets/js/select2.full.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		/*
		 * translators: Link to open the unlink users list.
		 */
		$msg_unlink_user = sprintf( esc_html__( '%s to see list of unlinked users.', 'edwiser-bridge' ), '<a href="#">' . esc_html__( '  Click', 'edwiser-bridge' ) . '</a>' );
		wp_localize_script(
			$this->plugin_name,
			'eb_admin_js_object',
			array(
				'unsaved_warning'                 => esc_html__( 'Please save the changes.', 'edwiser-bridge' ),
				'plugin_url'                      => $eb_plugin_url,
				'ajaxurl'                         => admin_url( 'admin-ajax.php' ),
				'nonce'                           => $sync_nonce,
				'admin_nonce'                     => $admin_nonce,
				'msg_con_success'                 => esc_html__( 'Connection successful, Please save your connection details.', 'edwiser-bridge' ),
				'msg_courses_sync_success'        => esc_html__( 'Courses synchronized successfully.', 'edwiser-bridge' ),
				'msg_con_prob'                    => esc_html__( 'There is a problem while connecting to moodle server.', 'edwiser-bridge' ),
				'msg_err_users'                   => esc_html__( 'Error occurred for following users: ', 'edwiser-bridge' ),
				'msg_user_sync_success'           => esc_html__( 'User\'s course enrollment status synced successfully.', 'edwiser-bridge' ),
				'msg_unlink_users_list'           => $msg_unlink_user,
				'msg_user_link_to_moodle_success' => esc_html__( 'User\'s linked to moodle successfully.', 'edwiser-bridge' ),
				'msg_mail_delivery_fail'          => esc_html__( 'Mail delivery failed.', 'edwiser-bridge' ),
				'msg_test_mail_sent_to'           => esc_html__( 'Test mail sent to ', 'edwiser-bridge' ),
				'msg_err_parsing_res'             => esc_html__( 'An error occurred during parsing the response', 'edwiser-bridge' ),
				'msg_cat_sync_success'            => esc_html__( 'Categories synchronized successfully.', 'edwiser-bridge' ),
				'msg_tpl_not_found'               => esc_html__( 'Template not found', 'edwiser-bridge' ),
				'msg_link_user'                   => esc_html__( 'Linked ', 'edwiser-bridge' ),
				'msg_unlink_user'                 => esc_html__( 'Unlinked ', 'edwiser-bridge' ),
				'msg_error_unlink_user'           => '<div>' . esc_html__( 'Sorry, unable to link user', 'edwiser-bridge' ) . '<ol><li>' . esc_html__( 'Check if first name and last name of the user is empty.', 'edwiser-bridge' ) . '</li><li>' . esc_html__( 'Please test Moodle connection.', 'edwiser-bridge' ) . '</li></ol>' . esc_html__( 'To know more about this error please', 'edwiser-bridge' ) . "<a target='_blank' href='https://edwiser.helpscoutdocs.com/collection/85-edwiser-bridge-plugin'>" . esc_html__( ' click here', 'edwiser-bridge' ) . '</a></div>',
				'msg_error_link_user'             => '<div>' . esc_html__( 'Sorry, unable to link user', 'edwiser-bridge' ) . '<ol><li>' . esc_html__( 'Check if first name and last name of the user is empty.', 'edwiser-bridge' ) . ' </li><li>' . esc_html__( 'Please test Moodle connection.', 'edwiser-bridge' ) . '</li></ol>' . esc_html__( 'To know more about this error please ', 'edwiser-bridge' ) . "<a target='_blank' href='https://edwiser.helpscoutdocs.com/collection/85-edwiser-bridge-plugin'>" . esc_html__( ' click here', 'edwiser-bridge' ) . '</a></div>',
				'msg_error_moodle_link'           => esc_html__( 'Sorry, unable to link to the moodle', 'edwiser-bridge' ),
				'msg_confirm_refund'              => esc_html__( 'Do you want to refund for the order id: #', 'edwiser-bridge' ),
				'eb_order_refund_nonce'           => wp_create_nonce( 'eb_order_refund_nons_field' ),
				'msg_refund_failed'               => esc_html__( 'Failed to refund the order', 'edwiser-bridge' ),
				'edwiser_terms_title'             => esc_html__( 'Edwiser Terms and Conditions', 'edwiser-bridge' ),
				'edwiser_terms_content'           => esc_html__( 'Edwiser extensions licensing system used to provide the latest stable code of the product as well as to check the renewals for this license at our end. For this purpose, we acquire the details like <b> " Site Name, IP Address " </b>and once the license gets deactivated or expires we won\'t get this information from your site. We need this information for giving you a seamless experience of selling Moodle courses through WordPress. Do hit the <b> "Agree" </b> button if you are ready to share these details with us. ', 'edwiser-bridge' ),
				'testing_enrollment_process'      => esc_html__( 'Testing enrollment process for course ', 'edwiser-bridge' ),
				'checking_mandatory_settings'     => esc_html__( 'Checking mandatory settings', 'edwiser-bridge' ),
				'checking_course_options'         => esc_html__( 'Checking course options', 'edwiser-bridge' ),
				'checking_manual_enrollment'      => esc_html__( 'Checking manual enrollment', 'edwiser-bridge' ),
				'creating_dummy_user'             => esc_html__( 'Creating dummy user', 'edwiser-bridge' ),
				'enrolling_user'                  => esc_html__( 'Enrolling dummy user in the course', 'edwiser-bridge' ),
				'updating_mandatory_sett  ings'   => esc_html__( 'Updating mandatory settings', 'edwiser-bridge' ),
				'enabling_manual_enrollment'      => esc_html__( 'Enabling manual enrollment', 'edwiser-bridge' ),
				'please_select_course'            => esc_html__( 'Please select a course', 'edwiser-bridge' ),
				'button_unlink_user'              => esc_html__( 'Link Moodle Account', 'edwiser-bridge' ),
				'button_link_user'                => esc_html__( 'Unlink Moodle Account', 'edwiser-bridge' ),
				'new_enrollment'                  => esc_html__( 'Enroll New Student', 'edwiser-bridge' ),
				'student_name'                    => esc_html__( 'Student Name', 'edwiser-bridge' ),
				'enroll_courses'                  => esc_html__( 'Courses', 'edwiser-bridge' ),
				'enroll_cancel'                   => esc_html__( 'Cancel', 'edwiser-bridge' ),
				'enroll_enroll'                   => esc_html__( 'Enroll Student', 'edwiser-bridge' ),
				'enroll_courses_placeholder'      => esc_html__( 'Select courses to enroll', 'edwiser-bridge' ),
				'enroll_user_placeholder'         => esc_html__( 'Select user to enroll', 'edwiser-bridge' ),
				'token_validation'				  => esc_html__( 'Are there any spaces before/after the token?', 'edwiser-bridge' ),
				'json_valid'					  => esc_html__( 'Does API return a valid JSON response?', 'edwiser-bridge' ),
				'permalink_setting'				  => esc_html__( 'Are permalink settings correctly configured and rest api accessible?', 'edwiser-bridge' ),
				'get_endpoint'					  => esc_html__( 'Can moodle read data from WordPress(GET endpoint)?', 'edwiser-bridge' ),
				'post_endpoint' 				  => esc_html__( 'Can moodle write data to WordPress(POST endpoint)?', 'edwiser-bridge' ),
				'same_token'  					  => esc_html__( 'Is there a token mismatch between WordPress and Moodle sites?', 'edwiser-bridge' ),
				'server_blocking_check'           => esc_html__( 'Is the moodle site webservice accessible?', 'edwiser-bridge' ),
				'contact_support'				  => esc_html__( 'Invalid response from server. Please contact plugin support', 'edwiser-bridge' ),
				'contact_hosting'				  => esc_html__( 'The plugin is receiving an invalid response code from Moodle website or is unable to connect. Please contact your hosting provider.', 'edwiser-bridge' ),
				'turn_off_debug_log'			  => sprintf( esc_html__( 'Please turn off debug display(WP_DEBUG & WP_DEBUG_DISPLAY) in wp-config.php and disable debug mode on Moodle website as well to fix this issue. Click %s here %s to learn more.', 'edwiser-bridge' ), '<a href="https://edwiser.helpscoutdocs.com/article/575-disabling-debugging-in-wordpress-and-moodle" target="_blank">', '</a>' ),
				'token_mismatch'				  => esc_html__( 'Token added does not match the token configured on the moodle site.', 'edwiser-bridge' ),
				'not_authorized' 				  => esc_html__( 'The user(s) associated with the token creation in Moodle are either not included in the web service\'s authorized users list or lack the required site administrator or manager roles. Consequently, their access is limited, which may result in issues with data synchronization.', 'edwiser-bridge' ),
				'please_refresh'			  	  => esc_html__( 'Please refresh the page and check again. If the issue is still not resolved please contact support.', 'edwiser-bridge' ),
				'wp_version_issue'  			  => esc_html__( 'Your WordPress version is not supported. Please upgrade to the latest version.', 'edwiser-bridge' ),
				'rest_disable_issue'			  => esc_html__( 'The REST API is disabled by either a Security plugin or some other plugin using hooks. It might also have been disabled in your server configuration. Please disable any security plugins and search for conflicts. If the issue doesnt get resolved contact the hosting provider to confirm that server configuration is not causing any issues.', 'edwiser-bridge' ),
				'permalink_setting_issue'		  => sprintf( esc_html__( 'Please change your permalink settings manually to Post Name by navigating in Settings > %s Permalink Settings %s and check again.', 'edwiser-bridge' ), '<a href="/wp-admin/options-permalink.php" target="_blank">', '</a>' ),
				'htaccess_file_missing'			  => esc_html__( 'The .htaccess file is missing. Please click Fix now link shown to create the file.', 'edwiser-bridge' ),
				'htaccess_rule_missing'		      => esc_html__( 'The .htaccess file is missing the required rewrite rule. Please click Fix now link shown to add the rule.', 'edwiser-bridge' ),
				'htaccess_rule_instructions'	  => esc_html__( 'Please add the following rule to the .htaccess file located in the root of your website or create the file to add the rules. "# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress". Likewise, you may also contact your hosting provider to help you with the same.', 'edwiser-bridge' ),
				'contact_support_misc'		  	  => esc_html__( 'Please contact our support team to check the issue.', 'edwiser-bridge' ),
				'contact_support_get'			  => esc_html__( 'The GET endpoint seems to be missing please contact our support team to check the issue.', 'edwiser-bridge' ),
				'contact_support_post'			  => esc_html__( 'The POST endpoint seems to be missing please contact our support team to check the issue.', 'edwiser-bridge' ),
				'check_mdl_config'				  => esc_html__( 'Please check the moodle configuration and make sure the webservice is enabled and the user has the required permissions.', 'edwiser-bridge' ),
				'running_diagnostics'			  => esc_html__( 'Running Diagnostics', 'edwiser-bridge' ),
				'diagnostics_completed'           => esc_html__( 'Diagnostics Completed', 'edwiser-bridge' ),
				'eb_fix_now'					  => esc_html__( 'Fix Now', 'edwiser-bridge' ),
				'get_more_details'				  => esc_html__( 'Get More Details', 'edwiser-bridge' ),
			)
		);

		wp_enqueue_script(
			'jquery-tiptip-js',
			$eb_plugin_url . 'admin/assets/js/jquery.tipTip.minified.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		// add helpscout beacon script.
		add_action( 'eb_settings_footer', '\app\wisdmlabs\edwiserBridge\add_beacon_helpscout_script' );

		add_action( 'eb_settings_header', '\app\wisdmlabs\edwiserBridge\add_edwiser_header_content' );
	}

	/**
	 * Delete old logs
	 */
	public function delete_old_logs(){
		$log_files = array(
			'user',
			'course',
			'order',
			'product',
		);
		
		$log_folder = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_log_dir();

		foreach ( $log_files as $log_file ) {
			$log_file_path = trailingslashit( $log_folder ) . $log_file . '-' . sanitize_file_name( wp_hash( $log_file ) ) . '.log';
			if ( file_exists( $log_file_path ) ) {
				unlink( $log_file_path );
			}
		}
		
		// check if files older than one month are present.
		$log_files = glob( $log_folder . '*.log' );
		if ( $log_files ) {
			foreach ( $log_files as $log_file ) {
				$file_name = basename( $log_file );
				$m = (int)explode( '-', $file_name )[1];
				$y = (int)explode( '-', $file_name )[2];
				if ( $m <= date_i18n( 'm' ) - 2 || $y < date_i18n( 'y' ) ) {
					error_log( 'deleting file ' . $log_file );
					unlink( $log_file );
				}
			}
		}

	}
}
