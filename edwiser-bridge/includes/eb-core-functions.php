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
        wdmUpdatePageId($option_value, $option_key, $page_found_id, $eb_general_settings);
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
    $page_id   = wp_insert_post($page_data);
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
        echo '<span>' . USER_FORM_MESSAGE . '</span><br />';
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
    $eb_settings    = get_option('eb_general');

    if (isset($eb_settings['eb_useraccount_page_id'])) {
        $usr_ac_page_id = $eb_settings['eb_useraccount_page_id'];
    }

    $usr_ac_page_url = get_permalink($usr_ac_page_id);

    if (!$usr_ac_page_url) {
        $usr_ac_page_url = site_url('/user-account');
    }

    //Extract query string into local $_GET array.
    $get             = array();
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
        $usrAcPageId  = $ebSettings['eb_useraccount_page_id'];
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
    $get          = array();
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
            'user_id'                           => '',
            'my_courses_wrapper_title'          => __('My Courses', 'eb-textdomain'),
            'recommended_courses_wrapper_title' => __('Recommended Courses', 'eb-textdomain'),
            'number_of_recommended_courses'     => 4,
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
    foreach ($shortcodes as $tag => $args) {
        $buffer = '[' . $tag . ' ';
        foreach ($args as $attr => $value) {
            $buffer .= $attr . '="' . $value . '" ';
        }
        $buffer             .= ']';
        $page_content[$tag] = $buffer;
    }

    if (empty($the_tag)) {
        return $page_content;
    } elseif (isset($page_content[$the_tag])) {
        return $page_content[$the_tag];
    }
}

/**
 * Provides the functionality to get the current PayPal currency symbol.
 *
 * @return mixed returns the currency in string format or symbol
 */
function getCurrentPayPalcurrencySymb()
{
    $payment_options = get_option('eb_paypal');
    $currency        = $payment_options['eb_paypal_currency'];
    if (isset($payment_options['eb_paypal_currency']) && $payment_options['eb_paypal_currency'] == 'USD') {
        $currency = '$';
    }
    $currency = apply_filters('eb_paypal_get_currancy_symbol', $currency);

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
function getArrValue($arr, $key, $value = '')
{
    if (isset($arr[$key]) && !empty($arr[$key])) {
        $value = $arr[$key];
    }

    return $value;
}

function updateOrderHistMeta($orderId, $updatedBy, $note)
{
    $history = get_post_meta($orderId, 'eb_order_status_history', true);
    if (!is_array($history)) {
        $history = array();
    }
    $newHist = array(
        'by'   => $updatedBy,
        'time' => current_time('timestamp'),
        'note' => $note,
    );

    array_unshift($history, $newHist);
    $history = apply_filters('eb_order_history', $history, $newHist, $orderId);
    update_post_meta($orderId, 'eb_order_status_history', $history);
    do_action('eb_after_order_refund_meta_save', $orderId, $history);
}

function getTotalRefundAmt($refunds)
{
    $totalRefund = (float) "0.00";
    foreach ($refunds as $refund) {
        $refundAmt   = getArrValue($refund, "amt", "0.00");
        $totalRefund += (float) $refundAmt;
    }

    return $totalRefund;
}


function getAllEbSourses($postId = 0)
{
    $posts = get_posts(
        array(
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_type' => 'eb_course'
        )
    );

    if ($postId) {
        $key = array_search($postId, $posts);
        if ($key !== false) {
            unset($posts[$key]);
        }
    }

    $postsWithTitle = array();
    foreach ($posts as $value) {
        $postsWithTitle[$value] = get_the_title($value);
    }
    return $postsWithTitle;
}
