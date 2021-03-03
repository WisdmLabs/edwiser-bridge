<?php
/**
 *  PHP-PayPal-IPN Handler.
 *
 * @package Edwiser bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// NOTE: the IPN call is asynchronous and can arrive later than the browser is redirected to the success url by paypal
// You cannot rely on setting up some details here and then using them in your success page.

// Note: Not checking nonce since the request is comming from paypal.
$request_data = $_REQUEST; // WPCS: CSRF ok, input var ok.
$post_data    = $_POST; // WPCS: CSRF ok, input var ok.


// create an object of logger class.
edwiser_bridge_instance()->logger()->add( 'payment', "\n" );

edwiser_bridge_instance()->logger()->add( 'payment', wp_json_encode( $request_data ) );

edwiser_bridge_instance()->logger()->add( 'payment', 'IPN Listener Loading...' );

require 'class-eb-ipn-listener.php';
$listener = new Eb_Ipn_Listener();

edwiser_bridge_instance()->logger()->add( 'payment', 'IPN Listener Loaded' );

// While testing your IPN script you should be using a PayPal "Sandbox".
// (get an account at: https://developer.paypal.com ) When you are ready to go live.
// change use_sandbox to false.

$payment_options = array();

// get payment options.
$payment_options  = get_option( 'eb_paypal' );
$paypal_email     = isset( $payment_options['eb_paypal_email'] ) ? $payment_options['eb_paypal_email'] : '';
$paypal_currency  = isset( $payment_options['eb_paypal_currency'] ) ? $payment_options['eb_paypal_currency'] : 'USD';
$paypal_country   = isset( $payment_options['eb_paypal_country_code'] ) ? $payment_options['eb_paypal_country_code'] : 'US';
$paypal_cancelurl = isset( $payment_options['eb_paypal_cancel_url'] ) ? $payment_options['eb_paypal_cancel_url'] : site_url();
$paypal_returnurl = isset( $payment_options['eb_paypal_return_url'] ) ? $payment_options['eb_paypal_return_url'] : site_url();
$paypal_notifyurl = isset( $payment_options['eb_paypal_notify_url'] ) ? $payment_options['eb_paypal_notify_url'] : '';
$paypal_sandbox   = isset( $payment_options['eb_paypal_sandbox'] ) ? $payment_options['eb_paypal_sandbox'] : 'yes';

edwiser_bridge_instance()->logger()->add( 'payment', 'Payment Settings Loaded.' );

$listener->use_sandbox = false;

if ( 'yes' === $paypal_sandbox ) {
	$listener->use_sandbox = true;
	edwiser_bridge_instance()->logger()->add( 'payment', 'Sandbox Enabled.' );
}
// Initialize the verifcation by defualt to false.
$verified = false;

/**
 * Start the responce verification.
 */
try {
	edwiser_bridge_instance()->logger()->add( 'payment', 'Checking Post Method.' );

	$listener->require_post_method();

	$verified = $listener->process_ipn( $post_data );

	edwiser_bridge_instance()->logger()->add( 'payment', 'Post method check completed.' );
} catch ( \Exception $e ) {

	edwiser_bridge_instance()->logger()->add( 'payment', 'Found Exception: ' . $e->getMessage() . ' Exiting....' );
	exit( 0 );
}

$your_notification_email_address = get_option( 'admin_email' );
$seller_email                    = $paypal_email;

edwiser_bridge_instance()->logger()->add(
	'payment',
	'Loaded Email IDs. Notification Email: ' . $your_notification_email_address . '
	Seller Email: ' . $seller_email
);
$notify_on_valid_ipn = 1;

edwiser_bridge_instance()->logger()->add( 'payment', 'Payment Verified? : ' . ( ( $verified ) ? 'YES' : 'NO' ) );
/* The process_ipn() method returned true if the IPN was "VERIFIED" and false if it was "INVALID". */

