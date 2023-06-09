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
					'recommended_courses_wrapper_title' => __( 'Recommended Courses', 'edwiser-bridge' ),
					'number_of_recommended_courses'     => 7,
					'my_courses_progress'               => '0',
				)
			),
			$atts
		);
		$current_class = new Eb_Shortcode_My_Courses();

		do_action( 'eb_before_my_courses_wrapper' );

		$my_courses = \app\wisdmlabs\edwiserBridge\eb_get_user_enrolled_courses( $atts['user_id'] );

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
	 * Show my courses.
	 *
	 * @param text $my_courses my_courses.
	 * @param text $atts usattser.
	 */
	public function show_my_courses( $my_courses, $atts ) {
		$template_loader = new Eb_Template_Loader(
			edwiser_bridge_instance()->get_plugin_name(),
			edwiser_bridge_instance()->get_version()
		);

		echo '<div class="eb-my-courses-wrapper">';
		if ( ! empty( $atts['my_courses_wrapper_title'] ) ) {
			?><span class="eb-my-courses-h2"><?php echo esc_html( $atts['my_courses_wrapper_title'] ); ?></span>
			<?php
		}

		do_action( 'eb_before_my_courses' );
		if ( ! is_user_logged_in() ) {
			?>
			<p>
				<?php
				/* Translators 1: My account url */
				printf(
					esc_html__( 'You are not logged in. ', 'edwiser-bridge' ) . '%s' . esc_html__( ' to login.', 'edwiser-bridge' ),
					"<a href='" . esc_url( \app\wisdmlabs\edwiserBridge\wdm_eb_user_account_url() ) . "'>" . esc_html__( 'Click here', 'edwiser-bridge' ) . '</a>'
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

			echo "<div class='eb-my-course eb_course_cards_wrap'>";
			if ( $courses->have_posts() ) {
				$course_progress_manager = new \app\wisdmlabs\edwiserBridge\Eb_Course_Progress();
				$progress_data           = $course_progress_manager->get_course_progress();
				$user_id                 = get_current_user_id();
				$mdl_uid                 = get_user_meta( $user_id, 'moodle_user_id', true );

				$atts['show_progress'] = true;
				while ( $courses->have_posts() ) :
					$courses->the_post();

					if ( $mdl_uid && isset( $atts['my_courses_progress'] ) && $atts['my_courses_progress'] ) {
						$course_prog_data         = $this->get_course_progress( get_the_ID(), $progress_data, $user_id, $atts, $mdl_uid );
						$atts['progress_btn_div'] = $course_prog_data['html'];
						$atts['completed']        = $course_prog_data['completed'];
					} else {
						$atts['progress_btn_div'] = '';
						$atts['completed']        = 0;
					}

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
				printf(
					/* Translators 1: URL */
					esc_html__( 'You are not enrolled to any course. ', 'edwiser-bridge' ) . '%s' . esc_html__( ' to access the courses page.', 'edwiser-bridge' ),
					"<a href='" . esc_html( $link ) . "'>" . esc_html__( 'Click here', 'edwiser-bridge' ) . '</a>'
				);
				?>
			</h5>
			<?php
		}
		do_action( 'eb_after_my_courses' );
		echo '</div>';
	}

	/**
	 * Return teh course progress div.
	 *
	 * @param int   $course_id The course id to calculate the progress.
	 * @param array $progress_data The progress data.
	 * @param int   $user_id currentuser id.
	 * @param array $attr attr attr.
	 * @param int   $moodle_user_id moodle_user_id.
	 */
	private function get_course_progress( $course_id, $progress_data, $user_id, $attr, $moodle_user_id ) {
		$course_ids         = array_keys( $progress_data );
		$is_user_suspended  = \app\wisdmlabs\edwiserBridge\wdm_eb_get_user_suspended_status( $user_id, $course_id );
		$progress           = isset( $progress_data[ $course_id ] ) ? $progress_data[ $course_id ] : 1;
		$progress_meta_data = __( 'Not available', 'edwiser-bridge' );
		$completed          = 0;
		$course_mang        = \app\wisdmlabs\edwiserBridge\edwiser_bridge_instance()->course_manager();
		$mdl_course_id      = $course_mang->get_moodle_course_id( $course_id );
		$course_url         = \app\wisdmlabs\edwiserBridge\wdm_eb_get_my_course_url( $moodle_user_id, $mdl_course_id );
		$progress_class     = 'eb-course-action-btn-start';
		$btn_text           = __( 'Start', 'edwiser-bridge' );
		ob_start();
		?>
		<div class='eb-course-action-cont'>
			<?php
			if ( $is_user_suspended ) {
				$progress_class = 'eb-course-action-btn-suspended';
				$btn_text       = __( 'Suspended', 'edwiser-bridge' );
			} elseif ( in_array( $course_id, $course_ids ) ) {// @codingStandardsIgnoreLine.
				if ( 0 === $progress ) {
					$progress_class     = 'eb-course-action-btn-start';
					$btn_text           = __( 'Start', 'edwiser-bridge' );
					$progress_meta_data = __( 'Not yet started', 'edwiser-bridge' );
				} elseif ( $progress > 0 && $progress < 100 ) {
					$progress_class     = 'eb-course-action-btn-resume';
					$btn_text           = __( 'Resume', 'edwiser-bridge' );
					$progress_meta_data = esc_attr( round( $progress ) ) . __( '% Completed', 'edwiser-bridge' );
				} else {
					$completed          = 1;
					$progress_class     = 'eb-course-action-btn-completed';
					$btn_text           = __( 'View', 'edwiser-bridge' );
					$progress_meta_data = __( '100% Completed', 'edwiser-bridge' );
				}
			}
			?>
			<div class='eb-course-progres-wrap'>

				<div class='eb-course-action-progress-cont'>
					<?php
					if ( isset( $attr['show_progress'] ) && $attr['show_progress'] ) {
						?>
						<div class='eb-course-action-progress' style='width:<?php echo esc_attr( round( $progress ) ); ?>%' ></div>
						<div class='eb-course-progress-status'> <?php echo esc_attr( $progress_meta_data ); ?> </div>
						<?php
					}
					?>
					<span  class="<?php echo esc_attr( $progress_class ); ?>">
						<?php echo esc_attr( $btn_text ); ?>
					</span>
				</div>

			</div>
		</div>
		<?php
		return array(
			'html'      => ob_get_clean(),
			'completed' => $completed,
		);
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

		$template_loader = new Eb_Template_Loader(
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
			echo '<div class="eb-rec-courses eb_course_cards_wrap">';

			while ( $courses->have_posts() ) {
				$courses->the_post();
				$template_loader->wp_get_template_part( 'content', 'eb_course' );
			}
			do_action( 'eb_after_recommended_courses' );
			echo '</div> </div>';

			$eb_settings = get_option( 'eb_general' );

			/*
			* Set the login redirect url to the user account page.
			*/
			if ( isset( $eb_settings['eb_courses_page_id'] ) ) {
				$courses_page_id = $eb_settings['eb_courses_page_id'];
				$view_more_url   = get_permalink( $courses_page_id );
			} else {
				$eb_course     = get_post_type_object( 'eb_course' );
				$view_more_url = site_url( $eb_course->rewrite['slug'] );
			}
			?>
			<a href="<?php echo esc_html( $view_more_url ); ?>" class="wdm-btn eb-rec-courses-view-more">
				<?php esc_html_e( 'View More &rarr;', 'edwiser-bridge' ); ?>
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
				'tax_query'      => array( // @codingStandardsIgnoreLine
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
		$attr['recommended_courses_wrapper_title'] = esc_html__( 'Recommended Courses', 'edwiser-bridge' );

		if ( isset( $course_options['enable_recmnd_courses'] ) && 'yes' === $course_options['enable_recmnd_courses'] ) {
			if ( isset( $course_options['show_default_recmnd_course'] ) && 'yes' === $course_options['show_default_recmnd_course'] ) {
				$args = array(
					'post_type'      => 'eb_course',
					'post_status'    => 'publish',
					'posts_per_page' => 4,
					'tax_query'      => array( // @codingStandardsIgnoreLine
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
