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
*
*/
class EBGDPRCompatible
{
    public function __construct()
    {
    }

    public function ebDataExporter($email, $page = 1)
    {
        $user = get_user_by("email", $email);
        $moodleUserId = get_user_meta($user->ID, "moodle_user_id", 1);
        $enrolledCourses = $this->getEnrolledCourses($user->ID);
        $data = array(
                    array(
                      'name' => __('Moodle User ID', "woocommerce-integration"),
                      'value' => $moodleUserId
                    )
                );
        foreach ($enrolledCourses as $key => $value) {
            array_push($data, array(
                'name' => __("Moodle Course ID", "woocommerce-integration"),
                'value' => $key
                ));

            array_push($data, array(
                'name' => __("Moodle Course Name", "woocommerce-integration"),
                'value' => $value
                ));
        }
        $page =$page;
        $export_items = array();
        if ($moodleUserId) {
            $export_items[] = array(
                'group_id' => "eb_user_meta",
                'group_label' => __("Edwiser and Extensions Meta", "woocommerce-integration"),
                'item_id' => "eb_user_meta",
                'data' => $data,
              );

          // Tell core if we have more comments to work on still
            return array(
                'data' => $export_items,
                'done' => true,
            );
        } else {
            return array(
                'data' => array(
                'group_id' => "eb_user_meta",
                'group_label' => __("Edwiser and Extensions Meta", "woocommerce-integration"),
                'item_id' => "eb_user_meta",

                    array(
                      'name' => __('Moodle User ID', "woocommerce-integration"),
                      'value' => __("Not Available (Not linked to the Moodle LMS site)", "woocommerce-integration")
                    )
                ),
                'done' => true,
            );
        }
    }


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


    public function ebRegisterMyPluginExporter($exporters)
    {
        $exporters['my-plugin-slug'] = array(
            'exporter_friendly_name' => __('Edwiser Bridge Plugin'),
            'callback' => array($this, 'ebDataExporter'),
        );
        return $exporters;
    }



    /**************  DELETE FUNCTION  ****************/


    public function ebPluginDataEraser($email, $page = 1)
    {
        global $wpdb;
        $page = $page;
        $generalSettings    = get_option('eb_general');
        $user = get_user_by("email", $email);
        $msg = array();
        $enrollMentManager = EBEnrollmentManager::instance(edwiserBridgeInstance()->getPluginName(), edwiserBridgeInstance()->getVersion());
        $enrolledCourses = $this->getEnrolledCourses($user->ID);

        if ($enrolledCourses && !empty($enrolledCourses)) {
            if (isset($generalSettings['eb_erase_moodle_data']) && $generalSettings['eb_erase_moodle_data'] == "yes") {
                foreach ($enrolledCourses as $key => $value) {
                    $value = $value;
                    $args = array(
                        'user_id' => $user->ID,
                        'courses' => array($key),
                        'unenroll' => 1,
                    );
                    $enrollMentManager->updateUserCourseEnrollment($args);
                    array_push($msg, __("Edwiser Bridge : Unenrolled user from courses", "eb-textdomain"));
                }
            }

            $tableName = $wpdb->prefix."moodle_enrollment";
            $query = $wpdb->prepare('DELETE FROM '.$tableName.' WHERE user_id = %d', $user->ID);
            $wpdb->get_results($query);
            array_push($msg, __("Edwiser Bridge : Deleted Courses related data from the wordpress site", "eb-textdomain"));
            delete_user_meta($user->ID, "moodle_user_id");
            array_push($msg, __("Edwiser Bridge : Deleted moodle user ID", "eb-textdomain"));
        }

        return array(
            'items_removed' => true,
            'items_retained' => false, // always false in this example
            'messages' => $msg, // no messages in this example
            'done' => 1,
        );
    }



    public function ebRegisterPluginEraser($erasers)
    {
        $erasers['my-plugin-slug'] = array(
            'eraser_friendly_name' => __('Edwiser Bridge Plugin'),
            'callback'             => array($this, 'ebPluginDataEraser'),
        );
        return $erasers;
    }


