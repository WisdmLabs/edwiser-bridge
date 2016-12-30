<?php
/**
 * Shortcode eb_courses.
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EbShortcodeCourses
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
        extract($atts = shortcode_atts(apply_filters('eb_shortcode_courses_defaults', array(
            'categories' => '',
            'category_operator' => 'AND',
            'order' => 'DESC',
            'per_page' => 12
                )), $atts));

        $args = array(
            'post_type' => 'eb_course',
            'posts_per_page' => $atts['per_page'],
            'order' => $atts['order'],
            'post_status' => 'publish',
            'paged' => get_query_var('paged') ? get_query_var('paged') : 1
        );

        if (!empty($atts['categories'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'eb_course_cat',
                    'field' => 'slug',
                    'terms' => explode(',', $atts['categories']),
                    'operator' => $atts['category_operator']
                )
            );
        }

        $custom_query = new \WP_Query($args);

        // Pagination fix
        $temp_query = isset($wp_query) ? $wp_query : null;
        $wp_query = null;
        $wp_query = $custom_query;

        $template_loader = new EbTemplateLoader(
            edwiserBridgeInstance()->getPluginName(),
            edwiserBridgeInstance()->getVersion()
        );

        echo '<div class="sc-eb_courses-wrapper">';
        do_action('eb_before_course_archive');
        if ($custom_query->have_posts()) {
            while ($custom_query->have_posts()) :
                $custom_query->the_post();
                $template_loader->wpGetTemplatePart('content', 'eb_course');
            endwhile;
        } else {
            $template_loader->wpGetTemplatePart('content', 'none');
        }
        wp_reset_postdata();
        ?>
        <div style="clear:both"></div>
        <?php
        $template_loader->wpGetTemplate(
            'course-pagination.php',
            array(
            'current_page' => $args['paged'],
            'max_num_pages' => $custom_query->max_num_pages
                )
        );
        // Reset main query object
        $wp_query = null;
        $wp_query = $temp_query;
        do_action('eb_after_course_archive');
        echo '</div>';
    }
}
