<?php
/**
 * This class defines all code necessary manage purchase of courses & payment handling.
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
 * Edwiser payment manager.
 */
class Eb_Payment_Manager {
	/**
	 * Consructor.
	 */
	public function __construct() {
		// craeate payment button shortcode.
		add_shortcode( 'eb_payment_buttons', array( $this, 'eb_payment_buttons_shortcode' ) );
	}

	/**
	 * Rewrite rules to create paypal endpoint for IPN notification.
	 *
	 * @since  1.0.0
	 *
	 * @param array $wp_rewrite WordPress rewrite rules array.
	 */
	public function paypal_rewrite_rules( $wp_rewrite ) {
		$wp_rewrite->rules = array_merge(
			array( 'eb/paypal-notify' => 'index.php?eb=paypal-notify' ),
			$wp_rewrite->rules
		);
	}

	/**
	 * Add query vars for paypal endpoint.
	 *
	 * @since  1.0.0
	 * @param text $vars vars.
	 */
	public function add_query_vars( $vars ) {
		return array_merge( array( 'eb' ), $vars );
	}

	/**
	 * Handles paypal IPN request and call functions accordingly.
	 *
	 * @since  1.0.0
	 *
	 * @param object $_wp global wp object.
	 */
	public function parse_ipn_request( $_wp ) {
		if ( array_key_exists( 'eb', $_wp->query_vars ) && 'paypal-notify' === $_wp->query_vars['eb'] ) {
			require_once 'class-eb-ipn.php';
			$eb_ipn = new Eb_Ipn();
			$eb_ipn->process_ipn();
		}
	}

	/**
	 * Display access this course button on single course page.
	 * displayed for courses already purchased by user.
	 *
	 * @since      1.0.0
	 *
	 * @param int $course_id id of course.
	 */
	public static function access_course_button( $course_id ) {
		$button = '';
		if ( is_numeric( $course_id ) ) {
			$course            = get_post( $course_id );
			$moodle_course_id  = edwiser_bridge_instance()->course_manager()->get_moodle_course_id( $course_id );
			$access_course_url = '';
			$access_button     = '';
			$eb_access_url     = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();

			if ( '' !== $moodle_course_id ) {
				$access_course_url = $eb_access_url . '/course/view.php?id=' . $moodle_course_id;
				if ( empty( $access_course_url ) ) {
					$access_button = '';
				} else {
					if ( ! strpos( $access_course_url, '://' ) ) {
						$access_course_url = 'http://' . $access_course_url;
					}

					$access_button = '<div class="eb_join_button">
					<a class="wdm-btn eb_primary_btn button button-primary et_pb_button et_pb_contact_submit" href="' . $access_course_url . '" id="wdm-btn">' .
					esc_html__( 'Access Course', 'edwiser-bridge' ) . '</a></div>';
				}

				$access_params = array(
					'access_course_url' => $access_course_url,
					'post'              => $course,
				);

				$button = apply_filters( 'eb_course_access_button', $access_button, $access_params );

				// Compatibility with SSO plugin.
				$button = str_replace( 'class="wdm-btn"', 'class="wdm-btn eb_primary_btn button button-primary et_pb_button et_pb_contact_submit"', $button );

				$user_id           = get_current_user_id();
				$is_user_suspended = \app\wisdmlabs\edwiserBridge\wdm_eb_get_user_suspended_status( $user_id, $course_id );

				if ( $is_user_suspended ) {
					$button = str_replace( 'Access Course', 'Suspended', $button );
				}
			}
		}
		return $button;
	}

	/**
	 * Function to check the value.
	 *
	 * @param text $array array.
	 * @param text $var var.
	 * @param text $value value.
	 * @param text $default default.
	 */
	protected static function check_array_value_set( $array, $var, $value, $default = '' ) {
		if ( 'eb_paypal_sandbox' === $var && isset( $array[ $var ] ) ) {
				$default = ( 'no' === $array[ $var ] ) ? '0' : '1';
		}
		if ( isset( $array[ $var ] ) ) {
			$default = $array[ $var ];
		}
		unset( $value );
		return $default;
	}

