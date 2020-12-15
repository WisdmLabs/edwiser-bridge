<?php
/**
 * Edwiser Bridge extensions page
 *
 * Referred code from woocommerce
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Eb_Admin_Extensions Class
 */
class Eb_Extensions {

	/**
	 * Handle extensions page output.
	 */
	public static function output() {
		$file_path  = plugin_dir_url( __DIR__ ) . '/admin/assets/edwiserbridge-extensions.json';
		$ext_data   = wp_remote_retrieve_body( wp_remote_get( $file_path ) );
		$extensions = json_decode( $ext_data );
		include_once 'partials/html-admin-page-extensions.php';
	}
}
