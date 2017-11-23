<?php
/**
 * This class defines all code necessary to manage user's course orders meta'.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EBOrderStatus
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The current version of this plugin.
     */
    private $version;

    public function __construct($pluginName, $version)
    {
        $this->plugin_name = $pluginName;
        $this->version     = $version;
    }

    /**
     * Callback function to save the order status history data.
     *
     * @since 1.3.0
     * @param numer $orderId current updated order id.
     * @return number order id
     */
    public function saveMeta($orderId)
    {
        if (!current_user_can('edit_post', $orderId)) {
            return $orderId;
        }
        $nonce = getArrValue($_POST, 'eb_order_meta_nons');
        if (!wp_verify_nonce($nonce, "eb_order_history_meta_nons")) {
            return $orderId;
        }
        $this->saveOrderStatusHistory($orderId, $_POST);
    }
    /**
     * Function provides the functionality to edit the history data and add new
     * at first position. and save the value into the database.
     *
     * @since 1.3.0
     * @param type $orderId
     */
    private function saveOrderStatusHistory($orderId, $data)
    {
        $history = get_post_meta($orderId, "eb_order_status_history", true);
        if (!is_array($history)) {
            $history = array();
        }
        array_unshift($history, $this->prepareNote($orderId, $data));
        update_post_meta($orderId, "eb_order_status_history", $history);
    }

    /**
     * Function provides the functionality to create the notes formated array
     *
     * @since 1.3.0
     * @param number $orderId current eb_order post id.
     * @param array $data order update meta.
     * @return array returns an array of the new status note
     */
    private function prepareNote($orderId, $data)
    {
        $curUser = wp_get_current_user();
        $curUser->user_login;
        $note    = array(
            "by"   => $curUser->user_login,
            "time" => current_time("timestamp"),
            "note" => $this->createNoteData($orderId, $data),
        );
        return $note;
    }

    /**
     * Provides the functionality to prepate the refund note data in the format of
     * array(
     * "status"=>"",
     * "refund_amt"=>"",
     * "refund_note"=>"",
     * "refund_unenroll_users"=>"",
     * "currancy"=>"",
     * )
     * @since 1.3.0
     * @param number $orderId current eb_order post id.
     * @param array $data order update meta.
     * @return array returns an array of the refund status data
     */
    private function createNoteData($orderId, $data)
    {
        $ordDetail = get_post_meta($orderId, 'eb_order_options', true);
        $orderData = getArrValue($data, 'eb_order_options', false);
        if ($orderData == false) {
            return;
        }
        $oldStatus = getArrValue($ordDetail, "order_status");
        $newStatus = getArrValue($orderData, "order_status");
        $note      = array("old_status" => $oldStatus, "new_status" => $newStatus);
        $ordStatus = getArrValue($data, 'order_status');
        if ($ordStatus == "refunded") {
            $note['refund_data'] = array(
                "status"               => "refunded",
                "refund_amt"           => getArrValue($data, 'eb_ord_refund_amt'),
                "refund_note"          => getArrValue($data, 'eb_order_refund_note'),
                "refund_unenroll_users" =>getArrValue($data, 'eb_order_meta_unenroll_user'),
                "currency"             => getCurrentPayPalcurrencySymb(),
            );
        }
        return $note;
    }
}
