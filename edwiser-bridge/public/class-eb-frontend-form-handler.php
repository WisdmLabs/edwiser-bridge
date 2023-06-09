<?php
/**
 * Handles frontend form submissions.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Form handler.
 */
class Eb_Frontend_Form_Handler {


	/**
	 * Process the login form.
	 *
	 * @throws \Exception Exception.
	 */
	public static function process_login() {
		// Proceed only if nonce is verified.
		if ( ! empty( $_POST['wdm_login'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-login' ) ) {
			try {
				$creds = array();

				$validation_error = new \WP_Error();

				$validation_error = apply_filters(
					'eb_process_login_errors',
					$validation_error,
					isset( $_POST['wdm_username'] ) ? sanitize_text_field( wp_unslash( $_POST['wdm_username'] ) ) : '',
					isset( $_POST['wdm_password'] ) ? sanitize_text_field( wp_unslash( $_POST['wdm_password'] ) ) : ''
				);

				if ( $validation_error->get_error_code() ) {
					throw new \Exception(
						'<strong>' .
						esc_html__( 'Error', 'edwiser-bridge' ) .
						':</strong> ' .
						$validation_error->get_error_message()
					);
				}

				if ( empty( $_POST['wdm_username'] ) ) {
					throw new \Exception(
						'<strong>' .
						esc_html__( 'Error', 'edwiser-bridge' ) .
						':</strong> ' .
						esc_html__( 'Username is required.', 'edwiser-bridge' )
					);
				}

				if ( empty( $_POST['wdm_password'] ) ) {
					throw new \Exception(
						'<strong>' .
						esc_html__( 'Error', 'edwiser-bridge' ) .
						':</strong> ' .
						esc_html__( 'Password is required.', 'edwiser-bridge' )
					);
				}

				// recaptcha validation.
				$general_settings = get_option( 'eb_general' );
				if ( isset( $general_settings['eb_enable_recaptcha'] ) && 'yes' === $general_settings['eb_enable_recaptcha'] && isset( $general_settings['eb_recaptcha_show_on_login'] ) && 'yes' === $general_settings['eb_recaptcha_show_on_login'] ) {
					if ( isset( $_POST['g-recaptcha-response'] ) && ! empty( $_POST['g-recaptcha-response'] ) ) {
						$secret = isset( $general_settings['eb_recaptcha_secret_key'] ) ? $general_settings['eb_recaptcha_secret_key'] : '';
						if ( '' === $secret ) {
							throw new \Exception(
								'<strong>' .
								esc_html__( 'Error', 'edwiser-bridge' ) .
								':</strong> ' .
								esc_html__( 'reCAPTCHA secret key is not set. please contact admin.', 'edwiser-bridge' )
							);
						}
						$captcha       = $_POST['g-recaptcha-response']; // @codingStandardsIgnoreLine
						$response      = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $captcha ); // @codingStandardsIgnoreLine
						$response_keys = json_decode( $response, true );
						if ( 1 !== intval( $response_keys['success'] ) ) {
							throw new \Exception(
								'<strong>' .
								esc_html__( 'Error', 'edwiser-bridge' ) .
								':</strong> ' .
								esc_html__( 'Invalid Captcha.', 'edwiser-bridge' )
							);
						}
					} else {
						throw new \Exception(
							'<strong>' .
							esc_html__( 'Error', 'edwiser-bridge' ) .
							':</strong> ' .
							esc_html__( 'Captcha is required.', 'edwiser-bridge' )
						);
					}
				}

				$creds['user_login']    = isset( $_POST['wdm_username'] ) ? sanitize_text_field( wp_unslash( $_POST['wdm_username'] ) ) : '';
				$creds['user_password'] = isset( $_POST['wdm_password'] ) ? $_POST['wdm_password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				$creds['remember']      = isset( $_POST['rememberme'] );
				$secure_cookie          = is_ssl() ? true : false;
				$user                   = wp_signon( apply_filters( 'eb_login_credentials', $creds ), $secure_cookie );

				if ( is_wp_error( $user ) ) {
					throw new \Exception( $user->get_error_message() );
				} else {
					$redirect  = self::calc_redirect();
					$redirect1 = apply_filters( 'eb_login_redirect', $redirect, $user );
					wp_safe_redirect( $redirect1 );
					exit;
				}
			} catch ( \Exception $e ) {
				\app\wisdmlabs\edwiserBridge\wdm_eb_login_reg_add_notices( $e->getMessage() );
			}
		}
	}

	/**
	 * Redirect.
	 */
	private static function calc_redirect() {
		$redirect = '';
		// Proceed only if nonce is verified.
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-login' ) ) {

			if ( ! empty( $_GET['redirect_to'] ) ) {
				$redirect = isset( $_GET['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) : '';
			} else {
				$redirect = \app\wisdmlabs\edwiserBridge\wdm_eb_user_redirect_url();
			}

			if ( self::auto_enroll( $_GET ) ) {
				$redirect = add_query_arg( 'auto_enroll', 'true', $redirect );
			}
			return $redirect;
		}
	}

	/**
	 * Process the registration form.
	 *
	 * @throws \Exception Exception.
	 */
	public static function process_registration() {
		$general_settings = get_option( 'eb_general' );
		if ( isset( $general_settings['eb_enable_terms_and_cond'] ) && 'yes' === $general_settings['eb_enable_terms_and_cond'] ) {
			if ( isset( $_POST['reg_terms_and_cond'] ) && 'on' !== $_POST['reg_terms_and_cond'] ) {
				return;
			}
		}

		// Proceed only if nonce is verified.
		if ( ! empty( $_POST['register'] ) &&
			isset( $_POST['_wpnonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-register' ) ) {
			$email         = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
			$firstname     = isset( $_POST['firstname'] ) ? sanitize_text_field( wp_unslash( $_POST['firstname'] ) ) : '';
			$lastname      = isset( $_POST['lastname'] ) ? sanitize_text_field( wp_unslash( $_POST['lastname'] ) ) : '';
			$user_psw      = isset( $_POST['user_psw'] ) ? $_POST['user_psw'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$conf_user_psw = isset( $_POST['conf_user_psw'] ) ? $_POST['conf_user_psw'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			/**
			 * Password verification is completed.
			 */

			try {
				$validation_error = new \WP_Error();
				$validation_error = apply_filters(
					'eb_process_registration_errors',
					$validation_error,
					$firstname,
					$lastname,
					$email
				);
				if ( empty( $user_psw ) || empty( $conf_user_psw ) ) {
					throw new \Exception( __( 'Password fields can not be empty.', 'edwiser-bridge' ) );
				}
				if ( ! empty( $user_psw ) && $user_psw !== $conf_user_psw ) {
					throw new \Exception( __( 'Password are not matching.', 'edwiser-bridge' ) );
				}
				if ( $validation_error->get_error_code() ) {
					throw new \Exception( $validation_error->get_error_message() );
				}

				/* Anti-spam trap */
				if ( ! empty( $_POST['email_2'] ) ) {
					throw new \Exception( __( 'Anti-spam field was filled in.', 'edwiser-bridge' ) );
				}

				// recaptcha validation.
				if ( isset( $general_settings['eb_enable_recaptcha'] ) && 'yes' === $general_settings['eb_enable_recaptcha'] && isset( $general_settings['eb_recaptcha_show_on_register'] ) && 'yes' === $general_settings['eb_recaptcha_show_on_register'] ) {
					if ( isset( $_POST['g-recaptcha-response'] ) && ! empty( $_POST['g-recaptcha-response'] ) ) {
						$secret = isset( $general_settings['eb_recaptcha_secret_key'] ) ? $general_settings['eb_recaptcha_secret_key'] : '';
						if ( '' === $secret ) {
							throw new \Exception(
								'<strong>' .
								esc_html__( 'Error', 'edwiser-bridge' ) .
								':</strong> ' .
								esc_html__( 'reCAPTCHA secret key is not set. please contact admin.', 'edwiser-bridge' )
							);
						}
						$captcha       = $_POST['g-recaptcha-response']; // @codingStandardsIgnoreLine
						$response      = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $captcha ); // @codingStandardsIgnoreLine
						$response_keys = json_decode( $response, true );
						if ( 1 !== intval( $response_keys['success'] ) ) {
							throw new \Exception(
								'<strong>' .
								esc_html__( 'Error', 'edwiser-bridge' ) .
								':</strong> ' .
								esc_html__( 'Invalid Captcha.', 'edwiser-bridge' )
							);
						}
					} else {
						throw new \Exception(
							'<strong>' .
							esc_html__( 'Error', 'edwiser-bridge' ) .
							':</strong> ' .
							esc_html__( 'Captcha is required.', 'edwiser-bridge' )
						);
					}
				}

				// added afyter.
				$role = \app\wisdmlabs\edwiserBridge\wdm_eb_default_registration_role();

				/* Create user manager class object*/
				$user_manager = new Eb_User_Manager(
					edwiser_bridge_instance()->get_plugin_name(),
					edwiser_bridge_instance()->get_version()
				);
				if ( ! empty( $_GET['redirect_to'] ) ) {
					$redirect = sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) );
				} else {
					$redirect = \app\wisdmlabs\edwiserBridge\wdm_eb_user_redirect_url();
				}
				if ( self::auto_enroll( $_GET ) ) {
					$redirect = add_query_arg( 'auto_enroll', 'true', $redirect );
				}
				$new_user = $user_manager->create_wordpress_user( sanitize_email( $email ), $firstname, $lastname, $role, $user_psw, $redirect );

				if ( is_wp_error( $new_user ) ) {
					throw new \Exception( $new_user->get_error_message() );
				}

				// add role code here.

				$user_manager->set_user_auth_cookie( $new_user );
				wp_safe_redirect( apply_filters( 'eb_registration_redirect', $redirect, $new_user ) );
				exit;
			} catch ( \Exception $e ) {
				\app\wisdmlabs\edwiserBridge\wdm_eb_login_reg_add_notices( $e->getMessage() );
			}
		}
	}


	/**
	 * Process course join for free courses.
	 *
	 * @since  1.0.0
	 */
	public static function process_free_course_join_request() {
		if ( ! isset( $_POST['course_join'] ) || ! isset( $_POST['course_id'] ) ) {
			return;
		}

		if ( ! isset( $_POST['eb_course_payment_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eb_course_payment_nonce'] ) ), 'eb_course_payment_nonce' ) ) {
			return;
		}

		$course_id = isset( $_POST['course_id'] ) ? sanitize_text_field( wp_unslash( $_POST['course_id'] ) ) : 0;

		if ( is_numeric( $course_id ) ) {
			$course = get_post( $course_id );
		} else {
			return;
		}

		/* return if post type is not eb_course */
		if ( 'eb_course' !== $course->post_type ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( empty( $user_id ) ) {
			$login_url = site_url( '/user-account?is_enroll=true&redirect_to=' . get_permalink( $course_id ) );
			wp_safe_redirect( $login_url );
			exit;
		}

		$course_meta = get_post_meta( $course_id, 'eb_course_options', true ); // get options of current course.

		/* get current user object */
		$user = get_userdata( $user_id );

		/* link existing moodle account or create a new one */
		edwiser_bridge_instance()->user_manager()->link_moodle_user( $user );

		if ( ! isset( $course_meta['course_price_type'] ) ||
				'free' === $course_meta['course_price_type'] ||
				'paid' === $course_meta['course_price_type'] &&
				empty( $course_meta['course_price'] ) ) {
			/* define args */
			$args = array(
				'user_id' => $user_id,
				'courses' => array( $course_id ),
			);

			edwiser_bridge_instance()->enrollment_manager()->update_user_course_enrollment( $args );

			$edwiser       = EdwiserBridge::instance();
			$order_manager = Eb_Order_Manager::instance( $edwiser->get_plugin_name(), $edwiser->get_version() );
			$order_data    = array(
				'buyer_id'     => $user_id,
				'course_id'    => $course_id,
				'order_status' => 'completed',
			);
			$order_manager->create_new_order( $order_data );
		}
	}

	/**
	 * Enroll.
	 *
	 * @param string $data data.
	 */
	private static function auto_enroll( $data ) {
		if ( isset( $data['is_enroll'] ) && 'true' === $data['is_enroll'] ) {
			return true;
		} else {
			return false;
		}
	}
}
