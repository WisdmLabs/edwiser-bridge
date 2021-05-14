<?php
/**
 * This class is responsible to get course progress from Moodle
 *
 * @link       https://edwiser.org
 * @since      1.4
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Course Progress.
 */
class Eb_Course_Progress {

	/**
	 * Progress.
	 */
	public function get_course_progress() {

		$user        = wp_get_current_user();
		$user_id     = $user->ID;
		$mdl_user_id = get_user_meta( $user->ID, 'moodle_user_id', true );

		if ( $mdl_user_id ) {
			$webservice_function = 'eb_get_course_progress';

			$request_data = array( 'user_id' => $mdl_user_id ); // prepare request data array.

			$response = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper(
				$webservice_function,
				$request_data
			);

			$course_progress_array = array();

			if ( isset( $response['success'] ) && $response['success'] ) {
				foreach ( $response['response_data'] as $value ) {
					$course_id                           = \app\wisdmlabs\edwiserBridge\wdm_eb_get_wp_course_id_from_moodle_course_id( $value->course_id );
					$course_progress_array[ $course_id ] = $value->completion;
				}
			}
			update_user_meta( $user_id, 'moodle_course_progress', serialize( $course_progress_array ) ); // @codingStandardsIgnoreLine
			return $course_progress_array;
		}
	}
}
