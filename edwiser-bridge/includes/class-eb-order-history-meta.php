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

class EBOrderHistory
{

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Provides the functionality to display order status history's meta box list.
     *
     * @since 1.3.0
     * @global WP_Post object $post current post variable defined by WP.
     */
    public function addOrderStatusHistoryMeta()
    {
        global $post;
        $orderHist = get_post_meta($post->ID, "eb_order_status_history", true);
        ?>
        <div>
            <?php
            wp_nonce_field("eb_order_history_meta_nons", "eb_order_meta_nons");
            if (is_array($orderHist) && count($orderHist) > 0) {
                echo '<ul class="eb-sso-hist-note-wrap">';
                foreach ($orderHist as $history) {
                    $this->getHistoryTag($history);
                }
                echo '</ul>';
            }
            ?>
        </div>
        <?php
    }

    /**
     * Provides the functionality to create post history meta list element.
     *
     * @since 1.3.0
     * @param type $ordHist array of the order history element.
     */
    private function getHistoryTag($ordHist)
    {
        $updatedBy = getArrValue($ordHist, "by");
        $updatedOn = getArrValue($ordHist, "time");
        $noteData  = getArrValue($ordHist, "note", array());
        $note      = $this->createNoteMsg($noteData);
        ?>
        <li>
            <div class="eb-sso-hist-note">
                <?php echo $note; ?>
            </div>
            <div class="eb-sso-hist-by">
                <?php printf(__("added by %s on %s.", "eb-textdomain"), $updatedBy, date("F j, Y, g:i a", $updatedOn)); ?>
            </div>
        </li>
        <?php
    }

    private function createNoteMsg($noteData)
    {
        $type = getArrValue($noteData, "type", "");
        $msg  = getArrValue($noteData, "msg", "");
        $note = "";
        switch ($type) {
            case "status_update":
                $note = $this->getStatusUpdateMsg($msg);
                break;
            case "refund":
                $note = $this->getRefundNoteMsg($msg);
                break;
            case "new_order":
                $note = $this->getNewORderNoteMsg($msg);
                break;
            default:
                $note = apply_filters("eb_order_history_meta_type_default", $msg, $type);
                break;
        }
        return $note;
    }

    /**
     * Provides the functionality to create the post update status statement.
     *
     * @since 1.3.0
     * @param string $newStatus new updated order status.
     * @param type $oldStatus old order updated status.
     * @return string returns the order status updates in statement format.
     */
    private function getStatusUpdateMsg($note)
    {
        $oldStatus   = getArrValue($note, "old_status");
        $newStatus   = getArrValue($note, "new_status");
        $constStatus = array(
            'pending'   => __('Pending', "eb-textdomain"),
            'completed' => __('Completed', "eb-textdomain"),
            'failed'    => __('Failed', "eb-textdomain"),
            'refunded'  => __('Refunded', "eb-textdomain"),
        );

        $user = get_userdata(get_current_user_id());

        $statOld     = getArrValue($constStatus, $oldStatus);
        $statNew     = getArrValue($constStatus, $newStatus);
        $noteState   = sprintf(__("Order status changed from %s to %s.", "eb-textdomain"), $statOld, $statNew);

        if (empty($oldStatus)) {
            $noteState   = sprintf(__("New Order created by %s.", "eb-textdomain"), $user->user_login);
        }
        $noteState   = apply_filters("eb_order_history_disp_status_change_msg", $noteState, $note);
        return $noteState;
    }

    private function getRefundNoteMsg($note)
    {
        // $currency        = getArrValue($note, 'currency', getCurrentPayPalcurrencySymb());
        // $refundAmt       = getArrValue($note, 'refund_amt', "0.00");
        $refundNote      = getArrValue($note, 'refund_note');
        $refundIsUneroll = getArrValue($note, 'refund_uneroll_users');
        $unenrollMsg     = "";
        if ($refundIsUneroll == "ON") {
            $unenrollMsg = __(" Also the user is unenrolled from associated course.", "eb-textdomain");
        }
        $histNote = sprintf(__($refundNote." %s", "eb-textdomain"), $unenrollMsg);
        $histNote = apply_filters("eb_order_history_disp_refund_msg", $histNote, $note);
        return $histNote;
    }

    private function getNewORderNoteMsg($note)
    {
        $note = apply_filters("eb_order_history_disp_refund_msg", $note);
        return $note;
    }
}
