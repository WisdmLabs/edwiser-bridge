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
            'order' => 'DESC',
            'group_by_cat' => 'no',
            'cat_per_page' => '4', //-1 for all in one row
            'horizontally_scroll' => 'no',
            'per_page' => 10
                )), $atts));

        $args = array(
            'post_type' => 'eb_course',
            'order' => $atts['order'],
            'post_status' => 'publish',
            'posts_per_page' => $atts['per_page'],
        );

        /**
         * Get all ctegorys from shortcode
         */
        $inPutCat = explode(',', $atts['categories']);
        if (!empty($atts['categories'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'eb_course_cat',
                    'field' => 'slug',
                    'terms' => $inPutCat
                )
            );
        }
        /**
         * Create class of current object
         */
        $currClass = new EbShortcodeCourses();
        /**
         * Initialize horizontal scroll defualt is false
         * It takes boolean value
         */
        $scrollHorizontal = false;
        /**
         * Check is horizintal scroll spesified in shortcoe and set value
         */
        if (isset($atts['horizontally_scroll']) && $atts['horizontally_scroll'] == 'yes') {
            $scrollHorizontal = true;
            $args['posts_per_page']=-1;
        }

        /**
         * It will check whether to display courses page output in categorys grouping ot not
         * If in shortcode parameter it is spesified group_by_cat parameter value
         * true then shows courses in category groups.
         */
        if (isset($atts['group_by_cat']) && $atts['group_by_cat'] == 'yes') {
            $dispCat = $currClass->showCatView($inPutCat);
            $catCnt = count($dispCat);
            $page = 1;
            if (isset($_GET['eb-cat-page-no'])) {
                $page = $_GET['eb-cat-page-no'];
            }
            $catStart = $page * (int) $atts['cat_per_page'] - (int) $atts['cat_per_page'];
            $cnt = 0;
            $args['posts_per_page']=-1;
            foreach ($dispCat as $category) {
                $cnt++;
                if ($cnt < $catStart + 1 ||$cnt > $catStart + (int) $atts['cat_per_page']) {
                    continue;
                }
                ?>
                <div class='eb-cat-parent'>
                    <h3 class="eb-cat-title"><?php echo $category->name; ?></h3>
                    <?php
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'eb_course_cat',
                            'field' => 'slug',
                            'include_children' => false,
                            'terms' => $category->slug,
                        )
                    );
                    $currClass->genCoursesGridView($args, $scrollHorizontal);
                    ?>
                </div>
                <?php
            }
            $currClass->catPagination($catCnt, $atts['cat_per_page'], $page);
        } else {
            if (!$scrollHorizontal) {
                $args['posts_per_page'] = $atts['per_page'];
                $args['paged'] = get_query_var('paged') ? get_query_var('paged') : 1;
            }
            ?>
            <div class='eb-cat-parent'>
                <?php
                $currClass->genCoursesGridView($args, $scrollHorizontal);
                ?>
            </div>
            <?php
        }
    }

    /**
     * It will  check which categorys need to show in the shortcode output
     * @param array $inPutCat Array of the category slugs to display courses from those categorys only
     * @return array returns array of the categorys object
     */
    public function showCatView($inPutCat)
    {
        $ebAllCat = get_terms(array('taxonomy' => 'eb_course_cat'));
        $firstInPutCat = trim($inPutCat[0]);
        /**
         * Check is the no category spesified in the shortcode parameter or emapy slug provided
         */
        if (count($inPutCat) < 2 && empty($firstInPutCat)) {
            return $ebAllCat;
        }

        $catToDisplay = array();
        foreach ($ebAllCat as $cat) {
            if (in_array($cat->slug, $inPutCat)) {
                $catToDisplay[] = $cat;
            }
        }
        return $catToDisplay;
    }

    /**
     * Genrates the pagination for the category view
     *
     * @param int $catCnt total category count
     * @param int $perPage Categorys to display on each page
     * @param int $currentPage current page number shown in output
     * @return HTML returns the html output for the pagination
     */
    public function catPagination($catCnt, $perPage, $currentPage = 1)
    {
        /**
         * Check is the cat is less than perpage ammount
         * If yes then don't show pagination.
         */
        if ($catCnt <= $perPage) {
            return;
        }
        ob_start();
        ?>
        <nav class="navigation pagination" role="navigation">
            <h2 class="screen-reader-text"><?php _e("Courses navigation", "eb-textdomain"); ?></h2>
            <div class="nav-links">
                <?php
                $page = 1;
                if ($currentPage != 1) {
                    ?>
                    <a class="prev page-numbers" href="<?php echo add_query_arg(array("eb-cat-page-no" => $currentPage - 1), get_permalink()); ?>">
                        <?php _e("Previous page", 'eb-textdomain'); ?>
                    </a>
                    <?php
                }
                for ($cnt = 1; $cnt <= $catCnt; $cnt += (int) $perPage) {
                    $pageIdCss = "page-numbers";
                    if ($page == $currentPage) {
                        ?>
                        <span class="page-numbers current">
                            <span class="meta-nav screen-reader-text">Page </span>
                            <?php echo $page; ?>
                        </span>
                        <?php
                        $pageIdCss .= " current";
                    } else {
                        ?>
                        <a class="page-numbers" href="<?php echo add_query_arg(array("eb-cat-page-no" => $page), get_permalink()); ?>">
                            <?php echo $page; ?>
                        </a>
                        <?php
                    }
                    $page++;
                }
                if ($currentPage < $page - 1) {
                    ?>
                    <a class="next page-numbers" href="<?php echo add_query_arg(array("eb-cat-page-no" => $currentPage <= 1 ? 2 : $currentPage + 1), get_permalink()); ?>">
                        <?php _e("Next Page", 'eb-textdomain'); ?>
                    </a>
                    <?php
                }
                ?>
            </div>
        </nav>
        <?php
        ob_get_flush();
    }

    /**
     * This will print the courses list.
     * @param Array $args Get courses posts selection parameters
     * @param boolean $groupByCat group the courses by categorys or not.
     */
    public function genCoursesGridView($args, $groupByCat)
    {
        $scrollClass = "sc-eb_courses-wrapper";
        if ($groupByCat) {
            $scrollClass = "eb-cat-courses-cont sc-eb_courses-wrapper";
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
        ?>
        <div class="<?php echo $scrollClass; ?>">
            <?php if ($groupByCat) { ?>
                <span class="fa fa-angle-left eb-scroll-left" id="eb-scroll-left"></span>
            <?php } ?>
            <?php
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
                    'max_num_pages' => $custom_query->max_num_pages
                    )
            );
            // Reset main query object
            $wp_query = null;
            $wp_query = $temp_query;
            do_action('eb_after_course_archive');
            if ($groupByCat) {
                ?>
                <span class="fa fa-angle-right eb-scroll-right" id="eb-scroll-right"></span>
            <?php } ?>
        </div>
        <?php
    }
}
