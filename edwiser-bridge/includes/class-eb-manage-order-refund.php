<?php
namespace app\wisdmlabs\edwiserBridge;

class EbOrderRefundManage
{

    private $pluginName;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->pluginName = $plugin_name;
        $this->version    = $version;
    }

    public function initRefund($orderId, $refundData)
    {
        $amt          = getArrValue($refundData, "amt");
        $note         = getArrValue($refundData, "note");
        $refundStatus = array(
            "amt"=>$amt,
            "status" => false,
            "msg"    => __("Failed to initiate refund for order #$orderId", "eb-textdomain"),
        );
        $refundType   = $this->getRefundType($orderId, $amt);
        edwiserBridgeInstance()->logger()->add('refund', "Initaiting $refundType refund for order ID: ['$orderId'], Refund amount: $amt and refund note: $note");
        $refundStatus = apply_filters("eb_order_refund_init", $refundStatus, $orderId, $refundType, $amt, $note);
        return $refundStatus;
    }

    private function getRefundType($orderId, $amt)
    {
        $type        = "Partial";
        $orderData   = get_post_meta($orderId, "eb_order_options", true);
        $refunds     = get_post_meta($orderId, "eb_order_refund_hist", true);
        $order       = new EBOrderMeta($this->pluginName, $this->version);
        $paidAmt     = getArrValue($orderData, "amount_paid");
        $totalRefund   = $order->getTotalRefuncdAmt($refunds);

        if (empty($refunds) && $paidAmt==$amt) {
            $type = "Full";
        }

        if ($paidAmt<=$totalRefund+$amt) {
            edwiserBridgeInstance()->orderManager()->updateOrderStatus($orderId, 'refunded');
        }
        return $type;
    }
}
