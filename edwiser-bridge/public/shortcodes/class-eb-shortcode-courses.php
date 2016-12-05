<?php

/**
 * The file that defines the user profile shortcode.
 *
 * @link       https://edwiser.org
 * @since      1.0.2
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EbShortcodeCourses
{
    /**
     * Get the shortcode content.
     *
     * @since  1.0.2
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
     * @since  1.0.2
     *
     * @param array $atts
     */
    public static function output($atts)
    {
        extract($atts = shortcode_atts(apply_filters('eb_output_courses_defaults', array(
            'posts_per_page'  => -1,
            'order'           => 'DESC',
            'post_status'     => 'publish',
            'categories'      => ''
        )), $atts));
        
        $atts['post_type'] = 'eb_course';

        if (!empty($atts['categories'])) {
            $atts['tax_query'] = array(
                array(
                    'taxonomy' => 'eb_course_cat',
                    'field'    => 'slug',
                    'terms'    => explode(',', $atts['categories']),
                    'operator' => 'AND'
                )
            );
        }
        
        unset($atts['categories']);

        $courses = new \WP_Query($atts);

        $template_loader = new EbTemplateLoader(edwiserBridgeInstance()->getPluginName(), edwiserBridgeInstance()->getVersion());
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
