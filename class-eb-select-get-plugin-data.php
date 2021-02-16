<?php
/**
 * File responisble to get the licensing data.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Selective_Sync
 * @subpackage Selective_Sync/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace ebSelectSync\includes;

if ( ! class_exists( 'Eb_Select_Get_Plugin_Data' ) ) {

	/**
	 * Class to get the plugin data.
	 */
	class Eb_Select_Get_Plugin_Data {

		/**
		 * Resopnse data.
		 *
		 * @var string $response_data    response data.
		 */
		public static $response_data;

		/**
		 * Constructor of the class.
		 *
		 * @param string $plugin_data plugin data.
		 */
		public static function get_data_from_db( $plugin_data ) {
			$plugin_name = $plugin_data['plugin_name'];
			$plugin_slug = $plugin_data['plugin_slug'];
			$store_url   = $plugin_data['store_url'];

			$get_trans = get_transient( 'eb_' . $plugin_slug . '_license_trans' );

			if ( ! $get_trans ) {
				$license_key = trim( get_option( 'edd_' . $plugin_slug . '_license_key' ) );

				if ( $license_key ) {
					$api_params = array(
						'edd_action'      => 'check_license',
						'license'         => $license_key,
						'item_name'       => urlencode( $plugin_name ),
						'current_version' => $plugin_data['plugin_version'],
					);

					$response = wp_remote_get(
						add_query_arg( $api_params, $store_url ),
						array(
							'timeout'   => 15,
							'sslverify' => false,
							'blocking'  => true,
						)
					);

					if ( is_wp_error( $response ) ) {
						return false;
					}

					$license_data        = json_decode( wp_remote_retrieve_body( $response ) );
					$valid_response_code = array( '200', '301' );
					$curl_resp_code      = wp_remote_retrieve_response_code( $response );

					if ( null === $license_data || ! in_array( $curl_resp_code, $valid_response_code, true ) ) {
						// if server does not respond, read current license information.
						$license_status = get_option( 'edd_' . $plugin_slug . '_license_status', '' );
						if ( empty( $license_data ) ) {
							set_transient( 'eb_' . $plugin_slug . '_license_trans', 'server_did_not_respond', 60 * 60 * 24 );
						}
					} else {
						$license_status = $license_data->license;
					}

					if ( empty( $license_status ) ) {
						return;
					}

					$active_site = self::get_site_list( $plugin_data['plugin_slug'] );
					if ( isset( $license_data->license ) && ! empty( $license_data->license ) ) {
						update_option( 'edd_' . $plugin_slug . '_license_status', $license_status );
					}

					$license_status = $license_data->license;
					self::set_response_data( $license_status, $active_site, $plugin_slug, true );
					return self::$response_data;
				}
			} else {
				$license_status = get_option( 'edd_' . $plugin_slug . '_license_status' );
				$active_site    = self::get_site_list( $plugin_data['plugin_slug'] );
				self::set_response_data( $license_status, $active_site, $plugin_slug );
				return self::$response_data;
			}
		}


		/**
		 * Set response data.
		 *
		 * @param string $license_status license status.
		 * @param string $active_site active sites list.
		 * @param string $plugin_slug plugin slug.
		 * @param string $set_transient transient time.
		 */
		public static function set_response_data( $license_status, $active_site, $plugin_slug, $set_transient = false ) {
			self::$response_data = 'unavailable';
			if ( 'expired' === $license_status && ( ! empty( $active_site ) || '' !== $active_site ) ) {
				self::$response_data = 'unavailable';
			} elseif ( 'expired' === $license_status || 'valid' === $license_status ) {
				self::$response_data = 'available';
			}
			if ( $set_transient ) {
				if ( 'valid' === $license_status ) {
					$time = 60 * 60 * 24 * 7;
				} else {
					$time = 60 * 60 * 24;
				}
				set_transient( 'wdm_' . $plugin_slug . '_license_trans', $license_status, $time );
			}
		}


		/**
		 * This function is used to get list of sites where license key is already acvtivated.
		 *
		 * @param type $plugin_slug current plugin's slug.
		 * @return string  list of site
		 */
		public static function get_site_list( $plugin_slug ) {
			$sites       = get_option( 'eb_' . $plugin_slug . '_license_key_sites' );
			$max         = get_option( 'eb_' . $plugin_slug . '_license_max_site' );
			$cur_site    = get_site_url();
			$cur_site    = preg_replace( '#^https?://#', '', $cur_site );
			$site_count  = 0;
			$active_site = '';

			if ( ! empty( $sites ) || '' !== $sites ) {
				foreach ( $sites as $key ) {
					foreach ( $key as $value ) {
						$value = rtrim( $value, '/' );

						if ( 0 !== strcasecmp( $value, $cur_site ) ) {
							$active_site .= '<li>' . $value . '</li>';
							$site_count++;
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
	}
}
