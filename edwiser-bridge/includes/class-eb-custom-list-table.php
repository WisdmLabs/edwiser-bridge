<?php

namespace app\wisdmlabs\edwiserBridge;

if (!class_exists('\WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if (!class_exists('\app\wisdmlabs\edwiserBridge\EBCustomListTable')) {

    class EBCustomListTable extends \WP_List_Table
    {

        protected $bpColumns;

        public function __construct()
        {
            // Set parent defaults.
            parent::__construct(array(
                'singular' => 'enrollment',
                'plural' => 'enrollments',
                'ajax' => true,
            ));

            // Columns.
            $this->bpColumns = apply_filters('edwiser_add_colomn_to_manage_enrollment', array(
                'cb' => '<input type="checkbox" />',
                'rId' => _x('Record ID', 'Column label', 'eb-textdomain'),
                'user' => __('User', 'eb-textdomain'),
                'course' => __('Course', 'eb-textdomain'),
                'enrolled_date' => __('Enrolled Date', 'eb-textdomain'),
                'manage' => __('Manage', 'eb-textdomain'),
            ));
        }

        public function bpGetTable($searchText)
        {
            global $wpdb;
            $tblRecords = array();
            $stmt = "SELECT * FROM {$wpdb->prefix}moodle_enrollment";
            $results = $wpdb->get_results($stmt);
            foreach ($results as $result) {
                /*if (!empty($searchText)) {
                    $user_info = get_userdata($result->user_id);
                    if (strpos($user_info->user_login, $searchText) === false && strpos(get_the_title($result->course_id), $searchText) === false) {
                        continue;
                    }
                }*/

                $row = array();
                $row['user_id'] = $result->user_id;
                $row['user'] = $this->getUserProfileURL($result->user_id) ;
                $row['course'] = '<a href="' . esc_url(get_permalink($result->course_id)) . '">' . get_the_title($result->course_id) . '</a>';
                $row['enrolled_date'] = $result->time;
                $row['manage'] = true;
                $row['ID'] = $result->id;
                $row['rId'] = $result->id;
                $row['course_id'] = $result->course_id;
                $tblRecords[] = apply_filters('eb_manage_student_enrollment_each_row', $row, $searchText);
            }


            return apply_filters("eb_manage_student_enrollment_table_data", $tblRecords);
        }

        /**
         * Returns the user profile link.
         * @param type $userId
         * @param type $with_a
         * @param type $default
         * @return type
         */
        private function getUserProfileURL($userId)
        {
            $userName = "";
            $user_info = get_userdata($userId);
            if ($user_info) {
                $edit_link = get_edit_user_link($userId);
                $userName = '<a href="' . esc_url($edit_link) . '">' . $user_info->user_login . '</a>';
            }
            return $userName;
        }

        public function get_columns()
        {
            return $this->bpColumns;
        }

        protected function get_sortable_columns()
        {
            $sortable_columns = array(
                'rId' => array('rId', false),
                'course' => array('course', false),
                'user' => array('user', false),
                'enrolled_date' => array('enrolled_date', false),
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
        protected function column_default($item, $column_name)
        {
            /*switch ($column_name) {
                default:
                    return $item[$column_name];
            }*/
            // from 1.3.5
            return $item[$column_name];
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
        protected function column_cb($item)
        {
            return sprintf(
                    '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['ID']
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
        protected function column_rId($item)
        {
            return sprintf('%1$s', $item['rId']);
        }

        protected function column_manage($item)
        {
            $outPut="---";
            if ($item['manage']) {
                $outPut=apply_filters('edwiser_unenroll_column_in_manage_enrollment', '<a class="eb-unenrol" data-user-id="' . $item['user_id'] . '" data-record-id="' . $item['ID'] . '" data-course-id="' . $item['course_id'] . '">' . __('Unenroll', 'eb-textdomain') . '</a>');
            }
            return $outPut;
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
        protected function get_bulk_actions()
        {
            $actions = array(
                'unenroll' => _x('Bulk Unenroll', 'Unenrolles the selected students from the courses', 'eb-textdomain'),
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
         * @see $this->prepare_items()
         */
        protected function process_bulk_action()
        {
            // Detect when a bulk action is being triggered.
            if ('unenroll' === $this->current_action()) {
                if (isset($_POST["enrollment"]) && is_array($_POST["enrollment"]) && count($_POST["enrollment"])) {
                    $this->unerollUser($_POST["enrollment"]);
                } else {
                    echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>' . __("No records selected to unenroll student, Please select the records to unenroll", 'eb-textdomain') . '</p>';
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
        function prepare_items()
        {
            /*
             * First, lets decide how many records per page to show
             */
            $per_page = 20;

            /*
             * REQUIRED. Now we need to define our column headers. This includes a complete
             * array of columns to be displayed (slugs & titles), a list of columns
             * to keep hidden, and a list of columns that are sortable. Each of these
             * can be defined in another method (as we've done here) before being
             * used to build the value for our _column_headers property.
             */
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();

            /*
             * REQUIRED. Finally, we build an array to be used by the class for column
             * headers. The $this->_column_headers property takes an array which contains
             * three other arrays. One for all columns, one for hidden columns, and one
             * for sortable columns.
             */
            $this->_column_headers = array($columns, $hidden, $sortable);

            /**
             * Optional. You can handle your bulk actions however you see fit. In this
             * case, we'll handle them within our package just to keep things clean.
             */
            $this->process_bulk_action();

            $searchText = "";

            if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])) {
                $searchText = $_REQUEST['s'];
            }

            $data = $this->bpGetTable($searchText);

            /*
             * This checks for sorting input and sorts the data in our array of dummy
             * data accordingly (using a custom usort_reorder() function). It's for
             * example purposes only.
             *
             * In a real-world situation involving a database, you would probably want
             * to handle sorting by passing the 'orderby' and 'order' values directly
             * to a custom query. The returned data will be pre-sorted, and this array
             * sorting technique would be unnecessary. In other words: remove this when
             * you implement your own query.
             */
            usort($data, array($this, 'usort_reorder'));

            /*
             * REQUIRED for pagination. Let's figure out what page the user is currently
             * looking at. We'll need this later, so you should always include it in
             * your own package classes.
             */
            $current_page = $this->get_pagenum();

            /*
             * REQUIRED for pagination. Let's check how many items are in our data array.
             * In real-world use, this would be the total number of items in your database,
             * without filtering. We'll need this later, so you should always include it
             * in your own package classes.
             */
            $total_items = count($data);

            /*
             * The WP_List_Table class does not handle pagination for us, so we need
             * to ensure that the data is trimmed to only the current page. We can use
             * array_slice() to do that.
             */
            $data = array_slice($data, ( ( $current_page - 1 ) * $per_page), $per_page);

            /*
             * REQUIRED. Now we can add our *sorted* data to the items property, where
             * it can be used by the rest of the class.
             */
            $this->items = $data;

            /**
             * REQUIRED. We also have to register our pagination options & calculations.
             */
            $this->set_pagination_args(array(
                'total_items' => $total_items, // WE have to calculate the total number of items.
                'per_page' => $per_page, // WE have to determine how many items to show on a page.
                'total_pages' => ceil($total_items / $per_page), // WE have to calculate the total number of pages.
            ));
        }

        /**
         * Callback to allow sorting of example data.
         *
         * @param string $first First value.
         * @param string $second Second value.
         *
         * @return int
         */
        protected function usort_reorder($first, $second)
        {
            // If no sort, default to title.
            $orderby = !empty($_REQUEST['orderby']) ? wp_unslash($_REQUEST['orderby']) : 'rId'; // WPCS: Input var ok.
            // If no order, default to asc.
            $order = !empty($_REQUEST['order']) ? wp_unslash($_REQUEST['order']) : 'asc'; // WPCS: Input var ok.
            // Determine sort order.
            $result = strcmp($first[$orderby], $second[$orderby]);

            return ( 'asc' === $order ) ? $result : - $result;
        }
    }
}
