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

// By default name of the array passed in template is args.

// Check price type , if it is set then show price for courses. If closed then don't do anything.
if ( 'eb_course' === $post->post_type && ( 'paid' === $args['course_price_type'] || 'free' === $args['course_price_type'] ) ) {
	?>
	<div class="wdm-price <?php echo wp_kses_post( $args['course_price_type'] ); ?>">
		<?php
			echo wp_kses_post( $args['course_price_formatted'], \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() );
		?>
	</div>
	<?php
}
