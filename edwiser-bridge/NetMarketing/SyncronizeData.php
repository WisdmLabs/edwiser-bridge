<?php

namespace wisdmalbs\newtowrk\marketing;

if (!class_exists('wisdmalbs\newtowrk\marketing\SynchronizeData')) {

    /**
     * Provides the functionality for the communication with the server and fetch
     * the data from the server using the API and update it into the database.
     */
    class SyncronizeData
    {
        private $requestUrl;
        private $parameter;
        private $transKey;

        /**
         * The functiona provides the functionality for the preparing the API request.
         *
         * @param type $requestPrameter The data to send with the request.
         * @param type $method          Request method.
         */
        public function prepareRequest()
        {
            if (false === ($extensions = get_transient($this->transKey))) {
                $this->updateData();
            }
        }

        public function updateData()
        {
            $responce = wp_remote_get($this->requestUrl, $this->parameter);
            if (!is_wp_error($responce)) {
                $extensions = json_decode(wp_remote_retrieve_body($responce));
                if ($extensions) {
                    $this->parseResult($extensions);
                    set_transient($this->transKey, $extensions, 168 * HOUR_IN_SECONDS);
                }
            }
        }
    }
}
