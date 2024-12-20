<?php
/**
 * Shortcode eb_courses.
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
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
					'order'               => 'ASC',
					'group_by_cat'        => 'no',
					'cat_per_page'        => '4', // -1 for all in one row
					'horizontally_scroll' => 'no',
					'per_page'            => 10,
					'show_filter'         => 'no',
				)
			),
			$atts
		);
		if ( isset( $atts['categories'] ) ) {
				$categories = $atts['categories'];
		}
		if ( isset( $atts['order'] ) ) {
				$order = $atts['order'];
		}
		if ( isset( $atts['group_by_cat'] ) ) {
				$group_by_cat = $atts['group_by_cat'];
		}
		if ( isset( $atts['cat_per_page'] ) ) {
				$cat_per_page = $atts['cat_per_page'];
		}
		if ( isset( $atts['horizontally_scroll'] ) ) {
				$horizontally_scroll = $atts['horizontally_scroll'];
		}
		if ( isset( $atts['per_page'] ) ) {
				$per_page = $atts['per_page'];
		}

		// Course Filter related intilization.
		$filter    = '';
		$sorting   = '';
		$tax_query = array();
		$order_by  = '';
		$page      = 1;

		// Get filter data here.
		if ( isset( $_REQUEST['eb_courses_page_key'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['eb_courses_page_key'] ) ), 'eb_courses_page_key' ) ) {
			$page = isset( $_GET['eb-cat-page-no'] ) ? sanitize_text_field( wp_unslash( $_GET['eb-cat-page-no'] ) ) : 1;
			if ( 'yes' === $atts['show_filter'] ) {
				$filter  = isset( $_REQUEST['eb_category_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['eb_category_filter'] ) ) : '';
				$sorting = isset( $_REQUEST['eb_category_sort'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['eb_category_sort'] ) ) : '';
			}
		}

		$args = array(
			'post_type'      => 'eb_course',
			'orderby'        => 'title',
			'order'          => $atts['order'],
			'post_status'    => 'publish',
			'posts_per_page' => $atts['per_page'],
		);

		/**
		 * Get all ctegorys from shortcode
		 */
		$input_cat = explode( ',', $atts['categories'] );
		if ( ! empty( $atts['categories'] ) ) {
			$args['tax_query'] = array( // @codingStandardsIgnoreLine
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
			$scroll_horizontal      = true;
			$args['posts_per_page'] = -1;
		}

		/*
		 * Check if the shortcode attr for show filter is set.
		 */
		if ( 'yes' === $atts['show_filter'] ) {
			/*
			* get sorting data and merge it with the wp_query args.
			*/
			$args = apply_filters( 'eb_courses_wp_query_args', $args, $sorting );

			// Functionality to show filters and sorting dropdowns.
			do_action( 'eb_show_course_page_filter_and_sorting', $filter, $sorting );
		}

		/**
		 * It will check whether to display courses page output in categorys grouping ot not
		 * If in shortcode parameter it is spesified group_by_cat parameter value
		 * true then shows courses in category groups.
		 */
		if ( ( isset( $atts['group_by_cat'] ) && 'yes' === $atts['group_by_cat'] ) || ( ! empty( $filter ) && 'eb_archive_filter_all' !== $filter ) ) {
			$disp_cat = $curr_class->showCatView( $input_cat, $filter );

			/*
			* Check if the shortcode attr for show filter is set.
			*/
			if ( 'yes' === $atts['show_filter'] ) {
				// Filter to apply changes according to the selected filter from eb_course page.
				$disp_cat = apply_filters( 'eb_courses_filter_args', $disp_cat, $filter );
			}

			$cat_cnt                = count( $disp_cat );
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
					<span class="eb-cat-title"><?php echo esc_html( $category->name ); ?></span>
					<?php
					$args['tax_query'] = array( // @codingStandardsIgnoreLine
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

			$curr_class->catPagination( $cat_cnt, $atts['cat_per_page'], $page, $filter, $sorting );
		} else {
			if ( ! $scroll_horizontal ) {
				$args['posts_per_page'] = $atts['per_page'];
				$args['paged']          = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			}

			$curr_class->genCoursesGridView( $args, $scroll_horizontal );
		}
	}


	/**
	 * It will  check which categorys need to show in the shortcode output
	 *
	 * @deprecated
	 * @param array  $input_cat Array of the category slugs to display courses from those categorys only.
	 * @param string $filter Array of the category slugs to display courses from those categorys only.
	 * @return array returns array of the categorys object
	 */
	public function showCatView( $input_cat, $filter ) {
		$cat_to_display  = array();
		$first_input_cat = trim( $input_cat[0] );
		$eb_all_cat      = get_terms( array( 'taxonomy' => 'eb_course_cat' ) );

		/**
		 * Check is the no category spesified in the shortcode parameter or emapy slug provided
		 */
		if ( count( $input_cat ) < 2 && empty( $first_input_cat ) ) {
			$eb_all_cat = get_terms( array( 'taxonomy' => 'eb_course_cat' ) );
			return $eb_all_cat;
		}

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
	 * @deprecated
	 * @param int    $cat_cnt total category count.
	 * @param int    $per_page Categorys to display on each page.
	 * @param int    $current_page current page number shown in output.
	 * @param string $filter category filter selected on page.
	 * @param string $sorting sorting filter selected on the page.
	 *
	 * @return HTML returns the html output for the pagination.
	 */
	public function catPagination( $cat_cnt, $per_page, $current_page = 1, $filter = '', $sorting = '' ) {
		/**
		 * Check is the cat is less than perpage ammount
		 * If yes then don't show pagination.
		 */

		if ( $cat_cnt <= $per_page ) {
			return;
		}

		$nonce = wp_create_nonce( 'eb_courses_page_key' );

		ob_start();
		?>
		<nav class="navigation pagination" role="navigation">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Courses navigation', 'edwiser-bridge' ); ?></h2>
			<form>
				<input type="hidden" name="eb_courses_page_key" value="<?php esc_html( wp_create_nonce( 'eb_courses_page_key' ) ); ?>"> 
				<div class="nav-links">
					<?php
					$page = 1;
					if ( 1 !== $current_page ) {

						?>
						<a class="prev page-numbers eb_primary_btn button button-primary et_pb_button et_pb_contact_submit" href="
						<?php
						echo esc_html(
							add_query_arg(
								array(
									'eb_category_filter'  => $filter,
									'eb_category_sort'    => $sorting,
									'eb-cat-page-no'      => $current_page - 1,
									'eb_courses_page_key' => $nonce,
								),
								get_permalink()
							)
						);
						?>
						">
							<?php esc_html_e( 'Prev', 'edwiser-bridge' ); ?>
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
							<a class="page-numbers eb-page-numbers" href="
							<?php
							echo esc_html(
								add_query_arg(
									array(
										'eb_category_filter' => $filter,
										'eb_category_sort' => $sorting,
										'eb-cat-page-no'   => $page,
										'eb_courses_page_key' => $nonce,
									),
									get_permalink()
								)
							);
							?>
							">
								<?php echo esc_html( $page ); ?>
							</a>
							<?php
						}
						$page++;
					}
					if ( $current_page < $page - 1 ) {

						?>
						<a class="next page-numbers eb_primary_btn button button-primary et_pb_button et_pb_contact_submit" href="
						<?php
						echo esc_html(
							add_query_arg(
								array(
									'eb_category_filter'  => $filter,
									'eb_category_sort'    => $sorting,
									'eb-cat-page-no'      => $current_page <= 1 ? 2 : $current_page + 1,
									'eb_courses_page_key' => $nonce,
								),
								get_permalink()
							)
						);
						?>
						">
							<?php esc_html_e( 'Next', 'edwiser-bridge' ); ?>
						</a>
						<?php
					}
					?>
				</div>
			</form>
		</nav>
		<?php
		ob_get_flush();
	}

	/**
	 * This will print the courses list.
	 *
	 * @deprecated
	 * @param Array   $args Get courses posts selection parameters.
	 * @param boolean $group_by_cat group the courses by categorys or not.
	 */
	public function genCoursesGridView( $args, $group_by_cat ) {
		$scroll_class = 'sc-eb_courses-wrapper eb_course_cards_wrap';
		if ( $group_by_cat ) {
			$scroll_class = 'eb-cat-courses-cont sc-eb_courses-wrapper';
		} else {
			?>
			<div class='eb-cat-parent'>
			<?php
		}
		$custom_query = new \WP_Query( $args );

		// Pagination fix.
		$wp_query = $custom_query;

		$template_loader = new Eb_Template_Loader(
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

			if ( ! $group_by_cat ) {
				// CLosing category parent div.
				?>
				</div>
				<?php
			}

			?>
			<div style="clear:both"></div>
			<?php
			$template_loader->wp_get_template(
				'course-pagination.php',
				array(
					'max_num_pages' => $custom_query->max_num_pages,
				)
			);
			do_action( 'eb_after_course_archive' );
			if ( $group_by_cat ) {
				?>
				<span class="fa fa-angle-right eb-scroll-right" id="eb-scroll-right"></span>
			<?php } ?>
		</div>
		<?php
	}
}
