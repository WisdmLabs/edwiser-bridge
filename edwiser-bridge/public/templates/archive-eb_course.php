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
$eb_template  = get_option( 'eb_general' );
$attr         = isset( $attr ) ? $attr : array();

$template_loader = new \app\wisdmlabs\edwiserBridge\Eb_Template_Loader(
	\app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->get_plugin_name(),
	\app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->get_version()
);


/**
 * -------------------------------------
 * INTIALIZATION END
 *-------------------------------------
 */

\app\wisdmlabs\edwiserBridge\wdm_eb_get_header();

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
		<h1 class="page-title"><?php esc_html_e( 'Courses', 'edwiser-bridge' ); ?></h1>
		<?php

	endif;

	do_action( 'eb_archive_before_course_cards', $attr );

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
				'prev_text'          => '<span class="wdm-btn eb_primary_btn button button-primary et_pb_button et_pb_contact_submit">' . __( ' Prev', 'edwiser-bridge' ) . '</span>',
				'next_text'          => '<span class="wdm-btn eb_primary_btn button button-primary et_pb_button et_pb_contact_submit"> ' . __( 'Next ', 'edwiser-bridge' ) . '</span>',
				'before_page_number' => '<span class="meta-nav screen-reader-text">' .
				esc_html__( 'Page', 'edwiser-bridge' ) . ' </span>',
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

	// Here get_sidebar() method can be called for sidebar content.

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
\app\wisdmlabs\edwiserBridge\wdm_eb_get_footer();
