<?php
/**
 * Shortcode eb_courses.
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 * @package    Edwiser Bridge
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

/**
 * Courses.
 */
class Eb_Shortcode_Courses {


	/**
	 * Get the shortcode content.
	 *
	 * @since  1.2.0
	 *
	 * @param array $atts atta.
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
		$atts = shortcode_atts(
			apply_filters(
				'eb_shortcode_courses_defaults',
				array(
					'categories'          => '',
					'order'               => 'DESC',
					'group_by_cat'        => 'no',
					'cat_per_page'        => '4', // -1 for all in one row
					'horizontally_scroll' => 'no',
					'per_page'            => 10,
				)
			),
			$atts
		);
		// extract( $atts );

		$args = array(
			'post_type'      => 'eb_course',
			'order'          => $atts['order'],
			'post_status'    => 'publish',
			'posts_per_page' => $atts['per_page'],
		);

		/**
		 * Get all ctegorys from shortcode
		 */
		$input_cat = explode( ',', $atts['categories'] );
		if ( ! empty( $atts['categories'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'eb_course_cat',
					'field'    => 'slug',
					'terms'    => $input_cat,
				),
			);
		}
		/**
		 * Create class of current object
		 */
		$curr_class = new Eb_Shortcode_Courses();
		/**
		 * Initialize horizontal scroll defualt is false
		 * It takes boolean value
		 */
		$scroll_horizontal = false;
		/**
		 * Check is horizintal scroll spesified in shortcoe and set value
		 */
		if ( isset( $atts['horizontally_scroll'] ) && 'yes' === $atts['horizontally_scroll'] ) {
			$scroll_horizontal       = true;
			$args['posts_per_page'] = -1;
		}

