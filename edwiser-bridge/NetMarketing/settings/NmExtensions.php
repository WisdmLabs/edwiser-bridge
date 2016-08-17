<?php

namespace wisdmalbs\newtowrk\marketing;

if (!class_exists("wisdmalbs\newtowrk\marketing\NmExtensions")) {
    class NmExtensions
    {
        public function __construct()
        {
        }

        public function updateExtensionsData($requestUrl, $parameter)
        {
            $transKey = $_GET['page'].'_extensions_data';
            $sync = new SyncronizeData($requestUrl, $parameter, $transKey);
            $sync->prepareRequest();
        }

        public function getExtensions()
        {
            $extensions = get_option($_GET['page'].'_nm_extensions');
            if ($extensions !== false) {
                $dashbord = new DashBoard();
                $dashbord->displayExtensions($extensions);
            } else {
                echo '<h2>'._e('No extensions are available for this plugin', NetworkMarketing::$nmTextDomain).'</h2>';
            }
        }
    }
}
