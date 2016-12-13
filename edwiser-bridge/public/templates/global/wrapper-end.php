<?php
/**
 * Primary wrapper end HTML content.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$template = get_option('template');

switch ($template) {
    case 'twentyeleven':
        echo '</div>';
        get_sidebar();
        echo '</div>';
        break;
    case 'twentytwelve':
        echo '</div></div>';
        break;
    case 'twentythirteen':
        echo '</div></div>';
        break;
    case 'twentyfourteen':
        echo '</div></div></div>';
        get_sidebar();
        break;
    case 'twentyfifteen':
        echo '</div></div>';
        break;
    case 'twentysixteen':
        echo '</main></div>';
        break;
    default:
        echo '</div></div>';
        break;
}
