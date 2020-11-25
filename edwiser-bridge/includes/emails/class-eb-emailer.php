<?php

/**
 * This class defines all code necessary to send emails on course purchase.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class Eb_Emailer
{

	private $template_name;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The current version of this plugin.
	 */
	private $version;
	//template loader object
	private $plugin_template_loader;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		/**
		 * Class responsible for loading templates.
		 */
		require_once EB_PLUGIN_DIR . 'public/class-eb-template-loader.php';

		$this->plugin_template_loader = new EbTemplateLoader($this->plugin_name, $this->version);
	}

	/**
	 * runs on each email template to add email header and css styling.
	 *
	 * @param string $header email heading
	 */
	public function get_email_header($header)
	{
		$this->template_name = 'emails/email-header.php';
		echo $this->get_content_html(array('header' => $header), $this->plugin_template_loader);
	}

	/**
	 * runs on each email template to add email footer content.
	 */
	public function get_email_footer()
	{
		$this->template_name = 'emails/email-footer.php';
		echo $this->get_content_html('', $this->plugin_template_loader);
	}



    public function set_bcc_field_in_email_header($args, $email_option_key)
    {

error_log('set_bcc_field_in_email_header :: '.print_r($email_option_key, 1));

        $header = get_option($email_option_key."_bcc_email");
error_log('HEADER :: '.print_r($header, 1));

        $args["headers"] = "";
        if ($header) {
            $args["headers"] = "Bcc: ".$header;
        }

error_log('args :: '.print_r($args, 1));


        return $args;



        /*$args          = apply_filters("eb_args_data", $args);
        $emailTmplData = ExtendedEBAdminEmailTemplate::getEmailTmplContent("eb_emailtmpl_create_user");
        $allowNotify   = get_option("eb_emailtmpl_create_user_notify_allow");
        if ($allowNotify == false || $allowNotify != "ON") {
            return;
        }
        if ($emailTmplData) {
            $emailTmplObj = new ExtendedEBAdminEmailTemplate();
            return $emailTmplObj->sendEmail($args['user_email'], $args, $emailTmplData);
        }*/


        /**
         * Using Default
         */
        /*$this->template_name = 'emails/user-new-account.php';

        // prepare arguments array for email
        $args = apply_filters('eb_filter_email_parameters', $args, $this->template_name);

        $email_subject  = apply_filters('eb_new_user_email_subject', __('New User Account Details', 'eb-textdomain'));
        $args['header'] = $email_subject; // send email subject as header in email template
        $email_content  = $this->getContentHtml($args);
        $email_headers  = apply_filters('eb_email_headers', array('Content-Type: text/html; charset=UTF-8'));

        //send email
        $sent = $this->mailer($args['user_email'], $email_subject, $email_content, $email_headers);*/
    }



	public function send_course_access_expire_email($args)
	{
		$emailTmplData = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_course_access_expir");
		$allowNotify   = get_option("eb_emailtmpl_course_access_expir_notify_allow");
		if ($emailTmplData && $allowNotify == "ON") {
			$emailTmplObj = new EBAdminEmailTemplate();

			//CUSTOMIZATION HOOKS
            $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_course_access_expir");
            error_log('ARGS :: '.print_r($args, 1));


			return $emailTmplObj->send_email($args['user_email'], $args, $emailTmplData);
		}
	}

	public function send_existing_wp_user_new_moodle_account_email($args)
	{
		$emailTmplData = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_linked_existing_wp_new_moodle_user");
		$allowNotify   = get_option("eb_emailtmpl_linked_existing_wp_new_moodle_user_notify_allow");
		if ($emailTmplData && $allowNotify == "ON") {
			$emailTmplObj = new EBAdminEmailTemplate();

			//CUSTOMIZATION HOOKS
            $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_linked_existing_wp_new_moodle_user");
            error_log('ARGS :: '.print_r($args, 1));

			return $emailTmplObj->send_email($args['user_email'], $args, $emailTmplData);
		}
	}

	/**
	 * send succes refund email to user and admin.
	 * @return [type] [description]
	 */
	public function refund_completion_email($args)
	{

		$args               = apply_filters("eb_args_data", $args);
		$userEmailTmplData  = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_refund_completion_notifier_to_user");
		$adminEmailTmplData = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_refund_completion_notifier_to_admin");

		$emailTmplObj = new EBAdminEmailTemplate();

		$ebGeneral = get_option('eb_general');
		if ($ebGeneral) {
			$sendEmailToAdmin        = get_arr_value($ebGeneral, 'eb_refund_mail_to_admin', false);
			$specifiedEmailForRefund = get_arr_value($ebGeneral, 'eb_refund_mail', false);
		}

		$allowNotify = get_option("eb_emailtmpl_refund_completion_notifier_to_user_notify_allow");
		if ($allowNotify != false && $allowNotify == "ON") {
			if ($userEmailTmplData) {
				$user= get_user_by("id", get_arr_value($args, "buyer_id"), "");
				$args['first_name'] = $user->first_name;
				$args['last_name'] = $user->last_name;
				$args['username'] = $user->user_login;

				//CUSTOMIZATION HOOKS
                $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_refund_completion_notifier_to_user");
            error_log('ARGS :: '.print_r($args, 1));

				$emailTmplObj->send_email($user->user_email, $args, $userEmailTmplData);
			}
		}

		$allowNotify = get_option("eb_emailtmpl_refund_completion_notifier_to_admin_notify_allow");
		if ($allowNotify != false && $allowNotify == "ON") {
			if (isset($sendEmailToAdmin) && !empty($sendEmailToAdmin) &&  "yes" == $sendEmailToAdmin) {
				$userArgs = array(
					'role' => 'Administrator',
				);
				$result   = get_users($userArgs);

				foreach ($result as $value) {
					$adminUser = get_user_by("id", $value->data->ID, "");
					$args['first_name'] = $adminUser->first_name;
					$args['last_name'] = $adminUser->last_name;
					$args['username'] = $adminUser->user_login;
					
					//CUSTOMIZATION HOOKS
            		$args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_refund_completion_notifier_to_admin");
            error_log('ARGS :: '.print_r($args, 1));

					$emailTmplObj->send_email($value->data->user_email, $args, $adminEmailTmplData);
				}
			}

			if (isset($specifiedEmailForRefund) && !empty($specifiedEmailForRefund)) {

				 //CUSTOMIZATION HOOKS
            	$args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_refund_completion_notifier_to_admin");
            error_log('ARGS :: '.print_r($args, 1));

				$emailTmplObj->send_email($specifiedEmailForRefund, $args, $adminEmailTmplData);
			}
		}

		return 1;
	}



	/**
	 * Sends a new user registration email notification.
	 *
	 * called using 'eb_created_user' hook after user registration.
	 *
	 * @param array $args user details array
	 *
	 * @return bool
	 */
	public function send_new_user_email($args)
	{


error_log('send_new_user_email ::: ');

		/**
		 * Using Email template Editor
		 */
		$args          = apply_filters("eb_args_data", $args);
		$emailTmplData = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_create_user");
		$allowNotify   = get_option("eb_emailtmpl_create_user_notify_allow");
		if ($allowNotify == false || $allowNotify != "ON") {
			return;
		}
		if ($emailTmplData) {
			$emailTmplObj = new EBAdminEmailTemplate();
			//CUSTOMIZATION HOOKS
        	$args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_create_user");
            error_log('ARGS :: '.print_r($args, 1));

			return $emailTmplObj->send_email($args['user_email'], $args, $emailTmplData);
		}
		/**
		 * Using Default
		 */
		$this->template_name = 'emails/user-new-account.php';

		// prepare arguments array for email
		$args = apply_filters('eb_filter_email_parameters', $args, $this->template_name);

		$email_subject  = apply_filters('eb_new_user_email_subject', __('New User Account Details', 'eb-textdomain'));
		$args['header'] = $email_subject; // send email subject as header in email template
		$email_content  = $this->get_content_html($args);
		$email_headers  = apply_filters('eb_email_headers', array('Content-Type: text/html; charset=UTF-8'));


		//CUSTOMIZATION HOOKS
        $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_create_user");
            error_log('ARGS :: '.print_r($args, 1));

		//send email
		$sent = $this->mailer($args['user_email'], $email_subject, $email_content, $email_headers);

		return $sent;
	}

	/**
	 * Sends an email with moodle account credentials to existing wordpress users.
	 *
	 * called using 'eb_linked_moodle_to_existing_user' hook on user login.
	 * for users who already have a wordpress account.
	 *
	 * @param array $args user details array
	 *
	 * @return bool
	 */
	public function send_existing_user_moodle_account_email($args)
	{
		/**
		 * Using Email template Editor
		 */
		$emailTmplData = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_linked_existing_wp_user");

		$allowNotify = get_option("eb_emailtmpl_linked_existing_wp_user_notify_allow");

		if ($allowNotify == false || $allowNotify != "ON") {
			return;
		}
		if ($emailTmplData) {
			$emailTmplObj = new EBAdminEmailTemplate();
            $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_linked_existing_wp_user");
            error_log('ARGS :: '.print_r($args, 1));

			return $emailTmplObj->send_email($args['user_email'], $args, $emailTmplData);
		}
		/**
		 * Using Default
		 */
		$this->template_name          = 'emails/user-existing-wp-account.php';
		$this->plugin_template_loader = new EbTemplateLoader($this->plugin_name, $this->version);

		// prepare arguments array for email
		$args = apply_filters('eb_filter_email_parameters', $args, $this->template_name);

		$email_subject = apply_filters(
			'eb_existing_wp_user_email_subject',
			__('Your Learning Account Credentials', 'eb-textdomain')
		);
		$args['header'] = $email_subject; // send email subject as header in email template
		$email_content  = $this->get_content_html($args);
		$email_headers  = apply_filters('eb_email_headers', array('Content-Type: text/html; charset=UTF-8'));

		//Customization Hook
        $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_linked_existing_wp_user");
            error_log('ARGS :: '.print_r($args, 1));


		//send email
		$sent = $this->mailer($args['user_email'], $email_subject, $email_content, $email_headers);

		return $sent;
	}

	/**
	 * Sends an email on successful course purchase ( Order Completion )
	 * called using 'eb_order_status_completed' hook on order completion.
	 *
	 * @param array $args order id
	 *
	 * @return bool
	 */
	public function send_order_completion_email($order_id)
	{
		//global $wpdb;

		$order_detail = get_post_meta($order_id, 'eb_order_options', true); //get order details

		// return if there is a problem in order details
		if (!$this->check_order_details($order_detail)) {
			return;
		}

		$buyer_detail = get_userdata($order_detail['buyer_id']); //get buyer details
		$args         = array(); // arguments array for email

		$this->template_name          = 'emails/user-order-completion-email.php'; // template for order completion email
		$this->plugin_template_loader = new EbTemplateLoader(
			$this->plugin_name,
			$this->version
		); //template loader object

		// prepare arguments array for email
		$args = apply_filters(
			'eb_filter_email_parameters',
			array(
			// 'order_id' => $order_id,
			'eb_order_id' => $order_id, // changed 1.4.7
			'course_id' => $order_detail['course_id'],
			'user_email' => $buyer_detail->user_email,
			'username' => $buyer_detail->user_login,
			'first_name' => isset($buyer_detail->first_name) ? $buyer_detail->first_name : '',
			'last_name' => isset($buyer_detail->last_name) ? $buyer_detail->last_name : '',
				),
			$this->template_name
		);

		/**
		 * Using Email template Editor
		 */
		$emailTmplData = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_order_completed");

		$allowNotify = get_option("eb_emailtmpl_order_completed_notify_allow");
		if ($allowNotify == false || $allowNotify != "ON") {
			return;
		}
		if ($emailTmplData) {
			$emailTmplObj = new EBAdminEmailTemplate();
			//CUSTOMIZATION HOOKS
            $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_order_completed");
            error_log('ARGS :: '.print_r($args, 1));

			return $emailTmplObj->send_email($args['user_email'], $args, $emailTmplData);
		}

		/**
		 * Using Default
		 */
		$email_subject = apply_filters(
			'eb_order_completion_email_subject',
			__('Your order completed successfully.', 'eb-textdomain')
		);
		$args['header'] = $email_subject; // send email subject as header in email template
		$email_content = $this->get_content_html($args);
		$email_headers = apply_filters('eb_email_headers', array('Content-Type: text/html; charset=UTF-8'));

		//CUSTOMIZATION HOOKS
        $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_order_completed");
            error_log('ARGS :: '.print_r($args, 1));


		//send email
		$sent = $this->mailer($args['user_email'], $email_subject, $email_content, $email_headers);

		return $sent;
	}




