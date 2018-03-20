<?php

namespace app\wisdmlabs\edwiserBridge;

/*
 * EDW PayPal settings page
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('EbSettingsPayPal')) :

    /**
     * EbSettingsPayPal.
     */
    class EbSettingsPayPal extends EBSettingsPage
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->_id = 'paypal';
            $this->label = __('PayPal Settings', 'eb-textdomain');

            add_filter('eb_settings_tabs_array', array($this, 'addSettingsPage'), 20);
            add_action('eb_settings_'.$this->_id, array($this, 'output'));
            add_action('eb_settings_save_'.$this->_id, array($this, 'save'));
        }

        /**
         * Output the settings.
         *
         * @since  1.0.0
         */
        public function output()
        {
            global $current_section;

            $settings = $this->getSettings($current_section);

            EbAdminSettings::outputFields($settings);
        }

        /**
         * Save settings.
         *
         * @since  1.0.0
         */
        public function save()
        {
            global $current_section;

            $settings = $this->getSettings($current_section);
            EbAdminSettings::saveFields($settings);
        }

        /**
         * Get settings array.
         *
         * @since  1.0.0
         *
         * @return array
         */
        public function getSettings($current_section = '')
        {
            $settings = apply_filters(
                'eb_paypal_settings',
                array(
                array(
                    'title' => __('PayPal Settings', 'eb-textdomain'),
                    'type' => 'title',
                    'id' => 'paypal_options',
                    ),
                    array(
                    'title' => __('PayPal Email', 'eb-textdomain'),
                    'desc' => __('Enter your PayPal email here.', 'eb-textdomain'),
                    'id' => 'eb_paypal_email',
                    'css' => 'min-width:350px;',
                    'default' => '',
                    'type' => 'email',
                    'desc_tip' => true,
                    'custom_attributes' => array('required' => 'required'),
                    ),
                    array(
                    'title' => __('PayPal Currency', 'eb-textdomain'),
                    'desc' => __('Select transaction currency code, Default is USD.', 'eb-textdomain'),
                    'id' => 'eb_paypal_currency',
                    'css' => 'min-width:350px;',
                    'default' => '',
                    'type' => 'select',
                    'desc_tip' => true,
                    'options' => array(
                        'USD' => __('U.S. Dollar (USD)', 'eb-textdomain'),
                        'CAD' => __('Canadian Dollar (CAD)', 'eb-textdomain'),
                        'NZD' => __('New Zealand Dollar (NZD)', 'eb-textdomain'),
                        'HKD' => __('Hong Kong Dollar (HKD)', 'eb-textdomain'),
                        'EUR' => __('Euro (EUR)', 'eb-textdomain'),
                        'JPY' => __('Japanese Yen (JPY)', 'eb-textdomain'),
                        'MXN' => __('Mexican Peso (MXN)', 'eb-textdomain'),
                        'CHF' => __('Swiss Franc (CHF)', 'eb-textdomain'),
                        'GBP' => __('Pound Sterling (GBP)', 'eb-textdomain'),
                        'AUD' => __('Australian Dollar (AUD)', 'eb-textdomain'),
                        'PLN' => __('Polish Zloty (PLN)', 'eb-textdomain'),
                        'DKK' => __('Danish Krone (DKK)', 'eb-textdomain'),
                        'SGD' => __('Singapore Dollar (SGD)', 'eb-textdomain'),
                    ),
                    ),
                    array(
                    'title' => __('PayPal Country', 'eb-textdomain'),
                    'desc' => __('Enter your country code here.', 'eb-textdomain'),
                    'id' => 'eb_paypal_country_code',
                    'css' => 'min-width:350px;',
                    'default' => 'US',
                    'type' => 'text',
                    'desc_tip' => true,
                    'custom_attributes' => array('required' => 'required'),
                    ),
                    array(
                    'title' => __('PayPal Cancel URL', 'eb-textdomain'),
                    'desc' => __('Enter the URL used for purchase cancellations.', 'eb-textdomain'),
                    'id' => 'eb_paypal_cancel_url',
                    'css' => 'min-width:350px;',
                    'default' => site_url(),
                    'type' => 'url',
                    'desc_tip' => true,
                    'custom_attributes' => array('required' => 'required'),
                    ),
                    array(
                    'title' => __('PayPal Return URL', 'eb-textdomain'),
                    'desc' => __(
                        'Enter the URL used for completed purchases (a thank you page).',
                        'eb-textdomain'
                    ),
                    'id' => 'eb_paypal_return_url',
                    'css' => 'min-width:350px;',
                    'default' => site_url('/thank-you-for-purchase/ '),
                    'type' => 'url',
                    'desc_tip' => true,
                    'custom_attributes' => array('required' => 'required'),
                    ),
                    array(
                    'title' => __('PayPal Notify URL', 'eb-textdomain'),
                    'desc' => __('Enter the URL used for IPN notifications.', 'eb-textdomain'),
                    'id' => 'eb_paypal_notify_url',
                    'css' => 'min-width:350px;',
                    'default' => site_url('/eb/paypal-notify'),
                    'type' => 'url',
                    'desc_tip' => true,
                    'custom_attributes' => array('readonly' => 'readonly'),
                    ),
                    array(
                    'title' => __('Use PayPal Sandbox', 'eb-textdomain'),
                    'desc' => __('Check to enable the PayPal sandbox.', 'eb-textdomain'),
                    'id' => 'eb_paypal_sandbox',
                    'default' => 'no',
                    'type' => 'checkbox',
                    'show_if_checked' => 'option',
                    'autoload' => false,
                    ),
                    array(
                    'type' => 'sectionend',
                    'id' => 'paypal_options',
                    ),
                    array(
                    'title' => __('PayPal API Credentials (Optional)', 'eb-textdomain'),
                    'type' => 'title',
                    'id' => 'paypal_api_options',
                    'desc' => __('To use order refunds following fields are mandatory.', 'eb-textdomain'),
                    ),
                    array(
                    'title' => __('API username', 'eb-textdomain'),
                    'id' => 'eb_api_username',
                    'css' => 'min-width:350px;',
                    'default' => '',
                    'type' => 'text',
                    // 'show_if_checked' => 'option',
                    'autoload' => false,
                    ),
                    array(
                    'title' => __('API password', 'eb-textdomain'),
                    'id' => 'eb_api_password',
                    'css' => 'min-width:350px;',
                    'default' => '',
                    'type' => 'password',
                    // 'show_if_checked' => 'option',
                    'autoload' => false,
                    ),
                    array(
                    'title' => __('API signature', 'eb-textdomain'),
                    'id' => 'eb_api_signature',
                    'css' => 'min-width:350px;',
                    'default' => '',
                    'type' => 'text',
                    // 'show_if_checked' => 'option',
                    'autoload' => false,
                    ),
                    array(
                    'type' => 'sectionend',
                    'id' => 'paypal_api_options',
                    ),
                    )
            );
            //}

            return apply_filters('eb_get_settings_'.$this->_id, $settings, $current_section);
        }
    }

endif;

return new EbSettingsPayPal();