	/**
	 * Display take this course button on single course page.
	 *
	 * @since  1.0.0
	 *
	 * @param int $course_id Description.
	 *
	 * @return string take course button html.
	 */
	public static function take_course_button( $course_id ) {
		$take_course_button = '';
		if ( is_numeric( $course_id ) ) {
			$course = get_post( $course_id );

			// Return if not post type is not eb_course.
			if ( 'eb_course' === $course->post_type ) {
				$course_meta       = get_post_meta( $course_id, 'eb_course_options', true );
				$course_price_type = self::check_array_value_set(
					$course_meta,
					'course_price_type',
					'course_price_type',
					'free'
				);
				$course_price      = self::check_array_value_set(
					$course_meta,
					'course_price',
					'course_price',
					0
				);
				$closed_button_url = self::check_array_value_set(
					$course_meta,
					'course_closed_url',
					'course_closed_url',
					'#'
				);
				$payment_options   = get_option( 'eb_paypal' );

				$paypal_email     = self::check_array_value_set(
					$payment_options,
					'eb_paypal_email',
					'eb_paypal_email',
					''
				);
				$paypal_currency  = self::check_array_value_set(
					$payment_options,
					'eb_paypal_currency',
					'eb_paypal_currency',
					'USD'
				);
				$paypal_country   = self::check_array_value_set(
					$payment_options,
					'eb_paypal_country_code',
					'eb_paypal_country_code',
					'US'
				);
				$paypal_returnurl = self::check_array_value_set(
					$payment_options,
					'eb_paypal_return_url',
					'eb_paypal_return_url',
					site_url()
				);
				$paypal_notifyurl = self::check_array_value_set(
					$payment_options,
					'eb_paypal_notify_url',
					'eb_paypal_notify_url',
					''
				);
				$paypal_sandbox   = self::check_array_value_set(
					$payment_options,
					'eb_paypal_sandbox',
					'eb_paypal_sandbox',
					'yes'
				);
				if ( ! is_user_logged_in() && 'closed' === $course_price_type ) {
					$closed_button = '';
					if ( ! empty( $closed_button_url ) ) {
						if ( ! strpos( $closed_button_url, '://' ) ) {
							$closed_button_url = 'http://' . $closed_button_url;
						}
						$take_course_button = '<div class="eb_join_button">
						<a class="wdm-btn eb_primary_btn button button-primary et_pb_button et_pb_contact_submit" href="' . $closed_button_url . '" id="wdm-btn">' .
						esc_html__( 'Take this Course', 'edwiser-bridge' ) . '</a></div>';
					}
					$closed_params      = array(
						'closed_button_url' => $closed_button_url,
						'post'              => $course,
					);
					$take_course_button = apply_filters( 'eb_course_closed_button', $take_course_button, $closed_params );
				} elseif ( ! is_user_logged_in() ) {
					$url_args           = array(
						'redirect_to' => get_permalink( $course_id ),
						'is_enroll'   => 'true',
					);
					$login_url          = \app\wisdmlabs\edwiserBridge\wdm_eb_user_account_url( $url_args );
					$take_course_button = '<div class="eb_join_button">
					<a class="wdm-btn eb_primary_btn button button-primary et_pb_button et_pb_contact_submit" href="' . $login_url . '" id="wdm-btn">' .
					esc_html__( 'Take this Course', 'edwiser-bridge' ) . '</a></div>';

					$take_course_button = apply_filters( 'eb_course_login_button', $take_course_button, $login_url );
				} else {
					// get current user id.
					$user_id = get_current_user_id();

					/*
					* Handle take course button in case user already has course access or course access is in suspended state.
					* Now we are showing take this course button for suspended user courses.
					*/
					$is_user_suspended = \app\wisdmlabs\edwiserBridge\wdm_eb_get_user_suspended_status( $user_id, $course_id );
					if ( edwiser_bridge_instance()->enrollment_manager()->user_has_course_access( $user_id, $course_id ) && ! $is_user_suspended ) {
						return '';
					}

					if ( ! empty( $course_price_type ) ) {
						if ( 'closed' === $course_price_type ) { // closed course button.
							if ( empty( $closed_button_url ) ) {
								$take_course_button = '';
							} else {
								if ( ! strpos( $closed_button_url, '://' ) ) {
									$closed_button_url = 'http://' . $closed_button_url;
								}
								$take_course_button = '<div class="eb_join_button">
								<a class="wdm-btn eb_primary_btn button button-primary et_pb_button et_pb_contact_submit" href="' . $closed_button_url . '" id="wdm-btn">' .
								esc_html__( 'Take this Course', 'edwiser-bridge' ) . '</a></div>';
							}
							$closed_params      = array(
								'closed_button_url' => $closed_button_url,
								'post'              => $course,
							);
							$take_course_button = apply_filters( 'eb_course_closed_button', $take_course_button, $closed_params );
						} elseif ( 'free' === $course_price_type || ( 'paid' === $course_price_type && empty( $course_price ) ) ) { // free course button.

							$take_course_button = '<div class="eb_join_button"><form method="post">
											<input type="hidden" value="' . $course->ID . '" name="course_id">
											<input type="submit"
											value="' . esc_html__( 'Take this Course', 'edwiser-bridge' ) . '"
											name="course_join" class="wdm-btn eb_primary_btn button button-primary et_pb_button et_pb_contact_submit" id="wdm-btn">
								
											' . wp_nonce_field( 'eb_course_payment_nonce', 'eb_course_payment_nonce' ) . '
										</form></div>';
							$take_course_button = apply_filters( 'eb_course_free_button', $take_course_button, $course->ID );
						} elseif ( ! empty( $course_price ) && 'paid' === $course_price_type ) { // paid course button.
							require_once 'enhanced-paypal-shortcodes.php';

							$paypal_button = '';
							if ( ! empty( $paypal_email ) ) {
								$paypal_button  = wptexturize(
									do_shortcode(
										"[paypal type='paynow'
										amount='{$course_price}'
										sandbox='{$paypal_sandbox}'
										email='{$paypal_email}'
										itemno='{$course->ID}'
										name='{$course->post_title}'
										noshipping='1' nonote='1'
										qty='1' currencycode='{$paypal_currency}'
										rm='2' notifyurl='{$paypal_notifyurl}'
										returnurl='{$paypal_returnurl}'
										scriptcode='scriptcode' imagewidth='100px'
										pagestyle='paypal' lc='{$paypal_country}'
										cbt='" . esc_html__( 'Complete Your Purchase', 'edwiser-bridge' ) .
												"' custom='" . $user_id . "']"
									)
								);
								$payment_params = array(
									'price' => $course_price,
									'post'  => $course,
								);

								$payment_buttons = apply_filters( 'eb_course_payment_button', $paypal_button, $payment_params );

								if ( ! empty( $payment_buttons ) ) {
									$take_course_button = '<div class="eb_join_button">' . $payment_buttons . '</div>';
								}
							}
						}
					} else {
						$not_purchasable    = apply_filters(
							'eb_course_not_purchasable_notice',
							esc_html__( 'Course Not Available', 'edwiser-bridge' )
						);
						$take_course_button = '<div class="eb_join_button course-not-available"><p>' . $not_purchasable . '</p></div>';
					}
				}
			}
		}
		return $take_course_button;
	}
}
