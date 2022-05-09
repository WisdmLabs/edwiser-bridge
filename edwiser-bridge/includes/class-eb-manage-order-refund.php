<?php
/**
 * Allows log files to be written to for debugging purposes.
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
 * Manage refund.
 */
class Eb_Manage_Order_Refund {
	/**
	 * Plugin name.
	 *
	 * @since    1.0.0
	 *
	 * @var string plugin_name name.
	 */
	private $plugin_name;

	/**
	 * Version name.
	 *
	 * @since    1.0.0
	 *
	 * @var string version name.
	 */
	private $version;

	/**
	 * COntrsuctor.
	 *
	 * @param int $plugin_name plugin_name.
	 * @param int $version version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * INIt refund.
	 *
	 * @param int $order_id order_id.
	 * @param int $refund_data refund_data.
	 */
	public function init_refund( $order_id, $refund_data ) {
		$amt           = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $refund_data, 'amt' );
		$note          = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $refund_data, 'note' );
		$refund_status = array(
			'amt'    => $amt,
			'status' => false,
			'msg'    => esc_html__( 'Failed to initiate refund for order #', 'edwiser-bridge' ) . $order_id,
		);
		$refund_type   = $this->get_refund_type( $order_id, $amt );
		edwiser_bridge_instance()->logger()->add( 'refund', "Initaiting $refund_type refund for order ID: ['$order_id'], Refund amount: $amt and refund note: $note" );
		$refund_status = apply_filters( 'eb_order_refund_init', $refund_status, $order_id, $refund_type, $amt, $note );

		if ( isset( $refund_status['status'] ) && $refund_status['status'] ) {
			$order        = new Eb_Order_Meta( $this->plugin_name, $this->version );
			$order_data   = get_post_meta( $order_id, 'eb_order_options', true );
			$refunds      = $order->get_orders_all_refund( $order_id );
			$paid_amt     = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $order_data, 'amount_paid' );
			$total_refund = \app\wisdmlabs\edwiserBridge\wdm_eb_get_total_refund_amt( $refunds );

			if ( $paid_amt <= $total_refund + $amt ) {
				edwiser_bridge_instance()->order_manager()->update_order_status( $order_id, 'refunded' );
			}
		}

		return $refund_status;
	}

	/**
	 * Refund type.
	 *
	 * @param int $order_id order_id.
	 * @param int $amt amt.
	 */
	private function get_refund_type( $order_id, $amt ) {
		$type       = 'Partial';
		$order_data = get_post_meta( $order_id, 'eb_order_options', true );
		$order      = new Eb_Order_Meta( $this->plugin_name, $this->version );
		$refunds    = $order->get_orders_all_refund( $order_id );
		$paid_amt   = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $order_data, 'amount_paid' );

		if ( empty( $refunds ) && $amt === $paid_amt ) {
			$type = 'Full';
		}

		return $type;
	}
}
