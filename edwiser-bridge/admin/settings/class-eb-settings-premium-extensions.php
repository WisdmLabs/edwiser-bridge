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
                            <div class="eb-premium-general-info">
                                <?php
                                    _e(
                                        "We have packed the best of Edwiser Bridge extensions in a bundle. Select and sync courses from Moodle to WP, use a common login for all your accounts, tie your courses as products to sell via WooCommerce.
                                        ",
                                        "eb-textdomain"
                                    );
                                ?>
                            </div>
                            <div class="eb-premium-discount">
                                <a href="https://edwiser.org/bridge/extensions/edwiser-bundle/?utm_source=InProduct&utm_medium=Page&utm_campaign=preext&utm_term=Apr&utm_content=cta1">
                                    <div>
                                    <?php
                                        _e(
                                            "Buy Edwiser Bridge Bundle @25% Off!",
                                            "eb-textdomain"
                                        );
                                    ?>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="eb-premium-interest">
                            <div>
                                <?php
                                _e("Interested?", "eb-textdomain");
                                ?>
                            </div>
                            <div>
                                <?php
                                _e("Scroll down to know more about the extensions.", "eb-textdomain");
                                ?>
                            </div>
                            <div class="downArrow bounce">
                                <img width="40" height="40" alt="" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDMyIDMyIiBoZWlnaHQ9IjMycHgiIGlkPSLQodC70L7QuV8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCAzMiAzMiIgd2lkdGg9IjMycHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxwYXRoIGQ9Ik0yNC4yODUsMTEuMjg0TDE2LDE5LjU3MWwtOC4yODUtOC4yODhjLTAuMzk1LTAuMzk1LTEuMDM0LTAuMzk1LTEuNDI5LDAgIGMtMC4zOTQsMC4zOTUtMC4zOTQsMS4wMzUsMCwxLjQzbDguOTk5LDkuMDAybDAsMGwwLDBjMC4zOTQsMC4zOTUsMS4wMzQsMC4zOTUsMS40MjgsMGw4Ljk5OS05LjAwMiAgYzAuMzk0LTAuMzk1LDAuMzk0LTEuMDM2LDAtMS40MzFDMjUuMzE5LDEwLjg4OSwyNC42NzksMTAuODg5LDI0LjI4NSwxMS4yODR6IiBmaWxsPSIjMTIxMzEzIiBpZD0iRXhwYW5kX01vcmUiLz48Zy8+PGcvPjxnLz48Zy8+PGcvPjxnLz48L3N2Zz4=" />
                            </div>
                            <!-- <div class="container">
                                <div class="chevron"></div>
                                <div class="chevron"></div>
                                <div class="chevron"></div>
                            </div> -->
                        </div>
                    </div>
                    <div class="eb-premium-middle">
                        <div class="eb-premium-extensions eb-premium-extension-woo-int">
                            <div class="eb-premium-woo-int-title">
                                <h1>
                                    <?php
                                        _e(
                                            "WooCommerce Moodle Integration",
                                            "eb-textdomain"
                                        );
                                    ?>
                                </h1>
                            </div>
                            <div class="eb-premium-woo-int-wrapper">
                                <div class="eb-premium-woo-int-info">
                                    <h4>
                                        <?php
                                        _e("AMPLIFIED REACH FOR YOUR COURSES", "eb-textdomain");
                                        ?>
                                    </h4>
                                <?php
                                    _e(
                                        "
                                        WooCommerce Integration enables you, as a course creator, to sell a course as a product.
                                        The integration gives you a platform, a complete online shop-like setup.

                                        Features like shelf display, “bestsellers”, product reviews and ratings, product description, discounts, buying multiple courses at once- can only be achieved with a well-built eCommerce ecosystem.

                                        And WooCommerce is just the answer, the best one.
                                        ",
                                        "eb-textdomain"
                                    );
                                ?>
                                </div>
                                <div class="eb-premium-woo-int-img">
                                    <img src="<?php echo plugins_url("edwiser-bridge/admin/assets/images/woo-int.png"); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="eb-premium-extensions eb-premium-extension-sso">
                            <div class="eb-premium-sso-title">
                                <h1>
                                    <?php
                                        _e(
                                            "Single Sign On",
                                            "eb-textdomain"
                                        );
                                    ?>
                                </h1>
                            </div>
                            <div class="eb-premium-sso-info-wrapper">
                                <div class="eb-premium-sso-img">
                                    <img src="<?php echo plugins_url("edwiser-bridge/admin/assets/images/sso.png"); ?>">
                                </div>
                                <div class="eb-premium-sso-info">
                                    <h4>
                                        <?php
                                        _e("SIMPLIFIED WORKFLOW WITH SSO", "eb-textdomain");
                                        ?>
                                    </h4>
                                <?php
                                    _e(
                                        "
                                        Save the time and energy that you spend remembering multiple usernames and passwords for various accounts. Use Single-Sign On!
                                        Click on an enrolled course while you’re logged into WordPress, and the course on Moodle will directly pop-up in the next tab.

                                        That’s it. No hassle, no time wasted.

                                        Ps. SSO’s latest update has introduced “Social Login”! <b> Connect your Facebook and Google </b> accounts seamlessly, securely, with Single Sign On by Edwiser Bridge.",
                                        "eb-textdomain"
                                    );
                                ?>
                                </div>
                            </div>
                        </div>

                        <div class="eb-premium-extensions eb-premium-extension-selective-synch">
                            <div class="eb-premium-selective-synch-title">
                                <h1>
                                    <?php
                                        _e(
                                            "Selective Synchronisation",
                                            "eb-textdomain"
                                        );
                                    ?>
                                </h1>
                            </div>
                            <div class="eb-premium-selective-synch-info-wrapper">

                                <div class="eb-premium-selective-synch-info">
                                    <h4>
                                        <?php
                                        _e("SYNC COURSES BETWEEN WORDPRESS AND MOODLE", "eb-textdomain");
                                        ?>
                                    </h4>
                                <?php
                                    _e(
                                        "
                                        Imagine this- You have courses stored in Moodle Archives, hundreds of them. But you prefer making only thirty courses available on WordPress at a given time.

                                        What do you do? How will you select only a few of your courses?

                                        Using Selective Synchronisation!

                                        With Selective Synchronisation, take complete control and freedom to choose which courses will be shown on your WordPress site. <b>Just select - and sync!</b>",
                                        "eb-textdomain"
                                    );
                                ?>
                                </div>

                                <div class="eb-premium-selective-synch-img">
                                    <img src="<?php echo plugins_url("edwiser-bridge/admin/assets/images/selective-synch.png"); ?>">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="eb-premium-bottom">
                        <div class="eb-premium-disc">
                            <a href="https://edwiser.org/bridge/extensions/edwiser-bundle/?utm_source=InProduct&utm_medium=Page&utm_campaign=preext&utm_term=Apr&utm_content=cta2">
                                <div>
                                    <?php
                                        _e(
                                            "Buy Edwiser Bridge Bundle @25% Off!",
                                            "eb-textdomain"
                                        );
                                    ?>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>



            <?php
        }
    }


endif;

return new EbSettingsRemui();
