<?php
/**
 * The template for displaying all single moodle courses.
 */

namespace app\wisdmlabs\edwiserBridge;

get_header(); ?>
	<div id="primary" class="content-area">
		<main id="content" role="main" class="site-main" style="overflow:auto;">

			<?php do_action('eb_before_single_course'); ?>
			<?php while (have_posts()) :
                the_post();

                $plugin_template_loader = new EbTemplateLoader(
                    edwiserBridgeInstance()->getPluginName(),
                    edwiserBridgeInstance()->getVersion()
                );
                $plugin_template_loader->wpGetTemplatePart('content-single', get_post_type());

                comments_template();

endwhile; ?>
			<?php do_action('eb_after_single_course'); ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer();
