<?php

namespace app\wisdmlabs\edwiserBridge;

/**
 * Edwiser Bridge Email template page
 *
 * referred code from woocommerce
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * EBAdminExtensions Class
 */
class EBAdminEmailTemplate
{

    public function __construct()
    {
        $this->output();
    }

    public function ebEmailistLdd($emailList)
    {
        $emailList["eb_emailtmpl_create_user"]="New User Account Details";
        $emailList["eb_emailtmpl_linked_existing_wp_user"]="Link WP user account to moodle";
        $emailList["eb_emailtmpl_order_completed"]="Course order complet";
        return $emailList;
    }

    /**
     * handle extensions page output
     */
    public function output()
    {
        $settings = array(
            'media_buttons' => false,
            'drag_drop_upload' => false,
            'textarea_rows' => 15,
        );
        $fromEmail = $this->getFromEmail();
        $fromName = $this->getFromName();
        $tmplData=array("tmpl_name"=>"Template 1","tmpl_subject"=>"Edwiser email template subject","content"=>"<h1 style='color: #ff0000;'>This is the content</h1>");
        $tmplContent = apply_filters("eb_email_template_data", $tmplData);
        add_filter("eb_email_templates_list", array( $this, "ebEmailistLdd" ), 10, 1);
        add_filter("eb_email_template_constant", array( $this, "emailTemplateContsnt" ), 10, 1);
        include_once('partials/html-admin-manage-email-template.php');
    }

    /**
     * Getter methods start
     */
    /**
     *
     * @return string Returns from email address.
     */
    private function getFromEmail()
    {
        $fromEmail=get_option("eb_mail_from_email");
        if ($fromEmail==false) {
            $fromEmail=  get_option("admin_email");
        }
        return $fromEmail;
    }

    private function getFromName()
    {
        $fromName=get_option("eb_mail_from_name");
        if ($fromName==false) {
            $fromName=get_bloginfo("name");
        }
        return $fromName;
    }

    public function emailTemplateContsnt($constants)
    {
        $constants["USER_NAME"] = "The display name of the user.";
        $constants["FIRST_NAME"] = "The first name of the user.";
        $constants["LAST_NAME"] = "The last name of the user.";
        $constants["SITE_NAME"] = "The name of the website.";
        $constants["SITE_URL"] = "The URL of the website.";
        $constants["COURSE_TITLE"] = "The title of the course for the unit that's just been completed.";
        $constants["MOODLE_URL"] = "The moodle site url entered in the connection.";
        return $constants;
    }

    /**
     * Getter methods end
     */

    /**
     * Setter methods start
     */
    private function setFromEmail($email)
    {
        update_option("eb_mail_from_email", $email);
    }

    private function setFromName($name)
    {
        update_option("eb_mail_from_name", $name);
    }

    private function setTemplateData($tempalteName, $tempalteData)
    {
        update_option("eb_mail_" . $tempalteName, $tempalteData);
    }

    /**
     * Setter methods end
     */
    public function sendTestEmail()
    {
    }
    
    private function save()
    {
    }
}
