<?php
/**
 * Common functions.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

/**
 * Get a log file path.
 *
 * @since 1.0.0
 *
 * @param string $handle name.
 *
 * @return string the log file path
 */
function wdm_log_file_path( $handle ) {
	return trailingslashit( EB_LOG_DIR ) . $handle . '-' . sanitize_file_name( wp_hash( $handle ) ) . '.log';
}

/**
 * Create a page and store the ID in an option.
 *
 * @param mixed  $slug         Slug for the new page.
 * @param string $option_key   Option name to store the page's ID.
 * @param string $page_title   (default: '') Title for the new page.
 * @param string $page_content (default: '') Content for the new page.
 *
 * @return int page ID
 */
function wdm_create_page( $slug, $option_key = '', $page_title = '', $page_content = '' ) {
	global $wpdb;

	// get all settings of settings general tab.
	$eb_general_settings = array();
	$eb_general_settings = get_option( 'eb_general', array() );

	$option_value = 0;
	if ( '' !== trim( $option_key ) ) {
		if ( isset( $eb_general_settings[ $option_key ] ) ) {
			$option_value = $eb_general_settings[ $option_key ];
		}
	}

	if ( $option_value > 0 && get_post( $option_value ) ) {
		return -1;
	}

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode).
		$page_found_id = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . "
				WHERE post_type='page' AND post_content LIKE %s LIMIT 1;",
				"%{$page_content}%"
			)
		);
	} else {
		// Search for an existing page with the specified page slug.
		$page_found_id = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . "
				WHERE post_type='page' AND post_name = %s LIMIT 1;",
				$slug
			)
		);
	}

	if ( $page_found_id ) {
		wdm_update_page_id( $option_value, $option_key, $page_found_id, $eb_general_settings );
		return $page_found_id;
	}

	$page_data = array(
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'post_author'    => 1,
		'post_name'      => $slug,
		'post_title'     => $page_title,
		'post_content'   => $page_content,
		'comment_status' => 'closed',
	);
	$page_id   = wp_insert_post( $page_data );
	wdm_update_page_id( $option_value, $option_key, $page_id, $eb_general_settings );
	return $page_id;
}

/**
 * Create a page and store the ID in an option.
 *
 * @param mixed  $option_value   option_value.
 * @param string $option_key   Option name to store the page's ID.
 * @param string $_id   _id.
 * @param string $eb_general_settings eb_general_settings.
 */
function wdm_update_page_id( $option_value, $option_key, $_id, &$eb_general_settings ) {
	if ( '' === $option_value && '' !== trim( $option_key ) ) {
		$eb_general_settings[ $option_key ] = $_id;
		update_option( 'eb_general', $eb_general_settings );
	}
}

/**
 * Add messages.
 *
 * @param text $message message.
 */
function wdm_add_notices( $message ) {
	define( 'USER_FORM_MESSAGE', $message );
}

/**
 * Display messages.
 */
function wdm_show_notices() {
	// display form messages.
	if ( defined( 'USER_FORM_MESSAGE' ) ) {
		echo "<div class='wdm-flash-error'>";
		echo '<span>' . esc_html( USER_FORM_MESSAGE ) . '</span><br />';
		echo '</div>';
	}
}


/**
 * Remodified wdmUserAccountUrl() to return user account url.
 *
 * @param text $query_str query_str.
 * @since 1.2.0
 */
function wdm_user_account_url( $query_str = '' ) {
	$usr_ac_page_id = null;
	$eb_settings    = get_option( 'eb_general' );

	if ( isset( $eb_settings['eb_useraccount_page_id'] ) ) {
		$usr_ac_page_id = $eb_settings['eb_useraccount_page_id'];
	}

	$usr_ac_page_url = get_permalink( $usr_ac_page_id );

	if ( ! $usr_ac_page_url ) {
		$usr_ac_page_url = site_url( '/user-account' );
	}

	// Extract query string into local $_GET array.
	$get = array();
	parse_str( wp_parse_url( $query_str, PHP_URL_QUERY ), $get );
	$usr_ac_page_url = add_query_arg( $get, $usr_ac_page_url );

	return $usr_ac_page_url;
}

/**
 * Provides the functionality to calculate the user login redirect url.
 *
 * @return URL Returns the my courses page url if the flag is true otherwise
 *             returns the default $usr_ac_page_url.
 *
 * @param text $query_str query_str.
 * @since 1.2.0
 */
