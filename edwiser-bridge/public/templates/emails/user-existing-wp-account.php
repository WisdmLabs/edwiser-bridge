<?php
/**
 * New User Account Email Template.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php do_action( 'eb_email_header', $args['header'] ); ?>

<p>
	<?php
		/* Tanslators 1: first_name */
		printf( __( 'Hi %$1s', 'eb-textdomain' ), $args['first_name'] );
	?>
</p>

<p>
	<?php
	printf(
		__(
			'A learning account is linked to your profile.
        Use credentials given below while accessing your courses.',
			'eb-textdomain'
		)
	);
	?>
</p>

<p>
	<?php
		/* Tanslators 1: username */
		printf( __( 'Username: <strong>%$1s</strong>', 'eb-textdomain' ), $args['username'] );
	?>
</p>

<p>
	<?php
		/* Tanslators 1: password */
		printf( __( 'Password: <strong>%$1s</strong>', 'eb-textdomain' ), $args['password'] );
	?>
</p>

<p>
	<?php
	/* Tanslators 1: course url */
	printf(
		__(
			'To purchase and access more courses click here: <a href="%$1s">Courses</a>.',
			'eb-textdomain'
		),
		site_url( '/courses' )
	);
	?>
</p>

<?php
do_action( 'eb_email_footer' );
