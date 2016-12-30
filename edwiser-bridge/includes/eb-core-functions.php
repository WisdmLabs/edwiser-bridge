<?php

/**
 * Get a log file path.
 *
 * @since 1.0.0
 *
 * @param string $handle name
 *
 * @return string the log file path
 */
function wdmLogFilePath($handle)
{
    return trailingslashit(EB_LOG_DIR).$handle.'-'.sanitize_file_name(wp_hash($handle)).'.log';
}

/**
 * Create a page and store the ID in an option.
 *
 * @param mixed  $slug         Slug for the new page
 * @param string $option_key   Option name to store the page's ID
 * @param string $page_title   (default: '') Title for the new page
 * @param string $page_content (default: '') Content for the new page
 * @param int    $post_parent  (default: 0) Parent for the new page
 *
 * @return int page ID
 */
function wdmCreatePage($slug, $option_key = '', $page_title = '', $page_content = '')
{
    global $wpdb;

    // get all settings of settings general tab
    $eb_general_settings = array();
    $eb_general_settings = get_option('eb_general', array());

    $option_value = 0;
    if (trim($option_key) != '') {
        // if (empty($eb_general_settings)) {
        //     $eb_general_settings = array();
        // }

        if (isset($eb_general_settings[$option_key])) {
            $option_value = $eb_general_settings[$option_key];
        }
    }

    if ($option_value > 0 && get_post($option_value)) {
        return -1;
    }

    if (strlen($page_content) > 0) {
        // Search for an existing page with the specified page content (typically a shortcode)
        $page_found_id = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT ID FROM '.$wpdb->posts."
                WHERE post_type='page' AND post_content LIKE %s LIMIT 1;",
                "%{$page_content}%"
            )
        );
    } else {
        // Search for an existing page with the specified page slug
        $page_found_id = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT ID FROM '.$wpdb->posts."
                WHERE post_type='page' AND post_name = %s LIMIT 1;",
                $slug
            )
        );
    }

    if ($page_found_id) {
        // if ($option_value == '' && trim($option_key) != '') {
        //     // update the page id in general settings
        //     $eb_general_settings[$option_key] = $page_found_id;
        //     update_option('eb_general', $eb_general_settings);
        // }
        wdmUpdatePageId($option_value, $option_key, $page_found_id, $eb_general_settings);

        return $page_found_id;
    }

    $page_data = array(
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 1,
        'post_name' => $slug,
        'post_title' => $page_title,
        'post_content' => $page_content,
        'comment_status' => 'closed',
    );
    $page_id = wp_insert_post($page_data);

    // update the page id in general settings
    // if ($option_value == '' && trim($option_key) != '') {
    //     $eb_general_settings[$option_key] = $page_id;
    //     update_option('eb_general', $eb_general_settings);
    // }
    wdmUpdatePageId($option_value, $option_key, $page_id, $eb_general_settings);

    return $page_id;
}

function wdmUpdatePageId($option_value, $option_key, $_id, &$eb_general_settings)
{
    if ($option_value == '' && trim($option_key) != '') {
        $eb_general_settings[$option_key] = $_id;
        update_option('eb_general', $eb_general_settings);
    }
}

// add messages
function wdmAddNotices($message)
{
    define('USER_FORM_MESSAGE', $message);
}

// display messages
function wdmShowNotices()
{
    //display form messages
    if (defined('USER_FORM_MESSAGE')) {
        echo "<div class='wdm-flash-error'>";
        echo '<span>'.USER_FORM_MESSAGE.'</span><br />';
        echo '</div>';
    }
}

/*
  //Old wdmUserAccountUrl() removed because of permalink issue.
  function wdmUserAccountUrl($arg = '')
  {
  $eb_general_settings = get_option('eb_general');
  $user_account_page_id = '';
  if (isset($eb_general_settings['eb_useraccount_page_id'])) {
  $user_account_page_id = $eb_general_settings['eb_useraccount_page_id'];
  }

  if (!is_numeric($user_account_page_id)) {
  $link = site_url('/user-account').$arg;
  } else {
  $link = get_permalink($user_account_page_id).$arg;
  }

  return $link;
  }
 */

