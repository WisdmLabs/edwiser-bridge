<?php

/**
 * My courses.
 *
 * @link       https://edwiser.org
 * @since      1.0.2
 * @deprecated 1.2.0 Use shortcode eb_user_account
 * @package    Edwiser Bridge.
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
// get selected my courses page content.
$eb_general_option = get_option('eb_general');
$eb_gutenberg_pages = get_option('eb_gutenberg_pages', array());

// If page is selected then show its content.
if ($eb_gutenberg_pages['my_courses'] === $eb_general_option['eb_my_courses_page_id']) {
	$content = get_post($eb_general_option['eb_my_courses_page_id']);
	echo do_blocks($content->post_content);
} elseif (isset($eb_general_option['eb_my_courses_page_id']) && ! empty($eb_general_option['eb_my_courses_page_id'])) {
	$content = get_post($eb_general_option['eb_my_courses_page_id']);
	echo do_shortcode($content->post_content);
} else {
	// echo the default shortcode.
	echo do_shortcode('[eb_my_courses my_courses_wrapper_title="My Courses" recommended_courses_wrapper_title="Recommended Courses" number_of_recommended_courses="4" ]');
}
