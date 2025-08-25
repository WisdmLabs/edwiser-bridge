<?php

namespace app\wisdmlabs\edwiserBridgePro\includes\sso;

if (!defined('ABSPATH')) {
    exit;
}

// Use SSO functions from Edwiser Bridge Pro plugin
if (file_exists(ABSPATH . 'wp-content/plugins/edwiser-bridge-pro/includes/sso/ebsso-functions.php')) {
    require_once ABSPATH . 'wp-content/plugins/edwiser-bridge-pro/includes/sso/ebsso-functions.php';
}

class EdwiserBridge_Blocks_UserAccount_API
{
    // API namespace
    private const API_NAMESPACE = 'eb/api/v1';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'eb_register_useraccount_routes'));
        add_filter('rest_authentication_errors', array($this, 'eb_rest_authentication_errors'), 10, 1);
    }

    /**
     * Register API routes.
     */
    public function eb_register_useraccount_routes()
    {
        register_rest_route(self::API_NAMESPACE, '/user-account/dashboard', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'eb_get_dashboard'),
            'permission_callback' => array($this, 'eb_check_permission'),
        ));

        register_rest_route(self::API_NAMESPACE, '/user-account/orders', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'eb_get_user_orders'),
            'permission_callback' => array($this, 'eb_check_permission'),
        ));

        register_rest_route(self::API_NAMESPACE, '/user-account/profile', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'eb_get_user_profile'),
            'permission_callback' => array($this, 'eb_check_permission'),
        ));

        register_rest_route(self::API_NAMESPACE, '/user-account/profile', array(
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'eb_update_user_profile'),
            'permission_callback' => array($this, 'eb_check_permission'),
        ));

        register_rest_route(self::API_NAMESPACE, '/user-account/auth', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'eb_check_auth'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route(self::API_NAMESPACE, '/user-account/login', array(
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'eb_process_login'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route(self::API_NAMESPACE, '/user-account/register', array(
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'eb_process_registration'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Custom permission callback to handle nonce issues after login.
     *
     * @param WP_REST_Request $request The request object.
     * @return bool|WP_Error True if permission granted, WP_Error otherwise.
     */
    public function eb_check_permission($request)
    {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return new \WP_Error(
                'rest_forbidden',
                __('You are not logged in.', 'edwiser-bridge'),
                array('status' => 401)
            );
        }

        // For REST API requests, we'll bypass the nonce check to avoid issues after login
        // The user authentication is already verified above
        return true;
    }

    /**
     * Handle REST API authentication errors to prevent nonce issues after login.
     *
     * @param WP_Error|null|true $result Authentication result.
     * @return WP_Error|null|true Modified authentication result.
     */
    public function eb_rest_authentication_errors($result)
    {
        // If there's already an error, return it
        if ($result !== null && $result !== true) {
            return $result;
        }

        // Check if this is our API endpoint
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        if (strpos($request_uri, '/wp-json/eb/api/v1/user-account/') !== false) {
            // For our endpoints, allow the request to proceed if user is logged in
            // This prevents the nonce check from failing after login
            if (is_user_logged_in()) {
                return true;
            }
        }

        return $result;
    }

    /**
     * Check if user is authenticated.
     *
     * @return WP_REST_Response The response object.
     */
    public function eb_check_auth()
    {
        $user_id = get_current_user_id();
        $custom_fields = $this->get_custom_fields_for_registration();

        $general_settings = get_option('eb_general');

        $enable_registration = isset($general_settings['eb_enable_registration']) && !empty($general_settings['eb_enable_registration']) && 'yes' === $general_settings['eb_enable_registration'];

        $enable_terms_and_cond = isset($general_settings['eb_enable_terms_and_cond']) && !empty($general_settings['eb_enable_terms_and_cond']) && 'yes' === $general_settings['eb_enable_terms_and_cond'];

        $terms_and_cond = isset($general_settings['eb_terms_and_cond']) ? $general_settings['eb_terms_and_cond'] : '';

        $enable_recaptcha = isset($general_settings['eb_enable_recaptcha']) && !empty($general_settings['eb_enable_recaptcha']) && 'yes' === $general_settings['eb_enable_recaptcha'];

        $recaptcha_type = isset($general_settings['eb_recaptcha_type']) ? $general_settings['eb_recaptcha_type'] : 'v2';

        $recaptcha_site_key = isset($general_settings['eb_recaptcha_site_key']) ? $general_settings['eb_recaptcha_site_key'] : '';

        $show_recaptcha_on_login = isset($general_settings['eb_recaptcha_show_on_login']) && !empty($general_settings['eb_recaptcha_show_on_login']) && 'yes' === $general_settings['eb_recaptcha_show_on_login'];

        $show_recaptcha_on_register = isset($general_settings['eb_recaptcha_show_on_register']) && !empty($general_settings['eb_recaptcha_show_on_register']) && 'yes' === $general_settings['eb_recaptcha_show_on_register'];

        $sso_settings = get_option('eb_sso_settings_general');

        // Get Google auth URL
        $google_auth_url = null;
        if (isset($sso_settings['eb_sso_gp_enable']) && ('both' === $sso_settings['eb_sso_gp_enable'] || 'user_account' === $sso_settings['eb_sso_gp_enable'])) {
            $google_auth_url = $this->get_google_auth_url();
        }

        // Get Facebook auth URL
        $facebook_auth_url = null;
        if (isset($sso_settings['eb_sso_fb_enable']) && ('both' === $sso_settings['eb_sso_fb_enable'] || 'user_account' === $sso_settings['eb_sso_fb_enable'])) {
            $facebook_auth_url = $this->get_facebook_auth_url();
        }

        $user = null;
        if (!$user_id) {
            $message = __('User not logged in', 'edwiser-bridge');
        } else {
            $user = get_user_by('id', $user_id);
            if (!$user) {
                $message = __('User not found', 'edwiser-bridge');
            }
        }

        if (!$user_id || !$user) {
            return new \WP_REST_Response(array(
                'is_logged_in' => false,
                'message' => $message,
                'custom_fields' => $custom_fields,
                'lost_password_url' => esc_url(wp_lostpassword_url()),
                'enable_registration' => $enable_registration,
                'enable_terms_and_cond' => $enable_terms_and_cond,
                'terms_and_cond' => $terms_and_cond,
                'enable_recaptcha' => $enable_recaptcha,
                'recaptcha_type' => $recaptcha_type,
                'recaptcha_site_key' => $recaptcha_site_key,
                'show_recaptcha_on_login' => $show_recaptcha_on_login,
                'show_recaptcha_on_register' => $show_recaptcha_on_register,
                'sso' => array(
                    'google' => $google_auth_url,
                    'facebook' => $facebook_auth_url
                )
            ), 200);
        }

        return new \WP_REST_Response(array(
            'is_logged_in' => true,
        ), 200);
    }

    /**
     * Get Google auth URL by extracting from button HTML
     */
    private function get_google_auth_url()
    {
        $class_name = 'app\\wisdmlabs\\edwiserBridgePro\\includes\\sso\\Sso_Google_Plus_Init';

        if (!class_exists($class_name)) {
            return null;
        }

        $google_sso = new $class_name('plugin_name', 'version');
        $result = $google_sso->load_dependencies();

        if (!$result) {
            return null;
        }

        $button_html = $google_sso->add_google_login_button();

        if ($button_html) {
            preg_match('/href="([^"]*)"/', $button_html, $matches);
            return isset($matches[1]) ? html_entity_decode($matches[1]) : null;
        }


        return null;
    }

    /**
     * Get Facebook auth URL by extracting from button HTML
     */
    private function get_facebook_auth_url()
    {
        $class_name = 'app\\wisdmlabs\\edwiserBridgePro\\includes\\sso\\Sso_Facebook_Init';

        if (!class_exists($class_name)) {
            return null;
        }

        $facebook_sso = new $class_name('plugin_name', 'version');
        $result = $facebook_sso->load_dependencies();

        if (!$result) {
            return null;
        }

        $button_html = $facebook_sso->add_facebook_login_button();

        if ($button_html) {
            preg_match('/href="([^"]*)"/', $button_html, $matches);
            return isset($matches[1]) ? html_entity_decode($matches[1]) : null;
        }


        return null;
    }

    /**
     * Process the login form.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response The response object.
     */
    public function eb_process_login($request)
    {
        $params = $request->get_json_params();

        $username = isset($params['username']) ? sanitize_text_field($params['username']) : '';
        $password = isset($params['password']) ? $params['password'] : '';
        $remember = isset($params['remember']) ? (bool) $params['remember'] : false;

        $validation_error = new \WP_Error();

        $validation_error = apply_filters(
            'eb_process_login_errors',
            $validation_error,
            isset($username) ? sanitize_text_field(wp_unslash($username)) : '',
            isset($password) ? sanitize_text_field(wp_unslash($password)) : ''
        );

        if ($validation_error->get_error_code()) {
            return new \WP_Error(
                'login_failed',
                $validation_error->get_error_message(),
                array('status' => 400)
            );
        }

        if (empty($username)) {
            return new \WP_Error(
                'username_required',
                __('Username is required.', 'edwiser-bridge'),
                array('status' => 400)
            );
        }

        if (empty($password)) {
            return new \WP_Error(
                'password_required',
                __('Password is required.', 'edwiser-bridge'),
                array('status' => 400)
            );
        }

        // For REST API, use direct authentication instead of wp_signon to avoid redirects
        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            // If email is not verified, send an HTML message with a Resend link that points to the site URL (not REST)
            if ($user->get_error_code() === 'eb_user_email_verification') {
                $login_identifier = $username;
                $wp_user = is_email($login_identifier) ? get_user_by('email', $login_identifier) : get_user_by('login', $login_identifier);
                $resend_url = '';
                if ($wp_user && isset($wp_user->ID)) {
                    // Prefer the referring page as the base so the link looks like: current-page?action=...&eb_user_email_verification_id=...
                    $referer = wp_get_referer();
                    $base_url = $referer ? esc_url_raw($referer) : home_url('/');
                    $resend_url = add_query_arg(
                        array(
                            'action' => 'eb_user_verification_resend',
                            'eb_user_email_verification_id' => $wp_user->ID,
                        ),
                        $base_url
                    );
                }

                $html_message = sprintf(
                    '%s %s',
                    __('Your email is not verified. Please verify your email sent to your email or', 'edwiser-bridge'),
                    $resend_url ? ('<a href="' . esc_url($resend_url) . '">' . __('resend verification email', 'edwiser-bridge') . '</a>') : ''
                );

                return new \WP_Error(
                    'login_failed',
                    $html_message,
                    array('status' => 400, 'is_html' => true)
                );
            }

            // Default error passthrough
            return new \WP_Error(
                'login_failed',
                $user->get_error_message(),
                array('status' => 400)
            );
        }

        // Set the current user for the session
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);

        // Get redirect parameters
        $redirect_to = isset($params['redirect_to']) ? sanitize_text_field($params['redirect_to']) : '';
        $ignore_setting = !empty($redirect_to) ? 1 : 0;

        // Check if Edwiser Bridge Pro plugin is active and SSO is enabled
        $pro_plugin_active = $this->is_edwiser_bridge_pro_active();
        $sso_enabled = $this->is_sso_feature_enabled();

        // Get enhanced redirect URL only if pro plugin is active and SSO is enabled
        if ($pro_plugin_active && $sso_enabled) {
            $redirect = $this->get_enhanced_redirect_url($user, $redirect_to, $ignore_setting, $request);
        } else {
            // Fall back to basic redirect logic
            $redirect = !empty($redirect_to) ? $redirect_to : $this->get_user_redirect_url();
        }

        $final_redirect = apply_filters('eb_login_redirect', $redirect, $user);

        // Handle Moodle SSO only if pro plugin is active and SSO is enabled
        if ($pro_plugin_active && $sso_enabled) {
            $moodle_result = $this->handle_moodle_sso($user, $final_redirect);
        } else {
            // Return disabled SSO result
            $moodle_result = array(
                'success' => false,
                'enabled' => false,
                'reason' => 'pro_plugin_or_sso_disabled',
                'moodle_url' => '',
                'redirect_url' => $final_redirect
            );
        }

        // Prepare response with SSO information
        return new \WP_REST_Response(array(
            'success' => true,
            'message' => __('Login successful', 'edwiser-bridge'),
            'user_id' => $user->ID,
            'user_email' => $user->user_email,
            'display_name' => $user->display_name,
            'redirect_url' => $final_redirect,
            'moodle_sso' => array(
                'enabled' => $moodle_result['enabled'],
                'status' => $moodle_result['success'] ? 'success' : 'failed',
                'error' => isset($moodle_result['error']) ? $moodle_result['error'] : null,
                'moodle_url' => $moodle_result['success'] ? $moodle_result['moodle_url'] : null,
            ),
        ), 200);
    }

    /**
     * Process the registration form.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error The response object.
     */
    public function eb_process_registration($request)
    {
        $params = $request->get_json_params();

        $email = isset($params['email']) ? sanitize_text_field($params['email']) : '';
        $firstname = isset($params['firstname']) ? sanitize_text_field($params['firstname']) : '';
        $lastname = isset($params['lastname']) ? sanitize_text_field($params['lastname']) : '';
        $password = isset($params['password']) ? $params['password'] : '';
        $confirm_password = isset($params['confirm_password']) ? $params['confirm_password'] : '';
        $reg_terms_and_cond = isset($params['reg_terms_and_cond']) ? $params['reg_terms_and_cond'] : '';

        $custom_fields = isset($params['custom_fields']) ? $params['custom_fields'] : array();

        // Check terms and conditions if enabled
        $general_settings = get_option('eb_general');
        if (isset($general_settings['eb_enable_terms_and_cond']) && 'yes' === $general_settings['eb_enable_terms_and_cond']) {
            if (empty($reg_terms_and_cond) || true !== $reg_terms_and_cond) {
                return new \WP_Error(
                    'terms_required',
                    __('Terms and conditions must be accepted.', 'edwiser-bridge'),
                    array('status' => 400)
                );
            }
        }

        $validation_error = new \WP_Error();
        $validation_error = apply_filters(
            'eb_process_registration_errors',
            $validation_error,
            $firstname,
            $lastname,
            $email
        );

        if (empty($email)) {
            return new \WP_Error(
                'email_required',
                __('Email is required.', 'edwiser-bridge'),
                array('status' => 400)
            );
        }

        if (!is_email($email)) {
            return new \WP_Error(
                'invalid_email',
                __('Invalid email address.', 'edwiser-bridge'),
                array('status' => 400)
            );
        }

        if (email_exists($email)) {
            return new \WP_Error(
                'email_exists',
                __('Email already exists.', 'edwiser-bridge'),
                array('status' => 400)
            );
        }

        if (empty($firstname)) {
            return new \WP_Error(
                'firstname_required',
                __('First name is required.', 'edwiser-bridge'),
                array('status' => 400)
            );
        }

        if (empty($lastname)) {
            return new \WP_Error(
                'lastname_required',
                __('Last name is required.', 'edwiser-bridge'),
                array('status' => 400)
            );
        }

        if (empty($password) || empty($confirm_password)) {
            return new \WP_Error(
                'password_required',
                __('Password fields can not be empty.', 'edwiser-bridge'),
                array('status' => 400)
            );
        }

        if (!empty($password) && $password !== $confirm_password) {
            return new \WP_Error(
                'password_mismatch',
                __('Passwords are not matching.', 'edwiser-bridge'),
                array('status' => 400)
            );
        }

        if ($validation_error->get_error_code()) {
            return new \WP_Error(
                'registration_failed',
                $validation_error->get_error_message(),
                array('status' => 400)
            );
        }

        // Get default registration role
        $role = \app\wisdmlabs\edwiserBridge\wdm_eb_default_registration_role();

        // Determine redirect URL (used only when verification is disabled)
        $redirect = $this->get_user_redirect_url();

        // Create WordPress user
        $new_user_id = $this->create_wordpress_user(sanitize_email($email), $firstname, $lastname, $role, $password);

        $new_user = get_user_by('id', $new_user_id);

        if (is_wp_error($new_user)) {
            return new \WP_Error(
                'registration_failed',
                $new_user->get_error_message(),
                array('status' => 400)
            );
        }

        // If email verification is enabled, set user as unverified and send verification email
        $general_settings = get_option('eb_general');
        $email_verification_enabled = isset($general_settings['eb_email_verification']) && 'yes' === $general_settings['eb_email_verification'];

        if ($email_verification_enabled) {
            // Mark as unverified
            update_user_meta($new_user_id, 'eb_user_email_verified', 0);

            // Send verification email using existing user manager method
            $version = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_version();
            $user_manager = new \app\wisdmlabs\edwiserBridge\Eb_User_Manager('edwiserbridge', $version);
            $user_manager->eb_send_email_verification_link($new_user_id);
        } else {
            // If verification is disabled, log user in immediately (backward compatibility)
            wp_set_auth_cookie($new_user_id, true);
        }

        // Sync custom fields if provided
        if (!empty($custom_fields)) {
            $this->sync_custom_fields($custom_fields, $new_user_id);
        }

        // In REST context, return a clean frontend redirect URL without adding query params
        $final_redirect = $redirect;

        // Build response aligned with verification state
        if ($email_verification_enabled) {
            return new \WP_REST_Response(array(
                'success' => true,
                'requires_verification' => true,
                'should_redirect' => false,
                'message' => __('A verification email has been sent to your email address. Please verify your email address and try enrolling in the course again.', 'edwiser-bridge'),
                'user_id' => $new_user->ID,
                'user_email' => $new_user->user_email,
                'display_name' => $new_user->display_name,
                'redirect_url' => '',
            ), 200);
        }

        return new \WP_REST_Response(array(
            'success' => true,
            'requires_verification' => false,
            'should_redirect' => true,
            'message' => __('Registration successful', 'edwiser-bridge'),
            'user_id' => $new_user->ID,
            'user_email' => $new_user->user_email,
            'display_name' => $new_user->display_name,
            'redirect_url' => $final_redirect,
        ), 200);
    }

    /**
     * Get user dashboard data with user information and course statistics.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response The response object.
     */
    public function eb_get_dashboard($request)
    {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return new \WP_Error('not_logged_in', __('User not logged in', 'edwiser-bridge'), array('status' => 401));
        }

        $user = get_user_by('id', $user_id);

        if (!$user) {
            return new \WP_Error('user_not_found', __('User not found', 'edwiser-bridge'), array('status' => 404));
        }

        $params = $request->get_params();

        $redirect_to = isset($params['redirect_to']) ? sanitize_text_field($params['redirect_to']) : '';

        // Get user avatar
        $user_avatar = get_avatar_url($user_id, array('size' => 96));

        // Get course statistics
        $course_stats = $this->get_user_course_statistics($user_id);

        return new \WP_REST_Response(array(
            'id' => $user->ID,
            'display_name' => $user->display_name,
            'user_email' => $user->user_email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'avatar' => $user_avatar,
            'logout_url' => html_entity_decode(wp_logout_url($redirect_to)),
            'course_statistics' => $course_stats,
        ), 200);
    }

    /**
     * Get user orders data.
     *
     * @return WP_REST_Response The response object.
     */
    public function eb_get_user_orders()
    {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return new \WP_Error('not_logged_in', __('User not logged in', 'edwiser-bridge'), array('status' => 401));
        }

        $user_orders = $this->get_user_orders($user_id);

        return new \WP_REST_Response(array(
            'orders' => $user_orders,
            'total_orders' => count($user_orders),
        ), 200);
    }

    public function eb_get_user_profile()
    {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return new \WP_Error('not_logged_in', __('User not logged in', 'edwiser-bridge'), array('status' => 401));
        }

        $user = get_user_by('id', $user_id);

        if (!$user) {
            return new \WP_Error('user_not_found', __('User not found', 'edwiser-bridge'), array('status' => 404));
        }

        $formatted_custom_fields = [];

        $custom_fields = get_option('edwiser_custom_fields');
        $modules_data = get_option('eb_pro_modules_data');

        if (!empty($custom_fields)) {
            foreach ($custom_fields as $name => $field_details) {
                if (isset($modules_data['woo_integration']) && 'active' === $modules_data['woo_integration'] && isset($field_details['enabled']) && "1" === $field_details['enabled'] && isset($field_details['eb-user-accnt']) && "1" === $field_details['eb-user-accnt']) {
                    $field_value = get_user_meta($user_id, $name, true);
                    if (empty($field_value)) {
                        $field_value = $field_details['default-val'];
                    }

                    $field_data = array(
                        'type' => $field_details['type'],
                        'value' => $field_value,
                        'required' => isset($field_details['required']) && $field_details['required'] === "1",
                        'label' => $field_details['label'],
                        'placeholder' => isset($field_details['placeholder']) ? $field_details['placeholder'] : '',
                        'class' => isset($field_details['class']) ? $field_details['class'] : '',
                        'id' => 'eb_cf_' . esc_attr($name),
                        'name' => $name
                    );

                    if ($field_details['type'] === 'select' && !empty($field_details['options'])) {
                        $formatted_options = array();
                        foreach ($field_details['options'] as $option_value => $option_label) {
                            $formatted_options[] = array(
                                'label' => $option_label,
                                'value' => $option_value
                            );
                        }
                        $field_data['options'] = $formatted_options;
                    }

                    if ($field_details['type'] === 'checkbox') {
                        $field_data['checked'] = in_array($field_value, ['on', 'true', true, '1', 1], true);
                        $field_data['value'] = in_array($field_value, ['on', 'true', true, '1', 1], true);
                    }

                    $formatted_custom_fields[] = $field_data;
                }
            }
        }

        $countries = $this->get_countries();

        return new \WP_REST_Response(array(
            'profile' => array(
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'nickname' => $user->nickname,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'description' => $user->description,
                'city' => $user->city,
                'country' => $user->country,
            ),
            'custom_fields' => $formatted_custom_fields,
            'countries' => $countries,
        ), 200);
    }

    public function eb_update_user_profile($request)
    {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return new \WP_Error('not_logged_in', __('User not logged in', 'edwiser-bridge'), array('status' => 401));
        }

        $user = get_user_by('id', $user_id);

        if (!$user) {
            return new \WP_Error('user_not_found', __('User not found', 'edwiser-bridge'), array('status' => 404));
        }

        $params = $request->get_json_params();

        $first_name = isset($params['first_name']) ? sanitize_text_field($params['first_name']) : '';
        $last_name = isset($params['last_name']) ? sanitize_text_field($params['last_name']) : '';
        $nickname = isset($params['display_name']) ? sanitize_text_field($params['display_name']) : '';
        $email = isset($params['email']) ? sanitize_email($params['email']) : '';
        $description = isset($params['bio']) ? sanitize_text_field($params['bio']) : '';
        $city = isset($params['city']) ? sanitize_text_field($params['city']) : '';
        $country = isset($params['country']) ? sanitize_text_field($params['country']) : '';
        $current_password = isset($params['current_password']) ? sanitize_text_field($params['current_password']) : '';
        $new_password = isset($params['new_password']) ? sanitize_text_field($params['new_password']) : '';
        $confirm_password = isset($params['confirm_password']) ? sanitize_text_field($params['confirm_password']) : '';

        // Basic field validation
        if (empty($email)) {
            return new \WP_Error('email_required', __('Email address is required', 'edwiser-bridge'), array('status' => 400));
        }

        if (!is_email($email)) {
            return new \WP_Error('invalid_email', __('Invalid email address', 'edwiser-bridge'), array('status' => 400));
        }

        if (!empty($email) && email_exists($email) && $email !== $user->user_email) {
            return new \WP_Error('email_exists', __('Email already exists', 'edwiser-bridge'), array('status' => 400));
        }

        if (!empty($new_password) && $new_password !== $confirm_password) {
            return new \WP_Error('password_mismatch', __('New password and confirm password do not match', 'edwiser-bridge'), array('status' => 400));
        }

        if (!empty($current_password) && !wp_check_password($current_password, $user->user_pass)) {
            return new \WP_Error('invalid_current_password', __('Invalid current password', 'edwiser-bridge'), array('status' => 400));
        }

        // Update WordPress profile
        $wp_update_success = $this->update_wordpress_profile($user_id, array(
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'nickname' => $nickname,
            'description' => $description,
            'city' => $city,
            'country' => $country,
            'new_password' => $new_password,
        ));

        if (!$wp_update_success) {
            return new \WP_Error('wp_update_failed', __('Couldn\'t update your profile on WordPress! Something went wrong.', 'edwiser-bridge'), array('status' => 400));
        }

        // Update moodle profile only if user has a Moodle account
        $moodle_update_success = true; // Default to success
        $mdl_uid = get_user_meta($user_id, 'moodle_user_id', true);
        if (is_numeric($mdl_uid)) {
            $moodle_update_success = $this->update_moodle_profile($mdl_uid, array(
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'nickname' => $nickname,
                'description' => $description,
                'city' => $city,
                'country' => $country,
                'new_password' => $new_password,
            ));

            // Only return error if user has Moodle account but update failed
            if (!$moodle_update_success) {
                return new \WP_Error('moodle_update_failed', __('Couldn\'t update your profile on Moodle! Something went wrong.', 'edwiser-bridge'), array('status' => 400));
            }
        }

        $this->sync_custom_fields($params['custom_fields'], $user_id);

        return new \WP_REST_Response(array(
            'success' => true,
            'message' => __('Profile updated successfully', 'edwiser-bridge'),
            'moodle_updated' => $moodle_update_success,
            'wordpress_updated' => $wp_update_success,
        ), 200);
    }

    private function update_wordpress_profile($user_id, $params)
    {
        update_user_meta($user_id, 'city', $params['city']);
        update_user_meta($user_id, 'country', $params['country']);

        // Prepare user update arguments
        $args = array(
            'ID'          => $user_id,
            'user_email'  => $params['email'],
            'first_name'  => $params['first_name'],
            'last_name'   => $params['last_name'],
            'nickname'    => $params['nickname'],
            'description' => $params['description'],
        );

        // Add password if provided
        if (isset($params['new_password']) && !empty($params['new_password'])) {
            $args['user_pass'] = $params['new_password'];
        }

        $result = wp_update_user($args);

        if (is_wp_error($result)) {
            return false;
        }

        return true;
    }

    private function update_moodle_profile($mdl_uid, $params)
    {
        $user_data = array(
            'id'            => (int) $mdl_uid,
            'email'         => $params['email'],
            'firstname'     => $params['first_name'],
            'lastname'      => $params['last_name'],
            'alternatename' => $params['nickname'],
            'auth'          => 'manual',
            'city'          => $params['city'],
            'country'       => $params['country'],
            'description'   => $params['description'],
        );

        if (isset($params['new_password']) && !empty($params['new_password'])) {
            $user_data['password'] = $params['new_password'];
        }

        // Use existing Edwiser Bridge user manager
        $version = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_version();
        $user_manager = new \app\wisdmlabs\edwiserBridge\Eb_User_Manager('edwiserbridge', $version);
        $response = $user_manager->create_moodle_user($user_data, 1);

        return isset($response['user_updated']) && $response['user_updated'];
    }

    private function sync_custom_fields($custom_fields, $user_id)
    {
        $user_data = array();
        $fields = get_option('edwiser_custom_fields', array());

        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $field_name => $field_details) {
                if (isset($field_details['enabled']) && 1 == $field_details['enabled']) {
                    if ('checkbox' === $field_details['type']) {
                        $field_value = isset($custom_fields[$field_name]) ? sanitize_text_field($custom_fields[$field_name]) : 0;
                    } else {
                        $field_value = isset($custom_fields[$field_name]) ? sanitize_text_field($custom_fields[$field_name]) : get_user_meta($user_id, $field_name, true);
                    }

                    // apply filter to field value before updating.
                    $field_value = apply_filters('eb_cf_before_update_field_value', $field_value, $field_name, $user_id, $field_details);

                    // update user meta.
                    update_user_meta($user_id, $field_name, $field_value);

                    if (! isset($field_details['sync-on-moodle']) || ! $field_details['sync-on-moodle']) {
                        continue;
                    }

                    // if type is date then convert date to epoch.
                    if ('date' === $field_details['type']) {
                        $field_value = strtotime($field_value);
                    }

                    array_push(
                        $user_data,
                        array(
                            'type'  => $field_name,
                            'value' => $field_value,
                        )
                    );
                }
            }
        }

        $user_data = apply_filters('eb_cf_user_data', $user_data, $user_id);
        $moodle_user_id = get_user_meta($user_id, 'moodle_user_id', true);

        $response = \app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper(
            'core_user_update_users',
            array(
                'users' => array(array(
                    'id'           => $moodle_user_id,
                    'customfields' => $user_data,
                )),
            )
        );
    }

    /**
     * Get user course statistics including total, not started, in progress, and completed counts.
     *
     * @param int $user_id User ID.
     * @return array Array of course statistics.
     */
    private function get_user_course_statistics($user_id)
    {
        // Initialize counters
        $stats = array(
            'total_enrolled' => 0,
            'not_started' => 0,
            'in_progress' => 0,
            'completed' => 0,
        );

        // Get enrolled course IDs using the same function as My Courses API
        $enrolled_course_ids = \app\wisdmlabs\edwiserBridge\eb_get_user_enrolled_courses($user_id);

        if (empty($enrolled_course_ids)) {
            return $stats;
        }

        $stats['total_enrolled'] = count($enrolled_course_ids);

        // Get progress data for all courses
        $course_progress_manager = new \app\wisdmlabs\edwiserBridge\Eb_Course_Progress();
        $progress_data = $course_progress_manager->get_course_progress();

        // Count courses by status
        foreach ($enrolled_course_ids as $course_id) {
            $progress_percentage = isset($progress_data[$course_id]) ? floatval($progress_data[$course_id]) : 0;

            // Determine status based on progress percentage (same logic as My Courses API)
            if ($progress_percentage <= 0) {
                $stats['not_started']++;
            } elseif ($progress_percentage >= 100) {
                $stats['completed']++;
            } else {
                $stats['in_progress']++;
            }
        }

        return $stats;
    }

    /**
     * Get user orders from database.
     *
     * @param int $user_id User ID.
     * @return array Array of user orders.
     */
    private function get_user_orders($user_id)
    {
        $user_orders = array();

        // Get all completed orders of a user.
        $args = array(
            'posts_per_page' => -1,
            'meta_key'       => '',
            'post_type'      => 'eb_order',
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $overall_orders = get_posts($args); // Get all orders from db.

        foreach ($overall_orders as $order_id) {
            $order_detail = get_post_meta($order_id, 'eb_order_options', true);

            if (!empty($order_detail) && trim($order_detail['buyer_id']) === trim($user_id)) {
                // Handle ordered items (can be single course or array of courses)
                $ordered_items = $order_detail['course_id'];
                if (!is_array($ordered_items)) {
                    $ordered_items = array($ordered_items);
                }

                // Process course information
                $courses = array();
                foreach ($ordered_items as $course_id) {
                    $course_title = get_the_title($course_id);
                    if (empty($course_title)) {
                        $course_title = __('Not Available', 'edwiser-bridge');
                        $course_link = '';
                    } else {
                        $course_link = get_permalink($course_id);
                    }

                    $courses[] = array(
                        'id' => $course_id,
                        'title' => $course_title,
                        'link' => $course_link,
                    );
                }

                $user_orders[] = array(
                    'eb_order_id'   => $order_id,
                    'ordered_item'  => $order_detail['course_id'], // Keep original for backward compatibility
                    'courses'       => $courses, // New structured course data
                    'billing_email' => isset($order_detail['billing_email']) ? $order_detail['billing_email'] : '-',
                    'currency'      => isset($order_detail['currency']) ? $order_detail['currency'] : '$',
                    'amount_paid'   => isset($order_detail['amount_paid']) ? $order_detail['amount_paid'] : '',
                    'status'        => isset($order_detail['order_status']) ? $order_detail['order_status'] : '',
                    'date'          => get_the_date('Y-m-d', $order_id),
                );
            }
        }

        return $user_orders;
    }

    /**
     * Get custom fields formatted for registration form.
     *
     * @return array Array of formatted custom fields.
     */
    private function get_custom_fields_for_registration()
    {
        $formatted_custom_fields = [];

        $custom_fields = get_option('edwiser_custom_fields');
        $modules_data = get_option('eb_pro_modules_data');

        if (!empty($custom_fields)) {
            foreach ($custom_fields as $name => $field_details) {
                // Check if field is enabled and should be shown in registration
                if (
                    isset($modules_data['woo_integration']) && 'active' === $modules_data['woo_integration'] &&
                    isset($field_details['enabled']) && "1" === $field_details['enabled'] &&
                    isset($field_details['eb-reg']) && "1" === $field_details['eb-reg']
                ) {

                    $field_data = array(
                        'type' => $field_details['type'],
                        'value' => isset($field_details['default-val']) ? $field_details['default-val'] : '',
                        'required' => isset($field_details['required']) && $field_details['required'] === "1",
                        'label' => $field_details['label'],
                        'placeholder' => isset($field_details['placeholder']) ? $field_details['placeholder'] : '',
                        'class' => isset($field_details['class']) ? $field_details['class'] : '',
                        'id' => 'eb_cf_' . esc_attr($name),
                        'name' => $name
                    );

                    if ($field_details['type'] === 'select' && !empty($field_details['options'])) {
                        $formatted_options = array();
                        foreach ($field_details['options'] as $option_value => $option_label) {
                            $formatted_options[] = array(
                                'label' => $option_label,
                                'value' => $option_value
                            );
                        }
                        $field_data['options'] = $formatted_options;
                    }

                    if ($field_details['type'] === 'checkbox') {
                        $field_data['checked'] = in_array($field_data['value'], ['on', 'true', true, '1', 1], true);
                    }

                    $formatted_custom_fields[] = $field_data;
                }
            }
        }

        return $formatted_custom_fields;
    }

    private function get_countries()
    {
        $formatted_countries = array();

        // Check if WooCommerce is available
        if (class_exists('\WC_Countries')) {
            $wc_class = '\WC_Countries';
            $wc_countries = new $wc_class();
            $countries = $wc_countries->get_countries();

            foreach ($countries as $code => $name) {
                $country_data = array(
                    'value' => $code,
                    'label' => html_entity_decode($name),
                );

                $formatted_countries[] = $country_data;
            }
        } else {
            // Fallback to basic countries list if WooCommerce is not available
            $basic_countries = array(
                'US' => 'United States',
                'CA' => 'Canada',
                'GB' => 'United Kingdom',
                'AU' => 'Australia',
                'DE' => 'Germany',
                'FR' => 'France',
                'IN' => 'India',
                'JP' => 'Japan',
                'BR' => 'Brazil',
                'MX' => 'Mexico',
            );

            foreach ($basic_countries as $code => $name) {
                $country_data = array(
                    'value' => $code,
                    'label' => html_entity_decode($name),
                );

                $formatted_countries[] = $country_data;
            }
        }

        return $formatted_countries;
    }

    /**
     * Get user redirect URL using the same logic as wdm_eb_user_redirect_url().
     *
     * @param string $redirect_to Optional redirect URL from frontend.
     * @param string $is_enroll Optional auto enroll flag.
     * @return string The redirect URL.
     */
    private function get_user_redirect_url()
    {
        // Get the Edwiser Bridge general settings.
        $eb_settings = get_option('eb_general');
        $redirect_url = '';

        // Set the login redirect url to the user account page.
        if (isset($eb_settings['eb_useraccount_page_id'])) {
            $redirect_url = get_permalink($eb_settings['eb_useraccount_page_id']);
        }

        // Sets redirect_url to my course page if the redirection to the my
        // courses page is enabled in settings
        if (isset($eb_settings['eb_enable_my_courses']) && 'yes' === $eb_settings['eb_enable_my_courses']) {
            if (isset($eb_settings['eb_my_courses_page_id'])) {
                $redirect_url = get_permalink($eb_settings['eb_my_courses_page_id']);
            }
        }

        return $redirect_url;
    }

    /**
     * Enhanced redirect URL logic that incorporates SSO settings and course ID.
     *
     * @param object $user User object.
     * @param string $default_redirect Default redirect URL.
     * @param int $ignore_setting_redirect_url Whether to ignore setting redirect URL.
     * @return string The redirect URL.
     */
    private function get_enhanced_redirect_url($user, $default_redirect = '', $ignore_setting_redirect_url = 0, $request = null)
    {
        // Ensure this method only works when pro plugin and SSO are active
        if (!$this->is_edwiser_bridge_pro_active() || !$this->is_sso_feature_enabled()) {
            // Fall back to basic redirect logic
            return !empty($default_redirect) ? $default_redirect : $this->get_user_redirect_url();
        }

        // Check for mdl_course_id in request
        $mdl_course_id = null;
        if ($request !== null) {
            $params = $request->get_params();
            $mdl_course_id = isset($params['mdl_course_id']) ? sanitize_text_field($params['mdl_course_id']) : null;
        }

        if (!empty($mdl_course_id)) {
            return eb_get_mdl_url() . '/course/view.php?id=' . $mdl_course_id;
        }

        // Use SSO redirection settings
        $redirect_urls = get_option('eb_sso_settings_redirection');
        $redirect_url = '';

        // Role-based redirect logic
        if (isset($redirect_urls['ebsso_role_base_redirect']) && 'no' !== $redirect_urls['ebsso_role_base_redirect']) {
            if (isset($user->roles[0])) {
                $role_key = 'ebsso_login_redirect_url_' . $user->roles[0];
                if (isset($redirect_urls[$role_key]) && !empty($redirect_urls[$role_key])) {
                    $redirect_url = $redirect_urls[$role_key];
                }
            }
        }

        // General redirect URL
        if (empty($redirect_url) && isset($redirect_urls['ebsso_login_redirect_url']) && !empty($redirect_urls['ebsso_login_redirect_url'])) {
            $redirect_url = $redirect_urls['ebsso_login_redirect_url'];
        }

        // If ignore setting is enabled and default redirect is provided
        if ($ignore_setting_redirect_url && !empty($default_redirect)) {
            $redirect_url = $default_redirect;
        }

        // Fall back to default redirect or current implementation
        if (empty($redirect_url)) {
            $redirect_url = !empty($default_redirect) ? $default_redirect : $this->get_user_redirect_url();
        }

        return $redirect_url;
    }

    /**
     * Handle Moodle SSO process.
     * 
     * @param object $user User object.
     * @param string $redirect_url Redirect URL.
     * @return array Result with success status and redirect URL.
     */
    private function handle_moodle_sso($user, $redirect_url = '')
    {
        // Ensure this method only works when pro plugin and SSO are active
        if (!$this->is_edwiser_bridge_pro_active() || !$this->is_sso_feature_enabled()) {
            return array(
                'success' => false,
                'enabled' => false,
                'reason' => 'pro_plugin_or_sso_disabled',
                'moodle_url' => '',
                'redirect_url' => $redirect_url
            );
        }

        // Check if user has a Moodle ID
        $moodle_user_id = get_user_meta($user->ID, 'moodle_user_id', true);
        if (empty($moodle_user_id)) {
            return array(
                'success' => false,
                'enabled' => false,
                'reason' => 'no_moodle_id',
                'moodle_url' => '',
                'redirect_url' => $redirect_url
            );
        }

        // Generate one-time hash for verification
        $hash = hash('md5', wp_rand(10, 1000));

        // Build query for Moodle
        $query = array(
            'moodle_user_id' => $moodle_user_id,
            'login_redirect' => $redirect_url,
            'wp_one_time_hash' => $hash,
        );

        // Execute Moodle request
        $result = $this->execute_moodle_request($query);

        if (!$result['success']) {
            return array(
                'success' => false,
                'enabled' => true,
                'reason' => 'request_failed',
                'error' => $result['error'],
                'moodle_url' => '',
                'redirect_url' => $redirect_url
            );
        }

        // Generate final Moodle URL with verification hash
        $eb_moodle_url = eb_get_mdl_url();
        $final_url = $eb_moodle_url . '/auth/edwiserbridge/login.php?login_id=' . $moodle_user_id . '&veridy_code=' . $hash;

        return array(
            'success' => true,
            'enabled' => true,
            'moodle_url' => $final_url,
            'redirect_url' => $redirect_url
        );
    }

    /**
     * Execute request to Moodle for SSO.
     * 
     * @param array $query Query parameters.
     * @return array Result with success status.
     */
    private function execute_moodle_request($query)
    {
        // Get Moodle URL and token
        $eb_moodle_url = eb_get_mdl_url();
        $sso_secret_key = eb_get_mdl_token();

        if (empty($eb_moodle_url)) {
            return array(
                'success' => false,
                'error' => 'Moodle URL is not configured'
            );
        }

        // Encrypt the data
        $details = http_build_query($query);
        $wdm_data = encryptString($details, $sso_secret_key);

        // Prepare request arguments
        $request_args = array(
            'body' => array('wdm_data' => $wdm_data),
            'timeout' => 100,
        );

        // Send request to Moodle
        $response = wp_remote_post($eb_moodle_url . '/auth/edwiserbridge/login.php', $request_args);

        // Handle errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();

            // Log error if logging function exists
            if (function_exists('\app\wisdmlabs\edwiserBridge\wdm_log_json')) {
                global $current_user;
                wp_get_current_user();
                $error_data = array(
                    'url' => $eb_moodle_url . '/auth/edwiserbridge/login.php',
                    'arguments' => $request_args,
                    'user' => isset($current_user) ? $current_user->user_login . '(' . $current_user->first_name . ' ' . $current_user->last_name . ')' : '',
                    'responsecode' => '',
                    'exception' => '',
                    'errorcode' => '',
                    'message' => $error_message,
                    'backtrace' => wp_debug_backtrace_summary(null, 0, false),
                );
                \app\wisdmlabs\edwiserBridge\wdm_log_json($error_data);
            }

            return array(
                'success' => false,
                'error' => $error_message
            );
        }

        return array(
            'success' => true
        );
    }

    private function create_wordpress_user($email, $firstname, $lastname, $role, $password)
    {
        $username = sanitize_user(current(explode('@', $email)), true);

        // Ensure username is unique.
        $append     = 1;
        $o_username = $username;

        while (username_exists($username)) {
            $username = $o_username . $append;
            ++$append;
        }

        // Handle password creation.
        if (empty($password)) {
            $password = wp_generate_password();
        }

        // WP Validation.
        do_action('eb_register_post', $username, $email);

        // Added after 1.3.4.
        if ('' === $role) {
            $role = get_option('default_role');
        }

        $wp_user_data = apply_filters(
            'eb_new_user_data',
            array(
                'user_login' => $username,
                'user_pass'  => $password,
                'user_email' => $email,
                'role'       => $role,
                'first_name' => $firstname,
                'last_name'  => $lastname,
            )
        );

        $user_id = wp_insert_user($wp_user_data);

        if (is_wp_error($user_id)) {
            return new \WP_Error(
                'registration-error',
                __(
                    'Couldn\'t register you... please contact us if you continue to have problems.',
                    'edwiser-bridge'
                )
            );
        }

        return $user_id;
    }

    /**
     * Check if Edwiser Bridge Pro plugin is active.
     *
     * @return bool True if pro plugin is active, false otherwise.
     */
    private function is_edwiser_bridge_pro_active()
    {
        // Check if the pro plugin file exists and is active
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active('edwiser-bridge-pro/edwiser-bridge-pro.php');
    }

    /**
     * Check if SSO feature is enabled.
     *
     * @return bool True if SSO is enabled, false otherwise.
     */
    private function is_sso_feature_enabled()
    {
        // Check if SSO module is active in pro plugin
        $modules_data = get_option('eb_pro_modules_data', array());

        if (isset($modules_data['sso']) && 'active' === $modules_data['sso']) {
            return true;
        }

        return false;
    }
}

new EdwiserBridge_Blocks_UserAccount_API();
