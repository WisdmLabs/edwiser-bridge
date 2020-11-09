<?php
/**
 * This class is responsible to get course progress from Moodle
 *
 * @link       https://edwiser.org
 * @since      1.4
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

/**
*
*/
class Eb_Course_Progress
{
	public function __construct()
	{
	}


	public function get_course_progress()
	{
		// $settings = get_option("eb_general");
		// if (isset($settings["eb_my_courses_page_id"]) && !empty($settings["eb_my_courses_page_id"]) && isset($settings["eb_useraccount_page_id"]) && !empty($settings["eb_useraccount_page_id"])) {
			// if (is_page($settings["eb_my_courses_page_id"]) || is_page($settings["eb_useraccount_page_id"])) {
		$user= wp_get_current_user();
		$user_id = $user->ID;
		$mdl_user_id = get_user_meta($user->ID, "moodle_user_id", true);

		if ($mdl_user_id) {
			$webservice_function = "eb_get_course_progress";

			$request_data = array('user_id' => $mdl_user_id); // prepare request data array

			$response = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper(
				$webservice_function,
				$request_data
			);


			$course_progress_array = array();

			if (isset($response["success"]) && $response["success"]) {
				foreach ($response["response_data"] as $value) {
					$courseId = get_wp_course_id_from_moodle_course_id($value->course_id);
					$course_progress_array[$courseId] = $value->completion;
				}
			}
			update_user_meta($user_id, "moodle_course_progress", serialize($course_progress_array));
			return $course_progress_array;
		}
			// }
		// }
	}
}
