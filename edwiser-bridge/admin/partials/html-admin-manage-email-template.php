<?php
/**
 * Partial: Page - Extensions.
 *
 * @var object
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline eb-emailtemp-head">Manage Email Template</h1>
    <div class="eb-email-template-wrap">
        <div class="eb-template-edit-form">
            <h3 id="eb-email-template-name"><?php echo $tmplContent['tmpl_name'] ?></h3>
            <form name="manage-email-template" method="POST">
                <?php
                wp_nonce_field('eb_email_template_nonce', 'eb_update_email_template_filed');
                ?>
                <table>
                    <tr>
                        <td class="eb-email-lable">From Email</td>
                        <td>
                            <input type="email" name="eb_email_from" id="eb_email_from" value="<?php echo $fromEmail; ?>" class="eb-email-input" title="Enter an email address here to use as the form mail in email sent from site using Edwisaer plugins" placeholder="Enter from email address"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="eb-email-lable">From Name</td>
                        <td>
                            <input type="text" name="eb_email_from_name" id="eb_email_from_name" value="<?php echo $fromName; ?>" class="eb-email-input" title="Enter name here to use as the form name in email sent from site using Edwisaer plugins" placeholder="Enter from name"/>
                        </td>
                    </tr>

                    <tr>
                        <td class="eb-email-lable">Subject</td>
                        <td>
                            <input type="text" name="eb_email_subject" id="eb_email_subject" value="<?php echo $tmplContent['tmpl_subject']; ?>" class="eb-email-input" title="Enter the subject for the current email template. Current template will use the entered subject to send email from the site" placeholder="Enter email subject"/>
                        </td>
                    </tr>
                    <tr>    

                        <td colspan="2" class="eb-template-edit-cell">
                            <?php wp_editor($tmplContent['content'], 'eb_template_email_content', $settings) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="submit" class="button-primary" value="Save Changes" name="eb_send_test_email" title="Save changes"/>
                        </td>
                    </tr>
                </table>
            </form>
            <div class="eb-email-testmail-wrap">
                <h3>Send test email using current selected template</h3>
                <div class="eb-email-temp-test-mail-wrap">
                    <label class="eb-email-lable">To : </label>
                    <input type="email" name="eb_test_email_add" id="eb_test_email_add" value="" title="Type an email address here and then click Send Test to generate a test email using current selected template." placeholder="Enter email address"/>
                    <input type="button" class="button-primary" value="Send Test" name="eb_send_test_email" title="Send sample email with current selected template"/>
                </div>
            </div>
        </div>
        <div class="eb-edit-email-template-aside">
            <div class="eb-email-templates-list">
                <h3>Email Templates</h3>
                <ul id="eb_email_templates_list">
                    <?php
                    $tmplList = array();
                    $tmplList = apply_filters('eb_email_templates_list', $tmplList);
                    foreach ($tmplList as $tmplId => $tmplName) {
                        echo "<li id='$tmplId'>$tmplName</li>";
                    }
                    ?>                  
                </ul>
            </div>
            <div class="eb-email-templates-const-wrap">
                <h3>Template Constants</h3>
                <div class="eb-emiltemp-const-wrap">
                    <?php
                    $constants = array();
                    $tmplConst = apply_filters('eb_email_template_constant', $constants);
                    foreach ($tmplConst as $const => $desc) {
                        echo '<div class="eb-mail-templat-const"><span>{'.$const.'}</span>'.$desc.'</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
