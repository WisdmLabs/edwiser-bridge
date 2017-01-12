<?php
/**
 * The template for displaying course archive content.
 */
//Variables
global $post;
$post_id = $post->ID;

// get currency
$payment_options = get_option('eb_paypal');
$currency = isset($payment_options['eb_paypal_currency']) ? $payment_options['eb_paypal_currency'] : 'USD';

$course_price_type = 'free';
$course_price = '0';
$short_description = '';

$course_options = get_post_meta($post_id, 'eb_course_options', true);
if (is_array($course_options)) {
    $course_price_type = (isset($course_options['course_price_type'])) ? $course_options['course_price_type'] : 'free';
    $course_price = (isset($course_options['course_price']) &&
            is_numeric($course_options['course_price'])) ?
            $course_options['course_price'] : '0';
    $course_closed_url = (isset($course_options['course_closed_url'])) ?
            $course_options['course_closed_url'] : '#';
    $short_description = (isset($course_options['course_short_description'])) ?
            $course_options['course_short_description'] : '';
}

if (is_numeric($course_price)) {
    if ($currency == 'USD') {
        $course_price_formatted = '$'.$course_price;
    } else {
        $course_price_formatted = $currency.' '.$course_price;
    }

    if ($course_price == 0) {
        $course_price_formatted = __('Free', 'eb-textdomain');
    }
}

$course_class = null;
$user_id = get_current_user_id();
$logged_in = !empty($user_id);
$enrollManag = app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->enrollmentManager();
$has_access = $enrollManag->userHasCourseAccess($user_id, $post->ID);

/*
 * To add class according to user access
 *
 */
if ($has_access) {
    $course_class = 'has-access';
    $h_title = __('Click to access.', 'eb-textdomain');
} else {
    $course_class = 'no-access';
    $h_title = __('Click to read more.', 'eb-textdomain');
}

$course_id = $post_id;

//Shortcode eb_my_courses.
if (isset($is_eb_my_courses) && $is_eb_my_courses) {
    $courseMang = app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->courseManager();
    $mdl_course_id = $courseMang->getMoodleCourseId($course_id);
    $course_url = EB_ACCESS_URL.'/course/view.php?id='.$mdl_course_id;
} else {
    $is_eb_my_courses = false;
    $course_url = get_permalink();
}
?>

<article id="post-<?php the_ID(); ?>"
            <?php post_class('wdm-col-3-2-1 eb-course-col wdm-course-grid-wrap '.$course_class); ?> title="<?php echo $h_title; ?>">
    <div class="wdm-course-grid">

        <a href="<?php echo esc_url($course_url); ?>" rel="bookmark" class="wdm-course-thumbnail">
            <div class="wdm-course-image">
                <?php
                if (has_post_thumbnail()) {
                    the_post_thumbnail('course_archive');
                } else {
                    ?>
                    <img src="<?php echo EB_PLUGIN_URL;
                    ?>images/no-image.jpg"/>
                            <?php
                }
                        ?>
            </div>
            <div class="wdm-caption">
                <h4 class=""><?php the_title(); ?></h4>
                <?php if (!empty($short_description)) {
    ?>
                    <p class="entry-content"><?php echo $short_description;
    ?></p>
                        <?php
}
                    ?>
                    <?php
                    if ($post->post_type == 'eb_course' && !$is_eb_my_courses) {
                        if ($course_price_type == 'paid' || $course_price_type == 'free') {
                            echo '<div class="wdm-price '.$course_price_type.'">';
                            echo $course_price_formatted;
                            echo '</div>';
                        }
                    }
                    ?>

            </div><!-- .wdm-caption -->
        </a>
    </div><!-- .wdm-course-grid -->
</article><!-- #post -->
