<?php
if (!defined('ABSPATH')) {
    exit;
}

class EdwiserBridge_Blocks_UserAccount_API
{
    // API namespace
    private const API_NAMESPACE = 'eb/api/v1';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'eb_register_useraccount_routes'));
        // add_action('woocommerce_init', array($this, 'eb_register_useraccount_routes'));
    }

    /**
     * Register API routes.
     */
    public function eb_register_useraccount_routes()
    {
        register_rest_route(self::API_NAMESPACE, '/user-account/dashboard', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array($this, 'eb_get_dashboard'),
            'permission_callback' => '__return_true',
        ));
    }

    public function eb_get_dashboard($request)
    {
        // return rest_ensure_response(
        //     array(
        //         'function_exists' => function_exists('WC'),
        //         'woocommerce' =>  WC(),
        //     )
        // );
        if (!class_exists('WooCommerce') || !function_exists('WC')) {
            return new WP_REST_Response(['error' => 'WooCommerce is not active'], 503);
        }

        if (!WC()->session) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }

        if (!WC()->cart) {
            WC()->cart = new WC_Cart();
        }

        $cart = WC()->cart->get_cart();
        $items = [];

        foreach ($cart as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];

            $items[] = [
                'cart_item_key' => $cart_item_key,
                'product_id'    => $cart_item['product_id'],
                'name'          => $product->get_name(),
                'price'         => wc_price($product->get_price()),
                'quantity'      => $cart_item['quantity'],
                'subtotal'      => wc_price($cart_item['line_subtotal']),
                'total'         => wc_price($cart_item['line_total']),
                'image'         => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                'url'           => get_permalink($cart_item['product_id']),
            ];
        }

        return new WP_REST_Response(['cart_items' =>  $cart], 200);
    }
}

new EdwiserBridge_Blocks_UserAccount_API();
