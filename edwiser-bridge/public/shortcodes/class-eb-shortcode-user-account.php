<?php
/**
 * The file that defines the user account shortcode.
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
 * Account.
 */
class Eb_Shortcode_User_Account {

	/**
	 * Get the shortcode content.
	 *
	 * @since  1.0.0
	 *
	 * @param array $atts atts.
	 *
	 * @return string
	 */
	public static function get( $atts ) {
		return Eb_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}
	/**
	 * Output the shortcode.
	 *
	 * @deprecated
	 *
	 * @since  1.0.0
	 */
	public static function getInstance() {
		return new Eb_Shortcode_User_Account();
	}

	/**
	 * Output the shortcode.
	 *
	 * @deprecated
	 *
	 * @param text $atts atts.
	 * @since  1.0.0
	 */
	public static function output( $atts ) {
		$template_loader = new Eb_Template_Loader(
			edwiser_bridge_instance()->get_plugin_name(),
			edwiser_bridge_instance()->get_version()
		);
		if ( is_user_logged_in() ) {
			self::display_user_account_page( $template_loader, $atts );
		} else {
			self::wdm_show_loagin_page( $template_loader );
		}
	}

	/**
	 * Function to display the user account page.
	 *
	 * @param string $template_loader template loader object.
	 * @param array  $atts Array of the shortcode atttributes.
	 */
	private static function display_user_account_page( $template_loader, $atts ) {
		$tmpl_data        = self::user_account( $atts );
		$eb_shortcode_obj = self::getInstance();
		extract( $tmpl_data ); // @codingStandardsIgnoreLine
		include $template_loader->eb_get_page_template( 'account/user-account.php' );
	}

	/**
	 * Functin preapres the data required for the login page.
	 *
	 * @param object $template_loader Template loader class object.
	 */
	private static function wdm_show_loagin_page( $template_loader ) {
		$general_settings    = get_option( 'eb_general' );
		$enable_registration = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $general_settings, 'eb_enable_registration', '' );
		$eb_action           = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$username            = '';
		$reg_link_args       = array( 'action' => 'eb_register' );

