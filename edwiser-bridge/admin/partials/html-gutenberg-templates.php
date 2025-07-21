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

// Get enabled templates status
$enabled_templates = get_option('eb_enabled_templates', array());

// Get all pages for dropdowns
$pages = get_pages(array(
    'sort_column' => 'post_title',
    'sort_order' => 'ASC',
));

// Get the currently selected page IDs
$woo_gutenberg_pages = get_option('eb_woo_gutenberg_pages', array());
$course_gutenberg_pages = get_option('eb_gutenberg_pages', array());
$shop_page_id = ! empty($woo_gutenberg_pages['eb_pro_shop_page_id']) ? $woo_gutenberg_pages['eb_pro_shop_page_id'] : 0;
$cart_page_id = ! empty($woo_gutenberg_pages['eb_pro_cart_page_id']) ? $woo_gutenberg_pages['eb_pro_cart_page_id'] : 0;
$checkout_page_id = ! empty($woo_gutenberg_pages['eb_pro_checkout_page_id']) ? $woo_gutenberg_pages['eb_pro_checkout_page_id'] : 0;
$single_product_page_id = ! empty($woo_gutenberg_pages['eb_pro_single_product_page_id']) ? $woo_gutenberg_pages['eb_pro_single_product_page_id'] : 0;
$thankyou_page_id = ! empty($woo_gutenberg_pages['eb_pro_thank_you_page_id']) ? $woo_gutenberg_pages['eb_pro_thank_you_page_id'] : 0;
$single_course_page_id = ! empty($course_gutenberg_pages['single_course']) ? $course_gutenberg_pages['single_course'] : 0;
$courses_page_id = ! empty($course_gutenberg_pages['all_courses']) ? $course_gutenberg_pages['all_courses'] : 0;

$eb_pro_active = is_plugin_active('edwiser-bridge-pro/edwiser-bridge-pro.php');
$module_data = get_option('eb_pro_modules_data');
$woo_integration_enabled = (isset($module_data['woo_integration']) && 'active' === $module_data['woo_integration']) ? true : false;

$is_license_valid = 'valid' === get_option('edd_edwiser_bridge_pro_license_status');
?>

