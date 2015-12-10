<?php

/**
 * This class defines all code necessary to send emails on course purchase.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * 
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
class EB_Emailer {

	private $template_name;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $plugin_template_loader; //template loader object

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		/**
         * Class responsible for loading templates
         */
        require_once EB_PLUGIN_DIR . 'public/class-eb-template-loader.php';
		
		$this->plugin_template_loader = new EB_Template_Loader( $this->plugin_name, $this->version );
	}

	/**
	 * runs on each email template to add email header and css styling
	 * 
	 * @param  string $header email heading
	 */
	public function get_email_header( $header ) {
		$this->template_name    = 'emails/email-header.php';
		echo $this->get_content_html( array('header' => $header), $this->plugin_template_loader );
	}

	/**
	 * runs on each email template to add email footer content
	 * 
	 */
	public function get_email_footer( ) {
		$this->template_name    = 'emails/email-footer.php';
		echo $this->get_content_html( '', $this->plugin_template_loader );
	}

	/**
	 * Sends a new user registration email notification
	 *
	 * called using 'eb_created_user' hook after user registration.
	 *
	 * @param array   $args user details array
	 * @return bool
	 */
	public function send_new_user_email( $args ) {
		
		$this->template_name    = 'emails/user-new-account.php';

		// prepare arguments array for email
		$args = apply_filters( 'eb_filter_email_parameters', $args, $this->template_name );

		$email_subject = apply_filters( 'eb_new_user_email_subject', __( 'New User Account Details', 'eb-textdomain' ) );
		$args['header'] = $email_subject; // send email subject as header in email template
		$email_content = $this->get_content_html( $args );
		$email_headers  = apply_filters( 'eb_email_headers' ,array( 'Content-Type: text/html; charset=UTF-8' ) );

		//send email
		$sent = $this->mailer( $args['user_email'], $email_subject, $email_content, $email_headers );

		return 1;
	}

	/**
	 * Sends an email with moodle account credentials to existing wordpress users
	 *
	 * called using 'eb_linked_moodle_to_existing_user' hook on user login.
	 * for users who already have a wordpress account.
	 *
	 * @param array   $args user details array
	 * @return bool
	 */
	public function send_existing_user_moodle_account_email( $args ) {
		
		$this->template_name    = 'emails/user-existing-wp-account.php';
		$this->plugin_template_loader = new EB_Template_Loader( $this->plugin_name, $this->version );

		// prepare arguments array for email
		$args = apply_filters( 'eb_filter_email_parameters', $args, $this->template_name );

		$email_subject  = apply_filters( 'eb_existing_wp_user_email_subject', __( 'Your Learning Account Credentials', 'eb-textdomain' ) );
		$args['header'] = $email_subject; // send email subject as header in email template
		$email_content  = $this->get_content_html( $args );
		$email_headers  = apply_filters( 'eb_email_headers' ,array( 'Content-Type: text/html; charset=UTF-8' ) );

		//send email
		$sent = $this->mailer( $args['user_email'], $email_subject, $email_content, $email_headers );

		return 1;
	}

	/**
	 * Sends an email on successful course purchase ( Order Completion )
	 * called using 'eb_order_status_completed' hook on order completion.
	 *
	 * @param array   $args order id
	 * @return bool
	 */
	public function send_order_completion_email( $order_id ) {

		global $wpdb;

		$order_detail = get_post_meta( $order_id, 'eb_order_options', true ); //get order details

		// return if there is a problem in order details
		if ( !isset( $order_detail['order_status'] ) || !isset( $order_detail['buyer_id'] ) || !isset( $order_detail['course_id'] ) ) {
			return;
		}

		$buyer_detail = get_userdata( $order_detail['buyer_id'] ); //get buyer details
		$args = array(); // arguments array for email

		$this->template_name    = 'emails/user-order-completion-email.php'; // template for order completion email
		$this->plugin_template_loader = new EB_Template_Loader( $this->plugin_name, $this->version ); //template loader object

		// prepare arguments array for email
		$args = apply_filters( 'eb_filter_email_parameters', array(
				'order_id'   => $order_id,
				'course_id'  => $order_detail['course_id'],
				'user_email' => $buyer_detail->user_email,
				'username'   => $buyer_detail->user_login,
				'first_name' => isset( $buyer_detail->first_name )?$buyer_detail->first_name:'',
				'last_name'  => isset( $buyer_detail->last_name )?$buyer_detail->last_name:''
			), $this->template_name );

		$email_subject  = apply_filters( 'eb_order_completion_email_subject', __( 'Your order completed successfully.', 'eb-textdomain' ) );
		$args['header'] = $email_subject; // send email subject as header in email template
		$email_content  = $this->get_content_html( $args );
		$email_headers  = apply_filters( 'eb_email_headers' ,array( 'Content-Type: text/html; charset=UTF-8' ) );

		//send email
		$sent = $this->mailer( $args['user_email'], $email_subject, $email_content, $email_headers );

		return 1;
	}

	// custom mailer
	public function mailer( $to, $email_subject, $email_content, $email_headers = '' ) {

		// inject CSS rules for text and image alignment
		$email_css   = $this->mailer_css();
		$email_content  = $email_css . $email_content;

		$sent = wp_mail( $to, $email_subject, $email_content, $email_headers );

		return $sent;
	}

	// custom css to be added in emails
	public function mailer_css() {
		$css = '<style type="text/css"> .alignleft {float: left;margin: 5px 20px 5px 0;}.alignright {float: right;margin: 5px 0 5px 20px;}.aligncenter {display: block;margin: 5px auto;}img.alignnone {margin: 5px 0;}'.
			'blockquote,q {quotes: none;}blockquote:before,blockquote:after,q:before,q:after {content: "";content: none;}'.
			'blockquote {font-size: 24px;font-style: italic;font-weight: 300;margin: 24px 40px;}'.
			'blockquote blockquote {margin-right: 0;}blockquote cite,blockquote small {font-size: 14px;font-weight: normal;text-transform: uppercase;}'.
			'cite {border-bottom: 0;}abbr[title] {border-bottom: 1px dotted;}address {font-style: italic;margin: 0 0 24px;}'.
			'del {color: #333;}ins {background: #fff9c0;border: none;color: #333;text-decoration: none;}'.
			'sub,sup {font-size: 75%;line-height: 0;position: relative;vertical-align: baseline;}'.
			'sup {top: -0.5em;}sub {bottom: -0.25em;}</style>';

		return $css;
	}

	/**
	 * get_content_html function
	 * returns the template content
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html( $args ) {
		ob_start();
		$this->plugin_template_loader->wp_get_template( $this->template_name, $args );
		return ob_get_clean();
	}
}
