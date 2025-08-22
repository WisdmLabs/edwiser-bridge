<?php

/**
 * My courses.
 *
 * @link       https://edwiser.org
 * @package    Edwiser Bridge
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Helper: Check if a block exists in given content.
 *
 * @param array  $blocks     Parsed blocks.
 * @param string $block_name Full block name (namespace/block).
 * @return bool
 */
function eb_contains_block($blocks, $block_name)
{
	foreach ($blocks as $block) {
		if ($block['blockName'] === $block_name) {
			return true;
		}
		if (! empty($block['innerBlocks']) && eb_contains_block($block['innerBlocks'], $block_name)) {
			return true;
		}
	}
	return false;
}

// Get plugin options.
$eb_general_option   = get_option('eb_general');
$my_courses_page_id  = isset($eb_general_option['eb_my_courses_page_id']) ? intval($eb_general_option['eb_my_courses_page_id']) : 0;

// If a "My Courses" page is selected.
if ($my_courses_page_id) {
	$content = get_post($my_courses_page_id);

	if ($content) {
		$block_name = 'edwiser-bridge/my-courses';

		$has_block = has_block($block_name, $my_courses_page_id);

		// If has_block() failed, double-check using parse_blocks().
		if (! $has_block) {
			$has_block = eb_contains_block(parse_blocks($content->post_content), $block_name);
		}

		if ($has_block) {
			// Render Gutenberg blocks.
			echo do_blocks($content->post_content);
		} else {
			// Render as shortcode.
			echo do_shortcode($content->post_content);
		}
	}
} else {
	// Fallback to default shortcode.
	echo do_shortcode(
		'[eb_my_courses my_courses_wrapper_title="My Courses" recommended_courses_wrapper_title="Recommended Courses" number_of_recommended_courses="4"]'
	);
}
