<?php
/**
 * The template for displaying course archive content.
 *
 * @package Edwiser Bridge.
 */

// Variables.
global $post;
$eb_post_id = $post->ID;


// get currency.
$payment_options = get_option( 'eb_paypal' );
$currency        = isset( $payment_options['eb_paypal_currency'] ) ? $payment_options['eb_paypal_currency'] : 'USD';

$course_price_type = 'free';
$course_price      = '0';
$short_description = '';

$course_options = get_post_meta( $eb_post_id, 'eb_course_options', true );
if ( is_array( $course_options ) ) {
	$course_price_type = ( isset( $course_options['course_price_type'] ) ) ? $course_options['course_price_type'] : 'free';
	$course_price      = ( isset( $course_options['course_price'] ) &&
			is_numeric( $course_options['course_price'] ) ) ?
			$course_options['course_price'] : '0';
	$course_closed_url = ( isset( $course_options['course_closed_url'] ) ) ?
			$course_options['course_closed_url'] : '#';
	$short_description = ( isset( $course_options['course_short_description'] ) ) ?
			$course_options['course_short_description'] : '';
}

if ( is_numeric( $course_price ) ) {
	if ( 'USD' === $currency ) {
		$course_price_formatted = '$' . $course_price;
	} else {
		$course_price_formatted = $currency . ' ' . $course_price;
	}

	if ( 0 === $currency ) {
		$course_price_formatted = esc_html__( 'Free', 'eb-textdomain' );
	}
}

$course_class = null;
$user_id      = get_current_user_id();
$logged_in    = ! empty( $user_id );
$enroll_manag = app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->enrollment_manager();
$has_access   = $enroll_manag->user_has_course_access( $user_id, $post->ID );

/*
 * To add class according to user access.
 *
 */
if ( $has_access ) {
	$course_class = 'has-access';
	/* Tanslators 1: title */
	$h_title = sprintf( esc_html__( 'Click to access', 'eb-textdomain' ) . ' %s' . esc_html__( ' course', 'eb-textdomain' ), get_the_title( get_the_ID() ) );
} else {
	$course_class = 'no-access';
	/* Tanslators 1: title */
	$h_title = sprintf( esc_html__( 'Click to read more about', 'eb-textdomain' ) . ' %s' . esc_html__( ' course', 'eb-textdomain' ), get_the_title( get_the_ID() ) );
}

$course_id = $eb_post_id;

// Shortcode eb_my_courses.
if ( isset( $is_eb_my_courses ) && $is_eb_my_courses && isset( $attr ) ) {
	$course_class  .= ' eb_my_course_article';
	$course_mang    = app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->course_manager();
	$mdl_course_id  = $course_mang->get_moodle_course_id( $course_id );
	$moodle_user_id = get_user_meta( $user_id, 'moodle_user_id', true );

	if ( $moodle_user_id && isset( $attr['my_courses_progress'] ) && $attr['my_courses_progress'] ) {
		$show_progress           = 1;
		$course_progress_manager = new app\wisdmlabs\edwiserBridge\Eb_Course_Progress();

		// Before showing progress check for the suspended course.
		$progress_data = $course_progress_manager->get_course_progress();
		$course_id     = array_keys( $progress_data );
		// Function to get suspended status info.
		$is_user_suspended = get_user_suspended_status( $user_id, $course_id );

		if ( $is_user_suspended ) {
			// User course is suspended.
			$progress_class   = 'suspended';
			$progress_btn_div = "<div class='eb-course-action-btn-suspended'>" . esc_html__( 'SUSPENDED', 'eb-textdomain' ) . '</div>';
		} elseif ( in_array( get_the_ID(), $course_id ) ) {// @codingStandardsIgnoreLine.
			// User course is not suspended then show these buttons.
			if ( 0 === $progress_data[ get_the_ID() ] ) {
				$progress_class   = 'start';
				$progress_btn_div = "<div class='eb-course-action-btn-start'>" . esc_html__( 'START', 'eb-textdomain' ) . '</div>';
			} elseif ( $progress_data[ get_the_ID() ] > 0 && $progress_data[ get_the_ID() ] < 100 ) {
				$progress_class   = 'resume';
				$progress_width   = $progress_data[ get_the_ID() ];
				$progress_btn_div = "<div class='eb-course-action-btn-resume'>" . esc_html__( 'RESUME', 'eb-textdomain' ) . '</div>';
			} else {
				$progress_class   = 'completed';
				$progress_btn_div = "<div class='eb-course-action-btn-completed'>" . esc_html__( 'COMPLETED', 'eb-textdomain' ) . '</div>';
			}
		}
	}

	if ( '' !== $moodle_user_id && function_exists( 'ebsso\generateMoodleUrl' ) ) {
		$query      = array(
			'moodle_user_id'   => $moodle_user_id, // moodle user id.
			'moodle_course_id' => $mdl_course_id,
		);
		$course_url = \ebsso\generateMoodleUrl( $query );
	} else {
		$course_url = EB_ACCESS_URL . '/course/view.php?id=' . $mdl_course_id;
	}
} else {
	$is_eb_my_courses = false;
	$course_url       = get_permalink();
}
?>
<article id="<?php echo 'post-' . get_the_ID(); ?>" <?php post_class( 'wdm-col-3-2-1 eb-course-col wdm-course-grid-wrap ' . $course_class ); ?> title="<?php echo esc_html( $h_title ); ?>">
	<div class="eb-grid-container">
		<div class="wdm-course-grid">
			<a href="<?php echo esc_url( $course_url ); ?>" rel="bookmark" class="wdm-course-thumbnail">
				<?php
				echo '<div class="wdm-course-image">';
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'course_archive' );
				} else {
					echo '<img src="' . esc_html( EB_PLUGIN_URL ) . 'images/no-image.jpg"/>';
				}
				echo '</div>';
				echo '<div class="wdm-caption">';
				echo '<h4 class="">';
				the_title();
				echo '</h4>';
				if ( ! empty( $short_description ) ) {
					echo '<p class="entry-content">' . esc_html( $short_description ) . '</p>';
				}
				if ( 'eb_course' === $post->post_type && ! $is_eb_my_courses ) {
					if ( 'paid' === $course_price_type || 'free' === $course_price_type ) {
						echo '<div class="wdm-price ' . wp_kses_post( $course_price_type ) . '">';
						echo wp_kses_post( $course_price_formatted );
						echo '</div>';
					}
				}

				if ( isset( $show_progress ) && 1 === $show_progress ) {
					echo "<div class='eb-course-action-cont'>";
					if ( 'resume' === $progress_class ) {
						echo "<div class='eb-course-action-progress-cont'>  <div class='eb-course-action-progress' style='width:" . esc_html( round( $progress_width ) ) . "%' ></div></div>";
					}
					echo esc_html( $progress_btn_div );
					echo '</div>';
				}

				echo '</div>'; // wdm-caption.

				?>
			</a>
		</div>
	</div>
	<!-- .wdm-course-grid -->
</article><!-- #post -->
