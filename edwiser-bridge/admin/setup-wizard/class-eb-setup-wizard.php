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


			if ( ! isset( $_POST['action'] ) && $_GET['page'] === 'eb-setup-wizard' ) {

				add_action( 'admin_init', array( $this, 'eb_setup_wizard_template' ), 9 );
				add_action( 'admin_init', array( $this, 'eb_setup_steps_save_handler' ) );
				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			}
			

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );


			$steps = $this->eb_setup_wizard_get_steps();

			foreach( $steps as $key => $step ) {

				add_action( 'wp_ajax_' . $step['function'], array( $this, 'eb_setup_' . $key ) );
			}

			add_action( 'wp_ajax_eb_setup_save_and_continue', array( $this, 'eb_setup_save_and_continue' ) );
			add_action( 'wp_ajax_eb_setup_test_connection', array( $this, 'eb_setup_test_connection_handler' ) );

	}




	public function eb_setup_test_connection_handler() {

var_dump('eb_setup_test_connection_handler ::: ');

		$url   = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

		$connection_helper = new EBConnectionHelper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connection_test_helper( $url, $token );

		wp_send_json_success($response);


	}




	public function eb_setup_save_and_continue() {
		// $setup_wizard_handler = new \eb_setup_wizard();


		// var_dump($_POST);
		// var_dump($_POST['data']);


        $data =  $_POST['data'];

        $current_step = $data['current_step'];
        $next_step = $data['next_step'];
        $is_next_sub_step = $data['is_next_sub_step'];

		
error_log('current_step ::: '.print_r($current_step, 1));
error_log('next_step ::: '.print_r($next_step, 1));
error_log('is_next_sub_step ::: '.print_r($is_next_sub_step, 1));



        // $setup_wizard_handler = new \eb_setup_wizard();
        $steps = $this->eb_setup_wizard_get_steps();





		$free_setup_steps = array(
			
			'user_sync' => array(
				'sidebar' => 1,
				'name'    => __( 'User syncronization', 'eb-textdoamin' ),
				'function'    => 'eb_setup_user_sync',
				'sub_step' => 0,

			),
			'free_recommended_settings' => array(
				'sidebar' => 1,
				'name'    => __( 'Recommended settings', 'eb-textdoamin' ),
				'function'    => 'eb_setup_free_recommended_settings',
				'sub_step' => 0,

			),

		);

		$pro_setup_steps = array(
			'pro_initialize'       => array(
				'sidebar' => 1,
				'name'    => __( 'Initialize Edwiser Bridge PRO setup ', 'eb-textdoamin' ),
				'function'    => 'eb_setup_pro_initialize',
				'sub_step' => 0,

			),
			'license'       => array(
				'sidebar' => 1,
				'name'    => __( 'Edwiser Bridge PRO License setup', 'eb-textdoamin' ),
				'function'    => 'eb_setup_license',
				'sub_step' => 0,

			),
			// 'wp_plugins'       => array(
			// 	'sidebar' => 1,
			// 	'name'    => __( 'Edwiser Bridge PRO WordPress plugin installation', 'eb-textdoamin' ),
			// 	'function'    => 'eb_setup_wp_plugins',
			// 	'sub_step' => 0,

			// ),
			'mdl_plugins'       => array(
				'sidebar' => 1,
				'name'    => __( 'Download Edwiser Bridge PRO Moodle plugins', 'eb-textdoamin' ),
				'function'    => 'eb_setup_mdl_plugins',
				'sub_step' => 0,

			),
			'sso'     => array(
				'sidebar' => 1,
				'name'    => __( 'Single Sign On setup', 'eb-textdoamin' ),
				'function'    => 'eb_setup_sso',
				'sub_step' => 0,

			),
			'wi_products_sync'     => array(
				'sidebar' => 1,
				'name'    => __( 'WooCommerce product creation', 'eb-textdoamin' ),
				'function'    => 'eb_setup_wi_products_sync',
				'sub_step' => 0,

			),
			'pro_settings'     => array(
				'sidebar' => 1,
				'name'    => __( 'Edwiser Bridge PRO plugin settings', 'eb-textdoamin' ),
				'function'    => 'eb_setup_pro_settings',
				'sub_step' => 0,

			),
		);


       switch ( $current_step ) {
           case 'moodle_redirection':


               // Create web service and update data in EB settings
                if ( isset( $data['mdl_url'] ) ) {


					$url = get_option( 'eb_connection' );
					$url = is_array( $url ) ? $url : array(); 

					$url['eb_url'] = $data['mdl_url'];

					// $url = array_filter( $url );

					update_option( 'eb_connection', $url );

				}
               break;

            case 'test_connection':

                if ( isset( $data['mdl_url'] ) && isset( $data['mdl_token'] ) && isset( $data['mdl_lang_code'] ) ) {
					$url = get_option( 'eb_connection' );
					$url['eb_url']          = $data['mdl_url'];
					$url['eb_access_token'] = $data['mdl_token'];

					update_option( 'eb_connection', $url );

					$general_settings = get_option( 'eb_general' );
					$language         = $data['mdl_lang_code'];
					$general_settings['eb_language_code'] = $language ;

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

// var_dump('1111');


				break;
           
           default:

               break;
       }





        // Check current step.
        // Check if there is any data to be saved.


	   // Save step form progress




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
        $function = $steps[$next_step]['function'];
        $next_step_html = $this->$function( 1 );


	}


	public function get_next_step( $current_step ){

        $steps = $this->eb_setup_wizard_get_steps();
        $step = '';
        $found_step = 0;
        foreach ($steps as $key => $value) {

            if ( $found_step ) {
                $step = $key;
                break;
            }

            if ( $current_step == $key ) {
                $found_step = 1;
            }

            

        }


        return $step;
    }


	public function enqueue_scripts() {

		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		// Include CSS
		wp_enqueue_style(
			'eb-setup-wizard-css',
			$eb_plugin_url . 'admin/assets/css/eb-setup-wizard.css',
			array('dashicons'),
			time(),
		);

		wp_register_script(
			'eb-setup-wizard-js',
			$eb_plugin_url . 'admin/assets/js/eb-setup-wizard.js',
			array( 'jquery', 'jquery-ui-dialog' ),
		);

		$setup_nonce    = wp_create_nonce( 'eb_setup_wizard' );
		$sync_nonce    = wp_create_nonce( 'check_sync_action' );


		wp_localize_script(
			'eb-setup-wizard-js',
			'eb_setup_wizard',
			array(
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'plugin_url'                      => $eb_plugin_url,
				'nonce'                           => $setup_nonce,
				'sync_nonce'                      => $sync_nonce,
				'msg_user_link_to_moodle_success' => esc_html__( 'User\'s linked to moodle successfully.', 'eb-textdomain' ),
				'msg_con_success'                 => esc_html__( 'Connection successful, Please save your connection details.', 'eb-textdomain' ),
				'msg_courses_sync_success'        => esc_html__( 'Courses synchronized successfully.', 'eb-textdomain' ),
				'msg_con_prob'                    => esc_html__( 'There is a problem while connecting to moodle server.', 'eb-textdomain' ),
				'msg_err_users'                   => esc_html__( 'Error occured for following users: ', 'eb-textdomain' ),
				'msg_user_sync_success'           => esc_html__( 'User\'s course enrollment status synced successfully.', 'eb-textdomain' ),

			)

		);


	}


	public function eb_setup_steps_save_handler(){
		
		$url   = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

		$connection_helper = new EBConnectionHelper( $this->plugin_name, $this->version );
		$response          = $connection_helper->connection_test_helper( $url, $token );
		
		wp_send_json_success($return);
		
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
				'function'    => 'eb_setup_initialize',
				'sidebar' => 0,
				'sub_step' => 0
			),
			'free_installtion_guide' => array(
				'name'    => __( 'Edwiser Bridge FREE plugin installation guide', 'eb-textdoamin' ),
				'function'    => 'eb_setup_free_installtion_guide',
				'sidebar' => 1,
				'sub_step' => 0,
			),
			'moodle_redirection' => array(
				'name'    => __( 'Connection test between WordPress and Moodle', 'eb-textdoamin' ),
				'sidebar' => 1,
				'function'    => 'eb_setup_moodle_redirection',
				'sub_step' => 1,

			),
			'test_connection' => array(
				'name'    => __( 'Connection test between WordPress and Moodle', 'eb-textdoamin' ),
				'sidebar' => 1,
				'function'    => 'eb_setup_test_connection',
				'sub_step' => 0,

			),
			'course_sync' => array(
				'sidebar' => 1,
				'name'    => __( 'Courses syncronization', 'eb-textdoamin' ),
				'function'    => 'eb_setup_course_sync',
				'sub_step' => 0,

			),
			'user_sync' => array(
				'sidebar' => 1,
				'name'    => __( 'User syncronization', 'eb-textdoamin' ),
				'function'    => 'eb_setup_user_sync',
				'sub_step' => 0,

			),
			'free_recommended_settings' => array(
				'sidebar' => 1,
				'name'    => __( 'Recommended settings', 'eb-textdoamin' ),
				'function'    => 'eb_setup_free_recommended_settings',
				'sub_step' => 0,

			),

		);

		$pro_setup_steps = array(
			'pro_initialize'       => array(
				'sidebar' => 1,
				'name'    => __( 'Initialize Edwiser Bridge PRO setup ', 'eb-textdoamin' ),
				'function'    => 'eb_setup_pro_initialize',
				'sub_step' => 0,

			),
			'license'       => array(
				'sidebar' => 1,
				'name'    => __( 'Edwiser Bridge PRO License setup', 'eb-textdoamin' ),
				'function'    => 'eb_setup_license',
				'sub_step' => 0,

			),
			// 'wp_plugins'       => array(
			// 	'sidebar' => 1,
			// 	'name'    => __( 'Edwiser Bridge PRO WordPress plugin installation', 'eb-textdoamin' ),
			// 	'function'    => 'eb_setup_wp_plugins',
			// 	'sub_step' => 0,

			// ),
			'mdl_plugins'       => array(
				'sidebar' => 1,
				'name'    => __( 'Download Edwiser Bridge PRO Moodle plugins', 'eb-textdoamin' ),
				'function'    => 'eb_setup_mdl_plugins',
				'sub_step' => 0,

			),
			'mdl_plugins_installation' => array(
				'sidebar' => 1,
				'name'    => __( 'Let’s install Edwiser Bridge PRO Moodle plugins', 'eb-textdoamin' ),
				'function'    => 'eb_setup_mdl_plugins_installation',
				'sub_step' => 1,

			),
			'sso'     => array(
				'sidebar' => 1,
				'name'    => __( 'Single Sign On setup', 'eb-textdoamin' ),
				'function'    => 'eb_setup_sso',
				'sub_step' => 0,

			),
			'wi_products_sync'     => array(
				'sidebar' => 1,
				'name'    => __( 'WooCommerce product creation', 'eb-textdoamin' ),
				'function'    => 'eb_setup_wi_products_sync',
				'sub_step' => 0,

			),
			'pro_settings'     => array(
				'sidebar' => 1,
				'name'    => __( 'Edwiser Bridge PRO plugin settings', 'eb-textdoamin' ),
				'function'    => 'eb_setup_pro_settings',
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
		} elseif ( 'both' === $setup_wizard['name'] ) {
			$steps = array_merge( $free_setup_steps, $pro_setup_steps );
		}

		return $steps;
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
			'read',
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
		

		// Get current step.
		$step = 'initialize';
		$content_class = "";

		if ( ! empty( $_POST['eb_setup_free_initialize'] ) ) {
			
			// save set up data.
			get_option( 'eb_setup_data' );
			$chosen_setup = '';
			
			
			if ( isset( $_POST['eb_free_setup'] ) ) {

				$chosen_setup = 'free';
			} elseif ( isset( $_POST['eb_pro_setup'] ) ) {

				$chosen_setup = 'pro';
			} elseif ( isset( $_POST['eb_free_and_pro'] ) ) {

				$chosen_setup = 'both';
			}

			$setup_array = array( 'name' => $chosen_setup );


			update_option( 'eb_setup_data', $setup_array );
			$step = 'installation';
		}


		$this->setup_wizard_header();



		if( 'initialize' === $step ){
			$content_class = "eb_setup_full_width";
		}


		// content area.
			// sidebar.
				?>

				<div class="eb-setup-content-area">
				<?php	
				
				if( 'initialize' !== $step ){

				?>
				<!-- Sidebar -->
					<div class="eb-setup-sidebar">

						<?php

						$this->eb_setup_steps_html();

						?>

					</div>
				<?php
				}
				?>

					<!-- content -->
					<div class="eb-setup-content <?php echo esc_attr( $content_class ); ?>">
						<?php
						if( 'initialize' === $step ){

							$this->eb_setup_initialize( 0 );

						} else {
							$this->eb_setup_free_installtion_guide( 0 );
						}
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


		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		// same as default WP from wp-admin/admin-header.php.
		// $wp_version_class = 'branch-' . str_replace( array( '.', ',' ), '-', floatval( get_bloginfo( 'version' ) ) );

		set_current_screen();


		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php  ?></title>
			<?php do_action( 'admin_enqueue_scripts' ); ?>
			<?php wp_print_scripts( 'eb-setup-wizard-js' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>

		</head>


		<body class="wc-setup wp-core-ui <?php echo esc_attr( 'wc-setup-step__' . $this->step ); ?> <?php /*echo esc_attr( $wp_version_class );*/ ?>">

		<header class="eb-setup-wizard-header">

			<div class="eb-setup-header-logo">
				<div class="eb-setup-header-logo-img-wrap">
					<img src="<?php echo esc_attr( $eb_plugin_url . 'images/wordpress-logo.png' ); ?>" />
				</div>
			</div>

			<div class="eb-setup-header-title-wrap">

				<div class="eb-setup-header-title">

Title
				
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






	public function eb_setup_initialize( $ajax = 1 ) {

		if ( $ajax ) {
			ob_start();
		}

		?>

		<div class="eb_setup_free_initialize">

			<form method="POST">

				<div>

					<p> <?php esc_html_e( 'What are you trying to setup?', 'eb-textdomain' ); ?> </p>

					<div class="eb_setup_free_initialize_inp_wrap">
						<input type="checkbox" name="eb_free_setup">
						<label> <?php esc_html_e( 'Only Edwiser Bridge FREE', 'eb-textdomain' ); ?> </label>
					</div>

					<div class="eb_setup_free_initialize_inp_wrap">
						<input type="checkbox" name="eb_pro_setup">
						<label> <?php esc_html_e( 'Only Edwiser Bridge PRO', 'eb-textdomain' ); ?> </label>
					</div>

					<div class="eb_setup_free_initialize_inp_wrap">
						<input type="checkbox" name="eb_free_and_pro">
						<label> <?php esc_html_e( 'Both, Edwiser Bridge FREE & PRO', 'eb-textdomain' ); ?> </label>
					</div>
				</div>

				<div class="eb_setup_btn_wrap">
					<input type="submit" name="eb_setup_free_initialize" class="eb_setup_btn" value="<?php esc_html_e( 'Start The Setup', 'eb-textdomain' ); ?>">
				</div>

			</form>



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



		if ( $ajax ) {
			$html = ob_get_clean();

			$return = array('content' => $html);
			wp_send_json_success($return);
		}
		


	}

	public function eb_setup_free_installtion_guide( $ajax = 1 ) {
		$step = 'free_installtion_guide';
		$is_next_sub_step = 1;
		$next_step = $this->get_next_step( $step );

		if ( $ajax ) {
			ob_start();
		}

		?>
		<div class="eb_setup_installation_guide">
			<div>
				<span> <?php esc_html_e( 'To start the setup you need to have the following plugins installed on WordPress & Moodle.', 'eb-textdomain' ); ?> </span>
				
				<div class='eb_setup_h2_wrap'>

					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bridge Free WordPress plugin', 'eb-textdomain' ); ?> <p>
					
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bridge Free Moodle plugin', 'eb-textdomain' ); ?> <p>

				</div>


				<span> <?php esc_html_e( 'To start the setup you need to have the following plugins installed on WordPress & Moodle.', 'eb-textdomain' ); ?> </span>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>'> <?php esc_html_e( 'Continue the setup', 'eb-textdomain' ); ?> </button>
				</div>

			</div>

			<div>
				<div>
					<div class="accordion"><span class="dashicons dashicons-editor-help"></span><?php esc_html_e( 'What to do if I have not installed the Moodle plugin?', 'eb-textdomain' ); ?><span class="dashicons dashicons-arrow-down-alt2"></span><span class="dashicons dashicons-arrow-up-alt2"></span></div>

					<div class="panel">

						<div>
							<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Download the plugin now', 'eb-textdomain' ); ?> </button>
						</div>

						<p>
							<span> <?php esc_html_e( 'After download please follow the steps below;', 'eb-textdomain' ); ?> </span>
						
							<ol>
								<li> <?php esc_html_e( 'Login to your Moodle site with Adminstrative access', 'eb-textdomain' ); ?></li>
								<li><?php esc_html_e( 'Navigate to Site adminstration > Plugins > Install plugins ', 'eb-textdomain' ); ?></li>
								<li><?php esc_html_e( ' Upload the Edwiser Bridge FREE Moodle plugin here', 'eb-textdomain' ); ?></li>
								<li><?php esc_html_e( 'We will assist you with the rest of the setup from there 🙂', 'eb-textdomain' ); ?></li>
							</ol>

						</p>
					</div>
				</div>
			</div>
	
	
		</div>

		<?php

		if ( $ajax ) {
			$html = ob_get_clean();
			$return = array('content' => $html);
			wp_send_json_success($return);
		}

	}



	public function eb_setup_moodle_redirection( $ajax = 1 ) {
		$step = 'moodle_redirection';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );
		
		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class="eb_setup_conn_url">

			<div>
			
				<span class="eb_setup_h2"> <?php esc_html_e( 'Enter your Moodle URL to intiate the configuration on moodle site for Edwiser Bridge FREE Moodle plugin.', 'eb-textdomain' ); ?> </span>

				<div class="eb_setup_conn_url_inp_wrap">
					<p> <label class='eb_setup_h2'> <?php esc_html_e( 'Moodle URL', 'eb-textdomain' ); ?></label></p>
					<input class='eb_setup_inp' name='eb_setup_test_conn_mdl_url' id='eb_setup_test_conn_mdl_url' type='text' >
				</div>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>'> <?php esc_html_e( 'Submit & Continue', 'eb-textdomain' ); ?> </button>
				</div>

			</div>


			<div>

				<div>
					<div class="accordion"> <?php esc_html_e( 'Unable to navigate directly to the Edwiser Bridge FREE plugin setup on Moodle from the above step?  ', 'eb-textdomain' ); ?> </div>
					<div class="panel">
					<p>  </p>
					</div>
				</div>
		
			</div>

		</div>

		<?php
		if ( $ajax ) {
			$html = ob_get_clean();
			$return = array('content' => $html);
			wp_send_json_success($return);
		}
	}



	public function eb_setup_test_connection( $ajax = 1 ) {
		$step = 'test_connection';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );

		if ( $ajax ) {

			ob_start();
		}
		?>
		<div class="eb_setup_test_connection">
			<div>
				<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Enter your Moodle URL to intiate the configuration on moodle site for Edwiser Bridge FREE Moodle plugin.', 'eb-textdomain' ); ?> </div>

				<div>
					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle URL', 'eb-textdomain' ); ?></label></p>
						<input class='eb_setup_inp' name='eb_setup_test_conn_mdl_url' id='eb_setup_test_conn_mdl_url' type='text' >
					</div>

					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle access token', 'eb-textdomain' ); ?></label> </p>
						<input class='eb_setup_inp' name='eb_setup_test_conn_token' id='eb_setup_test_conn_token' type='text' >
					</div>
					
					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Language code', 'eb-textdomain' ); ?></label></p>
						<input class='eb_setup_inp' name='eb_setup_test_conn_lang_code' id='eb_setup_test_conn_lang_code' type='text' >
					</div>
				
				</div>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn eb_setup_test_connection_btn" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>'> <?php esc_html_e( 'Submit & Continue', 'eb-textdomain' ); ?> </button>
					
					<button class="eb_setup_btn eb_setup_save_and_continue eb_setup_test_connection_cont_btn" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>'> <?php esc_html_e( 'Submit & Continue', 'eb-textdomain' ); ?> </button>

					<div class='eb_setup_settings_success_msg eb_setup_test_conn_success'> <span class="dashicons dashicons-yes-alt"></span> <?php  esc_html_e( 'WordPress to Moodle connection successful!', 'eb-textdomain' ); ?> </div>

					<div class='eb_setup_settings_error_msg eb_setup_test_conn_error'> <span class="dashicons dashicons-yes-alt"></span> <?php  esc_html_e( 'WordPress to Moodle connection successful!', 'eb-textdomain' ); ?> </div>
				
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

				</div>
		
			</div>

		</div>

		<?php
		if ( $ajax ) {
			$html = ob_get_clean();

			$return = array('content' => $html);
			wp_send_json_success($return);
		}
	}


	public function eb_setup_course_sync() {
		$step = 'course_sync';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );
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
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>' > <?php esc_html_e( 'Synchronize the courses', 'eb-textdomain'); ?> </button>
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
		$step = 'user_sync';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );
		ob_start();

		?>
		<div>
		<?php
		/**
		 * If Moodle has more than 2000 users.
		 * Please show other screen. 
		 */
		$result = count_users();
		if ( $result['total_users'] < 1000 ) {
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
						<?php  esc_html_e( 'We have noticed that you have', 'eb-textdomain' ) . $result['total_users'] . esc_html_e( '2500 Moodle users and the synchronization would take approximately half an hour. ', 'eb-textdomain' ); ?>
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

					<button class='eb_setup_btn eb_setup_users_sync_btn' data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>'> <?php esc_html_e( 'Synchronize the courses', 'eb-textdomain'); ?> </button>

					<button class="eb_setup_btn eb_setup_save_and_continue" style="display:none" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>'> <?php esc_html_e( 'Synchronize the courses', 'eb-textdomain'); ?> </button>

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

		</div>

		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}


	public function eb_setup_free_recommended_settings() {
		$step = 'free_recommended_settings';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );
		ob_start();

		?>
		<div class="eb_setup_free_recommended_settings">
			<span> <?php esc_html_e( 'Enable user registration.', 'eb-textdomain' ); ?> </span>

			<div class="eb_setup_user_sync_inp_wrap">
				<input type="checkbox" >
				<label> <?php esc_html_e( 'Enable user account creation on Edwiser Bridge user-account page’ ', 'eb-textdomain' ); ?></label>
			</div>

			<p>  <?php esc_html_e( 'Default page is set to Edwiser Bridge - User Account.', 'eb-textdomain' ); ?> </p>

			<div class="eb_setup_conn_url_inp_wrap">
				<p><label class="eb_setup_h2"> <?php esc_html_e( 'User Account Page', 'eb-textdomain' ); ?></label> </p>
				<input class="eb_setup_inp" type="text" >

			</div>

			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'eb-textdomain'); ?> </button>
				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>' > <?php esc_html_e( 'Synchronize the courses', 'eb-textdomain'); ?> </button>
			</div>

		</div>


		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}


	public function eb_setup_pro_initialize() {
		$step = 'pro_initialize';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );

		ob_start();

		?>
		<div class="eb_setup_pro_initialize">
			
			<div>
				<?php esc_html_e( 'We are about to install the “Edwiser Bridge PRO” plugins. Click on ‘Continue’ once you are ready.', 'eb-textdomain' ); ?>	
			</div>

			<div>
				<?php esc_html_e( 'If you still haven’t purchased the “Edwiser Bridge PRO” plugin then you can purchase it from here', 'eb-textdomain' ); ?>
			</div>


			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-sub-step='<?php echo $sub_step ?>'> <?php esc_html( 'Continue the Setup', 'eb-textdomain'); ?> </button>
			</div>

		</div>

		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);

	}


	public function eb_setup_license() {
		$step = 'license';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );
		ob_start();

		?>
		<div class="eb_setup_license">
			<div>
				<p>
					<?php esc_html_e( 'Please enter Edwiser Bridge PRO license keys here to install Edwiser Bridge PRO WordPress plugins.', 'eb-textdomain' ); ?>	
				</p>

				<p>
					<?php esc_html_e( 'You can find the keys in the purchase receipt email or you can navigate to My account page on Edwiser.', 'eb-textdomain' ); ?>	
				</p>
			<div>

			<div>
				<div class="eb_setup_license_inp_wrap">
					<div class="eb_setup_conn_url_inp_wrap  ">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle access token', 'eb-textdomain' ); ?></label> </p>
						<input class="eb_setup_inp" type="text" >

					</div>

					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle access token', 'eb-textdomain' ); ?></label> </p>
						<input class="eb_setup_inp" type="text" >

					</div>

				</div>
			
				<div class="eb_setup_license_inp_wrap">
					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle access token', 'eb-textdomain' ); ?></label> </p>
						<input class="eb_setup_inp" type="text" >

					</div>

					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle access token', 'eb-textdomain' ); ?></label> </p>
						<input class="eb_setup_inp" type="text" >

					</div>

				</div>
			
			
			</div>



			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>' > <?php esc_html_e( 'Continue the Setup', 'eb-textdomain'); ?> </button>
			</div>

		</div>


		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}


	public function eb_setup_mdl_plugins() {
		$step = 'mdl_plugins';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );

		ob_start();

		?>
		<div>
			
			<div>
				<?php esc_html_e( 'Please download the listed two plugin and install manually', 'eb-textdomain' ); ?>	
			
				<div>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Single Sign On Moodle plugin', 'eb-textdomain' ); ?> <p>
					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Download', 'eb-textdomain'); ?> </button>
				</div>

				<div>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bulk Purchase Moodle plugin', 'eb-textdomain' ); ?> <p>
					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Download', 'eb-textdomain'); ?> </button>
				</div>
			</div>

			<hr />

			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>' > <?php esc_html_e( 'Continue the Setup', 'eb-textdomain'); ?> </button>
			</div>

		</div>


		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);


	}


	public function eb_setup_mdl_plugins_installation() {
		$step = 'mdl_plugins';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );
		ob_start();

		?>
		<div class="eb_setup_mdl_plugins_installation">
			<span> <?php esc_html_e( 'You will have to follow the steps given below to install the Moodle plugins manually.', 'eb-textdomain'); ?>  </span>

			<div>
			
				<fieldset>
					<legend> <?php esc_html_e( 'STEP 1', 'eb-textdomain' ); ?> </legend> 
					<p>
						<?php esc_html_e( 'Click on Install button and you will be redirected to Moodle’s plugin installation page. (Login to your Moodle site if not logged in).', 'eb-textdomain' ); ?>
					</p>
	
				</fieldset>

				<div class="eb_setup_user_sync_btn_wrap">
					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Install plugins on Moodle', 'eb-textdomain'); ?> </button>
				</div>

			</div>


			<div>
			
				<fieldset>
					<legend> <?php esc_html_e( 'STEP 2', 'eb-textdomain' ); ?> </legend> 
					<p>
						<?php esc_html_e( 'Upload and install the Edwiser Bridge PRO plugin one by one which are downloaded in your browser.', 'eb-textdomain' ); ?>
					</p>
	
				</fieldset>

			</div>


			<div>
			
				<fieldset>
					<legend> <?php esc_html_e( 'STEP 3', 'eb-textdomain' ); ?> </legend> 
					<p>
						<?php esc_html_e( 'Come back to this tab and continue your Edwiser Bridge PRO setup.', 'eb-textdomain' ); ?>
					</p>
	
				</fieldset>

				<div class="eb_setup_user_sync_btn_wrap">

					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Install plugins on Moodle', 'eb-textdomain'); ?> </button>
				</div>

			</div>



		</div>

		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}


	public function eb_setup_sso() {
		$step = 'mdl_plugins';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );

		ob_start();

		?>
		<div>
			
			<div>
				<?php esc_html_e( 'Please download the listed two plugin and install manually', 'eb-textdomain' ); ?>	

				<div>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'To find the secret key on your Moodle site, please click on Single Sign On secret key and then copy & paste the key here.', 'eb-textdomain' ); ?> <p>
				</div>

				<div>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Click on ‘Verify token’ once you add the secret key.', 'eb-textdomain' ); ?> <p>
				</div>

				<div class="eb_setup_conn_url_inp_wrap">
					<p>
						<label class="eb_setup_h2"> <?php esc_html_e( 'SSO secret key', 'eb-textdomain' ); ?></label>
					</p>

					<input class='eb_setup_inp' name='eb_setup_pro_sso_key' type='text' >

				</div>
			</div>


			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'eb-textdomain'); ?> </button>

				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>'> <?php esc_html_e( 'Verify token', 'eb-textdomain'); ?> </button>

				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>'> <?php esc_html_e( 'Continue the Setup', 'eb-textdomain'); ?> </button>
			</div>

		</div>


		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}


	public function eb_setup_wi_product_sync() {
		ob_start();

		?>
		<div>

			<?php esc_html_e( 'Please download the listed two plugin and install manually', 'eb-textdomain' ); ?>	

			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'eb-textdomain'); ?> </button>
				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Skip', 'eb-textdomain'); ?> </button>

				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>'> <?php esc_html_e( 'Create', 'eb-textdomain'); ?> </button>
			</div>

		</div>


		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}


	public function eb_setup_pro_settings() {
		$step = 'pro_settings';
		$sub_step = '';
		$is_next_sub_step = 0;

		$next_step = $this->get_next_step( $step );
		ob_start();

		?>
		<div class='eb_setup_pro_settings'>
			

			<p>  <?php esc_html_e( 'Enable this setting to hide Edwiser Bridge - “Course archive page” if you are using WooCommerce to sell Moodle courses as WooCommerce products ', 'eb-textdomain' ); ?> </p>

			<div class="eb_setup_conn_url_inp_wrap">
				<input class='eb_setup_inp' type='checkbox' >

				<p><label class="eb_setup_h2"> <?php esc_html_e( 'Hide “Course Archive page”', 'eb-textdomain' ); ?></label> </p>

			</div>

			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'eb-textdomain'); ?> </button>
				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo $step ?>' data-next-step='<?php echo $next_step ?>' data-is-next-sub-step='<?php echo $is_next_sub_step ?>' > <?php esc_html_e( 'Synchronize the courses', 'eb-textdomain'); ?> </button>
			</div>

		</div>


		<?php
		$html = ob_get_clean();

		$return = array('content' => $html);
		wp_send_json_success($return);
	}







}


new Eb_Setup_Wizard();

