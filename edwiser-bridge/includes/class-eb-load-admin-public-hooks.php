<?php
/**
*
*/

namespace app\wisdmlabs\edwiserBridge;

class EdwiserBridgeAdminPublicHookLoader
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     *
     * @var EBLoader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

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


    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct($pluginName, $version, $loader)
    {
        $this->plugin_name = $pluginName;
        $this->version     = $version;
        $this->loader      = $loader; 
        /*$fileLoader = new EbFileLoader();
        // $this->defineConstants();
        // $this->loadDependencies();

        $fileLoader->loadDependencies();*/
        



        /*$this->loader = new EBLoader();

        // $this->definePluginHooks();
        $commonHooksLoader = new EdwiserBridgeLoadCommonHooks($this->loader);
        $commonHooksLoader->loadHooks();*/


        if (is_admin()) {
            $this->defineAdminHooks();
        } else {
            $this->definePublicHooks();
        }
    }


    /*public function loadDependencies()
    {
        $fileLoader = new EbFileLoader();
        $fileLoader->loadDependencies();
        $this->loader = new EBLoader();
    }*/


    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }


    /**
     * Register all plugin hooks.
     *
     * @since   1.0.0
     */
/*    private function definePluginHooks()
    {
        $this->defineUserHooks();

        $this->defineSystemHooks();

        $this->defineEmailHooks();

        if (is_admin()) {
            $this->defineAdminHooks();
        } else {
            $this->definePublicHooks();
        }
    }*/

    /**
     * Register all of the hooks related to the admin area & admin settings area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function defineAdminHooks()
    {
        $plugin_admin = new EbAdmin($this->getPluginName(), $this->getVersion());
        $this->loader->addAction('admin_enqueue_scripts', $plugin_admin, 'adminEnqueueStyles');
        $this->loader->addAction('admin_enqueue_scripts', $plugin_admin, 'adminEnqueueScripts');

        /**
         * Add action to add the meta boxes in backend for the order
         */
        $orderMeta = new EBOrderMeta($this->plugin_name, $this->version);
        $saveOrderMeta = new EBOrderStatus($this->plugin_name, $this->version);
        $paypalRefundManager =  new EbPayPalRefundManager($this->plugin_name, $this->version);

        // add_filter("eb_order_refund_init", array($this, "refund"), 10, 5);
        $this->loader->addAction(
            'eb_order_refund_init',
            $paypalRefundManager,
            'refund',
            10,
            5
        );

        $this->loader->addAction(
            'add_meta_boxes',
            $orderMeta,
            'addEbOrderMetaBoxes'
        );
        $this->loader->addAction(
            'save_post_eb_order',
            $saveOrderMeta,
            'saveStatusUpdateMeta',
            05
        );
        $this->loader->addAction(
            'eb_order_created',
            $saveOrderMeta,
            'saveNewOrderPlaceNote'
        );

        $this->loader->addAction(
            'wp_ajax_wdm_eb_order_refund',
            $saveOrderMeta,
            'initEbOrderRefund'
        );

        $adminNoticeHandler = new EBAdminNoticeHandler();
        // add_action('admin_notices', 'app\wisdmlabs\edwiserBridge\ebAdminUpdateMoodlePluginNotice');

        $this->loader->addAction(
            'admin_notices',
            $adminNoticeHandler,
            'ebAdminUpdateMoodlePluginNotice'
        );

        // add_action('admin_init', 'app\wisdmlabs\edwiserBridge\ebAdminUpdateNoticeDismissHandler');
        $this->loader->addAction(
            'admin_init',
            $adminNoticeHandler,
            'ebAdminUpdateNoticeDismissHandler'
        );

        $this->loader->addAction(
            'admin_init',
            $adminNoticeHandler,
            'ebAdminNoticeDismissHandler'
        );

        $hook = "in_plugin_update_message-".EB_PLUGIN_NAME."/".EB_BASE_FILE_NAME;
        $this->loader->addAction(
            $hook,
            $adminNoticeHandler,
            'ebShowInlinePluginUpdateNotification',
            10,
            2
        );

        /*
         * Handling custom button events on settings page
         * Responsible for initiating ajax requests made by custom buttons placed in settings pages.
         * Specifically 'Synchronization Request' & 'Test Connection Request' on Moodle settings page.
         */
        $admin_settings_init = new EBSettingsAjaxInitiater($this->getPluginName(), $this->getVersion());

        /**
         * Email template editor ajax start
         */
        $emailTmplEditor = new EBAdminEmailTemplate();
        $manageEnrollment = new EBManageUserEnrollment($this->plugin_name, $this->version);

        $this->loader->addAction(
            'wp_ajax_wdm_eb_get_email_template',
            $emailTmplEditor,
            'getTemplateDataAjaxCallBack'
        );
        $this->loader->addAction(
            'wp_ajax_nopriv_wdm_eb_get_email_template',
            $emailTmplEditor,
            'getTemplateDataAjaxCallBack'
        );
        $this->loader->addAction(
            'wp_ajax_wdm_eb_send_test_email',
            $emailTmplEditor,
            'sendTestEmail'
        );
        $this->loader->addAction(
            'wp_ajax_nopriv_wdm_eb_send_test_email',
            $emailTmplEditor,
            'sendTestEmail'
        );
        $this->loader->addAction(
            'wp_ajax_nopriv_wdm_eb_user_manage_unenroll_unenroll_user',
            $manageEnrollment,
            'unenrollUserAjaxHandler'
        );
        $this->loader->addAction(
            'wp_ajax_nopriv_wdm_eb_email_tmpl_restore_content',
            $manageEnrollment,
            'resetEmailTemplateContent'
        );

        /**
         * Email template editor end
         */
        $this->loader->addAction(
            'wp_ajax_handleCourseSynchronization',
            $admin_settings_init,
            'courseSynchronizationInitiater'
        );
        $this->loader->addAction(
            'wp_ajax_handleUserCourseSynchronization',
            $admin_settings_init,
            'userDataSynchronizationInitiater'
        );
        $this->loader->addAction(
            'wp_ajax_handleUserLinkToMoodle',
            $admin_settings_init,
            'usersLinkToMoodleSynchronization'
        );
        $this->loader->addAction(
            'wp_ajax_handleConnectionTest',
            $admin_settings_init,
            'connectionTestInitiater'
        );
        $this->loader->addAction(
            'wp_ajax_wdm_eb_user_manage_unenroll_unenroll_user',
            $manageEnrollment,
            'unenrollUserAjaxHandler'
        );
        $this->loader->addAction(
            'wp_ajax_wdm_eb_email_tmpl_restore_content',
            $emailTmplEditor,
            'resetEmailTemplateContent'
        );

        $gdprCompatible = new EBGDPRCompatible();
        /**
         *used to add eb personal while exporting personal data
         *@since  1.3.2
         */
        $this->loader->addAction(
            'wp_privacy_personal_data_exporters',
            $gdprCompatible,
            'ebRegisterMyPluginExporter'
        );

        /**
         *used to add eb personal while exporting personal data
         *@since  1.3.2
         */
        $this->loader->addAction(
            'wp_privacy_personal_data_erasers',
            $gdprCompatible,
            'ebRegisterPluginEraser'
        );

        $this->loader->addAction(
            "admin_init",
            $gdprCompatible,
            'ebPrivacyPolicyPageData'
        );
    }

    

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function definePublicHooks()
    {
        $plugin_public = new EbPublic($this->getPluginName(), $this->getVersion());
        $template_loader = new EbTemplateLoader($this->getPluginName(), $this->getVersion());
/*        $courseProgress = new EbCourseProgress();
        $this->loader->addAction('template_redirect', $courseProgress, 'getCourseProgress');*/

        $plugin_i18n = new EBI18n();
        $plugin_i18n->setDomain('eb-textdomain');

        $this->loader->addAction('plugins_loaded', $plugin_i18n, 'loadPluginTextdomain');


        $this->loader->addAction('wp_enqueue_scripts', $plugin_public, 'publicEnqueueStyles');
        $this->loader->addAction('wp_enqueue_scripts', $plugin_public, 'publicEnqueueScripts');

        // Template loader hooks
        $this->loader->addFilter('template_include', $template_loader, 'templateLoader');

        // Initiate our shortcodes class on init hook
        $this->loader->addAction('init', 'app\wisdmlabs\edwiserBridge\EbShortcodes', 'init');

        // Frontend form handler hooks to handle user login & registration
        $this->loader->addAction('wp_loaded', 'app\wisdmlabs\edwiserBridge\EbFrontendFormHandler', 'processLogin', 20);
        $this->loader->addAction('wp_loaded', 'app\wisdmlabs\edwiserBridge\EbFrontendFormHandler', 'processRegistration', 20);
        // process course join request for free courses
        $this->loader->addAction('wp_loaded', 'app\wisdmlabs\edwiserBridge\EbFrontendFormHandler', 'processFreeCourseJoinRequest');

        $this->loader->addAction('after_setup_theme', $plugin_public, 'afterSetupTheme');
        add_action('template_redirect', array('\app\wisdmlabs\edwiserBridge\EbShortcodeUserAccount', 'saveAccountDetails'));
    }
}
