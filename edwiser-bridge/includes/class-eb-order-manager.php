<?php

/**
 * This class defines all code necessary to manage user's course orders'.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EBOrderManager
{

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
     * @var EB_Course_Manager The single instance of the class
     *
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * Main EBOrderManager Instance.
     *
     * Ensures only one instance of EBOrderManager is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     *
     * @see EBOrderManager()
     *
     * @return EBOrderManager - Main instance
     */
    public static function instance($plugin_name, $version)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($plugin_name, $version);
        }

        return self::$instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since   1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'eb-textdomain'), '1.0.0');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since   1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'eb-textdomain'), '1.0.0');
    }

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * get status of an order by order id.
     *
     * @since  1.0.0
     *
     * @param int $order_id id of an order
     *
     * @return string $order_status   current status of an order
     */
    public function getOrderStatus($order_id)
    {
        //get previous status
        $plugin_post_types = new EBPostTypes($this->plugin_name, $this->version);
        $order_status = $plugin_post_types->getPostOptions($order_id, 'order_status', 'eb_order');

        return $order_status;
    }


    /**
     * update order status on saving an order from edit order page.
     *
     * calls updateOrderStatus()
     *
     * @since  1.0.0
     *
     * @param int $order_id id of an order
     *
     * @return
     */
    public function updateOrderStatusOnOrderSave($order_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        $post_options = isset($_POST['eb_order_options']) ? $_POST['eb_order_options'] : array();
        if (empty($post_options)) {
            return false;
        }

        if (!empty($post_options) && isset($post_options['order_status'])) {
            $this->updateOrderStatus($order_id, $post_options['order_status'], $post_options);
        }


        // $this->updateOrderStatusForNewOrder($order_id, $post_options);
    }

    /**
     * update order status and all meta-data on new order creation.
     *
     * @since 1.3.1
     *
     * @param int $order_id     id of order
     * @param int $order_status new status of order ( completed, pending or failed )
     *
     * @return bool
     */
    public function updateOrderStatusForNewOrder($order_id, $order_options)
    {
        $eb_order_options['buyer_id'] = $order_options['eb_order_username'];
        $eb_order_options['order_status'] = $order_options['order_status'];
        $eb_order_options['course_id'] = $order_options['eb_order_course'];
        // $eb_order_options['creation_date'] = strtotime($order_options['eb_order_date']);

        update_post_meta($order_id, 'eb_order_options', $eb_order_options);
    }



    /**
     * Change status of an order.
     *
     * @since 1.0.0
     *
     * @param int $order_id     id of order
     * @param int $order_status new status of order ( completed, pending or failed )
     *
     * @return bool
     */
    public function updateOrderStatus($order_id, $order_status, $post_options = array())
    {
        // get previous status
        $plugin_post_types = new EBPostTypes($this->plugin_name, $this->version);
        $previous_status = $plugin_post_types->getPostOptions($order_id, 'order_status', 'eb_order');

        if (!$previous_status || $previous_status != $order_status) {
            edwiserBridgeInstance()->logger()->add('order', 'Updating order status...'); // add order log

            $order_options = get_post_meta($order_id, 'eb_order_options', true);

            /**
             * Unenroll the user if the order is get marked as pending or failed form the compleated.
             */

            if (isset($order_options['order_status']) && $order_options['order_status'] == "completed" && $order_status != "completed") {
                $enrollmentManager = EBEnrollmentManager::instance($this->plugin_name, $this->version);
                $ordDetail=get_post_meta($order_id, 'eb_order_options', true);
                $args = array(
                    'user_id' => $ordDetail['buyer_id'],
                    'role_id' => 5,
                    'courses' => array($order_options['course_id']),
                    'unenroll' => 1,
                    'suspend' => 0,
                );
                $enrollmentManager->updateUserCourseEnrollment($args);
            }

            if (isset($order_options) && !empty($order_options)) {
                foreach ($order_options as $key => $option) {
                    $option;
                    if ($key == 'order_status') {
                        $order_options[$key] = $order_status;
                    }
                }
                update_post_meta($order_id, 'eb_order_options', $order_options);
            } else {
                $this->updateOrderStatusForNewOrder($order_id, $post_options);
            }
            do_action('eb_order_status_' . $order_status, $order_id);
        }
        edwiserBridgeInstance()->logger()->add('order', 'Order status updated, Status: ' . $order_status); // add order log
        return 1;
    }

    /**
     * used to insert a new order in database
     * executed by createNewOrderAjaxWrapper() on paid course purchase.
     *
     * @since 1.0.0
     *
     * @param array $order_data accepts order meta data
     *
     * @return int $order_id   returns id of newly created order or error object
     */
    public function createNewOrder($order_data = array())
    {
        edwiserBridgeInstance()->logger()->add('order', 'Creating new order...'); // add order log

        $buyer_id = '';
        if (isset($order_data['buyer_id'])) {
            $buyer_id = $order_data['buyer_id'];
        }
        $course_id = '';
        if (isset($order_data['course_id'])) {
            $course_id = $order_data['course_id'];
        }
        $order_status = 'pending';
        if (isset($order_data['order_status'])) {
            $order_status = $order_data['order_status'];
        }

        if (empty($buyer_id) || empty($course_id) || empty($order_status)) {
            return new \WP_Error('warning', __('Order details are not correct. Existing', 'eb-textdomain'));
        }

        // get buyer details
        //$buyer = get_userdata($buyer_id);

        $course_title = '';
        $course = get_post($course_id);

        if (!empty($course)) {
            $course_title = $course->post_title;
        }

        $order_id = wp_insert_post(
            array(
                    'post_title' => sprintf(__("Course %s", 'eb-textdomain'), $course_title),
                    'post_type' => 'eb_order',
                    'post_status' => 'publish',
                    'post_author' => 1,
                )
        );

        if (!is_wp_error($order_id)) {
            //update order meta
            $price= $this->getCoursePrice($course_id);
            $price = apply_filters("eb_new_order_course_price", $price, $order_data);
            update_post_meta(
                $order_id,
                'eb_order_options',
                array(
                    'order_status' => $order_status,
                    'buyer_id' => $buyer_id,
                    'course_id' => $course_id,
                    'price' => $price,
                )
            );
        }

        edwiserBridgeInstance()->logger()->add('order', 'New order created, Order ID: ' . $order_id); // add order log

        /*
         * hooks to execute a function on new order creation
         * $order id is passed as argument
         */
        do_action('eb_order_created', $order_id);

        return $order_id;
    }

    /**
     * Provides the functionality to get the courses price from course meta.
     *
     * @since 1.3.0
     * @param type $courseId
     * @return string returns the courses associated price.
     */
    private function getCoursePrice($courseId)
    {
        $courseMeta = get_post_meta($courseId, "eb_course_options", true);
        $price="0.00";
        $courseType= getArrValue($courseMeta, "course_price_type", false);
        if ($courseType && $courseType=="paid") {
            $price = getArrValue($courseMeta, "course_price", "0.00");
        }
        return $price;
    }

    /**
     * used to create an order by ajax
     * runs when clicking 'take this course' button for integrated paypal payment gateway ( for paid courses ).
     *
     * sends details of new order to ajax call
     *
     * @since 1.0.0
     *
     * @return array order details
     */
    public function createNewOrderAjaxWrapper()
    {
        if (!isset($_POST['_wpnonce_field'])) {
            die('Busted!');
        }

        // verifying generated nonce we created earlier
        if (!wp_verify_nonce($_POST['_wpnonce_field'], 'public_js_nonce')) {
            die('Busted!');
        }

        $success = 0;
        $order_id = 0;

        $buyer_id = '';
        if (isset($_POST['buyer_id'])) {
            $buyer_id = $_POST['buyer_id'];
        }
        $course_id = '';
        if (isset($_POST['course_id'])) {
            $course_id = $_POST['course_id'];
        }

        if (empty($buyer_id) || empty($course_id)) {
            $success = 0;
        } else {
            $order_id_created = $this->createNewOrder(array('buyer_id' => $buyer_id, 'course_id' => $course_id));

            if (!is_wp_error($order_id_created)) {
                $success = 1;
                $order_id = $order_id_created;

                /**
                 * @since 1.2.4
                 *update post meta if the sandbox is enabled for each order if the sandbox is enabled
                 */
                $options = get_option("eb_paypal");
                if (isset($options["eb_paypal_sandbox"]) && $options["eb_paypal_sandbox"] == "yes") {
                    update_post_meta($order_id, "eb_paypal_sandbox", "yes");
                }

                if (isset($options['eb_paypal_currency']) && !empty($options['eb_paypal_currency'])) {
                    update_post_meta($order_id, 'eb_paypal_currency', $options['eb_paypal_currency']);
                }
            }
        }

        // response
        $response = json_encode(array('success' => $success, 'order_id' => $order_id));
        echo $response;
        die();
    }

    /**
     * runs on order completion hook
     * enroll buyer to associated course on order completion.
     *
     * called by: do_action( 'eb_order_status_completed' );
     *
     * @since  1.0.0
     *
     * @param int $order_id id of the order
     *
     * @return bool true / false
     */
    public function enrollToCourseOnOrderComplete($order_id)
    {
        // get order options
        $order_options = get_post_meta($order_id, 'eb_order_options', true);

        if (!isset($order_options['buyer_id']) || !isset($order_options['course_id'])) {
            return;
        }

        $buyer_id = $order_options['buyer_id'];
        $course_id = $order_options['course_id'];

        if (is_numeric($course_id)) {
            $course = get_post($course_id);
        } else {
            return;
        }

        //return if post type is not eb_course
        if ($course->post_type != 'eb_course' || empty($buyer_id)) {
            return;
        }

        // get current user object
        $buyer = get_userdata($buyer_id);

        // link existing moodle account or create a new one
        edwiserBridgeInstance()->userManager()->linkMoodleUser($buyer);

        //$course_meta = get_post_meta( $course_id, "eb_course_options", true );
        // define args
        $args = array(
            'user_id' => $buyer_id,
            'courses' => array($course_id),
        );

        $course_enrolled = edwiserBridgeInstance()->enrollmentManager()->updateUserCourseEnrollment($args); // enroll user to course

        return $course_enrolled;
    }

    /**
     * add order status and ordered by columns to orders table in admin.
     *
     * @since  1.0.0
     *
     * @param array $columns default columns array
     *
     * @return array $new_columns   updated columns array
     */
    public function addOrderStatusColumn($columns)
    {
        $new_columns = array(); // new columns array

        foreach ($columns as $k => $value) {
            if ($k === 'title') {
                $new_columns[$k] = __('Order Title', 'eb-textdomain');
            } else {
                $new_columns[$k] = $value;
            }

            if ($k === 'title') {
                $new_columns['order_status'] = __('Order Status', 'eb-textdomain');
                $new_columns['ordered_by'] = __('Ordered By', 'eb-textdomain');
            }
        }

        return $new_columns;
    }

    /**
     * add a content to order status column.
     *
     * @since  1.0.0
     *
     * @param array $columns name of a column
     */
    public function addOrderStatusColumnContent($column_name, $post_ID)
    {
        if ($column_name == 'order_status') {
            $status = EBPostTypes::getPostOptions($post_ID, 'order_status', 'eb_order');
            $options = array(
                'pending' => __('Pending', 'eb-textdomain'),
                'completed' => __('Completed', 'eb-textdomain'),
                'failed' => __('Failed', 'eb-textdomain'),
            );
            echo isset($options[$status]) ? $options[$status] : ucfirst($status);
        } elseif ($column_name == 'ordered_by') {
            //get order details
            $order_buyer_id = EBPostTypes::getPostOptions($post_ID, 'buyer_id', 'eb_order');

            $buyer = get_userdata($order_buyer_id); // buyer details

            if (!$buyer) {
                echo '-';
                return;
            }

            $buyer_name = '';
            if (isset($buyer->first_name) && isset($buyer->last_name)) {
                $buyer_name = $buyer->first_name . ' ' . $buyer->last_name;
            }

            if ($buyer_name == '') {
                $buyer_name = $buyer->user_login;
            }
            echo "<a href='" . get_edit_user_link($order_buyer_id) . "'>" . $buyer_name . '</a>';
        }
    }
}
