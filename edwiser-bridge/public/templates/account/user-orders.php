<?php
/**
 * User account.
 *
 * @link       https://edwiser.org
 * @since      1.0.2
 * @deprecated 1.2.0 Use shortcode eb_user_account
 * @package    Edwiser Bridge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="eb-cph-wrapper">
	<div class="wdm-transaction-header">
		<h4 style=""><?php esc_html_e( 'Course Purchase History', 'eb-textdomain' ); ?></h4>
	</div>
	<table id="wdm_user_order_history" class="display">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Order ID', 'eb-textdomain' ); ?></th>
				<th><?php esc_html_e( 'Ordered Course', 'eb-textdomain' ); ?></th>
				<th><?php esc_html_e( 'Order Date', 'eb-textdomain' ); ?></th>
				<th><?php esc_html_e( 'Status', 'eb-textdomain' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $user_orders as $eb_order ) {
				?>
				<tr>
					<td><strong>#<?php echo esc_html( $eb_order['eb_order_id'] ); ?></strong></td>
					<?php
					if ( is_array( $eb_order['ordered_item'] ) ) {
						$ord_items = $eb_order['ordered_item'];
					} else {
						$ord_items = array( $eb_order['ordered_item'] );
					}
					$row = "<ul class='eb-user-order-courses'>";
					foreach ( $ord_items as $item ) {
						if ( get_the_title( $item ) === '' ) {
							$eb_title = esc_html__( 'Not Available', 'eb-textdomain' );
						} else {
							$eb_title = "<a href='" . get_permalink( $item ) . "'/>" . get_the_title( $item ) . '</a>';
						}
						$row .= "<li>$eb_title</li>";
					}
					$row .= '</ul>';
					?>
					<td><?php echo wp_kses( $row, \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() ); ?></td>
					<td><?php echo esc_html( $eb_order['date'] ); ?> </td>
					<td><?php esc_html_e( ucfirst( $eb_order['status'] ), 'eb-textdomain' ); // @codingStandardsIgnoreLine.?></td>
				</tr>
				<?php
			}
			do_action( 'eb_after_order_history' );
			?>
		</tbody>
	</table>
</div>
