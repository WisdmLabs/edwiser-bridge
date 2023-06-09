<?php
/**
 * Manage enrollement.
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

if ( ! class_exists( '\app\wisdmlabs\edwiserBridge\Eb_Manage_Enrollment' ) ) {

	/**
	 * Manage enrollment.
	 */
	class Eb_Manage_Enrollment {


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
		 * Main Eb_Order_Manager Instance.
		 *
		 * Ensures only one instance of Eb_Order_Manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @see Eb_Order_Manager()
		 *
		 * @param text $plugin_name plgin name.
		 * @param text $version plgin version.
		 * @return Eb_Order_Manager - Main instance
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
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'edwiser-bridge' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since   1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'edwiser-bridge' ), '1.0.0' );
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
			$list_table     = new Eb_Custom_List_Table();
			$current_action = $list_table->current_action();
			$this->handle_bulk_action( $current_action );
			$list_table->prepare_items();
			$post_page        = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // WPCS: CSRF ok, input var ok. // @codingStandardsIgnoreLine
			$search_text      = isset( $_REQUEST['ebemt_search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ebemt_search'] ) ) : ''; // WPCS: CSRF ok, input var ok. // @codingStandardsIgnoreLine
			$from             = isset( $_REQUEST['enrollment_from_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['enrollment_from_date'] ) ) : ''; // WPCS: CSRF ok, input var ok. // @codingStandardsIgnoreLine
			$to               = isset( $_REQUEST['enrollment_to_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['enrollment_to_date'] ) ) : ''; // WPCS: CSRF ok, input var ok. // @codingStandardsIgnoreLine
			$eb_total_records = isset( $_REQUEST['eb_enrollment_total_records'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['eb_enrollment_total_records'] ) ) : $list_table->eb_get_enrollment_total_record( $search_text, $from, $to ); // WPCS: CSRF ok, input var ok. // @codingStandardsIgnoreLine
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

				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

				<div class="eb-notices" id="eb-notices"><!-- Add custom notices inside this. --></div>
				<?php do_action( 'eb_before_manage_user_enrollment_table' ); ?>
				<form id="eb-manage-user-enrollment-filter" method="post">
				<p class='search-box'>
					<input type="text" id="ebemt_search" name="ebemt_search" value="<?php echo esc_html( $search_text ); ?>">
					<input type="submit" name="eb_manage_enroll_search" id="eb_manage_enroll_search" class="button action" value="<?php echo esc_html__( 'Search Courses', 'edwiser-bridge' ); ?>"/>
				</p>
					<input type="hidden" name="page" value="<?php echo esc_html( $post_page ); ?>" />
					<input type="hidden" name="eb_enrollment_total_records" value="<?php echo esc_html( $eb_total_records ); ?>" />
					<?php
					wp_nonce_field( 'eb-manage-user-enrol', 'eb-manage-user-enrol' );

					// will add search box in next update.
					$list_table->display();
					?>
				</form>
				<?php do_action( 'eb_after_manage_user_enrollment_table' ); ?>
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
			if ( ! isset( $_POST['eb-manage-user-enrol'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eb-manage-user-enrol'] ) ), 'eb-manage-user-enrol' ) ) {
				$post_data = array();
			} else {
				$post_data = $_POST;
			}
			switch ( $action ) {
				case 'unenroll':
					$this->multiple_unenroll_by_rec_id( $post_data );
					break;
				default:
					break;
			}
		}

		/**
		 * Provides the functionality to unenroll multipal users from the course
		 *
		 * @param type $data bulk action data to unenroll users.
		 * @return type
		 */
		private function multiple_unenroll_by_rec_id( $data ) {
			global $wpdb;
			if ( ! isset( $data['enrollment'] ) ) {
				return;
			}

			$users      = $data['enrollment'];
			$enroll_tbl = $wpdb->prefix . 'moodle_enrollment';
			$query      = $wpdb->prepare( "select user_id,course_id from {$wpdb->prefix}moodle_enrollment where id in(%s)", implode( "','", $users ) );
			$query      = wp_unslash( $query );
			$results    = $wpdb->get_results( $query, ARRAY_A ); // WPCS: unprepared SQL OK. // @codingStandardsIgnoreLine
			$cnt        = 0;

			foreach ( $results as $rec ) {

				if ( $this->unenroll_user( $rec['course_id'], $rec['user_id'] ) ) {

					$cnt++;
				}
			}
			if ( $cnt > 0 ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<strong>
							<?php sprintf( '%s ', $cnt ) . esc_html_e( 'users has been unenrolled successfully.', 'edwiser-bridge' ); ?>
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
							<?php esc_html_e( 'No users has been unenrolled', 'edwiser-bridge' ); ?>
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

		/**
		 * Ajax callback to unenroo the users from the database
		 */
		public function unenroll_user_ajax_handler() {
			$response = esc_html__( 'Failed to unenroll user', 'edwiser-bridge' );
			if ( isset( $_POST['user_id'] ) && isset( $_POST['course_id'] ) && isset( $_POST['action'] ) && 'wdm_eb_user_manage_unenroll_unenroll_user' === $_POST['action'] && isset( $_POST['admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['admin_nonce'] ) ), 'eb_admin_nonce' ) ) {

				$course_id = sanitize_text_field( wp_unslash( $_POST['course_id'] ) );
				$user_id   = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
				$res       = $this->unenroll_user( $course_id, $user_id );
				if ( $res ) {
					$course_name = get_the_title( $course_id );
					$user        = get_userdata( $user_id );
					$response    = ucfirst( $user->user_login ) . esc_html__( ' has been unenrolled from the ', 'edwiser-bridge' ) . $course_name . esc_html__( ' course', 'edwiser-bridge' );
					wp_send_json_success( $response );
				} else {
					wp_send_json_error( $response );
				}
			} else {
				wp_send_json_error( $response );
			}
		}

		/**
		 * Provides the functionality to unenroll the user from the course
		 *
		 * @param type $course_id course_id.
		 * @param type $user_id user_id.
		 * @return bolean returns ture if the user is unenrolled from the course
		 * othrewise returns false.
		 */
		private function unenroll_user( $course_id, $user_id ) {
			/**
			 * This is commented due to the error Avoid using static access to class
			 * This doesn't allow the class to call other class statically
			 */

			$enrollment_manager = new Eb_Enrollment_Manager( $this->plugin_name, $this->version );

			$args = array(
				'user_id'           => $user_id,
				'role_id'           => 5,
				'courses'           => array( $course_id ),
				'unenroll'          => 1,
				'suspend'           => 0,
				'complete_unenroll' => 1,
			);
			return $enrollment_manager->update_user_course_enrollment( $args );
		}

		/**
		 * NOT USED FUNCTION
		 *
		 * @param type $moodle_course_id moodle_course_id.
		 */
		public function get_wp_post_id( $moodle_course_id ) {
			global $wpdb;
			$result = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_value=%s AND meta_key = 'moodle_course_id'", $moodle_course_id ) ); // @codingStandardsIgnoreLine

			return $result;
		}
	}
}
