<?php
namespace app\wisdmlabs\edwiserBridge;

/**
 * class which will handle linking and unlinking of the wordpress users with moodle on the toggle switch provided on users page
 */
class LinkUnlink
{
    /**
     * calling function to add column and its content on filter
     */
    public function __construct()
    {
        add_filter('manage_users_columns', array($this,'addingMoodleAccountColumn'));
        add_filter('manage_users_custom_column', array($this, 'showContent'), 10, 3);
    }

    /**
     * Adding Moodle account column in users page
     * @param  [array] $column array of all column from users.php page
     * @return [array]         returning array by adding our column in it
     */
    public function addingMoodleAccountColumn($column)
    {
           $column['moodle_Account'] = sprintf(__('Moodle Account', 'eb-textdomain'));
          return $column;
    }

    /**
     * Adding toggle switch in the moodle account column
     * @param  [string] $val         value which will be added in column
     * @param  [array] $column_name array of all column names
     * @param  [integer] $user_id     id of the user
     * @return [string]              returning data needed to add in column
     */
    public function showContent($val, $column_name, $user_id)
    {
        $link = get_user_meta($user_id, "moodle_user_id", true);
        $checked="block";
        $unchecked="none";
        if (trim($link) == "") {
            $checked="none";
            $unchecked="block";
        }
        if ($column_name == "moodle_Account") {
            $val='<div id="'.$user_id.'" class="wdm-wpcwn-type">
                        <label class="link-unlink" id="'.$user_id.'-link" title="Link user with Moodle account" class="link-unlink link" style="display:'.$unchecked.';">'.sprintf(__('Link User', 'eb-textdomain')).'</label>
                        <label class="link-unlink" id="'.$user_id.'-unlink" title="Unlink user with moodle account." class="link-unlink unlink" style="display:'.$checked.';">'.sprintf(__('Unlink User', 'eb-textdomain')).'</label>
                      </div>
                  ';
        }
        return $val;
    }
}

new LinkUnlink();
