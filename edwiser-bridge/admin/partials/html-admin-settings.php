<?php
/**
 * Admin View: Settings.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
?>
<div class="wrap edw">
    <?php
    do_action("eb_settings_header");
    if ($tab != 'licensing') {
        ?>
        <form method="post" id="mainform" action="" enctype="multipart/form-data">
            <?php
    }
        ?>
        <div class="icon32 icon32-eb-settings" id="icon-edw"><br /></div>
        <h2 class="nav-tab-wrapper eb-nav-tab-wrapper">
            <?php
            foreach ($tabs as $name => $label) {
                echo '<a href="'.admin_url('admin.php?page=eb-settings&tab='.$name).
                '" class="nav-tab '.($current_tab == $name ? 'nav-tab-active' : '').'">'
                .$label.
                '</a>';
            }
            do_action('eb_settings_tabs');
            ?>
        </h2>

        <div class="form-content">
            <?php
            do_action('eb_sections_'.$current_tab);
            do_action('eb_settings_'.$current_tab);
            ?>

            <p class="submit">
                <?php if (!isset($GLOBALS['hide_save_button'])) : ?>
                    <input name="save" class="button-primary" type="submit"
                           value="<?php _e('Save changes', 'eb-textdomain'); ?>" />
                        <?php endif;
                        ?>
                <input type="hidden" name="subtab" id="last_tab" />
                <?php wp_nonce_field('eb-settings'); ?>
            </p>
        </div>
        <?php
        if ($tab != 'licensing') {
            ?>
        </form>
        <?php
        }
    ?>
</div>
<?php
