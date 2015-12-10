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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'EB_Settings_Connection' ) ) :

	/**
	 * EB_Settings_Connection
	 */
	class EB_Settings_Connection extends EB_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'connection';
		$this->label = __( 'Connection Settings', 'eb-textdomain' );

		add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'eb_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'eb_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Output the settings
	 *
	 * @since  1.0.0
	 */
	public function output() {
		global $current_section;

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
		
		$settings = apply_filters( 'eb_connection_settings', array(
				array(
					'title'  => __( 'Connection Settings', 'eb-textdomain' ),
					'type'   => 'title',
					'id'   => 'connection_options'
				),

				array(
					'title'    => __( 'Moodle URL', 'eb-textdomain' ),
					'desc'     => __( 'Moodle URL ( Like: http://example.com or http://example.com/moodle etc.)', 'eb-textdomain' ),
					'id'       => 'eb_url',
					'css'      => 'min-width:350px;',
					'default'  => '',
					'type'     => 'url',
					'desc_tip' =>  true,
					'custom_attributes'	=> array( 'required' => 'required' )
				),

				array(
					'title'    => __( 'Moodle Access Token', 'eb-textdomain' ),
					'desc'     => __( 'Moodle access token.', 'eb-textdomain' ),
					'id'       => 'eb_access_token',
					'css'      => 'min-width:350px;',
					'default'  => '',
					'type'     => 'text',
					'desc_tip' =>  true,
					'custom_attributes'	=> array('required' => 'required')
				),

				array(
					'title'    => __( '', 'eb-textdomain' ),
					'desc'     => __( '', 'eb-textdomain' ),
					'id'       => 'eb_test_connection_button',
					'default'  => 'Test Connection',
					'type'     => 'button',
					'desc_tip' =>  false,
					'class'    => 'button secondary'
				),

				array(
					'type'  => 'sectionend',
					'id'  => 'connection_options'
				),

			) );

		return apply_filters( 'eb_get_settings_' . $this->id, $settings, $current_section );
	}
}

endif;

return new EB_Settings_Connection();