		/**
		 * It will check whether to display courses page output in categorys grouping ot not
		 * If in shortcode parameter it is spesified group_by_cat parameter value
		 * true then shows courses in category groups.
		 */
		if ( isset( $atts['group_by_cat'] ) && 'yes' === $atts['group_by_cat'] ) {
			$disp_cat = $curr_class->showCatView( $input_cat );
			$cat_cnt  = count( $disp_cat );
			$page     = 1;
			if ( isset( $_GET['eb-cat-page-no'] ) ) {
				$page = sanitize_text_field( wp_unslash( $_GET['eb-cat-page-no'] ) );
			}
			$cat_start              = $page * (int) $atts['cat_per_page'] - (int) $atts['cat_per_page'];
			$cnt                    = 0;
			$args['posts_per_page'] = -1;
			foreach ( $disp_cat as $category ) {
				$cnt++;
				if ( $cnt < $cat_start + 1 || $cnt > $cat_start + (int) $atts['cat_per_page'] ) {
					continue;
				}
				?>
				<div class='eb-cat-parent'>
					<h3 class="eb-cat-title"><?php echo esc_html( $category->name ); ?></h3>
					<?php
					$args['tax_query'] = array(
						array(
							'taxonomy'         => 'eb_course_cat',
							'field'            => 'slug',
							'include_children' => false,
							'terms'            => $category->slug,
						),
					);
					$curr_class->genCoursesGridView( $args, $scroll_horizontal );
					?>
				</div>
				<?php
			}
			$curr_class->catPagination( $cat_cnt, $atts['cat_per_page'], $page );
		} else {
			if ( ! $scroll_horizontal ) {
				$args['posts_per_page'] = $atts['per_page'];
				$args['paged']          = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			}
			?>
			<div class='eb-cat-parent'>
				<?php
				$curr_class->genCoursesGridView( $args, $scroll_horizontal );
				?>
			</div>
			<?php
		}
	}

	/**
	 * It will  check which categorys need to show in the shortcode output
	 *
	 * @param array $input_cat Array of the category slugs to display courses from those categorys only.
	 * @return array returns array of the categorys object
	 */
	public function showCatView( $input_cat ) {
		$eb_all_cat      = get_terms( array( 'taxonomy' => 'eb_course_cat' ) );
		$first_input_cat = trim( $input_cat[0] );
		/**
		 * Check is the no category spesified in the shortcode parameter or emapy slug provided
		 */
		if ( count( $input_cat ) < 2 && empty( $first_input_cat ) ) {
			return $eb_all_cat;
		}

		$cat_to_display = array();
		foreach ( $eb_all_cat as $cat ) {
			if ( in_array( $cat->slug, $input_cat ) ) { // @codingStandardsIgnoreLine.
				$cat_to_display[] = $cat;
			}
		}
		return $cat_to_display;
	}

	/**
	 * Genrates the pagination for the category view
	 *
	 * @param int $cat_cnt total category count.
	 * @param int $per_page Categorys to display on each page.
	 * @param int $current_page current page number shown in output.
	 * @return HTML returns the html output for the pagination.
	 */
	public function catPagination( $cat_cnt, $per_page, $current_page = 1 ) {
		/**
		 * Check is the cat is less than per_page ammount
		 * If yes then don't show pagination.
		 */
		if ( $cat_cnt <= $per_page ) {
			return;
		}
		ob_start();
		?>
		<nav class="navigation pagination" role="navigation">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Courses navigation', 'eb-textdomain' ); ?></h2>
			<div class="nav-links">
				<?php
				$page = 1;
				if ( 1 !== $current_page ) {
					?>
					<a class="prev page-numbers" href="<?php echo esc_html( add_query_arg( array( 'eb-cat-page-no' => $current_page - 1 ), get_permalink() ) ); ?>">
						<?php esc_html_e( '&larr;', 'eb-textdomain' ); ?>
					</a>
					<?php
				}
				for ( $cnt = 1; $cnt <= $cat_cnt; $cnt += (int) $per_page ) {
					$page_id_css = 'page-numbers';
					if ( $page === $current_page ) {
						?>
						<span class="page-numbers current">
							<span class="meta-nav screen-reader-text">Page </span>
							<?php echo esc_html( $page ); ?>
						</span>
						<?php
						$page_id_css .= ' current';
					} else {
						?>
						<a class="page-numbers" href="<?php echo esc_html( add_query_arg( array( 'eb-cat-page-no' => $page ), get_permalink() ) ); ?>">
							<?php echo esc_html( $page ); ?>
						</a>
						<?php
					}
					$page++;
				}
				if ( $current_page < $page - 1 ) {
					?>
					<a class="next page-numbers" href="<?php echo esc_html( add_query_arg( array( 'eb-cat-page-no' => $current_page <= 1 ? 2 : $current_page + 1 ), get_permalink() ) ); ?>">
						<?php esc_html_e( '&rarr;', 'eb-textdomain' ); ?>
					</a>
					<?php
				}
				?>
			</div>
		</nav>
		<?php
		ob_get_flush();
	}

	/**
	 * This will print the courses list.
	 *
	 * @param Array   $args Get courses posts selection parameters.
	 * @param boolean $group_by_cat group the courses by categorys or not.
	 */
	public function genCoursesGridView( $args, $group_by_cat ) {
		$scroll_class = 'sc-eb_courses-wrapper';
		if ( $group_by_cat ) {
			$scroll_class = 'eb-cat-courses-cont sc-eb_courses-wrapper';
		}
		$custom_query = new \WP_Query( $args );

		// Pagination fix.
		$temp_query = isset( $wp_query ) ? $wp_query : null;
		$wp_query   = null;
		$wp_query   = $custom_query;

		$template_loader = new EbTemplateLoader(
			edwiser_bridge_instance()->get_plugin_name(),
			edwiser_bridge_instance()->get_version()
		);
		?>
		<div class="<?php echo esc_html( $scroll_class ); ?>">
			<?php if ( $group_by_cat ) { ?>
				<span class="fa fa-angle-left eb-scroll-left" id="eb-scroll-left"></span>
			<?php } ?>
			<?php
			do_action( 'eb_before_course_archive' );
			if ( $custom_query->have_posts() ) {
				while ( $custom_query->have_posts() ) :
					$custom_query->the_post();
					$template_loader->wp_get_template_part( 'content', 'eb_course' );
				endwhile;
			} else {
				$template_loader->wp_get_template_part( 'content', 'none' );
			}
			wp_reset_postdata();
			?>
			<div style="clear:both"></div>
			<?php
			$template_loader->wp_get_template(
				'course-pagination.php',
				array(
					'max_num_pages' => $custom_query->max_num_pages,
				)
			);
			// Reset main query object.
			$wp_query = null;
			$wp_query = $temp_query;
			do_action( 'eb_after_course_archive' );
			if ( $group_by_cat ) {
				?>
				<span class="fa fa-angle-right eb-scroll-right" id="eb-scroll-right"></span>
			<?php } ?>
		</div>
		<?php
	}
}