function wdm_eb_user_redirect_url( $query_str = '' ) {
	$usr_ac_page_id = null;

	/*
	 * Get the Edwiser Bridge genral settings.
	 */
	$eb_settings = get_option( 'eb_general' );

	/*
	 * Set the login redirect url to the user account page.
	 */
	if ( isset( $eb_settings['eb_useraccount_page_id'] ) ) {
		$usr_ac_page_id  = $eb_settings['eb_useraccount_page_id'];
		$usr_ac_page_url = get_permalink( $usr_ac_page_id );
	}
	/**
	 * Sets $usr_ac_page_url to my course page if the redirection to the my
	 * courses page is enabled in settings
	 */
	if ( isset( $eb_settings['eb_enable_my_courses'] ) && 'yes' === $eb_settings['eb_enable_my_courses'] ) {
		$usr_ac_page_url = eb_get_my_courses_page( $eb_settings );
	}

	// Extract query string into local $_GET array.
	$get = array();
	parse_str( wp_parse_url( $query_str, PHP_URL_QUERY ), $get );
	$usr_ac_page_url = add_query_arg( $get, $usr_ac_page_url );

	return $usr_ac_page_url;
}

/**
 * My course page.
 *
 * @param text $eb_settings settings.
 */
function eb_get_my_courses_page( $eb_settings ) {
	$usr_ac_page_url = site_url( '/user-account' );
	if ( isset( $eb_settings['eb_my_courses_page_id'] ) ) {
		$usr_ac_page_url = get_permalink( $eb_settings['eb_my_courses_page_id'] );
	}
	return $usr_ac_page_url;
}


/**
 * Used as a callback for usort() to sort a numeric array.
 *
 * @param text $element1 element1.
 * @param text $element2 element2.
 */
function eb_usort_numeric_callback( $element1, $element2 ) {
	return $element1->id - $element2->id;
}

/**
 * Function returns shortcode pages content.
 *
 * @param text $the_tag the_tag.
 * @since 1.2.0
 */
function eb_get_shortcode_page_content( $the_tag = '' ) {
	// Shortcodes and their attributes.
	$shortcodes = array(
		'eb_my_courses' => array(
			'user_id'                           => '',
			'my_courses_wrapper_title'          => __( 'My Courses', 'eb-textdomain' ),
			'recommended_courses_wrapper_title' => __( 'Recommended Courses', 'eb-textdomain' ),
			'number_of_recommended_courses'     => 4,
			'my_courses_progress'               => 1,
		),
		'eb_course'     => array(
			'id' => '',
		),
		'eb_courses'    => array(
			'categories'          => '',
			'order'               => 'DESC',
			'per_page'            => 12,
			'cat_per_page'        => 3,
			'group_by_cat'        => 'yes',
			'horizontally_scroll' => 'yes',
		),
	);

	$page_content = array();
	foreach ( $shortcodes as $tag => $args ) {
		$buffer = '[' . $tag . ' ';
		foreach ( $args as $attr => $value ) {
			$buffer .= $attr . '="' . $value . '" ';
		}
		$buffer              .= ']';
		$page_content[ $tag ] = $buffer;
	}

	if ( empty( $the_tag ) ) {
		return $page_content;
	} elseif ( isset( $page_content[ $the_tag ] ) ) {
		return $page_content[ $the_tag ];
	}
}

/**
 * Provides the functionality to get the current PayPal currency symbol.
 *
 * @return mixed returns the currency in string format or symbol
 */
function eb_get_current_paypal_currency_symb() {
	$payment_options = get_option( 'eb_paypal' );
	$currency        = $payment_options['eb_paypal_currency'];
	if ( isset( $payment_options['eb_paypal_currency'] ) && 'USD' === $payment_options['eb_paypal_currency'] ) {
		$currency = '$';
	}
	$currency = apply_filters( 'eb_paypal_get_currancy_symbol', $currency );

	return $currency;
}

/**
 * Function provides the functionality to check that  is the array key value is present in array or not
 * otherwise returns the default value.
 *
 * @param array  $arr   array to check the value present or not.
 * @param string $key   array key to check the value.
 * @param mixed  $value default value to return by default empty string.
 *
 * @return returns array value.
 */
function get_arr_value( $arr, $key, $value = '' ) {
	if ( isset( $arr[ $key ] ) && ! empty( $arr[ $key ] ) ) {
		$value = $arr[ $key ];
	}

	return $value;
}

