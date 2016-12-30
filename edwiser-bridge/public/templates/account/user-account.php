<div class="wdm-transaction-header">
    <h2 style=""><?php _e('Course Purchase History', 'eb-textdomain'); ?></h2>

    <p>
        <?php
        printf(
            __('Hey %s (not %s? %s). From here you can check the courses you have purchased and access them.', 'eb-textdomain'),
            '<strong>' . $current_user->user_login . '</strong>',
            '<strong>' . $current_user->user_login . '</strong>',
            '<a href="'. esc_url(wp_logout_url(get_permalink())) . '">' . __('Sign out', 'eb-textdomain') . '</a>'
        );
        ?>
    </p>

</div>
<table id="wdm_user_order_history" class="display">
    <thead>
        <tr>
            <th><?php _e('Order ID', 'eb-textdomain'); ?></th>
            <th><?php _e('Ordered Course', 'eb-textdomain'); ?></th>
            <th><?php _e('Billing Email', 'eb-textdomain'); ?></th>
            <th><?php _e('Amount Paid', 'eb-textdomain'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($user_orders as $order) {
            echo '<tr>';
            echo '<td><strong>#'.$order['order_id'].'</strong></td>';
            if (get_the_title($order['ordered_item']) == '') {
                echo '<td>' . __('Not Available', 'eb-textdomain') . '</td>';
            } else {
                echo '<td>
        <a href="'.get_permalink($order['ordered_item']).'"/>'.
                get_the_title($order['ordered_item']).
                '</a>
    </td>';
            }
            echo '<td>'.$order['billing_email'].'</td>';
            if ($order['amount_paid'] > 0) {
                echo '<td><strong>'.$order['currency'].''.$order['amount_paid'].'</strong></td>';
            } else {
                echo '<td><strong>-</strong></td>';
            }
            echo '</tr>';
        }

        do_action('eb_after_order_history');
        ?>
    </tbody>
</table>
