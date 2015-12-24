<?php

/**
 * The file that defines the user profile shortcode
 *
 * @link       https://edwiser.org
 * @since      1.0.2
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public/shortcodes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
class EbShortcodeUserProfile
{
    /**
     * Get the shortcode content.
     *
     * @since  1.0.2
     * @access public
     * @param array   $atts
     * @return string
     */
    public static function get($atts)
    {
        return EbShortcodes::shortcodeWrapper(array( __CLASS__, 'output' ), $atts);
    }

    /**
     * Output the shortcode.
     *
     * @since  1.0.2
     * @access public
     * @param array   $atts
     * @return void
     */
    public static function output($atts)
    {
        if (! is_user_logged_in()) {
            $template_loader = new EbTemplateLoader(EB()->get_plugin_name(), EB()->get_version());
            $template_loader->wpGetTemplate('account/form-login.php');
        } else {
            self::userProfile($atts);
        }
    }

    /**
     * User Profile page.
     *
     * @since  1.0.2
     * @access public
     * @param array   $atts
     * @return void
     */
    public static function userProfile($atts)
    {
        extract(shortcode_atts(array(
            'user_id' => isset($atts[ 'user_id' ]) ? $atts[ 'user_id' ] : '',
        ), $atts));

        if ($user_id != "") {
            $user = get_user_by('id', $user_id);
            $user_meta = get_user_meta($user_id);
        } else {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $user_meta = get_user_meta($user_id);
        }

        $user_avatar = get_avatar($user_id, 125);

        $course_args = array(
            'post_type'        => 'eb_course',
            'post_status'    => 'publish',
            'posts_per_page'=> -1,
        );

        // fetch courses
        $courses = get_posts($course_args);

        // remove course from array in which user is not enrolled
        foreach ($courses as $key => $course) {
            $has_access = EB()->enrollment_manager()->user_has_course_access($user_id, $course->ID);

            if (!$has_access) {
                unset($courses[$key]);
            }
        }
        if (is_array($courses)) {
            $courses = array_values($courses); // reset array keys
        } else {
            $courses = array();
        }

        // load profile template
        $template_loader = new EbTemplateLoader(EB()->get_plugin_name(), EB()->get_version());
        $template_loader->wpGetTemplate(
            'account/user-profile.php',
            array(
                'user_avatar' => $user_avatar,
                'user' => $user,
                'user_meta' => $user_meta,
                'enrolled_courses' => $courses
            )
        );
    }
}
