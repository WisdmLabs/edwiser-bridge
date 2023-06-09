<?php
/**
 * Handles the plugin license functionality.
 *
 * @package    edwiserBridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Eb_Get_Plugin_Data' ) ) {
	/**
	 * Class to fetch the license data.
	 */
	class Eb_Get_Plugin_Data {

		/**
		 * Responce data refarance.
		 *
		 * @var object $response_data variable stores the responce data.
		 */
		public static $response_data;

		/**
		 * Function to get license info from DB.
		 *
		 * @param array $plugin_slug Plugin slug name.
		 * @param bool  $cache cache value info.
		 */
		public static function get_data_from_db( $plugin_slug, $cache = true ) {

			if ( null !== self::$response_data && true === $cache ) {
				return self::$response_data;
			}
			if ( ! class_exists( 'Eb_Licensing_Manager' ) ) {
				include_once plugin_dir_path( __FILE__ ) . 'class-eb-licensing-manager.php';
			}
			$plugin_data = Eb_Licensing_Manager::get_plugin_data( $plugin_slug );
			$plugin_name = $plugin_data['plugin_name'];
			$plugin_slug = $plugin_data['plugin_slug'];
			$store_url   = Eb_Licensing_Manager::$store_url;

			$license_transient = get_transient( 'wdm_' . $plugin_slug . '_license_trans' );

			if ( ! $license_transient ) {
				$license_key = trim( get_option( 'edd_' . $plugin_slug . '_license_key' ) );

				if ( $license_key ) {
					$api_params = array(
						'edd_action'      => 'check_license',
						'license'         => $license_key,
						'item_name'       => urlencode( $plugin_name ), // @codingStandardsIgnoreLine
						'current_version' => $plugin_data['plugin_version'],
					);

					$response = wp_remote_get(
						add_query_arg( $api_params, $store_url ),
						array(
							'timeout'    => 15,
							'sslverify'  => false,
							'blocking'   => true,
							'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) . 'Edwiser Bridge',
						)
					);

					if ( is_wp_error( $response ) ) {
						return false;
					}

					$license_data = json_decode( wp_remote_retrieve_body( $response ) );

					$valid_resp_code = array( '200', '301' );

					$curr_resp_code = wp_remote_retrieve_response_code( $response );

					if ( null === $license_data || ! in_array( $curr_resp_code, $valid_resp_code, true ) ) {
						// if server does not respond, read current license information.
						$license_status = get_option( 'edd_' . $plugin_slug . '_license_status', '' );
						if ( empty( $license_data ) ) {
							set_transient( 'wdm_' . $plugin_slug . '_license_trans', 'server_did_not_respond', 60 * 60 * 24 );
						}
					} else {
						include_once plugin_dir_path( __FILE__ ) . 'class-eb-select-add-plugin-data-in-db.php';
						$license_status = Eb_Licensing_Manager::update_status( $license_data, $plugin_slug );
					}

					$active_site = self::get_site_list( $plugin_slug );

					self::set_response_data( $license_status, $active_site, $plugin_slug, true );

					return self::$response_data;
				}
			} else {
				$license_status = get_option( 'edd_' . $plugin_slug . '_license_status' );
				$active_site    = self::get_site_list( $plugin_slug );

				self::set_response_data( $license_status, $active_site, $plugin_slug );
				return self::$response_data;
			}
		}

		/**
		 * This function is used to get list of sites where license key is already acvtivated.
		 *
		 * @param string $plugin_slug current plugin's slug.
		 */
		public static function get_site_list( $plugin_slug ) {
			$sites    = get_option( 'eb_' . $plugin_slug . '_license_key_sites' );
			$max      = get_option( 'eb_' . $plugin_slug . '_license_max_site' );
			$cur_site = get_site_url();
			$cur_site = preg_replace( '#^https?://#', '', $cur_site );

			$site_count  = 0;
			$active_site = '';
			if ( $sites && '' !== $sites ) {
				foreach ( $sites as $key ) {
					foreach ( $key as $value ) {
						$value = rtrim( $value, '/' );
						if ( 0 !== strcasecmp( $value, $cur_site ) ) {
							$active_site .= '<li>' . $value . '</li>';
							++$site_count;
						}
					}
				}
			}

			if ( $site_count >= $max ) {
				return $active_site;
			} else {
				return '';
			}
		}


		/**
		 * Function to add the responce data into the DB.
		 *
		 * @param string $license_status License status.
		 * @param array  $active_site List of the active site.
		 * @param string $plugin_slug  Plugin slug.
		 * @param bool   $set_trans Should set transient or not.
		 */
		public static function set_response_data( $license_status, $active_site, $plugin_slug, $set_trans = false ) {

			if ( 'valid' === $license_status ) {
				self::$response_data = 'available';
			} elseif ( 'expired' === $license_status && ( ! empty( $active_site ) || '' !== $active_site ) ) {
				self::$response_data = 'unavailable';
			} elseif ( 'expired' === $license_status ) {
				self::$response_data = 'available';
			} else {
				self::$response_data = 'unavailable';
			}

			if ( $set_trans ) {
				switch ( $license_status ) {
					case 'invalid':
					case 'no_activations_left':
						$time = 0; // Do not repeat.
						break;
					case 'failed':
						$time = 86400; // Repeat everyday.
						break;
					case 'expired':
						$time = 86400 * 2; // Repeat every 2 days.
						break;
					case 'disabled':
						$time = 86400 * 4; // Repeat every 4 days.
						break;
					case 'valid':
						$time = 86400 * 7; // Repeat every 7 days.
						break;
					default:
						$time = 86400 * 7; // Fallback. Repeat every 7 days.
						break;
				}
				set_transient( 'wdm_' . $plugin_slug . '_license_trans', $license_status, $time );
			}
		}
	}
}
