<?php
namespace app\wisdmlabs\edwiserBridge;

if (!defined('ABSPATH')) {
    exit;
}

class Modular_Analytics_System {
    private static $instance;
    private $server_url = 'https://dev1.edwiser.org/wp-json/analytics/v1/collect'; // Replace with your server URL
    private $deactivation_url = 'https://dev1.edwiser.org/wp-json/analytics/v1/deactivate'; // URL for deactivation feedback
    private $plugin_file = 'edwiser-bridge/edwiser-bridge.php'; //replace with your plugin file
    private $plugin_name = 'Edwiser Bridge - WordPress Moodle LMS Integration'; //replace with your plugin name
    private $plugin_version = '4.0.0'; //replace with your plugin version

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook($this->plugin_file, [$this, 'on_activation']);
        register_deactivation_hook($this->plugin_file, [$this, 'on_deactivation']); // Add deactivation hook
        add_action('admin_init', [$this, 'check_consent']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_modal_scripts']); // Enqueue JS and CSS
        add_action('admin_footer', [$this, 'set_deactivation_url']); // Set deactivation URL
    }

    public function on_activation() {

        $consent = get_option('modular_analytics_consent');

        if ($consent === 'yes') {
            $this->send_data();
        } elseif ($consent === 'no') {
            // User has opted out, do nothing
        } else {
            // Default to pending consent
            add_option('modular_analytics_consent', 'pending');
        }
        
    }

    public function on_deactivation() {
        add_option('modular_analytics_deactivation_feedback', 'pending');
    }

    function set_deactivation_url( $plugin_file = 'edwiser-bridge/edwiser-bridge.php' ) { //replace with your plugin file

        // Check if the plugin file exists.
        $plugins = get_plugins(); // Get all installed plugins.
    
        if ( ! isset( $plugins[ $plugin_file ] ) ) {
            return false; // Plugin not found.
        }
    
        // Get the plugin's directory.
        $plugin_dir = dirname( $plugin_file );
    
        // Build the deactivation link.  We use admin_url() for proper URL generation.
        $deactivate_link = add_query_arg(
            array(
                'action'   => 'deactivate',
                'plugin'   => $plugin_file,
                'plugin_status' => 'all', // Important for multisite.
                'paged' => 1, // Important for multisite.
                '_wpnonce' => wp_create_nonce( 'deactivate-plugin_' . $plugin_file ),
            ),
            admin_url( 'plugins.php' )
        );

        return $deactivate_link;
    }

    public function enqueue_modal_scripts() {

        wp_enqueue_script('modular-analytics-modal', plugin_dir_url(__FILE__) . 'js/modal.js', ['jquery'], '1.0', true); // Path to your JS file

        // Localize script to pass data to JS
        wp_localize_script('modular-analytics-modal', 'modular_analytics_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('modular_analytics_deactivation'),
            'deactivation_url' => $this->set_deactivation_url(), // Pass the deactivation URL
            'website' => get_site_url(),
            'email' => wp_get_current_user()->user_email,
            'ip' => $this->get_user_ip(),
        ]);
    }

    public function check_consent() {
        $consent = get_option('modular_analytics_consent', 'pending');

        if ($consent === 'pending' && current_user_can('manage_options')) {
            add_action('admin_notices', [$this, 'consent_notice']);
        }
    }

    public function consent_notice() {
        ?>
        <div class="notice eb_admin_remui_demo_notice">
            <div class="eb_remui_demo_notice_content">
                <p style="font-size: 21px; font-weight: 500; color: #133F3F;"><?php echo sprintf(__('Please %sopt in%s to permit the collection of your email address, along with basic WordPress environment and usage data. This information will be used solely to %s enhance and improve the plugin %s, and will be handled in accordance with our %sPrivacy Policy%s.', 'edwiser-bridge'), '<strong style="color: #f00;">', '</strong>', '<strong style="color: #f00;">', '</strong>', '<strong style="color: #f00;"><a href="https://edwiser.org/privacy-policy/" target="_blank">', '</a></strong>'); ?></p>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=modular_analytics_consent&consent=yes')); ?>" class="button-primary" style="background-color: #F75D25; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 500; margin-right: 10px;"><?php _e('Allow & Continue', 'edwiser-bridge'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=modular_analytics_consent&consent=no')); ?>" class="button-secondary" style="background-color: #fff; color: #F75D25; border: 1px solid #F75D25; border-radius: 5px; cursor: pointer; font-size: 15px;"><?php _e('Skip', 'edwiser-bridge'); ?></a>
                </p>
            </div>
        </div>
        <?php
    }

    public function handle_consent() {
        if (!current_user_can('manage_options') || !isset($_GET['consent'])) {
            wp_die(__('Unauthorized action.', 'edwiser-bridge'));
        }

        $consent = sanitize_text_field($_GET['consent']);
        update_option('modular_analytics_consent', $consent);

        if ($consent === 'yes') {
            $this->send_data();
        }

        wp_redirect(admin_url().'edit.php?post_type=eb_course&page=eb-settings'); //replace with your redirect URL
        exit;
    }

    private function send_data() {
        $user = wp_get_current_user();
        $website = get_site_url();
        $ip = $this->get_user_ip();
        $timestamp = current_time('mysql');

        $data = [
            'email'       => sanitize_email($user->user_email),
            'first_name'  => sanitize_text_field($user->first_name),
            'last_name'   => sanitize_text_field($user->last_name),
            'website'     => esc_url_raw($website),
            'site_name'   => sanitize_text_field(get_bloginfo('name')),
            'wp_version'  => sanitize_text_field(get_bloginfo('version')),
            'php_version' => sanitize_text_field(phpversion()),
            'plugin_name' => $this->plugin_name,
            'plugin_version' => $this->plugin_version,
            'ip'          => sanitize_text_field($ip),
            'timestamp'   => sanitize_text_field($timestamp),
        ];

        wp_remote_post($this->server_url, [
            'method'    => 'POST',
            'body'      => wp_json_encode($data),
            'headers'   => ['Content-Type' => 'application/json'],
            'timeout'   => 10,
        ]);
    }

    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function handle_deactivation_feedback_ajax() {
        check_ajax_referer('modular_analytics_deactivation', 'nonce');

        if (!isset($_POST['reason'])) {
            wp_send_json_error(['message' => 'Reason is required']);
        }

        $reason = sanitize_text_field($_POST['reason']);

        $response = $this->send_deactivation_feedback($reason);
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        } else {
            update_option('modular_analytics_deactivation_feedback', 'sent');
            wp_send_json_success();
        }
    }

    private function send_deactivation_feedback($reason) {
        $data = [
            'email' => sanitize_email(wp_get_current_user()->user_email),
            'website' => esc_url_raw(get_site_url()),
            'plugin_name' => $this->plugin_name,
            'ip' => sanitize_text_field($this->get_user_ip()),
            'timestamp' => sanitize_text_field(current_time('mysql')),
            'reason' => $reason,
        ];

        $response = wp_remote_post($this->deactivation_url, [
            'method' => 'POST',
            'body' => wp_json_encode($data),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 10,
        ]);

        return $response;
    }

    public function handle_dismiss_feedback() {
        check_ajax_referer('modular_analytics_dismiss', 'nonce');
        update_option('modular_analytics_deactivation_feedback', 'dismissed');
        wp_die();
    }
}

// Register the consent handler
add_action('admin_post_modular_analytics_consent', [Modular_Analytics_System::get_instance(), 'handle_consent']);