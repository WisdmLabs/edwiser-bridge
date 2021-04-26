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
		private $products_data = array(
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

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->addon_licensing = array( 'test' );
			$this->_id             = 'licensing_sol';
			$this->label           = __( 'Licenses Sol', 'eb-textdomain' );

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
			$store_url                   = 'https://edwiser.org';
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
					<button class="button-primary eb-activate-plugin" name="activate_plugin" type='submit' value="<?php echo esc_attr( $plugin ); ?>"><?php esc_attr_e( 'Activate Plugin', 'eb-textdomain' ); ?></button>
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
			$renew_link = get_option( 'eb_' . $plugin_slug . '_product_site' );
			$status     = get_option( 'edd_' . $plugin_slug . '_license_status' );
			include_once plugin_dir_path( __FILE__ ) . 'eb-get-plugin-data.php';
			$active_site = EbGetPluginData::get_site_list( $plugin_slug );

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
						$resp_data = array();
						break;
					default:
						$resp_data['msg'] = __( 'Invalid action.', 'eb-textdomain' );
						break;
				}
			}
			$resp_data['msg'] = apply_filters( 'eb_setting_messages', $resp_data['msg'] );
			if ( $action ) {
				?>
				<div class="notice <?php echo esc_attr( $resp_data['notice_class'] ); ?> is-dismissible">
					<p><?php echo $resp_data['msg']; ?></p>
				</div>
				<?php
			}
		}

		/**
		 * Function to manage the plugin license activation and deactivation.
		 *
		 * @param array  $data License activation/deactivation request data.
		 * @param  string $action Name of the action like activate/deactivate.
		 */
		private function manage_license( $data, $action ) {
			$resp = array(
				'msg'          => '',
				'notice_class' => 'notice-error',
			);
			include_once plugin_dir_path( __FILE__ ) . 'eb-licensing-manager.php';
			$plugin_data        = $this->products_data[ $data['action'] ];
			$plugin_data['url'] = get_home_url();
			$plugin_data['key'] = $data[ $plugin_data['key'] ];
			$license_maanger    = new EbLicensingManger( $plugin_data );
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
