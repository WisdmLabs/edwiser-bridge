<?php
/**
 * User account.
 *
 * @link       https://edwiser.org
 * @since      1.0.2
 * @deprecated 1.2.0 Use shortcode eb_user_account
 * @package    Edwiser Bridge.
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<div class="eb-user-profile" >
	<?php
	$eb_shortcode_obj = \app\wisdmlabs\edwiserBridge\Eb_Shortcode_User_Account::getInstance();
	if ( isset( $_GET['eb_action'] ) && 'edit-profile' === sanitize_text_field( wp_unslash( $_GET['eb_action'] ) ) ) { // WPCS: input var okay, CSRF ok.
		$template_loader->wp_get_template(
			'account/edit-user-profile.php',
			array(
				'user_avatar'      => $user_avatar,
				'user'             => $user,
				'user_meta'        => $user_meta,
				'enrolled_courses' => $enrolled_courses,
				'template_loader'  => $template_loader,
			)
		);
	} else {
		$labels = $eb_shortcode_obj->get_user_account_navigation_items();
		?>
		<div class="eb-user-account-navigation">
			<div>
				<?php
				foreach ( $labels as $label ) {
					$nav_item  = isset( $label['label'] ) ? $label['label'] : '';
					$nav_href  = isset( $label['href'] ) ? $label['href'] : '';
					$css_class = 'eb-user-account-navigation-link';
					if ( isset( $_GET['eb-active-link'] ) && sanitize_text_field( wp_unslash( $_GET['eb-active-link'] ) ) === $nav_href ) { // WPCS: input var okay, CSRF ok.
						$css_class .= ' eb-active-profile-nav';
					}
					?>
					<nav class="<?php echo esc_html( $css_class ); ?>">
						<a href="<?php echo esc_url( add_query_arg( 'eb-active-link', $nav_href, get_permalink() ) ); ?>"><?php _e( $nav_item, 'eb-textdomain' ); // @codingStandardsIgnoreLine. ?></a>
					</nav>
					<?php
				}
				?>
			</div>
		</div>
		<div class="eb-user-account-content">
			<?php
			if ( isset( $_GET['eb-active-link'] ) ) { // WPCS: input var okay, CSRF ok.
				$eb_shortcode_obj->get_user_account_content( sanitize_text_field( wp_unslash( $_GET['eb-active-link'] ) ), $user_orders, $order_count, $user_avatar, $user, $user_meta, $enrolled_courses, $template_loader ); // WPCS: input var okay, CSRF ok.
			} else {
				$template_loader->wp_get_template(
					'account/user-data.php',
					array(
						'user'        => $user,
						'user_avatar' => $user_avatar,
					)
				);
			}
			?>
		</div>
		<?php
	}// end of else i.e content for logged in users
	?>
</div>
