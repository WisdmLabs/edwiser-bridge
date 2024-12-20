<?php
/**
 * Primary wrapper starting HTML content.
 *
 * @package Edwiser Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $parentcss ) || ! is_string( $parentcss ) ) {
	$parentcss = '';
}

$template = get_option( 'template' );

switch ( $template ) {
	case 'twentyeleven':
		echo '<section id="primary" style="' . esc_html( $parentcss ) . '"><div id="content" role="main">';
		break;
	case 'twentytwelve':
		echo '<div id="primary" class="site-content" style="'
		. esc_html( $parentcss ) . '"><div id="content" role="main" class="twentytwelve">';
		break;
	case 'twentythirteen':
		echo '<div id="primary" class="site-content" style="'
		. esc_html( $parentcss ) . '"><div id="content" role="main" class="entry-content twentythirteen">';
		break;
	case 'twentyfourteen':
		echo '<div id="primary" class="content-area" style="'
		. esc_html( $parentcss ) . '"><div id="content" role="main" class="site-content twentyfourteen"><div class="tfwc">';
		break;
	case 'twentyfifteen':
		echo '<div id="primary" role="main" class="content-area twentyfifteen" style="'
		. esc_html( $parentcss ) . '"><div id="main" class="site-main t15wc">';
		break;
	case 'twentysixteen':
		echo '<div id="primary" class="content-area twentysixteen eb-primary" style="'
		. esc_html( $parentcss ) . '"><main id="main" class="site-main" role="main">';
		break;
	case 'twentyseventeen':
		echo '<div class="wrap"><div id="primary" class="content-area twentyseventeen" style="'
		. esc_html( $parentcss ) . '"><main id="main" class="site-main" role="main">';
		break;
		// Divi.
	case 'twentytwentytwo':
		echo '<div id="container" class="eb-primary wp-site-blocks"><div id="content" role="main">';
		break;
	case 'Divi':
		echo '<div id="main-content" style="'
		. esc_html( $parentcss ) . '"><div class="container eb-primary">';

		if ( is_archive() ) {
			echo '<div id="content-area" class="clearfix">';
		}


		break;

	case 'flatsome':
		echo '<div id="content" style="padding:30px 0px;"'
		. esc_html( $parentcss ) . '"><div class="row row-large row-divided">';
		break;

	case 'astra':
		if ( is_archive() ) {
			echo '<div>';
		}

		break;

	default:
		echo '<div id="container" class="eb-primary" style="'
		. esc_html( $parentcss ) . '"><div id="content" role="main">';
		break;
}
