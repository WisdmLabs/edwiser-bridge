<?php

/**
 * The template for displaying single course content.
 */
namespace app\wisdmlabs\edwiserBridge;

//Variables
global $post;
$post_id = $post->ID;

// get currency
$payment_options = get_option('eb_paypal');
$currency = isset($payment_options['eb_paypal_currency']) ? $payment_options['eb_paypal_currency'] : 'USD';

$course_price_type = 'free';
$course_price = 0;
$short_description = '';

$course_options = get_post_meta($post_id, 'eb_course_options', true);
if (is_array($course_options)) {
    $course_price_type = (isset($course_options['course_price_type'])) ? $course_options['course_price_type'] : 'free';
    $course_price = (isset($course_options['course_price']) &&
                            is_numeric($course_options['course_price'])) ?
                        $course_options['course_price'] : 0;
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
$has_access = edwiserBridgeInstance()->enrollmentManager()->userHasCourseAccess($user_id, $post->ID);

$course_id = $post_id;
?>

<article id="course-<?php the_ID(); ?>" class="type-post hentry single-course" >
	<header class="entry-header">

<?php
if (is_single()) {
    ?>
        <h1 class="entry-title"><?php the_title();
    ?></h1>
        <?php

} else {
    ?>
        <h1 class="entry-title">
    <a href="<?php the_permalink();
    ?>" rel="bookmark">
    <?php the_title();
    ?>
    </a>
        </h1>
        <?php

} // is_single()
?>

<?php
if (has_post_thumbnail()) {
    ?>
        <div class="wdm-course-thumbnail">
            <?php the_post_thumbnail();
    ?>
        </div>
        <?php

} else {
    ?>
    <div class="wdm-course-thumbnail">
        <img src="<?php echo EB_PLUGIN_URL;
    ?>images/no-image.jpg"/>
    </div>
        <?php

}
?>
	</header><!-- .entry-header -->

<?php
if (is_search()) { // Only display Excerpts for Search ?>
    <div class="entry-summary">
        <?php the_excerpt();
    ?>
    </div><!-- .entry-summary -->
    <?php

} else {
    ?>
    <div class="entry-content">
    <?php
    the_content();
    if (!$has_access || !is_user_logged_in()) {
        if ($course_price_type == 'paid' || $course_price_type == 'free') {
            echo '<div class="wdm-price '.$course_price_type.'">';
            echo __('<strong>Price: </strong>', 'eb-textdomain').$course_price_formatted;
            echo '</div>';
        }
        echo EBPaymentManager::takeCourseButton($post->ID);
    } else {
        echo EBPaymentManager::accessCourseButton($post->ID);
    }
    ?>
    <?php
    wp_link_pages(
        array(
        'before' => '<div class="page-links"><span class="page-links-title">'.
        __('Courses:', 'eb-textdomain').'</span>',
        'after' => '</div>', 'link_before' => '<span>',
        'link_after' => '</span>',
        )
    );
    ?>
    </div><!-- .entry-content -->
    <?php

}
?>


</article><!-- #post -->