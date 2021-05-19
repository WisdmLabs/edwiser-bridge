<?php
/**
 * Handles frontend form submissions.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Theme compat.
 */
class Eb_Theme_Compatibility {

	/**
	 * Cpmati.
	 *
	 * @param text $wrapper_args wrapper_args.
	 */
	public function eb_content_start_theme_compatibility( $wrapper_args ) {
		$template = get_option( 'template' );

		switch ( $template ) {
			case 'Divi':
				echo '<div id="content-area" class="clearfix"> <div id="left-area">';
				break;

			case 'flatsome':
				echo '<div class="large-9 col">';
				break;

			case 'astra':
				echo '<div id="primary" class="content-area primary">';
				break;

			case 'storefront':
				echo '<div id="primary" class="content-area">';
				break;

			default:
				echo '<div class="content-area">';
				break;
		}
		unset( $wrapper_args );
	}

	/**
	 * Compati.
	 *
	 * @param text $wrapper_args wrapper_args.
	 */
	public function eb_content_end_theme_compatibility( $wrapper_args ) {
		echo '</div>';
		unset( $wrapper_args );
	}

	/**
	 * Compati.
	 *
	 * @param text $wrapper_args wrapper_args.
	 */
	public function eb_sidebar_start_theme_compatibility( $wrapper_args ) {
		$template = get_option( 'template' );

		switch ( $template ) {
				// Divi.
			case 'flatsome':
				echo '<div class="large-3 col">';
				break;

			case 'astra':
				echo '';
				break;

			default:
				echo '<div>';
				break;
		}
		unset( $wrapper_args );

	}

	/**
	 * Compati.
	 *
	 * @param text $wrapper_args wrapper_args.
	 */
	public function eb_sidebar_end_theme_compatibility( $wrapper_args ) {
		$template = get_option( 'template' );
		if ( 'astra' !== $template ) {
			$this->eb_content_end_theme_compatibility( $wrapper_args );
		}
	}
}
