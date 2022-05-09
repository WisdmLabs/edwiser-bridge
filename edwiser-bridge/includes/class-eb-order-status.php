<?php
/**
 * This class defines all code necessary to manage user's course orders meta'.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order status.
 */
class Eb_Order_Status {


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The current version of this plugin.
	 */
	private $version;

	/**
	 * Contsructor.
	 *
	 * @param text $plugin_name plugin_name.
	 * @param text $version version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Function initiates the refund it is ajax callback for the eb order refund refund.
	 *
	 * @since 1.3.0
	 */
	public function init_eb_order_refund() {
		check_ajax_referer( 'eb_order_refund_nons_field', 'order_nonce' );
		$order_id = isset( $_POST['eb_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_order_id'] ) ) : '';

		$refund_manager = new Eb_Manage_Order_Refund( $this->plugin_name, $this->version );
		$refund_data    = array(
			'amt'            => isset( $_POST['eb_ord_refund_amt'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_ord_refund_amt'] ) ) : '',
			'note'           => isset( $_POST['eb_order_refund_note'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_order_refund_note'] ) ) : '',
			'unenroll_users' => isset( $_POST['eb_order_meta_unenroll_user'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_order_meta_unenroll_user'] ) ) : 'NO',

		);
		$refund        = $refund_manager->init_refund( $order_id, $refund_data );
		$refund_status = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $refund, 'status', false );
		$refund_msg    = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $refund, 'msg', '' );
		if ( $refund_status ) {
			$refund_data['note'] = $refund_msg;
			$note                = $this->get_order_refund_status_msg( $order_id, $refund_data );
			$this->save_order_status_history( $order_id, $note );
			do_action( 'eb_order_refund_init_success', $order_id, $note );
			wp_send_json_success( $refund_msg );
		} else {
			wp_send_json_error( $refund_msg );
		}
	}

	/**
	 * Callback function to save the order status history data.
	 *
	 * @since 1.3.0
	 * @param int $order_id current updated order id.
	 */
	public function save_status_update_meta( $order_id ) {
		if ( ! current_user_can( 'edit_post', $order_id ) ) {
			return $order_id;
		}

		// Taking nonce field in a new.
		$nonce = isset( $_POST['eb_order_meta_nons'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_order_meta_nons'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'eb_order_history_meta_nons' ) ) {
			return $order_id;
		}
		$note = $this->get_status_update_note( $order_id, $_POST );
		$this->save_order_status_history( $order_id, $note );
	}

	/**
	 * New order place note.
	 *
	 * @since 1.3.0
	 * @param int $order_id current updated order id.
	 */
	public function save_new_order_place_note( $order_id ) {
		$ord_detail = get_post_meta( $order_id, 'eb_order_options', true );
		$course_id  = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $ord_detail, 'course_id' );
		$msg        = esc_html__( 'New order has been placed for the ', 'edwiser-bridge' ) . '<strong>' . sprintf( '%s', get_the_title( $course_id ) ) . '</strong>' . esc_html__( ' course.', 'edwiser-bridge' );
		$msg        = apply_filters( 'eb_order_history_save_status_new_order_msg', $msg );
		$note       = array(
			'type' => 'new_order',
			'msg'  => $msg,
		);
		$this->save_order_status_history( $order_id, $note );
	}

	/**
	 * Function provides the functionality to create the notes formated array
	 *
	 * @since 1.3.0
	 * @param int   $order_id current eb_order post id.
	 * @param array $data order update meta.
	 * @return array returns an array of the new status note
	 */
	private function get_status_update_note( $order_id, $data ) {
		$ord_detail = get_post_meta( $order_id, 'eb_order_options', true );
		$order_data = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $data, 'eb_order_options', false );
		$note       = array();
		if ( false !== $order_data ) {
			$old_status = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $ord_detail, 'order_status', false );
			$new_status = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $order_data, 'order_status', false );
			$msg        = array(
				'old_status' => $old_status,
				'new_status' => $new_status,
			);
			$msg        = apply_filters( 'eb_order_history_save_status_change_msg', $msg );
			$note       = array(
				'type' => 'status_update',
				'msg'  => $msg,
			);
		}
		return $note;
	}

