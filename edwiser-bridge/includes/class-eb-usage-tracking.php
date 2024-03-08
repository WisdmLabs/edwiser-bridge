<?php
/**
 * Edwiser Usage Tracking
 * We send anonymous user data to imporve our product compatibility with various plugins and systems.
 *
 * Cards Format - A topics based format that uses card layout to diaply the content.
 *
 * @package    format_remuiformat
 * @copyright  (c) 2020 WisdmLabs (https://wisdmlabs.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Usage tracking.
 */
class EB_Usage_Tracking {
	// Action hook for the usage tracking.
	/**
	 * Call this function on the registration Hook.
	 * Functionalitity to call the usage tracking call back on every month
	 */
	public function usage_tracking_cron() {
		if ( ! wp_next_scheduled( 'eb_monthly_usage_tracking' ) ) {
			wp_schedule_event( time(), 'monthly', 'eb_monthly_usage_tracking' );

		}
	}

	/**
	 * Send usage analytics to Edwiser, only anonymous data is sent.
	 *
	 * Every 7 days the data is sent, function runs for admin user only.
	 */
	public function send_usage_analytics() {
		// execute code only if current user is site admin.

		// check consent to send tracking data.
		$eb_general = get_option( 'eb_general' );
		if ( $eb_general ) {
			$consent = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $eb_general, 'eb_usage_tracking', false );
		}

		if ( $consent ) {
			$result_arr = array();

			$analytics_data = wp_json_encode( $this->prepare_usage_analytics() );
			$url            = 'https://edwiser.org/wp-json/edwiser_customizations/send_usage_data';
			$request_args   = array(
				'sslverify' => false,
				'body'      => $analytics_data,
				'timeout'   => 100,
			);
			$result         = wp_remote_post( $url, $request_args );

			if ( 200 === wp_remote_retrieve_response_code( $result ) ) {
				$result_arr = json_decode( wp_remote_retrieve_body( $result ) );
			}
		}
	}

	/**
	 * Prepare usage analytics.
	 */
	private function prepare_usage_analytics() {

		global $wp_version;
		$server_ip = ( isset( $_SERVER['REMOTE_ADDR'] ) && null !== $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		// Suppressing all the errors here, just in case the setting does not exists, to avoid many if statements.
		$analytics_data = array(
			'siteurl'           => $this->detect_site_type() . preg_replace( '#^https?://#', '', rtrim( get_site_url(), '/' ) ), // replace protocol and trailing slash.
			'product_name'      => 'Edwiser Bridge',
			'product_settings'  => $this->get_plugin_settings( 'edwiser_bridge' ), // all settings in json, of current product which you are tracking.
			'active_theme'      => get_option( 'stylesheet' ),
			'total_courses'     => $this->eb_get_course_count(), // Include only with format type remuicourseformat.
			'total_categories'  => $this->eb_get_cat_count(), // includes hidden categories.
			'total_users'       => $this->eb_get_user_count(), // exclude deleted.
			'installed_plugins' => $this->get_user_installed_plugins(), // along with versions.
			'system_version'    => $wp_version, // Moodle version.
			'system_lang'       => get_locale(),
			'system_settings'   => array(
				'multiste' => is_multisite() ? 1 : 0,
			),
			'server_ip'         => $server_ip,
			'web_server'        => ( isset( $_SERVER['SERVER_SOFTWARE'] ) && null !== $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			'php_version'       => phpversion(),
			'php_settings'      => array(
				'memory_limit'        => ini_get( 'memory_limit' ),
				'max_execution_time'  => ini_get( 'max_execution_time' ),
				'post_max_size'       => ini_get( 'post_max_size' ),
				'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
				'memory_limit'        => ini_get( 'memory_limit' ),
			),
		);
		return $analytics_data;
	}


	/**
	 * Course content.
	 */
	private function eb_get_course_count() {
		global $wpdb;

		$count = $wpdb->get_var( // @codingStandardsIgnoreLine
			"SELECT count(*) count
			FROM {$wpdb->prefix}posts
			WHERE post_type = 'eb_course'"
		);

		return $count;
	}

	/**
	 * Category count.
	 */
	private function eb_get_cat_count() {
		global $wpdb;

		$count = $wpdb->get_var( // @codingStandardsIgnoreLine
			"SELECT count(*) count
			FROM {$wpdb->prefix}term_taxonomy
			WHERE taxonomy = 'eb_course_cat'"
		);

		return $count;
	}

	/**
	 * User count.
	 */
	private function eb_get_user_count() {
		global $wpdb;

		$count = $wpdb->get_var( // @codingStandardsIgnoreLine
			"SELECT count(*) count
			FROM {$wpdb->prefix}users"
		);

		return $count;
	}


	/**
	 * Get plugins installed by user excluding the default plugins.
	 */
	private function get_user_installed_plugins() {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins  = array();
		$plugin_infos = get_plugins();

		foreach ( $plugin_infos as $key => $each_plugin_details ) {
			$all_plugins[] = array(
				'name'    => $each_plugin_details['Name'],
				'version' => $each_plugin_details['Version'],
			);
		}

		return $all_plugins;
	}


	/**
	 * Get specific settings of the current plugin, eg: remui.
	 *
	 * @param text $plugin plugin.
	 */
	private function get_plugin_settings( $plugin ) {
		// get complete config.
		$settings                              = array();
		$settings['edwiser_bridge']['general'] = get_option( 'eb_general' );

		if ( is_plugin_active( 'edwiser-bridge-sso/sso.php' ) ) {
			$settings['sso_settings']['general'] = get_option( 'eb_sso_settings_general' );
			$settings['sso_settings']['general'] = get_option( 'eb_sso_settings_redirection' );
		}

		if ( is_plugin_active( 'woocommerce-integration/bridge-woocommerce.php' ) ) {
			$settings['woo_int']['general'] = get_option( 'eb_woo_int_settings' );
		}
		unset( $plugin );

		return $settings;
	}

	/**
	 * Check if site is running on localhost or not.
	 */
	private function detect_site_type() {
		$whitelist = array(
			'127.0.0.1',
			'::1',
		);
		$is_local  = '';
		// Check if site is running on localhost or not.
		if ( isset( $_SERVER['REMOTE_ADDR'] ) && in_array( $_SERVER['REMOTE_ADDR'], $whitelist, true ) ) {
			$is_local = 'localsite--';
		}
		return $is_local;
	}
}
