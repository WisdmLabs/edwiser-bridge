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
$user_id      = get_current_user_id();
$logged_in    = !empty($user_id);
$enrollManag  = app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->enrollment_manager();
$has_access   = $enrollManag->user_has_course_access($user_id, $post->ID);

/*
 * To add class according to user access
 *
 */
if ($has_access) {
	$course_class = 'has-access';
	$h_title = __(sprintf('Click to access %s course', get_the_title(get_the_ID())), 'eb-textdomain');
} else {
	$course_class = 'no-access';
	$h_title = __(sprintf('Click to read more about %s course', get_the_title(get_the_ID())), 'eb-textdomain');
}




$course_id = $post_id;
//Shortcode eb_my_courses.


if (isset($is_eb_my_courses) && $is_eb_my_courses && isset($attr)) {
	$course_class .= ' eb_my_course_article';
	$course_mang = app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->course_manager();
	$mdl_course_id = $course_mang->get_moodle_course_id($course_id);
	$moodle_user_id = get_user_meta($user_id, 'moodle_user_id', true);

/*******   two way synch  *******/

	if ($moodle_user_id && isset($attr["my_courses_progress"]) && $attr["my_courses_progress"]) {
		$showProgress = 1;
		$course_progress_manager = new app\wisdmlabs\edwiserBridge\Eb_Course_Progress();

		// Before showing progress check for the suspended course.

		$progress_data = $course_progress_manager->get_course_progress();
		$courseId = array_keys($progress_data);
		// Function to get suspended status info.
		$is_user_suspended = get_user_suspended_status($user_id, $course_id);

		if ($is_user_suspended) {
			// User course is suspended.
			$progress_class = "suspended";
			$progress_btn_div = "<div class='eb-course-action-btn-suspended'>".__("SUSPENDED", "eb-textdomain")."</div>";
		} elseif (in_array(get_the_ID(), $courseId)) {
			// User course is not suspended then show these buttons.
			if ($progress_data[get_the_ID()] == 0) {
				$progress_class = "start";
				$progress_btn_div = "<div class='eb-course-action-btn-start'>".__("START", "eb-textdomain")."</div>";
			} elseif ($progress_data[get_the_ID()] > 0 && $progress_data[get_the_ID()] < 100) {
				$progress_class = "resume";
				$progressWidth = $progress_data[get_the_ID()];
				$progress_btn_div = "<div class='eb-course-action-btn-resume'>".__("RESUME", "eb-textdomain")."</div>";
			} else {
				$progress_class = "completed";
				$progress_btn_div = "<div class='eb-course-action-btn-completed'>".__("COMPLETED", "eb-textdomain")."</div>";
			}
		}
	}
/**********************/


	if ($moodle_user_id != '' && function_exists("ebsso\generateMoodleUrl")) {
		$query = array(
			'moodle_user_id' => $moodle_user_id, //moodle user id
			'moodle_course_id' => $mdl_course_id,
		);
		$course_url = \ebsso\generateMoodleUrl($query);
	} else {
		$course_url = EB_ACCESS_URL.'/course/view.php?id='.$mdl_course_id;
	}
} else {
	$is_eb_my_courses = false;
	$course_url = get_permalink();
}
?>
<article id="<?php echo 'post-'.get_the_ID(); ?>" <?php post_class('wdm-col-3-2-1 eb-course-col wdm-course-grid-wrap '.$course_class); ?> title="<?php echo $h_title; ?>">
<div class="eb-grid-container">
	<div class="wdm-course-grid">
		<a href="<?php echo esc_url($course_url); ?>" rel="bookmark" class="wdm-course-thumbnail">
			<?php
			echo '<div class="wdm-course-image">';
			if (has_post_thumbnail()) {
				the_post_thumbnail('course_archive');
			} else {
				echo '<img src="'.EB_PLUGIN_URL.'images/no-image.jpg"/>';
			}
			echo '</div>';
			echo '<div class="wdm-caption">';
			echo '<h4 class="">';
			the_title();
			echo '</h4>';
			if (!empty($short_description)) {
				echo '<p class="entry-content">'.$short_description.'</p>';
			}
			if ($post->post_type == 'eb_course' && !$is_eb_my_courses) {
				if ($course_price_type == 'paid' || $course_price_type == 'free') {
					echo '<div class="wdm-price '.$course_price_type.'">';
					echo $course_price_formatted;
					echo '</div>';
				}
			}

			if (isset($showProgress) && $showProgress == 1) {
				echo "<div class='eb-course-action-cont'>";
				if ($progress_class == "resume") {
					echo "<div class='eb-course-action-progress-cont'>  <div class='eb-course-action-progress' style='width:".round($progressWidth)."%' ></div></div>";
				}
				echo $progress_btn_div;
				echo "</div>";
			}


			echo '</div>'; //wdm-caption

			?>
		</a>
	</div>
</div>




	<!-- .wdm-course-grid -->
</article><!-- #post -->
