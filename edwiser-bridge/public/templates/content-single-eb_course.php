<?php
/**
 * The template for displaying single course content.
 *
 * This template can be overridden by copying it to yourtheme/edwiser-bridge/
 *
 * @author      WisdmLabs
 * @version     1.2.0
 * @package     eb_course
 */

namespace app\wisdmlabs\edwiserBridge;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Variables.
global $post;
$post_id = $post->ID; // @codingStandardsIgnoreLine.

// get currency.
$payment_options = get_option( 'eb_paypal' );
$currency        = isset( $payment_options['eb_paypal_currency'] ) ? $payment_options['eb_paypal_currency'] : 'USD';

$course_price_type = 'free';
$course_price      = 0;
$short_description = '';

$course_options = get_post_meta( $post_id, 'eb_course_options', true );
if ( is_array( $course_options ) ) {
	$course_price_type = ( isset( $course_options['course_price_type'] ) ) ? $course_options['course_price_type'] : 'free';
	$course_price      = ( isset( $course_options['course_price'] ) &&
			is_numeric( $course_options['course_price'] ) ) ?
			$course_options['course_price'] : 0;
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

	if ( 0 === $course_price ) {
		$course_price_formatted = __( 'Free', 'eb-textdomain' );
	}
}

$course_class = null;
$user_id      = get_current_user_id();
$logged_in    = ! empty( $user_id );
$has_access   = edwiser_bridge_instance()->enrollment_manager()->user_has_course_access( $user_id, $post->ID );

$course_id = $post_id;

$categories = array();
$terms      = wp_get_post_terms(
	$course_id,
	'eb_course_cat',
	array(
		'orderby' => 'name',
		'order'   => 'ASC',
		'fields'  => 'all',
	)
); // @codingStandardsIgnoreLine.

if ( is_array( $terms ) ) {
	foreach ( $terms as $eb_term ) {
		$lnk          = get_term_link( $eb_term->term_id, 'eb_course_cat' );
		$categories[] = '<a href="' . esc_url( $lnk ) . '" target="_blank">' . esc_html( $eb_term->name ) . '</a>';
	}
}

/*
 * Check is course has expiry date
 */
if ( isset( $course_options['course_expirey'] ) && 'yes' === $course_options['course_expirey'] ) {
	if ( is_user_logged_in() && $has_access ) {
		$expiry_date_time = '<span><strong>' . Eb_Enrollment_Manager::access_remianing( $user_id, $post->ID ) . ' ' . __( ' days access remaining', 'eb-textdomain' ) . '</strong></span>';
	} else {
		$expiry_date_time = '<span><strong>' . __( 'Includes  ', 'eb-textdomain' ) . ' ' . $course_options['num_days_course_access'] . __( ' days access', 'eb-textdomain' ) . '</strong> </span>';
	}
} else {
	$expiry_date_time = '<span><strong>' . __( 'Includes lifetime access ', 'eb-textdomain' ) . '</strong></span>';
}
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
				echo '<img src="' . esc_html( EB_PLUGIN_URL ) . 'images/no-image.jpg" />';
			}
			?>
		</div>

		<div class="eb-course-summary">
			<?php
			if ( ! is_search() ) {
				if ( ! $has_access || ! is_user_logged_in() ) {
					if ( 'paid' === $course_price_type || 'free' === $course_price_type ) {
						?>
						<div class="<?php echo 'wdm-price' . esc_html( $course_price_type ); ?>">
									<?php echo '<h3>' . esc_html( $course_price_formatted ) . '</h3>'; ?>
						</div>
						<?php
					}
					echo wp_kses( Eb_Payment_Manager::take_course_button( $post->ID ), eb_sinlge_course_get_allowed_html_tags() );
				} else {
					echo wp_kses( Eb_Payment_Manager::access_course_button( $post->ID ), eb_sinlge_course_get_allowed_html_tags() );
				}

				if ( count( $categories ) ) {
					?>
					<div  class="eb-cat-wrapper">
						<span><strong><?php esc_html_e( 'Categories: ', 'eb-textdomain' ); ?></strong><?php echo wp_kses( implode( ', ', $categories ), eb_sinlge_course_get_allowed_html_tags() ); ?></span>
					</div>                  
					<?php
				}
				?>
				<div  class="eb-validity-wrapper">
					<expiry_date_time; ?>
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

			if ( ! $has_access || ! is_user_logged_in() ) {
				echo wp_kses( Eb_Payment_Manager::take_course_button( $post->ID ), eb_sinlge_course_get_allowed_html_tags() );
			} else {
				echo wp_kses( Eb_Payment_Manager::access_course_button( $post->ID ), eb_sinlge_course_get_allowed_html_tags() );
			}
		}
		?>
	</div>
</article><!-- #post -->
