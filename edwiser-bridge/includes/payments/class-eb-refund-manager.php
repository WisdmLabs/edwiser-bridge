<?php
/**
 * This class defines all code necessary to manage paypal refund feature.
 *
 * @link       https://edwiser.org
 * @since      1.2.1
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Eb paypal refund manager.
 */
class Eb_Refund_Manager {
	/**
	 * Plugin name.
	 *
	 * @since    1.0.0
	 *
	 * @var string plugin name.
	 */
	private $plugin_name = null;

	/**
	 * Plugin version.
	 *
	 * @since    1.0.0
	 *
	 * @var string plugin version.
	 */
	private $version = null;

	/**
	 * Constructor.
	 *
	 * @param  string $plugin_name plugin_name.
	 * @param  string $version version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Refund an order via PayPal.
	 *
	 * @param  text   $status   Eb order $order.
	 * @param  float  $order_id  $amount amount.
	 * @param  string $refund_type amount.
	 * @param  string $amount amount.
	 * @param  string $reason reason.
	 * @return object Either an object of name value pairs for a success, or a WP_ERROR object.
	 */
	public function refund( $status, $order_id, $refund_type = 'Full', $amount = null, $reason = '' ) {
		$success     = 1;
		$sandbox     = get_post_meta( $order_id, 'eb_paypal_sandbox', 1 );
		$pay_pal_url = 'https://api-3t.paypal.com/nvp';
		if ( isset( $sandbox ) && ! empty( $sandbox ) && 'yes' === $sandbox ) {
			$pay_pal_url = 'https://api-3t.sandbox.paypal.com/nvp';
		}
		$request_data = $this->get_refund_request_data( $order_id, $refund_type, $amount, $reason );
		if ( $request_data['status'] ) {
			$req_args = array(
				'method'      => 'POST',
				'body'        => $request_data['data'],
				'timeout'     => 500,
				'httpversion' => '1.1',
				'headers'     => array( 'content-type' => 'application/json' ),
			);
			edwiser_bridge_instance()->logger()->add( 'refund', "Order: $order_id ,Sending Refund request to PayPal. Request data is : " . serialize( $req_args ) ); // @codingStandardsIgnoreLine
			$response = wp_safe_remote_post( $pay_pal_url, $req_args );
			try {
				if ( is_wp_error( $response ) ) {
					$success       = 0;
					$status['msg'] = $response;
				} elseif ( empty( $response['body'] ) ) {
					$success       = 0;
					$status['msg'] = esc_html__( 'No Response from PayPal', 'edwiser-bridge' );
				}
				parse_str( $response['body'], $response );
				edwiser_bridge_instance()->logger()->add( 'refund', 'PayPal refund responce: ' . serialize( $response ) ); // @codingStandardsIgnoreLine
			} catch ( Exception $ex ) {
				edwiser_bridge_instance()->logger()->add( 'refund', "Order: $order_id ,Exception: " . serialize( $ex ) ); // @codingStandardsIgnoreLine
			}
			$resp_status = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $response, 'ACK', false );
			if ( 'Success' === $resp_status ) {
				$status['msg'] = esc_html__( 'Refund for amount', 'edwiser-bridge' ) . sprintf( ' %s against the order #%s has been initiated successfully. Transaction id: %s', \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $response, 'GROSSREFUNDAMT' ), $order_id, \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $response, 'REFUNDTRANSACTIONID' ) );
			} elseif ( 'Failure' === $resp_status ) {
				$success       = 0;
				$status['msg'] = '<strong>' . esc_html__( 'PayPal Responce: ', 'edwiser-bridge' ) . '</strong>' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $response, 'L_LONGMESSAGE0', '' );
			}
		} else {
			$success       = 0;
			$status['msg'] = $request_data['data'];
		}
		$status['status'] = $success;
		return $status;
	}

	/**
	 * Get refund request args.
	 *
	 * @param  int    $order_id order_id.
	 * @param  float  $refund_type refund_type.
	 * @param  string $amount amount.
	 * @param  string $reason reason.
	 * @return array
	 */
	private function get_refund_request_data( $order_id, $refund_type, $amount = null, $reason = '' ) {
		$txn_id   = $this->get_transaction_id( $order_id );
		$sucess   = 1;
		$req_data = array();
		if ( ! $txn_id ) {
			$sucess           = 0;
			$req_data['data'] = esc_html__( 'Sorry, can not process this request as this is invalid transaction.', 'edwiser-bridge' );
		}

		$api_details = $this->get_paypal_api_details();

		if ( ! $api_details ) {
			$sucess           = 0;
			$req_data['data'] = esc_html__( 'Please update Paypal API details on edwiser paypal settings page.', 'edwiser-bridge' );
		}

		if ( $sucess ) {
			$data = array(
				'VERSION'       => '84.0',
				'SIGNATURE'     => $api_details['sign'],
				'USER'          => $api_details['username'],
				'PWD'           => $api_details['pwd'],
				'METHOD'        => 'RefundTransaction',
				'TRANSACTIONID' => $txn_id,
				'NOTE'          => $reason,
				'REFUNDTYPE'    => $refund_type,
			);
			if ( ! is_null( $amount ) ) {
				$data['AMT']          = $this->eb_number_format( $amount, $order_id );
				$data['CURRENCYCODE'] = $this->get_currency_code( $order_id );
			}
			$req_data['data'] = $data;
		}
		$req_data['status'] = $sucess;
		return $req_data;
	}

	/**
	 * Paypal API details.
	 */
	private function get_paypal_api_details() {
		$api_details  = get_option( 'eb_paypal' );
		$pay_pal_data = array(
			'username' => \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $api_details, 'eb_api_username', '' ),
			'pwd'      => \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $api_details, 'eb_api_password', '' ),
			'sign'     => \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $api_details, 'eb_api_signature', '' ),
		);
		return $pay_pal_data;
	}

	/**
	 * Get transaction ID.
	 *
	 * @param int $order_id order id.
	 */
	private function get_transaction_id( $order_id ) {
		$txn_id = get_post_meta( $order_id, 'eb_transaction_id', true );
		if ( $txn_id && ! empty( $txn_id ) ) {
			return $txn_id;
		}
		return 0;
	}

	/**
	 * Get corrency code ID.
	 *
	 * @param int $order_id order id.
	 */
	private function get_currency_code( $order_id ) {
		$currency_code = get_post_meta( $order_id, 'eb_paypal_currency', 1 );
		if ( ! $currency_code && empty( $currency_code ) ) {
			$default_options = array(
				'eb_paypal_email'        => '',
				'eb_paypal_currency'     => '',
				'eb_paypal_country_code' => '',
				'eb_paypal_cancel_url'   => '',
				'eb_paypal_return_url'   => '',
				'eb_paypal_notify_url'   => '',
				'eb_paypal_sandbox'      => '',
				'eb_api_username'        => '',
				'eb_api_password'        => '',
				'eb_api_signature'       => '',
			);
			$option          = unserialize( get_option( 'eb_paypal' ), $default_options ); // @codingStandardsIgnoreLine
			return $option['eb_paypal_currency'];
		}
		return $currency_code;
	}

	/**
	 * Currency decimals.
	 *
	 * @param int $currency currency.
	 */
	private function currency_has_decimals( $currency ) {
		if ( in_array( $currency, array( 'HUF', 'JPY', 'TWD' ), true ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Currency decimals.
	 *
	 * @param int $price price.
	 * @param int $order_id order_id.
	 */
	private function eb_number_format( $price, $order_id ) {
		$decimals = 2;
		if ( ! $this->currency_has_decimals( $this->get_currency_code( $order_id ) ) ) {
			$decimals = 0;
		}
		return number_format( $price, $decimals, '.', '' );
	}
}
