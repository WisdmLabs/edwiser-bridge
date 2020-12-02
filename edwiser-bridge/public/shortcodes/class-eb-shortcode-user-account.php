<?php
/**
 * The file that defines the user account shortcode.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

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
	 * @since  1.0.0
	 */
	public static function getInstance() {
		return new Eb_Shortcode_User_Account();
	}

	public static function output( $atts ) {
		if ( ! is_user_logged_in() ) {
			$template_loader = new EbTemplateLoader(
				edwiser_bridge_instance()->get_plugin_name(),
				edwiser_bridge_instance()->get_version()
			);
			$template_loader->wp_get_template( 'account/form-login.php' );
		} else {
			self::user_account( $atts );
		}
	}

	/**
	 * User account page.
	 *
	 * @since  1.0.0
	 *
	 * @param array $atts atts.
	 */
	private static function user_account( $atts ) {
		extract(
			shortcode_atts(
				array(
					'user_id' => isset( $atts['user_id'] ) ? $atts['user_id'] : '',
				),
				$atts
			)
		);
		if ( '' !== $user_id ) {
			$user      = get_user_by( 'id', $user_id );
			$user_meta = get_user_meta( $user_id );
		} else {
			$user      = wp_get_current_user();
			$user_id   = $user->ID;
			$user_meta = get_user_meta( $user_id );
		}
		$user_avatar = get_avatar( $user_id, 125 );
		$course_args = array(
			'post_type'      => 'eb_course',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		// fetch courses.
		$courses = get_posts( $course_args );

		// remove course from array in which user is not enrolled.
		foreach ( $courses as $key => $course ) {
			$has_access = edwiser_bridge_instance()->enrollment_manager()->user_has_course_access( $user_id, $course->ID );
			if ( ! $has_access ) {
				unset( $courses[ $key ] );
			}
		}
		if ( is_array( $courses ) ) {
			$courses = array_values( $courses ); // reset array keys.
		} else {
			$courses = array();
		}
		// Course Purchase History.
		$user_orders         = array(); // users completed orders.
		$order_count         = 15;
				$user_orders = self::get_user_orders( $user_id );
		$template_loader     = new EbTemplateLoader(
			edwiser_bridge_instance()->get_plugin_name(),
			edwiser_bridge_instance()->get_version()
		);

		$template_loader->wp_get_template(
			'account/user-account.php',
			array(
				'current_user'     => get_user_by( 'id', get_current_user_id() ),
				'user_orders'      => $user_orders,
				'order_count'      => $order_count,
				'user_avatar'      => $user_avatar,
				'user'             => $user,
				'user_meta'        => $user_meta,
				'enrolled_courses' => $courses,
				'template_loader'  => $template_loader,
			)
		);
	}

	/**
	 * Get user orders.
	 *
	 * @param text $user_id user_id.
	 */
	public static function get_user_orders( $user_id ) {
		$user_orders = array();
		// $user_id;
		// get all completed orders of a user
		$args           = array(
			'posts_per_page' => -1,
			'meta_key'       => '',
			'post_type'      => 'eb_order',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'order'          => 'ASC',
		);
		$overall_orders = get_posts( $args ); // get all orders from db.
		foreach ( $overall_orders as $order_id ) {
			$order_detail = get_post_meta( $order_id, 'eb_order_options', true );

			if ( ! empty( $order_detail ) && $order_detail['buyer_id'] === $user_id ) {
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
					if ( self::update_moodle_profile( $posted_data ) ) {
						self::update_wordpress_profile( $posted_data );
						$_SESSION[ 'eb_msgs_' . $user->ID ] = '<p class="eb-success">' . __( 'Account details saved successfully.', 'eb-textdomain' ) . '</p>';
						do_action( 'eb_save_account_details', $user->ID );
					} else {
						$_SESSION[ 'eb_msgs_' . $user->ID ] = '<p class="eb-error">' . __( 'Couldn\'t update your profile! This might be because wrong data sent to Moodle site or a Connection Error.', 'eb-textdomain' ) . '</p>';
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
		if ( empty( $_POST['action'] ) || 'eb-update-user' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'eb-update-user' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Post data.
	 */
	public static function get_posted_data() {
		$posted_data                = array();
		$posted_data['first_name']  = self::get_posted_field( 'first_name' );
		$posted_data['last_name']   = self::get_posted_field( 'last_name' );
		$posted_data['nickname']    = self::get_posted_field( 'nickname' );
		$posted_data['email']       = self::get_posted_field( 'email' );
		$posted_data['curr_psw']    = self::get_posted_field( 'curr_psw', false );
		$posted_data['new_psw']     = self::get_posted_field( 'new_psw', false );
		$posted_data['confirm_psw'] = self::get_posted_field( 'confirm_psw', false );
		$posted_data['description'] = self::get_posted_field( 'description' );
		$posted_data['country']     = self::get_posted_field( 'country' );
		$posted_data['city']        = self::get_posted_field( 'city' );
		return $posted_data;
	}

	/**
	 * Field.
	 *
	 * @param text $fieldname fieldname.
	 * @param text $sanitize sanitize.
	 */
	public static function get_posted_field( $fieldname, $sanitize = true ) {
		$val = '';
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
				'email' => __( 'Email Address', 'eb-textdomain' ),
			)
		);

		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $posted_data[ $field_key ] ) ) {
				/* Translators 1: field name */
				$errors[] = sprintf( __( '%$1s is required field.', 'eb-textdomain' ), '<strong>' . $field_name . '</strong>' );
			}
		}
		$email    = sanitize_email( $posted_data['email'] );
		$curr_psw = sanitize_user( $posted_data['curr_psw'] );
		$pass1    = sanitize_user( $posted_data['new_psw'] );
		$pass2    = sanitize_user( $posted_data['confirm_psw'] );

		if ( ! is_email( $email ) ) {
				/* Translators 1: email */
			$errors[] = sprintf( esc_html__( '%$1s is invalid email.', 'eb-textdomain' ), '<strong>' . $email . '</strong>' );
		} elseif ( email_exists( $email ) && $email !== $current_user->user_email ) {
				/* Translators 1: email */
			$errors[] = sprintf( __( '%$1s is already exists.', 'eb-textdomain' ), '<strong>' . $email . '</strong>' );
		}

		if ( ! empty( $curr_psw ) && empty( $pass1 ) && empty( $pass2 ) ) {
			$errors[] = __( 'Please fill out all password fields.', 'eb-textdomain' );
		} elseif ( ! empty( $pass1 ) && empty( $curr_psw ) ) {
			$errors[] = __( 'Please enter your current password.', 'eb-textdomain' );
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			$errors[] = __( 'Please re-enter your password.', 'eb-textdomain' );
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			$errors[] = __( 'New passwords do not match.', 'eb-textdomain' );
		} elseif ( ! empty( $pass1 ) && ! wp_check_password( $curr_psw, $current_user->user_pass, $current_user->ID ) ) {
			$errors[] = __( 'Your current password is incorrect.', 'eb-textdomain' );
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
			$user_manager = new EBUserManager( 'edwiserbridge', EB_VERSION );
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
		wp_update_user( $args );
	}

	/**
	 * Navigation.
	 */
	public function get_user_account_navigation_items() {
		return apply_filters(
			'eb_user_account_labels',
			array(
				array(
					'label' => __( 'Dashboard', 'eb-textdomain' ),
					'href'  => '',
					''      => __( 'Dashboard', 'eb-textdomain' ),
				),
				array(
					'label' => __( 'Account Details', 'eb-textdomain' ),
					'href'  => 'eb-my-profile',
					''      => __( 'Edit account details', 'eb-textdomain' ),
				),
				array(
					'label' => __( 'Orders', 'eb-textdomain' ),
					'href'  => 'eb-orders',
					''      => __( 'Course purchase history', 'eb-textdomain' ),
				),
				array(
					'label' => __( 'My Courses', 'eb-textdomain' ),
					'href'  => 'eb-my-courses',
					''      => __( 'My Courses', 'eb-textdomain' ),
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
				do_action( 'eb_user_account_label_content', isset( $_GET['eb-active-link'] ) ? sanitize_text_field( wp_unslash( $_GET['eb-active-link'] ) ) : '' );
				break;
		}
	}
}
