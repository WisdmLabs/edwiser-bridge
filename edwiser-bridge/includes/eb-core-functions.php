<?php
/**
 * Common functions.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! function_exists( 'wdm_eb_log_file_path' ) ) {
	/**
	 * Get a log file path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $handle name.
	 *
	 * @return string the log file path
	 */
	function wdm_eb_log_file_path( $handle ) {
		$eb_log_dir_path = wdm_edwiser_bridge_plugin_log_dir();
		return trailingslashit( $eb_log_dir_path ) . $handle . '-' . sanitize_file_name( wp_hash( $handle ) ) . '.log';
	}
}


if ( ! function_exists( 'wdm_eb_create_page' ) ) {
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
	function wdm_eb_create_page( $slug, $option_key = '', $page_title = '', $page_content = '' ) {
		global $wpdb;

		// get all settings of settings general tab.
		$eb_general_settings = array();
		$eb_general_settings = get_option( 'eb_general', array() );

		$option_value = 0;
		if ( '' !== trim( $option_key ) && isset( $eb_general_settings[ $option_key ] ) ) {
				$option_value = $eb_general_settings[ $option_key ];
		}

		if ( $option_value > 0 && get_post( $option_value ) ) {
			return -1;
		}

		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$page_found_id = $wpdb->get_var( // @codingStandardsIgnoreLine
				$wpdb->prepare(
					'SELECT ID FROM ' . $wpdb->posts . "
					WHERE post_type='page' AND post_content LIKE %s LIMIT 1;",
					"%{$page_content}%"
				)
			);
		} else {
			// Search for an existing page with the specified page slug.
			$page_found_id = $wpdb->get_var( // @codingStandardsIgnoreLine
				$wpdb->prepare(
					'SELECT ID FROM ' . $wpdb->posts . "
					WHERE post_type='page' AND post_name = %s LIMIT 1;",
					$slug
				)
			);
		}

		if ( $page_found_id ) {
			wdm_eb_update_page_id( $option_value, $option_key, $page_found_id, $eb_general_settings );
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
		wdm_eb_update_page_id( $option_value, $option_key, $page_id, $eb_general_settings );
		return $page_id;
	}
}


if ( ! function_exists( 'wdm_eb_update_page_id' ) ) {
	/**
	 * Create a page and store the ID in an option.
	 *
	 * @param mixed  $option_value   option_value.
	 * @param string $option_key   Option name to store the page's ID.
	 * @param string $_id   _id.
	 * @param string $eb_general_settings eb_general_settings.
	 */
	function wdm_eb_update_page_id( $option_value, $option_key, $_id, &$eb_general_settings ) {
		if ( ! empty( $option_key ) ) {
			$eb_general_settings[ $option_key ] = $_id;
			update_option( 'eb_general', $eb_general_settings );
		}
	}
}

if ( ! function_exists( 'wdm_eb_login_reg_add_notices' ) ) {
	/**
	 * Add messages.
	 *
	 * @param text $message message.
	 */
	function wdm_eb_login_reg_add_notices( $message ) {
		define( 'WDM_EDWISER_BRIDGE_USER_FORM_MESSAGE', $message );
	}
}


if ( ! function_exists( 'wdm_eb_login_reg_show_notices' ) ) {
	/**
	 * Display messages.
	 */
	function wdm_eb_login_reg_show_notices() {
		// display form messages.
		if ( defined( 'WDM_EDWISER_BRIDGE_USER_FORM_MESSAGE' ) ) {
			echo "<div class='wdm-flash-error'>";
			echo '<span>' . wp_kses( WDM_EDWISER_BRIDGE_USER_FORM_MESSAGE, \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() ) . '</span><br />';
			echo '</div>';
		}
	}
}

if ( ! function_exists( 'wdm_eb_user_account_url' ) ) {
	/**
	 * Remodified wdmUserAccountUrl() to return user account url.
	 *
	 * @param text $args query_string arguments array.
	 * @since 1.2.0
	 */
	function wdm_eb_user_account_url( $args = array() ) {
		$usr_ac_page_id = null;
		$eb_settings    = get_option( 'eb_general' );

		if ( isset( $eb_settings['eb_useraccount_page_id'] ) ) {
			$usr_ac_page_id = $eb_settings['eb_useraccount_page_id'];
		}

		$usr_ac_page_url = get_permalink( $usr_ac_page_id );

		if ( ! $usr_ac_page_url ) {
			$usr_ac_page_url = site_url( '/user-account' );
		}

		$usr_ac_page_url = add_query_arg( $args, $usr_ac_page_url );

		return $usr_ac_page_url;
	}
}

if ( ! function_exists( 'wdm_eb_user_redirect_url' ) ) {
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
			$usr_ac_page_url = wdm_eb_get_my_courses_page( $eb_settings );
		}

		// Extract query string into local $_GET array.
		$get = array();
		parse_str( wp_parse_url( $query_str, PHP_URL_QUERY ), $get );
		$usr_ac_page_url = add_query_arg( $get, $usr_ac_page_url );

		return $usr_ac_page_url;
	}
}

