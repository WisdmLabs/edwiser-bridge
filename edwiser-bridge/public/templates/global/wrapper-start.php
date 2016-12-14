<?php
/**
 * Primary wrapper starting HTML content.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!isset($parentcss) || !is_string($parentcss)) {
    $parentcss = '';
}

$template = get_option('template');

switch ($template) {
    case 'twentysixteen':
        echo '<div id="primary" class="content-area twentysixteen eb-primary" style="'.$parentcss.'"><main id="main" class="site-main" role="main">';
        break;
    case 'twentyseventeen':
        echo '<div class="wrap"><div id="primary" class="content-area" style="'.$parentcss.'"><main id="main" class="site-main" role="main">';
        break;
    default:
        echo '<div id="container" class="eb-primary" style="'.$parentcss.'"><div id="content" role="main">';
        break;
}
