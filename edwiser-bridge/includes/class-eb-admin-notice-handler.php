<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://edwiser.org
 * @since      1.3.4
 * @package    Edwiser Bridge
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

/**
 * Eb admin handler.
 */
class Eb_Admin_Notice_Handler {

	/**
	 * Check if installed.
	 */
	public function check_if_moodle_plugin_installed() {
		$connection_options = get_option( 'eb_connection' );
		$eb_moodle_url      = '';
		if ( isset( $connection_options['eb_url'] ) ) {
			$eb_moodle_url = $connection_options['eb_url'];
		}
		$eb_moodle_token = '';
		if ( isset( $connection_options['eb_access_token'] ) ) {
			$eb_moodle_token = $connection_options['eb_access_token'];
		}
		$request_url = $eb_moodle_url . '/webservice/rest/server.php?wstoken=';

		$moodle_function = 'eb_get_course_progress';
		$request_url    .= $eb_moodle_token . '&wsfunction=' . $moodle_function . '&moodlewsrestformat=json';
		$response        = wp_remote_post( $request_url );

		if ( is_wp_error( $response ) ) {
			return 0;
		} elseif ( wp_remote_retrieve_response_code( $response ) == 200 ||
				wp_remote_retrieve_response_code( $response ) == 303 ) {
			$body = wp_json_decode( wp_remote_retrieve_body( $response ) );

			if ( 'accessexception' === $body->errorcode ) {
				return 0;
			}
		} else {
			return 0;
		}

		return 1;
	}


	/**
	 * Show admin feedback notice.
	 *
	 * @since 1.3.1
	 */
	public function eb_admin_update_moodle_plugin_notice() {
		$redirection = '?eb-update-notice-dismissed';
		if ( isset( $_GET ) && ! empty( $_GET ) ) {
			$request_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
			$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$redirection  = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $request_host . $request_uri;
			$redirection .= '&eb-update-notice-dismissed';
		}

		if ( ! get_option( 'eb_update_notice_dismissed' ) ) {
			if ( $this->check_if_moodle_plugin_installed() ) {
				update_option( 'eb_update_notice_dismissed', 'true', true );
			}
			echo '  <div class="notice  eb_admin_update_notice_message_cont">
						<div class="eb_admin_update_notice_message">

							<div class="eb_update_notice_content">
								' . esc_html__( 'Thanks for updating to the latest version of Edwiser Bridge plugin, <b>please make sure you have also installed our associated Moodle Plugin to avoid any malfunctioning.</b>', 'eb-textdomain' ) . '
								<a href="https://edwiser.org/wp-content/uploads/edd/2020/11/edwiserbridgemoodle_2.0.0.zip">' . esc_html__( ' Click here ', 'eb-textdomain' ) . '</a>
								' . esc_html__( ' to download Moodle plugin.', 'eb-textdomain' ) . '

									' . esc_html__( 'For setup assistance check our ', 'eb-textdomain' ) . '
									<a href="https://edwiser.org/bridge/documentation/#tab-b540a7a7-e59f-3">' . esc_html__( ' documentation', 'eb-textdomain' ) . '</a>.
							</div>
							
							<div class="eb_update_notice_dismiss_wrap">
								<span style="padding-left: 5px;">
									<a href="' . esc_html( $redirection ) . '">
										' . esc_html__( ' Dismiss notice', 'eb-textdomain' ) . '
									</a>
								</span>
							</div>

						</div>
						<div class="eb_admin_update_dismiss_notice_message">
								<span class="dashicons dashicons-dismiss eb_update_notice_hide"></span>
						</div>
					</div>';
		}
	}



	/**
	 * NOT USED FUNCTION
	 * handle notice dismiss
	 *
	 * @since 1.3.1
	 */
	public function eb_admin_discount_notice_dismiss_handler() {
		$user_id = get_current_user_id();
		if ( isset( $_GET['eb-discount-notice-dismissed'] ) ) {
			add_user_meta( $user_id, 'eb_discount_notice_dismissed', 'true', true );
		}
	}


