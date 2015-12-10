<?php

/**
 * The file that defines the shortcodes used in plugin.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
class EB_Shortcodes {

	/**
	 * Init shortcodes
	 */
	public static function init() {
		// Define shortcodes
		$shortcodes = array(
			'eb_user_account'    => __CLASS__ . '::user_account',
			'eb_user_profile'	 => __CLASS__ . '::user_profile'
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper
	 *
	 * @since  1.0.0
	 * @param mixed   $function
	 * @param array   $atts     (default: array())
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts    = array(),
		$wrapper = array(
			'class'  => '',
			'before' => null,
			'after'  => null
		)
	) {
		ob_start();

		$before = empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		$after  = empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		echo $before;
		call_user_func( $function, $atts );
		echo $after;

		return ob_get_clean();
	}

	/**
	 * user account shortcode.
	 *
	 * @since  1.0.0
	 * @param mixed   $atts
	 * @return string
	 */
	public static function user_account( $atts ) {
		return self::shortcode_wrapper( array( 'EB_Shortcode_User_Account', 'output' ), $atts );
	}

	/**
	 * user profile shortcode, display user details & courses on one page.
	 *
	 * @since  1.0.2
	 * @param mixed   $atts
	 * @return string
	 */
	public static function user_profile( $atts ) {
		return self::shortcode_wrapper( array( 'EB_Shortcode_User_Profile', 'output' ), $atts );
	}
}
