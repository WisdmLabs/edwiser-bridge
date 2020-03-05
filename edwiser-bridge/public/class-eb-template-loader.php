<?php

/**
 * Template Loader
 *
 * Define template loading and overriding functionality
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EbTemplateLoader
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string  $plugin_name The name of the plugin.
     * @param string  $version     The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Load a template.
     *
     * Handles template usage so that we can use our own templates instead of the themes.
     *
     * Templates are in the 'templates' folder. edw looks for theme
     * overrides in /theme/edw/ by default
     *
     * @since 1.0.0
     * @param mixed   $template
     * @return string
     */
    public function templateLoader($template)
    {
        $file = '';

        if (is_single() && get_post_type() == 'eb_course') {
            $file = 'single-eb_course.php';
            $find[] = $file;
            $find[] = EB_TEMPLATE_PATH.$file;
        } elseif (is_post_type_archive('eb_course')) {
            $file = 'archive-eb_course.php';
            $find[] = $file;
            $find[] = EB_TEMPLATE_PATH.$file;
        }

        if ($file) {
            $template = locate_template(array_unique($find));
            if (!$template) {
                $template = EB_PLUGIN_DIR.'public/templates/'.$file;
            }
        }

        return $template;
    }

    /**
     * Get template part (for templates like the shop-loop).
     *
     * @since  1.0.0
     * @access public
     * @param mixed   $slug
     * @param string  $name (default: '')
     * @return void
     */
    public function wpGetTemplatePart($slug, $name = '')
    {
        $template = '';

        // Look in yourtheme/edw/slug-name.php
        if ($name) {
            $template = locate_template(array("{$slug}-{$name}.php", EB_TEMPLATE_PATH."{$slug}-{$name}.php"));
        }

        // Get default slug-name.php
        if (!$template && $name && file_exists(EB_PLUGIN_DIR."public/templates/{$slug}-{$name}.php")) {
            $template = EB_PLUGIN_DIR."public/templates/{$slug}-{$name}.php";
        }

        // If template file doesn't exist, look in yourtheme/edw/slug.php
        if (!$template) {
            $template = locate_template(array("{$slug}.php", EB_TEMPLATE_PATH."{$slug}.php"));
        }

        // Allow 3rd party plugin filter template file from their plugin
        if ((!$template) || $template) {
            $template = apply_filters('eb_get_template_part', $template, $slug, $name);
        }

        if ($template) {
            load_template($template, false);
        }
    }

    /**
     * Get other templates.
     *
     * @since  1.0.0
     * @access public
     * @param string  $template_name
     * @param array   $args          (default: array())
     * @param string  $template_path (default: '')
     * @param string  $default_path  (default: '')
     * @return void
     */
    public function wpGetTemplate($template_name, $args = array(), $template_path = '', $default_path = '')
    {
        if ($args && is_array($args)) {
            extract($args);
        }

        $located = $this->wpLocateTemplate($template_name, $template_path, $default_path);

        if (!file_exists($located)) {
            _doing_it_wrong(__FUNCTION__, sprintf(__('%s does not exist.', 'eb-textdomain'), '<code>' . $located . '</code>'), '2.1');
            return;
        }

        // Allow 3rd party plugin filter template file from their plugin
        $located = apply_filters('eb_get_template', $located, $template_name, $args, $template_path, $default_path);

        do_action('eb_before_template_part', $template_name, $template_path, $located, $args);

        include $located;

        do_action('eb_after_template_part', $template_name, $template_path, $located, $args);
    }

    /**
     * Locate a template and return the path for inclusion.
     *
     * This is the load order:
     *
     *  yourtheme  / $template_path / $template_name
     *  yourtheme  / $template_name
     *  $default_path / $template_name
     *
     * @since  1.0.0
     * @access public
     * @param string  $template_name
     * @param string  $template_path (default: '')
     * @param string  $default_path  (default: '')
     * @return string
     */
    public function wpLocateTemplate($template_name, $template_path = '', $default_path = '')
    {
        if (!$template_path) {
            $template_path = EB_TEMPLATE_PATH;
        }

        if (!$default_path) {
            $default_path = EB_PLUGIN_DIR.'public/templates/';
        }

        // Look within passed path within the theme - this is priority
        $template = locate_template(
            array(
                    trailingslashit($template_path).$template_name,
                    $template_name
                )
        );

        // Get default template
        if (!$template) {
            $template = $default_path.$template_name;
        }

        // Return what we found
        return apply_filters('eb_locate_template', $template, $template_name, $template_path);
    }
}
