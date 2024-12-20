<?php
/**
 * New User Account Email Template.
 *
 * @package Edwiser Bridge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php do_action( 'eb_email_header', $args['header'] ); ?>

<p>
	<?php
		/* Tanslators 1: first_name */
		printf( esc_html__( 'Hi %$1s', 'edwiser-bridge' ), esc_html( $args['first_name'] ) );
	?>
</p>

<p>
	<?php
	printf(
		esc_html__(
			'A learning account is linked to your profile.
        Use credentials given below while accessing your courses.',
			'edwiser-bridge'
		)
	);
	?>
</p>

<p>
	<?php
		/* Tanslators 1: username */
		printf( esc_html__( 'Username: <strong>%$1s</strong>', 'edwiser-bridge' ), esc_html( $args['username'] ) );
	?>
</p>

<p>
	<?php
		/* Tanslators 1: password */
		printf( esc_html__( 'Password: <strong>%$1s</strong>', 'edwiser-bridge' ), esc_html( $args['password'] ) );
	?>
</p>

<p>
	<?php
	/* Tanslators 1: course url */
	printf(
		esc_html__(
			'To purchase and access more courses click here: <a href="%$1s">Courses</a>.',
			'edwiser-bridge'
		),
		esc_html( wp_site_url( '/courses' ) )
	);
	?>
</p>

<?php
do_action( 'eb_email_footer' );
