<?php

/**
 * My Courses API for Edwiser Bridge Blocks
 *
 * @package    Edwiser Bridge
 * @subpackage API
 * @since      1.0.0
 */

namespace app\wisdmlabs\edwiserBridge;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * EdwiserBridge_Blocks_My_Courses_API
 *
 * REST API endpoint for user course data.
 *
 * @since 1.0.0
 */
class EdwiserBridge_Blocks_My_Courses_API
{
    /**
     * API namespace
     *
     * @var string
     */
    private const API_NAMESPACE = 'eb/api/v1';

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'eb_register_my_courses_routes'));
        add_filter('rest_authentication_errors', array($this, 'eb_rest_authentication_errors'), 10, 1);
    }

    /**
     * Register REST API routes
     *
     * @since 1.0.0
     */
    public function eb_register_my_courses_routes()
    {
        register_rest_route(
            self::API_NAMESPACE,
            '/my-courses',
            array(
                'methods' => \WP_REST_Server::READABLE,
                'callback' => array($this, 'eb_get_my_courses'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'number_of_recommended_courses' => array(
                        'required' => false,
                        'type' => 'integer',
                        'default' => 6,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );
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
        if (strpos($request_uri, '/wp-json/eb/api/v1/my-courses') !== false) {
            // For our endpoints, allow the request to proceed if user is logged in
            // This prevents the nonce check from failing after login
            if (is_user_logged_in()) {
                return true;
            }
        }

        return $result;
    }

    /**
     * Get user's enrolled and recommended courses
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function eb_get_my_courses($request)
    {
        $user_id = get_current_user_id();
        // Get enrolled course IDs for recommendations
        $enrolled_course_ids = \app\wisdmlabs\edwiserBridge\eb_get_user_enrolled_courses($user_id);

        // Get recommended courses
        $recommended_courses = array();
        $eb_general_setings = get_option('eb_general');
        $number_of_recommended_courses = $request->get_param('number_of_recommended_courses');

        if (isset($eb_general_setings['eb_enable_recmnd_courses']) && 'yes' === $eb_general_setings['eb_enable_recmnd_courses'] && is_numeric($number_of_recommended_courses) && $number_of_recommended_courses > 0) {
            $rec_cats = $this->get_recommended_categories($enrolled_course_ids);
            // Show recommended courses if either categories exist OR custom courses are configured
            if (count($rec_cats) || (isset($eb_general_setings['eb_recmnd_courses']) && count($eb_general_setings['eb_recmnd_courses']))) {
                $recommended_courses = $this->get_recommended_courses($user_id, $enrolled_course_ids, $number_of_recommended_courses, $rec_cats);
            }
        }

        if (!is_user_logged_in()) {
            return new \WP_REST_Response(array(
                'auth_required' => true,
                'message' => __('You must be logged in to access your courses!', 'edwiser-bridge'),
                'sign_in_url' => html_entity_decode(esc_url(\app\wisdmlabs\edwiserBridge\wdm_eb_user_account_url())),
                'enrolled_courses' => array(),
                'recommended_courses' => $recommended_courses,
                'auth_required' => true,
            ), 200);
        }

        // Get current user ID
        $user_id = get_current_user_id();

        // Get enrolled courses
        $enrolled_courses = $this->get_enrolled_courses($user_id);

        $setting = get_option('eb_general', array());
        $course_page_url     = isset($setting['eb_courses_page_id']) ? get_permalink($setting['eb_courses_page_id']) : null;


        $response_data = array(
            'enrolled_courses' => $enrolled_courses,
            'recommended_courses' => $recommended_courses,
            'courses_page_url' => $course_page_url,
            'auth_required' => false,
        );

        return new \WP_REST_Response($response_data, 200);
    }

    /**
     * Get user's enrolled courses
     *
     * @param int  $user_id User ID.
     * @return array
     */
    private function get_enrolled_courses($user_id)
    {
        // Get enrolled course IDs
        $enrolled_course_ids = \app\wisdmlabs\edwiserBridge\eb_get_user_enrolled_courses($user_id);

        if (empty($enrolled_course_ids)) {
            return array();
        }

        // Query for course posts
        $enrolled_courses = get_posts(array(
            'post_type' => 'eb_course',
            'post_status' => 'publish',
            'post__in' => $enrolled_course_ids,
            'posts_per_page' => -1,
        ));

        // Get progress data if needed
        $progress_data = array();
        $course_progress_manager = new \app\wisdmlabs\edwiserBridge\Eb_Course_Progress();
        $progress_data = $course_progress_manager->get_course_progress();

        // Format course data
        $formatted_courses = array();
        foreach ($enrolled_courses as $course) {
            $is_enrolled = in_array($course->ID, $enrolled_course_ids);
            $course_data      = apply_filters('eb_content_course_before', $course->ID, array(), $is_enrolled);

            $course_categories = [];

            foreach ($course_data['categories'] as $id => $name) {
                $course_categories[] = [
                    'id' => (int)$id,
                    'name' => $name
                ];
            }

            // extract price and currency
            $price_info = $this->extract_price_info($course_data['course_price_formatted'] ?? '$ 0');

            $course_data = array(
                'id'        => $course->ID,
                'title'     => __($course->post_title, 'edwiser-bridge'),
                'link'      => get_permalink($course->ID),
                'excerpt'   => !empty($course_data['short_description']) ? __($course_data['short_description'], 'edwiser-bridge') : wp_strip_all_tags(html_entity_decode(__($course->post_content, 'edwiser-bridge'))),
                'category'  => !empty($course_data['categories']) ? __(html_entity_decode(reset($course_data['categories']), ENT_QUOTES, 'UTF-8'), 'edwiser-bridge') : __('Uncategorized', 'edwiser-bridge'),
                'thumbnail' =>  $course_data['thumb_url'],
                'suspended' => \app\wisdmlabs\edwiserBridge\wdm_eb_get_user_suspended_status($user_id, $course->ID) == true,
                'price'     => [
                    'amount'        => $price_info['amount'],
                    'currency'      => $price_info['currency'],
                    'type'     => $course_data['course_price_type'],
                    'enrolled'      => $course_data['is_eb_my_courses'],
                    'originalAmount' => null,
                ],
                'createdAt' => $course->post_date,
                'categories' => $course_categories,
            );

            $course_data['progress'] = $this->get_course_progress_data($course->ID, $user_id, $progress_data);

            $formatted_courses[] = $course_data;
        }

        return $formatted_courses;
    }

    /**
     * Get recommended courses for user
     *
     * @param int $user_id User ID.
     * @param array $enrolled_course_ids Array of enrolled course IDs to exclude.
     * @param int $limit Number of courses to return.
     * @param array $rec_cats Recommended categories array.
     * @return array
     */
    private function get_recommended_courses($user_id, $enrolled_course_ids, $limit = 3, $rec_cats = array())
    {
        $eb_general_setings = get_option('eb_general');
        $args = array();

        // Create query based on settings - matches shortcode logic
        if (isset($eb_general_setings['eb_show_default_recmnd_courses']) && 'yes' === $eb_general_setings['eb_show_default_recmnd_courses'] && !empty($rec_cats)) {
            // Category-based recommendations (only if categories exist)
            $args = array(
                'post_type' => 'eb_course',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'eb_course_cat',
                        'field' => 'slug',
                        'terms' => array_keys($rec_cats),
                    ),
                ),
                'post__not_in' => $enrolled_course_ids,
            );
        } elseif (isset($eb_general_setings['eb_recmnd_courses']) && !empty($eb_general_setings['eb_recmnd_courses'])) {
            // Custom selected courses
            $args = array(
                'post_type' => 'eb_course',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                'post__in' => $eb_general_setings['eb_recmnd_courses'],
            );
        }

        if (empty($args)) {
            return array();
        }

        // Query for recommended courses
        $recommended_courses = get_posts($args);

        // Format course data
        $formatted_courses = array();
        foreach ($recommended_courses as $course) {
            $is_enrolled = in_array($course->ID, $enrolled_course_ids);
            $course_data      = apply_filters('eb_content_course_before', $course->ID, array(), $is_enrolled);

            $course_categories = [];

            foreach ($course_data['categories'] as $id => $name) {
                $course_categories[] = [
                    'id' => (int)$id,
                    'name' => $name
                ];
            }

            // extract price and currency
            $price_info = $this->extract_price_info($course_data['course_price_formatted'] ?? '$ 0');

            $formatted_courses[] = array(
                'id'        => $course->ID,
                'title'     => __($course->post_title, 'edwiser-bridge'),
                'link'      => get_permalink($course->ID),
                'excerpt'   => !empty($course_data['short_description']) ? __($course_data['short_description'], 'edwiser-bridge') : wp_strip_all_tags(html_entity_decode(__($course->post_content, 'edwiser-bridge'))),
                'category'  => !empty($course_data['categories']) ? __(html_entity_decode(reset($course_data['categories']), ENT_QUOTES, 'UTF-8'), 'edwiser-bridge') : __('Uncategorized', 'edwiser-bridge'),
                'thumbnail' =>  $course_data['thumb_url'],
                'suspended' => \app\wisdmlabs\edwiserBridge\wdm_eb_get_user_suspended_status($user_id, $course->ID) == true,
                'price'     => [
                    'amount'        => $price_info['amount'],
                    'currency'      => $price_info['currency'],
                    'type'     => $course_data['course_price_type'],
                    'enrolled'      => $course_data['is_eb_my_courses'],
                    'originalAmount' => null,
                ],
                'createdAt' => $course->post_date,
                'categories' => $course_categories,
            );
        }

        return $formatted_courses;
    }

    /**
     * Get recommended categories based on enrolled courses
     *
     * @param array $enrolled_course_ids Array of enrolled course IDs.
     * @return array
     */
    private function get_recommended_categories($enrolled_course_ids)
    {
        $rec_cats = array();

        foreach ($enrolled_course_ids as $course_id) {
            $terms = get_the_terms($course_id, 'eb_course_cat');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $rec_cats[$term->slug] = $term->name;
                }
            }
        }

        return $rec_cats;
    }

    /**
     * Get course progress data
     *
     * @param int   $course_id Course ID.
     * @param int   $user_id User ID.
     * @param array $progress_data Progress data array.
     * @return array
     */
    private function get_course_progress_data($course_id, $user_id, $progress_data)
    {
        $progress_percentage = isset($progress_data[$course_id]) ? floatval($progress_data[$course_id]) : 0;

        // Get Moodle course URL
        $mdl_uid = get_user_meta($user_id, 'moodle_user_id', true);
        $course_manager = \app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->course_manager();
        $mdl_course_id = $course_manager->get_moodle_course_id($course_id);
        $course_url = $mdl_uid ? \app\wisdmlabs\edwiserBridge\wdm_eb_get_my_course_url($mdl_uid, $mdl_course_id) : '';

        // Determine status
        $status = 'not_started';
        $action_text = __('Start', 'edwiser-bridge');

        if ($progress_percentage > 0) {
            if ($progress_percentage >= 100) {
                $status = 'completed';
                $action_text = __('View', 'edwiser-bridge');
            } else {
                $status = 'in_progress';
                $action_text = __('Resume', 'edwiser-bridge');
            }
        }

        return array(
            'percentage' => round($progress_percentage),
            'status' => $status,
            'action_text' => $action_text,
            'course_url' => $course_url,
            'completed' => $progress_percentage >= 100 ? true : false,
        );
    }

    /**
     * Extract currency symbol and amount from formatted price string
     * 
     * @param string $formatted_price The formatted price string
     * @return array Array with currency symbol and amount
     */
    private function extract_price_info($formatted_price)
    {
        preg_match('/^(\D+)\s*(\d+(\.\d+)?)/', $formatted_price, $matches);

        return [
            'currency' => isset($matches[1]) ? trim($matches[1]) : '$',
            'amount' => isset($matches[2]) ? floatval($matches[2]) : 0
        ];
    }
}

// Initialize the API
new EdwiserBridge_Blocks_My_Courses_API();
