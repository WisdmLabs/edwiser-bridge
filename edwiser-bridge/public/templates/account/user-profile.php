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
$nonce_name = 'eb_user_account_nav_nonce';

// Return only if nonce is not set.
if ( ! isset( $_GET[ $nonce_name ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET[ $nonce_name ] ) ), $nonce_name ) ) {
	return false;
}

if ( isset( $_GET['eb_action'] ) && 'edit-profile' === sanitize_text_field( wp_unslash( $_GET['eb_action'] ) ) ) {
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
	?>
	<section class="eb-user-info">
		<aside class="eb-user-picture">
			<?php echo esc_html( $user_avatar ); ?>
		</aside>
		<div class="eb-user-data">
			<div><?php echo esc_html( $user->first_name ) . ' ' . esc_html( $user->last_name ); ?></div>
			<div><?php esc_html( $user->user_email ); ?></div>
		</div>

		<div class="eb-edit-profile" >
			<a href="<?php echo esc_url( add_query_arg( 'eb_action', 'edit-profile', get_permalink() ) ); ?>" class="wdm-btn"><?php esc_html_e( 'Edit Profile', 'edwiser-bridge' ); ?></a>
		</div>

	</section>
	<?php
}
?>

	<section class="eb-user-courses">
		<div class="course-heading" ><span><?php esc_html_e( 'S.No.', 'edwiser-bridge' ); ?></span> <span><?php esc_html_e( 'Enrolled Courses', 'edwiser-bridge' ); ?></span></div>
		<div class="eb-course-data">
<?php
if ( ! empty( $enrolled_courses ) ) {
	foreach ( $enrolled_courses as $key => $course ) {
		echo '<div class="eb-course-section course_' . esc_html( $course->ID ) . '">';
		echo '<div>' . esc_html( ( $key++ ) ) . '. </div>';
		echo '<div><a href="' . esc_html( get_the_permalink( $course->ID ) ) . '">' . esc_html( $course->post_title ) . '</a></div>';
		echo esc_html( \app\wisdmlabs\edwiserBridge\Eb_Payment_Manager::access_course_button( $course->ID ) );
		echo '</div>';
	}
} else {
	?>
	<p class="eb-no-course">
		<?php
		/* Translator 1: course url */
		printf(
			esc_html__( 'Looks like you are not enrolled in any course, get your first course %$1s', 'edwiser-bridge' ),
			'<a href="' . esc_url( site_url( '/courses' ) ) . '">' . esc_html__( 'here', 'edwiser-bridge' ) . '</a>.'
		);
		?>
	</p>
	<?php
}
?>
		</div>
	</section>
</div>
