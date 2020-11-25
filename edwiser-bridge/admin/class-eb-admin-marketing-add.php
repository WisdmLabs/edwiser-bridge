<?php

namespace app\wisdmlabs\edwiserBridge;

/*
 * EDW Eb_Admin_Marketing_Add Class.
 *
 * @since      1.2.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Eb_Admin_Marketing_Add')) {

    /**
     * EbAdminSettings.
     */
    class Eb_Admin_Marketing_Add
    {

        public function __construct()
        {
            add_action("eb_settings_footer", array($this, "outPut"));
        }

        public function outPut()
        {
            ?>
            <div class='eb-marketing-add'>
                <a target="_blank" href='https://edwiser.org/bridge/extensions/edwiser-bundle/'>
                    <img alt="<?php __('Sorry, Unable to load image', 'eb-textdomain') ?>" src="<?php echo plugins_url("edwiser-bridge/admin/assets/images/rem-ui.png"); ?>">
                </a>
            </div>
            <?php
        }
    }
}
new Eb_Admin_Marketing_Add();

