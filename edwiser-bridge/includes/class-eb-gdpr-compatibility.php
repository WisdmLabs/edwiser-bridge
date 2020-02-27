<?php
/**
 * created to make plugin compatible with GDPR
 *
 * This class defines all code necessary to make plugin compatible with GDPR.
 *
 * @link       https://edwiser.org
 * @since      1.3.3
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

/**
*Class responsible to create plugin GDPR compatible
*/
class EBGDPRCompatible
{
    public function __construct()
    {
    }

    /**
     * functionality to add data to the wordpress data exporter function
     * @param  string  $email email id of the user
     * @param  integer $page  how many export data pages we have
     * @return status of the export
     */
    public function ebDataExporter($email)
    {
        $user = get_user_by("email", $email);
        $moodleUserId = get_user_meta($user->ID, "moodle_user_id", 1);
        $enrolledCourses = $this->getEnrolledCoursesWithDate($user->ID);
        $data = array(
                    array(
                      'name' => __('Course Name', "woocommerce-integration"),
                      'value' => __('Enrollment Date and Time', "woocommerce-integration")
                    )
                );
        foreach ($enrolledCourses as $value) {
            array_push($data, array(
                'name' => $value["name"],
                'value' => $value["time"]
                ));
        }
        // $page = $page;
        $export_items = array();
        if ($moodleUserId) {
            $export_items[] = array(
                'group_id' => "eb_user_meta",
                'group_label' => __("User enrollment data", "woocommerce-integration"),
                'item_id' => "eb_user_meta",
                'data' => $data,
              );

          // Tell core if we have more comments to work on still
            return array(
                'data' => $export_items,
                'done' => true,
            );
        } else {
            $export_items[] = array(
                'group_id' => "eb_user_meta",
                'group_label' => __("User enrollment data", "woocommerce-integration"),
                'item_id' => "eb_user_meta",
                'data' =>  array(
                        array(
                            'name' => __('Enrollment data', "woocommerce-integration"),
                            'value' => __("Not Available (Not linked to the Moodle LMS site)", "woocommerce-integration")
                        )
                    )
                );
            return array(
                'data' => $export_items,
                'done' => true,
            );
        }
    }


    /**
     * functionality to get list all enrolled courses
     * @param  [type] $userId [description]
     * @return [type]         [description]
     */
    public function getEnrolledCourses($userId)
    {
        global $wpdb;
        $tableName = $wpdb->prefix."moodle_enrollment";
        $query = $wpdb->prepare('SELECT `course_id` FROM '.$tableName.' WHERE user_id = %d', $userId);

        $enrolledCourse = array();
        $result = $wpdb->get_results($query);

        if (! empty($result)) {
            foreach ($result as $single_result) {
                $enrolledCourse[$single_result->course_id] = get_the_title($single_result->course_id);
            }
        }
        return $enrolledCourse;
    }


    /**
     * functionality to get list all enrolled courses
     * @param  [type] $userId [description]
     * @return [type]         [description]
     */
    public function getEnrolledCoursesWithDate($userId)
    {
        global $wpdb;
        $tableName = $wpdb->prefix."moodle_enrollment";
        $query = $wpdb->prepare('SELECT `course_id`, `time` FROM '.$tableName.' WHERE user_id = %d', $userId);

        $enrolledCourse = array();
        $result = $wpdb->get_results($query);

        if (! empty($result)) {
            foreach ($result as $single_result) {
                $enrolledCourse[$single_result->course_id] = array(
                                    "time" => $single_result->time,
                                    "name" => get_the_title($single_result->course_id)
                                );
            }
        }
        return $enrolledCourse;
    }

    /**
     * functionality to register data exporter function
     * @param  [type] $exporters [description]
     * @return [type]            [description]
     */
    public function ebRegisterMyPluginExporter($exporters)
    {
        $exporters['edwiser-bridge'] = array(
            'exporter_friendly_name' => __('Edwiser Bridge Plugin'),
            'callback' => array($this, 'ebDataExporter'),
        );
        return $exporters;
    }

