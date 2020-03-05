<?php

namespace app\wisdmlabs\edwiserBridge;

/**
 * Edwiser Bridge extensions page
 *
 * referred code from woocommerce
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * EBAdminExtensions Class
 */
class EBAdminExtensions
{

    /**
     * handle extensions page output
     */
    public static function output()
    {
        $extensions = get_transient('edwiser_bridge_extensions_data');
        if (false === $extensions) {
            $extensions_json = wp_remote_get(
                'https://edwiser.org/edwiserbridge-extensions.json',
                array(
                'user-agent' => 'Edwiser Bridge Extensions Page'
                    )
            );

            if (!is_wp_error($extensions_json)) {
                $extensions = json_decode(wp_remote_retrieve_body($extensions_json));

                if ($extensions) {
                    set_transient('edwiser_bridge_extensions_data', $extensions, 72 * HOUR_IN_SECONDS);
                }
            }
        }

        include_once('partials/html-admin-page-extensions.php');
    }
}
