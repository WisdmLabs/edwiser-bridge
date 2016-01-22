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
    public static function output($atts)
    {
        if (!is_user_logged_in()) {
            $template_loader = new EbTemplateLoader(edwiserBridgeInstance()->getPluginName(), edwiserBridgeInstance()->getVersion());
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
        unset($atts);
        $user_id = get_current_user_id();
        $user_orders = array(); // users completed orders
        $order_count = 15;

        // get all completed orders of a user
        $args = array(
            'posts_per_page' => -1,
            'meta_key' => '',
            'post_type' => 'eb_order',
            'post_status' => 'publish',
            'fields' => 'ids',
            'order' => 'ASC',

        );
        $overall_orders = get_posts($args); // get all orders from db

        foreach ($overall_orders as $order_id) {
            $order_detail = get_post_meta($order_id, 'eb_order_options', true);

            // return if there is a problem in order details
            /*if (!isset($order_detail['order_status']) ||
                !isset($order_detail['buyer_id']) ||
                !isset($order_detail['course_id'])) {
                return;
            }*/

            if (!empty($order_detail) && $order_detail['order_status'] == 'completed'
                && $order_detail['buyer_id'] == $user_id) {
                $user_orders[] = array(
                    'order_id' => $order_id,
                    'ordered_item' => $order_detail['course_id'],
                    'billing_email' => isset($order_detail['billing_email']) ? $order_detail['billing_email'] : '-',
                    'currency' => isset($order_detail['currency']) ? $order_detail['currency'] : '$',
                    'amount_paid' => isset($order_detail['amount_paid']) ? $order_detail['amount_paid'] : '',
                );
            }
        }

        $template_loader = new EbTemplateLoader(edwiserBridgeInstance()->getPluginName(), edwiserBridgeInstance()->getVersion());

        $template_loader->wpGetTemplate(
            'account/user-account.php',
            array(
                'current_user' => get_user_by('id', get_current_user_id()),
                'user_orders' => $user_orders,
                'order_count' => $order_count,
            )
        );
    }
}
