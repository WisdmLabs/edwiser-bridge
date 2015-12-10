<?php

/**
 * EDW PayPal settings page
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

if ( ! class_exists( 'EB_Settings_PayPal' ) ) :

	/**
	 * EB_Settings_PayPal
	 */
	class EB_Settings_PayPal extends EB_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'paypal';
		$this->label = __( 'PayPal Settings', 'eb-textdomain' );

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

		$settings = apply_filters( 'eb_paypal_settings', array(
				array(
					'title'  => __( 'PayPal Settings', 'eb-textdomain' ),
					'type'   => 'title',
					'id'   => 'paypal_options'
				),

				array(
					'title'    => __( 'PayPal Email', 'eb-textdomain' ),
					'desc'     => __( 'Enter your PayPal email here.', 'eb-textdomain' ),
					'id'       => 'eb_paypal_email',
					'css'      => 'min-width:350px;',
					'default'  => '',
					'type'     => 'email',
					'desc_tip' =>  true,
					'custom_attributes'	=> array('required' => 'required')
				),

				array(
					'title'    => __( 'PayPal Currency', 'eb-textdomain' ),
					'desc'     => __( 'Select transaction currency code, Default is USD.', 'eb-textdomain' ),
					'id'       => 'eb_paypal_currency',
					'css'      => 'min-width:350px;',
					'default'  => '',
					'type'     => 'select',
					'desc_tip' =>  true,
					'options'  => array(
						'USD'   => __( 'U.S. Dollar (USD)', 'eb-textdomain' ),
						'CAD'   => __( 'Canadian Dollar (CAD)', 'Wp-moodle' ),
						'NZD'   => __( 'New Zealand Dollar (NZD)', 'Wp-moodle' ),
						'HKD'   => __( 'Hong Kong Dollar (HKD)', 'Wp-moodle' ),
						'EUR'   => __( 'Euro (EUR)', 'Wp-moodle' ),
						'JPY'   => __( 'Japanese Yen (JPY)', 'Wp-moodle' ),
						'MXN'   => __( 'Mexican Peso (MXN)', 'Wp-moodle' ),
						'CHF'   => __( 'Swiss Franc (CHF)', 'Wp-moodle' ),
						'GBP'   => __( 'Pound Sterling (GBP)', 'Wp-moodle' )
					)
				),

				array(
					'title'    => __( 'PayPal Country', 'eb-textdomain' ),
					'desc'     => __( 'Enter your country code here.', 'eb-textdomain' ),
					'id'       => 'eb_paypal_country_code',
					'css'      => 'min-width:350px;',
					'default'  => 'US',
					'type'     => 'text',
					'desc_tip' =>  true,
					'custom_attributes'	=> array('required' => 'required')
				),

				array(
					'title'    => __( 'PayPal Cancel URL', 'eb-textdomain' ),
					'desc'     => __( 'Enter the URL used for purchase cancellations.', 'eb-textdomain' ),
					'id'       => 'eb_paypal_cancel_url',
					'css'      => 'min-width:350px;',
					'default'  => site_url(),
					'type'     => 'url',
					'desc_tip' =>  true,
					'custom_attributes'	=> array('required' => 'required')
				),

				array(
					'title'    => __( 'PayPal Return URL', 'eb-textdomain' ),
					'desc'     => __( 'Enter the URL used for completed purchases (a thank you page).', 'eb-textdomain' ),
					'id'       => 'eb_paypal_return_url',
					'css'      => 'min-width:350px;',
					'default'  => site_url( '/thank-you-for-purchase/ ' ),
					'type'     => 'url',
					'desc_tip' =>  true,
					'custom_attributes'	=> array('required' => 'required')
				),

				array(
					'title'    => __( 'PayPal Notify URL', 'eb-textdomain' ),
					'desc'     => __( 'Enter the URL used for IPN notifications.', 'eb-textdomain' ),
					'id'       => 'eb_paypal_notify_url',
					'css'      => 'min-width:350px;',
					'default'  => site_url( '/eb/paypal-notify' ),
					'type'     => 'url',
					'desc_tip' =>  true,
					'custom_attributes' => array( 'readonly' => 'readonly' )
				),

				array(
					'title'           => __( 'Use PayPal Sandbox', 'eb-textdomain' ),
					'desc'            => __( 'Check to enable the PayPal sandbox.', 'eb-textdomain' ),
					'id'              => 'eb_paypal_sandbox',
					'default'         => 'no',
					'type'            => 'checkbox',
					'show_if_checked' => 'option',
					'autoload'        => false
				),

				array(
					'type'  => 'sectionend',
					'id'    => 'paypal_options'
				),

			) );
		//}

		return apply_filters( 'eb_get_settings_' . $this->id, $settings, $current_section );
	}
}

endif;

return new EB_Settings_PayPal();
