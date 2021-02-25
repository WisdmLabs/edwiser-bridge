<?php
/**
 * Formatting FUnctions.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'wpClean' ) ) {
	/**
	 * DEPRECATED FUNCTION.
	 *
	 * Clean variables.
	 *
	 * @deprecated since 2.0.1 use wp_clean() insted
	 * @param string $var var.
	 *
	 * @return string
	 */
	function wpClean( $var ) {
		return sanitize_text_field( $var );
	}
}

if ( ! function_exists( 'wdm_edwiser_bridge_wp_clean' ) ) {
	/**
	 * Clean variables.
	 *
	 * @param string $var var.
	 *
	 * @return string
	 */
	function wdm_edwiser_bridge_wp_clean( $var ) {
		return sanitize_text_field( $var );
	}
}
