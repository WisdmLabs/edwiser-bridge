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
            "amt"    => $amt,
            "status" => false,
            "msg"    => __("Failed to initiate refund for order #$orderId", "eb-textdomain"),
        );
        $refundType   = $this->getRefundType($orderId, $amt);
        edwiserBridgeInstance()->logger()->add('refund', "Initaiting $refundType refund for order ID: ['$orderId'], Refund amount: $amt and refund note: $note");
        $refundStatus = apply_filters("eb_order_refund_init", $refundStatus, $orderId, $refundType, $amt, $note);
        
        if (isset($refundStatus['status']) && $refundStatus['status']) {
            $order     = new EBOrderMeta($this->pluginName, $this->version);
            $orderData = get_post_meta($orderId, "eb_order_options", true);
            $refunds   = $order->getOrdersAllRefund($orderId);
            $paidAmt     = getArrValue($orderData, "amount_paid");
            $totalRefund = getTotalRefundAmt($refunds);

            if ($paidAmt <= $totalRefund + $amt) {
                edwiserBridgeInstance()->orderManager()->updateOrderStatus($orderId, 'refunded');
            }
        }
        
        return $refundStatus;
    }

    private function getRefundType($orderId, $amt)
    {
        $type      = "Partial";
        $orderData = get_post_meta($orderId, "eb_order_options", true);
        $order     = new EBOrderMeta($this->pluginName, $this->version);
        $refunds   = $order->getOrdersAllRefund($orderId);
        $paidAmt     = getArrValue($orderData, "amount_paid");
        //$totalRefund = getTotalRefundAmt($refunds);

        if (empty($refunds) && $paidAmt == $amt) {
            $type = "Full";
        }

        return $type;
    }
}
