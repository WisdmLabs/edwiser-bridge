<?php
/**
 * This class is responsible to shopw manage enrollment table.
 *
 * @link       https://edwiser.org
 * @since      1.4
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( '\app\wisdmlabs\edwiserBridge\Eb_Custom_List_Table' ) ) {

	/**
	 * Custom list table.
	 */
	class Eb_Custom_List_Table extends \WP_List_Table {

		/**
		 * Bp_columns.
		 *
		 * @since    1.0.0
		 *
		 * @var string bp_columns.
		 */
		protected $bp_columns;

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Set parent defaults.
			parent::__construct(
				array(
					'singular' => 'enrollment',
					'plural'   => 'enrollments',
					'ajax'     => true,
				)
			);

			// Columns.
			$this->bp_columns = apply_filters(
				'edwiser_add_colomn_to_manage_enrollment',
				array(
					'cb'            => '<input type="checkbox" />',
					'rId'           => esc_html_x( 'Record ID', 'Column label', 'edwiser-bridge' ),
					'user'          => esc_html__( 'User', 'edwiser-bridge' ),
					'course'        => esc_html__( 'Course', 'edwiser-bridge' ),
					'enrolled_date' => esc_html__( 'Enrolled Date', 'edwiser-bridge' ),
					'manage'        => esc_html__( 'Manage', 'edwiser-bridge' ),
				)
			);
		}


		/**
		 * Get filter query.
		 *
		 * @param text $filter filter.
		 * @param text $search_text text.
		 * @param text $from from.
		 * @param text $to to.
		 * @param text $order order.
		 * @param text $per_page per_page.
		 * @param text $offset offset.
		 * @param text $stmt stmt.
		 */
		private function eb_get_filter_query( $filter, $search_text, $from, $to, $order, $per_page, $offset, $stmt ) {
			global $wpdb;
			$column     = '';
			$where      = '';
			$post_table = '';
			$user_table = '';
			$date       = empty( $from ) ? '' : "time> '" . esc_sql( $from ) . "'" . ( empty( $to ) ? '' : " AND time< '" . esc_sql( $to ) . "'" );

			// There are 2 filters which need join query.
			// 1. Course name.
			// 2. User name.
			if ( 'course' === $filter ) {
				$column     = ' p.post_title ';
				$order      = $column . $order;
				$where      = 'e.course_id=p.id';
				$post_table = ', ' . $wpdb->posts . ' p ';
				$stmt  = $wpdb->prepare( "SELECT e.*, {$column} FROM {$wpdb->prefix}moodle_enrollment e  {$post_table} {$user_table}  WHERE {$where} ORDER BY {$order} LIMIT %d OFFSET %d", $per_page, $offset ); // @codingStandardsIgnoreLine.

				if ( ! empty( $search_text ) ) {
					$stmt = $wpdb->prepare( "SELECT e.*, {$column} FROM {$wpdb->prefix}moodle_enrollment e, {$wpdb->posts} p WHERE {$where} AND p.post_title like %s ORDER BY {$order} LIMIT %d OFFSET %d", '%' . $search_text . '%', $per_page, $offset ); // @codingStandardsIgnoreLine.
				}
			} elseif ( 'user' === $filter ) {
				$column     = ' u.user_login ';
				$order      = $column . $order;
				$where      = 'e.user_id=u.ID';
				$user_table = ', ' . $wpdb->users . ' u';
				$stmt  = $wpdb->prepare( "SELECT e.*, {$column} FROM {$wpdb->prefix}moodle_enrollment e  {$post_table} {$user_table}  WHERE {$where} ORDER BY {$order} LIMIT %d OFFSET %d", $per_page, $offset ); // @codingStandardsIgnoreLine.

				if ( ! empty( $search_text ) ) {
					$stmt = $wpdb->prepare( "SELECT e.*, u.user_login FROM {$wpdb->prefix}moodle_enrollment e LEFT JOIN {$wpdb->posts} p ON p.ID=e.course_id LEFT JOIN {$wpdb->users} u ON u.ID=e.user_id WHERE p.post_title like %s AND u.ID=e.user_id  ORDER BY u.user_login", '%' . $search_text . '%' );
				}
			}

			if ( ! empty( $from ) || ! empty( $to ) ) {
				$stmt = $wpdb->prepare( "SELECT e.*, {$column} FROM {$wpdb->prefix}moodle_enrollment e {$post_table} {$user_table}  WHERE {$where} AND {$date} ORDER BY {$order} LIMIT %d OFFSET %d", $per_page, $offset ); // @codingStandardsIgnoreLine.
			}

			if ( ( ! empty( $from ) || ! empty( $to ) ) && ! empty( $search_text ) ) {
				$stmt = $wpdb->prepare( "SELECT e.*, {$column} FROM wp_moodle_enrollment e LEFT JOIN wp_posts p ON p.ID=e.course_id LEFT JOIN wp_users u ON e.user_id=u.ID WHERE p.post_title like %s AND time>= %s  AND time<= %s ORDER BY {$order} LIMIT %d OFFSET %d", '%' . $search_text . '%', $from, $to, $per_page, $offset ); // @codingStandardsIgnoreLine.
			}

			return $stmt;
		}


		/**
		 * Get table.
		 *
		 * @param text $post_data post_data.
		 * @param text $search_text text.
		 * @param text $current_page current_page.
		 */
		public function bp_get_table( $post_data, $search_text, $current_page ) {
			global $wpdb;
			$per_page = 20;

			if ( isset( $_REQUEST['eb-manage-user-enrol'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['eb-manage-user-enrol'] ) ), 'eb-manage-user-enrol' ) ) {
				$post_data = $_REQUEST;
			} else {
				$post_data = array();
			}
			$search_text = isset( $post_data['ebemt_search'] ) ? sanitize_text_field( wp_unslash( $post_data['ebemt_search'] ) ) : '';
			$from        = isset( $post_data['enrollment_from_date'] ) ? sanitize_text_field( wp_unslash( $post_data['enrollment_from_date'] ) ) : '';
			$to          = isset( $post_data['enrollment_to_date'] ) ? sanitize_text_field( wp_unslash( $post_data['enrollment_to_date'] ) ) : '';
			$date        = empty( $from ) ? '' : "time> '" . esc_sql( $from ) . "'" . ( empty( $to ) ? '' : " AND time< '" . esc_sql( $to ) . "'" );

			// If no sort, default to title.
			$order_by = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';
			// If no order, default to asc.
			$order = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'asc';

			$allowed_orderby = $this->get_sortable_columns();
			if ( ! array_key_exists( $order_by, $allowed_orderby ) ) {
				$order_by = 'id';
			}
			if ( ! in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ) {
				$order = 'ASC';
			}
			if ( 'rId' === $order_by ) {
				$order_by = 'id';
			} elseif ( 'enrolled_date' === $order_by ) {
				$order_by = 'time';
			}

			$order_by = $wpdb->_real_escape( $order_by );
			$order    = $wpdb->_real_escape( $order );

			$order_query = $order_by . ' ' . strtoupper( $order );

			// Determine sort order.
			$tbl_records = array();
			$offset      = ( $current_page - 1 ) * $per_page;
			$stmt        = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moodle_enrollment ORDER BY {$order_query} LIMIT %d OFFSET %d", $per_page, $offset ); // @codingStandardsIgnoreLine.

			if ( ! empty( $search_text ) ) {
				$stmt = $wpdb->prepare( "SELECT e.* FROM {$wpdb->prefix}moodle_enrollment e, {$wpdb->posts} p where e.course_id=p.id AND p.post_title like %s ORDER BY {$order_query} LIMIT %d OFFSET %d", '%' . $search_text . '%', $per_page, $offset ); // @codingStandardsIgnoreLine.
			}

			if ( ! empty( $from ) || ! empty( $to ) ) {
				$stmt = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moodle_enrollment  WHERE {$date} ORDER BY {$order_query} LIMIT %d OFFSET %d", $per_page, $offset ); // @codingStandardsIgnoreLine.
			}

			if ( ( ! empty( $from ) || ! empty( $to ) ) && ! empty( $search_text ) ) {
				$stmt = $wpdb->prepare( "SELECT e.* FROM {$wpdb->prefix}moodle_enrollment e, {$wpdb->posts} p where e.course_id=p.id AND p.post_title like %s AND {$date} ORDER BY {$order_query} LIMIT %d OFFSET %d", '%' . $search_text . '%', $per_page, $offset ); // @codingStandardsIgnoreLine.
			}

			// Need to check above if conditions again because of prepare statements as direct concatenating is prohibited.
			// Also Not creating one common query with all tables as it will take time for all other searches.
			if ( ! empty( $order_by ) && 'id' !== $order_by && 'time' !== $order_by ) {
				$stmt = $this->eb_get_filter_query( $order_by, $search_text, $from, $to, $order, $per_page, $offset, $stmt );
			}

			$results = $wpdb->get_results( $stmt ); // @codingStandardsIgnoreLine

			foreach ( $results as $result ) {
				$profile_url          = $this->get_user_profile_url( $result->user_id );
				$row                  = array();
				$row['user_id']       = $result->user_id;
				$row['user']          = $profile_url;
				$row['course']        = '<a href="' . esc_url( get_permalink( $result->course_id ) ) . '">' . get_the_title( $result->course_id ) . '</a>';
				$row['enrolled_date'] = $result->time;
				$row['manage']        = true;
				$row['ID']            = $result->id;
				$row['rId']           = $result->id;
				$row['course_id']     = $result->course_id;

				$tbl_records[] = apply_filters( 'eb_manage_student_enrollment_each_row', $row, $result, $search_text );
			}

			$table_data    = apply_filters( 'eb_manage_student_enrollment_table_data', $tbl_records );
			$total_records = $this->eb_get_enrollment_total_record( $search_text, $from, $to ); // WPCS: CSRF ok, input var ok. // @codingStandardsIgnoreLine
			return array(
				'total_records' => $total_records,
				'data'          => $table_data,
			);
		}

		/**
		 * Returns the user profile link.
		 *
		 * @param string $search_text search_text.
		 * @param string $from from date.
		 * @param string $to to date.
		 * @return type
		 */
		public function eb_get_enrollment_total_record( $search_text, $from, $to ) {
			global $wpdb;
			$stmt = "SELECT * FROM {$wpdb->prefix}moodle_enrollment";

			if ( ! empty( $search_text ) ) {
				$stmt = $wpdb->prepare( "SELECT e.* FROM {$wpdb->prefix}moodle_enrollment e, {$wpdb->posts} p where e.course_id=p.id AND p.post_title like %s", '%' . $search_text . '%' );
			}

			if ( ! empty( $from ) ) {
				$stmt = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moodle_enrollment  WHERE  time> %s ", $from );
			}

			if ( ! empty( $from ) && ! empty( $to ) ) {
				$stmt = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moodle_enrollment  WHERE  time>= %s  AND time<= %s ", $from, $to );
			}

			if ( ! empty( $from ) && ! empty( $search_text ) ) {
				$stmt = $wpdb->prepare( "SELECT e.* FROM {$wpdb->prefix}moodle_enrollment e, {$wpdb->posts} p where e.course_id=p.id AND p.post_title like %s AND time>= %s ", '%' . $search_text . '%', $from ); // @codingStandardsIgnoreLine.
			}

			if ( ! empty( $from ) && ! empty( $to ) && ! empty( $search_text ) ) {
				$stmt = $wpdb->prepare( "SELECT e.* FROM {$wpdb->prefix}moodle_enrollment e, {$wpdb->posts} p where e.course_id=p.id AND p.post_title like %s AND time>= %s  AND time<= %s ", '%' . $search_text . '%', $from, $to );
			}

			$total_result_stmt = $wpdb->get_results( $stmt ); // @codingStandardsIgnoreLine
			return count( $total_result_stmt );
		}

		/**
		 * Returns the user profile link.
		 *
		 * @param type $user_id user_id.
		 * @return type
		 */
		private function get_user_profile_url( $user_id ) {
			$user_name = '';
			$user_info = get_userdata( $user_id );
			if ( $user_info ) {
				$edit_link = get_edit_user_link( $user_id );
				$user_name = '<a href="' . esc_url( $edit_link ) . '">' . $user_info->user_login . '</a>';
			}
			return $user_name;
		}

		/**
		 * Get columns.
		 */
		public function get_columns() {
			return $this->bp_columns;
		}

		/**
		 * Get sortable columns
		 */
		protected function get_sortable_columns() {
			$sortable_columns = array(
				'rId'           => array( 'rId', false ),
				'course'        => array( 'course', false ),
				'user'          => array( 'user', false ),
				'enrolled_date' => array( 'enrolled_date', false ),
			);
			return $sortable_columns;
		}

		/**
		 * Get default column value.
		 *
		 * Recommended. This method is called when the parent class can't find a method
		 * specifically build for a given column. Generally, it's recommended to include
		 * one method for each column you want to render, keeping your package class
		 * neat and organized. For example, if the class needs to process a column
		 * named 'title', it would first see if a method named $this->column_title()
		 * exists - if it does, that method will be used. If it doesn't, this one will
		 * be used. Generally, you should try to use custom column methods as much as
		 * possible.
		 *
		 * Since we have defined a column_title() method later on, this method doesn't
		 * need to concern itself with any column with a name of 'title'. Instead, it
		 * needs to handle everything else.
		 *
		 * For more detailed insight into how columns are handled, take a look at
		 * WP_List_Table::single_row_columns()
		 *
		 * @param object $item        A singular item (one full row's worth of data).
		 * @param string $column_name The name/slug of the column to be processed.
		 * @return string Text or HTML to be placed inside the column <td>.
		 */
		protected function column_default( $item, $column_name ) {
			// from 1.3.5.
			return $item[ $column_name ];
		}

		/**
		 * Get value for checkbox column.
		 *
		 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
		 * is given special treatment when columns are processed. It ALWAYS needs to
		 * have it's own method.
		 *
		 * @param object $item A singular item (one full row's worth of data).
		 * @return string Text to be placed inside the column <td>.
		 */
		protected function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['singular'],
				$item['ID']
			);
		}

		/**
		 * Get title column value.
		 *
		 * Recommended. This is a custom column method and is responsible for what
		 * is rendered in any column with a name/slug of 'title'. Every time the class
		 * needs to render a column, it first looks for a method named
		 * column_{$column_title} - if it exists, that method is run. If it doesn't
		 * exist, column_default() is called instead.
		 *
		 * This example also illustrates how to implement rollover actions. Actions
		 * should be an associative array formatted as 'slug'=>'link html' - and you
		 * will need to generate the URLs yourself. You could even ensure the links are
		 * secured with wp_nonce_url(), as an expected security measure.
		 *
		 * @param object $item A singular item (one full row's worth of data).
		 * @return string Text to be placed inside the column <td>.
		 */
		protected function column_rId( $item ) { // @codingStandardsIgnoreLine
			return sprintf( '%1$s', $item['rId'] );
		}

		/**
		 * Column maneg.
		 *
		 * @param text $item item.
		 */
		protected function column_manage( $item ) {
			$output = '---';
			if ( $item['manage'] ) {
				$output = apply_filters( 'edwiser_unenroll_column_in_manage_enrollment', '<a class="eb-unenrol" data-user-id="' . $item['user_id'] . '" data-record-id="' . $item['ID'] . '" data-course-id="' . $item['course_id'] . '">' . esc_html__( 'Unenroll', 'edwiser-bridge' ) . '</a>' );
			}
			return $output;
		}

		/**
		 * Get an associative array ( option_name => option_title ) with the list
		 * of bulk actions available on this table.
		 *
		 * Optional. If you need to include bulk actions in your list table, this is
		 * the place to define them. Bulk actions are an associative array in the format
		 * 'slug'=>'Visible Title'
		 *
		 * If this method returns an empty value, no bulk action will be rendered. If
		 * you specify any bulk actions, the bulk actions box will be rendered with
		 * the table automatically on display().
		 *
		 * Also note that list tables are not automatically wrapped in <form> elements,
		 * so you will need to create those manually in order for bulk actions to function.
		 *
		 * @return array An associative array containing all the bulk actions.
		 */
		protected function get_bulk_actions() {
			$actions = array(
				'unenroll' => esc_html_x( 'Bulk Unenroll', 'Unenrolles the selected students from the courses', 'edwiser-bridge' ),
			);
			return $actions;
		}

		/**
		 * Handle bulk actions.
		 *
		 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
		 * For this example package, we will handle it in the class to keep things
		 * clean and organized.
		 *
		 * @param text $post_data post_data.
		 * @see $this->prepare_items()
		 */
		protected function process_bulk_action( $post_data ) {
			// Detect when a bulk action is being triggered.
			if ( 'unenroll' === $this->current_action() ) {
				if ( isset( $post_data['enrollment'] ) && is_array( $post_data['enrollment'] ) && count( $post_data['enrollment'] ) ) {
					$this->unerollUser( sanitize_text_field( wp_unslash( $post_data['enrollment'] ) ) );
				} else {
					echo '<div class="notice notice-error is-dismissible">';
					echo '<p>' . esc_html__( 'No records selected to unenroll student, Please select the records to unenroll', 'edwiser-bridge' ) . '</p>';
					echo '</div>';
				}
			}
		}

		/**
		 * Extra table nav.
		 *
		 * @param text $which which.
		 */
		public function extra_tablenav( $which ) {
			if ( isset( $_REQUEST['eb-manage-user-enrol'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['eb-manage-user-enrol'] ) ), 'eb-manage-user-enrol' ) ) {
				$from = isset( $_REQUEST['enrollment_from_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['enrollment_from_date'] ) ) : '';
				$to   = isset( $_REQUEST['enrollment_to_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['enrollment_to_date'] ) ) : '';
			} else {
				$from = '';
				$to   = '';
			}
			if ( 'top' === $which ) {
				?>
				<div class="alignleft actions bulkactions eb_manage_enroll_custom_nav_wrap">
					<lable><?php echo esc_html__( 'From : ', 'edwiser-bridge' ); ?></lable>
					<input type="date" id="enrollment_from_date" name="enrollment_from_date" value="<?php echo esc_html( $from ); ?>">
					<lable><?php echo esc_html__( 'To : ', 'edwiser-bridge' ); ?></lable>
					<input type="date" id="enrollment_to_date" name="enrollment_to_date" value="<?php echo esc_html( $to ); ?>">
					<input type="submit" name="eb_manage_enroll_dt_search" id="eb_manage_enroll_dt_search" class="button action" value="<?php echo esc_html__( 'Filter', 'edwiser-bridge' ); ?>"/>
				</div>				
				<?php
			}
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 * REQUIRED! This is where you prepare your data for display. This method will
		 * usually be used to query the database, sort and filter the data, and generally
		 * get it ready to be displayed. At a minimum, we should set $this->items and
		 * $this->set_pagination_args(), although the following properties and methods
		 * are frequently interacted with here.
		 *
		 * @global wpdb $wpdb
		 * @uses $this->_column_headers
		 * @uses $this->items
		 * @uses $this->get_columns()
		 * @uses $this->get_sortable_columns()
		 * @uses $this->get_pagenum()
		 * @uses $this->set_pagination_args()
		 */
		public function prepare_items() {
			/*
			 * First, lets decide how many records per page to show
			 */
			$per_page = 20;

			$options = array();

			/*
			 * REQUIRED. Now we need to define our column headers. This includes a complete
			 * array of columns to be displayed (slugs & titles), a list of columns
			 * to keep hidden, and a list of columns that are sortable. Each of these
			 * can be defined in another method (as we've done here) before being
			 * used to build the value for our _column_headers property.
			 */
			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();

			/*
			 * REQUIRED. Finally, we build an array to be used by the class for column
			 * headers. The $this->_column_headers property takes an array which contains
			 * three other arrays. One for all columns, one for hidden columns, and one
			 * for sortable columns.
			 */
			$this->_column_headers = array( $columns, $hidden, $sortable );

			/*
			 * REQUIRED for pagination. Let's figure out what page the user is currently
			 * looking at. We'll need this later, so you should always include it in
			 * your own package classes.
			 */
			$current_page = $this->get_pagenum();

			/**
			 * Optional. You can handle your bulk actions however you see fit. In this
			 * case, we'll handle them within our package just to keep things clean.
			 */

			if ( isset( $_REQUEST['eb-manage-user-enrol'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['eb-manage-user-enrol'] ) ), 'eb-manage-user-enrol' ) ) {
				$this->process_bulk_action( $_POST );

				$search_text = isset( $_REQUEST['ebemt_search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ebemt_search'] ) ) : '';

				$options['ebemt_search'] = $search_text;

				// adding nonce in the options so that data will not be lost on pagination.
				$options['eb-manage-user-enrol'] = sanitize_text_field( wp_unslash( $_REQUEST['eb-manage-user-enrol'] ) );

				if ( isset( $_REQUEST['enrollment_from_date'] ) ) {
					$options['enrollment_from_date'] = sanitize_text_field( wp_unslash( $_REQUEST['enrollment_from_date'] ) );
				}
				if ( isset( $_REQUEST['enrollment_to_date'] ) ) {
					$options['enrollment_to_date'] = sanitize_text_field( wp_unslash( $_REQUEST['enrollment_to_date'] ) );
				}
			} else {
				$search_text = '';
			}

			$table_data = $this->bp_get_table( $_REQUEST, $search_text, $current_page );
			$data       = $table_data['data'];

			/*
			 * REQUIRED for pagination. Let's check how many items are in our data array.
			 * In real-world use, this would be the total number of items in your database,
			 * without filtering. We'll need this later, so you should always include it
			 * in your own package classes.
			 */
			$total_items = $table_data['total_records'];

			/*
			 * REQUIRED. Now we can add our *sorted* data to the items property, where
			 * it can be used by the rest of the class.
			 */
			$this->items = $data;

			/**
			 * REQUIRED. We also have to register our pagination options & calculations.
			 */
			$this->set_pagination_args(
				array(
					'total_items' => $total_items, // WE have to calculate the total number of items.
					'per_page'    => $per_page, // WE have to determine how many items to show on a page.
					'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
				)
			);

			// Update the current URI with the new options.
			$_SERVER['REQUEST_URI'] = add_query_arg( $options, $_SERVER['REQUEST_URI'] ); // @codingStandardsIgnoreLine
		}
	}
}
