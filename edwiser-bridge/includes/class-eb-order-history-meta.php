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
        $updatedBy  = getArrValue($ordHist, "by");
        $updatedOn  = getArrValue($ordHist, "time");
        $note       = getArrValue($ordHist, "note");
        $extraNote  = getArrValue($note, "extra_note", false);
        $oldStatus  = getArrValue($note, "old_status");
        $newStatus  = getArrValue($note, "new_status");
        $refundData = getArrValue($note, "refund_data", false);
        $statusNote = $this->getStatusUpdateNote($newStatus, $oldStatus);
        $refundNote="";
        if ($refundData != false) {
            $refundNote = $this->addRefundNote($refundData);
        }

        if ($extraNote) {
            $newOrd= getArrValue($extraNote, "new_ord", false);
            if ($newOrd) {
                $statusNote=getArrValue($extraNote, "msg", "");
            }
        }
        $statusNote = apply_filters("eb_update_sso_status_update_note", $statusNote, $ordHist);
        $refundNote = apply_filters("eb_update_sso_refund_status_update_note", $refundNote, $refundData);
        ?>
        <li>
            <div class="eb-sso-hist-note">
                <?php
                echo $statusNote;
                echo $refundNote;
                ?>
            </div>
            <div class="eb-sso-hist-by">
                <?php
                printf(__("added by %s on %s.", "eb-textdomain"), $updatedBy, date("F j, Y, g:i a", $updatedOn));
                ?>
            </div>
        </li>
        <?php
    }

    /**
     * Provides the functionality to create the post update status statement.
     *
     * @since 1.3.0
     * @param string $newStatus new updated order status.
     * @param type $oldStatus old order updated status.
     * @return string returns the order status updates in statement format.
     */
    private function getStatusUpdateNote($newStatus, $oldStatus)
    {
        $status    = array(
            'pending'   => __('Pending', "eb-textdomain"),
            'completed' => __('Completed', "eb-textdomain"),
            'failed'    => __('Failed', "eb-textdomain"),
            'refunded'  => __('Refunded', "eb-textdomain"),
        );
        $statOld   = getArrValue($status, $oldStatus);
        $statNew   = getArrValue($status, $newStatus);
        $noteState = sprintf(__("Order status changed from %s to %s.", "eb-textdomain"), $statOld, $statNew);
        return $noteState;
    }

    /**
     * Provides the functionality to prepare the refund statement.
     *
     * @since 1.3.0
     * @param array  $refundData array of the refunded order status element.
     * @return string returns the order status updates in statement format.
     */
    private function addRefundNote($refundData)
    {
        $status = getArrValue($refundData, "status", false);
        if ($status == false) {
            return "";
        }
        $currency        = getArrValue($refundData, 'currency', getCurrentPayPalcurrencySymb());
        $refundAmt       = getArrValue($refundData, 'refund_amt', "0.00");
        $refundNote      = getArrValue($refundData, 'refund_note');
        $refundIsUneroll = getArrValue($refundData, 'refund_uneroll_users');
        ob_start();
        ?>
        <div>
            <?php
            printf(
                __("Amount %s%s has been refunded due to %s", "eb-textdomain"),
                $currency,
                $refundAmt,
                $refundNote
            );
            if ($refundIsUneroll == "ON") {
                _e(" also the user is unenrolled from associated course.", "eb-textdomain");
            }
            ?>
        </div>
        <?php
        $stmtHistNote   = ob_get_clean();
        return $stmtHistNote;
    }
}
