<?php
/**
 * REST authentication helper for Edwiser Bridge Blocks APIs.
 *
 * @package    Edwiser Bridge
 * @subpackage API
 * @since      4.3.5
 */

namespace app\wisdmlabs\edwiserBridge;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Eb_Rest_Auth_Helper
 *
 * Decides whether the REST nonce check may be relaxed for a request.
 *
 * The check is deliberately strict. It never looks at $_SERVER['REQUEST_URI'], because that
 * contains the query string, which an attacker controls. Instead it uses the route WordPress
 * itself will dispatch, and compares it to an exact list of read-only routes.
 *
 * @since 4.3.5
 */
class Eb_Rest_Auth_Helper
{
    /**
     * Whether this request is a plain read-only (GET) request that WordPress will
     * dispatch to one of the given routes.
     *
     * @param string[] $allowed_routes Exact REST routes (e.g. '/eb/api/v1/my-courses').
     * @return bool
     */
    public static function is_read_only_request_to(array $allowed_routes)
    {
        // The transport method must be GET.
        $transport_method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD']))) : '';
        if ('GET' !== $transport_method) {
            return false;
        }

        // WP_REST_Server lets the _method parameter and the X-HTTP-Method-Override header replace
        // the transport method when it dispatches. Never relax auth if either is present.
        if (isset($_GET['_method']) || isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return false;
        }

        $route = self::get_dispatched_route();
        if ('' === $route) {
            return false;
        }

        // Exact, case-sensitive match. Anything unexpected fails closed.
        return in_array($route, $allowed_routes, true);
    }

    /**
     * Get the route that WordPress will dispatch for this REST request.
     *
     * This is the same value rest_api_loaded() passes to the REST server, so it matches what is
     * actually dispatched and is not affected by extra query parameters.
     *
     * @return string Route, or an empty string if it can't be determined.
     */
    private static function get_dispatched_route()
    {
        global $wp;

        if (!isset($wp) || !is_object($wp) || empty($wp->query_vars['rest_route']) || !is_string($wp->query_vars['rest_route'])) {
            return '';
        }

        return untrailingslashit($wp->query_vars['rest_route']);
    }
}
