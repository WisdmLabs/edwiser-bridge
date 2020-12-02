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
	/* Tanslators 1: blog_name 2: Username */
	printf(
		__(
			'Thanks for creating an account on %$1s. Your username is <strong>%$2s</strong>.',
			'eb-textdomain'
		),
		get_bloginfo( 'name' ),
		$args['username']
	);
	?>
</p>

<p>
	<?php
	/* Tanslators 1: password */
	printf(
		__(
			'Your password has been automatically generated: <strong>%$1s</strong>',
			'eb-textdomain'
		),
		$args['password']
	);
	?>
</p>

<p>
	<?php
	/* Tanslators 1: user acount url */
	printf(
		__(
			'You can access your account here: <a href="%$1s">User Account</a>.',
			'eb-textdomain'
		),
		wdm_user_account_url()
	);
	?>
</p>

<?php
do_action( 'eb_email_footer' );
