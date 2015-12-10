<?php
/**
 * Order Completion Email Template
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public/templates/emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php do_action( 'eb_email_header', $args['header'] ); ?>

<p><?php printf( __("Hi %s", 'eb-textdomain'), $args['first_name'] ); ?></p>

<p><?php printf( __( "Thanks for purchasing %s course. ", 'eb-textdomain' ), '<strong>'. get_the_title($args['course_id']).'</strong>' ); ?></p>

<p><?php printf( __( 'Your order with ID #%s completed successfully.', 'eb-textdomain' ), $args['order_id'] ); ?></p>

<p><?php printf( __( 'You can access your account here: <a href="%s">User Account</a>.', 'eb-textdomain' ), wdm_user_account_url() ); ?></p>

<?php do_action( 'eb_email_footer' ); ?>
