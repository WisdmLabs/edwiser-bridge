<?php
/**
 * Selective Sync Module
 * This class is responsible for selective sync module.
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks for selective sync module.
 *
 * @link       http://edwiser.org
 * @since      3.0.0
 * @package    Edwiser Bridge Pro
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Pro_Plugins_Settings' ) ) {
	/**
	 * Edwiser Bridge Pro Plugins tab class
	 */
	class Eb_Settings_Pro_Featuers extends EB_Settings_Page {

		/**
		 * Addon licensing.
		 *
		 * @var text $addon_licensing addon licensing.
		 */
		public $plugin_licensing_data;

		/**
		 * Edwiser Pro plugin data.
		 *
		 * @var array
		 */
		public $plugin_activation_data = array();

		/**
		 * All Bridge Plugins.
		 *
		 * @var array
		 */
		public $plugin_data = array();

		/**
		 * Bridge Pro.
		 *
		 * @var bool
		 */
		public $bridge_pro = false;

		/**
		 * Settings page constructor.
		 */
		public function __construct() {
			$this->addon_licensing = array( 'pro-features' );
			$this->_id             = 'pro_features';
			$this->label           = __( 'Pro Features', 'edwiser-bridge' );

			$this->plugin_licensing_data = get_option( 'edd_edwiser_bridge_pro_license_addon_data' );
			if ( ! is_array( $this->plugin_licensing_data ) ) {
				$this->plugin_licensing_data = array();
			}

			$this->plugin_activation_data = get_option( 'eb_pro_modules_data' );
			if ( ! is_array( $this->plugin_activation_data ) ) {
				$this->plugin_activation_data = array();
			}

			if ( ! class_exists( 'Eb_Licensing_Manager' ) ) {
				include_once WP_PLUGIN_DIR . '/edwiser-bridge/admin/licensing/class-eb-licensing-manager.php';
			}

			$license = get_option( 'edd_edwiser_bridge_pro_license_status' );
			if ( ! is_plugin_active( 'edwiser-bridge-pro/edwiser-bridge-pro.php' ) || 'valid' !== $license ) {
				$this->bridge_pro = false;
			} else {
				$this->bridge_pro = true;
			}

			$this->plugin_data = self::get_plugin_data();
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
			$setting_messages            = $this->pro_plugin_form_submission_handler();
			$plugin_path                 = plugin_dir_path( __DIR__ );
			$store_url                   = Eb_Licensing_Manager::$store_url;
			$author_name                 = 'WisdmLabs';
			$bridge_pro                  = get_option( 'edd_edwiser_bridge_pro_license_status' );
			if ( 'valid' === $bridge_pro ) {
				$bridge_pro = true;
			} else {
				$bridge_pro = false;
			}
			require_once $plugin_path . 'partials/html-pro-featuers.php';
		}

		/**
		 * Edwiser Bridge Pro plugin data.
		 * This function is used to get all the plugin data.
		 *
		 * @since  1.0.0
		 * @param  string $plugin_slug plugin slug.
		 * @return array
		 */
		public static function get_plugin_data( $plugin_slug = false ) {
			$plugin_data = array(
				'sso'             => array(
					'slug'        => 'single_sign_on',
					'item_name'   => 'Edwiser Bridge Single Sign On',
					'description' => __( 'Experience seamless login synchronization between Moodle and WordPress, eliminating login hassles and saving time for learners.', 'edwiser-bridge' ),
					'setting_url' => admin_url( 'admin.php?page=eb-settings&tab=sso_settings_general' ),
				),
				'woo_integration' => array(
					'slug'        => 'woocommerce_integration',
					'item_name'   => 'WooCommerce Integration',
					'description' => __( 'Effortlessly sell Moodle courses on WordPress with WooCommerce, optimizing pages and integrating with Moodle LMS.', 'edwiser-bridge' ),
					'setting_url' => admin_url( 'admin.php?page=eb-settings&tab=woo_int_settings' ),
				),
				'bulk_purchase'   => array(
					'slug'        => 'bulk-purchase',
					'item_name'   => 'Bulk Purchase',
					'description' => __( 'Create a loyal user base by offering course bundles, increasing earnings and user satisfaction through discounts.', 'edwiser-bridge' ),
					'setting_url' => admin_url( 'admin.php?page=eb-settings&tab=general' ),
				),
				'selective_sync'  => array(
					'slug'        => 'selective_sync',
					'item_name'   => 'Selective Synchronization',
					'description' => __( 'Flexiblity to choose specific courses to sync. Save time by choosing to sync only updated courses, course categories and users.', 'edwiser-bridge' ),
					'setting_url' => admin_url( 'admin.php?page=eb-settings&tab=selective_synch_settings' ),
				),
				'custom_fields'   => array(
					'slug'        => 'edwiser_custom_fields',
					'item_name'   => 'Edwiser Bridge Custom Fields',
					'description' => __( 'Enhance registration and checkout forms with Custom Fields in WordPress, WooCommerce, and Edwiser Bridge for personalized information collection.', 'edwiser-bridge' ),
					'setting_url' => admin_url( 'edit.php?post_type=eb_course&page=eb-custom-fields' ),
				),
			);
			if ( $plugin_slug ) {
				$plugin_data = $plugin_data[ $plugin_slug ];
			}
			return $plugin_data;
		}

		/**
		 * Pro plugin form submission handler.
		 *
		 * @since  1.0.0
		 */
		public function pro_plugin_form_submission_handler() {
			$resp_data = array(
				'msg'          => __( 'Security check failed.', 'edwiser-bridge' ),
				'notice_class' => 'notice-error',
			);
			$post_data = wp_unslash( $_POST );
			// nonce verification.
			$action = isset( $post_data['action'] ) ? sanitize_text_field( $post_data['action'] ) : false;
			if ( $action && isset( $post_data[ $action ] ) && wp_verify_nonce( $post_data[ $action ], 'eb-licence-nonce' ) ) {
				$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
				$status = isset( $_POST['activate_plugin'] ) ? 'active' : 'deactive';

				$woo_path = 'woocommerce/woocommerce.php';

				do_action( 'eb_check_mdl_plugin_update' );

				$moodle_pro = get_option( 'moodle_edwiser_bridge_pro' );

				if ( 'bulk_purchase' === $action && 'active' === $status && 'active' !== $this->plugin_activation_data['woo_integration'] ) {
					$resp_data['msg'] = __( 'Bulk Purchase requires WooCommerce Integration to be active.', 'edwiser-bridge' );
				} elseif ( 'bulk_purchase' === $action && 'active' === $status && 'available' !== $moodle_pro ) {
					$resp_data['msg'] = __( 'Moodle Edwiser Pro license is not active, to use bulk purchase functionality activate moodle edwiser bridge pro license.', 'edwiser-bridge' );
				} elseif ( 'sso' === $action && 'active' === $status && 'available' !== $moodle_pro ) {
					$resp_data['msg'] = __( 'Moodle Edwiser Pro license is not active, to use Single Sign On functionality activate moodle edwiser bridge pro license.', 'edwiser-bridge' );
				} elseif ( 'woo_integration' === $action && 'deactive' === $status && 'active' === $this->plugin_activation_data['bulk_purchase'] ) {
					$this->plugin_activation_data[ $action ]       = $status;
					$this->plugin_activation_data['bulk_purchase'] = 'deactive';
					update_option( 'eb_pro_modules_data', $this->plugin_activation_data );

					$resp_data['msg'] = __( 'Bulk Purchase has been deactivated.', 'edwiser-bridge' );
				} elseif ( 'woo_integration' === $action && 'active' === $status && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					$resp_data['msg'] = __( 'WooCommerce Integration requires WooCommerce to be active.', 'edwiser-bridge' );
				} else {

					$this->plugin_activation_data[ $action ] = $status;
					update_option( 'eb_pro_modules_data', $this->plugin_activation_data );
					$resp_data['msg'] = '';
				}
			} else {
				$resp_data['msg'] = __( 'Security check failed.', 'edwiser-bridge' );
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
		 * Check if plugin is active or not.
		 *
		 * @since 1.0.0
		 * @param string $plugin_slug plugin slug.
		 */
		public function is_plugin_active( $plugin_slug ) {
			// if pro license is activated.
			if ( ! $this->bridge_pro ) {
				return false;
			}
			if ( isset( $this->plugin_activation_data[ $plugin_slug ] ) && 'active' === $this->plugin_activation_data[ $plugin_slug ] && in_array( $this->plugin_data[$plugin_slug]['item_name'], $this->plugin_licensing_data ) ) { // @codingStandardsIgnoreLine
				return true;
			} else {
				$this->plugin_activation_data[ $plugin_slug ] = 'deactive';
				update_option( 'eb_pro_modules_data', $this->plugin_activation_data );
				return false;
			}
		}
	}
}

return new Eb_Settings_Pro_Featuers();
