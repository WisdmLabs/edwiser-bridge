<?php
/**
 * This class defines all code necessary to send emails on course purchase.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Eb Mailer.
 */
class Eb_Emailer {
	/**
	 * Template name.
	 *
	 * @since    1.0.0
	 *
	 * @var string template_name.
	 */
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

	/**
	 * Template loader object.
	 *
	 * @since    1.0.0
	 *
	 * @var string template loader object.
	 */
	private $plugin_template_loader;

	/**
	 * Constructor.
	 *
	 * @param text $plugin_name plugin name.
	 * @param text $version Version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$plugin_path       = plugin_dir_path( __DIR__ );

		/**
		 * Class responsible for loading templates.
		 */
		require_once $plugin_path . '../public/class-eb-template-loader.php';

		$this->plugin_template_loader = new Eb_Template_Loader( $this->plugin_name, $this->version );
	}

	/**
	 * Runs on each email template to add email header and css styling.
	 *
	 * @param string $header email heading.
	 */
	public function get_email_header( $header ) {
		$this->template_name = 'emails/email-header.php';
		echo esc_html( $this->get_content_html( array( 'header' => $header ), $this->plugin_template_loader ) );
	}

	/**
	 * Runs on each email template to add email footer content.
	 */
	public function get_email_footer() {
		$this->template_name = 'emails/email-footer.php';
		echo esc_html( $this->get_content_html( '', $this->plugin_template_loader ) );
	}


	/**
	 * Bcc field.
	 *
	 * @param string $args args.
	 * @param string $email_option_key email option key.
	 */
	public function set_bcc_field_in_email_header( $args, $email_option_key ) {
		$header = get_option( $email_option_key . '_bcc_email' );

		$args['headers'] = '';
		if ( $header ) {
			$args['headers'] = 'Bcc: ' . $header;
		}

		return $args;
	}


	/**
	 * Send_course_access_expire_email.
	 *
	 * @param text $args Args.
	 */
	public function send_course_access_expire_email( $args ) {
		$email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_course_access_expir' );
		$allow_notify    = get_option( 'eb_emailtmpl_course_access_expir_notify_allow' );
		if ( $email_tmpl_data && 'ON' === $allow_notify ) {
			$email_tmpl_obj = new EB_Email_Template();

			// CUSTOMIZATION HOOKS.
			$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_course_access_expir' );

			return $email_tmpl_obj->send_email( $args['user_email'], $args, $email_tmpl_data );
		}
	}

	/**
	 * Send existing mail.
	 *
	 * @param text $args Args.
	 */
	public function send_existing_wp_user_new_moodle_account_email( $args ) {
		$email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_linked_existing_wp_new_moodle_user' );
		$allow_notify    = get_option( 'eb_emailtmpl_linked_existing_wp_new_moodle_user_notify_allow' );
		if ( $email_tmpl_data && 'ON' === $allow_notify ) {
			$email_tmpl_obj = new EB_Email_Template();

			// CUSTOMIZATION HOOKS.
			$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_linked_existing_wp_new_moodle_user' );

			return $email_tmpl_obj->send_email( $args['user_email'], $args, $email_tmpl_data );
		}
	}

	/**
	 * Send succes refund email to user and admin.
	 *
	 * @param text $args Args.
	 * @return [type] [description]
	 */
	public function refund_completion_email( $args ) {

		$args                  = apply_filters( 'eb_args_data', $args );
		$user_email_tmpl_data  = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_refund_completion_notifier_to_user' );
		$admin_email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_refund_completion_notifier_to_admin' );

		$email_tmpl_obj = new EB_Email_Template();

		$eb_general = get_option( 'eb_general' );
		if ( $eb_general ) {
			$send_email_to_admin        = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $eb_general, 'eb_refund_mail_to_admin', false );
			$specified_email_for_refund = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $eb_general, 'eb_refund_mail', false );
		}

		$allow_notify = get_option( 'eb_emailtmpl_refund_completion_notifier_to_user_notify_allow' );
		if ( false !== $allow_notify && 'ON' === $allow_notify && $user_email_tmpl_data ) {
			$user               = get_user_by( 'id', \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $args, 'buyer_id' ), '' );
			$args['first_name'] = $user->first_name;
			$args['last_name']  = $user->last_name;
			$args['username']   = $user->user_login;

			// CUSTOMIZATION HOOKS.
			$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_refund_completion_notifier_to_user' );

			$email_tmpl_obj->send_email( $user->user_email, $args, $user_email_tmpl_data );
		}

		$allow_notify = get_option( 'eb_emailtmpl_refund_completion_notifier_to_admin_notify_allow' );
		if ( false !== $allow_notify && 'ON' === $allow_notify ) {
			if ( isset( $send_email_to_admin ) && ! empty( $send_email_to_admin ) && 'yes' === $send_email_to_admin ) {
				$user_args = array(
					'role' => 'Administrator',
				);
				$result    = get_users( $user_args );

				foreach ( $result as $value ) {
					$admin_user         = get_user_by( 'id', $value->data->ID, '' );
					$args['first_name'] = $admin_user->first_name;
					$args['last_name']  = $admin_user->last_name;
					$args['username']   = $admin_user->user_login;

					// CUSTOMIZATION HOOKS.
					$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_refund_completion_notifier_to_admin' );

					$email_tmpl_obj->send_email( $value->data->user_email, $args, $admin_email_tmpl_data );
				}
			}

			if ( isset( $specified_email_for_refund ) && ! empty( $specified_email_for_refund ) ) {

				// CUSTOMIZATION HOOKS.
				$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_refund_completion_notifier_to_admin' );

				$email_tmpl_obj->send_email( $specified_email_for_refund, $args, $admin_email_tmpl_data );
			}
		}

		return 1;
	}



	/**
	 * Sends a new user registration email notification.
	 *
	 * Called using 'eb_created_user' hook after user registration.
	 *
	 * @param array $args user details array.
	 *
	 * @return bool
	 */
	public function send_new_user_email( $args ) {

		/**
		 * Using Email template Editor
		 */
		$args            = apply_filters( 'eb_args_data', $args );
		$email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_create_user' );
		$allow_notify    = get_option( 'eb_emailtmpl_create_user_notify_allow' );
		if ( false === $allow_notify || 'ON' !== $allow_notify ) {
			return false;
		}
		if ( $email_tmpl_data ) {
			$email_tmpl_obj = new EB_Email_Template();
			// CUSTOMIZATION HOOKS.
			$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_create_user' );

			return $email_tmpl_obj->send_email( $args['user_email'], $args, $email_tmpl_data );
		}
		/**
		 * Using Default
		 */
		$this->template_name = 'emails/user-new-account.php';

		// Prepare arguments array for email.
		$args           = apply_filters( 'eb_filter_email_parameters', $args, $this->template_name );
		$email_subject  = apply_filters( 'eb_new_user_email_subject', esc_html__( 'New User Account Details', 'edwiser-bridge' ) );
		$args['header'] = $email_subject; // send email subject as header in email template.
		$email_content  = $this->get_content_html( $args );
		$email_headers  = apply_filters( 'eb_email_headers', array( 'Content-Type: text/html; charset=UTF-8' ) );

		// CUSTOMIZATION HOOKS.
		$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_create_user' );
		// send email.
		$sent = $this->mailer( $args['user_email'], $email_subject, $email_content, $email_headers );

		return $sent;
	}

	/**
	 * Sends an email with moodle account credentials to existing WordPress users.
	 *
	 * Called using 'eb_linked_moodle_to_existing_user' hook on user login.
	 * for users who already have a WordPress account.
	 *
	 * @param array $args user details array.
	 *
	 * @return bool
	 */
	public function send_existing_user_moodle_account_email( $args ) {
		/**
		 * Using Email template Editor
		 */
		$email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_linked_existing_wp_user' );

		$allow_notify = get_option( 'eb_emailtmpl_linked_existing_wp_user_notify_allow' );

		if ( false === $allow_notify || 'ON' !== $allow_notify ) {
			return false;
		}
		if ( $email_tmpl_data ) {
			$email_tmpl_obj = new EB_Email_Template();
			$args           = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_linked_existing_wp_user' );

			return $email_tmpl_obj->send_email( $args['user_email'], $args, $email_tmpl_data );
		}
		/**
		 * Using Default
		 */
		$this->template_name          = 'emails/user-existing-wp-account.php';
		$this->plugin_template_loader = new Eb_Template_Loader( $this->plugin_name, $this->version );

		// prepare arguments array for email.
		$args = apply_filters( 'eb_filter_email_parameters', $args, $this->template_name );

		$email_subject  = apply_filters(
			'eb_existing_wp_user_email_subject',
			esc_html__( 'Your Learning Account Credentials', 'edwiser-bridge' )
		);
		$args['header'] = $email_subject; // send email subject as header in email template.
		$email_content  = $this->get_content_html( $args );
		$email_headers  = apply_filters( 'eb_email_headers', array( 'Content-Type: text/html; charset=UTF-8' ) );

		// Customization Hook.
		$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_linked_existing_wp_user' );

		// send email.
		$sent = $this->mailer( $args['user_email'], $email_subject, $email_content, $email_headers );

		return $sent;
	}

	/**
	 * Sends an email on successful course purchase ( Order Completion )
	 * called using 'eb_order_status_completed' hook on order completion.
	 *
	 * @param array $order_id order id.
	 *
	 * @return bool
	 */
	public function send_order_completion_email( $order_id ) {
		$order_detail = get_post_meta( $order_id, 'eb_order_options', true ); // get order details.
		$is_mailed    = false;

		// return if there is a problem in order details.
		if ( $this->check_order_details( $order_detail ) ) {

			$buyer_detail = get_userdata( $order_detail['buyer_id'] ); // get buyer details.
			$args         = array(); // arguments array for email.

			$this->template_name          = 'emails/user-order-completion-email.php'; // template for order completion email.
			$this->plugin_template_loader = new Eb_Template_Loader(
				$this->plugin_name,
				$this->version
			); // template loader object.

			// prepare arguments array for email.
			$args = apply_filters(
				'eb_filter_email_parameters',
				array(
					'eb_order_id' => $order_id, // changed 1.4.7.
					'course_id'   => $order_detail['course_id'],
					'user_email'  => $buyer_detail->user_email,
					'username'    => $buyer_detail->user_login,
					'first_name'  => isset( $buyer_detail->first_name ) ? $buyer_detail->first_name : '',
					'last_name'   => isset( $buyer_detail->last_name ) ? $buyer_detail->last_name : '',
				),
				$this->template_name
			);

			/**
			 * Using Email template Editor
			 */
			$email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_order_completed' );

			$allow_notify = get_option( 'eb_emailtmpl_order_completed_notify_allow' );
			if ( true === $allow_notify || 'ON' === $allow_notify ) {
				if ( $email_tmpl_data ) {
					$email_tmpl_obj = new EB_Email_Template();
					// CUSTOMIZATION HOOKS.
					$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_order_completed' );

					$is_mailed = $email_tmpl_obj->send_email( $args['user_email'], $args, $email_tmpl_data );
				} else {
					/**
					 * Using Default
					 */
					$email_subject  = apply_filters(
						'eb_order_completion_email_subject',
						esc_html__( 'Your order completed successfully.', 'edwiser-bridge' )
					);
					$args['header'] = $email_subject; // send email subject as header in email template.
					$email_content  = $this->get_content_html( $args );
					$email_headers  = apply_filters( 'eb_email_headers', array( 'Content-Type: text/html; charset=UTF-8' ) );

					// CUSTOMIZATION HOOKS.
					$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_order_completed' );

					// send email.
					$is_mailed = $this->mailer( $args['user_email'], $email_subject, $email_content, $email_headers );
				}
			}
		}
		return $is_mailed;
	}


	/**
	 * Sends email notification when Enrollment triggered on Moodle.
	 *
	 * Called using 'eb_created_user' hook after user registration.
	 *
	 * @param array $args user details array.
	 *
	 * @return bool
	 */
	public function send_mdl_triggered_enrollment_email( $args ) {
		/**
		 * Using Email template Editor
		 */

		$args            = apply_filters( 'eb_args_data', $args );
		$email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_mdl_enrollment_trigger' );
		$allow_notify    = get_option( 'eb_emailtmpl_mdl_enrollment_trigger_notify_allow' );
		if ( false === $allow_notify || 'ON' !== $allow_notify ) {
			return false;
		}

		if ( $email_tmpl_data ) {
			$email_tmpl_obj = new EB_Email_Template();
			// CUSTOMIZATION HOOKS.
			$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_mdl_enrollment_trigger' );

			return $email_tmpl_obj->send_email( $args['user_email'], $args, $email_tmpl_data );
		}
		/**
		 * Using Default
		 */
	}








	/**
	 * Sends email notification when Un Enrollment triggered on Moodle.
	 *
	 * Called using 'eb_created_user' hook after user registration.
	 *
	 * @param array $args user details array.
	 *
	 * @return bool
	 */
	public function send_mdl_triggered_unenrollment_email( $args ) {
		/**
		 * Using Email template Editor
		 */

		$args            = apply_filters( 'eb_args_data', $args );
		$email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_mdl_un_enrollment_trigger' );
		$allow_notify    = get_option( 'eb_emailtmpl_mdl_un_enrollment_trigger_notify_allow' );
		if ( false === $allow_notify || 'ON' !== $allow_notify ) {
			return false;
		}
		if ( $email_tmpl_data ) {
			$email_tmpl_obj = new EB_Email_Template();
			// CUSTOMIZATION HOOKS.
			$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_mdl_un_enrollment_trigger' );

			return $email_tmpl_obj->send_email( $args['user_email'], $args, $email_tmpl_data );
		}

		/**
		 * Using Default
		 */
	}








	/**
	 * Sends email notification when User Deletion triggered on Moodle.
	 *
	 * Called using 'eb_created_user' hook after user registration.
	 *
	 * @param array $args user details array.
	 *
	 * @return bool
	 */
	public function send_mdl_triggered_user_deletion_email( $args ) {
		/**
		 * Using Email template Editor
		 */

		$args            = apply_filters( 'eb_args_data', $args );
		$email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_mdl_user_deletion_trigger' );
		$allow_notify    = get_option( 'eb_emailtmpl_mdl_user_deletion_trigger_notify_allow' );
		if ( false === $allow_notify || 'ON' !== $allow_notify ) {
			return false;
		}
		if ( $email_tmpl_data ) {
			$email_tmpl_obj = new EB_Email_Template();
			// CUSTOMIZATION HOOKS.
			$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_mdl_user_deletion_trigger' );

			return $email_tmpl_obj->send_email( $args['user_email'], $args, $email_tmpl_data );
		}
		/**
		 * Using Default
		 */
	}

	/**
	 * Sends email verification email to new user.
	 *
	 * @param array $args user details array.
	 *
	 * @return bool
	 */
	public function send_new_user_email_verification_email( $args ) {
		/**
		 * Using Email template Editor
		 */

		$args            = apply_filters( 'eb_args_data', $args );
		$email_tmpl_data = EB_Email_Template::get_email_tmpl_content( 'eb_emailtmpl_new_user_email_verification' );
		$allow_notify    = get_option( 'eb_emailtmpl_new_user_email_verification_notify_allow' );
		if ( false === $allow_notify || 'ON' !== $allow_notify ) {
			return false;
		}
		if ( $email_tmpl_data ) {
			$email_tmpl_obj = new EB_Email_Template();
			// CUSTOMIZATION HOOKS.
			$args = apply_filters( 'eb_email_custom_args', $args, 'eb_emailtmpl_new_user_email_verification' );

			return $email_tmpl_obj->send_email( $args['user_email'], $args, $email_tmpl_data );
		}
		/**
		 * Using Default
		 */
	}

	/**
	 * Order details.
	 *
	 * @param array $order_detail order_detail array.
	 */
	private function check_order_details( $order_detail ) {
		$check_order_detials = true;

		if ( ! isset( $order_detail['order_status'] ) || ! isset( $order_detail['buyer_id'] ) || ! isset( $order_detail['course_id'] ) ) {
			$check_order_detials = false;
		}
		return $check_order_detials;
	}


	/**
	 * Custom mailer.
	 *
	 * @param array $_to to mail.
	 * @param array $email_subject Email subject.
	 * @param array $email_content email content.
	 * @param array $email_headers email headers.
	 */
	public function mailer( $_to, $email_subject, $email_content, $email_headers = '' ) {

		// inject CSS rules for text and image alignment.
		$email_css     = $this->mailer_css();
		$email_content = $email_css . $email_content;

		$sent = wp_mail( $_to, $email_subject, $email_content, $email_headers );

		return $sent;
	}

	/**
	 * Custom css to be added in emails.
	 */
	public function mailer_css() {
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
	 * Get Content Html function.
	 * returns the template content.
	 *
	 * @param array $args email content.
	 * @return string
	 */
	public function get_content_html( $args ) {
		ob_start();
		$this->plugin_template_loader->wp_get_template( $this->template_name, $args );

		return ob_get_clean();
	}
}
