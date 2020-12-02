<?php
/**
 * Order Completion Email Template.
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

?>

<?php do_action( 'eb_email_header', $args['header'] ); ?>

<p>
	<?php
		/* Tanslators 1: First name */
		printf( __( 'Hi %$1s', 'eb-textdomain' ), $args['first_name'] );
	?>
</p>

<p>
	<?php
	/* Tanslators 1: course_id */
	printf(
		__(
			'Thanks for purchasing %$1s course.',
			'eb-textdomain'
		),
		'<strong>' . get_the_title( $args['course_id'] ) . '</strong>'
	);
	?>
</p>

<p>
	<?php
		/* Tanslators 1: eb_order_id */
		printf( __( 'Your order with ID #%$1s completed successfully.', 'eb-textdomain' ), $args['eb_order_id'] );   // cahnges 1.4.7
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
