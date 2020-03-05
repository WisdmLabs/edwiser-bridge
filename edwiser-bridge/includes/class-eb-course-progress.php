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
class EbCourseProgress
{
    public function __construct()
    {
    }


    public function getCourseProgress()
    {
        // $settings = get_option("eb_general");
        // if (isset($settings["eb_my_courses_page_id"]) && !empty($settings["eb_my_courses_page_id"]) && isset($settings["eb_useraccount_page_id"]) && !empty($settings["eb_useraccount_page_id"])) {
            // if (is_page($settings["eb_my_courses_page_id"]) || is_page($settings["eb_useraccount_page_id"])) {
        $user= wp_get_current_user();
        $userId = $user->ID;
        $mdlUserId = get_user_meta($user->ID, "moodle_user_id", true);

        if ($mdlUserId) {
            $webservice_function = "eb_get_course_progress";

            $request_data = array('user_id' => $mdlUserId); // prepare request data array

            $response = edwiserBridgeInstance()->connectionHelper()->connectMoodleWithArgsHelper(
                $webservice_function,
                $request_data
            );


            $courseProgressArray = array();

            if (isset($response["success"]) && $response["success"]) {
                foreach ($response["response_data"] as $value) {
                    $courseId = getWpCourseIdFromMoodleCourseId($value->course_id);
                    $courseProgressArray[$courseId] = $value->completion;
                }
            }
            update_user_meta($userId, "moodle_course_progress", serialize($courseProgressArray));
            return $courseProgressArray;
        }
            // }
        // }
    }
}
