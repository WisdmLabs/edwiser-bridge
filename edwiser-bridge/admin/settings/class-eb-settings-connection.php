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

if ( ! class_exists( 'Eb_Settings_Connection' ) ) :

	/**
	 * Eb_Settings_Connection.
	 */
	class Eb_Settings_Connection extends EB_Settings_Page {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'connection';
			$this->label = __( 'Connection', 'edwiser-bridge' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
			add_action( 'eb_settings_save_' . $this->_id, array( $this, 'save' ) );
			add_action( 'eb_sections_' . $this->_id, array( $this, 'output_sections' ) );
		}

		/**
		 * Get sections.
		 *
		 * @since  2.2.1
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''           => __( 'Connection', 'edwiser-bridge' ),
				'enrollment' => __( 'Test Enrollment', 'edwiser-bridge' ),
			);

			return $sections;
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			global $current_section;

			if ( 'enrollment' === $current_section ) {
				$GLOBALS['hide_save_button'] = true;
			}

			$settings = $this->get_settings( $current_section );

			Eb_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings.
		 *
		 * @since  1.0.0
		 */
		public function save() {
			global $current_section;

			$settings = $this->get_settings( $current_section );
			Eb_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Get All Courses
		 *
		 * @return array
		 *
		 * @since  2.2.1
		 */
		public function get_courses() {
			$courses = array();

			$course_args = array(
				'post_type'      => 'eb_course',
				'post_status'    => 'publish', // remove this line to get all courses.
				'posts_per_page' => -1,
			);
			$all_courses = get_posts( $course_args );

			foreach ( $all_courses as $course ) {
				if ( Eb_Post_Types::get_post_options( $course->ID, 'mdl_course_deleted', 'eb_course' ) ) {

					continue;
				}
				$courses[ $course->ID ] = $course->post_title;
			}

			return $courses;
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.0.0
		 *
		 * @param text $current_section name of the section.
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			if ( 'enrollment' === $current_section ) {
				$settings = apply_filters(
					'test_enrollment_settings',
					array(
						array(
							'title' => __( 'Test Course Enrollment', 'edwiser-bridge' ),
							'type'  => 'title',
							'id'    => 'test_enrollment_options',
						),

						array(
							'title'             => __( 'Select Course', 'edwiser-bridge' ),
							'desc'              => __( 'Select a course to test enrollment.', 'edwiser-bridge' ),
							'id'                => 'eb_test_enrollment_course',
							'css'               => 'min-width:350px;',
							'default'           => __( 'Select Course', 'edwiser-bridge' ),
							'type'              => 'select',
							'desc_tip'          => true,
							'options'           => $this->get_courses(),
							'custom_attributes' => array( 'required' => 'required' ),
						),

						array(
							'title'    => '',
							'desc'     => '',
							'id'       => 'eb_test_enrollment_button',
							'default'  => __( 'Test Enrollment', 'edwiser-bridge' ),
							'type'     => 'button',
							'desc_tip' => false,
							'class'    => 'button secondary',
						),
						array(
							'html' => '<th></th><td>
							<h2 class="test-enrollment-heading"></h2>
							<ul class="enroll-progress">
								<li id="progress_settings">' . __( 'Mandatory Settings', 'edwiser-bridge' ) . '</li>
								<li id="progress_user">' . __( 'User Creation', 'edwiser-bridge' ) . '</li>
								<li id="progress_enroll">' . __( 'User Enrollment', 'edwiser-bridge' ) . '</li>
								<li id="progress_finish">' . __( 'Finish', 'edwiser-bridge' ) . '</li>
							</ul></td>',
							'type' => 'cust_html',
						),
						array(
							'html' => '<th></th><td> <div class="eb_test_enrollment_response"></div> </td>',
							'type' => 'cust_html',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'test_enrollment_options',
						),
					)
				);
			} else {
				$settings = apply_filters(
					'eb_connection_settings',
					array(
						array(
							'title' => __( 'Connection Settings', 'edwiser-bridge' ),
							'type'  => 'title',
							'id'    => 'connection_options',
						),

						array(
							'title'             => __( 'Moodle URL', 'edwiser-bridge' ),
							'desc'              => __(
								'Moodle URL ( Like: http://example.com or http://example.com/moodle etc.)',
								'edwiser-bridge'
							),
							'id'                => 'eb_url',
							'css'               => 'min-width:350px;',
							'default'           => '',
							'type'              => 'url',
							'desc_tip'          => true,
							'custom_attributes' => array( 'required' => 'required' ),
						),

						array(
							'title'             => __( 'Moodle Access Token', 'edwiser-bridge' ),
							'desc'              => __( 'Add the access token generated on the Moodle Site while creating a web service.', 'edwiser-bridge' ),
							'id'                => 'eb_access_token',
							'css'               => 'min-width:350px;',
							'default'           => '',
							'type'              => 'text',
							'desc_tip'          => true,
							'custom_attributes' => array( 'required' => 'required' ),
						),

						array(
							'title'    => '',
							'desc'     => '',
							'id'       => 'eb_test_connection_button',
							'default'  => __( 'Test Connection', 'edwiser-bridge' ),
							'type'     => 'button',
							'desc_tip' => false,
							'class'    => 'button secondary',
						),
						array(
							'html' => '<th></th><td> <div class="eb_test_connection_response"></div> </td>',
							'type' => 'cust_html',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'connection_options',
						),
					)
				);
			}

			return apply_filters( 'eb_get_settings_' . $this->_id, $settings, $current_section );
		}
	}

endif;

return new Eb_Settings_Connection();
