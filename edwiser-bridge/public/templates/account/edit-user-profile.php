<?php
/**
 * The file that defines the user profile shortcode.
 *
 * @link       https://edwiser.org
 * @since      1.0.2
 * @deprecated 1.2.0 Use shortcode eb_user_account
 * @package    Edwiser Bridge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $current_user, $wp_roles;

if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'eb-update-user' ) ) {
	$username    = $current_user->user_login;
	$first_name  = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : $current_user->first_name;
	$last_name   = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : $current_user->last_name;
	$nickname    = isset( $_POST['nickname'] ) ? sanitize_text_field( wp_unslash( $_POST['nickname'] ) ) : $current_user->nickname;
	$email       = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : $current_user->user_email;
	$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : $current_user->description;
	$city        = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : $current_user->city;
	$country     = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : $current_user->country;
} else {
	$username    = $current_user->user_login;
	$first_name  = $current_user->first_name;
	$last_name   = $current_user->last_name;
	$nickname    = $current_user->nickname;
	$email       = $current_user->user_email;
	$description = $current_user->description;
	$city        = $current_user->city;
	$country     = $current_user->country;
}


?>

<section class="eb-user-info eb-edit-user-wrapper">
	<h4 class="eb-user-info-h4"><?php esc_html_e( 'Edit Account Details', 'eb-textdomain' ); ?></h4>    
	<?php
	if ( ! is_user_logged_in() ) {
		?>
		<p class="eb-warning"><?php esc_html_e( 'You must be logged in to edit your profile.', 'eb-textdomain' ); ?></p>
		<?php
	} else {
		if ( isset( $_SESSION[ 'eb_msgs_' . $current_user->ID ] ) ) {
			echo wp_kses( $_SESSION[ 'eb_msgs_' . $current_user->ID ], \app\wisdmlabs\edwiserBridge\wdm_eb_sinlge_course_get_allowed_html_tags() );
			unset( $_SESSION[ 'eb_msgs_' . $current_user->ID ] );
		}
		?>
		<form method="post" id="eb-update-profile" action="">
			<fieldset>
				<legend><?php esc_html_e( 'Account Details', 'eb-textdomain' ); ?></legend>
				<div class="eb-profile-row-block">
					<p class="eb-profile-txt-field">
						<label for="first-name"><?php esc_html_e( 'First Name', 'eb-textdomain' ); ?></label>
						<input class="text-input" name="first_name" type="text" id="first_name" value="<?php echo esc_html( $first_name ); ?>" />
					</p>
					<p class="eb-profile-txt-field">
						<label for="last-name"><?php esc_html_e( 'Last Name', 'eb-textdomain' ); ?></label>
						<input class="text-input" name="last_name" type="text" id="last_name" value="<?php echo esc_html( $last_name ); ?>" />
					</p>
				</div>
				<p class="eb-profile-txt-field">
					<label for="nickname"><?php esc_html_e( 'Nick Name', 'eb-textdomain' ); ?></label>
					<input class="text-input" name="nickname" type="text" id="nickname" value="<?php echo esc_html( $nickname ); ?>" />
				</p>

				<p class="eb-profile-txt-field">
					<label for="email"><?php esc_html_e( 'E-mail *', 'eb-textdomain' ); ?></label>
					<input class="text-input" name="email" type="email" id="email" value="<?php echo esc_html( $email ); ?>" required />
				</p>
				<?php


				do_action( 'eb_user_account_show_account_details_fields', $current_user );


				/**
				 * This will add the list of the countrys in the dropdown.
				 */
				wp_enqueue_script( 'edwiserbridge-edit-user-profile' );
				?>
				<p class="eb-profile-txt-field">
					<label for="country"><?php esc_html_e( 'Country', 'eb-textdomain' ); ?></label>
					<select name="country" id="country"></select>
					<input name="eb-selected-country" type="hidden" id="eb-selected-country" value="<?php echo esc_html( $country ); ?>" />
				</p>
				<p class="eb-profile-txt-field">
					<label for="city"><?php esc_html_e( 'City', 'eb-textdomain' ); ?></label>
					<input class="text-input" name="city" type="text" id="city" value="<?php echo esc_html( $city ); ?>" />
				</p>
				<p class="eb-profile-txt-area-field">
					<label for="description"><?php esc_html_e( 'Biographical Information', 'eb-textdomain' ); ?></label>
					<textarea name="description" id="description" rows="3" cols="50"><?php echo esc_html( $description ); ?></textarea>
				</p>
			</fieldset>
			<fieldset>
				<legend><?php esc_html_e( 'Password Change', 'eb-textdomain' ); ?></legend>
				<p class="eb-profile-password-field">
					<label for="eb_curr_psw"><?php esc_html_e( 'Current Password', 'eb-textdomain' ); ?> <span class="eb-small"><?php esc_html_e( '(Keep blank to leave unchanged)', 'eb-textdomain' ); ?></span></label>
					<input class="text-input" name="curr_psw" type="password" id="eb_curr_psw" />                
				</p>
				<p class="eb-profile-password-field">
					<label for="eb_new_psw"><?php esc_html_e( 'New Password', 'eb-textdomain' ); ?> <span class="eb-small"><?php esc_html_e( '(Keep blank to leave unchanged)', 'eb-textdomain' ); ?></span></label>
					<input class="text-input" name="new_psw" type="password" id="eb_new_psw" />
				</p>
				<p class="eb-profile-password-field">
					<label for="eb_confirm_psw"><?php esc_html_e( 'Confirm Password', 'eb-textdomain' ); ?></label>
					<input class="text-input" name="confirm_psw" type="password" id="eb_confirm_psw" />
				</p>
			</fieldset>
			<?php
			// action hook for plugin and extra fields.
			do_action( 'eb_edit_user_profile', $current_user );
			?>
			<p class="eb-small">
				<?php esc_html_e( 'Note: All fields will be updated on Moodle as well as on WordPress site.', 'eb-textdomain' ); ?>
			</p>
			<p class="eb-profile-form-submit">
				<input name="updateuser" type="submit" id="updateuser" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'eb-textdomain' ); ?>" />
				<?php wp_nonce_field( 'eb-update-user' ); ?>
				<input name="action" type="hidden" id="action" value="eb-update-user" />
			</p>
		</form>
	<?php } ?>
</section>
