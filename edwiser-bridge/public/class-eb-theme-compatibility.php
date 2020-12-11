<?php
/**
 * Handles frontend form submissions.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

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

			default:
				echo '<div>';
				break;
		}

	}

	/**
	 * Compati.
	 *
	 * @param text $wrapper_args wrapper_args.
	 */
	public function eb_content_end_theme_compatibility( $wrapper_args ) {
		$template = get_option( 'template' );

		switch ( $template ) {
				// Divi.
			case 'Divi':
				echo '</div>';
				break;
			default:
				// Divi container.
				echo '</div>';
				break;
		}

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

			default:
				echo '<div>';
				break;
		}

	}

	/**
	 * Compati.
	 *
	 * @param text $wrapper_args wrapper_args.
	 */
	public function eb_sidebar_end_theme_compatibility( $wrapper_args ) {
		$template = get_option( 'template' );

		switch ( $template ) {
				// Divi.
			case 'Divi':
				echo '</div>';
				break;

			case 'flatsome':
				echo '</div>';
				break;

			default:
				echo '</div>';
				break;
		}

	}
}
