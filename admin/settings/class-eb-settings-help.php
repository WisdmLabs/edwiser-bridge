<?php

/**
 * Edwiser Bridge user help page
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'EB_Settings_Get_Help' ) ) :

	/**
	 * EB_Settings_PayPal
	 */
	class EB_Settings_Get_Help extends EB_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'get-help';
		$this->label = __( 'Get Help', 'eb-textdomain' );

		add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		//add_action( 'eb_settings_' . $this->id, array( $this, 'output' ) );
		//add_action( 'eb_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'admin_action_eb_help', array( $this, 'help_subscribe_handler' ) );
	}

	/**
	 * Output the settings
	 *
	 * @since  1.0.0
	 */
	public function output() {
		global $current_section;

		// Hide the save button
		$GLOBALS['hide_save_button'] = true;

		
	}

	/**
	 * user help subscription form handler
	 *
	 * We get user's email for providing help regarding plugin functionality.
	 *
	 * @since  1.0.0
	 */
	public function user_help_handler() {

		// verify nonce
		if (
			! isset( $_POST['subscribe_nonce_field'] ) || ! wp_verify_nonce( $_POST['subscribe_nonce_field'], 'subscribe_nonce' )
		) {

			print 'Sorry, there is a problem!';
			exit;

		} else {

			// process subscription
			$plugin_author_email = 'bharat.pareek@edwiser.org';

			$admin_email = filter_input( INPUT_POST, "eb_sub_admin_email", FILTER_VALIDATE_EMAIL );

			// prepare email content
			$subject = apply_filters( 'eb_plugin_subscription_email_subject', 'Edwiser Bridge Plugin Subscription Notification' );

			$message = "Edwiser subscription user details: \n";
			$message .= "\nCustomer Website:\n". site_url();
			$message .= "\n\nCustomer Email: \n";
			$message .= $admin_email;

			$sent = wp_mail( $plugin_author_email, $subject, $message );

			if ( $sent ) {
				$subscribed = 1;
			}
		}

		wp_redirect( admin_url( '/?page=eb-about&subscribed='.$subscribed ) );
		exit;
	}
}

endif;

return new EB_Settings_Get_Help();
