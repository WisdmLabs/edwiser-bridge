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

/*
 * Filter to get all the initial infoe i.e all initial variables which will be used while showing each card of archive page. 
 *
 */
$course_data = apply_filters( 'eb_content_course_before', $post->ID, $attr, $is_eb_my_courses );

?>

<article id="<?php echo 'post-' . get_the_ID(); ?>" <?php post_class( 'wdm-col-3-2-1 eb-course-col wdm-course-grid-wrap ' . $course_data['course_class'] ); ?> title="<?php echo esc_html( $course_data['h_title'] ); ?>">
	<div class="eb-grid-container">
		<div class="wdm-course-grid">

		<?php
			// If the cards are for My courses then no need of link for whole card as we are providing button at the bottom.
			if ( ! isset( $course_data['is_eb_my_courses'] ) || ( isset( $course_data['is_eb_my_courses'] ) && ! $course_data['is_eb_my_courses'] ) ) {

			?>
				<a href="<?php echo esc_url( $course_data['course_url'] ); ?>" rel="bookmark" class="wdm-course-thumbnail">
					<!-- Course card image container -->
					<div class="wdm-course-image">
						<img src="<?php echo esc_url( $course_data['thumb_url'] ); ?>"/>
					</div>
					<div class="wdm-caption">
						<h4 class="eb-course-title"><?php the_title(); ?></h4>
						<p class="entry-content">
							<?php echo esc_html( $course_data['short_description'] ); ?> 
						</p>

						<?php

						// Add_action for price type and price div.
						do_action( 'eb_course_archive_price', $course_data );

						?>
					</div>
				</a>


			<?php

			} else {
				// My courses page cards with progress and everything.
				?>

				<!-- <a href="<?php echo esc_url( $course_data['course_url'] ); ?>" rel="bookmark" class="wdm-course-thumbnail"> -->
				<div>
					<div class="wdm-course-image">
						<img src="<?php echo esc_url( $course_data['thumb_url'] ); ?>"/>
					</div>
					<div class="wdm-caption">
						<h4 class="eb-course-title"><?php the_title(); ?></h4>
						<p class="entry-content">
							<?php echo esc_html( $course_data['short_description'] ); ?> 
						</p>
							<?php

							// Add_action for course progress and related buttons i.e resume, start and completed.
							do_action( 'eb_my_course_archive_progress', $course_data, $attr, $is_eb_my_courses );


							/*if ( isset( $attr['show_progress'] ) && $attr['show_progress'] ) {
								echo wp_kses_post( $attr['progress_btn_div'] );
							}*/
							?>
					</div>
				</div>
				<!-- </a> -->

				<?php
			}


			?>
		</div>
	</div>
	<!-- .wdm-course-grid -->
</article><!-- #post -->
