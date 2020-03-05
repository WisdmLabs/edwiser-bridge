<?php
namespace app\wisdmlabs\edwiserBridge;

/*
 * EDW Admin Settings Class.
 *
 * Adapted from code in woocommerce 2.3
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('EbAdminSettings')) {

    /**
     * EbAdminSettings.
     */
    class EbAdminSettings
    {

        private static $settings = array();
        private static $errors = array();
        private static $messages = array();

        /**
         * Include the settings page classes.
         */
        public static function getSettingsPages()
        {
            if (empty(self::$settings)) {
                $settings = array();

                // include the settings page class
                include_once 'settings/class-eb-settings-page.php';
                include_once 'class-eb-admin-marketing-add.php';

                $settings[] = include 'settings/class-eb-settings-general.php';
                $settings[] = include 'settings/class-eb-settings-connection.php';
                $settings[] = include 'settings/class-eb-settings-synchronization.php';
                $settings[] = include 'settings/class-eb-settings-paypal.php';
                self::$settings = apply_filters('eb_get_settings_pages', $settings);
                $settings[] = include 'settings/class-eb-settings-licensing.php';
                $settings[] = include 'settings/class-eb-settings-shortcode-doc.php';
                $settings[] = include 'settings/class-eb-settings-premium-extensions.php';
            }

            return self::$settings;
        }

        /**
         * Save the settings.
         *
         * @since  1.0.0
         */
        public static function save()
        {
            global $current_tab;

            $referer = '';

            if (isset($_POST['_wp_http_referer'])) {
                $referer = $_POST['_wp_http_referer'];
            }

            if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'eb-settings')) {
                die(__('Action failed. Please refresh the page and retry.', 'eb-textdomain'));
            }

            // Trigger actions
            do_action('eb_settings_save_'.$current_tab);
            do_action('eb_update_options_'.$current_tab);
            do_action('eb_update_options');
            if (!strpos($referer, 'admin.php?page=eb-settings&tab=licensing')) {
                self::addMessage(__('Your settings have been saved.', 'eb-textdomain'));
            }
            do_action('eb_settings_saved');
        }

        /**
         * Add a message.
         *
         * @since  1.0.0
         *
         * @param string $text
         */
        public static function addMessage($text)
        {
            self::$messages[] = $text;
        }

        /**
         * Add an error.
         *
         * @since  1.0.0
         *
         * @param string $text
         */
        public static function addError($text)
        {
            self::$errors[] = $text;
        }

        /**
         * Output messages + errors.
         *
         * @since  1.0.0
         *
         * @return string
         */
        public static function showMessages()
        {
            if (sizeof(self::$errors) > 0) {
                foreach (self::$errors as $error) {
                    echo '<div id="message" class="error fade"><p><strong>'.esc_html($error).'</strong></p></div>';
                }
            } elseif (sizeof(self::$messages) > 0) {
                foreach (self::$messages as $message) {
                    echo '<div id="message" class="updated fade">
                            <p>
                                <strong>'.esc_html($message).'</strong>
                            </p>
                        </div>';
                }
            }
        }

        /**
         * Settings page.
         *
         * Handles the display of the main edw settings page in admin.
         *
         * @since  1.0.0
         */
        public static function output()
        {
            global $current_section, $current_tab;

            do_action('eb_settings_start');

            // Include settings pages
            self::getSettingsPages();

            // Get current tab/section
            $current_tab = '';
            if (empty($_GET['tab'])) {
                $current_tab = 'general';
            } else {
                $current_tab = sanitize_title($_GET['tab']);
            }
            $current_section = '';
            if (!empty($_REQUEST['section'])) {
                $current_section = sanitize_title($_REQUEST['section']);
            }

            // Save settings if data has been posted
            if (!empty($_POST)) {
                self::save();
            }

            // Add any posted messages
            if (!empty($_GET['wp_error'])) {
                self::addError(stripslashes($_GET['wp_error']));
            }

            if (!empty($_GET['wp_message'])) {
                self::addMessage(stripslashes($_GET['wp_message']));
            }

            self::showMessages();

            // Get tabs for the settings page
            $tabs = apply_filters('eb_settings_tabs_array', array());

            //include 'partials/html-admin-settings.php';
            include_once EB_PLUGIN_DIR.'admin/partials/html-admin-settings.php';
        }

        /**
         * Get a setting from the settings API.
         *
         * @since  1.0.0
         *
         * @param string $option_name field name for which value to be fetched
         * @param string $current_tab tab in which the above field resides
         * @param string $default     default value to be returned in case field value not found
         *
         * @return option value
         */
        public static function getOption($option_name, $current_tab, $default = '')
        {

            //get options of current tab
            $options_values = get_option('eb_'.$current_tab);

            // Get value
            $option_value = null;
            if (isset($options_values[$option_name])) {
                $option_value = $options_values[$option_name];
            }

            if (is_array($option_value)) {
                $option_value = array_map('stripslashes', $option_value);
            } elseif (!is_null($option_value)) {
                $option_value = stripslashes($option_value);
            }

            return $option_value === null ? $default : $option_value;
        }

        /**
         * Output admin fields.
         *
         * Loops though the edw options array and outputs each field.
         *
         * @since  1.0.0
         *
         * @param array $options Opens array to output
         */
        public static function outputFields($options)
        {
            global $current_tab;

            foreach ($options as $value) {
                if (!isset($value['type'])) {
                    continue;
                }
                if (!isset($value['id'])) {
                    $value['id'] = '';
                }
                if (!isset($value['title'])) {
                    $value['title'] = isset($value['name']) ? $value['name'] : '';
                }
                if (!isset($value['class'])) {
                    $value['class'] = '';
                }
                if (!isset($value['css'])) {
                    $value['css'] = '';
                }
                if (!isset($value['default'])) {
                    $value['default'] = '';
                }
                if (!isset($value['desc'])) {
                    $value['desc'] = '';
                }
                if (!isset($value['desc_tip'])) {
                    $value['desc_tip'] = false;
                }
                if (!isset($value['placeholder'])) {
                    $value['placeholder'] = '';
                }

                // Custom attribute handling
                $custom_attributes = array();
                if (!empty($value['custom_attributes']) && is_array($value['custom_attributes'])) {
                    foreach ($value['custom_attributes'] as $attribute => $attribute_value) {
                        $custom_attributes[] = esc_attr($attribute).'="'.esc_attr($attribute_value).'"';
                    }
                }

                // Description handling
                $field_description = self::getFieldDescription($value);
                extract($field_description);

                // Switch based on type
                switch ($value['type']) {
                    // Section Titles
                    case 'title':

                        if (!isset($value['class'])) {
                            $value['class'] = "";
                        }
                        if (!empty($value['title'])) {
                            echo "<h3 class='".$value['class']."'>".esc_html($value['title']).'</h3>';
                        }
                        if (!empty($value['desc'])) {
                            echo wpautop(wptexturize(wp_kses_post($value['desc'])));
                        }
                        echo '<table class="form-table">'."\n\n";
                        if (!empty($value['id'])) {
                            do_action('eb_settings_'.sanitize_title($value['id']));
                        }
                        break;

                    // Section Ends
                    case 'sectionend':
                        if (!empty($value['id'])) {
                            do_action('eb_settings_'.sanitize_title($value['id']).'_end');
                        }
                        echo '</table>';
                        if (!empty($value['id'])) {
                            do_action('eb_settings_'.sanitize_title($value['id']).'_after');
                        }
                        break;

                    // Standard text inputs and subtypes like 'number'
                    case 'text':
                    case 'email':
                    case 'url':
                    case 'number':
                    case 'color':
                    case 'password':
                        $type = $value['type'];
                        $option_value = self::getOption($value['id'], $current_tab, $value['default']);

                        if ($value['type'] == 'color') {
                            $type = 'text';
                            $value['class'] .= 'colorpick';
                            $description .= '<div id="colorPickerDiv_'.esc_attr($value['id']).'"
                            class="colorpickdiv" style="z-index: 100;background:#eee;
                            border:1px solid #ccc;position:absolute;display:none;"></div>';
                        }
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>">
                                    <?php echo esc_html($value['title']); ?>
                                </label>
                                <?php echo $tooltip_html; ?>
                            </th>
                            <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                                <input
                                    name="<?php echo esc_attr($value['id']); ?>"
                                    id="<?php echo esc_attr($value['id']); ?>"
                                    type="<?php echo esc_attr($type); ?>"
                                    style="<?php echo esc_attr($value['css']); ?>"
                                    value="<?php echo esc_attr($option_value); ?>"
                                    class="<?php echo esc_attr($value['class']); ?>"
                                    placeholder="<?php echo esc_attr($value['placeholder']); ?>"
                                    <?php echo implode(' ', $custom_attributes); ?>
                                    />
                                    <?php echo $description; ?>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Textarea
                    case 'textarea':
                        $option_value = self::getOption($value['id'], $current_tab, $value['default']);
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>">
                                    <?php echo esc_html($value['title']); ?>
                                </label>
                                <?php echo $tooltip_html; ?>
                            </th>
                            <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                                <?php echo $description; ?>

                                <textarea
                                    name="<?php echo esc_attr($value['id']); ?>"
                                    id="<?php echo esc_attr($value['id']); ?>"
                                    style="<?php echo esc_attr($value['css']); ?>"
                                    class="<?php echo esc_attr($value['class']); ?>"
                                    placeholder="<?php echo esc_attr($value['placeholder']); ?>"
                                    <?php echo implode(' ', $custom_attributes); ?>><?php echo esc_textarea($option_value); ?></textarea>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Button input
                    case 'button':
                        $type = $value['type'];
                        $option_value = $value['default'];
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                            </th>
                            <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                                <input
                                    name="<?php echo esc_attr($value['id']); ?>"
                                    id="<?php echo esc_attr($value['id']); ?>"
                                    type="<?php echo esc_attr($type); ?>"
                                    style="<?php echo esc_attr($value['css']); ?>"
                                    value="<?php echo esc_attr($option_value); ?>"
                                    class="<?php echo esc_attr($value['class']); ?>"
                                    <?php echo implode(' ', $custom_attributes); ?> />
                                    <?php echo $description; ?>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Select boxes
                    case 'select':
                    case 'multiselect':
                        $option_value = self::getOption($value['id'], $current_tab, $value['default']);
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>">
                                    <?php echo esc_html($value['title']); ?>
                                </label>
                                <?php echo $tooltip_html; ?>
                            </th>
                            <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                                <select
                                    name="<?php
                                    echo esc_attr($value['id']);
                                    if ($value['type'] == 'multiselect') {
                                        echo '[]';
                                    }
                                    ?>"
                                    id="<?php echo esc_attr($value['id']); ?>"
                                    style="<?php echo esc_attr($value['css']); ?>"
                                    class="<?php echo esc_attr($value['class']); ?>"
                                    <?php echo implode(' ', $custom_attributes); ?>
                                    <?php echo ('multiselect' == $value['type']) ? 'multiple="multiple"' : ''; ?>>
                                        <?php
                                        if (isset($value["default"]) && !empty($value["default"])) {
                                            ?>
                                            <option value=""> <?= $value["default"] ?></option>
                                            <?php
                                        }


                                        foreach ($value['options'] as $key => $val) { ?>
                                        <option value="<?php echo esc_attr($key);
                                            ?>"
                                                <?php
                                                if (is_array($option_value)) {
                                                    selected(in_array($key, $option_value), true);
                                                } else {
                                                    selected($option_value, $key);
                                                }
                                                ?>>
                                                    <?php echo $val ?>
                                        </option> <?php }
                                                ?>
                                </select>
                                <?php echo $description; ?>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Radio inputs
                    case 'radio':
                        $option_value = self::getOption($value['id'], $current_tab, $value['default']);
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($value['id']); ?>">
                                    <?php echo esc_html($value['title']); ?>
                                </label>
                                <?php echo $tooltip_html; ?>
                            </th>
                            <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                                <fieldset>
                                    <?php echo $description; ?>
                                    <ul>
                                        <?php foreach ($value['options'] as $key => $val) {
                                            ?>
                                            <li>
                                                <label>
                                                    <input
                                                        name="<?php echo esc_attr($value['id']); ?>"
                                                        value="<?php echo $key; ?>"
                                                        type="radio"
                                                        style="<?php echo esc_attr($value['css']); ?>"
                                                        class="<?php echo esc_attr($value['class']); ?>"
                                                        <?php echo implode(' ', $custom_attributes); ?>
                                                        <?php checked($key, $option_value); ?> /> 
                                                        <?php echo $val ?>
                                                </label>
                                            </li>
                                        <?php }
                                        ?>
                                    </ul>
                                </fieldset>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Checkbox input
                    case 'checkbox':
                        $option_value = self::getOption($value['id'], $current_tab, $value['default']);
                        $visbility_class = array();

                        if (!isset($value['hide_if_checked'])) {
                            $value['hide_if_checked'] = false;
                        }
                        if (!isset($value['show_if_checked'])) {
                            $value['show_if_checked'] = false;
                        }
                        if ('yes' == $value['hide_if_checked'] || 'yes' == $value['show_if_checked']) {
                            $visbility_class[] = 'wdm_hidden_option';
                        }
                        if ('option' == $value['hide_if_checked']) {
                            $visbility_class[] = 'hide_options_if_checked';
                        }
                        if ('option' == $value['show_if_checked']) {
                            $visbility_class[] = 'show_options_if_checked';
                        }

                        if (!isset($value['checkboxgroup']) || 'start' == $value['checkboxgroup']) {
                            ?>
                            <tr valign="top" class="<?php echo esc_attr(implode(' ', $visbility_class)); ?>">
                                <th scope="row" class="titledesc"><?php echo esc_html($value['title']) ?>
                                </th>
                                <td class="forminp forminp-checkbox">
                                    <fieldset>
                                    <?php } else { ?>
                                        <fieldset class="<?php echo esc_attr(implode(' ', $visbility_class)); ?>">
                                        <?php } if (!empty($value['title'])) { ?>
                                            <legend class="screen-reader-text">
                                                <span>
                                                    <?php echo esc_html($value['title']) ?>
                                                </span>
                                            </legend>
                                        <?php } ?>
                                        <label for="<?php echo $value['id'] ?>">
                                            <input
                                                name="<?php echo esc_attr($value['id']); ?>"
                                                id="<?php echo esc_attr($value['id']); ?>"
                                                type="checkbox"
                                                value="1"
                                                <?php checked($option_value, 'yes'); ?>
                                                <?php echo implode(' ', $custom_attributes); ?> /> <?php echo $description ?>
                                        </label>
                                        <?php echo $tooltip_html; ?>
                                        <?php if (!isset($value['checkboxgroup']) || 'end' == $value['checkboxgroup']) {
                                            ?>
                                        </fieldset>
                                </td>
                            </tr>
                            <?php
                        } else {
                            ?>
                            </fieldset>
                            <?php
                        }
                        break;

                    // Single page selects
                    case 'single_select_page':
                        $args = array(
                            'name' => $value['id'],
                            'id' => $value['id'],
                            'sort_column' => 'menu_order',
                            'sort_order' => 'ASC',
                            'show_option_none' => ' ',
                            'class' => $value['class'],
                            'echo' => false,
                            'selected' => absint(self::getOption($value['id'], $current_tab)),
                        );

                        if (isset($value['args'])) {
                            $args = wp_parse_args($value['args'], $args);
                        }
                        ?>
                        <tr valign="top" class="single_select_page">
                            <th scope="row" class="titledesc"><?php echo esc_html($value['title']) ?>
                                <?php echo $tooltip_html; ?>
                            </th>
                            <td class="forminp">
                                <?php
                                echo str_replace(
                                        ' id=', " data-placeholder='".__('Select a page', 'eb-textdomain')."'
                                     style='".$value['css']."' class='".$value['class']."' id=", wp_dropdown_pages($args)
                                );
                                echo $description;
                                ?>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Single sidebar select
                    case 'select_sidebar':
                        $args = array(
                            'name' => $value['id'],
                            'id' => $value['id'],
                            'sort_column' => 'menu_order',
                            'sort_order' => 'ASC',
                            'show_option_none' => ' ',
                            'class' => $value['class'],
                            'echo' => false,
                            'selected' => self::getOption($value['id'], $current_tab),
                        );

                        if (isset($value['args'])) {
                            $args = wp_parse_args($value['args'], $args);
                        }
                        ?>
                        <tr valign="top" class="single_select_page">
                            <th scope="row" class="titledesc"><?php echo esc_html($value['title']) ?>
                                <?php echo $tooltip_html; ?>
                            </th>
                            <td class="forminp">
                                <select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
                                    <option selected><?php _e('- Select a sidebar -', 'eb-textdomain'); ?></option>
                                    <?php foreach ($GLOBALS['wp_registered_sidebars'] as $sidebar) { ?>
                                        <option value="<?php echo $sidebar['id']; ?>" <?php selected($args['selected'], $sidebar['id']); ?>>
                                            <?php echo ucwords($sidebar['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Single sidebar select
                    case 'courses_per_row':
                        $selectedVal = self::getOption($value['id'], $current_tab);
                        $selectedVal = trim($selectedVal);
                        $selectedVal = empty($selectedVal) ? "4" : $selectedVal;
                        $args = array(
                            'name' => $value['id'],
                            'id' => $value['id'],
                            'sort_column' => 'menu_order',
                            'sort_order' => 'ASC',
                            'show_option_none' => ' ',
                            'class' => $value['class'],
                            'echo' => false,
                            'selected' => $selectedVal,
                        );

                        if (isset($value['args'])) {
                            $args = wp_parse_args($value['args'], $args);
                        }
                        ?>
                        <tr valign="top" class="single_select_page">
                            <th scope="row" class="titledesc"><?php echo esc_html($value['title']) ?>
                                <?php echo $tooltip_html; ?>
                            </th>
                            <td class="forminp">
                                <select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
                                    <option <?php selected($args['selected'], '2'); ?>><?php _e('2', 'eb-textdomain'); ?></option>
                                    <option <?php selected($args['selected'], '3'); ?>><?php _e('3', 'eb-textdomain'); ?></option>
                                    <option <?php selected($args['selected'], '4'); ?>><?php _e('4', 'eb-textdomain'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <?php
                        break;
                    case 'horizontal_line':
                        ?>
                        <tr valign="top" class="single_select_page">
                            <td>
                                <hr>
                            </td>
                        </tr>
                        <?php
                        break;
                    // Default: run an action
                    default:
                        do_action('eb_admin_field_'.$value['type'], $current_tab, $value);
                        break;
                }
            }
        }

        /**
         * Save admin fields.
         *
         * Loops though the edw options array and outputs each field.
         *
         * @since  1.0.0
         *
         * @param array $options Opens array to output
         *
         * @return bool
         */
        public static function saveFields($options)
        {
            global $current_tab;

            if (empty($_POST)) {
                return false;
            }

            // Options to update will be stored here
            $update_options = array();

            // Loop options and get values to save
            foreach ($options as $value) {
                if (!isset($value['id']) || !isset($value['type'])) {
                    continue;
                }

                // Get posted value
                if (strstr($value['id'], '[')) {
                    parse_str($value['id'], $option_name_array);

                    $option_name = current(array_keys($option_name_array));
                    $setting_name = key($option_name_array[$option_name]);
                    $option_value = null;
                    if (isset($_POST[$option_name][$setting_name])) {
                        $option_value = wp_unslash($_POST[$option_name][$setting_name]);
                    }
                } else {
                    $option_name = $value['id'];
                    $setting_name = '';
                    $option_value = null;
                    if (isset($_POST[$value['id']])) {
                        $option_value = wp_unslash($_POST[$value['id']]);
                    }
                }

                // Format value
                switch (sanitize_title($value['type'])) {
                    case 'checkbox':
                        //$option_value = '';
                        if (is_null($option_value)) {
                            $option_value = 'no';
                        } else {
                            $option_value = 'yes';
                        }
                        break;
                    case 'textarea':
                        $option_value = wp_kses_post(trim($option_value));
                        break;
                    case 'text':
                    case 'email':
                    case 'url':
                    case 'number':
                    case 'select':
                    case 'color':
                    case 'password':
                    case 'single_select_page':
                    case 'radio':
                        $option_value = wpClean($option_value);
                        break;
                    case 'multiselect':
                        $option_value = array_filter(array_map('wpClean', (array) $option_value));
                        break;
                    default:
                        do_action('eb_update_option_'.sanitize_title($value['type']), $value);
                        break;
                }

                if (!is_null($option_value)) {
                    // Check if option is an array
                    if ($option_name && $setting_name) {
                        // Get old option value
                        if (!isset($update_options[$option_name])) {
                            $update_options[$option_name] = get_option($option_name, array());
                        }

                        if (!is_array($update_options[$option_name])) {
                            $update_options[$option_name] = array();
                        }

                        $update_options[$option_name][$setting_name] = $option_value;

                        // Single value
                    } else {
                        $update_options[$option_name] = $option_value;
                    }
                }

                // Custom handling
                do_action('eb_update_option', $value);
            }

            // Now save the options
            // foreach ( $update_options as $name => $value ) {
            //  update_option( $name, $value );
            // }

            $upd_opt_filtered = array_filter($update_options);
            update_option('eb_'.$current_tab, $upd_opt_filtered);

            return true;
        }

        /**
         * Helper function to get the formated description and tip HTML for a
         * given form field. Plugins can call this when implementing their own custom
         * settings types.
         *
         * @since  1.0.0
         *
         * @param array $value The form field value array
         * @returns array The description and tip as a 2 element array
         */
        public static function getFieldDescription($value)
        {
            $description = '';
            $tooltip_html = '';

            if (true === $value['desc_tip']) {
                $tooltip_html = $value['desc'];
            } elseif (!empty($value['desc_tip'])) {
                $description = $value['desc'];
                $tooltip_html = $value['desc_tip'];
            } elseif (!empty($value['desc'])) {
                $description = $value['desc'];
            }

            if ($description && in_array($value['type'], array('textarea', 'radio'))) {
                $description = '<p style="margin-top:0">'.wp_kses_post($description).'</p>';
            } elseif ($description && in_array($value['type'], array('checkbox'))) {
                $description = wp_kses_post($description);
            } elseif (in_array($value['type'], array('button'))) {
                $description = '<span class="load-response">
                                    <img src="'.EB_PLUGIN_URL.'images/loader.gif" height="20" width="20" />
                                </span>
                                <span class="response-box"></span>
                                <span class="linkresponse-box"></span>
                                <div id="unlinkerrorid-modal" class="unlinkerror-modal">
                                  <div class="unlinkerror-modal-content">
                                    <span class="unlinkerror-modal-close">&times;</span>
                                    <table class="unlink-table">
                                     <thead>
                                        <tr>
                                           <th>'.__("User ID", "eb-textdomain").'</th>
                                           <th>'.__("Name", "eb-textdomain").'</th>
                                        </tr>
                                     </thead>
                                     <tbody>
                                     </tbody>
                                  </table>
                                  </div>
                                 </div>';
            } elseif ($description) {
                $description = '<span class="description">'.wp_kses_post($description).'</span>';
            }

            if ($tooltip_html && in_array($value['type'], array('checkbox'))) {
                $tooltip_html = '<p class="description">'.$tooltip_html.'</p>';
            } elseif ($tooltip_html && in_array($value['type'], array('button'))) {
                $tooltip_html = '';
            } elseif ($tooltip_html) {
                $tooltip_html = '<img class="help_tip"
                                    data-tip="'.esc_attr($tooltip_html).'"
                                    src="'.EB_PLUGIN_URL.'images/help.png"
                                    height="20"
                                    width="20" />';
                //$tooltip_html = 'dsdf';
            }

            return array(
                'description' => $description,
                'tooltip_html' => $tooltip_html,
            );
        }
    }
}
new EbAdminSettings();
