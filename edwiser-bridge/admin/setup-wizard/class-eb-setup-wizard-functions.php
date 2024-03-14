<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup Edwiser Bridge plugin.
 *
 * @package     Edwiser Bridge
 * @version     2.6.0
 */

namespace app\wisdmlabs\edwiserBridge;

use app\wisdmlabs\edwiserBridgePro\includes as includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Eb_Setup_Wizard class.
 */
class Eb_Setup_Wizard_Functions {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$setup_templates = new Eb_Setup_Wizard_Templates();

		/* phpcs:disable WordPress.Security.NonceVerification */
		if ( ! isset( $_POST['action'] ) && isset( $_GET['page'] ) && 'eb-setup-wizard' === $_GET['page'] ) {
			add_action( 'admin_init', array( $setup_templates, 'eb_setup_wizard_template' ), 9 );
			add_action( 'admin_init', array( $this, 'eb_setup_steps_save_handler' ) );
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
		/* phpcs: enable */

		add_action( 'admin_init', array( $this, 'welcome_handler' ) );

		add_filter( 'eb_send_new_user_email_on_user_sync', array( $this, 'eb_setup_send_mail_on_user_sync' ) );
		add_action( 'wp_ajax_eb_setup_change_step', array( $this, 'eb_setup_change_step' ) );
		add_action( 'wp_ajax_eb_setup_course_sync', array( $this, 'eb_setup_course_sync' ) );
		add_action( 'wp_ajax_eb_setup_close_setup', array( $setup_templates, 'eb_setup_close_setup' ) );
		add_action( 'wp_ajax_eb_setup_save_and_continue', array( $this, 'eb_setup_save_and_continue' ) );
		add_action( 'wp_ajax_eb_setup_test_connection', array( $this, 'eb_setup_test_connection_handler' ) );
		add_action( 'wp_ajax_eb_setup_manage_license', array( $this, 'eb_setup_manage_license' ) );
		add_action( 'wp_ajax_eb_setup_validate_license', array( $this, 'eb_setup_validate_license' ) );
	}

	/**
	 * Sends user to the welcome page on plugin activation.
	 *
	 * @since  1.0.0
	 */
	public function welcome_handler() {
		// Return if no activation redirect transient is set. Or not network admin.
		if ( ! get_transient( '_eb_activation_redirect' ) || is_network_admin() ) {
			return;
		}

		// check if setup wizard already completed or closed by user.
		$setup_completed = get_option( 'eb_setup_wizard_completed' );
		if ( $setup_completed ) {
			return;
		}

		if ( get_transient( '_eb_activation_redirect' ) ) {
			// Delete transient used for redirection.
			delete_transient( '_eb_activation_redirect' );
			update_option( 'eb_setup_wizard_completed', 1 );

			$setup_data = get_option( 'eb_setup_data' );
			$wc_url     = admin_url( '/?page=eb-setup-wizard' );

			if ( isset( $setup_data ) && ! empty( $setup_data ) ) {
				$name      = $setup_data['name'];
				$progress  = $setup_data['progress'];
				$next_step = $setup_data['next_step'];
				if ( isset( $next_step ) && ! empty( $next_step ) ) {
					$wc_url = admin_url( '/?page=eb-setup-wizard&current_step=' . $next_step );
				}
			}
			wp_safe_redirect( $wc_url );
			exit;
		}
	}