	/**
	 * Provides the functionality to prepate the refund note data in the format of
	 * array(
	 * "refund_note"=>"",
	 * "refund_unenroll_users"=>"",
	 * )
	 *
	 * @since 1.3.0
	 * @param number $order_id current eb_order post id.
	 * @param array  $data order update meta.
	 * @return array returns an array of the refund status data
	 */
	private function get_order_refund_status_msg( $order_id, $data ) {
		$refund_amt = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $data, 'amt' );
		$msg        = array(
			'amt'                   => $refund_amt,
			'refund_note'           => \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $data, 'note' ),
			'refund_unenroll_users' => \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $data, 'unenroll_users', false ),
		);
		if ( 'ON' === \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $msg, 'refund_unenroll_users' ) ) {
			$this->unenroll_user_from_courses( $order_id );
		}
		$msg  = apply_filters( 'eb_order_history_save_refund_status_msg', $msg );
		$note = array(
			'type' => 'refund',
			'msg'  => $msg,
		);
		$this->save_order_refund_amt( $order_id, $refund_amt );
		return $note;
	}



	/**
	 * Save refund amount.
	 *
	 * @since 1.3.0
	 * @param number $order_id order id.
	 * @param array  $refund_amt refund_amt.
	 */
	private function save_order_refund_amt( $order_id, $refund_amt ) {
		$cur_user = wp_get_current_user();
		$refunds  = get_post_meta( $order_id, 'eb_order_refund_hist', true );
		$refund   = array(
			'amt'      => $refund_amt,
			'by'       => $cur_user->user_login,
			'time'     => current_time( 'Y-m-d' ),
			'currency' => \app\wisdmlabs\edwiserBridge\wdm_eb_get_current_paypal_currency_symb(),
		);
		if ( is_array( $refunds ) ) {
			$refunds[] = $refund;
		} else {
			$refunds = array( $refund );
		}
		update_post_meta( $order_id, 'eb_order_refund_hist', $refunds );
	}

	/**
	 * Function provides the functionality to edit the history data and add new
	 * at first position. and save the value into the database.
	 *
	 * @since 1.3.0
	 * @param int $order_id order_id.
	 * @param int $note note.
	 */
	private function save_order_status_history( $order_id, $note ) {
		$cur_user = wp_get_current_user();
		\app\wisdmlabs\edwiserBridge\wdm_eb_update_order_hist_meta( $order_id, $cur_user->user_login, $note );
	}

	/**
	 * Order id.
	 *
	 * @since 1.3.0
	 * @param int $order_id order_id.
	 */
	private function unenroll_user_from_courses( $order_id ) {
		$order_details   = get_post_meta( $order_id, 'eb_order_options', true );
		$course_id       = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $order_details, 'course_id', '' );
		$user_wp_id      = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $order_details, 'buyer_id', '' );
		$enrollment_mang = Eb_Enrollment_Manager::instance( $this->plugin_name, $this->version );
		$args            = array(
			'user_id' => $user_wp_id,
			'courses' => array( $course_id ),
			'suspend' => 1,
		);
		$resp            = $enrollment_mang->update_user_course_enrollment( $args );

		if ( $resp ) {
			$cur_user = wp_get_current_user();
			$note     = array(
				'type' => 'enrollment_susspend',
				'msg'  => __( 'User enrollment has been suspended on order refund request.', 'edwiser-bridge' ),
			);
			\app\wisdmlabs\edwiserBridge\wdm_eb_update_order_hist_meta( $order_id, $cur_user->user_login, $note );
		}
		return $resp;
	}
}
