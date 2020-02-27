<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EdwiserBridge
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     *
     * @var EBLoader Maintains and registers all hooks for the plugin.
     */
    // protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * @var EDW The single instance of the class
     *
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     *
     * @var string The current version of the plugin.
     */
    protected $version;

    /**
     * Main EDW Instance.
     *
     * Ensures only one instance of EDW is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     *
     * @see edwiserBridgeInstance()
     *
     * @return EDW - Main instance
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since   1.0.0
     */
/*    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'eb-textdomain'), '1.0.0');
    }*/

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since   1.0.0
     */
/*    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'eb-textdomain'), '1.0.0');
    }*/

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->plugin_name = 'edwiserbridge';
        $this->version = '1.4.3';

        $fileLoader = new EbFileLoader();
        $fileLoader->loadDependencies();

        $loader = new EBLoader();

        new EdwiserBridgeAdminPublicHookLoader($this->plugin_name, $this->version, $loader);
        // $this->definePluginHooks();
        $commonHooksLoader = new EdwiserBridgeLoadCommonHooks($loader);
        $commonHooksLoader->loadHooks();

        // parent::__construct();
    }



    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WPmoodle_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     */
/*    private function setLocale()
    {
        $plugin_i18n = new EBI18n();
        $plugin_i18n->setDomain('eb-textdomain');

        $this->loader->addAction('plugins_loaded', $plugin_i18n, 'loadPluginTextdomain');
    }
*/
    /**
     * Get User Manager class.
     *
     * @since    1.0.0
     *
     * @return EBUserManager
     */
    public function userManager()
    {
        return EBUserManager::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Course Manager class.
     *
     * @since    1.0.0
     *
     * @return EBCourseManager
     */
    public function courseManager()
    {
        return EBCourseManager::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Enrollment Manager class.
     *
     * @since    1.0.0
     *
     * @return EBEnrollmentManager
     */
    public function enrollmentManager()
    {
        return EBEnrollmentManager::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Order Manager class.
     *
     * @since    1.0.0
     *
     * @return EBOrderManager
     */
    public function orderManager()
    {
        return EBOrderManager::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Connection Helper class.
     *
     * @since    1.0.0
     *
     * @return EBConnectionHelper
     */
    public function connectionHelper()
    {
        return EBConnectionHelper::instance($this->getPluginName(), $this->getVersion());
    }

    /**
     * Get Logger class.
     *
     * @since    1.0.0
     *
     * @return EBLogger
     */
    public function logger()
    {
        return EBLogger::instance($this->getPluginName(), $this->getVersion());
    }


    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     *
     * @return string The name of the plugin.
     */
    public function getPluginName()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     *
     * @return EBLoader Orchestrates the hooks of the plugin.
     */
/*    public function getLoader()
    {
        return $this->loader;
    }*/

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     *
     * @return string The version number of the plugin.
     */
    public function getVersion()
    {
        return $this->version;
    }
}

/**
 * Returns the main instance of EDW to prevent the need to use globals.
 *
 * @since  1.0.0
 *
 * @return EDW
 */
function edwiserBridgeInstance()
{
    return EdwiserBridge::instance();
}
