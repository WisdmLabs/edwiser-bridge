<?php
/**
 * The template for displaying course archive page prices.
 *
 * @package Edwiser Bridge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $post;


$shortcode_attr = $args['shortcode_attr'];


if ( isset( $shortcode_attr['show_progress'] ) && $shortcode_attr['show_progress'] ) {
	echo wp_kses_post( $shortcode_attr['progress_btn_div'] );
}

