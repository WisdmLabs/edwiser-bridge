<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EBActivator
{
    /**
     * networkWide tells if the plugin was activated for the entire network or just for single site.
     *
     * @since    1.1.1
     */

    private static $networkWide = false;

    /**
     * activation function.
     *
     * @since    1.0.0
     */

    public static function activate($networkWide)
    {
        /**
         * deactivates legacy extensions
         */
        
        self::$networkWide =  $networkWide;
    
        self::deactivateLegacyExtensions();

        // create database tables & Pages
        self::checkSingleOrMultiSite();

        // create required files & directories
        self::createFiles();

        // redirect to welcome screen
        set_transient('_eb_activation_redirect', 1, 30);
    }

    /**
     * deactivates legacy extensions
     *
     * @since 1.1
     */
    public static function deactivateLegacyExtensions()
    {
        // prepare extensions array
        $extensions = array(
                'selective_sync' => array('selective-synchronization/selective-synchronization.php', '1.0.0'),
                'woocommerce_integration' => array('woocommerce-integration/bridge-woocommerce.php', '1.0.4'),
                'single_signon'  => array('edwiser-bridge-sso/sso.php', '1.0.0')
        );

        // deactive legacy extensions
        foreach ($extensions as $extension) {
            if (is_plugin_active($extension[0])) {
                $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $extension[0]);

                if (isset($plugin_data['Version'])) {
                    if (version_compare($plugin_data['Version'], $extension[1]) <= 0) {
                        deactivate_plugins($extension[0]);
                    }
                }
            }
        }
    }

    /**
     *checks if the plugin is activated on a SIngle site or Network wide
     *
     * @since    1.1.1
     */
    public static function checkSingleOrMultiSite()
    {
        global $wpdb;
 
        if (is_multisite()) {
             // print_r(is_plugin_active_for_network('edwiser-bridge/edwiser-bridge.php')); die();

            if (self::$networkWide) {
                $allSites = wp_get_sites();


                foreach ($allSites as $blog) {
                    switch_to_blog($blog['blog_id']);
            
                    self::createMoodleDBTables();
                    self::createPages();
  
                    restore_current_blog();
                }
            } else {
                switch_to_blog($wpdb->blogid);
                self::createMoodleDBTables();
                self::createPages();
                restore_current_blog();
            }
        } else {
            self::createMoodleDBTables();
            self::createPages();
        }
    }

    /**
     * Create DB tables
     *
     * @since  1.0.0
     */
    public static function createMoodleDBTables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $enrollment_tbl_name = $wpdb->prefix.'moodle_enrollment';

        $enrollment_table = "CREATE TABLE IF NOT EXISTS $enrollment_tbl_name (
                id            mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id       int(11) NOT NULL,
                course_id     int(11) NOT NULL,
                role_id       int(11) NOT NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY id (id)
            ) $charset_collate;";
            
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        dbDelta($enrollment_table);
    }

      /**
     *handles addtion of new blog
     *
     * @since  1.1.1
     */
    public static function handleNewBlog($blog_id)
    {
        switch_to_blog($blog_id);
        self::createMoodleDBTables();
        self::createPages();
        restore_current_blog();
    }

    /**
     * Create files/directories.
     *
     * @since  1.0.0
     */
    private static function createFiles()
    {
        // Install files and folders for uploading files and prevent hotlinking
        $upload_dir = wp_upload_dir();

        $files = array(
            array(
                'base' => $upload_dir['basedir'].'/eb-logs/',
                'file' => '.htaccess',
                'content' => 'deny from all',
            ),
        );

        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']).$file['file'])) {
                if ($file_handle = @fopen(trailingslashit($file['base']).$file['file'], 'w')) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }

    /**
     * Create default pages with shortcodes.
     *
     * Create pages that the plugin relies on, storing page id's in variables.
     *
     *  @since  1.0.0
     */
    public static function createPages()
    {
        include_once 'eb-core-functions.php';

        $pages = apply_filters(
            'eb_create_default_pages',
            array(

                'thankyou' => array(
                    'name' => _x('thank-you-for-purchase', 'Page slug', 'eb-textdomain'),
                    'title' => _x('Thank You for Purchase', 'Page title', 'eb-textdomain'),
                    'content' => 'Thanks for purchasing the course, your order will be processed shortly.',
                    'option_key' => '',
                ),

                'useraccount' => array(
                    'name' => _x('user-account', 'Page slug', 'eb-textdomain'),
                    'title' => _x('User Account', 'Page title', 'eb-textdomain'),
                    'content' => '['.apply_filters('eb_user_account_shortcode_tag', 'eb_user_account').']',
                    'option_key' => 'eb_useraccount_page_id',
                ),
            )
        );

        foreach ($pages as $key => $page) {
            $key;
            wdmCreatePage(esc_sql($page['name']), $page['option_key'], $page['title'], $page['content']);
        }
    }
}
