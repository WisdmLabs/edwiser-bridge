<?php

class EdwiserBridge_Blocks
{
    public function __construct()
    {
        add_action('init', array($this, 'eb_register_blocks'));
        add_action('wp_enqueue_scripts', array($this, 'eb_woo_storeapi_nonce'));
        add_filter('block_categories_all', array($this, 'eb_register_edwiser_category'));
        add_action('wp_after_insert_post', array($this, 'handle_block_setting_change'), 10, 3);
    }

    public function eb_register_blocks()
    {
        register_block_type(__DIR__  . '/../blocks/build/courses');
        register_block_type(__DIR__  . '/../blocks/build/course-description');
        register_post_meta('page', 'courseId', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'integer',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ));
        register_post_meta('page', 'showRecommendedCourses', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ));
    }

    public function eb_woo_storeapi_nonce()
    {
        wp_register_script('eb_woo_storeapi_nonce', '', [], '', true);

        wp_enqueue_script('eb_woo_storeapi_nonce');

        $nonce = wp_create_nonce('wc_store_api');

        wp_localize_script(
            'eb_woo_storeapi_nonce',
            'ebStoreApiNonce',
            array(
                'nonce' => $nonce,
            )
        );
    }

    public function eb_register_edwiser_category($categories)
    {

        $categories[] = array(
            'slug'  => 'edwiser',
            'title' => 'Edwiser'
        );

        return $categories;
    }

    public function handle_block_setting_change($post_id, $post, $update) {
        // Only run on post updates, not new posts
        if (!$update) return;
    
        // Skip autosaves and revisions
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Only handle specific post types if needed
        if (!in_array($post->post_type, ['page'])) {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get the current setting value
        $current_value = get_post_meta($post_id, 'courseId', true);
    
        // Get the previous value (stored in a separate meta field)
        $previous_value = get_post_meta($post_id, 'courseIdold', true);
    
        // Check if the value has changed
        if ($current_value !== $previous_value) {
            $course_id = $current_value;
            $gutenberg_pages = get_option('eb_gutenberg_pages', array());
            $gutenberg_pages['single_course_block_id'] = (int) $course_id;
            update_option('eb_gutenberg_pages', $gutenberg_pages);
            // Update the previous value for next comparison
            update_post_meta($post_id, 'courseIdold', $current_value);
        }
    }
}

new EdwiserBridge_Blocks();
