<?php
/**
 * Primary wrapper starting HTML content.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$template = get_option('template');

switch ($template) {
    case 'twentyeleven':
        echo '<div id="primary" class="eb-primary"><div id="content" role="main" class="twentyeleven">';
        break;
    case 'twentytwelve':
        echo '<div id="primary" class="site-content eb-primary"><div id="content" role="main" class="twentytwelve">';
        break;
    case 'twentythirteen':
        echo '<div id="primary" class="site-content eb-primary"><div id="content" role="main" class="entry-content twentythirteen">';
        break;
    case 'twentyfourteen':
        echo '<div id="primary" class="content-area eb-primary"><div id="content" role="main" class="site-content twentyfourteen"><div class="tfwc">';
        break;
    case 'twentyfifteen':
        echo '<div id="primary" role="main" class="content-area twentyfifteen eb-primary"><div id="main" class="site-main t15wc">';
        break;
    case 'twentysixteen':
        echo '<div id="primary" class="content-area twentysixteen eb-primary"><main id="main" class="site-main" role="main">';
        break;
    default:
        echo '<div id="container" class="eb-primary"><div id="content" role="main">';
        break;
}
