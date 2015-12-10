<?php
/**
 *  PHP-PayPal-IPN Handler
 */

/*NOTE: the IPN call is asynchronous and can arrive later than the browser is redirected to the success url by paypal
	You cannot rely on setting up some details here and then using them in your success page.
	*/

// if ( !defined( 'IPN_ERROR_LOG' ) )
//  define( 'IPN_ERROR_LOG', 1 );

//create an object of logger class
EB()->logger()->add( 'payment', "\n"  );

EB()->logger()->add( 'payment', print_r( $_REQUEST, true ) );

EB()->logger()->add( 'payment', 'IPN Listener Loading...' );

include 'eb-ipnlistener.php';
$listener = new EB_IpnListener();

EB()->logger()->add( 'payment', 'IPN Listener Loaded' );

/*While testing your IPN script you should be using a PayPal "Sandbox" (get an account at: https://developer.paypal.com )
	When you are ready to go live change use_sandbox to false.*/

$payment_options = array();

//get payment options
$payment_options = get_option( 'eb_paypal' );

$paypal_email    = isset( $payment_options['eb_paypal_email'] )?$payment_options['eb_paypal_email']:'';
$paypal_currency = isset( $payment_options['eb_paypal_currency'] )?$payment_options['eb_paypal_currency']:'USD';
$paypal_country  = isset( $payment_options['eb_paypal_country'] )?$payment_options['eb_paypal_country']:'US';
$paypal_cancelurl= isset( $payment_options['eb_paypal_cancel_url'] )?$payment_options['eb_paypal_cancel_url']:site_url();
$paypal_returnurl= isset( $payment_options['eb_paypal_return_url'] )?$payment_options['eb_paypal_return_url']:site_url();
$paypal_notifyurl= isset( $payment_options['eb_paypal_notify_url'] )?$payment_options['eb_paypal_notify_url']:'';
$paypal_sandbox  = isset( $payment_options['eb_paypal_sandbox'] )?$payment_options['eb_paypal_sandbox']:'yes';

EB()->logger()->add( 'payment', 'Payment Settings Loaded.' );

$listener->use_sandbox = false;

if ( $paypal_sandbox == 'yes' ) {
	$listener->use_sandbox = true;
	EB()->logger()->add( 'payment', 'Sandbox Enabled.' );
}

try {
	EB()->logger()->add( 'payment', 'Checking Post Method.' );
	$listener->requirePostMethod();
	$verified = $listener->processIpn();
	EB()->logger()->add( 'payment', 'Post method check completed.' );
} catch ( Exception $e ) {

	EB()->logger()->add( 'payment', 'Found Exception: ' .$e->getMessage(). ' Exiting....' );
	exit( 0 );
}

$YOUR_NOTIFICATION_EMAIL_ADDRESS = get_option( 'admin_email' );
$seller_email = $paypal_email;

EB()->logger()->add( 'payment', 'Loaded Email IDs. Notification Email: '. $YOUR_NOTIFICATION_EMAIL_ADDRESS .' Seller Email: '. $seller_email );
$notify_on_valid_ipn = 1;

EB()->logger()->add( 'payment', 'Payment Verified? : '.( ( $verified )? "YES":"NO" ) );
/*The processIpn() method returned true if the IPN was "VERIFIED" and false if it was "INVALID".*/

