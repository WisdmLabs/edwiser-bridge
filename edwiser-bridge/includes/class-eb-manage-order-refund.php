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

    public function init_refund($order_id, $refundData)
    {
        $amt          = getArrValue($refundData, "amt");
        $note         = getArrValue($refundData, "note");
        $refund_status = array(
            "amt"    => $amt,
            "status" => false,
            "msg"    => __("Failed to initiate refund for order #$order_id", "eb-textdomain"),
        );
        $refund_type   = $this->get_refund_type($order_id, $amt);
        edwiserBridgeInstance()->logger()->add('refund', "Initaiting $refund_type refund for order ID: ['$order_id'], Refund amount: $amt and refund note: $note");
        $refund_status = apply_filters("eb_order_refund_init", $refund_status, $order_id, $refund_type, $amt, $note);
        
        if (isset($refund_status['status']) && $refund_status['status']) {
            $order     = new EBOrderMeta($this->pluginName, $this->version);
            $order_data = get_post_meta($order_id, "eb_order_options", true);
            $refunds   = $order->get_orders_all_refund($order_id);
            $paid_amt     = getArrValue($order_data, "amount_paid");
            $total_refund = getTotalRefundAmt($refunds);

            if ($paid_amt <= $total_refund + $amt) {
                edwiserBridgeInstance()->order_manager()->update_order_status($order_id, 'refunded');
            }
        }
        
        return $refund_status;
    }

    private function get_refund_type($order_id, $amt)
    {
        $type      = "Partial";
        $order_data = get_post_meta($order_id, "eb_order_options", true);
        $order     = new EBOrderMeta($this->pluginName, $this->version);
        $refunds   = $order->get_orders_all_refund($order_id);
        $paid_amt     = getArrValue($order_data, "amount_paid");
        //$totalRefund = getTotalRefundAmt($refunds);

        if (empty($refunds) && $paid_amt == $amt) {
            $type = "Full";
        }

        return $type;
    }
}
