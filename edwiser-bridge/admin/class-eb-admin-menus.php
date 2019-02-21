<?php

namespace app\wisdmlabs\edwiserBridge;

/**
 * Setup plugin menus in WP admin.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

/**
 * EbAdminMenus Class
 */
class EbAdminMenus
{

    /**
     * Hook in tabs.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        // Add menus
        add_action('admin_menu', array($this, 'adminMenu'), 9);
        add_action('admin_menu', array($this, 'settingsMenu'), 10);
        add_action('admin_menu', array($this, 'emailTemplate'), 10);
        add_action('admin_menu', array($this, 'manageEnrollmentMenu'), 10);
        add_action('admin_menu', array($this, 'extensionsMenu'), 10);
        add_action('admin_menu', array($this, 'helpMenu'), 10);
        add_action('parent_file', array($this, 'addMenuPageTaxonomyFix'), 10);

        add_action('admin_footer', array($this, 'openHelpMenuNewTab')); // open help menu in new tab
    }

    /**
     * Add menu items
     *
     * @since 1.0.0
     */
    public function adminMenu()
    {
        global $menu;

        // add menu separator
        if (current_user_can('manage_options')) {
            $menu[53.5] = array('', 'read', 'separator-edwiserbridge_lms', '', 'wp-menu-separator edwiserbridge_lms');
        }

        add_menu_page(
            __('Edwiser Bridge', 'eb-textdomain'),
            __('Edwiser Bridge', 'eb-textdomain'),
            'manage_options',
            'edwiserbridge_lms',
            null,
            'dashicons-book-alt',
            54
        );

        global $submenu;
        $location = 55;
        $add_submenu = array(
            array(
                "name" => __("Courses", 'eb-textdomain'),
                "cap" => "manage_options",
                "link" => "edit.php?post_type=eb_course"
            ),
            array(
                "name" => __("Course Categories", 'eb-textdomain'),
                "cap" => "manage_options",
                "link" => "edit-tags.php?taxonomy=eb_course_cat&post_type=eb_course"
            ),
            array(
                "name" => __("Orders", 'eb-textdomain'),
                "cap" => "manage_options",
                "link" => "edit.php?post_type=eb_order"
            )
        );

        foreach ($add_submenu as $add_submenu_item) {
            if (current_user_can($add_submenu_item["cap"])) {
                $submenu['edwiserbridge_lms'][$location++] = array(
                    $add_submenu_item['name'],
                    $add_submenu_item['cap'],
                    $add_submenu_item['link']
                );
            }
        }

        //echo '<pre>'; print_r($menu); echo '</pre>';
    }

    /**
     * Taxonomy fix to display correct submenu selected when on moodle categories menu
     *
     * @since 1.0.0
     * @param string  $parent_file slug of current main menu selected
     */
    public function addMenuPageTaxonomyFix($parent_file)
    {
        global $submenu_file, $current_screen, $pagenow;

        // Set the submenu as active/current while anywhere in Custom Post Type ( courses, orders )
        if ($current_screen->post_type == 'eb_course' || $current_screen->post_type == 'eb_order') {
            if ($pagenow == 'post.php') {
                $submenu_file = 'edit.php?post_type=' . $current_screen->post_type;
            }

            if ($pagenow == 'edit-tags.php') {
                $submenu_file = 'edit-tags.php?taxonomy=eb_course_cat&post_type=' . $current_screen->post_type;
            }
            $parent_file = 'edwiserbridge_lms';
        }
        return $parent_file;
    }


    /**
     * Add settings submenu item
     *
     * @since 1.1.0
     */
    public function settingsMenu()
    {
        add_submenu_page(
            'edwiserbridge_lms',
            __('Settings', 'eb-textdomain'),
            __('Settings', 'eb-textdomain'),
            'manage_options',
            'eb-settings',
            array($this, 'settingsPage')
        );
    }

    /**
     * Add submenu `Manage Enrollment` to manage user enrollment under EB.
     *
     * @since 1.2.2
     */
    public function manageEnrollmentMenu()
    {
        add_submenu_page(
            'edwiserbridge_lms',
            __('User Enrollment', 'eb-textdomain'),
            __('Manage Enrollment', 'eb-textdomain'),
            'manage_options',
            'mucp-manage-enrollment',
            array($this, 'manageEnrollmentContent')
        );
    }

    /**
     * Add email template submenu item
     *
     * @since 1.0.0
     */
    public function extensionsMenu()
    {
        add_submenu_page(
            'edwiserbridge_lms',
            __('Extensions', 'eb-textdomain'),
            __('Extensions', 'eb-textdomain'),
            'manage_options',
            'eb-extensions',
            array($this, 'extensionsPage')
        );
    }

    /**
     * Add help submenu item
     *
     * @since 1.0.0
     */
    public function helpMenu()
    {
        global $submenu;
        if (current_user_can('manage_options')) {
            $submenu['edwiserbridge_lms'][] = array(
                '<div id="helpmenu">' . __('Help', 'eb-textdomain') . '</div>',
                'manage_options',
                'https://edwiser.org/bridge/documentation/'
            );
        }
    }

    /**
     * Add extensions submenu item
     *
     * @since 1.0.0
     */
    public function emailTemplate()
    {
        add_submenu_page(
            'edwiserbridge_lms',
            __('Manage Email Templates', 'eb-textdomain'),
            __('Manage Email Templates', 'eb-textdomain'),
            'manage_options',
            'eb-email-template',
            array($this, 'emailTemplatePage')
        );
    }

    /**
     * open plugin help link in new tab
     *
     * @since  1.0.0
     */
    public function openHelpMenuNewTab()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#helpmenu').parent().attr('target', '_blank');
            });
        </script>
        <?php
    }

    /**
     * Initialize the settings page
     *
     * @since 1.0.0
     */
    public function settingsPage()
    {
        EbAdminSettings::output();
    }

    /**
     * Render Enrollment  manager page content.
     *
     * @since 1.2.2
     */
    public function manageEnrollmentContent()
    {
        $edwiser=EdwiserBridge::instance();
        $enrollmentManager=new EBManageUserEnrollment($edwiser->getPluginName(), $edwiser->getVersion());
        $enrollmentManager->outPut();
    }

    /**
     * Initialize the extensions page
     */
    public function extensionsPage()
    {
        EBAdminExtensions::output();
    }

    public function emailTemplatePage()
    {
        $emailTmpl = new EBAdminEmailTemplate();
        $emailTmpl->outPut();
    }
}
return new EbAdminMenus();
