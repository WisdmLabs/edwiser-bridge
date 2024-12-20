<?php
/**
 * Created to make plugin compatible with GDPR
 *
 * This class defines all code necessary to make plugin compatible with GDPR.
 *
 * @link       https://edwiser.org
 * @since      1.3.3
 * @package    EWdwisr Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class responsible to create plugin GDPR compatible.
 */
class Eb_Gdpr_Compatibility {

	/**
	 * Functionality to add data to the WordPress data exporter function.
	 *
	 * @param string $email email id of the user.
	 */
	public function eb_data_exporter( $email ) {
		$user             = get_user_by( 'email', $email );
		$moodle_user_id   = get_user_meta( $user->ID, 'moodle_user_id', 1 );
		$enrolled_courses = $this->get_enrolled_courses_with_date( $user->ID );
		$data             = array(
			array(
				'name'  => esc_html__( 'Course Name', 'edwiser-bridge' ),
				'value' => esc_html__( 'Enrollment Date and Time', 'edwiser-bridge' ),
			),
		);

		foreach ( $enrolled_courses as $value ) {
			array_push(
				$data,
				array(
					'name'  => $value['name'],
					'value' => $value['time'],
				)
			);
		}

		$export_items = array();
		if ( $moodle_user_id ) {
			$export_items[] = array(
				'group_id'    => 'eb_user_meta',
				'group_label' => esc_html__( 'User enrollment data', 'edwiser-bridge' ),
				'item_id'     => 'eb_user_meta',
				'data'        => $data,
			);

			// Tell core if we have more comments to work on still.
			return array(
				'data' => $export_items,
				'done' => true,
			);
		} else {
			$export_items[] = array(
				'group_id'    => 'eb_user_meta',
				'group_label' => esc_html__( 'User enrollment data', 'edwiser-bridge' ),
				'item_id'     => 'eb_user_meta',
				'data'        => array(
					array(
						'name'  => esc_html__( 'Enrollment data', 'edwiser-bridge' ),
						'value' => esc_html__( 'Not Available (Not linked to the Moodle LMS site)', 'edwiser-bridge' ),
					),
				),
			);
			return array(
				'data' => $export_items,
				'done' => true,
			);
		}
	}


	/**
	 * Functionality to get list all enrolled courses
	 *
	 * @param  text $user_id user id.
	 */
	public function get_enrolled_courses( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'moodle_enrollment';

		$enrolled_course = array();
		$result          = $wpdb->get_results( $wpdb->prepare( 'SELECT `course_id` FROM {$wpdb->prefix}moodle_enrollment  WHERE user_id = %d', $user_id ) ); // @codingStandardsIgnoreLine

		if ( ! empty( $result ) ) {
			foreach ( $result as $single_result ) {
				$enrolled_course[ $single_result->course_id ] = get_the_title( $single_result->course_id );
			}
		}
		return $enrolled_course;
	}


	/**
	 * Functionality to get list all enrolled courses
	 *
	 * @param text $user_id user id.
	 */
	public function get_enrolled_courses_with_date( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'moodle_enrollment';

		$enrolled_course = array();
		$result          = $wpdb->get_results( $wpdb->prepare( 'SELECT `course_id`, `time` FROM {$wpdb->prefix}moodle_enrollment WHERE user_id = %d', $user_id ) ); // @codingStandardsIgnoreLine

		if ( ! empty( $result ) ) {
			foreach ( $result as $single_result ) {
				$enrolled_course[ $single_result->course_id ] = array(
					'time' => $single_result->time,
					'name' => get_the_title( $single_result->course_id ),
				);
			}
		}
		return $enrolled_course;
	}

	/**
	 * Functionality to register data exporter function
	 *
	 * @param  text $exporters exporters.
	 */
	public function eb_register_my_plugin_exporter( $exporters ) {
		$exporters['edwiser-bridge'] = array(
			'exporter_friendly_name' => esc_html__( 'Edwiser Bridge Plugin', 'edwiser-bridge' ),
			'callback'               => array( $this, 'eb_data_exporter' ),
		);
		return $exporters;
	}

