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
if ( ! class_exists( 'Eb_Licensing_Manger' ) ) {

	/**
	 * Class manages the plugin license data.
	 */
	class Eb_Licensing_Manger {

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
				$license_data = $this->send_license_request( $license_key, 'deactivate_license' );
				if ( ! $license_data['status'] ) {
					return $license_data['data'];
				}
				$license_data = $license_data['data'];

				if ( 'deactivated' === $license_data->license || 'failed' === $license_data->license ) {
					update_option( 'edd_' . $this->plugin_slug . '_license_status', 'deactivated' );
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

			$resp_data  = array(
				'status' => false,
				'data'   => '',
			);
			$api_params = array(
				'edd_action'      => $action,
				'license'         => $license_key,
				'item_name'       => urlencode( $this->plugin_name ),
				'current_version' => $this->plugin_version,
				'url'             => get_home_url(),
			);

			$response = wp_remote_get(
				add_query_arg( $api_params, self::$store_url ),
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'blocking'  => true,
				)
			);

			if ( is_wp_error( $response ) ) {
				$resp_data['data'] = $response->get_error_messages();
			}

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			$is_data_avlb = $this->check_if_no_data( $license_data, wp_remote_retrieve_response_code( $response ) );
			if ( $is_data_avlb ) {
				$resp_data['data'] = __( 'No responce from server edwiser.org.', 'eb-textdomain' );
			} else {
				$resp_data['data']   = $license_data;
				$resp_data['status'] = true;
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
		 * @param  string $plugin_slug  Slug of the plugin. Format of the key in options table is 'edd_<$plugin_slug>_license_status'.
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

				if ( isset( $license_data->error ) && ! empty( $license_data->error ) ) {
					$status = 'disabled' === $license_data->error ? '' : $license_data->error;
				}

				if ( empty( $status ) ) {
					if ( isset( $license_data->license ) ) {
						$status = $license_data->license;
						if ( 'failed' === $license_data->license ) {
							$status                                   = 'failed';
							$GLOBALS['wdm_license_activation_failed'] = true;
						} elseif ( 'invalid' === $license_data->license && isset( $license_data->activations_left ) && '0' === $license_data->activations_left ) {
							include_once plugin_dir_path( __FILE__ ) . 'class-eb-get-plugin-data.php';
							$active_site = Eb_Get_Plugin_Data::get_site_list( $plugin_slug );
							$status      = '' !== trim( $active_site ) ? 'no_activations_left' : $license_data->license;
						}
					}
				}
			}

			$status = empty( $status ) ? 'invalid' : $status;
			update_option( 'edd_' . $plugin_slug . '_license_status', $status );
			return $status;
		}

		/**
		 * Activates License.
		 */
		public function activate_license() {
			$license_key = trim( $this->key );
			if ( $license_key ) {
				update_option( 'edd_' . $this->plugin_slug . '_license_key', $license_key );

				$license_data = $this->send_license_request( $license_key );
				if ( ! $license_data['status'] ) {
					return $license_data['data'];
				}
				$license_data = $license_data['data'];
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
					$time = ( 'valid' === $license_status ) ? ( 60 * 60 * 24 * 7 ) : ( 60 * 60 * 24 );
					set_transient( 'wdm_' . $this->plugin_slug . '_license_trans', $license_status, $time );
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
			if ( isset( $GLOBALS['wdm_server_null_response'] ) && true === $GLOBALS['wdm_server_null_response'] ) {
				$status = 'server_did_not_respond';
			} elseif ( isset( $GLOBALS['wdm_license_activation_failed'] ) && true === $GLOBALS['wdm_license_activation_failed'] ) {
				$status = 'license_activation_failed';
			} elseif ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) && empty( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) ) {
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
			if ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) &&
					! isset( $_POST['eb_server_nr'] ) ) {
				// Handle Submission of inputs on license page.
				if ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) &&
						empty( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) ) {
					// If empty, show error message.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'Please enter license key for %s.', 'ebbp-textdomain' ), $this->plugin_name ),
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
						sprintf( __( 'License key for %s is activated.', 'ebbp-textdomain' ), $this->plugin_name ),
						'updated'
					);
				} elseif ( false !== $status && 'expired' === $status && ( ! empty( $display ) || '' !== $display ) ) { // Expired license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License key for %s have been Expired. Please, Renew it. <br/>Your License Key is already activated at : ' . $display, 'ebbp-textdomain' ), $this->plugin_name ),
						'error'
					);
				} elseif ( false !== $status && 'expired' === $status ) { // Expired license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License key for %s have been Expired. Please, Renew it.', 'ebbp-textdomain' ), $this->plugin_name ),
						'error'
					);
				} elseif ( false !== $status && 'disabled' === $status ) { // Disabled license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License key for %s is Disabled.', 'ebbp-textdomain' ), $this->plugin_name ),
						'error'
					);
				} elseif ( 'no_activations_left' === $status ) { // Invalid license key   and site.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf( __( 'License Key for %1$s is already activated at : %2$s', 'ebbp-textdomain' ), $this->plugin_name, $display ),
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
			if ( 'invalid' === $status && ( ! empty( $display ) || '' !== $display ) ) { // Invalid license key   and site.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf( __( 'License Key for %s is already activated at : ' . $display, 'ebbp-textdomain' ), $this->plugin_name ),
					'error'
				);
			} elseif ( 'invalid' === $status ) { // Invalid license key.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf( __( 'Please enter valid license key for %s.', 'ebbp-textdomain' ), $this->plugin_name ),
					'error'
				);
			} elseif ( 'site_inactive' === $status && ( ! empty( $display ) || '' !== $display ) ) { // Invalid license key   and site inactive.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf( __( 'License Key for %s is already activated at : ' . $display, 'ebbp-textdomain' ), $this->plugin_name ),
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
					sprintf( __( 'License Key for %s is deactivated', 'ebbp-textdomain' ), $this->plugin_name ),
					'updated'
				);
			}
		}
	}
}
