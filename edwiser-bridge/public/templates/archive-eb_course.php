<?php
/**
 * The template for displaying moodle course archive page.
 */
$wrapper_args = array();

$eb_template = get_option('eb_template');
if (isset($eb_template['archive_enable_right_sidebar']) && $eb_template['archive_enable_right_sidebar'] === 'yes') {
    $wrapper_args['enable_right_sidebar'] = true;
    $wrapper_args['parentcss'] = '';
} else {
    $wrapper_args['enable_right_sidebar'] = false;
    $wrapper_args['parentcss'] = 'width:100%;';
}

$wrapper_args['sidebar_id'] = isset($eb_template['archive_right_sidebar']) ? $eb_template['archive_right_sidebar'] : '';

//
$count = isset($eb_template['courses_per_row']) && is_numeric($eb_template['courses_per_row']) && $eb_template['courses_per_row'] < 5 ? (int) $eb_template['courses_per_row'] : 4;

//CSS to handle course grid.
$grid_css = '<style type="text/css">' . '.eb-course-col{width:' . (100/$count) .'%;}' . '.eb-course-col:nth-of-type(4' . $count . '+1){clear:left;}</style>';
echo $grid_css;

$template_loader = new app\wisdmlabs\edwiserBridge\EbTemplateLoader(
    app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->getPluginName(),
    app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->getVersion()
);
?>

<?php get_header(); ?>

<?php $template_loader->wpGetTemplate('global/wrapper-start.php', $wrapper_args); ?>

<?php if (apply_filters('eb_show_page_title', true)) : ?>

    <h1 class="page-title"><?php _e('Courses', 'eb-textdomain'); ?></h1>

<?php endif; ?>

        <?php
        if (have_posts()) {
            ?>
            <?php
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

<?php $template_loader->wpGetTemplate('global/wrapper-end.php', $wrapper_args); ?>

<?php
get_footer();
