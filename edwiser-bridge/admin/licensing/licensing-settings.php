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
		 * @param string $key_name License key option name.
		 * @return string license status.
		 */
		private function get_license_status( $key_name ) {
			$status = 'inactive';
			return $status;
		}

		/**
		 * Function to get the licensing form actions.
		 *
		 * @param string $key Plugin license key option name.
		 * @param string $plugin Plugin file path.
		 */
		private function get_license_buttons( $key, $plugin ) {
			$action = '';
			if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
				$action = 'install_plugin';
				?>
				<input class="button-primary" type="submit" name="install_plugin" value="<?php esc_attr_e( 'Install Plugin', 'eb-textdomain' ); ?>">
				<?php
			} elseif ( ! is_plugin_active( $plugin ) ) {
				$action = 'activate_plugin';
				?>
					<button class="button-primary eb-activate-plugin" name="activate_plugin" type='submit' value="<?php echo esc_attr( $plugin ); ?>"><?php esc_attr_e( 'Activate Plugin', 'eb-textdomain' ); ?></button>
				<?php
			} elseif ( 'inactive' === $this->get_license_status( $key ) ) {
				$action = 'activate_license';
				?>
					<input class="button-primary" type="submit" name="activate_license" value="<?php esc_attr_e( 'Activate License', 'eb-textdomain' ); ?>">
				<?php
			} else {
				$action = 'deactivate_license';
				?>
					<input class="button-primary" type="submit" name="deactivate_license" value="<?php esc_attr_e( 'Deactivate License', 'eb-textdomain' ); ?>">
				<?php
			}
			?>
			<input type="hidden" name="licence_action" value="<?php echo esc_attr( $action ); ?>">
			<?php
		}

		/**
		 * Function handles the licensing form submission.
		 */
		private function license_form_submission_handler() {
			$post_data = wp_unslash( $_POST );
			error_log( print_r( $post_data, 1 ) );
			$resp_data = array(
				'msg'          => __( 'Security check failed.', 'eb-textdomain' ),
				'notice_class' => 'notice-error',
			);
			$action    = isset( $post_data['action'] ) ? sanitize_text_field( $post_data['action'] ) : false;
			if ( $action && isset( $post_data['activate_plugin'] ) && isset( $post_data[ $action ] ) && wp_verify_nonce( $post_data[ $action ], 'eb-licence-nonce' ) ) {
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
						break;
				}
			}
			if ( $action ) {
				?>
				<div class="notice <?php echo esc_attr( $resp_data['notice_class'] ); ?> is-dismissible">
					<p><?php echo esc_attr( $resp_data['msg'] ); ?></p>
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
			$resp        = array(
				'msg'          => '',
				'notice_class' => 'notice-error',
			);
			$plugin_data = $this->products_data[ $data['action'] ];

			if ( 'activate' === $action ) {
				// To do.
			} else {
				// To do.
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
