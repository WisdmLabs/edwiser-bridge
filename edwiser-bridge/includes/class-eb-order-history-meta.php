<?php
/**
 * This class defines all code necessary to manage user's course orders meta'.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class Eb_Order_History_Meta {

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
	public function add_order_status_history_meta() {
		global $post;
		$order_hist = get_post_meta($post->ID, 'eb_order_status_history', true);
		?>
		<div>
			<?php
			wp_nonce_field("eb_order_history_meta_nons", "eb_order_meta_nons");
			if (is_array($order_hist) && count($order_hist) > 0) {
				echo '<ul class="eb-sso-hist-note-wrap">';
				foreach ($order_hist as $history) {
					$this->get_history_tag($history);
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
	private function get_history_tag($ordHist)
	{
		$updatedBy = get_arr_value($ordHist, "by");
		$updatedOn = get_arr_value($ordHist, "time");
		$noteData  = get_arr_value($ordHist, "note", array());
		$note      = $this->create_note_msg($noteData);
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

	private function create_note_msg($noteData)
	{
		$type = get_arr_value($noteData, "type", "");
		$msg  = get_arr_value($noteData, "msg", "");
		$note = "";
		switch ($type) {
			case "status_update":
				$note = $this->get_status_update_msg($msg);
				break;
			case "refund":
				$note = $this->get_refund_note_msg($msg);
				break;
			case "new_order":
				$note = $this->get_new_order_note_msg($msg);
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
	private function get_status_update_msg($note)
	{
		$old_status   = get_arr_value($note, "old_status");
		$new_status   = get_arr_value($note, "new_status");
		$const_status = array(
			'pending'   => __('Pending', "eb-textdomain"),
			'completed' => __('Completed', "eb-textdomain"),
			'failed'    => __('Failed', "eb-textdomain"),
			'refunded'  => __('Refunded', "eb-textdomain"),
		);

		$user = get_userdata(get_current_user_id());

		$stat_old     = get_arr_value($const_status, $old_status);
		$stat_new     = get_arr_value($const_status, $new_status);
		$note_state   = sprintf(__("Order status changed from %s to %s.", "eb-textdomain"), $stat_old, $stat_new);

		if (empty($old_status)) {
			$note_state   = sprintf(__("New Order created by %s.", "eb-textdomain"), $user->user_login);
		}
		$note_state   = apply_filters("eb_order_history_disp_status_change_msg", $note_state, $note);
		return $note_state;
	}

	private function get_refund_note_msg($note)
	{
		// $currency        = get_arr_value($note, 'currency', getCurrentPayPalcurrencySymb());
		// $refundAmt       = get_arr_value($note, 'refund_amt', "0.00");
		$refund_note      = get_arr_value($note, 'refund_note');
		$refund_is_uneroll = get_arr_value($note, 'refund_uneroll_users');
		$unenroll_msg     = "";
		if ($refund_is_uneroll == "ON") {
			$unenroll_msg = __(" Also the user is unenrolled from associated course.", "eb-textdomain");
		}
		$hist_note = sprintf(__($refund_note." %s", "eb-textdomain"), $unenroll_msg);
		$hist_note = apply_filters("eb_order_history_disp_refund_msg", $hist_note, $note);
		return $hist_note;
	}

	private function get_new_order_note_msg($note)
	{
		$note = apply_filters("eb_order_history_disp_refund_msg", $note);
		return $note;
	}
}
