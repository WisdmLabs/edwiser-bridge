<?php
/**
 * The file that defines the user profile shortcode.
 *
 * @link       https://edwiser.org
 * @since      1.0.2
 * @deprecated 1.2.0 Use shortcode eb_user_account
 * @package    Edwiser Bridge.
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

/**
 * Profile.
 */
class Eb_Shortcode_User_Profile {


	/**
	 * Get the shortcode content.
	 *
	 * @since  1.0.2
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
	 * @since  1.0.2
	 *
	 * @param array $atts atts.
	 */
	public static function output( $atts ) {
		if ( ! is_user_logged_in() ) {
			$template_loader = new EbTemplateLoader(
				edwiser_bridge_instance()->get_plugin_name(),
				edwiser_bridge_instance()->get_version()
			);
			$template_loader->wp_get_template( 'account/form-login.php' );
		} else {
			self::user_profile( $atts );
		}
	}

	/**
	 * User Profile page.
	 *
	 * @since  1.0.2
	 *
	 * @param array $atts atts.
	 */
	public static function user_profile( $atts ) {

		if ( isset( $atts['user_id'] ) && '' !== $atts['user_id'] ) {
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

		// load profile template.
		$template_loader = new EbTemplateLoader(
			edwiser_bridge_instance()->get_plugin_name(),
			edwiser_bridge_instance()->get_version()
		);
		$template_loader->wp_get_template(
			'account/user-profile.php',
			array(
				'user_avatar'      => $user_avatar,
				'user'             => $user,
				'user_meta'        => $user_meta,
				'enrolled_courses' => $courses,
				'template_loader'  => $template_loader,
			)
		);
	}

	/**
	 * Save.
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
	 * Update.
	 */
	public static function is_update_user_profile() {
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' !== strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) ) {
			return false;
		}

		if ( empty( $_POST['action'] ) || 'eb-update-user' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-update-user' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get .
	 */
	public static function get_posted_data() {
		$posted_data = array();

		$posted_data['username']    = self::get_posted_field( 'username' );
		$posted_data['first_name']  = self::get_posted_field( 'first_name' );
		$posted_data['last_name']   = self::get_posted_field( 'last_name' );
		$posted_data['nickname']    = self::get_posted_field( 'nickname' );
		$posted_data['email']       = self::get_posted_field( 'email' );
		$posted_data['pass_1']      = self::get_posted_field( 'pass_1', false );
		$posted_data['description'] = self::get_posted_field( 'description' );
		$posted_data['country']     = self::get_posted_field( 'country' );
		$posted_data['city']        = self::get_posted_field( 'city' );

		return $posted_data;
	}

	/**
	 * FIeld.
	 *
	 * @param text $fieldname fieldname.
	 * @param text $sanitize sanitize.
	 */
	public static function get_posted_field( $fieldname, $sanitize = true ) {
		$val = '';
		if ( empty( $_POST['action'] ) || 'eb-update-user' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-update-user' ) ) {
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
	 * Error.
	 *
	 * @param text $posted_data posted_data.
	 */
	public static function get_errors( $posted_data ) {
		$user         = new \stdClass();
		$user->ID     = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );

		$errors = array();

		$required_fields = apply_filters(
			'eb_save_account_details_required_fields',
			array(
				'username' => __( 'Username', 'eb-textdomain' ),
				'email'    => __( 'Email Address', 'eb-textdomain' ),
			)
		);

		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $posted_data[ $field_key ] ) ) {
				/* Translators 1: field name */
				$errors[] = sprintf( __( '%$1s is required field.', 'eb-textdomain' ), '<strong>' . $field_name . '</strong>' );
			}
		}

		$email = sanitize_email( $posted_data['email'] );
		if ( ! is_email( $email ) ) {
			/* Translators 1: email */
			$errors[] = sprintf( __( '%$1s is invalid email.', 'eb-textdomain' ), '<strong>' . $email . '</strong>' );
		} elseif ( email_exists( $email ) && $email !== $current_user->user_email ) {
			/* Translators 1: email */
			$errors[] = sprintf( __( '%$1s is already exists.', 'eb-textdomain' ), '<strong>' . $email . '</strong>' );
		}

		$username = sanitize_user( $posted_data['username'] );
		if ( username_exists( $username ) && $username !== $current_user->user_login ) {
			/* Translators 1: User name */
			$errors[] = sprintf( __( '%$1s is already exists.', 'eb-textdomain' ), '<strong>' . $username . '</strong>' );
		}

		return $errors;
	}

	/**
	 * Update.
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

			if ( isset( $posted_data['pass_1'] ) && ! empty( $posted_data['pass_1'] ) ) {
				$user_data['password'] = $posted_data['pass_1'];
			}
			$user_data    = apply_filters( 'eb_update_moodle_profile_data', $user_data );

			$version      = wdm_edwiser_bridge_version();
			$user_manager = new EBUserManager( 'edwiserbridge', $version );
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
		$args = apply_filters( 'eb_wp_update_user_profile', $args );
		if ( isset( $posted_data['pass_1'] ) && ! empty( $posted_data['pass_1'] ) ) {
			$args['user_pass'] = $posted_data['pass_1'];
		}
		wp_update_user( $args );
	}
}
