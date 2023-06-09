<?php
/**
 * EDW Connection Settings
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Settings_Shortcode_Doc' ) ) {

	/**
	 * Eb_Settings_Shortcode_Doc.
	 */
	class Eb_Settings_Shortcode_Doc extends EB_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'shortcode_doc';
			$this->label = __( 'Shortcodes', 'edwiser-bridge' );

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
			echo wp_kses_post( $this->get_documentation() );
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
			<h3><?php esc_html_e( 'Edwiser Bridge Shortcodes', 'edwiser-bridge' ); ?></h3>
			<div class="eb-shortcode-doc-wpra">
				<div class="eb-shortcode-doc">
					<h3><?php esc_html_e( 'Shortcode Options', 'edwiser-bridge' ); ?> </h3>
					<div class="eb-shortcode-doc-desc">
						<p><?php esc_html_e( 'You may use shortcodes to add information to any page/course/lesson/quiz. Here are built-in shortcodes for displaying relavent user information.', 'edwiser-bridge' ); ?></p>
					</div>
				</div>
				<div class="eb-shortcode-doc">
					<h4>[eb_user_account]</h4>
					<div class="eb-shortcode-doc-desc">
						<p><?php esc_html_e( 'This shortcode shows the user account details,his enrolled courses and orders placed by him. This shortcode also provides the functoinality to edit user profile.', 'edwiser-bridge' ); ?></p>
					</div>
				</div>
				<div class="eb-shortcode-doc">
					<h4>[eb_courses]</h4>
					<div class="eb-shortcode-doc-desc">
						<p><?php esc_html_e( 'This shortcode shows the list of the edwiser bridge courses. You can use this shortcode on any page. This shortcode can take following parameters:', 'edwiser-bridge' ); ?></p>
						<ul>
							<li>
								<span class="eb_shortcode-doc-para">order</span>: 
								<?php
									/**
									 * Translators: shortcode description example.
									 */
									printf( esc_html__( 'Sets order of courses. Possible values: DESC, ASC. Example: ', 'edwiser-bridge' ) . '%s ' . esc_html__( 'shows courses in ascending order.', 'edwiser-bridge' ), '<strong>[eb_courses order="ASC"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">per_page</span>:
								<?php
								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'Sets number of courses per page. Example:', 'edwiser-bridge' ) . ' %s', '<strong>[eb_courses per_page="10"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">categories</span>:
								<?php
								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'Shows courses from spesified category slugs. Example: ', 'edwiser-bridge' ) . '%s', '<strong>[eb_courses categories="basic,moderated"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">cat_per_page</span>:
								<?php

								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'Sets number of categorys groups shown per page Example:', 'edwiser-bridge' ) . ' %s', '<strong>[eb_courses cat_per_page="3"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">group_by_cat</span>:
								<?php

								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'This shows the courses grouped by the categorys. Possible values:yes,no. Example:', 'edwiser-bridge' ) . ' %s', '<strong>[eb_courses group_by_cat="yes"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">horizontally_scroll</span>:
								<?php

								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'This will shows the courses in one row with horizontal scroll. Possible values:yes,no. Example: ', 'edwiser-bridge' ) . '%s', '<strong>[eb_courses horizontally_scroll="yes"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">show_filter</span>:
								<?php

								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'This will show category wise filter and sorting section on page. Possible values:yes,no. Example: ', 'edwiser-bridge' ) . '%s', '<strong>[eb_courses show_filter="yes"]</strong>' );
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

						/**
						 * Translators: shortcode description example.
						 */
						printf( esc_html__( 'This shortcode shows single course page.This shortcode takes course id as a parameter. Example: ', 'edwiser-bridge' ) . '%s', '[eb_course id="10"]' );
						?>
						</p>
					</div>
				</div>
				<div class="eb-shortcode-doc">
					<h4>[eb_my_courses]</h4>
					<div class="eb-shortcode-doc-desc">
						<p><?php esc_html_e( 'This shortcode shows the users enrolled courses list and the courses from the enrolled courses categorys where user is not enrolled as the recommended courses. This shortcode can take following parameters.', 'edwiser-bridge' ); ?></p>
						<ul>
							<li>
								<span class="eb_shortcode-doc-para">my_courses_wrapper_title</span>:
								<?php

								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'This will sets the title for the courses wrapper Example: ', 'edwiser-bridge' ) . '%s', '<strong>[eb_my_courses my_courses_wrapper_title="My Courses"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">recommended_courses_wrapper_title</span>:
								<?php

								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'This will sets the title for the courses wrapper Example:', 'edwiser-bridge' ) . ' %s', '<strong>[eb_my_courses recommended_courses_wrapper_title="Recommended courses"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">number_of_recommended_courses</span>:
								<?php

								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'This will sets the quntity to show in the recommended courses Example:', 'edwiser-bridge' ) . ' %s', '<strong>[eb_my_courses number_of_recommended_courses="4"]</strong>' );
								?>
							</li>
							<li>
								<span class="eb_shortcode-doc-para">my_courses_progress</span>:
								<?php

								/**
								 * Translators: shortcode description example.
								 */
								printf( esc_html__( 'This will show the course progress if it is set 1 and will hide course progress if set to 0 and if the parameter is not set', 'edwiser-bridge' ), '<strong>[eb_my_courses number_of_recommended_courses="4"]</strong>' );
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
