<?php
/**
 * Shortcode eb_my_courses.
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EbShortcodeMyCourses
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
        extract($atts = shortcode_atts(apply_filters('eb_shortcode_my_courses_defaults', array(
            'user_id' => get_current_user_id(),
            'my_courses_wrapper_title' => '',
            'recommended_courses_wrapper_title' => __('Recommended Courses', 'eb-textdomain'),
            'number_of_recommended_courses' => 7,
                )), $atts));
        $my_courses = self::getUserCourses($atts['user_id']);

        self::showMyCourses($my_courses, $atts);
        if (is_numeric($atts['number_of_recommended_courses']) && $atts['number_of_recommended_courses'] > 0) {
            $rec_cats = self::getRecommendedCategories($my_courses);
            if (count($rec_cats)) {
                self::showRecommendedCourses($rec_cats, $my_courses, $atts['number_of_recommended_courses'], $atts);
            }
        }
    }

    public static function getUserCourses($user_id = null)
    {
        $user_id = !is_numeric($user_id) ? get_current_user_id() : (int) $user_id;

        $courses = get_posts(array('post_type' => 'eb_course', 'post_status' => 'publish', 'posts_per_page' => -1));

        $user_courses = array();

        foreach ($courses as $course) {
            if (edwiserBridgeInstance()->enrollmentManager()->userHasCourseAccess($user_id, $course->ID)) {
                $user_courses[] = $course->ID;
            }
        }

        return $user_courses;
    }

    public static function showMyCourses($my_courses, $atts)
    {
        $template_loader = new EbTemplateLoader(
            edwiserBridgeInstance()->getPluginName(),
            edwiserBridgeInstance()->getVersion()
        );
        echo '<div class="eb-my-courses-wrapper">';
        if (!empty($atts['my_courses_wrapper_title'])) {
            ?><h2><?php echo $atts['my_courses_wrapper_title']; ?></h2><?php
        }
        do_action('eb_before_my_courses');
        if (!is_user_logged_in()) {
            ?>
            <p>
                <?php
                printf(
                    __('You are not logged in. %s to login.', 'eb-textdomain'),
                    "<a href='".esc_url(site_url('/user-account'))."'>".__("Click here", "eb-textdomain")."</a>"
                );
                ?>
            </p>
            <?php
        } elseif (count($my_courses)) {
            //My Courses.
            $args = array(
                'post_type' => 'eb_course',
                'post_status' => 'publish',
                'post__in' => $my_courses,
                'ignore_sticky_posts' => true,
                'posts_per_page' => -1
            );

            $courses = new \WP_Query($args);

            echo "<div class='eb-my-course'>";
            if ($courses->have_posts()) {
                while ($courses->have_posts()) :
                    $courses->the_post();
                    $template_loader->wpGetTemplate('content-eb_course.php', array('is_eb_my_courses' => true));
                endwhile;
            } else {
                $template_loader->wpGetTemplatePart('content', 'none');
            }
            echo "</div>";
        } else {
            ?>
            <h5>
                <?php
                printf(
                    __('You are not enrolled to any course. %s to access the courses page.', 'eb-textdomain'),
                    "<a href='".esc_url(site_url('/courses'))."'>".__("Click here", "eb-textdomain")."</a>"
                );
                ?>
            </h5>
            <?php
        }
        do_action('eb_after_my_courses');
        echo '</div>';
    }

    public static function getRecommendedCategories($user_courses)
    {
        //Recommended Courses.
        $rec_cats = array();

        foreach ($user_courses as $user_course_id) {
            $terms = wp_get_post_terms($user_course_id, 'eb_course_cat');
            foreach ($terms as $term) {
                $rec_cats[$term->slug] = $term->name;
            }
        }

        return $rec_cats;
    }

    public static function showRecommendedCourses($rec_cats, $exclude_courses, $count, $atts)
    {
        $args = array(
            'post_type' => 'eb_course',
            'post_status' => 'publish',
            'posts_per_page' => $count,
            'tax_query' => array(
                array(
                    'taxonomy' => 'eb_course_cat',
                    'field' => 'slug',
                    'terms' => array_keys($rec_cats),
                ),
            ),
            'post__not_in' => $exclude_courses
        );

        $courses = new \WP_Query($args);

        $template_loader = new EbTemplateLoader(
            edwiserBridgeInstance()->getPluginName(),
            edwiserBridgeInstance()->getVersion()
        );

        echo '<div class="eb-rec-courses-wrapper">';
        if (!empty($atts['recommended_courses_wrapper_title'])) {
            ?><h2><?php echo $atts['recommended_courses_wrapper_title']; ?></h2><?php
        }
        do_action('eb_before_recommended_courses');
        if ($courses->have_posts()) {
            while ($courses->have_posts()) :
                $courses->the_post();
                $template_loader->wpGetTemplatePart('content', 'eb_course');
            endwhile;
        } else {
            $template_loader->wpGetTemplatePart('content', 'none');
        }
        do_action('eb_after_recommended_courses');
        echo '</div>';
        $eb_course = get_post_type_object('eb_course');
        $view_more_url = site_url($eb_course->rewrite['slug']);
        ?>
        <a href="<?php echo $view_more_url; ?>" class="wdm-btn eb-rec-courses-view-more">
            <?php _e('View More &rarr;', 'eb-textdomain'); ?>
        </a>
        <?php
    }
}
