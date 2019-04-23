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

class EbPayPalRefundManager
{

    private $pluginName = null;
    private $version    = null;

    public function __construct($pluginName, $version)
    {
        $this->pluginName = $pluginName;
        $this->version    = $version;
        // add_filter("eb_order_refund_init", array($this, "refund"), 10, 5);
    }

    /**
     * Refund an order via PayPal.
     * @param  Eb order $order
     * @param  float    $amount
     * @param  string   $reason
     * @return object Either an object of name value pairs for a success, or a WP_ERROR object.
     */
    public function refund($status, $orderId, $refundType = "Full", $amount = null, $reason = '')
    {
        $success   = 1;
        $sandbox   = get_post_meta($orderId, "eb_paypal_sandbox", 1);
        $payPalURL = "https://api-3t.paypal.com/nvp";
        if (isset($sandbox) && !empty($sandbox) && $sandbox == "yes") {
            $payPalURL = "https://api-3t.sandbox.paypal.com/nvp";
        }
        $requestData = $this->getRefundRequestData($orderId, $refundType, $amount, $reason);
        if ($requestData["status"]) {
            $reqArgs  = array(
                'method'      => 'POST',
                'body'        => $requestData["data"],
                'timeout'     => 500,
                'httpversion' => '1.1',
                'headers'     => array("content-type" => "application/json")
            );
            edwiserBridgeInstance()->logger()->add('refund', "Order: $orderId ,Sending Refund request to PayPal. Request data is : " . serialize($reqArgs));
            $response = wp_safe_remote_post($payPalURL, $reqArgs);
            try {
                if (is_wp_error($response)) {
                    $success       = 0;
                    $status['msg'] = $response;
                } elseif (empty($response['body'])) {
                    $success       = 0;
                    $status['msg'] = __('No Response from PayPal', "eb-textdomain");
                }
                parse_str($response['body'], $response);
                edwiserBridgeInstance()->logger()->add('refund', "PayPal refund responce: " . serialize($response));
            } catch (Exception $ex) {
                edwiserBridgeInstance()->logger()->add('refund', "Order: $orderId ,Exception: " . serialize($ex));
            }
            $respStatus = getArrValue($response, "ACK", false);
            if ($respStatus == "Success") {
                $status['msg'] = __(sprintf("Refund for amount %s against the order #%s has been initiated successfully. Transaction id: %s", getArrValue($response, "GROSSREFUNDAMT"), $orderId, getArrValue($response, "REFUNDTRANSACTIONID")));
            } else if ($respStatus == "Failure") {
                $success       = 0;
                $status['msg'] = "<strong>".__("PayPal Responce: ", "eb-textdomain")."</strong>".getArrValue($response, "L_LONGMESSAGE0", "");
            }
        } else {
            $success       = 0;
            $status['msg'] = $requestData['data'];
        }
        $status['status'] = $success;
        return $status;
    }

    /**
     * Get refund request args.
     * @param  eb_order $order
     * @param  float    $amount
     * @param  string   $reason
     * @return array
     */
    private function getRefundRequestData($orderId, $refundType, $amount = null, $reason = '')
    {
        $txnId   = $this->getTransactionId($orderId);
        $sucess  = 1;
        $reqData = array();
        if (!$txnId) {
            $sucess          = 0;
            $reqData['data'] = __("Sorry, can not process this request as this is invalid transaction.", "eb-textdomain");
        }

        $apiDetails = $this->getPaypalApiDetails();

        if (!$apiDetails) {
            $sucess          = 0;
            $reqData['data'] = __("Please update Paypal API details on edwiser paypal settings page.", "eb-textdomain");
        }

        if ($sucess) {
            $data = array(
                'VERSION'       => '84.0',
                'SIGNATURE'     => $apiDetails['sign'],
                'USER'          => $apiDetails['username'],
                'PWD'           => $apiDetails['password'],
                'METHOD'        => 'RefundTransaction',
                'TRANSACTIONID' => $txnId,
                'NOTE'          => $reason,
                'REFUNDTYPE'    => $refundType
            );
            if (!is_null($amount)) {
                $data['AMT']          = $this->numberFormat($amount, $orderId);
                $data['CURRENCYCODE'] = $this->getCurrencyCode($orderId);
            }
            $reqData['data'] = $data;
        }
        $reqData['status'] = $sucess;
        return $reqData;
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
        $txnId = get_post_meta($orderId, "eb_transaction_id", true);
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
