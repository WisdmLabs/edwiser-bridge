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
     * Function initiates the refund it is ajax callback for the eb order refund refund.
     * @since 1.3.0
     * @param type $requestData
     */
    public function initEbOrderRefund()
    {
    }

    public function saveNewOrderPlaceNote($orderId)
    {
        $ordDetail = get_post_meta($orderId, 'eb_order_options', true);
        $courseId  = getArrValue($ordDetail, "course_id");
        $noteData  = array(
            "extra_note" => array(
                "new_ord" => true,
                "msg"     => sprintf(__("New order has been placed for the <strong>%s</strong> course.", "eb-textdomain"), get_the_title($courseId)),
            ),
        );
        $this->saveOrderStatusHistory($orderId, $noteData);
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
        do_action("eb_before_order_refund_meta_save");
        update_post_meta($orderId, "eb_order_status_history", $history);
        do_action("eb_after_order_refund_meta_save");
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
        $curUser   = wp_get_current_user();
        $note      = $this->createNoteData($orderId, $data);
        $extraNote = getArrValue($data, "extra_note", false);
        $note      = $this->getExtraNote($note, $extraNote);
        $note      = array(
            "by"   => $curUser->user_login,
            "time" => current_time("timestamp"),
            "note" => $note,
        );
        $note      = apply_filters("eb_order_current_history_status_update", $note);
        return $note;
    }

    private function getExtraNote($note, $extraNote)
    {
        if (is_array($note)) {
            $note['extra_note'] = $extraNote;
        } else {
            $note = array('extra_note' => $extraNote);
        }
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
        // $extraData = getArrValue($data, "extra_note", false);

        $note = array(
            "old_status" => $oldStatus,
            "new_status" => $newStatus,
        );
        $ordStatus = getArrValue($ordDetail, 'order_status');
        if ($ordStatus == "refunded") {
            $refundAmt           = getArrValue($data, 'eb_ord_refund_amt');
            $note['refund_data'] = array(
                "status"                => "refunded",
                "refund_amt"            => $refundAmt,
                "refund_note"           => getArrValue($data, 'eb_order_refund_note'),
                "refund_unenroll_users" => getArrValue($data, 'eb_order_meta_unenroll_user'),
                "currency"              => getCurrentPayPalcurrencySymb(),
            );
            $this->saveOrderRefundAmt($orderId, $refundAmt);
        }
        return $note;
    }

    // private function getExtraNoteData($extraNote)
    // {
    //     $newOrd = getArrValue($extraNote, "new_ord", false);
    // }

    private function saveOrderRefundAmt($orderId, $refundAmt)
    {
        $curUser = wp_get_current_user();
        $curUser->user_login;
        $refunds = get_post_meta($orderId, "eb_order_refund_hist", true);
        $refund  = array(
            "amt"  => $refundAmt,
            "by"   => $curUser->user_login,
            "time" => current_time("timestamp"),
            "currency" => getCurrentPayPalcurrencySymb(),
        );
        if (is_array($refunds)) {
            $refunds[] = $refund;
        } else {
            $refunds = array($refund);
        }
        update_post_meta($orderId, "eb_order_refund_hist", $refunds);
    }
}
