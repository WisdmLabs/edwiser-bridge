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

<section class="eb-user-info eb-edit-user-wrapper">
	<aside class="eb-user-picture">
		<?php echo wp_kses( $user_avatar, \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() ); ?>
	</aside>
	<div class="eb-user-data">
		<?php
		/* translators 1: display name 2: display name 3: a tag opening string 4: a tag closing */
		printf( esc_attr__( 'Hello', 'edwiser-bridge' ) . ' <strong>%s</strong> (' . esc_attr__( 'not', 'edwiser-bridge' ) . ' <strong>%s</strong>? %s ' . esc_attr__( 'Sign out ', 'edwiser-bridge' ) . '%s)', esc_html( $user->display_name ), esc_html( $user->display_name ), '<a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">', '</a>' );
		?>
		<div>
			<?php
			$user_info = get_userdata( get_current_user_id() );
			echo esc_html( $user_info->description );
			?>
		</div>
	</div>
</section>
