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
	case 'twentytwelve':
		echo '</div></div>';
		break;
	case 'twentythirteen':
		echo '</div></div>';
		break;
	case 'twentyfourteen':
		echo '</div></div></div>';
		break;
	case 'twentyfifteen':
		echo '</div></div>';
		break;
	case 'twentysixteen':
		echo '</main></div>';
		break;
	case 'twentyseventeen':
		echo '</main></div>';
		echo '</div>';
		break;
		// divi.
	case 'Divi':
		echo '</div></div>';
		// Ending one div which started in wrapper-content-start template.
		echo '</div>';
		break;

	case 'flatsome':
		echo '</div></div>';
		// Ending one div which started in wrapper-content-start template.
		break;

	default:
		// Divi container.
		echo '</div></div>';
		// Ending one div which started in wrapper-content-start template.
		break;
}
