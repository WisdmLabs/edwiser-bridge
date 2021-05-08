<?php
/**
 * Handles template related dependncies
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Theme compat.
 */
class Eb_Template_Functions {


	public function content_eb_course_tml_dependency( $post_id, $attr, $is_eb_my_courses )
	{
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
		$course_class      = '';
		$user_id           = get_current_user_id();
		$logged_in         = ! empty( $user_id );
		$enroll_manag      = \app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->enrollment_manager();
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
			$course_mang    = \app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->course_manager();
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


		return array(
			'course_class'           => $course_class,
			'h_title'                => $h_title,
			'thumb_url'              => $thumb_url,
			'course_url'             => $course_url,
			'short_description'      => $short_description,
			'course_price_formatted' => $course_price_formatted,
			'is_eb_my_courses'       => $is_eb_my_courses,
			'course_price_type'      => $course_price_type,
		);
	}






	public function content_single_eb_course_tml_dependency()
	{
		global $post;

		$post_id       = $post->ID; // @codingStandardsIgnoreLine.
		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

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


		return array(
			'eb_plugin_url'          => $eb_plugin_url,
			'has_access'             => $has_access,
			'course_price_type'      => $course_price_type,
			'course_price_formatted' => $course_price_formatted,
			'expiry_date_time'       => $expiry_date_time,
			'categories'             => $categories,
		);


	} 




	public function eb_course_archive_price_tmpl( $course_data )
	{
		$template_loader = new EbTemplateLoader(
			edwiser_bridge_instance()->get_plugin_name(),
			edwiser_bridge_instance()->get_version()
		);

		$template_loader->wp_get_template(
			'courses/courses-price.php',
			$course_data
		);
	}



	public function eb_my_course_archive_progress_tmpl( $course_data, $shortcode_attr )
	{

error_log('eb_my_course_archive_progress_tmpl ::: ');

		$template_loader = new EbTemplateLoader(
			edwiser_bridge_instance()->get_plugin_name(),
			edwiser_bridge_instance()->get_version()
		);

		$template_loader->wp_get_template(
			'courses/my-courses-progress.php',
			array( 
				'course_data'    => $course_data,
				'shortcode_attr' => $shortcode_attr,
			)
		);
	}








}
