<?php
/**
 * Shortcode eb_my_courses.
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * My courses.
 */
class Eb_Shortcode_My_Courses {


	/**
	 * Get the shortcode content.
	 *
	 * @since  1.2.0
	 *
	 * @param array $atts atts.
	 *
	 * @return string
	 */
	public static function get( $atts ) {
		return Eb_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 *
	 * @since  1.2.0
	 *
	 * @param array $atts atts.
	 */
	public static function output( $atts ) {
		$atts          = shortcode_atts(
			apply_filters(
				'eb_shortcode_my_courses_defaults',
				array(
					'user_id'                           => get_current_user_id(),
					'my_courses_wrapper_title'          => '',
					'recommended_courses_wrapper_title' => __( 'Recommended Courses', 'eb-textdomain' ),
					'number_of_recommended_courses'     => 7,
					'my_courses_progress'               => '0',
				)
			),
			$atts
		);
		$current_class = new Eb_Shortcode_My_Courses();

		do_action( 'eb_before_my_courses_wrapper' );

		$my_courses = $current_class->get_user_courses( $atts['user_id'] );

		$current_class->show_my_courses( $my_courses, $atts );

		$eb_general_setings = get_option( 'eb_general' );

		if ( isset( $eb_general_setings['eb_enable_recmnd_courses'] ) && 'yes' === $eb_general_setings['eb_enable_recmnd_courses'] && is_numeric( $atts['number_of_recommended_courses'] ) && $atts['number_of_recommended_courses'] > 0 ) {
			$rec_cats = $current_class->get_recommended_categories( $my_courses );
			if ( count( $rec_cats ) || ( isset( $eb_general_setings['eb_recmnd_courses'] ) && count( $eb_general_setings['eb_recmnd_courses'] ) ) ) {
				$current_class->show_recommended_courses( $rec_cats, $my_courses, $atts['number_of_recommended_courses'], $atts );
			}
		}
	}

	/**
	 * Get user courses.
	 *
	 * @param text $user_id user.
	 */
	public function get_user_courses( $user_id = null ) {
		$user_id = ! is_numeric( $user_id ) ? get_current_user_id() : (int) $user_id;

		$courses = get_posts(
			array(
				'post_type'      => 'eb_course',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);

		$user_courses = array();

		foreach ( $courses as $course ) {
			if ( edwiser_bridge_instance()->enrollment_manager()->user_has_course_access( $user_id, $course->ID ) ) {
				$user_courses[] = $course->ID;
			}
		}

		return $user_courses;
	}

	/**
	 * Show my courses.
	 *
	 * @param text $my_courses my_courses.
	 * @param text $atts usattser.
	 */
	public function show_my_courses( $my_courses, $atts ) {
		$template_loader = new EbTemplateLoader(
			edwiser_bridge_instance()->get_plugin_name(),
			edwiser_bridge_instance()->get_version()
		);

		echo '<div class="eb-my-courses-wrapper">';
		if ( ! empty( $atts['my_courses_wrapper_title'] ) ) {
			?><h2 class="eb-my-courses-h2"><?php echo esc_html( $atts['my_courses_wrapper_title'] ); ?></h2>
			<?php
		}

		do_action( 'eb_before_my_courses' );
		if ( ! is_user_logged_in() ) {
			?>
			<p>
				<?php
				/* Translators 1: My account url */
				printf(
					esc_html__( 'You are not logged in. ', 'eb-textdomain' ) . '%s' . esc_html__( ' to login.', 'eb-textdomain' ),
					"<a href='" . esc_url( site_url( '/user-account' ) ) . "'>" . esc_html__( 'Click here', 'eb-textdomain' ) . '</a>'
				);
				?>
			</p>
			<?php
		} elseif ( count( $my_courses ) ) {

			// My Courses.
			$args = array(
				'post_type'           => 'eb_course',
				'post_status'         => 'publish',
				'post__in'            => $my_courses,
				'ignore_sticky_posts' => true,
				'posts_per_page'      => -1,
			);

			$courses = new \WP_Query( $args );

			echo "<div class='eb-my-course'>";
			if ( $courses->have_posts() ) {
				while ( $courses->have_posts() ) :
					$courses->the_post();
					$template_loader->wp_get_template(
						'content-eb_course.php',
						array(
							'is_eb_my_courses' => true,
							'attr'             => $atts,
						)
					);
				endwhile;
			} else {
				$template_loader->wp_get_template_part( 'content', 'none' );
			}
			echo '</div>';
		} else {

			$eb_general_settings = get_option( 'eb_general' );
			if ( isset( $eb_general_settings['eb_my_course_link'] ) && ! empty( $eb_general_settings['eb_my_course_link'] ) ) {
				$link = $eb_general_settings['eb_my_course_link'];
			} else {
				$link = site_url( '/courses' );
			}
			?>
			<h5>
				<?php
				/* Translators 1: URL */
				printf(
					esc_html__( 'You are not enrolled to any course. ', 'eb-textdomain' ) . '%s' . esc_html__( ' to access the courses page.', 'eb-textdomain' ),
					"<a href='" . esc_html( $link ) . "'>" . esc_html__( 'Click here', 'eb-textdomain' ) . '</a>'
				);
				?>
			</h5>
			<?php
		}
		do_action( 'eb_after_my_courses' );
		echo '</div>';
	}

	/**
	 * Functionality to return the recommended categories for the recommended courses.
	 *
	 * @param text $user_courses user_courses.
	 * @since  1.3.4
	 */
	public function get_recommended_categories( $user_courses ) {
		// Recommended Courses.
		$rec_cats = array();
		foreach ( $user_courses as $user_course_id ) {
			$terms = wp_get_post_terms( $user_course_id, 'eb_course_cat' );
			foreach ( $terms as $term ) {
				$rec_cats[ $term->slug ] = $term->name;
			}
		}
		return $rec_cats;
	}

	/**
	 * Recomended courses.
	 *
	 * @param  string $rec_cats        Recommended categories for the courses.
	 * @param  string $exclude_courses which courses should be excluded from the recommended courses sestion.
	 * @param  string $count           No of courses shown in the recommended section.
	 * @param  string $atts            This array contains the attrivutes sent from the shortcode.
	 * @param  string $args            Wp-Query.
	 */
	public function show_recommended_courses( $rec_cats = '', $exclude_courses = '', $count = '', $atts = '', $args = '' ) {
		if ( '' === $args ) {
			$args = $this->create_query( $count, $rec_cats, $exclude_courses );
		}
		$courses = new \WP_Query( $args );

		$template_loader = new EbTemplateLoader(
			edwiser_bridge_instance()->get_plugin_name(),
			edwiser_bridge_instance()->get_version()
		);

		if ( $courses->have_posts() ) {
			echo '<div class="eb-rec-courses-wrapper">';
			if ( ! empty( $atts['recommended_courses_wrapper_title'] ) ) {
				?>
				<h2><?php echo esc_html( $atts['recommended_courses_wrapper_title'] ); ?></h2>
				<?php
			}
			do_action( 'eb_before_recommended_courses' );
			echo '<div class="eb-rec-courses">';

			while ( $courses->have_posts() ) :
				$courses->the_post();
				$template_loader->wp_get_template_part( 'content', 'eb_course' );
			endwhile;
			do_action( 'eb_after_recommended_courses' );
			echo '</div> </div>';
			$eb_course     = get_post_type_object( 'eb_course' );
			$view_more_url = site_url( $eb_course->rewrite['slug'] );
			?>
			<a href="<?php echo esc_html( $view_more_url ); ?>" class="wdm-btn eb-rec-courses-view-more">
				<?php esc_html_e( 'View More &rarr;', 'eb-textdomain' ); ?>
			</a>
			<?php
		} else {
			$template_loader->wp_get_template_part( 'content', 'none' );
		}
	}



	/**
	 * FUnction used to create wp-query according to the backend options
	 *
	 * @since  1.3.4
	 * @param  [type] $count           No of courses shown in the recommended section.
	 * @param  [type] $rec_cats        Recommended categories for the courses.
	 * @param  [type] $exclude_courses which courses should be excluded from the recommended courses sestion.
	 * @return [type]                  Wp-query
	 */
	public function create_query( $count, $rec_cats, $exclude_courses ) {
		$eb_general_setings = get_option( 'eb_general' );
		$args               = array();
		if ( isset( $eb_general_setings['eb_show_default_recmnd_courses'] ) && 'yes' === $eb_general_setings['eb_show_default_recmnd_courses'] ) {
			$args = array(
				'post_type'      => 'eb_course',
				'post_status'    => 'publish',
				'posts_per_page' => $count,
				'tax_query'      => array(
					array(
						'taxonomy' => 'eb_course_cat',
						'field'    => 'slug',
						'terms'    => array_keys( $rec_cats ),
					),
				),
				'post__not_in'   => $exclude_courses,
			);
		} elseif ( isset( $eb_general_setings['eb_recmnd_courses'] ) && ! empty( $eb_general_setings['eb_recmnd_courses'] ) ) {
			$args = array(
				'post_type'      => 'eb_course',
				'post_status'    => 'publish',
				'posts_per_page' => $count,
				'post__in'       => $eb_general_setings['eb_recmnd_courses'],
			);
		}
		return $args;
	}

	/**
	 * Function to create a custom wp query which created on the basis of the category or the custom courses selected in setting
	 *
	 * @since  1.3.4
	 */
	public function generate_recommended_courses() {
		global $post;
		$course_options                            = get_post_meta( $post->ID, 'eb_course_options', true );
		$attr['recommended_courses_wrapper_title'] = esc_html__( 'Recommended Courses', 'eb-textdomain' );

		if ( isset( $course_options['enable_recmnd_courses'] ) && 'yes' === $course_options['enable_recmnd_courses'] ) {
			if ( isset( $course_options['show_default_recmnd_course'] ) && 'yes' === $course_options['show_default_recmnd_course'] ) {
				$args = array(
					'post_type'      => 'eb_course',
					'post_status'    => 'publish',
					'posts_per_page' => 4,
					'tax_query'      => array(
						array(
							'taxonomy' => 'eb_course_cat',
							'field'    => 'slug',
							'terms'    => $this->get_recommended_categories( array( $post->ID ) ),
						),
					),
					'post__not_in'   => array( $post->ID ),
				);
				$this->show_recommended_courses( '', '', '', $attr, $args );
			} elseif ( isset( $course_options['enable_recmnd_courses_single_course'] ) && ! empty( $course_options['enable_recmnd_courses_single_course'] ) ) {
				$args = array(
					'post_type'   => 'eb_course',
					'post_status' => 'publish',
					'post__in'    => $course_options['enable_recmnd_courses_single_course'],
				);
				$this->show_recommended_courses( '', '', '', $attr, $args );
			}
		}
	}
}
