<?php

/**
 * EDW Product Settings
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'EB_Settings_Synchronization' ) ) :

	/**
	 * EB_Settings_Products
	 */
	class EB_Settings_Synchronization extends EB_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'synchronization';
		$this->label = __( 'Synchronization', 'eb-textdomain' );

		add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'eb_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'eb_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'eb_sections_' . $this->id, array( $this, 'output_sections' ) );
	}

	/**
	 * Get sections
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''            => __( 'Courses', 'eb-textdomain' ),
			'user_data'   => __( 'Users', 'eb-textdomain' )
		);

		return apply_filters( 'eb_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output the settings
	 *
	 * @since  1.0.0
	 */
	public function output() {
		global $current_section;

		// Hide the save button
		$GLOBALS['hide_save_button'] = true;

		$settings = $this->get_settings( $current_section );

		EB_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 *
	 * @since  1.0.0
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		EB_Admin_Settings::save_fields( $settings );
	}

	/**
	 * Get settings array
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		if ( 'user_data' == $current_section ) {

			$settings = apply_filters( 'eb_user_synchronization_settings', array(

					array(
						'title'  => __( 'Synchronize User Data', 'eb-textdomain' ),
						'type'   => 'title',
						'id'     => 'user_synchronization_options'
					),

					array(
						'title'           => __( 'Synchronization Options', 'eb-textdomain' ),
						'desc'            => __( 'Update user\'s course enrollment status', 'eb-textdomain' ),
						'id'              => 'eb_synchronize_user_courses',
						//'custom_attributes' => array( 'disabled' => 'disabled' ),
						'default'         => 'no',
						'type'            => 'checkbox',
						'checkboxgroup'   => 'start',
						'show_if_checked' => 'option',
						'autoload'        => false

					),

					array(
						'title'    => __( '', 'eb-textdomain' ),
						'desc'     => __( '', 'eb-textdomain' ),
						'id'       => 'eb_synchronize_users_button',
						'default'  => 'Start Synchronization',
						'type'     => 'button',
						'desc_tip' =>  false,
						'class'    => 'button secondary'
					),

					array(
						'type'  => 'sectionend',
						'id'    => 'user_synchronization_options'
					),

				) );
		} else {
			$settings = apply_filters( 'eb_course_synchronization_settings', array(
					array(
						'title'  => __( 'Synchronize Courses', 'eb-textdomain' ),
						'type'   => 'title',
						'id'     => 'course_synchronization_options'
					),

					array(
						'title'           => __( 'Synchronization Options', 'eb-textdomain' ),
						'desc'            => __( 'Synchronize course categories', 'eb-textdomain' ),
						'id'              => 'eb_synchronize_categories',
						'default'         => 'no',
						'type'            => 'checkbox',
						'checkboxgroup'   => 'start',
						'show_if_checked' => 'option',
						'autoload'        => false
					),

					array(
						'desc'            => __( 'Update previously synchronized courses', 'eb-textdomain' ),
						'id'              => 'eb_synchronize_previous',
						'default'         => 'no',
						'type'            => 'checkbox',
						'checkboxgroup'   => '',
						'show_if_checked' => 'no',
						'autoload'        => false
					),

					array(
						'desc'            => __( 'Keep synchronized courses as draft', 'eb-textdomain' ),
						'id'              => 'eb_synchronize_draft',
						'default'         => 'yes',
						'type'            => 'checkbox',
						'checkboxgroup'   => '',
						'show_if_checked' => 'yes',
						'autoload'        => false
					),

					array(
						'title'    => __( '', 'eb-textdomain' ),
						'desc'     => __( '', 'eb-textdomain' ),
						'id'       => 'eb_synchronize_courses_button',
						'default'  => 'Start Synchronization',
						'type'     => 'button',
						'desc_tip' =>  false,
						'class'    => 'button secondary'
					),

					array(
						'type'  => 'sectionend',
						'id'  => 'course_synchronization_options'
					),

				) );
		}

		return apply_filters( 'eb_get_settings_' . $this->id, $settings, $current_section );
	}
}

endif;

return new EB_Settings_Synchronization();
