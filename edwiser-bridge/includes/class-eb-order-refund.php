<?php
namespace app\wisdmlabs\edwiserBridge;

class EBOrderInitRefund
{

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function processRefund($orderId, $amt, $curanecy)
    {
        unset($orderId, $amt, $curanecy);
    }
}
