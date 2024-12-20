<?php
/**
 * This class defines all code necessary to manage user's course orders meta'.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * History meta.
 */
class Eb_Order_History_Meta {

	/**
	 * Plugin name.
	 *
	 * @since    1.0.0
	 *
	 * @var string plugin name.
	 */
	private $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @since    1.0.0
	 *
	 * @var string plugin version.
	 */
	private $version;

	/**
	 * COntrsuctor.
	 *
	 * @param int $plugin_name plugin_name.
	 * @param int $version version.
	 */
	public function __construct( $plugin_name, $version ) {
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
		$order_hist = get_post_meta( $post->ID, 'eb_order_status_history', true );
		?>
		<div>
			<?php
			wp_nonce_field( 'eb_order_history_meta_nons', 'eb_order_meta_nons' );
			if ( is_array( $order_hist ) && count( $order_hist ) > 0 ) {
				echo '<ul class="eb-sso-hist-note-wrap">';
				foreach ( $order_hist as $history ) {
					$this->get_history_tag( $history );
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
	 * @param type $ord_hist array of the order history element.
	 */
	private function get_history_tag( $ord_hist ) {
		$updated_by = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $ord_hist, 'by' );
		$updated_on = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $ord_hist, 'time' );
		$note_data  = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $ord_hist, 'note', array() );
		$note       = $this->create_note_msg( $note_data );
		?>
		<li>
			<div class="eb-sso-hist-note">
				<?php echo wp_kses_post( $note ); ?>
			</div>
			<div class="eb-sso-hist-by">
				<?php esc_html__( 'added by  ', 'edwiser-bridge' ) . printf( '%s on %s.', esc_html( $updated_by ), esc_html( $updated_on ) ); ?>
			</div>
		</li>
		<?php
	}

	/**
	 * Create note msg.
	 *
	 * @param type $note_data note_data.
	 */
	private function create_note_msg( $note_data ) {
		$type = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $note_data, 'type', '' );
		$msg  = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $note_data, 'msg', '' );
		$note = '';
		switch ( $type ) {
			case 'status_update':
				$note = $this->get_status_update_msg( $msg );
				break;
			case 'refund':
				$note = $this->get_refund_note_msg( $msg );
				break;
			case 'new_order':
				$note = $this->get_new_order_note_msg( $msg );
				break;
			default:
				$note = apply_filters( 'eb_order_history_meta_type_default', $msg, $type );
				break;
		}
		return $note;
	}

	/**
	 * Provides the functionality to create the post update status statement.
	 *
	 * @since 1.3.0
	 * @param type $note note.
	 */
	private function get_status_update_msg( $note ) {
		$old_status   = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $note, 'old_status' );
		$new_status   = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $note, 'new_status' );
		$const_status = array(
			'pending'   => __( 'Pending', 'edwiser-bridge' ),
			'completed' => __( 'Completed', 'edwiser-bridge' ),
			'failed'    => __( 'Failed', 'edwiser-bridge' ),
			'refunded'  => __( 'Refunded', 'edwiser-bridge' ),
		);

		$user = get_userdata( get_current_user_id() );

		$stat_old   = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $const_status, $old_status );
		$stat_new   = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $const_status, $new_status );
		$note_state = esc_html__( 'Order status changed from ', 'edwiser-bridge' ) . sprintf( '%s .', $stat_old ) . esc_html__( ' to ', 'edwiser-bridge' ) . sprintf( ' %s.', $stat_new );

		if ( empty( $old_status ) ) {
			$note_state = esc_html__( 'New Order created by ', 'edwiser-bridge' ) . sprintf( '%s.', $user->user_login );
		}
		$note_state = apply_filters( 'eb_order_history_disp_status_change_msg', $note_state, $note );
		return $note_state;
	}

	/**
	 * Refund msg.
	 *
	 * @param type $note note.
	 */
	private function get_refund_note_msg( $note ) {
		$refund_note       = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $note, 'refund_note' );
		$refund_is_uneroll = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $note, 'refund_uneroll_users' );
		$unenroll_msg      = '';
		if ( 'ON' === $refund_is_uneroll ) {
			$unenroll_msg = esc_html__( ' Also the user is unenrolled from associated course.', 'edwiser-bridge' );
		}
		$hist_note = esc_html( $refund_note ) . sprintf( '%s', $unenroll_msg );
		$hist_note = apply_filters( 'eb_order_history_disp_refund_msg', $hist_note, $note );
		return $hist_note;
	}

	/**
	 * New order msg.
	 *
	 * @param type $note note.
	 */
	private function get_new_order_note_msg( $note ) {
		$note = apply_filters( 'eb_order_history_disp_refund_msg', $note );
		return $note;
	}
}
