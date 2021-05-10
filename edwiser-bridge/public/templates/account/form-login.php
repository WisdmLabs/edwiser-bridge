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
do_action( 'eb_before_customer_login_form' );
?>
<div id="user_login">
	<?php
	\app\wisdmlabs\edwiserBridge\wdm_eb_login_reg_show_notices();
	if ( 'eb_register' !== $eb_action ) {
		?>
		<h2>
			<?php esc_html_e( 'Login', 'eb-textdomain' ); ?>
		</h2>
		<div class="eb-wrap-login-form">
			<form method="post" class="login">
				<?php do_action( 'eb_login_form_start' ); ?>
				<p class="form-row form-row-wide">
					<label for="wdm_username">
						<?php esc_html_e( 'Username', 'eb-textdomain' ); ?>
						<span class="required">*</span>
					</label>
					<input type="text" class="input-text" placeholder="<?php esc_html_e( 'Enter user name', 'eb-textdomain' ); ?>" name="wdm_username" id="wdm_username" value="<?php echo esc_attr( $username ); ?>" />
				</p>
				<p class="form-row form-row-wide">
					<label for="wdm_password">
						<?php esc_html_e( 'Password', 'eb-textdomain' ); ?>
						<span class="required">*</span>
					</label>
					<input class="input-text" type="password" placeholder="<?php esc_html_e( 'Enter password', 'eb-textdomain' ); ?>" name="wdm_password" id="wdm_password" />
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password', 'eb-textdomain' ); ?></a>
				</p>
				<?php do_action( 'eb_login_form' ); ?>
				<p class="form-row">
					<?php wp_nonce_field( 'eb-login' ); ?>
					<label for="rememberme" class="inline">
						<input name="rememberme" type="checkbox" id="rememberme" value="forever" />
						<?php esc_html_e( 'Remember me', 'eb-textdomain' ); ?>
					</label>
				</p>
				<p>
					<input type="submit" class="eb-login-button button" name="wdm_login" value="<?php esc_html_e( 'Login', 'eb-textdomain' ); ?>" />
				</p>
				<?php
				if ( 'yes' === $enable_registration ) {
					?>
					<p class="register-link form-row">
						<a href='<?php echo esc_url( $reg_link ); ?>'>
							<?php esc_html_e( 'Don\'t have an Account?', 'eb-textdomain' ); ?>
						</a>
					</p>
					<?php
				}
				do_action( 'eb_login_form_end' );
				?>
			</form>
		</div>
		<?php
	}
	?>
	<?php
	if ( $eb_action && 'eb_register' === $eb_action && 'yes' === $enable_registration ) {
		?>
		<h2> <?php esc_html_e( 'Register', 'eb-textdomain' ); ?> </h2>
		<div class="eb-user-reg-form">
			<form method="post" class="register">
				<?php do_action( 'eb_register_form_start' ); ?>
				<p class="form-row form-row-wide">
					<label for="reg_firstname">
						<?php esc_html_e( 'First Name', 'eb-textdomain' ); ?>
						<span class="required">*</span>
					</label>
					<input type="text" class="input-text" name="firstname" id="reg_firstname" value="<?php echo esc_attr( $fname ); ?>" required/>
				</p>
				<p class="form-row form-row-wide">
					<label for="reg_lastname">
						<?php esc_html_e( 'Last Name', 'eb-textdomain' ); ?>
						<span class="required">*</span>
					</label>
					<input type="text" class="input-text" name="lastname" id="reg_lastname" value="<?php echo esc_attr( $lname ); ?>" required/>
				</p>

				<p class="form-row form-row-wide">
					<label for="reg_email">
						<?php esc_html_e( 'Email', 'eb-textdomain' ); ?>
						<span class="required">*</span>
					</label>
					<input type="email" class="input-text" name="email" id="reg_email" value="<?php echo esc_attr( $email ); ?>" required/>
				</p>
				<p class="form-row form-row-wide">
					<label for="reg_pass">
						<?php esc_html_e( 'Password', 'eb-textdomain' ); ?>
						<span class="required">*</span>
					</label>
					<input type="password" class="input-text" name="user_psw" id="reg_pass" value="" required/>
				</p>
				<p class="form-row form-row-wide">
					<label for="reg_pass_confirm">
						<?php esc_html_e( 'Confirm Password', 'eb-textdomain' ); ?>
						<span class="required">*</span>
					</label>
					<input type="password" class="input-text" name="conf_user_psw" id="reg_pass_confirm" value="" required/>
				</p>

				<?php
				if ( $eb_terms_and_cond ) {
					?>

				<p class="form-row form-row-wide">
					<input type="checkbox" class="input-text" name="reg_terms_and_cond" id="reg_terms_and_cond"  required/>
					<?php esc_html_e( 'I agree to the ', 'eb-textdomain' ); ?>
					<span style="cursor: pointer;" id="eb_terms_cond_check"> <u><?php esc_html_e( 'Terms and Conditions', 'eb-textdomain' ); ?></u></span>
				</p>

				<div class="eb-user-account-terms">
					<div id = "eb-user-account-terms-content" title="<?php esc_html_e( 'Terms and Conditions', 'eb-textdomain' ); ?>">
						<?php echo esc_html( $eb_terms_and_cond ); ?>
					</div>
				</div>
					<?php
				}
				?>


				<!-- Spam Trap -->
				<div style="<?php echo ( is_rtl() ) ? 'right' : 'left'; ?>: -999em; position: absolute;">
					<label for="trap">
						<?php esc_html_e( 'Anti-spam', 'eb-textdomain' ); ?>
					</label>
					<input type="text" name="email_2" id="trap" tabindex="-1" />
				</div>

				<?php
				do_action( 'eb_register_form' );
				?>

				<p class="form-row">
					<?php wp_nonce_field( 'eb-register' ); ?>
					<input type="submit" class="button" name="register" value="<?php esc_html_e( 'Register', 'eb-textdomain' ); ?>" />
				</p>

								<p class="login-link">
					<a href='<?php echo esc_url( \app\wisdmlabs\edwiserBridge\wdm_eb_user_account_url( $redirect_to ) ); ?>'>
						<?php esc_html_e( 'Already have an account?', 'eb-textdomain' ); ?>
					</a>
				</p>
				<?php do_action( 'eb_register_form_end' ); ?>
			</form>
		</div>
		<?php
	}
	?>
</div>
<?php
do_action( 'eb_after_customer_login_form' );
