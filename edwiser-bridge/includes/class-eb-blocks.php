<?php

class EdwiserBridge_Blocks
{
    public function __construct()
    {
        add_action('init', array($this, 'eb_register_blocks'));
        add_action('wp_enqueue_scripts', array($this, 'eb_set_script_translations'));
        add_action('wp_enqueue_scripts', array($this, 'eb_woo_storeapi_nonce'));
        add_filter('block_categories_all', array($this, 'eb_register_edwiser_category'));
        add_action('wp_after_insert_post', array($this, 'handle_block_setting_change'), 10, 3);

        // AJAX handlers for order details
        add_action('wp_ajax_eb_get_order_details', array($this, 'eb_get_order_details'));
        add_action('wp_ajax_nopriv_eb_get_order_details', array($this, 'eb_get_order_details'));
    }

    public function eb_register_blocks()
    {
        load_plugin_textdomain('edwiser-bridge', false, dirname(plugin_basename(__DIR__)) . '/languages');

        wp_register_script(
            'eb-courses-script',
            plugins_url('/blocks/build/courses/index.js', __DIR__),
            array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components'),
            filemtime(plugin_dir_path(__DIR__) . 'blocks/build/courses/index.js')
        );
        wp_register_script(
            'eb-course-description-script',
            plugins_url('/blocks/build/course-description/index.js', __DIR__),
            array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components'),
            filemtime(plugin_dir_path(__DIR__) . 'blocks/build/course-description/index.js')
        );

        register_block_type(__DIR__  . '/../blocks/build/courses', array(
            'editor_script' => 'eb-courses-script',
        ));
        register_block_type(__DIR__  . '/../blocks/build/course-description', array(
            'editor_script' => 'eb-course-description-script',
        ));

        register_post_meta('page', 'courseId', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'integer',
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            }
        ));

        register_block_type(__DIR__  . '/../blocks/build/user-account');
        register_block_type(__DIR__  . '/../blocks/build/my-courses');
        register_block_type(__DIR__  . '/../blocks/build/user-account-v2');

        // Tabs Blocks
        register_block_type(__DIR__  . '/../blocks/build/dashboard');
        register_block_type(__DIR__  . '/../blocks/build/orders');
        register_block_type(__DIR__  . '/../blocks/build/profile');
    }

    public function eb_set_script_translations()
    {
        wp_set_script_translations('eb-courses-script', 'edwiser-bridge', plugin_dir_path(__FILE__) . 'languages/');
        wp_set_script_translations('eb-course-description-script', 'edwiser-bridge', plugin_dir_path(__FILE__) . 'languages/');
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

        wp_localize_script('eb_woo_storeapi_nonce', 'wc_params', array(
            'cancel_order_nonce' => wp_create_nonce('woocommerce-cancel_order'),
            'order_again_nonce' => wp_create_nonce('woocommerce-order_again')
        ));

        wp_localize_script('eb_woo_storeapi_nonce', 'eb_order_details', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eb_order_details_nonce')
        ));
    }

    public function eb_register_edwiser_category($categories)
    {

        $categories[] = array(
            'slug'  => 'edwiser',
            'title' => 'Edwiser'
        );

        return $categories;
    }

    public function handle_block_setting_change($post_id, $post, $update)
    {
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

    /**
     * AJAX handler to get WooCommerce order details template
     */
    public function eb_get_order_details()
    {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            wp_die('WooCommerce is not active');
        }

        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'eb_order_details_nonce')) {
            wp_die('Security check failed');
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_die('User not logged in');
        }

        $order_id = intval($_POST['order_id']);

        if (!$order_id) {
            wp_die('Invalid order ID');
        }

        // Get the order
        $order = function_exists('wc_get_order') ? wc_get_order($order_id) : null;

        if (!$order) {
            wp_die('Order not found');
        }

        // Check if the order belongs to the current user
        if ($order->get_user_id() !== get_current_user_id()) {
            wp_die('Access denied');
        }

        // Set up the necessary global variables and context
        global $post;

        // Set the order ID for the template context
        $order_id = $order->get_id();

        // Ensure we're in the right context for WooCommerce templates
        if (!defined('WOOCOMMERCE_CHECKOUT')) {
            define('WOOCOMMERCE_CHECKOUT', true);
        }

        // Set up the post object for template context
        $post = get_post($order_id);
        setup_postdata($post);

        // Start output buffering to capture the template
        ob_start();

        // Include WooCommerce order details template
        if (function_exists('wc_get_template')) {
            // Get order items for the template
            $order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));

            // Set up the template variables that WooCommerce expects
            $template_vars = array(
                'order' => $order,
                'order_id' => $order_id,
                'order_items' => $order_items,
                'show_purchase_note' => $order->has_status(array('completed', 'processing')),
                'download_permitted' => $order->is_download_permitted(),
                'show_downloads' => $order->has_downloadable_item() && $order->is_download_permitted(),
                'downloads' => $order->get_downloadable_items(),
                'actions' => array_filter(wc_get_account_orders_actions($order), function ($key) {
                    return 'view' !== $key;
                }, ARRAY_FILTER_USE_KEY),
                'show_customer_details' => $order->get_user_id() === get_current_user_id()
            );

            wc_get_template('order/order-details.php', $template_vars);
        } else {
            wp_die('WooCommerce template functions not available');
        }

        $template_html = ob_get_clean();

        // Clean up post data
        wp_reset_postdata();

        // Return the HTML with debug info
        wp_send_json_success(array(
            'html' => $template_html,
        ));
    }
}

new EdwiserBridge_Blocks();
