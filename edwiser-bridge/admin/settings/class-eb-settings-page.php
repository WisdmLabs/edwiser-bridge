<?php

namespace app\wisdmlabs\edwiserBridge;

/**
 * EDW Settings Page/Tab
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

if (!class_exists('EBSettingsPage')) :

    /**
     * EBSettingsPage
     */
    abstract class EBSettingsPage
    {

        protected $_id = '';
        protected $label = '';

        /**
         * Constructor
         */
        public function __construct()
        {
            add_filter('eb_settings_tabs_array', array($this, 'addSettingsPage'), 20);
            add_action('eb_sections_'.$this->_id, array($this, 'outputSections'));
            add_action('eb_settings_'.$this->_id, array($this, 'output'));
            add_action('eb_settings_save_'.$this->_id, array($this, 'save'));
        }

        /**
         * Add this page to settings
         *
         * @since  1.0.0
         */
        public function addSettingsPage($pages)
        {
            $pages[$this->_id] = $this->label;

            return $pages;
        }

        /**
         * Get settings array
         *
         * @since  1.0.0
         * @return array
         */
        public function getSettings()
        {
            return apply_filters('eb_getSettings_'.$this->_id, array());
        }

        /**
         * Get sections
         *
         * @since  1.0.0
         * @return array
         */
        public function getSections()
        {
            return apply_filters('eb_getSections_'.$this->_id, array());
        }

        /**
         * Output sections
         *
         * @since  1.0.0
         */
        public function outputSections()
        {
            global $current_section;

            $sections = $this->getSections();

            if (empty($sections)) {
                return;
            }

            echo '<ul class="subsubsub">';

            $array_keys = array_keys($sections);

            foreach ($sections as $id => $label) {
                echo '<li>';
                echo '<a href="'.
                admin_url(
                    'admin.php?page=eb-settings&tab='.$this->_id.'&section='.sanitize_title($id)
                ).'" class="'.($current_section == $id ? 'current' : '').'">'.$label.'</a> ';
                echo (end($array_keys) == $id ? '' : '|').' </li>';
            }

            echo '</ul><br class="clear" />';
        }

        /**
         * Output the settings
         *
         * @since  1.0.0
         */
        public function output()
        {
            $settings = $this->getSettings();

            EbAdminSettings::outputFields($settings);
        }

        /**
         * Save settings
         *
         * @since  1.0.0
         */
        public function save()
        {
            global $current_section;

            $settings = $this->getSettings();
            EbAdminSettings::saveFields($settings);

            if ($current_section) {
                do_action('eb_update_options_'.$this->_id.'_'.$current_section);
            }
        }
    }

endif;