	/**
	 * Functionality to erase all user related data
	 *
	 * @param text $email email.
	 */
	public function eb_plugin_data_eraser( $email ) {
		global $wpdb;
		$general_settings   = get_option( 'eb_general' );
		$user               = get_user_by( 'email', $email );
		$msg                = array();
		$enrollment_manager = Eb_Enrollment_Manager::instance( edwiser_bridge_instance()->get_plugin_name(), edwiser_bridge_instance()->get_version() );
		$enrolled_courses   = $this->get_enrolled_courses( $user->ID );
		$unenrolled         = 0;
		if ( $enrolled_courses && ! empty( $enrolled_courses ) ) {
			if ( isset( $general_settings['eb_erase_moodle_data'] ) && 'yes' === $general_settings['eb_erase_moodle_data'] ) {

				$course_key = array_keys( $enrolled_courses );
				foreach ( $course_key as $value ) {
					$args = array(
						'user_id'  => $user->ID,
						'courses'  => array( $value ),
						'unenroll' => 1,
					);
					$enrollment_manager->update_user_course_enrollment( $args );
					$unenrolled = 1;
				}
			}
			if ( $unenrolled ) {
				array_push( $msg, esc_html__( 'Deleted Courses related data from the Moodle site', 'edwiser-bridge' ) );
			}

			$wpdb->get_results( $wpdb->prepare( 'DELETE FROM  {$wpdb->prefix}moodle_enrollment  WHERE user_id = %d', $user->ID ) ); // @codingStandardsIgnoreLine
			array_push( $msg, esc_html__( 'Deleted Courses related data from the WordPress site', 'edwiser-bridge' ) );
			delete_user_meta( $user->ID, 'moodle_user_id' );
			array_push( $msg, esc_html__( 'Deleted Moodle user ID', 'edwiser-bridge' ) );
		}

		return array(
			'items_removed'  => true,
			'items_retained' => false, // always false in this example.
			'messages'       => $msg, // no messages in this example.
			'done'           => 1,
		);
	}


	/**
	 * Functionality to register eraser function.
	 *
	 * @param  text $erasers erasers.
	 */
	public function eb_register_plugin_eraser( $erasers ) {
		$erasers['edwiser-bridge'] = array(
			'eraser_friendly_name' => esc_html__( 'Edwiser Bridge Plugin', 'edwiser-bridge' ),
			'callback'             => array( $this, 'eb_plugin_data_eraser' ),
		);
		return $erasers;
	}

