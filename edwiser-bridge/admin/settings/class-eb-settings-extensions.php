<?php

namespace app\wisdmlabs\edwiserBridge;

use wisdmalbs\newtowrk\marketing;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('EBExtensions')) {

    /**
     * EBSettingsConnection.
     */
    class EBExtensions extends EBSettingsPage
    {
        public function __construct()
        {
            marketing\NetworkMarketing::$nmTextDomain="eb-textdomain";
            $this->_id = 'eb-extension';
            $this->label = __('Extensions', 'eb-textdomain');
            add_filter('eb_settings_tabs_array', array($this, 'addSettingsPage'), 20);
            add_action('eb_settings_'.$this->_id, array($this, 'output'));
            add_action('eb_settings_save_'.$this->_id, array($this, 'save'));
        }
        public function output()
        {
            $GLOBALS['hide_save_button'] = true;
            include_once(dirname(plugin_dir_path(__DIR__)) . "/NetMarketing/settings/NmExtensions.php");
            $extensions = new marketing\NmExtensions();
            $extensions->updateExtensionsData("https://edwiser.org/edwiserbridge-extensions.json", array('user-agent' => 'Edwiser Bridge Extensions Page'));
            $extensions->getExtensions();
        }
    }
    new EBExtensions();
}
