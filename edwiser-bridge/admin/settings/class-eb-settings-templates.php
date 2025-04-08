<?php

/**
 * EDW Product Settings
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (! class_exists('Eb_Settings_Templates')) {
    /**
     * Eb_Settings_Templates.
     */
    class Eb_Settings_Templates extends EB_Settings_Page
    {
        /**
         * Addon licensing.
         *
         * @var array $addon_licensing addon licensing
         */
        public $addon_licensing;

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->addon_licensing = array('test');
            $this->_id = 'templates';
            $this->label = __('Templates', 'edwiser-bridge');

            add_filter('eb_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('eb_settings_' . $this->_id, array($this, 'output'));
            add_action('eb_settings_save_' . $this->_id, array($this, 'save'));
            add_action('eb_sections_' . $this->_id, array($this, 'output_sections'));
        }

        /**
         * Get sections.
         *
         * @since  1.0.0
         *
         * @return array
         */
        public function get_sections()
        {
            $sections = array(
                ''           => __('Gutenberg Templates', 'edwiser-bridge'),
            );

            if (function_exists('is_plugin_active') && is_plugin_active('edwiser-bridge-pro/edwiser-bridge-pro.php')) {
                $sections['elementor-templates'] = __('Elementor Pro Templates', 'edwiser-bridge');
            }

            return apply_filters('eb_get_sections_' . $this->_id, $sections);
        }

        /**
         * Output the settings.
         *
         * @since  1.0.0
         */
        public function output()
        {
            global $current_section;

            // Check if we're in a specific section
            if ('elementor-templates' === $current_section) {
                // Hide the save button for elementor templates
                $GLOBALS['hide_save_button'] = true;

                $this->handle_elementor_template_actions();

                require_once plugin_dir_path(dirname(__FILE__)) . 'partials/html-elementor-templates.php';
            } else {
                require_once plugin_dir_path(dirname(__FILE__)) . 'partials/html-gutenberg-templates.php';
            }

            // Output settings fields if needed
            $settings = $this->get_settings($current_section);
            if (!empty($settings)) {
                Eb_Admin_Settings::output_fields($settings);
            }
        }

        /**
         * Save settings.
         *
         * @since  1.0.0
         */
        public function save()
        {
            global $current_section;

            $settings = $this->get_settings($current_section);
            Eb_Admin_Settings::save_fields($settings);
        }

        /**
         * Get settings array.
         *
         * @since  1.0.0
         *
         * @param string $current_section name of the section.
         * @return array
         */
        public function get_settings($current_section = '')
        {
            $settings = apply_filters(
                'eb_licensing',
                array(
                    array(
                        'type' => 'sectionend',
                        'id'   => 'gutenberg_template_settings',
                    ),
                )
            );

            return apply_filters('eb_get_settings_' . $this->_id, $settings, $current_section);
        }

        /**
         * Handle elementor template actions.
         *
         * @since  1.0.0
         */
        public function handle_elementor_template_actions()
        {
            if (isset($_GET['action']) && isset($_GET['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'eb_pro_elementor_template')) {
                $template = isset($_GET['template']) ? sanitize_text_field(wp_unslash($_GET['template'])) : '';

                if (class_exists('\app\wisdmlabs\edwiserBridgePro\includes\Eb_Pro_Activator')) {
                    if ('product_archive' === $template) {
                        $activator = new \app\wisdmlabs\edwiserBridgePro\includes\Eb_Pro_Activator();
                        $post_id = $activator::create_elementor_shop_page_template();
                        if ($post_id) {
                            wp_safe_redirect(admin_url('post.php?post=' . $post_id . '&action=elementor'));
                            exit;
                        }
                    } elseif ('product_single' === $template) {
                        $activator = new \app\wisdmlabs\edwiserBridgePro\includes\Eb_Pro_Activator();
                        $post_id = $activator::create_elementor_product_page_template();
                        if ($post_id) {
                            wp_safe_redirect(admin_url('post.php?post=' . $post_id . '&action=elementor'));
                            exit;
                        }
                    }
                }
            }
        }
    }
}

return new Eb_Settings_Templates();