    /**************  DELETE FUNCTION  ****************/

    /**
     * functionality to erase all user related data
     * @param  [type]  $email [description]
     * @param  integer $page  [description]
     * @return [type]         [description]
     */
    public function ebPluginDataEraser($email)
    {
        global $wpdb;
        // $page = $page;
        $generalSettings    = get_option('eb_general');
        $user = get_user_by("email", $email);
        $msg = array();
        $enrollMentManager = EBEnrollmentManager::instance(edwiserBridgeInstance()->getPluginName(), edwiserBridgeInstance()->getVersion());
        $enrolledCourses = $this->getEnrolledCourses($user->ID);
        $unenrolled = 0;
        if ($enrolledCourses && !empty($enrolledCourses)) {
            if (isset($generalSettings['eb_erase_moodle_data']) && $generalSettings['eb_erase_moodle_data'] == "yes") {
                /*foreach ($enrolledCourses as $key => $value) {
                    $value = $value;
                    $args = array(
                        'user_id' => $user->ID,
                        'courses' => array($key),
                        'unenroll' => 1,
                    );
                    $enrollMentManager->updateUserCourseEnrollment($args);
                    $unenrolled = 1;
                }*/

                $courseKey = array_keys($enrolledCourses);
                foreach ($courseKey as $value) {
                    $args = array(
                        'user_id' => $user->ID,
                        'courses' => array($value),
                        'unenroll' => 1,
                    );
                    $enrollMentManager->updateUserCourseEnrollment($args);
                    $unenrolled = 1;
                }
            }
            if ($unenrolled) {
                array_push($msg, __("Deleted Courses related data from the Moodle site", "eb-textdomain"));
            }

            $tableName = $wpdb->prefix."moodle_enrollment";
            $query = $wpdb->prepare('DELETE FROM '.$tableName.' WHERE user_id = %d', $user->ID);
            $wpdb->get_results($query);
            array_push($msg, __("Deleted Courses related data from the wordpress site", "eb-textdomain"));
            delete_user_meta($user->ID, "moodle_user_id");
            array_push($msg, __("Deleted Moodle user ID", "eb-textdomain"));
        }

        return array(
            'items_removed' => true,
            'items_retained' => false, // always false in this example
            'messages' => $msg, // no messages in this example
            'done' => 1,
        );
    }


    /**
     * functionality to register eraser function.
     * @param  [type] $erasers [description]
     * @return [type]          [description]
     */
    public function ebRegisterPluginEraser($erasers)
    {
        $erasers['edwiser-bridge'] = array(
            'eraser_friendly_name' => __('Edwiser Bridge Plugin'),
            'callback'             => array($this, 'ebPluginDataEraser'),
        );
        return $erasers;
    }

    /**
     * get all privacy policy related data
     * @return [type] [description]
     */
    public function ebPrivacyPolicyPageData()
    {
        $content = apply_filters("eb-privacy-policy-content", $this->ebPrivacyPolicyContent());

        if (function_exists('wp_add_privacy_policy_content')) {
            wp_add_privacy_policy_content("Edwiser Bridge", $content);
        }
    }


    /**
     * functionality to merge all the sections data which we want to show on the privacy policy page
     * @return [type] [description]
     */
    public function ebPrivacyPolicyContent()
    {
        $sections = array(__("User Account Creation", "eb-textdomain") => $this->ebUserAccountCreationPolicy());

        $sections[__("Payments", "eb-textdomain")] = $this->ebPaymentPolicy();
        $activePlugins = apply_filters('active_plugins', get_option('active_plugins'));
        if (in_array("edwiser-bridge-sso/sso.php", $activePlugins)) {
            $sections[__("User’s Simultaneous login and logout", "eb-textdomain")] = $this->ebSSOPolicy();
        }

        $sections = apply_filters("eb-policy-sections", $sections);
        $html = "<div class= 'wp-suggested-text'>
                    <div>
                        <h2>".__("Edwiser", "eb-textdomain")."</h2>
                        <p>
                            ".__("This sample language includes the basics of what personal data our site is using to integrate our site with the Moodle LMS site.", "eb-textdomain")."
                        </p>
                        <p>
                            ".__("We collect information about you and process them for the following purposes.", "eb-textdomain")."
                        </p>
                    </div>";

        foreach ($sections as $key => $value) {
            $html .= "<div>
                        <h2>
                            ".$key."
                        </h2>
                        ".$value."
                      </div>";
        }
        return $html;
    }

