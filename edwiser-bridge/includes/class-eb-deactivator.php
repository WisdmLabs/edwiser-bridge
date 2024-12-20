<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Deactivator.
 */
class Eb_Deactivator {

	/**
	 * Short Description. (use period).
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// All deactivate dependency will come here.
	}
}
