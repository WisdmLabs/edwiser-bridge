        <div class="eb-cph-wrapper">
            <div class="wdm-transaction-header">
                <h4 style=""><?php _e('Course Purchase History', 'eb-textdomain');
        ?></h4>
            </div>
            <table id="wdm_user_order_history" class="display">
                <thead>
                    <tr>
                        <th><?php _e('Order ID', 'eb-textdomain');
        ?></th>
                        <th><?php _e('Ordered Course', 'eb-textdomain');
        ?></th>
                        <th><?php _e('Order Date', 'eb-textdomain');
        ?></th>
                        <th><?php _e('Status', 'eb-textdomain');
        ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($user_orders as $order) {
                        echo '<tr>';
                        echo '<td><strong>#'.$order['order_id'].'</strong></td>';
                        if (get_the_title($order['ordered_item']) == '') {
                            echo '<td>'.__('Not Available', 'eb-textdomain').'</td>';
                        } else {
                            echo '<td> <a href="'.get_permalink($order['ordered_item']).'"/>'.
                            get_the_title($order['ordered_item']).'</a> </td>';
                        }
                        echo '<td>'.$order['date'].'</td>';
                        echo '<td>'.ucfirst($order['status']).'</td>';
                        echo '</tr>';
                    }

                    do_action('eb_after_order_history');
                    ?>
                </tbody>
            </table>
        </div>
