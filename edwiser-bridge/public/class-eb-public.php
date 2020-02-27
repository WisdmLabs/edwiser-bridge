<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EbPublic
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
     * @since    1.0.0
     * @param string  $plugin_name The name of the plugin.
     * @param string  $version     The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function publicEnqueueStyles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style(
            $this->plugin_name . '_font_awesome',
            EB_PLUGIN_URL.'public/assets/css/font-awesome-4.4.0/css/font-awesome.min.css',
            array(),
            $this->version,
            'all'
        );
        
        wp_enqueue_style(
            $this->plugin_name,
            EB_PLUGIN_URL.'public/assets/css/eb-public.css',
            array($this->plugin_name . '_font_awesome'),
            $this->version,
            'all'
        );
        wp_enqueue_style(
            'wdmdatatablecss',
            EB_PLUGIN_URL.'public/assets/css/datatable.css',
            array(),
            $this->version,
            'all'
        );
        wp_enqueue_style(
            "eb-public-jquery-ui-css",
            EB_PLUGIN_URL.'admin/assets/css/jquery-ui.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function publicEnqueueScripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $nonce = wp_create_nonce('public_js_nonce');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script(
            $this->plugin_name,
            EB_PLUGIN_URL . 'public/assets/js/eb-public.js',
            array( 'jquery', 'jquery-ui-dialog' ),
            $this->version,
            false
        );
        wp_register_script(
            $this->plugin_name . '-edit-user-profile',
            EB_PLUGIN_URL . 'public/assets/js/edit-user-profile.js',
            array('jquery'),
            $this->version,
            false
        );
        wp_localize_script(
            $this->plugin_name . '-edit-user-profile',
            'ebEditProfile',
            array(
                'default' => __('- Select Country -', 'eb-textdomain')
            )
        );

        wp_enqueue_script(
            $this->plugin_name."-ui-block",
            EB_PLUGIN_URL.'public/assets/js/jquery-blockui-min.js',
            array('jquery'),
            $this->version,
            false
        );
        wp_localize_script(
            $this->plugin_name,
            'eb_public_js_object',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => $nonce,
                'msg_val_fn' => __("The field 'First Name' cannot be left blank", 'eb-textdomain'),
                'msg_val_ln' => __("The field 'Last Name' cannot be left blank", 'eb-textdomain'),
                'msg_val_mail' => __("The field 'Email' cannot be left blank", 'eb-textdomain'),
                'msg_ordr_pro_err' => __('Problems in processing your order, Please try later.', 'eb-textdomain'),
                'msg_processing' => __('Processing...', 'eb-textdomain'),
                'access_course' => __('Access Course', 'eb-textdomain'),
            )
        );

        // datatable
        wp_localize_script(
            $this->plugin_name,
            'ebDataTable',
            array(
                'search' => __('Search:', 'eb-textdomain'),
                'sEmptyTable' => __('No data available in table', 'eb-textdomain'),
                'sLoadingRecords' => __('Loading...', 'eb-textdomain'),
                'sSearch' => __('Search', 'eb-textdomain'),
                'sZeroRecords' => __('No matching records found', 'eb-textdomain'),
                'sProcessing' => __('Processing...', 'eb-textdomain'),
                'sInfo' => __('Showing _START_ to _END_ of _TOTAL_ entries', 'eb-textdomain'),
                'sInfoEmpty' => __('Showing 0 to 0 of 0 entries', 'eb-textdomain'),
                'sInfoFiltered' => __('filtered from _MAX_ total entries', 'eb-textdomain'),
                'sInfoPostFix' => __('', 'eb-textdomain'),
                'sInfoThousands' => __(',', 'eb-textdomain'),
                'sLengthMenu' => __('Show _MENU_ entries', 'eb-textdomain'),
                'sFirst' => __('First', 'eb-textdomain'),
                'sLast' => __('Last', 'eb-textdomain'),
                'sNext' => __('Next', 'eb-textdomain'),
                'sPrevious' => __('Previous', 'eb-textdomain'),
                'sSortAscending' => __(': activate to sort column ascending', 'eb-textdomain'),
                'sSortDescending' => __(': activate to sort column descending', 'eb-textdomain')
            )
        );

        // datatable js for user order table
        wp_enqueue_script(
            'wdmdatatablejs',
            EB_PLUGIN_URL.'public/assets/js/datatable.js',
            array('jquery'),
            $this->version,
            false
        );
    }


    /**
     * Theme specific setup.
     *
     * @since    1.2.0
     */
    public function afterSetupTheme()
    {
        add_theme_support('post-thumbnails');

        //Custom sized thumbnails - single course page.
        add_image_size('course_single', 600, 450, true);

        //Custom sized thumbnails - archive course page.
        add_image_size('course_archive', 200, 150, true);
    }
}
