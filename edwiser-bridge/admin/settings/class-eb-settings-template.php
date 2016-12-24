<?php

namespace app\wisdmlabs\edwiserBridge;

/*
 * EDW template Settings
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

if (!class_exists('EBSettingsTemplate')) :
    /**
     * EBSettingsTemplate.
     */
    class EBSettingsTemplate extends EBSettingsPage
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->_id = 'template';
            $this->label = __('Template Settings', 'eb-textdomain');

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
                'eb_template_settings',
                array(
                    array(
                        'title' => __('Courses archive page template settings', 'eb-textdomain'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'courses_archive_template_options',
						"class"=>"eb-settings-title",
                    ),

                    array(
                        'title' => __('Courses Per Row', 'eb-textdomain'),
                        'desc' => '',
                        'id' => 'courses_per_row',
                        'type' => 'courses_per_row',
                        'default' => '',
                        'css' => '',
                    ),

                    array(
                        'title' => __('Enable right idebar', 'eb-textdomain'),
                        'desc' => "",
                        'id' => 'archive_enable_right_sidebar',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'autoload' => false,
                    ),

                    array(
                        'title' => '',
                        'desc' => '',
                        'id' => 'archive_right_sidebar',
                        'type' => 'select_sidebar',
                        'default' => '',
                        'css' => '',
                        'args' => array(
                            'show_option_none' => 'Select a sidebar',
                            'option_none_value' => '',
                        ),
                    ),
                    array(
                        'title' => __('Enable left sidebar', 'eb-textdomain'),
                        'desc' => "",
                        'id' => 'archive_enable_left_sidebar',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'autoload' => false,
                    ),

                    array(
                        'title' => '',
                        'desc' => '',
                        'id' => 'archive_left_sidebar',
                        'type' => 'select_sidebar',
                        'default' => '',
                        'css' => '',
                        'args' => array(
                            'show_option_none' => 'Select a sidebar',
                            'option_none_value' => '',
                        ),
                    ),

                    array('type' => 'sectionend', 'id' => 'courses_archive_template_options'),

                    array(
                        'title' => __('Single course page template settings', 'eb-textdomain'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'single_course_template_options',
						"class"=>"eb-settings-title",
                    ),                   

                    array(
                        'title' => __('Enable right sidebar', 'eb-textdomain'),
                        'desc' => "",
                        'id' => 'single_enable_right_sidebar',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'autoload' => false,
                    ),

                    array(
                        'title' => '',
                        'desc' => '',
                        'id' => 'single_right_sidebar',
                        'type' => 'select_sidebar',
                        'default' => '',
                        'css' => '',
                        'args' => array(
                            'show_option_none' => 'Select a sidebar',
                            'option_none_value' => '',
                        ),
                    ),
                    array(
                        'title' => __('Enable left sidebar', 'eb-textdomain'),
                        'desc' => "",
                        'id' => 'single_enable_left_sidebar',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'autoload' => false,
                    ),

                    array(
                        'title' => '',
                        'desc' => '',
                        'id' => 'single_left_sidebar',
                        'type' => 'select_sidebar',
                        'default' => '',
                        'css' => '',
                        'args' => array(
                            'show_option_none' => 'Select a sidebar',
                            'option_none_value' => '',
                        ),
                    ),

                    array('type' => 'sectionend', 'id' => 'single_course_template_options'),
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

return new EBSettingsTemplate();