	/**
	 * Send email .
	 *
	 * @param  boolean $send_email true or false.
	 * @since  1.0.0
	 */
	public function eb_setup_send_mail_on_user_sync( $send_email ) {
		// Nonce should be same as user sync nonce.
		if ( isset( $_POST['_wpnonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'check_sync_action' ) ) {
			if ( isset( $_POST['send_mail'] ) ) {
				return $_POST['send_mail']; // phpcs:ignore
			}
		}
		return $send_email;
	}

	/**
	 * Setup Wizard steps.
	 */
	public function eb_setup_wizard_get_steps() {

		/**
		 * Loop through the steps.
		 * Ajax call for each of the steps and save.
		 * step change logic.
		 * load data on step change.
		 */
		$default_array = array(
			'initialize' => array(
				'name'     => __( 'Setup Initialize', 'edwiser-bridge' ),
				'title'    => __( 'Edwiser Bridge plugin - Setup Initialization', 'edwiser-bridge' ),
				'function' => 'eb_setup_initialize',
				'priority' => 10,
				'sidebar'  => 0,
				'sub_step' => 0,
			),
		);

		$free_setup_steps = array(
			'free_installtion_guide'    => array(
				'name'     => __( 'Edwiser Bridge FREE plugin installation guide', 'edwiser-bridge' ),
				'title'    => __( 'Edwiser Bridge FREE plugin installation guide', 'edwiser-bridge' ),
				'function' => 'eb_setup_free_installtion_guide',
				'sidebar'  => 1,
				'priority' => 20,
				'sub_step' => 0,
			),
			'moodle_redirection'        => array(
				'name'     => __( 'Edwiser Bridge FREE Moodle plugin configuration', 'edwiser-bridge' ),
				'title'    => __( 'Edwiser Bridge FREE Moodle plugin configuration', 'edwiser-bridge' ),
				'sidebar'  => 1,
				'priority' => 30,
				'function' => 'eb_setup_moodle_redirection',
				'sub_step' => 0,

			),
			'test_connection'           => array(
				'name'     => __( 'Connection test between WordPress and Moodle', 'edwiser-bridge' ),
				'title'    => __( 'Connection test between WordPress and Moodle', 'edwiser-bridge' ),
				'sidebar'  => 1,
				'priority' => 40,
				'function' => 'eb_setup_test_connection',
				'sub_step' => 0,

			),
			'course_sync'               => array(
				'sidebar'  => 1,
				'name'     => __( 'Courses syncronization', 'edwiser-bridge' ),
				'title'    => __( 'Courses syncronization', 'edwiser-bridge' ),
				'function' => 'eb_setup_course_sync',
				'priority' => 50,
				'sub_step' => 0,

			),
			'user_sync'                 => array(
				'sidebar'  => 1,
				'name'     => __( 'User syncronization', 'edwiser-bridge' ),
				'title'    => __( 'User syncronization', 'edwiser-bridge' ),
				'function' => 'eb_setup_user_sync',
				'priority' => 60,
				'sub_step' => 0,

			),
			'free_recommended_settings' => array(
				'sidebar'  => 1,
				'name'     => __( 'Recommended settings', 'edwiser-bridge' ),
				'title'    => __( 'Recommended settings', 'edwiser-bridge' ),
				'function' => 'eb_setup_free_recommended_settings',
				'priority' => 70,
				'sub_step' => 0,

			),
			'free_completed_popup'      => array(
				'sidebar'  => 1,
				'name'     => __( 'Recommended settings', 'edwiser-bridge' ),
				'title'    => __( 'Edwiser Bridge FREE plugin recommended settings', 'edwiser-bridge' ),
				'function' => 'eb_setup_free_completed_popup',
				'priority' => 80,
				'sub_step' => 1,

			),
		);

		$pro_setup_steps = array(
			'pro_initialize'   => array(
				'sidebar'  => 1,
				'name'     => __( 'Initialize Edwiser Bridge PRO setup ', 'edwiser-bridge' ),
				'title'    => __( 'Initialize Edwiser Bridge PRO plugin setup ', 'edwiser-bridge' ),
				'function' => 'eb_setup_pro_initialize',
				'priority' => 90,
				'sub_step' => 0,
			),
			'license'          => array(
				'sidebar'  => 1,
				'name'     => __( 'Edwiser Bridge PRO License setup', 'edwiser-bridge' ),
				'title'    => __( 'Edwiser Bridge PRO License setup', 'edwiser-bridge' ),
				'function' => 'eb_setup_license',
				'priority' => 100,
				'sub_step' => 0,
			),
			'pro_plugins'      => array(
				'sidebar'  => 1,
				'name'     => __( 'Enable or disable the Edwiser Bridge PRO WordPress features', 'edwiser-bridge' ),
				'title'    => __( 'Enable/Disable the Edwiser Bridge PRO WordPress features', 'edwiser-bridge' ),
				'function' => 'eb_setup_pro_plugins',
				'priority' => 110,
				'sub_step' => 0,
			),
			'mdl_plugins'      => array(
				'sidebar'  => 1,
				'name'     => __( 'Activate Edwiser Bridge Pro Features on Moodle', 'edwiser-bridge' ),
				'title'    => __( 'Activate Edwiser Bridge Pro Features on Moodle', 'edwiser-bridge' ),
				'function' => 'eb_setup_mdl_plugins',
				'priority' => 110,
				'sub_step' => 0,
			),
			'sso'              => array(
				'sidebar'  => 1,
				'name'     => __( 'Single Sign On setup', 'edwiser-bridge' ),
				'title'    => __( 'Single Sign On setup', 'edwiser-bridge' ),
				'function' => 'eb_setup_sso',
				'priority' => 130,
				'sub_step' => 0,
			),
			'wi_products_sync' => array(
				'sidebar'  => 1,
				'name'     => __( 'WooCommerce product creation', 'edwiser-bridge' ),
				'title'    => __( 'WooCommerce product creation', 'edwiser-bridge' ),
				'function' => 'eb_setup_wi_products_sync',
				'priority' => 140,
				'sub_step' => 0,
			),
			'pro_settings'     => array(
				'sidebar'  => 1,
				'name'     => __( 'Edwiser Bridge PRO plugin settings', 'edwiser-bridge' ),
				'title'    => __( 'Edwiser Bridge PRO plugin settings', 'edwiser-bridge' ),
				'function' => 'eb_setup_pro_settings',
				'priority' => 150,
				'sub_step' => 0,
			),
		);

		/**
		 * Check the value of the selected setup.
		 * If free don't show only free plugins steps.
		 * If pro select only pro steps.
		 * If selected both merge above two arrays and show those steps.
		 */
		$setup_wizard = get_option( 'eb_setup_data' );

		$steps = array_merge( $default_array, $free_setup_steps );

		if ( isset( $setup_wizard['name'] ) && 'pro' === $setup_wizard['name'] ) {
			$steps = array_merge( $default_array, $pro_setup_steps );
		} elseif ( isset( $setup_wizard['name'] ) && 'free_and_pro' === $setup_wizard['name'] ) {
			$steps = array_merge( $default_array, $free_setup_steps, $pro_setup_steps );
		}

		return $steps;
	}



	/**
	 * Added This new function instead of adding one by one function for wp_ajax hook, as by default parameter is not being set in each step callback so wrote below wrapper function for all of them and provided parameter 1.
	 */
	public function eb_setup_change_step() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'eb_setup_wizard' ) ) {
			$step                   = isset( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : '';
			$steps                  = $this->eb_setup_wizard_get_steps();
			$function               = $steps[ $step ]['function'];
			$setup_wizard_templates = new Eb_Setup_Wizard_Templates();

			$step_html = $setup_wizard_templates->$function( 1 );
		}
	}


