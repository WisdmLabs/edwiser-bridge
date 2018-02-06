<?php
/**
 * This class defines all code necessary to manage paypal refund feature.
 *
 * @link       https://edwiser.org
 * @since      1.2.1
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EBRefundPaymentManager
{


    public function refundInitiater()
    {

        if (!wp_verify_nonce($_POST['_wpnonce_field'], 'check_sync_action')) {
            die('Busted!');
        }

        if (isset($_POST['order_id']) && !empty($_POST['order_id']) && isset($_POST['refund_amt']) && !empty($_POST['refund_amt'])) {
            $response = self::refundTransaction($_POST['order_id'], $_POST['refund_amt'], "dummy reason ");
        }

        error_log("POST ::".print_r($_POST, 1));
        error_log("response :: ".print_r($response, 1));
        echo json_encode(array("succes" => $response));
        die();
    }




    /**
     * Refund an order via PayPal.
     * @param  Eb order $order
     * @param  float    $amount
     * @param  string   $reason
     * @return object Either an object of name value pairs for a success, or a WP_ERROR object.
     */
    public static function refundTransaction($orderId, $amount = null, $reason = '')
    {
        $sandbox = get_post_meta($orderId, "eb_paypal_sandbox", 1);
        if (isset($sandbox) && !empty($sandbox) && $sandbox == "yes") {
            $sandbox = 1;
        } else {
            $sandbox = 0;
        }


        $raw_response = wp_safe_remote_post(
            $sandbox ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp',
            array(
                'method'      => 'POST',
                'body'        => self::getRefundRequestData($orderId, $amount, $reason),
                'timeout'     => 70,
                // 'user-agent'  => 'WooCommerce/' . WC()->version,
                'httpversion' => '1.1',
            )
        );


        if (empty($raw_response['body'])) {
            return new WP_Error('paypal-api', 'Empty Response');
        } elseif (is_wp_error($raw_response)) {
            return $raw_response;
        }

        parse_str($raw_response['body'], $response);

        return (object) $response;
    }


    /**
     * Get refund request args.
     * @param  eb_order $order
     * @param  float    $amount
     * @param  string   $reason
     * @return array
     */
    public static function getRefundRequestData($orderId, $amount = null, $reason = '')
    {
        $txnId = self::getTransactionId($orderId);
        if (!$txnId) {
            echo json_encode(array("msg" => "Sorry, can not process this request as this is invalid transaction."));
            die();
        }

        $apiDetails = self::getPaypalApiDetails();

        if (!$apiDetails) {
            echo json_encode(array("msg" => "Please update Paypal API details on edwiser paypal settings page."));
            die();
        }

        $request = array(
            'VERSION'       => '84.0',
            'SIGNATURE'     => $apiDetails['sign'],
            'USER'          => $apiDetails['username'],
            'PWD'           => $apiDetails['password'],
            'METHOD'        => 'RefundTransaction',
            'TRANSACTIONID' => $txnId,
            /*'NOTE'          => html_entity_decode(wc_trim_string($reason, 255), ENT_NOQUOTES, 'UTF-8'),*/
            'NOTE'          => $reason,
            'REFUNDTYPE'    => 'Full'
        );




        if (! is_null($amount)) {
            $request['AMT']          = self::numberFormat($amount, $orderId);

            $request['CURRENCYCODE'] = self::getCurrencyCode($orderId);

            $request['REFUNDTYPE']   = 'Partial';
        }
        return $request;
        // return apply_filters( 'woocommerce_paypal_refund_request', $request, $order, $amount, $reason );
    }


    public static function getPaypalApiDetails()
    {

        $apiDetails = get_option("eb_paypal");
        if (isset($apiDetails['eb_api_username']) && !empty($apiDetails['eb_api_username']) && isset($apiDetails['eb_api_password']) && !empty($apiDetails['eb_api_password']) && isset($apiDetails['eb_api_signature']) && !empty($apiDetails['eb_api_signature'])) {
            return array("username" => $apiDetails['eb_api_username'], "password" => $apiDetails['eb_api_password'], "sign" => $apiDetails['eb_api_signature']);
        }
        return 0;
    }


    public static function getTransactionId($orderId)
    {
        $txnId = get_post_meta($orderId, "eb_transaction_id", 1);

        if ($txnId && !empty($txnId)) {
            return $txnId;
        }
        return 0;
    }

    public static function getCurrencyCode($orderId)
    {
        $currencyCode = get_post_meta($orderId, "eb_paypal_currency", 1);
        if (!$currencyCode && empty($currencyCode)) {
            $option = unserialize(get_option("eb_paypal"));
            return $option['eb_paypal_currency'];
        }
        return $currencyCode;
    }



    public static function currencyHasDecimals($currency)
    {
        if (in_array($currency, array('HUF', 'JPY', 'TWD'))) {
            return false;
        }

        return true;
    }

    public static function numberFormat($price, $orderId)
    {
        $decimals = 2;

        if (!self::currencyHasDecimals(self::getCurrencyCode($orderId))) {
            $decimals = 0;
        }

        return number_format($price, $decimals, '.', '');
    }
}
