<?php
/**
 * The template for displaying moodle course archive page.
 */
 
/*
eb_only_courses, eb_courses_sidebar, eb_sidebar_courses, eb_sidebar_courses_sidebar.
*/

$eb_template = get_option('eb_template');
if (isset($eb_template['archive_enable_right_sidebar']) && $eb_template['archive_enable_right_sidebar'] === 'yes') {
    $eb_courses_sidebar = true;
} else {
    $eb_courses_sidebar = false;
}
$sidebar_id = isset($eb_template['archive_right_sidebar']) ? $eb_template['archive_right_sidebar'] : '';

//
$count = isset($eb_template['courses_per_row']) && is_numeric($eb_template['courses_per_row']) && $eb_template['courses_per_row'] < 5 ? (int) $eb_template['courses_per_row'] : 4;

$eb_width = !$eb_courses_sidebar ? '.eb-primary{width: 100% !important;}' : '';

//CSS to handle course grid.
$grid_css = '<style type="text/css">' . '.eb-course-col{width:' . (100/$count) .'%;}' . '.eb-course-col:nth-of-type(4' . $count . '+1){clear:left;}' . $eb_width . '</style>';
echo $grid_css;

$template_loader = new app\wisdmlabs\edwiserBridge\EbTemplateLoader(
    app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->getPluginName(),
    app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->getVersion()
);
?>

<?php get_header(); ?>

<?php $template_loader->wpGetTemplate('global/wrapper-start.php'); ?>

<?php if (apply_filters('eb_show_page_title', true)) : ?>

    <h1 class="page-title"><?php _e('Courses', 'eb-textdomain'); ?></h1>

<?php endif; ?>

        <?php
        if (have_posts()) {
            ?>
            <?php
            //do_action('woocommerce_before_shop_loop');
            // Start the Loop.
            while (have_posts()) :
                the_post();
                $template_loader->wpGetTemplatePart('content', get_post_type());
            // End the loop.
            endwhile;

            //Previous/next page navigation.
            the_posts_pagination(
                array(
                        'prev_text' => __('Previous page', 'eb-textdomain'),
                        'next_text' => __('Next page', 'eb-textdomain'),
                        'before_page_number' => '<span class="meta-nav screen-reader-text">'.
                        __('Page', 'eb-textdomain').' </span>',
                    )
            );
        } else {
            $template_loader->wpGetTemplatePart('content', 'none');
        }
        ?>

<?php $template_loader->wpGetTemplate('global/wrapper-end.php'); ?>

<?php
if ($eb_courses_sidebar) {
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

<?php
get_footer();
