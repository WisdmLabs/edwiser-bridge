<?php
/**
 * The template for displaying course archive content.
 */

//Variables
global $post;
$post_id = $post->ID;

// get currency
$payment_options = get_option( 'eb_paypal' );
$currency = isset( $payment_options['eb_paypal_currency'] )?$payment_options['eb_paypal_currency']:'USD';

$course_price_type   = 'free';
$course_price        = '0';
$short_description   = '';

$course_options = get_post_meta( $post_id, "eb_course_options", true );
if ( is_array( $course_options ) ) {
	$course_price_type = ( isset( $course_options['course_price_type'] ) )?$course_options['course_price_type']:'free';
	$course_price      = ( isset( $course_options['course_price'] ) && is_numeric( $course_options['course_price'] ) )?$course_options['course_price']:'0';
	$course_closed_url = ( isset( $course_options['course_closed_url'] ) ) ?$course_options['course_closed_url']:'#';
	$short_description = ( isset( $course_options['course_short_description'] ) )?$course_options['course_short_description']:'';
}

if ( is_numeric( $course_price ) ) {
	if ( $currency == "USD" )
		$course_price_formatted = '$' . $course_price;
	else
		$course_price_formatted = $currency .' '. $course_price;

	if ( $course_price == 0 )
		$course_price_formatted = __( 'Free', 'eb-textdomain' );
}

$course_class = null;
$user_id      = get_current_user_id();
$logged_in    = ! empty( $user_id );
$has_access   = EB()->enrollment_manager()->user_has_course_access( $user_id, $post->ID );

/*
 * To add class according to user access
 *
 */
if ( $has_access ) {
	$course_class = 'has-access';
}
else {
	$course_class = 'no-access';
}

$course_id   = $post_id;

?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'wdm-col-3-2-1 wdm-course-grid-wrap '.$course_class ); ?>>
		<div class="wdm-course-grid">
		<?php
if ( $post->post_type == 'eb_course' ): ?>
		<?php
	if ( $course_price_type == 'paid' || $course_price_type == 'free' ) {
		echo '<div class="wdm-price '.$course_price_type. '">';
		echo $course_price_formatted;
		echo '</div>';
	}
?>
		<?php endif;?>

		<?php if ( has_post_thumbnail() ) :?>
		<a href="<?php the_permalink(); ?>" rel="bookmark" class="wdm-course-thumbnail">
			<?php the_post_thumbnail( 'full' ); ?>
		</a>
		<?php else :?>
		<a href="<?php the_permalink(); ?>" rel="bookmark" class="wdm-course-thumbnail">
			<img src="<?php echo EB_PLUGIN_URL; ?>images/no-image.jpg"/>
		</a>
		<?php endif;?>
		<div class="wdm-caption">
			<h4 class=""><?php the_title(); ?></h4>
			<?php if ( !empty( $short_description ) ) { ?>
			<p class="entry-content"><?php echo $short_description; ?></p>
			<?php  } ?>
			
			<p class="read-more"><a class="wdm-btn eb_join_button" role="button" href="<?php the_permalink(); ?>" rel="bookmark"><?php _e( "Read More", 'eb-textdomain' ); ?></a></p>

			<?php
				// if ( $has_access ) {
				// 	echo '<p class="read-more">'.EB_Payment_Manager::access_course_button( get_the_ID() ).'</p>';
				// } else {
				// 	echo '<p class="read-more">'.EB_Payment_Manager::take_course_button( get_the_ID() ).'</p>';
				// }
			?>

		</div><!-- .wdm-caption -->
		</div><!-- .wdm-course-grid -->
	</article><!-- #post -->
