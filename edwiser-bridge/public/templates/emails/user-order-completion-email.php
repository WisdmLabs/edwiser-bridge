<?php
/**
 * Order Completion Email Template.
 *
 * @package Edwiser Bridge.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<?php do_action( 'eb_email_header', $args['header'] ); ?>

<p>
	<?php
		/* Tanslators 1: First name */
		printf( esc_html__( 'Hi %$1s', 'eb-textdomain' ), esc_html( $args['first_name'] ) );
	?>
</p>

<p>
	<?php
	/* Tanslators 1: course_id */
	printf(
		esc_html__(
			'Thanks for purchasing %$1s course.',
			'eb-textdomain'
		),
		'<strong>' . esc_html( get_the_title( $args['course_id'] ) ) . '</strong>'
	);
	?>
</p>

<p>
	<?php
		/* Tanslators 1: eb_order_id */
		printf( esc_html__( 'Your order with ID #%$1s completed successfully.', 'eb-textdomain' ), esc_html( $args['eb_order_id'] ) );   // cahnges 1.4.7.
	?>
</p>

<p>
	<?php
	/* Tanslators 1: user acount url */
	printf(
		esc_html__(
			'You can access your account here: <a href="%$1s">User Account</a>.',
			'eb-textdomain'
		),
		esc_html( \app\wisdmlabs\edwiserBridge\wdm_eb_user_account_url() )
	);
	?>
</p>

<?php
do_action( 'eb_email_footer' );
