<style type="text/css">
    .eb-page-header{
        border-top: 4px solid #1a1a1a;
        margin: 0 7.6923% 3.5em;
        padding-top: 1.75em;
        display: block;
        box-sizing: inherit;

        margin-right: 0;
        margin-left: 0;

        padding-left: 1em;
    }

    .eb_courses_sidebar{
        float: left;
        margin-right: -100%;
        width: 70%;
    }

    @media (max-width:480px){
        .eb-content-area{
            margin: 0 auto;
            float: none;
            width: 90%;
        }
    }
</style>
<?php

/*
eb_only_courses, eb_courses_sidebar, eb_sidebar_courses, eb_sidebar_courses_sidebar.

*/
$eb_template = get_option('eb_template');
if (isset($eb_template['enable_right_sidebar']) && $eb_template['enable_right_sidebar'] === 'yes') {
    $eb_courses_sidebar = true;
} else {
    $eb_courses_sidebar = false;
}



$plugin_template_loader = new app\wisdmlabs\edwiserBridge\EbTemplateLoader(
    app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->getPluginName(),
    app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->getVersion()
);
?>


<?php
/**
 * The template for displaying moodle course archive page.
 */
get_header();
?>

<div id="eb-primary" class="eb-content-area <?php echo $eb_courses_sidebar ? 'eb_courses_sidebar' : ''; ?>">
    <main id="eb-main" class="eb-site-main" role="eb-main">
        <?php
        if (have_posts()) {
            ?>

            <header class="eb-page-header">
                <?php
                echo '<h1 class="page-title">Courses</h1>';
            ?>
            </header><!-- .page-header -->

            <?php
            // Start the Loop.
            while (have_posts()) :
                the_post();
                $plugin_template_loader->wpGetTemplatePart('content', get_post_type());
            // End the loop.
            endwhile;

            // Previous/next page navigation.
            the_posts_pagination(
                array(
                        'prev_text' => __('Previous page', 'eb-textdomain'),
                        'next_text' => __('Next page', 'eb-textdomain'),
                        'before_page_number' => '<span class="meta-nav screen-reader-text">'.
                        __('Page', 'eb-textdomain').' </span>',
                    )
            );

            // If no content, include the "No posts found" template.
        } else {
            $plugin_template_loader->wpGetTemplatePart('content', 'none');
        }
        ?>
    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php
if ($eb_courses_sidebar) {
?>
<div class="eb-siderbar-right">
    <?php get_sidebar(); ?>
</div>
<?php
}
?>

<br style="clear: both;" />
<?php
get_footer();
