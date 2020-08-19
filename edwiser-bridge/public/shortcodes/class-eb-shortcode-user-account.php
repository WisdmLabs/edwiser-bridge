<?php
/**
 * The file that defines the user account shortcode.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EbShortcodeUserAccount
{
    /**
     * Get the shortcode content.
     *
     * @since  1.0.0
     *
     * @param array $atts
     *
     * @return string
     */
    public static function get($atts)
    {
        return EbShortcodes::shortcodeWrapper(array(__CLASS__, 'output'), $atts);
    }
    /**
     * Output the shortcode.
     *
     * @since  1.0.0
     *
     * @param array $atts
     */
    public static function getInstance()
    {
        return new EbShortcodeUserAccount();
    }

    public static function output($atts)
    {
        if (!is_user_logged_in()) {
            $template_loader = new EbTemplateLoader(
                edwiserBridgeInstance()->getPluginName(),
                edwiserBridgeInstance()->getVersion()
            );
            $template_loader->wpGetTemplate('account/form-login.php');
        } else {
            self::userAccount($atts);
        }
    }

    /**
     * User account page.
     *
     * @since  1.0.0
     *
     * @param array $atts
     */
    private static function userAccount($atts)
    {
        extract(
            shortcode_atts(
                array(
                    'user_id' => isset($atts['user_id']) ? $atts['user_id'] : '',
                    ),
                $atts
            )
        );
        if ($user_id != '') {
            $user      = get_user_by('id', $user_id);
            $user_meta = get_user_meta($user_id);
        } else {
            $user      = wp_get_current_user();
            $user_id   = $user->ID;
            $user_meta = get_user_meta($user_id);
        }
        $user_avatar = get_avatar($user_id, 125);
        $course_args = array(
            'post_type'      => 'eb_course',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );
        // fetch courses
        $courses     = get_posts($course_args);
        // remove course from array in which user is not enrolled
        foreach ($courses as $key => $course) {
            $has_access = edwiserBridgeInstance()->enrollmentManager()->userHasCourseAccess($user_id, $course->ID);
            if (!$has_access) {
                unset($courses[$key]);
            }
        }
        if (is_array($courses)) {
            $courses = array_values($courses); // reset array keys
        } else {
            $courses = array();
        }
        // Course Purchase History.
        $user_orders     = array(); // users completed orders
        $order_count     = 15;
        //
        $user_orders     = self::getUserOrders($user_id);
        $template_loader = new EbTemplateLoader(
            edwiserBridgeInstance()->getPluginName(),
            edwiserBridgeInstance()->getVersion()
        );

        $template_loader->wpGetTemplate(
            'account/user-account.php',
            array(
            // CPH
            'current_user'     => get_user_by('id', get_current_user_id()),
            'user_orders'      => $user_orders,
            'order_count'      => $order_count,
            // User profile
            'user_avatar'      => $user_avatar,
            'user'             => $user,
            'user_meta'        => $user_meta,
            'enrolled_courses' => $courses,
            'template_loader'  => $template_loader
                )
        );
    }

    public static function getUserOrders($user_id)
    {
        $user_orders    = array();
        $user_id;
        // get all completed orders of a user
        $args           = array(
            'posts_per_page' => -1,
            'meta_key'       => '',
            'post_type'      => 'eb_order',
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'order'          => 'ASC',
        );
        $overall_orders = get_posts($args); // get all orders from db
        foreach ($overall_orders as $order_id) {
            $order_detail = get_post_meta($order_id, 'eb_order_options', true);

            if (!empty($order_detail) && $order_detail['buyer_id'] == $user_id) {
                $user_orders[] = array(
                    // 'order_id'      => $order_id,
                    'eb_order_id'      => $order_id, // cahnged 1.4.7 Order Id 
                    'ordered_item'  => $order_detail['course_id'],
                    'billing_email' => isset($order_detail['billing_email']) ? $order_detail['billing_email'] : '-',
                    'currency'      => isset($order_detail['currency']) ? $order_detail['currency'] : '$',
                    'amount_paid'   => isset($order_detail['amount_paid']) ? $order_detail['amount_paid'] : '',
                    'status'        => isset($order_detail['order_status']) ? $order_detail['order_status'] : '',
                    'date'          => get_the_date("Y-m-d", $order_id),
                );
            }
        }
        return apply_filters("eb_user_orders", $user_orders);
    }

    public static function saveAccountDetails()
    {
        if (self::isUpdateUserProfile()) {
            $user         = new \stdClass();
            $user->ID     = (int) get_current_user_id();
            $current_user = get_user_by('id', $user->ID);
            if ($user->ID > 0) {
                if (isset($_SESSION['eb_msgs_' . $current_user->ID])) {
                    unset($_SESSION['eb_msgs_' . $current_user->ID]);
                }
                $posted_data = self::getPostedData();
                $errors      = self::getErrors($posted_data);
                if (count($errors)) {
                    $_SESSION['eb_msgs_' . $user->ID] = '<p class="eb-error">' . implode("<br />", $errors) . '</p>';
                } else {
                    // Profile updated on Moodle successfully.
                    if (self::updateMoodleProfile($posted_data)) {
                        self::updateWordPressProfile($posted_data);
                        $_SESSION['eb_msgs_' . $user->ID] = '<p class="eb-success">' . __('Account details saved successfully.', 'eb-textdomain') . '</p>';
                        do_action('eb_save_account_details', $user->ID);
                    } else {
                        $_SESSION['eb_msgs_' . $user->ID] = '<p class="eb-error">' . __('Couldn\'t update your profile! This might be because wrong data sent to Moodle site or a Connection Error.', 'eb-textdomain') . '</p>';
                    }
                }
            }
        }
    }

    public static function isUpdateUserProfile()
    {
        if ('POST' !== strtoupper($_SERVER['REQUEST_METHOD'])) {
            return false;
        }
        if (empty($_POST['action']) || 'eb-update-user' !== $_POST['action'] || empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'eb-update-user')) {
            return false;
        }
        return true;
    }

    public static function getPostedData()
    {
        $posted_data                = array();
//        $posted_data['username']    = self::getPostedField('username');
        $posted_data['first_name']  = self::getPostedField('first_name');
        $posted_data['last_name']   = self::getPostedField('last_name');
        $posted_data['nickname']    = self::getPostedField('nickname');
        $posted_data['email']       = self::getPostedField('email');
        $posted_data['curr_psw']    = self::getPostedField('curr_psw', false);
        $posted_data['new_psw']     = self::getPostedField('new_psw', false);
        $posted_data['confirm_psw'] = self::getPostedField('confirm_psw', false);
        $posted_data['description'] = self::getPostedField('description');
        $posted_data['country']     = self::getPostedField('country');
        $posted_data['city']        = self::getPostedField('city');
        return $posted_data;
    }

    public static function getPostedField($fieldname, $sanitize = true)
    {
        $val = '';
        if (isset($_POST[$fieldname]) && !empty($_POST[$fieldname])) {
            $val = $_POST[$fieldname];
            if ($sanitize) {
                $val = sanitize_text_field($val);
            }
        }
        return $val;
    }

    public static function getErrors($posted_data)
    {
        $user            = new \stdClass();
        $user->ID        = (int) get_current_user_id();
        $current_user    = get_user_by('id', $user->ID);
        $errors          = array();
        $required_fields = apply_filters('eb_save_account_details_required_fields', array(
            'email'    => __('Email Address', 'eb-textdomain'),
        ));

        foreach ($required_fields as $field_key => $field_name) {
            if (empty($posted_data[$field_key])) {
                $errors[] = sprintf(__('%s is required field.', 'eb-textdomain'), '<strong>' . $field_name . '</strong>');
            }
        }
        $email    = sanitize_email($posted_data['email']);
        $currPsw  = sanitize_user($posted_data['curr_psw']);
        $pass1    = sanitize_user($posted_data['new_psw']);
        $pass2    = sanitize_user($posted_data['confirm_psw']);

        if (!is_email($email)) {
            $errors[] = sprintf(__('%s is invalid email.', 'eb-textdomain'), '<strong>' . $email . '</strong>');
        } elseif (email_exists($email) && $email !== $current_user->user_email) {
            $errors[] = sprintf(__('%s is already exists.', 'eb-textdomain'), '<strong>' . $email . '</strong>');
        }

        if (!empty($currPsw) && empty($pass1) && empty($pass2)) {
            $errors[] = __('Please fill out all password fields.', 'eb-textdomain');
        } elseif (!empty($pass1) && empty($currPsw)) {
            $errors[] = __('Please enter your current password.', 'eb-textdomain');
        } elseif (!empty($pass1) && empty($pass2)) {
            $errors[] = __('Please re-enter your password.', 'eb-textdomain');
        } elseif ((!empty($pass1) || !empty($pass2) ) && $pass1 !== $pass2) {
            $errors[] = __('New passwords do not match.', 'eb-textdomain');
        } elseif (!empty($pass1) && !wp_check_password($currPsw, $current_user->user_pass, $current_user->ID)) {
            $errors[] = __('Your current password is incorrect.', 'eb-textdomain');
        }
        return $errors;
    }

    public static function updateMoodleProfile($posted_data)
    {
        $user     = new \stdClass();
        $user->ID = (int) get_current_user_id();
        // Update Moodle profile.
        $mdl_uid  = get_user_meta($user->ID, 'moodle_user_id', true);
        if (is_numeric($mdl_uid)) {
            $user_data = array(
                'id'            => (int) $mdl_uid,
                'email'         => $posted_data['email'],
                'firstname'     => $posted_data['first_name'],
                'lastname'      => $posted_data['last_name'],
                'alternatename' => $posted_data['nickname'],
                'auth'          => 'manual',
                'city'          => $posted_data['city'],
                'country'       => $posted_data['country'] ? $posted_data['country'] : '',
                'description'   => $posted_data['description'],
            );
            if (isset($posted_data['new_psw']) && !empty($posted_data['new_psw'])) {
                $user_data['password'] = $posted_data['new_psw'];
            }
            $user_manager = new EBUserManager('edwiserbridge', EB_VERSION);
            $response     = $user_manager->createMoodleUser($user_data, 1);
            if (isset($response['user_updated']) && $response['user_updated']) {
                return true;
            }
        }
        return false;
    }

    public static function updateWordPressProfile($posted_data)
    {
        $user     = new \stdClass();
        $user->ID = (int) get_current_user_id();
        // Update WP profile.
        update_user_meta($user->ID, 'city', $posted_data['city']);
        update_user_meta($user->ID, 'country', $posted_data['country']);
        $args     = array(
            'ID'          => $user->ID,
            'user_email'  => $posted_data['email'],
            'first_name'  => $posted_data['first_name'],
            'last_name'   => $posted_data['last_name'],
            'nickname'    => $posted_data['nickname'],
            'description' => $posted_data['description']
        );
        if (isset($posted_data['new_psw']) && !empty($posted_data['new_psw'])) {
            $args['user_pass'] = $posted_data['new_psw'];
        }
        wp_update_user($args);
    }

    public function getUserAccountNavigationItems()
    {
        return apply_filters(
            'eb_user_account_labels',
            array(
                array(
                'label' => __('Dashboard', "eb-textdomain"),
                'href'  => '',
                ''      => __('Dashboard', "eb-textdomain"),
            ),
            array(
                'label' => __('Account Details', "eb-textdomain"),
                'href'  => 'eb-my-profile',
                ''      => __('Edit account details', "eb-textdomain"),
            ),
            array(
                'label' => __('Orders', "eb-textdomain"),
                'href'  => 'eb-orders',
                ''      => __('Course purchase history', "eb-textdomain"),
            ),
            array(
                'label' => __('My Courses', "eb-textdomain"),
                'href'  => 'eb-my-courses',
                ''      => __('My Courses', "eb-textdomain"),
            )
                )
        );
    }

    public function getUserAccountContent($ebActiveLink, $user_orders, $order_count, $user_avatar, $user, $user_meta, $enrolled_courses, $template_loader)
    {
        switch ($ebActiveLink) {
            case '':
                $template_loader->wpGetTemplate(
                    'account/user-data.php',
                    array(
                    'user'        => $user,
                    'user_avatar' => $user_avatar
                        )
                );
                break;

            case 'eb-my-profile':
                $template_loader->wpGetTemplate(
                    'account/edit-user-profile.php',
                    array(
                    'user_avatar'      => $user_avatar,
                    'user'             => $user,
                    'user_meta'        => $user_meta,
                    'enrolled_courses' => $enrolled_courses,
                    'template_loader'  => $template_loader,
                        )
                );

                break;
            case 'eb-orders':
                $template_loader->wpGetTemplate(
                    'account/user-orders.php',
                    array(
                    'user'             => $user,
                    'user_meta'        => $user_meta,
                    'enrolled_courses' => $enrolled_courses,
                    'template_loader'  => $template_loader,
                    'user_orders'      => $user_orders,
                    'user_count'       => $order_count
                        )
                );

                break;
            case 'eb-my-courses':
                $template_loader->wpGetTemplate(
                    'account/my-courses.php',
                    array(
                    'user'             => $user,
                    'user_meta'        => $user_meta,
                    'enrolled_courses' => $enrolled_courses,
                    'template_loader'  => $template_loader,
                        )
                );

                break;
            default:
                do_action('eb_user_account_label_content', $_GET['eb-active-link']);
                break;
        }
    }
}
