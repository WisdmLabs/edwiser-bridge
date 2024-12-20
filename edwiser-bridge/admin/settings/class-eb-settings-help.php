<?php
/**
 * Edwiser Bridge user help page
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

if ( ! class_exists( 'Eb_Settings_Help' ) ) :

	/**
	 * Eb_Settings_Help.
	 */
	class Eb_Settings_Help extends EB_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'get-help';
			$this->label = __( 'Get Help', 'edwiser-bridge' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'admin_action_eb_help', array( $this, 'helpSubscribeHandler' ) );
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			// Hide the save button.
			$GLOBALS['hide_save_button'] = true;
		}



		/*NOT-USED function*/

		/**
		 * User help subscription form handler.
		 *
		 * We get user's email for providing help regarding plugin functionality.
		 *
		 * @since  1.0.0
		 */
		public function user_help_handler() {

			// verify nonce.
			if ( ! isset( $_POST['subscribe_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['subscribe_nonce_field'] ) ), 'subscribe_nonce' ) ) {
				esc_html_e( 'Sorry, there is a problem!', 'edwiser-bridge' );
				exit;
			} else {
				// process subscription.
				$plugin_author_email = 'bharat.pareek@edwiser.org';

				$admin_email = filter_input( INPUT_POST, 'eb_sub_admin_email', FILTER_VALIDATE_EMAIL );

				// prepare email content.
				$subject = apply_filters(
					'eb_plugin_subscription_email_subject',
					__( 'Edwiser Bridge Plugin Subscription Notification', 'edwiser-bridge' )
				);

				$message = sprintf(
					/**
					 * Translators: dispays the subuscription message.
					 */
					esc_html__( 'Edwiser subscription user details: \n\n Customer Website: ', 'edwiser-bridge' ) . '%s ' . esc_html__( '\n Customer Email:', 'edwiser-bridge' ) . ' %s ',
					site_url(),
					$admin_email
				);

				$sent = wp_mail( $plugin_author_email, $subject, $message );

				if ( $sent ) {
					$subscribed = 1;
				}
			}
			$wc_url = admin_url( '/?page=eb-about&subscribed=' . $subscribed ) . '&edw-wc-nonce=' . wp_create_nonce( 'edw-wc-nonce' );

			wp_safe_redirect( $wc_url );
			exit;
		}
	}

endif;

return new Eb_Settings_Help();