/**
* The process_ipn() method returned true if the IPN was "VERIFIED" and false if it was "INVALID".
*/
if ( $verified ) {
	edwiser_bridge_instance()->logger()->add( 'payment', 'Sure, Verfied! Moving Ahead.' );
	/** Once you have a verified IPN you need to do a few more checks on the POST
	*  fields--typically against data you stored in your database during when the
	*  end user made a purchase (such as in the "success" page on a web payments
	*  standard button). The fields PayPal recommends checking are:
	*  1. Check the $_POST['payment_status'] is "Completed"
	*  2. Check that $_POST['txn_id'] has not been previously processed
	*  3. Check that $_POST['receiver_email'] is get_option('EVI_Paypal_Seller_email')
	*  4. Check that $_POST['payment_amount'] and $_POST['payment_currency']
	*  are correct
	*/

	// note: This is just notification for us. Paypal has already made up its mind and the payment has been processed.
	// You can't cancel that here.
	$post_receiver_email = isset( $post_data['receiver_email'] ) ? sanitize_text_field( wp_unslash( $post_data['receiver_email'] ) ) : '';

	edwiser_bridge_instance()->logger()->add( 'payment', 'Receiver Email: ' . $post_receiver_email . 'Valid Receiver Email? :' . ( ( $post_receiver_email === $seller_email ) ? 'YES' : 'NO' ) );

	if ( $post_receiver_email !== $seller_email ) {
		if ( ! empty( $your_notification_email_address ) ) {
			wp_mail(
				$your_notification_email_address,
				'Warning: IPN with invalid receiver email!',
				$listener->get_text_report()
			);
			edwiser_bridge_instance()->logger()->add( 'payment', 'Warning! IPN with invalid receiver email!' );
		} else {
			edwiser_bridge_instance()->logger()->add( 'payment', 'Warning! notification email not set' );
		}
	}

	$post_payment_status = isset( $post_data['payment_status'] ) ? sanitize_text_field( wp_unslash( $post_data['payment_status'] ) ) : '';

	edwiser_bridge_instance()->logger()->add(
		'payment',
		'Payment Status: ' . $post_payment_status . ' Completed? :' . ( ( 'Completed' === $post_payment_status ) ? 'YES' : 'NO' )
	);

	$custom_data = isset( $request_data['custom'] ) ? json_decode( sanitize_text_field( wp_unslash( $request_data['custom'] ) ) ) : '';

	if ( 'Completed' === $post_payment_status ) {

		edwiser_bridge_instance()->logger()->add( 'payment', 'Sure, Completed! Moving Ahead.' );
		// a customer has purchased from this website.
		// email used by buyer to purchase course.
		$billing_email = isset( $request_data['payer_email'] ) ? sanitize_text_field( wp_unslash( $request_data['payer_email'] ) ) : '';

		edwiser_bridge_instance()->logger()->add( 'payment', 'Billing Email: ' . $billing_email );

		// id of course passed by PayPal.
		$course_id = isset( $request_data['item_number'] ) ? sanitize_text_field( wp_unslash( $request_data['item_number'] ) ) : '';

		edwiser_bridge_instance()->logger()->add( 'payment', 'Checking if payment amount is correct and was not modified.' );

		// verify course price.
		$course_price = Eb_Post_Types::get_post_options( $course_id, 'course_price', 'eb_course' );

		$post_mc_gross = isset( $request_data['mc_gross'] ) ? sanitize_text_field( wp_unslash( $request_data['mc_gross'] ) ) : '';

		if ( round( trim( $post_mc_gross ) ) === round( trim( $course_price ) ) ) {
			edwiser_bridge_instance()->logger()->add( 'payment', 'Course price is varified. Let\'s continue...' );
		} else {
			edwiser_bridge_instance()->logger()->add(
				'payment',
				'WARNING ! Course price is modified by the purchaser, course access not given. Exiting!!!'
			);
			exit( 0 );
		}

		$post_mc_currency = isset( $request_data['mc_currency'] ) ? sanitize_text_field( wp_unslash( $request_data['mc_currency'] ) ) : '';

		if ( $post_mc_currency !== $paypal_currency ) {
			edwiser_bridge_instance()->logger()->add(
				'payment',
				'WARNING ! Paypal currency is modified by the purchaser, course access not given. Exiting!!!'
			);
			exit( 0 );
		}
		// verify user id & order id.
		if ( ! empty( $custom_data ) ) {

			edwiser_bridge_instance()->logger()->add( 'payment', sanitize_text_field( wp_unslash( $request_data['custom'] ) ) );

			// decode json data.
			edwiser_bridge_instance()->logger()->add( 'payment', wp_json_encode( $custom_data ) );
			$buyer_id = isset( $custom_data->buyer_id ) ? $custom_data->buyer_id : '';
			$order_id = isset( $custom_data->order_id ) ? $custom_data->order_id : '';

			edwiser_bridge_instance()->logger()->add( 'payment', 'Buyer ID: ' . $buyer_id . ' - Order ID: ' . $order_id );

			if ( empty( $buyer_id ) || empty( $order_id ) ) {
				edwiser_bridge_instance()->logger()->add( 'payment', 'WARNING ! Buyer ID or Order ID is missing. Exiting!!!' );
				exit( 0 );
			}

			$buyer = get_user_by( 'id', $buyer_id );
			// exit if no user found with this id.
			if ( ! $buyer ) {
				edwiser_bridge_instance()->logger()->add(
					'payment',
					'User ID [' . $buyer_id . '] passed back by Paypal. But no user with this ID is found. Exiting!!! '
				);
				exit( 0 );
			}

			edwiser_bridge_instance()->logger()->add(
				'payment',
				'User ID [' . $buyer_id . '] passed back by Paypal. Checking if user exists.
				User Found: ' . ( ! empty( $buyer->ID ) ? 'Yes' : 'No' )
			);
		} else {
			edwiser_bridge_instance()->logger()->add( 'payment', 'WARNING! Custom data (order id & buyer id) not recieved. Exiting!!!' );
			exit( 0 );
		}

		// verify order.
		// get order details.
		$order_buyer_id = Eb_Post_Types::get_post_options( $order_id, 'buyer_id', 'eb_order' );
		if ( trim( $buyer_id ) !== trim( $order_buyer_id ) ) {
			edwiser_bridge_instance()->logger()->add(
				'payment',
				'Buyer ID [' . $buyer_id . '] passed back by Paypal. But actual order has a different buyer id in DB.
				Actual Buyer ID:' . $order_buyer_id . ' Exiting!!!'
			);
			exit( 0 );
		}

		$order_course_id = Eb_Post_Types::get_post_options( $order_id, 'course_id', 'eb_order' );
		if ( trim( $course_id ) !== trim( $order_course_id ) ) {
			edwiser_bridge_instance()->logger()->add(
				'payment',
				'Item ID [' . $course_id . '] passed back by Paypal. But actual order has a different item id in DB.
				Actual Item ID:' . $order_course_id . ' Exiting!!!'
			);
			exit( 0 );
		}

		// // record in course.
		// log transaction.
		edwiser_bridge_instance()->logger()->add( 'payment', 'Starting Order Status Updation.' );

		// update billing email in order meta.
		$order_options                  = get_post_meta( $order_id, 'eb_order_options', true );
		$order_options['billing_email'] = $billing_email;
		$order_options['amount_paid']   = $course_price;
		update_post_meta( $order_id, 'eb_order_options', $order_options );

		// since 1.2.4.
		$post_txn_id = isset( $request_data['txn_id'] ) ? sanitize_text_field( wp_unslash( $request_data['txn_id'] ) ) : '';

		if ( $post_txn_id ) {
			update_post_meta( $order_id, 'eb_transaction_id', $post_txn_id );
		}

		$order_completed = edwiser_bridge_instance()->order_manager()->update_order_status( $order_id, 'completed' );

		if ( $order_completed ) {
			edwiser_bridge_instance()->logger()->add( 'payment', 'Order status set to Complete: ' . $order_id );
			$note = array(
				'type' => 'PayPal IPN',
				'msg'  => esc_html__( 'IPN has been recived for the order id #', 'eb-textdomain' ) . $order_id . esc_html__( 'payment status: ', 'eb-textdomain' ) . $post_payment_status . esc_html__( ' Transaction id: ', 'eb-textdomain' ) . $post_txn_id . '. ',
			);
			\app\wisdmlabs\edwiserBridge\wdm_eb_update_order_hist_meta( $order_id, esc_html__( 'Paypal IPN', 'eb-textdomain' ), $note );
		}
	} elseif ( 'Refunded' === $post_payment_status ) {
		$post_mc_gross = isset( $request_data['mc_gross'] ) ? sanitize_text_field( wp_unslash( $request_data['mc_gross'] ) ) : '';

		$post_txn_id = isset( $request_data['txn_id'] ) ? sanitize_text_field( wp_unslash( $request_data['txn_id'] ) ) : '';
		edwiser_bridge_instance()->logger()->add( 'refund', wp_json_encode( $custom_data ) );
		$order_id = isset( $custom_data->order_id ) ? $custom_data->order_id : '';
		$note     = array(
			'type' => 'PayPal IPN',
			'msg'  => esc_html__( 'IPN has been recived, for the refund of amount ', 'eb-textdomain' ) . abs( $post_mc_gross ) . esc_html__( '. Payment status: ', 'eb-textdomain' ) . $post_payment_status . esc_html__( ' Transaction id: ', 'eb-textdomain' ) . $post_txn_id . '.',
		);
		\app\wisdmlabs\edwiserBridge\wdm_eb_update_order_hist_meta( $order_id, esc_html__( 'Paypal IPN', 'eb-textdomain' ), $note );

		$args = array(
			'eb_order_id'     => $custom_data->order_id, // changed 1.4.7.
			'buyer_id'        => $custom_data->buyer_id,
			'refunded_cur'    => empty( $post_mc_currency ) ? 'USD' : $post_mc_currency,
			'refund_amount'   => abs( empty( $post_mc_gross ) ? '0.00' : $post_mc_gross ),
			'refunded_status' => empty( $post_payment_status ) ? 'Unknown' : $post_payment_status,
		);

		do_action( 'eb_refund_completion', $args );
	}

	edwiser_bridge_instance()->logger()->add( 'payment', 'IPN Processing Completed Successfully.' );
	$notify_on_valid = ! empty( $notify_on_valid_ipn ) ? $notify_on_valid_ipn : '0';
	if ( trim( '1' ) === trim( $notify_on_valid ) ) {
		wp_mail( $your_notification_email_address, 'Verified IPN', $listener->get_text_report() );
	}
} else {
	// An Invalid IPN *may* be caused by a fraudulent transaction attempt.
	// It's a good idea to have a developer or sys admin
	// manually investigate any invalid IPN.
	edwiser_bridge_instance()->logger()->add( 'payment', 'Invalid IPN. Shutting Down Processing.' );
	wp_mail( $your_notification_email_address, 'Invalid IPN', $listener->get_text_report() );
}
