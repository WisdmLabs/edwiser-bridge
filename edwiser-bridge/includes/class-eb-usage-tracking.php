<?php

/**
 * Edwiser Usage Tracking
 * We send anonymous user data to imporve our product compatibility with various plugins and systems.
 * 
 * Cards Format - A topics based format that uses card layout to diaply the content.
 * @package    format_remuiformat
 * @copyright  (c) 2020 WisdmLabs (https://wisdmlabs.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace app\wisdmlabs\edwiserBridge;


class EB_Usage_Tracking {


    //Action hook for the usage tracking.
    // add_action('eb_monthly_usage_tracking', array($this, 'send_usage_analytics'));

    /**
    * Call this function on the registration Hook.
    * Functionalitity to call the usage tracking call back on every month
    */
    public function usage_tracking_cron() {

        if ( ! wp_next_scheduled( 'eb_monthly_usage_tracking' ) ) {
            // wp_schedule_event( time(), 'every_month', 'eb_monthly_usage_tracking' );
            wp_schedule_event( time(), 'monthly', 'eb_monthly_usage_tracking' );
            
        }
    }



    /**
     * Send usage analytics to Edwiser, only anonymous data is sent.
     * 
     * every 7 days the data is sent, function runs for admin user only
     */
    public function send_usage_analytics() {

        global $DB, $CFG;

        // execute code only if current user is site admin
        // reduces calls to DB
            
        // check consent to send tracking data
        $eb_general = get_option('eb_general');
        if ($eb_general) {
            $consent = getArrValue($eb_general, 'eb_usage_tracking', false);
        }


        if($consent) {
            $result_arr = [];

            $analytics_data = json_encode($this->prepare_usage_analytics());                 

            $url = "https://edwiser.org/wp-json/edwiser_customizations/send_usage_data";
            // call api endpoint with data
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POSTFIELDS, $analytics_data);                                                                  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($analytics_data))                                                                       
            );

            //execute post
            $result = curl_exec($ch);

            if($result) {
                $result_arr = json_decode($result, 1);
            }
            //close connection
            curl_close($ch);
        }
    }

     /** 
      * Prepare usage analytics 
      */
    private function prepare_usage_analytics() {

        global $CFG, $DB, $wp_version;

        // Suppressing all the errors here, just in case the setting does not exists, to avoid many if statements
        $analytics_data = array(
            'siteurl' => $this->detect_site_type() . preg_replace('#^https?://#', '', rtrim(@get_site_url(),'/')), // replace protocol and trailing slash
            'product_name' => "Edwiser Bridge",
            'product_settings' => $this->get_plugin_settings('edwiser_bridge'), // all settings in json, of current product which you are tracking,
            'active_theme' => get_option('stylesheet'),
            'total_courses' => $this->eb_get_course_count(), // Include only with format type remuicourseformat.
            'total_categories' => $this->eb_get_cat_count(), // includes hidden categories
            'total_users' => $this->eb_get_user_count(), // exclude deleted
            'installed_plugins' => $this->get_user_installed_plugins(), // along with versions
            'system_version' => $wp_version, // Moodle version
            'system_lang' => get_locale(),
            'system_settings' => array(
                // 'blog_active' => @$CFG->enableblogs,
                'multiste' => is_multisite() ? 1: 0,
                // 'moodle_memory_limit' => @$CFG->extramemorylimit,
                // 'moodle_maxexec_time_limit' => @$CFG->maxtimelimit,
            ),
            // 'server_os' => @$CFG->os,
            'server_ip' => @$_SERVER['REMOTE_ADDR'],
            'web_server' => @$_SERVER['SERVER_SOFTWARE'],
            'php_version' => phpversion(),
            'php_settings' => array(
                'memory_limit' => ini_get("memory_limit"),
                'max_execution_time' => ini_get("max_execution_time"),
                'post_max_size' => ini_get("post_max_size"),
                'upload_max_filesize' => ini_get("upload_max_filesize"),
                'memory_limit' => ini_get("memory_limit")
            ),
        );
        return $analytics_data;
    }



    private function eb_get_course_count()
    {
        global $wpdb;

        $count = $wpdb->get_var(
            "SELECT count(*) count
            FROM {$wpdb->prefix}posts
            WHERE post_type = 'eb_course'"
        );

        return $count;

    }


   private function eb_get_cat_count()
    {
        global $wpdb;

        $count = $wpdb->get_var(
            "SELECT count(*) count
            FROM {$wpdb->prefix}term_taxonomy
            WHERE taxonomy = 'eb_course_cat'"
        );

        return $count;

    }


    private function eb_get_user_count()
    {
        global $wpdb;

        $count = $wpdb->get_var(
            "SELECT count(*) count
            FROM {$wpdb->prefix}users"
        );

        return $count;

    }





    // get plugins installed by user excluding the default plugins
    private function get_user_installed_plugins() {

        if ( ! function_exists( 'get_plugins' ) ) {
		    require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}


		$all_plugins = array();
		$plugin_infos = get_plugins();


        foreach($plugin_infos as $key => $each_plugin_details) {
            $all_plugins[] = array(
                'name'        => $each_plugin_details['Name'],
                'version'     => $each_plugin_details['Version']
            );
        }

        return $all_plugins;
    }

    // get specific settings of the current plugin, eg: remui
    private function get_plugin_settings($plugin) {
        global $DB;
        // get complete config
        $settings = array();
        $settings['edwiser_bridge']['general'] = get_option('eb_general');

        if (is_plugin_active('edwiser-bridge-sso/sso.php')) {
            $settings['sso_settings']['general'] = get_option('eb_sso_settings_general');
            $settings['sso_settings']['general'] = get_option('eb_sso_settings_redirection');
        }

        if (is_plugin_active('woocommerce-integration/bridge-woocommerce.php')) {
            $settings['woo_int']['general'] = get_option('eb_woo_int_settings');
        }

        
        return $settings;
    }

    /**
     * Check if site is running on localhost or not.
     */
    private function detect_site_type() {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        // Check if site is running on localhost or not.
        if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            $is_local = 'localsite--';
        }
        return $is_local;
    }
}
