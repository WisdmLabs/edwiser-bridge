<?php
/**
 * This class defines all code necessary to manage user's course orders'.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order manager.
 */
class Eb_Order_Manager {
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
	 * COntrsuctor.
	 *
	 * @param int $plugin_name plugin_name.
	 * @param int $version version.
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
	 * COntrsuctor.
	 *
	 * @param int $plugin_name plugin_name.
	 * @param int $version version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * NOT USED FUNCTION
	 * get status of an order by order id.
	 *
	 * @since  1.0.0
	 *
	 * @param int $order_id id of an order.
	 *
	 * @return string $order_status   current status of an order
	 */
	public function get_order_status( $order_id ) {
		// get previous status.
		$plugin_post_types = new Eb_Post_Types( $this->plugin_name, $this->version );
		$order_status      = $plugin_post_types->get_post_options( $order_id, 'order_status', 'eb_order' );

		return $order_status;
	}


	/**
	 * Update order status on saving an order from edit order page.
	 *
	 * @since  1.0.0
	 *
	 * @param int $order_id id of an order.
	 */
	public function update_order_status_on_order_save( $order_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $order_id;
		}

		if ( ! isset( $_POST['eb_post_meta_nonce'] ) || ! isset( $_POST['eb_post_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eb_post_meta_nonce'] ) ), 'eb_post_meta_nonce' ) ) {
			return $order_id;
		}

		$post_options = isset( $_POST['eb_order_options'] ) ? \app\wisdmlabs\edwiserBridge\wdm_eb_edwiser_sanitize_array( $_POST['eb_order_options'] ) : array(); //@codingStandardsIgnoreLine.

		if ( empty( $post_options ) ) {
			return false;
		}

		if ( ! empty( $post_options ) && isset( $post_options['order_status'] ) ) {

			$this->update_order_status( $order_id, $post_options['order_status'], $post_options );
		}

	}

	/**
	 * Update order status and all meta-data on new order creation.
	 *
	 * @since 1.3.1
	 *
	 * @param int $order_id     id of order.
	 * @param int $order_options new status of order ( completed, pending or failed ).
	 */
	public function update_order_status_for_new_order( $order_id, $order_options ) {
		$eb_order_options['buyer_id']     = $order_options['eb_order_username'];
		$eb_order_options['order_status'] = $order_options['order_status'];
		$eb_order_options['course_id']    = $order_options['eb_order_course'];

		update_post_meta( $order_id, 'eb_order_options', $eb_order_options );
	}



	/**
	 * Change status of an order.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id     id of order.
	 * @param int $order_status new status of order ( completed, pending or failed ).
	 * @param int $post_options new status of order ( completed, pending or failed ).
	 */
	public function update_order_status( $order_id, $order_status, $post_options = array() ) {
		// get previous status.
		$plugin_post_types = new Eb_Post_Types( $this->plugin_name, $this->version );
		$previous_status   = $plugin_post_types->get_post_options( $order_id, 'order_status', 'eb_order' );

		if ( ! $previous_status || $previous_status !== $order_status ) {
			edwiser_bridge_instance()->logger()->add( 'order', 'Updating order status...' ); // add order log.

			$order_options = get_post_meta( $order_id, 'eb_order_options', true );

			/**
			 * Unenroll the user if the order is get marked as pending or failed form the compleated.
			 */
			if ( isset( $order_options['order_status'] ) && 'completed' === $order_options['order_status'] && 'completed' !== $order_status ) {
				$enrollment_manager = Eb_Enrollment_Manager::instance( $this->plugin_name, $this->version );
				$ord_detail         = get_post_meta( $order_id, 'eb_order_options', true );
				$args               = array(
					'user_id'  => $ord_detail['buyer_id'],
					'role_id'  => 5,
					'courses'  => array( $order_options['course_id'] ),
					'unenroll' => 1,
					'suspend'  => 0,
				);

				$enrollment_manager->update_user_course_enrollment( $args );

			}

			if ( isset( $order_options ) && ! empty( $order_options ) ) {
				foreach ( $order_options as $key => $option ) {
					$option;
					if ( 'order_status' === $key ) {
						$order_options[ $key ] = $order_status;
					}
				}
				update_post_meta( $order_id, 'eb_order_options', $order_options );
			} else {
				$this->update_order_status_for_new_order( $order_id, $post_options );
			}
			do_action( 'eb_order_status_' . $order_status, $order_id );
		}
		edwiser_bridge_instance()->logger()->add( 'order', 'Order status updated, Status: ' . $order_status ); // add order log.
		return 1;
	}

	/**
	 * Used to insert a new order in database
	 * executed by createNewOrderAjaxWrapper() on paid course purchase.
	 *
	 * @since 1.0.0
	 *
	 * @param array $order_data accepts order meta data.
	 *
	 * @return int $order_id   returns id of newly created order or error object
	 */
	public function create_new_order( $order_data = array() ) {
		edwiser_bridge_instance()->logger()->add( 'order', 'Creating new order...' ); // add order log.

		$buyer_id = '';
		if ( isset( $order_data['buyer_id'] ) ) {
			$buyer_id = $order_data['buyer_id'];
		}
		$course_id = '';
		if ( isset( $order_data['course_id'] ) ) {
			$course_id = $order_data['course_id'];
		}
		$order_status = 'pending';
		if ( isset( $order_data['order_status'] ) ) {
			$order_status = $order_data['order_status'];
		}

		if ( empty( $buyer_id ) || empty( $course_id ) || empty( $order_status ) ) {
			return new \WP_Error( 'warning', esc_html__( 'Order details are not correct. Existing', 'edwiser-bridge' ) );
		}

		// get buyer details.

		$course_title = '';
		$course       = get_post( $course_id );

		if ( ! empty( $course ) ) {
			$course_title = $course->post_title;
		}

		$order_id = wp_insert_post(
			array(
				'post_title'  => esc_html__( 'Course ', 'edwiser-bridge' ) . sprintf( '%s', $course_title ),
				'post_type'   => 'eb_order',
				'post_status' => 'publish',
				'post_author' => 1,
			)
		);

		if ( ! is_wp_error( $order_id ) ) {

			// update order meta.
			$price = $this->get_course_price( $course_id );
			$price = apply_filters( 'eb_new_order_course_price', $price, $order_data );
			update_post_meta(
				$order_id,
				'eb_order_options',
				array(
					'order_status' => $order_status,
					'buyer_id'     => $buyer_id,
					'course_id'    => $course_id,
					'price'        => $price,
				)
			);
		}

		edwiser_bridge_instance()->logger()->add( 'order', 'New order created, Order ID: ' . $order_id ); // add order log.

		/*
		 * hooks to execute a function on new order creation
		 * $order id is passed as argument
		 */
		do_action( 'eb_order_created', $order_id );

		return $order_id;
	}

	/**
	 * Provides the functionality to get the courses price from course meta.
	 *
	 * @since 1.3.0
	 * @param type $course_id course_id.
	 * @return string returns the courses associated price.
	 */
	private function get_course_price( $course_id ) {
		$course_meta = get_post_meta( $course_id, 'eb_course_options', true );
		$price       = '0.00';
		$course_type = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $course_meta, 'course_price_type', false );
		if ( $course_type && 'paid' === $course_type ) {
			$price = \app\wisdmlabs\edwiserBridge\wdm_eb_get_value_from_array( $course_meta, 'course_price', '0.00' );
		}
		return $price;
	}

	/**
	 * Used to create an order by ajax
	 * runs when clicking 'take this course' button for integrated paypal payment gateway ( for paid courses ).
	 *
	 * @since 1.0.0
	 */
	public function create_new_order_ajax_wrapper() {

		// verifying generated nonce we created earlier.
		if ( ! isset( $_POST['_wpnonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_field'] ) ), 'public_js_nonce' ) ) {
			die( 'Busted!' );
		}

		$success  = 0;
		$order_id = 0;
		$buyer_id = '';
		if ( isset( $_POST['buyer_id'] ) ) {
			$buyer_id = sanitize_text_field( wp_unslash( $_POST['buyer_id'] ) );
		}
		$course_id = '';
		if ( isset( $_POST['course_id'] ) ) {
			$course_id = sanitize_text_field( wp_unslash( $_POST['course_id'] ) );
		}

		if ( empty( $buyer_id ) || empty( $course_id ) ) {
			$success = 0;
		} else {
			$order_id_created = $this->create_new_order(
				array(
					'buyer_id'  => $buyer_id,
					'course_id' => $course_id,
				)
			);

			if ( ! is_wp_error( $order_id_created ) ) {

				$success  = 1;
				$order_id = $order_id_created;

				/**
				 * Update post meta if the sandbox is enabled for each order if the sandbox is enabled.
				 */
				$options = get_option( 'eb_paypal' );
				if ( isset( $options['eb_paypal_sandbox'] ) && 'yes' === $options['eb_paypal_sandbox'] ) {

					update_post_meta( $order_id, 'eb_paypal_sandbox', 'yes' );
				}

				if ( isset( $options['eb_paypal_currency'] ) && ! empty( $options['eb_paypal_currency'] ) ) {

					update_post_meta( $order_id, 'eb_paypal_currency', $options['eb_paypal_currency'] );
				}
			}
		}

		// response.
		$response = array(
			'success'  => $success,
			'order_id' => $order_id,
			'nonce'    => wp_create_nonce( 'eb_paypal_nonce' ),
		);

		wp_send_json( $response );
		die();
	}

	/**
	 * Runs on order completion hook
	 * enroll buyer to associated course on order completion.
	 *
	 * @since  1.0.0
	 *
	 * @param int $order_id id of the order.
	 *
	 * @return bool true / false
	 */
	public function enroll_to_course_on_order_complete( $order_id ) {
		// get order options.
		$order_options   = get_post_meta( $order_id, 'eb_order_options', true );
		$course_enrolled = false;
		if ( isset( $order_options['buyer_id'] ) || isset( $order_options['course_id'] ) ) {
			$buyer_id  = $order_options['buyer_id'];
			$course_id = $order_options['course_id'];

			if ( is_numeric( $course_id ) ) {
				$course = get_post( $course_id );
				if ( 'eb_course' === $course->post_type || empty( $buyer_id ) ) {
					$buyer = get_userdata( $buyer_id );

					// link existing moodle account or create a new one.
					edwiser_bridge_instance()->user_manager()->link_moodle_user( $buyer );

					// define args.
					$args            = array(
						'user_id' => $buyer_id,
						'courses' => array( $course_id ),
					);
					$course_enrolled = edwiser_bridge_instance()->enrollment_manager()->update_user_course_enrollment( $args ); // enroll user to course.
				}
			}
		}
		return $course_enrolled;
	}

	/**
	 * Add order status and ordered by columns to orders table in admin.
	 *
	 * @since  1.0.0
	 *
	 * @param array $columns default columns array.
	 *
	 * @return array $new_columns   updated columns array
	 */
	public function add_order_status_column( $columns ) {
		$new_columns = array(); // new columns array.

		foreach ( $columns as $k => $value ) {
			if ( 'title' === $k ) {
				$new_columns[ $k ] = esc_html__( 'Order Title', 'edwiser-bridge' );
			} else {
				$new_columns[ $k ] = $value;
			}

			if ( 'title' === $k ) {
				$new_columns['order_status'] = esc_html__( 'Order Status', 'edwiser-bridge' );
				$new_columns['ordered_by']   = esc_html__( 'Ordered By', 'edwiser-bridge' );
			}
		}

		return $new_columns;
	}

	/**
	 * Add a content to order status column.
	 *
	 * @since  1.0.0
	 *
	 * @param array $column_name name of a column.
	 * @param array $post_id post_id.
	 */
	public function add_order_status_column_content( $column_name, $post_id ) {
		if ( 'order_status' === $column_name ) {
			$status  = Eb_Post_Types::get_post_options( $post_id, 'order_status', 'eb_order' );
			$options = array(
				'pending'   => esc_html__( 'Pending', 'edwiser-bridge' ),
				'completed' => esc_html__( 'Completed', 'edwiser-bridge' ),
				'failed'    => esc_html__( 'Failed', 'edwiser-bridge' ),
				'refunded'  => esc_html__( 'Refunded', 'edwiser-bridge' ),
			);
			echo isset( $options[ $status ] ) ? esc_html( $options[ $status ] ) : esc_html( ucfirst( $status ) );
		} elseif ( 'ordered_by' === $column_name ) {
			// get order details.
			$order_buyer_id = Eb_Post_Types::get_post_options( $post_id, 'buyer_id', 'eb_order' );

			$buyer = get_userdata( $order_buyer_id ); // buyer details.

			if ( ! $buyer ) {
				echo '-';
				return;
			}

			$buyer_name = '';
			if ( isset( $buyer->first_name ) && isset( $buyer->last_name ) ) {
				$buyer_name = $buyer->first_name . ' ' . $buyer->last_name;
			}

			if ( '' === $buyer_name ) {
				$buyer_name = $buyer->user_login;
			}
			echo "<a href='" . esc_html( get_edit_user_link( $order_buyer_id ) ) . "'>" . esc_html( $buyer_name ) . '</a>';
		}
	}
}
