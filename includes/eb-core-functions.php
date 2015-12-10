<?php

/**
 * Get a log file path
 *
 * @since 1.0.0
 * @param string  $handle name
 * @return string the log file path
 */
function wdm_log_file_path( $handle ) {
	return trailingslashit( EB_LOG_DIR ) . $handle . '-' . sanitize_file_name( wp_hash( $handle ) ) . '.log';
}

/**
 * Create a page and store the ID in an option.
 *
 * @param mixed   $slug         Slug for the new page
 * @param string  $option_key   Option name to store the page's ID
 * @param string  $page_title   (default: '') Title for the new page
 * @param string  $page_content (default: '') Content for the new page
 * @param int     $post_parent  (default: 0) Parent for the new page
 * @return int page ID
 */
function wdm_create_page( $slug, $option_key = '', $page_title = '', $page_content = '' ) {
	global $wpdb;

	// get all settings of settings general tab
	$eb_general_settings = get_option( 'eb_general' );

	if ( trim( $option_key ) != '' ) {

		if ( empty( $eb_general_settings ) ) {
			$eb_general_settings = array();
		}

		$option_value = isset( $eb_general_settings[$option_key] )?$eb_general_settings[$option_key]:0;
	}
	else
		$option_value = 0;

	if ( $option_value > 0 && get_post( $option_value ) ) {
		return -1;
	}

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$page_found_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$page_found_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", $slug ) );
	}

	if ( $page_found_id ) {
		if ( $option_value == '' && trim( $option_key ) != '' ) {
			// update the page id in general settings
			$eb_general_settings[$option_key] = $page_found_id;
			update_option( 'eb_general', $eb_general_settings );
		}
		return $page_found_id;
	}

	$page_data = array(
		'post_status'     => 'publish',
		'post_type'       => 'page',
		'post_author'     => 1,
		'post_name'       => $slug,
		'post_title'      => $page_title,
		'post_content'    => $page_content,
		'comment_status'  => 'closed'
	);
	$page_id = wp_insert_post( $page_data );

	// update the page id in general settings
	if ( $option_value == '' && trim( $option_key ) != '' ) {
		$eb_general_settings[$option_key] = $page_id;
		update_option( 'eb_general', $eb_general_settings );
	}

	return $page_id;
}

// add messages
function wdm_add_notices( $message ) {
	define( 'USER_FORM_MESSAGE', $message );
}

// display messages
function wdm_show_notices() {
	//display form messages
	if ( defined( 'USER_FORM_MESSAGE' ) ) {
		echo "<div class='wdm-flash-error'>";
		echo "<span>".USER_FORM_MESSAGE."</span><br />";
		echo "</div>";
	}
}

// get user account url
function wdm_user_account_url( $arg = '' ) {

	// $user_account_page_id = EB_Admin_Settings::get_option( 'eb_useraccount_page_id', 'general' );

	$eb_general_settings = get_option( 'eb_general' );
	$user_account_page_id = isset( $eb_general_settings['eb_useraccount_page_id'] )?$eb_general_settings['eb_useraccount_page_id']:'';

	if ( !is_numeric( $user_account_page_id ) ) {
		$link = site_url( '/user-account' ) . $arg;
	} else {
		$link = get_permalink( $user_account_page_id ) . $arg;
	}

	return $link;
}

// used as a callback for usort() to sort a numeric array
function usort_numeric_callback( $element1, $element2 ) {
	return $element1->id - $element2->id;
}
