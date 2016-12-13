<?php
/**
 * The template for displaying all single moodle courses.
 */
namespace app\wisdmlabs\edwiserBridge;

$eb_template = get_option('eb_template');
if (isset($eb_template['single_enable_right_sidebar']) && $eb_template['single_enable_right_sidebar'] === 'yes') {
    $single_enable_right_sidebar = true;
} else {
    $single_enable_right_sidebar = false;
}
$sidebar_id = isset($eb_template['single_right_sidebar']) ? $eb_template['single_right_sidebar'] : '';

$template_loader = new EbTemplateLoader(edwiserBridgeInstance()->getPluginName(), edwiserBridgeInstance()->getVersion());

?>

<?php get_header(); ?>

<?php $template_loader->wpGetTemplate('global/wrapper-start.php'); ?>

        <?php do_action('eb_before_single_course'); ?>
        <?php
        while (have_posts()) :
            the_post();
            $template_loader->wpGetTemplatePart('content-single', get_post_type());
            comments_template();
        endwhile;
        ?>
        <?php do_action('eb_after_single_course'); ?>

<?php $template_loader->wpGetTemplate('global/wrapper-end.php'); ?>

<?php

if ($single_enable_right_sidebar) {
?>
<div class="eb-siderbar-right">
    <?php if (is_active_sidebar($sidebar_id)) : ?>
    <aside id="secondary" class="sidebar widget-area" role="complementary">
        <?php dynamic_sidebar($sidebar_id); ?>
    </aside><!-- .sidebar .widget-area -->
    <?php endif; ?>
</div>
<?php
}
?>

<?php get_footer();
