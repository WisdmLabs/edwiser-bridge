<?php

/**
 * Shortcode eb_courses.
 *
 * @link       https://edwiser.org
 * @since      1.1.3
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EbShortcodeCourses
{
    /**
     * Get the shortcode content.
     *
     * @since  1.1.3
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
     * @since  1.1.3
     *
     * @param array $atts
     */
    public static function output($atts)
    {
        extract($atts = shortcode_atts(apply_filters('eb_shortcode_courses_defaults', array(
            'categories'        => '',
            'category_operator' => 'AND',
            'order'             => 'DESC',
            'number_of_courses' => -1
        )), $atts));

        $args = array(
            'post_type' => 'eb_course',
            'posts_per_page' => $atts['number_of_courses'],
            'post_status' => 'publish'
        );

        if (!empty($atts['categories'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'eb_course_cat',
                    'field'    => 'slug',
                    'terms'    => explode(',', $atts['categories']),
                    'operator' => $atts['category_operator']
                )
            );
        }

        $courses = new \WP_Query($args);

        $template_loader = new EbTemplateLoader(
            edwiserBridgeInstance()->getPluginName(),
            edwiserBridgeInstance()->getVersion()
        );

        echo '<div class="sc-eb_courses-wrapper">';
        do_action('eb_before_course_archive');
        if ($courses->have_posts()) {
            while ($courses->have_posts()) :
                $courses->the_post();
                $template_loader->wpGetTemplatePart('content', 'eb_course');
            endwhile;
        } else {
            $template_loader->wpGetTemplatePart('content', 'none');
        }
        do_action('eb_after_course_archive');
        echo '</div>';
    }
}