    public function ebPrivacyPolicyPageData()
    {
        $content = apply_filters("eb-privacy-policy-content", $this->ebPrivacyPolicyContent());
        /*if (in_array("woocommerce-integration/bridge-woocommerce.php", $active_plugins)) {
        }*/

        if (function_exists('wp_add_privacy_policy_content')) {
            wp_add_privacy_policy_content("Edwiser Bridge", $content);
        }
    }



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
                            ".__("This sample language includes the basics around what personal data our site is using to integrate our site with the Moodle LMS site.", "eb-textdomain")."
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

    public function ebUserAccountCreationPolicy()
    {
        $activePlugins = apply_filters('active_plugins', get_option('active_plugins'));
        $content = "<p>
                        ".__("We enroll user into the course in Moodle for which we need to create account in Moodle below are the ways by which we create users in moodle.", "eb-textdomain")."
                    </p>
                    <p>
                        ".__("When you purchase from us through courses page, we’ll ask you to provide information including your first name, last name and email and creates username and password for the user. We’ll use this information for purposes, such as, to:", "eb-textdomain")."
                        <ul>
                            <li>".__("Create user on the ", "eb-textdomain")."<a href = ".EB_ACCESS_URL.">".__("Moodle site", "eb-textdomain")."</a></li>
                            <li>".__("Enroll same user into the course.", "eb-textdomain")."</li>
                        </ul>
                    </p>";

        if (in_array("woocommerce-integration/bridge-woocommerce.php", $activePlugins)) {
            $content .= "<p>
                            ".__("We collect user information whenever you submit an checkout form on woocommerce store. When you submit woocommerce checkout form, we will use following information to create the user account on the moodle site(this should be the link of the moodle site, get the connection url).:", "eb-textdomain")."

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


    public function ebSSOPolicy()
    {
        $content = "<p>
                        We allow user to login on Wordpress as well as Moodle site simultaneously if the user is linked to the Moodle site. We use Moodle user id of the user for logging into the Moodle site and vice versa. All this login and logout actions performed using very secured encoding method in PHP which is through PHP Mcrypt extension.
                    </p>";
        $content = apply_filters("eb-privacy-policy-sso-section", $content);
        return $content;
    }


/*    public function ebebPrivacyPolicyContent()
    {

        $html = "<div>
                    <div>
                        <h2>Edwiser</h2>
                        <p>
                            Edwiser Bridge or any of its extensions does not capture any data or information from your site.
                            All the data collection and data processing takes place on your server and no information or data ever goes out of your site to Edwiser or any of its associated sites.
                        </p>
                    </div>
                    <div>
                        <h2>User Account Creation</h2>
                        <p>
                            Whenever a user purchases course in WordPress and completes the payment through PayPal or any payment gateway.
                            Once the payment gets credited, the user account gets created in WordPress and subsequent user account gets created in Moodle.
                        </p>
                        <p>
                            Example - A user purchases course from “ bridge.edwiser.org “ and his/her payment gets credited then their user account gets created in “ bridge.edwiser.org/moodle “
                        </p>
                        <p>
                            The user related data that is needed to create these user accounts gets stored in your WordPress & Moodle sites respectively.
                        </p>
                    </div>
                    <div>
                        <h2>Payments</h2>
                        <p>
                            In this subsection you should list which third party payment processors you’re using to take payments on your store since these may handle customer data. We’ve included PayPal as an example, but you should remove this if you’re not using PayPal.
                        </p>
                        <p>
                            We accept payments through PayPal. When processing payments, some of your data will be passed to PayPal, including information required to process or support the payment, such as the purchase total and billing information.
                        </p>
                        <p>
                            Please see the <a href = 'https://www.paypal.com/us/webapps/mpp/ua/privacy-full'> PayPal Privacy Policy </a> for more details.
                        </p>
                        <p>
                            For more details you could read our Privacy Policy and Terms and Conditions for better understanding of our product and services.
                        </p>
                    </div>
                </div>";
        return $html;
    }*/
}
