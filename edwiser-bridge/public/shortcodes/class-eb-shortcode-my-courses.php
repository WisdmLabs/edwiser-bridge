<?php

/**
 * Shortcode eb_my_courses.
 *
 * @link       https://edwiser.org
 * @since      1.1.3
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EbShortcodeMyCourses
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
        extract($atts = shortcode_atts(apply_filters('eb_output_my_courses_defaults', array(
            'user_id'                       => get_current_user_id(),
            'show_recommended_courses'      => 1,
            'number_of_recommended_courses' => 4,
        )), $atts));

        $courses = get_posts(
            array(
                'post_type'      => 'eb_course',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );
        
        $my_courses = array();
        
        foreach ($courses as $course) {
            if (edwiserBridgeInstance()->enrollmentManager()->userHasCourseAccess($atts['user_id'], $course->ID)) {
                $my_courses[] = $course->ID;
            }
        }

        error_log('@ var my courses:');
        error_log(print_r($_SERVER, true));
        error_log(print_r($my_courses, true));

        //My Courses.
        $args = array(
            'post_type'   => 'eb_course',
            'post_status' => 'publish',
            'post__in'    => $my_courses,
            //'ignore_sticky_posts' => 1
        );

        $courses = new \WP_Query($args);
        
        $template_loader = new EbTemplateLoader(
            edwiserBridgeInstance()->getPluginName(),
            edwiserBridgeInstance()->getVersion()
        );

        echo '<div class="sc-eb_my_courses-wrapper">';
        do_action('eb_before_my_courses');
        if ($courses->have_posts()) {
            while ($courses->have_posts()) :
                $courses->the_post();
                //$template_loader->wpGetTemplatePart('content', 'eb_course');
                $template_loader->wpGetTemplate('content-eb_course.php', array('is_eb_my_courses' => true));
            endwhile;
        } else {
            $template_loader->wpGetTemplatePart('content', 'none');
        }
        do_action('eb_after_my_courses');
        echo '</div>';


        //Recommended Courses.
        $rec_cats = array();

        if ($atts['show_recommended_courses']) {
            foreach ($my_courses as $my_course_id) {
                $terms = wp_get_post_terms($my_course_id, 'eb_course_cat');
                foreach ($terms as $term) {
                    $rec_cats[$term->slug] = $term->name;
                }
            }
        }

        error_log('@ var recommended_categories:');
        error_log(print_r($rec_cats, true));

        $args = array(
            'post_type'   => 'eb_course',
            'post_status' => 'publish',
            'posts_per_page' => $atts['number_of_recommended_courses'],
            'tax_query' => array(
                array(
                    'taxonomy' => 'eb_course_cat',
                    'field'    => 'slug',
                    'terms'    => array_keys($rec_cats),
                ),
            ),
            'post__not_in' => $my_courses
        );

        $courses = new \WP_Query($args);

        echo '<div class="eb_rec-courses-wrapper" style="margin: 30px 0">';
        ?>
        <h2><?php _e('Recommended Courses', 'eb-textdomain'); ?></h2>
        <?php
        do_action('eb_before_my_courses');
        if ($courses->have_posts()) {
            while ($courses->have_posts()) :
                $courses->the_post();
                $template_loader->wpGetTemplatePart('content', 'eb_course');
            endwhile;
        } else {
            $template_loader->wpGetTemplatePart('content', 'none');
        }
        do_action('eb_after_my_courses');
        echo '</div>';
        ?>
        <a href="#" class="wdm-btn" style="float: right"><?php _e('View More &rarr;', 'eb-textdomain'); ?></a>
        <?php
    }
}
