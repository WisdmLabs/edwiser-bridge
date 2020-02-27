<?php
/**
*
*/
namespace app\wisdmlabs\edwiserBridge;

class EbFileLoader
{
    
    public function __construct()
    {
        $this->defineConstants();
    }


    /**
     * Setup plugin constants.
     *
     * @since 1.0.0
     */
    private function defineConstants()
    {
        $upload_dir = wp_upload_dir();

        //get connection settings
        $connection_options = get_option('eb_connection');

        $eb_moodle_url = '';
        if (isset($connection_options['eb_url'])) {
            $eb_moodle_url = $connection_options['eb_url'];
        }
        $eb_moodle_token = '';
        if (isset($connection_options['eb_access_token'])) {
            $eb_moodle_token = $connection_options['eb_access_token'];
        }
        /**
         * Define plugin constants.
         */
        $this->checkConstantDefined('EB_VERSION', $this->version);
        $this->checkConstantDefined('EB_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));
        $this->checkConstantDefined('EB_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));
        $this->checkConstantDefined('EB_TEMPLATE_PATH', 'edwiserBridge/');
        $this->checkConstantDefined('EB_ACCESS_TOKEN', $eb_moodle_token);
        $this->checkConstantDefined('EB_ACCESS_URL', $eb_moodle_url);
        $this->checkConstantDefined('EB_LOG_DIR', $upload_dir['basedir'].'/eb-logs/');
    }


    private function checkConstantDefined($key, $value)
    {
        if (!defined($key)) {
            define($key, $value);
        }
    }


    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - EBLoader. Orchestrates the hooks of the plugin.
     * - EBI18n. Defines internationalization functionality.
     * - EbAdmin. Defines all hooks for the admin area.
     * - EbPublic. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     */
    public function loadDependencies()
    {

        // load admin & public facing files conditionally
        if (is_admin()) {
            $this->adminDependencies();
        } else {
            $this->frontendDependencies();
        }

        /**
         * The core class to manage debug log on the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/api/class-eb-external-api.php';

        /**
         * The core class to manage debug log on the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-logger.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-loader.php';

        /**
         * The class responsible for managing emails sent to user on purchase.
         */
        require_once EB_PLUGIN_DIR.'includes/emails/class-eb-emailer.php';

        /**
         * The class responsible for defining post types and meta boxes
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-post-types.php';

        /**
         * The class responsible for defining course synchronization & management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-course-manager.php';

        /**
         * The class responsible for defining order management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-order-manager.php';

        /**
         * The class responsible for defining user management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-user-manager.php';

        /**
         * The class responsible for defining enrollment management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-enrollment-manager.php';

        /**
         * The class responsible for defining connection management functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/api/class-eb-connection-helper.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-i18n.php';

        /**
         * The core class to manage payments on the site.
         */
        require_once EB_PLUGIN_DIR.'includes/payments/class-eb-payment-manager.php';

        /*
         * loading refund dependencies.
         */
        require_once EB_PLUGIN_DIR.'includes/payments/class-eb-refund-manager.php';


        // core functions
        require_once EB_PLUGIN_DIR.'includes/eb-core-functions.php';
        require_once EB_PLUGIN_DIR.'includes/eb-formatting-functions.php';

        // To handle addition of new blog (for multisite installations)

        require_once EB_PLUGIN_DIR.'includes/class-eb-activator.php';

        //To handel the email template modification
        require_once EB_PLUGIN_DIR.'admin/class-eb-email-template.php';
        require_once EB_PLUGIN_DIR.'includes/class-eb-email-template-parser.php';
        
        include_once EB_PLUGIN_DIR.'includes/class-eb-default-email-templates.php';


        /*
         * loading refund dependencies.
         * @since      1.3.3
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-gdpr-compatibility.php';
    }

    /**
     * admin facing code.
     *
     * @since    1.0.0
     */
    private function adminDependencies()
    {

        /*
         *Class responsible to show admin notices
         */

        require_once EB_PLUGIN_DIR.'includes/class-eb-admin-notice-handler.php';


        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once EB_PLUGIN_DIR.'admin/class-eb-admin.php';
        require_once EB_PLUGIN_DIR.'admin/class-eb-welcome.php';
        require_once EB_PLUGIN_DIR.'admin/class-eb-extensions.php';

        /**
        *The class used to add Moodle account column on users page frontend
        */
        require_once EB_PLUGIN_DIR.'admin/class-eb-moodle-linkunlink.php';
        require_once EB_PLUGIN_DIR.'includes/class-eb-custom-list-table.php';
        require_once EB_PLUGIN_DIR.'includes/class-eb-manage-enrollment.php';

        /**
         * The core classes that initiates settings module.
         */
        require_once EB_PLUGIN_DIR.'admin/class-eb-admin-menus.php';
        require_once EB_PLUGIN_DIR.'admin/class-eb-admin-settings.php';

        /**
         * The core class to handle custom events events on settings page.
         *
         * Used in:
         *
         * Test Connection
         * Courses & User Data Synchronization
         */
        require_once EB_PLUGIN_DIR.'admin/class-eb-settings-ajax-initiater.php';

        /**
         * Add order meta boxes.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-order-meta.php';
        require_once EB_PLUGIN_DIR.'includes/class-eb-order-status-update.php';
        require_once EB_PLUGIN_DIR.'includes/class-eb-order-history-meta.php';
        require_once EB_PLUGIN_DIR.'includes/class-eb-manage-order-refund.php';
    }

    /**
     * public facing code.
     *
     * @since    1.0.0
     */
    private function frontendDependencies()
    {

        /*
         * inlcuding course progress file
         * @since 1.4
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-course-progress.php';


        /**
         * The classes responsible for defining and handling all actions that occur in the public-facing
         * side of the site.
         */
        require_once EB_PLUGIN_DIR.'public/class-eb-public.php';
        /**
         * Tha classes responsible for defining shortcodes.
         */
        require_once EB_PLUGIN_DIR.'includes/class-eb-manage-enrollment.php';
        require_once EB_PLUGIN_DIR.'public/class-eb-shortcodes.php';
        require_once EB_PLUGIN_DIR.'public/shortcodes/class-eb-shortcode-user-account.php';
        require_once EB_PLUGIN_DIR.'public/shortcodes/class-eb-shortcode-user-profile.php';
        require_once EB_PLUGIN_DIR.'public/shortcodes/class-eb-shortcode-courses.php';
        require_once EB_PLUGIN_DIR.'public/shortcodes/class-eb-shortcode-course.php';
        require_once EB_PLUGIN_DIR.'public/shortcodes/class-eb-shortcode-my-courses.php';

        /**
         * The class responsible for handling frontend forms, specifically login & registration forms.
         */
        require_once EB_PLUGIN_DIR.'public/class-eb-frontend-form-handler.php';
    }
}
