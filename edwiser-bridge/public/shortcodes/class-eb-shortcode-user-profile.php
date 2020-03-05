<?php

/**
 * The file that defines the user profile shortcode.
 *
 * @link       https://edwiser.org
 * @since      1.0.2
 * @deprecated 1.2.0 Use shortcode eb_user_account
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EbShortcodeUserProfile
{

    /**
     * Get the shortcode content.
     *
     * @since  1.0.2
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
     * @since  1.0.2
     *
     * @param array $atts
     */
    public static function output($atts)
    {
        if (!is_user_logged_in()) {
            $template_loader = new EbTemplateLoader(
                edwiserBridgeInstance()->getPluginName(),
                edwiserBridgeInstance()->getVersion()
            );
            $template_loader->wpGetTemplate('account/form-login.php');
        } else {
            self::userProfile($atts);
        }
    }

    /**
     * User Profile page.
     *
     * @since  1.0.2
     *
     * @param array $atts
     */
    public static function userProfile($atts)
    {
        extract(
            shortcode_atts(
                array(
                    'user_id' => isset($atts[ 'user_id' ]) ? $atts[ 'user_id' ] : '',
                ),
                $atts
            )
        );

        if ($user_id != '') {
            $user = get_user_by('id', $user_id);
            $user_meta = get_user_meta($user_id);
        } else {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $user_meta = get_user_meta($user_id);
        }

        $user_avatar = get_avatar($user_id, 125);

        $course_args = array(
            'post_type' => 'eb_course',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );

        // fetch courses
        $courses = get_posts($course_args);

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

        // load profile template
        $template_loader = new EbTemplateLoader(
            edwiserBridgeInstance()->getPluginName(),
            edwiserBridgeInstance()->getVersion()
        );
        $template_loader->wpGetTemplate(
            'account/user-profile.php',
            array(
                'user_avatar' => $user_avatar,
                'user' => $user,
                'user_meta' => $user_meta,
                'enrolled_courses' => $courses,
                'template_loader' => $template_loader
            )
        );
    }

    public static function saveAccountDetails()
    {
        if (self::isUpdateUserProfile()) {
            $user         = new \stdClass();
            $user->ID     = (int) get_current_user_id();
            $current_user = get_user_by('id', $user->ID);

            if ($user->ID > 0) {
                if (isset($_SESSION['eb_msgs_'.$current_user->ID])) {
                    unset($_SESSION['eb_msgs_'.$current_user->ID]);
                }
                $posted_data = self::getPostedData();
                $errors = self::getErrors($posted_data);

                if (count($errors)) {
                    $_SESSION['eb_msgs_'.$user->ID] = '<p class="eb-error">' . implode("<br />", $errors) . '</p>';
                } else {
                    // Profile updated on Moodle successfully.
                    if (self::updateMoodleProfile($posted_data)) {
                        self::updateWordPressProfile($posted_data);

                        $_SESSION['eb_msgs_'.$user->ID] = '<p class="eb-success">' . __('Account details saved successfully.', 'eb-textdomain') . '</p>';

                        do_action('eb_save_account_details', $user->ID);
                    } else {
                        $_SESSION['eb_msgs_'.$user->ID] = '<p class="eb-error">' . __('Couldn\'t update your profile! This might be because wrong data sent to Moodle site or a Connection Error.', 'eb-textdomain') . '</p>';
                    }
                }
            }
        }
    }

    public static function isUpdateUserProfile()
    {
        if ('POST' !== strtoupper($_SERVER[ 'REQUEST_METHOD' ])) {
            return false;
        }

        if (empty($_POST[ 'action' ]) || 'eb-update-user' !== $_POST[ 'action' ] || empty($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'eb-update-user')) {
            return false;
        }

        return true;
    }

    public static function getPostedData()
    {
        $posted_data = array();

        $posted_data['username']     = self::getPostedField('username');
        $posted_data['first_name']   = self::getPostedField('first_name');
        $posted_data['last_name']    = self::getPostedField('last_name');
        $posted_data['nickname']     = self::getPostedField('nickname');
        $posted_data['email']        = self::getPostedField('email');
        $posted_data['pass_1']       = self::getPostedField('pass_1', false);
        $posted_data['description']  = self::getPostedField('description');
        $posted_data['country']      = self::getPostedField('country');
        $posted_data['city']         = self::getPostedField('city');

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
        $user         = new \stdClass();
        $user->ID     = (int) get_current_user_id();
        $current_user = get_user_by('id', $user->ID);

        $errors = array();

        $required_fields = apply_filters('eb_save_account_details_required_fields', array(
            'username'   => __('Username', 'eb-textdomain'),
            'email'      => __('Email Address', 'eb-textdomain'),
        ));

        foreach ($required_fields as $field_key => $field_name) {
            if (empty($posted_data[ $field_key ])) {
                $errors[] = sprintf(__('%s is required field.', 'eb-textdomain'), '<strong>' . $field_name . '</strong>');
            }
        }

        $email = sanitize_email($posted_data['email']);
        if (! is_email($email)) {
            $errors[] = sprintf(__('%s is invalid email.', 'eb-textdomain'), '<strong>' . $email . '</strong>');
        } elseif (email_exists($email) && $email !== $current_user->user_email) {
            $errors[] = sprintf(__('%s is already exists.', 'eb-textdomain'), '<strong>' . $email . '</strong>');
        }

        $username = sanitize_user($posted_data['username']);
        if (username_exists($username) && $username !== $current_user->user_login) {
            $errors[] = sprintf(__('%s is already exists.', 'eb-textdomain'), '<strong>' . $username . '</strong>');
        }

        return $errors;
    }

    public static function updateMoodleProfile($posted_data)
    {
        $user         = new \stdClass();
        $user->ID     = (int) get_current_user_id();

        // Update Moodle profile.
        $mdl_uid = get_user_meta($user->ID, 'moodle_user_id', true);
        if (is_numeric($mdl_uid)) {
            $user_data = array(
                'id'            => (int)$mdl_uid,
                'email'         => $posted_data['email'],
                'firstname'     => $posted_data['first_name'],
                'lastname'      => $posted_data['last_name'],
                'alternatename' => $posted_data['nickname'],
                'auth'          => 'manual',
                'city'          => $posted_data['city'],
                'country'       => $posted_data['country'] ? $posted_data['country'] : '',
                'description'   => $posted_data['description'],
            );

            if (isset($posted_data['pass_1']) && ! empty($posted_data['pass_1'])) {
                $user_data['password'] = $posted_data['pass_1'];
            }
            $user_data=  apply_filters("eb_update_moodle_profile_data", $user_data);
            $user_manager = new EBUserManager('edwiserbridge', EB_VERSION);
            $response = $user_manager->createMoodleUser($user_data, 1);

            if (isset($response['user_updated']) && $response['user_updated']) {
                return true;
            }
        }

        return false;
    }

    public static function updateWordPressProfile($posted_data)
    {
        $user         = new \stdClass();
        $user->ID     = (int) get_current_user_id();

        // Update WP profile.
        update_user_meta($user->ID, 'city', $posted_data['city']);
        update_user_meta($user->ID, 'country', $posted_data['country']);

        $args = array(
            'ID'            => $user->ID,
            'user_email'    => $posted_data['email'],
            'first_name'    => $posted_data['first_name'],
            'last_name'     => $posted_data['last_name'],
            'nickname'      => $posted_data['nickname'],
            'description'   => $posted_data['description']
        );
        $args=  apply_filters("eb_wp_update_user_profile", $args);
        if (isset($posted_data['pass_1']) && ! empty($posted_data['pass_1'])) {
            $args['user_pass'] = $posted_data['pass_1'];
        }
        wp_update_user($args);
    }
}
