<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace wisdmalbs\newtowrk\marketing;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists("wisdmalbs\newtowrk\marketing\Licensing")) {
    class Licensing
    {
        private $settingMessages = '';
        private $licensingInfo = array();

        public function __construct()
        {
            $this->settingMessages = apply_filters('nm_setting_messages', $settingMessages);
            $this->licensingInfo = apply_filters('nm_licensing_information', $licensingInfo);
        }

        public function outPut()
        {
            DashBoard::dashBoardHeader();
            if (!empty($this->settingMessages)) {
                echo $this->settingMessages;
            }
            if (!empty($this->licensingInfo)) {
                ?>
                <div class="eb_table">
                    <div class="eb_table_body">
                        <?php
                        foreach ($this->licensingInfo as $single) {
                            ?>
                            <form name="<?php echo $single['plugin_slug'].'_licensing_form';
                            ?>" method="post" id="mainform" >
                                <div class="eb_table_row">

                                    <div class="eb_table_cell_1">
                                        <?php echo $single['plugin_name'];
                            ?>
                                    </div>

                                    <div class="eb_table_cell_2">
                                        <?php echo $single['license_key'];
                            ?>
                                    </div>

                                    <div class="eb_table_cell_3">
                                        <?php echo $single['license_status'];
                            ?>
                                    </div>

                                    <div class="eb_table_cell_4">
                                        <?php echo $single['activate_license'];
                            ?>
                                    </div>

                                </div>                               
                            </form>
                            <?php
                        }
                ?>
                    </div>
                </div>
                <?php
            } else {
                printf(
                    __('%1s You do not have any extensions activated. %2s Please activate any installed extensions. If you do not have any extensions, you can take a look at the list %3s here%4s.%5s', NetworkMarketing::$nmTextDomain),
                    '<div class="update-nag"><strong>',
                    '</strong>',
                    '<a href="http://www.wisdmlabs.com/" target="_blank">',
                    '</a>',
                    '</div>'
                );
            }
        }
    }
}
