<?php
namespace app\wisdmlabs\edwiserBridge;

if (!class_exists('EbSettingsGenralDash')) {
    include_once 'class-eb-settings-general-info.php';
    class EbSettingsGenralDash extends EBSettingsPage
    {
        
        public function __construct()
        {
            $this->_id = 'nm-genral-info';
            $this->label = __('General Info', 'eb-textdomain');
            add_filter('eb_settings_tabs_array', array($this, 'addSettingsPage'), 20);
            add_action('eb_settings_'.$this->_id, array($this, 'output'));
            add_action('eb_settings_save_'.$this->_id, array($this, 'save'));
            
        }
        public function output()
        {
            $GLOBALS['hide_save_button'] = true;
            $genralInfo=new EbSettingsGenralInfo();
            $genralInfo->outPut();
        }
    }
    new EbSettingsGenralDash();
}
