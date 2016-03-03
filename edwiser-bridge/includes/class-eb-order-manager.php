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
     * get details of an order by order id.
     *
     * @since  1.0.0
     *
     * @param int $order_id id of an order
     *
     * @return string order details
     */
    public function getOrderDetails($order_id)
    {

        //get order billing id & email
        $order_data = get_post_meta($order_id, 'eb_order_options', true);

        if (!is_array($order_data)) {
            $order_data = array();
        }

        echo '<div>';
        echo '<h2>Order #'.$order_id.' Details</h2>';
        foreach ($order_data as $key => $value) {
            if ($key == 'buyer_id') {
                echo '<strong>Buyer ID: </strong>'.$value.'<br/>';
            } elseif ($key == 'billing_email') {
                echo '<strong>Billing Email: </strong>'.$value.'<br/>';
            } else {
                continue;
            }
        }
        echo '</div>';

        //get ordered item id
        $course_id = get_post_meta($order_id, 'course_id', true);
        //return if order does not have an item(course) associated
        if (!is_numeric($course_id)) {
            return;
        }

        //return array( 'buyer_id' => $buyer_id, 'billing_email' => $billing_email, 'course_id' => $course_id );
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

        $post_options = isset($_POST[ 'eb_order_options' ]) ? $_POST[ 'eb_order_options' ] : array();
        if (empty($post_options)) {
            return false;
        }

        if (!empty($post_options) && isset($post_options['order_status'])) {
            $this->updateOrderStatus($order_id, $post_options['order_status']);
        }
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
    public function updateOrderStatus($order_id, $order_status)
    {

        // get previous status
        $plugin_post_types = new EBPostTypes($this->plugin_name, $this->version);
        $previous_status = $plugin_post_types->getPostOptions($order_id, 'order_status', 'eb_order');

        if (!$previous_status || $previous_status != $order_status) {
            edwiserBridgeInstance()->logger()->add('order', 'Updating order status...'); // add order log

            $order_options = get_post_meta($order_id, 'eb_order_options', true);

            foreach ($order_options as $key => $option) {
                $option;
                if ($key == 'order_status') {
                    $order_options[$key] = $order_status;
                }
            }

            update_post_meta($order_id, 'eb_order_options', $order_options);
            do_action('eb_order_status_'.$order_status, $order_id);
        }

        edwiserBridgeInstance()->logger()->add('order', 'Order status updated, Status: '.$order_status); // add order log

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

        if (empty($buyer_id) || empty($course_id) || empty($order_status)) {
            return new \WP_Error('warning', __('Order details are not correct. Exiting', 'edw'));
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
                'post_title' => "Course {$course_title}",
                'post_type' => 'eb_order',
                'post_status' => 'publish',
                'post_author' => 1,
            )
        );

        if (!is_wp_error($order_id)) {
            //update order meta
            update_post_meta(
                $order_id,
                'eb_order_options',
                array(
                    'order_status' => $order_status,
                    'buyer_id' => $buyer_id,
                    'course_id' => $course_id,
                )
            );
        }

        edwiserBridgeInstance()->logger()->add('order', 'New order created, Order ID: '.$order_id); // add order log

        /*
         * hooks to execute a function on new order creation
         * $order id is passed as argument
         */
        do_action('eb_order_created', $order_id);

        return $order_id;
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
        // $course_enrolled = edwiserBridgeInstance()->enrollment_manager()->update_user_course_enrollment(
        //      $buyer_id,
        //      array( $course_id )
        // );

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
                $new_columns[$k] = 'Order Title';
            } else {
                $new_columns[$k] = $value;
            }

            if ($k === 'title') {
                $new_columns['order_status'] = 'Order Status';
                $new_columns['ordered_by'] = 'Ordered By';
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
            echo ucfirst(EBPostTypes::getPostOptions($post_ID, 'order_status', 'eb_order'));
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
                $buyer_name = $buyer->first_name.' '.$buyer->last_name;
            }

            if ($buyer_name == '') {
                $buyer_name = $buyer->user_login;
            }
            echo "<a href='".get_edit_user_link($order_buyer_id)."'>".$buyer_name.'</a>';
        }
    }
}
