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
		$steps         = $setup_functions->eb_setup_wizard_get_steps();
		$step          = $setup_functions->eb_setup_handle_page_submission_or_refresh();
		$title         = $setup_functions->eb_get_step_title( $step );

		$this->setup_wizard_header( $title );

		if ( 'initialize' === $step ) {
			$content_class = 'eb_setup_full_width';
		}

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

				$setup_functions->eb_setup_steps_html();

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
	 */
	public function setup_wizard_header( $title = '' ) {

		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		// same as default WP from wp-admin/admin-header.php.

		set_current_screen();

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title></title>
			<?php do_action( 'admin_enqueue_scripts' ); ?>
			<?php wp_print_scripts( 'eb-setup-wizard-js' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>

		</head>


		<body class="wc-setup wp-core-ui <?php echo esc_attr( 'wc-setup-step__' . $this->step ); ?>">

		<header class='eb-setup-wizard-header'>

			<div class='eb-setup-header-logo'>
				<div class='eb-setup-header-logo-img-wrap'>
					<img src="<?php echo esc_attr( $eb_plugin_url . 'images/wordpress-logo.png' ); ?>" />
				</div>
			</div>

			<div class='eb-setup-header-title-wrap'>

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
					<a>
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
		$step             = 'initialize';
		$is_next_sub_step = 0;
		$next_step        = $setup_functions->get_next_step( $step );
		$title            = $setup_functions->eb_get_step_title( $step );
		if ( $ajax ) {
			ob_start();
		}

		?>

		<div class="eb_setup_free_initialize">

			<form method="POST">

				<div>

					<p> <?php esc_html_e( 'What are you trying to setup?', 'edwiser-bridge' ); ?> </p>

					<div class="eb_setup_free_initialize_inp_wrap">
						<input type="checkbox" name="eb_free_setup">
						<label> <?php esc_html_e( 'Only Edwiser Bridge FREE', 'edwiser-bridge' ); ?> </label>
					</div>

					<div class="eb_setup_free_initialize_inp_wrap">
						<input type="checkbox" name="eb_pro_setup">
						<label> <?php esc_html_e( 'Only Edwiser Bridge PRO', 'edwiser-bridge' ); ?> </label>
					</div>

					<div class="eb_setup_free_initialize_inp_wrap">
						<input type="checkbox" name="eb_free_and_pro">
						<label> <?php esc_html_e( 'Both, Edwiser Bridge FREE & PRO', 'edwiser-bridge' ); ?> </label>
					</div>
				</div>

				<div class="eb_setup_btn_wrap">
					<input type="submit" name="eb_setup_free_initialize" class="eb_setup_btn" value="<?php esc_html_e( 'Start The Setup', 'edwiser-bridge' ); ?>">
				</div>

			</form>

			<div>
				<fieldset>
					<legend> <?php esc_html_e( 'Note', 'edwiser-bridge' ); ?> </legend> 
					<p>
						<?php esc_html_e( 'It approximately takes 10-15 minutes to complete the setup since we will be installing plugins, enabling mandatory settings and synchronizing courses and users.', 'edwiser-bridge' ); ?>
					</p>
				</fieldset>
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

		if ( $ajax ) {
			ob_start();
		}

		?>
		<div class="eb_setup_installation_guide">
			<div>
				<span> <?php esc_html_e( 'To start the setup you need to have the following plugins installed on WordPress & Moodle.', 'edwiser-bridge' ); ?> </span>
				<div class='eb_setup_h2_wrap'>

					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bridge Free WordPress plugin', 'edwiser-bridge' ); ?> <p>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bridge Free Moodle plugin', 'edwiser-bridge' ); ?> <p>

				</div>


				<span> <?php esc_html_e( 'To start the setup you need to have the following plugins installed on WordPress & Moodle.', 'edwiser-bridge' ); ?> </span>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Continue the setup', 'edwiser-bridge' ); ?> </button>
				</div>

			</div>

			<div>
				<div>
					<div class="accordion"><span class="dashicons dashicons-editor-help"></span><?php esc_html_e( 'What to do if I have not installed the Moodle plugin?', 'edwiser-bridge' ); ?><span class="dashicons dashicons-arrow-down-alt2"></span><span class="dashicons dashicons-arrow-up-alt2"></span></div>

					<div class="panel">

						<div>
							<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Download the plugin now', 'edwiser-bridge' ); ?> </button>
						</div>

						<p>
							<span> <?php esc_html_e( 'After download please follow the steps below;', 'edwiser-bridge' ); ?> </span>
							<ol>
								<li> <?php esc_html_e( 'Login to your Moodle site with Adminstrative access', 'edwiser-bridge' ); ?></li>
								<li><?php esc_html_e( 'Navigate to Site adminstration > Plugins > Install plugins ', 'edwiser-bridge' ); ?></li>
								<li><?php esc_html_e( ' Upload the Edwiser Bridge FREE Moodle plugin here', 'edwiser-bridge' ); ?></li>
								<li><?php esc_html_e( 'We will assist you with the rest of the setup from there 🙂', 'edwiser-bridge' ); ?></li>
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
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'moodle_redirection';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$next_step        = $setup_functions->get_next_step( $step );
		$title            = $setup_functions->eb_get_step_title( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class="eb_setup_conn_url">

			<div>

				<span class="eb_setup_h2"> <?php esc_html_e( 'Enter your Moodle URL to intiate the configuration on moodle site for Edwiser Bridge FREE Moodle plugin.', 'edwiser-bridge' ); ?> </span>

				<div class="eb_setup_conn_url_inp_wrap">
					<p> <label class='eb_setup_h2'> <?php esc_html_e( 'Moodle URL', 'edwiser-bridge' ); ?></label></p>
					<input class='eb_setup_inp' name='eb_setup_test_conn_mdl_url' id='eb_setup_test_conn_mdl_url' type='text' >
				</div>
				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Submit & Continue', 'edwiser-bridge' ); ?> </button>
				</div>
			</div>


			<div>
				<div>
					<div class="accordion"> <?php esc_html_e( 'Unable to navigate directly to the Edwiser Bridge FREE plugin setup on Moodle from the above step?  ', 'edwiser-bridge' ); ?> </div>
					<div class="panel">
					<p>  </p>
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
	 * Setup Wizard test connection.
	 *
	 * @param  int $ajax if request is ajax.
	 */
	public function eb_setup_test_connection( $ajax = '1' ) {
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'test_connection';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$next_step        = $setup_functions->get_next_step( $step );
		$title            = $setup_functions->eb_get_step_title( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class="eb_setup_test_connection">
			<div>
				<div class='eb_setup_h2'> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Enter your Moodle URL to intiate the configuration on moodle site for Edwiser Bridge FREE Moodle plugin.', 'edwiser-bridge' ); ?> </div>

				<div>
					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle URL', 'edwiser-bridge' ); ?></label></p>
						<input class='eb_setup_inp' name='eb_setup_test_conn_mdl_url' id='eb_setup_test_conn_mdl_url' type='text' >
					</div>

					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Moodle access token', 'edwiser-bridge' ); ?></label> </p>
						<input class='eb_setup_inp' name='eb_setup_test_conn_token' id='eb_setup_test_conn_token' type='text' >
					</div>
					<div class="eb_setup_conn_url_inp_wrap">
						<p><label class="eb_setup_h2"> <?php esc_html_e( 'Language code', 'edwiser-bridge' ); ?></label></p>
						<input class='eb_setup_inp' name='eb_setup_test_conn_lang_code' id='eb_setup_test_conn_lang_code' type='text' >
					</div>
				</div>

				<div class="eb_setup_btn_wrap">
					<button class="eb_setup_btn eb_setup_test_connection_btn" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Submit & Continue', 'edwiser-bridge' ); ?> </button>
					<button class="eb_setup_btn eb_setup_save_and_continue eb_setup_test_connection_cont_btn" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Submit & Continue', 'edwiser-bridge' ); ?> </button>

					<div class='eb_setup_settings_success_msg eb_setup_test_conn_success'> <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'WordPress to Moodle connection successful!', 'edwiser-bridge' ); ?> </div>

					<div class='eb_setup_settings_error_msg eb_setup_test_conn_error'> <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'WordPress to Moodle connection successful!', 'edwiser-bridge' ); ?> </div>
				</div>

			</div>

			<div>
				<div class="eb_setup_separator"> 
					<div class="eb_setup_hr"><hr></div>
					<div> <span> <?php esc_html_e( ' OR ', 'edwiser-bridge' ); ?> </span> </div>
					<div class="eb_setup_hr"><hr></div>
				</div>
				<div>
					<div>
						<span class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Choose and upload the Moodle Credential file here', 'edwiser-bridge' ); ?> </span>
						<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Submit & Continue', 'edwiser-bridge' ); ?> </button>	
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
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'course_sync';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class="eb_setup_course_sync">

			<span class="eb_setup_h2"> <?php esc_html_e( 'This will synchronize all your Moodle course ID, title, description from Moodle to WordPress.', 'edwiser-bridge' ); ?> </span>

			<div class="eb_setup_course_sync_note">

				<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'If “Enabled”, synchronized courses will be set as ‘Published’ on WordPress.', 'edwiser-bridge' ); ?> </div>

				<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'If “Disabled”, courses will be synchronized as ‘Draft’.', 'edwiser-bridge' ); ?> </div>

				<div class="eb_setup_course_sync_inp_wrap">
					<input type="checkbox" >
					<label> <?php esc_html_e( 'Enabled - Synchronized courses will be set as ‘Published’ ', 'edwiser-bridge' ); ?></label>

				</div>

				<div class="eb_setup_course_sync_btn_wrap">
					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button>
					<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Synchronize the courses', 'edwiser-bridge' ); ?> </button>
				</div>

			</div>

			<div>
				<fieldset>
					<legend> <?php esc_html_e( 'Note', 'edwiser-bridge' ); ?> </legend>
					<p>
						<?php esc_html_e( 'It approximately takes 10-15 minutes to complete the setup since we will be installing plugins, enabling mandatory settings and synchronizing courses and users.', 'edwiser-bridge' ); ?>

					</p>

				</fieldset>
			</div>

		</div>

		<?php
		if ( $ajax ) {

			$html = ob_get_clean();

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
		if ( $result['total_users'] < 1000 ) {
			?>
			<div class="eb_setup_user_sync">

				<span class="eb_setup_h2"> <?php esc_html_e( 'This will synchronize all your Moodle users from Moodle to WordPress.', 'edwiser-bridge' ); ?> </span>

				<div class="eb_setup_user_sync_note">

					<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'If “Enabled”, send email notification to all synchronized users with their login credentials.', 'edwiser-bridge' ); ?> </div>

					<div class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'If “Disabled”, it will not send email notification to all synchronized users', 'edwiser-bridge' ); ?> </div>

					<div class="eb_setup_user_sync_inp_wrap">
						<input type="checkbox" >
						<label> <?php esc_html_e( 'Enabled - Synchronized courses will be set as ‘Published’ ', 'edwiser-bridge' ); ?></label>

					</div>
				</div>

				<div class="eb_setup_user_sync_btn_wrap">

					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button>

					<button class='eb_setup_btn eb_setup_users_sync_btn' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Synchronize the courses', 'edwiser-bridge' ); ?> </button>

					<button class="eb_setup_btn eb_setup_save_and_continue" style="display:none" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Synchronize users & notify', 'edwiser-bridge' ); ?> </button>

				</div>


				<div>
					<fieldset>
						<legend> <?php esc_html_e( 'Note', 'edwiser-bridge' ); ?> </legend>
						<p>
							<?php esc_html_e( 'It approximately takes 10-15 minutes to complete the setup since we will be installing plugins, enabling mandatory settings and synchronizing courses and users.', 'edwiser-bridge' ); ?>

						</p>

					</fieldset>
				</div>
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
						<?php esc_html_e( 'We have noticed that you have', 'edwiser-bridge' ) . $result['total_users'] . esc_html_e( '2500 Moodle users and the synchronization would take approximately half an hour. ', 'edwiser-bridge' ); ?>
					</p>

					<p>
						<?php esc_html_e( 'We strongly recommend you to synchronize the users manually by referring to the documentation link.', 'edwiser-bridge' ); ?>
					</p>

				</div>

				<div class="eb_setup_user_sync_btn_wrap">

					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button>

					<button class='eb_setup_btn eb_setup_users_sync_btn' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Synchronize the courses', 'edwiser-bridge' ); ?> </button>

					<button class="eb_setup_btn eb_setup_save_and_continue" style="display:none" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Skip and continue', 'edwiser-bridge' ); ?> </button>

				</div>


				<div>
					<fieldset>
						<legend> <?php esc_html_e( 'Note', 'edwiser-bridge' ); ?> </legend>
						<p>
							<?php esc_html_e( 'It approximately takes 10-15 minutes to complete the setup since we will be installing plugins, enabling mandatory settings and synchronizing courses and users.', 'edwiser-bridge' ); ?>

						</p>

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

		$args = array(
			'name'             => 'eb_setup_user_accnt_page',
			'id'               => 'eb_setup_user_accnt_page',
			'sort_column'      => 'menu_order',
			'sort_order'       => 'ASC',
			'show_option_none' => ' ',
			'class'            => 'eb_setup_inp',
			'echo'             => false,
		);

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class="eb_setup_free_recommended_settings">
			<span> <?php esc_html_e( 'Enable user registration.', 'edwiser-bridge' ); ?> </span>

			<div class='' style="padding-bottom: 30px;" >
				<input type='checkbox' name='eb_setup_user_account_creation' id='eb_setup_user_account_creation'  >
				<label class='eb_setup_h2'> <?php esc_html_e( 'Enable user creation on Edwiser Bridge user-account page ', 'edwiser-bridge' ); ?></label>
			</div>

			<span>  <?php esc_html_e( 'Default page is set to Edwiser Bridge - User Account.', 'edwiser-bridge' ); ?> </span>

			<div class="eb_setup_inp_wrap">
				<div><label class="eb_setup_h2"> <?php esc_html_e( 'User Account Page', 'edwiser-bridge' ); ?></label> </div>
				<?php
				echo wp_kses( str_replace( ' id=', " data-placeholder='" . __( 'Select a page', 'edwiser-bridge' ) . "'style='' class='' id=", wp_dropdown_pages( $args ) ), \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags() );

				?>

			</div>


			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button>
				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Save settings', 'edwiser-bridge' ); ?> </button>
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

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_popup_content'>

			<div class=''>
				<p> <span class='dashicons dashicons-yes-alt eb_setup_pupup_success_icon'></span> </p>

				<p class="eb_setup_h2"> <?php esc_html_e( 'Edwiser Bridge FREE plugin Setup is Completed.', 'edwiser-bridge' ); ?></p>

				<p>  <?php esc_html_e( 'Let’s continue with Edwiser Bridge PRO setup', 'edwiser-bridge' ); ?> </p>

			</div>

			<div class="eb_setup_user_sync_btn_wrap">
				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-sub-step='<?php echo wp_kses( $sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Start Edwiser Bridge PRO Setup', 'edwiser-bridge' ); ?> </button>

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
		<div class="eb_setup_pro_initialize">

			<div>
				<?php esc_html_e( 'We are about to install the “Edwiser Bridge PRO” plugins. Click on ‘Continue’ once you are ready.', 'edwiser-bridge' ); ?>	
			</div>

			<div>
				<?php esc_html_e( 'If you still haven’t purchased the “Edwiser Bridge PRO” plugin then you can purchase it from here', 'edwiser-bridge' ); ?>
			</div>


			<div class="eb_setup_user_sync_btn_wrap">

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
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'license';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class="eb_setup_license">
			<div>
				<p>
					<?php esc_html_e( 'Please enter Edwiser Bridge PRO license keys here to install Edwiser Bridge PRO WordPress plugins.', 'edwiser-bridge' ); ?>	
				</p>

				<p>
					<?php esc_html_e( 'You can find the keys in the purchase receipt email or you can navigate to My account page on Edwiser.', 'edwiser-bridge' ); ?>	
				</p>
			<div>

			<div>
				<div class='eb_setup_license_inp_wrap'>
					<div class='eb_setup_conn_url_inp_wrap  '>
						<p><label class='eb_setup_h2'> <?php esc_html_e( 'WooCommerce Integration', 'edwiser-bridge' ); ?></label> </p>
						<input class='eb_setup_inp eb_setup_license_inp' name='eb_setup_woo_int' id='eb_setup_woo_int' data-action='woocommerce_integration'  type='text' >
						<div class='eb_setup_woocommerce_integration_license_msg'> </div>

					</div>

					<div class='eb_setup_conn_url_inp_wrap'>
						<p><label class='eb_setup_h2'> <?php esc_html_e( 'Selective Sync', 'edwiser-bridge' ); ?></label> </p>
						<input class='eb_setup_inp eb_setup_license_inp' name='eb_setup_selective_sync' id='eb_setup_selective_sync' data-action='selective_sync' type='text' >
						<div class='eb_setup_selective_sync_license_msg'> </div>

					</div>

				</div>

				<div class='eb_setup_license_inp_wrap'>
					<div class='eb_setup_conn_url_inp_wrap'>
						<p><label class='eb_setup_h2'> <?php esc_html_e( 'Bulk Purchase', 'edwiser-bridge' ); ?></label> </p>
						<input class='eb_setup_inp eb_setup_license_inp' name='eb_setup_bulk_purchase' id='eb_setup_bulk_purchase' data-action='bulk-purchase' type='text' >
						<div class='eb_setup_bulk-purchase_license_msg'> </div>

					</div>

					<div class='eb_setup_conn_url_inp_wrap'>
						<p><label class='eb_setup_h2'> <?php esc_html_e( 'Single Sign On', 'edwiser-bridge' ); ?></label> </p>
						<input class='eb_setup_inp eb_setup_license_inp' name='eb_setup_sso' id='eb_setup_sso' data-action='single_sign_on' type='text' >
						<div class='eb_setup_single_sign_on_license_msg'> </div>

					</div>

				</div>

			</div>



			<div class="eb_setup_user_sync_btn_wrap">

				<button class='eb_setup_btn eb_setup_license_install_plugins' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Install the plugins', 'edwiser-bridge' ); ?> </button>

				<button class='eb_setup_btn eb_setup_save_and_continue' data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>' > <?php esc_html_e( 'Continue the Setup', 'edwiser-bridge' ); ?> </button>
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
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'mdl_plugins';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_mdl_plugins'>

			<div>
				<?php esc_html_e( 'Please download the listed two plugin and install manually', 'edwiser-bridge' ); ?>	

				<div>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Single Sign On Moodle plugin', 'edwiser-bridge' ); ?> <p>
					<div class='eb_setup_user_sync_btn_wrap'>
						<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Download', 'edwiser-bridge' ); ?> </button>
					</div>
				</div>

				<div>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Edwiser Bulk Purchase Moodle plugin', 'edwiser-bridge' ); ?> <p>
					<div class='eb_setup_user_sync_btn_wrap'>
						<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Download', 'edwiser-bridge' ); ?> </button>
					</div>
				</div>
			</div>

			<hr />

			<div class="eb_setup_user_sync_btn_wrap">

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
		$setup_functions = new Eb_Setup_Wizard_Functions();

		$step             = 'mdl_plugins_installation';
		$sub_step         = '';
		$is_next_sub_step = 0;
		$title            = $setup_functions->eb_get_step_title( $step );
		$next_step        = $setup_functions->get_next_step( $step );
		$allowed_tags     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class="eb_setup_mdl_plugins_installation">
			<span> <?php esc_html_e( 'You will have to follow the steps given below to install the Moodle plugins manually.', 'edwiser-bridge' ); ?>  </span>

			<div>

				<fieldset>
					<legend> <?php esc_html_e( 'STEP 1', 'edwiser-bridge' ); ?> </legend> 
					<p>
						<?php esc_html_e( 'Click on Install button and you will be redirected to Moodle’s plugin installation page. (Login to your Moodle site if not logged in).', 'edwiser-bridge' ); ?>
					</p>

				</fieldset>

				<div class="eb_setup_user_sync_btn_wrap">
					<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Install plugins on Moodle', 'edwiser-bridge' ); ?> </button>
				</div>

			</div>


			<div>

				<fieldset>
					<legend> <?php esc_html_e( 'STEP 2', 'edwiser-bridge' ); ?> </legend> 
					<p>
						<?php esc_html_e( 'Upload and install the Edwiser Bridge PRO plugin one by one which are downloaded in your browser.', 'edwiser-bridge' ); ?>
					</p>

				</fieldset>

			</div>


			<div>

				<fieldset>
					<legend> <?php esc_html_e( 'STEP 3', 'edwiser-bridge' ); ?> </legend> 
					<p>
						<?php esc_html_e( 'Come back to this tab and continue your Edwiser Bridge PRO setup.', 'edwiser-bridge' ); ?>
					</p>

				</fieldset>

				<div class="eb_setup_user_sync_btn_wrap">

					<button class='eb_setup_sec_btn'> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button>

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

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_sso'>

			<div>
				<?php esc_html_e( 'Please download the listed two plugin and install manually', 'edwiser-bridge' ); ?>	

				<div>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'To find the secret key on your Moodle site, please click on Single Sign On secret key and then copy & paste the key here.', 'edwiser-bridge' ); ?> <p>
				</div>

				<div>
					<p class="eb_setup_h2"> <span class="dashicons dashicons-arrow-right-alt2"></span> <?php esc_html_e( 'Click on ‘Verify token’ once you add the secret key.', 'edwiser-bridge' ); ?> <p>
				</div>

				<div class="eb_setup_conn_url_inp_wrap">
					<p>
						<label class="eb_setup_h2"> <?php esc_html_e( 'SSO secret key', 'edwiser-bridge' ); ?></label>
					</p>

					<input class='eb_setup_inp' id='eb_setup_pro_sso_key' name='eb_setup_pro_sso_key' type='text' >
					<div class='eb_setup_sso_response'> </div>

				</div>
			</div>


			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button>

				<button class="eb_setup_btn eb_setup_verify_sso_roken_btn" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Verify token', 'edwiser-bridge' ); ?> </button>

				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Continue the Setup', 'edwiser-bridge' ); ?> </button>
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

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_wi_products_sync'>

			<?php esc_html_e( 'This will create a WooCommerce product for all your synchronized Moodle courses', 'edwiser-bridge' ); ?>	

			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button>
				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Skip', 'edwiser-bridge' ); ?> </button>

				<button class="eb_setup_btn eb_setup_save_and_continue" data-step='<?php echo wp_kses( $step, $allowed_tags ); ?>' data-next-step='<?php echo wp_kses( $next_step, $allowed_tags ); ?>' data-is-next-sub-step='<?php echo wp_kses( $is_next_sub_step, $allowed_tags ); ?>'> <?php esc_html_e( 'Create', 'edwiser-bridge' ); ?> </button>
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

		if ( $ajax ) {
			ob_start();
		}
		?>
		<div class='eb_setup_pro_settings'>

			<p>  <?php esc_html_e( 'Enable this setting to hide Edwiser Bridge - “Course archive page” if you are using WooCommerce to sell Moodle courses as WooCommerce products ', 'edwiser-bridge' ); ?> </p>

			<div class="eb_setup_inp_wrap">
				<input class='' name='eb_pro_rec_set_archive_page' id='eb_pro_rec_set_archive_page' type='checkbox' >

				<label class="eb_setup_h2"> <?php esc_html_e( 'Hide “Course Archive page”', 'edwiser-bridge' ); ?></label>

			</div>

			<div class="eb_setup_user_sync_btn_wrap">

				<button class="eb_setup_sec_btn"> <?php esc_html_e( 'Back', 'edwiser-bridge' ); ?> </button>
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

				<p class="eb_setup_h2"> <?php esc_html_e( 'Edwiser Bridge PRO plugin Setup is Completed.', 'edwiser-bridge' ); ?></p>

				<p>  <?php esc_html_e( 'Set a price to your Moodle course and start selling. Click ‘Continue’ to configure your WooCommerce products.', 'edwiser-bridge' ); ?> </p>

			</div>

			<div class="eb_setup_user_sync_btn_wrap">
				<a href=' <?php echo esc_url( get_site_url() . '/wp-admin' ); ?>' class='eb_setup_btn' > <?php esc_html_e( 'Continue', 'edwiser-bridge' ); ?> </a>

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

		ob_start();
		?>
		<div class='eb_setup_popup_content'>

			<div class=''>
				<p> <span class='dashicons dashicons-warning eb_setup_pupup_warning_icon'></span> </p>

				<p class='eb_setup_h2'> <?php esc_html_e( 'Are you sure you want to close the Edwiser Bridge WordPress setup wizard?', 'edwiser-bridge' ); ?></p>

				<div class="eb_setup_user_sync_btn_wrap">
					<a href=' <?php echo esc_url( get_site_url() . '/wp-admin' ); ?>' class='eb_setup_btn' > <?php esc_html_e( 'Yes', 'edwiser-bridge' ); ?> </a>
					<button class='eb_setup_sec_btn eb_setup_do_not_close'> <?php esc_html_e( 'No', 'edwiser-bridge' ); ?> </button>
				</div>

			</div>

			<div>
				<fieldset>
					<legend> <?php esc_html_e( 'Note', 'edwiser-bridge' ); ?> </legend>
					<div>
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

