<?php
/**
 * Error log manager.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( '\app\wisdmlabs\edwiserBridge\Eb_Manage_Error_Log' ) ) {

	/**
	 * Manage enrollment.
	 */
	class Eb_Manage_Error_Log {


		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 *
		 * @var string The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 *
		 * @var string The current version of this plugin.
		 */
		private $version;

		/**
		 * The instance of this plugin.
		 *
		 * @var EB_Course_Manager The single instance of the class
		 *
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * Main Eb_Manage_Error_Log Instance.
		 *
		 * Ensures only one instance of Eb_Manage_Error_Log is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @see Eb_Manage_Error_Log()
		 *
		 * @param text $plugin_name plgin name.
		 * @param text $version plgin version.
		 * @return Eb_Manage_Error_Log - Main instance
		 */
		public static function instance( $plugin_name, $version ) {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self( $plugin_name, $version );
			}

			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since   1.0.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'eb-textdomain' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since   1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'eb-textdomain' ), '1.0.0' );
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param text $plugin_name plgin name.
		 * @param text $version plgin version.
		 */
		public function __construct( $plugin_name, $version ) {
			$this->plugin_name = $plugin_name;
			$this->version     = $version;
		}

		/**
		 * Displays the manage user enrollment page output
		 */
		public function out_put() {
			$list_table     = new Eb_Error_Log_Table();
			$current_action = $list_table->current_action();
			$this->handle_bulk_action( $current_action );
			$list_table->prepare_items();
			$post_page        = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // WPCS: CSRF ok, input var ok. // @codingStandardsIgnoreLine
			
			?>
			<div class="eb-manage-user-enrol-wrap">

				<!-- Display the proccessing popup start. -->
				<div id="loading-div-background">
					<div id="loading-div" class="ui-corner-all" >
						<img style="height:40px;margin:40px;" src="images/loading.gif" alt="Loading.."/>
						<h2 style="color:gray;font-weight:normal;">
							<?php esc_html_e( 'Please wait processing request ....', 'eb-textdomain' ); ?>
						</h2>
					</div>
				</div>
				<!-- Display the proccessing popup end. -->

				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

				<div class="eb-notices" id="eb-notices"><!-- Add custom notices inside this. --></div>
				<?php do_action( 'eb_before_error_log_table' ); ?>
                <form method="post" >
				<?php
                wp_nonce_field( 'eb-error-log-bulk-action', 'eb-error-log-bulk-action' );

                $list_table->display(); ?>

				<?php do_action( 'eb_after_error_log_table' ); ?>
			</div>
			<?php
		}

		/**
		 * Callback to handle the bulk or individul action applied on the list
		 * table row from the manage user enrolment page
		 *
		 * @param type $action bulk action.
		 */
		private function handle_bulk_action( $action ) {
			if ( ! isset( $_POST['eb-error-log-bulk-action'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eb-error-log-bulk-action'] ) ), 'eb-error-log-bulk-action' ) ) {
				$post_data = array();
			} else {
				$post_data = $_POST;
			}
			switch ( $action ) {
				case 'delete':
                    $this->multiple_error_log_delete( $post_data );
					break;
				default:
					break;
			}
		}

		/**
		 * Provides the functionality to unenroll multipal users from the course
		 *
		 * @param type $data bulk action data to unenroll users.
		 * 
		 */
		private function multiple_error_log_delete( $data ) {
			global $wpdb;
			if ( ! isset( $data['error'] ) ) {
				return;
			}

			$keys       = $data['error'];
            error_log(print_r($keys,true));
			
            $error_log_file = wdm_edwiser_bridge_plugin_log_dir() . 'error_log.json';
            $error_logs = file_get_contents( $error_log_file );
            $error_logs = json_decode( $error_logs, true );
			$cnt        = 0;

            foreach ( $keys as $key ) {
                if( isset( $error_logs[ $key ] ) ) {
                    unset( $error_logs[ $key ] );
                    $cnt++;
                }
            }
            $error_logs = json_encode( $error_logs );
            file_put_contents( $error_log_file, $error_logs );
            
			if ( $cnt > 0 ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<strong>
							<?php sprintf( '%s ', $cnt ) . esc_html_e( ' error logs are deleted successfully.', 'eb-textdomain' ); ?>
						</strong>
					</p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">
						<?php
						esc_html_e( 'Dismiss this notice', 'eb-textdomain' );
						?>
						.</span>
					</button>
				</div>
				<?php
			} else {
				?>
				<div class="error notice">
					<p>
						<strong>
							<?php esc_html_e( 'No error log deleted', 'eb-textdomain' ); ?>
						</strong>
					</p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">
						<?php
						esc_html_e( 'Dismiss this notice', 'eb-textdomain' );
						?>
						.</span>
					</button>
				</div>
				<?php
			}
		}

		/**
		 * Ajax callback to get error log data for given id
		 */
		public function ajax_get_error_log_data() {
            $response = esc_html__( 'Error log not found', 'eb-textdomain' );
            if ( isset( $_POST['key'] ) && isset( $_POST['action'] ) && 'wdm_eb_get_error_log_data' === $_POST['action'] && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {

                $key = sanitize_text_field( wp_unslash( $_POST['key'] ) );
                $error_log_file = wdm_edwiser_bridge_plugin_log_dir() . 'error_log.json';
                $error_logs = file_get_contents( $error_log_file );
                $error_logs = json_decode( $error_logs, true );

                if( !is_array( $error_logs ) ){
                    wp_send_json_error( $response );
                }
                else{
                    if( isset( $error_logs[ $key ] ) ){
                        $response = $error_logs[ $key ];
                        wp_send_json_success( $response );
                    }
                    else{
                        wp_send_json_error( $response );
                    }
                }
            }
            else{
                wp_send_json_error( $response );
            }
        }

        /**
		 * Ajax callback to mark error log resolved
		 */
		public function ajax_error_log_resolved() {
            $response = esc_html__( 'Error log not found', 'eb-textdomain' );
            if ( isset( $_POST['key'] ) && isset( $_POST['action'] ) && 'wdm_eb_mark_error_log_resolved' === $_POST['action'] && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {

				//get error log file
                $key = sanitize_text_field( wp_unslash( $_POST['key'] ) );
                $error_log_file = wdm_edwiser_bridge_plugin_log_dir() . 'error_log.json';
                $error_logs = file_get_contents( $error_log_file );
                $error_logs = json_decode( $error_logs, true );

				//get resolved error log file for this month
				$resolved_log_file = wdm_edwiser_bridge_plugin_log_dir() . 'error_log-' . date('m-y') . '.json';
                $resolved_logs = file_get_contents( $resolved_log_file );
                $resolved_logs = json_decode( $resolved_logs, true );

                if( !is_array( $error_logs ) ){
                    wp_send_json_error( $response );
                }
                else{
                    $error_logs[$key]['status'] = 'RESOLVED';

					if(!is_array($resolved_logs)){
						$resolved_logs = array();
					}
					$resolved_logs[] = $error_logs[$key];
					unset( $error_logs[ $key ] );

					$error_logs = json_encode( $error_logs );
                    $resolved_logs = json_encode( $resolved_logs );
                    file_put_contents( $error_log_file, $error_logs );
					file_put_contents( $resolved_log_file, $resolved_logs );
                    wp_send_json_success();
                }
            }
            else{
                wp_send_json_error( $response );
            }
        }

        /**
		 * Ajax callback to delete error log
		 */
		public function ajax_send_error_log_to_support() {
            $response = esc_html__( 'Failed', 'eb-textdomain' );
            if ( isset( $_POST['key'] ) && isset( $_POST['action'] ) && 'send_error_log_to_support' === $_POST['action'] && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {

                $key = sanitize_text_field( wp_unslash( $_POST['key'] ) );
				if(isset($_POST['email'])){
					$email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
				}
				else{
					$email = get_option( 'admin_email' );
				}
				$email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
                $error_log_file = wdm_edwiser_bridge_plugin_log_dir() . 'error_log.json';
                $error_logs = file_get_contents( $error_log_file );
                $error_logs = json_decode( $error_logs, true );

                if( !is_array( $error_logs ) ){
                    wp_send_json_error( $response );
                }
                else{
					$error_log_data = $error_logs[ $key ][ 'data' ];
                    //send mail to support
					//get site name and url for subject
					$site_name = get_option( 'blogname' );
					$site_url = get_option( 'siteurl' );
					$subject = 'Error Log From : ' . $site_name . ' - ' . $site_url ;
					$message = '<p>' . esc_html__( 'Error log details', 'eb-textdomain' ) . '</p>';
					if(isset($email)){
						$message .= '<p>' . esc_html__( 'Support Email', 'eb-textdomain' ) . ' : ' . $email . '</p>';
					}
					$message .= '<p>' . esc_html__( 'Error log message', 'eb-textdomain' ) . ': ' . $error_log_data['message'] . '</p>';
					$message .= '<p>' . esc_html__( 'URL', 'eb-textdomain' ) . ': ' . $error_log_data['url'] . '</p>';
					$message .= '<p>' . esc_html__( 'HTTP Response Code', 'eb-textdomain' ) . ': ' . $error_log_data['responsecode'] . '</p>';
					$message .= '<p>' . esc_html__( 'User', 'eb-textdomain' ) . ': ' . $error_log_data['user'] . '</p>';
					$message .= '<p>' . esc_html__( 'Exception', 'eb-textdomain' ) . ': ' . $error_log_data['exception'] . '</p>';
					$message .= '<p>' . esc_html__( 'Error Code', 'eb-textdomain' ) . ': ' . $error_log_data['errorcode'] . '</p>';
					if(isset($error_log_data['debuginfo'])){
						$message .= '<p>' . esc_html__( 'Debug Info', 'eb-textdomain' ) . ': ' . $error_log_data['debuginfo'] . '</p>';
					}
					if(isset($error_log_data['backtrace'])){
						$message .= '<pre>' . esc_html__( 'Backtrace', 'eb-textdomain' ) . ': ' . print_r($error_log_data['backtrace'], true) . '</pre>';
					}

					$headers = array('Content-Type: text/html; charset=UTF-8');
					$support_email = 'ishwar.singh.solanki@wisdmlabs.com';

					$mail_sent = wp_mail( $support_email, $subject, $message, $headers );
					if( $mail_sent ){
						$response = esc_html__( 'Mail sent successfully', 'eb-textdomain' );

						//add status in error log file
						$error_logs[$key]['status'] = 'SENT TO SUPPORT';

						$error_logs = json_encode( $error_logs );
						file_put_contents( $error_log_file, $error_logs );

						wp_send_json_success( $response );
					}
					else{
						wp_send_json_error( $response );
					}
                }
            }
            else{
                wp_send_json_error( $response );
            }
        }
	}
}
