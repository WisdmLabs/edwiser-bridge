<?php
/**
 * The template for displaying course archive content.
 *
 * @package Edwiser Bridge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
// Variables.
global $post;

/**
 * Default data initilization.
 * This data is part of the template, In case of template overriding this need to be reinitialize.
 */
$eb_post_id    = $post->ID;
$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

// get currency.
$payment_options   = get_option( 'eb_paypal' );
$currency          = isset( $payment_options['eb_paypal_currency'] ) ? $payment_options['eb_paypal_currency'] : 'USD';
$course_price_type = 'free';
$course_price      = '0';
$short_description = '';
$course_class      = null;
$user_id           = get_current_user_id();
$logged_in         = ! empty( $user_id );
$enroll_manag      = app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->enrollment_manager();
$has_access        = $enroll_manag->user_has_course_access( $user_id, $post->ID );
$course_options    = get_post_meta( $eb_post_id, 'eb_course_options', true );
/**
 * Leagacy data.
 */
$course_id = $eb_post_id;
$thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url() : $eb_plugin_url . 'images/no-image.jpg';

if ( is_array( $course_options ) ) {
	$course_price_type = ( isset( $course_options['course_price_type'] ) ) ? $course_options['course_price_type'] : 'free';
	$course_price      = ( isset( $course_options['course_price'] ) && is_numeric( $course_options['course_price'] ) ) ? $course_options['course_price'] : '0';
	$course_closed_url = ( isset( $course_options['course_closed_url'] ) ) ? $course_options['course_closed_url'] : '#';
	$short_description = ( isset( $course_options['course_short_description'] ) ) ? $course_options['course_short_description'] : '';
}

if ( is_numeric( $course_price ) ) {
	$currency_sym           = 'USD' === $currency ? '$' : $currency;
	$course_price_formatted = '0' === $course_price ? __( 'Free', 'eb-textdomain' ) : $currency_sym . ' ' . $course_price;
}


/*
 * To add class according to user access.
 */

$course_class = 'no-access';
/* Tanslators 1: title */
$h_title = sprintf( esc_html__( 'Click to read more about', 'eb-textdomain' ) . ' %s' . esc_html__( ' course', 'eb-textdomain' ), get_the_title( get_the_ID() ) );
if ( $has_access ) {
	$course_class = 'has-access';
	/* Tanslators 1: title */
	$h_title = sprintf( esc_html__( 'Click to access', 'eb-textdomain' ) . ' %s' . esc_html__( ' course', 'eb-textdomain' ), get_the_title( get_the_ID() ) );
}

// Shortcode eb_my_courses.
if ( isset( $is_eb_my_courses ) && $is_eb_my_courses && isset( $attr ) ) {
	$course_class  .= ' eb_my_course_article';
	$course_mang    = app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->course_manager();
	$mdl_course_id  = $course_mang->get_moodle_course_id( $course_id );
	$moodle_user_id = get_user_meta( $user_id, 'moodle_user_id', true );

	if ( '' !== $moodle_user_id && function_exists( 'ebsso\generateMoodleUrl' ) ) {
		$query      = array(
			'moodle_user_id'   => $moodle_user_id, // moodle user id.
			'moodle_course_id' => $mdl_course_id,
		);
		$course_url = \ebsso\generateMoodleUrl( $query );
	} else {
		$eb_access_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();
		$course_url    = $eb_access_url . '/course/view.php?id=' . $mdl_course_id;
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
				<div class="wdm-course-image">
					<img src="<?php echo esc_url( $thumb_url ); ?>"/>
				</div>
				<div class="wdm-caption">
				<h4 class="eb-course-title"><?php the_title(); ?></h4>
				<p class="entry-content">
					<?php echo esc_html( $short_description ); ?> 
				</p>
				<?php
				if ( 'eb_course' === $post->post_type && ! $is_eb_my_courses && ( 'paid' === $course_price_type || 'free' === $course_price_type ) ) {
					?>
						<div class="wdm-price <?php echo wp_kses_post( $course_price_type ); ?>">
							<?php echo wp_kses_post( $course_price_formatted, \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() ); ?>
						</div>
					<?php
				}

				if ( isset( $attr['show_progress'] ) && $attr['show_progress'] ) {
					echo wp_kses_post( $attr['progress_btn_div'] );
				}
				?>
				</div>
			</a>
		</div>
	</div>
	<!-- .wdm-course-grid -->
</article><!-- #post -->
