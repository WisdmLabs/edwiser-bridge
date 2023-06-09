<?php
/**
 * EDW Product Settings
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

if ( ! class_exists( 'Eb_Settings_Synchronization' ) ) {

	/**
	 * Eb_Settings_Synchronization.
	 */
	class Eb_Settings_Synchronization extends EB_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'synchronization';
			$this->label = __( 'Synchronization', 'edwiser-bridge' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
			add_action( 'eb_settings_save_' . $this->_id, array( $this, 'save' ) );
			add_action( 'eb_sections_' . $this->_id, array( $this, 'output_sections' ) );
		}

		/**
		 * Get sections.
		 *
		 * @since  1.0.0
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''          => __( 'Courses', 'edwiser-bridge' ),
				'user_data' => __( 'Users', 'edwiser-bridge' ),
			);

			$new_sections = apply_filters( 'eb_get_sections_' . $this->_id, $sections );
			if ( is_array( $new_sections ) ) {
				$sections = array_merge( $sections, $new_sections );
			}

			$new_sections = apply_filters_deprecated( 'eb_getSections_' . $this->_id, array( $sections ), '5.5', 'eb_get_sections_' . $this->_id );
			if ( is_array( $new_sections ) ) {
				$sections = array_merge( $sections, $new_sections );
			}

			return $sections;
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			global $current_section;

			// Hide the save button.
			$GLOBALS['hide_save_button'] = true;

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
		 * Get settings array.
		 *
		 * @since  1.0.0
		 *
		 * @param string $current_section name of the current section.
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			if ( 'user_data' === $current_section ) {
				$settings = apply_filters(
					'eb_user_synchronization_settings',
					array(
						array(
							'title' => __( 'Synchronize User Data', 'edwiser-bridge' ),
							'type'  => 'title',
							'id'    => 'user_synchronization_options',
						),
						array(
							'title'           => __( 'Synchronization Options', 'edwiser-bridge' ),
							'desc'            => __( 'Update user\'s course enrollment status', 'edwiser-bridge' ),
							'id'              => 'eb_synchronize_user_courses',
							'default'         => 'no',
							'type'            => 'checkbox',
							'checkboxgroup'   => 'start',
							'show_if_checked' => 'option',
							'autoload'        => false,
						),
						array(
							'desc'            => __( 'Link user\'s account to moodle', 'edwiser-bridge' ),
							'id'              => 'eb_link_users_to_moodle',
							'default'         => 'no',
							'type'            => 'checkbox',
							'checkboxgroup'   => '',
							'show_if_checked' => 'no',
							'autoload'        => false,
						),
						array(
							'title' => '',
							'type'  => 'title',
							'id'    => 'user_sync_email_notice',
							'desc'  => '<div class="user_sync_email_notice">' . __( 'Note: Performing user synchronization will email all the users with their login credentials. To disable the same you may deactivate', 'edwiser-bridge' ) . '<b>' . __( '"Link WP user account to moodle and Create new moodle account"', 'edwiser-bridge' ) . '</b>' . __( 'from Manage email templates.', 'edwiser-bridge' ) . '</div>',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'user_sync_email_notice',
						),
						array(
							'title'    => '',
							'desc'     => '',
							'id'       => 'eb_synchronize_users_button',
							'default'  => __( 'Start Synchronization', 'edwiser-bridge' ),
							'type'     => 'button',
							'desc_tip' => false,
							'class'    => 'button secondary',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'user_synchronization_options',
						),

					)
				);
			} else {
				$settings = apply_filters(
					'eb_course_synchronization_settings',
					array(
						array(
							'title' => __( 'Synchronize Courses', 'edwiser-bridge' ),
							'type'  => 'title',
							'id'    => 'course_synchronization_options',
						),
						array(
							'title'           => __( 'Synchronization Options', 'edwiser-bridge' ),
							'desc'            => __( 'Synchronize course categories', 'edwiser-bridge' ),
							'id'              => 'eb_synchronize_categories',
							'default'         => 'no',
							'type'            => 'checkbox',
							'checkboxgroup'   => 'start',
							'show_if_checked' => 'option',
							'autoload'        => false,
						),
						array(
							'desc'            => __( 'Update previously synchronized courses', 'edwiser-bridge' ),
							'id'              => 'eb_synchronize_previous',
							'default'         => 'no',
							'type'            => 'checkbox',
							'checkboxgroup'   => '',
							'show_if_checked' => 'no',
							'autoload'        => false,
						),

						array(
							'desc'            => __( 'Keep synchronized courses as draft. (Courses in the draft will not reflect on the learners "My Courses" page!)', 'edwiser-bridge' ),
							'id'              => 'eb_synchronize_draft',
							'default'         => 'yes',
							'type'            => 'checkbox',
							'checkboxgroup'   => '',
							'show_if_checked' => 'yes',
							'autoload'        => false,
						),
						array(
							'desc'            => __( 'Sync Course images from moodle', 'edwiser-bridge' ),
							'id'              => 'eb_synchronize_images',
							'default'         => 'no',
							'type'            => 'checkbox',
							'checkboxgroup'   => '',
							'show_if_checked' => 'yes',
							'autoload'        => false,
						),
						array(
							'title' => '',
							'type'  => 'title',
							'id'    => 'course_draft_notice',
							'desc'  => '<div class="user_sync_email_notice">' . sprintf( __( 'Pre-Requisite Alert: Before you can synchronize course images from Moodle, ensure that you have enabled the "Can Download File" option on your Moodle site. To learn how to do this, please click the %s provided.', 'edwiser-bridge' ), '<a href="https://edwiser.helpscoutdocs.com/article/559-how-to-enable-can-download-file-in-the-active-external-web-service">' . __( 'link', 'edwiser-bridge' ) . '</a>' ) . '</div>', // @codingStandardsIgnoreLine
						),
						array(
							'type' => 'sectionend',
							'id'   => 'course_draft_notice',
						),

						array(
							'title'    => '',
							'desc'     => '',
							'id'       => 'eb_synchronize_courses_button',
							'default'  => __( 'Start Synchronization', 'edwiser-bridge' ),
							'type'     => 'button',
							'desc_tip' => false,
							'class'    => 'button secondary',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'course_synchronization_options',
						),

					)
				);
			}
			return apply_filters( 'eb_get_settings_' . $this->_id, $settings, $current_section );
		}
	}

}

return new Eb_Settings_Synchronization();
