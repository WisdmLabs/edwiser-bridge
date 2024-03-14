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
class Eb_Setup_Wizard_Templates {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
	}


	/**
	 * Setup Wizard template.
	 *
	 * @param  String $step step.
	 */
	public function eb_setup_wizard_template( $step = 'initialize' ) {

		// Intialization.
		$setup_functions = new Eb_Setup_Wizard_Functions();

		// Get current step.
		$content_class = '';
		$step          = $setup_functions->eb_setup_handle_page_submission_or_refresh();
		$steps         = $setup_functions->eb_setup_wizard_get_steps();
		$title         = $setup_functions->eb_get_step_title( $step );
		$header_class  = '';
		if ( 'initialize' === $step ) {
			$content_class = 'eb_setup_full_width';
			$header_class  = 'initialize';
		}

		$this->setup_wizard_header( $title, $header_class );

		// content area.
		// sidebar.
		?>

		<div class='eb-setup-content-area'>
		<?php

		if ( 'initialize' !== $step ) {

			?>
		<!-- Sidebar -->
			<div class='eb-setup-sidebar'>

				<?php

				$setup_functions->eb_setup_steps_html( $step );

				?>

			</div>
			<?php
		}
		?>

			<!-- content -->
			<div class="eb-setup-content <?php echo esc_attr( $content_class ); ?>">
				<?php

				$function = $steps[ $step ]['function'];
				$this->$function( 0 );

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
	 *
	 * @param  String $title title.
	 * @param  String $header_class header class.
	 */
	public function setup_wizard_header( $title = '', $header_class = '' ) {

		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		// same as default WP from wp-admin/admin-header.php.

		set_current_screen();

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<title><?php echo esc_html__( 'Edwiser Bridge Setup Wizard', 'edwiser-bridge' ); ?></title>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title></title>
			<?php do_action( 'admin_enqueue_scripts' ); ?>
			<?php wp_print_scripts( 'eb-setup-wizard-js' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>

		</head>

		<body class="eb-setup wp-core-ui">

			<header class='eb-setup-wizard-header'>
				<div class='eb-setup-header-logo'>
					<div class='eb-setup-header-logo-img-wrap'>
						<img src="<?php echo esc_attr( $eb_plugin_url . 'images/wordpress-logo.png' ); ?>" />
					</div>
				</div>

				<div class='eb-setup-header-title-wrap <?php echo esc_attr( $header_class ); ?>'>
					<div class='eb-setup-header-title'> <?php echo esc_attr( $title ); ?></div>
					<div class='eb-setup-close-icon'> <span class="dashicons dashicons-no"></span> </div>
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
					<?php esc_html_e( 'Copyright © 2022 Edwiser | Brought to you by WisdmLabs and Powered by Edwiser', 'edwiser-bridge' ); ?>
				</div>

				<div class="eb-setup-footer-button">
					<a href='https://edwiser.org/contact-us/' target='_blank'>
						<?php esc_html_e( 'Contact Us', 'edwiser-bridge' ); ?>
					</a>
				</div>

			</footer>

		</body>
	</html>

		<?php
	}


	/**
	 * Setup Wizard Initialize.
	 *
	 * @param  int $ajax if request is ajax.
	 */
	public function eb_setup_initialize( $ajax = 1 ) {
		$setup_functions  = new Eb_Setup_Wizard_Functions();
		$eb_plugin_url    = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();
		$step             = 'initialize';
		$is_next_sub_step = 0;
		$next_step        = $setup_functions->get_next_step( $step );
		$title            = $setup_functions->eb_get_step_title( $step );
		$nonce            = wp_create_nonce( 'eb_setup_wizard' );
		if ( $ajax ) {
			ob_start();
		}
		?>

		<div class='eb_setup_free_initialize'>

			<form method='POST'>

				<div>
					<p> <?php esc_html_e( 'What are you trying to setup?', 'edwiser-bridge' ); ?> </p>

					<div class='eb_setup_free_initialize_inp_wrap'>
						<!-- <input type='radio' name='eb_setup_name' value='eb_free_setup'> -->
						<label class="esw-radio-container">
							<input type="radio"  name='eb_setup_name' id='eb_setup_free' value='eb_free_setup'>
							<span class="esw-radio-checkmark"></span>
						</label>
						<label for='eb_setup_free' class='eb_cursor_point es-p-l-30'> <?php esc_html_e( 'Only Edwiser Bridge FREE', 'edwiser-bridge' ); ?> </label>
					</div>

					<div class='eb_setup_free_initialize_inp_wrap'>
						<!-- <input type='radio' name='eb_setup_name' value='eb_pro_setup'> -->
						<label class="esw-radio-container">
							<input type="radio"  name='eb_setup_name' id='eb_setup_pro' value='eb_pro_setup'>
							<span class="esw-radio-checkmark"></span>
						</label>
						<label for='eb_setup_pro' class='eb_cursor_point es-p-l-30'> <?php esc_html_e( 'Only Edwiser Bridge PRO', 'edwiser-bridge' ); ?> </label>
					</div>

					<div class='eb_setup_free_initialize_inp_wrap'>
						<!-- <input type='radio' name='eb_setup_name' value='eb_free_and_pro'> -->
						<label class="esw-radio-container">
							<input type="radio"  name='eb_setup_name' id='eb_setup_free_and_pro' value='eb_free_and_pro'>
							<span class="esw-radio-checkmark"></span>
						</label>
						<label for='eb_setup_free_and_pro' class='eb_cursor_point es-p-l-30'> <?php esc_html_e( 'Both, Edwiser Bridge FREE & PRO', 'edwiser-bridge' ); ?> </label>
					</div>
				</div>

				<div class='eb_setup_btn_wrap'>
					<input type='hidden' name='nonce' value='<?php echo esc_attr( $nonce ); ?>'>
					<input type='submit' id='eb_setup_free_initialize' name='eb_setup_free_initialize' class='eb_setup_btn disabled' value="<?php esc_html_e( 'Start The Setup', 'edwiser-bridge' ); ?>" disabled>
				</div>

			</form>


			<div>
				<fieldset>
					<legend> <?php esc_html_e( 'Note', 'edwiser-bridge' ); ?> </legend>
					<div class='fieldset_content'>
						<?php esc_html_e( 'It approximately takes 10-15 minutes to complete the setup since we will be installing plugins, enabling mandatory settings and synchronizing courses and users.', 'edwiser-bridge' ); ?>
					</div>
				</fieldset>
			</div>
		</div>

		<div class='eb_setup_product_sync_progress_popup eb_setup_moodle_redirection_popup'>
				<div class='eb_setup_popup_content eb_setup_prod_sync_popup'>

					<div class='eb_setup_h2'>
					<?php esc_html_e( 'Redirecting to Moodle Setup wizard', 'edwiser-bridge' ); ?>
					</div>

					<div class='eb_setup_product_sync_progress_images'>

						<div class='eb_setup_users_sync_wp_img'>
							<img src="<?php echo esc_attr( $eb_plugin_url . 'images/wordpress-logo.png' ); ?>" class='' />
						</div>

						<div class='eb_setup_product_sync_progress_arrows'>

							<div class="animated  animated--on-hover  mt-2">
								<span class="animated__text">
									<span class='dashicons dashicons-arrow-right-alt2' style='color:#bedbe2;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#76bccc;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#5abec3;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#14979d;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#007075;'></span>
								</span>
							</div>

						</div>

						<div class='eb_setup_users_sync_mdl_img'>
							<img src="<?php echo esc_attr( $eb_plugin_url . 'images/Moodle-logo.png' ); ?>" />
						</div>

					</div>
				</div>

			</div>
			<?php
			$is_pro = eb_is_legacy_pro();
			if ( $is_pro ) {
				?>
				<div class="eb-admin-pro-popup eb-admin-pro-popup-setup-wizard">
					<div class='eb-admin-pro-popup-content'>
						<p class="eb-admin-pro-popup-title"><?php echo esc_html__( 'Introducing', 'edwiser-bridge' ) . '<strong>' . esc_html__( ' consolidated Edwiser Bridge Pro Plugin', 'edwiser-bridge' ) . '</strong>' . esc_html__( ' for smoother configuration experience', 'edwiser-bridge' ); ?></p>
						<p class="eb-admin-pro-popup-text"><?php echo esc_html__( 'Starting from Edwiser Bridge version 3.0.0,  all the Edwiser Bridge Pro add-on plugins  have been combined into a', 'edwiser-bridge' ) . '<strong>' . esc_html__( ' single plugin -Edwiser Bridge Pro', 'edwiser-bridge' ) . '</strong>' . esc_html__( ' to provide a smoother and better experience for installing, configuring and updating Edwiser Bridge Pro.', 'edwiser-bridge' ); ?></p>
						<p class="eb-admin-pro-popup-text"><?php echo esc_html__( 'To install and activate the', 'edwiser-bridge' ) . '<strong>' . esc_html__( ' new Edwiser Bridge Pro plugin', 'edwiser-bridge' ) . '</strong>' . esc_html__( ' click below', 'edwiser-bridge' ); ?></p>
						<a class="eb-admin-pro-popup-button" href="<?php echo esc_url( admin_url( 'admin.php?page=eb-settings&tab=licensing' ) ); ?>"><?php esc_html_e( 'Activate Now', 'edwiser-bridge' ); ?> </a>
					</div>
				</div>
				<?php
			}
			?>

		<?php

		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard for free plugin installation.
	 *
	 * @param  int $ajax if request is ajax.
	 */
	public function eb_setup_free_installtion_guide( $ajax = 1 ) {
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'free_installtion_guide';
		$is_next_sub_step = 1;
		$next_step        = $setup_functions->get_next_step( $step );
		$title            = $setup_functions->eb_get_step_title( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		$download_url = 'https://edwiser.org/plugins/edwiserbridge.zip';
		$download_url = apply_filters( 'eb_setup_mdl_plugin_link', $download_url );

		if ( $ajax ) {
			ob_start();
		}

		?>
		<div class="eb_setup_installation_guide es-w-80">
			<div>
				<span> <?php esc_html_e( 'To start the setup you need to have the following plugins installed on WordPress & Moodle.', 'edwiser-bridge' ); ?> </span>
				<div class='eb_setup_h2_wrap'>

					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bridge Free WordPress plugin', 'edwiser-bridge' ); ?> <p>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bridge Free Moodle plugin', 'edwiser-bridge' ); ?> <p>

				</div>

				<span> <?php esc_html_e( 'If you have already installed Edwiser Bridge FREE plugin on WordPress & Moodle, please click', 'edwiser-bridge' ); ?> </span>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Continue the setup', 'edwiser-bridge' ); ?> </button>
				</div>

			</div>

			<div>
				<div class='es-p-t-10'>
					<div class='accordion'><span class="dashicons dashicons-editor-help"></span><?php esc_html_e( 'What to do if I have not installed the Moodle plugin?', 'edwiser-bridge' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span><span class="dashicons dashicons-arrow-up-alt2"></span></div>

					<div class='panel'>

						<div class='es-m-t-20'>
							<a class='eb_setup_sec_btn' href='<?php echo wp_kses( $download_url, $allowed_tags ); ?>'> <?php esc_html_e( 'Download the plugin now', 'edwiser-bridge' ); ?> </a>
						</div>

						<p>
							<span> <?php esc_html_e( 'After download please follow the steps below;', 'edwiser-bridge' ); ?> </span>
							<ol>
								<li class='p-b-5'> <?php esc_html_e( 'Login to your Moodle site with Adminstrative access', 'edwiser-bridge' ); ?></li>
								<li class='p-b-5'><?php esc_html_e( 'Navigate to Site adminstration > Plugins > Install plugins ', 'edwiser-bridge' ); ?></li>
								<li class='p-b-5'><?php esc_html_e( ' Upload the Edwiser Bridge FREE Moodle plugin here', 'edwiser-bridge' ); ?></li>
								<li class='p-b-5'><?php esc_html_e( 'We will assist you with the rest of the setup from there 🙂', 'edwiser-bridge' ); ?></li>
							</ol>

						</p>
					</div>
				</div>
			</div>
		</div>

		<?php

		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}

	}

	/**
	 * Setup Wizard redirection to moodle.
	 *
	 * @param  int $ajax if request is ajax.
	 */
	public function eb_setup_moodle_redirection( $ajax = '1' ) {
		$mdl_url = \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url();
		$mdl_url = ( 'MOODLE_URL' === $mdl_url ) ? '' : $mdl_url;

		$setup_functions  = new Eb_Setup_Wizard_Functions();
		$step             = 'moodle_redirection';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$next_step        = $setup_functions->get_next_step( $step );
		$title            = $setup_functions->eb_get_step_title( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$eb_plugin_url    = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class="eb_setup_conn_url es-w-80">

			<div>

				<span class=""> <?php esc_html_e( 'Enter your Moodle URL to intiate the configuration on moodle site for Edwiser Bridge FREE Moodle plugin.', 'edwiser-bridge' ); ?> </span>

				<div class="eb_setup_conn_url_inp_wrap">
					<p>
						<label class='eb_setup_h2'> <?php esc_html_e( 'Moodle URL', 'edwiser-bridge' ); ?></label>
						<span class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Ensure there is no blank space. And it should follow the URL with Hypertext Transfer Protocol "https://"', 'edwiser-bridge' ); ?></span> </span>
					</p>
					<input class='eb_setup_inp' name='eb_setup_test_conn_mdl_url' id='eb_setup_test_conn_mdl_url' type='text' value='<?php echo esc_attr( $mdl_url ); ?>' >
				</div>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn eb_setup_save_and_continue eb_setup_moodle_redirection_btn" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Submit & Continue', 'edwiser-bridge' ); ?> </button>
				</div>

			</div>

			<div>
				<div class='es-p-t-10'>
					<div class="accordion"> <span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Unable to navigate directly to the Edwiser Bridge FREE plugin setup on Moodle from the above step?  ', 'edwiser-bridge' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span><span class="dashicons dashicons-arrow-up-alt2"></span> </div>
					<div class="panel">
						<div>
							<span> <?php esc_html_e( 'Please follow these manual steps below;', 'edwiser-bridge' ); ?> </span>
							<ol>
								<li class='p-b-5'><?php esc_html_e( 'Login to your Moodle site with Adminstrative access', 'edwiser-bridge' ); ?></li>
								<li class='p-b-5'><?php esc_html_e( 'Navigate to Site administration > Plugins > Edwiser Bridge', 'edwiser-bridge' ); ?></li>
								<li class='p-b-5'><?php esc_html_e( 'Now click on ‘Initiate Edwiser Bridge Moodle Setup wizard’', 'edwiser-bridge' ); ?></li>
								<li class='p-b-5'><?php esc_html_e( 'We will assist you with the rest of the setup from there 🙂', 'edwiser-bridge' ); ?></li>
							</ol>

						</div>
					</div>
				</div>
			</div>

			<!--  -->
			<div class='eb_setup_product_sync_progress_popup eb_setup_moodle_redirection_popup'>
				<div class='eb_setup_popup_content eb_setup_prod_sync_popup'>

					<div class='eb_setup_h2'>
					<?php esc_html_e( 'Redirecting to Moodle Setup wizard', 'edwiser-bridge' ); ?>
					</div>

					<div class='eb_setup_product_sync_progress_images'>

						<div class='eb_setup_users_sync_wp_img'>
							<img src="<?php echo esc_attr( $eb_plugin_url . 'images/wordpress-logo.png' ); ?>" class='' />
						</div>

						<div class='eb_setup_product_sync_progress_arrows'>

							<div class="animated  animated--on-hover  mt-2">
								<span class="animated__text">
									<span class='dashicons dashicons-arrow-right-alt2' style='color:#bedbe2;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#76bccc;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#5abec3;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#14979d;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#007075;'></span>
								</span>
							</div>

						</div>

						<div class='eb_setup_users_sync_mdl_img'>
							<img src="<?php echo esc_attr( $eb_plugin_url . 'images/Moodle-logo.png' ); ?>" />
						</div>

					</div>
				</div>

			</div>
			<!--  -->




		</div>

		<?php
		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard test connection.
	 *
	 * @param  int $ajax if request is ajax.
	 */
	public function eb_setup_test_connection( $ajax = '1' ) {
		$mdl_url   = \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url();
		$mdl_url   = ( 'MOODLE_URL' === $mdl_url ) ? '' : $mdl_url;
		$token     = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();
		$lang_code = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_get_lang_code();

		$setup_functions  = new Eb_Setup_Wizard_Functions();
		$step             = 'test_connection';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$next_step        = $setup_functions->get_next_step( $step );
		$title            = $setup_functions->eb_get_step_title( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;

		$disbaled = 'disabled';
		if ( ! empty( $mdl_url ) && ! empty( $token ) && ! empty( $lang_code ) ) {
			$disbaled = '';
		}

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_test_connection es-w-80'>
			<div>
				<div class='eb_setup_h2 eb_setup_test_conn_h2'> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Manually “Copy and Paste” the Moodle credentials to Edwiser Bridge WordPress settings from the Moodle page.', 'edwiser-bridge' ); ?> </div>

				<div>
					<div class='eb_setup_conn_url_inp_wrap'>
						<p class="eb_setup_test_conn_text"> <?php esc_html_e( 'Click on ‘Test connection’ once the details are added to the respective fields.', 'edwiser-bridge' ); ?> </p>

						<p>
							<label class="eb_setup_h2"> <?php esc_html_e( 'Moodle URL', 'edwiser-bridge' ); ?></label>
							<span class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Ensure there is no blank space. And it should follow the URL with Hypertext Transfer Protocol "https://"', 'edwiser-bridge' ); ?></span> </span>
						</p>
						<input class='eb_setup_inp' name='eb_setup_test_conn_mdl_url' id='eb_setup_test_conn_mdl_url' type='text' value='<?php echo esc_attr( $mdl_url ); ?>' >
					</div>

					<div class='eb_setup_conn_url_inp_wrap'>
						<p>
							<label class="eb_setup_h2"> <?php esc_html_e( 'Moodle access token', 'edwiser-bridge' ); ?></label>
							<span class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Acts as an authenticator and is auto generated. Same has to be copied and pasted on the WordPress site.', 'edwiser-bridge' ); ?></span> </span>
						</p>
						<input class='eb_setup_inp' name='eb_setup_test_conn_token' id='eb_setup_test_conn_token' type='text' value='<?php echo esc_attr( $token ); ?>'>
					</div>

					<div class='eb_setup_conn_url_inp_wrap'>
						<p>
							<label class="eb_setup_h2"> <?php esc_html_e( 'Language code', 'edwiser-bridge' ); ?></label>
							<span class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Copy paste this code on the WordPress site. Mis-Matched code may affect the integration.', 'edwiser-bridge' ); ?></span> </span>
						</p>
						<input class='eb_setup_inp' name='eb_setup_test_conn_lang_code' id='eb_setup_test_conn_lang_code' type='text' value='<?php echo esc_attr( $lang_code ); ?>'>

						<div class='eb_setup_settings_success_msg eb_setup_test_conn_success'> <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'WordPress to Moodle connection successful!', 'edwiser-bridge' ); ?> </div>
						<div class='eb_setup_settings_error_msg eb_setup_test_conn_error'> <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'WordPress to Moodle connection successful!', 'edwiser-bridge' ); ?> </div>
					</div>

				</div>

				<div class='eb_setup_btn_wrap'>
					<input type='hidden' class='eb_setup_test_conne_url' >
					<input type='hidden' class='eb_setup_test_conne_token' >
					<input type='hidden' class='eb_setup_test_conne_lang' >

					<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>
					<!-- <button class='eb_setup_sec_btn' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button> -->
					<button class='eb_setup_btn eb_setup_test_connection_btn <?php echo esc_attr( $disbaled ); ?>' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' <?php echo esc_attr( $disbaled ); ?>> <?php esc_html_e( 'Test connection', 'edwiser-bridge' ); ?> </button>
					<button class='eb_setup_btn eb_setup_save_and_continue eb_setup_test_connection_cont_btn' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Continue the setup', 'edwiser-bridge' ); ?> </button>

				</div>

			</div>

			<div class='eb_setup_test_conn_seprator_wrap'>
				<div class='eb_setup_separator'> 
					<div class='eb_setup_hr'><hr></div>
					<div> <span> <?php esc_html_e( ' OR ', 'edwiser-bridge' ); ?> </span> </div>
					<div class='eb_setup_hr'><hr></div>
				</div>
				<div>
					<div>
						<div>
							<span class='eb_setup_h2'> <span style='vertical-align: baseline;' class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Choose and upload the Moodle Credential file here', 'edwiser-bridge' ); ?> </span>		
						</div>	
						<div>
							<input class="eb_setup_sec_btn eb_setup_file_btn" type="file" accept=".json">
							<button class="eb_setup_sec_btn eb_setup_upload_btn disabled" disabled> <?php esc_html_e( 'Upload', 'edwiser-bridge' ); ?> </button>	
						</div>

					</div>

				</div>
			</div>

		</div>

		<?php
		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}


	/**
	 * Setup Wizard course sync.
	 *
	 * @param  int $ajax if request is ajax.
	 */
	public function eb_setup_course_sync( $ajax = 1 ) {
		$setup_functions  = new Eb_Setup_Wizard_Functions();
		$step             = 'course_sync';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_course_sync es-w-80'>
			<span class=''> <?php esc_html_e( 'This will synchronize all your Moodle course ID, title, description from Moodle to WordPress.', 'edwiser-bridge' ); ?> </span>
			<div class='eb_setup_course_sync_note'>

				<div class='eb_setup_course_sync_inp_wrap'>
					<!-- <input type='checkbox' class='eb_setup_course_sync_inp'> -->
					<label class="esw-cb-container">
						<input type="checkbox"  class='eb_setup_course_sync_inp'>
						<span class="esw-cb-checkmark"></span>
					</label>

					<label class='es-sec-h es-p-l-30'> <?php esc_html_e( '‘Enabling’ Synchronized courses will publish them on WordPress. If disabled, courses will be set as ‘Draft’.', 'edwiser-bridge' ); ?></label>

					<div class='eb_setup_settings_success_msg' style='display:none;'> <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Courses Synchronized successful!', 'edwiser-bridge' ); ?> </div>
				</div>

				<div class='eb_setup_course_sync_btn_wrap'>
					<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>
					<!-- <button class='eb_setup_sec_btn'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button> -->
					<button class='eb_setup_btn eb_setup_course_sync_btn' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Synchronize the courses', 'edwiser-bridge' ); ?> </button>
					<button class="eb_setup_btn eb_setup_save_and_continue eb_setup_course_sync_cont_btn" style="display:none" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Continue the setup', 'edwiser-bridge' ); ?> </button>

				</div>
			</div>

		</div>

		<?php
		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard User sync.
	 *
	 * @param  int $ajax if request is ajax.
	 */
	public function eb_setup_user_sync( $ajax = 1 ) {
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'user_sync';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$eb_plugin_url    = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;
		$next_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $next_step;

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div>
		<?php
		/**
		 * If Moodle has more than 2000 users.
		 * Please show other screen.
		 */
		$result = count_users();
		if ( $result['total_users'] < 1500 ) {
			?>
			<div class='eb_setup_user_sync es-w-80'>

				<span class=''> <?php esc_html_e( 'This will synchronize all your WordPress users from WordPress to Moodle.', 'edwiser-bridge' ); ?> </span>
				<div class='eb_setup_user_sync_note'>

					<div class='eb_setup_user_sync_inp_wrap'>
						<!-- <input type='checkbox' id='eb_setup_user_sync_cb'> -->
						<label class='esw-cb-container' >
							<input type='checkbox'  id='eb_setup_user_sync_cb'>
							<span class='esw-cb-checkmark'></span>
						</label>

						<label class='es-sec-h es-p-l-30'> <?php esc_html_e( '‘Enabling’ will synchronize and send login credentials to WordPress users. If disabled, it will not send email notification to all synchronized users.', 'edwiser-bridge' ); ?></label>
					</div>
				</div>

				<div class="eb_setup_user_sync_btn_wrap">
					<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>' > <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>
					<!-- <button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button> -->
					<button class='eb_setup_btn eb_setup_users_sync_btn' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Synchronize users & notify', 'edwiser-bridge' ); ?> </button>
					<button class="eb_setup_btn eb_setup_save_and_continue" style="display:none" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Continue the setup', 'edwiser-bridge' ); ?> </button>
				</div>

				<div>
					<fieldset>
						<legend> <?php esc_html_e( 'Note', 'edwiser-bridge' ); ?> </legend>
						<div class='fieldset_content'>
							<?php esc_html_e( 'WordPress emailing functionality (SMTP) needs to be setup and configured on your WordPress site to send emails to your users.', 'edwiser-bridge' ); ?>
						</div>

					</fieldset>
				</div>

			</div>

			<div class='eb_setup_users_sync_progress_popup'>
				<div class='eb_setup_popup_content'>

					<div class='eb_setup_h2'>
						<?php esc_html_e( 'WordPress users are being synchronized to Moodle', 'edwiser-bridge' ); ?>
					</div>

					<div class='eb_setup_users_sync_progress_images'>

						<div class='eb_setup_users_sync_wp_img'>
							<img src="<?php echo esc_attr( $eb_plugin_url . 'images/wordpress-logo.png' ); ?>" />
						</div>

						<div class='eb_setup_users_sync_progress_arrows'>
							<!-- <span class='arrow'> > > > </span> -->

							<div class="animated  animated--on-hover  mt-2">
								<span class="animated__text">
									<span class='dashicons dashicons-arrow-right-alt2' style='color:#bedbe2;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#76bccc;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#5abec3;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#14979d;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#007075;'></span>

								</span>
								<!-- <span class="dashicons dashicons-arrow-right-alt2 animated__text"></span>
								<span class="dashicons dashicons-arrow-right-alt2 animated__text"></span>
								<span class="dashicons dashicons-arrow-right-alt2 animated__text"></span> -->
							</div>

						</div>

						<div class='eb_setup_users_sync_mdl_img'>
							<img src="<?php echo esc_attr( $eb_plugin_url . 'images/Moodle-logo.png' ); ?>" />
						</div>

					</div>

					<div class='eb_setup_user_sync_progress_text'>
						<span class='eb_setup_users_sync_users'></span> / <span class='eb_setup_users_sync_total_users'></span> <?php esc_html_e( ' users synchronized', 'edwiser-bridge' ); ?>
					</div>

				</div>

			</div>

		</div>

			<?php
		} else {
			?>
			<div class="eb_setup_user_sync es-w-80">
				<div class='eb_setup_large_users_warn'>
					<div style='width:15%;'>
						<!-- dashicons -->
						<!-- <span class="dashicons dashicons-warning"></span> -->
						<div> <img style='height:60px;' src="<?php echo esc_attr( $eb_plugin_url . 'images/warning-1.png' ); ?>" /> </div>

					</div>

					<div style='width:85%;'>
						<div>
							<?php esc_html_e( 'We have noticed that ', 'edwiser-bridge' ) . '<b>' . esc_html_e( ' you have ', 'edwiser-bridge' ) . $result['total_users'] . esc_html_e( ' Moodle users ', 'edwiser-bridge' ) . '</b>' . esc_html_e( ' and the synchronization would take approximately half an hour. ', 'edwiser-bridge' ); ?>
						</div>

						<div>
							<?php '<b>' . esc_html_e( 'We strongly recommend you ', 'edwiser-bridge' ) . '</b>' . esc_html_e( ' to synchronize the users manually by referring to the documentation link.', 'edwiser-bridge' ); ?>
						</div>
					</div>
				</div>


				<div class="eb_setup_user_sync_btn_wrap">

					<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>' > <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>
					<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $next_url ); ?>' > <?php esc_html_e( 'Skip and continue', 'edwiser-bridge' ); ?> </a>
					<!-- <button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button> -->
					<!-- <button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Skip and continue', 'edwiser-bridge' ); ?> </button> -->

				</div>


				<div>
					<fieldset>
						<legend> <?php esc_html_e( 'Note', 'edwiser-bridge' ); ?> </legend>
						<div class='fieldset_content'>
							<?php esc_html_e( 'WordPress emailing functionality (SMTP) needs to be setup and configured on your WordPress site to send emails to your users.', 'edwiser-bridge' ); ?>
						</div>
					</fieldset>
				</div>
			</div>

		</div>

			<?php
		}

		?>


		<?php
		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard Recommended settings.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_free_recommended_settings( $ajax = 1 ) {
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'free_recommended_settings';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;

		$args = array(
			'name'             => 'eb_setup_user_accnt_page',
			'id'               => 'eb_setup_user_accnt_page',
			'sort_column'      => 'menu_order',
			'sort_order'       => 'ASC',
			'show_option_none' => 'Select Page',
			'class'            => 'eb_setup_inp_select',
			'echo'             => false,
		);

		$general_settings = get_option( 'eb_general' );

		if ( isset( $general_settings['eb_useraccount_page_id'] ) && ! empty( $general_settings['eb_useraccount_page_id'] ) ) {
			$args['selected'] = $general_settings['eb_useraccount_page_id'];
		}

		$checked = ( isset( $general_settings['eb_enable_registration'] ) && 'yes' === $general_settings['eb_enable_registration'] ) ? 'checked' : '';

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_free_recommended_settings es-w-80'>
			<div class='eb_setup_h2 eb_setup_test_conn_h2'> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Enable User registration', 'edwiser-bridge' ); ?> </div>
			<div class='es-p-l-25'>
				<div class='es-p-t-b-5'> <?php esc_html_e( 'This allows user to create user account while purchasing a Moodle course', 'edwiser-bridge' ); ?> </div>

				<div class='' style="padding-bottom: 30px;padding-top:10px;" >
					<!-- <input type='checkbox' name='eb_setup_user_account_creation' id='eb_setup_user_account_creation'> -->
					<label class='esw-cb-container' >
						<input type='checkbox' name='eb_setup_user_account_creation' id='eb_setup_user_account_creation' <?php echo wp_kses( $checked, $allowed_tags ); ?>>
						<span class='esw-cb-checkmark'></span>
					</label>
					<label class='es-sec-h es-p-l-30'> <?php esc_html_e( 'Enable user creation on Edwiser Bridge user-account page ', 'edwiser-bridge' ); ?></label>
				</div>
			</div>

			<div class='eb_setup_h2 eb_setup_test_conn_h2'> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Page setup', 'edwiser-bridge' ); ?> </div>
			<div class='es-p-l-25'>
				<div class='es-p-t-b-10'> <?php esc_html_e( 'Set up user account page to send users to the following page after login/sign-up', 'edwiser-bridge' ); ?> </div>

				<div class='' class='eb_setup_inp_wrap'>
					<div><label class="eb_setup_h2"> <?php esc_html_e( 'User Account Page', 'edwiser-bridge' ); ?></label> </div>
					<?php
					echo wp_kses( str_replace( ' id=', " data-placeholder='" . __( 'Select a page', 'edwiser-bridge' ) . "' style='' class='' id=", wp_dropdown_pages( $args ) ), \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags() );
					?>
				</div>
			</div>

			<div class="eb_setup_user_sync_btn_wrap">
				<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>
				<!-- <button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button> -->
				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Save settings', 'edwiser-bridge' ); ?> </button>
			</div>

		</div>


		<?php

		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard Completed popup for free version.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_free_completed_popup( $ajax = 1 ) {
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'free_completed_popup';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		// Save step form progress.
		$setup_data = get_option( 'eb_setup_data' );

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_popup_content'>

			<div class=''>
				<p> <span class='dashicons dashicons-yes-alt eb_setup_pupup_success_icon'></span> </p>

				<p class="eb_setup_h2"> <?php esc_html_e( 'Edwiser Bridge FREE plugin Setup is Completed.', 'edwiser-bridge' ); ?></p>
				<?php
				if ( isset( $setup_data['name'] ) && 'free' !== $setup_data['name'] ) {
					?>
					<p>  <?php esc_html_e( 'Let’s continue with Edwiser Bridge PRO setup', 'edwiser-bridge' ); ?> </p>
					<?php
				}
				?>
			</div>

			<div class="eb_setup_user_sync_btn_wrap">

				<?php

				if ( isset( $setup_data['name'] ) && 'free' === $setup_data['name'] ) {
					?>
					<a href=' <?php echo esc_url( get_site_url() . '/wp-admin/edit.php?post_type=eb_course&page=eb-settings' ); ?>' class='eb_setup_btn eb_complete_setup_btn' > <?php esc_html_e( 'Thank You !', 'edwiser-bridge' ); ?> </a>
					<?php
				} else {
					?>
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-sub-step='<?php echo wp_kses( $sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Start Edwiser Bridge PRO Setup', 'edwiser-bridge' ); ?> </button>
					<?php
				}

				?>

			</div>

		</div>

		<?php
		if ( $ajax ) {

			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 1,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard Initialization for pro version.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_pro_initialize( $ajax = 1 ) {
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'pro_initialize';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		if ( $ajax ) {
			ob_start();
		}

		?>
		<div class="eb_setup_pro_initialize es-w-80">
			<p>
				<?php esc_html_e( 'We are about to install the “Edwiser Bridge PRO” plugins. Click on ‘Continue’ once you are ready.', 'edwiser-bridge' ); ?>	
			</p>

			<div>
				<?php echo esc_html__( 'If you still haven’t purchased the “Edwiser Bridge PRO” plugin then you can purchase it from ', 'edwiser-bridge' ) . '<a class="es-primary-color es_text_links" target="_blank" href="https://edwiser.org/bridge-wordpress-moodle-integration/?utm_source=bridge%20plugin&utm_medium=in%20product&utm_campaign=upgrade#downloadfree">' . esc_html__( ' here ', 'edwiser-bridge' ) . '</a>'; ?>
			</div>

			<div class="eb_setup_user_sync_btn_wrap">
				<button class="eb_setup_sec_btn eb-setup-close-icon eb-exit" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-sub-step='<?php echo wp_kses( $sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Exit', 'edwiser-bridge' ); ?> </button>
				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-sub-step='<?php echo wp_kses( $sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Continue the Setup', 'edwiser-bridge' ); ?> </button>
			</div>

		</div>

		<?php

		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard License step.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_license( $ajax = 1 ) {
		$setup_functions  = new Eb_Setup_Wizard_Functions();
		$step             = 'license';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		// License keys.
		$license['eb_pro_license'] = get_option( 'edd_edwiser_bridge_pro_license_key' );

		$class = 'disable';
		foreach ( $license as $value ) {
			if ( empty( $value ) || ! $value ) {
				$class = '';
			}
		}

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class="eb_setup_license es-w-80">
			<div>
				<p>
					<?php esc_html_e( 'Please enter Edwiser Bridge PRO license keys here to install Edwiser Bridge PRO WordPress plugins.', 'edwiser-bridge' ); ?>	
				</p>

				<p>
					<?php echo esc_html__( 'You can find the keys in the purchase receipt email or you can navigate to ', 'edwiser-bridge' ) . '<a class="es-primary-color es_text_links" target="_blank" href="https://edwiser.org/my-account" >' . esc_html__( 'My account page on Edwiser.', 'edwiser-bridge' ) . '</a>'; ?>	
				</p>
			<div>

			<div>
				<div class='eb_setup_license_inp_wrap'>
					<div class='eb_setup_pro_license_inp_wrap'>
						<p>
							<label class='eb_setup_h2'> <?php esc_html_e( 'Edwiser Bridge Pro', 'edwiser-bridge' ); ?></label>
							<span class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Copy and paste the license key without any white space for Edwiser Bridge Pro.', 'edwiser-bridge' ); ?></span> </span>
						</p>
						<div class="eb_setup_license_div">
							<input class='eb_setup_inp eb_setup_license_inp eb_setup_edwiser_bridge_pro_license' name='eb_setup_eb_pro' id='eb_setup_eb_pro' data-action='edwiser_bridge_pro'  type='text' value='<?php echo esc_attr( $license['eb_pro_license'] ); ?>' >
							<div class='eb_setup_edwiser_bridge_pro_license_msg'></div>
						</div>
					</div>
				</div>
			</div>

			<div class="eb_setup_user_sync_btn_wrap">
				<span class="text_install" style='display:none;'> <?php echo esc_html__( 'Retry adding valid licence key and ', 'edwiser-bridge' ); ?> </span>
				<button class='eb_setup_sec_btn eb_setup_license_install_plugins' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Install the plugins', 'edwiser-bridge' ); ?> </button>
				<span style='display:none;' class='ebs_license_install_plugins_cont'>
					<?php echo '<b>' . esc_html__( ' OR ', 'edwiser-bridge' ) . '</b>'; ?> 
					<button  class='eb_setup_btn eb_setup_save_and_continue ebs_license_install_plugins_cont' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Continue the Setup', 'edwiser-bridge' ); ?> </button>
				</span>
			</div>

		</div>


		<?php
		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Enable/Disable pro plugins.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_pro_plugins( $ajax = 1 ) {
		$setup_functions  = new Eb_Setup_Wizard_Functions();
		$step             = 'pro_plugins';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;

		$module_data = get_option( 'eb_pro_modules_data' );

		$modules_data = array(
			'selective_sync'  => isset( $module_data['selective_sync'] ) ? $module_data['selective_sync'] : 'deactive',
			'sso'             => isset( $module_data['sso'] ) ? $module_data['sso'] : 'deactive',
			'woo_integration' => isset( $module_data['woo_integration'] ) ? $module_data['woo_integration'] : 'deactive',
			'bulk_purchase'   => isset( $module_data['bulk_purchase'] ) ? $module_data['bulk_purchase'] : 'deactive',
			'custom_fields'   => isset( $module_data['custom_fields'] ) ? $module_data['custom_fields'] : 'deactive',
		);
		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_pro_plugins es-w-80'>
			<span class='eb_setup_h2'> <?php esc_html_e( 'Select the Pro feature you want to enable', 'edwiser-bridge' ); ?> </span>
			<fieldset>
				<legend>Note</legend>
				<div class='fieldset_content'>
					<?php esc_html_e( 'To enable the bulk purchase you must also enable Woocommerce Integration', 'edwiser-bridge' ); ?>
				</div>
			</fieldset>
			<div class='eb_setup_course_sync_note'>

				<div class='eb_setup_pro_plugin_inp_wrap'>
					<label class="esw-cb-container">
						<input type="checkbox"  class='eb_setup_sso_inp' <?php echo 'active' === $module_data['sso'] ? 'checked' : ''; ?>>
						<span class="esw-cb-checkmark"></span>
						<label class='eb_setup_h2 es-sec-h es-p-l-30'> <?php esc_html_e( 'Edwiser Bridge Single Sign On', 'edwiser-bridge' ); ?></label>
						<i class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Experience seamless login synchronization between Moodle and WordPress, eliminating login hassles and saving time for learners.', 'edwiser-bridge' ); ?></span> </i>
					</label>
				</div>
				<div class='eb_setup_pro_plugin_inp_wrap'>
					<label class="esw-cb-container">
						<input type="checkbox"  class='eb_setup_woo_int_inp' <?php echo 'active' === $module_data['woo_integration'] ? 'checked' : ''; ?>>
						<span class="esw-cb-checkmark"></span>
						<label class='eb_setup_h2 es-sec-h es-p-l-30'> <?php esc_html_e( 'WooCommerce Integration', 'edwiser-bridge' ); ?></label>
						<i class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Effortlessly sell Moodle courses on WordPress with WooCommerce, optimizing pages and integrating with Moodle LMS.', 'edwiser-bridge' ); ?></span> </i>
					</label>
				</div>
				<div class='eb_setup_pro_plugin_inp_wrap'>
					<label class="esw-cb-container">
						<input type="checkbox"  class='eb_setup_bulk_purchase_inp' <?php echo 'active' === $module_data['bulk_purchase'] ? 'checked' : ''; ?>>
						<span class="esw-cb-checkmark"></span>
						<label class='eb_setup_h2 es-sec-h es-p-l-30'> <?php esc_html_e( 'Bulk Purchase', 'edwiser-bridge' ); ?></label>
						<i class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Create a loyal user base by offering course bundles, increasing earnings and user satisfaction through discounts.', 'edwiser-bridge' ); ?></span> </i>
					</label>
				</div>
				<div class='eb_setup_pro_plugin_inp_wrap'>
					<label class="esw-cb-container">
						<input type="checkbox"  class='eb_setup_selective_sync_inp' <?php echo 'active' === $module_data['selective_sync'] ? 'checked' : ''; ?>>
						<span class="esw-cb-checkmark"></span>
						<label class='eb_setup_h2 es-sec-h es-p-l-30'> <?php esc_html_e( 'Selective Sync', 'edwiser-bridge' ); ?></label>
						<i class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Flexiblity to choose specific courses to sync. Save time by choosing to sync only updated courses, course categories and users.', 'edwiser-bridge' ); ?></span> </i>
					</label>
				</div>
				<div class='eb_setup_pro_plugin_inp_wrap'>
					<label class="esw-cb-container">
						<input type="checkbox"  class='eb_setup_custom_fields_inp' <?php echo 'active' === $module_data['custom_fields'] ? 'checked' : ''; ?>>
						<span class="esw-cb-checkmark"></span>
						<label class='eb_setup_h2 es-sec-h es-p-l-30'> <?php esc_html_e( 'Edwiser Bridge Custom Fields', 'edwiser-bridge' ); ?></label>
						<i class="dashicons dashicons-info-outline eb-tooltip"> <span class='eb-tooltiptext'><?php esc_html_e( 'Enhance registration and checkout forms with Custom Fields in WordPress, WooCommerce, and Edwiser Bridge for personalized information collection.', 'edwiser-bridge' ); ?></span> </i>
					</label>
				</div>
				<div class='eb_setup_settings_error_msg' style="display:none;"></div>

				<div class='eb_setup_course_sync_btn_wrap'>
					<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>
					<!-- <button class='eb_setup_sec_btn'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button> -->
					<!-- <button class='eb_setup_btn eb_setup_course_sync_btn' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Synchronize the courses', 'edwiser-bridge' ); ?> </button> -->
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Save settings', 'edwiser-bridge' ); ?> </button>

				</div>
			</div>

		</div>

		<?php
		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard Moodle Plugins Download.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_mdl_plugins( $ajax = 1 ) {
		$setup_functions  = new Eb_Setup_Wizard_Functions();
		$step             = 'mdl_plugins';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;

		$mdl_url = \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url();
		$mdl_url = ( 'MOODLE_URL' === $mdl_url ) ? '' : $mdl_url;
		$mdl_url = $mdl_url . '/auth/edwiserbridge/edwiserbridge.php?tab=summary';

		$sso_download_url = 'https://edwiser.org/wp-content/uploads/edd/2023/03/wdmwpmoodle.zip';
		$sso_download_url = apply_filters( 'eb_setup_sso_plugin_download_link', $sso_download_url );

		$bp_download_url = 'https://edwiser.org/wp-content/uploads/edd/2023/02/wdmgroupregistration.zip';
		$bp_download_url = apply_filters( 'eb_setup_bp_plugin_download_link', $bp_download_url );

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_mdl_plugins es-w-80'>
			<div>
				<?php esc_html_e( 'You will have to enter the license key on your Moodle site to active the Edwiser Bridge Pro Feature on the Moodle end', 'edwiser-bridge' ); ?>	

				<div>
					<p class='eb_setup_h2'> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'To get the new Moodle version license key', 'edwiser-bridge' ); ?> <a class="es-primary-color es_text_links" target="_blank" href="https://edwiser.org/my-account"><?php esc_html_e( 'Click here', 'edwiser-bridge' ); ?></a><p>
				</div>

				<div>
					<p class='eb_setup_h2'> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Insert the new Moodle version license key', 'edwiser-bridge' ); ?> <a class="es-primary-color es_text_links" target="_blank" href="<?php echo esc_url( $mdl_url ); ?>"><?php esc_html_e( 'Click here', 'edwiser-bridge' ); ?></a><p>
				</div>
			</div>
			<div class="eb_setup_user_sync_btn_wrap">
				<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>
				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Continue the Setup', 'edwiser-bridge' ); ?> </button>
			</div>
		</div>


		<?php
		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard Moodle Plugins Installation.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_mdl_plugins_installation( $ajax = 1 ) {
		$setup_functions  = new Eb_Setup_Wizard_Functions();
		$step             = 'mdl_plugins_installation';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;

		$mdl_url        = \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url();
		$mdl_url        = ( 'MOODLE_URL' === $mdl_url ) ? '' : $mdl_url;
		$mdl_plugin_url = $mdl_url . '/admin/tool/installaddon/index.php';
		$mdl_url        = $mdl_url . '/auth/edwiserbridge/edwiserbridge.php?tab=service';

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_mdl_plugins_installation es-w-80'>
			<span> <?php esc_html_e( 'You will have to follow the steps given below to install the Moodle plugins manually.', 'edwiser-bridge' ); ?>  </span>

			<div class='es-p-t-10'>
				<fieldset>
					<legend> <?php esc_html_e( 'STEP 1', 'edwiser-bridge' ); ?> </legend> 
					<div class='fieldset_content fieldset_content_non_i'>
						<?php echo esc_html__( 'Click on Install button and you will be redirected to Moodle’s plugin installation page.', 'edwiser-bridge' ) . '<b>' . esc_html__( ' (Login to your Moodle site if not logged in).', 'edwiser-bridge' ) . '</b>'; ?>
					</div>
					<div class='es-p-t-30 es-m-b-10'>
						<!-- <button class="eb_setup_sec_btn"> <?php esc_html_e( 'Install plugins on Moodle', 'edwiser-bridge' ); ?> </button> -->
						<a target='_blank' class='eb_setup_sec_btn' href='<?php echo esc_attr( $mdl_plugin_url ); ?>'> <?php esc_html_e( 'Install plugins on Moodle', 'edwiser-bridge' ); ?> </a>
					</div>
				</fieldset>

			</div>


			<div class='es-p-t-10'>
				<fieldset>
					<legend> <?php esc_html_e( 'STEP 2', 'edwiser-bridge' ); ?> </legend> 
					<div class='fieldset_content fieldset_content_non_i'>
						<?php echo '<b>' . esc_html__( 'Upload and install the Edwiser Bridge PRO plugin ', 'edwiser-bridge' ) . '</b>' . esc_html__( ' one by one which are downloaded in your browser.', 'edwiser-bridge' ); ?>
					</div>
				</fieldset>

			</div>

			<div class='es-p-t-10'>
				<fieldset>
					<legend> <?php esc_html_e( 'STEP 3', 'edwiser-bridge' ); ?> </legend> 
					<div class='fieldset_content fieldset_content_non_i'>
						<?php echo esc_html__( 'Navigate to this URL  (', 'edwiser-bridge' ) . '<a style="color: #f98012;" target="_blank" href="' . esc_attr( $mdl_url ) . '">' . esc_attr( $mdl_url ) . '</a>' . esc_html__( ') and click on ‘Update Web services’.', 'edwiser-bridge' ); ?>
					</div>
				</fieldset>

			</div>

			<div class='es-p-t-10'>
				<fieldset>
					<legend> <?php esc_html_e( 'STEP 4', 'edwiser-bridge' ); ?> </legend>
					<div class='fieldset_content fieldset_content_non_i'>
						<?php echo '<b>' . esc_html__( 'Come back to this tab ', 'edwiser-bridge' ) . '</b>' . esc_html__( ' and continue your Edwiser Bridge PRO setup.', 'edwiser-bridge' ); ?>
					</div>
				</fieldset>

				<div class="eb_setup_user_sync_btn_wrap">

					<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>
					<!-- <button class='eb_setup_sec_btn'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button> -->
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Continue the Setup', 'edwiser-bridge' ); ?> </button>

				</div>
			</div>



		</div>

		<?php
		if ( $ajax ) {
			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard SSO plugin setup.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_sso( $ajax = 1 ) {
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'sso';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;
		$next_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $next_step;
		$mdl_url          = \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url();
		$mdl_url          = ( 'MOODLE_URL' === $mdl_url ) ? '' : $mdl_url;
		$mdl_url          = $mdl_url . '/auth/edwiserbridge/edwiserbridge.php?tab=sso';
		$eb_sso           = get_option( 'eb_sso_settings_general' );
		$key              = isset( $eb_sso['eb_sso_secret_key'] ) ? $eb_sso['eb_sso_secret_key'] : '';

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_sso es-w-80'>
			<?php
			$module_data = get_option( 'eb_pro_modules_data' );

			if ( ! isset( $module_data['sso'] ) || 'active' !== $module_data['sso'] ) {
				?>
					<div class='eb_setup_settings_error_msg'> <?php echo esc_attr( 'Single sign on plugin is not activated, Please active it first or skip the step. ' ); ?> </div>
					<div class='eb_setup_user_sync_btn_wrap' style='margin-top:20px;'>
						<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $next_url ); ?>' > <?php esc_html_e( 'Skip', 'edwiser-bridge' ); ?> </a>
					</div>

					<?php
			} else {
				?>

				<div>

					<div>
						<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php echo esc_html__( 'To find the secret key on your Moodle site, please click on', 'edwiser-bridge' ) . '<a class="es-primary-color es_text_links" target="_blank" href="' . wp_kses( $mdl_url, $allowed_tags ) . '" >' . esc_html__( ' Single Sign On secret key ', 'edwiser-bridge' ) . '</a>' . esc_html__( 'and then copy & paste the key here. Set a unique alphanumeric password in Moodle under the Secret key setting & copy-paste it in WordPress, under the same setting (Secret Key).', 'edwiser-bridge' ); ?> <div>
						<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Click on ‘Verify token’ once you add the secret key.', 'edwiser-bridge' ); ?> <p>
					</div>

					<div class="eb_setup_conn_url_inp_wrap">
						<div>
							<label class="eb_setup_h2"> <?php esc_html_e( 'SSO secret key', 'edwiser-bridge' ); ?></label>
						</div>

						<input class='eb_setup_inp' id='eb_setup_pro_sso_key' name='eb_setup_pro_sso_key' type='text' value='<?php echo wp_kses( $key, $allowed_tags ); ?>'>
						<div class='eb_setup_sso_response'> </div>

					</div>
				</div>

				<div class='eb_setup_user_sync_btn_wrap'>
					<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>
					<button class='eb_setup_btn eb_setup_verify_sso_roken_btn' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Verify token', 'edwiser-bridge' ); ?> </button>
					<button class='eb_setup_btn eb_setup_save_and_continue' style='display:none;' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Continue the Setup', 'edwiser-bridge' ); ?> </button>
				</div>

			</div>

				<?php
			}

			if ( $ajax ) {
				$html   = ob_get_clean();
				$return = array(
					'title'   => $title,
					'content' => $html,
					'popup'   => 0,
				);
				wp_send_json_success( $return );
			}
	}

	/**
	 * Setup Wizard Woo-int plugin products sync.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_wi_products_sync( $ajax = 1 ) {
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'wi_products_sync';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$eb_plugin_url    = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();
		$next_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $next_step;
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_wi_products_sync es-w-80'>
			<?php
			$module_data = get_option( 'eb_pro_modules_data' );

			if ( ! isset( $module_data['woo_integration'] ) || 'active' !== $module_data['woo_integration'] ) {
				?>
					<div class='eb_setup_settings_error_msg'> <?php echo esc_attr( 'Woocommerce Integration plugin is not activated, Please active it first or skip the step. ' ); ?> </div>
					<div class='eb_setup_user_sync_btn_wrap' style='margin-top:20px;'>
						<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $next_url ); ?>' > <?php esc_html_e( 'Skip', 'edwiser-bridge' ); ?> </a>
					</div>

					<?php
			} else {
				?>
				<?php esc_html_e( 'This will create a WooCommerce product for all your synchronized Moodle courses', 'edwiser-bridge' ); ?>	

			<div class="eb_setup_user_sync_btn_wrap">
				<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>

				<!-- <button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button> -->
				<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $next_url ); ?>' > <?php esc_html_e( 'Skip', 'edwiser-bridge' ); ?> </a>

				<!-- <button class="eb_setup_sec_btn"> <?php esc_html_e( 'Skip', 'edwiser-bridge' ); ?> </button> -->

				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Create', 'edwiser-bridge' ); ?> </button>
			</div>

			<!--  -->

			<div class='eb_setup_product_sync_progress_popup'>
				<div class='eb_setup_popup_content eb_setup_prod_sync_popup'>

					<div class='eb_setup_h2'>
					<?php esc_html_e( 'WooCommerce products of your Moodle courses creating', 'edwiser-bridge' ); ?>
					</div>

					<div class='eb_setup_product_sync_progress_images'>

						<div class='eb_setup_users_sync_wp_img'>
							<img src="<?php echo esc_attr( $eb_plugin_url . 'images/woo.png' ); ?>" class='es-woo-img' />
							<img src="<?php echo esc_attr( $eb_plugin_url . 'images/commerce.png' ); ?>" />
						</div>

						<div class='eb_setup_product_sync_progress_arrows'>

							<div class="animated  animated--on-hover  mt-2">
								<span class="animated__text">
									<span class='dashicons dashicons-arrow-right-alt2' style='color:#bedbe2;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#76bccc;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#5abec3;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#14979d;'></span>
									<span class="dashicons dashicons-arrow-right-alt2" style='color:#007075;'></span>

								</span>
							</div>

						</div>

						<div class='eb_setup_users_sync_mdl_img'>
							<img src="<?php echo esc_attr( $eb_plugin_url . 'images/Moodle-logo.png' ); ?>" />
						</div>

					</div>
				</div>

			</div>



		</div>

				<?php
			}

			if ( $ajax ) {
				$html   = ob_get_clean();
				$return = array(
					'title'   => $title,
					'content' => $html,
					'popup'   => 0,
				);
				wp_send_json_success( $return );
			}
	}


	/**
	 * Setup Wizard Settings for pro version.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_pro_settings( $ajax = 1 ) {
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'pro_settings';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();
		$prev_step        = $setup_functions->get_prev_step( $step );
		$prev_url         = get_site_url() . '/wp-admin/?page=eb-setup-wizard&current_step=' . $prev_step;

		$general_settings = get_option( 'eb_general' );
		$checked_archive  = ( isset( $general_settings['eb_show_archive'] ) && 'no' === $general_settings['eb_show_archive'] ) ? 'checked' : '';
		$checked_guest    = get_option( 'woocommerce_enable_guest_checkout' );
		$checked_guest    = ( isset( $checked_guest ) && 'no' === $checked_guest ) ? 'checked' : '';
		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_pro_settings es-w-80'>

			<div class='eb_setup_h2 eb_setup_test_conn_h2'> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Course archive page', 'edwiser-bridge' ); ?> </div>
			<div class='es-p-l-25'>
				<div class='es-p-t-b-5'>  <?php echo esc_html__( 'Enable this setting to hide Edwiser Bridge - “Course archive page” if you are using ', 'edwiser-bridge' ) . '<a class="es-primary-color es_text_links" target="_blank" href="https://wordpress.org/plugins/woocommerce/" >' . esc_html__( ' WooCommerce ', 'edwiser-bridge' ) . '</a>' . esc_html__( ' to sell Moodle courses as WooCommerce products ', 'edwiser-bridge' ); ?> </div>

				<div class="eb_setup_inp_wrap" style="padding-bottom: 30px;padding-top:10px;">
					<label class='esw-cb-container' >
						<input type='checkbox' name='eb_pro_rec_set_archive_page' id='eb_pro_rec_set_archive_page' checked>
						<span class='esw-cb-checkmark'></span>
					</label>
					<label class="es-sec-h es-p-l-30"> <?php esc_html_e( 'Hide “Course Archive page”', 'edwiser-bridge' ); ?></label>
				</div>
			</div>
			<?php

			$active_plugins = get_option( 'active_plugins' );

			if ( in_array( 'woocommerce-integration/bridge-woocommerce.php', $active_plugins, true ) ) {
				?>
				<div class='eb_setup_h2 eb_setup_test_conn_h2'> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Guest checkout', 'edwiser-bridge' ); ?> </div>
				<div class='es-p-l-25'>
					<div class='es-p-t-b-5'>  <?php echo esc_html__( 'Disable setting ‘to allow customers to place orders without an account’ since user registration is required for course enrollment in Moodle.', 'edwiser-bridge' ); ?> </div>

					<div class="eb_setup_inp_wrap">
						<!-- <input class='' name='eb_pro_rec_set_archive_page' id='eb_pro_rec_set_archive_page' type='checkbox' > -->
						<label class='esw-cb-container' >
							<input type='checkbox' name='eb_pro_rec_set_guest_checkout' id='eb_pro_rec_set_guest_checkout' checked>
							<span class='esw-cb-checkmark'></span>
						</label>
						<label class="es-sec-h es-p-l-30"> <?php esc_html_e( 'Disable Guest checkout', 'edwiser-bridge' ); ?></label>
					</div>
				</div>
				<?php
			}
			?>

			<div class="eb_setup_user_sync_btn_wrap">
				<a class='eb_setup_sec_btn' href='<?php echo esc_attr( $prev_url ); ?>'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </a>

				<!-- <button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button> -->
				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Save settings', 'edwiser-bridge' ); ?> </button>
			</div>

		</div>

		<?php
		if ( $ajax ) {

			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 0,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard Completed popup for pro version.
	 *
	 * @param int $ajax if request is ajax.
	 */
	public function eb_setup_pro_completed_popup( $ajax = 1 ) {
		$step             = '';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = '';
		$next_step        = '';

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_popup_content'>

			<div class=''>
				<p> <span class='dashicons dashicons-yes-alt eb_setup_pupup_success_icon'></span> </p>

				<p class='eb_setup_h2'> <?php esc_html_e( 'Edwiser Bridge PRO plugin Setup is Completed.', 'edwiser-bridge' ); ?></p>

				<p>  <?php esc_html_e( 'Set a price to your Moodle course and start selling. Click ‘Continue’ to configure your WooCommerce products.', 'edwiser-bridge' ); ?> </p>

			</div>

			<div class="eb_setup_user_sync_btn_wrap">
				<a href=' <?php echo esc_url( get_site_url() . '/wp-admin/edit.php?post_type=product' ); ?>' class='eb_setup_btn eb_complete_setup_btn' > <?php esc_html_e( 'Continue', 'edwiser-bridge' ); ?> </a>

			</div>

		</div>

		<?php
		if ( $ajax ) {

			$html   = ob_get_clean();
			$return = array(
				'title'   => $title,
				'content' => $html,
				'popup'   => 1,
			);
			wp_send_json_success( $return );
		}
	}

	/**
	 * Setup Wizard close setup.
	 */
	public function eb_setup_close_setup() {
		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		ob_start();
		?>
		<div class='eb_setup_popup_content'>

			<div class=''>
				<div> <img style='height:60px;' src="<?php echo esc_attr( $eb_plugin_url . 'images/warning-1.png' ); ?>" /> </div>

				<p class='eb_setup_h2'> <?php esc_html_e( 'Are you sure you want to close the Edwiser Bridge WordPress setup wizard?', 'edwiser-bridge' ); ?></p>

				<div class='eb_setup_user_sync_btn_wrap'>
					<a href=' <?php echo esc_url( get_site_url() . '/wp-admin/edit.php?post_type=eb_course&page=eb-settings' ); ?>' class='eb_setup_sec_btn' > <?php esc_html_e( 'Yes', 'edwiser-bridge' ); ?> </a>
					<button class='eb_setup_sec_btn eb_setup_do_not_close'> <?php esc_html_e( 'No', 'edwiser-bridge' ); ?> </button>
				</div>

			</div>

			<div>
				<fieldset>
					<legend> <?php esc_html_e( 'Note', 'edwiser-bridge' ); ?> </legend>
					<div class='fieldset_content'>
						<?php esc_html_e( 'You can run the setup wizard again by navigating to WordPress backend > Edwiser Bridge > Setting > General settings > Run Setup wizard.', 'edwiser-bridge' ); ?>
					</div>
				</fieldset>
			</div>

		</div>

		<?php
		$html   = ob_get_clean();
		$return = array( 'content' => $html );
		wp_send_json_success( $return );
	}

}


new Eb_Setup_Wizard_Templates();

