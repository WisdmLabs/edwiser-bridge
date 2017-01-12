<?php
global $current_user, $wp_roles;

$username = $current_user->user_login;
$first_name = (isset($_POST['first_name']) && !empty($_POST['first_name'])) ? $_POST['first_name'] : $current_user->first_name;
$last_name = (isset($_POST['last_name']) && !empty($_POST['last_name'])) ? $_POST['last_name'] : $current_user->last_name;
$nickname = (isset($_POST['nickname']) && !empty($_POST['nickname'])) ? $_POST['nickname'] : $current_user->nickname;
$email = (isset($_POST['email']) && !empty($_POST['email'])) ? $_POST['email'] : $current_user->user_email;
$description = (isset($_POST['description']) && !empty($_POST['description'])) ? $_POST['description'] : $current_user->description;
$city = (isset($_POST['city']) && !empty($_POST['city'])) ? $_POST['city'] : $current_user->city;
$country = (isset($_POST['country']) && !empty($_POST['country'])) ? $_POST['country'] : $current_user->country;

?>

<section class="eb-user-info eb-edit-user-wrapper">
    <h4><?php _e('Edit Account Details', 'eb-textdomain'); ?></h4>
    <p class="eb-small"><?php _e('Note: Following all fields will be updated on Moodle as well as on WordPress site.', 'eb-textdomain'); ?></p>
<?php
if (!is_user_logged_in()) :
?>
<p class="eb-warning"><?php _e('You must be logged in to edit your profile.', 'eb-textdomain'); ?></p>
<?php else : ?>
<?php
//\app\wisdmlabs\edwiserBridge\EbShortcodeUserProfile::saveAccountDetails();
if (isset($_SESSION['eb_msgs_'.$current_user->ID])) {
    echo $_SESSION['eb_msgs_'.$current_user->ID];
    session_unset($_SESSION['eb_msgs_'.$current_user->ID]);
}
?>
    <form method="post" id="eb-update-profile" action="<?php echo esc_url(add_query_arg('eb_action', 'edit-profile', get_permalink())); ?>">
        <p class="form-username">
            <label for="username"><?php _e('Username *', 'eb-textdomain'); ?> <span class="eb-small"><?php _e('(The system does not allow username to be changed)', 'eb-textdomain'); ?></span></label>
            <input class="text-input" name="username" type="text" id="username" value="<?php echo $username; ?>" readonly />
        </p><!-- .form-username -->
        <p class="form-first_name">
            <label for="first-name"><?php _e('First Name', 'eb-textdomain'); ?></label>
            <input class="text-input" name="first_name" type="text" id="first_name" value="<?php echo $first_name; ?>" />
        </p><!-- .form-first_name -->
        <p class="form-last_name">
            <label for="last-name"><?php _e('Last Name', 'eb-textdomain'); ?></label>
            <input class="text-input" name="last_name" type="text" id="last_name" value="<?php echo $last_name; ?>" />
        </p><!-- .form-last_name -->
        <p class="form-nickname">
            <label for="nickname"><?php _e('Nick Name', 'eb-textdomain'); ?></label>
            <input class="text-input" name="nickname" type="text" id="nickname" value="<?php echo $nickname; ?>" />
        </p><!-- .form-nickname -->
        <p class="form-email">
            <label for="email"><?php _e('E-mail *', 'eb-textdomain'); ?></label>
            <input class="text-input" name="email" type="email" id="email" value="<?php echo $email; ?>" required />
        </p><!-- .form-email -->
        <p class="form-password">
            <label for="pass_1"><?php _e('New Password', 'eb-textdomain'); ?> <span class="eb-small"><?php _e('(Keep blank to leave unchanged)', 'eb-textdomain'); ?></span></label>
            <input class="text-input" name="pass_1" type="password" id="pass_1" />
            <input type="checkbox" id="eb-unmask"><span><?php _e('Unmask', 'eb-textdomain'); ?></span>
        </p><!-- .form-password -->
        <p class="form-textarea">
            <label for="description"><?php _e('Biographical Information', 'eb-textdomain') ?></label>
            <textarea name="description" id="description" rows="3" cols="50"><?php echo $description; ?></textarea>
        </p><!-- .form-textarea -->
        <p class="form-city">
            <label for="city"><?php _e('City', 'eb-textdomain'); ?></label>
            <input class="text-input" name="city" type="text" id="city" value="<?php echo $city; ?>" />
        </p><!-- .form-city -->
        <?php wp_enqueue_script('edwiserbridge-edit-user-profile'); ?>
        <p class="form-country">
            <label for="country"><?php _e('Country', 'eb-textdomain'); ?></label>
            <select name="country" id="country"></select>
            <input name="eb-selected-country" type="hidden" id="eb-selected-country" value="<?php echo $country; ?>" />
        </p><!-- .form-city -->

        <?php
            //action hook for plugin and extra fields
            do_action('eb_edit_user_profile', $current_user);
        ?>
        <p class="form-submit">
            <?php //echo $referer; ?>
            <input name="updateuser" type="submit" id="updateuser" class="submit button" value="<?php _e('Update', 'eb-textdomain'); ?>" />
            <?php wp_nonce_field('eb-update-user') ?>
            <input name="action" type="hidden" id="action" value="eb-update-user" />
        </p><!-- .form-submit -->
    </form><!-- #eb-update-profile -->
<?php endif; ?>

    <div class="eb-edit-profile" >
        <a href="<?php echo get_permalink(); ?>" class="wdm-btn"><?php _e('Back to the profile', 'eb-textdomain'); ?></a>
    </div>
</section>