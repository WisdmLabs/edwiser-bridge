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
        extract(
            $atts = shortcode_atts(
                apply_filters(
                    'eb_shortcode_my_courses_defaults',
                    array
                    (
                        'user_id' => get_current_user_id(),
                        'my_courses_wrapper_title' => '',
                        'recommended_courses_wrapper_title' => __('Recommended Courses', 'eb-textdomain'),
                        'number_of_recommended_courses' => 7,
                        'my_courses_progress' => "0"
                    )
                ),
                $atts
            )
        );
        $currentClass = new EbShortcodeMyCourses();

        do_action('eb_before_my_courses_wrapper');

        $my_courses = $currentClass->getUserCourses($atts['user_id']);

        $currentClass->showMyCourses($my_courses, $atts);

        $ebGeneralSetings = get_option("eb_general");

        if (isset($ebGeneralSetings['eb_enable_recmnd_courses']) && $ebGeneralSetings['eb_enable_recmnd_courses'] == "yes") {
            if (is_numeric($atts['number_of_recommended_courses']) && $atts['number_of_recommended_courses'] > 0) {
                $rec_cats = $currentClass->getRecommendedCategories($my_courses);
                if (count($rec_cats) || (isset($ebGeneralSetings['eb_recmnd_courses']) && count($ebGeneralSetings['eb_recmnd_courses']))) {
                    $currentClass->showRecommendedCourses($rec_cats, $my_courses, $atts['number_of_recommended_courses'], $atts);
                }
            }
        }
    }

    public function getUserCourses($user_id = null)
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

    public function showMyCourses($my_courses, $atts)
    {

        $template_loader = new EbTemplateLoader(
            edwiserBridgeInstance()->getPluginName(),
            edwiserBridgeInstance()->getVersion()
        );
        echo '<div class="eb-my-courses-wrapper">';
        if (!empty($atts['my_courses_wrapper_title'])) {
            ?><h2 class="eb-my-courses-h2"><?php echo $atts['my_courses_wrapper_title']; ?></h2><?php
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
                    $template_loader->wpGetTemplate('content-eb_course.php', array('is_eb_my_courses' => true, "attr" => $atts));
                endwhile;
            } else {
                $template_loader->wpGetTemplatePart('content', 'none');
            }
            echo "</div>";
        } else {
            $ebGeneralSettings = get_option("eb_general");
            if (isset($ebGeneralSettings['eb_my_course_link']) && !empty($ebGeneralSettings['eb_my_course_link'])) {
                $link = $ebGeneralSettings['eb_my_course_link'];
            } else {
                $link = site_url('/courses');
            }
            ?>
            <h5>
                <?php
                printf(
                    __('You are not enrolled to any course. %s to access the courses page.', 'eb-textdomain'),
                    "<a href='".$link."'>".__("Click here", "eb-textdomain")."</a>"
                );
                ?>
            </h5>
            <?php
        }
        do_action('eb_after_my_courses');
        echo '</div>';
    }

    /**
     * Functionality to return the recommended categories for the recommended courses.
     * @since  1.3.4
     */
    public function getRecommendedCategories($user_courses)
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

    /**
     *
     * @param  string $rec_cats        Recommended categories for the courses.
     * @param  string $exclude_courses which courses should be excluded from the recommended courses sestion.
     * @param  string $count           No of courses shown in the recommended section.
     * @param  string $atts            This array contains the attrivutes sent from the shortcode.
     * @param  string $args            Wp-Query
     * @return [type]                  Prints entire section of the recommended courses.
     */
    public function showRecommendedCourses($rec_cats = "", $exclude_courses = "", $count = "", $atts = "", $args = "")
    {
        if ($args == "") {
            $args = $this->createQuery($count, $rec_cats, $exclude_courses);
        }
        $courses = new \WP_Query($args);

        $template_loader = new EbTemplateLoader(
            edwiserBridgeInstance()->getPluginName(),
            edwiserBridgeInstance()->getVersion()
        );

        if ($courses->have_posts()) {
            echo '<div class="eb-rec-courses-wrapper">';
            if (!empty($atts['recommended_courses_wrapper_title'])) {
                ?><h2><?php echo $atts['recommended_courses_wrapper_title']; ?></h2><?php
            }
            do_action('eb_before_recommended_courses');
            while ($courses->have_posts()) :
                $courses->the_post();
                $template_loader->wpGetTemplatePart('content', 'eb_course');
            endwhile;
            do_action('eb_after_recommended_courses');
            echo '</div>';
            $eb_course = get_post_type_object('eb_course');
            $view_more_url = site_url($eb_course->rewrite['slug']);
            ?>
            <a href="<?php echo $view_more_url; ?>" class="wdm-btn eb-rec-courses-view-more">
                <?php _e('View More &rarr;', 'eb-textdomain'); ?>
            </a>
            <?php
        } else {
            $template_loader->wpGetTemplatePart('content', 'none');
        }
    }



    /**
     * FUnction used to create wp-query according to the backend options
     * @since  1.3.4
     * @param  [type] $count           No of courses shown in the recommended section
     * @param  [type] $rec_cats        Recommended categories for the courses
     * @param  [type] $exclude_courses which courses should be excluded from the recommended courses sestion
     * @return [type]                  Wp-query
     */
    public function createQuery($count, $rec_cats, $exclude_courses)
    {
        $ebGeneralSetings = get_option("eb_general");

        if (isset($ebGeneralSetings['eb_show_default_recmnd_courses']) && $ebGeneralSetings['eb_show_default_recmnd_courses'] == "yes") {
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
        } elseif (isset($ebGeneralSetings['eb_recmnd_courses']) && !empty($ebGeneralSetings['eb_recmnd_courses'])) {
            $args = array(
                'post_type' => 'eb_course',
                'post_status' => 'publish',
                'posts_per_page' => $count,
                'post__in' => $ebGeneralSetings['eb_recmnd_courses']
            );
        }
        return $args;
    }

    /**
     * function to create a custom wp query which created on the basis of the category or the custom courses selected in setting
     * @since  1.3.4
     * @return [type] [description]
     */
    public function generateRecommendedCourses()
    {
        global $post;
        $courseOptions = get_post_meta($post->ID, "eb_course_options", true);
        $attr["recommended_courses_wrapper_title"] = __("Recommended Courses", "eb-textdomain");

        if (isset($courseOptions['enable_recmnd_courses']) && $courseOptions['enable_recmnd_courses'] == "yes") {
            if (isset($courseOptions['show_default_recmnd_course']) && $courseOptions['show_default_recmnd_course'] == "yes") {
                $args = array(
                    'post_type' => 'eb_course',
                    'post_status' => 'publish',
                    'posts_per_page' => 4,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'eb_course_cat',
                            'field' => 'slug',
                            'terms' => $this->getRecommendedCategories(array($post->ID))
                        ),
                    ),
                    'post__not_in' => array($post->ID)
                );
                $this->showRecommendedCourses("", "", "", $attr, $args);
            } elseif (isset($courseOptions['enable_recmnd_courses_single_course']) && !empty($courseOptions['enable_recmnd_courses_single_course'])) {
                $args = array(
                    'post_type' => 'eb_course',
                    'post_status' => 'publish',
                    // 'posts_per_page' => 4,
                    'post__in' => $courseOptions['enable_recmnd_courses_single_course']
                );
                $this->showRecommendedCourses("", "", "", $attr, $args);
            }
        }
    }
}
