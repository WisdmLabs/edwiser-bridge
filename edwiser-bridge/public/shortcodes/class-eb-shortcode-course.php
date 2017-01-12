<?php

/**
 * Shortcode eb_course
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EbShortcodeCourse
{

    /**
     * Get the shortcode content.
     *
     * @since  1.2.0
     *
     * @param array $atts
     *
     * @return string
     */
    public static function get($atts)
    {
        return EbShortcodes::shortcodeWrapper(array(__CLASS__, 'output'), $atts);
    }

    /**
     * Output the shortcode.
     *
     * @since  1.2.0
     *
     * @param array $atts
     */
    public static function output($atts)
    {
        extract($atts = shortcode_atts(apply_filters('eb_output_course_defaults', array(
            'id' => ''
                )), $atts));

        //Course id required.
        if (!isset($atts['id']) || !is_numeric($atts['id'])) {
            return;
        }

        $atts['post_type'] = 'eb_course';
        $atts['post_status'] = 'publish';
        $atts['p'] = $atts['id'];

        $courses = new \WP_Query($atts);

        //Course not found.
        if ($courses->post_count !== 1) {
            return;
        }

        //Show single course.
        do_action('eb_before_single_course');
        while ($courses->have_posts()) :
            $courses->the_post();

            $template_loader = new EbTemplateLoader(
                edwiserBridgeInstance()->getPluginName(),
                edwiserBridgeInstance()->getVersion()
            );
            $template_loader->wpGetTemplatePart('content-single', 'eb_course');

            $courses->comments_template();
        endwhile;
        do_action('eb_after_single_course');
    }
}
