<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://edwiser.org
 * @since      1.3.4
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Eb admin handler.
 */
class Eb_Admin_Notice_Handler {

	/**
	 * Check if installed.
	 */
	public function check_if_moodle_plugin_installed() {
		$plugin_installed   = 1;
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
			$plugin_installed = 0;
		} elseif ( 200 === wp_remote_retrieve_response_code( $response ) ||
				300 === wp_remote_retrieve_response_code( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );

			if ( 'accessexception' === $body->errorcode ) {
				$plugin_installed = 0;
			}
		} else {
			$plugin_installed = 0;
		}

		return $plugin_installed;
	}

	/**
	 * Get Moodle plugin Info.
	 * Currently only version is provided.
	 */
	public function eb_get_mdl_plugin_info() {

		$moodle_function = 'eb_get_edwiser_plugins_info';

		$response = edwiser_bridge_instance()->connection_helper()->connect_moodle_helper( $moodle_function );

		if ( isset( $response['success'] ) && $response['success'] ) {
			$plugin_info = $response['response_data'];
			return $plugin_info;
		} else {
			return false;
		}
	}



	/**
	 * Show admin feedback notice.
	 *
	 * @since 1.3.1
	 */
	public function eb_admin_update_moodle_plugin_notice() {

		// show notice.
		$redirection = add_query_arg( 'eb-update-notice-dismissed', true );
		$plugin_data = get_option( 'eb_mdl_plugin_update_check' );
		if ( ! get_option( 'eb_mdl_plugin_update_notice_dismissed' ) && is_array( $plugin_data ) && ! empty( $plugin_data ) ) {
			?>
			<div class="notice  eb_admin_update_notice_message_cont">
				<div class="eb_admin_update_notice_message">
					<div class="eb_admin_update_notice_message_logo">
						<img src="<?php echo esc_url( plugins_url( 'images/logo.png', dirname( __FILE__ ) ) ); ?>" alt="Edwiser Bridge Logo" />
					</div>
					<div class="eb_update_notice_content">
						<p>
							<?php esc_html_e( 'Thanks for using Edwiser Bridge plugin. To avoid any malfunctioning please', 'edwiser-bridge' ); ?> <strong><?php esc_html_e( 'make sure you have also installed and activated our latest associated Moodle Plugin', 'edwiser-bridge' ); ?></strong>
							<?php esc_html_e( 'on your Moodle site.', 'edwiser-bridge' ); ?>
						</p>
						<p>
							<?php esc_html_e( 'New version of the following plugins are available for the Moodle site.', 'edwiser-bridge' ); ?>
						</p>
						<ul>
							<?php
							foreach ( $plugin_data as $plugin ) {
								?>
								<li>
									<?php
									echo '<strong>' . esc_html( $plugin['name'] ) . '</strong> ' . esc_html( $plugin['new_version'] ) . ' ' . esc_html__( 'is available.', 'edwiser-bridge' );
									?>
									<a href="<?php echo esc_url( $plugin['url'] ); ?>"><?php esc_html_e( ' Click here ', 'edwiser-bridge' ); ?></a>
									<?php esc_html_e( ' to download plugin.', 'edwiser-bridge' ); ?>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
				</div>
				<div class="eb_admin_update_dismiss_notice_message">
					<a href="<?php echo esc_url( $redirection ); ?>">
						<span class="dashicons dashicons-no-alt eb_update_notice_hide"></span>
					</a>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Check if Moodle plugin update is available.
	 *
	 * @since 2.2.6
	 */
	public function eb_check_mdl_plugin_update() {
		$plugin_info = $this->eb_get_mdl_plugin_info();
		$url         = 'https://edwiser.org/edwiserdemoimporter/bridge-free-plugin-info.json';

		// set user agent.
		$args        = array(
			'timeout' => 15,
			'headers' => array(
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			),
		);
		$response    = wp_remote_get( $url, $args );
		$update_data = array();

		if ( 200 === wp_remote_retrieve_response_code( $response ) && $plugin_info ) {
			$responce = json_decode( wp_remote_retrieve_body( $response ) );
			if ( isset( $plugin_info->plugin_name ) && 'edwiser_bridge' === $plugin_info->plugin_name ) {
				$update_data[] = array(
					'slug'        => 'moodle_edwiser_bridge',
					'name'        => $responce->moodle_edwiser_bridge->name,
					'new_version' => $responce->moodle_edwiser_bridge->version,
					'old_version' => $plugin_info->version,
					'url'         => $responce->moodle_edwiser_bridge->url,
				);
			} else {
				$plugin_info = $plugin_info->plugins;
				foreach ( $plugin_info as $plugin ) {
					$plugin_name = $plugin->plugin_name;
					if ( 'moodle_edwiser_bridge_pro' === $plugin_name ) {
						update_option( 'moodle_edwiser_bridge_pro', $plugin->version );
						continue;
					}
					if ( version_compare( $plugin->version, $responce->{$plugin_name}->version, '<' ) ) {
						$update_data[] = array(
							'slug'        => $plugin_name,
							'name'        => $responce->{$plugin_name}->name,
							'new_version' => $responce->{$plugin_name}->version,
							'old_version' => $plugin->version,
							'url'         => $responce->{$plugin_name}->url,
						);
					}
				}
			}
		}
		update_option( 'eb_mdl_plugin_update_check', $update_data );
		// delete option dismiss notice.
		delete_option( 'eb_mdl_plugin_update_notice_dismissed' );
	}



	/**
	 * NOT USED FUNCTION
	 * handle notice dismiss
	 *
	 * @deprecated since 2.0.1 discontinued.
	 * @since 1.3.1
	 */
	public function eb_admin_discount_notice_dismiss_handler() {
		if ( true === filter_input( INPUT_GET, 'eb-discount-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) ) {
			$user_id = get_current_user_id();
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
		$redirection = add_query_arg( 'eb-discount-notice-dismissed', true );

		$user_id = get_current_user_id();
		if ( ! get_user_meta( $user_id, 'eb_discount_notice_dismissed' ) ) {
			echo '  <div class="notice  eb_admin_discount_notice_message">
						<div class="eb_admin_discount_notice_message_cont">
							<div class="eb_admin_discount_notice_content">
								' . esc_html__( 'Get all Premium Edwiser Products at Flat 20% Off!', 'edwiser-bridge' ) . '

								<div style="font-size:13px; padding-top:4px;">
									<a href="' . esc_html( $redirection ) . '">
										' . esc_html__( ' Dismiss this notice', 'edwiser-bridge' ) . '
									</a>
								</div>
							</div>
							<div>
								<a class="eb_admin_discount_offer_btn" href="https://edwiser.org/edwiser-lifetime-kit/?utm_source=WordPress&utm_medium=notif&utm_campaign=inbridge"  target="_blank">' . esc_html__( 'Avail Offer Now!', 'edwiser-bridge' ) . '</a>
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
		if ( true === filter_input( INPUT_GET, 'eb-update-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) ) {
			update_option( 'eb_mdl_plugin_update_notice_dismissed', 'true', true );
		}
	}




	/**
	 * NOT USED FUNCTION
	 * show admin feedback notice
	 *
	 * @since 1.3.1
	 */
	public function eb_admin_feedback_notice() {
		$redirection       = add_query_arg( 'eb-feedback-notice-dismissed', true );
		$user_id           = get_current_user_id();
		$feedback_usermeta = get_user_meta( $user_id, 'eb_feedback_notice_dismissed', true );
		if ( 'eb_admin_feedback_notice' !== get_transient( 'edwiser_bridge_admin_feedback_notice' ) && ( ! $feedback_usermeta || 'remind_me_later' !== $feedback_usermeta ) && 'dismiss_permanantly' !== $feedback_usermeta ) {
				echo '  <div class="notice eb_admin_feedback_notice_message_cont">
							<div class="eb_admin_feedback_notice_message">'
								. esc_html__( 'Enjoying Edwiser bridge, Please  ', 'edwiser-bridge' ) . '
								<a href="https://WordPress.org/plugins/edwiser-bridge/">'
									. esc_html__( ' click here ', 'edwiser-bridge' ) .
								'</a>'
								. esc_html__( ' to rate us.', 'edwiser-bridge' ) . '
								<div style="padding-top:8px; font-size:13px;">
									<span class="eb_feedback_rate_links">
										<a href="' . esc_html( $redirection ) . '=remind_me_later">
										' . esc_html__( 'Remind me Later!', 'edwiser-bridge' ) . '
										</a>
									</span>
									<span class="eb_feedback_rate_links">
										<a href="' . esc_html( $redirection ) . '=dismiss_permanantly">
										' . esc_html__( 'Dismiss Notice', 'edwiser-bridge' ) . '
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


	/**
	 * Handle notice dismiss
	 *
	 * @since 1.3.1
	 */
	public function eb_admin_notice_dismiss_handler() {
		if ( true === filter_input( INPUT_GET, 'eb-feedback-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) ) {
			$user_id = get_current_user_id();
			add_user_meta( $user_id, 'eb_feedback_notice_dismissed', filter_input( INPUT_GET, 'eb-feedback-notice-dismissed', FILTER_VALIDATE_BOOLEAN ), true );
		}
	}






	/**
	 * SHow notfi.
	 *
	 * @param text $curr_plugin_meta_data curr_plugin_meta_data.
	 * @param text $new_plugin_meta_data new_plugin_meta_data.
	 */
	public function eb_show_inline_plugin_update_notification( $curr_plugin_meta_data, $new_plugin_meta_data ) {
		ob_start();
		?>
<p>
	<strong><?php echo esc_html__( 'Important Update Notice:', 'edwiser-bridge' ); ?></strong>
		<?php echo esc_html__( 'Please download and update associated edwiserbridge Moodle plugin.', 'edwiser-bridge' ); ?>
	<a href="https://edwiser.org/bridge-wordpress-moodle-integration/"><?php echo esc_html__( 'Click here ', 'edwiser-bridge' ); ?></a>
		<?php echo esc_html__( ' to download', 'edwiser-bridge' ); ?>

</p>

		<?php
		echo wp_kses( ob_get_clean(), \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags() );

		// added this just for commit purpose.
		unset( $curr_plugin_meta_data );
		unset( $new_plugin_meta_data );
	}

	/**
	 * Bfcm notice dismiss handler.
	 */
	public function eb_admin_bfcm_notice_dismiss_handler() {
		if ( true === filter_input( INPUT_GET, 'eb-admin-bfcm-pre-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) ) {
			$user_id = get_current_user_id();
			add_user_meta( $user_id, 'eb_admin_bfcm_pre_notice_dismissed', filter_input( INPUT_GET, 'eb-admin-bfcm-pre-notice-dismissed', FILTER_VALIDATE_BOOLEAN ), true );
		}
		if ( true === filter_input( INPUT_GET, 'eb-admin-bfcm-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) ) {
			$user_id = get_current_user_id();
			add_user_meta( $user_id, 'eb_admin_bfcm_notice_dismissed', filter_input( INPUT_GET, 'eb-admin-bfcm-notice-dismissed', FILTER_VALIDATE_BOOLEAN ), true );
		}
	}
	/**
	 * BFCM admin notice
	 *
	 * @since 2.2.4
	 */
	public function eb_admin_bfcm_notice() {

		$user_id = get_current_user_id();
		global $pagenow;
		$screen = get_current_screen();
		if ( is_admin() && ( 'index.php' === $pagenow || 'eb_course_page_eb-settings' === $screen->id ) ) {

			$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

			// chek if current date is between 4th and 23rd of november.
			$bfcm_pre_start_date = strtotime( '2022-11-04' );
			$bfcm_pre_end_date   = strtotime( '2022-11-23' );
			$bfcm_start_date     = strtotime( '2022-11-24' );
			$bfcm_end_date       = strtotime( '2022-11-30' );
			$current_date        = strtotime( wp_date( 'Y-m-d' ) );

			// pre bfcm banner.
			if ( $current_date >= $bfcm_pre_start_date && $current_date <= $bfcm_pre_end_date && ! get_user_meta( $user_id, 'eb_admin_bfcm_pre_notice_dismissed' ) ) {
				$redirection = add_query_arg( 'eb-admin-bfcm-pre-notice-dismissed', true );
				?>
				<div class="notice eb-admin-bfcm-notice-message">
					<div class="eb-admin-bfcm-notice-message-content" style="background-image: url('<?php echo esc_url( $eb_plugin_url ); ?>images/bfcm-pre.png');">
						<p class="title"><?php esc_html_e( 'Play a Game with Edwiser to get ahead of the Black Friday Sale!', 'edwiser-bridge' ); ?></p>
						<p class="desc"><?php esc_html_e( 'Spin the Wheel to Win Free Access or Discounts on our Premium Moodle theme & plugins', 'edwiser-bridge' ); ?></p>
						<a class="button" href="https://edwiser.org/edwiser-black-friday-giveaway/?utm_source=giveaway&utm_medium=spinthewheel&utm_campaign=bfcm22"  target="_blank"><?php esc_html_e( 'Spin and Win', 'edwiser-bridge' ); ?></a>
					</div>
					<div class="eb-admin-bfcm-notice-message-dismiss">
						<a href="<?php echo esc_html( $redirection ); ?>">
							<span class="dashicons dashicons-no-alt eb_admin_bfcm_notice_hide"></span>
						</a>
					</div>
				</div>
				<?php
			} elseif ( $current_date >= $bfcm_start_date && $current_date <= $bfcm_end_date && ! get_user_meta( $user_id, 'eb_admin_bfcm_notice_dismissed' ) ) {
				// bfcm banner.
				$extensions = array(
					'woocommerce-integration/bridge-woocommerce.php',
					'selective-synchronization/selective-synchronization.php',
					'edwiser-bridge-sso/sso.php',
					'edwiser-multiple-users-course-purchase/edwiser-multiple-users-course-purchase.php',
				);
				foreach ( $extensions as $plugin_path ) {
					if ( is_plugin_active( $plugin_path ) ) {
						$free = false;
					} else {
						$free = true;
						break;
					}
				}
				$redirection = add_query_arg( 'eb-admin-bfcm-notice-dismissed', true );
				?>
				<div class="notice eb-admin-bfcm-notice-message">
					<div class="eb-admin-bfcm-notice-message-content" style="background-image: url('<?php echo esc_url( $eb_plugin_url ); ?>images/bfcm.png');">
						<p class="title"><?php esc_html_e( 'Get the power of selling courses via WooCommerce!', 'edwiser-bridge' ); ?></p>
						<?php
						if ( $free ) {
							?>
							<p class="desc"><?php esc_html_e( 'Get amazing Black Friday discounts on Edwiser Bridge Pro', 'edwiser-bridge' ); ?></p>
							<a class="button" href="https://edwiser.org/bridge-wordpress-moodle-integration/?utm_source=freeplugin&utm_medium=banner&utm_campaign=bfcm22"  target="_blank"><?php esc_html_e( 'Upgrade Now', 'edwiser-bridge' ); ?></a>
							<?php
						} else {
							?>
							<p class="desc"><?php esc_html_e( 'Get amazing Black Friday discounts on Edwiser Bridge Pro Lifetime license', 'edwiser-bridge' ); ?></p>
							<a class="button" href="https://edwiser.org/bridge-wordpress-moodle-integration/?utm_source=proplugin&utm_medium=banner&utm_campaign=bfcm22"  target="_blank"><?php esc_html_e( 'Upgrade tO Lifetime', 'edwiser-bridge' ); ?></a>
							<?php
						}
						?>
					</div>
					<div class="eb-admin-bfcm-notice-message-dismiss">
						<a href="<?php echo esc_html( $redirection ); ?>">
							<span class="dashicons dashicons-no-alt eb_admin_bfcm_notice_hide"></span>
						</a>
					</div>
				</div>
				<?php
			}
		}
	}

	/**
	 * Dismiss the notice.
	 */
	public function eb_admin_remui_demo_notice_dismiss_handler() {
		if ( true === filter_input( INPUT_GET, 'eb-admin-remui-notice-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) ) {
			$user_id = get_current_user_id();
			add_user_meta( $user_id, 'eb_admin_remui_demo_notice_dismissed', filter_input( INPUT_GET, 'eb-admin-remui-notice-notice-dismissed', FILTER_VALIDATE_BOOLEAN ), true );
		}
	}

	/**
	 * Remui demo notice.
	 */
	public function eb_admin_remui_demo_notice() {
		$redirection = add_query_arg( 'eb-admin-remui-notice-notice-dismissed', true );
		$user_id     = get_current_user_id();
		if ( ! get_user_meta( $user_id, 'eb_admin_remui_demo_notice_dismissed' ) ) {
			?>
			<div class="notice  eb_admin_remui_demo_notice">
				<a style="text-decoration:none;" target="_blank" href="https://remui.edwiser.org/schoolv2/?utm_source=remui4.0-launch-bridge-thankyoupage&utm_medium=remui4.0-launch-bridge-thankyoupage-bannerctaclicks&utm_campaign=remui-4.0-launch">
				<div class="eb_admin_remui_demo_notice_message">
					<div class="eb_admin_remui_demo_notice_message_logo">
						<img src="<?php echo esc_url( plugins_url( 'images/logo.png', dirname( __FILE__ ) ) ); ?>" alt="Edwiser Bridge Logo" />
					</div>
					<div class="eb_remui_demo_notice_content" style="background-image:url('<?php echo esc_url( plugins_url( 'images/remui_back.png', dirname( __FILE__ ) ) ); ?>'">
						<p class="remui-title">
							<?php esc_html_e( 'We’ve something new for you to help you design an amazingly personalized Moodle - faster & easier.', 'edwiser-bridge' ); ?> <b><?php esc_html_e( ' Our fully redesigned Edwiser RemUI theme is LAUNCHED!', 'edwiser-bridge' ); ?></b>
						</p>
						<p class="remui-content">
							<?php esc_html_e( 'Check out the demo to see the modern layouts, ready-to-use homepages, brilliantly upgraded dashboard & more', 'edwiser-bridge' ); ?>
						</p>
						<button class="remui-button" href="#">Explore the demo</button>
					</div>
				</div>
				</a>
				<img class="eb_admin_remui_demo_image" src="<?php echo esc_url( plugins_url( 'images/remui-demo.png', dirname( __FILE__ ) ) ); ?>" alt="Edwiser Remu UI demo">
				<div class="eb_admin_remui_demo_dismiss_notice_message">
					<a href="<?php echo esc_url( $redirection ); ?>">
						<span class="dashicons dashicons-no-alt eb_remui_demo_notice_hide"></span>
					</a>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Dismiss the notice.
	 */
	public function eb_admin_pro_notice_dismiss_handler() {
		if ( true === filter_input( INPUT_GET, 'eb-admin-pro-notice-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) ) {
			update_option( 'eb_pro_consolidated_plugin_notice_dismissed', filter_input( INPUT_GET, 'eb-admin-pro-notice-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) );
		}
	}

	/**
	 * Notice/popup for edwiser bridge pro after activation.
	 */
	public function eb_admin_pro_notice() {
		global $eb_plugin_data;
		$transient = get_transient( '_eb_pro_consolidated_plugin_notice' );
		$dismissed = get_option( 'eb_pro_consolidated_plugin_notice_dismissed' );
		// if transient is set then show popup.
		if ( $transient ) {
			delete_transient( '_eb_pro_consolidated_plugin_notice' );
			?>
			<div class="eb-admin-pro-popup">
				<div class='eb-admin-pro-popup-content'>
					<div class="eb-admin-pro-popup-success-msg"> <span class="dashicons dashicons-yes-alt"></span> <?php echo sprintf( esc_html__( 'Edwiser Bridge version %s activated successfully', 'edwiser-bridge' ), $eb_plugin_data['version'] ); // @codingStandardsIgnoreLine ?></div>
					<p class="eb-admin-pro-popup-title"><?php echo esc_html__( 'Introducing', 'edwiser-bridge' ) . '<strong>' . esc_html__( ' consolidated Edwiser Bridge Pro Plugin', 'edwiser-bridge' ) . '</strong>' . esc_html__( ' for smoother configuration experience', 'edwiser-bridge' ); ?></p>
					<p class="eb-admin-pro-popup-text"><?php echo esc_html__( 'Starting from Edwiser Bridge version 3.0.0,  all the Edwiser Bridge Pro add-on plugins  have been combined into a', 'edwiser-bridge' ) . '<strong>' . esc_html__( ' single plugin -Edwiser Bridge Pro', 'edwiser-bridge' ) . '</strong>' . esc_html__( ' to provide a smoother and better experience for installing, configuring and updating Edwiser Bridge Pro.', 'edwiser-bridge' ); ?></p>
					<p class="eb-admin-pro-popup-text"><?php echo esc_html__( 'To install and activate the', 'edwiser-bridge' ) . '<strong>' . esc_html__( ' new Edwiser Bridge Pro plugin', 'edwiser-bridge' ) . '</strong>' . esc_html__( ' click below', 'edwiser-bridge' ); ?></p>
					<a class="eb-admin-pro-popup-button" href="<?php echo esc_url( admin_url( 'admin.php?page=eb-settings&tab=licensing' ) ); ?>"><?php esc_html_e( 'Activate Now', 'edwiser-bridge' ); ?> </a>
				</div>
			</div>
			<?php
		} elseif ( ( ! $dismissed && ! $transient ) ) {
			$redirection = add_query_arg( 'eb-admin-pro-notice-notice-dismissed', true );
			?>
			<div class="notice  eb_admin_update_notice_message_cont">
				<div class="eb_admin_update_notice_message">
					<div class="eb_admin_update_notice_message_logo">
						<img src="<?php echo esc_url( plugins_url( 'images/logo.png', dirname( __FILE__ ) ) ); ?>" alt="Edwiser Bridge Logo" />
					</div>
					<div class="eb_update_notice_content">
						<p>
							<?php esc_html_e( 'Introducing', 'edwiser-bridge' ); ?> <strong><?php esc_html_e( 'consolidated Edwiser Bridge Pro Plugin', 'edwiser-bridge' ); ?></strong> <?php esc_html_e( 'for smoother configuration experience', 'edwiser-bridge' ); ?>
						</p>
						<p>
							<?php esc_html_e( 'Starting from Edwiser Bridge version 3.0.0,  all the Edwiser Bridge Pro add-on plugins  have been combined into a single plugin -Edwiser Bridge Pro to provide a smoother and better experience for installing, configuring and updating Edwiser Bridge Pro.', 'edwiser-bridge' ); ?>
						</p>
						<p>
							<?php esc_html_e( 'To install and activate the ', 'edwiser-bridge' ); ?> <strong><?php esc_html_e( 'new Edwiser Bridge Pro plugin', 'edwiser-bridge' ); ?> </strong><?php esc_html_e( ' click below', 'edwiser-bridge' ); ?>
						</p>
						<p class="eb-admin-pro-popup-button-wrap">
							<a class="eb-admin-pro-popup-button" href="<?php echo esc_url( admin_url( 'admin.php?page=eb-settings&tab=licensing' ) ); ?>"><?php esc_html_e( 'Activate Now', 'edwiser-bridge' ); ?> </a>
							<a class="eb-admin-pro-popup-dismiss-button" href="<?php echo esc_url( $redirection ); ?>"><?php esc_html_e( 'Dismiss Forever', 'edwiser-bridge' ); ?> </a>
						</p>
					</div>
				</div>
				<div class="eb_admin_update_dismiss_notice_message">
					<!-- <a href="<?php echo esc_url( $redirection ); ?>"> -->
						<span class="dashicons dashicons-no-alt eb_update_notice_hide"></span>
					<!-- </a> -->
				</div>
			</div>
			<?php
		}
	}

}
