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
 * Order Meta.
 */
class Eb_Order_Meta {


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

	/**
	 * Contsructor.
	 *
	 * @param text $plugin_name plugin name.
	 * @param text $version plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}
	/**
	 * Meta boxes.
	 */
	public function add_eb_order_meta_boxes() {
		$status_hit = new Eb_Order_History_Meta( $this->plugin_name, $this->version );
		add_meta_box( 'eb_order_status_update_history_meta', __( 'Order status history', 'edwiser-bridge' ), array( $status_hit, 'add_order_status_history_meta' ), 'eb_order', 'side', 'default' );
		add_meta_box( 'eb_order_refund_meta', __( 'Refund order', 'edwiser-bridge' ), array( $this, 'add_order_refund_meta' ), 'eb_order', 'advanced', 'default' );
	}

	/**
	 * Refund meta
	 */
	public function add_order_refund_meta() {
		global $post;
		$refundable = get_post_meta( $post->ID, 'eb_transaction_id', true );
		if ( ! $refundable || empty( $refundable ) ) {
			esc_html_e( 'Refund not available for this order', 'edwiser-bridge' );
			return;
		}
		$currency       = \app\wisdmlabs\edwiserBridge\wdm_eb_get_current_paypal_currency_symb();
		$price          = $this->get_course_price( $post->ID );
		$refunds        = $this->get_orders_all_refund( $post->ID );
		$refunded_amt   = \app\wisdmlabs\edwiserBridge\wdm_eb_get_total_refund_amt( $refunds );
		$avl_refund_amt = $price - $refunded_amt;
		?>
		<div class="eb-order-refund-data">
			<?php $this->disp_refunds( $refunds ); ?>
			<table class="eb-order-refund-unenroll">
				<tbody>
					<?php do_action( 'eb_before_order_refund_meta' ); ?>
					<tr>
						<td>
							<?php esc_html_e( 'Suspend course enrollment?: ', 'edwiser-bridge' ); ?>
						</td>
						<td>
							<input type="checkbox" name="eb_order_meta_unenroll_user" id="eb_order_meta_unenroll_user" value="ON" />
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Purchase cost: ', 'edwiser-bridge' ); ?>
						</td>
						<td>
							<label class="eb-ord-cost"><?php echo esc_html( $currency . $price ); ?></label>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Amount already refunded: ', 'edwiser-bridge' ); ?>
						</td>
						<td>
							<label class="eb-ord-refunded-amt">- <?php echo esc_html( $currency . $refunded_amt ); ?></label>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Total available to refund: ', 'edwiser-bridge' ); ?>
						</td>
						<td>
							<label class="eb-ord-avlb-refund-amt"><?php echo esc_html( $currency . $avl_refund_amt ); ?></label>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Refund amount: ', 'edwiser-bridge' ); ?>
						</td>
						<td>
							<input type="text" id="eb_ord_refund_amt" min="0" max="<?php echo esc_html( $avl_refund_amt ); ?>" name="eb_ord_refund_amt" placeholder="0.00"/>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Reason for refund (optional): ', 'edwiser-bridge' ); ?>
						</td>
						<td>
							<input type="text" id="eb_order_refund_note" name="eb_order_refund_note" />
						</td>
					</tr>
					<?php do_action( 'eb_after_order_refund_meta' ); ?>
				</tbody>
			</table>
			<div class="eb-ord-refund-btn-cont">
				<?php do_action( 'eb_before_order_refund_meta_button' ); ?>
				<button type="button" class="button-primary" id="eb_order_refund_btn" name="eb_order_refund_btn" >
					<?php echo esc_html__( 'Refund', 'edwiser-bridge' ) . esc_html( ' ' . $currency . ' ' ); ?>
					<span id="eb-ord-refund-amt-btn-txt">0.00</span>
				</button>
				<?php
				do_action( 'eb_after_order_refund_meta_button' );
				wp_nonce_field( 'eb_order_refund_nons_field', 'eb_order_refund_nons' );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Current price.
	 *
	 * @param text $order_id order_id.
	 */
	private function get_course_price( $order_id ) {
		$order_data = get_post_meta( $order_id, 'eb_order_options', true );
		$price      = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $order_data, 'price', '0.00' );
		return (float) $price;
	}

	/**
	 * Current price.
	 *
	 * @param text $order_id order_id.
	 */
	public function get_orders_all_refund( $order_id ) {
		$refunds = get_post_meta( $order_id, 'eb_order_refund_hist', true );
		if ( ! is_array( $refunds ) ) {
			$refunds = array();
		}
		return $refunds;
	}

