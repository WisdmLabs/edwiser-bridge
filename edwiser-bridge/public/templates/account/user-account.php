<div class="eb-user-profile" >

    <?php
    if (isset($_GET['eb_action']) && $_GET['eb_action'] === 'edit-profile') {
        $template_loader->wpGetTemplate(
            'account/edit-user-profile.php',
            array(
                'user_avatar' => $user_avatar,
                'user' => $user,
                'user_meta' => $user_meta,
                'enrolled_courses' => $enrolled_courses,
                'template_loader' => $template_loader,
            )
        );
    } else {
        ?>
        <section class="eb-user-info">
            <aside class="eb-user-picture">
                <?php echo $user_avatar;
        ?>
            </aside>
            <div class="eb-user-data">
                <div>
                    <?php
                    printf(esc_attr__('Hello %s%s%s (not %2$s? %sSign out%s)', 'eb-textdomain'), '<strong>', esc_html($user->display_name), '</strong>', '<a href="'.esc_url(wp_logout_url(get_permalink())).'">', '</a>');
        ?>
                </div>
            </div>

            <div class="eb-edit-profile" >
                <a href="<?php echo esc_url(add_query_arg('eb_action', 'edit-profile', get_permalink()));
        ?>" class="wdm-btn">
                    <?php _e('Edit Profile', 'eb-textdomain');
        ?>
                </a>
            </div>

        </section>

        <section class="eb-user-courses">
            <div class="course-heading" >
                <span><?php _e('S.No.', 'eb-textdomain');
        ?></span>
                <span><?php _e('Enrolled Courses', 'eb-textdomain');
        ?></span>
            </div>
            <div class="eb-course-data">
                <?php
                if (!empty($enrolled_courses)) {
                    foreach ($enrolled_courses as $key => $course) {
                        echo '<div class="eb-course-section course_'.$course->ID.'">';
                        echo '<div>'.($key + 1).'. </div>';
                        echo '<div><a href="'.get_the_permalink($course->ID).'">'.$course->post_title.'</a></div>';
                        echo app\wisdmlabs\edwiserBridge\EBPaymentManager::accessCourseButton($course->ID);
                        echo '</div>';
                    }
                } else {
                    ?>
                    <p class="eb-no-course">
                        <?php
                        printf(
                            __(
                                'Looks like you are not enrolled in any course, get your first course %s',
                                'eb-textdomain'
                            ),
                            '<a href="'.esc_url(site_url('/courses')).'">'.__('here', 'eb-textdomain').'</a>.'
                        );
                    ?>
                    </p>
                    <?php
                }
        ?>
            </div>
        </section>

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


        <?php
    }
    ?>
</div>
