<?php
/**
 * The template for displaying moodle course archive page.
 */

get_header(); ?>

	<section id="primary" class="content-area" style="overflow:auto; padding:15px;">
		<main id="main" class="site-main" role="main">

		<?php 
			// our template loader 
			$plugin_template_loader     = new EB_Template_Loader( EB()->get_plugin_name(), EB()->get_version() );
		?>

		<?php do_action( 'eb_before_course_archive' ); ?>

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<?php
					echo '<h1 class="page-title">Courses</h1>';
				?>
			</header><!-- .page-header -->

			<?php

			// Start the Loop.
			while ( have_posts() ) : the_post();
				$plugin_template_loader->wp_get_template_part( 'content', get_post_type() );

			// End the loop.
			endwhile;

			// Previous/next page navigation.
			the_posts_pagination( array(
				'prev_text'          => __( 'Previous page', 'eb-textdomain' ),
				'next_text'          => __( 'Next page', 'eb-textdomain' ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'eb-textdomain' ) . ' </span>',
			) );

		// If no content, include the "No posts found" template.
		else :
			$plugin_template_loader->wp_get_template_part( 'content', 'none' );

		endif;
		?>

		<?php do_action( 'eb_after_course_archive' ); ?>

		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer(); ?>
