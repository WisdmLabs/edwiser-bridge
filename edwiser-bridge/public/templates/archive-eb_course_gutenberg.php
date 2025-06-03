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

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

$wrapper_args = array();
$eb_template  = get_option('eb_general');
$attr         = isset($attr) ? $attr : array();

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

// $template_loader->wp_get_template('global/wrapper-start.php', $wrapper_args);


/*
	 * -------------------------------------
	 * CONTENT START
	 * --------------------------------------
	 */

?>
<main class="eb-archive-content" style="margin-top: 60px; margin-bottom: 60px; width: 100%">
	<?php


	do_action('eb_archive_before_content', $wrapper_args);

	do_action('eb_archive_before_course_cards', $attr);
	$gutenberg_pages = get_option('eb_gutenberg_pages', array());
	$eb_gutenberg_page_content = $gutenberg_pages['all_courses'];
	$eb_gutenberg_page = get_post($eb_gutenberg_page_content);
	if ($eb_gutenberg_page && !is_wp_error($eb_gutenberg_page)) {
		echo apply_filters('the_content', $eb_gutenberg_page->post_content);
	} else {
		$template_loader->wp_get_template_part('content', 'none');
	}

	/*
	 * Edwiser hook after content.
	 * Used mainly for by default compatibility with some themes.
	 */
	do_action('eb_archive_after_content', $wrapper_args);

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
	?>
</main>
<?php
// $template_loader->wp_get_template('global/wrapper-end.php', $wrapper_args);
?>
<?php
\app\wisdmlabs\edwiserBridge\wdm_eb_get_footer();