		if ( ! empty( $_GET['redirect_to'] ) ) {
			$reg_link_args['redirect_to'] = sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) );
		}

		if ( isset( $_GET['is_enroll'] ) && 'true' === $_GET['is_enroll'] ) {
			$reg_link_args['is_enroll'] = sanitize_text_field( wp_unslash( $_GET['is_enroll'] ) );
		}

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-login' ) ) {
			$username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		}
		$reg_link = wdm_eb_user_account_url( $reg_link_args );
		if ( 'eb_register' === $eb_action ) {
			$redirect_to       = ! empty( $_GET['redirect_to'] ) ? array( 'redirect_to' => sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) ) : array();
			$fname             = '';
			$lname             = '';
			$email             = '';
			$eb_terms_and_cond = isset( $general_settings['eb_enable_terms_and_cond'] ) && 'yes' === $general_settings['eb_enable_terms_and_cond'] && isset( $general_settings['eb_terms_and_cond'] ) ? $general_settings['eb_terms_and_cond'] : false;
			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-login' ) ) {
				$data['fname'] = isset( $_POST['firstname'] ) ? sanitize_text_field( wp_unslash( $_POST['firstname'] ) ) : '';
				$data['lname'] = isset( $_POST['lastname'] ) ? sanitize_text_field( wp_unslash( $_POST['lastname'] ) ) : '';
				$data['email'] = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
			}
		}
		/**
		 * Load the login page form.
		 */
		include $template_loader->eb_get_page_template( 'account/form-login.php' );
	}

	/**
	 * User account page.
	 *
	 * @since  1.0.0
	 *
	 * @param array $atts atts.
	 */
	private static function user_account( $atts ) {

		if ( isset( $atts['user_id'] ) && '' !== $atts['user_id'] ) {
			$user = get_user_by( 'id', $atts['user_id'] );
		} else {
			$user = wp_get_current_user();
		}
		$user_id     = $user->ID;
		$user_meta   = get_user_meta( $user_id );
		$user_avatar = get_avatar( $user_id, 125 );
		$course_args = array(
			'post_type'      => 'eb_course',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);

		$courses = array();

		// Commenting below line as we are loading user-orders everywhere $user_orders = self::get_user_orders( $user_id ).
		$user_orders = array();
		return array(
			'current_user'     => get_user_by( 'id', get_current_user_id() ),
			'user_orders'      => $user_orders,
			'order_count'      => 15,
			'user_avatar'      => $user_avatar,
			'user'             => $user,
			'user_meta'        => $user_meta,
			'enrolled_courses' => is_array( $courses ) ? array_values( $courses ) : array(),
		);
	}

	/**
	 * Get user orders.
	 *
	 * @param text $user_id user_id.
	 */
	public static function get_user_orders( $user_id ) {
		$user_orders = array();
		// get all completed orders of a user.
		$args           = array(
			'posts_per_page' => -1,
			'meta_key'       => '', // @codingStandardsIgnoreLine
			'post_type'      => 'eb_order',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'order'          => 'ASC',
		);
		$overall_orders = get_posts( $args ); // get all orders from db.

		foreach ( $overall_orders as $order_id ) {
			$order_detail = get_post_meta( $order_id, 'eb_order_options', true );

			if ( ! empty( $order_detail ) && trim( $order_detail['buyer_id'] ) === trim( $user_id ) ) {
				$user_orders[] = array(
					'eb_order_id'   => $order_id, // cahnged 1.4.7 Order Id.
					'ordered_item'  => $order_detail['course_id'],
					'billing_email' => isset( $order_detail['billing_email'] ) ? $order_detail['billing_email'] : '-',
					'currency'      => isset( $order_detail['currency'] ) ? $order_detail['currency'] : '$',
					'amount_paid'   => isset( $order_detail['amount_paid'] ) ? $order_detail['amount_paid'] : '',
					'status'        => isset( $order_detail['order_status'] ) ? $order_detail['order_status'] : '',
					'date'          => get_the_date( 'Y-m-d', $order_id ),
				);
			}
		}
		return apply_filters( 'eb_user_orders', $user_orders );
	}

	/**
	 * Account details.
	 */
	public static function save_account_details() {
		if ( self::is_update_user_profile() ) {
			$user         = new \stdClass();
			$user->ID     = (int) get_current_user_id();
			$current_user = get_user_by( 'id', $user->ID );
			if ( $user->ID > 0 ) {
				if ( isset( $_SESSION[ 'eb_msgs_' . $current_user->ID ] ) ) {
					unset( $_SESSION[ 'eb_msgs_' . $current_user->ID ] );
				}
				$posted_data = self::get_posted_data();
				$errors      = self::get_errors( $posted_data );
				if ( count( $errors ) ) {
					$_SESSION[ 'eb_msgs_' . $user->ID ] = '<p class="eb-error">' . implode( '<br />', $errors ) . '</p>';
				} else {
					// Profile updated on Moodle successfully.
					if ( self::update_wordpress_profile( $posted_data ) ) {
						$mdl_uid = get_user_meta( $user->ID, 'moodle_user_id', true );
						if ( is_numeric( $mdl_uid ) ) {
							if ( self::update_moodle_profile( $posted_data ) ) {
								$_SESSION[ 'eb_msgs_' . $user->ID ] = '<p class="eb-success">' . __( 'Account details saved successfully.', 'edwiser-bridge' ) . '</p>';
							} else {
								$_SESSION[ 'eb_msgs_' . $user->ID ] = '<p class="eb-error">' . __( 'Error in updating profile on Moodle.', 'edwiser-bridge' ) . '</p>';
							}
						} else {
							$_SESSION[ 'eb_msgs_' . $user->ID ] = '<p class="eb-success">' . __( 'Account details saved successfully.', 'edwiser-bridge' ) . '</p>';
						}
						do_action( 'eb_save_account_details', $user->ID );
					} else {
						$_SESSION[ 'eb_msgs_' . $user->ID ] = '<p class="eb-error">' . __( 'Couldn\'t update your profile! Something went wrong.', 'edwiser-bridge' ) . '</p>';
					}
				}
			}
		}
	}

	/**
	 * Update user profile.
	 */
	public static function is_update_user_profile() {
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' !== strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) ) {
			return false;
		}
		if ( empty( $_POST['action'] ) || 'eb-update-user' !== $_POST['action'] || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-update-user' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Post data.
	 */
	public static function get_posted_data() {
		if ( empty( $_POST['action'] ) || 'eb-update-user' !== $_POST['action'] || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-update-user' ) ) {
			return false;
		}

		$first_name  = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name   = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$nick_name   = isset( $_POST['nickname'] ) ? sanitize_text_field( wp_unslash( $_POST['nickname'] ) ) : '';
		$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$curr_psw    = isset( $_POST['curr_psw'] ) ? sanitize_text_field( wp_unslash( $_POST['curr_psw'] ) ) : '';
		$new_psw     = isset( $_POST['new_psw'] ) ? sanitize_text_field( wp_unslash( $_POST['new_psw'] ) ) : '';
		$confirm_psw = isset( $_POST['confirm_psw'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm_psw'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
		$country     = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
		$city        = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';

		$posted_data = array(
			'first_name'  => $first_name,
			'last_name'   => $last_name,
			'nickname'    => $nick_name,
			'email'       => $email,
			'curr_psw'    => $curr_psw,
			'new_psw'     => $new_psw,
			'confirm_psw' => $confirm_psw,
			'description' => $description,
			'country'     => $country,
			'city'        => $city,
		);
		return $posted_data;
	}

	/**
	 * Field.
	 *
	 * @deprecated since 2.0.2
	 *
	 *  @param text $fieldname fieldname.
	 * @param text $sanitize sanitize.
	 */
	public static function get_posted_field( $fieldname, $sanitize = true ) {
		$val = '';

		if ( empty( $_POST['action'] ) || 'eb-update-user' !== $_POST['action'] || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-update-user' ) ) {
			return false;
		}

		if ( isset( $_POST[ $fieldname ] ) && ! empty( $_POST[ $fieldname ] ) ) {
			$val = sanitize_text_field( wp_unslash( $_POST[ $fieldname ] ) );
			if ( $sanitize ) {
				$val = sanitize_text_field( $val );
			}
		}
		return $val;
	}

	/**
	 * Errors.
	 *
	 * @param text $posted_data posted_data.
	 */
	public static function get_errors( $posted_data ) {
		$user            = new \stdClass();
		$user->ID        = (int) get_current_user_id();
		$current_user    = get_user_by( 'id', $user->ID );
		$errors          = array();
		$required_fields = apply_filters(
			'eb_save_account_details_required_fields',
			array(
				'email' => __( 'Email Address', 'edwiser-bridge' ),
			)
		);

		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $posted_data[ $field_key ] ) ) {
				/* Translators 1: field name */
				$errors[] = sprintf( __( '%1$s is required field.', 'edwiser-bridge' ), '<strong>' . $field_name . '</strong>' ); // @codingStandardsIgnoreLine
			}
		}
		$email    = sanitize_email( $posted_data['email'] );
		$curr_psw = sanitize_user( $posted_data['curr_psw'] );
		$pass1    = sanitize_user( $posted_data['new_psw'] );
		$pass2    = sanitize_user( $posted_data['confirm_psw'] );

		if ( ! is_email( $email ) ) {
				/* Translators 1: email */
			$errors[] = sprintf( esc_html__( '%1$s is invalid email.', 'edwiser-bridge' ), '<strong>' . $email . '</strong>' ); //@codingStandardsIgnoreLine
		} elseif ( email_exists( $email ) && $email !== $current_user->user_email ) {
				/* Translators 1: email */
			$errors[] = sprintf( __( '%1$s is already exists.', 'edwiser-bridge' ), '<strong>' . $email . '</strong>' ); //@codingStandardsIgnoreLine
		}

		if ( ! empty( $curr_psw ) && empty( $pass1 ) && empty( $pass2 ) ) {
			$errors[] = __( 'Please fill out all password fields.', 'edwiser-bridge' );
		} elseif ( ! empty( $pass1 ) && empty( $curr_psw ) ) {
			$errors[] = __( 'Please enter your current password.', 'edwiser-bridge' );
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			$errors[] = __( 'Please re-enter your password.', 'edwiser-bridge' );
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			$errors[] = __( 'New passwords do not match.', 'edwiser-bridge' );
		} elseif ( ! empty( $pass1 ) && ! wp_check_password( $curr_psw, $current_user->user_pass, $current_user->ID ) ) {
			$errors[] = __( 'Your current password is incorrect.', 'edwiser-bridge' );
		}
		return $errors;
	}

	/**
	 * Moodle profile.
	 *
	 * @param text $posted_data posted_data.
	 */
	public static function update_moodle_profile( $posted_data ) {
		$user     = new \stdClass();
		$user->ID = (int) get_current_user_id();
		// Update Moodle profile.
		$mdl_uid = get_user_meta( $user->ID, 'moodle_user_id', true );
		if ( is_numeric( $mdl_uid ) ) {
			$user_data = array(
				'id'            => (int) $mdl_uid,
				'email'         => $posted_data['email'],
				'firstname'     => $posted_data['first_name'],
				'lastname'      => $posted_data['last_name'],
				'alternatename' => $posted_data['nickname'],
				'auth'          => 'manual',
				'city'          => $posted_data['city'],
				'country'       => $posted_data['country'] ? $posted_data['country'] : '',
				'description'   => $posted_data['description'],
			);

			if ( isset( $posted_data['new_psw'] ) && ! empty( $posted_data['new_psw'] ) ) {
				$user_data['password'] = $posted_data['new_psw'];
			}

			$version      = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_version();
			$user_manager = new Eb_User_Manager( 'edwiserbridge', $version );
			$response     = $user_manager->create_moodle_user( $user_data, 1 );
			if ( isset( $response['user_updated'] ) && $response['user_updated'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Profile.
	 *
	 * @param text $posted_data posted_data.
	 */
	public static function update_wordpress_profile( $posted_data ) {
		$user     = new \stdClass();
		$user->ID = (int) get_current_user_id();
		// Update WP profile.
		update_user_meta( $user->ID, 'city', $posted_data['city'] );
		update_user_meta( $user->ID, 'country', $posted_data['country'] );
		$args = array(
			'ID'          => $user->ID,
			'user_email'  => $posted_data['email'],
			'first_name'  => $posted_data['first_name'],
			'last_name'   => $posted_data['last_name'],
			'nickname'    => $posted_data['nickname'],
			'description' => $posted_data['description'],
		);
		if ( isset( $posted_data['new_psw'] ) && ! empty( $posted_data['new_psw'] ) ) {
			$args['user_pass'] = $posted_data['new_psw'];
		}
		$result = wp_update_user( $args );
		if ( is_wp_error( $result ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Navigation.
	 */
	public function get_user_account_navigation_items() {
		return apply_filters(
			'eb_user_account_labels',
			array(
				array(
					'label' => __( 'Dashboard', 'edwiser-bridge' ),
					'href'  => '',
					''      => __( 'Dashboard', 'edwiser-bridge' ),
				),
				array(
					'label' => __( 'Account Details', 'edwiser-bridge' ),
					'href'  => 'eb-my-profile',
					''      => __( 'Edit account details', 'edwiser-bridge' ),
				),
				array(
					'label' => __( 'Orders', 'edwiser-bridge' ),
					'href'  => 'eb-orders',
					''      => __( 'Course purchase history', 'edwiser-bridge' ),
				),
				array(
					'label' => __( 'My Courses', 'edwiser-bridge' ),
					'href'  => 'eb-my-courses',
					''      => __( 'My Courses', 'edwiser-bridge' ),
				),
			)
		);
	}

	/**
	 * Content.
	 *
	 * @param text $eb_active_link eb_active_link.
	 * @param text $user_orders user_orders.
	 * @param text $order_count order_count.
	 * @param text $user_avatar user_avatar.
	 * @param text $user user.
	 * @param text $user_meta user_meta.
	 * @param text $enrolled_courses enrolled_courses.
	 * @param text $template_loader template_loader.
	 */
	public function get_user_account_content( $eb_active_link, $user_orders, $order_count, $user_avatar, $user, $user_meta, $enrolled_courses, $template_loader ) {
		switch ( $eb_active_link ) {
			case '':
				$template_loader->wp_get_template(
					'account/user-data.php',
					array(
						'user'        => $user,
						'user_avatar' => $user_avatar,
					)
				);
				break;

			case 'eb-my-profile':
				$template_loader->wp_get_template(
					'account/edit-user-profile.php',
					array(
						'user_avatar'      => $user_avatar,
						'user'             => $user,
						'user_meta'        => $user_meta,
						'enrolled_courses' => $enrolled_courses,
						'template_loader'  => $template_loader,
					)
				);

				break;
			case 'eb-orders':
				// Getting user orders here.
				$user_orders = self::get_user_orders( $user->ID );

				$template_loader->wp_get_template(
					'account/user-orders.php',
					array(
						'user'             => $user,
						'user_meta'        => $user_meta,
						'enrolled_courses' => $enrolled_courses,
						'template_loader'  => $template_loader,
						'user_orders'      => $user_orders,
						'user_count'       => $order_count,
					)
				);

				break;
			case 'eb-my-courses':
				$template_loader->wp_get_template(
					'account/my-courses.php',
					array(
						'user'             => $user,
						'user_meta'        => $user_meta,
						'enrolled_courses' => $enrolled_courses,
						'template_loader'  => $template_loader,
					)
				);

				break;
			default:
				do_action( 'eb_user_account_label_content', sanitize_text_field( wp_unslash( $eb_active_link ) ) );
				break;
		}
	}
}
