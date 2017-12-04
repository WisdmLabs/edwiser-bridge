<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EbAdmin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function adminEnqueueStyles()
    {

        /*
         * An instance of this class should be passed to the run() function
         * defined in EB_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The EB_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style(
            $this->plugin_name,
            EB_PLUGIN_URL.'admin/assets/css/eb-admin.css',
            array(),
            $this->version,
            'all'
        );
        wp_enqueue_style(
            'jquery-tiptip-css',
            EB_PLUGIN_URL.'admin/assets/css/tipTip.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function adminEnqueueScripts()
    {

        /*
         * An instance of this class should be passed to the run() function
         * defined in EBLoader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $nonce = wp_create_nonce('check_sync_action');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');

        wp_enqueue_script('iris');

        wp_enqueue_script(
            $this->plugin_name,
            EB_PLUGIN_URL.'admin/assets/js/eb-admin.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script(
            $this->plugin_name,
            'eb_admin_js_object',
            array(
                'unsaved_warning' => __('Please save the changes.', 'eb-textdomain'),
                'plugin_url' => EB_PLUGIN_URL,
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => $nonce,
                'msg_con_success' => __('Connection successful, Please save your connection details.', 'eb-textdomain'),
                'msg_courses_sync_success' => __('Courses synchronized successfully.', 'eb-textdomain'),
                'msg_con_prob' => __('There is a problem while connecting to moodle server.', 'eb-textdomain'),
                'msg_err_users' => __('Error occured for following users: ', 'eb-textdomain'),
                'msg_user_sync_success' => __('User data synced successfully.', 'eb-textdomain'),
                'msg_mail_delivery_fail' => __('Mail delivery failed.', 'eb-textdomain'),
                'msg_test_mail_sent_to' => __('Test mail sent to ', 'eb-textdomain'),
                'msg_err_parsing_res' => __('An error occurred during parsing the response', 'eb-textdomain'),
                'msg_cat_sync_success' => __('Categories synchronized successfully.', 'eb-textdomain'),
                'msg_tpl_not_found' => __('Template not found', 'eb-textdomain'),
                'msg_link_user' => __('Linked ', 'eb-textdomain'),
                'msg_unlink_user' => __('Unlinked ', 'eb-textdomain'),
                'msg_error_unlink_user' => __('Sorry, unable unlink  user', 'eb-textdomain'),
                 'msg_error_link_user' => __('Sorry, unable link  user', 'eb-textdomain'),
                 'msg_error_moodle_link' => __('Sorry, unable to link to moodle', 'eb-textdomain')
            )
        );

        wp_enqueue_script(
            'jquery-tiptip-js',
            EB_PLUGIN_URL.'admin/assets/js/jquery.tipTip.minified.js',
            array('jquery'),
            $this->version,
            false
        );
    }
}
