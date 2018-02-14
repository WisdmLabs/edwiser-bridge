<?php
namespace app\wisdmlabs\edwiserBridge;

class EBManageOrderRefund
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
        // $orderDetail= get_post_meta($orderId, "", true);
        // $curanecy = getCurrentPayPalcurrencySymb();
        // $amt      = getArrValue($refundData, "amt");
         $msg      = sprintf(__("Failed to initiate refund for order: #%s due to %s."), $orderId, "");
         unset($refundData);
        return array("status" => false, "msg" => $msg);
    }
}
