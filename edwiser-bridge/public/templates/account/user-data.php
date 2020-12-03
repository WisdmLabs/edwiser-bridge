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
		<?php
		echo wp_kses(
			$user_avatar,
			array(
				'img' => array(
					'src'    => array(),
					'class'  => array(),
					'srcset' => array(),
					'width'  => array(),
					'height' => array(),
				),
			)
		);
		?>
	</aside>
	<div class="eb-user-data">
		<?php
		/* translators 1: display name 2: display name 3: a tag opening string 4: a tag closing */
		printf( esc_html__( 'Hello ', 'eb-textdomain' ) . '<strong>%s</strong>' . esc_html__( ' (not ', 'eb-textdomain' ) . '<strong>%s</strong>' . esc_html__( '? ', 'eb-textdomain' ) . '%s ' . esc_html__( 'Sign out', 'eb-textdomain' ) . '%s)', esc_html( $user->display_name ), esc_html( $user->display_name ), '<a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">', '</a>' );
		?>
		<div>
			<?php
			$user_info = get_userdata( get_current_user_id() );
			echo esc_html( $user_info->description );
			?>
		</div>
	</div>
</section>