/**
 * Meta.
 *
 * @param string $order_id  order_id .
 * @param string $updated_by  updated_by .
 * @param string $note  note .
 */
function update_order_hist_meta( $order_id, $updated_by, $note ) {
	$history = get_post_meta( $order_id, 'eb_order_status_history', true );
	if ( ! is_array( $history ) ) {
		$history = array();
	}
	$new_hist = array(
		'by'   => $updated_by,
		// 'time' => current_time( 'timestamp' ),
		'time' => gmdate(),
		'note' => $note,
	);

	array_unshift( $history, $new_hist );
	$history = apply_filters( 'eb_order_history', $history, $new_hist, $order_id );
	update_post_meta( $order_id, 'eb_order_status_history', $history );
	do_action( 'eb_after_order_refund_meta_save', $order_id, $history );
}

/**
 * Refund amt.
 *
 * @param text $refunds refunds.
 */
function get_total_refund_amt( $refunds ) {
	$total_refund = (float) '0.00';
	foreach ( $refunds as $refund ) {
		$refund_amt    = get_arr_value( $refund, 'amt', '0.00' );
		$total_refund += (float) $refund_amt;
	}

	return $total_refund;
}

/**
 * All eb courses.
 *
 * @param text $post_id post_id.
 */
function get_all_eb_sourses( $post_id = 0 ) {
	$posts = get_posts(
		array(
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_type'      => 'eb_course',
		)
	);

	if ( $post_id ) {
		$key = array_search( $post_id, $posts, true );
		if ( false !== $key ) {
			unset( $posts[ $key ] );
		}
	}

	$posts_with_title = array();
	foreach ( $posts as $value ) {
		$posts_with_title[ $value ] = get_the_title( $value );
	}
	return $posts_with_title;
}

/**
 * WP roles
 */
function get_all_wp_roles() {
	global $wp_roles;
	$all_roles = $wp_roles->get_names();
	return $all_roles;
}



/**
 * FUnction accptes moodle user id and returns WordPress user id and if not exists then false
 *
 * @param text $mdl_user_id mdl_user_id.
 */
function get_wp_user_id_from_moodle_id( $mdl_user_id ) {
	global $wpdb;
	$result = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_value=%d AND meta_key = 'moodle_user_id'", $mdl_user_id ) );
	return $result;
}





/**
 * FUnction accptes moodle course id and returns WordPress course id and if not exists then false
 *
 * @param text $mdl_course_id mdl_course_id.
 */
function get_wp_course_id_from_moodle_course_id( $mdl_course_id ) {
	global $wpdb;
	$result = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_value=%d AND meta_key = 'moodle_course_id'", $mdl_course_id ) );
	return $result;
}



/**
 * Default role set to the user on registration from user-account page
 *
 * @return [type] [description]
 */
function default_registration_role() {
	$role       = '';
	$eb_options = get_option( 'eb_general' );
	if ( isset( $eb_options['eb_default_role'] ) && ! empty( $eb_options['eb_default_role'] ) ) {
		$role = apply_filters( 'eb_registration_role', $eb_options['eb_default_role'] );
	}
	return $role;
}

/**
 * Web functions.
 */
function eb_get_all_web_service_functions() {
	$extensions = apply_filters(
		'eb_extensions_web_service_functions',
		array(
			'edwiser-multiple-users-course-purchase/edwiser-multiple-users-course-purchase.php' => array(
				'core_cohort_add_cohort_members',
				'core_cohort_create_cohorts',
				'core_role_assign_roles',
				'core_role_unassign_roles',
				'core_cohort_delete_cohort_members',
				'core_cohort_get_cohorts',
				// 'eb_manage_cohort_enrollment',
				'eb_delete_cohort',
				// 'wdm_manage_cohort_enrollment'
			),
			'woocommerce-integration/bridge-woocommerce.php' => array(),
			'edwiser-bridge-sso/sso.php' => array(
				'wdm_sso_verify_token',
			),
			'selective-synchronization/selective-synchronization.php' => array(
				'eb_get_users',
			),
		)
	);

	$edwiser_bridge_fns = apply_filters(
		'eb_web_service_functions',
		array(
			'core_user_get_users_by_field',
			'core_user_update_users',
			'core_course_get_courses',
			'core_course_get_categories',
			'enrol_manual_enrol_users',
			'enrol_manual_unenrol_users',
			'core_enrol_get_users_courses',
			'eb_test_connection',
			'eb_get_site_data',
			'eb_get_course_progress',
		)
	);

	foreach ( $extensions as $extension => $functions ) {
		if ( is_plugin_active( $extension ) ) {
			if ( 'edwiser-multiple-users-course-purchase/edwiser-multiple-users-course-purchase.php' === $extension ) {
				$bp_version = get_option( 'eb_bp_plugin_version' );
				if ( version_compare( '2.0.0', $bp_version ) <= 0 ) {
					array_merge( $functions, array( 'wdm_manage_cohort_enrollment' ) );
				} elseif ( 0 === version_compare( '2.1.0', $bp_version ) ) {
					array_merge( $functions, array( 'eb_manage_cohort_enrollment' ) );
				}
			}

			$edwiser_bridge_fns = array_merge( $edwiser_bridge_fns, $functions );
		}
	}

	return apply_filters( 'eb_total_web_service_functions', $edwiser_bridge_fns );
}



