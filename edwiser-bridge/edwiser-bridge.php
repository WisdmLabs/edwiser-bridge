<?php

namespace app\wisdmlabs\edwiserBridge;

/*
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
 * Version:           1.4.9
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
 * This action is documented in includes/class-eb-activator.php.
 */

if (!defined("EB_BASE_FILE_NAME")) {
    define("EB_BASE_FILE_NAME", basename(__FILE__));
}

if (!defined("EB_PLUGIN_NAME")) {
    define("EB_PLUGIN_NAME", basename(dirname(__FILE__)));
}


function activate_edwiser_bridge($netWide)
{
    require_once plugin_dir_path(__FILE__).'includes/class-eb-activator.php';
    Eb_Activator::activate($netWide);
}

register_activation_hook(__FILE__, 'app\wisdmlabs\edwiserBridge\activate_edwiser_bridge');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-eb-deactivator.php.
 */
function deactivate_edwiser_bridge()
{
    require_once plugin_dir_path(__FILE__).'includes/class-eb-deactivator.php';
    Eb_Deactivator::deactivate();
}

register_deactivation_hook(__FILE__, 'app\wisdmlabs\edwiserBridge\deactivate_edwiser_bridge');

/*
 * Applied to the list of links to display on the plugins page (beside the activate/deactivate links).
 *
 * A nes link is added that takes user to plugin settings.
 */
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'app\wisdmlabs\edwiserBridge\wdm_add_settings_action_link');

function wdm_add_settings_action_link($links)
{
    $plugin_links = array(
        '<a href="'.admin_url('/admin.php?page=eb-settings').'">' . __('Settings', 'eb-textdomain') . '</a>',
            //'<a href="https://edwiser.org/bridge/documentation/" target="_blank">Documentation</a>',
    );

    return array_merge($links, $plugin_links);
}

/*
 * Show row meta on the plugin screen, custom docs link added.
 */
add_filter('plugin_row_meta', 'app\wisdmlabs\edwiserBridge\wdm_plugin_row_meta', 10, 2);

function wdm_plugin_row_meta($links, $file)
{
    if ($file == plugin_basename(__FILE__)) {
        $row_meta = array(
            'docs' => '<a href="https://edwiser.org/bridge/documentation/" target="_blank"
                        title="'.esc_attr(__('Edwiser Bridge Documentation', 'eb-textdomain')).'">'.
            __('Documentation', 'eb-textdomain').
            '</a>',
        );

        return array_merge($links, $row_meta);
    }

    return (array) $links;
}



/*
 * Always show warning if legacy extensions are active
 *
 * @since 1.1
 */
add_action('admin_init', 'app\wisdmlabs\edwiserBridge\wdm_show_legacy_extensions');

function wdm_show_legacy_extensions()
{
    // prepare extensions array
    $extensions = array(
        'selective_sync' => array('selective-synchronization/selective-synchronization.php', '1.0.0'),
        'woocommerce_integration' => array('woocommerce-integration/bridge-woocommerce.php', '1.0.4'),
        'single_signon' => array('edwiser-bridge-sso/sso.php', '1.0.0'),
    );

    // legacy extensions
    foreach ($extensions as $extension) {
        if (is_plugin_active($extension[0])) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR.'/'.$extension[0]);

            if (isset($plugin_data['Version'])) {
                if (version_compare($plugin_data['Version'], $extension[1]) <= 0) {
                    add_action('admin_notices', 'app\wisdmlabs\edwiserBridge\wdm_show_legacy_extensions_notices');
                }
            }
        }
    }
}

function wdm_show_legacy_extensions_notices()
{
    ob_start(); ?>
    <div class="error">
        <p>
            <?php
            printf(
                __('Please update all %s extensions to latest version.', 'eb-textdomain'),
                '<strong>'.__('Edwiser Bridge', 'eb-textdomain').'</strong>'
            ); ?>
        </p>
    </div>
    <?php
    echo ob_get_clean();
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__).'includes/class-eb.php';

/*
 * Executes on the plugin update.
 */
add_action('admin_init', 'app\wisdmlabs\edwiserBridge\process_upgrade');
function process_upgrade()
{
    $new_version = '1.4.9';
    $current_version = get_option('eb_current_version');
    if ($current_version == false || $current_version != $new_version) {
        require_once plugin_dir_path(__FILE__).'includes/class-eb-activator.php';
        Eb_Activator::activate(false);
        update_option('eb_current_version', $new_version);
    }
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_edwiser_bridge()
{
    edwiser_bridge_instance()->run();
}

run_edwiser_bridge(); // start plugin execution

require_once plugin_dir_path(__FILE__).'includes/api/class-eb-external-api.php';

