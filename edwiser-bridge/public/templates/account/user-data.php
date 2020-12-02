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

<section class="eb-user-info eb-edit-user-wrapper">
	<aside class="eb-user-picture">
		<?php echo esc_html( $user_avatar ); ?>
	</aside>
	<div class="eb-user-data">
		<?php
		/* translators 1: display name 2: display name 3: a tag opening string 4: a tag closing */
		printf( esc_attr__( 'Hello <strong>%$1s</strong> (not <strong>%$2s</strong>? %$3sSign out%$4s)', 'eb-textdomain' ), esc_html( $user->display_name ), esc_html( $user->display_name ), '<a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">', '</a>' );
		?>
		<div>
			<?php
			$user_info = get_userdata( get_current_user_id() );
			echo esc_html( $user_info->description );
			?>
		</div>
	</div>
</section>
