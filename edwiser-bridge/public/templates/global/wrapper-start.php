<?php

/**
 * Primary wrapper starting HTML content.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! isset( $parentcss ) || ! is_string( $parentcss ) ) {
	$parentcss = '';
}

$template = get_option( 'template' );


switch ( $template ) {
	case 'twentyeleven':
		echo '<section id="primary" style="' . $parentcss . '"><div id='content' role="main">';
		break;
	case 'twentytwelve':
		echo '<div id="primary" class="site-content" style="'
		. $parentcss . '"><div id='content' role="main" class="twentytwelve">';
		break;
	case 'twentythirteen':
		echo '<div id="primary" class="site-content" style="'
		. $parentcss . '"><div id='content' role="main" class="entry-content twentythirteen">';
		break;
	case 'twentyfourteen':
		echo '<div id="primary" class="content-area" style="'
		. $parentcss . '"><div id='content' role="main" class="site-content twentyfourteen"><div class="tfwc">';
		break;
	case 'twentyfifteen':
		echo '<div id="primary" role="main" class="content-area twentyfifteen" style="'
		. $parentcss . '"><div id="main" class="site-main t15wc">';
		break;
	case 'twentysixteen':
		echo '<div id="primary" class="content-area twentysixteen eb-primary" style="'
		. $parentcss . '"><main id="main" class="site-main" role="main">';
		break;
	case 'twentyseventeen':
		echo '<div class="wrap"><div id="primary" class="content-area twentyseventeen" style="'
		. $parentcss . '"><main id="main" class="site-main" role="main">';
		break;
		// flatsome
	case 'twentyseventeen':
		echo '<div class="wrap"><div id="primary" class="content-area twentyseventeen" style="'
		. $parentcss . '"><main id="main" class="site-main" role="main">';
		break;
		// Divi
	case 'Divi':
		echo '<div id="main-content" style="'
		. $parentcss . '"><div class="container">';
		break;

	case 'flatsome':
		echo '<div id='content' style="padding:30px 0px;"'
		. $parentcss . '"><div class="row row-large row-divided">';
		break;

	default:
		echo '<div id="container" class="eb-primary" style="'
		. $parentcss . '"><div id='content' role="main">';
		break;
}
