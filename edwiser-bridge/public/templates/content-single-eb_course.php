<?php
/**
 * The template for displaying single course content.
 *
 * This template can be overridden by copying it to yourtheme/edwiser-bridge/
 *
 * @version     1.2.0
 * @package     eb_course
 */

namespace app\wisdmlabs\edwiserBridge;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Variables.
global $post;


/*
eb_plugin_url
has_access
course_price_type
course_price_formatted
expiry_date_time
categories


*/

$single_course_data = apply_filters('eb_content_single_course_before', $post->ID);


?>

<article id="course-<?php the_ID(); ?>" class="type-post hentry single-course" >
	<h1 class="entry-title">
		<?php
		if ( is_single() ) {
			the_title();
		} else {
			?>
			<a href="
			<?php
			the_permalink();
			?>
			" rel="bookmark"><?php the_title(); ?></a>
			<?php
		}
		?>
	</h1>
	<div>
		<div class="eb-course-img-wrapper">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'course_single' );
			} else {
				echo '<img src="' . esc_html( $single_course_data['eb_plugin_url'] ) . 'images/no-image.jpg" />';
			}
			?>
		</div>

		<div class="eb-course-summary">
			<?php
			if ( ! is_search() ) {
				if ( ! $single_course_data['has_access'] || ! is_user_logged_in() ) {
					if ( 'paid' === $single_course_data['course_price_type'] || 'free' === $single_course_data['course_price_type'] ) {
						?>
						<div class="<?php echo 'wdm-price' . esc_html( $single_course_data['course_price_type'] ); ?>">
									<?php echo '<h3>' . esc_html( $single_course_data['course_price_formatted'] ) . '</h3>'; ?>
						</div>
						<?php
					}

					echo wp_kses( Eb_Payment_Manager::take_course_button( $post->ID ), \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() );
				} else {
					echo wp_kses( Eb_Payment_Manager::access_course_button( $post->ID ), \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() );
				}

				if ( count( $single_course_data['categories'] ) ) {
					?>
					<div  class="eb-cat-wrapper">
						<span><strong><?php esc_html_e( 'Categories: ', 'eb-textdomain' ); ?></strong><?php echo wp_kses( implode( ', ', $single_course_data['categories'] ), \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() ); ?></span>
					</div>                  
					<?php
				}
				?>
				<div  class="eb-validity-wrapper">
					<?php echo wp_kses( $single_course_data['expiry_date_time'], \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() ); ?>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<div class="eb-course-desc-wrapper">
		<?php
		if ( is_search() ) {
			?>
			<div class="entry-summary">
			<?php
			the_excerpt();
			?>
			</div>
				<?php
		} else {
			?>
			<h2><?php esc_html_e( 'Course Overview', 'eb-textdomain' ); ?></h2>
			<?php
			the_content();

			if ( ! $single_course_data['has_access'] || ! is_user_logged_in() ) {
				echo wp_kses( Eb_Payment_Manager::take_course_button( $post->ID ), \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() );
			} else {
				echo wp_kses( Eb_Payment_Manager::access_course_button( $post->ID ), \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() );
			}
		}
		?>
	</div>
</article><!-- #post -->
