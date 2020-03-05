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
     * Function initiates the refund it is ajax callback for the eb order refund refund.
     * @since 1.3.0
     * @param type $requestData
     */
    public function initEbOrderRefund()
    {
        check_ajax_referer("eb_order_refund_nons_field", "order_nonce");
        $orderId = getArrValue($_POST, "eb_order_id");

        $refundManager = new EbOrderRefundManage($this->plugin_name, $this->version);
        $refundData    = array(
            "amt"            => getArrValue($_POST, "eb_ord_refund_amt"),
            "note"           => getArrValue($_POST, "eb_order_refund_note", ""),
            "unenroll_users" => getArrValue($_POST, "eb_order_meta_unenroll_user", "NO"),
        );
        $refund        = $refundManager->initRefund($orderId, $refundData);
        $refundStatus  = getArrValue($refund, "status", false);
        $refundMsg     = getArrValue($refund, "msg", "");
        if ($refundStatus) {
            $refundData["note"] = $refundMsg;
            $note               = $this->getOrderRefundStatusMsg($orderId, $refundData);
            $this->saveOrderStatusHistory($orderId, $note);
            do_action("eb_order_refund_init_success", $orderId, $note);
            wp_send_json_success($refundMsg);
        } else {
            wp_send_json_error($refundMsg);
        }
    }

    /**
     * Callback function to save the order status history data.
     *
     * @since 1.3.0
     * @param numer $orderId current updated order id.
     * @return number order id
     */
    public function saveStatusUpdateMeta($orderId)
    {
        if (!current_user_can('edit_post', $orderId)) {
            return $orderId;
        }
        $nonce = getArrValue($_POST, 'eb_order_meta_nons');
        if (!wp_verify_nonce($nonce, "eb_order_history_meta_nons")) {
            return $orderId;
        }
        $note = $this->getStatusUpdateNote($orderId, $_POST);
        $this->saveOrderStatusHistory($orderId, $note);
    }

    public function saveNewOrderPlaceNote($orderId)
    {
        $ordDetail = get_post_meta($orderId, 'eb_order_options', true);
        $courseId  = getArrValue($ordDetail, "course_id");
        $msg       = sprintf(__("New order has been placed for the <strong>%s</strong> course.", "eb-textdomain"), get_the_title($courseId));
        $msg       = apply_filters("eb_order_history_save_status_new_order_msg", $msg);
        $note      = array(
            "type" => "new_order",
            "msg"  => $msg,
        );
        $this->saveOrderStatusHistory($orderId, $note);
    }

    /**
     * Function provides the functionality to create the notes formated array
     *
     * @since 1.3.0
     * @param number $orderId current eb_order post id.
     * @param array $data order update meta.
     * @return array returns an array of the new status note
     */
    private function getStatusUpdateNote($orderId, $data)
    {
        $ordDetail = get_post_meta($orderId, 'eb_order_options', true);
        $orderData = getArrValue($data, 'eb_order_options', false);
        if ($orderData == false) {
            return;
        }
        $oldStatus = getArrValue($ordDetail, "order_status", false);
        $newStatus = getArrValue($orderData, "order_status", false);
        $msg       = array(
            "old_status" => $oldStatus,
            "new_status" => $newStatus,
        );
        $msg       = apply_filters("eb_order_history_save_status_change_msg", $msg);
        $note      = array(
            "type" => "status_update",
            "msg"  => $msg,
        );
        return $note;
    }

    /**
     * Provides the functionality to prepate the refund note data in the format of
     * array(
     * "refund_note"=>"",
     * "refund_unenroll_users"=>"",
     * )
     * @since 1.3.0
     * @param number $orderId current eb_order post id.
     * @param array $data order update meta.
     * @return array returns an array of the refund status data
     */
    private function getOrderRefundStatusMsg($orderId, $data)
    {
        $refundAmt = getArrValue($data, 'amt');
        $msg       = array(
            "amt"=>$refundAmt,
            "refund_note"           => getArrValue($data, 'note'),
            "refund_unenroll_users" => getArrValue($data, 'unenroll_users', false),
        );
        if (getArrValue($msg, "refund_unenroll_users")=="ON") {
            $this->unenrollUserFromCourses($orderId);
        }
        $msg  = apply_filters("eb_order_history_save_refund_status_msg", $msg);
        $note = array(
            "type" => "refund",
            "msg"  => $msg
        );
        $this->saveOrderRefundAmt($orderId, $refundAmt);
        return $note;
    }

    private function saveOrderRefundAmt($orderId, $refundAmt)
    {
        $curUser = wp_get_current_user();
        $refunds = get_post_meta($orderId, "eb_order_refund_hist", true);
        $refund  = array(
            "amt"      => $refundAmt,
            "by"       => $curUser->user_login,
            "time"     => current_time("timestamp"),
            "currency" => getCurrentPayPalcurrencySymb(),
        );
        if (is_array($refunds)) {
            $refunds[] = $refund;
        } else {
            $refunds = array($refund);
        }
        update_post_meta($orderId, "eb_order_refund_hist", $refunds);
    }

    /**
     * Function provides the functionality to edit the history data and add new
     * at first position. and save the value into the database.
     *
     * @since 1.3.0
     * @param type $orderId
     */
    private function saveOrderStatusHistory($orderId, $note)
    {
        $curUser = wp_get_current_user();
        updateOrderHistMeta($orderId, $curUser->user_login, $note);
    }

    private function unenrollUserFromCourses($orderId)
    {
        $orderDetails   = get_post_meta($orderId, "eb_order_options", true);
        $courseId       = getArrValue($orderDetails, "course_id", "");
        $userWpId       = getArrValue($orderDetails, "buyer_id", "");
        $enrollmentMang = EBEnrollmentManager::instance($this->plugin_name, $this->version);
        $args           = array(
            'user_id' => $userWpId,
            'courses' => array($courseId),
            'suspend' => 1,
        );
        $resp           = $enrollmentMang->updateUserCourseEnrollment($args);
        if ($resp) {
            $curUser = wp_get_current_user();
            $note    = array(
                "type" => "enrollment_susspend",
                "msg"  => __("User enrollment has been suspended on order refund request.", "eb-textdomain")
            );
            updateOrderHistMeta($orderId, $curUser->user_login, $note);
        }
        return $resp;
    }
}
