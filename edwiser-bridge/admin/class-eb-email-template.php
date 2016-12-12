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

	public function __construct() {

		/**
		 * Filter for the email template list and email temaplte constant.
		 */
		add_filter( "eb_email_templates_list", array( $this, "ebAddEmailList" ), 10, 1 );
		add_filter( "eb_email_template_constant", array( $this, "emailTemplateContsnt" ), 10, 1 );
	}

	public function ebAddEmailList( $emailList ) {
		$emailList["eb_emailtmpl_create_user"] = "New User Account Details";
		$emailList["eb_emailtmpl_linked_existing_wp_user"] = "Link WP user account to moodle";
		$emailList["eb_emailtmpl_order_completed"] = "Course order complet";
		return $emailList;
	}

	/**
	 * handle extensions page output
	 */
	public function outPut() {
		if ( isset( $_POST["eb_save_tmpl"] ) && $_POST["eb_save_tmpl"] == "Save Changes" ) {
			$this->save();
		}
		$fromEmail = $this->getFromEmail();
		$fromName = $this->getFromName();
		$tmplData = $this->getEmailTemplate( "eb_emailtmpl_create_user" );
		$tmplContent = apply_filters( "eb_email_template_data", $tmplData );
		$tmplList = array();
		$tmplList = apply_filters( 'eb_email_templates_list', $tmplList );
		$constants = array();
		$tmplConst = apply_filters( 'eb_email_template_constant', $constants );
		$tmplKey = key( $tmplList );
		$tmplName = current( $tmplList );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline eb-emailtemp-head">Manage Email Template</h1>
			<div class="eb-email-template-wrap">
				<div class="eb-template-edit-form">
					<h3 id="eb-email-template-name"><?php echo $tmplName; ?></h3>
					<form name="manage-email-template" method="POST">
						<input type="hidden" name="eb_tmpl_name" id="eb_emailtmpl_name" value="<?php echo $tmplKey; ?>"/>
						<?php
						wp_nonce_field( "eb_emailtmpl_sec", "eb_emailtmpl_nonce" );
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
									<input type="text" name="eb_email_subject" id="eb_email_subject" value="<?php echo $tmplContent['subject']; ?>" class="eb-email-input" title="Enter the subject for the current email template. Current template will use the entered subject to send email from the site" placeholder="Enter email subject"/>
								</td>
							</tr>
							<tr>	
								<td colspan="2" class="eb-template-edit-cell">
									<?php
									$this->getEditor( $tmplContent['content'] );
									?>
								</td>
							</tr>
							<tr>
								<td>
									<input type="submit" class="button-primary" value="Save Changes" name="eb_save_tmpl" title="Save changes"/>
								</td>
							</tr>
						</table>
					</form>
					<div class="eb-email-testmail-wrap">
						<h3>Send test email using current selected template</h3>
						<div class="eb-email-temp-test-mail-wrap">
							<label class="eb-email-lable">To : </label>
							<?php wp_nonce_field( "eb_send_testmail_sec", "eb_send_testmail_sec_filed" ); ?>
							<input type="email" name="eb_test_email_add" id="eb_test_email_add_txt" value="" title="Type an email address here and then click Send Test to generate a test email using current selected template." placeholder="Enter email address"/>
							<input type="button" class="button-primary" value="Send Test" name="eb_send_test_email" id="eb_send_test_email" title="Send sample email with current selected template"/>
							<span class="load-response">
								<img src="http://localhost/wpeb/wp-content/plugins/edwiser-bridge/images/loader.gif" height="20" width="20">
							</span>
							<div class="response-box">
							</div>
						</div>
					</div>
				</div>
				<div class="eb-edit-email-template-aside">
					<div class="eb-email-templates-list">
						<h3>Email Templates</h3>
						<ul id="eb_email_templates_list">
							<?php
							foreach ( $tmplList as $tmplId => $tmplName ) {
								if ( !($tmplKey == $tmplId ) ) {
									echo "<li id='$tmplId' class='eb-emailtmpl-list-item'>$tmplName</li>";
								} else {
									echo "<li id='$tmplId' class='eb-emailtmpl-list-item eb-emailtmpl-active'>$tmplName</li>";
								}
							}
							?>					
						</ul>
					</div>
					<div class="eb-email-templates-const-wrap">
						<h3>Template Constants</h3>
						<div class="eb-emiltemp-const-wrap">
							<?php
							foreach ( $tmplConst as $const => $desc ) {
								echo '<div class="eb-mail-templat-const"><span>{' . $const . '}</span>' . $desc . '</div>';
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function getEditor( $content ) {
		$settings = array(
			'media_buttons' => false,
			'drag_drop_upload' => false,
			'textarea_rows' => 15,
		);
		wp_editor( $content, 'eb_emailtmpl_editor', $settings );
	}

	public function getTemplateDataAjaxCallBack() {

		$data = array();
		if ( isset( $_POST['tmpl_name'] ) ) {
			$tmplData = get_option( $_POST['tmpl_name'] );
			$data['from_email'] = $this->getFromEmail();
			$data['from_name'] = $this->getFromName();
			$data['subject'] = $tmplData['subject'];
			$data['content'] = $tmplData['content'];
		}
		echo json_encode( $data );
		die();
	}

	/**
	 * Getter methods start
	 */

	/**
	 * 
	 * @return string Returns from email address.
	 */
	private function getFromEmail() {
		$fromEmail = get_option( "eb_mail_from_email" );
		if ( $fromEmail == FALSE ) {
			$fromEmail = get_option( "admin_email" );
		}
		return $fromEmail;
	}

	private function getFromName() {
		$fromName = get_option( "eb_mail_from_name" );
		if ( $fromName == FALSE ) {
			$fromName = get_bloginfo( "name" );
		}
		return $fromName;
	}

	public function emailTemplateContsnt( $constants ) {
		$constants["USER_NAME"] = "The display name of the user.";
		$constants["FIRST_NAME"] = "The first name of the user.";
		$constants["LAST_NAME"] = "The last name of the user.";
		$constants["SITE_NAME"] = "The name of the website.";
		$constants["SITE_URL"] = "The URL of the website.";
		$constants["COURSE_TITLE"] = "The title of the course for the unit that's just been completed.";
		$constants["MOODLE_URL"] = "The moodle site url entered in the connection.";
		$constants["COURSES_PAGE_LINK"] = "The link to the courses archive page.";
		$constants["USER_PASSWORD"] = "The user accounts password this is valid only for the New User Account Details and Link WP user account to moodle tempaltes.";
		$constants["ORDER_ID"] = "The order id of the purchased order completed this is valid only for the Course order complet template.";
		$constants["WP_LOGIN_PAGE_LINK"] = "The wordpress login page link.";
		$constants["MOODLE_URL"] = "The moodle page url entered in the connection settings.";
		return $constants;
	}

	private function getEmailTemplate( $tmplName ) {
		return get_option( $tmplName );
	}

	/**
	 * Getter methods end
	 */

	/**
	 * Setter methods start
	 */
	private function setFromEmail( $email ) {
		update_option( "eb_mail_from_email", $email );
	}

	private function setFromName( $name ) {
		update_option( "eb_mail_from_name", $name );
	}

	private function setTemplateData( $tempalteName, $tempalteData ) {
		update_option( $tempalteName, $tempalteData );
	}

	private function save() {
		if ( isset( $_POST["eb_emailtmpl_nonce"] ) && wp_verify_nonce( $_POST["eb_emailtmpl_nonce"], "eb_emailtmpl_sec" ) ) {
			$fromEmail = $this->checkIsEmpty( $_POST, "eb_email_from" );
			$fromName = $this->checkIsEmpty( $_POST, "eb_email_from_name" );
			$subject = $this->checkIsEmpty( $_POST, "eb_email_subject" );
			$tmplContetn = $this->checkIsEmpty( $_POST, "eb_emailtmpl_editor" );
			$tmplName = $this->checkIsEmpty( $_POST, "eb_tmpl_name" );
			$tmplKey = $this->checkIsEmpty( $_POST, "eb_tmpl_name" );
			$data = array(
				"subject" => __( 'Your Learning Account Credentials', 'eb-textdomain' ),
				"content" => stripslashes( $tmplContetn ),
			);
			$this->setFromEmail( $fromEmail );
			$this->setFromName( $fromName );
			$this->setTemplateData( $tmplName, $data );
		}
	}

	private function checkIsEmpty( $dataArray, $key ) {
		if ( isset( $dataArray[$key] ) && !empty( $dataArray[$key] ) ) {
			return $dataArray[$key];
		} else {
			return false;
		}
	}

	/**
	 * Setter methods end
	 */
	public function sendTestEmail() {

		if ( isset( $_POST["security"] ) && wp_verify_nonce( $_POST["security"], "eb_send_testmail_sec" ) ) {
			$mailTo = $this->checkIsEmpty( $_POST, "mail_to" );
			$subject = $this->checkIsEmpty( $_POST, "subject" );
			$message = stripslashes($this->checkIsEmpty( $_POST, "message" ));
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			function wpse27856_set_content_type() {
				return "text/html";
			}

			add_filter( 'wp_mail_content_type',  function() {
				return "text/html";
			});
			$mail= wp_mail( $mailTo, $subject, $message, $headers );
			remove_filter( 'wp_mail_content_type',  function() {
				return "text/html";
			});
			if($mail){
				echo json_encode(array("success"=>"1"));
			}else{
				echo json_encode(array("success"=>"0","resp_msg"=>"failed"));
			}
			exit;
		}else{
			echo json_encode(array("success"=>"0","resp_msg"=>"Invalid request"));
		}
	}

}
