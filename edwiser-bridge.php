<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link    https://edwiser.org
 * @since   1.0.0
 * @package Edwiser Bridge
 *
 * @wordpress-plugin
 * Plugin Name:       Edwiser Bridge - WordPress Moodle LMS Integration
 * Plugin URI:        https://edwiser.org/bridge/
 * Description:       Edwiser Bridge integrates WordPress with the Moodle LMS. The plugin provides an easy option to import Moodle courses to WordPress and sell them using PayPal. The plugin also allows automatic registration of WordPress users on the Moodle website along with single login credentials for both the systems.
 * Version:           1.0.2
 * Author:            WisdmLabs
 * Author URI:        https://edwiser.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       eb-textdomain
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-eb-activator.php
 */
function activate_edwiserbridge()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-eb-activator.php';
    EB_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_edwiserbridge');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-eb-deactivator.php
 */
function deactivate_edwiserbridge()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-eb-deactivator.php';
    EB_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_edwiserbridge');

/**
 * Applied to the list of links to display on the plugins page (beside the activate/deactivate links).
 *
 * A nes link is added that takes user to plugin settings.
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wdm_add_settings_action_link');
function wdm_add_settings_action_link($links)
{
    $pluginlinks = array(
        '<a href="' . admin_url('/admin.php?page=eb-settings') . '">Settings</a>'
        //'<a href="https://edwiser.org/bridge/documentation/" target="_blank">Documentation</a>',
    );
    return array_merge($links, $pluginlinks);
}

/**
 * Show row meta on the plugin screen, custom docs link added.
 */
add_filter('plugin_row_meta', 'wdm_plugin_row_meta', 10, 2);
function wdm_plugin_row_meta($links, $file)
{
    if ($file == plugin_basename(__FILE__)) {
        $row_meta = array(
            'docs'    => '<a href="https://edwiser.org/bridge/documentation/" target="_blank"
                        title="'.esc_attr(__('Edwiser Bridge Documentation', 'eb-textdomain')).'">'.
                        __('Documentation', 'eb-textdomain') .
                        '</a>',
        );

        return array_merge($links, $row_meta);
    }

    return (array) $links;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-eb.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_edwiserbridge()
{
    EB()->run();
}
run_edwiserbridge(); // start plugin execution
