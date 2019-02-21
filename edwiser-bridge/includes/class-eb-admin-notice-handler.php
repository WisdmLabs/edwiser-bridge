<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://edwiser.org
 * @since      1.3.4
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EBAdminNoticeHandler
{

    public function __construct()
    {
    }

    /**
     * show admin feedback notice
     * @since 1.3.1
     * @return [type] [description]
     */
    public function ebAdminUpdateMoodlePluginNotice()
    {
        $redirection = '?eb-update-notice-dismissed';
        if (isset($_GET) && !empty($_GET)) {
            $redirection = (isset($_SERVER['HTTPS']) ? 'https' : 'http')."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $redirection .= '&eb-update-notice-dismissed';
        }

        $user_id = get_current_user_id();
        if (!get_user_meta($user_id, 'eb_update_notice_dismissed')) {
            echo '  <div class="notice  eb_admin_update_notice_message_cont">
                        <div class="eb_admin_update_notice_message">
                            <div style="width: 21%">
                                <img class="eb_update_notice_img" src="'.EB_PLUGIN_URL.'images/logo.png" alt="'.__('Sorry, unable to load image', "eb-textdomain").'">
                            </div>
                            <div class="eb_update_notice_content">
                                '. __('Thanks for updating to the latest version of Edwiser Bridge plugin, <b>please make sure you have also installed our associated Moodle Plugin to avoid any malfunctioning.</b>', 'eb-textdomain').'
                                <a href="#">'.__(' Click here ', "eb-textdomain").'</a>
                                '.__(" to download Moodle plugin", "eb-textdomain").'
                                <div style="padding-top: 8px;">
                                    <a href="'.$redirection.'">
                                        '.__('Dismiss this notice', 'eb-textdomain').'
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="eb_admin_update_dismiss_notice_message">
                                <span class="dashicons dashicons-dismiss eb_update_notice_hide"></span>
                        </div>
                    </div>';
        }
    }



    /**
     * handle notice dismiss
     * @since 1.3.1
     * @return [type] [description]
     */
    public function ebAdminUpdateNoticeDismissHandler()
    {
        $user_id = get_current_user_id();
        if (isset($_GET['eb-update-notice-dismissed'])) {
            add_user_meta($user_id, 'eb_update_notice_dismissed', 'true', true);
        }
    }





    /**
     * show admin feedback notice
     * @since 1.3.1
     * @return [type] [description]
     */
    public function ebAdminFeedbackNotice()
    {
        $redirection = '?eb-feedback-notice-dismissed';
        if (isset($_GET) && !empty($_GET)) {
            $redirection = (isset($_SERVER['HTTPS']) ? 'https' : 'http')."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $redirection .= '&eb-feedback-notice-dismissed';
        }

        if ('eb_admin_feedback_notice' != get_transient('edwiser_bridge_admin_feedback_notice')) {
            $user_id = get_current_user_id();
            if (!get_user_meta($user_id, 'eb_feedback_notice_dismissed')) {
                echo '  <div class="notice notice-success eb_admin_feedback_notice_message_cont">
                            <div class="eb_admin_feedback_notice_message">'.__('Enjoying Edwiser bridge, Please  ', 'eb-textdomain').'<a href="https://wordpress.org/plugins/edwiser-bridge/">'.__(' click here ', 'eb-textdomain').'</a>'.__(' to rate us.', 'eb-textdomain').'</div>
                            <div class="eb_admin_feedback_dismiss_notice_message">
                                <a href="'.$redirection.'">
                                    <span class="dashicons dashicons-dismiss"></span>
                                    '.__(' Dismiss ', 'eb-textdomain').'
                                </a>
                            </div>
                        </div>';
            }
        }
    }


    /**
     * handle notice dismiss
     * @since 1.3.1
     * @return [type] [description]
     */
    public function ebAdminNoticeDismissHandler()
    {
        $user_id = get_current_user_id();
        if (isset($_GET['eb-feedback-notice-dismissed'])) {
            add_user_meta($user_id, 'eb_feedback_notice_dismissed', 'true', true);
        }
    }
}
