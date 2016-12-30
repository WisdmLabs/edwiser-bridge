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

        public function outPut($args, $tmplContent)
        {
            $tmplConst = $this->getTmplConstant($args);
            foreach ($tmplConst as $const => $val) {
                $tmplContent = str_replace($const, $val, $tmplContent);
            }
            return apply_filters("eb_emailtmpl_content", $tmplContent);
        }

        private function getTmplConstant($args)
        {
            $constant = array();
            if (is_user_logged_in()) {
                $curUser = wp_get_current_user();
                $constant["{USER_NAME}"] = $curUser->user_login;
                $constant["{FIRST_NAME}"] = $curUser->first_name;
                $constant["{LAST_NAME}"] = $curUser->last_name;
            } else {
                $constant["{USER_NAME}"] = $args['username'];
                $constant["{FIRST_NAME}"] = $args['first_name'];
                $constant["{LAST_NAME}"] = $args['last_name'];
            }

            $constant["{SITE_NAME}"] = get_bloginfo("name");
            $constant["{SITE_URL}"] = "<a href='".get_bloginfo("url")."'> Site</a>";
            $constant["{COURSES_PAGE_LINK}"] = "<a href='".site_url('/courses')."'> Courses</a>";
            $constant["{USER_ACCOUNT_PAGE_LINK}"] = "<a href='".wdmUserAccountUrl()."'> User Account</a>";
            $constant["{WP_LOGIN_PAGE_LINK}"] = "<a href='".wp_login_url()."'> Login Page</a>";
            $constant["{MOODLE_URL}"] = "<a href='".$this->getMoodleURL()."'> Moodle Site</a>";
            $constant["{COURSE_NAME}"] = $this->getCourseName($args);
            $constant["{USER_PASSWORD}"] = $this->getUserPassword($args);
            $constant["{ORDER_ID}"] = $this->getOrderID($args);
            $constant["{WP_COURSE_PAGE_LINK}"] = $this->getCoursePageLink($args);

            return apply_filters("eb_emailtmpl_constants_values", $constant);
        }

        private function getCoursePageLink($args)
        {

            if (isset($args['course_id'])) {
                return "<a href='".get_post_permalink($args['course_id'])."'>click here</a>";
            } else {
                $url = get_site_url();
                return "<a href='".$url."'>Click here</a>";
            }
        }

        private function getMoodleURL()
        {
            $url = get_option("eb_connection");
            if ($url) {
                return $url["eb_url"];
            }
            return "MOODLE_URL";
        }

        private function getCourseName($args)
        {
            if (isset($args["course_id"])) {
                return get_the_title($args['course_id']);
            }
            return "COURSE_NAME";
        }

        private function getUserPassword($args)
        {
            if (isset($args["password"])) {
                return $args["password"];
            }
            return "USER_PASSWORD";
        }

        private function getOrderID($args)
        {
            if (isset($args["order_id"])) {
                return $args["order_id"];
            }
            return "ORDER ID";
        }
    }

}
