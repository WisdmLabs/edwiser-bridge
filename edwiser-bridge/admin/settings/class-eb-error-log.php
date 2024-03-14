<?php
/**
 * EDW Connection Settings
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Error_Log' ) ) {

	/**
	 * Eb_Error_Log.
	 */
	class Eb_Error_Log extends EB_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'logs';
			$this->label = __( 'Logs', 'edwiser-bridge' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
		}

		/**
		 * Displays the manage user enrollment page output
		 */
		public function output() {
			$GLOBALS['hide_save_button'] = true;

			$list_table     = new Eb_Error_Logs_Table();
			$current_action = $list_table->current_action();
			$this->handle_bulk_action( $current_action );
			$list_table->prepare_items();
			?>
			<div class="eb-manage-user-enrol-wrap">

				<!-- Display the proccessing popup start. -->
				<div id="loading-div-background">
					<div id="loading-div" class="ui-corner-all" >
						<img style="height:40px;margin:40px;" src="images/loading.gif" alt="Loading.."/>
						<h2 style="color:gray;font-weight:normal;">
							<?php esc_html_e( 'Please wait processing request ....', 'edwiser-bridge' ); ?>
						</h2>
					</div>
				</div>
				<!-- Display the proccessing popup end. -->

				<h1><?php esc_html_e( 'Error logs', 'edwiser-bridge' ); ?></h1>

				<div class="eb-notices" id="eb-notices"><!-- Add custom notices inside this. --></div>
				<?php do_action( 'eb_before_log_table' ); ?>
				<form method="post" >
				<?php
				wp_nonce_field( 'eb-error-log-bulk-action', 'eb-error-log-bulk-action' );

				$list_table->display();
				?>

				<?php do_action( 'eb_after_log_table' ); ?>
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
					$this->multiple_log_delete( $post_data );
					break;
				default:
					break;
			}
		}

		/**
		 * Provides the functionality to unenroll multipal users from the course
		 *
		 * @param type $data bulk action data to unenroll users.
		 */
		private function multiple_log_delete( $data ) {
			global $wpdb;
			if ( ! isset( $data['error'] ) ) {
				return;
			}

			$keys     = $data['error'];
			$log_file = wdm_edwiser_bridge_plugin_log_dir() . 'log.json';
			$logs     = file_get_contents( $log_file ); // @codingStandardsIgnoreLine
			$logs     = json_decode( $logs, true );
			$cnt      = 0;

			foreach ( $keys as $key ) {
				if ( isset( $logs[ $key ] ) ) {
					unset( $logs[ $key ] );
					$cnt++;
				}
			}
			$logs = wp_json_encode( $logs );
			file_put_contents( $log_file, $logs ); // @codingStandardsIgnoreLine

			if ( $cnt > 0 ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<strong>
							<?php sprintf( '%s ', $cnt ) . esc_html_e( ' error logs are deleted successfully.', 'edwiser-bridge' ); ?>
						</strong>
					</p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">
						<?php
						esc_html_e( 'Dismiss this notice', 'edwiser-bridge' );
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
							<?php esc_html_e( 'No error log deleted', 'edwiser-bridge' ); ?>
						</strong>
					</p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">
						<?php
						esc_html_e( 'Dismiss this notice', 'edwiser-bridge' );
						?>
						.</span>
					</button>
				</div>
				<?php
			}
		}
	}
}

return new Eb_Error_Log();
