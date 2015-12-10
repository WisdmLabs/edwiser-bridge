<?php

/**
 * Sanitize a string destined to be a tooltip. Prevents XSS.
 *
 * @param string  $var
 * @return string
 */
function wp_sanitize_tooltip( $var ) {
	return wp_kses( html_entity_decode( $var ), array(
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'span'   => array(),
			'ul'     => array(),
			'li'     => array(),
			'ol'     => array(),
			'p'      => array(),
		) );
}

/**
 * Clean variables
 *
 * @param string  $var
 * @return string
 */
function wp_clean( $var ) {
	return sanitize_text_field( $var );
}
