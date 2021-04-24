<?php
/**
 * User account.
 *
 * @link       https://edwiser.org
 * @since      1.0.2
 * @deprecated 1.2.0 Use shortcode eb_user_account
 * @package    Edwiser Bridge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
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
		$labels      = $eb_shortcode_obj->get_user_account_navigation_items();
		$active_link = isset( $_GET['eb-active-link'] ) ? sanitize_text_field( wp_unslash( $_GET['eb-active-link'] ) ) : ''; // WPCS: input var okay, CSRF ok.
		?>
		<div class="eb-user-account-navigation">
			<div>
				<?php
				foreach ( $labels as $label ) {
					$nav_item  = isset( $label['label'] ) ? $label['label'] : '';
					$nav_href  = isset( $label['href'] ) ? $label['href'] : '';
					$tab_url   = add_query_arg( 'eb-active-link', $nav_href, get_permalink() );
					$tab_url   = add_query_arg( 'eb_user_account_nav_nonce', wp_create_nonce( 'eb_user_account_nav_nonce' ), $tab_url );
					$css_class = 'eb-user-account-navigation-link';
					if ( $active_link === $nav_href ) {
						$css_class .= ' eb-active-profile-nav';
					}
					?>
					<nav class="<?php echo esc_html( $css_class ); ?>">
						<a href="<?php echo esc_url( $tab_url ); ?>"><?php esc_html_e( $nav_item, 'eb-textdomain' ); // @codingStandardsIgnoreLine. ?></a>
					</nav>
					<?php
				}
				?>
			</div>
		</div>
		<div class="eb-user-account-content">
			<?php
			if ( '' !== $active_link ) {
				$eb_shortcode_obj->get_user_account_content( $active_link, $user_orders, $order_count, $user_avatar, $user, $user_meta, $enrolled_courses, $template_loader );
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
