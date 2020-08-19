<?php
/**
 * Edwiser Bridge Email template page
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

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
        add_filter('mce_external_plugins', array($this, 'addMCEPlugin'));
        /**
         * Filter for the email template list and email temaplte constant.
         */
        add_filter("eb_email_templates_list", array($this, "ebAddEmailList"), 10, 1);
        add_filter("eb_email_template_constant", array($this, "emailTemplateContsnt"), 10, 1);
        add_filter('wp_mail_from_name', array($this, "wpbSenderName"), 99, 1);
    }

    /**
     * Provides the functionality to prepare the email temaplte list to display
     * in the manage email temaplte page
     *
     * This is the callback for the eb_email_templates_list
     *
     * @param type $emailList array of the email template list
     * @return array of the email tempalte list
     */
    public function ebAddEmailList($emailList)
    {
        $emailList["eb_emailtmpl_create_user"] = __("New User Account Details", 'eb-textdomain');
        $emailList["eb_emailtmpl_linked_existing_wp_user"] = __("Link WP user account to moodle", 'eb-textdomain');
        $emailList["eb_emailtmpl_linked_existing_wp_new_moodle_user"] = __("Create new moodle account", 'eb-textdomain');
        $emailList["eb_emailtmpl_order_completed"] = __("Course Order Completion", 'eb-textdomain');
        $emailList["eb_emailtmpl_course_access_expir"] = __("Course access expired", 'eb-textdomain');

        $emailList["eb_emailtmpl_refund_completion_notifier_to_user"] = __("Refund Success mail to customer", 'eb-textdomain');
        $emailList["eb_emailtmpl_refund_completion_notifier_to_admin"] = __("Refund Success mail to admin or specified email", 'eb-textdomain');


/*******  Two way synch ********/

        $emailList["eb_emailtmpl_mdl_enrollment_trigger"] = __("Moodle Course Enrollment", 'eb-textdomain');
        $emailList["eb_emailtmpl_mdl_un_enrollment_trigger"] = __("Moodle Course Un-Enrollment", 'eb-textdomain');
        $emailList["eb_emailtmpl_mdl_user_deletion_trigger"] = __("User Account Deleted", 'eb-textdomain');

/******************/


        return $emailList;
    }

    /**
     * handles the manage email temaplte page output
     */
    public function outPut()
    {
        if (isset($_POST["eb-mail-tpl-submit"]) && $_POST["eb-mail-tpl-submit"] == "eb-mail-tpl-save-changes") {
            $this->save();
        }
        $fromName = $this->getFromName();
        $tmplList = array();
        $tmplList = apply_filters('eb_email_templates_list', $tmplList);
        $section = array();
        $constSec = apply_filters('eb_email_template_constant', $section);
        $checked = array();
        $notifOn = "";

        if (isset($_GET["curr_tmpl"])) {
            $tmplKey = $_GET["curr_tmpl"];
            $tmplName = $tmplList[$_GET["curr_tmpl"]];
            $notifOn = $this->isNotifEnabled($_GET["curr_tmpl"]);
        } else {
            $tmplKey = key($tmplList);
            $tmplName = current($tmplList);
            $notifOn = $this->isNotifEnabled($tmplKey);
        }

        $tmplData = $this->getEmailTemplate($tmplKey);
        $tmplContent = apply_filters("eb_email_template_data", $tmplData);
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline eb-emailtemp-head"><?php _e("Manage Email Templates", "eb-textdomain"); ?></h1>
            <div class="eb-email-template-wrap">
                <div class="eb-template-edit-form">
                    <h3 id="eb-email-template-name"><?php echo $tmplName; ?></h3>
                    <form name="manage-email-template" method="POST">
                        <input type="hidden" name="eb_tmpl_name" id="eb_emailtmpl_name"
                               value="<?php echo $tmplKey; ?>"/>
                                <?php
                                wp_nonce_field("eb_emailtmpl_sec", "eb_emailtmpl_nonce");
                                ?>
                        <table>
                            <tr>
                                <td class="eb-email-lable"><?php _e("From Name", "eb-textdomain"); ?></td>
                                <td>
                                    <input type="text" name="eb_email_from_name" id="eb_email_from_name" value="<?php echo $fromName; ?>" class="eb-email-input" title="<?php _e("Enter name here to use as the form name in email sent from site using Edwisaer plugins", "eb-textdomain"); ?>" placeholder="<?php _e('Enter from name', 'eb-textdomain'); ?>"/>
                                </td>
                            </tr>

                            <tr>
                                <td class="eb-email-lable"><?php _e("Subject", "eb-textdomain"); ?></td>
                                <td>
                                    <input type="text" name="eb_email_subject" id="eb_email_subject" value="<?php echo $tmplContent['subject']; ?>" class="eb-email-input" title="<?php _e("Enter the subject for the current email template. Current template will use the entered subject to send email from the site", "eb-textdomain"); ?>" placeholder="<?php _e('Enter email subject', 'eb-textdomain'); ?>"/>
                                </td>
                            </tr>

                            <tr>
                                <td class="eb-email-lable"><?php _e("Send email notification to the user?", "eb-textdomain"); ?></td>
                                <td>
                                    <input type="checkbox" name="eb_email_notification_on" id="eb_email_notification_on" value="ON" <?php echo checked($notifOn, "ON"); ?> class="eb-email-input" title="<?php _e("Check the option to notify the user using selected template on action", "eb-textdomain"); ?>" />
                                </td>
                            </tr>



                            <?php

                            do_action("eb_manage_email_template_before_text_editor", $tmplKey);

                            ?>



                            <tr>
                                <td colspan="2" class="eb-template-edit-cell">
                                    <?php
                                    $this->getEditor($tmplContent['content']);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input name="eb-mail-tpl-submit" type="hidden" id="eb-mail-tpl-submit" value="eb-mail-tpl-save-changes" />
                                    <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'eb-textdomain'); ?>" name="eb_save_tmpl" title="<?php _e("Save changes", "eb-textdomain"); ?>"/>
                                    <input type="button" class="button-primary" value="<?php _e("Restore template content", "eb-textdomain"); ?>" id="eb_email_reset_template" name="eb_email_reset_template" />
                                    <input type="hidden" id="current_selected_email_tmpl_key" name="current_selected_email_tmpl_key" value="<?php echo $tmplKey; ?>" />
                                    <input type="hidden" id="current-tmpl-name" name="current_selected_email_tmpl_name" value="<?php echo $tmplContent['subject']; ?>" />
                                </td>
                            </tr>
                        </table>
                    </form>
                    <div class="eb-email-testmail-wrap">
                        <h3><?php _e("Send a test email of the selected template", "eb-textdomain"); ?></h3>
                        <div class="eb-email-temp-test-mail-wrap">
                            <label class="eb-email-lable"><?php _e("To", "eb-textdomain"); ?> : </label>
                            <?php wp_nonce_field("eb_send_testmail_sec", "eb_send_testmail_sec_filed"); ?>
                            <input type="email" name="eb_test_email_add" id="eb_test_email_add_txt" value="" title="<?php _e("Type an email address here and then click Send Test to generate a test email using current selected template", "eb-textdomain"); ?>." placeholder="<?php _e('Enter email address', 'eb-textdomain'); ?>"/>
                            <input type="button" class="button-primary" value="<?php _e("Send Test", "eb-textdomain"); ?>" name="eb_send_test_email" id="eb_send_test_email" title="<?php _e("Send sample email with current selected template", "eb-textdomain"); ?>"/>
                            <span class="load-response">
                                <img alt="<?php __('Sorry, unable to load the image', 'eb-textdomain') ?>" src="<?php echo EB_PLUGIN_URL . '/images/loader.gif'; ?>" height="20" width="20">
                            </span>
                            <div class="response-box">
                            </div>
                        </div>
                        <span class="eb-email-note"><strong><?php _e("Note", "eb-textdomain"); ?>:-</strong> <?php _e("Some of the constants in these emails would be replaced by demo content", "eb-textdomain"); ?>.</span>

                    </div>
                </div>
                <div class="eb-edit-email-template-aside">
                    <div class="eb-email-templates-list">
                        <h3><?php _e("Email Templates", "eb-textdomain"); ?></h3>
                        <ul id="eb_email_templates_list">
                            <?php
                            foreach ($tmplList as $tmplId => $tmplName) {
                                if ($tmplKey == $tmplId) {
                                    echo "<li id='$tmplId' class='eb-emailtmpl-list-item eb-emailtmpl-active'>$tmplName</li>";
                                } else {
                                    echo "<li id='$tmplId' class='eb-emailtmpl-list-item'>$tmplName</li>";
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="eb-email-templates-const-wrap">
                        <h3><?php _e("Template Constants", "eb-textdomain"); ?></h3>
                        <div class="eb-emiltemp-const-wrap">
                            <?php
                            foreach ($constSec as $secName => $tmplConst) {
                                echo "<div class='eb-emailtmpl-const-sec'>";
                                echo "<h3>$secName</h3>";
                                foreach ($tmplConst as $const => $desc) {
                                    echo '<div class="eb-mail-templat-const"><span>' . $const . '</span>' . $desc . '</div>';
                                }
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Provides the functionality to check is the notification enabled for the email temaplte.
     * @param string $currTmplName email temaplte option key
     * @return string returns ON if the email template is enambled for the provided template
     */
    private function isNotifEnabled($currTmplName)
    {
        $notifEnabled = get_option($currTmplName . "_notify_allow");
        if (isset($notifEnabled) && !empty($notifEnabled) && $notifEnabled=="ON") {
            return "ON";
        } else {
            return "";
        }
    }

    /**
     * Provides the functionality to prepare the wp editor for the email template edit
     * @param TinyMCE editor $content returns the TinyMCE  editor with the template content
     */
    private function getEditor($content)
    {

        $settings = array(
            'media_buttons' => false,
            'drag_drop_upload' => false,
            'textarea_rows' => 15,
        );
        wp_editor($content, 'eb_emailtmpl_editor', $settings);
    }

    /**
     * Provides the functionality to add the mce plugin for the email tempalte editing
     * callback for the mce_external_plugins actoin
     * @return string
     */
    public function addMCEPlugin()
    {
        $plugins = array("legacyoutput" => plugins_url('assets/', __FILE__) . 'tinymce/legacyoutput/plugin.min.js');
        return $plugins;
    }

    /**
     * Ajax callback to get the template content
     * callback for the action wdm_eb_get_email_template
     */
    public function getTemplateDataAjaxCallBack()
    {

        $data = array();
        if (isset($_POST['tmpl_name'])) {
            $tmplData = get_option($_POST['tmpl_name']);
            $notifyAllow = get_option($_POST['tmpl_name'] . "_notify_allow");
            $data['from_name'] = $this->getFromName();
            $data['subject'] = $tmplData['subject'];
            $data['content'] = $tmplData['content'];
            $data['notify_allow'] = $notifyAllow;
        }
        echo json_encode($data);
        die();
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
        $fromEmail = get_option("eb_mail_from_email");
        if ($fromEmail == false) {
            $fromEmail = get_option("admin_email");
        }
        return $fromEmail;
    }

    /**
     * Provides the functoinality to get the From email name
     * @return string returns the from name for the email
     */
    private function getFromName()
    {
        $fromName = get_option("eb_mail_from_name");
        if ($fromName == false) {
            $fromName = get_bloginfo("name");
        }
        return $fromName;
    }

    /**
     * Defaines the email template constants
     * callback for the action eb_email_template_constant
     */
    public function emailTemplateContsnt($constants)
    {
        /**
         * Genral constants.
         */
        $genral["{USER_NAME}"] = __("The display name of the user.", 'eb-textdomain');
        $genral["{FIRST_NAME}"] = __("The first name of the user.", 'eb-textdomain');
        $genral["{LAST_NAME}"] = __("The last name of the user.", 'eb-textdomain');
        $genral["{SITE_NAME}"] = __("The name of the website.", 'eb-textdomain');
        $genral["{SITE_URL}"] = __("The URL of the website.", 'eb-textdomain');
        $genral["{COURSES_PAGE_LINK}"] = __("The link to the courses archive page.", 'eb-textdomain');
        $genral["{MY_COURSES_PAGE_LINK}"] = __("The link to the my courses page.", 'eb-textdomain');
        $genral["{USER_ACCOUNT_PAGE_LINK}"] = __("The wordpress user account page link.", 'eb-textdomain');
        $genral["{WP_LOGIN_PAGE_LINK}"] = __("The wordpress login page link.", 'eb-textdomain');
        $genral["{MOODLE_URL}"] = __("The moodle site url entered in the connection.", 'eb-textdomain');
        /**
         * New account and link account constants
         */
        $account["{USER_PASSWORD}"] = __("The user accounts password.", 'eb-textdomain');
        /**
         * Course order template constants
         */
//		$constants["Course order complet template constants"]="<hr>";
        $order["{COURSE_NAME}"] = __("The title of the course.", 'eb-textdomain');
        $order["{ORDER_ID}"] = __("The order id of the purchased order completed.", 'eb-textdomain');

        /*
         *Refund Order template constants
         */
        $refund['{ORDER_ID}'] = __("Refund order id.", 'eb-textdomain');
        $refund['{CUSTOMER_DETAILS}'] = __("This will get replaced by the customer details.", 'eb-textdomain');
        $refund['{ORDER_ITEM}'] = __("Order associated item list.", 'eb-textdomain');
        $refund['{TOTAL_AMOUNT_PAID}'] = __("Amount paid at the time of order placed.", 'eb-textdomain');
        $refund['{CURRENT_REFUNDED_AMOUNT}'] = __("Currantly refunded amount.", 'eb-textdomain');
        $refund['{TOTAL_REFUNDED_AMOUNT}'] = __("Total amount refunded till the time.", 'eb-textdomain');
        $refund['{ORDER_REFUND_STATUS}'] = __("Order refund status transaction.", 'eb-textdomain');
//        $refund['{REFUND_AMOUNT}'] = __("Refunded amount for the oder", 'eb-textdomain');
//        $refund['{REFUND_DATE}'] = __("Refund completion date.", 'eb-textdomain');
//        $refund['{REFUND_TXN_ID}'] = __("Refund transaction ID", 'eb-textdomain');


        /**
         * Course unenrollment alert constants
         */
        $unenrollment["{WP_COURSE_PAGE_LINK}"] = __("The current course page link.", 'eb-textdomain');

        $constants["General constants"] = $genral;
        $constants["New moodle user account"] = $account;
        $constants["Order Completion "] = $order;
        $constants["Course Unenrollment "] = $unenrollment;
        $constants["Order Refund"] = $refund;
        return $constants;
    }

    /**
     * Provides the functioanlity to get the template contetn from teh database
     * @param type $tmplName the option key to fetch the email temaplate content
     * @return returns the array of the email template subject and content
     */
    private function getEmailTemplate($tmplName)
    {
        return get_option($tmplName);
    }
    /**
     * Getter methods end
     */

    /**
     * Setter methods start
     */

    private function setFromName($name)
    {
        update_option("eb_mail_from_name", $name);
    }

    /**
     * Settor method to store the email template content
     * Stores the email temaplte content in the wp opotions table with the key @parm $tempalteName
     * @param type $tempalteName template option key to store into the databse
     * @param type $tempalteData store the template conten in the database
     */
    private function setTemplateData($tempalteName, $tempalteData)
    {
        update_option($tempalteName, $tempalteData);
    }

    /**
     * Provides the functionality to set the notification enable disable value into the databse
     * @param type $tempalteName template option key
     * @param type $notifyAllow is notificaiotn allow to send or not
     */
    private function setNotifyAllow($tempalteName, $notifyAllow)
    {
        update_option($tempalteName . "_notify_allow", $notifyAllow);
    }

    /**
     * Provides the functionality to save the email temaplte content into the database
     */
    private function save()
    {
        if (isset($_POST["eb_emailtmpl_nonce"]) && wp_verify_nonce($_POST["eb_emailtmpl_nonce"], "eb_emailtmpl_sec")) {
            $fromName = $this->checkIsEmpty($_POST, "eb_email_from_name");
            $subject = $this->checkIsEmpty($_POST, "eb_email_subject");
            $tmplContetn = $this->checkIsEmpty($_POST, "eb_emailtmpl_editor");
            $tmplName = $this->checkIsEmpty($_POST, "eb_tmpl_name");
            $notifyAllow = $this->checkIsEmpty($_POST, "eb_email_notification_on");
            $notifyAllow = $notifyAllow == "ON" ? $notifyAllow : "OFF";
            $data = array(
                "subject" => $subject,
                "content" => stripslashes($tmplContetn),
            );
            $this->setFromName($fromName);
            $this->setNotifyAllow($tmplName, $notifyAllow);
            $this->setTemplateData($tmplName, $data);
            echo self::getNoticeHtml(__('Changes saved successfully!', 'eb-textdomain'));
        } else {
            echo self::getNoticeHtml(__('Due to the security issue changes are not saved, Try to re-update it.', 'eb-textdomain'), "error");
        }
    }

    /**
     * Checks the array value is set for the current key
     * @param type $dataArray array of the data
     * @param type $key key to check value is present in the array
     * @return boolean/string the value associated for the array key otherwise returns false
     */
    private function checkIsEmpty($dataArray, $key)
    {
        if (isset($dataArray[$key]) && !empty($dataArray[$key])) {
            return $dataArray[$key];
        } else {
            return false;
        }
    }

    /**
     * Provides teh functioanlityto get the email tempalte constant
     * @param type $tmplName template key
     * @return string returns the template content associated with the template
     * kay othrewise emapty string
     */
    public static function getEmailTmplContent($tmplName)
    {
        $tmplContent = get_option($tmplName);
        if ($tmplContent) {
            return $tmplContent;
        }
        return "";
    }

    /**
     * Provides the functioanlity to send the test email
     */
    public function sendTestEmail()
    {
        if (isset($_POST["security"]) && wp_verify_nonce($_POST["security"], "eb_send_testmail_sec")) {
            $mailTo = $this->checkIsEmpty($_POST, "mail_to");
            /**
             * Dummy data.
             */
            $args = array(
                "course_id" => "1",
                "password" => "eb-pa88@#d",
                "eb_order_id" => "12235" // chnaged 1.4.7
            );
            $mail = $this->sendEmail($mailTo, $args, $_POST);
            if ($mail) {
                wp_send_json_success("OK");
            } else {
                wp_send_json_error("Failed to send test email.");
            }
        } else {
            wp_send_json_error("Invalid request");
        }
    }

    /**
     * Provides the funcationlity to send the email temaplte
     * @param type $mailTo email id to send the email id
     * @param type $args the default email argument
     * @param type $tmplData email template contetn
     * @return boolean returns true if the email sent successfully othrewise false
     */
    public function sendEmail($mailTo, $args, $tmplData)
    {
        $fromEmail = $this->getFromEmail();
        $fromName = $this->getFromName();
        $subject = $this->checkIsEmpty($tmplData, "subject");
        $tmplContent = stripslashes($this->checkIsEmpty($tmplData, "content"));

        /**
         * Call the email template parser
         */
        $emailTmplParser = new EBEmailTmplParser();
        $tmplContent = $emailTmplParser->outPut($args, $tmplContent);

        /**
         * Email send start
         */
        $tmplContent = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
                . '<html>'
                . '<body>'
                . $tmplContent
                . "</body>"
                . "</html>";

        $headers = array('Content-Type: text/html; charset=UTF-8; http-equiv="Content-Language" content="en-us"');


        add_filter('wp_mail_content_type', function () {
            return "text/html";
        });


        $mail = wp_mail($mailTo, $subject, $tmplContent, $headers);
        remove_filter('wp_mail_content_type', function () {
            return "text/html";
        });

        remove_filter('wp_mail_from_name', array($this, "wpb_sender_name"));
        /**
         * Email send end
         */
        return $mail;
    }

    /**
     * Functioanlity to fetch the from email from database
     * @return string returns from email
     */
    public function wpbSenderEmail($email)
    {
        return $this->getFromEmail();
    }

    /**
     * Functioanlity to fetch the from email from database
     * @param type $name
     * @return string returns from email
     */
    public function wpbSenderName($name)
    {
        return $this->getFromName();
    }

    /**
     * Prepares the email tempalte content
     * @param type $msg
     * @param type $type
     * @param type $dismissible
     * @return type
     */
    public static function getNoticeHtml($msg, $type = 'success', $dismissible = true)
    {
        $classes = 'notice notice-' . $type;
        if ($dismissible) {
            $classes .= ' is-dismissible';
        }
        ob_start();
        ?>
        <div class="<?php echo $classes; ?>">
            <p><?php echo $msg; ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Provides the functionality to restore the email temaplte content and subject
     */
    public function resetEmailTemplateContent()
    {
        $responce = array("data"=>__("Failed to reset email template", "eb-textdomain"),"status"=>"failed");
        if (isset($_POST['action']) && isset($_POST['tmpl_name']) && $_POST['action'] == "wdm_eb_email_tmpl_restore_content") {
            $args = $this->restoreEmailTemplate(array("is_restored" => false, "tmpl_name"=>$_POST['tmpl_name']));
            if ($args["is_restored"] == true) {
                $responce['data'] = __("Template restored successfully", "eb-textdomain");
                $responce['status']="success";
                wp_send_json_success($responce);
            } else {
                wp_send_json_error($responce);
            }
        } else {
            wp_send_json_error($responce);
        }
    }

    /**
     * Provides the functonality to restore the email temaplte content
     * @param type $args
     * @return boolean
     */
    public function restoreEmailTemplate($args)
    {
        $defaultTmpl = new EBDefaultEmailTemplate();
        $tmplKey=$args['tmpl_name'];
        switch ($tmplKey) {
            case "eb_emailtmpl_create_user":
                $value=$defaultTmpl->newUserAcoount("eb_emailtmpl_create_user", true);
                break;
            case "eb_emailtmpl_linked_existing_wp_user":
                $value=$defaultTmpl->linkWPMoodleAccount("eb_emailtmpl_linked_existing_wp_user", true);
                break;
            case "eb_emailtmpl_order_completed":
                $value=$defaultTmpl->orderComplete("eb_emailtmpl_order_completed", true);
                break;
            case "eb_emailtmpl_course_access_expir":
                $value=$defaultTmpl->courseAccessExpired("eb_emailtmpl_course_access_expir", true);
                break;
            case "eb_emailtmpl_linked_existing_wp_new_moodle_user":
                $value=$defaultTmpl->linkNewMoodleAccount("eb_emailtmpl_linked_existing_wp_new_moodle_user", true);
                break;

            case "eb_emailtmpl_refund_completion_notifier_to_user":
                $value=$defaultTmpl->notifyUserOnOrderRefund("eb_emailtmpl_refund_completion_notifier_to_user", true);
                break;
            case "eb_emailtmpl_refund_completion_notifier_to_admin":
                $value=$defaultTmpl->notifyAdminOnOrderRefund("eb_emailtmpl_refund_completion_notifier_to_admin", true);
                break;


            case "eb_emailtmpl_mdl_enrollment_trigger":
                $value=$defaultTmpl->moodleEnrollmentTrigger("eb_emailtmpl_mdl_enrollment_trigger", true);
                break;

            case "eb_emailtmpl_mdl_un_enrollment_trigger":
                $value=$defaultTmpl->moodleUnenrollmentTrigger("eb_emailtmpl_mdl_un_enrollment_trigger", true);
                break;

            case "eb_emailtmpl_mdl_user_deletion_trigger":
                $value=$defaultTmpl->userDeletionTrigger("eb_emailtmpl_mdl_user_deletion_trigger", true);
                break;


            default:
                $args=apply_filters("eb_reset_email_tmpl_content", array("is_restored" => false, "tmpl_name"=>$args['tmpl_name']));
                return $args;
        }
        $status=  update_option($tmplKey, $value);
        if ($status) {
            $args['is_restored']=true;
            return $args;
        } else {
            return $args;
        }
    }
}
