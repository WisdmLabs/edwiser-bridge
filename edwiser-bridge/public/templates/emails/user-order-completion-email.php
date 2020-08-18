<?php
/**
 * Order Completion Email Template.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php do_action('eb_email_header', $args['header']); ?>

<p><?php printf(__('Hi %s', 'eb-textdomain'), $args['first_name']); ?></p>

<p>
    <?php
    printf(
        __(
            'Thanks for purchasing %s course.',
            'eb-textdomain'
        ),
        '<strong>'.get_the_title($args['course_id']).'</strong>'
    );
    ?>
</p>

<p><?php printf(__('Your order with ID #%s completed successfully.', 'eb-textdomain'), $args['eb_order_id']);   // cahnges 1.4.7 ?></p>

<p>
    <?php
    printf(
        __(
            'You can access your account here: <a href="%s">User Account</a>.',
            'eb-textdomain'
        ),
        wdmUserAccountUrl()
    );
    ?>
</p>

<?php
do_action('eb_email_footer');
