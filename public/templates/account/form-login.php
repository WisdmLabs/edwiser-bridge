<?php
/**
 * Login Form
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public/templates/account
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// check if registration enabled
$general_settings = get_option( 'eb_general' );
$enable_registration = isset($general_settings['eb_enable_registration'])?$general_settings['eb_enable_registration']:''; 

?>

<?php do_action( 'eb_before_customer_login_form' ); ?>
<div id="user_login">

	<?php wdm_show_notices(); ?>

	<?php if ( !isset( $_GET['action'] ) || isset( $_GET['action'] ) && $_GET['action'] != 'eb_register' ) { ?>
		<div>

		<h2><?php _e( 'Login', 'eb-textdomain' ); ?></h2>

		<form method="post" class="login">

			<?php do_action( 'eb_login_form_start' ); ?>

			<p class="form-row form-row-wide">
				<label for="username"><?php _e( 'Username', 'eb-textdomain' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="username" id="username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
			</p>
			<p class="form-row form-row-wide">
				<label for="password"><?php _e( 'Password', 'eb-textdomain' ); ?> <span class="required">*</span></label>
				<input class="input-text" type="password" name="password" id="password" />
			</p>

			<!-- <input class="input-text" type="hidden" name="redirect_to" id="redirect_to" value="<?php echo wp_get_referer(); ?>" /> -->

			<?php do_action( 'eb_login_form' ); ?>

			<p class="form-row">
				<label for="rememberme" class="inline">
					<input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'eb-textdomain' ); ?>
				</label>
			</p>

			<p class="form-row">
				<?php wp_nonce_field( 'eb-login' ); ?>
				<input type="submit" class="button" name="login" value="<?php _e( 'Login', 'eb-textdomain' ); ?>" />
			</p>
			<p class="lost_password">
				<a href="<?php echo esc_url( wp_lostpassword_url( ) ); ?>"><?php _e( 'Lost your password?', 'eb-textdomain' ); ?></a>
			</p>
			<?php if ( $enable_registration == 'yes' ) {

					if ( ! empty( $_GET['redirect_to'] ) ) {
						$redirect_to = '&redirect_to='.$_GET['redirect_to'];
					} else {
						$redirect_to = '';
					}
			?>
				<p class="register-link">

					<a href='<?php echo esc_url( wdm_user_account_url( "?action=eb_register".$redirect_to )); ?>'><?php _e( 'Don\'t have an Account?', 'eb-textdomain' ); ?></a>
				</p>
			<?php } ?>

			<?php do_action( 'eb_login_form_end' ); ?>

		</form>

	</div>
	<?php } ?>

	<?php if ( isset( $_GET['action'] ) && $_GET['action'] == 'eb_register' && $enable_registration == 'yes' ) { ?>
	<div>

		<h2><?php _e( 'Register', 'eb-textdomain' ); ?></h2>

		<form method="post" class="register">

			<?php do_action( 'eb_register_form_start' ); ?>

			<p class="form-row form-row-wide">
				<label for="reg_firstname"><?php _e( 'First Name', 'eb-textdomain' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="firstname" id="reg_firstname" value="<?php if ( ! empty( $_POST['firstname'] ) ) echo esc_attr( $_POST['firstname'] ); ?>" required/>
			</p>

			<p class="form-row form-row-wide">
				<label for="reg_lastname"><?php _e( 'Last Name', 'eb-textdomain' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="lastname" id="reg_lastname" value="<?php if ( ! empty( $_POST['lastname'] ) ) echo esc_attr( $_POST['lastname'] ); ?>" required/>
			</p>

			<p class="form-row form-row-wide">
				<label for="reg_email"><?php _e( 'Email', 'eb-textdomain' ); ?> <span class="required">*</span></label>
				<input type="email" class="input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" required/>
			</p>

			<!-- <input class="input-text" type="hidden" name="redirect_to" id="redirect_to" value="<?php echo wp_get_referer(); ?>" /> -->

			<!-- Spam Trap -->
			<div style="<?php echo ( is_rtl() ) ? 'right' : 'left'; ?>: -999em; position: absolute;"><label for="trap"><?php _e( 'Anti-spam', 'eb-textdomain' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" /></div>

			<?php do_action( 'eb_register_form' ); ?>

			<p class="form-row">
				<?php wp_nonce_field( 'eb-register' ); ?>
				<input type="submit" class="button" name="register" value="<?php _e( 'Register', 'eb-textdomain' ); ?>" />
			</p>

			<?php
				if ( ! empty( $_GET['redirect_to'] ) ) {
					$redirect_to = '?redirect_to='.$_GET['redirect_to'];
				} else {
					$redirect_to = '';
				}
			?>
			<p class="login-link">
				<a href='<?php echo esc_url( wdm_user_account_url($redirect_to) ); ?>'><?php _e( 'Already have an account?', 'eb-textdomain' ); ?></a>
			</p>

			<?php do_action( 'eb_register_form_end' ); ?>

		</form>

	</div>
	<?php } ?>
</div>

<?php do_action( 'eb_after_customer_login_form' ); ?>
