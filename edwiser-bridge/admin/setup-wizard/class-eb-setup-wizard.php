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
class Eb_Setup_Wizard {

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

		// if ( apply_filters( 'woocommerce_enable_setup_wizard', true ) && current_user_can( 'manage_woocommerce' ) ) {
			// add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			// add_action( 'admin_init', array( $this, 'setup_wizard' ) );
			// add_action( 'admin_init', array( $this, 'eb_setup_wizard_handler' ) );



			// add_action( 'admin_init', array( $this, 'eb_setup_wizard_template' ) );
			// add_action( 'admin_menu', array( $this, 'admin_menus' ) );

			// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );




			$steps = $this->eb_setup_wizard_get_steps();

			foreach( $steps as $key => $step ) {

				error_log('::: eb_setup_'.print_r($key, 1));
				error_log(':::: wp_ajax_'.print_r($step['function'], 1));

				add_action( 'wp_ajax_' . $step['function'], array( $this, 'eb_setup_' . $key ) );
			}


		// }
	}


	public function enqueue_scripts() {

		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		// Include CSS
		wp_enqueue_style(
			'eb-setup-wizard-css',
			$eb_plugin_url . 'admin/assets/css/eb-setup-wizard.css',
			array('dashicons'),
		);

		wp_register_script(
			'eb-setup-wizard-js',
			$eb_plugin_url . 'admin/assets/js/eb-setup-wizard.js',
			array( 'jquery', 'jquery-ui-dialog' ),
		);



		wp_localize_script(
			'eb-setup-wizard-js',
			'eb_setup_wizard',
			array(
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				// 'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				// 'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
			)
		);


	}


	/**
	 * 
	 */
	public function eb_setup_wizard_get_steps() {

		/**
		 * Loop through the steps.
		 * Ajax call for each of the steps and save.
		 * step change logic.
		 * load data on step change.
		 * 
		 */

		$free_setup_steps = array(
			'initialize' => array(
				'name'    => __( 'Setup Initialize', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_initialize' ),
				'function'    => 'eb_setup_initialize',
				'sidebar' => 0,
				'handler' => array( $this, 'eb_setup_initialize_save' ),
			),
			'free_installtion_guide' => array(
				'name'    => __( 'Edwiser Bridge FREE plugin installation guide', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_free_installtion_guide' ),
				'function'    => 'eb_setup_free_installtion_guide',

				'sidebar' => 1,
				'handler' => array( $this, 'eb_setup_free_installtion_guide_save' ),
			),
			'test_connection' => array(
				'name'    => __( 'Connection test between WordPress and Moodle', 'eb-textdoamin' ),
				'sidebar' => 1,
				'view'    => array( $this, 'eb_setup_test_connection' ),
				'function'    => 'eb_setup_test_connection',

				'handler' => array( $this, 'eb_setup_' ),
			),
			'course_sync' => array(
				'sidebar' => 1,
				'name'    => __( 'Courses syncronization', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_course_sync' ),
				'function'    => 'eb_setup_course_sync',

				'handler' => array( $this, 'eb_setup_' ),
			),
			'user_sync' => array(
				'sidebar' => 1,
				'name'    => __( 'User syncronization', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_user_sync' ),
				'function'    => 'eb_setup_user_sync',

				'handler' => array( $this, 'eb_setup_recommended_save' ),
			),
			'free_recommended_settings' => array(
				'sidebar' => 1,
				'name'    => __( 'Recommended settings', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_free_recommended_settings' ),
				'function'    => 'eb_setup_free_recommended_settings',

				'handler' => array( $this, 'eb_setup_activate_save' ),
			),

		);

		$pro_setup_steps = array(
			'pro_initialize'       => array(
				'sidebar' => 1,
				'name'    => __( 'Initialize Edwiser Bridge PRO setup ', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_pro_initialize' ),
				'function'    => 'eb_setup_free_recommended_settings',

				'handler' => array( $this, 'eb_setup_activate_save' ),
			),
			'license'       => array(
				'sidebar' => 1,
				'name'    => __( 'Edwiser Bridge PRO License setup', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_license' ),
				'function'    => 'eb_setup_license',

				'handler' => array( $this, 'eb_setup_activate_save' ),
			),
			'wp_plugins'       => array(
				'sidebar' => 1,
				'name'    => __( 'Edwiser Bridge PRO WordPress plugin installation', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_wp_plugins' ),
				'function'    => 'eb_setup_wp_plugins',

				'handler' => array( $this, 'eb_setup_activate_save' ),
			),
			'mdl_plugins'       => array(
				'sidebar' => 1,
				'name'    => __( 'Download Edwiser Bridge PRO Moodle plugins', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_mdl_plugins' ),
				'function'    => 'eb_setup_mdl_plugins',

				'handler' => array( $this, 'eb_setup_activate_save' ),
			),
			'sso'     => array(
				'sidebar' => 1,
				'name'    => __( 'Single Sign On setup', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_sso' ),
				'function'    => 'eb_setup_sso',

				'handler' => '',
			),
			'wi_products_sync'     => array(
				'sidebar' => 1,
				'name'    => __( 'WooCommerce product creation', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_wi_products_sync' ),
				'function'    => 'eb_setup_wi_products_sync',

				'handler' => '',
			),
			'pro_settings'     => array(
				'sidebar' => 1,
				'name'    => __( 'Edwiser Bridge PRO plugin settings', 'eb-textdoamin' ),
				'view'    => array( $this, 'eb_setup_pro_settings' ),
				'function'    => 'eb_setup_pro_settings',

				'handler' => '',
			),
		);


		/**
		 * Check the value of the selected setup.
		 * If free don't show only free plugins steps.
		 * If pro select only pro steps.
		 * If selected both merge above two arrays and show those steps.
		 */

		return array_merge( $free_setup_steps, $pro_setup_steps );
	}



	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		// if ( ! isset( $_GET['edw-wc-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['edw-wc-nonce'] ) ), 'edw-wc-nonce' ) ) {
		// 	return;
		// }

		// if ( ! isset( $_GET['page'] ) || empty( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) {
		// 	return;
		// }

		$welcome_page_name  = esc_html__( 'About Edwiser Bridge', 'eb-textdomain' );
		$welcome_page_title = esc_html__( 'Welcome to Edwiser Bridge', 'eb-textdomain' );

		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		add_dashboard_page(
			'',
			'',
			'manage_options',
			'eb-setup-wizard',
			// array( $this, 'eb_setup_wizard_template' )
		);

	}



	public function eb_setup_steps_html() {
		$steps = $this->eb_setup_wizard_get_steps();

		if ( ! empty( $steps ) && is_array( $steps ) ) {
		?>
		<ul class="eb-setup-steps">

		<?php
			foreach( $steps as $key => $step ) {
			?>
			<li class="eb-setup-step eb-setup-step-completed-wrap">
				<span class="eb-setup-step-circle" > </span> </span>
				<span class="eb-setup-steps-title eb-setup-step-completed" data-step="<?php esc_attr_e( $key ); ?>">
					<?php esc_attr_e( $step['name'], 'eb-textdomain' ); ?>
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
	 * 
	 */
	public function eb_setup_wizard_template(  ) {
		// Intialization.

		$this->setup_wizard_header();

		// content area.
			// sidebar.
				?>

				<div class="eb-setup-content-area">

					<!-- Sidebar -->
					<div class="eb-setup-sidebar">

						<?php
						
						$this->eb_setup_steps_html();
						
						?>

						<!--<ul class="eb-setup-steps">

							 <li class="eb-setup-step eb-setup-step-completed">
									<span class="eb-setup-step-circle" > <span class="dashicons dashicons-yes"></span> </span>
									<span class="eb-setup-steps-title">Setup Initialize</span>
							</li>
							
							<li class="eb-setup-step eb-setup-step-active">
								<span class="eb-setup-step-circle" datetime="10:03"></span>
								<span class="eb-setup-steps-title">Edwiser Bridge FREE plugin installation guide</span>
							</li>
						
							<li class="eb-setup-step">
							<span class="eb-setup-step-circle" datetime="10:03"></span> 
							<span class="eb-setup-steps-title">Connection test Between WordPress and Moodle</span></li>
						
							<li class="eb-setup-step">
								<span class="eb-setup-step-circle" datetime="10:03"></span> 
								<span class="eb-setup-steps-title">Connection test Between WordPress and Moodle</span>
							</li>
						
							<li class="eb-setup-step">
								<time class="eb-setup-step-circle" datetime="10:03"></time> 
								<span class="eb-setup-steps-title">Connection test Between WordPress and Moodle</span>
							</li>
						
							<li class="eb-setup-step">
								<time class="eb-setup-step-circle" datetime="10:03"></time> 
								<span class="eb-setup-steps-title">Finish</span>
							</li> 


						</ul> -->


					<!--  -->


					</div>

					<!-- content -->
					<div class="eb-setup-content">
						<?php 
						$this->eb_setup_user_sync();
						?>
					</div>

				</div>

				<?php

				// sidebar progress.
			// Content.

		// Footer part.
		$this->setup_wizard_footer();

		exit();

	}



	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header( $title = '' ) {

		error_log('setup_wizard_header ::: ');
		var_dump('setup_wizard_header ::: ');

		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();


		// same as default WP from wp-admin/admin-header.php.
		$wp_version_class = 'branch-' . str_replace( array( '.', ',' ), '-', floatval( get_bloginfo( 'version' ) ) );

		set_current_screen();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php /*esc_html_e( 'WooCommerce &rsaquo; Setup Wizard', 'woocommerce' );*/ ?></title>
			<?php do_action( 'admin_enqueue_scripts' ); ?>
			<?php wp_print_scripts( 'eb-setup-wizard-js' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>

		</head>


		<body class="wc-setup wp-core-ui <?php echo esc_attr( 'wc-setup-step__' . $this->step ); ?> <?php echo esc_attr( $wp_version_class ); ?>">

		<header class="eb-setup-wizard-header">

			<div class="eb-setup-header-logo">
				<div class="eb-setup-header-logo-img-wrap">
					<img src="<?php echo esc_attr( $eb_plugin_url . 'images/wordpress-logo.png' ); ?>" />
				</div>
			</div>

			<div class="eb-setup-header-title-wrap">

				<div class="eb-setup-header-title">

title
				
				</div>

			</div>
		
		</header>
		<?php
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		?>
			<footer class="eb-setup-wizard-footer">

				<div class="eb-setup-footer-copyright">
					<?php esc_html_e( 'Copyright © 2022 Edwiser | Brought to you by WisdmLabs and Powered by Edwiser', 'eb-textdomain' ); ?>
				</div>

				<div class="eb-setup-footer-button">
					<a>
						<?php esc_html_e( 'Contact Us', 'eb-textdomain' ); ?>
					</a>
				</div>

			</footer>

		</body>
	</html>


		<?php
	}






	public function eb_setup_initialize() {

error_log('eb_setup_initialize ::: ');

		ob_start();

		?>

		<div class="eb_setup_free_initialize">
			<div>

				<p> <?php esc_html_e( 'What are you trying to setup?', 'eb-textdomain' ); ?> </p>

				<div class="eb_setup_free_initialize_inp_wrap">
					<input type="checkbox" name="eb_free_setup">
					<label> <?php esc_html_e( 'Only Edwiser Bridge FREE', 'eb-textdomain' ); ?> </label>
				</div>

				<div class="eb_setup_free_initialize_inp_wrap">
					<input type="checkbox" name="eb_free_setup">
					<label> <?php esc_html_e( 'Only Edwiser Bridge PRO', 'eb-textdomain' ); ?> </label>
				</div>

				<div class="eb_setup_free_initialize_inp_wrap">
					<input type="checkbox" name="eb_free_setup">
					<label> <?php esc_html_e( 'Both, Edwiser Bridge FREE & PRO', 'eb-textdomain' ); ?> </label>
				</div>

				<button class="eb_setup_btn"> <?php esc_html_e( 'Start The Setup', 'eb-textdomain' ); ?> </button>
			</div>

			<div>
				<fieldset>
					<legend> <?php esc_html_e( 'Note', 'eb-textdomain' ); ?> </legend> 
					<p>
						<?php esc_html_e( 'It approximately takes 10-15 minutes to complete the setup since we will be installing plugins, enabling mandatory settings and synchronizing courses and users.', 'eb-textdomain' ); ?>
					</p>
	
				</fieldset>
			</div>
		</div>

		<?php

		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
		


	}

	public function eb_setup_free_installtion_guide() {
		ob_start();

		?>
		<div class="eb_setup_installation_guide">
			<div>
				<span> <?php esc_html_e( 'To start the setup you need to have the following plugins installed on WordPress & Moodle.', 'eb-textdomain' ); ?> </span>
				
				<div>

					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bridge Free WordPress plugin', 'eb-textdomain' ); ?> <p>
					
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bridge Free Moodle plugin', 'eb-textdomain' ); ?> <p>

				</div>


				<span> <?php esc_html_e( 'To start the setup you need to have the following plugins installed on WordPress & Moodle.', 'eb-textdomain' ); ?> </span>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn"> <?php esc_html_e( 'Continue the setup', 'eb-textdomain' ); ?> </button>
				</div>

			</div>

			<div>
				<div>
					<div class="accordion"> <?php esc_html_e( 'What to do if I have not installed the Moodle plugin? ', 'eb-textdomain' ); ?> </div>
					<div class="panel">

						<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Download the plugin now', 'eb-textdomain' ); ?> </button>

						<p>
							<span> <?php esc_html_e( 'After download please follow the steps below;', 'eb-textdomain' ); ?> </span>
						
							<ul>
								<li> <?php esc_html_e( 'Login to your Moodle site with Adminstrative access', 'eb-textdomain' ); ?></li>
								<li><?php esc_html_e( 'Navigate to Site adminstration > Plugins > Install plugins ', 'eb-textdomain' ); ?></li>
								<li><?php esc_html_e( ' Upload the Edwiser Bridge FREE Moodle plugin here', 'eb-textdomain' ); ?></li>
								<li><?php esc_html_e( 'We will assist you with the rest of the setup from there 🙂', 'eb-textdomain' ); ?></li>
							</ul>

						</p>
					</div>
				</div>

				<div>
					<div class="accordion"> <?php esc_html_e( 'What to do if I have not installed the Moodle plugin?  ', 'eb-textdomain' ); ?> </div>
					<div class="panel">
					<p>  </p>
					</div>
				</div>
			</div>
	
	
		</div>

		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}

	public function eb_setup_connection_url() {
		ob_start();

		?>
		<div class="eb_setup_conn_url">

			<div>
			
				<span class="eb_setup_h2"> <?php esc_html_e( 'Enter your Moodle URL to intiate the configuration on moodle site for Edwiser Bridge FREE Moodle plugin.', 'eb-textdomain' ); ?> </span>

				<div class="eb_setup_conn_url_inp_wrap">
					<p> <label class="eb_setup_h2"> <?php esc_html_e( 'Moodle URL', 'eb-textdomain' ); ?></label></p>
					<input class="eb_setup_inp" type="text" >
				</div>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn"> <?php esc_html_e( 'Submit & Continue', 'eb-textdomain' ); ?> </button>
				</div>

			</div>


			<div>

				<div>
					<div class="accordion"> <?php esc_html_e( 'Unable to navigate directly to the Edwiser Bridge FREE plugin setup on Moodle from the above step?  ', 'eb-textdomain' ); ?> </div>
					<div class="panel">
					<p>  </p>
					</div>
				</div>

				<div>
					<div class="accordion"> <?php esc_html_e( 'Unable to navigate directly to the Edwiser Bridge FREE plugin setup on Moodle from the above step?  ', 'eb-textdomain' ); ?> </div>
					<div class="panel">
					<p>  </p>
					</div>
				</div>
		
			</div>

		</div>

		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}



	public function eb_setup_test_connection() {
		ob_start();

		?>
		<div class="eb_setup_test_connection">
			<div>
				<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Enter your Moodle URL to intiate the configuration on moodle site for Edwiser Bridge FREE Moodle plugin.', 'eb-textdomain' ); ?> </div>

				<div>
					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle URL', 'eb-textdomain' ); ?></label></p>
						<input class="eb_setup_inp" type="text" >
					</div>

					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle access token', 'eb-textdomain' ); ?></label> </p>
						<input class="eb_setup_inp" type="text" >

					</div>
					
					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Language code', 'eb-textdomain' ); ?></label></p>
						<input class="eb_setup_inp" type="text" >

					</div>
				
				</div>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn"> <?php esc_html_e( 'Submit & Continue', 'eb-textdomain' ); ?> </button>
				</div>

			</div>

			<div>
				<div class="eb_setup_separator"> 
					<div class="eb_setup_hr"><hr></div>
					<div> <span> <?php esc_html_e( ' OR ', 'eb-textdomain'); ?> </span> </div>
					<div class="eb_setup_hr"><hr></div>
				</div>
				
				<div>
					<div>
						<span class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Choose and upload the Moodle Credential file here', 'eb-textdomain' ); ?> </span>
						<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Submit & Continue', 'eb-textdomain'); ?> </button>
						
					</div>

					<div>
						<span class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"> </span> <?php esc_html_e( 'Choose and upload the Moodle Credential file here', 'eb-textdomain' ); ?> </span>
						<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Submit & Continue', 'eb-textdomain'); ?> </button>
					
					</div>
				</div>
		
			</div>

		</div>

		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}


	public function eb_setup_course_sync() {
		ob_start();

		?>
		<div class="eb_setup_course_sync">
			
			<span class="eb_setup_h2"> <?php esc_html_e( 'This will synchronize all your Moodle course ID, title, description from Moodle to WordPress.', 'eb-textdomain' ); ?> </span>
			
			<div class="eb_setup_course_sync_note">

				<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'If “Enabled”, synchronized courses will be set as ‘Published’ on WordPress.', 'eb-textdomain' ); ?> </div>
				
				<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'If “Disabled”, courses will be synchronized as ‘Draft’.', 'eb-textdomain' ); ?> </div>

				<div class="eb_setup_course_sync_inp_wrap">
					<input type="checkbox" >
					<label> <?php esc_html_e( 'Enabled - Synchronized courses will be set as ‘Published’ ', 'eb-textdomain' ); ?></label>
					
				</div>

				<div class="eb_setup_course_sync_btn_wrap">
					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'eb-textdomain'); ?> </button>
					<button class="eb_setup_btn"> <?php esc_html_e( 'Synchronize the courses', 'eb-textdomain'); ?> </button>
				</div>
				

			</div>

			<div>
				<fieldset>
					<legend> <?php esc_html_e( 'Note', 'eb-textdomain' ); ?> </legend>
					<p>
						<?php esc_html_e( 'It approximately takes 10-15 minutes to complete the setup since we will be installing plugins, enabling mandatory settings and synchronizing courses and users.', 'eb-textdomain' ); ?>

					</p>
	
				</fieldset>
			</div>


		</div>

		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}



	public function eb_setup_user_sync() {
		ob_start();

		?>
		<div>
		<?php
		/**
		 * If Moodle has more than 2000 users.
		 * Please show other screen. 
		 */
		$users = 2199;
		if ( $users < 2000 ) {
		?>
			<div class="eb_setup_user_sync">

				<span class="eb_setup_h2"> <?php esc_html_e( 'This will synchronize all your Moodle users from Moodle to WordPress.', 'eb-textdomain' ); ?> </span>
			
				<div class="eb_setup_user_sync_note">

					<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'If “Enabled”, send email notification to all synchronized users with their login credentials.', 'eb-textdomain' ); ?> </div>
					
					<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'If “Disabled”, it will not send email notification to all synchronized users', 'eb-textdomain' ); ?> </div>

					<div class="eb_setup_user_sync_inp_wrap">
						<input type="checkbox" >
						<label> <?php esc_html_e( 'Enabled - Synchronized courses will be set as ‘Published’ ', 'eb-textdomain' ); ?></label>
						
					</div>
				</div>


		<?php
		} else {
			?>
			<div class="eb_setup_user_sync">

				<div>
					<!-- dashicons -->
					<span class="dashicons dashicons-warning"></span>
				</div>

				<div>
					<p>
						<?php esc_html_e( 'We have noticed that you have 2500 Moodle users and the synchronization would take approximately half an hour. ', 'eb-textdomain' ); ?>
					</p>
				
					<p>
						<?php esc_html_e( 'We strongly recommend you to synchronize the users manually by referring to the documentation link.', 'eb-textdomain' ); ?>
					</p>

				</div>

			<?php
		}

		?>

				<div class="eb_setup_user_sync_btn_wrap">
					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'eb-textdomain'); ?> </button>
					<button class="eb_setup_btn"> <?php esc_html_e( 'Synchronize the courses', 'eb-textdomain'); ?> </button>
				</div>

			</div>

			<div>
				<fieldset>
					<legend> <?php esc_html_e( 'Note', 'eb-textdomain' ); ?> </legend>
					<p>
						<?php esc_html_e( 'It approximately takes 10-15 minutes to complete the setup since we will be installing plugins, enabling mandatory settings and synchronizing courses and users.', 'eb-textdomain' ); ?>

					</p>
	
				</fieldset>
			</div>


		</div>

		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}


	public function eb_setup_recommended_setting() {
		ob_start();

		?>
		<div>
			<span> <?php esc_html_e( 'Enable user registration.', 'eb-textdomain' ); ?> </span>

			<div>
				<input type="checkbox" >
				<label> <?php esc_html_e( 'Enable user account creation on Edwiser Bridge user-account page’ ', 'eb-textdomain' ); ?></label>
			</div>

			<span>  <?php esc_html_e( 'Default page is set to Edwiser Bridge - User Account.', 'eb-textdomain' ); ?> </span>

			<div>

			</div>


			<button class="eb_setup_sec_btn"> <?php esc_html( 'Back', 'eb-textdomain'); ?> </button>
			<button class="eb_setup_btn"> <?php esc_html( 'Synchronize the courses', 'eb-textdomain'); ?> </button>

		</div>


		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}

	public function eb_setup_pro_initialize() {

	}

	public function eb_setup_license() {

	}

	public function eb_setup_wp_plugins() {

	}


	public function eb_setup_mdl_plugins() {

	}

	public function eb_setup_sso() {

	}


	public function eb_setup_wi_product_sync() {

	}


	public function eb_setup_pro_settings() {

	}







}


new Eb_Setup_Wizard();

