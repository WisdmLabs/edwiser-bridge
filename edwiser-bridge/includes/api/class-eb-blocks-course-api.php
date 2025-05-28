<?php

use app\wisdmlabs\edwiserBridge\Eb_Enrollment_Manager;
use app\wisdmlabs\edwiserBridge\Eb_Payment_Manager;

use function app\wisdmlabs\edwiserBridge\edwiser_bridge_instance;

if (!defined('ABSPATH')) {
    exit;
}

class EdwiserBridge_Blocks_Course_API
{

    // API namespace
    private const API_NAMESPACE = 'eb/api/v1';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'eb_register_course_routes'));
    }

    /**
     * Register API routes.
     */
    public function eb_register_course_routes()
    {
        register_rest_route(self::API_NAMESPACE, '/courses', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array($this, 'eb_get_courses'),
            'permission_callback' => '__return_true',
            'args' => array(
                'page' => array(
                    'description' => __('Page number for pagination', 'edwiser-bridge'),
                    'type'        => 'integer',
                    'default'     => 1,
                    'sanitize_callback' => 'absint',
                ),
                'per_page' => array(
                    'description' => __('Number of courses per page', 'edwiser-bridge'),
                    'type'        => 'integer',
                    'default'     => 9,
                    'sanitize_callback' => 'absint',
                ),
                'sort_order' => array(
                    'description' => __('Sort order (latest, oldest, a-z, z-a)', 'edwiser-bridge'),
                    'type'        => 'string',
                    'enum'        => array('latest', 'oldest', 'a-z', 'z-a'),
                    'default'     => 'latest',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'search' => array(
                    'description' => __('Search term to filter courses', 'edwiser-bridge'),
                    'type'        => 'string',
                    'default'     => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'group_by_category' => array(
                    'description' => __('Group courses by category', 'edwiser-bridge'),
                    'type'        => 'boolean',
                    'default'     => false,
                ),
                'category_per_page' => array(
                    'description' => __('Number of categories per page when grouping by category', 'edwiser-bridge'),
                    'type'        => 'integer',
                    'default'     => 0,
                    'sanitize_callback' => 'absint',
                ),
                'categories' => array(
                    'description' => __('Comma-separated list of category slugs to filter', 'edwiser-bridge'),
                    'type'        => 'string',
                    'default'     => '',
                    'sanitize_callback' => 'sanitize_text_field',
                )
            )
        ));

        register_rest_route(self::API_NAMESPACE, '/courses/(?P<course_id>\d+)', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array($this, 'eb_get_single_course'),
            'permission_callback' => '__return_true',
            'args' => array(
                'course_id' => array(
                    'description' => __('Course ID', 'edwiser-bridge'),
                    'type'        => 'integer',
                    'required'    => true,
                    'sanitize_callback' => 'absint',
                ),
            )
        ));
    }

    /**
     * Get all courses with pagination and filtering
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response with courses data
     */
    public function eb_get_courses($request)
    {
        // get query parameters
        $page = max(1, $request['page']);
        $per_page = max(9, $request['per_page']);
        $sort_order = strtolower($request['sort_order']);
        $search_query = strtolower($request['search']);
        $category = isset($request['category']) ? sanitize_text_field($request['category']) : '';
        $group_by_category = isset($request['group_by_category']) ? filter_var($request['group_by_category'], FILTER_VALIDATE_BOOLEAN) : false;
        $category_per_page = isset($request['category_per_page']) ? absint($request['category_per_page']) : 0;
        $categories_filter = isset($request['categories']) ? sanitize_text_field($request['categories']) : '';

        $user_id = apply_filters('determine_current_user', false);
        wp_set_current_user($user_id);

        // get the taxonomy used for course categories
        $taxonomy_names = get_object_taxonomies('eb_course');
        $category_taxonomy = '';

        // find the category taxonomy
        foreach ($taxonomy_names as $taxonomy) {
            if (strpos($taxonomy, 'category') !== false || strpos($taxonomy, 'cat') !== false) {
                $category_taxonomy = $taxonomy;
                break;
            }
        }

        // Parse the comma-separated categories
        $selected_categories_slugs = [];
        if (!empty($categories_filter)) {
            $selected_categories_slugs = array_map('trim', explode(',', $categories_filter));
        }

        // get all available categories for eb_course post type
        $categories = [];

        if (!empty($category_taxonomy)) {
            $terms_args = [
                'taxonomy' => $category_taxonomy,
                'hide_empty' => true,
            ];

            // Filter categories by slug if specified
            if (!empty($selected_categories_slugs)) {
                $terms_args['slug'] = $selected_categories_slugs;
            }

            $terms = get_terms($terms_args);

            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    $categories[] = [
                        'id' => $term->term_id,
                        'name' => __(html_entity_decode($term->name, ENT_QUOTES, 'UTF-8'), 'edwiser-bridge'),
                        'slug' => $term->slug,
                        'count' => $term->count
                    ];
                }
            }
        }

        if ($group_by_category) {
            return $this->get_courses_grouped_by_category(
                $page,
                $sort_order,
                $search_query,
                $category,
                $category_taxonomy,
                $categories,
                $category_per_page,
                $selected_categories_slugs
            );
        }

        $args = array(
            'post_type'      => 'eb_course',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page
        );

        if (!empty($search_query)) {
            $args['s'] = $search_query;
        }

        // Tax query for filtering
        $tax_query = [];

        // add category filter if provided
        if (!empty($category) && !empty($category_taxonomy)) {
            $tax_query[] = array(
                'taxonomy' => $category_taxonomy,
                'field'    => 'slug',
                'terms'    => $category,
            );
        }

        // Add selected categories filter
        if (!empty($selected_categories_slugs) && !empty($category_taxonomy)) {
            $tax_query[] = array(
                'taxonomy' => $category_taxonomy,
                'field'    => 'slug',
                'terms'    => $selected_categories_slugs,
                'operator' => 'IN',
            );
        }

        // Apply tax query if we have conditions
        if (!empty($tax_query)) {
            if (count($tax_query) > 1) {
                $tax_query['relation'] = 'AND';
            }
            $args['tax_query'] = $tax_query;
        }

        // sort courses
        switch ($sort_order) {
            case 'latest':
                $args['orderby'] = 'post_date';
                $args['order'] = 'DESC';
                break;
            case 'oldest':
                $args['orderby'] = 'post_date';
                $args['order'] = 'ASC';
                break;
            case 'a-z':
                $args['orderby'] = 'post_title';
                $args['order'] = 'ASC';
                break;
            case 'z-a':
                $args['orderby'] = 'post_title';
                $args['order'] = 'DESC';
                break;
        }

        // get courses
        $courses = get_posts($args);
        $formatted_courses = [];
        $enrolled_courses = \app\wisdmlabs\edwiserBridge\eb_get_user_enrolled_courses();

        foreach ($courses as &$course) {
            $is_enrolled = in_array($course->ID, $enrolled_courses);
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

        // get total courses (without pagination)
        $total_args = $args;
        $total_args['posts_per_page'] = -1;
        unset($total_args['paged']);
        $total_courses = count(get_posts($total_args));

        return new WP_REST_Response(array(
            'total_courses' => $total_courses,
            'total_pages' => ceil($total_courses / $per_page),
            'current_page' => $page,
            'per_page' => $per_page,
            'courses' => $formatted_courses,
            'categories' => $categories,
        ), 200);
    }

    /**
     * Get courses grouped by category with pagination
     *
     * @param int $page Current page number
     * @param int $per_page Courses per page
     * @param string $sort_order Sort order
     * @param string $search_query Search query
     * @param string $selected_category Selected category slug
     * @param string $category_taxonomy Category taxonomy name
     * @param array $categories Array of category data
     * @param array $all_category_terms Array of WP_Term objects
     * @param int $category_per_page Number of categories per page
     * @return WP_REST_Response Response with courses grouped by category
     */
    private function get_courses_grouped_by_category(
        $page,
        $sort_order,
        $search_query,
        $selected_category,
        $category_taxonomy,
        $categories,
        $category_per_page,
        $selected_categories_slugs = []
    ) {
        $categorized_courses = [];
        $selected_categories = [];
        $enrolled_courses = \app\wisdmlabs\edwiserBridge\eb_get_user_enrolled_courses();

        $user_id = apply_filters('determine_current_user', false);
        wp_set_current_user($user_id);

        // Determine which categories to show
        if (!empty($selected_category)) {
            // If a specific category is selected, only show that category
            foreach ($categories as $cat) {
                if ($cat['slug'] === $selected_category) {
                    $selected_categories[] = $cat;
                    break;
                }
            }
        } else {
            // Show all categories (or paginated if category_per_page is set)
            if ($category_per_page > 0) {
                $offset = ($page - 1) * $category_per_page;
                $selected_categories = array_slice($categories, $offset, $category_per_page);
            } else {
                $selected_categories = $categories;
            }
        }

        // Get courses for each selected category
        foreach ($selected_categories as $category) {
            $args = array(
                'post_type'      => 'eb_course',
                'post_status'    => 'publish',
                'posts_per_page' => -1, // Get all courses for this category
                'tax_query'      => array(
                    array(
                        'taxonomy' => $category_taxonomy,
                        'field'    => 'slug',
                        'terms'    => $category['slug'],
                    ),
                )
            );

            // Add search query if provided
            if (!empty($search_query)) {
                $args['s'] = $search_query;
            }

            // Apply additional category filtering if specified
            if (!empty($selected_categories_slugs) && $category_taxonomy) {
                $args['tax_query'] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => $category_taxonomy,
                        'field'    => 'slug',
                        'terms'    => $category['slug'],
                    ),
                    array(
                        'taxonomy' => $category_taxonomy,
                        'field'    => 'slug',
                        'terms'    => $selected_categories_slugs,
                        'operator' => 'IN',
                    )
                );
            }

            // Sort courses
            switch ($sort_order) {
                case 'latest':
                    $args['orderby'] = 'post_date';
                    $args['order'] = 'DESC';
                    break;
                case 'oldest':
                    $args['orderby'] = 'post_date';
                    $args['order'] = 'ASC';
                    break;
                case 'a-z':
                    $args['orderby'] = 'post_title';
                    $args['order'] = 'ASC';
                    break;
                case 'z-a':
                    $args['orderby'] = 'post_title';
                    $args['order'] = 'DESC';
                    break;
            }

            $courses = get_posts($args);
            $formatted_cat_courses = [];

            foreach ($courses as $course) {
                $is_enrolled = in_array($course->ID, $enrolled_courses);
                $course_data = apply_filters('eb_content_course_before', $course->ID, array(), $is_enrolled);

                $course_categories = [];

                foreach ($course_data['categories'] as $id => $name) {
                    $course_categories[] = [
                        'id' => (int)$id,
                        'name' => $name
                    ];
                }

                // Extract price and currency
                $price_info = $this->extract_price_info($course_data['course_price_formatted'] ?? '$ 0');

                $formatted_cat_courses[] = array(
                    'id'        => $course->ID,
                    'title'     => __($course->post_title, 'edwiser-bridge'),
                    'link'      => get_permalink($course->ID),
                    'excerpt'   => !empty($course_data['short_description']) ? __($course_data['short_description'], 'edwiser-bridge') : wp_strip_all_tags(html_entity_decode(__($course->post_content, 'edwiser-bridge'))),
                    'category'  => __($category['name'], 'edwiser-bridge'),
                    'thumbnail' => $course_data['thumb_url'],
                    'suspended' => \app\wisdmlabs\edwiserBridge\wdm_eb_get_user_suspended_status($user_id, $course->ID) == true,
                    'price'     => [
                        'amount'        => $price_info['amount'],
                        'currency'      => $price_info['currency'],
                        'type'          => $course_data['course_price_type'],
                        'enrolled'      => $course_data['is_eb_my_courses'],
                        'originalAmount' => null,
                    ],
                    'createdAt' => $course->post_date,
                    'categories' => $course_categories,
                );
            }

            if (!empty($formatted_cat_courses)) {
                $categorized_courses[$category['slug']] = $formatted_cat_courses;
            }
        }

        // Calculate total pages for category pagination
        $total_pages = 1;
        if ($category_per_page > 0 && count($categories) > 0) {
            if (!empty($selected_category)) {
                $total_pages = 1;
            } else {
                $total_pages = ceil(count($categories) / $category_per_page);
            }
        }

        return new WP_REST_Response(array(
            'total_pages' => $total_pages,
            'current_page' => $page,
            'categories' => $categories,
            'displayed_categories' => $selected_categories,
            'categorized_courses' => $categorized_courses,
        ), 200);
    }

    /**
     * Get single course by ID
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response with course data or error
     */
    public function eb_get_single_course($request)
    {
        $course_id = $request['course_id'];
        $course = get_post($course_id);

        $user_id = apply_filters('determine_current_user', false);
        wp_set_current_user($user_id);

        if (!$course || $course->post_type !== 'eb_course') {
            return new WP_Error('no_course', __('Course not found', 'edwiser-bridge'), ['status' => 404]);
        }

        $enrolled_courses = \app\wisdmlabs\edwiserBridge\eb_get_user_enrolled_courses();
        $is_enrolled = in_array($course->ID, $enrolled_courses);
        $course_data = apply_filters('eb_content_course_before', $course->ID, [], $is_enrolled);

        // Extract price information
        $price_info = $this->extract_price_info($course_data['course_price_formatted'] ?? '$ 0');

        // Get course options
        $course_options = get_post_meta($course->ID, 'eb_course_options', true);

        // Get recommended courses if enabled
        $recommended_courses = [];
        $show_recommended = isset($course_options['enable_recmnd_courses']) && $course_options['enable_recmnd_courses'] === 'yes';

        if ($show_recommended) {
            $recommended_courses = $this->get_recommended_courses($course, $course_options, $course_data);
        }

        // Get course CTA details
        $has_course_access = edwiser_bridge_instance()->enrollment_manager()->user_has_course_access($user_id, $course_id);
        $user_is_suspended = \app\wisdmlabs\edwiserBridge\wdm_eb_get_user_suspended_status($user_id, $course_id);

        $show_take_course_button = !$has_course_access || $user_is_suspended || !is_user_logged_in();

        $course_cta_html = $show_take_course_button
            ? Eb_Payment_Manager::take_course_button($course_id)
            : Eb_Payment_Manager::access_course_button($course_id);

        $course_cta = wp_kses($course_cta_html, \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags());

        $is_user_suspended = \app\wisdmlabs\edwiserBridge\wdm_eb_get_user_suspended_status($user_id, $course->ID);
        $status = null;
        if ($is_user_suspended) {
            $status = 'suspended';
        } elseif ($is_enrolled) {
            $status = 'enrolled';
        }

        $remaining_access = Eb_Enrollment_Manager::access_remianing($user_id, $course->ID);

        $categories = [];

        foreach ($course_data['categories'] as $id => $name) {
            $categories[] = [
                'id' => (int)$id,
                'name' => __($name, 'edwiser-bridge')
            ];
        }

        $response_data = [
            'id' => $course->ID,
            'title' => __($course->post_title, 'edwiser-bridge'),
            'content' => apply_filters('the_content', __($course->post_content, 'edwiser-bridge')),
            'category' => !empty($course_data['categories']) ? reset($course_data['categories']) : __('Uncategorized', 'edwiser-bridge'),
            'permalink' => get_permalink($course->ID),
            'thumbnail' => $course_data['thumb_url'],
            'course_expiry' => isset($course_options['course_expirey']) && $course_options['course_expirey'] === 'yes',
            'remaining_access' => $remaining_access,
            'course_closed_url' => $course_options['course_closed_url'] ?? '',
            'status' => $status,
            'price' => [
                'amount' => $price_info['amount'],
                'currency' => $price_info['currency'],
                'type' => $course_data['course_price_type'] ?? '',
                'originalAmount' => null,
            ],
            'course_cta' => $course_cta,
            'moodle_course_id' => $course_options['moodle_course_id'] ?? 0,
            'show_recommended_courses' => $show_recommended,
            'recommended_courses' => $recommended_courses,
            'categories' => $categories
        ];

        if (isset($course_options['course_expirey']) && $course_options['course_expirey'] === 'yes') {
            $response_data['course_expires_after_days'] = is_user_logged_in() && $is_enrolled && '0000-00-00 00:00:00' !== $remaining_access ? $remaining_access : $course_options['num_days_course_access'];
        }

        ob_end_clean();
        return new WP_REST_Response($response_data, 200);
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

    /**
     * Get recommended courses for a specific course
     * 
     * @param WP_Post $course The course post object
     * @param array $course_options Course options metadata
     * @param array $course_data Course data from filters
     * @return array Array of recommended course post objects
     */
    private function get_recommended_courses($course, $course_options, $course_data)
    {
        // If recommended courses are not enabled, return empty array
        if (!isset($course_options['enable_recmnd_courses']) || $course_options['enable_recmnd_courses'] !== 'yes') {
            return [];
        }

        $user_id = apply_filters('determine_current_user', false);
        wp_set_current_user($user_id);

        $query_args = [];

        // Default recommendation based on category
        if (isset($course_options['show_default_recmnd_course']) && $course_options['show_default_recmnd_course'] === 'yes') {
            $query_args = [
                'post_type'      => 'eb_course',
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
                'numberposts'    => 3,
                'post__not_in'   => [$course->ID], // Exclude current course
                'tax_query'      => [
                    [
                        'taxonomy' => 'eb_course_cat',
                        'field'    => 'tag_ID',
                        'terms'    => array_keys($course_data['categories'] ?? []),
                    ],
                ],
            ];
        }
        // Specific recommended courses
        elseif (!empty($course_options['enable_recmnd_courses_single_course'])) {
            $query_args = [
                'post_type'      => 'eb_course',
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
                'post__in'       => $course_options['enable_recmnd_courses_single_course'],
                'post__not_in'   => [$course->ID], // Exclude current course
            ];
        }

        // If no valid query args, return empty array
        if (empty($query_args)) {
            return [];
        }

        // Get recommended courses
        $courses = get_posts($query_args);
        $formatted_courses = [];
        $enrolled_courses = \app\wisdmlabs\edwiserBridge\eb_get_user_enrolled_courses();

        // Format each course
        foreach ($courses as $rec_course) {
            $is_enrolled = in_array($rec_course->ID, $enrolled_courses);
            $rec_course_data = apply_filters('eb_content_course_before', $rec_course->ID, [], $is_enrolled);

            $rec_course_categories = [];

            foreach ($rec_course_data['categories'] as $id => $name) {
                $rec_course_categories[] = [
                    'id' => (int)$id,
                    'name' => __($name, 'edwiser-bridge')
                ];
            }

            // Extract price information
            $price_info = $this->extract_price_info($rec_course_data['course_price_formatted'] ?? '$ 0');

            $formatted_courses[] = [
                'id'        => $rec_course->ID,
                'title'     => __($rec_course->post_title, 'edwiser-bridge'),
                'link'      => get_permalink($rec_course->ID),
                'excerpt'   =>  !empty($rec_course_data['short_description']) ? __($rec_course_data['short_description'], 'edwiser-bridge') : wp_strip_all_tags(html_entity_decode(__($rec_course->post_content, 'edwiser-bridge'))),
                'category'  => !empty($rec_course_data['categories']) ? reset($rec_course_data['categories']) : __('Uncategorized', 'edwiser-bridge'),
                'thumbnail' => $rec_course_data['thumb_url'] ?? '',
                'suspended' => \app\wisdmlabs\edwiserBridge\wdm_eb_get_user_suspended_status($user_id, $rec_course->ID) == true,
                'price'     => [
                    'amount'        => $price_info['amount'],
                    'currency'      => $price_info['currency'],
                    'type'          => $rec_course_data['course_price_type'] ?? '',
                    'enrolled'      => $rec_course_data['is_eb_my_courses'] ?? false,
                    'originalAmount' => null,
                ],
                'createdAt' => $rec_course->post_date,
                'categories' => $rec_course_categories,
            ];
        }

        return $formatted_courses;
    }
}

new EdwiserBridge_Blocks_Course_API();
