<?php
/**
 * EDW Dummy Settings
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

if ( ! class_exists( 'Eb_Settings_Dummy' ) ) {
	/**
	 * Eb_Settings_Dummy_Woo_Int.
	 */
	class Eb_Settings_Dummy {
		/**
		 * Constructor.
		 */
		public function __construct() {

			// new dummy settings pages flow for pro modules.
			$bridge_pro_data = get_option( 'eb_pro_modules_data' );
			if ( is_array( $bridge_pro_data ) && ! empty( $bridge_pro_data ) ) { // @codingStandardsIgnoreLine
				// do nothing.
			} else {
				if ( ! is_plugin_active( 'woocommerce-integration/bridge-woocommerce.php' ) ) {
					new Eb_Settings_Dummy_Woo_Int();
				}
				if ( ! is_plugin_active( 'selective-synchronization/selective-synchronization.php' ) ) {
					new Eb_Settings_Dummy_Sel_Sync();
				}
				if ( ! is_plugin_active( 'edwiser-bridge-sso/sso.php' ) ) {
					new Eb_Settings_Dummy_SSO();
				}
				if ( ! is_plugin_active( 'edwiser-custom-fields/edwiser-custom-fields.php' ) ) {
					new Eb_Settings_Dummy_Custom_Fields();
				}
			}

		}

		/**
		 * Output the settings.
		 *
		 * @param array $data array of data.
		 * @since  1.0.0
		 */
		public static function disp_setting_img( $data ) {
			?>
			<div class="eb-dummy-settings">
				<img alt="<?php echo esc_html( $data['img_alt_text'] ); ?>" src="<?php echo esc_url( plugins_url( 'edwiser-bridge/admin/assets/images/' . $data['img_name'] ) ); ?>"/>
				<div class="eb-dummy-set-wrap">
					<div class="eb-dummy-set-cta">
						<h3><?php echo esc_html( $data['cta_msg'] ); ?></h3>
						<p><?php esc_html_e( 'I want to know more.' ); ?></p>
						<div class="ebpf-st-arrow">
							<span class="dashicons dashicons-arrow-down-alt2"></span>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</div>
						<a class="eb-go-pro-btn" target="_blank" href="<?php echo esc_url( $data['go_to_url'] ); ?>"><?php echo esc_html( $data['btn_text'] ); ?></a>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
if ( ! class_exists( 'Eb_Settings_Dummy_Woo_Int' ) ) {
	/**
	 * Eb_Settings_Dummy_Woo_Int.
	 */
	class Eb_Settings_Dummy_Woo_Int extends EB_Settings_Page { // @codingStandardsIgnoreLine

		/**
		 * Setting data.
		 *
		 * @var array $setting_data.
		 */
		private $setting_data = array();
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->setting_data = array(
				'img_alt_text' => __( 'Woo Integration setting image not found', 'edwiser-bridge' ),
				'img_name'     => 'woo_int.png',
				'go_to_url'    => 'https://bit.ly/2YWsjEj',
				'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
				'cta_msg'      => __( 'Sell Moodle Courses via 160+ Payment Gateways with WooCommerce.', 'edwiser-bridge' ),
			);

			$this->_id   = 'woo_int_dummy';
			$this->label = __( 'Woo Integration', 'edwiser-bridge' );
			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
			add_filter( 'eb_get_sections_synchronization', array( $this, 'prod_sections' ), 10, 1 );
			add_filter( 'eb_get_settings_synchronization', array( $this, 'prod_sync_setting' ), 10, 2 );
		}

		/**
		 * Get sections.
		 *
		 * @since  1.0.0
		 *
		 * @param string $section the current section name.
		 * @return array
		 */
		public function prod_sections( $section ) {
			if ( count( $section ) > 1 ) {
				$result = array_merge(
					array_slice( $section, 0, 1 ),
					array(
						'product_data_dummy' => __( 'Products', 'edwiser-bridge' ),
					),
					array_slice( $section, 1, null )
				);
			} else {
				$result = array( 'product_data_dummy' => __( 'Products', 'edwiser-bridge' ) );
			}
			return $result;
		}

		/**
		 * Add fields in "Products" tab
		 *
		 * @param array  $settings array List of settings fields.
		 *
		 * @param string $current_section string Gives current displayed section.
		 *
		 * @return $settings array Modified array with settings for Product section
		 * @since 1.0.2
		 */
		public function prod_sync_setting( $settings, $current_section ) {
			if ( 'product_data_dummy' === $current_section ) {
				$data     = array(
					'img_alt_text' => __( 'Woo Integration setting not found', 'edwiser-bridge' ),
					'img_name'     => 'woo_int_prod_sync.png',
					'go_to_url'    => 'https://bit.ly/2YWsjEj',
					'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
					'cta_msg'      => __( 'Sell Moodle Courses via 160+ Payment Gateways with WooCommerce.', 'edwiser-bridge' ),
				);
				$settings = apply_filters(
					'bridge_woo_product_synchronization_settings',
					array(
						array(
							'type' => 'cust_html',
							'html' => Eb_Settings_Dummy::disp_setting_img( $data ),
						),
					)
				);
			}
			return $settings;
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			global $current_section;

			// Hide the save button.
			$GLOBALS['hide_save_button'] = true;

			$settings = $this->get_settings( $current_section );

			Eb_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.0.0
		 *
		 * @param text $current_section name of the section.
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			$settings = apply_filters(
				'woo_int_dummy',
				array(
					array(
						'type' => 'cust_html',
						'html' => Eb_Settings_Dummy::disp_setting_img( $this->setting_data ),
					),
				)
			);
			return apply_filters( 'eb_get_settings_' . $this->_id, $settings, $current_section );
		}
	}
}

if ( ! class_exists( 'Eb_Settings_Dummy_Sel_Sync' ) ) {
	/**
	 * Eb_Settings_Dummy_Sel_Sync.
	 */
	class Eb_Settings_Dummy_Sel_Sync extends EB_Settings_Page { // @codingStandardsIgnoreLine
		/**
		 * Setting data.
		 *
		 * @var array $setting_data.
		 */
		private $setting_data = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->setting_data = array(
				'img_alt_text' => __( 'Selective Sync setting not found', 'edwiser-bridge' ),
				'img_name'     => 'selective-synch.png',
				'go_to_url'    => 'https://bit.ly/3tNRmrJ',
				'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
				'cta_msg'      => __( 'PICK and CHOOSE the courses and users to synchronize from Moodle to WordPress.', 'edwiser-bridge' ),
			);

			$this->_id   = 'sel_sync_dummy';
			$this->label = __( 'Selective Sync', 'edwiser-bridge' );
			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
			add_action( 'eb_sections_' . $this->_id, array( $this, 'output_sections' ) );
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			global $current_section;

			// Hide the save button.
			$GLOBALS['hide_save_button'] = true;

			$settings = $this->get_settings( $current_section );

			Eb_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.0.0
		 *
		 * @param text $current_section name of the section.
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			if ( 'users' === $current_section ) {
				$this->setting_data = array(
					'img_alt_text' => __( 'Selective Sync user synchronisation setting not found', 'edwiser-bridge' ),
					'img_name'     => 'selective_users.png',
					'go_to_url'    => 'https://bit.ly/3tNRmrJ',
					'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
					'cta_msg'      => __( 'PICK and CHOOSE the courses and users to synchronize from Moodle to WordPress.', 'edwiser-bridge' ),
				);
			} else {
				$this->setting_data = array(
					'img_alt_text' => __( 'Selective Sync Course synchronisation setting not found', 'edwiser-bridge' ),
					'img_name'     => 'selective_sync.png',
					'go_to_url'    => 'https://bit.ly/3tNRmrJ',
					'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
					'cta_msg'      => __( 'PICK and CHOOSE the courses and users to synchronize from Moodle to WordPress.', 'edwiser-bridge' ),
				);
			}
			$settings = apply_filters(
				'sel_sync_dummy',
				array(
					array(
						'type' => 'cust_html',
						'html' => Eb_Settings_Dummy::disp_setting_img( $this->setting_data ),
					),
				)
			);
			return apply_filters( 'eb_get_settings_' . $this->_id, $settings, $current_section );
		}

		/**
		 * Get sections.
		 *
		 * @since  1.0.0
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''      => __( 'Courses', 'edwiser-bridge' ),
				'users' => __( 'Users', 'edwiser-bridge' ),
			);

			$new_sections = apply_filters( 'eb_get_sections_' . $this->_id, $sections );
			if ( is_array( $new_sections ) ) {
				$sections = array_merge( $sections, $new_sections );
			}

			$new_sections = apply_filters_deprecated( 'eb_getSections_' . $this->_id, array( $sections ), '5.5', 'eb_get_sections_' . $this->_id );
			if ( is_array( $new_sections ) ) {
				$sections = array_merge( $sections, $new_sections );
			}
			return $sections;
		}
	}
}

if ( ! class_exists( 'Eb_Settings_Dummy_SSO' ) ) {
	/**
	 * Eb_Settings_Dummy_SSO.
	 */
	class Eb_Settings_Dummy_SSO extends EB_Settings_Page { // @codingStandardsIgnoreLine
		/**
		 * Setting data.
		 *
		 * @var array $setting_data.
		 */
		private $setting_data = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->setting_data = array(
				'img_alt_text' => __( 'Single Sign On setting not found', 'edwiser-bridge' ),
				'img_name'     => 'sso.png',
				'go_to_url'    => 'https://bit.ly/3tICgDx',
				'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
				'cta_msg'      => __( 'Moodle WordPress Single Sign-On for quick & easy login.', 'edwiser-bridge' ),
			);
			$this->_id          = 'sso-dummy';
			$this->label        = __( 'Single Sign On', 'edwiser-bridge' );
			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
			add_action( 'eb_sections_' . $this->_id, array( $this, 'output_sections' ) );
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			global $current_section;

			// Hide the save button.
			$GLOBALS['hide_save_button'] = true;

			$settings = $this->get_settings( $current_section );

			Eb_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.0.0
		 *
		 * @param text $current_section name of the section.
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			if ( 'redirection' === $current_section ) {
				$this->setting_data = array(
					'img_alt_text' => __( 'Single Sign On setting not found', 'edwiser-bridge' ),
					'img_name'     => 'sso_redirection.png',
					'go_to_url'    => 'https://bit.ly/3tICgDx',
					'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
					'cta_msg'      => __( 'Moodle WordPress Single Sign-On for quick & easy login.', 'edwiser-bridge' ),
				);
			} elseif ( 'social_login' === $current_section ) {
				$this->setting_data = array(
					'img_alt_text' => __( 'Single Sign On setting not found', 'edwiser-bridge' ),
					'img_name'     => 'sso_social.png',
					'go_to_url'    => 'https://bit.ly/3tICgDx',
					'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
					'cta_msg'      => __( 'Moodle WordPress Single Sign-On for quick & easy login.', 'edwiser-bridge' ),
				);
			} else {
				$this->setting_data = array(
					'img_alt_text' => __( 'Single Sign On setting not found', 'edwiser-bridge' ),
					'img_name'     => 'sso.png',
					'go_to_url'    => 'https://bit.ly/3tICgDx',
					'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
					'cta_msg'      => __( 'Moodle WordPress Single Sign-On for quick & easy login.', 'edwiser-bridge' ),
				);
			}
			$settings = apply_filters(
				'sso_dummy',
				array(
					array(
						'type' => 'cust_html',
						'html' => Eb_Settings_Dummy::disp_setting_img( $this->setting_data ),
					),
				)
			);
			return apply_filters( 'eb_get_settings_' . $this->_id, $settings, $current_section );
		}

		/**
		 * Get sections.
		 *
		 * @since  1.0.0
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''             => __( 'General', 'edwiser-bridge' ),
				'redirection'  => __( 'Redirection', 'edwiser-bridge' ),
				'social_login' => __( 'Social Login', 'edwiser-bridge' ),
			);

			$new_sections = apply_filters( 'eb_get_sections_' . $this->_id, $sections );
			if ( is_array( $new_sections ) ) {
				$sections = array_merge( $sections, $new_sections );
			}

			$new_sections = apply_filters_deprecated( 'eb_getSections_' . $this->_id, array( $sections ), '5.5', 'eb_get_sections_' . $this->_id );
			if ( is_array( $new_sections ) ) {
				$sections = array_merge( $sections, $new_sections );
			}
			return $sections;
		}
	}
}

if ( ! class_exists( 'Eb_Settings_Dummy_Custom_Fields' ) ) {
	/**
	 * Eb_Settings_Dummy_Custom_Fields.
	 */
	class Eb_Settings_Dummy_Custom_Fields extends EB_Settings_Page { // @codingStandardsIgnoreLine

		/**
		 * Setting data.
		 *
		 * @var array $setting_data.
		 */
		private $setting_data = array();
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->setting_data = array(
				'img_alt_text' => __( 'Custom Fields setting image not found', 'edwiser-bridge' ),
				'img_name'     => 'custom_fields.png',
				'go_to_url'    => 'https://bit.ly/2YWsjEj',
				'btn_text'     => __( 'Available in Edwiser Bridge Pro', 'edwiser-bridge' ),
				'cta_msg'      => __( 'Create and Configure custom fields on Edwiser Registration, User account, WooCommerce Registration, My Account, Checkout page.', 'edwiser-bridge' ),
			);

			$this->_id   = 'custom_fields_dummy';
			$this->label = __( 'Custom Fields', 'edwiser-bridge' );
			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			global $current_section;

			// Hide the save button.
			$GLOBALS['hide_save_button'] = true;

			$settings = $this->get_settings( $current_section );

			Eb_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.0.0
		 *
		 * @param text $current_section name of the section.
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			$settings = apply_filters(
				'custom_fields_dummy',
				array(
					array(
						'type' => 'cust_html',
						'html' => Eb_Settings_Dummy::disp_setting_img( $this->setting_data ),
					),
				)
			);
			return apply_filters( 'eb_get_settings_' . $this->_id, $settings, $current_section );
		}
	}
}

return new Eb_Settings_Dummy();
