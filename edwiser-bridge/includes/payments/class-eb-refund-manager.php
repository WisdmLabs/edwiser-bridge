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

class EbPaymentRefundManager
{

    public function refundInitiater()
    {
        return;

        if (!wp_verify_nonce($_POST['_wpnonce_field'], 'check_sync_action')) {
            die('Busted!');
        }

        if (isset($_POST['order_id']) && !empty($_POST['order_id']) && isset($_POST['refund_amt']) && !empty($_POST['refund_amt'])) {
            $response = $this->refundTransaction($_POST['order_id'], $_POST['refund_amt'], "dummy reason ");
        }

        error_log("POST ::" . print_r($_POST, 1));
        error_log("response :: " . print_r($response, 1));
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
    public function refundTransaction($orderId, $amount = null, $reason = '')
    {
        $sandbox   = get_post_meta($orderId, "eb_paypal_sandbox", 1);
        $payPalURL = "https://api-3t.paypal.com/nvp";
        if (isset($sandbox) && !empty($sandbox) && $sandbox == "yes") {
            $payPalURL = "https://api-3t.sandbox.paypal.com/nvp";
        }

        $raw_response = wp_safe_remote_post(
            $payPalURL,
            array(
            'method'      => 'POST',
            'body'        => $this->getRefundRequestData($orderId, $amount, $reason),
            'timeout'     => 100,
            'httpversion' => '1.1',
                )
        );

        if (empty($raw_response['body'])) {
            return new WP_Error('paypal-api', __('Empty Response', "eb-textdomain"));
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
    private function getRefundRequestData($orderId, $amount = null, $reason = '')
    {
        $txnId = $this->getTransactionId($orderId);
        if (!$txnId) {
            echo json_encode(array("msg" => __("Sorry, can not process this request as this is invalid transaction.", "eb-textdomain")));
            die();
        }

        $apiDetails = $this->getPaypalApiDetails();

        if (!$apiDetails) {
            echo json_encode(array("msg" => __("Please update Paypal API details on edwiser paypal settings page.", "eb-textdomain")));
            die();
        }

        $request = array(
            'VERSION'       => '84.0',
            'SIGNATURE'     => $apiDetails['sign'],
            'USER'          => $apiDetails['username'],
            'PWD'           => $apiDetails['password'],
            'METHOD'        => 'RefundTransaction',
            'TRANSACTIONID' => $txnId,
            'NOTE'          => $reason,
            'REFUNDTYPE'    => 'Full'
        );
        if (!is_null($amount)) {
            $request['AMT'] = $this->numberFormat($amount, $orderId);

            $request['CURRENCYCODE'] = $this->getCurrencyCode($orderId);

            $request['REFUNDTYPE'] = 'Partial';
        }
        return $request;
    }

    private function getPaypalApiDetails()
    {
        $apiDetails = get_option("eb_paypal");
        $payPalData = array(
            "username" => getArrValue($apiDetails, 'eb_api_username', ""),
            "password" => getArrValue($apiDetails, 'eb_api_password', ""),
            "sign"     => getArrValue($apiDetails, 'eb_api_signature', "")
        );
        return $payPalData;
    }

    private function getTransactionId($orderId)
    {
        $txnId = get_post_meta($orderId, "eb_transaction_id", 1);
        if ($txnId && !empty($txnId)) {
            return $txnId;
        }
        return 0;
    }

    private function getCurrencyCode($orderId)
    {
        $currencyCode = get_post_meta($orderId, "eb_paypal_currency", 1);
        if (!$currencyCode && empty($currencyCode)) {
            $option = unserialize(get_option("eb_paypal"));
            return $option['eb_paypal_currency'];
        }
        return $currencyCode;
    }

    private function currencyHasDecimals($currency)
    {
        if (in_array($currency, array('HUF', 'JPY', 'TWD'))) {
            return false;
        }
        return true;
    }

    private function numberFormat($price, $orderId)
    {
        $decimals = 2;

        if (!$this->currencyHasDecimals($this->getCurrencyCode($orderId))) {
            $decimals = 0;
        }
        return number_format($price, $decimals, '.', '');
    }
}