    /**
     * policy content of all the account creation activities
     * @return [type] [description]
     */
    public function ebUserAccountCreationPolicy()
    {
        $activePlugins = apply_filters('active_plugins', get_option('active_plugins'));
        $content = "<p>
                        ".__("We enroll the user in the course in Moodle for which we need to create an account in Moodle below are the ways by which we create users in Moodle.", "eb-textdomain")."
                    </p>
                    <p>
                        ".__("When you purchase from us through courses page, we’ll ask you to provide information including your first name, last name and email and creates username and password for the user. We’ll use this information for purposes, such as, to:", "eb-textdomain")."
                        <ul>
                            <li>".__("Create a user on the ", "eb-textdomain")."<a href = ".EB_ACCESS_URL.">".__("Moodle site", "eb-textdomain")."</a></li>
                            <li>".__("Enroll the same user into the course.", "eb-textdomain")."</li>
                        </ul>
                    </p>";

        if (in_array("woocommerce-integration/bridge-woocommerce.php", $activePlugins)) {
            $content .= "<p>
                            ".__("We collect user information whenever you submit a checkout form on woocommerce store. When you submit woocommerce checkout form, we will use following information to create the user account on the Moodle site:", "eb-textdomain")."

                            <ul>
                                <li>".__("First Name", "eb-textdomain")."</li>
                                <li>".__("Last Name", "eb-textdomain")."</li>
                                <li>".__("Email", "eb-textdomain")."</li>
                                <li>".__("Username", "eb-textdomain")."</li>
                                <li>".__("Password", "eb-textdomain")."</li>
                            </ul>
                        </p>
                        <p>
                            ".__("The collected information will be used to:", "eb-textdomain")."
                            <ul>
                                ".__("Enroll user in the specified course.", "eb-textdomain")."
                            </ul>
                        </p>";
        }

        $content = apply_filters("eb-privacy-policy-user-section", $content);
        return $content;
    }

    /**
     * payments policy data
     * @return [type] [description]
     */
    public function ebPaymentPolicy()
    {
        $content = "<p>
                        ".__("We accept payments through PayPal. When processing payments, some of your data will be passed to PayPal, including information required to process or support the payment, such as the purchase total and billing information.", "eb-textdomain")."
                    </p>
                    <p>
                        ".__("Please see the", "eb-textdomain")." <a href = 'https://www.paypal.com/us/webapps/mpp/ua/privacy-full'> ".__("PayPal Privacy Policy", "eb-textdomain")." </a> ".__("for more details.", "eb-textdomain")."
                    </p>
                    <p>
                        ".__("For more details you could read our Privacy Policy and Terms and Conditions for better understanding of our product and services.", "eb-textdomain")."
                    </p>";

        $content = apply_filters("eb-privacy-policy-payments-section", $content);
        return $content;
    }

    /**
     * sso policy data
     * @return [type] [description]
     */
    public function ebSSOPolicy()
    {
        $content = "<p>
                        We allow user to login on Wordpress as well as Moodle site simultaneously if the user is linked to the Moodle site. We use Moodle user id of the user for logging into the Moodle site and vice versa. All this login and logout actions performed using very secured encoding method in PHP which is through PHP Mcrypt extension.
                    </p>";
        $content = apply_filters("eb-privacy-policy-sso-section", $content);
        return $content;
    }
}
