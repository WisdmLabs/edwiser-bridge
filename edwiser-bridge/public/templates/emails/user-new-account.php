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
	/* Tanslators 1: blog_name 2: Username */
	printf(
		esc_html__(
			'Thanks for creating an account on %$1s. Your username is <strong>%$2s</strong>.',
			'edwiser-bridge'
		),
		esc_html( get_bloginfo( 'name' ) ),
		esc_html( $args['username'] )
	);
	?>
</p>

<p>
	<?php
	/* Tanslators 1: password */
	printf(
		esc_html__(
			'Your password has been automatically generated: <strong>%$1s</strong>',
			'edwiser-bridge'
		),
		esc_html( $args['password'] )
	);
	?>
</p>

<p>
	<?php
	/* Tanslators 1: user acount url */
	printf(
		esc_html__(
			'You can access your account here: <a href="%$1s">User Account</a>.',
			'edwiser-bridge'
		),
		esc_html( \app\wisdmlabs\edwiserBridge\wdm_eb_user_account_url() )
	);
	?>
</p>

<?php
do_action( 'eb_email_footer' );
