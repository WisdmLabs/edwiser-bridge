<?php

$documentation = '';

$extensionsDetails = apply_filters(
    'eb_setting_help_data',
    array(
        'woo-int'         => array(
            'name'   =>'Woocommerce Integration',
            'path'   => 'woocommerce-integration/bridge-woocommerce.php',
            'doc'    =>'https://edwiser.org/bridge/extensions/woocommerce-integration/documentation/',
            'rating' =>'https://edwiser.org/bridge/extensions/woocommerce-integration/documentation/'
            ),
        'sso'             => array(
            'name'   =>'Single Sign On',
            'path'   => 'edwiser-bridge-sso/sso.php',
            'doc'    =>'https://edwiser.org/bridge/extensions/single-sign-on/#Documentation',
            'rating' =>'https://edwiser.org/bridge/extensions/woocommerce-integration/documentation/'
            ),
        'bulk-purchase'   => array(
            'name'   =>'Single Sign On',
            'path'   => 'edwiser-multiple-users-course-purchase/edwiser-multiple-users-course-purchase.php',
            'doc'    =>'https://edwiser.org/bridge/extensions/bulk-purchase/documentation/',
            'rating' =>'https://edwiser.org/bridge/extensions/woocommerce-integration/documentation/'
            ),
        'selective-synch' =>  array(
            'name'   => 'Single Sign On',
            'path'   => 'selective-synchronization/selective-synchronization.php',
            'doc'    => 'https://edwiser.org/documentation/selective-synchronization/',
            'rating' =>'https://edwiser.org/bridge/extensions/woocommerce-integration/documentation/'
            )
    )
);


foreach ($extensionsDetails as $key => $value) {
    if (is_plugin_active($value['path'])) {
        $documentation .='<li>
                            <a href="'. $value['doc'] .'"> '.__($value['name'], 'eb-textdomain') .'</a>
                        </li>';
    }
}



?>

<div>
    <div class="eb_settings_pop_btn_wrap">
        <div class="eb_settings_help_btn_wrap">
            <button class='eb_open_btn'> <?= __('Get Help', 'eb-textdomain') ?></button>
        </div>
        <div class="eb_settings_rate_btn_wrap">
            <a class="eb_open_btn" target="_blank" href="https://wordpress.org/support/plugin/edwiser-bridge/reviews/">
                <?= __('Rate Us', 'eb-textdomain') ?>
            </a>
        </div>
    </div>

    <div class="eb_setting_pop_up_wrap">
        <div class='eb-setting-right-sidebar'>

            <div class="eb_setting_help_pop_up">
                <div>
                    <span class="closebtn">×</span>
                </div>
                <!-- <h3 class="eb-setting-sidebar-h3"> Help </h3> -->
                <div class="eb-setting-help-accordion">
                    <h4 class='eb_setting_help_h4'><?= __('Documentation', 'eb-teh3xtdomain') ?></h4>
                    <div>
                        <ol>

                            <li>
                                 <a href="https://edwiser.org/bridge/documentation/"> <?= __('Edwiser Bridge', 'eb-textdomain') ?></a>
                            </li>


                        <?=  $documentation ?>

                        </ol>
                    </div>

                    <h4 class='eb_setting_help_h4'><?= __('FAQs', 'eb-textdomain') ?></h4>
                    <div>
                        <a href="https://knowledgebase.edwiser.org/en/category/edwiser-bridge-plugin-5d8teq/"> <?= __('Click here to check frequently asked questions.', 'eb-textdomain') ?>  </a>
                    </div>

                    <h4 class='eb_setting_help_h4'><?= __('Contact Us', 'eb-textdomain') ?></h4>
                    <div>
                        <a href="https://edwiser.org/bridge/"> <?= __('Click here to chat with us', 'eb-textdomain') ?>  </a>
                    </div>
                </div>
            </div>


            <!-- <div class="eb_setting_rate_pop_up">
                <div>
                    <span class="closebtn">×</span>
                </div>
                <h4 class="eb_setting_help_h4"><?= __('Rate And Review', 'eb-textdomain') ?></h4>
                <div>
                    <a href="https://wordpress.org/support/plugin/edwiser-bridge/reviews/"> <?= __('Click here', 'eb-textdomain') ?> </a>
                    <?= __(' to rate us', 'eb-textdomain') ?>
                </div>
            </div> -->


            <!-- <div>
                <h3 class="eb-setting-sidebar-h3"> Other Products </h3>
                <div>
                    <div> LINK 1 </div>
                    <div> LINK 2 </div>
                    <div> LINK 3 </div>
                </div>
            </div> -->

        </div>
    </div>
</div>