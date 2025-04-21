<?php

/**
 * Partial: Page - Extensions.
 *
 * @package    Edwiser Bridge
 * @var object
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
$templates = array(
    'product_archive' => array(
        'title' => __('Shop Page Template', 'edwiser-bridge-pro'),
        'desc'  => __('Customize the design of your entire shop page. This option allows you to apply consistent branding and layout across all your product listings, enhancing the overall shopping experience for your learners.', 'edwiser-bridge-pro'),
        'img'   => 'product-archive.png',
        'template_id' => get_option('eb_pro_elementor_shop_page_template_id'),
    ),
    'product_single' => array(
        'title' => __('Product Page Template', 'edwiser-bridge-pro'),
        'desc'  => __('Tailor the design of the product page to showcase each product uniquely. This option enables you to highlight specific product features, benefits, and details, optimizing the presentation for better conversions and user engagement.', 'edwiser-bridge-pro'),
        'img'   => 'product-single.png',
        'template_id' => get_option('eb_pro_elementor_single_product_page_template_id'),
    ),
);

// check if elementor pro is active
$elementor_pro = (is_plugin_active('elementor-pro/elementor-pro.php')) ? true : false;
$module_data = get_option('eb_pro_modules_data');
$woo_int_enabled = (isset($module_data['woo_integration']) && 'active' === $module_data['woo_integration']) ? true : false;
?>
<div class="eb__templates-wrapper">
    <div class="eb__templates">
        <div class="eb_table_row">
            <h2><?php esc_html_e('Elementor Pro Templates', 'edwiser-bridge-pro'); ?></h2>
            <?php
            if (! $elementor_pro) {
            ?>
                <div class="eb-pro-elementor-notice">
                    <div class="warning-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="40" viewBox="0 0 45 40" fill="none">
                            <g clip-path="url(#clip0_516_8715)">
                                <path d="M3.1892 39.1928C1.86372 39.1928 0.797363 38.1134 0.797363 36.7717C0.797363 36.3479 0.906989 35.9242 1.11627 35.5611L20.251 2.01754C20.9087 0.857389 22.3737 0.463946 23.5198 1.12977C23.8886 1.34163 24.1875 1.64427 24.3968 2.01754L43.5216 35.551C44.1793 36.7111 43.7906 38.1941 42.6446 38.8599C42.2758 39.0718 41.8672 39.1827 41.4486 39.1827L3.1892 39.1928Z" fill="#FFD21E" />
                                <path d="M22.3239 1.61424C22.8919 1.60416 23.4201 1.91689 23.7091 2.42131L42.8339 35.9648C43.2724 36.7416 43.0133 37.7303 42.2459 38.1741C42.0067 38.3154 41.7276 38.386 41.4486 38.386H3.18916C2.31215 38.386 1.5946 37.6596 1.5946 36.7719C1.5946 36.4894 1.66436 36.2069 1.80389 35.9648L20.9386 2.42131C21.2176 1.91689 21.7458 1.60416 22.3239 1.61424ZM22.3239 0.00012023C21.1778 -0.00996804 20.1214 0.615505 19.5633 1.61424L0.428579 35.1577C-0.448428 36.7013 0.0698033 38.6786 1.5946 39.5663C2.08293 39.8488 2.63106 40.0001 3.18916 40.0001H41.4486C43.2126 40.0001 44.6377 38.5575 44.6377 36.7719C44.6377 36.2069 44.4882 35.6521 44.2092 35.1577L25.0844 1.61424C24.5164 0.615505 23.46 -0.00996804 22.3239 0.00012023Z" fill="#373737" />
                                <path d="M22.3238 33.7151C23.2045 33.7151 23.9184 32.9924 23.9184 32.1009C23.9184 31.2095 23.2045 30.4868 22.3238 30.4868C21.4432 30.4868 20.7292 31.2095 20.7292 32.1009C20.7292 32.9924 21.4432 33.7151 22.3238 33.7151Z" fill="#373737" />
                                <path d="M22.3238 11.1172C23.2008 11.1172 23.9184 11.8435 23.9184 12.7313V25.6443C23.9184 26.5321 23.2008 27.2584 22.3238 27.2584C21.4468 27.2584 20.7292 26.5321 20.7292 25.6443V12.7313C20.7292 11.8335 21.4368 11.1172 22.3238 11.1172Z" fill="#373737" />
                            </g>
                            <defs>
                                <clipPath id="clip0_516_8715">
                                    <rect width="44.6377" height="40" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <div class="notice-body">
                        <p><?php esc_html_e('Based on you license key, it seems that you have not installed the ‘Elementor PRO’. Please download and install the ‘Elementor PRO’ to use below Edwiser bridge templates.', 'edwiser-bridge-pro'); ?></p>
                    </div>
                </div>
            <?php
            }
            if (! $woo_int_enabled) {
            ?>
                <div class="eb-pro-elementor-notice">
                    <div class="warning-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="40" viewBox="0 0 45 40" fill="none">
                            <g clip-path="url(#clip0_516_8715)">
                                <path d="M3.1892 39.1928C1.86372 39.1928 0.797363 38.1134 0.797363 36.7717C0.797363 36.3479 0.906989 35.9242 1.11627 35.5611L20.251 2.01754C20.9087 0.857389 22.3737 0.463946 23.5198 1.12977C23.8886 1.34163 24.1875 1.64427 24.3968 2.01754L43.5216 35.551C44.1793 36.7111 43.7906 38.1941 42.6446 38.8599C42.2758 39.0718 41.8672 39.1827 41.4486 39.1827L3.1892 39.1928Z" fill="#FFD21E" />
                                <path d="M22.3239 1.61424C22.8919 1.60416 23.4201 1.91689 23.7091 2.42131L42.8339 35.9648C43.2724 36.7416 43.0133 37.7303 42.2459 38.1741C42.0067 38.3154 41.7276 38.386 41.4486 38.386H3.18916C2.31215 38.386 1.5946 37.6596 1.5946 36.7719C1.5946 36.4894 1.66436 36.2069 1.80389 35.9648L20.9386 2.42131C21.2176 1.91689 21.7458 1.60416 22.3239 1.61424ZM22.3239 0.00012023C21.1778 -0.00996804 20.1214 0.615505 19.5633 1.61424L0.428579 35.1577C-0.448428 36.7013 0.0698033 38.6786 1.5946 39.5663C2.08293 39.8488 2.63106 40.0001 3.18916 40.0001H41.4486C43.2126 40.0001 44.6377 38.5575 44.6377 36.7719C44.6377 36.2069 44.4882 35.6521 44.2092 35.1577L25.0844 1.61424C24.5164 0.615505 23.46 -0.00996804 22.3239 0.00012023Z" fill="#373737" />
                                <path d="M22.3238 33.7151C23.2045 33.7151 23.9184 32.9924 23.9184 32.1009C23.9184 31.2095 23.2045 30.4868 22.3238 30.4868C21.4432 30.4868 20.7292 31.2095 20.7292 32.1009C20.7292 32.9924 21.4432 33.7151 22.3238 33.7151Z" fill="#373737" />
                                <path d="M22.3238 11.1172C23.2008 11.1172 23.9184 11.8435 23.9184 12.7313V25.6443C23.9184 26.5321 23.2008 27.2584 22.3238 27.2584C21.4468 27.2584 20.7292 26.5321 20.7292 25.6443V12.7313C20.7292 11.8335 21.4368 11.1172 22.3238 11.1172Z" fill="#373737" />
                            </g>
                            <defs>
                                <clipPath id="clip0_516_8715">
                                    <rect width="44.6377" height="40" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <div class="notice-body">
                        <p><?php esc_html_e('It seems that you have not enabled WooCommerce Integration Feature from Edwiser Bridge Pro Featuers. Please activate the ‘WooCommerce Integration’ to use below Edwiser bridge templates, from', 'edwiser-bridge-pro'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=eb-settings&tab=pro_features')); ?>"><?php esc_html_e('here', 'edwiser-bridge-pro'); ?></a></p>
                    </div>
                </div>
            <?php
            }
            foreach ($templates as $key => $template) {
                if ($elementor_pro && $woo_int_enabled && $template['template_id']) {
                    $edit_link = add_query_arg(array('post' => $template['template_id'], 'action' => 'elementor'), admin_url('post.php'));
                    $edit_html = '<a target="_blank" style="font-size:13px;" href="' . esc_url($edit_link) . '">' . esc_html__('Edit', 'edwiser-bridge-pro') . '</a>';
                } else {
                    $edit_html = '';
                }
            ?>
                <div class="eb_template">
                    <div class="eb_template_img">
                        <img src="<?php echo esc_url(EB_PRO_PLUGIN_URL . 'admin/assets/images/' . $template['img']); ?>" alt="<?php echo esc_attr($template['title']); ?>">
                    </div>
                    <div class="eb_template_content">
                        <h2><?php echo esc_html($template['title']); ?> <?php echo $edit_html; ?></h2>
                        <p><?php echo esc_html($template['desc']); ?></p>
                        <?php
                        if ($elementor_pro && $woo_int_enabled) {
                        ?>
                            <div class="eb_template_actions" data-template="<?php echo esc_attr($key); ?>">
                                <?php
                                if (! $template['template_id']) {
                                ?>
                                    <a href="#" data-template="<?php echo esc_attr($key); ?>" class="eb-pro-button eb-pro-primary eb-template-restore"><?php esc_html_e('Create Template', 'edwiser-bridge-pro'); ?></a>
                                <?php
                                } else {
                                ?>
                                    <!-- <a target="_blank" href="<?php echo esc_url($edit_link); ?>" class="eb-pro-button eb-pro-primary"><?php esc_html_e('Edit Template', 'edwiser-bridge-pro'); ?></a> -->
                                    <a href="#" data-template="<?php echo esc_attr($key); ?>" class="eb-pro-button eb-pro-primary eb-template-restore"><?php esc_html_e('Use this template', 'edwiser-bridge-pro'); ?></a>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="eb-template-restore-confirm" data-template="<?php echo esc_attr($key); ?>">
                                <div class="warning-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="34" height="31" viewBox="0 0 34 31" fill="none">
                                        <g clip-path="url(#clip0_517_8175)">
                                            <path d="M2.39178 29.895C1.39767 29.895 0.5979 29.0854 0.5979 28.0791C0.5979 27.7613 0.68012 27.4435 0.837084 27.1712L15.1881 2.01352C15.6814 1.14341 16.7802 0.848326 17.6397 1.3477C17.9163 1.50659 18.1405 1.73357 18.2975 2.01352L32.6411 27.1636C33.1344 28.0337 32.8429 29.1459 31.9833 29.6453C31.7067 29.8042 31.4003 29.8874 31.0864 29.8874L2.39178 29.895Z" fill="#FFD21E" />
                                            <path d="M16.7428 1.71068C17.1689 1.70312 17.565 1.93767 17.7818 2.31598L32.1253 27.4736C32.4542 28.0562 32.2599 28.7977 31.6843 29.1306C31.505 29.2365 31.2957 29.2895 31.0864 29.2895H2.39181C1.73405 29.2895 1.19589 28.7447 1.19589 28.0789C1.19589 27.8671 1.24821 27.6552 1.35285 27.4736L15.7039 2.31598C15.9132 1.93767 16.3093 1.70312 16.7428 1.71068ZM16.7428 0.50009C15.8833 0.492524 15.091 0.961629 14.6724 1.71068L0.321373 26.8683C-0.336382 28.0259 0.0522915 29.5089 1.19589 30.1747C1.56214 30.3866 1.97324 30.5001 2.39181 30.5001H31.0864C32.4094 30.5001 33.4782 29.4181 33.4782 28.0789C33.4782 27.6552 33.3661 27.2391 33.1568 26.8683L18.8133 1.71068C18.3872 0.961629 17.5949 0.492524 16.7428 0.50009Z" fill="#373737" />
                                            <path d="M16.7429 25.7864C17.4033 25.7864 17.9388 25.2444 17.9388 24.5758C17.9388 23.9072 17.4033 23.3652 16.7429 23.3652C16.0824 23.3652 15.5469 23.9072 15.5469 24.5758C15.5469 25.2444 16.0824 25.7864 16.7429 25.7864Z" fill="#373737" />
                                            <path d="M16.7429 8.83789C17.4006 8.83789 17.9388 9.38266 17.9388 10.0485V19.7332C17.9388 20.3991 17.4006 20.9438 16.7429 20.9438C16.0851 20.9438 15.5469 20.3991 15.5469 19.7332V10.0485C15.5469 9.37509 16.0776 8.83789 16.7429 8.83789Z" fill="#373737" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_517_8175">
                                                <rect width="33.4783" height="30" fill="white" transform="translate(0 0.5)" />
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                                <?php
                                if ($template['template_id']) {
                                ?>
                                    <div class="confirmation-body">
                                        <p class="confirmation-info"><?php esc_html_e('Restoring this page will revert it to its default state, undoing any customizations you may have made. Proceed with caution if you intend to revert to the original design.', 'edwiser-bridge-pro'); ?></p>
                                        <p class="confirmation-info"><?php esc_html_e('Note: If you are setting this up for the first time, you can safely ignore this warning.', 'edwiser-bridge-pro'); ?></p>
                                        <div class="confirmation-action">
                                            <?php
                                            $restore_link = add_query_arg(
                                                array(
                                                    'action' => 'eb_pro_restore_template',
                                                    'template' => $key,
                                                    'nonce' => wp_create_nonce('eb_pro_elementor_template')
                                                ),
                                                admin_url('admin.php?page=eb-settings&tab=templates&section=elementor-templates')
                                            );
                                            ?>
                                            <span><?php esc_html_e('Are you sure you want to ‘Restore’ this page?', 'edwiser-bridge-pro'); ?></span>
                                            <a href="<?php echo esc_url($restore_link); ?>" class="eb-pro-button eb-pro-secondary eb-template-restore-confirm-yes"><?php esc_html_e('Yes', 'edwiser-bridge-pro'); ?></a>
                                            <a href="#" data-template="<?php echo esc_attr($key); ?>" class="eb-pro-button eb-pro-secondary eb-template-restore-confirm-no"><?php esc_html_e('No', 'edwiser-bridge-pro'); ?></a>
                                        </div>
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <div class="confirmation-body">
                                        <p class="confirmation-info"><?php esc_html_e('This will overide your old template with the Edwiser Bridge’s new template. No data will be lost.', 'edwiser-bridge-pro'); ?></p>
                                        <div class="confirmation-action">
                                            <?php
                                            $create_link = add_query_arg(
                                                array(
                                                    'action' => 'eb_pro_create_template',
                                                    'template' => $key,
                                                    'nonce' => wp_create_nonce('eb_pro_elementor_template')
                                                ),
                                                admin_url('admin.php?page=eb-settings&tab=templates&section=elementor-templates')
                                            );
                                            ?>
                                            <span><?php esc_html_e('Are you sure you want to create this template?', 'edwiser-bridge-pro'); ?></span>
                                            <a href="<?php echo esc_url($create_link); ?>" class="eb-pro-button eb-pro-secondary eb-template-restore-confirm-yes"><?php esc_html_e('Yes', 'edwiser-bridge-pro'); ?></a>
                                            <a href="#" data-template="<?php echo esc_attr($key); ?>" class="eb-pro-button eb-pro-secondary eb-template-restore-confirm-no"><?php esc_html_e('No', 'edwiser-bridge-pro'); ?></a>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        <div class="eb-license-help">
            <div class="eb-help-tootip">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="11.5" fill="white" stroke="#C4C4C4" />
                    <path d="M10.5332 14.1085V13.5708C10.5332 13.1058 10.6325 12.7013 10.8311 12.3574C11.0297 12.0135 11.393 11.6478 11.921 11.2603C12.4296 10.897 12.7638 10.6015 12.9237 10.3738C13.0884 10.1462 13.1707 9.89188 13.1707 9.61093C13.1707 9.29608 13.0545 9.05631 12.8219 8.89162C12.5894 8.72692 12.2649 8.64458 11.8483 8.64458C11.1217 8.64458 10.2934 8.88193 9.36341 9.35663L8.57143 7.76541C9.65162 7.15992 10.7972 6.85718 12.0082 6.85718C13.006 6.85718 13.798 7.09695 14.3841 7.5765C14.9751 8.05604 15.2705 8.69544 15.2705 9.49468C15.2705 10.0275 15.1494 10.4877 14.9072 10.8752C14.6651 11.2627 14.2049 11.6987 13.5267 12.183C13.0617 12.527 12.7662 12.7885 12.6403 12.9678C12.5192 13.147 12.4587 13.3819 12.4587 13.6725V14.1085H10.5332ZM10.3007 16.5934C10.3007 16.1865 10.4097 15.8789 10.6277 15.6707C10.8456 15.4624 11.1629 15.3582 11.5795 15.3582C11.9815 15.3582 12.2915 15.4648 12.5095 15.6779C12.7323 15.891 12.8437 16.1962 12.8437 16.5934C12.8437 16.9761 12.7323 17.2788 12.5095 17.5016C12.2867 17.7196 11.9767 17.8286 11.5795 17.8286C11.1726 17.8286 10.8577 17.722 10.6349 17.5089C10.4121 17.2909 10.3007 16.9858 10.3007 16.5934Z" fill="#F98012" />
                </svg>
                <span class="eb-help-tootip-content"><?php esc_html_e('Looking for help?', 'edwiser-bridge-pro'); ?></span>
            </div>
            <ul>
                <li><a target="_blank" href="https://edwiser.org/documentation/edwiser-bridge-woocommerce-integration/elementor-pro-enhanced-templates/"><?php esc_html_e('For setup instructions, click here.', 'edwiser-bridge-pro'); ?></a></li>
                <li><?php esc_html_e('Talk to us:', 'edwiser-bridge-pro'); ?> <a href="mailto:edwiser@wisdmlabs.com">edwiser@wisdmlabs.com</a></li>
            </ul>
        </div>
    </div>
</div>
