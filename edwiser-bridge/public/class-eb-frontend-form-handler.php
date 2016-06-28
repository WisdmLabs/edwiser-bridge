<?php

/**
 * Handles frontend form submissions.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EbFrontendFormHandler
{
    /**
     * Process the login form.
     */
    public static function processLogin()
    {
        if (!empty($_POST['wdm_login']) &&
            !empty($_POST['_wpnonce']) &&
            wp_verify_nonce($_POST['_wpnonce'], 'eb-login')) {
            try {
                $creds = array();

                $validation_error = new \WP_Error();
                $validation_error = apply_filters(
                    'eb_process_login_errors',
                    $validation_error,
                    $_POST['wdm_username'],
                    $_POST['wdm_password']
                );

                if ($validation_error->get_error_code()) {
                    throw new \Exception(
                        '<strong>'.
                        __('Error', 'eb-textdomain').
                        ':</strong> '.
                        $validation_error->get_error_message()
                    );
                }

                if (empty($_POST['wdm_username'])) {
                    throw new \Exception(
                        '<strong>'.
                        __('Error', 'eb-textdomain').
                        ':</strong> '.
                        __('Username is required.', 'eb-textdomain')
                    );
                }

                if (empty($_POST['wdm_password'])) {
                    throw new \Exception(
                        '<strong>'.
                        __('Error', 'eb-textdomain').
                        ':</strong> '.
                        __('Password is required.', 'eb-textdomain')
                    );
                }

                $creds['user_login'] = $_POST['wdm_username'];

                $creds['user_password'] = $_POST['wdm_password'];
                $creds['remember'] = isset($_POST['rememberme']);
                $secure_cookie = is_ssl() ? true : false;
                $user = wp_signon(apply_filters('eb_login_credentials', $creds), $secure_cookie);

                if (is_wp_error($user)) {
                    throw new \Exception($user->get_error_message());
                } else {
                    if (!empty($_GET['redirect_to'])) {
                        $redirect = $_GET['redirect_to'];
                    } else {
                        $redirect = wdmUserAccountUrl();
                    }

                    wp_safe_redirect(apply_filters('eb_login_redirect', $redirect, $user));
                    exit;
                }
            } catch (\Exception $e) {
                wdmAddNotices($e->getMessage());
            }
        }
    }

    /**
     * Process the registration form.
     */
    public static function processRegistration()
    {
        if (!empty($_POST['register']) &&
            isset($_POST['_wpnonce']) &&
            wp_verify_nonce($_POST['_wpnonce'], 'eb-register')) {
            $email = $_POST['email'];
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];

            /*get object of user manager class*/
            $user_manager = new EBUserManager(edwiserBridgeInstance()->getPluginName(), edwiserBridgeInstance()->getVersion());

            try {
                $validation_error = new \WP_Error();
                $validation_error = apply_filters(
                    'eb_process_registration_errors',
                    $validation_error,
                    $firstname,
                    $lastname,
                    $email
                );

                if ($validation_error->get_error_code()) {
                    throw new \Exception($validation_error->get_error_message());
                }

                /*Anti-spam trap*/
                if (!empty($_POST['email_2'])) {
                    throw new \Exception(__('Anti-spam field was filled in.', 'eb-textdomain'));
                }

                $new_user = $user_manager->createWordpressUser(sanitize_email($email), $firstname, $lastname);

                if (is_wp_error($new_user)) {
                    throw new \Exception($new_user->get_error_message());
                }

                $user_manager->setUserAuthCookie($new_user);

                if (!empty($_GET['redirect_to'])) {
                    $redirect = $_GET['redirect_to'];
                } else {
                    $redirect = wdmUserAccountUrl();
                }

                wp_safe_redirect(apply_filters('eb_registration_redirect', $redirect, $new_user));
                exit;
            } catch (\Exception $e) {
                wdmAddNotices($e->getMessage());
            }
        }
    }

    /**
     * process course join for free courses.
     *
     * @since  1.0.0
     *
     * @return
     */
    public static function processFreeCourseJoinRequest()
    {
        if (!isset($_POST['course_join']) || !isset($_POST['course_id'])) {
            return;
        }

        $course_id = $_POST['course_id'];

        if (is_numeric($course_id)) {
            $course = get_post($course_id);
        } else {
            return;
        }

        /*return if post type is not eb_course*/
        if ($course->post_type != 'eb_course') {
            return;
        }

        $user_id = get_current_user_id();
        if (empty($user_id)) {
            $login_url = site_url('/user-account?redirect_to='.get_permalink($course_id));
            wp_redirect($login_url);
            exit;
        }

        $course_meta = get_post_meta($course_id, 'eb_course_options', true); // get options of current course

        /*get current user object*/
        $user = get_userdata($user_id);

        /*link existing moodle account or create a new one*/
        edwiserBridgeInstance()->userManager()->linkMoodleUser($user);

        if (!isset($course_meta['course_price_type']) ||
            $course_meta['course_price_type'] == 'free' ||
            $course_meta['course_price_type'] == 'paid' &&
            empty($course_meta['course_price'])) {
            /*define args*/
            $args = array(
                'user_id' => $user_id,
                'courses' => array($course_id),
            );
            /*enroll user to course*/
            //$course_enrolled =
            edwiserBridgeInstance()->enrollmentManager()->updateUserCourseEnrollment($args);
        }
    }
}
