<?php
/**
 * EDW PayPal settings page
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

if ( ! class_exists( 'Eb_Settings_PayPal' ) ) :

	/**
	 * Eb_Settings_PayPal.
	 */
	class Eb_Settings_PayPal extends EB_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'paypal';
			$this->label = __( 'PayPal', 'edwiser-bridge' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
			add_action( 'eb_settings_save_' . $this->_id, array( $this, 'save' ) );
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			global $current_section;

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
		 * @param text $current_section current section.
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			$settings = apply_filters(
				'eb_paypal_settings',
				array(
					array(
						'title' => __( 'PayPal Settings', 'edwiser-bridge' ),
						'type'  => 'title',
						'id'    => 'paypal_options',
					),
					array(
						'title'             => __( 'PayPal Email', 'edwiser-bridge' ),
						'desc'              => __( 'Enter your PayPal email here.', 'edwiser-bridge' ),
						'id'                => 'eb_paypal_email',
						'css'               => 'min-width:350px;',
						'default'           => '',
						'type'              => 'email',
						'desc_tip'          => true,
						'custom_attributes' => array( 'required' => 'required' ),
					),
					array(
						'title'    => __( 'PayPal Currency', 'edwiser-bridge' ),
						'desc'     => __( 'Select transaction currency code, Default is USD.', 'edwiser-bridge' ),
						'id'       => 'eb_paypal_currency',
						'css'      => 'min-width:350px;',
						'default'  => '',
						'type'     => 'select',
						'desc_tip' => true,
						'options'  => array(
							'USD' => __( 'U.S. Dollar (USD)', 'edwiser-bridge' ),
							'CAD' => __( 'Canadian Dollar (CAD)', 'edwiser-bridge' ),
							'NZD' => __( 'New Zealand Dollar (NZD)', 'edwiser-bridge' ),
							'HKD' => __( 'Hong Kong Dollar (HKD)', 'edwiser-bridge' ),
							'EUR' => __( 'Euro (EUR)', 'edwiser-bridge' ),
							'JPY' => __( 'Japanese Yen (JPY)', 'edwiser-bridge' ),
							'MXN' => __( 'Mexican Peso (MXN)', 'edwiser-bridge' ),
							'CHF' => __( 'Swiss Franc (CHF)', 'edwiser-bridge' ),
							'GBP' => __( 'Pound Sterling (GBP)', 'edwiser-bridge' ),
							'AUD' => __( 'Australian Dollar (AUD)', 'edwiser-bridge' ),
							'PLN' => __( 'Polish Zloty (PLN)', 'edwiser-bridge' ),
							'DKK' => __( 'Danish Krone (DKK)', 'edwiser-bridge' ),
							'SGD' => __( 'Singapore Dollar (SGD)', 'edwiser-bridge' ),
							'BRL' => __( 'Brazilian Real (BRL)', 'edwiser-bridge' ),
						),
					),
					array(
						'title'             => __( 'PayPal Country', 'edwiser-bridge' ),
						'desc'              => __( 'Enter your country code here.', 'edwiser-bridge' ),
						'id'                => 'eb_paypal_country_code',
						'css'               => 'min-width:350px;',
						'default'           => 'US',
						'type'              => 'text',
						'desc_tip'          => true,
						'custom_attributes' => array( 'required' => 'required' ),
					),
					array(
						'title'             => __( 'PayPal Cancel URL', 'edwiser-bridge' ),
						'desc'              => __( 'Enter the URL used for purchase cancellations.', 'edwiser-bridge' ),
						'id'                => 'eb_paypal_cancel_url',
						'css'               => 'min-width:350px;',
						'default'           => site_url(),
						'type'              => 'url',
						'desc_tip'          => true,
						'custom_attributes' => array( 'required' => 'required' ),
					),
					array(
						'title'             => __( 'PayPal Return URL', 'edwiser-bridge' ),
						'desc'              => __(
							'Enter the URL used for completed purchases (a thank you page).',
							'edwiser-bridge'
						),
						'id'                => 'eb_paypal_return_url',
						'css'               => 'min-width:350px;',
						'default'           => site_url( '/thank-you-for-purchase/ ' ),
						'type'              => 'url',
						'desc_tip'          => true,
						'custom_attributes' => array( 'required' => 'required' ),
					),
					array(
						'title'             => __( 'PayPal Notify URL', 'edwiser-bridge' ),
						'desc'              => __( 'Enter the URL used for IPN notifications.', 'edwiser-bridge' ),
						'id'                => 'eb_paypal_notify_url',
						'css'               => 'min-width:350px;',
						'default'           => site_url( '/eb/paypal-notify' ),
						'type'              => 'url',
						'desc_tip'          => true,
						'custom_attributes' => array( 'readonly' => 'readonly' ),
					),
					array(
						'title'           => __( 'Use PayPal Sandbox', 'edwiser-bridge' ),
						'desc'            => __( 'Check to enable the PayPal sandbox.', 'edwiser-bridge' ),
						'id'              => 'eb_paypal_sandbox',
						'default'         => 'no',
						'type'            => 'checkbox',
						'show_if_checked' => 'option',
						'autoload'        => false,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'paypal_options',
					),
					array(
						'title' => __( 'PayPal API Credentials (Optional)', 'edwiser-bridge' ),
						'type'  => 'title',
						'id'    => 'paypal_api_options',
						'desc'  => __( 'To use order refunds following fields are mandatory.', 'edwiser-bridge' ),
					),
					array(
						'title'    => __( 'API username', 'edwiser-bridge' ),
						'id'       => 'eb_api_username',
						'css'      => 'min-width:350px;',
						'default'  => '',
						'type'     => 'text',
						'autoload' => false,
					),
					array(
						'title'    => __( 'API password', 'edwiser-bridge' ),
						'id'       => 'eb_api_password',
						'css'      => 'min-width:350px;',
						'default'  => '',
						'type'     => 'password',
						'autoload' => false,
					),
					array(
						'title'    => __( 'API signature', 'edwiser-bridge' ),
						'id'       => 'eb_api_signature',
						'css'      => 'min-width:350px;',
						'default'  => '',
						'type'     => 'text',
						'autoload' => false,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'paypal_api_options',
					),
				)
			);

			return apply_filters( 'eb_get_settings_' . $this->_id, $settings, $current_section );
		}
	}

endif;

return new Eb_Settings_PayPal();
