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
 * EBAdminEmailTemplate Class
 */
class EBAdminEmailTemplate
{

	public function __construct()
	{
		add_filter('mce_external_plugins', array($this, 'add_mce_plugin'));
		/**
		 * Filter for the email template list and email temaplte constant.
		 */
		add_filter("eb_email_templates_list", array($this, "eb_add_email_list"), 10, 1);
		add_filter("eb_email_template_constant", array($this, "email_template_constant"), 10, 1);
		add_filter('wp_mail_from_name', array($this, "wp_sender_name"), 99, 1);
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
	public function eb_add_email_list($emailList)
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
		$from_name = $this->get_from_name();
		$tmpl_list = array();
		$tmpl_list = apply_filters('eb_email_templates_list', $tmpl_list);
		$section   = array();
		$const_sec = apply_filters('eb_email_template_constant', $section);
		$checked   = array();
		$notif_on  = "";


		if (isset($_GET["curr_tmpl"])) {
			$tmpl_key  = $_GET["curr_tmpl"];
			$tmpl_name = $tmpl_list[$_GET["curr_tmpl"]];
			$notif_on  = $this->is_not_if_enabled($_GET["curr_tmpl"]);
			$bcc_email = $this->get_bcc_email($_GET["curr_tmpl"]);
		} else {
			$tmpl_key  = key($tmpl_list);
			$tmpl_name = current($tmpl_list);
			$notif_on  = $this->is_not_if_enabled($tmpl_key);
			$bcc_email  = $this->get_bcc_email($tmpl_key);
		}

		$tmplData = $this->get_email_template($tmpl_key);
		$tmplContent = apply_filters("eb_email_template_data", $tmplData);
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline eb-emailtemp-head"><?php _e("Manage Email Templates", "eb-textdomain"); ?></h1>
			<div class="eb-email-template-wrap">
				<div class="eb-template-edit-form">
					<h3 id="eb-email-template-name"><?php echo $tmpl_name; ?></h3>
					<form name="manage-email-template" method="POST">
						<input type="hidden" name="eb_tmpl_name" id="eb_emailtmpl_name"
							   value="<?php echo $tmpl_key; ?>"/>
								<?php
								wp_nonce_field("eb_emailtmpl_sec", "eb_emailtmpl_nonce");
								?>
						<table>
							<tr>
								<td class="eb-email-lable"><?php _e("From Name", "eb-textdomain"); ?></td>
								<td>
									<input type="text" name="eb_email_from_name" id="eb_email_from_name" value="<?php echo $from_name; ?>" class="eb-email-input" title="<?php _e("Enter name here to use as the form name in email sent from site using Edwisaer plugins", "eb-textdomain"); ?>" placeholder="<?php _e('Enter from name', 'eb-textdomain'); ?>"/>
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
									<input type="checkbox" name="eb_email_notification_on" id="eb_email_notification_on" value="ON" <?php echo checked($notif_on, "ON"); ?> class="eb-email-input" title="<?php _e("Check the option to notify the user using selected template on action", "eb-textdomain"); ?>" />
								</td>
							</tr>


					        <tr>
					            <td class="eb-email-lable">
					                <?php _e("Additional Email Adress For BCC in Mail", "eb-extension"); ?>
					            </td>
					            <td>
					                <input type="text" value="<?= $bcc_email ?>" name="eb_bcc_email" id="eb_bcc_email" class="eb-email-input"/>
					            </td>
					        </tr>
					        <?php

							do_action("eb_manage_email_template_before_text_editor", $tmpl_key);

							?>



							<tr>
								<td colspan="2" class="eb-template-edit-cell">
									<?php
									$this->get_editor($tmplContent['content']);
									?>
								</td>
							</tr>
							<tr>
								<td>
									<input name="eb-mail-tpl-submit" type="hidden" id="eb-mail-tpl-submit" value="eb-mail-tpl-save-changes" />
									<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'eb-textdomain'); ?>" name="eb_save_tmpl" title="<?php _e("Save changes", "eb-textdomain"); ?>"/>
									<input type="button" class="button-primary" value="<?php _e("Restore template content", "eb-textdomain"); ?>" id="eb_email_reset_template" name="eb_email_reset_template" />
									<input type="hidden" id="current_selected_email_tmpl_key" name="current_selected_email_tmpl_key" value="<?php echo $tmpl_key; ?>" />
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
							foreach ($tmpl_list as $tmplId => $tmpl_name) {
								if ($tmpl_key == $tmplId) {
									echo "<li id='$tmplId' class='eb-emailtmpl-list-item eb-emailtmpl-active'>$tmpl_name</li>";
								} else {
									echo "<li id='$tmplId' class='eb-emailtmpl-list-item'>$tmpl_name</li>";
								}
							}
							?>
						</ul>
					</div>
					<div class="eb-email-templates-const-wrap">
						<h3><?php _e("Template Constants", "eb-textdomain"); ?></h3>
						<div class="eb-emiltemp-const-wrap">
							<?php
							foreach ($const_sec as $secName => $tmplConst) {
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
	private function is_not_if_enabled($curr_tmpl_name)
	{
		$notif_enabled = get_option($curr_tmpl_name . "_notify_allow");
		if (isset($notif_enabled) && !empty($notif_enabled) && $notif_enabled=="ON") {
			return "ON";
		} else {
			return "";
		}
	}


	public function get_bcc_email($curr_tmpl_name) {
		$bcc_email = get_option($curr_tmpl_name . "_bcc_email");
		if (!$bcc_email) {
			$bcc_email =  "";
		}
		return $bcc_email;
	}


	/**
	 * Provides the functionality to prepare the wp editor for the email template edit
	 * @param TinyMCE editor $content returns the TinyMCE  editor with the template content
	 */
	private function get_editor($content)
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
	public function add_mce_plugin()
	{
		$plugins = array("legacyoutput" => plugins_url('assets/', __FILE__) . 'tinymce/legacyoutput/plugin.min.js');
		return $plugins;
	}

	/**
	 * Ajax callback to get the template content
	 * callback for the action wdm_eb_get_email_template
	 */
	public function get_template_data_ajax_callBack()
	{

		
		$data = array();
		if (isset($_POST['tmpl_name']) && isset($_POST['admin_nonce']) && wp_verify_nonce($_POST['admin_nonce'], 'eb_admin_nonce')) {
			$tmpl_data    = get_option($_POST['tmpl_name']);
			$notify_allow = get_option($_POST['tmpl_name'] . "_notify_allow");
			$bcc_email    = get_option($_POST['tmpl_name'] . "_bcc_email");

			if (!$bcc_email) {
				$bcc_email = '';
			}

			$data['from_name']    = $this->get_from_name();
			$data['subject']      = $tmpl_data['subject'];
			$data['content']      = $tmpl_data['content'];
			$data['notify_allow'] = $notify_allow;
			$data['bcc_email']    = $bcc_email;
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
	private function get_from_email()
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
	private function get_from_name()
	{
		$from_name = get_option("eb_mail_from_name");
		if ($from_name == false) {
			$from_name = get_bloginfo("name");
		}
		return $from_name;
	}

	/**
	 * Defaines the email template constants
	 * callback for the action eb_email_template_constant
	 */
	public function email_template_constant($constants)
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
	private function get_email_template($tmpl_name)
	{
		return get_option($tmpl_name);
	}
	/**
	 * Getter methods end
	 */

	/**
	 * Setter methods start
	 */

	private function set_from_name($name)
	{
		update_option("eb_mail_from_name", $name);
	}

	/**
	 * Settor method to store the email template content
	 * Stores the email temaplte content in the wp opotions table with the key @parm $tempalteName
	 * @param type $tempalteName template option key to store into the databse
	 * @param type $tempalteData store the template conten in the database
	 */
	private function set_template_data($tempalte_name, $tempalteData)
	{
		update_option($tempalte_name, $tempalteData);
	}

	/**
	 * Provides the functionality to set the notification enable disable value into the databse
	 * @param type $tempalteName template option key
	 * @param type $notifyAllow is notificaiotn allow to send or not
	 */
	private function set_notify_allow($tempalte_name, $notifyAllow)
	{
		update_option($tempalte_name . "_notify_allow", $notifyAllow);
	}


	/**
	 * Provides the functionality to set the notification enable disable value into the databse
	 * @param type $tempalteName template option key
	 * @param type $notifyAllow is notificaiotn allow to send or not
	 */
	private function set_bcc_email_address($tempalte_name, $notifyAllow)
	{
		update_option($tempalte_name . "_bcc_email", $notifyAllow);
	}


	/**
	 * Provides the functionality to save the email temaplte content into the database
	 */
	private function save()
	{
		if (isset($_POST["eb_emailtmpl_nonce"]) && wp_verify_nonce($_POST["eb_emailtmpl_nonce"], "eb_emailtmpl_sec")) {
			$from_name = $this->check_is_empty($_POST, "eb_email_from_name");
			$subject = $this->check_is_empty($_POST, "eb_email_subject");
			$tmpl_contetn = $this->check_is_empty($_POST, "eb_emailtmpl_editor");
			$tmpl_name = $this->check_is_empty($_POST, "eb_tmpl_name");
			$notify_allow = $this->check_is_empty($_POST, "eb_email_notification_on");
			$notify_allow = $notify_allow == "ON" ? $notify_allow : "OFF";
			$bcc_email = $this->check_is_empty($_POST, "eb_bcc_email");

			$data = array(
				"subject" => $subject,
				"content" => stripslashes($tmpl_contetn),
			);

			$this->set_from_name($from_name);
			$this->set_notify_allow($tmpl_name, $notify_allow);
			$this->set_template_data($tmpl_name, $data);
			$this->set_bcc_email_address($tmpl_name, $bcc_email);

			echo self::get_notice_html(__('Changes saved successfully!', 'eb-textdomain'));
		} else {
			echo self::get_notice_html(__('Due to the security issue changes are not saved, Try to re-update it.', 'eb-textdomain'), "error");
		}
	}

	/**
	 * Checks the array value is set for the current key
	 * @param type $dataArray array of the data
	 * @param type $key key to check value is present in the array
	 * @return boolean/string the value associated for the array key otherwise returns false
	 */
	private function check_is_empty($dataArray, $key)
	{
		if (isset($dataArray[$key]) && !empty($dataArray[$key])) {
			return $dataArray[$key];
		} else {
			return false;
		}
	}



	/**
	 * DEPRECATED FUNCTION.
	 * 
	 * Provides teh functioanlityto get the email tempalte constant
	 * @param type $tmplName template key
	 * @return string returns the template content associated with the template
	 * kay othrewise emapty string
	 */
	public static function getEmailTmplContent($tmpl_name)
	{
		$tmplContent = get_option($tmpl_name);
		if ($tmplContent) {
			return $tmplContent;
		}
		return "";
	}



	/**
	 * Provides teh functioanlityto get the email tempalte constant
	 * @param type $tmplName template key
	 * @return string returns the template content associated with the template
	 * kay othrewise emapty string
	 */
	public static function get_email_tmpl_content($tmpl_name)
	{
		$tmplContent = get_option($tmpl_name);
		if ($tmplContent) {
			return $tmplContent;
		}
		return "";
	}

	/**
	 * Provides the functioanlity to send the test email
	 */
	public function send_test_email()
	{
		if (isset($_POST["security"]) && wp_verify_nonce($_POST["security"], "eb_send_testmail_sec")) {
			$mailTo = $this->check_is_empty($_POST, "mail_to");
			/**
			 * Dummy data.
			 */
			$args = array(
				"course_id"   => "1",
				"password"    => "eb-pa88@#d",
				"eb_order_id" => "12235", // chnaged 1.4.7
				"headers"     => isset($_POST['headers']) ? $_POST['headers'] : "",
			);
			$mail = $this->send_email($mailTo, $args, $_POST);
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
	 * DEPRECATED FUNCTION.
	 *
	 * Provides the funcationlity to send the email temaplte
	 * @param type $mailTo email id to send the email id
	 * @param type $args the default email argument
	 * @param type $tmplData email template contetn
	 * @return boolean returns true if the email sent successfully othrewise false
	 */
	public function sendEmail($mailTo, $args, $tmplData)
	{

		$fromEmail = $this->get_from_email();
		$from_name = $this->get_from_name();
		$subject = $this->check_is_empty($tmplData, "subject");
		$tmplContent = stripslashes($this->check_is_empty($tmplData, "content"));

		/**
		 * Call the email template parser
		 */
		$emailTmplParser = new Eb_Email_Tmpl_Parser();
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


		//CUSTOMIZATION CHANGES
		if (isset($args["headers"])) {
	        $headers[] = $args["headers"];
		}



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
	 * Provides the funcationlity to send the email temaplte
	 * @param type $mailTo email id to send the email id
	 * @param type $args the default email argument
	 * @param type $tmplData email template contetn
	 * @return boolean returns true if the email sent successfully othrewise false
	 */
	public function send_email($mailTo, $args, $tmplData)
	{


		$fromEmail = $this->get_from_email();
		$from_name = $this->get_from_name();
		$subject = $this->check_is_empty($tmplData, "subject");
		$tmplContent = stripslashes($this->check_is_empty($tmplData, "content"));

		/**
		 * Call the email template parser
		 */
		$emailTmplParser = new Eb_Email_Tmpl_Parser();
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


		//CUSTOMIZATION CHANGES
		if (isset($args["headers"])) {
	        $headers[] = $args["headers"];
		}


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
		return $this->get_from_email();
	}

	/**
	 * Functioanlity to fetch the from email from database
	 * @param type $name
	 * @return string returns from email
	 */
	public function wp_sender_name($name)
	{
		return $this->get_from_name();
	}

	/**
	 * Prepares the email tempalte content
	 * @param type $msg
	 * @param type $type
	 * @param type $dismissible
	 * @return type
	 */
	public static function get_notice_html($msg, $type = 'success', $dismissible = true)
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
	public function reset_email_template_content()
	{
		$responce = array("data"=>__("Failed to reset email template", "eb-textdomain"),"status"=>"failed");
		if (isset($_POST['action']) && isset($_POST['tmpl_name']) && $_POST['action'] == "wdm_eb_email_tmpl_restore_content" && isset($_POST['admin_nonce']) && wp_verify_nonce($_POST['admin_nonce'], 'eb_admin_nonce')) {

			$args = $this->restore_email_template(array("is_restored" => false, "tmpl_name"=>$_POST['tmpl_name']));
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
	public function restore_email_template($args)
	{
		$defaultTmpl = new Eb_Default_Email_Template();
		$tmpl_key=$args['tmpl_name'];
		switch ($tmpl_key) {
			case "eb_emailtmpl_create_user":
				$value=$defaultTmpl->new_user_acoount("eb_emailtmpl_create_user", true);
				break;
			case "eb_emailtmpl_linked_existing_wp_user":
				$value=$defaultTmpl->link_wp_moodle_account("eb_emailtmpl_linked_existing_wp_user", true);
				break;
			case "eb_emailtmpl_order_completed":
				$value=$defaultTmpl->order_complete("eb_emailtmpl_order_completed", true);
				break;
			case "eb_emailtmpl_course_access_expir":
				$value=$defaultTmpl->course_access_expired("eb_emailtmpl_course_access_expir", true);
				break;
			case "eb_emailtmpl_linked_existing_wp_new_moodle_user":
				$value=$defaultTmpl->link_new_moodle_account("eb_emailtmpl_linked_existing_wp_new_moodle_user", true);
				break;

			case "eb_emailtmpl_refund_completion_notifier_to_user":
				$value=$defaultTmpl->notify_user_on_order_refund("eb_emailtmpl_refund_completion_notifier_to_user", true);
				break;
			case "eb_emailtmpl_refund_completion_notifier_to_admin":
				$value=$defaultTmpl->notify_admin_on_order_refund("eb_emailtmpl_refund_completion_notifier_to_admin", true);
				break;


			case "eb_emailtmpl_mdl_enrollment_trigger":
				$value=$defaultTmpl->moodle_enrollment_trigger("eb_emailtmpl_mdl_enrollment_trigger", true);
				break;

			case "eb_emailtmpl_mdl_un_enrollment_trigger":
				$value=$defaultTmpl->moodle_unenrollment_trigger("eb_emailtmpl_mdl_un_enrollment_trigger", true);
				break;

			case "eb_emailtmpl_mdl_user_deletion_trigger":
				$value=$defaultTmpl->user_deletion_trigger("eb_emailtmpl_mdl_user_deletion_trigger", true);
				break;


			default:
				$args=apply_filters("eb_reset_email_tmpl_content", array("is_restored" => false, "tmpl_name"=>$args['tmpl_name']));
				return $args;
		}
		$status=  update_option($tmpl_key, $value);
		if ($status) {
			$args['is_restored']=true;
			return $args;
		} else {
			return $args;
		}
	}
}