/**
 * Remodified wdmUserAccountUrl() to return user account url.
 *
 * @since 1.2.0
 */
function wdmUserAccountUrl($query_str = '')
{
    $usr_ac_page_id = null;
    $eb_settings = get_option('eb_general');

    if (isset($eb_settings['eb_useraccount_page_id'])) {
        $usr_ac_page_id = $eb_settings['eb_useraccount_page_id'];
    }

    $usr_ac_page_url = get_permalink($usr_ac_page_id);

    if (!$usr_ac_page_url) {
        $usr_ac_page_url = site_url('/user-account');
    }

    //Extract query string into local $_GET array.
    $get = array();
    parse_str(parse_url($query_str, PHP_URL_QUERY), $get);
    $usr_ac_page_url = add_query_arg($get, $usr_ac_page_url);

    return $usr_ac_page_url;
}

/**
 * Provides the functionality to calculate the user login redirect url.
 *
 * @return URL Returns the my courses page url if the flag is true otherwise
 *             returns the default $usr_ac_page_url.
 *
 * @since 1.2.0
 */
function wdmEBUserRedirectUrl($queryStr = '')
{
    $usrAcPageId = null;
    /*
     * Set default user account page url
     */
//    $usrAcPageUrl = site_url('/user-account');

    /*
     * Get the Edwiser Bridge genral settings.
     */
    $ebSettings = get_option('eb_general');
    
    /*
     * Set the login redirect url to the user account page.
     */
    if (isset($ebSettings['eb_useraccount_page_id'])) {
        $usrAcPageId = $ebSettings['eb_useraccount_page_id'];
        $usrAcPageUrl = get_permalink($usrAcPageId);
    }
    /*
     * Sets $usrAcPageUrl to my course page if the redirection to the my 
     * courses page is enabled in settings 
     */
    if (isset($ebSettings['eb_enable_my_courses']) && $ebSettings['eb_enable_my_courses'] == 'yes') {
        $usrAcPageUrl = getMycoursesPage($ebSettings);
    }

    //Extract query string into local $_GET array.
    $get = array();
    parse_str(parse_url($queryStr, PHP_URL_QUERY), $get);
    $usrAcPageUrl = add_query_arg($get, $usrAcPageUrl);

    return $usrAcPageUrl;
}

function getMycoursesPage($ebSettings)
{
    $usrAcPageUrl = site_url('/user-account');
    if (isset($ebSettings['eb_my_courses_page_id'])) {
        $usrAcPageUrl = get_permalink($ebSettings['eb_my_courses_page_id']);
    }
    return $usrAcPageUrl;
}

// used as a callback for usort() to sort a numeric array
function usortNumericCallback($element1, $element2)
{
    return $element1->id - $element2->id;
}

/**
 * Function returns shortcode pages content.
 *
 * @since 1.2.0
 */
function getShortcodePageContent($the_tag = '')
{
    //Shortcodes and their attributes.
    $shortcodes = array(
        'eb_my_courses' => array(
            'user_id' => '',
            'my_courses_wrapper_title' => __('My Courses', 'eb-textdomain'),
            'recommended_courses_wrapper_title' => __('Recommended Courses', 'eb-textdomain'),
            'number_of_recommended_courses' => 4,
        ),
        'eb_course' => array(
            'id' => '',
        ),
        'eb_courses' => array(
            'categories' => '',
            'category_operator' => 'AND',
            'order' => 'DESC',
            'per_page' => 12,
        ),
    );

    $page_content = array();
    foreach ($shortcodes as $tag => $args) {
        $buffer = '['.$tag.' ';
        foreach ($args as $attr => $value) {
            $buffer .= $attr.'="'.$value.'" ';
        }
        $buffer .= ']';
        $page_content[$tag] = $buffer;
    }

    if (empty($the_tag)) {
        return $page_content;
    } elseif (isset($page_content[$the_tag])) {
        return $page_content[$the_tag];
    }
}
