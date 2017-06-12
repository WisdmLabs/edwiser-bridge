<?php

namespace app\wisdmlabs\edwiserBridge;

/**
 * Edwiser Bridge Email template parser
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists("EBEmailTmplParser")) {
    class EBEmailTmplParser
    {
        /**
         * Provides the functionality to parse the email temaplte raw content
         *
         * Provides the filters to parse the template content.
         *
         * @param array $args default arguments to replace the email template constants
         * @param HTML $tmplContent html content to prepare the email content
         *
         * @return html returns the email template content
         */
        public function outPut($args, $tmplContent)
        {
            $tmplContent = apply_filters("eb_emailtmpl_content_before", array("args" => $args, "content" => $tmplContent));
            $args = $tmplContent['args'];
            $tmplContent = $tmplContent['content'];
            $tmplConst = $this->getTmplConstant($args);
            foreach ($tmplConst as $const => $val) {
                $tmplContent = str_replace($const, $val, $tmplContent);
            }
            $tmplContent = apply_filters("eb_emailtmpl_content", array("args" => $args, "content" => $tmplContent));
            $args = $tmplContent['args'];
            $tmplContent = $tmplContent['content'];
            return $tmplContent;
        }

        /**
         * Provides the functionality to get the values for the email temaplte constants
         * @param array $args array of the default values for the constants to
         * prepare the email template contetn
         * @return array returns the array of the email temaplte constants with
         * associated values for the constants
         */
        private function getTmplConstant($args)
        {
            $constant = array();
            if (isset($args['username']) && $args['first_name'] && $args['last_name']) {
                $constant["{USER_NAME}"] = $args['username'];
                $constant["{FIRST_NAME}"] = $args['first_name'];
                $constant["{LAST_NAME}"] = $args['last_name'];
            } elseif (is_user_logged_in()) {
                $curUser = wp_get_current_user();
                $constant["{USER_NAME}"] = $curUser->user_login;
                $constant["{FIRST_NAME}"] = $curUser->first_name;
                $constant["{LAST_NAME}"] = $curUser->last_name;
            }
            $constant["{SITE_NAME}"] = get_bloginfo("name");
            $constant["{SITE_URL}"] = "<a href='".get_bloginfo("url")."'>".  get_bloginfo("name")."</a>";
            $constant["{COURSES_PAGE_LINK}"] = "<a href='".site_url('/courses')."'>".__('Courses', 'eb-textdomain')."</a>";
            $constant["{MY_COURSES_PAGE_LINK}"] = $this->getMyCoursesPageLink();
            $constant["{USER_ACCOUNT_PAGE_LINK}"] = "<a href='".wdmUserAccountUrl()."'>".__('User Account', 'eb-textdomain')."</a>";
            $constant["{WP_LOGIN_PAGE_LINK}"] = "<a href='".$this->getLoginPageUrl()."'>".__('Login Page', 'eb-textdomain')."</a>";
            $constant["{MOODLE_URL}"] = "<a href='".$this->getMoodleURL()."'>".__('Moodle Site', 'eb-textdomain')."</a>";
            $constant["{COURSE_NAME}"] = $this->getCourseName($args);
            $constant["{USER_PASSWORD}"] = $this->getUserPassword($args);
            $constant["{ORDER_ID}"] = $this->getOrderID($args);
            $constant["{WP_COURSE_PAGE_LINK}"] = $this->getCoursePageLink($args);
            return apply_filters("eb_emailtmpl_constants_values", $constant);
        }

        /**
         * Prvides the functionality to ge tthe mycourses page link
         * @return link returns the mycourses page (set in the EB settings) link
         */
        private function getMyCoursesPageLink()
        {
            $genralSettings = get_option("eb_general");
            $myCoursesPageId = $genralSettings["eb_my_courses_page_id"];
            $url=get_permalink($myCoursesPageId);
            return "<a href='$url'>".__('My Courses', 'eb-textdomain')."</a>";
        }

        /**
         * Provides the login page link
         * @return link rerutns the link for the login page(set in the EB settings) url
         */
        private function getLoginPageUrl()
        {
            $genralSettings = get_option("eb_general");
            $accountPageId = $genralSettings["eb_useraccount_page_id"];
            return get_permalink($accountPageId);
        }

        /**
         * Provides the functionality to get the course page link
         * @param array $args accepts the email tempalte argumaent to prepare the email template
         * @return link returns the link for the emal single course page link
         */
        private function getCoursePageLink($args)
        {
            if (isset($args['course_id'])) {
                return "<a href='" . get_post_permalink($args['course_id']) . "'>" . __('click here', 'eb-textdomain') . "</a>";
            } else {
                $url = get_site_url();
                return "<a href='" . $url . "'>" . __('Click here', 'eb-textdomain') . "</a>";
            }
        }

        /**
         * Provides the functionality to get the moodle site link
         * @return linl returns the link to the moodle site.
         */
        private function getMoodleURL()
        {
            $url = get_option("eb_connection");
            if ($url) {
                return $url["eb_url"];
            }
            return "MOODLE_URL";
        }

        /**
         * Provides the functionality to ge tthe course name using course id
         * @param array $args  array of default email page arguments
         * @return string returns the course name
         */
        private function getCourseName($args)
        {
            if (isset($args["course_id"])) {
                return get_the_title($args['course_id']);
            }
            return "COURSE_NAME";
        }

        /**
         * Provides the functionality to ge tthe user accounts password
         * @param array $args  array of default email page arguments
         * @return string returns the account password
         */
        private function getUserPassword($args)
        {
            if (isset($args["password"])) {
                return $args["password"];
            }
            return "USER_PASSWORD";
        }

        /**
         * Provides the functionality to get the order id
         * array $args  array of default email page arguments
         * @return string returns the order id
         */
        private function getOrderID($args)
        {
            if (isset($args["order_id"])) {
                return $args["order_id"];
            }
            return "ORDER ID";
        }
    }
}
