<?php
/**
 * Edwiser Bridge Email template page
 *
 * @link  https://edwiser.org
 * @since 1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * EB_Email_Template Class
 */
class EB_Email_Template {


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'mce_external_plugins', array( $this, 'add_mce_plugin' ) );
		/**
		 * Filter for the email template list and email temaplte constant.
		 */
		add_filter( 'eb_email_templates_list', array( $this, 'eb_add_email_list' ), 10, 1 );
		add_filter( 'eb_email_template_constant', array( $this, 'email_template_constant' ), 10, 1 );
		add_filter( 'wp_mail_from_name', array( $this, 'wp_sender_name' ), 99, 1 );
	}

	/**
	 * Provides the functionality to prepare the email temaplte list to display
	 * in the manage email temaplte page.
	 *
	 * This is the callback for the eb_email_templates_list.
	 *
	 * @param  array $email_list array of the email template list.
	 * @return array of the email tempalte list.
	 */
	public function eb_add_email_list( $email_list ) {
		$email_list['eb_emailtmpl_create_user']                         = esc_html__( 'New User Account Details', 'edwiser-bridge' );
		$email_list['eb_emailtmpl_linked_existing_wp_user']             = esc_html__( 'Link WP user account to moodle', 'edwiser-bridge' );
		$email_list['eb_emailtmpl_linked_existing_wp_new_moodle_user']  = esc_html__( 'Create new moodle account', 'edwiser-bridge' );
		$email_list['eb_emailtmpl_order_completed']                     = esc_html__( 'Course Order Completion', 'edwiser-bridge' );
		$email_list['eb_emailtmpl_course_access_expir']                 = esc_html__( 'Course access expired', 'edwiser-bridge' );
		$email_list['eb_emailtmpl_refund_completion_notifier_to_user']  = esc_html__( 'Refund Success mail to customer', 'edwiser-bridge' );
		$email_list['eb_emailtmpl_refund_completion_notifier_to_admin'] = esc_html__( 'Refund Success mail to admin or specified email', 'edwiser-bridge' );
		$email_list['eb_emailtmpl_new_user_email_verification']         = esc_html__( 'Verify Your Email Address', 'edwiser-bridge' );

		/**
		 *   Two way synch.
		 */

		$email_list['eb_emailtmpl_mdl_enrollment_trigger']    = esc_html__( 'Moodle Course Enrollment', 'edwiser-bridge' );
		$email_list['eb_emailtmpl_mdl_un_enrollment_trigger'] = esc_html__( 'Moodle Course Un-Enrollment', 'edwiser-bridge' );
		$email_list['eb_emailtmpl_mdl_user_deletion_trigger'] = esc_html__( 'User Account Deleted', 'edwiser-bridge' );
		return $email_list;
	}

	/**
	 * Handles the manage email temaplte page output
	 */
	public function output() {
		$sub_action = isset( $_POST['eb-mail-tpl-submit'] ) ? sanitize_text_field( wp_unslash( $_POST['eb-mail-tpl-submit'] ) ) : 0;

		// Save fields only if nonce is verified.
		if ( isset( $_POST['eb_emailtmpl_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eb_emailtmpl_nonce'] ) ), 'eb_emailtmpl_sec' ) && 'eb-mail-tpl-save-changes' === $sub_action ) {
			$this->save();
		}

		// Even if nonce is not verified show the default data.
		$from_name     = $this->get_from_name();
		$tmpl_list     = array();
		$tmpl_list     = apply_filters( 'eb_email_templates_list', $tmpl_list );
		$section       = array();
		$const_sec     = apply_filters( 'eb_email_template_constant', $section );
		$checked       = array();
		$notif_on      = '';
		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		if ( isset( $_GET['curr_tmpl'] ) ) {
			$tmpl_key  = sanitize_text_field( wp_unslash( $_GET['curr_tmpl'] ) );
			$tmpl_name = $tmpl_list[ sanitize_text_field( wp_unslash( $_GET['curr_tmpl'] ) ) ];
			$notif_on  = $this->is_not_if_enabled( sanitize_text_field( wp_unslash( $_GET['curr_tmpl'] ) ) );
			$bcc_email = $this->get_bcc_email( sanitize_text_field( wp_unslash( $_GET['curr_tmpl'] ) ) );
		} else {
			$tmpl_key  = key( $tmpl_list );
			$tmpl_name = current( $tmpl_list );
			$notif_on  = $this->is_not_if_enabled( $tmpl_key );
			$bcc_email = $this->get_bcc_email( $tmpl_key );
		}

		$tmpl_data    = $this->get_email_template( $tmpl_key );
		$tmpl_content = apply_filters( 'eb_email_template_data', $tmpl_data );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline eb-emailtemp-head"><?php esc_html_e( 'Manage Email Templates', 'edwiser-bridge' ); ?></h1>
			<div class="eb-email-template-wrap">
				<div class="eb-template-edit-form">
					<h3 id="eb-email-template-name"><?php echo esc_html( $tmpl_name ); ?></h3>
					<form name="manage-email-template" method="POST">
						<input type="hidden" name="eb_tmpl_name" id="eb_emailtmpl_name"
								value="<?php echo esc_html( $tmpl_key ); ?>"/>
								<?php
								wp_nonce_field( 'eb_emailtmpl_sec', 'eb_emailtmpl_nonce' );
								?>
						<table>
							<tr>
								<td class="eb-email-lable"><?php esc_html_e( 'From Name', 'edwiser-bridge' ); ?></td>
								<td>
									<input type="text" name="eb_email_from_name" id="eb_email_from_name" value="<?php echo esc_html( $from_name ); ?>" class="eb-email-input" title="<?php esc_html_e( 'Enter name here to use as the form name in email sent from site using Edwisaer plugins', 'edwiser-bridge' ); ?>" placeholder="<?php esc_attr_e( 'Enter from name', 'edwiser-bridge' ); ?>"/>
								</td>
							</tr>

							<tr>
								<td class="eb-email-lable"><?php esc_html_e( 'Subject', 'edwiser-bridge' ); ?></td>
								<td>
									<input type="text" name="eb_email_subject" id="eb_email_subject" value="<?php echo esc_attr( $tmpl_content['subject'] ); ?>" class="eb-email-input" title="<?php esc_html_e( 'Enter the subject for the current email template. Current template will use the entered subject to send email from the site', 'edwiser-bridge' ); ?>" placeholder="<?php esc_html_e( 'Enter email subject', 'edwiser-bridge' ); ?>"/>
								</td>
							</tr>

							<tr>
								<td class="eb-email-lable"><?php esc_html_e( 'Send email notification to the user?', 'edwiser-bridge' ); ?></td>
								<td>
									<input type="checkbox" name="eb_email_notification_on" id="eb_email_notification_on" value="ON" <?php echo checked( $notif_on, 'ON' ); ?> class="eb-email-input" title="<?php esc_html_e( 'Check the option to notify the user using selected template on action', 'edwiser-bridge' ); ?>" />
								</td>
							</tr>


							<tr>
								<td class="eb-email-lable">
									<?php esc_html_e( 'BCC in email', 'edwiser-bridge' ); ?>
								</td>
								<td>
									<input type="text" value="<?php echo esc_html( $bcc_email ); ?>" name="eb_bcc_email" id="eb_bcc_email" class="eb-email-input"/>
								</td>
							</tr>
							<?php

								do_action( 'eb_manage_email_template_before_text_editor', $tmpl_key );

							?>
							<tr>
								<td colspan="2" class="eb-template-edit-cell">
			<?php
			$this->get_editor( $tmpl_content['content'] );
			?>
								</td>
							</tr>
							<tr>
								<td>
									<input name="eb-mail-tpl-submit" type="hidden" id="eb-mail-tpl-submit" value="eb-mail-tpl-save-changes" />
									<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'edwiser-bridge' ); ?>" name="eb_save_tmpl" title="<?php esc_html_e( 'Save changes', 'edwiser-bridge' ); ?>"/>
									<input type="button" class="button-primary" value="<?php esc_html_e( 'Restore template content', 'edwiser-bridge' ); ?>" id="eb_email_reset_template" name="eb_email_reset_template" />
									<input type="hidden" id="current_selected_email_tmpl_key" name="current_selected_email_tmpl_key" value="<?php echo esc_html( $tmpl_key ); ?>" />
									<input type="hidden" id="current-tmpl-name" name="current_selected_email_tmpl_name" value="<?php echo esc_attr( $tmpl_content['subject'] ); ?>" />
								</td>
							</tr>
						</table>
					</form>
					<div class="eb-email-testmail-wrap">
						<h3><?php esc_html_e( 'Send a test email of the selected template', 'edwiser-bridge' ); ?></h3>
						<div class="eb-email-temp-test-mail-wrap">
							<label class="eb-email-lable"><?php esc_html_e( 'To', 'edwiser-bridge' ); ?> : </label>
							<?php wp_nonce_field( 'eb_send_testmail_sec', 'eb_send_testmail_sec_filed' ); ?>
							<input type="email" name="eb_test_email_add" id="eb_test_email_add_txt" value="" title="<?php esc_html_e( 'Type an email address here and then click Send Test to generate a test email using current selected template', 'edwiser-bridge' ); ?>." placeholder="<?php esc_html_e( 'Enter email address', 'edwiser-bridge' ); ?>"/>
							<input type="button" class="button-primary" value="<?php esc_html_e( 'Send Test', 'edwiser-bridge' ); ?>" name="eb_send_test_email" id="eb_send_test_email" title="<?php esc_html_e( 'Send sample email with current selected template', 'edwiser-bridge' ); ?>"/>
							<span class="load-response">
								<img alt="<?php esc_html__( 'Sorry, unable to load the image', 'edwiser-bridge' ); ?>" src="<?php echo esc_url( $eb_plugin_url . '/images/loader.gif' ); ?>" height="20" width="20">
							</span>
							<div class="response-box">
							</div>
						</div>
						<span class="eb-email-note"><strong><?php esc_html_e( 'Note', 'edwiser-bridge' ); ?>:-</strong> <?php esc_html_e( 'Some of the constants in these emails would be replaced by demo content', 'edwiser-bridge' ); ?>.</span>

					</div>
				</div>
				<div class="eb-edit-email-template-aside">
					<div class="eb-email-templates-list">
						<h3><?php esc_attr_e( 'Email Templates', 'edwiser-bridge' ); ?></h3>
						<ul id="eb_email_templates_list">
				<?php
				foreach ( $tmpl_list as $tmpl_id => $tmpl_name ) {
					$tml_list_class = 'eb-emailtmpl-list-item';
					if ( $tmpl_key === $tmpl_id ) {
						$tml_list_class = 'eb-emailtmpl-list-item eb-emailtmpl-active';
					}
					?>
					<li id='<?php echo esc_attr( $tmpl_id ); ?>' class='<?php echo esc_attr( $tml_list_class ); ?>'><?php echo esc_attr( $tmpl_name ); ?></li>
					<?php
				}
				?>
						</ul>
					</div>
					<div class="eb-email-templates-const-wrap">
						<h3><?php esc_html_e( 'Template Constants', 'edwiser-bridge' ); ?></h3>
						<div class="eb-emiltemp-const-wrap">
		<?php
		foreach ( $const_sec as $sec_name => $tmpl_const ) {
			?>
			<div class='eb-emailtmpl-const-sec'>
				<h3><?php echo esc_attr( $sec_name ); ?></h3>
				<?php foreach ( $tmpl_const as $const => $desc ) { ?>
					<div class="eb-mail-templat-const">
						<span><?php echo esc_attr( $const ); ?></span>
						<?php echo esc_attr( $desc ); ?>
					</div>
				<?php } ?>
			</div>
			<?php
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
	 *
	 * @param  string $curr_tmpl_name email temaplte option key.
	 * @return string returns ON if the email template is enambled for the provided template
	 */
	private function is_not_if_enabled( $curr_tmpl_name ) {
		$notif_enabled = get_option( $curr_tmpl_name . '_notify_allow' );
		if ( isset( $notif_enabled ) && ! empty( $notif_enabled ) && 'ON' === $notif_enabled ) {
			return 'ON';
		} else {
			return '';
		}
	}

	/**
	 * Addes the bcc mail tmpl.
	 *
	 * @param string $curr_tmpl_name current template name.
	 *
	 * @return string bcc emial address accociated with the template.
	 */
	public function get_bcc_email( $curr_tmpl_name ) {
		$bcc_email = get_option( $curr_tmpl_name . '_bcc_email' );
		if ( ! $bcc_email ) {
			$bcc_email = '';
		}
		return $bcc_email;
	}


	/**
	 * Provides the functionality to prepare the wp editor for the email template edit
	 *
	 * @param TinyMCE editor $content returns the TinyMCE  editor with the template content.
	 */
	private function get_editor( $content ) {

		$settings = array(
			'media_buttons'    => false,
			'drag_drop_upload' => false,
			'textarea_rows'    => 15,
		);
		wp_editor( $content, 'eb_emailtmpl_editor', $settings );
	}

	/**
	 * Provides the functionality to add the mce plugin for the email tempalte editing
	 * callback for the mce_external_plugins actoin
	 *
	 * @return string
	 */
	public function add_mce_plugin() {
		$plugins = array( 'legacyoutput' => plugins_url( 'assets/', __FILE__ ) . 'tinymce/legacyoutput/plugin.min.js' );
		return $plugins;
	}

	/**
	 * Ajax callback to get the template content
	 * callback for the action wdm_eb_get_email_template
	 */
	public function get_template_data_ajax_call_back() {
		$data = array();

		// Process only if nonce is verified.
		if ( isset( $_POST['tmpl_name'] ) && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {
			$tmpl_name    = sanitize_text_field( wp_unslash( $_POST['tmpl_name'] ) );
			$tmpl_data    = get_option( $tmpl_name );
			$notify_allow = get_option( $tmpl_name . '_notify_allow' );
			$bcc_email    = get_option( $tmpl_name . '_bcc_email' );

			if ( ! $bcc_email ) {
				$bcc_email = '';
			}

			$data['from_name']    = $this->get_from_name();
			$data['subject']      = $tmpl_data['subject'];
			$data['content']      = $tmpl_data['content'];
			$data['notify_allow'] = $notify_allow;
			$data['bcc_email']    = $bcc_email;
		}
		echo wp_json_encode( $data );
		die();
	}
	/**
	 * Getter methods start
	 */

	/**
	 * The method retrives the from email address.
	 *
	 * @return string Returns from email address.
	 */
	private function get_from_email() {
		$from_email = get_option( 'eb_mail_from_email' );
		if ( false === $from_email ) {
			$from_email = get_option( 'admin_email' );
		}
		return $from_email;
	}

	/**
	 * Provides the functoinality to get the From email name
	 *
	 * @return string returns the from name for the email
	 */
	private function get_from_name() {
		$from_name = get_option( 'eb_mail_from_name' );
		if ( false === $from_name ) {
			$from_name = get_bloginfo( 'name' );
		}
		return $from_name;
	}

	/**
	 * Defaines the email template constants
	 * callback for the action eb_email_template_constant
	 *
	 * @param array $constants array of the email template constants.
	 */
	public function email_template_constant( $constants ) {
		/**
		 * Genral constants.
		 */
		$genral['{USER_NAME}']              = __( 'The display name of the user.', 'edwiser-bridge' );
		$genral['{FIRST_NAME}']             = __( 'The first name of the user.', 'edwiser-bridge' );
		$genral['{LAST_NAME}']              = __( 'The last name of the user.', 'edwiser-bridge' );
		$genral['{SITE_NAME}']              = __( 'The name of the website.', 'edwiser-bridge' );
		$genral['{SITE_URL}']               = __( 'The URL of the website.', 'edwiser-bridge' );
		$genral['{COURSES_PAGE_LINK}']      = __( 'The link to the courses archive page.', 'edwiser-bridge' );
		$genral['{MY_COURSES_PAGE_LINK}']   = __( 'The link to the my courses page.', 'edwiser-bridge' );
		$genral['{USER_ACCOUNT_PAGE_LINK}'] = __( 'The WordPress user account page link.', 'edwiser-bridge' );
		$genral['{WP_LOGIN_PAGE_LINK}']     = __( 'The WordPress login page link.', 'edwiser-bridge' );
		$genral['{MOODLE_URL}']             = __( 'The moodle site url entered in the connection.', 'edwiser-bridge' );
		/**
		 * New account and link account constants
		 */
		$account['{USER_PASSWORD}'] = __( 'The user accounts password.', 'edwiser-bridge' );
		/**
		 * Course order template constants
		 */
		$order['{COURSE_NAME}'] = __( 'The title of the course.', 'edwiser-bridge' );
		$order['{ORDER_ID}']    = __( 'The order id of the purchased order completed.', 'edwiser-bridge' );

		/*
		*Refund Order template constants
		*/
		$refund['{ORDER_ID}']                = __( 'Refund order id.', 'edwiser-bridge' );
		$refund['{CUSTOMER_DETAILS}']        = __( 'This will get replaced by the customer details.', 'edwiser-bridge' );
		$refund['{ORDER_ITEM}']              = __( 'Order associated item list.', 'edwiser-bridge' );
		$refund['{TOTAL_AMOUNT_PAID}']       = __( 'Amount paid at the time of order placed.', 'edwiser-bridge' );
		$refund['{CURRENT_REFUNDED_AMOUNT}'] = __( 'Currantly refunded amount.', 'edwiser-bridge' );
		$refund['{TOTAL_REFUNDED_AMOUNT}']   = __( 'Total amount refunded till the time.', 'edwiser-bridge' );
		$refund['{ORDER_REFUND_STATUS}']     = __( 'Order refund status transaction.', 'edwiser-bridge' );

		/**
		 * Course unenrollment alert constants
		 */
		$unenrollment['{WP_COURSE_PAGE_LINK}'] = __( 'The current course page link.', 'edwiser-bridge' );

		$constants['General constants']       = $genral;
		$constants['New moodle user account'] = $account;
		$constants['Order Completion ']       = $order;
		$constants['Course Unenrollment ']    = $unenrollment;
		$constants['Order Refund']            = $refund;
		return $constants;
	}

	/**
	 * Provides the functioanlity to get the template contetn from teh database
	 *
	 * @param  string $tmpl_name the option key to fetch the email temaplate content.
	 * @return returns the array of the email template subject and content
	 */
	private function get_email_template( $tmpl_name ) {
		return get_option( $tmpl_name );
	}
	/**
	 * Getter methods end
	 */

	/**
	 * Setter methods start.
	 * Sets the from email name in wp database.
	 *
	 * @param string $name the from name for email.
	 */
	private function set_from_name( $name ) {
		update_option( 'eb_mail_from_name', $name );
	}

	/**
	 * Settor method to store the email template content
	 * Stores the email temaplte content in the wp opotions table with the key @parm $tempalteName
	 *
	 * @param type $tempalte_name template option key to store into the databse.
	 * @param type $tempalte_data store the template conten in the database.
	 */
	private function set_template_data( $tempalte_name, $tempalte_data ) {
		update_option( $tempalte_name, $tempalte_data );
	}

	/**
	 * Provides the functionality to set the notification enable disable value into the databse
	 *
	 * @param type $tempalte_name template option key.
	 * @param type $notify_allow  is notificaiotn allow to send or not.
	 */
	private function set_notify_allow( $tempalte_name, $notify_allow ) {
		update_option( $tempalte_name . '_notify_allow', $notify_allow );
	}


	/**
	 * Provides the functionality to set the notification enable disable value into the databse
	 *
	 * @param string  $tempalte_name template option key.
	 * @param boolean $notify_allow  is notificaiotn allow to send or not.
	 */
	private function set_bcc_email_address( $tempalte_name, $notify_allow ) {
		update_option( $tempalte_name . '_bcc_email', $notify_allow );
	}


	/**
	 * Provides the functionality to save the email temaplte content into the database
	 */
	private function save() {
		$message = '';
		// Process saving only if the nonce is verified.
		if ( isset( $_POST['eb_emailtmpl_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eb_emailtmpl_nonce'] ) ), 'eb_emailtmpl_sec' ) ) {
			$from_name    = $this->check_is_empty( $_POST, 'eb_email_from_name' );
			$subject      = $this->check_is_empty( $_POST, 'eb_email_subject' );
			$tmpl_contetn = $this->check_is_empty( $_POST, 'eb_emailtmpl_editor' );
			$tmpl_name    = $this->check_is_empty( $_POST, 'eb_tmpl_name' );
			$notify_allow = $this->check_is_empty( $_POST, 'eb_email_notification_on' );
			$notify_allow = 'ON' === $notify_allow ? $notify_allow : 'OFF';
			$bcc_email    = $this->check_is_empty( $_POST, 'eb_bcc_email' );

			$data = array(
				'subject' => $subject,
				'content' => stripslashes( $tmpl_contetn ),
			);

			$this->set_from_name( $from_name );
			$this->set_notify_allow( $tmpl_name, $notify_allow );
			$this->set_template_data( $tmpl_name, $data );
			$this->set_bcc_email_address( $tmpl_name, $bcc_email );
			$message = self::get_notice_html( __( 'Changes saved successfully!', 'edwiser-bridge' ) );
		} else {
			$message = self::get_notice_html( __( 'Due to the security issue changes are not saved, Try to re-update it.', 'edwiser-bridge' ), 'error' );
		}
	}

	/**
	 * Checks the array value is set for the current key
	 *
	 * @param  array  $data_array array of the data.
	 * @param  string $key       key to check value is present in the array.
	 * @return boolean/string the value associated for the array key otherwise returns false
	 */
	private function check_is_empty( $data_array, $key ) {
		if ( isset( $data_array[ $key ] ) && ! empty( $data_array[ $key ] ) ) {
			return $data_array[ $key ];
		} else {
			return false;
		}
	}



	/**
	 * DEPRECATED FUNCTION.
	 *
	 * Provides teh functioanlityto get the email tempalte constant.
	 *
	 * @deprecated since 2.0.1 use get_email_tmpl_content($tmpl_name) insted.
	 * @param  string $tmpl_name template key.
	 * @return string returns the template content associated with the template
	 * kay othrewise emapty string
	 */
	public static function getEmailTmplContent( $tmpl_name ) {
		return self::get_email_tmpl_content( $tmpl_name );
	}



	/**
	 * Provides teh functioanlityto get the email tempalte constant
	 *
	 * @param  string $tmpl_name template key.
	 * @return string returns the template content associated with the template
	 * kay othrewise emapty string
	 */
	public static function get_email_tmpl_content( $tmpl_name ) {
		$tmpl_content = get_option( $tmpl_name );
		if ( $tmpl_content ) {
			return $tmpl_content;
		}
		return '';
	}

	/**
	 * Provides the functioanlity to send the test email
	 */
	public function send_test_email() {
		// Send test mail only if nonce is verified.
		if ( isset( $_POST['security'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'eb_send_testmail_sec' ) ) {
			$mail_to = $this->check_is_empty( $_POST, 'mail_to' );
			/**
			 * Dummy data.
			 */
			$args = array(
				'course_id'   => '1',
				'password'    => get_option( 'eb_emailtmpl_create_user_notify_allow' ),
				'eb_order_id' => '12235', // chnaged 1.4.7.
				'headers'     => isset( $_POST['headers'] ) ? sanitize_text_field( wp_unslash( $_POST['headers'] ) ) : '',
			);
			$mail = $this->send_email( $mail_to, $args, $_POST );
			if ( $mail ) {
				wp_send_json_success( 'OK' );
			} else {
				wp_send_json_error( 'Failed to send test email.' );
			}
		} else {
			wp_send_json_error( 'Invalid request' );
		}
	}


	/**
	 * DEPRECATED FUNCTION.
	 *
	 * Provides the funcationlity to send the email temaplte.
	 *
	 * @deprecated since 2.0.1 use send_emial( $mail_to, $args, $tmpl_data ) insted.
	 * @param  text  $mail_to   email id to send the email id.
	 * @param  array $args      the default email argument.
	 * @param  html  $tmpl_data email template contetn.
	 * @return boolean returns true if the email sent successfully othrewise false
	 */
	public function sendEmail( $mail_to, $args, $tmpl_data ) {
		return $this->send_email( $mail_to, $args, $tmpl_data );
	}




	/**
	 * Provides the funcationlity to send the email temaplte
	 *
	 * @param  text  $mail_to   email id to send the email id.
	 * @param  array $args      the default email argument.
	 * @param  html  $tmpl_data email template contetn.
	 * @return boolean returns true if the email sent successfully othrewise false
	 */
	public function send_email( $mail_to, $args, $tmpl_data ) {

		$from_email   = $this->get_from_email();
		$from_name    = $this->get_from_name();
		$subject      = $this->check_is_empty( $tmpl_data, 'subject' );
		$tmpl_content = stripslashes( $this->check_is_empty( $tmpl_data, 'content' ) );

		/**
		 * Call the email template parser
		 */
		$email_tmpl_parser = new Eb_Email_Template_Parser();
		$tmpl_content      = $email_tmpl_parser->out_put( $args, $tmpl_content );

		/**
		 * Email send start
		 */
		$tmpl_content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
		. '<html>'
		. '<body>'
		. $tmpl_content
		. '</body>'
		. '</html>';

		$headers = array( 'Content-Type: text/html; charset=UTF-8; http-equiv="Content-Language" content="en-us"' );

		add_filter(
			'wp_mail_content_type',
			function () {
				return 'text/html';
			}
		);

		// CUSTOMIZATION CHANGES.
		if ( isset( $args['headers'] ) ) {
			$headers[] = $args['headers'];
		}

		$mail = wp_mail( $mail_to, $subject, $tmpl_content, $headers );
		remove_filter(
			'wp_mail_content_type',
			function () {
				return 'text/html';
			}
		);

		remove_filter( 'wp_mail_from_name', array( $this, 'wpb_sender_name' ) );
		/**
		 * Email send end
		 */
		return $mail;
	}

	/**
	 * Functioanlity to fetch the from email from database.
	 *
	 * @deprecated since 2.0.1 use wpb_sender_email( $email ) insted
	 * @param string $email start the email send process.
	 *
	 * @return string returns from email.
	 */
	public function wpbSenderEmail( $email ) {
		return $this->wpb_sender_email( $email );
	}

	/**
	 * Functioanlity to fetch the from email from database.
	 *
	 * @param string $email start the email send process.
	 *
	 * @return string returns from email.
	 */
	public function wpb_sender_email( $email ) {
		return $this->get_from_email( $email );
	}

	/**
	 * Functioanlity to fetch the from email from database
	 *
	 * @param  string $name email sender name.
	 * @return string returns from email
	 */
	public function wp_sender_name( $name ) {
		return $this->get_from_name( $name );
	}

	/**
	 * Prepares the email tempalte content
	 *
	 * @param  string  $msg the message to display.
	 * @param  string  $type type of the message.
	 * @param  boolean $dismissible is the message dismissible.
	 */
	public static function get_notice_html( $msg, $type = 'success', $dismissible = true ) {
		$classes = 'notice notice-' . $type;
		if ( $dismissible ) {
			$classes .= ' is-dismissible';
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<p><?php echo esc_html( $msg ); ?></p>
		</div>
		<?php
	}

	/**
	 * Provides the functionality to restore the email temaplte content and subject
	 */
	public function reset_email_template_content() {
		$responce = array(
			'data'   => __( 'Failed to reset email template', 'edwiser-bridge' ),
			'status' => 'failed',
		);
		if ( isset( $_POST['action'] ) && isset( $_POST['tmpl_name'] ) && 'wdm_eb_email_tmpl_restore_content' === $_POST['action'] && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {

			$args = $this->restore_email_template(
				array(
					'is_restored' => false,
					'tmpl_name'   => sanitize_text_field( wp_unslash( $_POST['tmpl_name'] ) ),
				)
			);
			if ( true === $args['is_restored'] ) {
				$responce['data']   = __( 'Template restored successfully', 'edwiser-bridge' );
				$responce['status'] = 'success';
				wp_send_json_success( $responce );
			} else {
				wp_send_json_error( $responce );
			}
		} else {
			wp_send_json_error( $responce );
		}
	}

	/**
	 * Provides the functonality to restore the email temaplte content
	 *
	 * @param  string $args array of the emial tempalte config.
	 * @return boolean
	 */
	public function restore_email_template( $args ) {
		$default_tmpl = new Eb_Default_Email_Templates();
		$tmpl_key     = $args['tmpl_name'];
		switch ( $tmpl_key ) {
			case 'eb_emailtmpl_create_user':
				$value = $default_tmpl->new_user_acoount( 'eb_emailtmpl_create_user', true );
				break;
			case 'eb_emailtmpl_linked_existing_wp_user':
				$value = $default_tmpl->link_wp_moodle_account( 'eb_emailtmpl_linked_existing_wp_user', true );
				break;
			case 'eb_emailtmpl_order_completed':
				$value = $default_tmpl->order_complete( 'eb_emailtmpl_order_completed', true );
				break;
			case 'eb_emailtmpl_course_access_expir':
				$value = $default_tmpl->course_access_expired( 'eb_emailtmpl_course_access_expir', true );
				break;
			case 'eb_emailtmpl_linked_existing_wp_new_moodle_user':
				$value = $default_tmpl->link_new_moodle_account( 'eb_emailtmpl_linked_existing_wp_new_moodle_user', true );
				break;
			case 'eb_emailtmpl_refund_completion_notifier_to_user':
				$value = $default_tmpl->notify_user_on_order_refund( 'eb_emailtmpl_refund_completion_notifier_to_user', true );
				break;
			case 'eb_emailtmpl_refund_completion_notifier_to_admin':
				$value = $default_tmpl->notify_admin_on_order_refund( 'eb_emailtmpl_refund_completion_notifier_to_admin', true );
				break;
			case 'eb_emailtmpl_mdl_enrollment_trigger':
				$value = $default_tmpl->moodle_enrollment_trigger( 'eb_emailtmpl_mdl_enrollment_trigger', true );
				break;
			case 'eb_emailtmpl_mdl_un_enrollment_trigger':
				$value = $default_tmpl->moodle_unenrollment_trigger( 'eb_emailtmpl_mdl_un_enrollment_trigger', true );
				break;
			case 'eb_emailtmpl_mdl_user_deletion_trigger':
				$value = $default_tmpl->user_deletion_trigger( 'eb_emailtmpl_mdl_user_deletion_trigger', true );
				break;
			case 'eb_emailtmpl_new_user_email_verification':
				$value = $default_tmpl->new_user_email_verification( 'eb_emailtmpl_new_user_email_verification', true );
				break;
			default:
				$args = apply_filters(
					'eb_reset_email_tmpl_content',
					array(
						'is_restored' => false,
						'tmpl_name'   => $args['tmpl_name'],
					)
				);
				return $args;
		}
		$status = update_option( $tmpl_key, $value );
		if ( $status ) {
			$args['is_restored'] = true;
			return $args;
		} else {
			return $args;
		}
	}
}

/**
 * Deprecated Class.
 *
 * @deprecated 3.0.0
 */
class EBAdminEmailTemplate extends EB_Email_Template { // @codingStandardsIgnoreLine

}
