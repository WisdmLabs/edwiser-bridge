<?php
namespace app\wisdmlabs\edwiserBridge;

/*
 * EDW General Settings
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Settings_General' ) ) :

	/**
	 * Eb_Settings_General.
	 */
	class Eb_Settings_General extends EBSettingsPage {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'general';
			$this->label = __( 'General', 'eb-textdomain' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
			add_action( 'eb_settings_save_' . $this->_id, array( $this, 'save' ) );
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.0.0
		 *
		 * @return array
		 */
		public function get_settings() {
			$settings = apply_filters(
				'eb_general_settings',
				array(
					array(
						'title' => __( 'General Options', 'eb-textdomain' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_options',
					),
					array(
						'title'    => __( 'Enable Registration', 'eb-textdomain' ),
						'desc'     => __( 'Enable user registration', 'eb-textdomain' ),
						'id'       => 'eb_enable_registration',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'User Account Page', 'eb-textdomain' ),
						'desc'     => '<br/>' . sprintf(
							__( 'Select user account page here. Default page is %s ', 'eb-textdomain' ),
							'<a href="' . esc_url( site_url( '/user-account' ) ) . '">' . __( 'User Account', 'eb-textdomain' ) . '</a>'
						),
						'id'       => 'eb_useraccount_page_id',
						'type'     => 'single_select_page',
						'default'  => '',
						'css'      => 'min-width:300px;',
						'args'     => array(
							'show_option_none'  => __( 'Select a page', 'eb-textdomain' ),
							'option_none_value' => '',
						),
						'desc_tip' => __( 'This sets the user account page, where user can see his/her purchase history.', 'eb-textdomain' ),
					),
					array(
						'title'    => __( 'Select Role', 'eb-textdomain' ),
						'desc'     => '<br/>' .
							__( 'Select default role for users on registration from User Account Page.', 'eb-textdomain' ),
						'id'       => 'eb_default_role',
						'type'     => 'select',
						'default'  => __( 'Select Role', 'eb-textdomain' ),
						'css'      => 'min-width:300px;',
						'options'     => get_all_wp_roles(),
						'desc_tip' => __('Select default role for users on registration from User Account Page.', 'eb-textdomain'),
					),
					array(
						'title'    => __( 'Moodle Language Code', 'eb-textdomain' ),
						'desc'     => __( 'Enter language code which you get from moodle language settings.', 'eb-textdomain' ),
						'id'       => 'eb_language_code',
						'default'  => 'en',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'desc_tip' => true,
					),
					array(
						'title'    => __( 'Redirect to My Courses', 'eb-textdomain' ),
						'desc'     => sprintf(
							__( 'Redirect user to the My Courses page on %s from the %s page.', 'eb-textdomain' ),
							'<strong>' . __( 'Login / Registration', 'eb-textdomain' ) . '</strong>',
							'<a href="' . esc_url( site_url( '/user-account' ) ) . '">' . __( 'User Account', 'eb-textdomain' ) . '</a>'
						),
						__( 'Redirect user to the My Courses page on login and registration', 'eb-textdomain' ),
						'id'       => 'eb_enable_my_courses',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'My Courses Page', 'eb-textdomain' ),
						'desc'     => '<br/>' . sprintf(
							__( 'Select my courses page here. Default page is %s ', 'eb-textdomain' ),
							'<a href="' . esc_url( site_url( '/eb-my-courses' ) ) . '">' . __( 'My Courses', 'eb-textdomain' ) . '</a>'
						),
						'id'       => 'eb_my_courses_page_id',
						'type'     => 'single_select_page',
						'default'  => '',
						'css'      => 'min-width:300px;',
						'args'     => array(
							'show_option_none'  => __( 'Select a page', "eb-textdomain" ),
							'option_none_value' => '',
						),
						'desc_tip' => __( "This sets 'My Courses' page, where the user can see all his purchased courses and access them directly. You have to use this shortcode [eb_my_courses] to create this page.", 'eb-textdomain' ),
					),
					array(
						'title'    => __( 'Empty My courses Page Redirect Link', 'eb-textdomain' ),
						'desc'     => __( 'Enter the link to where you want to redirect user from My Courses page when no course is enrolled if empty then will be redirected to the courses page', 'eb-textdomain' ),
						'id'       => 'eb_my_course_link',
						'default'  => '',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'desc_tip' => true,
					),
					array(
						'title'    => __( 'Max number of courses in a row on the courses page', 'eb-textdomain' ),
						'desc'     => '',
						'id'       => 'courses_per_row',
						'type'     => 'courses_per_row',
						'default'  => '',
						'css'      => '',
						'desc_tip' =>
						__( 'This setting will be applicable only on the `/courses` page template', 'eb-textdomain' ),
					),
					array(
						'title'    => __( 'Erase associated Moodle data from Moodle site', 'eb-textdomain' ),
						'desc'     => __( 'Erase associated Moodle data from Moodle site on erase personal data of wordpress site', 'eb-textdomain' ),
						'id'       => 'eb_erase_moodle_data',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array('type' => 'sectionend', 'id' => 'general_options'),
					/*array(
						'title' => __( 'User Enrollment', 'eb-textdomain' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_user_enrollment',
					),
					array(
						'title'    => __( 'On course Expiration', 'eb-textdomain' ),
						'desc'     => '<br/>' . __( 'Select an action to perform on course access expiration.', 'eb-textdomain' ),
						'id'       => 'eb_sitewide_course_expire_action',
						'type'     => 'select',
						'default'  => '',
						'css'      => 'min-width:300px;',
						'options'  => array(
                            'unenroll'   => 'Unenroll',
                            'suspend'    => 'Suspend',
                            'do-nothing' => 'Do Nothing',
                            ),
                        'args'    => array(
                            'show_option_none'  =>__( '- Select a page -', 'eb-textdomain' ),
                            'option_none_value' => '',
                        ),
						'desc_tip' => __( "Select an action to perform on course access expiration.", 'eb-textdomain' ),
					),
					array('type' => 'sectionend', 'id' => 'general_user_enrollment'),*/
					array(
						'title' => __( 'Privacy Policy', 'eb-textdomain' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_privacy_policy',
					),
					array(
						'title'    => __( 'Enable terms and conditions', 'eb-textdomain' ),
						'desc'     => __( 'Check this to use terms and conditions checkbox on the user-account page.', 'eb-textdomain' ),
						'id'       => 'eb_enable_terms_and_cond',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Terms and Conditions', 'eb-textdomain' ),
						'desc'     => __(
							'Please enter the Terms and Conditions you want to show on user-account page.',
							'eb-textdomain'
						),
						'id'       => 'eb_terms_and_cond',
						'default'  => '',
						'type'     => 'textarea',
						'css'      => 'min-width:300px; min-height: 110px;',
						'desc_tip' => true,
					),
					array('type' => 'sectionend', 'id' => 'general_privacy_policy'),
					array(
						'title' => __( 'Recommended Courses Settings', 'eb-textdomain' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_recommended_options',
					),
					array(
						'title'    => __( 'Show Recommended Courses', 'eb-textdomain' ),
						'desc'     => sprintf( __( 'Show recommended courses on eb-my-courses page.', 'eb-textdomain' ) ),
						'id'       => 'eb_enable_recmnd_courses',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Show Default Recommended Courses', 'eb-textdomain' ),
						'desc'     => sprintf( __( 'Show category wise selected recommended courses on eb-my-courses page.', 'eb-textdomain' ) ),
						'id'       => 'eb_show_default_recmnd_courses',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Select Courses', 'eb-textdomain' ),
						'desc'     => '<br/>' . sprintf( __( 'Select courses to show in custom courses in recommended course section.', 'eb-textdomain' ) ),
						'id'       => 'eb_recmnd_courses',
						'type'     => 'multiselect',
						'default'  => '',
						'options'  => get_all_eb_sourses(),
						'desc_tip' => '',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'general_recommended_options',
					),
					array(
						'title' => __( 'Refund Notification Settings', 'eb-textdomain' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'refund_options',
					),
					array(
						'title'    => __( 'Notify Admin', 'eb-textdomain' ),
						'desc'     => sprintf( __( 'Notify admin users on refund.', 'eb-textdomain' ) ),
						'id'       => 'eb_refund_mail_to_admin',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Notification Email', 'eb-textdomain' ),
						'desc'     => '<br/>' . sprintf( __( 'Email address to send refund notification.', 'eb-textdomain' ) ),
						'id'       => 'eb_refund_mail',
						'type'     => 'text',
						'default'  => '',
						'desc_tip' => __( 'Specify email address to send refund notification, otherwise keep it blank.', 'eb-textdomain' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'refund_options',
					),
					array(
						'title' => __( 'Usage Tracking', 'eb-textdomain' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'refund_options',
					),
					array(
						'title'    => __( 'Allow Usage Tracking', 'eb-textdomain' ),
						'desc'     => sprintf( __( 'This will help us in building more useful functionalities for you.', 'eb-textdomain' ) ),
						'id'       => 'eb_usage_tracking',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
                        'type' => 'cust_html',
                        'html' => $this->getPopupCode()
                    ),
					array(
						'type' => 'sectionend',
						'id'   => 'general_options',
					),

				)
			);
			return apply_filters( 'eb_get_settings_' . $this->_id, $settings );
		}



		private function getPopupCode()
        {
            ob_start(); ?>
            <div id="dialog-tnc" style="display:none;">
                <p>Here is an overview of the different data collected by Edwiser products and why it will be beneficial for the Edwiser community.</p>
                <div>
                    <h3>Goal</h3>
                    <ul>
                        <li>To generate statistics about the usage of Edwiser products and its various features.</li>
                        <li>This knowledge will help us improve those features that are popularly used by the community.</li>
                        <li>In turn, help in catering to Edwiser communities needs in a better way.</li>
                        <li>Provide future updates to existing products after taking into consideration the various WordPress & Moodle environments of Edwiser users.</li>
                        <li>This information also be used to better debugging and the roll-out of the zero-error product</li>
                    <ul>
                </div>
                <div>
                    <h3>Things we would NEVER do</h3>
                    <ul>
                        <li>Edwiser would never collect any personal or sensitive information like email address, user results, etc.</li>
                        <li>Nor would we collect any type of information that could expose the personal information of you or your students,</li>
                    </ul>
                </div>
                <div>
                    <h3>Data Collected during this process </h3>
                    <ul>
                        <li>The data is automatically gathered unless disabled within the product.</li>
                        <li>All the data points mentioned here may not be included as part of all Edwiser products some of these are product specific. The ones which are specific to the product will be explicitly mentioned below.</li>
                    </ul>
                </div>
                <h4> The Data collected primarily falls under the following categories:</h4>
                <div>
                    <h3>Site Details</h3>
                    <ul>
                        <li>Information like Edwiser plugin settings, Site URL, Moodle Version, Active WordPress theme, Active Moodle Theme, Active WordPress plugins, Active Moodle plugins, Total Courses, Categories & Users, etc.</li>
                        <li>This information helps us understand the WordPress and Moodle environment used by Edwiser users and accordingly develop solutions that would work well in these environments.</li>
                    </ul>
                </div>
                <div>
                    <h3>Debug</h3>
                    <p>Many times we end up losing a lot of time when it comes to resolving issues that arise on the sites of a few WordPress and Moodle users.</p>
                    <p>Usually, these WordPress and Moodle sites are hosted on a shared hosting service, and without the necessary information about the server doing any debugging could cause more issues.</p>
                    <p>To reduce the time lost in debugging an issue and to always provide a stable solution to you we need the following details,</p>
                    <ul>
                        <li><i>Installed Plugins:</i> To check whether 3rd party plugins are causing any conflicts with Edwiser products.</li>
                        <li><i>Product Settings:</i> To understand the various features used by Edwiser users,</li>
                    </ul>
                </div>
            </div>
        <?php
            return ob_get_clean();
        }





		/**
		 * Save settings.
		 *
		 * @since  1.0.0
		 */
		public function save() {
			$settings = $this->get_settings();
			EbAdminSettings::save_fields( $settings );
		}
	}
endif;

return new Eb_Settings_General();
