<?php
namespace app\wisdmlabs\edwiserBridge;

if (!class_exists("Eb_Default_Email_Template")) {

	class Eb_Default_Email_Template
	{

		/**
		 * Preapares the default new user account creation on moodle and WP email
		 * notification tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function new_user_acoount($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('New User Account Details', 'eb-textdomain'),
				"content" => $this->get_new_user_account_template(),
			);
			return $data;
		}

		/**
		 * Preapares the default link moodle account email notification
		 * tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function link_wp_moodle_account($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('Your learning account is linked with moodle', 'eb-textdomain'),
				"content" => $this->get_link_wp_moodle_account_template(),
			);
			return $data;
		}

		/**
		 * Preapares the default new moolde account creation email notification
		 * tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function link_new_moodle_account($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('Your Learning Account Credentials', 'eb-textdomain'),
				"content" => $this->get_link_new_moodle_account_template(),
			);
			return $data;
		}

		/**
		 * Preapares the default new course order creation email notification
		 * tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function order_complete($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('Your order completed successfully.', 'eb-textdomain'),
				"content" => $this->get_order_complete_template(),
			);
			return $data;
		}

		/**
		 * Preapares the default course access expire email notification
		 * email tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function course_access_expired($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('Course access expired.', 'eb-textdomain'),
				"content" => $this->get_course_access_expired_template(),
			);
			return $data;
		}

		/**
		 * Preapares the default refund completion email for the user who placed this order
		 * notification tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function notify_user_on_order_refund($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('Order refund notification', 'eb-textdomain'),
				"content" => $this->user_refunded_notification_template(),
			);
			return $data;
		}

		/**
		 * Preapares the default refund completion email for all the admins
		 * notification tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function notify_admin_on_order_refund($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('Order refund notification', 'eb-textdomain'),
				"content" => $this->admin_refunded_notification_template(),
			);
			return $data;
		}


		/******  two way synch emails   ***********/
		/**
		 * Preapares the default refund completion email for all the admins
		 * notification tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function moodle_enrollment_trigger($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('Moodle Course Enrollment', 'eb-textdomain'),
				"content" => $this->moodle_enrollment_trigger_template(),
			);
			return $data;
		}


		/**
		 * Preapares the default refund completion email for all the admins
		 * notification tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function moodle_unenrollment_trigger($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('Moodle Course Un-Enrollment', 'eb-textdomain'),
				"content" => $this->moodle_unenrollment_trigger_template(),
			);
			return $data;
		}


		/**
		 * Preapares the default refund completion email for all the admins
		 * notification tempalte and subject
		 * @param type $tmplId temaplte optoin key for the new user template
		 * @param type $restore boolean value to restore the email temaplte or not
		 * @return array returns the array of the email tempalte content and subject
		 */
		public function user_deletion_trigger($tmplId, $restore = false)
		{
			$data = get_option($tmplId);
			if ($data && !$restore) {
				return $data;
			}
			$data = array(
				"subject" => __('User Account Deleted', 'eb-textdomain'),
				"content" => $this->moodle_user_deletion_trigger_template(),
			);
			return $data;
		}
/**********************/



		/**
		 * Prepares the html template with constants for the new WP and moodle user account creation
		 * @return html returns the email template body content for the new user
		 * acount creation on moodle and WP
		 */
		private function get_new_user_account_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;"><?php _e('Your Learning Account Credentials', 'eb-textdomain'); ?></h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
									printf(
										__('Hi %s', 'eb-textdomain'),
										'{FIRST_NAME}'
									);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Thanks for creating an account on %s. Your username is %s.', 'eb-textdomain'),
											'{SITE_NAME}',
											'<strong>{USER_NAME}</strong>'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Your password has been automatically generated: %s.', 'eb-textdomain'),
											'<strong>{USER_PASSWORD}</strong>'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('You can access your account here: %s.', 'eb-textdomain'),
											'<span style="color: #0000ff;">{USER_ACCOUNT_PAGE_LINK}</span>'
										);
									?>
								</div></td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean(). "</div>";
		}

		/**
		 * Prepares the html template with constants for the new moodle user account creation
		 * @return html returns the email template body content for the new user
		 * acount creation on moodle.
		 */
		private function get_link_new_moodle_account_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
									<?php _e('Your Learning Account Credentials', 'eb-textdomain'); ?>
								</h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Hi %s', 'eb-textdomain'),
											'{FIRST_NAME}'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										_e('A learning account is linked to your profile.Use credentials given below while accessing your courses.', 'eb-textdomain');
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Username: %s', 'eb-textdomain'),
											'<strong>{USER_NAME}</strong>'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
									printf(
										__('Password: %s', 'eb-textdomain'),
										'<strong>{USER_PASSWORD} </strong>'
									);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('You can purchase &amp; access courses here: %s.', 'eb-textdomain'),
											'<span style="color: #0000ff;">{COURSES_PAGE_LINK}</span>'
										);
									?>
								</div></td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean() . "</div>";
		}

		/**
		 * Prepares the html template with constants for the linking moodle user account with WP
		 * @return html returns the email template body content for the linking user
		 * acount to moodle
		 */
		private function get_link_wp_moodle_account_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
									<?php _e('Your learning account is linked with moodle', 'eb-textdomain'); ?>
								</h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Hi %s', 'eb-textdomain'),
											'{FIRST_NAME}'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										_e('A learning account is linked to your moodle profile.', 'eb-textdomain');
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('You can purchase &amp; access courses here: %s.', 'eb-textdomain'),
											'<span style="color: #0000ff;">{COURSES_PAGE_LINK}</span>'
										);
									?>
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean() . "</div>";
		}

		/**
		 * Prepares the html template with constants for the new course order
		 * creation
		 * @return html returns the email template body content for the new
		 * course order creation
		 */
		private function get_order_complete_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
									<?php _e('Your order completed successfully.', 'eb-textdomain'); ?>
								</h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Hi %s', 'eb-textdomain'),
											'{FIRST_NAME}'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Thanks for purchasing %s course.', 'eb-textdomain'),
											'<strong>{COURSE_NAME}</strong>'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Your order with ID %s completed successfully.', 'eb-textdomain'),
											'<strong>{ORDER_ID}</strong>'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('You can access your account here: %s.', 'eb-textdomain'),
											'<span style="color: #0000ff;">{USER_ACCOUNT_PAGE_LINK}</span>'
										);
									?>
								</div></td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean() . "</div>";
		}

		/**
		 * Prepares the html template with constants for the course access expire
		 * creation
		 * @return html returns the email template body content for the course
		 * access expire
		 */
		private function get_course_access_expired_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
									<?php
										printf(
											__('Your %s course access is expired.', 'eb-textdomain'),
											'{COURSE_NAME}'
										);
									?>
								</h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Hi %s', 'eb-textdomain'),
											'{FIRST_NAME}'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Your Subscription for %s course has expired.', 'eb-textdomain'),
											'{COURSE_NAME}'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Please purchase the course again to continue with it. %s to purchase now!', 'eb-textdomain'),
											'{WP_COURSE_PAGE_LINK}'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php _e('Thank you!', 'eb-textdomain'); ?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div></td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean() . "</div>";
		}


		/**
		 * User refund initiated notification email
		 * @return [type] [description]
		 */
		public function user_refunded_notification_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; padding: 70px 70px 70px 70px; margin: auto; height: auto;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 1px 2px 0px 1px #d0d0d0; border-radius: 6px !important; background-color: #dfdfdf; margin: auto;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-radius: 6px 6px 0px 0px; border-bottom: 0; font-family: Arial;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
									<?php
									printf(__("Your order %s has been successfully refunded.", "eb-textdomain"), "{ORDER_ID}");
									?>
								</h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php printf(__("Hello %s %s,", "eb-textdomain"), "{FIRST_NAME}", "{LAST_NAME}"); ?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
									printf(
										__(
											"This is to inform you that, The amount %s has been refunded successfully, against the order %s by {SITE_NAME}.",
											"eb-textdomain"
										),
										"{CURRENT_REFUNDED_AMOUNT}",
										"{ORDER_ID}",
										"{SITE_NAME}"
									);
									?>
								</div>
								<div></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php printf(__("Order %s Details:", "eb-textdomain"), "{ORDER_ID}"); ?>
								</div>
								<div></div>
								<div style="font-family: Arial;">
									<table style="border-collapse: collapse;">
										<tbody>
											<tr style="border: 1px solid #465b94; padding: 5px;">
												<td style="border: 1px solid #465b94; padding: 5px;">
													<?php _e("Order Item", "eb-textdomain"); ?>
												</td>
												<td style="border: 1px solid #465b94; padding: 5px;">
													{ORDER_ITEM}
												</td>
											</tr>
											<tr style="border: 1px solid #465b94; padding: 5px;">
												<td style="border: 1px solid #465b94; padding: 5px;">
													<?php _e("Total Amount Paid", "eb-textdomain"); ?>
												</td>
												<td style="border: 1px solid #465b94; padding: 5px;">
													{TOTAL_AMOUNT_PAID}
												</td>
											</tr>
											<tr style="border: 1px solid #465b94; padding: 5px;">
												<td style="border: 1px solid #465b94; padding: 5px;">
													<?php _e("Current Refunded Amount", "eb-textdomain"); ?>
												</td>
												<td style="border: 1px solid #465b94; padding: 5px;">
													{CURRENT_REFUNDED_AMOUNT}
												</td>
											</tr>
											<tr style="border: 1px solid #465b94; padding: 5px;">
												<td style="border: 1px solid #465b94; padding: 5px;">
													<?php _e("Total Refunded Amount", "eb-textdomain"); ?>
												</td>
												<td style="border: 1px solid #465b94; padding: 5px;">
													{TOTAL_REFUNDED_AMOUNT}
												</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
							</td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean() . "</div>";
		}


		/**
		 * Notification dend to admin on refund initiation.
		 * @return [type] [description]
		 */
		public function admin_refunded_notification_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; padding: 70px 70px 70px 70px; margin: auto; height: auto;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 1px 2px 0px 1px #d0d0d0; border-radius: 6px !important; background-color: #dfdfdf; margin: auto;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-radius: 6px 6px 0px 0px; border-bottom: 0; font-family: Arial;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
								<?php printf(__("Refund notification for the order id: %s.", "eb-textdomain"), "{ORDER_ID}"); ?>
								</h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									Hello,</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
								<?php printf(__("This is to inform you that, Refund for the order id %s has been %s.", "eb-textdomain"), "{ORDER_ID}", "{ORDER_REFUND_STATUS}"); ?>
									.
								</div>
								<div></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
								<?php printf(__("Order %s Details:", "eb-textdomain"), "{ORDER_ID}"); ?>
								</div>
								<div></div>
								<div style="font-family: Arial;">
									<table style="border-collapse: collapse;">
										<tbody>
											<tr style="border: 1px solid #465b94; padding: 5px;">
												<td style="border: 1px solid #465b94; padding: 5px;"> <?php _e("Customer Details", "eb-textdomain") ?></td>
												<td style="border: 1px solid #465b94; padding: 5px;">{CUSTOMER_DETAILS}</td>
											</tr>
											<tr style="border: 1px solid #465b94; padding: 5px;">
												<td style="border: 1px solid #465b94; padding: 5px;"> <?php _e("Order Item", "eb-textdomain") ?></td>
												<td style="border: 1px solid #465b94; padding: 5px;">{ORDER_ITEM}</td>
											</tr>
											<tr style="border: 1px solid #465b94; padding: 5px;">
												<td style="border: 1px solid #465b94; padding: 5px;"> <?php _e("Total paid amount", "eb-textdomain") ?></td>
												<td style="border: 1px solid #465b94; padding: 5px;">{TOTAL_AMOUNT_PAID}</td>
											</tr>
											<tr style="border: 1px solid #465b94; padding: 5px;">
												<td style="border: 1px solid #465b94; padding: 5px;"> <?php _e("Current Refunded Amount", "eb-textdomain") ?></td>
												<td style="border: 1px solid #465b94; padding: 5px;">{CURRENT_REFUNDED_AMOUNT}</td>
											</tr>
											<tr style="border: 1px solid #465b94; padding: 5px;">
												<td style="border: 1px solid #465b94; padding: 5px;"> <?php _e("Total Refunded Amount", "eb-textdomain") ?></td>
												<td style="border: 1px solid #465b94; padding: 5px;">{TOTAL_REFUNDED_AMOUNT}</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div></td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean() . "</div>";
		}


		/**
		 *Send enrollment email on  enrollment request from Moodle
		 * @return [type] [description]
		 */
		public function moodle_enrollment_trigger_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
									<?php _e('Course Enrollment.', 'eb-textdomain'); ?>
								</h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Hi %s', 'eb-textdomain'),
											'{FIRST_NAME}'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('You are successfully enrolled in %s course.', 'eb-textdomain'),
											'<strong>{COURSE_NAME}</strong>'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>

								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('You can access your account here: %s.', 'eb-textdomain'),
											'<span style="color: #0000ff;">{USER_ACCOUNT_PAGE_LINK}</span>'
										);
									?>
								</div></td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean() . "</div>";
		}


		/**
		 *Send Unenrollment email on  unenrollment request from Moodle
		 * @return [type] [description]
		 */
		public function moodle_unenrollment_trigger_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
									<?php _e('Course Un-Enrollment.', 'eb-textdomain'); ?>
								</h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Hi %s', 'eb-textdomain'),
											'{FIRST_NAME}'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('You are un-enrolled from %s course.', 'eb-textdomain'),
											'<strong>{COURSE_NAME}</strong>'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>

								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('You can access your account here: %s.', 'eb-textdomain'),
											'<span style="color: #0000ff;">{USER_ACCOUNT_PAGE_LINK}</span>'
										);
									?>
								</div></td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean() . "</div>";
		}



		/**
		 *Send User deletion email on  user deletion request from Moodle
		 * @return [type] [description]
		 */
		public function moodle_user_deletion_trigger_template()
		{
			ob_start();
			?>
			<div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
				<table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
								<h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
									<?php _e('User Deleted', 'eb-textdomain'); ?>
								</h1>
							</td>
						</tr>
						<tr>
							<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Hi %s', 'eb-textdomain'),
											'{FIRST_NAME}'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
									<?php
										printf(
											__('Your user account is deleted from %s.', 'eb-textdomain'),
											'<strong>{SITE_URL}</strong>'
										);
									?>
								</div>
								<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>

							</td>
						</tr>
						<tr>
							<td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			return "<div>" . ob_get_clean() . "</div>";
		}
	}
}
