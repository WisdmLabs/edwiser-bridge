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
if ( ! class_exists( 'Eb_Licensing_Manager' ) ) {

	/**
	 * Class manages the plugin license data.
	 */
	class Eb_Licensing_Manager {

		/**
		 * Short Name for plugin.
		 *
		 * @var string $plugin_short_name Short Name for plugin.
		 */

		public $plugin_short_name = '';

		/**
		 * Slug to be used in url and functions name.
		 *
		 * @var string $plugin_slug Slug to be used in url and functions name
		 */
		public $plugin_slug = '';

		/**
		 * Stores the current plugin version.
		 *
		 * @var string $plugin_version stores the current plugin version.
		 */
		public $plugin_version = '';

		/**
		 * Plugin name.
		 *
		 * @var string $plugin_name Handles the plugin name.
		 */
		public $plugin_name = '';

		/**
		 * Plugin store url.
		 *
		 * @var string  $store_url Stores the URL of store.
		 */
		public static $store_url = 'https://edwiser.org/check-update';

		/**
		 * Plugin author name.
		 *
		 * @var string  $author_name Name of the Author.
		 */
		public $author_name = 'WisdmLabs';

		/**
		 * Initializing the key to empty string.
		 *
		 * @var string $key license key.
		 */
		private $key = '';

		/**
		 * Class constructor.
		 *
		 * @param array $plugin_data Plugin data.
		 */
		public function __construct( $plugin_data ) {
			$this->plugin_name       = $plugin_data['item_name'];
			$this->plugin_short_name = $plugin_data['item_name'];
			$this->plugin_slug       = $plugin_data['slug'];
			$this->plugin_version    = $plugin_data['current_version'];
			$this->key               = $plugin_data['key'];

			add_filter( 'eb_license_setting_messages', array( $this, 'license_messages' ), 15, 1 );
		}

		/**
		 * Function returns the product licensing data.
		 *
		 * @param string $plugin_slug Plugin slug name to get the data.
		 */
		public static function get_plugin_data( $plugin_slug = false ) {
			$products_data = array(
				'single_sign_on'          => array(
					'slug'            => 'single_sign_on',
					'current_version' => '1.4.0',
					'item_name'       => 'Edwiser Bridge Single Sign On',
					'key'             => 'edd_single_sign_on_license_key',
					'path'            => 'edwiser-bridge-sso/sso.php',
				),
				'woocommerce_integration' => array(
					'slug'            => 'woocommerce_integration',
					'current_version' => '2.1.5',
					'item_name'       => 'WooCommerce Integration',
					'key'             => 'edd_woocommerce_integration_license_key',
					'path'            => 'woocommerce-integration/bridge-woocommerce.php',
				),
				'bulk-purchase'           => array(
					'slug'            => 'bulk-purchase',
					'current_version' => '2.3.5',
					'item_name'       => 'Bulk Purchase',
					'key'             => 'edd_bulk-purchase_license_key',
					'path'            => 'edwiser-multiple-users-course-purchase/edwiser-multiple-users-course-purchase.php',
				),
				'selective_sync'          => array(
					'slug'            => 'selective_sync',
					'current_version' => '2.1.2',
					'item_name'       => 'Selective Synchronization',
					'key'             => 'edd_selective_sync_license_key',
					'path'            => 'selective-synchronization/selective-synchronization.php',
				),
				'edwiser_custom_fields'   => array(
					'slug'            => 'edwiser_custom_fields',
					'current_version' => '1.0.0',
					'item_name'       => 'Edwiser Bridge Custom Fields',
					'key'             => 'edd_edwiser_custom_fields_license_key',
					'path'            => 'edwiser-custom-fields/edwiser-custom-fields.php',
				),
				'edwiser_bridge_pro'      => array(
					'slug'            => 'edwiser_bridge_pro',
					'current_version' => '3.0.0',
					'item_name'       => 'Edwiser Bridge Pro - WordPress',
					'key'             => 'edd_edwiser_bridge_pro_license_key',
					'path'            => 'edwiser-bridge-pro/edwiser-bridge-pro.php',
				),
			);
			if ( $plugin_slug ) {
				$products_data = $products_data[ $plugin_slug ];
			}
			return $products_data;
		}

		/**
		 * Deactivates License.
		 */
		public function deactivate_license() {
			$license_key = trim( get_option( 'edd_' . $this->plugin_slug . '_license_key' ) );

			if ( $license_key ) {
				$responce = $this->send_license_request( $license_key, 'deactivate_license' );
				if ( false === $responce['status'] ) {
					return $responce;
				}
				$license_data = $responce['data'];
				if ( 'deactivated' === $license_data->license || 'failed' === $license_data->license ) {
					update_option( 'edd_' . $this->plugin_slug . '_license_status', 'deactivated' );

					// remove addon data.
					// delete_option( 'edd_' . $this->plugin_slug . '_license_addon_data' );.
				}
				delete_transient( 'wdm_' . $this->plugin_slug . '_license_trans' );

				set_transient( 'wdm_' . $this->plugin_slug . '_license_trans', $license_data->license, 0 );
			}
		}

		/**
		 * Function send the license activation/deactivation request to the server.
		 *
		 * @param string $license_key Plugin license key.
		 * @param string $action Action to perform in request.
		 */
		private function send_license_request( $license_key, $action = 'activate_license' ) {

			$resp_data = wdm_request_edwiser(
				array(
					'edd_action'      => $action,
					'license'         => $license_key,
					'item_name'       => urlencode( $this->plugin_name ), // @codingStandardsIgnoreLine
					'current_version' => $this->plugin_version,
					'url'             => get_home_url(),
				)
			);
			if ( false !== $resp_data['status'] ) {
				$is_data_avlb = $this->check_if_no_data( $resp_data['data'], $resp_data['status'] );
				if ( $is_data_avlb ) {
					$resp_data['data']   = __( 'No responce from server edwiser.org.', 'edwiser-bridge' );
					$resp_data['status'] = false;
				}
			}
			return $resp_data;
		}

		/**
		 * Checks if any response received from server or not after making an API call. If no response obtained, then sets next api request after 24 hours.
		 *
		 * @param object $license_data         License Data obtained from server.
		 * @param  string $cur_resp_code    Response code of the API request.
		 */
		public function check_if_no_data( $license_data, $cur_resp_code ) {
			if ( null === $license_data || ! in_array( $cur_resp_code, array( 200, 301 ), true ) ) {
				$GLOBALS['wdm_server_null_response'] = true;
				set_transient( 'wdm_' . $this->plugin_slug . '_license_trans', 'server_did_not_respond', 60 * 60 * 24 );
				return true;
			}
			return false;
		}

		/**
		 * Updates license status in the database and returns status value.
		 *
		 * @param object $license_data License data returned from server.
		 * @param string $plugin_slug  Slug of the plugin. Format of the key in options table is 'edd_<$plugin_slug>_license_status'.
		 *
		 * @return string              Returns status of the license
		 */
		public static function update_status( $license_data, $plugin_slug ) {
			$status = '';
			if ( isset( $license_data->success ) ) {
				// Check if request was successful.
				if ( false === $license_data->success ) {
					if ( ! isset( $license_data->error ) || empty( $license_data->error ) ) {
						$license_data->error = 'invalid';
					}
				}

				// Is there any licensing related error?

				$status = self::check_licensing_error( $license_data );

				if ( ! empty( $status ) ) {
					update_option( 'edd_' . $plugin_slug . '_license_status', $status );
					return $status;
				}
				$status = 'invalid';
				// Check license status retrieved from EDD.
				$status = self::check_license_status( $license_data, $plugin_slug );
			}

			$status = ( empty( $status ) ) ? 'invalid' : $status;
			update_option( 'edd_' . $plugin_slug . '_license_status', $status );
			// save new licensing data.
			if ( 'valid' === $status && isset( $license_data->license_data ) && ! empty( $license_data->license_data ) ) {
				update_option( 'edd_' . $plugin_slug . '_license_addon_data', $license_data->license_data );
			} else {
				delete_option( 'edd_' . $plugin_slug . '_license_addon_data' );
			}

			return $status;
		}

		/**
		 * Checks if there is any error in response.
		 *
		 * @param object $license_data License Data obtained from server.
		 *
		 * @return string empty if no error or else error.
		 */
		public static function check_licensing_error( $license_data ) {
			$status = '';
			if ( isset( $license_data->error ) && ! empty( $license_data->error ) ) {
				switch ( $license_data->error ) {
					case 'revoked':
						$status = 'disabled';
						break;
					case 'expired':
						$status = 'expired';
						break;
					case 'item_name_mismatch':
						$status = 'item_name_mismatch';
						break;
					case 'no_activations_left':
						$status = 'no_activations_left';
						break;
				}
			}
			return $status;
		}

		/**
		 * Function to check the license status.
		 *
		 * @param array  $license_data License responce data.
		 * @param string $plugin_slug Plugin Slug to check the license.
		 */
		public static function check_license_status( $license_data, $plugin_slug ) {
			$status = 'invalid';
			if ( isset( $license_data->license ) && ! empty( $license_data->license ) ) {
				switch ( $license_data->license ) {
					case 'invalid':
						$status = 'invalid';
						if ( isset( $license_data->activations_left ) && $license_data->activations_left == '0' ) { // @codingStandardsIgnoreLine
							include_once plugin_dir_path( __FILE__ ) . 'class-eb-get-plugin-data.php';
							$active_site = Eb_Get_Plugin_Data::get_site_list( $plugin_slug );
							if ( ! empty( $active_site ) || '' !== $active_site ) {
								$status = 'no_activations_left';
							}
						}
						break;
					case 'failed':
						$status                                   = 'failed';
						$GLOBALS['wdm_license_activation_failed'] = true;
						break;

					default:
						$status = $license_data->license;
				}
			}
			return $status;
		}

		/**
		 * Activates License.
		 */
		public function activate_license() {
			$license_key = trim( $this->key );
			if ( $license_key ) {
				update_option( 'edd_' . $this->plugin_slug . '_license_key', $license_key );

				$responce = $this->send_license_request( $license_key );
				if ( false === $responce['status'] ) {
					return $responce;
				}
				$license_data = $responce['data'];
				$expir_time   = $this->get_expiration_time( $license_data );

				if ( isset( $license_data->expires ) && false !== $license_data->expires && 'lifetime' !== $license_data->expires && $expir_time <= time() && 0 !== $expir_time && ! isset( $license_data->error ) ) {
					$license_data->error = 'expired';
				}

				if ( isset( $license_data->renew_link ) && ( ! empty( $license_data->renew_link ) || '' !== $license_data->renew_link ) ) {
					update_option( 'wdm_' . $this->plugin_slug . '_product_site', $license_data->renew_link );
				}

				$this->update_number_of_sites_using_license( $license_data );

				$license_status = self::update_status( $license_data, $this->plugin_slug );

				$this->set_transient_on_activation( $license_status );
			}
		}

		/**
		 * Function to get the license expiry date.
		 *
		 * @param object $license_data License data object.
		 */
		public function get_expiration_time( $license_data ) {
			return isset( $license_data->expires ) ? strtotime( $license_data->expires ) : 0;
		}

		/**
		 * Function to get the number of sites using the license key.
		 *
		 * @param object $license_data License data object.
		 */
		private function update_number_of_sites_using_license( $license_data ) {
			if ( isset( $license_data->sites ) && ( ! empty( $license_data->sites ) || '' !== $license_data->sites ) ) {
				update_option( 'eb_' . $this->plugin_slug . '_license_key_sites', $license_data->sites );
				update_option( 'eb_' . $this->plugin_slug . '_license_max_site', $license_data->license_limit );
			} else {
				update_option( 'eb_' . $this->plugin_slug . '_license_key_sites', '' );
				update_option( 'eb_' . $this->plugin_slug . '_license_max_site', '' );
			}
			if ( isset( $license_data->expires ) && '' !== $license_data->expires ) {
				update_option( 'eb_' . $this->plugin_slug . '_license_key_expires', $license_data->expires );
			}
			if ( isset( $license_data->site_count ) && '' !== $license_data->site_count ) {
				update_option( 'eb_' . $this->plugin_slug . '_license_key_site_count', $license_data->site_count );
			}
			if ( isset( $license_data->license_limit ) && '' !== $license_data->license_limit ) {
				update_option( 'eb_' . $this->plugin_slug . '_license_key_license_limit', $license_data->license_limit );
			}

		}

		/**
		 * Function sets transient to activate the key.
		 *
		 * @param string $license_status License status.
		 */
		public function set_transient_on_activation( $license_status ) {
			$tran_var = get_transient( 'wdm_' . $this->plugin_slug . '_license_trans' );
			if ( isset( $tran_var ) ) {
				delete_transient( 'wdm_' . $this->plugin_slug . '_license_trans' );
				if ( ! empty( $license_status ) ) {

					include_once plugin_dir_path( __FILE__ ) . 'class-eb-get-plugin-data.php';
					Eb_Get_Plugin_Data::set_response_data( $license_status, '', $this->plugin_slug, true );
				}
			}
		}

		/**
		 * Function prints the list of active sites.
		 *
		 * @param array $active_site list of the  site where the license key is active.
		 */
		private function check_if_site_active( $active_site ) {
			if ( ! empty( $active_site ) || '' !== $active_site ) {
				$display = '<ul>' . $active_site . '</ul>';
			} else {
				$display = '';
			}
			return $display;
		}

		/**
		 * Function sets the licensing status globally.
		 *
		 * @param string $status License status.
		 */
		private function get_licenses_global_status( $status ) {
			if ( isset( $GLOBALS['wdm_server_null_response'] ) && true === $GLOBALS['wdm_server_null_response'] ) { // WPCS: CSRF ok, input var ok.
				$status = 'server_did_not_respond';
			} elseif ( isset( $GLOBALS['wdm_license_activation_failed'] ) && true === $GLOBALS['wdm_license_activation_failed'] ) { // WPCS: CSRF ok, input var ok.
				$status = 'license_activation_failed';
			} elseif ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) && empty( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) ) { // WPCS: CSRF ok, input var ok. @codingStandardsIgnoreLine
				$status = 'no_license_key_entered';
			}
			return $status;
		}

		/**
		 * Function prepares the license message.
		 *
		 * @param string $eb_lice_messages licensing message.
		 */
		public function license_messages( $eb_lice_messages ) {
			// Get License Status.
			$status = get_option( 'edd_' . $this->plugin_slug . '_license_status' );
			include_once plugin_dir_path( __FILE__ ) . 'class-eb-get-plugin-data.php';
			$status = $this->get_licenses_global_status( $status );

			$active_site = Eb_Get_Plugin_Data::get_site_list( $this->plugin_slug );

			$display = '';

			$display = $this->check_if_site_active( $active_site );
			if ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) && ! isset( $_POST['eb_server_nr'] ) ) { // WPCS: CSRF ok, input var ok. @codingStandardsIgnoreLine
				// Handle Submission of inputs on license page.
				if ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) && empty( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) ) { // WPCS: CSRF ok, input var ok. @codingStandardsIgnoreLine
					// If empty, show error message.

					/*
					* translators: license key.
					*/
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'Please enter license key for %s.', 'ebbp-textdomain' ), $this->plugin_name ), // @codingStandardsIgnoreLine
						'error'
					);
				} elseif ( 'server_did_not_respond' === $status ) {
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'No response from server. Please try again later.', 'ebbp-textdomain' ), $this->plugin_name ),
						'error'
					);
				} elseif ( 'item_name_mismatch' === $status ) {
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License key is not valid. Please check your license key and try again', 'ebbp-textdomain' ), $this->plugin_name ),
						'error'
					);
				} elseif ( false !== $status && 'valid' === $status ) { // Valid license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License key for %s is activated.', 'ebbp-textdomain' ), $this->plugin_name ), // @codingStandardsIgnoreLine
						'updated'
					);
				} elseif ( false !== $status && 'expired' === $status && ( ! empty( $display ) || '' !== $display ) ) { // Expired license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License key for %s have been Expired. Please, Renew it. <br/>Your License Key is already activated at : ' . $display, 'ebbp-textdomain' ), $this->plugin_name ), // @codingStandardsIgnoreLine
						'error'
					);
				} elseif ( false !== $status && 'expired' === $status ) { // Expired license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License key for %s have been Expired. Please, Renew it.', 'ebbp-textdomain' ), $this->plugin_name ), // @codingStandardsIgnoreLine
						'error'
					);
				} elseif ( false !== $status && 'disabled' === $status ) { // Disabled license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License key for %s is Disabled.', 'ebbp-textdomain' ), $this->plugin_name ), // @codingStandardsIgnoreLine
						'error'
					);
				} elseif ( 'no_activations_left' === $status ) { // Invalid license key   and site.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License Key for %1$s is already activated at : %2$s', 'ebbp-textdomain' ), $this->plugin_name, $display ), // @codingStandardsIgnoreLine
						'error'
					);
				} else {
					$this->invalid_status_messages( $status, $display );
				}
			}
			ob_start();
			settings_errors( 'eb_' . $this->plugin_slug . '_errors' );
			$ss_setting_messages = ob_get_contents();
			ob_end_clean();

			return $eb_lice_messages . $ss_setting_messages;
		}

		/**
		 * Function sets invalid status message.
		 *
		 * @param string $status status of the key.
		 * @param string $display should display the message or not .
		 */
		private function invalid_status_messages( $status, $display ) {
			if ( 'invalid' === $status && ( ! empty( $display ) || '' !== $display ) ) { // Invalid license key and site.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf( __( 'License Key for %s is already activated at : ' . $display, 'ebbp-textdomain' ), $this->plugin_name ), // @codingStandardsIgnoreLine
					'error'
				);
			} elseif ( 'invalid' === $status ) { // Invalid license key.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf( __( 'Please enter valid license key for %s.', 'ebbp-textdomain' ), $this->plugin_name ), // @codingStandardsIgnoreLine
					'error'
				);
			} elseif ( 'site_inactive' === $status && ( ! empty( $display ) || '' !== $display ) ) { // Invalid license key   and site inactive.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf( __( 'License Key for %s is already activated at : ' . $display, 'ebbp-textdomain' ), $this->plugin_name ), // @codingStandardsIgnoreLine
					'error'
				);
			} elseif ( 'site_inactive' === $status ) { // Site is inactive.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					__( 'Site inactive(Press Activate license to activate plugin)', 'ebbp-textdomain' ),
					'error'
				);
			} elseif ( 'deactivated' === $status ) { // Site is inactive.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf( __( 'License Key for %s is deactivated', 'ebbp-textdomain' ), $this->plugin_name ), // @codingStandardsIgnoreLine
					'updated'
				);
			}
		}
	}
}
