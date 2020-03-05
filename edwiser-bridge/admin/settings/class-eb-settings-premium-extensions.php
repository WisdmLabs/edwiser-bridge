<?php

namespace app\wisdmlabs\edwiserBridge;

/*
 * EDW Remui settings tab
 *
 * @link       https://edwiser.org
 * @since      1.3.1
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('EbSettingsRemui')) :

    /**
     * EbSettingsPayPal.
     */
    class EbSettingsRemui extends EBSettingsPage
    {

        public function __construct()
        {
            $this->_id = 'remui';
            $this->label = __('Premium Extensions', 'eb-textdomain');

            add_filter('eb_settings_tabs_array', array($this, 'addSettingsPage'), 20);
            add_action('eb_settings_'.$this->_id, array($this, 'output'));
            // add_action('eb_settings_save_'.$this->_id, array($this, 'save'));
        }

        /**
         * function to output conent on premium extensions page
         */
        public function output()
        {
            $GLOBALS['hide_save_button'] = 1;
            ?>
                <div class="eb-premium-container">
                    <div class="eb-premium-top">
                        <div class="eb-premium-info">
                            <div class="eb-premium-general-title">
                                <?php
                                    _e("A Comprehensive e-commerce solution for your Moodle.", "eb-textdomain");
                                ?>
                            </div>
                            <div class="eb-premium-general-sub-title">
                                <div>
                                <?php
                                    _e("With this solution, you get 4 extensions that automate your course selling process further!", "eb-textdomain");
                                ?>
                                </div>
                            </div>
                            <div class="eb-premium-discount">
                                <a href="https://edwiser.org/bridge/extensions/edwiser-bundle/?utm_source=wordpress&utm_medium=cta1&utm_campaign=bridgeplugincta1"  target="_blank">
                                    <span>
                                    <?php
                                        _e("Get Started!", "eb-textdomain");
                                    ?>
                                    <i style="-webkit-transform: rotate(20deg); transform: rotate(180deg);" class="dashicons dashicons-admin-collapse"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                        <div class="eb-premium-interest">
                            <div>
                                <?php
                                _e("Wish to explore more about this solution?", "eb-textdomain");
                                ?>
                            </div>
                            <div>
                                <?php
                                _e("Scroll down to know more!", "eb-textdomain");
                                ?>
                            </div>
                            <div class="downArrow bounce">

                                <img width = "20" alt="<?php __('Sorry, unable to load the image', 'eb-textdomain') ?>" src="<?php echo plugins_url("edwiser-bridge/admin/assets/images/arrow-down.png"); ?>">

                            </div>
                        </div>
                    </div>
                    <div class="eb-premium-middle">
                        <div class="eb-premium-middle-title">
                            <?php
                                _e("Edwiser Bundle solution completely automates the process of selling moodle courses using WordPress:", "eb-textdomain");
                            ?>
                        </div>
                        <div class="eb-premium-extensions eb-premium-extension-woo-int">
                            <div class="eb-premium-extensions-title">
                                <span>
                                    <?php
                                        _e("Automate your Course Selling Process", "eb-textdomain");
                                    ?>
                                </span>
                            </div>
                            <div class="eb-premium-woo-int-wrapper">
                                <div class="eb-premium-woo-int-img">
                                    <img alt="<?php __('Sorry, unable to load the image', 'eb-textdomain') ?>" src="<?php echo plugins_url("edwiser-bridge/admin/assets/images/commerce.png"); ?>">
                                </div>
                                <div class="eb-premium-woo-int-info">
                                    <ul class="eb-premium-exte-list">
                                        <li>
                                        <?php
                                            _e("Sell Courses with a digital Shopfront", "eb-textdomain");
                                        ?>
                                        </li>
                                        <li>
                                        <?php
                                            _e("Sell different types of products (ebook, merch, digests, etc) along with Moodle courses", "eb-textdomain");
                                        ?>
                                        </li>
                                        <li>
                                        <?php
                                            _e("Sell Moodle courses as Subscriptions", "eb-textdomain");
                                        ?>
                                        </li>
                                        <li>
                                        <?php
                                            _e("Seamless selling with 160+ Payment gateways", "eb-textdomain");
                                        ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="eb-premium-extensions eb-premium-extension-sso">
                            <div class="eb-premium-extensions-title">
                                <span>
                                    <?php
                                        _e("Simplified User Management", "eb-textdomain");
                                    ?>
                                </span>
                            </div>
                            <div class="eb-premium-sso-info-wrapper">
                                <div class="eb-premium-sso-info">
                                    <ul class="eb-premium-exte-list">
                                        <li>
                                        <?php
                                            _e("Single Set of Login Credentials (Simultaneous Login to  WordPress & Moodle)", "eb-textdomain");
                                        ?>
                                        </li>
                                        <li>
                                        <?php
                                            _e("Auto-enroll students to courses after purchase", "eb-textdomain");
                                        ?>
                                        </li>
                                        <li>
                                        <?php
                                            _e("Auto-sync of course progress across both platforms", "eb-textdomain");
                                        ?>
                                        </li>
                                    </ul>
                                </div>
                                <div class="eb-premium-sso-img">
                                    <img alt="<?php __('Sorry, unable to load the image', 'eb-textdomain') ?>" src="<?php echo plugins_url("edwiser-bridge/admin/assets/images/candidate.png"); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="eb-premium-extensions eb-premium-extension-selective-synch">
                            <div class="eb-premium-extensions-title">
                                <span>
                                    <?php
                                        _e("Advanced Reporting & Account Management", "eb-textdomain");
                                    ?>
                                </span>
                            </div>
                            <div class="eb-premium-selective-synch-info-wrapper">
                                <div class="eb-premium-selective-synch-img">
                                    <img alt="<?php __('Sorry, unable to load the image', 'eb-textdomain') ?>" src="<?php echo plugins_url("edwiser-bridge/admin/assets/images/dictionary.png"); ?>">
                                </div>
                                <div class="eb-premium-selective-synch-info">
                                    <ul class="eb-premium-exte-list">
                                        <li>
                                        <?php
                                            _e("Automated Invoicing for Purchases", "eb-textdomain");
                                        ?>
                                        </li>
                                        <li>
                                        <?php
                                            _e("Get access to smart in-depth reports", "eb-textdomain");
                                        ?>
                                        </li>
                                        <li>
                                        <?php
                                            _e("Manage Orders of users easily", "eb-textdomain");
                                        ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="eb-premium-bottom">
                        <div class="eb-premium-disc">
                                <a href="https://edwiser.org/bridge/extensions/edwiser-bundle/?utm_source=wordpress&utm_medium=cta2&utm_campaign=bridgeplugincta2"  target="_blank">
                                    <span>
                                        <?php
                                            _e("Get Edwiser Bundle Now!", "eb-textdomain");
                                        ?>
                                        <i style="-webkit-transform: rotate(20deg); transform: rotate(180deg);" class="dashicons dashicons-admin-collapse"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
        }
    }


endif;

return new EbSettingsRemui();
