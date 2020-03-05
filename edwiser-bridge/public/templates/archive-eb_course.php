<?php
/**
 * The template for displaying moodle course archive page.
 */
$wrapper_args = array();

$eb_template = get_option('eb_general');

$count = isset($eb_template['courses_per_row']) && is_numeric($eb_template['courses_per_row']) && $eb_template['courses_per_row'] < 5 ? (int) $eb_template['courses_per_row'] : 4;

//CSS to handle course grid.
echo '<style type="text/css">'.'.eb-course-col{width:'.(100 / $count).'%;}'
.'.eb-course-col:nth-of-type('.$count.'n+1){clear:left;}</style>';

$template_loader = new app\wisdmlabs\edwiserBridge\EbTemplateLoader(
    app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->getPluginName(),
    app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->getVersion()
);
?>

<?php get_header(); ?>
<div class="eb-archive-container">
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

    <?php
    $template_loader->wpGetTemplate('global/wrapper-end.php', $wrapper_args);
    ?>
    <?php
    if (file_exists(get_template_directory_uri().'/sidebar.php')) {
        get_sidebar();
    }
    ?>
</div>
<?php
get_footer();
