<?php
/**
 * Edwiser Bridge Email template parser
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Email_Template_Parser' ) ) {
	/**
	 * Parser.
	 */
	class Eb_Email_Template_Parser {
		/**
		 * Plugin name.
		 *
		 * @since    1.0.0
		 *
		 * @var string plugin name.
		 */
		private $plugin_name;

		/**
		 * Version.
		 *
		 * @since    1.0.0
		 *
		 * @var string version .
		 */
		private $version;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$eb_instance       = EdwiserBridge::instance();
			$this->plugin_name = $eb_instance->get_plugin_name();
			$this->version     = $eb_instance->get_version();
		}
		/**
		 * Provides the functionality to parse the email temaplte raw content.
		 *
		 * Provides the filters to parse the template content.
		 *
		 * @deprecated since 2.0.1 use out_put( $args, $tmpl_content ) insted.
		 * @param array $args default arguments to replace the email template constants.
		 * @param HTML  $tmpl_content html content to prepare the email content.
		 *
		 * @return html returns the email template content
		 */
		public function outPut( $args, $tmpl_content ) {
			return $this->out_put( $args, $tmpl_content );
		}

		/**
		 * Provides the functionality to parse the email temaplte raw content.
		 *
		 * Provides the filters to parse the template content.
		 *
		 * @param array $args default arguments to replace the email template constants.
		 * @param HTML  $tmpl_content html content to prepare the email content.
		 *
		 * @return html returns the email template content
		 */
		public function out_put( $args, $tmpl_content ) {
			$tmpl_content = apply_filters(
				'eb_emailtmpl_content_before',
				array(
					'args'    => $args,
					'content' => $tmpl_content,
				)
			);

			$args         = $tmpl_content['args'];
			$tmpl_content = $tmpl_content['content'];
			$tmpl_const   = $this->get_tmpl_constant( $args );
			foreach ( $tmpl_const as $const => $val ) {
				if ( empty( $val ) ) {
					$val = '';
				}
				$tmpl_content = str_replace( $const, $val, $tmpl_content );
			}
			$tmpl_content = apply_filters(
				'eb_emailtmpl_content',
				array(
					'args'    => $args,
					'content' => $tmpl_content,
				)
			);
			$args         = $tmpl_content['args'];
			$tmpl_content = $tmpl_content['content'];
			return $tmpl_content;
		}

		/**
		 * Provides the functionality to get the values for the email temaplte constants
		 *
		 * @param array $args array of the default values for the constants to.
		 * prepare the email template contetn.
		 * @return array returns the array of the email temaplte constants with
		 * associated values for the constants
		 */
		private function get_tmpl_constant( $args ) {
			$constant = array();
			if ( isset( $args['username'] ) && $args['first_name'] && $args['last_name'] ) {
				$constant['{USER_NAME}']  = $args['username'];
				$constant['{FIRST_NAME}'] = $args['first_name'];
				$constant['{LAST_NAME}']  = $args['last_name'];
			} elseif ( is_user_logged_in() ) {
				$cur_user                 = wp_get_current_user();
				$constant['{USER_NAME}']  = $cur_user->user_login;
				$constant['{FIRST_NAME}'] = $cur_user->first_name;
				$constant['{LAST_NAME}']  = $cur_user->last_name;
			}
			$constant['{SITE_NAME}']                   = get_bloginfo( 'name' );
			$constant['{SITE_URL}']                    = "<a href='" . get_bloginfo( 'url' ) . "'>" . get_bloginfo( 'name' ) . '</a>';
			$constant['{COURSES_PAGE_LINK}']           = "<a href='" . site_url( '/courses' ) . "'>" . esc_html__( 'Courses', 'edwiser-bridge' ) . '</a>';
			$constant['{MY_COURSES_PAGE_LINK}']        = $this->get_my_courses_page_link();
			$constant['{USER_ACCOUNT_PAGE_LINK}']      = "<a href='" . \app\wisdmlabs\edwiserBridge\wdm_eb_user_account_url() . "'>" . esc_html__( 'User Account', 'edwiser-bridge' ) . '</a>';
			$constant['{WP_LOGIN_PAGE_LINK}']          = "<a href='" . $this->get_login_page_url() . "'>" . esc_html__( 'Login Page', 'edwiser-bridge' ) . '</a>';
			$constant['{MOODLE_URL}']                  = "<a href='" . $this->get_moodle_url() . "'>" . esc_html__( 'Moodle Site', 'edwiser-bridge' ) . '</a>';
			$constant['{COURSE_NAME}']                 = $this->get_course_name( $args );
			$constant['{USER_PASSWORD}']               = $this->get_user_password( $args );
			$constant['{ORDER_ID}']                    = $this->get_order_id( $args );
			$constant['{WP_COURSE_PAGE_LINK}']         = $this->get_course_page_link( $args );
			$constant['{USER_EMAIL_VERIFY_PAGE_LINK}'] = isset( $args['verify_url'] ) ? $args['verify_url'] : '';

			/**
			 * Refund Template parser.
			 *
			 * @since 1.3.0
			 */
			$constant['{ORDER_ID}']                = $this->get_order_id( $args );
			$constant['{CUSTOMER_DETAILS}']        = $this->get_customer_details( $args );
			$constant['{TOTAL_AMOUNT_PAID}']       = $this->get_amount_paid_for_order( $args );
			$constant['{CURRENT_REFUNDED_AMOUNT}'] = $this->get_refund_amount( $args );
			$constant['{TOTAL_REFUNDED_AMOUNT}']   = $this->get_total_refunded_amt( $args );
			$constant['{ORDER_REFUND_STATUS}']     = $this->get_refund_status( $args );
			$constant['{ORDER_ITEM}']              = $this->get_order_ass_items( $args );
			return apply_filters( 'eb_emailtmpl_constants_values', $constant );
		}

		/**
		 * Provides the functionality to ge the refund amount using course id
		 *
		 * @param array $args  array of default email page arguments.
		 * @return string returns the course name
		 */
		private function get_refund_amount( $args ) {
			$refund_amt = 'CURRENT_REFUND_AMOUNT';
			if ( isset( $args['refund_amount'] ) ) {
				$refund_amt = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $args, 'refund_amount', '0.00' );
			}
			return $refund_amt;
		}

		/**
		 * Prvides the functionality to ge tthe mycourses page link
		 *
		 * @return link returns the mycourses page (set in the EB settings) link.
		 */
		private function get_my_courses_page_link() {
			$general_settings   = get_option( 'eb_general' );
			$my_courses_page_id = $general_settings['eb_my_courses_page_id'];
			$url                = get_permalink( $my_courses_page_id );
			return "<a href='$url'>" . __( 'My Courses', 'edwiser-bridge' ) . '</a>';
		}

		/**
		 * Provides the login page link.
		 *
		 * @return link rerutns the link for the login page(set in the EB settings) url
		 */
		private function get_login_page_url() {
			$general_settings = get_option( 'eb_general' );
			$account_page_id  = $general_settings['eb_useraccount_page_id'];
			return get_permalink( $account_page_id );
		}

		/**
		 * Provides the functionality to get the course page link
		 *
		 * @param array $args accepts the email tempalte argumaent to prepare the email template.
		 * @return link returns the link for the emal single course page link
		 */
		private function get_course_page_link( $args ) {
			if ( isset( $args['course_id'] ) ) {
				return "<a href='" . get_post_permalink( $args['course_id'] ) . "'>" . esc_html__( 'click here', 'edwiser-bridge' ) . '</a>';
			} else {
				$url = get_site_url();
				return "<a href='" . $url . "'>" . esc_html__( 'Click here', 'edwiser-bridge' ) . '</a>';
			}
		}

		/**
		 * Provides the functionality to get the moodle site link.
		 *
		 * @return linl returns the link to the moodle site.
		 */
		private function get_moodle_url() {
			$url = get_option( 'eb_connection' );
			if ( $url ) {
				return $url['eb_url'];
			}
			return 'MOODLE_URL';
		}

		/**
		 * Provides the functionality to ge tthe course name using course id
		 *
		 * @param array $args  array of default email page arguments.
		 * @return string returns the course name
		 */
		private function get_course_name( $args ) {
			if ( isset( $args['course_id'] ) ) {
				return get_the_title( $args['course_id'] );
			}
			return 'COURSE_NAME';
		}

		/**
		 * Provides the functionality to ge tthe user accounts password
		 *
		 * @param array $args  array of default email page arguments.
		 * @return string returns the account password
		 */
		private function get_user_password( $args ) {
			if ( isset( $args['password'] ) ) {
				return $args['password'];
			}
			return 'USER_PASSWORD';
		}

		/**
		 * Provides the functionality to get the order id
		 * array $args  array of default email page arguments
		 *
		 * @param text $args args.
		 * @return string returns the order id.
		 */
		private function get_order_id( $args ) {
			return '#' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $args, 'eb_order_id', 'ORDER ID' ); // chnaged 1.4.7.
		}

		/**
		 * Returns the customer details using order id.
		 *
		 * @param type $args array of the argument.
		 * @return returns the order id if $args contains the order_id otherwise constant  CUSTOMER_DETAILS
		 */
		private function get_customer_details( $args ) {
			$customer_details = 'CUSTOMER_DETAILS';
			$order_id         = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $args, 'eb_order_id', false ); // chnaged 1.4.7.
			if ( $order_id ) {
				$order_data    = get_post_meta( $order_id, 'eb_order_options', true );
				$buyer_details = isset( $order_data['buyer_id'] ) ? get_userdata( $order_data['buyer_id'] ) : '';

				if ( ! empty( $buyer_details ) ) {
					ob_start();
					?>
					<div class='eb-order-meta-byer-details'>
						<p>
							<label><?php esc_html_e( 'Name: ', 'edwiser-bridge' ); ?></label>
							<?php echo esc_html( $buyer_details->user_login ); ?>
						</p>
						<p>
							<label><?php esc_html_e( 'Email: ', 'edwiser-bridge' ); ?></label>
							<?php echo esc_html( $buyer_details->user_email ); ?>
						</p>
					</div>
					<?php
					$customer_details = ob_get_clean();
				}
			}
			return $customer_details;
		}

		/**
		 * Returns the list of the orders associated items.
		 *
		 * @param type $args argts.
		 * @return returns the list of the orders associated items if the order_id exists otherwise prints the constant ORDER_ITEM
		 */
		private function get_order_ass_items( $args ) {
			$order_items = 'ORDER_ITEM';
			$order_id    = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $args, 'eb_order_id', false ); // chnaged 1.4.7.
			if ( $order_id ) {
				$order_data = get_post_meta( $order_id, 'eb_order_options', true );
				$course_ids = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $order_data, 'course_id', array() );
				if ( ! is_array( $course_ids ) ) {
					$course_ids = (array) $course_ids;
				}
				ob_start();
				?>
				<ul class="eb-user-order-courses">
					<?php
					foreach ( $course_ids as $course_id ) {
						?>
						<li>
						<?php
						echo esc_html( get_the_title( $course_id ) );
						?>
						</li>
						<?php
					}
					?>
				</ul>
				<?php
				$order_items = ob_get_clean();
			}
			return $order_items;
		}

		/**
		 * Returns the amount paid for the order otherwise returns the constant TOTAL_AMOUNT_PAID.
		 *
		 * @param type $args args.
		 * @return returns the amount paid for the order_id exists otherwise prints the constant TOTAL_AMOUNT_PAID
		 */
		private function get_amount_paid_for_order( $args ) {
			$amt_paid_for_order = 'TOTAL_AMOUNT_PAID';
			$order_id           = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $args, 'eb_order_id', false );
			if ( $order_id ) {
				$order_data         = get_post_meta( $order_id, 'eb_order_options', true );
				$amt_paid_for_order = \app\wisdmlabs\edwiserBridge\wdm_eb_get_current_paypal_currency_symb() . \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $order_data, 'amount_paid', '0.00' );
			}
			return $amt_paid_for_order;
		}

		/**
		 * Refund.
		 *
		 * @param type $args args.
		 * @return string
		 */
		private function get_total_refunded_amt( $args ) {
			$amt_paid_for_order = 'TOTAL_REFUNDED_AMOUNT';
			$order_id           = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $args, 'eb_order_id', false );
			if ( $order_id ) {
				$refunds = get_post_meta( $order_id, 'eb_order_refund_hist', true );
				if ( ! is_array( $refunds ) ) {
					$refunds = array();
				}
				$amt_paid_for_order = \app\wisdmlabs\edwiserBridge\wdm_eb_get_total_refund_amt( $refunds );
			}
			return $amt_paid_for_order;
		}

		/**
		 * Refund.
		 *
		 * @param type $args args.
		 */
		private function get_refund_status( $args ) {
			return \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $args, 'refunded_status', 'ORDER_REFUND_STATUS' );
		}
	}
}
