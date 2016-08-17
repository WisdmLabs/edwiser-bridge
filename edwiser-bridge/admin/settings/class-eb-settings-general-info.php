<?php

namespace app\wisdmlabs\edwiserBridge;

use wisdmalbs\newtowrk\marketing;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists("EbSettingsGenralInfo")) {
    include_once(dirname(plugin_dir_path(__DIR__)) . "/NetMarketing/settings/General.php");

    class EbSettingsGenralInfo extends marketing\General
    {

        public function nmDocumentation()
        {
            marketing\NetworkMarketing::$nmTextDomain="eb-textdomain";
        }

        public function nmGetReadmeFile()
        {
            $file = dirname(plugin_dir_path(__DIR__)) . "/readme.txt";
            return $file;
        }
    }
}
