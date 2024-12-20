<?php
/**
 * Primary wrapper end HTML content.
 *
 * @package Edwiser Bridge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$template = get_option( 'template' );

switch ( $template ) {
	case 'twentyeleven':
		echo '</div></section>';
		break;
	case 'twentysixteen':
		echo '</main></div>';
		break;
	case 'twentyseventeen':
		echo '</main></div>';
		echo '</div>';
		break;
		// divi.
	case 'twentyfourteen':
	case 'Divi':
		echo '</div></div></div>';
		if ( is_archive() ) {
			echo '</div>';
		}
		break;

	case 'astra':
		if ( is_archive() ) {
			echo '</div>';
		}
		break;

	default:
		// Divi container.
		echo '</div></div>';
		// Ending one div which started in wrapper-content-start template.
		break;
}
