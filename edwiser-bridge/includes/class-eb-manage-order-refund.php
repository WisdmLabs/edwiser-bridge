<?php
namespace app\wisdmlabs\edwiserBridge;

class EbOrderRefundManage
{

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function initRefund($orderId, $refundData)
    {
        $amt          = getArrValue($refundData, "amt");
        $note         = getArrValue($refundData, "note");
        $refundStatus = array(
            "status" => false,
            "msg"    => __("Failed to initiate refund for order #$orderId", "eb-textdomain"),
        );
        $refundType   = $this->getRefundType($orderId, $amt);
        edwiserBridgeInstance()->logger()->add('refund', "Initaiting $refundType refund for order ID: ['$orderId'], Refund amount: $amt and refund note: $note");
        return apply_filters("eb_order_refund_init", $refundStatus, $orderId, $refundType, $amt, $note);
    }

    private function getRefundType($orderId, $amt)
    {
        $type        = "Full";
        $orderData   = get_post_meta($orderId, "eb_order_options", true);
        $refunds     = get_post_meta($orderId, "eb_order_status_history", true);
        $order       = new EBOrderMeta($this->pluginName, $this->version);
        $paidAmt     = getArrValue($orderData, "amount_paid");
        $refundAmt   = $order->getTotalRefuncdAmt($refunds);
        $refundedAmt = $paidAmt - ($refundAmt + $amt);
        if ($refundedAmt > 0) {
            $type = "Partial";
        }
        return $type;
    }
}
