<?php
/**
 * The template for displaying all single moodle courses
 */

get_header(); ?>
	
	<div id="primary" class="content-area">
		<main id="content" role="main" class="site-main" style="overflow:auto;">

			<?php do_action( 'eb_before_single_course' ); ?>
			<?php while ( have_posts() ) : the_post();

				$plugin_template_loader     = new EB_Template_Loader( EB()->get_plugin_name(), EB()->get_version() );
				$plugin_template_loader->wp_get_template_part( 'content-single', get_post_type() );
				
				comments_template();

			endwhile; ?>
			<?php do_action( 'eb_after_single_course' ); ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
