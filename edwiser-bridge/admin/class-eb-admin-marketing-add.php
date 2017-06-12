<?php

namespace app\wisdmlabs\edwiserBridge;

/*
 * EDW EbAdminMarketingAdd Class.
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

if (!class_exists('EbAdminMarketingAdd')) {

    /**
     * EbAdminSettings.
     */
    class EbAdminMarketingAdd
    {

        public function __construct()
        {
            add_action("eb_settings_header", array($this, "outPut"));
        }

        public function outPut()
        {
            ?>
            <div class='eb-marketing-add'>
                <a target="_blank" href='https://goo.gl/jOzaEl'>
                    <img src="<?php echo plugins_url("edwiser-bridge/admin/assets/images/rem-ui.jpg"); ?>">
                </a>
            </div>
            <?php
        }
    }
}
new EbAdminMarketingAdd();

