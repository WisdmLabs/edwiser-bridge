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
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Eb_Admin_Extensions Class
 */
class Eb_Admin_Extensions {

	/**
	 * Handle extensions page output.
	 */
	public static function output() {
		$file_path  = plugin_dir_path( __DIR__ ) . '/admin/assets/edwiserbridge-extensions.json';
		$extensions = json_decode( file_get_contents( $file_path ) );
		include_once( 'partials/html-admin-page-extensions.php' );
	}
}
