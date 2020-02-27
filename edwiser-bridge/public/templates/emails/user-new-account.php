<?php
/**
 * New User Account Email Template.
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
            'Thanks for creating an account on %s. Your username is <strong>%s</strong>.',
            'eb-textdomain'
        ),
        get_bloginfo('name'),
        $args['username']
    );
    ?>
</p>

<p>
    <?php
    printf(
        __(
            'Your password has been automatically generated: <strong>%s</strong>',
            'eb-textdomain'
        ),
        $args['password']
    );
    ?>
</p>

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