	/**
	 * Setup Wizard Manage license.
	 */
	public function eb_setup_manage_license() {

		if ( isset( $_POST['_wpnonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'eb_setup_wizard' ) ) {
			if ( ! class_exists( 'Licensing_Settings' ) ) {
				include_once plugin_dir_path( __DIR__ ) . 'settings/class-eb-settings-page.php';
				include_once plugin_dir_path( __DIR__ ) . 'licensing/class-licensing-settings.php';
			}

			$license_data = isset( $_POST['license_data'] ) ? sanitize_text_field( wp_unslash( $_POST['license_data'] ) ) : array();

			$license_data = (array) json_decode( $license_data );

			// Post data will provide key.
			// Here we will provide only activation functionality.
			// This action is the plugin name and 2nd parameter provided in function is licensse status action.

			$response = array();

			foreach ( $license_data as $key => $value ) {
				if ( ! empty( $value ) ) {
					$license_handler  = new Licensing_Settings();
					$result           = $this->eb_setup_wizard_install_plugins(
						array(
							'action'                       => $key,
							'edd_' . $key . '_license_key' => $value,
						)
					);
					$response[ $key ] = $result;
				}
			}

			wp_send_json_success( $response );
		}
	}

	/**
	 * Setup Wizard validate license keys.
	 */
	public function eb_setup_validate_license() {
		$response = array(
			'status' => 'error',
			'msg'    => __( 'Something went wrong. Please try again.', 'edwiser-bridge' ),
		);
		if ( isset( $_POST['_wpnonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'eb_setup_wizard' ) ) {
			$license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
			$action      = isset( $_POST['license_action'] ) ? sanitize_text_field( wp_unslash( $_POST['license_action'] ) ) : '';

			if ( ! empty( $license_key ) && ! empty( $action ) ) {
				if ( ! class_exists( 'Eb_Licensing_Manager' ) ) {
					include_once plugin_dir_path( __DIR__ ) . 'licensing/class-eb-licensing-manager.php';
				}

				$products_data   = Eb_Licensing_Manager::get_plugin_data();
				$plugin_data     = $products_data[ $action ];
				$license_manager = new Eb_Licensing_Manager( $products_data[ $action ] );

				$resp_data = wdm_request_edwiser(
					array(
						'edd_action'      => 'check_license',
						'license'         => $license_key,
						'item_name'       => urlencode( $plugin_data['item_name']), //phpcs:ignore
						'current_version' => $plugin_data['current_version'],
						'url'             => get_home_url(),
					)
				);

				if ( false === $resp_data['status'] || null === $resp_data['data'] || ! in_array( $resp_data['status'], array( 200, 301 ), true ) ) {
					$response = array(
						'status'  => 'error',
						'message' => __( 'No response from server edwiser.org.', 'edwiser-bridge' ),
					);
				}
				$license_data = $resp_data['data'];
				if ( isset( $license_data ) ) {
					switch ( $license_data->license ) {
						case 'valid':
							$response = array(
								'status'  => 'success',
								'message' => __( 'Valid license key.', 'edwiser-bridge' ),
							);
							break;
						case 'inactive':
							$response = array(
								'status'  => 'success',
								'message' => __( 'Valid license key.', 'edwiser-bridge' ),
							);
							break;
						case 'expired':
							$response = array(
								'status'  => 'error',
								'message' => __( 'License key has expired.', 'edwiser-bridge' ),
							);
							break;
						case 'no_activations_left':
							$response = array(
								'status'  => 'error',
								'message' => __( 'License key has no activations left.', 'edwiser-bridge' ),
							);
							break;
						case 'item_name_mismatch':
							$response = array(
								'status'  => 'error',
								'message' => __( 'License key is not valid for this product.', 'edwiser-bridge' ),
							);
							break;
						default:
							$response = array(
								'status'  => 'error',
								'message' => __( 'License key is invalid.', 'edwiser-bridge' ),
							);
							break;
					}
				} else {
					$response = array(
						'status'  => 'error',
						'message' => __( 'No response from server edwiser.org.', 'edwiser-bridge' ),
					);
				}
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Setup Wizard Test connection handler.
	 */
	public function eb_setup_test_connection_handler() {
		if ( isset( $_POST['_wpnonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'eb_setup_wizard' ) ) {

			$url   = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
			$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

			$version           = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_version();
			$connection_helper = new Eb_Connection_Helper( 'edwiserbridge', $version );
			$response          = $connection_helper->connection_test_helper( $url, $token, 1 );

			wp_send_json_success( $response );
		}
	}


	/**
	 * Setup Wizard Test connection handler.
	 */
	public function eb_setup_course_sync() {
		if ( isset( $_POST['_wpnonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'eb_setup_wizard' ) ) {
			$publish                              = isset( $_POST['publish'] ) ? sanitize_text_field( wp_unslash( $_POST['publish'] ) ) : '';
			$sync_options['eb_synchronize_draft'] = '1';

			if ( $publish ) {
				$sync_options['eb_synchronize_draft'] = '0';
			}

			$sync_options['eb_synchronize_categories'] = '1';
			$sync_options['eb_synchronize_previous']   = '1';
			$response                                  = edwiser_bridge_instance()->course_manager()->course_synchronization_handler( $sync_options );

			wp_send_json_success();
		}
	}

	/**
	 * Setup Wizard Save step and redirect to next step.
	 */
	public function eb_setup_save_and_continue() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'eb_setup_wizard' ) ) {
			$data             = $_POST['data']; // phpcs:ignore
			$current_step     = $data['current_step'];
			$next_step        = isset( $data['next_step'] ) ? $data['next_step'] : '';
			$is_next_sub_step = isset( $data['is_next_sub_step'] ) ? $data['is_next_sub_step'] : 0;

			$steps    = $this->eb_setup_wizard_get_steps();
			$function = ! empty( $next_step ) ? $steps[ $next_step ]['function'] : '';
			// Check current step.
			// Check if there is any data to be saved.

			// Save step form progress.
			$setup_data = get_option( 'eb_setup_data' );

			// Get the priority of the existing progress data.
			// If the existing priority is greater then dont update it, let old progress be as it is.
			if ( isset( $setup_data['progress'] ) && 'completed_setup' !== $current_step && $steps[ $setup_data['progress'] ]['priority'] < $steps[ $current_step ]['priority'] ) {
				$setup_data['progress']  = $current_step;
				$setup_data['next_step'] = $next_step;
				update_option( 'eb_setup_data', $setup_data );
			}

			switch ( $current_step ) {
				case 'moodle_redirection':
					// Create web service and update data in EB settings.
					if ( isset( $data['mdl_url'] ) ) {
						$url           = get_option( 'eb_connection' );
						$url           = is_array( $url ) ? $url : array();
						$url['eb_url'] = $data['mdl_url'];
						update_option( 'eb_connection', $url );
					}
					break;

				case 'test_connection':
					if ( isset( $data['mdl_url'] ) && isset( $data['mdl_token'] ) && isset( $data['mdl_lang_code'] ) ) {
						$url                    = get_option( 'eb_connection' );
						$url['eb_url']          = $data['mdl_url'];
						$url['eb_access_token'] = $data['mdl_token'];

						update_option( 'eb_connection', $url );

						$general_settings                     = get_option( 'eb_general' );
						$language                             = $data['mdl_lang_code'];
						$general_settings['eb_language_code'] = $language;

						update_option( 'eb_general', $general_settings );
					}

					break;

				case 'course_sync':
					break;

				case 'user_sync':
					break;

				case 'free_recommended_settings':
					$general_settings                           = get_option( 'eb_general' );
					$general_settings['eb_useraccount_page_id'] = $data['user_account_page'];
					$general_settings['eb_enable_registration'] = isset( $data['user_account_creation'] ) && '1' === $data['user_account_creation'] ? 'yes' : 'no';
					update_option( 'eb_general', $general_settings );
					$function = 'eb_setup_free_completed_popup';

					break;

				case 'free_completed_popup':
					break;
				case 'pro_plugins':
					$module_data  = get_option( 'eb_pro_modules_data' );
					$modules_data = array(
						'selective_sync'  => '1' === $data['selective_sync'] ? 'active' : 'deactive',
						'sso'             => '1' === $data['sso'] ? 'active' : 'deactive',
						'woo_integration' => '1' === $data['woo_integration'] ? 'active' : 'deactive',
						'bulk_purchase'   => '1' === $data['bulk_purchase'] ? 'active' : 'deactive',
						'custom_fields'   => '1' === $data['custom_fields'] ? 'active' : 'deactive',
					);
					update_option( 'eb_pro_modules_data', $modules_data );
					break;
				case 'sso':
					$old_sso_settings = get_option( 'eb_sso_settings_general' );
					if ( isset( $data['sso_key'] ) ) {
						$old_sso_settings['eb_sso_secret_key'] = $data['sso_key'];
					}
					update_option( 'eb_sso_settings_general', $old_sso_settings );
					break;

				case 'wi_products_sync':
					// require_once ABSPATH . '/wp-content/plugins/woocommerce-integration/includes/class-bridge-woocommerce.php';
					// require_once ABSPATH . '/wp-content/plugins/woocommerce-integration/includes/class-bridge-woocommerce-course.php';.

					$sync_options = array(
						'bridge_woo_synchronize_product_categories' => 1,
						'bridge_woo_synchronize_product_update'     => 1,
						'bridge_woo_synchronize_product_create'     => 1,
					);

					$course_woo_plugin = new includes\wooInt\Bridge_Woocommerce_Course( includes\edwiser_bridge_pro()->get_plugin_name(), includes\edwiser_bridge_pro()->get_version() );
					$response          = $course_woo_plugin->bridge_woo_product_sync_handler( $sync_options );

					break;

				case 'pro_settings':
					if ( isset( $data['archive_page'] ) ) {
						$general_settings                    = get_option( 'eb_general' );
						$general_settings['eb_show_archive'] = ( '1' === $data['archive_page'] ) ? 'yes' : 'no';
						$guest_checkout                      = ( '1' === $data['guest_checkout'] ) ? 'yes' : 'no';
						update_option( 'eb_general', $general_settings );
						update_option( 'woocommerce_enable_guest_checkout', $guest_checkout );
					}

					$function = 'eb_setup_pro_completed_popup';
					break;

				case 'completed_setup':
					// Check current step.
					// Check if there is any data to be saved.

					// Save step form progress.
					$setup_data              = get_option( 'eb_setup_data' );
					$setup_data['progress']  = 'initialize';
					$setup_data['next_step'] = '';
					update_option( 'eb_setup_data', $setup_data );
					break;

				default:
					break;
			}

			/*
			* There are multiple steps inside 1 step which are listed below.
			* 1. Web sevice
			*    a. web service
			*    b. WP site details
			*
			* 2. user and course sync
			*    a. User and course sync
			*    b. success screens
			*/
			// Check if there are any sub steps available.
			if ( 'completed_setup' !== $current_step ) {
				$setup_wizard_templates = new Eb_Setup_Wizard_Templates();
				$next_step_html         = $setup_wizard_templates->$function( 1 );
			}
		}
	}

	/**
	 * Setup Wizard Get next step.
	 *
	 * @param string $current_step Current step.
	 */
	public function get_next_step( $current_step ) {

		$steps      = $this->eb_setup_wizard_get_steps();
		$step       = '';
		$found_step = 0;
		foreach ( $steps as $key => $value ) {

			if ( $found_step && ! $value['sub_step'] ) {
				$step = $key;
				break;
			}

			if ( $current_step === $key ) {
				$found_step = 1;
			}
		}

		return $step;
	}

	/**
	 * Setup Wizard Get next step.
	 *
	 * @param string $current_step Current step.
	 */
	public function get_prev_step( $current_step ) {

		$steps      = $this->eb_setup_wizard_get_steps();
		$step       = '';
		$found_step = 0;
		$prevkey    = '';
		foreach ( $steps as $key => $value ) {
			if ( $current_step === $key ) {
				$found_step = 1;
			}

			if ( $found_step ) {
				$step = $prevkey;
				break;
			}

			$prevkey = $key;
		}

		return $step;
	}


	/**
	 * Setup Wizard Enqueue scripts.
	 */
	public function enqueue_scripts() {

		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		// Include CSS.
		wp_enqueue_style(
			'eb-setup-wizard-css',
			$eb_plugin_url . 'admin/assets/css/eb-setup-wizard.css',
			array( 'dashicons' ),
			time()
		);

		wp_register_script(
			'eb-setup-wizard-js',
			$eb_plugin_url . 'admin/assets/js/eb-setup-wizard.js',
			array( 'jquery', 'jquery-ui-dialog' ),
			time(),
			false
		);

		$setup_nonce = wp_create_nonce( 'eb_setup_wizard' );
		$sync_nonce  = wp_create_nonce( 'check_sync_action' );
		$sso_nonce   = wp_create_nonce( 'ebsso-verify-key' );

		wp_localize_script(
			'eb-setup-wizard-js',
			'eb_setup_wizard',
			array(
				'ajax_url'                        => admin_url( 'admin-ajax.php' ),
				'plugin_url'                      => $eb_plugin_url,
				'nonce'                           => $setup_nonce,
				'sync_nonce'                      => $sync_nonce,
				'sso_nonce'                       => $sso_nonce,
				'msg_user_link_to_moodle_success' => esc_html__( 'User\'s linked to moodle successfully.', 'edwiser-bridge' ),
				'msg_con_success'                 => esc_html__( 'Connection successful, Please save your connection details.', 'edwiser-bridge' ),
				'msg_courses_sync_success'        => esc_html__( 'Courses synchronized successfully.', 'edwiser-bridge' ),
				'msg_con_prob'                    => esc_html__( 'There is a problem while connecting to moodle server.', 'edwiser-bridge' ),
				'msg_err_users'                   => esc_html__( 'Error occured for following users: ', 'edwiser-bridge' ),
				'msg_user_sync_success'           => esc_html__( 'User\'s course enrollment status synced successfully.', 'edwiser-bridge' ),
				'msg_woo_int_enable_error'        => esc_html__( 'WooCommerce Integration must be enabled to use Bulk Purchase feature.', 'edwiser-bridge' ),
				'msg_empty_license_key'           => esc_html__( 'Please enter a valid license key.', 'edwiser-bridge' ),
				'msg_no_plugin_selected_error'    => esc_html__( 'No pro feature selected.', 'edwiser-bridge' ),
				'is_woo_active'                   => is_plugin_active( 'woocommerce/woocommerce.php' ),
				'msg_woo_enable_error'            => esc_html__( 'WooCommerce must be active to use WooCommerce Integration feature.', 'edwiser-bridge' ),
			)
		);

	}

	/**
	 * Setup Wizard save steps data
	 */
	public function eb_setup_steps_save_handler() {

		$url   = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

		$connection_helper = new Eb_Connection_Helper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connection_test_helper( $url, $token );
		wp_send_json_success( $return );
	}



	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {

		$welcome_page_name  = esc_html__( 'About Edwiser Bridge', 'edwiser-bridge' );
		$welcome_page_title = esc_html__( 'Welcome to Edwiser Bridge', 'edwiser-bridge' );
		$eb_plugin_url      = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		add_submenu_page(
			'',
			'Edwiser Bridge Setup',
			'Edwiser Bridge Setup',
			'read',
			'eb-setup-wizard'
		);
	}

	/**
	 * Setup Wizard Steps HTML content
	 *
	 * @param string $current_step Current step.
	 */
	public function eb_setup_steps_html( $current_step = '' ) {
		$steps = $this->eb_setup_wizard_get_steps();

		/**
		 * Get completed steps data.
		 */
		$setup_data = get_option( 'eb_setup_data' );
		$progress   = isset( $setup_data['progress'] ) ? $setup_data['progress'] : '';
		$completed  = 1;

		if ( empty( $progress ) ) {
			$completed = 0;
		}

		if ( ! empty( $steps ) && is_array( $steps ) ) {
			?>
			<ul class="eb-setup-steps">

			<?php
			$allowed_tags = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

			foreach ( $steps as $key => $step ) {
				if ( ! $step['sub_step'] ) {
					$class = '';
					$html  = '<span class="eb-setup-step-circle eb_setup_sidebar_progress_icons" > </span>';

					if ( 1 === $completed ) {
						$class = 'eb-setup-step-completed';
						$html  = '<span class="dashicons dashicons-yes-alt eb_setup_sidebar_progress_icons"></span>';
					}
					if ( $current_step === $key ) {
						$class = 'eb-setup-step-active';
						$html  = '<span class="dashicons dashicons-arrow-right-alt2 eb_setup_sidebar_progress_icons"></span>';
					}

					if ( $key === $progress ) {
						$completed = 0;
					}

					?>
					<li class='eb-setup-step  <?php echo ' eb-setup-step-' . esc_attr( $key ) . ' ' . wp_kses( $class, $allowed_tags ) . '-wrap'; ?>' >
						<?php echo wp_kses( $html, $allowed_tags ); ?>
						<span class='eb-setup-steps-title <?php echo wp_kses( $class, $allowed_tags ); ?>' data-step="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_attr( $step['name'] ); ?>
						</span>
					</li>

					<?php
				} else {
					if ( $key === $progress ) {
						$completed = 0;
					}
				}
			}
			?>
			</ul>
			<?php
		}
	}

	/**
	 * Setup Wizard Page submission or refresh handler
	 */
	public function eb_setup_handle_page_submission_or_refresh() {

		$steps      = $this->eb_setup_wizard_get_steps();
		$step       = 'initialize';
		$setup_data = get_option( 'eb_setup_data' );
		/**
		 * Handle form submission.
		 */
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'eb_setup_wizard' ) ) {
			if ( isset( $_POST['eb_setup_name'] ) && ! empty( $_POST['eb_setup_free_initialize'] ) ) {
				$setup_name = sanitize_text_field( wp_unslash( $_POST['eb_setup_name'] ) );
				$step       = 'free_installtion_guide';

				// save set up data.
				$setup_data   = get_option( 'eb_setup_data' );
				$chosen_setup = '';
				if ( 'eb_free_setup' === $setup_name ) {
					$chosen_setup = 'free';
				} elseif ( 'eb_pro_setup' === $setup_name ) {
					$step         = 'pro_initialize';
					$chosen_setup = 'pro';
				} elseif ( 'eb_free_and_pro' === $setup_name ) {
					$chosen_setup = 'free_and_pro';
				}
				if ( is_array( $setup_data ) ) {
					$setup_data['name'] = $chosen_setup;
				} else {
					$setup_data = array( 'name' => $chosen_setup );
				}
				// If this form is submitted i.e progress should be added.
				$setup_data['progress']  = 'initialize';
				$setup_data['next_step'] = $step;
				update_option( 'eb_setup_data', $setup_data );
			}
		} elseif ( isset( $_GET['current_step'] ) && ! empty( $_GET['current_step'] ) ) {
			$step = $_GET['current_step']; // phpcs:ignore
		} elseif ( isset( $setup_data ) && ! empty( $setup_data ) ) {
			$next_step = $setup_data['next_step'];
			if ( isset( $next_step ) && ! empty( $next_step ) ) {
				$step = $next_step;
			}
		} else {
			$step = 'initialize';
		}
		/* phpcs: enable */

		return $step;
	}

	/**
	 * Setup Wizard get step title.
	 *
	 * @param string $step Step name.
	 */
	public function eb_get_step_title( $step ) {
		$steps = $this->eb_setup_wizard_get_steps();
		return isset( $steps[ $step ]['title'] ) ? $steps[ $step ]['title'] : '';
	}

	/**
	 * Setup Wizard install plugins.
	 *
	 * @param array $data Plugin Data.
	 */
	public function eb_setup_wizard_install_plugins( $data ) {
		if ( ! class_exists( 'Eb_Licensing_Manager' ) ) {
			include_once plugin_dir_path( __DIR__ ) . 'licensing/class-eb-licensing-manager.php';
		}
		if ( ! class_exists( 'Eb_Get_Plugin_Data' ) ) {
			include_once plugin_dir_path( __DIR__ ) . 'licensing/class-eb-get-plugin-data.php';
		}

		$status['install']         = '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . __( 'Plugin insallation failed', 'edwiser-bridge' ) . '</span>';
		$slug                      = $data['action'];
		$products_data             = Eb_Licensing_Manager::get_plugin_data();
		$plugin_data               = $products_data[ $slug ];
		$plugin_data['edd_action'] = 'get_version';
		$l_key_name                = $plugin_data['key'];
		$l_key                     = trim( $data[ $l_key_name ] );
		$plugin_data['license']    = $l_key;

		// Before this check if the key provided and key already present are same.
		// If same then check if the license is already activated or not.
		// If already activated then check if plugin is installed or not.
		// if installed skip evrythinh show success msg.
		// If Not then only perform below actions.

		if ( empty( $plugin_data['license'] ) ) {
			$get_l_key_link  = '<a href="https://edwiser.org/bridge-wordpress-moodle-integration/#pricing">' . __( 'Click here', 'edwiser-bridge' ) . '</a>';
			$resp['message'] = __( 'License key cannot be empty, Please enter the valid license key.', 'edwiser-bridge' ) . $get_l_key_link . __( ' to get the license key.', 'edwiser-bridge' );
			return $resp;
		}

		// dependency check. depricated.
		if ( 'woocommerce_integration' === $slug ) {
			$all_plugins = get_plugins();
			$woo_path    = 'woocommerce/woocommerce.php';
			if ( array_key_exists( $woo_path, $all_plugins ) || in_array( $woo_path, $all_plugins, true ) ) {
				if ( ! is_plugin_active( $woo_path ) ) {
					activate_plugin( $woo_path );
				}
			} else {
				include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				$api = plugins_api(
					'plugin_information',
					array(
						'slug'   => 'woocommerce',
						'fields' => array(
							'sections' => false,
						),
					)
				);
				if ( is_wp_error( $api ) ) {
					$status['dependency']['woocommerce'] = $api->get_error_message();
				} else {
					$status['dependency']['woocommerce'] = $this->eb_setup_wizard_download_and_install( $api->download_link );
				}

				activate_plugin( 'woocommerce/woocommerce.php' );

				return $status;
			}
		} elseif ( 'bulk-purchase' === $slug ) {
			$woo_integration_path = 'woocommerce-integration/bridge-woocommerce.php';
			if ( ! is_plugin_active( $woo_integration_path ) ) {
				$status['message'] = __( 'Please activate the WooCommerce Integration plugin first.', 'edwiser-bridge' );
				return $status;
			}
		} elseif ( 'edwiser_custom_fields' === $slug ) {
			$woo_integration_path = 'woocommerce-integration/bridge-woocommerce.php';
			if ( is_plugin_active( $woo_integration_path ) ) {
				$woo_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $woo_integration_path );
				if ( version_compare( $woo_data['Version'], '2.2.1', '<' ) ) {
					$status['message'] = __( 'WooCommerce Integration plugin Version 2.2.1 required.', 'edwiser-bridge' );
					return $status;
				}
			}
		}

		$installed      = false;
		$activated      = false;
		$license_status = '';

		// check if license key is already present in the database.
		$license_key = get_option( $l_key_name );
		// if old license key is same as license key provided.
		if ( $license_key === $l_key ) {
			// check if license key is already activated.
			$license_status = get_option( 'edd_' . $slug . '_license_status' );
		} else {
			update_option( $l_key_name, $l_key );
		}

		if ( 'valid' !== $license_status ) {
			$products_data[ $slug ]['key'] = $l_key;
			$license_manager               = new Eb_Licensing_Manager( $products_data[ $slug ] );
			$license_manager->activate_license();

			// check license status and prepare response.
			$license_status = get_option( 'edd_' . $slug . '_license_status' );
			if ( 'valid' === $license_status ) {
				$status['activate'] = '<span class="eb_license_success"><span class="dashicons dashicons-yes"></span>' . __( 'License activated', 'edwiser-bridge' ) . '</span>';
			} elseif ( 'no_activations_left' === $license_status ) {
				$status['activate'] = '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . __( 'License is activated to maximum limit', 'edwiser-bridge' ) . '</span>';
			} elseif ( 'expired' === $license_status ) {
				$status['activate'] = '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . __( 'License is expired', 'edwiser-bridge' ) . '</span>';
			} else {
				$status['activate'] = '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . __( 'License activation failed', 'edwiser-bridge' ) . '</span>';
			}
		} else {
			$status['activate'] = '<span class="eb_license_success"><span class="dashicons dashicons-yes"></span>' . __( 'License already activated', 'edwiser-bridge' ) . '</span>';
		}

		// check if plugin is already installed and activated.
		$all_plugins = get_plugins();
		if ( array_key_exists( $plugin_data['path'], $all_plugins ) || in_array( $plugin_data['path'], $all_plugins, true ) ) {
			$installed         = true;
			$status['install'] = '<span class="eb_license_success"><span class="dashicons dashicons-yes"></span>' . __( 'Plugin already installed', 'edwiser-bridge' ) . '</span>';
			// check if plugin is already activated.
			if ( is_plugin_active( $plugin_data['path'] ) ) {
				$activated         = true;
				$status['install'] = $status['install'] . '<span class="eb_license_success">' . __( ' and activated', 'edwiser-bridge' ) . '</span>';
			}
		}

		// if not installed and license in valid then install the plugin.
		if ( ! $installed && 'valid' === $license_status ) {
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

					// Install the plugin.
					$status['install'] = $this->eb_setup_wizard_download_and_install( $request->download_link );

					if ( true === $status['install'] ) {
						$installed         = true;
						$status['install'] = '<span class="eb_license_success"><span class="dashicons dashicons-yes"></span>' . __( 'Plugin installed', 'edwiser-bridge' ) . '</span>';
					}
				} elseif ( isset( $request->msg ) ) {
					$status['message'] = $request->msg;
				} else {
					$status['message'] = __( 'Empty download link. Please check your license key or contact edwiser support for more detials.', 'edwiser-bridge' );
				}
			}
		}

		// if installed and license in valid and not activated then activate the plugin.
		if ( $installed && 'valid' === $license_status && ! $activated ) {
			$result = activate_plugin( $plugin_data['path'] );
			if ( is_wp_error( $result ) ) {
				$resp              = $result->get_error_messages();
				$status['install'] = $status['install'] . '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . __( 'plugin activation failed', 'edwiser-bridge' ) . '</span>';
			} else {
				$status['install'] = $status['install'] . '<span class="eb_license_success"><span class="dashicons dashicons-yes"></span>' . __( 'plugin activated', 'edwiser-bridge' ) . '</span>';
			}
		}
		return $status;
	}

	/**
	 * Download and install plugin.
	 *
	 * @param String $link Download link.
	 *
	 * @return String $status Status.
	 */
	public function eb_setup_wizard_download_and_install( $link ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();
		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $link );

		if ( is_wp_error( $result ) ) {
			$status = '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . $result->get_error_message() . '</span>';
		} elseif ( is_wp_error( $skin->result ) ) {
			$status = '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . $skin->result->get_error_message() . '</span>';
		} elseif ( $skin->get_errors()->has_errors() ) {
			$status = '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . $skin->get_error_messages() . '</span>';
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;

			$status = '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . __( 'Unable to connect to the filesystem. Please confirm your credentials.' ) . '</span>';

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
				$status = '<span class="eb_license_error"><span class="dashicons dashicons-no"></span>' . esc_html( $wp_filesystem->errors->get_error_message() ) . '</span>';
			}
		} else {
			$status = true;
		}
		return $status;
	}



}

new Eb_Setup_Wizard_Functions();
