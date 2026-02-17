<?php
/**
 * EDW Licensing Management
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Licensing_Settings' ) ) :

	/**
	 * Eb_Settings_Licensing.
	 */
	class Licensing_Settings extends EB_Settings_Page {

		/**
		 * Addon licensing.
		 *
		 * @var text $addon_licensing addon licensing.
		 */
		public $addon_licensing;

		/**
		 * Defines the licensing data.
		 *
		 * @var array
		 */
		private $products_data = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->addon_licensing = array( 'licensing' );
			$this->_id             = 'licensing';
			$this->label           = __( 'Licenses', 'edwiser-bridge' );
			if ( ! class_exists( 'Eb_Licensing_Manager' ) ) {
				include_once plugin_dir_path( __FILE__ ) . 'class-eb-licensing-manager.php';
			}
			if ( ! class_exists( 'Eb_Get_Plugin_Data' ) ) {
				include_once plugin_dir_path( __FILE__ ) . 'class-eb-get-plugin-data.php';
			}
			$this->products_data = Eb_Licensing_Manager::get_plugin_data();
			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			// Hide the save button.
			$GLOBALS['hide_save_button'] = true;
			$setting_messages            = $this->license_form_submission_handler();
			$plugin_path                 = plugin_dir_path( __DIR__ );
			$store_url                   = Eb_Licensing_Manager::$store_url;
			$author_name                 = 'WisdmLabs';
			$bridge_pro                  = get_option( 'edd_edwiser_bridge_pro_license_status' );
			if ( 'valid' === $bridge_pro ) {
				$bridge_pro = true;
			} else {
				$bridge_pro = false;
			}
			require_once $plugin_path . 'licensing/html-licensing.php';
		}

		/**
		 * Function to get the license key.
		 *
		 * @param string $key_name License key option name.
		 * @return string license key.
		 */
		private function get_licence_key( $key_name ) {
			$license_key = trim( get_option( $key_name ) );
			return $license_key ? $license_key : '';
		}

		/**
		 * Function to get the license status.
		 *
		 * @param string $plugin_slug plugin slug name.
		 */
		private function get_license_status( $plugin_slug ) {
			$status_option = get_option( 'edd_' . $plugin_slug . '_license_status' );
			$class         = '';
			$active_site   = Eb_Get_Plugin_Data::get_site_list( $plugin_slug );
			$display       = '';
			if ( ! empty( $active_site ) || '' !== $active_site ) {
				$display = '<ul>' . $active_site . '</ul>';
			}

			if ( false !== $status_option && 'valid' === $status_option ) {
				$class  = 'active';
				$status = __( 'Active', 'edwiser-bridge' );
			} elseif ( 'site_inactive' === $status_option ) {
				$class  = 'not_active';
				$status = __( 'Not Active', 'edwiser-bridge' );
			} elseif ( 'expired' === $status_option && ( ! empty( $display ) || '' !== $display ) ) {
				$class  = 'expired';
				$status = __( 'Expired', 'edwiser-bridge' );
			} elseif ( 'expired' === $status_option ) {
				$class  = 'expired';
				$status = __( 'Expired', 'edwiser-bridge' );
			} elseif ( 'invalid' === $status_option ) {
				$class  = 'expired';
				$status = __( 'Invalid Key', 'edwiser-bridge' );
			} else {
				$class  = 'not_active';
				$status = __( 'Not Active ', 'edwiser-bridge' );
			}

			if ( 'edwiser_bridge_pro' === $plugin_slug ) {
				$class = 'eb_pro_lic_' . $class;
			} else {
				$class = 'eb_lic_' . $class;
			}
			?>
			<span class="eb_lic_status <?php echo esc_attr( $class ); ?> eb_pro_lic_<?php echo esc_attr( $class ); ?>"><?php echo esc_attr( $status ); ?></span>
			<?php
		}

		/**
		 * Function will check license key status and mark the input as readonly.
		 *
		 * @param  mixed $slug Plugin slug.
		 */
		private function is_readonly_key( $slug ) {
			$status   = get_option( 'edd_' . $slug . '_license_status' );
			$readonly = '';
			if ( 'valid' === $status || 'expired' === $status ) {
				$readonly = 'readonly=readonly';
			}
			return $readonly;

		}

		/**
		 * Function to get the licensing form actions.
		 *
		 * @param array $plugin_slug Plugin slug.
		 */
		private function get_license_buttons( $plugin_slug ) {
			$action = '';
			$plugin = $this->products_data[ $plugin_slug ];
			if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin['path'] ) ) {
				$action = 'install_plugin';
				?>
				<span class="button-primary get_license_key wdm_eb_get_key_popup_btn"><?php esc_attr_e( 'Get License Key', 'edwiser-bridge' ); ?></span>
				<input class="button-primary install_plugin" type="submit"  name="install_plugin" value="<?php esc_attr_e( 'Install Plugin', 'edwiser-bridge' ); ?>">
				<?php
			} elseif ( ! is_plugin_active( $plugin['path'] ) ) {
				$action = 'activate_plugin';
				?>
					<a href="https://edwiser.org/my-account/"  class="button-primary get_license_key" target="_blank"><?php esc_attr_e( 'Get License Key', 'edwiser-bridge' ); ?></a>
					<button class="button-primary eb-activate-plugin" name="activate_plugin" type='submit' value="<?php echo esc_attr( $plugin['path'] ); ?>"><?php esc_attr_e( 'Activate Plugin', 'edwiser-bridge' ); ?></button>
				<?php
			} else {
				$action = $this->get_license_status_button( $plugin_slug, $action );
			}
			?>
			<input type="hidden" name="licence_action" value="<?php echo esc_attr( $action ); ?>">
			<?php
		}

		/**
		 * Function to get the activation/deactivation button.
		 *
		 * @param string $plugin_slug plugin slug.
		 * @param string $action name of the action.
		 */
		private function get_license_status_button( $plugin_slug, $action ) {
			$renew_link  = get_option( 'eb_' . $plugin_slug . '_product_site' );
			$status      = get_option( 'edd_' . $plugin_slug . '_license_status' );
			$active_site = Eb_Get_Plugin_Data::get_site_list( $plugin_slug );

			$display = '';
			if ( ! empty( $active_site ) || '' !== $active_site ) {
				$display = '<ul>' . $active_site . '</ul>';
			}
			if ( false !== $status && 'valid' === $status ) {
				$action = 'deactivate_license';
				?>
					<input type="submit" class="eb-license-button eb-deactive" name="deactivate_license" value="<?php esc_attr_e( 'Deactivate', 'edwiser-bridge' ); ?>"/>
					<?php
			} elseif ( 'expired' === $status && ( ! empty( $display ) || '' !== $display ) ) {
				$action = 'activate_license';
				?>
					<input type="submit" class="eb-license-button eb-deactive" name="deactivate_license" value="<?php esc_attr_e( 'Deactivate', 'edwiser-bridge' ); ?>" />
					<input type="button" class="eb-license-button eb-deactive" name="renew_license" value="<?php esc_attr_e( 'Renew License', 'edwiser-bridge' ); ?>" onclick="window.open( \'' . $renew_link . '\' )"/>
					<?php
			} elseif ( 'expired' === $status ) {
				$action = 'deactivate_license';
				?>
					<input type="submit" class="eb-license-button eb-deactive" name="deactivate_license" value="<?php esc_attr_e( 'Deactivate', 'edwiser-bridge' ); ?>" />
					<input type="button" class="eb-license-button eb-deactive" name="renew_license" value="<?php esc_attr_e( 'Renew License', 'edwiser-bridge' ); ?>" onclick="window.open( \'' . $renew_link . '\' )"/>
					<?php
			} else {
				$action = 'activate_license';
				?>
					<a href="https://edwiser.org/my-account/" class="eb-license-button eb-active get_license_key" target="_blank"><?php esc_attr_e( 'Get License Key', 'edwiser-bridge' ); ?></a>
					<input type="submit" class="eb-license-button eb-active activate_license" name="activate_license" value="<?php esc_attr_e( 'Activate', 'edwiser-bridge' ); ?>"/>
					<?php
			}

			return $action;
		}

		/**
		 * Function handles the licensing form submission.
		 */
		private function license_form_submission_handler() {
			$post_data = wp_unslash( $_POST );
			$resp_data = array(
				'msg'          => __( 'Security check failed.', 'edwiser-bridge' ),
				'notice_class' => 'notice-error',
			);
			$action    = isset( $post_data['action'] ) ? sanitize_text_field( $post_data['action'] ) : false;
			if ( $action && isset( $post_data['licence_action'] ) && isset( $post_data[ $action ] ) && wp_verify_nonce( $post_data[ $action ], 'eb-licence-nonce' ) ) {
				switch ( $post_data['licence_action'] ) {
					case 'activate_license':
						$resp_data = $this->manage_license( $post_data, 'activate' );
						break;
					case 'deactivate_license':
						$resp_data = $this->manage_license( $post_data, 'deactivate' );
						break;
					case 'activate_plugin':
						$resp_data = $this->wdm_eb_activate_plugin( $post_data );
						break;
					case 'install_plugin':
						$resp_data = $this->manage_license( $post_data, 'activate' );
						$resp_data = $this->wdm_install_plugin( $post_data );
						break;
					default:
						$resp_data['msg'] = __( 'Invalid action.', 'edwiser-bridge' );
						break;
				}
			}
			$plugin_error = '';
			if ( $action && ! empty( $resp_data['msg'] ) ) {
				ob_start();
				?>
				<div class="notice <?php echo esc_attr( $resp_data['notice_class'] ); ?> is-dismissible">
					<p><?php echo wp_kses_post( $resp_data['msg'] ); ?></p>
				</div>
				<?php
				$plugin_error = ob_get_clean();
			}
			$plugin_error = apply_filters( 'eb_license_setting_messages', $plugin_error );
			echo wp_kses_post( $plugin_error );
		}

		/**
		 * Function to install the plugin.
		 *
		 * @param  mixed $post_data Installation reuqest data.
		 * @param  bool  $flush_content   Whether to flush content or not.
		 */
		public function wdm_install_plugin( $post_data, $flush_content = 1 ) {
			$resp = array(
				'msg'          => '',
				'notice_class' => 'notice-error',
			);
			$slug = $post_data['action'];

			// removed dependency check during install plugin action.
			// $chec_plugin_dep = $this->check_plugin_dependancy( $slug );
			// if ( false !== $chec_plugin_dep ) {
			// $resp['msg'] = $chec_plugin_dep;
			// return $resp;
			// }.

			$plugin_data               = $this->products_data[ $slug ];
			$plugin_data['edd_action'] = 'get_version';
			$l_key_name                = $plugin_data['key'];
			$l_key                     = trim( $post_data[ $l_key_name ] );
			$plugin_data['license']    = $l_key;
			update_option( $l_key_name, $l_key );

			if ( empty( $plugin_data['license'] ) ) {
				$get_l_key_link = '<a href="https://edwiser.org/bridge-wordpress-moodle-integration/#pricing">' . __( 'Click here', 'edwiser-bridge' ) . '</a>';
				$resp['msg']    = __( 'License key cannot be empty, Please enter the valid license key.', 'edwiser-bridge' ) . $get_l_key_link . __( ' to get the license key.', 'edwiser-bridge' );
				return $resp;
			}
			$request = wp_remote_get(
				add_query_arg( $plugin_data, Eb_Licensing_Manager::$store_url ),
				array(
					'timeout'    => 15,
					'sslverify'  => false,
					'blocking'   => true,
					'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
				)
			);

			if ( ! is_wp_error( $request ) ) {
				$request = json_decode( wp_remote_retrieve_body( $request ) );
				if ( $request && isset( $request->download_link ) && ! empty( $request->download_link ) ) {
					$installed = $this->install_plugin( $request->download_link, $flush_content );
					if ( is_wp_error( $installed ) ) {
						$resp['msg'] = $installed->get_error_messages();
					} elseif ( $installed ) {
						$status = get_option( 'edd_' . $slug . '_license_status' );
						/*if ( 'valid' === $status || 'expired' === $status ) { @codingStandardsIgnoreLine
							$this->manage_license( $post_data, 'activate' );
						}*/
						$resp['msg']          = __( 'Plugin installed sucessfully.', 'edwiser-bridge' );
						$resp['notice_class'] = 'notice-success';
					} else {
						$resp['msg'] = __( 'Plugin installation failed.', 'edwiser-bridge' );
					}
				} elseif ( isset( $request->msg ) ) {
					$resp['msg'] = $request->msg;
				} else {
					$resp['msg'] = __( 'Empty download link. Please check your license key or contact edwiser support for more detials.', 'edwiser-bridge' );
				}
			}
			return $resp;
		}

		/**
		 * Function checks is the dependancy plugin is installed and active or not.
		 *
		 * @param  string $slug Plugin slug to check with.
		 * @return mixed Returns the message if dependacy failes otherwise false.
		 */
		private function check_plugin_dependancy( $slug ) {
			$msg = false;
			if ( 'bulk-purchase' === $slug && ! is_plugin_active( 'woocommerce-integration/bridge-woocommerce.php' ) ) {
				$msg = __( 'Please installed and activate Edwiser WooCommerce Integration plugin first.', 'edwiser-bridge' );
			} elseif ( 'woocommerce_integration' === $slug && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				$msg = __( 'Please installed and activate WooCommerce plugin first.', 'edwiser-bridge' );
			}
			return $msg;
		}

		/**
		 * Function to install the plugin.
		 *
		 * @param string $plugin_zip Plugin zip file url.
		 * @param bool   $flush Whether to flush content or not.
		 */
		private function install_plugin( $plugin_zip, $flush = 1 ) {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			// clear_update_cache.
			if ( $flush ) {
				wp_cache_flush();
				$args = array(
					'clear_update_cache' => true,
				);
			} else {
				$args = array(
					'clear_update_cache' => false,
				);
			}

			$upgrader  = new \Plugin_Upgrader();
			$installed = $upgrader->install( $plugin_zip, $args );
			return $installed;
		}

		/**
		 * Function to manage the plugin license activation and deactivation.
		 *
		 * @param array  $data License activation/deactivation request data.
		 * @param  string $action Name of the action like activate/deactivate.
		 */
		private function manage_license( $data, $action ) {

			$resp               = array(
				'msg'          => '',
				'notice_class' => 'notice-error',
			);
			$plugin_data        = $this->products_data[ $data['action'] ];
			$plugin_data['url'] = get_home_url();
			$plugin_data['key'] = $data[ $plugin_data['key'] ];
			$license_manager    = new Eb_Licensing_Manager( $plugin_data );
			if ( 'activate' === $action ) {
				$license_manager->activate_license();
			} else {
				$license_manager->deactivate_license();
			}
			return $resp;
		}
		/**
		 * Callback for the ajax eb_activate_plugin which will activate the plugin.
		 *
		 * @param array $data Request data.
		 */
		private function wdm_eb_activate_plugin( $data ) {
			$resp            = array(
				'msg'          => '',
				'notice_class' => 'notice-error',
			);
			$chec_plugin_dep = $this->check_plugin_dependancy( $data['action'] );
			if ( false !== $chec_plugin_dep ) {
				$resp['msg'] = $chec_plugin_dep;
				return $resp;
			}
			$result = activate_plugin( $data['activate_plugin'] );
			if ( is_wp_error( $result ) ) {
				$resp['msg']          = $result->get_error_messages();
				$resp['notice_class'] = 'notice-error';
			} else {
				if ( 'valid' !== get_option( 'edd_' . $data['action'] . '_license_status' ) ) {
					$resp = $this->manage_license( $data, 'activate' );
				}
				$resp['msg']          = __( 'Plugin activated successfully.', 'edwiser-bridge' );
				$resp['notice_class'] = 'notice-success';
			}
			return $resp;
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.0.0
		 * @param text $current_section current section.
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			$settings = apply_filters(
				'eb_licensing',
				array(
					array(
						'title' => __( 'Licenses', 'edwiser-bridge' ),
						'type'  => 'title',
						'id'    => 'licensing_management',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'licensing_management',
					),
				)
			);
			return apply_filters( 'eb_get_settings_' . $this->_id, $settings, $current_section );
		}

		/**
		 * Get license key button pop-up.
		 */
		public function eb_license_pop_up_data() {
			?>
			<div id="eb_get_license_key_dialog" style="display:none;">
				<ul>
					<li class="eb_get_license">
						<?php esc_html_e( 'If you already own a license then click here', 'edwiser-bridge' ); ?>
						<a href="https://edwiser.org/my-account/" target="_blank" class="button-primary"> <?php esc_html_e( 'Get License', 'edwiser-bridge' ); ?> </a>
					</li>

					<li class="eb_buy_license">
						<?php esc_html_e( 'If you wish to purchase Edwiser Bridge PRO then click here', 'edwiser-bridge' ); ?>
						<a href="https://edwiser.org/bridge-wordpress-moodle-integration/?utm_source=bridge%20plugin&utm_medium=in%20product&utm_campaign=upgrade#downloadfree" target="_blank" class="button-primary"> <?php esc_html_e( 'Buy License', 'edwiser-bridge' ); ?> </a>
					</li>

					<li class="eb_upgrade_license">
						<?php esc_html_e( 'If you already own one of the extensions, then click here to upgrade to Edwiser Bridge PRO', 'edwiser-bridge' ); ?>
						<a href="https://edwiser.org/my-account/" target="_blank" class="button-primary"> <?php esc_html_e( 'Upgrade License', 'edwiser-bridge' ); ?> </a>
					</li>
				</ul>
			</div>
			<?php
		}
	}

endif;

return new Licensing_Settings();