if ( ! function_exists( 'wdm_eb_get_my_courses_page' ) ) {
	/**
	 * My course page.
	 *
	 * @param text $eb_settings settings.
	 */
	function wdm_eb_get_my_courses_page( $eb_settings ) {
		$usr_ac_page_url = site_url( '/user-account' );
		if ( isset( $eb_settings['eb_my_courses_page_id'] ) ) {
			$usr_ac_page_url = get_permalink( $eb_settings['eb_my_courses_page_id'] );
		}
		return $usr_ac_page_url;
	}
}

if ( ! function_exists( 'wdm_eb_usort_numeric_callback' ) ) {
	/**
	 * Used as a callback for usort() to sort a numeric array.
	 *
	 * @param text $element1 element1.
	 * @param text $element2 element2.
	 */
	function wdm_eb_usort_numeric_callback( $element1, $element2 ) {
		return $element1->id - $element2->id;
	}
}

if ( ! function_exists( 'wdm_eb_get_shortcode_page_content' ) ) {
	/**
	 * Function returns shortcode pages content.
	 *
	 * @param text $the_tag the_tag.
	 * @since 1.2.0
	 */
	function wdm_eb_get_shortcode_page_content( $the_tag = '' ) {
		// Shortcodes and their attributes.
		$shortcodes = array(
			'eb_my_courses' => array(
				'user_id'                           => '',
				'my_courses_wrapper_title'          => __( 'My Courses', 'edwiser-bridge' ),
				'recommended_courses_wrapper_title' => __( 'Recommended Courses', 'edwiser-bridge' ),
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
				'group_by_cat'        => 'no',
				'horizontally_scroll' => 'no',
				'show_filter'         => 'yes',
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
}

if ( ! function_exists( 'wdm_eb_get_current_paypal_currency_symb' ) ) {
	/**
	 * Provides the functionality to get the current PayPal currency symbol.
	 *
	 * @return mixed returns the currency in string format or symbol
	 */
	function wdm_eb_get_current_paypal_currency_symb() {
		$payment_options = get_option( 'eb_paypal' );
		$currency        = $payment_options['eb_paypal_currency'];
		if ( isset( $payment_options['eb_paypal_currency'] ) && 'USD' === $payment_options['eb_paypal_currency'] ) {
			$currency = '$';
		}
		$currency = apply_filters( 'eb_paypal_get_currancy_symbol', $currency );

		return $currency;
	}
}

if ( ! function_exists( 'wdm_eb_get_value_from_array' ) ) {
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
	function wdm_eb_get_value_from_array( $arr, $key, $value = '' ) {
		if ( isset( $arr[ $key ] ) && ! empty( $arr[ $key ] ) ) {
			$value = $arr[ $key ];
		}

		return $value;
	}
}

if ( ! function_exists( 'wdm_eb_update_order_hist_meta' ) ) {
	/**
	 * Meta.
	 *
	 * @param string $order_id  order_id .
	 * @param string $updated_by  updated_by .
	 * @param string $note  note .
	 */
	function wdm_eb_update_order_hist_meta( $order_id, $updated_by, $note ) {
		$history = get_post_meta( $order_id, 'eb_order_status_history', true );
		if ( ! is_array( $history ) ) {
			$history = array();
		}
		$new_hist = array(
			'by'   => $updated_by,
			'time' => current_time( 'Y-m-d' ),
			'note' => $note,
		);

		array_unshift( $history, $new_hist );
		$history = apply_filters( 'eb_order_history', $history, $new_hist, $order_id );
		update_post_meta( $order_id, 'eb_order_status_history', $history );
		do_action( 'eb_after_order_refund_meta_save', $order_id, $history );
	}
}


if ( ! function_exists( 'wdm_eb_get_total_refund_amt' ) ) {
	/**
	 * Refund amt.
	 *
	 * @param text $refunds refunds.
	 */
	function wdm_eb_get_total_refund_amt( $refunds ) {
		$total_refund = (float) '0.00';
		foreach ( $refunds as $refund ) {
			$refund_amt    = wdm_eb_get_value_from_array( $refund, 'amt', '0.00' );
			$total_refund += (float) $refund_amt;
		}

		return $total_refund;
	}
}


if ( ! function_exists( 'wdm_eb_get_all_eb_sourses' ) ) {
	/**
	 * All eb courses.
	 *
	 * @param text $post_id post_id.
	 */
	function wdm_eb_get_all_eb_sourses( $post_id = 0 ) {
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
}

if ( ! function_exists( 'eb_get_user_enrolled_courses' ) ) {
	/**
	 * All eb courses.
	 *
	 * @param text $user_id user_id.
	 */
	function eb_get_user_enrolled_courses( $user_id = null ) {
		global $wpdb;
		$user_id = ! is_numeric( $user_id ) ? get_current_user_id() : (int) $user_id;

		$result = $wpdb->get_results( $wpdb->prepare( "SELECT course_id FROM {$wpdb->prefix}moodle_enrollment WHERE user_id=%d;", $user_id ) ); // @codingStandardsIgnoreLine
		$courses = array();
		foreach ( $result as $key => $course ) {
			$courses[] = $course->course_id;
		}

		return $courses;
	}
}


if ( ! function_exists( 'wdm_eb_get_all_wp_roles' ) ) {
	/**
	 * WP roles
	 */
	function wdm_eb_get_all_wp_roles() {
		global $wp_roles;
		$all_roles = $wp_roles->get_names();
		return $all_roles;
	}
}

if ( ! function_exists( 'wdm_eb_get_wp_user_id_from_moodle_id' ) ) {
	/**
	 * FUnction accptes moodle user id and returns WordPress user id and if not exists then false
	 *
	 * @param text $mdl_user_id mdl_user_id.
	 */
	function wdm_eb_get_wp_user_id_from_moodle_id( $mdl_user_id ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_value=%d AND meta_key = 'moodle_user_id'", $mdl_user_id ) ); // @codingStandardsIgnoreLine
		return $result;
	}
}


if ( ! function_exists( 'wdm_eb_get_wp_course_id_from_moodle_course_id' ) ) {
	/**
	 * FUnction accptes moodle course id and returns WordPress course id and if not exists then false
	 *
	 * @param text $mdl_course_id mdl_course_id.
	 */
	function wdm_eb_get_wp_course_id_from_moodle_course_id( $mdl_course_id ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_value=%d AND meta_key = 'moodle_course_id'", $mdl_course_id ) ); // @codingStandardsIgnoreLine
		return $result;
	}
}

if ( ! function_exists( 'wdm_eb_default_registration_role' ) ) {
	/**
	 * Default role set to the user on registration from user-account page
	 *
	 * @return [type] [description]
	 */
	function wdm_eb_default_registration_role() {
		$role       = '';
		$eb_options = get_option( 'eb_general' );
		if ( isset( $eb_options['eb_default_role'] ) && ! empty( $eb_options['eb_default_role'] ) ) {
			$role = apply_filters( 'eb_registration_role', $eb_options['eb_default_role'] );
		}
		return $role;
	}
}

if ( ! function_exists( 'wdm_eb_get_all_web_service_functions' ) ) {
	/**
	 * Web functions.
	 */
	function wdm_eb_get_all_web_service_functions() {
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
				'core_user_create_users',
				'core_user_update_users',
				'core_course_get_courses',
				'core_course_get_categories',
				'enrol_manual_enrol_users',
				'enrol_manual_unenrol_users',
				'core_enrol_get_users_courses',
				'eb_test_connection',
				'eb_get_site_data',
				'eb_get_course_progress',
				'eb_get_edwiser_plugins_info',
				'edwiserbridge_local_get_course_enrollment_method',
				'edwiserbridge_local_update_course_enrollment_method',
				'edwiserbridge_local_get_mandatory_settings',
				'edwiserbridge_local_enable_plugin_settings',
			)
		);

		foreach ( $extensions as $extension => $functions ) {
			if ( is_plugin_active( $extension ) ) {
				if ( 'edwiser-multiple-users-course-purchase/edwiser-multiple-users-course-purchase.php' === $extension ) {
					$bp_version = get_option( 'eb_bp_plugin_version' );
					if ( version_compare( '2.0.0', $bp_version ) <= 0 ) {
						$functions = array_merge( $functions, array( 'wdm_manage_cohort_enrollment' ) );
					} elseif ( 0 === version_compare( '2.1.0', $bp_version ) ) {
						$functions = array_merge( $functions, array( 'eb_manage_cohort_enrollment' ) );
					}
				}

				$edwiser_bridge_fns = array_merge( $edwiser_bridge_fns, $functions );
			}
		}

		return apply_filters( 'eb_total_web_service_functions', $edwiser_bridge_fns );
	}
}


if ( ! function_exists( 'wdm_eb_get_user_suspended_status' ) ) {
	/**
	 * Status.
	 *
	 * @param text $user_id user_id.
	 * @param text $course_id course_id.
	 */
	function wdm_eb_get_user_suspended_status( $user_id, $course_id ) {
		global $wpdb;
		$suspended = 0;

		if ( '' === $user_id || '' === $course_id ) {
			return $suspended;
		}

		// check if user has access to course.
		$suspended = $wpdb->get_var( // @codingStandardsIgnoreLine
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
}

if ( ! function_exists( 'wdm_eb_get_moodle_url' ) ) {
	/**
	 * Moodle url.
	 */
	function wdm_eb_get_moodle_url() {
		$url = get_option( 'eb_connection' );
		if ( $url ) {
			$eb_moodle_url = $url['eb_url'];

			if ( substr( $eb_moodle_url, -1 ) === '/' ) {
				$eb_moodle_url = substr( $eb_moodle_url, 0, -1 );
			}
			return $eb_moodle_url;
		}
		return 'MOODLE_URL';
	}
}

if ( ! function_exists( 'wdm_eb_get_moodle_url' ) ) {
	/**
	 * Moodle User role id default is 5.
	 */
	function eb_get_moodle_role_id() {
		$eb_general_settings = get_option( 'eb_general' );
		return isset( $eb_general_settings['eb_moodle_role_id'] ) && ! empty( $eb_general_settings['eb_moodle_role_id'] ) ? $eb_general_settings['eb_moodle_role_id'] : 5;
	}
}


if ( ! function_exists( 'wdm_eb_get_allowed_html_tags' ) ) {
	/**
	 * Returns the list of the tags allowed in the wp_kses function.
	 */
	function wdm_eb_get_allowed_html_tags() {
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
		$allowed_tags['span']   = array(
			'style'         => array(),
			'id'            => array(),
			'class'         => array(),
			'data-courseid' => array(),

		);
		$allowed_tags['h4'] = array(
			'style' => array(),
			'id'    => array(),
			'class' => array(),
		);
		return $allowed_tags;
	}
}

if ( ! function_exists( 'wdm_eb_sinlge_course_get_allowed_html_tags' ) ) {
	/**
	 * Returns the list of the tags allowed in the wp_kses function.
	 */
	function wdm_eb_sinlge_course_get_allowed_html_tags() {
		$allowed_tags           = wp_kses_allowed_html( 'post' );
		$allowed_tags['form']   = array(
			'method' => array(),
			'target' => array(),
			'action' => array(),
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
			'src'  => array(),
			'type' => array(),
		);
		$allowed_tags['a']      = array(
			'href'   => array(),
			'target' => array(),
			'class'  => array(),
			'id'     => array(),
		);
		$allowed_tags['img']    = array(
			'src'     => array(),
			'width'   => array(),
			'alt'     => array(),
			'class'   => array(),
			'height'  => array(),
			'loading' => array(),
		);
		$allowed_tags['span']   = array(
			'style' => array(),
			'id'    => array(),
			'class' => array(),

		);
		$allowed_tags['h4'] = array(
			'style' => array(),
			'id'    => array(),
			'class' => array(),

		);
		$allowed_tags['h2'] = array(
			'style' => array(),
			'id'    => array(),
			'class' => array(),

		);
		return $allowed_tags;
	}
}

if ( ! function_exists( 'wdm_eb_edwiser_sanitize_array' ) ) {
	/**
	 * Sanitize array.
	 *
	 * @param array $array_data array_data.
	 */
	function wdm_eb_edwiser_sanitize_array( $array_data ) {
		$sanitized_array = array();
		if ( is_array( $array_data ) ) {
			foreach ( $array_data as $key => $data ) {
				if ( is_array( $data ) ) {
					foreach ( $data as $data_key => $data_data ) {

						$sanitized_array[ $key ][ $data_key ] = sanitize_text_field( wp_unslash( $data_data ) );
					}
				} else {
					$sanitized_array[ $key ] = sanitize_text_field( wp_unslash( $data ) );
				}
			}
		} else {
			$sanitized_array = sanitize_text_field( wp_unslash( $array_data ) );
		}

		return $sanitized_array;
	}
}




if ( ! function_exists( 'wdm_edwiser_bridge_version' ) ) {

	/**
	 * Gwt edwiser Bridge version.
	 */
	function wdm_edwiser_bridge_version() {
		return '2.2.0';
	}
}



if ( ! function_exists( 'wdm_edwiser_bridge_plugin_url' ) ) {

	/**
	 * Gwt edwiser Bridge plugin url.
	 */
	function wdm_edwiser_bridge_plugin_url() {
		return plugin_dir_url( dirname( __FILE__ ) );
	}
}


if ( ! function_exists( 'wdm_edwiser_bridge_plugin_dir' ) ) {

	/**
	 * Gwt edwiser Bridge plugin url.
	 */
	function wdm_edwiser_bridge_plugin_dir() {
		return plugin_dir_path( dirname( __FILE__ ) );
	}
}


if ( ! function_exists( 'wdm_edwiser_bridge_plugin_template_path' ) ) {

	/**
	 * Gwt edwiser Bridge plugin url.
	 */
	function wdm_edwiser_bridge_plugin_template_path() {
		return 'edwiserBridge/';
	}
}


if ( ! function_exists( 'wdm_edwiser_bridge_get_lang_code' ) ) {

	/**
	 * Gwt edwiser Bridge plugin url.
	 */
	function wdm_edwiser_bridge_get_lang_code() {

		$general_options = get_option( 'eb_general' );

		$lang_code = '';
		if ( isset( $general_options['eb_language_code'] ) ) {
			$lang_code = $general_options['eb_language_code'];
		}

		return $lang_code;
	}
}




if ( ! function_exists( 'wdm_edwiser_bridge_plugin_get_access_token' ) ) {

	/**
	 * Gwt edwiser Bridge plugin url.
	 */
	function wdm_edwiser_bridge_plugin_get_access_token() {

		$connection_options = get_option( 'eb_connection' );

		$eb_moodle_token = '';
		if ( isset( $connection_options['eb_access_token'] ) ) {
			$eb_moodle_token = $connection_options['eb_access_token'];
		}

		return $eb_moodle_token;
	}
}


if ( ! function_exists( 'wdm_edwiser_bridge_plugin_get_access_url' ) ) {

	/**
	 * Gwt edwiser Bridge plugin url.
	 */
	function wdm_edwiser_bridge_plugin_get_access_url() {

		$connection_options = get_option( 'eb_connection' );

		$eb_moodle_url = '';
		if ( isset( $connection_options['eb_url'] ) ) {
			$eb_moodle_url = $connection_options['eb_url'];
		}

		if ( substr( $eb_moodle_url, -1 ) === '/' ) {
			$eb_moodle_url = substr( $eb_moodle_url, 0, -1 );
		}

		return $eb_moodle_url;
	}
}


if ( ! function_exists( 'wdm_edwiser_bridge_plugin_log_dir' ) ) {

	/**
	 * Gwt edwiser Bridge plugin url.
	 */
	function wdm_edwiser_bridge_plugin_log_dir() {

		$upload_dir = wp_upload_dir();

		return $upload_dir['basedir'] . '/eb-logs/';
	}
}
if ( ! function_exists( 'wdm_get_plugin_version' ) ) {
	/**
	 * Function to get the current plugin version.
	 *
	 * @param string $path Plugin file path.
	 */
	function wdm_get_plugin_version( $path ) {
		$plugin_info = array();
		if ( file_exists( WP_PLUGIN_DIR . '/' . $path ) ) {
			$plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/' . $path );
		}
		return isset( $plugin_info['Version'] ) ? $plugin_info['Version'] : __( 'Plugin not installed', 'edwiser-bridge' );
	}
}

if ( ! function_exists( 'wdm_request_edwiser' ) ) {
	/**
	 * Function to send the reuest to the edwiser site.
	 *
	 * @param  array $api_params request params data.
	 * @return array array of the status and data.
	 */
	function wdm_request_edwiser( $api_params ) {
		$store_url            = 'https://edwiser.org/check-update';
		$api_params['author'] = 'WisdmLabs';
		$resp_data            = array(
			'status' => false,
			'data'   => '',
		);
		$request              = wp_remote_get(
			add_query_arg( $api_params, $store_url ),
			array(
				'timeout'    => 15,
				'sslverify'  => false,
				'blocking'   => true,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			)
		);
		if ( ! is_wp_error( $request ) ) {
			$resp_data['data']   = json_decode( wp_remote_retrieve_body( $request ) );
			$resp_data['status'] = wp_remote_retrieve_response_code( $request );
		} else {
			$resp_data['data'] = $request->get_error_messages();
		}
		return $resp_data;
	}
}




if ( ! function_exists( 'wdm_eb_course_terms' ) ) {
	/**
	 * Function to send the reuest to the edwiser site.
	 *
	 * @param  array $course_id course id.
	 * @return array array of the status and data.
	 */
	function wdm_eb_course_terms( $course_id = '' ) {
		$categories = array();

		if ( ! empty( $course_id ) ) {
			$terms = wp_get_post_terms(
				$course_id,
				'eb_course_cat',
				array(
					'orderby' => 'name',
					'order'   => 'ASC',
					'fields'  => 'all',
				)
			);
		} else {
			$terms = get_terms(
				array(
					'taxonomy'   => 'eb_course_cat',
					'hide_empty' => false,
				)
			);
		}

		if ( is_array( $terms ) ) {
			foreach ( $terms as $eb_term ) {
				$categories[ $eb_term->term_id ] = esc_html( $eb_term->name );
			}
		}
		return $categories;
	}
}


if ( ! function_exists( 'wdm_eb_get_my_course_url' ) ) {
	/**
	 * Function to send the reuest to the edwiser site.
	 *
	 * @param  array $moodle_user_id moodle_user id.
	 * @param  array $mdl_course_id course id.
	 * @return array returns course URL.
	 */
	function wdm_eb_get_my_course_url( $moodle_user_id, $mdl_course_id ) {
		if ( '' !== $moodle_user_id && function_exists( 'app\wisdmlabs\edwiserBridgePro\includes\sso\generateMoodleUrl' ) ) {
			$query      = array(
				'moodle_user_id'   => $moodle_user_id, // moodle user id.
				'moodle_course_id' => $mdl_course_id,
			);
			$course_url = \app\wisdmlabs\edwiserBridgePro\includes\sso\generateMoodleUrl( $query );
		} else {
			$eb_access_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();
			$course_url    = $eb_access_url . '/course/view.php?id=' . $mdl_course_id;
		}

		return $course_url;
	}
}
if ( ! function_exists( 'wdm_eb_get_header' ) ) {
	/**
	 * Function to check if theme is block theme and 2022 theme then echo hardcoded header value.
	 * else print get_header()
	 */
	function wdm_eb_get_header() {
		$my_theme = wp_get_theme();

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() && 'Twenty Twenty-Two' === $my_theme->get( 'Name' ) ) {
			do_action( 'get_header', null, array() );

			$var = array(
				'blockName'    => 'core/template-part',
				'attrs'        => array(
					'slug'  => 'header',
					'theme' => 'twentytwentytwo',
				),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);

			echo render_block( $var ); // phpcs:ignore WordPress.Security.EscapeOutput
			wp_head();
		} else {
			get_header();
		}
	}
}



if ( ! function_exists( 'wdm_eb_get_footer' ) ) {
	/**
	 * Function to check if theme is block theme and 2022 theme then echo hardcoded header value.
	 * else print get_header()
	 */
	function wdm_eb_get_footer() {
		$my_theme = wp_get_theme();

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() && 'Twenty Twenty-Two' === $my_theme->get( 'Name' ) ) {
			do_action( 'get_footer', null, array() );

			$var = array(
				'blockName'    => 'core/template-part',
				'attrs'        => array(
					'slug'  => 'footer',
					'theme' => 'twentytwentytwo',
				),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);

			echo render_block( $var ); // phpcs:ignore WordPress.Security.EscapeOutput
			wp_footer();
		} else {
			get_footer();
		}
	}
}

if ( ! function_exists( 'wdm_eb_get_sidebar' ) ) {
	/**
	 * Function to check if theme is block theme and 2022 theme then echo hardcoded header value.
	 * else print get_header()
	 */
	function wdm_eb_get_sidebar() {

		$my_theme = wp_get_theme();

		if ( ( function_exists( 'wp_is_block_theme' ) && ! wp_is_block_theme() ) || 'Twenty Twenty-Two' !== $my_theme->get( 'Name' ) ) {
			get_sidebar();
		}
	}
}


if ( ! function_exists( 'wdm_eb_get_comments' ) ) {
	/**
	 * Function to check if theme is block theme and 2022 theme then echo comments block section value.
	 * else print comments_template()
	 */
	function wdm_eb_get_comments() {
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			$var = array(
				'blockName'    => 'core/post-comments',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);

			echo render_block( $var ); // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			comments_template();
		}
	}
}

if ( ! function_exists( 'is_access_exception' ) ) {
	/**
	 * Function to check if response is access exception.
	 *
	 * @param  array $response response.
	 */
	function is_access_exception( $response ) {
		$exception = false;
		if ( isset( $response['response_body']->exception ) && 'webservice_access_exception' === $response['response_body']->exception ) {
			$exception = true;
		}
		return $exception;
	}
}

if ( ! function_exists( 'wdm_eb_recaptcha_type' ) ) {
	/**
	 * Function to check if recaptcha is enabled.
	 */
	function wdm_eb_recaptcha_type() {
		$general_settings = get_option( 'eb_general' );
		if ( isset( $general_settings['eb_enable_recaptcha'] ) && 'yes' === $general_settings['eb_enable_recaptcha'] ) {
			return isset( $general_settings['eb_recaptcha_type'] ) ? $general_settings['eb_recaptcha_type'] : 'v2';
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'wdm_eb_render_recaptcha_v2' ) ) {
	/**
	 * Function to render recaptcha v2.
	 *
	 * @param  string $action action.
	 */
	function wdm_eb_render_recaptcha_v2( $action ) {
		$general_settings = get_option( 'eb_general' );
		if ( isset( $general_settings['eb_enable_recaptcha'] ) && 'yes' === $general_settings['eb_enable_recaptcha'] ) {
			$recaptcha_type     = isset( $general_settings['eb_recaptcha_type'] ) ? $general_settings['eb_recaptcha_type'] : 'v2';
			$recaptcha_site_key = isset( $general_settings['eb_recaptcha_site_key'] ) ? $general_settings['eb_recaptcha_site_key'] : '';
			if ( 'wdm_login' === $action ) {
				$show_recaptcha = isset( $general_settings['eb_recaptcha_show_on_login'] ) ? $general_settings['eb_recaptcha_show_on_login'] : 'no';
				if ( 'yes' !== $show_recaptcha ) {
					return;
				}
			} elseif ( 'register' === $action ) {
				$show_recaptcha = isset( $general_settings['eb_recaptcha_show_on_register'] ) ? $general_settings['eb_recaptcha_show_on_register'] : 'no';
				if ( 'yes' !== $show_recaptcha ) {
					return;
				}
			}
			if ( ! empty( $recaptcha_site_key ) && 'v2' === $recaptcha_type ) {
				?>
				<div class="g-recaptcha form-row form-row-wide eb-profile-txt-field" style="margin-bottom:25px;" data-sitekey="<?php echo esc_attr( $recaptcha_site_key ); ?>"></div>
				<?php
			}
		}
	}
}

if ( ! function_exists( 'wdm_eb_render_recaptcha_v3' ) ) {
	/**
	 * Function to render recaptcha v3.
	 *
	 * @param  string $action action.
	 */
	function wdm_eb_render_recaptcha_v3( $action ) {
		$general_settings = get_option( 'eb_general' );
		if ( isset( $general_settings['eb_enable_recaptcha'] ) && 'yes' === $general_settings['eb_enable_recaptcha'] ) {
			$recaptcha_type     = isset( $general_settings['eb_recaptcha_type'] ) ? $general_settings['eb_recaptcha_type'] : 'v2';
			$recaptcha_site_key = isset( $general_settings['eb_recaptcha_site_key'] ) ? $general_settings['eb_recaptcha_site_key'] : '';
			if ( ! empty( $recaptcha_site_key ) && 'v3' === $recaptcha_type ) {
				if ( 'wdm_login' === $action ) {
					$show_recaptcha = isset( $general_settings['eb_recaptcha_show_on_login'] ) ? $general_settings['eb_recaptcha_show_on_login'] : 'no';
					if ( 'yes' !== $show_recaptcha ) {
						?>
						<input type="submit" class="eb-login-button button button-primary et_pb_button et_pb_contact_submit" name="wdm_login" value="<?php esc_html_e( 'Login', 'edwiser-bridge' ); ?>" />
						<?php
						return;
					}
					$text  = esc_html__( 'Login', 'edwiser-bridge' );
					$class = 'eb-login-button';
				} elseif ( 'register' === $action ) {
					$show_recaptcha = isset( $general_settings['eb_recaptcha_show_on_register'] ) ? $general_settings['eb_recaptcha_show_on_register'] : 'no';
					if ( 'yes' !== $show_recaptcha ) {
						?>
						<input type="submit" class="eb-reg-button button button-primary et_pb_button et_pb_contact_submit" name="register" value="<?php esc_html_e( 'Register', 'edwiser-bridge' ); ?>" />
						<?php
						return;
					}
					$text  = esc_html__( 'Register', 'edwiser-bridge' );
					$class = 'eb-reg-button';
				} else {
					return;
				}
				?>
				<button data-sitekey="<?php echo esc_attr( $recaptcha_site_key ); ?>" data-callback='ebSubmitCaptchaForm' data-action='submit' class="g-recaptcha <?php echo esc_attr( $class ); ?> button button-primary et_pb_button et_pb_contact_submit" ><?php echo esc_attr( $text ); ?></button>
				<input type="hidden" name="<?php echo esc_attr( $action ); ?>" value="<?php echo esc_attr( $text ); ?>">
				<?php
			}
		}
	}
}

if ( ! function_exists( 'add_beacon_helpscout_script' ) ) {
	/**
	 * Add the Helpscout Beacon script on the PEP backend pages.
	 * Callback to action hook 'quoteup_pep_backend_page'.
	 */
	function add_beacon_helpscout_script() {
		?>
		<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
		<script type="text/javascript">window.Beacon('init', 'f087eb3e-6529-4c38-9056-93f9e1b27718')</script>
		<?php
	}
}

if ( ! function_exists( 'wdm_log_json ' ) ) {
	/**
	 * Function to write the error log in json format.
	 *
	 * @param  array $log_data error log data.
	 */
	function wdm_log_json( $log_data ) {

		$log_file = wdm_edwiser_bridge_plugin_log_dir() . 'log.json';

		if ( file_get_contents( $log_file ) ) { // @codingStandardsIgnoreLine
			$log_data_old = file_get_contents( $log_file ); // @codingStandardsIgnoreLine
			$log_data_old = json_decode( $log_data_old, true );
		} else {
			$log_data_old = array();
		}

		if ( ! is_array( $log_data_old ) ) {
			$log_data_old = array();
		}
		$log_data_old[] = array(
			'time'   => date_i18n( 'm-d-Y @ H:i:s' ),
			'status' => 'NEW',
			'data'   => $log_data,
		);
		$log_data_old   = json_encode( $log_data_old ); // @codingStandardsIgnoreLine
		file_put_contents( $log_file, $log_data_old ); // @codingStandardsIgnoreLine

	}
}

if ( ! function_exists( 'eb_is_legacy_pro' ) ) {
	/**
	 * Function to check if any of the pro extension is active.
	 *
	 * @param  boolean $check_license check if the license is active.
	 * @return boolean
	 */
	function eb_is_legacy_pro( $check_license = false ) {
		$pro = false;
		if ( ! $check_license ) {
			$extensions = array(
				'woocommerce-integration/bridge-woocommerce.php',
				'selective-synchronization/selective-synchronization.php',
				'edwiser-bridge-sso/sso.php',
				'edwiser-multiple-users-course-purchase/edwiser-multiple-users-course-purchase.php',
				'edwiser-custom-fields/edwiser-custom-fields.php',
			);
			foreach ( $extensions as $plugin_path ) {
				if ( is_plugin_active( $plugin_path ) ) {
					$pro = true;
					break;
				} else {
					$pro = false;
				}
			}
		} else {
			$extensions = array(
				'single_sign_on',
				'woocommerce_integration',
				'selective_sync',
				'edwiser_custom_fields',
				'bulk-purchase',
			);
			foreach ( $extensions as $extension ) {
				$license = get_option( 'edd_' . $extension . '_license_status' );
				if ( ! empty( $license ) ) {
					$pro = true;
					break;
				} else {
					$pro = false;
				}
			}
		}

		return $pro;
	}
}

if ( ! function_exists( 'add_edwiser_header_content' ) ) {
	/**
	 * Function to add the header content on the Edwiser Bridge settings page.
	 * Callback to action hook 'eb_settings_page_header'.
	 */
	function add_edwiser_header_content() {
		?>
		<div class="edwiser-settings-header">
			<div class="edwiser-settings-header-logo">
				<img src="<?php echo esc_url( plugins_url( 'images/logo.png', dirname( __FILE__ ) ) ); ?>" alt="Edwiser Bridge Logo" />
				<p><?php esc_html_e( 'Edwiser Bridge', 'edwiser-bridge' ); ?></p>
			</div>
		</div>
		<div class="edwiser-rating">
			<div class="edwiser-rating-customer">
				<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M8.57153 16C10.6933 16 12.7281 15.1571 14.2284 13.6569C15.7287 12.1566 16.5715 10.1217 16.5715 8C16.5715 5.87827 15.7287 3.84344 14.2284 2.34315C12.7281 0.842855 10.6933 0 8.57153 0C6.4498 0 4.41497 0.842855 2.91468 2.34315C1.41439 3.84344 0.571533 5.87827 0.571533 8C0.571533 10.1217 1.41439 12.1566 2.91468 13.6569C4.41497 15.1571 6.4498 16 8.57153 16ZM5.69966 10.1719C6.25903 10.8188 7.21528 11.5 8.57153 11.5C9.92778 11.5 10.884 10.8188 11.4434 10.1719C11.6247 9.9625 11.9403 9.94063 12.1497 10.1219C12.359 10.3031 12.3809 10.6187 12.1997 10.8281C11.5028 11.6281 10.2934 12.5 8.57153 12.5C6.84966 12.5 5.64028 11.6281 4.94341 10.8281C4.76216 10.6187 4.78403 10.3031 4.99341 10.1219C5.20278 9.94063 5.51841 9.9625 5.69966 10.1719ZM5.08403 6.5C5.08403 6.23478 5.18939 5.98043 5.37693 5.79289C5.56446 5.60536 5.81882 5.5 6.08403 5.5C6.34925 5.5 6.6036 5.60536 6.79114 5.79289C6.97868 5.98043 7.08403 6.23478 7.08403 6.5C7.08403 6.76522 6.97868 7.01957 6.79114 7.20711C6.6036 7.39464 6.34925 7.5 6.08403 7.5C5.81882 7.5 5.56446 7.39464 5.37693 7.20711C5.18939 7.01957 5.08403 6.76522 5.08403 6.5ZM11.084 5.5C11.3492 5.5 11.6036 5.60536 11.7911 5.79289C11.9787 5.98043 12.084 6.23478 12.084 6.5C12.084 6.76522 11.9787 7.01957 11.7911 7.20711C11.6036 7.39464 11.3492 7.5 11.084 7.5C10.8188 7.5 10.5645 7.39464 10.3769 7.20711C10.1894 7.01957 10.084 6.76522 10.084 6.5C10.084 6.23478 10.1894 5.98043 10.3769 5.79289C10.5645 5.60536 10.8188 5.5 11.084 5.5Z" fill="#FFB900"/>
				</svg>
				5k+ Customers
			</div>
			<div class="edwiser-rating-rate">
				<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M7.32303 0.450431C7.53611 -0.150144 8.46389 -0.150143 8.67697 0.450432L10.0775 4.39798C10.1728 4.66656 10.4461 4.84841 10.7545 4.84841H15.2868C15.9764 4.84841 16.2631 5.65648 15.7052 6.02766L12.0385 8.46737C11.789 8.63337 11.6846 8.9276 11.7799 9.19619L13.1805 13.1437C13.3936 13.7443 12.643 14.2437 12.0851 13.8725L8.41839 11.4328C8.16891 11.2668 7.83109 11.2668 7.58161 11.4328L3.91488 13.8725C3.35703 14.2437 2.60644 13.7443 2.81952 13.1437L4.22009 9.19619C4.31538 8.9276 4.21099 8.63337 3.96151 8.46737L0.294781 6.02765C-0.263071 5.65648 0.0236278 4.84841 0.71317 4.84841H5.2455C5.55387 4.84841 5.82717 4.66656 5.92247 4.39798L7.32303 0.450431Z" fill="#FFB900"/>
				</svg>
				4.5
				<a target="_blank" class="eb-rate-us-btn" href="https://wordpress.org/support/plugin/edwiser-bridge/reviews/"><?php esc_html_e( 'Rate Us', 'edwiser-bridge' ); ?></a>
			</div>
		</div>
		<h2></h2>
		<?php
	}
}
