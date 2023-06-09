<?php
/**
 * The template for displaying all single moodle courses.
 *
 * @package Edwiser Bridge.
 */

/**
 * -------------------------------------
 * INTIALIZATION START
 * Do not repalce these inititializations
 * --------------------------------------
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wrapper_args = array();

$eb_template = get_option( 'eb_template' );
if ( isset( $eb_template['single_enable_right_sidebar'] ) && 'yes' === $eb_template['single_enable_right_sidebar'] ) {
	$wrapper_args['enable_right_sidebar'] = true;
	$wrapper_args['parentcss']            = '';
} else {
	$wrapper_args['enable_right_sidebar'] = false;
	$wrapper_args['parentcss']            = 'width:100%;';
}
$wrapper_args['sidebar_id'] = isset( $eb_template['single_right_sidebar'] ) ? $eb_template['single_right_sidebar'] : '';

$template_loader = new Eb_Template_Loader(
	edwiser_bridge_instance()->get_plugin_name(),
	edwiser_bridge_instance()->get_version()
);

/*
 * -------------------------------------
 * INTIALIZATION END
 * --------------------------------------
 **/

\app\wisdmlabs\edwiserBridge\wdm_eb_get_header();


/*
 *-------------------------------------
 * Content Wrapper replace this with your theme wrapper i.e comment this
 * and add your archive.php files container div, if archive.php is not present
 * then check template heirarchy here https://developer.WordPress.org/themes/basics/template-hierarchy/#examples
 *--------------------------------------
 */

$template_loader->wp_get_template( 'global/wrapper-start.php', $wrapper_args );

/*
 * -------------------------------------
 * CONTENT START
 * --------------------------------------
 **/

do_action( 'eb_before_single_course' );

$eb_shrtcode_wrapper = new Eb_Shortcode_My_Courses();

while ( have_posts() ) :
	the_post();
	$template_loader->wp_get_template_part( 'content-single', get_post_type() );

	$eb_shrtcode_wrapper->generate_recommended_courses();

	\app\wisdmlabs\edwiserBridge\wdm_eb_get_comments();

endwhile;

// End of the single course page.
do_action( 'eb_after_single_course' );


// Use this Hook to add sidebar container.
do_action( 'eb_archive_before_sidebar', $wrapper_args );

\app\wisdmlabs\edwiserBridge\wdm_eb_get_sidebar();


// Use this Hook to close sidebar containers.
do_action( 'eb_archive_after_sidebar', $wrapper_args );

/*
 * -------------------------------------
 * CONTENT END
 * --------------------------------------
 **/

/*
 * -------------------------------------
 * Content Wrapper replace this with your theme wrapper end section
 * i.e comment this and add your archive.php files container div end section,
 * if archive.php is not present then check template heirarchy
 * here https://developer.WordPress.org/themes/basics/template-hierarchy/#examples
 * --------------------------------------
 **/


$template_loader->wp_get_template( 'global/wrapper-end.php', $wrapper_args );

\app\wisdmlabs\edwiserBridge\wdm_eb_get_footer();
