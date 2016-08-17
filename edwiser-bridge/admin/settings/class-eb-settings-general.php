<?php

namespace app\wisdmlabs\edwiserBridge;

/*
 * EDW General Settings
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

if (!class_exists('EBSettingsGeneral')) :
    /**
     * EBSettingsGeneral.
     */
    class EBSettingsGeneral extends EBSettingsPage
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->_id = 'general';
            $this->label = __('General Settings', 'eb-textdomain');

            add_filter('eb_settings_tabs_array', array($this, 'addSettingsPage'), 20);
            add_action('eb_settings_'.$this->_id, array($this, 'output'));
            add_action('eb_settings_save_'.$this->_id, array($this, 'save'));
        }

        /**
         * Get settings array.
         *
         * @since  1.0.0
         *
         * @return array
         */
        public function getSettings()
        {
            $settings = apply_filters(
                'eb_general_settings',
                array(
                    array(
                        'title' => __(
                            'General Options',
                            'eb-textdomain'
                        ),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'general_options',
                    ),

                    array(
                        'title' => __('Enable Registration', 'eb-textdomain'),
                        'desc' => __('Enable user registration', 'eb-textdomain'),
                        'id' => 'eb_enable_registration',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'autoload' => false,
                    ),

                    array(
                        'title' => __('User Account Page', 'eb-textdomain'),
                        'desc' => '<br/>'.sprintf(
                            __(
                                'Select user account page here. Default page is <a href="%s">User Account</a> ',
                                'eb-textdomain'
                            ),
                            site_url('/user-account')
                        ),
                        'id' => 'eb_useraccount_page_id',
                        'type' => 'single_select_page',
                        'default' => '',
                        'css' => 'min-width:300px;',
                        'args' => array(
                            'show_option_none' => 'Select a page',
                            'option_none_value' => '',
                        ),
                        'desc_tip' => __(
                            'This sets the user account page, where user can see his/her purchase history.',
                            'eb-textdomain'
                        ),
                    ),

                    array(
                        'title' => __('Moodle Language Code', 'eb-textdomain'),
                        'desc' => __(
                            'Enter language code which you get from moodle language settings.',
                            'eb-textdomain'
                        ),
                        'id' => 'eb_language_code',
                        'default' => 'en',
                        'type' => 'text',
                        'css' => 'min-width:300px;',
                        'desc_tip' => true,
                    ),

                    array('type' => 'sectionend', 'id' => 'general_options'),

                )
            );

            return apply_filters('eb_get_settings_'.$this->_id, $settings);
        }

        /**
         * Save settings.
         *
         * @since  1.0.0
         */
        public function save()
        {
            $settings = $this->getSettings();

            EbAdminSettings::saveFields($settings);
        }
    }

endif;

return new EBSettingsGeneral();
