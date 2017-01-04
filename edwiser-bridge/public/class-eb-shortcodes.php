<?php

/**
 * The file that defines the shortcodes used in plugin.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EbShortcodes
{

    /**
     * Init shortcodes
     */
    public static function init()
    {
        // Define shortcodes
        $shortcodes = array(
            'eb_user_account' => __CLASS__.'::userAccount',
            'eb_user_profile' => __CLASS__.'::userProfile', //Deprecated. Use shortcode eb_user_account.
            'eb_courses' => __CLASS__.'::courses',
            'eb_course' => __CLASS__.'::course',
            'eb_my_courses' => __CLASS__.'::myCourses'
        );

        foreach ($shortcodes as $shortcode => $function) {
            add_shortcode(apply_filters("{$shortcode}_shortcode_tag", $shortcode), $function);
        }
    }

    /**
     * Shortcode Wrapper
     *
     * @since  1.0.0
     * @param mixed   $function
     * @param array   $atts     (default: array())
     * @return string
     */
    public static function shortcodeWrapper(
        $function,
        $atts = array(),
        $wrapper = array(
        'class' => '',
        'before' => null,
        'after' => null
    )
    ) {
    
        ob_start();

        $before = empty($wrapper['before']) ? '<div class="'.esc_attr($wrapper['class']).'">' : $wrapper['before'];
        $after = empty($wrapper['after']) ? '</div>' : $wrapper['after'];

        echo $before;
        call_user_func($function, $atts);
        echo $after;

        return ob_get_clean();
    }

    /**
     * user account shortcode.
     *
     * @since  1.0.0
     * @param mixed   $atts
     * @return string
     */
    public static function userAccount($atts)
    {
        return self::shortcodeWrapper(array('app\wisdmlabs\edwiserBridge\EbShortcodeUserAccount', 'output'), $atts);
    }

    /**
     * user profile shortcode, display user details & courses on one page.
     *
     * @since  1.0.2
     * @deprecated 1.2.0 Use shortcode eb_user_account
     * @param mixed   $atts
     * @return string
     */
    public static function userProfile($atts)
    {
        return self::shortcodeWrapper(array('app\wisdmlabs\edwiserBridge\EbShortcodeUserProfile', 'output'), $atts);
    }

    /**
     * courses shortcode, display courses.
     *
     * @since  1.2.0
     * @param mixed $atts
     * @return courses
     */
    public static function courses($atts)
    {
        return self::shortcodeWrapper(array('app\wisdmlabs\edwiserBridge\EbShortcodeCourses', 'output'), $atts);
    }

    /**
     * course shortcode, displays single course.
     *
     * @since  1.2.0
     * @param mixed $atts
     * @return course
     */
    public static function course($atts)
    {
        return self::shortcodeWrapper(array('app\wisdmlabs\edwiserBridge\EbShortcodeCourse', 'output'), $atts);
    }

    /**
     * eb_my_courses shortcode, shows courses belonging to a user.
     *
     * @since  1.2.0
     * @param mixed $atts
     * @return courses
     */
    public static function myCourses($atts)
    {
        return self::shortcodeWrapper(array('app\wisdmlabs\edwiserBridge\EbShortcodeMyCourses', 'output'), $atts);
    }
}
