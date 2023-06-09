<?php
/**
 * This class is responsible to show manage error log table.
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

if ( ! class_exists( '\app\wisdmlabs\edwiserBridge\Eb_Error_Logs_Table' ) ) {

	/**
	 * Custom list table.
	 */
	class Eb_Error_Logs_Table extends \WP_List_Table {

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
					'singular' => 'error',
					'plural'   => 'errors',
					'ajax'     => true,
				)
			);

			// Columns.
			$this->bp_columns = apply_filters(
				'edwiser_add_colomn_to_log_table',
				array(
					'cb'     => '<input type="checkbox" />',
					'title'  => esc_html__( 'Title', 'edwiser-bridge' ),
					'view'   => esc_html__( 'View', 'edwiser-bridge' ),
					'user'   => esc_html__( 'User', 'edwiser-bridge' ),
					'status' => esc_html_x( 'Status', 'Column label', 'edwiser-bridge' ),
					'rcode'  => esc_html__( 'Response Code', 'edwiser-bridge' ),
					'time'   => esc_html__( 'Timestamp', 'edwiser-bridge' ),
				)
			);
		}


		/**
		 * Get table.
		 *
		 * @param text $post_data post_data.
		 * @param text $search_text text.
		 * @param text $current_page current_page.
		 */
		public function eb_get_table( $post_data, $search_text, $current_page ) {
			$log_file = wdm_edwiser_bridge_plugin_log_dir() . 'log.json';
			if ( ! file_exists( $log_file ) ) {
				return array(
					'total_records' => 0,
					'data'          => array(),
				);
			}
			$logs = file_get_contents( $log_file ); // @codingStandardsIgnoreLine
			$logs = json_decode( $logs, true );

			if ( ! is_array( $logs ) ) {
				$logs = array();
			}
			$tbl_records = array();

			foreach ( $logs as $key => $log ) {
				$row           = array();
				$row['key']    = $key;
				$row['status'] = $log['status'];
				$row['title']  = $log['data']['message'];
				$row['user']   = $log['data']['user'];
				$row['rcode']  = $log['data']['responsecode'];
				$row['time']   = $log['time'];
				$row['view']   = true;

				$tbl_records[] = apply_filters( 'eb_logs_each_row', $row, $logs, $search_text );
			}

			$table_data    = apply_filters( 'eb_logs_table_data', $tbl_records );
			$total_records = count( $logs );
			return array(
				'total_records' => $total_records,
				'data'          => $table_data,
			);
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
			$sortable_columns = array();
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
				$item['key']
			);
		}

		/**
		 * Column maneg.
		 *
		 * @param text $item item.
		 */
		protected function column_view( $item ) {
			$output = '-';
			if ( $item['view'] ) {
				$key           = $item['key'];
				$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();
				$output        = apply_filters(
					'edwiser_view_column_in_log_table',
					'<a href="#" class="eb-error-log-view" data-log-id="' . $key . '" >
                <span class="eb-view-eye-' . $key . ' dashicons dashicons-visibility"></span>
                <span class="load-response-' . $key . '" style="display:none">
                    <img src="' . $eb_plugin_url . 'images/loader.gif" height="20" width="20" />
                </span>
                </a>'
				);
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
				'delete' => esc_html_x( 'Bulk Delete', 'Delete the selected error logs', 'edwiser-bridge' ),
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
			if ( 'delete' === $this->current_action() ) {
				if ( isset( $post_data['error'] ) && is_array( $post_data['error'] ) && count( $post_data['error'] ) ) { // @codingStandardsIgnoreLine
					// do something.
				} else {
					echo '<div class="notice notice-error is-dismissible">';
					echo '<p>' . esc_html__( 'No logs selected for bulk action, Please select the logs to delete', 'edwiser-bridge' ) . '</p>';
					echo '</div>';
				}
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

			$table_data = $this->eb_get_table( '', '', $current_page );
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
			$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

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
		}
	}
}