/*********   Two way synch    **********/



	/**
	 * Sends email notification when Enrollment triggered on Moodle.
	 *
	 * called using 'eb_created_user' hook after user registration.
	 *
	 * @param array $args user details array
	 *
	 * @return bool
	 */
	public function send_mdl_triggered_enrollment_email($args)
	{
		/**
		 * Using Email template Editor
		 */

/// write appropriate filter name


error_log('debug_backtrace :: '.print_r(debug_backtrace(), 1));

		$args          = apply_filters("eb_args_data", $args);


		$emailTmplData = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_mdl_enrollment_trigger");
		$allowNotify   = get_option("eb_emailtmpl_mdl_enrollment_trigger_notify_allow");
		if ($allowNotify == false || $allowNotify != "ON") {
			return;
		}
		if ($emailTmplData) {
			$emailTmplObj = new EBAdminEmailTemplate();
			//CUSTOMIZATION HOOKS
            $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_mdl_enrollment_trigger");
            error_log('ARGS :: '.print_r($args, 1));

			return $emailTmplObj->send_email($args['user_email'], $args, $emailTmplData);
		}
		/**
		 * Using Default
		 */
		/*$this->template_name = 'emails/user-new-account.php';

		// prepare arguments array for email
		$args = apply_filters('eb_filter_email_parameters', $args, $this->template_name);

		$email_subject  = apply_filters('eb_new_user_email_subject', __('New User Account Details', 'eb-textdomain'));
		$args['header'] = $email_subject; // send email subject as header in email template
		$email_content  = $this->getContentHtml($args);
		$email_headers  = apply_filters('eb_email_headers', array('Content-Type: text/html; charset=UTF-8'));

		//send email
		$sent = $this->mailer($args['user_email'], $email_subject, $email_content, $email_headers);

		return $sent;*/
	}








	/**
	 * Sends email notification when Un Enrollment triggered on Moodle.
	 *
	 * called using 'eb_created_user' hook after user registration.
	 *
	 * @param array $args user details array
	 *
	 * @return bool
	 */
	public function send_mdl_triggered_unenrollment_email($args)
	{
		/**
		 * Using Email template Editor
		 */

/// write appropriate filter name


		$args          = apply_filters("eb_args_data", $args);
		$emailTmplData = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_mdl_un_enrollment_trigger");
		$allowNotify   = get_option("eb_emailtmpl_mdl_un_enrollment_trigger_notify_allow");
		if ($allowNotify == false || $allowNotify != "ON") {
			return;
		}
		if ($emailTmplData) {
			$emailTmplObj = new EBAdminEmailTemplate();
			//CUSTOMIZATION HOOKS
            $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_mdl_un_enrollment_trigger");
            error_log('ARGS :: '.print_r($args, 1));

			return $emailTmplObj->send_email($args['user_email'], $args, $emailTmplData);
		}
		/**
		 * Using Default
		 */
		/*$this->template_name = 'emails/user-new-account.php';

		// prepare arguments array for email
		$args = apply_filters('eb_filter_email_parameters', $args, $this->template_name);

		$email_subject  = apply_filters('eb_new_user_email_subject', __('New User Account Details', 'eb-textdomain'));
		$args['header'] = $email_subject; // send email subject as header in email template
		$email_content  = $this->getContentHtml($args);
		$email_headers  = apply_filters('eb_email_headers', array('Content-Type: text/html; charset=UTF-8'));

		//send email
		$sent = $this->mailer($args['user_email'], $email_subject, $email_content, $email_headers);

		return $sent;*/
	}








	/**
	 * Sends email notification when User Deletion triggered on Moodle.
	 *
	 * called using 'eb_created_user' hook after user registration.
	 *
	 * @param array $args user details array
	 *
	 * @return bool
	 */
	public function send_mdl_triggered_user_deletion_email($args)
	{
		/**
		 * Using Email template Editor
		 */

/// write appropriate filter name



		$args          = apply_filters("eb_args_data", $args);
		$emailTmplData = EBAdminEmailTemplate::get_email_tmpl_content("eb_emailtmpl_mdl_user_deletion_trigger");
		$allowNotify   = get_option("eb_emailtmpl_mdl_user_deletion_trigger_notify_allow");
		if ($allowNotify == false || $allowNotify != "ON") {
			return;
		}
		if ($emailTmplData) {
			$emailTmplObj = new EBAdminEmailTemplate();
			//CUSTOMIZATION HOOKS
            $args = apply_filters("eb_email_custom_args", $args, "eb_emailtmpl_mdl_user_deletion_trigger");
            error_log('ARGS :: '.print_r($args, 1));

			return $emailTmplObj->send_email($args['user_email'], $args, $emailTmplData);
		}
		/**
		 * Using Default
		 */
		/*$this->template_name = 'emails/user-new-account.php';

		// prepare arguments array for email
		$args = apply_filters('eb_filter_email_parameters', $args, $this->template_name);

		$email_subject  = apply_filters('eb_new_user_email_subject', __('New User Account Details', 'eb-textdomain'));
		$args['header'] = $email_subject; // send email subject as header in email template
		$email_content  = $this->getContentHtml($args);
		$email_headers  = apply_filters('eb_email_headers', array('Content-Type: text/html; charset=UTF-8'));

		//send email
		$sent = $this->mailer($args['user_email'], $email_subject, $email_content, $email_headers);

		return $sent;*/
	}