	/**
	 * Get all privacy policy related data.
	 */
	public function eb_privacy_policy_page_data() {
		$content = apply_filters( 'eb_privacy_policy_content', $this->eb_privacy_policy_content() );

		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content( 'Edwiser Bridge', $content );
		}
	}


	/**
	 * Functionality to merge all the sections data which we want to show on the privacy policy page
	 */
	public function eb_privacy_policy_content() {
		$sections = array( esc_html__( 'User Account Creation', 'edwiser-bridge' ) => $this->eb_user_account_creation_policy() );

		$sections[ esc_html__( 'Payments', 'edwiser-bridge' ) ] = $this->eb_payment_policy();
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		if ( in_array( 'edwiser-bridge-sso/sso.php', $active_plugins, true ) ) {
			$sections[ esc_html__( 'User’s Simultaneous login and logout', 'edwiser-bridge' ) ] = $this->eb_sso_policy();
		}
		$sections = apply_filters( 'eb_policy_sections', $sections );
		apply_filters_deprecated( 'eb-policy-sections', array( $sections ), '2.0.1', 'eb_policy_sections' );
		$html = "<div class= 'wp-suggested-text'>
					<div>
						<h2>" . esc_html__( 'Edwiser', 'edwiser-bridge' ) . '</h2>
						<p>
							' . esc_html__( 'This sample language includes the basics of what personal data our site is using to integrate our site with the Moodle LMS site.', 'edwiser-bridge' ) . '
						</p>
						<p>
							' . esc_html__( 'We collect information about you and process them for the following purposes.', 'edwiser-bridge' ) . '
						</p>
					</div>';
		foreach ( $sections as $key => $value ) {
			$html .= '<div>
						<h2>
							' . $key . '
						</h2>
						' . $value . '
					</div>';
		}
		return $html;
	}

	/**
	 * Policy content of all the account creation activities.
	 */
	public function eb_user_account_creation_policy() {
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$eb_access_url  = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();

		$content = '<p>
						' . esc_html__( 'We enroll the user in the course in Moodle for which we need to create an account in Moodle below are the ways by which we create users in Moodle.', 'edwiser-bridge' ) . '
					</p>
					<p>
						' . esc_html__( 'When you purchase from us through courses page, we’ll ask you to provide information including your first name, last name and email and creates username and password for the user. We’ll use this information for purposes, such as, to:', 'edwiser-bridge' ) . '
						<ul>
							<li>' . esc_html__( 'Create a user on the ', 'edwiser-bridge' ) . '<a href = ' . $eb_access_url . '>' . esc_html__( 'Moodle site', 'edwiser-bridge' ) . '</a></li>
							<li>' . esc_html__( 'Enroll the same user into the course.', 'edwiser-bridge' ) . '</li>
						</ul>
					</p>';

		if ( in_array( 'woocommerce-integration/bridge-woocommerce.php', $active_plugins, true ) ) {
			$content .= '<p>
							' . esc_html__( 'We collect user information whenever you submit a checkout form on woocommerce store. When you submit woocommerce checkout form, we will use following information to create the user account on the Moodle site:', 'edwiser-bridge' ) . '

							<ul>
								<li>' . esc_html__( 'First Name', 'edwiser-bridge' ) . '</li>
								<li>' . esc_html__( 'Last Name', 'edwiser-bridge' ) . '</li>
								<li>' . esc_html__( 'Email', 'edwiser-bridge' ) . '</li>
								<li>' . esc_html__( 'Username', 'edwiser-bridge' ) . '</li>
								<li>' . esc_html__( 'Password', 'edwiser-bridge' ) . '</li>
							</ul>
						</p>
						<p>
							' . esc_html__( 'The collected information will be used to:', 'edwiser-bridge' ) . '
							<ul>
								' . esc_html__( 'Enroll user in the specified course.', 'edwiser-bridge' ) . '
							</ul>
						</p>';
		}
		apply_filters( 'eb_privacy_policy_user_section', $content );
		return apply_filters_deprecated( 'eb-privacy-policy-user-section', array( $content ), '2.0.1', 'eb_privacy_policy_user_section' );
	}

	/**
	 * Payments policy data.
	 */
	public function eb_payment_policy() {
		$content = '<p>
						' . esc_html__( 'We accept payments through PayPal. When processing payments, some of your data will be passed to PayPal, including information required to process or support the payment, such as the purchase total and billing information.', 'edwiser-bridge' ) . '
					</p>
					<p>
						' . esc_html__( 'Please see the', 'edwiser-bridge' ) . ' <a href = "https://www.paypal.com/us/webapps/mpp/ua/privacy-full"> ' . esc_html__( 'PayPal Privacy Policy', 'edwiser-bridge' ) . ' </a> ' . esc_html__( 'for more details.', 'edwiser-bridge' ) . '
					</p>
					<p>
						' . esc_html__( 'For more details you could read our Privacy Policy and Terms and Conditions for better understanding of our product and services.', 'edwiser-bridge' ) . '
					</p>';
		apply_filters( 'eb_privacy_policy_payments_section', $content );
		return apply_filters_deprecated( 'eb-privacy-policy-payments-section', array( $content ), '2.0.1', 'eb_privacy_policy_payments_section' );
	}

	/**
	 * Sso policy data.
	 */
	public function eb_sso_policy() {
		$content = '<p>
						We allow user to login on WordPress as well as Moodle site simultaneously if the user is linked to the Moodle site. We use Moodle user id of the user for logging into the Moodle site and vice versa. All this login and logout actions performed using very secured encoding method in PHP which is through PHP Mcrypt extension.
					</p>';
		apply_filters( 'eb_privacy_policy_sso_section', $content );
		return apply_filters_deprecated( 'eb-privacy-policy-sso-section', array( $content ), '2.0.1', 'eb_privacy_policy_sso_section' );
	}
}