	/**
	 * Current price.
	 *
	 * @param text $refunds refunds.
	 */
	private function disp_refunds( $refunds ) {
		?>
		<ul class="eb-order-refund-hist-cont">
			<?php
			foreach ( $refunds as $refund ) {
				$refund_by = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $refund, 'by' );
				$time      = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $refund, 'time' );
				$amt       = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $refund, 'amt' );
				$currency  = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $refund, 'currency' );
				?>
				<li>
					<div class="eb-order-refund-hist-stmt"><?php esc_html__( 'Refunded by', 'edwiser-bridge' ) . printf( '%s ', esc_html( $refund_by ) ) . printf( ' on %s ', esc_attr( $time ) ); ?></div>
					<div class="eb-order-refund-hist-amt"><?php echo esc_html( "$currency$amt" ); ?></div>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}

	/**
	 * Get details of an order by order id.
	 *
	 * @since  1.0.0
	 *
	 * @param int $order_id id of an order.
	 */
	public function get_order_details( $order_id ) {
		// get order billing id & email.
		$order_data = get_post_meta( $order_id, 'eb_order_options', true );

		if ( ! is_array( $order_data ) ) {
			$order_data = array();
		}

		if ( isset( $order_data['buyer_id'] ) && ! empty( $order_data['buyer_id'] ) ) {
			$buyer_id_js_on_decoded = json_decode( $order_data['buyer_id'] );

			$buyer_id = $order_data['buyer_id'];
			if ( isset( $buyer_id_js_on_decoded->buyer_id ) && ! empty( $buyer_id_js_on_decoded->buyer_id ) ) {
				$buyer_id = $buyer_id_js_on_decoded->buyer_id;
			}
			$buyer_details = get_userdata( $buyer_id );
			$this->print_buyer_details( $buyer_details->data );
		} else {
			$this->print_buyer_details();
		}

		$this->print_product_details( $order_id, $order_data );
	}

	/**
	 * Get details of an buyer.
	 *
	 * @since  1.0.0
	 *
	 * @param int $buyer_details buyer_details.
	 */
	public function print_buyer_details( $buyer_details = '' ) {

		$user_id = 0;
		if ( isset( $buyer_details->ID ) && ! empty( $buyer_details->ID ) ) {
			$user_id = $buyer_details->ID;
		}

		?>
		<div class='eb-order-meta-byer-details'>
			<p>
				<strong><?php esc_html_e( 'Buyer Details: ', 'edwiser-bridge' ); ?></strong>
			</p>
			<?php
			if ( isset( $buyer_details->user_email ) && ! empty( $buyer_details->user_email ) ) {
				?>
				<p>
					<label><?php esc_html_e( 'Name: ', 'edwiser-bridge' ); ?></label>
					<?php echo esc_html( $buyer_details->user_login ); ?>
				</p>

				<p>

					<label><?php esc_html_e( 'Email: ', 'edwiser-bridge' ); ?></label>
					<?php echo esc_html( $buyer_details->user_email ); ?>
				</p>
				<?php
			} else {
				?>
				<p>
					<label><?php esc_html_e( 'Name: ', 'edwiser-bridge' ); ?></label>
					<!-- <input type="select" name="eb_order_options[eb_order_username]"> -->
					<div class='eb_order_meta_s2'>
						<select id="eb_order_username" name="eb_order_options[eb_order_username]" required>
						<?php
						$this->get_all_users( $user_id );
						?>
						</select>
					</div>
				</p>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * Get details of an prodct.
	 *
	 * @since  1.0.0
	 *
	 * @param int $order_id order_id.
	 * @param int $order_data order_data.
	 */
	private function print_product_details( $order_id, $order_data ) {
		$course_id = 0;
		if ( isset( $order_data['course_id'] ) && ! empty( $order_data['course_id'] ) ) {
			$course_id = $order_data['course_id'];
		}

		?>
		<div class='eb-order-meta-details'>
			<p>
				<strong><?php esc_html_e( 'Order Details: ', 'edwiser-bridge' ); ?></strong>
			</p>
			<p>
				<label><?php esc_html_e( 'Id: ', 'edwiser-bridge' ); ?></label>
				<?php echo esc_html( $order_id ); ?>
			</p>

			<?php
			if ( $course_id ) {
				?>

				<p>
					<label><?php esc_html_e( 'Course Name: ', 'edwiser-bridge' ); ?></label>
					<a href='<?php echo esc_html( get_permalink( $order_data['course_id'] ) ); ?>'>
						<?php echo esc_html( get_the_title( $course_id ) ); ?>
					</a>
				</p>

				<?php
			} else {
				?>
				<p>
					<label><?php esc_html_e( 'Course Name: ', 'edwiser-bridge' ); ?></label>
					<!-- <input type="text" name="eb_order_options[eb_order_course]"> -->
					<div class='eb_order_meta_s2'>
						<select id="eb_order_course" name="eb_order_options[eb_order_course]" required>
						<?php
						echo $this->get_all_courses( $course_id ); // @codingStandardsIgnoreLine
						?>
						</select>

					</div>
				</p>
				<?php
			}
			?>
			<p>
				<label>
					<?php esc_html_e( 'Date: ', 'edwiser-bridge' ); ?>
				</label>
				<?php echo get_the_date( 'Y-m-d H:i', $order_id ); ?>
			</p>
		</div>
		<?php
	}


	/**
	 * Function to get all users array.
	 *
	 * @param int $user_id user_id.
	 */
	public function get_all_users( $user_id = '' ) {
		$users = get_users();
		?>
		<option value='' disabled selected> Select User</option>
		<?php
		$selected = '';
		foreach ( $users as $user ) {
			if ( $user_id === $user->ID ) {
				$selected = 'selected';
			}
			?>

			<option value="<?php echo esc_attr( $user->ID ) . ' ' . esc_attr( $selected ); ?>" >
				<?php echo esc_attr( $user->user_login ); ?>
			</option>
			<?php
		}
	}

	/**
	 * Function to get list of all courses
	 *
	 * @param int $course_id course_id.
	 */
	public function get_all_courses( $course_id = '' ) {
		$course_args = array(
			'post_type'      => 'eb_course',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		$courses     = get_posts( $course_args );
		?>
		<option value='' disabled selected> Select Course </option>
		<?php
		foreach ( $courses as $course ) {
			$selected = '';
			if ( $course_id === $course->ID ) {
				$selected = 'selected';
			}
			?>
			<option value="<?php echo esc_attr( $course->ID ) . ' ' . esc_attr( $selected ); ?>">
				<?php echo esc_attr( $course->post_title ); ?>
			</option>
			<?php
		}
	}
}