/**
 * The function will check if the array key exist or not and dose the arrya key associated a non empty value
 *
 * @param array  $dataarray associative array.
 * @param string $key array key to get the value.
 * @returns arrays value associated with the key if exist and not empty. Otherwise retrurns false.
 */
function check_value_set( $dataarray, $key ) {
	$value = false;
	if ( is_array( $dataarray ) && array_key_exists( $key, $dataarray ) && $dataarray[ $key ] ) {
		$value = empty( $dataarray[ $key ] ) ? false : $dataarray[ $key ];
	}
	return $value;
}

/**
 * Status.
 *
 * @param text $user_id user_id.
 * @param text $course_id course_id.
 */
function get_user_suspended_status( $user_id, $course_id ) {
	global $wpdb;
	$suspended = 0;

	if ( '' === $user_id || '' === $course_id ) {
		return $suspended;
	}

	// check if user has access to course.
	$suspended = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT suspended
			FROM {$wpdb->prefix}moodle_enrollment
			WHERE course_id=%d
			AND user_id=%d;",
			$course_id,
			$user_id
		)
	);

	return $suspended;

}


/**
 * Moodle url.
 */
function get_moodle_url() {
	$url = get_option( 'eb_connection' );
	if ( $url ) {
		return $url['eb_url'];
	}
	return 'MOODLE_URL';
}
/**
 * Returns the list of the tags allowed in the wp_kses function.
 */
function get_allowed_html_tags() {
	$allowed_tags           = wp_kses_allowed_html( 'post' );
	$allowed_tags['iframe'] = array(
		'src'             => array(),
		'height'          => array(),
		'width'           => array(),
		'frameborder'     => array(),
		'allowfullscreen' => array(),
	);
	$allowed_tags['input']  = array(
		'class' => array(),
		'id'    => array(),
		'name'  => array(),
		'value' => array(),
		'type'  => array(),
	);
	$allowed_tags['select'] = array(
		'class'  => array(),
		'id'     => array(),
		'name'   => array(),
		'value'  => array(),
		'type'   => array(),
		'style'  => array(),
		'data-*' => true,
	);
	$allowed_tags['option'] = array(
		'class'    => array(),
		'value'    => array(),
		'selected' => array(),
	);
	$allowed_tags['style']  = array(
		'types' => array(),
	);
	return $allowed_tags;
}






/**
 * Returns the list of the tags allowed in the wp_kses function.
 */
function eb_sinlge_course_get_allowed_html_tags() {
	$allowed_tags           = wp_kses_allowed_html( 'post' );
	$allowed_tags['form']   = array(
		'method'             => array(),
	);
	$allowed_tags['input']  = array(
		'class' => array(),
		'id'    => array(),
		'name'  => array(),
		'value' => array(),
		'type'  => array(),
	);
	$allowed_tags['select'] = array(
		'class'  => array(),
		'id'     => array(),
		'name'   => array(),
		'value'  => array(),
		'type'   => array(),
		'style'  => array(),
		'data-*' => true,
	);
	$allowed_tags['option'] = array(
		'class'    => array(),
		'value'    => array(),
		'selected' => array(),
	);
	$allowed_tags['script'] = array(
		'src' => array(),
		'type' => array(),
	);
	$allowed_tags['a']      = array(
		'href' => array(),
		'target' => array(),
	);
	return $allowed_tags;
}


