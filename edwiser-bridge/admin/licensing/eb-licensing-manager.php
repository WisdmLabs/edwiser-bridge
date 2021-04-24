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
if ( ! class_exists( 'EbLicensingManger' ) ) {

	/**
	 * Class manages the plugin license data.
	 */
	class EbLicensingManger {

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
		public $store_url = '';

		/**
		 * Plugin author name.
		 *
		 * @var string  $author_name Name of the Author.
		 */
		public $author_name = '';

		/**
		 * Plugin text domain name.
		 *
		 * @var string  $plugin_text_domain Name of the Author.
		 */
		public $plugin_text_domain = '';

		/**
		 * Class constructor.
		 *
		 * @param array $plugin_data Plugin data.
		 */
		public function __construct( $plugin_data ) {
			$this->author_name        = $plugin_data['author_name'];
			$this->plugin_name        = $plugin_data['plugin_name'];
			$this->plugin_short_name  = $plugin_data['plugin_short_name'];
			$this->plugin_slug        = $plugin_data['plugin_slug'];
			$this->plugin_version     = $plugin_data['plugin_version'];
			$this->store_url          = $plugin_data['store_url'];
			$this->plugin_text_domain = $plugin_data['pluginTextDomain'];

			add_filter( 'eb_setting_messages', array( $this, 'licenseMessages' ), 15, 1 );
			add_filter( 'eb_licensing_information', array( $this, 'licenseInformation' ), 15, 1 );
			add_action( 'init', array( $this, 'addData' ), 5 );
		}

		/**
		 * Function adds the data in DB on the license activation/deactivation.
		 */
		public function addData() {
			if ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_activate' ] ) ) {
				if ( ! check_admin_referer( 'edd_' . $this->plugin_slug . '_nonce', 'edd_' . $this->plugin_slug . '_nonce' ) ) {
					return;
				}
				$this->activateLicense();
			} elseif ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_deactivate' ] ) ) {
				if ( ! check_admin_referer( 'edd_' . $this->plugin_slug . '_nonce', 'edd_' . $this->plugin_slug . '_nonce' ) ) {
					return;
				}
				$this->deactivateLicense();
			}
		}

		/**
		 * Deactivates License.
		 */
		public function deactivateLicense() {
			$license_key = trim( get_option( 'edd_' . $this->plugin_slug . '_license_key' ) );

			if ( $license_key ) {
				$api_params = array(
					'edd_action'      => 'deactivate_license',
					'license'         => $license_key,
					'item_name'       => urlencode( $this->plugin_name ),
					'current_version' => $this->plugin_version,
				);

				$response = wp_remote_get(
					add_query_arg( $api_params, $this->store_url ),
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'blocking'  => true,
					)
				);

				if ( is_wp_error( $response ) ) {
					return false;
				}

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				$valid_resp_code = array( '200', '301' );

				$cur_resp_code = wp_remote_retrieve_response_code( $response );

				$is_data_avlb = $this->checkIfNoData( $license_data, $cur_resp_code, $valid_resp_code );

				if ( ! $is_data_avlb ) {
					return;
				}

				if ( 'deactivated' === $license_data->license || 'failed' === $license_data->license ) {
					update_option( 'edd_' . $this->plugin_slug . '_license_status', 'deactivated' );
				}
				delete_transient( 'wdm_' . $this->plugin_slug . '_license_trans' );

				set_transient( 'wdm_' . $this->plugin_slug . '_license_trans', $license_data->license, 0 );
			}
		}

		/**
		 * Updates license status in the database and returns status value.
		 *
		 * @param object $license_data License data returned from server.
		 * @param  string $plugin_slug  Slug of the plugin. Format of the key in options table is 'edd_<$plugin_slug>_license_status'.
		 */
		public static function updateStatus( $license_data, $plugin_slug ) {
			$status = '';
			if ( isset( $license_data->success ) ) {
				// Check if request was successful.
				if ( false === $license_data->success ) {
					if ( ! isset( $license_data->error ) || empty( $license_data->error ) ) {
						$license_data->error = 'invalid';
					}
				}

				// Is there any licensing related error?

				$status = self::checkLicensingError( $license_data );

				if ( ! empty( $status ) ) {
					update_option( 'edd_' . $plugin_slug . '_license_status', $status );
					return $status;
				}
				$status = 'invalid';
				// Check license status retrieved from EDD.
				$status = self::checkLicenseStatus( $license_data, $plugin_slug );
			}

			$status = ( empty( $status ) ) ? 'invalid' : $status;
			update_option( 'edd_' . $plugin_slug . '_license_status', $status );
			return $status;
		}

		/**
		 * Checks if there is any error in response.
		 *
		 * @param object $license_data License Data obtained from server.
		 */
		public static function checkLicensingError( $license_data ) {
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
		 * Function checks the license status.
		 *
		 * @param object $license_data Object of the licensing data.
		 * @param string $plugin_slug Slug of the plug.
		 */
		public static function checkLicenseStatus( $license_data, $plugin_slug ) {
			$status = 'invalid';
			if ( isset( $license_data->license ) && ! empty( $license_data->license ) ) {
				switch ( $license_data->license ) {
					case 'invalid':
						$status = 'invalid';
						if ( isset( $license_data->activations_left ) && '0' === $license_data->activations_left ) {
							include_once plugin_dir_path( __FILE__ ) . 'class-eb-select-get-plugin-data.php';
							$active_site = EBMUSelectGetPluginData::getSiteList( $plugin_slug );

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
		 * Checks if any response received from server or not after making an API call. If no response obtained, then sets next api request after 24 hours.
		 *
		 * @param object $license_data         License Data obtained from server.
		 * @param  string $cur_resp_code    Response code of the API request.
		 * @param  array  $valid_resp_code      Array of acceptable response codes.
		 */
		public function checkIfNoData( $license_data, $cur_resp_code, $valid_resp_code ) {
			if ( null === $license_data || ! in_array( $cur_resp_code, $valid_resp_code, true ) ) {
				$GLOBALS['wdm_server_null_response'] = true;
				set_transient( 'wdm_' . $this->plugin_slug . '_license_trans', 'server_did_not_respond', 60 * 60 * 24 );

				return false;
			}
			return true;
		}

		/**
		 * Activates License.
		 */
		public function activateLicense() {

			$license_key = trim( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] );

			if ( $license_key ) {
				update_option( 'edd_' . $this->plugin_slug . '_license_key', $license_key );
				$api_params = array(
					'edd_action'      => 'activate_license',
					'license'         => $license_key,
					'item_name'       => urlencode( $this->plugin_name ),
					'current_version' => $this->plugin_version,
				);

				$response = wp_remote_get(
					add_query_arg( $api_params, $this->store_url ),
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'blocking'  => true,
					)
				);

				if ( is_wp_error( $response ) ) {
					return false;
				}

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				$valid_resp_code = array( '200', '301' );

				$cur_resp_code = wp_remote_retrieve_response_code( $response );

				$is_data_avlb = $this->checkIfNoData( $license_data, $cur_resp_code, $valid_resp_code );
				if ( ! $is_data_avlb ) {
					return;
				}

				$expir_time = $this->getExpirationTime( $license_data );
				$cur_time   = time();

				if ( isset( $license_data->expires ) && false !== $license_data->expires && 'lifetime' !== $license_data->expires && $expir_time <= $cur_time && 0 !== $expir_time && ! isset( $license_data->error ) ) {
					$license_data->error = 'expired';
				}

				if ( isset( $license_data->renew_link ) && ( ! empty( $license_data->renew_link ) || '' !== $license_data->renew_link ) ) {
					update_option( 'wdm_' . $this->plugin_slug . '_product_site', $license_data->renew_link );
				}

				$this->updateNumberOfSitesUsingLicense( $license_data );

				$license_status = self::updateStatus( $license_data, $this->plugin_slug );

				$this->setTransientOnActivation( $license_status );
			}
		}

		/**
		 * Function to get the license expiry date.
		 *
		 * @param object $license_data License data object.
		 */
		public function getExpirationTime( $license_data ) {
			return isset( $license_data->expires ) ? strtotime( $license_data->expires ) : 0;
		}

		/**
		 * Function to get the number of sites using the license key.
		 *
		 * @param object $license_data License data object.
		 */
		public function updateNumberOfSitesUsingLicense( $license_data ) {
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
		public function setTransientOnActivation( $license_status ) {
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
		public function checkIfSiteActive( $active_site ) {
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
		public function licenseMessages( $eb_lice_messages ) {
			// Get License Status.
			$status = get_option( 'edd_' . $this->plugin_slug . '_license_status' );
			include_once plugin_dir_path( __FILE__ ) . 'class-eb-select-get-plugin-data.php';
			$status = $this->get_licenses_global_status( $status );

			$active_site = EBMUSelectGetPluginData::getSiteList( $this->plugin_slug );

			$display = '';

			$display = $this->checkIfSiteActive( $active_site );
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
					$this->invalidStatusMessages( $status, $display );
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
		public function invalidStatusMessages( $status, $display ) {
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

		/**
		 * Function returns the license infomration.
		 *
		 * @param array $licensing_info license information array.
		 */
		public function licenseInformation( $licensing_info ) {
			$renew_link = get_option( 'eb_' . $this->plugin_slug . '_product_site' );

			// Get License Status.
			$status = get_option( 'edd_' . $this->plugin_slug . '_license_status' );
			include_once plugin_dir_path( __FILE__ ) . 'class-eb-select-get-plugin-data.php';

			$active_site = EBMUSelectGetPluginData::getSiteList( $this->plugin_slug );

			$display = '';
			if ( ! empty( $active_site ) || '' !== $active_site ) {
				$display = '<ul>' . $active_site . '</ul>';
			}

			$license_key = trim( get_option( 'edd_' . $this->plugin_slug . '_license_key' ) );

			// LICENSE KEY.
			if ( ( 'valid' === $status || 'expired' === $status ) && ( empty( $display ) || '' === $display ) ) {
				$license_key_html = '<input id="edd_' . $this->plugin_slug . '_license_key" name="edd_' . $this->plugin_slug . '_license_key" type="text" class="regular-text" value="' . esc_attr( $license_key ) . '" readonly/>';
			} else {
				$license_key_html = '<input id="edd_' . $this->plugin_slug . '_license_key" name="edd_' . $this->plugin_slug . '_license_key" type="text" class="regular-text" value="' . esc_attr( $license_key ) . '" />';
			}

			// LICENSE STATUS.
			$license_status = $this->displayLicenseStatus( $status, $display );

			// Activate License Action Buttons.
			ob_start();
			wp_nonce_field( 'edd_' . $this->plugin_slug . '_nonce', 'edd_' . $this->plugin_slug . '_nonce' );
			$nonce = ob_get_contents();
			ob_end_clean();
			if ( false !== $status && 'valid' === $status ) {
				$buttons = '<input type="submit" class="button-primary" name="edd_' . $this->plugin_slug . '_license_deactivate" value="' . __( 'Deactivate License', 'ebbp-textdomain' ) . '" />';
			} elseif ( 'expired' === $status && ( ! empty( $display ) || '' !== $display ) ) {
				$buttons  = '<input type = "submit" class = "button-primary" name = "edd_' . $this->plugin_slug . '_license_activate" value = "' . __( 'Activate License', 'ebbp-textdomain' ) . '"/>';
				$buttons .= ' <input type = "button" class = "button-primary" name = "edd_' . $this->plugin_slug . '_license_renew" value = "' . __( 'Renew License', 'ebbp-textdomain' ) . '" onclick = "window.open( \'' . $renew_link . '\')"/>';
			} elseif ( 'expired' === $status ) {
				$buttons  = '<input type="submit" class="button-primary" name="edd_' . $this->plugin_slug . '_license_deactivate" value="' . __( 'Deactivate License', 'ebbp-textdomain' ) . '" />';
				$buttons .= ' <input type="button" class="button-primary" name="edd_' . $this->plugin_slug . '_license_renew" value="' . __( 'Renew License', 'ebbp-textdomain' ) . '" onclick="window.open( \'' . $renew_link . '\' )"/>';
			} else {
				$buttons = '<input type="submit" class="button-primary" name="edd_' . $this->plugin_slug . '_license_activate" value="' . __( 'Activate License', 'ebbp-textdomain' ) . '"/>';
			}

			$info = array(
				'plugin_name'      => $this->plugin_name,
				'plugin_slug'      => $this->plugin_slug,
				'license_key'      => $license_key_html,
				'license_status'   => $license_status,
				'activate_license' => $nonce . $buttons,
			);

			$licensing_info[] = $info;

			return $licensing_info;
		}

		/**
		 * Function displayes licensing status.
		 *
		 * @param string $status Licenisng status.
		 * @param string $display Display the message or not.
		 */
		public function displayLicenseStatus( $status, $display ) {
			$status_option = get_option( 'edd_' . $this->plugin_slug . '_license_status' );
			$text_color    = 'red';
			if ( false !== $status && 'valid' === $status ) {
				$text_color = 'green';
				$txt_status = __( 'Active', 'ebbp-textdomain' );
			} elseif ( 'site_inactive' === $status_option ) {
				$txt_status = __( 'Not Active', 'ebbp-textdomain' );
			} elseif ( 'expired' === $status_option && ( ! empty( $display ) || '' !== $display ) ) {
				$txt_status = __( 'Expired', 'ebbp-textdomain' );
			} elseif ( 'expired' === $status_option ) {
				$txt_status = __( 'Expired', 'ebbp-textdomain' );
			} elseif ( 'invalid' === $status_option ) {
				$txt_status = __( 'Invalid Key', 'ebbp-textdomain' );
			} else {
				$txt_status = __( 'Not Active ', 'ebbp-textdomain' );
			}
			return "<span style='color:$text_color;'>{$txt_status}</span>";
		}
	}
}
