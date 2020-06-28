<?php
/**
 * This class works as a connection helper to connect with Moodle webservice API.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EBExternalApiEndpoint
{
    public function __construct()
    {
    }

    /**
     * This method registers the webservice endpointr
     * @return [type] [description]
     */
    public function apiRegistration()
    {
        register_rest_route(
            'edwiser-bridge',
            '/wisdmlabs/',
            array(
                'methods' => 'POST',
                'callback' => array($this, "externalApiEndpointDef"),
            )
        );
    }


    /**
     * this function parse the request coming from
     * @param  [type] $data request Data
     * @return [type]       error or success message
     */
    public function externalApiEndpointDef($data)
    {
        $data = stripcslashes($_POST["data"]);
        $data = unserialize($data);
        if (isset($_POST["action"]) && !empty($_POST["action"])) {
            switch ($_POST["action"]) {
                case 'test_connection':
                    $responseData = $this->ebTestConnection($data);
                    break;

                case 'course_enrollment':
                    $responseData = $this->ebCourseEnrollment($data, 0);
                    break;

                case 'course_un_enrollment':
                    $responseData = $this->ebCourseEnrollment($data, 1);
                    break;

                case 'user_creation':
                    $responseData = $this->ebTriggerUserCreation($data);
                    break;

                case 'user_deletion':
                    $responseData = $this->ebTriggerUserDelete($data);
                    break;

                default:
                    break;
            }
        }
        return $responseData;
    }


    /**
     * function to test connection for the request from Moodle
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    protected function ebTestConnection($data)
    {
        $status = 0;
        $msg = "Invalid token please check token";


        if (isset($data["token"])) {
            $settings = maybe_unserialize(get_option("eb_connection"));
            if (!isset($settings["eb_access_token"])) {
                $msg = "Please save connection settings on Worpdress";
            } elseif ($settings["eb_access_token"] == $data["token"]) {
                $msg = "Test connection successful";
                $status = 1;
            }
        }
        return array("status" => $status, "msg" => $msg);
    }


    /**
     * Function to enroll or unenroll from the course for the request coming from Moodle
     * @param  [type] $data     [description]
     * @param  [type] $unEnroll [description]
     * @return [type]           [description]
     */
    protected function ebCourseEnrollment($data, $unEnroll)
    {

        if (isset($data["user_id"]) && isset($data["course_id"])) {
            $mdlCourseId = $data["course_id"];
            $mdlCourseId =$mdlCourseId;
            $wpCourseId = getWpCourseIdFromMoodleCourseId($data["course_id"]);

            if ($wpCourseId) {
                $mdlUserId = $data["user_id"];
                $wpUserId = getWpUserIdFromMoodleId($data["user_id"]);
                if (!$wpUserId && empty($wpUserId) && $unEnroll == 0) {
                    $role = defaultRegistrationRole();
                    $wpUserId = $this->createOnlyWpUser($data['user_name'], $data['email'], $data['first_name'], $data['last_name'], $role);
                    update_user_meta($wpUserId, "moodle_user_id", $mdlUserId);
                }


                if ($wpUserId) {
                    $user = get_user_by("ID", $wpUserId);

                    $args = array(
                        'user_id'  => $wpUserId,
                        'role_id'  => 5,
                        'courses'  => array($wpCourseId),
                        'unenroll' => $unEnroll,
                        'suspend'  => 0,
                    );

                    //check if there any pending enrollments for the given course then don't enroll user.
                    $user_enrollment_meta = get_user_meta($wpUserId, 'eb_pending_enrollment', 1);

                    if (in_array($wpCourseId, $user_enrollment_meta)) {
                        return;
                    }


                    $args['complete_unenroll'] = 0;
                    if ($unEnroll) {
                        $args['complete_unenroll'] = 1;
                    }

                    edwiserBridgeInstance()->enrollmentManager()->updateEnrollmentRecordWordpress($args);

                    $args = array(
                        'user_email' => $user->user_email,
                        'username'   => $user->user_login,
                        'first_name' => $user->first_name,
                        'last_name'  => $user->last_name,
                        'course_id'=> $wpCourseId
                    );
                    if ($unEnroll) {
                        do_action("eb_mdl_un_enrollment_trigger", $args);
                    } else {
                        do_action('eb_mdl_enrollment_trigger', $args);
                    }
                }
            }
        }
    }



    /**
     * function to create user for the user creation request coming from Moodle
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function ebTriggerUserCreation($data)
    {
        if (isset($data['user_name']) && isset($data['email'])) {
            $role = defaultRegistrationRole();
            $wpUserId = $this->createOnlyWpUser($data["user_name"], $data["email"], $data["first_name"], $data['last_name'], $role);
            if ($wpUserId) {
                update_user_meta($wpUserId, "moodle_user_id", $data["user_id"]);
            }
        }
    }


    /**
     * function to delete user for the user deletion request coming from Moodle
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function ebTriggerUserDelete($data)
    {
        require_once(ABSPATH.'wp-admin/includes/user.php');
        if (isset($data["user_id"])) {
            $wpUserId = getWpUserIdFromMoodleId($data["user_id"]);
            if ($wpUserId) {
                $user = get_user_by("ID", $wpUserId);
                $args = array(
                        'user_email' => $user->user_email,
                        'username'   => $user->user_login,
                        'first_name' => $user->first_name,
                        'last_name'  => $user->last_name
                    );
                $deleted = wp_delete_user($wpUserId);
                if ($deleted) {
                    do_action("eb_mdl_user_deletion_trigger", $args);
                }
            }
        }
    }



    /**
     * functinality to create only wordpress user.
     * @param  [type] $username  username
     * @param  [type] $email     email
     * @param  [type] $firstname firstname
     * @param  [type] $lastname  lastname
     * @param  string $role      default role
     * @return [type]            success or error message
     */
    public function createOnlyWpUser($username, $email, $firstname, $lastname, $role = "")
    {
        if (email_exists($email)) {
            return new \WP_Error(
                'registration-error',
                __('An account is already registered with your email address. Please login.', 'eb-textdomain'),
                'eb_email_exists'
            );
        }

        // Ensure username is unique
        $append = 1;
        $o_username = $username;

        while (username_exists($username)) {
            $username = $o_username.$append;
            ++$append;
        }

        // Handle password creation
        $password = wp_generate_password();
        // WP Validation
        $validation_errors = new \WP_Error();

        if ($validation_errors->get_error_code()) {
            return $validation_errors;
        }

        //Added after 1.3.4
        if ($role == "") {
            $role = get_option("default_role");
        }


        $wp_user_data = apply_filters(
            'eb_new_user_data',
            array(
                'user_login' => $username,
                'user_pass' => $password,
                'user_email' => $email,
                'role' => $role,
            )
        );

        $user_id = wp_insert_user($wp_user_data);

        if (is_wp_error($user_id)) {
            return new \WP_Error(
                'registration-error',
                '<strong>'.__('ERROR', 'eb-textdomain').'</strong>: '.
                    __(
                        'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.',
                        'eb-textdomain'
                    )
            );
        }

        //update firstname, lastname
        update_user_meta($user_id, 'first_name', $firstname);
        update_user_meta($user_id, 'last_name', $lastname);

        $args = array(
            'user_email' => $email,
            'username' => $username,
            'first_name' => $firstname,
            'last_name' => $lastname,
            'password' => $password,
        );
        do_action('eb_created_user', $args);
        return $user_id;
    }
}
