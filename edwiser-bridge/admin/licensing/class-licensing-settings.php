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

if ( ! class_exists( 'Eb_Settings_Licensing' ) ) :

	/**
	 * Eb_Settings_Licensing.
	 */
	class Licensing_Settings extends EBSettingsPage {

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
			$this->label           = __( 'Licenses', 'eb-textdomain' );
			if ( ! class_exists( 'Eb_Licensing_Manger' ) ) {
				include_once plugin_dir_path( __FILE__ ) . 'class-eb-licensing-manager.php';
			}
			if ( ! class_exists( 'Eb_Get_Plugin_Data' ) ) {
				include_once plugin_dir_path( __FILE__ ) . 'class-eb-get-plugin-data.php';
			}
			$this->products_data = Eb_Licensing_Manger::get_plugin_data();
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
			$store_url                   = Eb_Licensing_Manger::$store_url;
			$author_name                 = 'WisdmLabs';
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
				$status = __( 'Active', 'ebbp-textdomain' );
			} elseif ( 'site_inactive' === $status_option ) {
				$status = __( 'Not Active', 'ebbp-textdomain' );
			} elseif ( 'expired' === $status_option && ( ! empty( $display ) || '' !== $display ) ) {
				$status = __( 'Expired', 'ebbp-textdomain' );
			} elseif ( 'expired' === $status_option ) {
				$status = __( 'Expired', 'ebbp-textdomain' );
			} elseif ( 'invalid' === $status_option ) {
				$status = __( 'Invalid Key', 'ebbp-textdomain' );
			} else {
				$status = __( 'Not Active ', 'ebbp-textdomain' );
			}
			?>
			<span class="eb_lic_status eb_lic_<?php echo esc_attr( $class ); ?>"><?php echo esc_attr( $status ); ?></span>
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
				$readonly = 'readonly="readonly"';
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
				<input class="button-primary" type="submit" name="install_plugin" value="<?php esc_attr_e( 'Install Plugin', 'eb-textdomain' ); ?>">
				<?php
			} elseif ( ! is_plugin_active( $plugin['path'] ) ) {
				$action = 'activate_plugin';
				?>
					<button class="button-primary eb-activate-plugin" name="activate_plugin" type='submit' value="<?php echo esc_attr( $plugin['path'] ); ?>"><?php esc_attr_e( 'Activate Plugin', 'eb-textdomain' ); ?></button>
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
					<input type="submit" class="button-primary" name="deactivate_license" value="<?php esc_attr_e( 'Deactivate License', 'ebbp-textdomain' ); ?>"/>
					<?php
			} elseif ( 'expired' === $status && ( ! empty( $display ) || '' !== $display ) ) {
				$action = 'activate_license';
				?>
					<input type="submit" class="button-primary" name="deactivate_license" value="<?php esc_attr_e( 'Deactivate License', 'ebbp-textdomain' ); ?>" />
					<input type="button" class="button-primary" name="renew_license" value="<?php esc_attr_e( 'Renew License', 'ebbp-textdomain' ); ?>" onclick="window.open( \'' . $renew_link . '\' )"/>
					<?php
			} elseif ( 'expired' === $status ) {
				$action = 'deactivate_license';
				?>
					<input type="submit" class="button-primary" name="deactivate_license" value="<?php esc_attr_e( 'Deactivate License', 'ebbp-textdomain' ); ?>" />
					<input type="button" class="button-primary" name="renew_license" value="<?php esc_attr_e( 'Renew License', 'ebbp-textdomain' ); ?>" onclick="window.open( \'' . $renew_link . '\' )"/>
					<?php
			} else {
				$action = 'activate_license';
				?>
					<input type="submit" class="button-primary" name="activate_license" value="<?php esc_attr_e( 'Activate License', 'ebbp-textdomain' ); ?>"/>
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
				'msg'          => __( 'Security check failed.', 'eb-textdomain' ),
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
						$resp_data = $this->wdm_install_plugin( $post_data );
						break;
					default:
						$resp_data['msg'] = __( 'Invalid action.', 'eb-textdomain' );
						break;
				}
			}
			$plugin_error = '';
			if ( $action && ! empty( $resp_data['msg'] ) ) {
				ob_start();
				?>
				<div class="notice <?php echo esc_attr( $resp_data['notice_class'] ); ?> is-dismissible">
					<p><?php echo esc_attr( $resp_data['msg'] ); ?></p>
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
		 */
		private function wdm_install_plugin( $post_data ) {
			$resp                      = array(
				'msg'          => '',
				'notice_class' => 'notice-error',
			);
			$slug                      = $post_data['action'];
			$plugin_data               = $this->products_data[ $slug ];
			$plugin_data['edd_action'] = 'get_version';
			$l_key_name                = $plugin_data['key'];
			$l_key                     = trim( $post_data[ $l_key_name ] );
			$plugin_data['license']    = $l_key;
			update_option( $l_key_name, $l_key );
			if ( empty( $plugin_data['license'] ) ) {
				$resp['msg'] = __( 'License key cannot be empty, Please enter the valid license key.', 'eb-textdomain' );
				return $resp;
			}
			$request = wp_remote_get(
				add_query_arg( $plugin_data, Eb_Licensing_Manger::$store_url ),
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'blocking'  => true,
				)
			);

			if ( ! is_wp_error( $request ) ) {
				$request = json_decode( wp_remote_retrieve_body( $request ) );
				if ( isset( $request->msg ) ) {
					$resp['msg'] = $request->msg;
				}
				if ( $request && isset( $request->download_link ) && ! empty( $request->download_link ) ) {
					$installed = $this->install_plugin( $request->download_link );
					if ( is_wp_error( $installed ) ) {
						$resp['msg'] = $installed->get_error_messages();
					} elseif ( $installed ) {
						$this->manage_license( $post_data, 'activate' );
						$resp['msg']          = __( 'Plugin installed and activated sucessfully.', 'eb-textdomain' );
						$resp['notice_class'] = 'notice-success';
					} else {
						$resp['msg'] = __( 'Plugin installation failed.', 'eb-textdomain' );
					}
				} else {
					$resp['msg'] = __( 'Empty download link. Please contact edwiser support for more detials.', 'eb-textdomain' );
				}
			}
			return $resp;
		}

		/**
		 * Function to install the plugin.
		 *
		 * @param string $plugin_zip Plugin zip file url.
		 */
		private function install_plugin( $plugin_zip ) {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			wp_cache_flush();
			$upgrader  = new \Plugin_Upgrader();
			$installed = $upgrader->install( $plugin_zip );
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
			$license_maanger    = new Eb_Licensing_Manger( $plugin_data );
			if ( 'activate' === $action ) {
				$license_maanger->activate_license();
			} else {
				$license_maanger->deactivate_license();
			}
			return $resp;
		}
		/**
		 * Callback for the ajax eb_activate_plugin which will activate the plugin.
		 *
		 * @param array $data Request data.
		 */
		private function wdm_eb_activate_plugin( $data ) {
			$resp   = array(
				'msg'          => '',
				'notice_class' => 'notice-error',
			);
			$result = activate_plugin( $data['activate_plugin'] );
			if ( is_wp_error( $result ) ) {
				$resp['msg']          = $result->get_error_messages();
				$resp['notice_class'] = 'notice-error';
			} else {
				if ( 'valid' !== get_option( 'edd_' . $data['action'] . '_license_status' ) ) {
					$this->manage_license( $data, 'activate' );
				}
				$resp['msg']          = __( 'Plugin activated successfully.', 'eb-textdomain' );
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
						'title' => __( 'Licenses', 'eb-textdomain' ),
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
	}

endif;

return new Licensing_Settings();