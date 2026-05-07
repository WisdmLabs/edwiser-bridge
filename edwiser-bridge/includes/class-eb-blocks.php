<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EdwiserBridge_Blocks
{
    public function __construct()
    {
        add_action('init', array($this, 'eb_register_blocks'));
        add_action('wp_enqueue_scripts', array($this, 'eb_set_script_translations'));
        add_action('wp_enqueue_scripts', array($this, 'eb_woo_storeapi_nonce'));
        add_action('wp_enqueue_scripts', array($this, 'eb_enqueue_block_styles_conditionally'));
        // Prevent auto-enqueuing of block styles - we'll load them conditionally
        add_filter('should_load_separate_core_block_assets', '__return_false');
        add_filter('block_type_metadata_settings', array($this, 'eb_prevent_block_style_auto_enqueue'), 10, 2);
        add_filter('block_categories_all', array($this, 'eb_register_edwiser_category'));
        add_action('wp_after_insert_post', array($this, 'handle_block_setting_change'), 10, 3);

        // Load block styles in the Gutenberg editor (wp_enqueue_scripts doesn't fire in admin)
        add_action('enqueue_block_editor_assets', array($this, 'eb_enqueue_editor_block_styles'));

        // AJAX handlers for order details
        add_action('wp_ajax_eb_get_order_details', array($this, 'eb_get_order_details'));
    }

    public function eb_register_blocks()
    {
        // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- Required for custom translation loading path.
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

        // Register blocks without auto-enqueuing styles - styles will load only when block is used
        register_block_type(__DIR__  . '/../blocks/build/courses', array(
            'editor_script' => 'eb-courses-script',
            'style' => false, // Prevent auto-enqueue, will load conditionally
        ));
        register_block_type(__DIR__  . '/../blocks/build/course-description', array(
            'editor_script' => 'eb-course-description-script',
            'style' => false, // Prevent auto-enqueue, will load conditionally
        ));

        register_post_meta('page', 'courseId', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'integer',
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            }
        ));

        // Register blocks without auto-enqueuing styles - styles will load only when block is used
        register_block_type(__DIR__  . '/../blocks/build/user-account', array(
            'style' => false, // Prevent auto-enqueue
        ));
        register_block_type(__DIR__  . '/../blocks/build/my-courses', array(
            'style' => false, // Prevent auto-enqueue
        ));
        register_block_type(__DIR__  . '/../blocks/build/user-account-v2', array(
            'style' => false, // Prevent auto-enqueue
        ));

        // Tabs Blocks
        register_block_type(__DIR__  . '/../blocks/build/dashboard', array(
            'style' => false, // Prevent auto-enqueue
        ));
        register_block_type(__DIR__  . '/../blocks/build/orders', array(
            'style' => false, // Prevent auto-enqueue
        ));
        register_block_type(__DIR__  . '/../blocks/build/profile', array(
            'style' => false, // Prevent auto-enqueue
        ));
    }

    public function eb_set_script_translations()
    {
        // Only set translations if blocks are used (editor or frontend with blocks)
        if (is_admin() || $this->has_eb_blocks()) {
            wp_set_script_translations('eb-courses-script', 'edwiser-bridge', plugin_dir_path(__FILE__) . 'languages/');
            wp_set_script_translations('eb-course-description-script', 'edwiser-bridge', plugin_dir_path(__FILE__) . 'languages/');
        }
    }

    public function eb_woo_storeapi_nonce()
    {
        // Only load on pages with EB blocks
        if (!$this->has_eb_blocks()) {
            return;
        }

        wp_register_script('eb_woo_storeapi_nonce', '', array(), '4.3.4', true);

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

    /**
     * Prevent block styles and viewScripts from auto-enqueuing
     */
    public function eb_prevent_block_style_auto_enqueue($settings, $metadata)
    {
        // Only affect EB blocks
        if (isset($metadata['name']) && strpos($metadata['name'], 'edwiser-bridge/') !== false) {
            // Remove only the raw viewScript key so WordPress still uses view_script_handles
            // to auto-enqueue view.js when do_blocks() renders the block (e.g. on course pages
            // where $post->post_content is empty and has_eb_blocks() cannot detect the block).
            if (isset($settings['viewScript'])) {
                unset($settings['viewScript']);
            }
        }
        return $settings;
    }

    /**
     * Conditionally enqueue block styles only when blocks are used
     */
    public function eb_enqueue_block_styles_conditionally()
    {
        if (!$this->has_eb_blocks()) {
            return;
        }
    }

    /**
     * Check if current page has EB blocks
     *
     * @return bool
     */
    private function has_eb_blocks()
    {
        global $post;
        if (!is_a($post, 'WP_Post')) {
            return false;
        }

        if (has_blocks($post->post_content)) {
            $blocks = parse_blocks($post->post_content);
            $found_blocks = array();
            foreach ($blocks as $block) {
                if (isset($block['blockName']) && strpos($block['blockName'], 'edwiser-bridge') !== false) {
                    $found_blocks[] = $block['blockName'];
                }
                // Check nested blocks
                if (!empty($block['innerBlocks'])) {
                    foreach ($block['innerBlocks'] as $inner_block) {
                        if (isset($inner_block['blockName']) && strpos($inner_block['blockName'], 'edwiser-bridge') !== false) {
                            $found_blocks[] = $inner_block['blockName'];
                        }
                    }
                }
            }

            // Enqueue styles for found blocks
            foreach (array_unique($found_blocks) as $block_name) {
                $this->enqueue_block_styles($block_name);
            }

            return !empty($found_blocks);
        }
        return false;
    }

    /**
     * Enqueue styles and viewScripts for specific EB block
     *
     * @param string $block_name
     */
    private function enqueue_block_styles($block_name)
    {
        $block_assets = array(
            'edwiser-bridge/courses' => array(
                'style' => '/blocks/build/courses/style-index.css',
                'viewScript' => '/blocks/build/courses/view.js'
            ),
            'edwiser-bridge/course-description' => array(
                'style' => '/blocks/build/course-description/style-index.css',
                'viewScript' => '/blocks/build/course-description/view.js'
            ),
            'edwiser-bridge/user-account' => array(
                'style' => '/blocks/build/user-account/style-index.css',
                'viewScript' => '/blocks/build/user-account/view.js'
            ),
            'edwiser-bridge/my-courses' => array(
                'style' => '/blocks/build/my-courses/style-index.css',
                'viewScript' => '/blocks/build/my-courses/view.js'
            ),
            'edwiser-bridge/user-account-v2' => array(
                'style' => '/blocks/build/user-account-v2/style-index.css',
                'viewScript' => '/blocks/build/user-account-v2/view.js'
            ),
            'edwiser-bridge/dashboard' => array(
                'style' => '/blocks/build/dashboard/style-index.css',
                'viewScript' => '/blocks/build/dashboard/view.js'
            ),
            'edwiser-bridge/orders' => array(
                'style' => '/blocks/build/orders/style-index.css',
                'viewScript' => '/blocks/build/orders/view.js'
            ),
            'edwiser-bridge/profile' => array(
                'style' => '/blocks/build/profile/style-index.css',
                'viewScript' => '/blocks/build/profile/view.js'
            ),
        );

        if (isset($block_assets[$block_name])) {
            $assets = $block_assets[$block_name];

            // Enqueue style
            if (isset($assets['style'])) {
                $style_path = plugin_dir_path(__DIR__) . $assets['style'];
                if (file_exists($style_path)) {
                    wp_enqueue_style(
                        'eb-block-' . str_replace('/', '-', $block_name),
                        plugins_url($assets['style'], __DIR__),
                        array(),
                        filemtime($style_path)
                    );
                }
            }
        }
    }

    /**
     * Load block styles in the Gutenberg editor context.
     * wp_enqueue_scripts does not fire in admin, so styles must be enqueued separately.
     */
    public function eb_enqueue_editor_block_styles()
    {
        $blocks = array(
            'courses',
            'my-courses',
            'user-account',
            'user-account-v2',
            'dashboard',
            'orders',
            'profile',
            'course-description',
        );

        foreach ($blocks as $block) {
            $style_path = plugin_dir_path(__DIR__) . "blocks/build/{$block}/style-index.css";
            if (file_exists($style_path)) {
                wp_enqueue_style(
                    'eb-block-editor-' . $block,
                    plugins_url("/blocks/build/{$block}/style-index.css", __DIR__),
                    array(),
                    filemtime($style_path)
                );
            }
        }
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
        // SECURITY FIX: Check if user is logged in first.
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => esc_html__('You must be logged in to view order details.', 'edwiser-bridge')));
        }

        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce is not active.', 'edwiser-bridge')));
        }

        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eb_order_details_nonce')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed.', 'edwiser-bridge')));
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
