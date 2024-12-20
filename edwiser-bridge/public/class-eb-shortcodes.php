<?php
/**
 * The file that defines the shortcodes used in plugin.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Shortcode.
 */
class Eb_Shortcodes {

	/**
	 * Init shortcodes
	 */
	public static function init() {
		// Define shortcodes.
		$shortcodes = array(
			'eb_user_account' => __CLASS__ . '::user_account',
			'eb_courses'      => __CLASS__ . '::courses',
			'eb_course'       => __CLASS__ . '::course',
			'eb_my_courses'   => __CLASS__ . '::my_courses',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper
	 *
	 * @since  1.0.0
	 * @param mixed $function fun.
	 * @param array $atts     (default: array()).
	 * @param array $wrapper     wrapper.
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'edwiser-bridge',
			'before' => null,
			'after'  => null,
		)
	) {

		ob_start();
		echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : esc_attr( $wrapper['before'] );
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : esc_attr( $wrapper['after'] );

		return ob_get_clean();
	}

	/**
	 * User account shortcode.
	 *
	 * @since  1.0.0
	 * @param mixed $atts atts.
	 * @return string
	 */
	public static function user_account( $atts ) {
		return self::shortcode_wrapper( array( '\app\wisdmlabs\edwiserBridge\Eb_Shortcode_User_Account', 'output' ), $atts );
	}

	/**
	 * Courses shortcode, display courses.
	 *
	 * @since  1.2.0
	 * @param mixed $atts atts.
	 * @return courses
	 */
	public static function courses( $atts ) {
		return self::shortcode_wrapper( array( '\app\wisdmlabs\edwiserBridge\Eb_Shortcode_Courses', 'output' ), $atts );
	}

	/**
	 * Course shortcode, displays single course.
	 *
	 * @since  1.2.0
	 * @param mixed $atts atts.
	 * @return course
	 */
	public static function course( $atts ) {
		return self::shortcode_wrapper( array( '\app\wisdmlabs\edwiserBridge\Eb_Shortcode_Course', 'output' ), $atts );
	}

	/**
	 * Eb_my_courses shortcode, shows courses belonging to a user.
	 *
	 * @since  1.2.0
	 * @param mixed $atts atts.
	 * @return courses
	 */
	public static function my_courses( $atts ) {
		return self::shortcode_wrapper( array( '\app\wisdmlabs\edwiserBridge\Eb_Shortcode_My_Courses', 'output' ), $atts );
	}
}
