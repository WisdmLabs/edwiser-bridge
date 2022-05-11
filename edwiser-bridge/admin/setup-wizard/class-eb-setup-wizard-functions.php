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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Eb_Setup_Wizard class.
 */
class Eb_Setup_Wizard_Functions {

	/**
	 * Current step
	 *
	 * @var string
	 */
	private $step = '';

	/**
	 * Steps for the setup wizard
	 *
	 * @var array
	 */
	private $steps = array();

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		/**
		 * Remaining tasks.
		 * 1. showing current step on refresh - DONE
		 * 2. Product sync.
		 * 3. Saving recommended settings.
		 * 4. coding standards.
		 * 5. nonce.
		 * 6. license.
		 * 7. Total flow.
		 * 8. pop-ups.
		 * 9. user sync Pop-up with progress.
		 * 10. Loader.
		 * 11. secret key. DONE
		 */

		if ( ! isset( $_POST['action'] ) && $_GET['page'] === 'eb-setup-wizard' ) {
			$setup_templates = new Eb_Setup_Wizard_Templates();
			add_action( 'admin_init', array( $setup_templates, 'eb_setup_wizard_template' ), 9 );
			add_action( 'admin_init', array( $this, 'eb_setup_steps_save_handler' ) );
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		$steps = $this->eb_setup_wizard_get_steps();

		add_action( 'wp_ajax_eb_setup_change_step', array( $this, 'eb_setup_change_step' ) );
		add_action( 'wp_ajax_eb_setup_close_setup', array( $this, 'eb_setup_close_setup' ) );

		add_action( 'wp_ajax_eb_setup_save_and_continue', array( $this, 'eb_setup_save_and_continue' ) );
		add_action( 'wp_ajax_eb_setup_test_connection', array( $this, 'eb_setup_test_connection_handler' ) );
		add_action( 'wp_ajax_eb_setup_manage_license', array( $this, 'eb_setup_manage_license' ) );

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

		$free_setup_steps = array(
			'initialize'                => array(
				'name'     => __( 'Setup Initialize', 'eb-textdoamin' ),
				'title'    => __( 'Edwiser Bridge plugin - Setup Initialization', 'eb-textdoamin' ),
				'function' => 'eb_setup_initialize',
				'sidebar'  => 0,
				'sub_step' => 0,
			),
			'free_installtion_guide'    => array(
				'name'     => __( 'Edwiser Bridge FREE plugin installation guide', 'eb-textdoamin' ),
				'title'    => __( 'Edwiser Bridge FREE plugin installation guide', 'eb-textdoamin' ),
				'function' => 'eb_setup_free_installtion_guide',
				'sidebar'  => 1,
				'sub_step' => 0,
			),
			'moodle_redirection'        => array(
				'name'     => __( 'Edwiser Bridge FREE plugin installation guide', 'eb-textdoamin' ),
				'title'    => __( 'Edwiser Bridge FREE Moodle plugin configuration', 'eb-textdoamin' ),
				'sidebar'  => 1,
				'function' => 'eb_setup_moodle_redirection',
				'sub_step' => 1,

			),
			'test_connection'           => array(
				'name'     => __( 'Connection test between WordPress and Moodle', 'eb-textdoamin' ),
				'title'    => __( 'Adding Moodle credential to WordPress', 'eb-textdoamin' ),
				'sidebar'  => 1,
				'function' => 'eb_setup_test_connection',
				'sub_step' => 0,

			),
			'course_sync'               => array(
				'sidebar'  => 1,
				'name'     => __( 'Courses syncronization', 'eb-textdoamin' ),
				'title'    => __( 'Synchronize Moodle courses', 'eb-textdoamin' ),
				'function' => 'eb_setup_course_sync',
				'sub_step' => 0,

			),
			'user_sync'                 => array(
				'sidebar'  => 1,
				'name'     => __( 'User syncronization', 'eb-textdoamin' ),
				'title'    => __( 'Synchronize Moodle users', 'eb-textdoamin' ),
				'function' => 'eb_setup_user_sync',
				'sub_step' => 0,

			),
			'free_recommended_settings' => array(
				'sidebar'  => 1,
				'name'     => __( 'Recommended settings', 'eb-textdoamin' ),
				'title'    => __( 'Edwiser Bridge FREE plugin recommended settings', 'eb-textdoamin' ),
				'function' => 'eb_setup_free_recommended_settings',
				'sub_step' => 0,

			),
			'free_completed_popup'      => array(
				'sidebar'  => 1,
				'name'     => __( 'Recommended settings', 'eb-textdoamin' ),
				'title'    => __( 'Edwiser Bridge FREE plugin recommended settings', 'eb-textdoamin' ),
				'function' => 'eb_setup_free_completed_popup',
				'sub_step' => 1,

			),

		);

		$pro_setup_steps = array(
			'pro_initialize'           => array(
				'sidebar'  => 1,
				'name'     => __( 'Initialize Edwiser Bridge PRO setup ', 'eb-textdoamin' ),
				'title'    => __( 'Initialize Edwiser Bridge PRO plugin setup ', 'eb-textdoamin' ),
				'function' => 'eb_setup_pro_initialize',
				'sub_step' => 0,
			),
			'license'                  => array(
				'sidebar'  => 1,
				'name'     => __( 'Edwiser Bridge PRO License setup', 'eb-textdoamin' ),
				'title'    => __( 'Install Edwiser Bridge PRO WordPress plugins', 'eb-textdoamin' ),
				'function' => 'eb_setup_license',
				'sub_step' => 0,
			),
			'mdl_plugins'              => array(
				'sidebar'  => 1,
				'name'     => __( 'Download Edwiser Bridge PRO Moodle plugins', 'eb-textdoamin' ),
				'title'    => __( 'Download Edwiser Bridge PRO Moodle plugins', 'eb-textdoamin' ),
				'function' => 'eb_setup_mdl_plugins',
				'sub_step' => 0,
			),
			'mdl_plugins_installation' => array(
				'sidebar'  => 1,
				'name'     => __( 'Let’s install Edwiser Bridge PRO Moodle plugins', 'eb-textdoamin' ),
				'title'    => __( 'Let’s install Edwiser Bridge PRO Moodle plugins', 'eb-textdoamin' ),
				'function' => 'eb_setup_mdl_plugins_installation',
				'sub_step' => 1,
			),
			'sso'                      => array(
				'sidebar'  => 1,
				'name'     => __( 'Single Sign On setup', 'eb-textdoamin' ),
				'title'    => __( 'Enter Single Sign On secret key', 'eb-textdoamin' ),
				'function' => 'eb_setup_sso',
				'sub_step' => 0,
			),
			'wi_products_sync'         => array(
				'sidebar'  => 1,
				'name'     => __( 'WooCommerce product creation', 'eb-textdoamin' ),
				'title'    => __( 'Create WooCommerce product of Moodle courses', 'eb-textdoamin' ),
				'function' => 'eb_setup_wi_products_sync',
				'sub_step' => 0,
			),
			'pro_settings'             => array(
				'sidebar'  => 1,
				'name'     => __( 'Edwiser Bridge PRO plugin settings', 'eb-textdoamin' ),
				'title'    => __( 'Recommended settings', 'eb-textdoamin' ),
				'function' => 'eb_setup_pro_settings',
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

		if ( 'free' === $setup_wizard['name'] ) {
			$steps = $free_setup_steps;
		} elseif ( 'pro' === $setup_wizard['name'] ) {
			$steps = $pro_setup_steps;
		} elseif ( 'free_and_pro' === $setup_wizard['name'] ) {
			$steps = array_merge( $free_setup_steps, $pro_setup_steps );
		}

		return $steps;
	}



	/**
	 * Added This new function instead of adding one by one function for wp_ajax hook, as by default parameter is not being set in each step callback so wrote below wrapper function for all of them and provided parameter 1.
	 */
	public function eb_setup_change_step() {
		$step                   = isset( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : '';
		$steps                  = $this->eb_setup_wizard_get_steps();
		$function               = $steps[ $step ]['function'];
		$setup_wizard_templates = new Eb_Setup_Wizard_Templates();

		$step_html = $setup_wizard_templates->$function( 1 );
	}

	/**
	 * Setup Wizard Test connection handler.
	 */
	public function eb_setup_test_connection_handler() {

		$url   = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

		$connection_helper = new EBConnectionHelper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connection_test_helper( $url, $token );

		wp_send_json_success( $response );

	}

	/**
	 * Setup Wizard Manage license.
	 */
	public function eb_setup_manage_license() {
		if ( ! class_exists( 'Licensing_Settings' ) ) {
			include_once plugin_dir_path( __DIR__ ) . 'settings/class-eb-settings-page.php';
			include_once plugin_dir_path( __DIR__ ) . 'licensing/class-licensing-settings.php';
		}

		$license_data = isset( $_POST['license_data'] ) ? sanitize_text_field( wp_unslash( $_POST['license_data'] ) ) : array();

		$license_data = (array) json_decode( $license_data );

		// Post data will provide key.
		// Here we will provide only activation functionality.
		// This action is the plugin name and 2nd parameter provided in function is licensse status action
		// $license_data['action'] = '';
		// $license_data['key']    = $_POST['key']; @codingStandardsIgnoreLine

		$response = array();

		foreach ( $license_data as $key => $value ) {
			if ( ! empty( $value ) ) {
				$license_handler  = new Licensing_Settings();
				$result           = $license_handler->wdm_install_plugin(
					array(
						'action'                       => $key,
						'edd_' . $key . '_license_key' => $value,
					),
					0
				);
				$response[ $key ] = $result;
			}
		}

		wp_send_json_success( array( 'result' => $response ) );
	}

	/**
	 * Setup Wizard Save step and redirect to next step.
	 */
	public function eb_setup_save_and_continue() {

		$data             = $_POST['data'];
		$current_step     = $data['current_step'];
		$next_step        = $data['next_step'];
		$is_next_sub_step = $data['is_next_sub_step'];

		$steps    = $this->eb_setup_wizard_get_steps();
		$function = $steps[ $next_step ]['function'];

		switch ( $current_step ) {
			case 'moodle_redirection':
				// Create web service and update data in EB settings.
				if ( isset( $data['mdl_url'] ) ) {
					$url = get_option( 'eb_connection' );
					$url = is_array( $url ) ? $url : array();

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
				if ( ! $data->existing_site ) {
					$sync_options['eb_synchronize_categories'] = '1';
					$sync_options['eb_synchronize_previous']   = '1';
					$sync_options['eb_synchronize_draft']      = '1';

					$response = edwiser_bridge_instance()->course_manager()->course_synchronization_handler( $sync_options );
				}

				break;

			case 'user_sync':
				break;

			case 'free_recommended_settings':
				$general_settings                           = get_option( 'eb_general' );
				$general_settings['eb_useraccount_page_id'] = $data['user_account_page'];
				$general_settings['eb_enable_registration'] = isset( $data['user_account_creation'] ) ? 1 : 0;
				$result                                     = update_option( 'general_settings', $general_settings );
				$function                                   = 'eb_setup_free_completed_popup';

				break;

			case 'free_completed_popup':
				break;

			case 'sso':
				$old_sso_settings = get_option( 'eb_sso_settings_general' );
				if ( isset( $data['sso_key'] ) ) {
					$old_sso_settings['eb_sso_secret_key'] = $data['sso_key'];
				}
				update_option( 'eb_sso_settings_general', $old_sso_settings );
				break;

			case 'wi_products_sync':
				require_once ABSPATH . '/wp-content/plugins/woocommerce-integration/includes/class-bridge-woocommerce.php';
				require_once ABSPATH . '/wp-content/plugins/woocommerce-integration/includes/class-bridge-woocommerce-course.php';

				$sync_options = array(
					'bridge_woo_synchronize_product_categories' => 1,
					'bridge_woo_synchronize_product_update'     => 1,
					'bridge_woo_synchronize_product_create'     => 1,
				);

				$course_woo_plugin = new EdwiserBridge\BridgeWoocommerceCourse( BridgeWoocommerce()->getPluginName(), BridgeWoocommerce()->getVersion() );
				$response          = $course_woo_plugin->bridgeWooProductSyncHandler( $sync_options );

				break;

			case 'pro_settings':
				$function = 'eb_setup_pro_completed_popup';
				break;

			default:
				break;
		}

		// Check current step.
		// Check if there is any data to be saved.

		// Save step form progress.
		$setup_data             = get_option( 'eb_setup_data' );
		$setup_data['progress'] = $current_step;
		update_option( 'eb_setup_data', $setup_data );

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

		$setup_wizard_templates = new Eb_Setup_Wizard_Templates();
		$next_step_html         = $setup_wizard_templates->$function( 1 );

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

			if ( $found_step ) {
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
	 * Setup Wizard Enqueue scripts.
	 */
	public function enqueue_scripts() {

		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		// Include CSS.
		wp_enqueue_style(
			'eb-setup-wizard-css',
			$eb_plugin_url . 'admin/assets/css/eb-setup-wizard.css',
			array( 'dashicons' ),
			time(),
		);

		wp_register_script(
			'eb-setup-wizard-js',
			$eb_plugin_url . 'admin/assets/js/eb-setup-wizard.js',
			array( 'jquery', 'jquery-ui-dialog' ),
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

			)
		);

	}

	/**
	 * Setup Wizard save steps data
	 */
	public function eb_setup_steps_save_handler() {

		$url   = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

		$connection_helper = new EBConnectionHelper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connection_test_helper( $url, $token );

		wp_send_json_success( $return );

	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {

		$welcome_page_name  = esc_html__( 'About Edwiser Bridge', 'edwiser-bridge' );
		$welcome_page_title = esc_html__( 'Welcome to Edwiser Bridge', 'edwiser-bridge' );

		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		add_dashboard_page(
			'',
			'',
			'read',
			'eb-setup-wizard',
		);

	}

	/**
	 * Setup Wizard Steps HTML content
	 */
	public function eb_setup_steps_html() {
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
				$class = '';
				$html  = '<span class="eb-setup-step-circle" > </span>';

				if ( 1 === $completed ) {
					$class = 'eb-setup-step-completed';
					$html  = '<span class="dashicons dashicons-arrow-right-alt2"></span>';
				}

				if ( $key === $progress ) {
					$completed = 0;
				}

				?>
			<li class='eb-setup-step  <?php echo wp_kses( $class, $allowed_tags ) . '-wrap'; ?>'>
				<?php echo wp_kses( $html, $allowed_tags ); ?>
				<span class='eb-setup-steps-title <?php echo wp_kses( $class, $allowed_tags ); ?>' data-step="<?php esc_attr_e( $key ); ?>">
					<?php esc_attr_e( $step['name'], 'edwiser-bridge' ); ?>
				</span>
			</li>

				<?php
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

		$steps = $this->eb_setup_wizard_get_steps();
		$step  = 'initialize';
		/**
		 * Handle form submission.
		 */
		if ( ! empty( $_POST['eb_setup_free_initialize'] ) ) {

			// save set up data.
			get_option( 'eb_setup_data' );
			$chosen_setup = '';

			if ( isset( $_POST['eb_free_setup'] ) ) {
				$chosen_setup = 'free';
			} elseif ( isset( $_POST['eb_pro_setup'] ) ) {
				$chosen_setup = 'pro';
			} elseif ( isset( $_POST['eb_free_and_pro'] ) ) {
				$chosen_setup = 'free_and_pro';
			}

			$setup_array = array( 'name' => $chosen_setup );

			update_option( 'eb_setup_data', $setup_array );
			$step = 'free_installtion_guide';
		}

		/**
		 * Handle page refresh.
		 */
		if ( isset( $_GET['current_step'] ) && ! empty( $_GET['current_step'] ) ) {
			$step = $_GET['current_step'];

		}

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



}


new Eb_Setup_Wizard_Functions();

