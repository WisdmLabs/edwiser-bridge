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
    public function init_eb_order_refund()
    {
        check_ajax_referer("eb_order_refund_nons_field", "order_nonce");
        $order_id = getArrValue($_POST, "eb_order_id");

        $refund_manager = new EbOrderRefundManage($this->plugin_name, $this->version);
        $refund_data    = array(
            "amt"            => getArrValue($_POST, "eb_ord_refund_amt"),
            "note"           => getArrValue($_POST, "eb_order_refund_note", ""),
            "unenroll_users" => getArrValue($_POST, "eb_order_meta_unenroll_user", "NO"),
        );
        $refund        = $refund_manager->init_refund($order_id, $refund_data);
        $refund_status  = getArrValue($refund, "status", false);
        $refund_msg     = getArrValue($refund, "msg", "");
        if ($refund_status) {
            $refund_data["note"] = $refund_msg;
            $note                = $this->get_order_refund_status_msg($order_id, $refund_data);
            $this->save_order_status_history($order_id, $note);
            do_action("eb_order_refund_init_success", $order_id, $note);
            wp_send_json_success($refund_msg);
        } else {
            wp_send_json_error($refund_msg);
        }
    }

    /**
     * Callback function to save the order status history data.
     *
     * @since 1.3.0
     * @param numer $orderId current updated order id.
     * @return number order id
     */
    public function save_status_update_meta($order_id)
    {
        if (!current_user_can('edit_post', $order_id)) {
            return $order_id;
        }
        $nonce = getArrValue($_POST, 'eb_order_meta_nons');
        if (!wp_verify_nonce($nonce, "eb_order_history_meta_nons")) {
            return $order_id;
        }
        $note = $this->get_status_update_note($order_id, $_POST);
        $this->save_order_status_history($order_id, $note);
    }

    public function save_new_order_place_note($order_id)
    {
        $ord_detail = get_post_meta($order_id, 'eb_order_options', true);
        $course_id  = getArrValue($ord_detail, "course_id");
        $msg       = sprintf(__("New order has been placed for the <strong>%s</strong> course.", "eb-textdomain"), get_the_title($course_id));
        $msg       = apply_filters("eb_order_history_save_status_new_order_msg", $msg);
        $note      = array(
            "type" => "new_order",
            "msg"  => $msg,
        );
        $this->save_order_status_history($order_id, $note);
    }

    /**
     * Function provides the functionality to create the notes formated array
     *
     * @since 1.3.0
     * @param number $orderId current eb_order post id.
     * @param array $data order update meta.
     * @return array returns an array of the new status note
     */
    private function get_status_update_note($order_id, $data)
    {
        $ord_detail = get_post_meta($order_id, 'eb_order_options', true);
        $order_data = getArrValue($data, 'eb_order_options', false);
        if ($order_data == false) {
            return;
        }
        $old_status = getArrValue($ord_detail, "order_status", false);
        $new_status = getArrValue($order_data, "order_status", false);
        $msg       = array(
            "old_status" => $old_status,
            "new_status" => $new_status,
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
    private function get_order_refund_status_msg($order_id, $data)
    {
        $refund_amt = getArrValue($data, 'amt');
        $msg       = array(
            "amt"=>$refund_amt,
            "refund_note"           => getArrValue($data, 'note'),
            "refund_unenroll_users" => getArrValue($data, 'unenroll_users', false),
        );
        if (getArrValue($msg, "refund_unenroll_users")=="ON") {
            $this->unenroll_user_from_courses($order_id);
        }
        $msg  = apply_filters("eb_order_history_save_refund_status_msg", $msg);
        $note = array(
            "type" => "refund",
            "msg"  => $msg
        );
        $this->save_order_refund_amt($order_id, $refund_amt);
        return $note;
    }




    private function save_order_refund_amt($order_id, $refund_amt)
    {
        $cur_user = wp_get_current_user();
        $refunds = get_post_meta($order_id, "eb_order_refund_hist", true);
        $refund  = array(
            "amt"      => $refund_amt,
            "by"       => $cur_user->user_login,
            "time"     => current_time("timestamp"),
            "currency" => getCurrentPayPalcurrencySymb(),
        );
        if (is_array($refunds)) {
            $refunds[] = $refund;
        } else {
            $refunds = array($refund);
        }
        update_post_meta($order_id, "eb_order_refund_hist", $refunds);
    }

    /**
     * Function provides the functionality to edit the history data and add new
     * at first position. and save the value into the database.
     *
     * @since 1.3.0
     * @param type $orderId
     */
    private function save_order_status_history($order_id, $note)
    {
        $curUser = wp_get_current_user();
        updateOrderHistMeta($order_id, $curUser->user_login, $note);
    }

    private function unenroll_user_from_courses($order_id)
    {
        $order_details   = get_post_meta($order_id, "eb_order_options", true);
        $course_id       = getArrValue($order_details, "course_id", "");
        $user_wp_id       = getArrValue($order_details, "buyer_id", "");
        $enrollment_mang = EBEnrollmentManager::instance($this->plugin_name, $this->version);
        $args           = array(
            'user_id' => $user_wp_id,
            'courses' => array($course_id),
            'suspend' => 1,
        );
        // $resp           = $enrollmentMang->updateUserCourseEnrollment($args);
        $resp           = $enrollment_mang->update_user_course_enrollment($args);
        
        if ($resp) {
            $curUser = wp_get_current_user();
            $note    = array(
                "type" => "enrollment_susspend",
                "msg"  => __("User enrollment has been suspended on order refund request.", "eb-textdomain")
            );
            updateOrderHistMeta($order_id, $curUser->user_login, $note);
        }
        return $resp;
    }
}
