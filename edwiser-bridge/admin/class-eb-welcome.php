<?php
/**
 * Welcome screen after plugin activation
 *
 * Shows a plugin overview and functionality list.
 *
 * Adapted from code in edd.
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

/**
 * EB_Welcome class.
 */
class Eb_Welcome {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		if ( ! isset( $_GET['edw-wc-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['edw-wc-nonce'] ) ), 'edw-wc-nonce' ) ) {
			return;
		}

		if ( ! isset( $_GET['page'] ) || empty( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) {
			return;
		}

		$welcome_page_name  = esc_html__( 'About Edwiser Bridge', 'edwiser-bridge' );
		$welcome_page_title = esc_html__( 'Welcome to Edwiser Bridge', 'edwiser-bridge' );

		switch ( $_GET['page'] ) {
			case 'eb-about':
				add_dashboard_page(
					$welcome_page_title,
					$welcome_page_name,
					'manage_options',
					'eb-about',
					array( $this, 'welcome_screen' )
				);
				break;
		}
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 *
	 * @since  1.0.0
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'eb-about' ); ?>

		<style type="text/css">
			/*<![CDATA[*/

			.eb-about-wrap {
				position: relative;
				margin: 25px 40px 0px 20px;
				/*margin:0 auto;*/
				max-width: 100%;
				font-size: 15px;
			}

			.eb-about-wrap .wc-feature {
				overflow: visible !important;
				*zoom:1;
			}
			.eb-about-wrap h3 + .wc-feature {
				margin-top: 0;
			}
			.eb-about-wrap .wc-feature:before,
			.eb-about-wrap .wc-feature:after {
				content: " ";
				display: table;
			}
			.eb-about-wrap .wc-feature:after {
				clear: both;
			}
			.eb-about-wrap .feature-rest div {
				width: 50% !important;
				padding-<?php echo is_rtl() ? 'left' : 'right'; ?>: 100px;
				-moz-box-sizing: border-box;
				box-sizing: border-box;
				margin: 0 !important;
			}
			.eb-about-wrap .feature-rest div.last-feature {
				padding-<?php echo is_rtl() ? 'right' : 'left'; ?>: 100px;
				padding-<?php echo is_rtl() ? 'left' : 'right'; ?>: 0;
			}
			.eb-about-wrap div.icon {
				width: 0 !important;
				padding: 0;
				margin: 20px 0 !important;
			}

			.eb-version-badge{
				position: absolute;
				top: 0px;
				right: 0px;
				padding: 10px;
				font-size: 13px;
				color: #FFF;
				border-radius: 3px;
				background: rgb(0, 145, 205) none repeat scroll 0% 0%;
				font-weight: 600;
				text-transform: uppercase;
			}

			.prompt-subscribe-wrap {
				background: #fff;
				margin: 20px 0;
				padding: 25px;
				text-align:center;
				-webkit-box-shadow: 0px 6px 12px -8px black;
				-moz-box-shadow: 0px 6px 12px -8px black;
				box-shadow: 0px 6px 12px -8px #000;
				border-radius: 5px;
			}

			.prompt-subscribe-form {
				margin:0 auto;
				max-width:500px;
			}

			.prompt-subscribe-form input[type="email"]{
				width: 350px;
				height: 40px;
				transition: all 0.07s ease-in-out 0s;
				border: medium none;
				border-radius: 2px;
				padding-left: 10px;
				background: #E4E8EC none repeat scroll 0% 0%;
			}

			.subscribe-submit{
				position: relative;
				right: 6px;
				height: 40px;
				font-size: 13px;
				transition: all 0.07s ease-in-out 0s;
				color: #FFF;
				border: medium none;
				border-radius: 0px 3px 3px 0px;
				background: #0091CD none repeat scroll 0% 0%;
				font-weight: 600;
				text-transform: uppercase;
				padding:0 10px;
				outline:0;
			}

			.changelog h4 {
				line-height: 1.4;
			}

			.eb-actions a{
				min-width: 75px;
				font-size: 13px !important;
				text-align: center !important;
				height: 30px !important;
			}

			/* subscription success message */
			.success-message, .error-message{
				margin-top:25px;
			}

			div.success-message span{
				color:rgb(18, 124, 18);
				font-weight:600;
			}

			/* subscription success message */
			div.error-message span{
				color:rgb(240, 45, 45);
				font-weight:600;
			}

			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Intro text shown on all about pages.
	 */
	private function intro() {

		// Flush rewrite rules after plugin install or update.
		flush_rewrite_rules();

		// get plugin version.
		$version = edwiser_bridge_instance()->get_version();
		?>

		<h1><?php printf( esc_html__( 'Welcome to Edwiser Bridge', 'edwiser-bridge' ), esc_html( $version ) ); ?></h1>

		<span class="eb-version-badge">
		<?php
		/*
		 * translators: Version number.
		 */
		printf( esc_html__( 'Version %s', 'edwiser-bridge' ), esc_html( $version ) );
		?>
		</span>

		<?php
	}

	/**
	 * Output the about screen.
	 */
	public function welcome_screen() {
		?>

		<div class="wrap about-wrap eb-about-wrap">

			<?php $this->intro(); ?>

			<div class="changelog">
				<p class="about-description">
					<?php
					esc_html_e( 'Thanks for installing Edwiser Bridge! Integrating WordPress with Moodle has never been so simple. We hope you enjoy using it.', 'edwiser-bridge' );
					?>
				</p>

				<p class="eb_welcome_mdl_dwnld_btn_wrap">
					<a class="eb_welcome_mdl_dwnld_btn" href="https://edwiser.org/plugins/edwiserbridge.zip"> <?php echo esc_html__( 'Download Moodle Plugin', 'edwiser-bridge' ); ?> </a>
				</p>

				<div class="changelog prompt-subscribe-wrap">
					<h1 style="text-align:center; margin:0; font-size:30px;">
						<?php esc_html_e( 'Get the latest updates on Edwiser Bridge in Your Inbox!', 'edwiser-bridge' ); ?>
					</h1>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="prompt-subscribe-form">
						<h4>
							<?php
							esc_html_e( 'Stay updated with the latest features in Edwiser Bridge and receive early-bird discounts on upcoming premium add ons.', 'edwiser-bridge' );
							?>
						</h4>
						<br/>
						<input
							type="email"
							name="eb_sub_admin_email"
							placeholder="<?php esc_html_e( 'Please enter your email address', 'edwiser-bridge' ); ?>" value="" />
						<input type="hidden" name="action" value="eb_subscribe" />
						<?php wp_nonce_field( 'subscribe_nonce', 'subscribe_nonce_field' ); ?>
						<input type="submit" class="subscribe-submit" value="<?php esc_html_e( 'Subscribe', 'edwiser-bridge' ); ?>" />
					</form>
					<?php
					// Proceed only if the nonce is verified.
					if ( isset( $_GET['edw-wc-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['edw-wc-nonce'] ) ), 'edw-wc-nonce' ) ) {
						if ( isset( $_GET['subscribed'] ) && sanitize_text_field( wp_unslash( $_GET['subscribed'] ) ) ) {
							?>
							<div class="success-message">
								<span><?php esc_html_e( 'Thanks for subscribing to Edwiser Bridge Updates & Notifications.', 'edwiser-bridge' ); ?></span>
							</div>
							<?php
						} elseif ( isset( $_GET['subscribed'] ) && ! sanitize_text_field( wp_unslash( $_GET['subscribed'] ) ) ) {
							?>
							<div class="error-message">
								<span><?php esc_html_e( 'An error occurred in subscription process, please try again.', 'edwiser-bridge' ); ?></span>
							</div>
							<?php
						}
					}
					?>
				</div>
			</div>

			<div class="eb-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=eb-settings' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Skip to Settings', 'edwiser-bridge' ); ?></a>
				<a href="<?php echo esc_url( 'https://edwiser.org/bridge/documentation/' ); ?>" target="_blank" class="docs button button-primary"><?php esc_html_e( 'Docs', 'edwiser-bridge' ); ?></a>
			</div>
		</div>
		<?php
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

		if ( get_transient( '_eb_activation_redirect' ) ) {
			// Delete transient used for redirection.
			delete_transient( '_eb_activation_redirect' );
			$wc_url = admin_url( '/?page=eb-about' ) . '&edw-wc-nonce=' . wp_create_nonce( 'edw-wc-nonce' );
			wp_safe_redirect( $wc_url );
			exit;
		}
	}

	/**
	 * User subscribe to updates & notifications form handler.
	 * sends an email to plugin owner on form submit.
	 *
	 * We get user's email for providing plugin addons and update notifications.
	 *
	 * @since  1.0.0
	 */
	public function subscribe_handler() {
		$subscribed = 0;

		// verify nonce.
		if ( ! isset( $_POST['subscribe_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['subscribe_nonce_field'] ) ), 'subscribe_nonce' ) ) {
			esc_html_e( 'Sorry, there is a problem!', 'edwiser-bridge' );
			exit;
		} else {
			// process subscription.
			$support_email = 'support@wisdmlabs.com'; // support email.

			$admin_email = filter_input( INPUT_POST, 'eb_sub_admin_email', FILTER_VALIDATE_EMAIL );

			// prepare email content.
			$subject = apply_filters(
				'eb_plugin_subscription_email_subject',
				__( 'Edwiser Bridge Plugin Subscription Notification', 'edwiser-bridge' )
			);

			$message  = __( 'Edwiser Bridge subscription user details:', 'edwiser-bridge' ) . " \n";
			$message .= "\n" . __( 'Customer Website:', 'edwiser-bridge' ) . "\n" . site_url();
			$message .= "\n\n" . __( 'Customer Email:', 'edwiser-bridge' ) . " \n";
			$message .= $admin_email;

			$sent = wp_mail( $support_email, $subject, $message );

			if ( $sent ) {
				$subscribed = 1;
			}
		}
		$wc_url = admin_url( '/?page=eb-about&subscribed=' . $subscribed ) . '&edw-wc-nonce=' . wp_create_nonce( 'edw-wc-nonce' );

		wp_safe_redirect( $wc_url );
		exit;
	}
}

new Eb_Welcome();
