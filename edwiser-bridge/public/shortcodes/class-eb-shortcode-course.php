<?php
/**
 * Shortcode eb_course
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 * @package    Edwiser bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * COurse.
 */
class Eb_Shortcode_Course {

	/**
	 * Get the shortcode content.
	 *
	 * @since  1.2.0
	 *
	 * @param array $atts atts.
	 *
	 * @return string
	 */
	public static function get( $atts ) {
		return Eb_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 *
	 * @since  1.2.0
	 *
	 * @param array $atts attas.
	 */
	public static function output( $atts ) {
		$atts = shortcode_atts(
			apply_filters(
				'eb_output_course_defaults',
				array(
					'id' => '',
				)
			),
			$atts
		);

		// Course id required.
		if ( ! isset( $atts['id'] ) || ! is_numeric( $atts['id'] ) ) {
			return;
		}

		$atts['post_type']   = 'eb_course';
		$atts['post_status'] = 'publish';
		$atts['p']           = $atts['id'];

		$courses = new \WP_Query( $atts );

		// Course not found.
		if ( 1 !== $courses->post_count ) {
			return;
		}

		// Show single course.
		do_action( 'eb_before_single_course' );
		while ( $courses->have_posts() ) :
			$courses->the_post();

			$template_loader = new Eb_Template_Loader(
				edwiser_bridge_instance()->get_plugin_name(),
				edwiser_bridge_instance()->get_version()
			);
			$template_loader->wp_get_template_part( 'content-single', 'eb_course' );

			$courses->comments_template();
		endwhile;
		do_action( 'eb_after_single_course' );
	}
}
