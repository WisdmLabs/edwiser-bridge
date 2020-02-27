<?php
/**
 * Partial: Page - Extensions.
 *
 * @var object
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
//Get Setting Messages
$setting_messages = '';
$setting_messages = apply_filters('eb_setting_messages', $setting_messages);
if (!empty($setting_messages)) {
    echo $setting_messages;
}
//Get Licensing Information

/*
 *
 * @var array License Information
 */
$licensing_info = array(); //array('plugin_name','plugin_slug','license_key','license_status','activate_license');
$licensing_info = apply_filters('eb_licensing_information', $licensing_info);
//echo '<pre>' . print_r( $licensing_info , 1 ) . '<pre>';
if (!empty($licensing_info)) {
    ?>
    <div class="eb_table">
        <div class="eb_table_body">
            <?php
            foreach ($licensing_info as $single) {
                ?>
                <form name="<?php echo $single['plugin_slug'].'_licensing_form';
                ?>" method="post" id="mainform" >
                    <div class="eb_table_row">

                        <div class="eb_table_cell_1">
                            <?php echo $single['plugin_name'];?>
                        </div>

                        <div class="eb_table_cell_2">
                            <?php echo $single['license_key'];?>
                        </div>

                        <div class="eb_table_cell_3">
                            <?php echo $single['license_status'];?>
                        </div>

                        <div class="eb_table_cell_4">
                            <?php echo $single['activate_license'];?>
                        </div>

                    </div>
                    <?php wp_nonce_field('eb-settings');?>
                </form>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
} else {
    printf(__('%1s You do not have any extensions activated. %2s Please activate any installed extensions. If you do not have any extensions, you can take a look at the list %3s here%4s.%5s', 'eb-textdomain'), '<div class="update-nag"><strong>', '</strong>', '<a href="https://edwiser.org/bridge/extensions/" target="_blank">', '</a>', '</div>');
}