if ( $verified ) {

	EB()->logger()->add( 'payment', 'Sure, Verfied! Moving Ahead.' );
	/*	Once you have a verified IPN you need to do a few more checks on the POST
		fields--typically against data you stored in your database during when the
		end user made a purchase (such as in the "success" page on a web payments
		standard button). The fields PayPal recommends checking are:
			1. Check the $_POST['payment_status'] is "Completed"
			2. Check that $_POST['txn_id'] has not been previously processed
			3. Check that $_POST['receiver_email'] is get_option('EVI_Paypal_Seller_email')
			4. Check that $_POST['payment_amount'] and $_POST['payment_currency']
				are correct
		*/

	//note: This is just notification for us. Paypal has already made up its mind and the payment has been processed
	//  (you can't cancel that here)
	EB()->logger()->add( 'payment', 'Receiver Email: '.$_POST['receiver_email'].' Valid Receiver Email? :'.( ( $_POST['receiver_email'] == $seller_email )? "YES":"NO" ) );

	if ( $_POST['receiver_email'] != $seller_email ) {
		if ( $YOUR_NOTIFICATION_EMAIL_ADDRESS !='' ) {
			wp_mail( $YOUR_NOTIFICATION_EMAIL_ADDRESS, 'Warning: IPN with invalid receiver email!', $listener->getTextReport() );
			EB()->logger()->add( 'payment', 'Warning! IPN with invalid receiver email!' );
		} else {
			EB()->logger()->add( 'payment', 'Warning! notification email not set' );
		}
	}

	EB()->logger()->add( 'payment', 'Payment Status: '.$_POST['payment_status'].' Completed? :'.( ( $_POST['payment_status'] == "Completed" )? "YES":"NO" ) );
	if ( $_POST['payment_status'] == "Completed" ) {

		EB()->logger()->add( 'payment', 'Sure, Completed! Moving Ahead.' );
		//a customer has purchased from this website

		// email used by buyer to purchase course
		$billing_email = $_REQUEST['payer_email'];
		EB()->logger()->add( 'payment', 'Billing Email: '.$billing_email );

		//id of course passed by PayPal
		$course_id = $_REQUEST['item_number'];

		EB()->logger()->add( 'payment', 'Checking if payment amount is correct and was not modified.' );

		//verify course price
		$course_price = EB_Post_Types::get_post_options( $course_id, 'course_price', 'eb_course' );

		if ( $_REQUEST['mc_gross'] == $course_price ) {
			EB()->logger()->add( 'payment', 'Course price is varified. Let\'s continue...'  );
		} else {
			EB()->logger()->add( 'payment', 'WARNING ! Course price is modified by the purchaser, course access not given. Exiting!!!' );
			exit( 0 );
		}

		if ( $_REQUEST['mc_currency'] != $paypal_currency ) {
			EB()->logger()->add( 'payment', 'WARNING ! Paypal currency is modified by the purchaser, course access not given. Exiting!!!' );
			exit( 0 );
		}

		//verify user id & order id
		if ( !empty( $_REQUEST['custom'] ) ) {

			EB()->logger()->add( 'payment', $_REQUEST['custom'] );

			// decode json data
			$custom_data = json_decode( stripslashes( $_REQUEST['custom'] ) );
			EB()->logger()->add( 'payment', print_r( $custom_data , 1 ) );
			$buyer_id = isset( $custom_data->buyer_id )?$custom_data->buyer_id:'';
			$order_id = isset( $custom_data->order_id )?$custom_data->order_id:'';

			EB()->logger()->add( 'payment', 'Buyer ID: '.$buyer_id.' - Order ID: '.$order_id );

			if ( empty( $buyer_id ) || empty( $order_id ) ) {
				EB()->logger()->add( 'payment', 'WARNING ! Buyer ID or Order ID is missing. Exiting!!!' );
				exit( 0 );
			}

			$buyer = get_user_by( "id", $buyer_id );
			// EB()->logger()->add( 'payment', print_r( $buyer, 1 ) );
			// exit if no user found with this id
			if ( !$buyer ) {
				EB()->logger()->add( 'payment', 'User ID ['.$buyer_id.'] passed back by Paypal. But no user with this ID is found. Exiting!!! ' );
				exit( 0 );
			}

			EB()->logger()->add( 'payment', 'User ID ['.$buyer_id.'] passed back by Paypal. Checking if user exists. User Found: '.( !empty( $buyer->ID )? "Yes":"No" ) );
		} else {
			EB()->logger()->add( 'payment', "WARNING! Custom data (order id & buyer id) not recieved. Exiting!!!" );
			exit( 0 );
		}

		//verify order
		//get order details
		$order_buyer_id = EB_Post_Types::get_post_options( $order_id, 'buyer_id', 'eb_order' );
		if ( $buyer_id != $order_buyer_id ) {
			EB()->logger()->add( 'payment', 'Buyer ID ['.$buyer_id.'] passed back by Paypal. But actual order has a different buyer id in DB. Actual Buyer ID:'. $order_buyer_id .' Exiting!!!' );
			exit( 0 );
		}

		$order_course_id = EB_Post_Types::get_post_options( $order_id, 'course_id', 'eb_order' );
		if ( $course_id != $order_course_id ) {
			EB()->logger()->add( 'payment', 'Item ID ['.$course_id.'] passed back by Paypal. But actual order has a different item id in DB. Actual Item ID:'. $order_course_id .' Exiting!!!' );
			exit( 0 );
		}

		// // record in course
		// EB()->logger()->add( 'payment', 'Starting to give course access...' );

		// $course_enrolled = EB()->enrollment_manager()->update_user_course_enrollment( $buyer_id, array( $course_id ) );
		// if ( $course_enrolled )
		//  EB()->logger()->add( 'payment', 'Course enrolled to the user: '.$buyer_id );
		// else
		//  EB()->logger()->add( 'payment', 'Error in course enrollment: '.$buyer_id );

		// log transaction
		EB()->logger()->add( 'payment', 'Starting Order Status Updation.' );

		//update billing email in order meta
		$order_options = get_post_meta( $order_id, 'eb_order_options', true );
		$order_options['billing_email'] = $billing_email;
		$order_options['amount_paid'] 	= $course_price;
		update_post_meta( $order_id, 'eb_order_options', $order_options );

		$order_completed = EB()->order_manager()->update_order_status( $order_id, 'completed' );

		if ( $order_completed ) {
			EB()->logger()->add( 'payment', 'Order status set to Complete: '.$order_id );
		}
	}

	EB()->logger()->add( 'payment', 'IPN Processing Completed Successfully.' );
	$notifyOnValid = $notify_on_valid_ipn != '' ? $notify_on_valid_ipn : '0';
	if ( $notifyOnValid == '1' ) {
		wp_mail( $YOUR_NOTIFICATION_EMAIL_ADDRESS, 'Verified IPN', $listener->getTextReport() );
	}
} else {
	/*An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's a good idea to have a developer or sys admin
		manually investigate any invalid IPN.*/
	EB()->logger()->add( 'payment', 'Invalid IPN. Shutting Down Processing.' );
	wp_mail( $YOUR_NOTIFICATION_EMAIL_ADDRESS, 'Invalid IPN', $listener->getTextReport() );
}

//we're done here