	/**
	 * NOT USED FUNCTION
	 * show admin feedback notice
	 *
	 * @since 1.3.1
	 */
	public function eb_admin_discount_notice() {
		$redirection  = '?eb-discount-notice-dismissed';
		$request_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		if ( isset( $_GET ) && ! empty( $_GET ) ) {
			$redirection  = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $request_host . $request_uri;
			$redirection .= '&eb-discount-notice-dismissed';
		}

		$user_id = get_current_user_id();
		if ( ! get_user_meta( $user_id, 'eb_discount_notice_dismissed' ) ) {
			echo '  <div class="notice  eb_admin_discount_notice_message">
						<div class="eb_admin_discount_notice_message_cont">
							<div class="eb_admin_discount_notice_content">
								' . esc_html__( 'Get all Premium Edwiser Products at Flat 20% Off!', 'eb-textdomain' ) . '

								<div style="font-size:13px; padding-top:4px;">
									<a href="' . esc_html( $redirection ) . '">
										' . esc_html__( ' Dismiss this notice', 'eb-textdomain' ) . '
									</a>
								</div>
							</div>
							<div>
								<a class="eb_admin_discount_offer_btn" href="https://edwiser.org/edwiser-lifetime-kit/?utm_source=WordPress&utm_medium=notif&utm_campaign=inbridge"  target="_blank">' . esc_html__( 'Avail Offer Now!', 'eb-textdomain' ) . '</a>
							</div>
						</div>
						<div class="eb_admin_discount_dismiss_notice_message">
							<span class="dashicons dashicons-dismiss eb_admin_discount_notice_hide"></span>
						</div>
					</div>';
		}
	}





	/**
	 * Handle notice dismiss
	 *
	 * @since 1.3.1
	 */
	public function eb_admin_update_notice_dismiss_handler() {
		if ( isset( $_GET['eb-update-notice-dismissed'] ) ) {
			update_option( 'eb_update_notice_dismissed', 'true', true );
		}
	}




	/**
	 * NOT USED FUNCTION
	 * show admin feedback notice
	 *
	 * @since 1.3.1
	 */
	public function eb_admin_feedback_notice() {
		$redirection = '?eb-feedback-notice-dismissed';
		if ( isset( $_GET ) && ! empty( $_GET ) ) {
			$request_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
			$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			$redirection  = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $request_host . $request_uri;
			$redirection .= '&eb-feedback-notice-dismissed';
		}

		$user_id           = get_current_user_id();
		$feedback_usermeta = get_user_meta( $user_id, 'eb_feedback_notice_dismissed', true );
		if ( 'eb_admin_feedback_notice' !== get_transient( 'edwiser_bridge_admin_feedback_notice' ) ) {
			if ( ( ! $feedback_usermeta || 'remind_me_later' !== $feedback_usermeta ) && 'dismiss_permanantly' !== $feedback_usermeta ) {
				echo '  <div class="notice eb_admin_feedback_notice_message_cont">
							<div class="eb_admin_feedback_notice_message">'
								. esc_html__( 'Enjoying Edwiser bridge, Please  ', 'eb-textdomain' ) . '
								<a href="https://WordPress.org/plugins/edwiser-bridge/">'
									. esc_html__( ' click here ', 'eb-textdomain' ) .
								'</a>'
								. esc_html__( ' to rate us.', 'eb-textdomain' ) . '
								<div style="padding-top:8px; font-size:13px;">
									<span class="eb_feedback_rate_links">
										<a href="' . esc_html( $redirection ) . '=remind_me_later">
										' . esc_html__( 'Remind me Later!', 'eb-textdomain' ) . '
										</a>
									</span>
									<span class="eb_feedback_rate_links">
										<a href="' . esc_html( $redirection ) . '=dismiss_permanantly">
										' . esc_html__( 'Dismiss Notice', 'eb-textdomain' ) . '
										</a>
									</span>
								</div>
							</div>
							<div class="eb_admin_feedback_dismiss_notice_message">
								<span class="dashicons dashicons-dismiss"></span>
							</div>
						</div>';
			}
		}
	}


	/**
	 * Handle notice dismiss
	 *
	 * @since 1.3.1
	 */
	public function eb_admin_notice_dismiss_handler() {
		$user_id = get_current_user_id();
		if ( isset( $_GET['eb-feedback-notice-dismissed'] ) ) {
			add_user_meta( $user_id, 'eb_feedback_notice_dismissed', sanitize_text_field( wp_unslash( $_GET['eb-feedback-notice-dismissed'] ) ), true );
		}
	}


	/**
	 * SHow notfi.
	 *
	 * @param text $curr_plugin_meta_data curr_plugin_meta_data.
	 * @param text $new_plugin_meta_data new_plugin_meta_data.
	 */
	public function eb_show_inline_plugin_update_notification( $curr_plugin_meta_data, $new_plugin_meta_data ) {
		// check "upgrade_notice".

		// added this just for commit purpose.
		$curr_plugin_meta_data = $curr_plugin_meta_data;
		$new_plugin_meta_data  = $new_plugin_meta_data;

		ob_start();
		?>
			<p>
				<strong><?php echo esc_html__( 'Important Update Notice:', 'eb-textdomain' ); ?></strong>
				<?php echo esc_html__( 'Please download and update associated edwiserbridge Moodle plugin.', 'eb-textdomain' ); ?>
				<a href="https://edwiser.org/bridge/"><?php echo esc_html__( 'Click here ', 'eb-textdomain' ); ?></a>
				<?php echo esc_html__( ' to download', 'eb-textdomain' ); ?>

			</p>

		<?php
		echo esc_html( ob_get_clean() );
	}
}
