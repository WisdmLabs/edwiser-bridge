<?php
/**
 * EDW Connection Settings
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Settings_Shortcode_Doc' ) ) {

	/**
	 * Eb_Settings_Shortcode_Doc.
	 */
	class Eb_Settings_Shortcode_Doc extends EBSettingsPage {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'shortcode_doc';
			$this->label = __( 'Shortcodes', 'eb-textdomain' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			$GLOBALS['hide_save_button'] = true;
			do_action( 'eb_befor_shortcode_doc' );
			echo esc_html( $this->get_documentation() );
			do_action( 'eb_after_shortcode_doc' );
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.0.0
		 *
		 * @return array
		 */
		public function get_documentation() {
			ob_start();
			?>
			<h3><?php esc_html_e( 'Edwiser Bridge Shortcodes', 'eb-textdomain' ); ?></h3>
			<div class="eb-shortcode-doc-wpra">
				<div class="eb-shortcode-doc">
					<h3><?php esc_html_e( 'Shortcode Options', 'eb-textdomain' ); ?> </h3>
					<div class="eb-shortcode-doc-desc">
						<p><?php esc_html_e( 'You may use shortcodes to add information to any page/course/lesson/quiz. Here are built-in shortcodes for displaying relavent user information.', 'eb-textdomain' ); ?></p>
					</div>
				</div>
				<div class="eb-shortcode-doc">
					<h4>[eb_user_account]</h4>
					<div class="eb-shortcode-doc-desc">
						<p><?php esc_html_e( 'This shortcode shows the user account details,his enrolled courses and orders placed by him. This shortcode also provides the functoinality to edit user profile.', 'eb-textdomain' ); ?></p>
					</div>
				</div>
				<div class="eb-shortcode-doc">
					<h4>[eb_courses]</h4>
					<div class="eb-shortcode-doc-desc">
						<p><?php esc_html_e( 'This shortcode shows the list of the edwiser bridge courses. You can use this shortcode on any page. This shortcode can take following parameters:', 'eb-textdomain' ); ?></p>
						<ul>
							<li>
								<span class="eb_shortcode-doc-para">order</span>: 
								<?php

									/*
									* translators: shortcode description example.
									*/
									printf( esc_html__( 'Sets order of courses. Possible values: DESC, ASC. Example: %s shows courses in ascending order.', 'eb-textdomain' ), '<strong>[eb_courses order="ASC"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">per_page</span>:
								<?php

								/*
								* translators: shortcode description example.
								*/
								printf( esc_html__( 'Sets number of courses per page. Example: %s', 'eb-textdomain' ), '<strong>[eb_courses per_page="10"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">categories</span>:
								<?php

								/*
								* translators: shortcode description example.
								*/
								printf( esc_html__( 'Shows courses from spesified category slugs. Example: %s', 'eb-textdomain' ), '<strong>[eb_courses categories="basic,moderated"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">cat_per_page</span>:
								<?php

								/*
								* translators: shortcode description example.
								*/
								printf( esc_html__( 'Sets number of categorys groups shown per page Example: %s', 'eb-textdomain' ), '<strong>[eb_courses cat_per_page="3"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">group_by_cat</span>:
								<?php

								/*
								* translators: shortcode description example.
								*/
								printf( esc_html__( 'This shows the courses grouped by the categorys. Possible values:yes,no. Example: %s', 'eb-textdomain' ), '<strong>[eb_courses group_by_cat="yes"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">horizontally_scroll</span>:
								<?php

								/*
								* translators: shortcode description example.
								*/
								printf( esc_html__( 'This will shows the courses in one row with horizontal scroll. Possible values:yes,no. Example: %s', 'eb-textdomain' ), '<strong>[eb_courses horizontally_scroll="yes"]</strong>' );
								?>
							</li>
						</ul>
					</div>
				</div>
				<div class="eb-shortcode-doc">
					<h4>[eb_course]</h4>
					<div class="eb-shortcode-doc-desc">
						<p>
						<?php

						/*
						 * translators: shortcode description example.
						 */
						printf( esc_html__( 'This shortcode shows single course page.This shortcode takes course id as a parameter. Example: %s', 'eb-textdomain' ), '[eb_course id="10"]' );
						?>
						</p>
					</div>
				</div>
				<div class="eb-shortcode-doc">
					<h4>[eb_my_courses]</h4>
					<div class="eb-shortcode-doc-desc">
						<p><?php esc_html_e( 'This shortcode shows the users enrolled courses list and the courses from the enrolled courses categorys where user is not enrolled as the recommended courses. This shortcode can take following parameters.', 'eb-textdomain' ); ?></p>
						<ul>
							<li>
								<span class="eb_shortcode-doc-para">my_courses_wrapper_title</span>:
								<?php

								/*
								* translators: shortcode description example.
								*/
								printf( esc_html__( 'This will sets the title for the courses wrapper Example: %s', 'eb-textdomain' ), '<strong>[eb_my_courses my_courses_wrapper_title="My Courses"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">recommended_courses_wrapper_title</span>:
								<?php

								/*
								* translators: shortcode description example.
								*/
								printf( esc_html__( 'This will sets the title for the courses wrapper Example: %s', 'eb-textdomain' ), '<strong>[eb_my_courses recommended_courses_wrapper_title="Recommended courses"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">number_of_recommended_courses</span>:
								<?php

								/*
								* translators: shortcode description example.
								*/
								printf( esc_html__( 'This will sets the quntity to show in the recommended courses Example: %s', 'eb-textdomain' ), '<strong>[eb_my_courses number_of_recommended_courses="4"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">my_courses_progress</span>:
								<?php

								/*
								* translators: shortcode description example.
								*/
								printf( esc_html__( 'This will show the course progress if it is set 1 and will hide course progress if set to 0 and if the parameter is not set', 'eb-textdomain' ), '<strong>[eb_my_courses number_of_recommended_courses="4"]</strong>' );
								?>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}
	}
}

return new Eb_Settings_Shortcode_Doc();
