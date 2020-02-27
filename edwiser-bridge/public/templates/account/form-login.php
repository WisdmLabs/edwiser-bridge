<?php
/**
 * Login Form.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// check if registration enabled
$general_settings    = get_option('eb_general');
$enable_registration = getArrValue($general_settings, "eb_enable_registration", "");
do_action('eb_before_customer_login_form');
?>
<div id="user_login">
    <?php
    wdmShowNotices();
    $action              = getArrValue($_GET, "action", false);
    $username            = getArrValue($_POST, "username", false);
    if (!$action || $action != 'eb_register') {
        ?>
        <h2>
            <?php
            _e('Login', 'eb-textdomain');
            ?>
        </h2>
        <div class="eb-wrap-login-form">
            <form method="post" class="login">
                <?php
                do_action('eb_login_form_start');
                ?>
                <p class="form-row form-row-wide">
                    <label for="wdm_username">
                        <?php
                        _e('Username', 'eb-textdomain');
                        ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" class="input-text" placeholder="<?php _e("Enter user name", "eb-textdomain"); ?>" name="wdm_username" id="wdm_username" value="<?php echo esc_attr($username); ?>" />
                </p>
                <p class="form-row form-row-wide">
                    <label for="wdm_password">
                        <?php _e('Password', 'eb-textdomain');
                        ?>
                        <span class="required">*</span>
                    </label>
                    <input class="input-text" type="password" placeholder="<?php _e("Enter password", "eb-textdomain"); ?>" name="wdm_password" id="wdm_password" />
                </p>
                <?php
                do_action('eb_login_form');
                ?>               
                <p class="form-row">
                    <?php
                    wp_nonce_field('eb-login');
                    ?>
                    <label for="rememberme" class="inline">
                        <input name="rememberme" type="checkbox" id="rememberme" value="forever" />
                        <?php
                        _e('Remember me', 'eb-textdomain');
                        ?>
                    </label>
                </p>
                <p>
                    <input type="submit" class="eb-login-button button" name="wdm_login" value="<?php _e('Login', 'eb-textdomain'); ?>" />

                </p>
                <p class="lost_password form-row">
                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php _e('Forgot password', 'eb-textdomain'); ?></a>
                </p>
                <?php
                if ($enable_registration == 'yes') {
                    $argList = '';
                    if (!empty($_GET['redirect_to'])) {
                        $argList = '&redirect_to=' . $_GET['redirect_to'];
                    }

                    if (isset($_GET['is_enroll']) && $_GET['is_enroll'] == 'true') {
                        $argList .= '&is_enroll=' . $_GET['is_enroll'];
                    }
                    ?>
                    <p class="register-link form-row">

                        <a href='<?php echo esc_url(wdmUserAccountUrl('?action=eb_register' . $argList)); ?>'>
                            <?php
                            _e('Don\'t have an Account?', 'eb-textdomain');
                            ?>
                        </a>
                    </p>
                    <?php
                }
                do_action('eb_login_form_end');
                ?>
            </form>
        </div>
        <?php
    }
    ?>

    <?php
    if (isset($_GET['action']) && $_GET['action'] == 'eb_register' && $enable_registration == 'yes') {
        $fname = getArrValue($_POST, 'firstname', "");
        $lname = getArrValue($_POST, 'lasttname', "");
        $email = getArrValue($_POST, 'email', "");
        ?>

        <h2>
            <?php
            _e('Register', 'eb-textdomain');
            ?>
        </h2>
        <div class="eb-user-reg-form">
            <form method="post" class="register">
                <?php
                do_action('eb_register_form_start');
                ?>
                <p class="form-row form-row-wide">
                    <label for="reg_firstname">
                        <?php
                        _e('First Name', 'eb-textdomain');
                        ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" class="input-text" name="firstname" id="reg_firstname" value="<?php echo esc_attr($fname); ?>" required/>
                </p>
                <p class="form-row form-row-wide">
                    <label for="reg_lastname">
                        <?php
                        _e('Last Name', 'eb-textdomain');
                        ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" class="input-text" name="lastname" id="reg_lastname" value="<?php echo esc_attr($lname); ?>" required/>
                </p>

                <p class="form-row form-row-wide">
                    <label for="reg_email">
                        <?php
                        _e('Email', 'eb-textdomain');
                        ?>
                        <span class="required">*</span>
                    </label>
                    <input type="email" class="input-text" name="email" id="reg_email" value="<?php echo esc_attr($email); ?>" required/>
                </p>

                <?php
                if (isset($general_settings['eb_enable_terms_and_cond']) && $general_settings['eb_enable_terms_and_cond'] == "yes" && isset($general_settings['eb_terms_and_cond'])) {
                    ?>

                <p class="form-row form-row-wide">
                    <input type="checkbox" class="input-text" name="reg_terms_and_cond" id="reg_terms_and_cond"  required/>
                    <?php _e("I agree to the ", "eb-textdomain"); ?>
                    <span style="cursor: pointer;" id="eb_terms_cond_check"> <u><?php _e("Terms and Conditions", "eb-textdomain"); ?></u></span>
                </p>

                <div class="eb-user-account-terms">
                    <div id = "eb-user-account-terms-content" title="<?php _e("Terms and Conditions", "eb-textdomain")?>">
                        <?=
                        $general_settings['eb_terms_and_cond'];
                        ?>
                    </div>
                </div>

                    <?php
                }
                ?>


                <!-- Spam Trap -->
                <div style="<?php echo (is_rtl()) ? 'right' : 'left'; ?>: -999em; position: absolute;">
                    <label for="trap">
                        <?php
                        _e('Anti-spam', 'eb-textdomain');
                        ?>
                    </label>
                    <input type="text" name="email_2" id="trap" tabindex="-1" />
                </div>

                <?php
                do_action('eb_register_form');
                ?>

                <p class="form-row">
                    <?php
                    wp_nonce_field('eb-register');
                    ?>
                    <input type="submit" class="button" name="register" value="<?php _e('Register', 'eb-textdomain'); ?>" />
                </p>

                <?php
                if (!empty($_GET['redirect_to'])) {
                    $redirect_to = '?redirect_to=' . $_GET['redirect_to'];
                } else {
                    $redirect_to = '';
                }
                ?>
                <p class="login-link">
                    <a href='<?php echo esc_url(wdmUserAccountUrl($redirect_to)); ?>'>
                        <?php
                        _e('Already have an account?', 'eb-textdomain');
                        ?>
                    </a>
                </p>

                <?php
                do_action('eb_register_form_end');
                ?>
            </form>
        </div>
        <?php
    }
    ?>
</div>
<?php
do_action('eb_after_customer_login_form');
