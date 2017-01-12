<?php

namespace app\wisdmlabs\edwiserBridge;

/*
 * EDW Licensing Management
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

if (!class_exists('EbSettingsLicensing')) :

    /**
     * EbSettingsLicensing.
     */
    class EbSettingsLicensing extends EBSettingsPage
    {

        public $addon_licensing;

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->addon_licensing = array('test');
            $this->_id = 'licensing';
            $this->label = __('Licenses', 'eb-textdomain');

            add_filter('eb_settings_tabs_array', array($this, 'addSettingsPage'), 20);
            add_action('eb_settings_'.$this->_id, array($this, 'output'));
        }

        /**
         * Output the settings.
         *
         * @since  1.0.0
         */
        public function output()
        {
            //global $current_section;
            // Hide the save button
            $GLOBALS['hide_save_button'] = true;
            include_once EB_PLUGIN_DIR.'admin/partials/html-admin-licensing.php';
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
                'eb_licensing',
                array(
                array(
                    'title' => __('Licenses', 'eb-textdomain'),
                    'type' => 'title',
                    'id' => 'licensing_management',
                    ),
                    array(
                    'type' => 'sectionend',
                    'id' => 'licensing_management',
                    ),
                    )
            );

            return apply_filters('eb_get_settings_'.$this->_id, $settings, $current_section);
        }
    }

endif;

return new EbSettingsLicensing();
