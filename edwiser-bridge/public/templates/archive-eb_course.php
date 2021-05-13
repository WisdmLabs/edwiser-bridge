<?php
/**
 * The template for displaying moodle course archive page.
 *
 * @package Edwiser Bridge.
 */

/**
 * -------------------------------------
 * INTIALIZATION START
 * Do not repalce these inititializations
 * --------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wrapper_args = array();

$eb_template = get_option( 'eb_general' );

$count = isset( $eb_template['courses_per_row'] ) && is_numeric( $eb_template['courses_per_row'] ) && $eb_template['courses_per_row'] < 5 ? (int) $eb_template['courses_per_row'] : 4;

// CSS to handle course grid.
/*echo '<style type="text/css"> .eb-course-col{width:' . ( 100 / esc_html( $count ) ) . '%;}'
. '.eb-course-col:nth-of-type(' . esc_html( $count ) . 'n+1){clear:left;}</style>';*/

$template_loader = new \app\wisdmlabs\edwiserBridge\EbTemplateLoader(
	\app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->get_plugin_name(),
	\app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->get_version()
);


/**
 * -------------------------------------
 * INTIALIZATION END
 *-------------------------------------
 */

get_header();

	/*
	 * -------------------------------------
	 * Content Wrapper replace this with your theme wrapper i.e comment this and add your archive.php files container div, if archive.php is not present then check template heirarchy here https://developer.WordPress.org/themes/basics/template-hierarchy/#examples
	 * --------------------------------------
	 **/

	$template_loader->wp_get_template( 'global/wrapper-start.php', $wrapper_args );


	/*
	 * -------------------------------------
	 * CONTENT START
	 * --------------------------------------
	 */


	do_action( 'eb_archive_before_content', $wrapper_args );
?>

	<?php if ( apply_filters( 'eb_show_page_title', true ) ) : ?>
		<h1 class="page-title"><?php esc_html_e( 'Courses', 'eb-textdomain' ); ?></h1>
	<?php endif; ?>


	<?php
	if ( have_posts() ) {
		?>
		<div class="eb_course_cards_wrap">
			<?php
			// Start the Loop.
			while ( have_posts() ) :
				the_post();
				$template_loader->wp_get_template_part( 'content', get_post_type() );
				// End the loop.
			endwhile;
			?>
		</div>
		<?php
		// Previous/next page navigation.
		the_posts_pagination(
			array(
				'prev_text'          => '< ' . __( ' Prev', 'eb-textdomain' ),
				'next_text'          => __( 'Next ', 'eb-textdomain' ) . ' >',
				'before_page_number' => '<span class="meta-nav screen-reader-text">' .
				esc_html__( 'Page', 'eb-textdomain' ) . ' </span>',
			)
		);
	} else {
		$template_loader->wp_get_template_part( 'content', 'none' );
	}

	/*
	 * Edwiser hook after content.
	 * Used mainly for by default compatibility with some themes.
	 */
	do_action( 'eb_archive_after_content', $wrapper_args );

	/*// Use this Hook to add sidebar container.
	do_action( 'eb_archive_before_sidebar', $wrapper_args );

	get_sidebar();

	// Use this Hook to close sidebar containers.
	do_action( 'eb_archive_after_sidebar', $wrapper_args );*/

	/*
	 * -------------------------------------
	 * CONTENT END
	 * --------------------------------------
	 **/


	/*
	 * -------------------------------------
	 * Content Wrapper replace this with your theme wrapper end section i.e comment this and add your archive.php files container div end section, if archive.php is not present then check template heirarchy here https://developer.WordPress.org/themes/basics/template-hierarchy/#examples
	 *--------------------------------------
	 */

	$template_loader->wp_get_template( 'global/wrapper-end.php', $wrapper_args );
	?>
<?php
get_footer();
