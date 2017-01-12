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
            $this->label = __('General', 'eb-textdomain');

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
                    'title' => __('Redirect to My Courses', 'eb-textdomain'),
                    'desc' => sprintf(
                        __(
                            'Redirect user to the My Courses page on %s form the %s page.',
                            'eb-textdomain'
                        ),
                        '<strong>' . __('Login / Registration', 'eb-textdomain') . '</strong>',
                        '<a href="' . esc_url(site_url('/user-account')) . '">' . __('User Account', 'eb-textdomain') . '</a>'
                    ),
                    __('Redirect user to the My Courses page on login and registration', 'eb-textdomain'),
                    'id' => 'eb_enable_my_courses',
                    'default' => 'no',
                    'type' => 'checkbox',
                    'autoload' => false,
                    ),
                    array(
                    'title' => __('My Courses Page', 'eb-textdomain'),
                    'desc' => '<br/>'.sprintf(
                        __(
                            'Select my courses page here. Default page is %s ',
                            'eb-textdomain'
                        ),
                        '<a href="' . esc_url(site_url('/eb-my-courses')) . '">' . __('My Courses', 'eb-textdomain') . '</a>'
                    ),
                    'id' => 'eb_my_courses_page_id',
                    'type' => 'single_select_page',
                    'default' => '',
                    'css' => 'min-width:300px;',
                    'args' => array(
                        'show_option_none' =>__('Select a page', "eb-textdomain"),
                        'option_none_value' => '',
                    ),
                    'desc_tip' => __(
                        "This sets 'My Courses' page, where the user can see all his purchased courses and access them directly. You have to use this shortcode [eb_my_courses] to create this page.",
                        'eb-textdomain'
                    ),
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
                            'Select user account page here. Default page is %s ',
                            'eb-textdomain'
                        ),
                        '<a href="' . esc_url(site_url('/user-account')) . '">' . __('User Account', 'eb-textdomain') . '</a>'
                    ),
                    'id' => 'eb_useraccount_page_id',
                    'type' => 'single_select_page',
                    'default' => '',
                    'css' => 'min-width:300px;',
                    'args' => array(
                        'show_option_none' =>__('Select a page', "eb-textdomain"),
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
                    array(
                    'title' => __('Max number of courses in a row on the courses page', 'eb-textdomain'),
                    'desc' => '',
                    'id' => 'courses_per_row',
                    'type' => 'courses_per_row',
                    'default' => '',
                    'css' => '',
                    'desc_tip' =>
                    __('This setting will be applicable only on the `/courses` page template', 'eb-textdomain'),
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
