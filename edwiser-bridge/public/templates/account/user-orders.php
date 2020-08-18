<div class="eb-cph-wrapper">
    <div class="wdm-transaction-header">
        <h4 style=""><?php _e('Course Purchase History', 'eb-textdomain');?></h4>
    </div>
    <table id="wdm_user_order_history" class="display">
        <thead>
            <tr>
                <th><?php _e('Order ID', 'eb-textdomain'); ?></th>
                <th><?php _e('Ordered Course', 'eb-textdomain'); ?></th>
                <th><?php _e('Order Date', 'eb-textdomain'); ?></th>
                <th><?php _e('Status', 'eb-textdomain'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($user_orders as $order) {
                ?>
                <tr>
                    <td><strong>#<?php echo $order['eb_order_id']; // changed 1.4.7 ?></strong></td>
                    <?php
                    if (is_array($order['ordered_item'])) {
                        $ordItems = $order['ordered_item'];
                    } else {
                        $ordItems = array($order['ordered_item']);
                    }
                    $row = "<ul class='eb-user-order-courses'>";
                    foreach ($ordItems as $item) {
                        if (get_the_title($item) == '') {
                            $title = __('Not Available', 'eb-textdomain');
                        } else {
                            $title = "<a href='" . get_permalink($item) . "'/>" . get_the_title($item) . "</a>";
                        }
                        $row .= "<li>$title</li>";
                    }
                    $row .= "</ul>";
                    ?>
                    <td><?php echo $row; ?></td>
                    <td><?php echo $order['date']; ?> </td>
                    <td><?php _e(ucfirst($order['status']), 'eb-textdomain'); ?></td>
                </tr>
                <?php
            }
            do_action('eb_after_order_history');
            ?>
        </tbody>
    </table>
</div>
