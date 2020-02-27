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
            'A learning account is linked to your profile.
        Use credentials given below while accessing your courses.',
            'eb-textdomain'
        )
    );
    ?>
</p>

<p><?php printf(__('Username: <strong>%s</strong>', 'eb-textdomain'), $args['username']); ?></p>

<p><?php printf(__('Password: <strong>%s</strong>', 'eb-textdomain'), $args['password']); ?></p>

<p>
    <?php
    printf(
        __(
            'To purchase and access more courses click here: <a href="%s">Courses</a>.',
            'eb-textdomain'
        ),
        site_url('/courses')
    );
    ?>
</p>

<?php
do_action('eb_email_footer');
