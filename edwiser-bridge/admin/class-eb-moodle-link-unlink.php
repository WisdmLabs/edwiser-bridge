<?php
/**
 * Link Unlink user.
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
 * Class which will handle linking and unlinking of the WordPress users with moodle on the toggle switch provided on users page
 */
class Eb_Moodle_Link_Unlink {
	/**
	 * Calling function to add column and its content on filter
	 */
	public function __construct() {
		add_filter( 'manage_users_columns', array( $this, 'adding_moodle_account_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'show_content' ), 10, 3 );
	}

	/**
	 * Adding Moodle account column in users page.
	 *
	 * @param  [array] $column array of all column from users.php page.
	 * @return [array]         returning array by adding our column in it
	 */
	public function adding_moodle_account_column( $column ) {
		$column['moodle_Account'] = sprintf( __( 'Moodle Account', 'edwiser-bridge' ) );
		return $column;
	}

	/**
	 * Adding toggle switch in the moodle account column.
	 *
	 * @param  [string]  $val         value which will be added in column.
	 * @param  [array]   $column_name  array of all column names.
	 * @param  [integer] $user_id     id of the user.
	 * @return [string]              returning data needed to add in column.
	 */
	public function show_content( $val, $column_name, $user_id ) {
		$link      = get_user_meta( $user_id, 'moodle_user_id', true );
		$checked   = 'block';
		$unchecked = 'none';
		if ( trim( $link ) === '' ) {
			$checked   = 'none';
			$unchecked = 'block';
		}
		if ( 'moodle_Account' === $column_name ) {
			$val = '<div id="' . $user_id . '" class="wdm-wpcwn-type">
				<label class="link-unlink" id="' . $user_id . '-link" title="Link user with Moodle account" class="link-unlink link" style="display:' . $unchecked . ';">' . sprintf( esc_html__( 'Link User', 'edwiser-bridge' ) ) . '</label>
				<label class="link-unlink" id="' . $user_id . '-unlink" title="Unlink user with moodle account." class="link-unlink unlink" style="display:' . $checked . ';">' . sprintf( esc_html__( 'Unlink User', 'edwiser-bridge' ) ) . '</label>
				</div>
			';
		}
		return $val;
	}
}

new Eb_Moodle_Link_Unlink();