/***********************************/


	private function check_order_details($order_detail)
	{
		if (!isset($order_detail['order_status']) || !isset($order_detail['buyer_id']) || !isset($order_detail['course_id'])) {
			return false;
		}
		return true;
	}

	// custom mailer
	public function mailer($_to, $email_subject, $email_content, $email_headers = '')
	{

		// inject CSS rules for text and image alignment
		$email_css     = $this->mailer_css();
		$email_content = $email_css . $email_content;

		$sent = wp_mail($_to, $email_subject, $email_content, $email_headers);

		return $sent;
	}

	// custom css to be added in emails
	public function mailer_css()
	{
		$css = '<style type="text/css"> .alignleft {float: left;margin: 5px 20px 5px 0;}
			.alignright {float: right;margin: 5px 0 5px 20px;}
			.aligncenter {display: block;margin: 5px auto;}img.alignnone {margin: 5px 0;}
			blockquote,q {quotes: none;}blockquote:before,blockquote:after,q:before,
			q:after {content: "";content: none;} blockquote {font-size: 24px;font-style:
				italic;font-weight: 300;margin: 24px 40px;}
			blockquote blockquote {margin-right: 0;}blockquote cite,blockquote
			 small {font-size: 14px;font-weight: normal;text-transform: uppercase;}' .
				'cite {border-bottom: 0;}abbr[title] {border-bottom: 1px dotted;}
			address {font-style: italic;margin: 0 0 24px;}' .
				'del {color: #333;}ins {background: #fff9c0;border: none;color: #333;text-decoration: none;}' .
				'sub,sup {font-size: 75%;line-height: 0;position: relative;vertical-align: baseline;}' .
				'sup {top: -0.5em;}sub {bottom: -0.25em;}</style>';

		return $css;
	}

	/**
	 * getContentHtml function
	 * returns the template content.
	 *
	 * @return string
	 */
	public function get_content_html($args)
	{
		ob_start();
		$this->plugin_template_loader->wp_get_template($this->template_name, $args);

		return ob_get_clean();
	}
}