<div class="eb__templates-wrapper">
    <div class="eb__templates">
        <h2><?php esc_html_e('Gutenberg Templates', 'edwiser-bridge'); ?></h2>
        <p class="eb__note">
            <strong><?php esc_html_e('NOTE:', 'edwiser-bridge'); ?></strong>
            <?php esc_html_e('Please disable the setting in any other plugin or code that might override these templates otherwise these will not work.', 'edwiser-bridge'); ?>
        </p>
        <?php foreach ($templates as $key => $template) :
            $is_enabled = isset($enabled_templates[$key]) ? $enabled_templates[$key] : false;
            $switch_id = 'switch_' . $key;
            $has_page_dropdown = in_array($key, array('shop', 'cart', 'checkout', 'single_product', 'thank_you', 'single_course', 'all_courses'));

            // Refactored: use associative array for page ID lookup
            $page_id_map = array(
                'shop' => $shop_page_id,
                'cart' => $cart_page_id,
                'checkout' => $checkout_page_id,
                'single_product' => $single_product_page_id,
                'thank_you' => $thankyou_page_id,
                'single_course' => $single_course_page_id,
                'all_courses' => $courses_page_id,
            );
            $current_page_id = isset($page_id_map[$key]) ? $page_id_map[$key] : 0;

            $image_url = plugins_url('assets/images/blocks-images/thumbnail/' . $template['img'], dirname(__FILE__));
            $image_full_url = plugins_url('assets/images/blocks-images/' . $template['img'], dirname(__FILE__));

            $template_id = isset($template['template_id']) ? $template['template_id'] : 0;
        ?>
            <div class="eb__template-item">
                <div class="eb__template-preview">
                    <div class="eb__image-container">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($template['title']); ?>">
                        <div class="eb__magnify-icon" data-image="<?php echo esc_url($image_full_url); ?>" data-title="<?php echo esc_attr($template['title']); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" x2="16.65" y1="21" y2="16.65" />
                                <line x1="11" x2="11" y1="8" y2="14" />
                                <line x1="8" x2="14" y1="11" y2="11" />
                            </svg>
                        </div>
                    </div>

                    <?php if ($template['is_pro']) : ?>
                        <div class="eb__pro-badge"> <?php echo esc_html__('PRO', 'edwiser-bridge'); ?> </div>
                    <?php endif; ?>
                </div>
                <div class="eb__template-content">
                    <h3><?php echo esc_html($template['title']); ?></h3>
                    <p><?php echo esc_html($template['desc']); ?></p>
                    <?php if (isset($template['note'])) : ?>
                        <p class="eb__template-note"><strong>Note: </strong><?php echo esc_html($template['note']); ?> </p>
                    <?php endif;
                    ?>
                    <div class="eb-switch-container">
                        <label class="eb-switch" for="<?php echo esc_attr($switch_id); ?>">
                            <input type="checkbox"
                                <?php disabled(($template['is_pro'] && (!$eb_pro_active || !$is_license_valid || !$woo_integration_enabled))); ?>
                                id="<?php echo esc_attr($switch_id); ?>"
                                name="eb_enabled_templates[<?php echo esc_attr($key); ?>]"
                                value="1"
                                <?php checked((!$template['is_pro'] && $is_enabled) || ($template['is_pro'] && $is_enabled && $eb_pro_active && $is_license_valid && $woo_integration_enabled)); ?>>
                            <span class="slider round"></span>
                        </label>
                        <span class="switch-label"><?php echo (!$template['is_pro'] && $is_enabled) || ($template['is_pro'] && $is_enabled && $eb_pro_active && $is_license_valid && $woo_integration_enabled) ? esc_html__('On', 'edwiser-bridge') : esc_html__('Off', 'edwiser-bridge'); ?></span>
                    </div>
                    <div class="eb__template-actions">
                        <?php if ($has_page_dropdown && isset($template['page_option'])) : ?>
                            <select id="<?php echo esc_attr($key); ?>_page_select" name="<?php echo esc_attr($template['page_option']); ?>" class="eb__page-select">
                                <option value="" selected disabled><?php esc_html_e('Select a page', 'edwiser-bridge'); ?></option>
                                <?php foreach ($pages as $page) : ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($current_page_id, $page->ID); ?>>
                                        <?php echo esc_html(! empty($page->post_title) ? $page->post_title : __('Untitled', 'edwiser-bridge') . ' (' . $page->ID . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <?php
                        // Only show buttons if the current page exists
                        $existing_page_ids = array_map(function ($page) {
                            return $page->ID;
                        }, $pages);
                        if (((!$template['is_pro'] && $template_id) || ($template['is_pro'] && $eb_pro_active && $is_license_valid)) && $current_page_id && in_array($current_page_id, $existing_page_ids)) : ?>
                            <a href="<?php echo esc_url(get_permalink($template_id)); ?>" target="_blank" class="eb__btn eb__btn-view" data-template="<?php echo esc_attr($key); ?>"><?php esc_html_e('View page', 'edwiser-bridge'); ?></a>

                            <a target="_blank" href="<?php echo esc_url(admin_url('post.php?post=' . $template['template_id'] . '&action=edit')); ?>" class="eb__btn eb__btn-edit"><?php esc_html_e('Edit page', 'edwiser-bridge'); ?><svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.24532 3.93555C4.52339 3.93555 3.93555 4.51929 3.93555 5.24532V12.7547C3.93555 13.4766 4.51929 14.0644 5.24532 14.0644H12.7547C13.4766 14.0644 14.0644 13.4807 14.0644 12.7547V10.9085C14.0644 10.6502 14.2739 10.4407 14.5322 10.4407C14.7906 10.4407 15 10.6502 15 10.9085V12.7547C15 13.9995 13.9912 15 12.7547 15H5.24532C4.00046 15 3 13.9912 3 12.7547V5.24532C3 4.00046 4.00884 3 5.24532 3H7.04782C7.30616 3 7.51559 3.20943 7.51559 3.46778C7.51559 3.72612 7.30616 3.93555 7.04782 3.93555H5.24532ZM9.48025 4.11642C9.48025 3.85808 9.68968 3.64865 9.94803 3.64865H12.3617C13.3997 3.64865 14.2391 4.48802 14.2391 5.52599V7.73389C14.2391 7.99223 14.0297 8.20166 13.7713 8.20166C13.513 8.20166 13.3035 7.99223 13.3035 7.73389V5.52599C13.3035 5.43961 13.292 5.35601 13.2704 5.27662L7.17566 11.4796C6.9946 11.6639 6.69843 11.6665 6.51415 11.4854C6.32987 11.3044 6.32727 11.0082 6.50833 10.8239L12.6075 4.6164C12.5292 4.5954 12.4468 4.5842 12.3617 4.5842H9.94803C9.68968 4.5842 9.48025 4.37477 9.48025 4.11642Z" fill="#819596" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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

<!-- Modal -->
<div id="eb-image-modal" class="eb-modal">
    <div class="eb-modal-content">
        <div class="eb-close"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x">
                <path d="M18 6 6 18" />
                <path d="m6 6 12 12" />
            </svg></div>
        <h3 id="eb-modal-title"></h3>
        <img id="eb-modal-image" src="" alt="">
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $('.eb__page-select').select2({
            width: 'resolve',
            placeholder: 'Select a page'
        });

        // Toggle switch handling
        $('.eb-switch input').on('change', function() {
            var $switch = $(this);
            var $label = $switch.closest('.eb-switch-container').find('.switch-label');

            if ($switch.is(':checked')) {
                $label.text('On');
            } else {
                $label.text('Off');
            }

            // Enable/disable associated dropdown
            var key = $switch.attr('id').replace('switch_', '');
            var $dropdown = $('#' + key + '_page_select');
            if ($dropdown.length) {
                $dropdown.prop('disabled', !$switch.is(':checked'));
            }
        });

        // Set initial state of dropdowns on page load
        $('.eb-switch input').each(function() {
            var $switch = $(this);
            var key = $switch.attr('id').replace('switch_', '');
            var $dropdown = $('#' + key + '_page_select');
            if ($dropdown.length) {
                $dropdown.prop('disabled', !$switch.is(':checked'));
            }
        });

        // Image magnification functionality
        $('.eb__magnify-icon').on('click', function() {
            var imageUrl = $(this).data('image');
            var title = $(this).data('title');

            $('#eb-modal-title').text(title);
            $('#eb-modal-image').attr('src', imageUrl);
            $('#eb-image-modal').fadeIn(300);
            $('body').addClass('modal-open');
        });

        // Close modal
        $('.eb-close').on('click', function() {
            $('#eb-image-modal').fadeOut(300);
            $('body').removeClass('modal-open');
        });

        // Close modal when clicking outside
        $(window).on('click', function(event) {
            if ($(event.target).is('#eb-image-modal')) {
                $('#eb-image-modal').fadeOut(300);
                $('body').removeClass('modal-open');
            }
        });

        // Close modal on ESC key
        $(document).on('keydown', function(event) {
            if (event.key === "Escape") {
                $('#eb-image-modal').fadeOut(300);
                $('body').removeClass('modal-open');
            }
        });
    });
</script>