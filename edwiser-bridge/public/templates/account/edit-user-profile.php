<?php
global $current_user, $wp_roles;

$username    = $current_user->user_login;
$first_name  = getArrValue($_POST, "first_name", $current_user->first_name);
$last_name   = getArrValue($_POST, "last_name", $current_user->last_name);
$nickname    = getArrValue($_POST, "nickname", $current_user->nickname);
$email       = getArrValue($_POST, "email", $current_user->user_email);
$description = getArrValue($_POST, "description", $current_user->description);
$city        = getArrValue($_POST, "city", $current_user->city);
$country     = getArrValue($_POST, "country", $current_user->country);
?>

<section class="eb-user-info eb-edit-user-wrapper">
    <h4 class="eb-user-info-h4"><?php _e('Edit Account Details', 'eb-textdomain'); ?></h4>    
    <?php
    if (!is_user_logged_in()) {
        ?>
        <p class="eb-warning"><?php _e('You must be logged in to edit your profile.', 'eb-textdomain'); ?></p>
        <?php
    } else {
        if (isset($_SESSION['eb_msgs_' . $current_user->ID])) {
            echo $_SESSION['eb_msgs_' . $current_user->ID];
            unset($_SESSION['eb_msgs_' . $current_user->ID]);
        }
        ?>
        <form method="post" id="eb-update-profile" action="#<?php // echo esc_url(add_query_arg('eb_action', 'edit-profile', get_permalink())); ?>">
            <fieldset>
                <legend><?php _e('Account Details', 'eb-textdomain'); ?></legend>
                <div class="eb-profile-row-block">
                    <p class="eb-profile-txt-field">
                        <label for="first-name"><?php _e('First Name', 'eb-textdomain'); ?></label>
                        <input class="text-input" name="first_name" type="text" id="first_name" value="<?php echo $first_name; ?>" />
                    </p>
                    <p class="eb-profile-txt-field">
                        <label for="last-name"><?php _e('Last Name', 'eb-textdomain'); ?></label>
                        <input class="text-input" name="last_name" type="text" id="last_name" value="<?php echo $last_name; ?>" />
                    </p>
                </div>
                <p class="eb-profile-txt-field">
                    <label for="nickname"><?php _e('Nick Name', 'eb-textdomain'); ?></label>
                    <input class="text-input" name="nickname" type="text" id="nickname" value="<?php echo $nickname; ?>" />
                </p>

                <p class="eb-profile-txt-field">
                    <label for="email"><?php _e('E-mail *', 'eb-textdomain'); ?></label>
                    <input class="text-input" name="email" type="email" id="email" value="<?php echo $email; ?>" required />
                </p>
                <?php


                do_action('eb_user_account_show_account_details_fields', $current_user);


                /**
                 * This will add the list of the countrys in the dropdown.
                 */
                wp_enqueue_script('edwiserbridge-edit-user-profile');
                ?>
                <p class="eb-profile-txt-field">
                    <label for="country"><?php _e('Country', 'eb-textdomain'); ?></label>
                    <select name="country" id="country"></select>
                    <input name="eb-selected-country" type="hidden" id="eb-selected-country" value="<?php echo $country; ?>" />
                </p>
                <p class="eb-profile-txt-field">
                    <label for="city"><?php _e('City', 'eb-textdomain'); ?></label>
                    <input class="text-input" name="city" type="text" id="city" value="<?php echo $city; ?>" />
                </p>
                <p class="eb-profile-txt-area-field">
                    <label for="description"><?php _e('Biographical Information', 'eb-textdomain') ?></label>
                    <textarea name="description" id="description" rows="3" cols="50"><?php echo $description; ?></textarea>
                </p>
            </fieldset>
            <fieldset>
                <legend><?php _e('Password Change', 'eb-textdomain'); ?></legend>
                <p class="eb-profile-password-field">
                    <label for="eb_curr_psw"><?php _e('Current Password', 'eb-textdomain'); ?> <span class="eb-small"><?php _e('(Keep blank to leave unchanged)', 'eb-textdomain'); ?></span></label>
                    <input class="text-input" name="curr_psw" type="password" id="eb_curr_psw" />                
                </p>
                <p class="eb-profile-password-field">
                    <label for="eb_new_psw"><?php _e('New Password', 'eb-textdomain'); ?> <span class="eb-small"><?php _e('(Keep blank to leave unchanged)', 'eb-textdomain'); ?></span></label>
                    <input class="text-input" name="new_psw" type="password" id="eb_new_psw" />
                </p>
                <p class="eb-profile-password-field">
                    <label for="eb_confirm_psw"><?php _e('Confirm Password', 'eb-textdomain'); ?></label>
                    <input class="text-input" name="confirm_psw" type="password" id="eb_confirm_psw" />
                </p>
            </fieldset>
            <?php
            //action hook for plugin and extra fields
            do_action('eb_edit_user_profile', $current_user);
            ?>
            <p class="eb-small">
                <?php _e('Note: All fields will be updated on Moodle as well as on WordPress site.', 'eb-textdomain'); ?>
            </p>
            <p class="eb-profile-form-submit">
                <?php //echo $referer;   ?>
                <input name="updateuser" type="submit" id="updateuser" class="button-primary" value="<?php _e('Save Changes', 'eb-textdomain'); ?>" />
                <?php wp_nonce_field('eb-update-user') ?>
                <input name="action" type="hidden" id="action" value="eb-update-user" />
            </p>
        </form>
    <?php } ?>
</section>
