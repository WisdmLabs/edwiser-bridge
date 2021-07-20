<?php
/**
 * This class defines all code necessary for moodle course synchronization.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Course manager.
 */
class Eb_Course_Manager {

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
	 * Instance.
	 *
	 * @var Eb_Course_Manager The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main Eb_Course_Manager Instance.
	 *
	 * Ensures only one instance of Eb_Course_Manager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @see Eb_Course_Manager()
	 * @param text $plugin_name plugin_name.
	 * @param text $version version.
	 * @return Eb_Course_Manager - Main instance
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
	 * Main Eb_Course_Manager contsructor.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @see Eb_Course_Manager()
	 * @param text $plugin_name plugin_name.
	 * @param text $version version.
	 * @return Eb_Course_Manager - Main instance
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Initiate the synchronization process.
	 * Called by course_synchronization_initiater() from class Eb_Settings_Ajax_Initiater.
	 *
	 * @param array $sync_options course sync options.
	 *
	 * @since   1.0.0
	 *
	 * @return array $response     array containing status & response message
	 */
	public function course_synchronization_handler( $sync_options = array() ) {
		edwiser_bridge_instance()->logger()->add( 'user', 'Initiating course & category sync process....' ); // add course log.
		$moodle_course_resp   = array(); // contains course response from moodle.
		$moodle_category_resp = array(); // contains category response from moodle.
		$response_array       = array(); // contains response message to be displayed to user.
		$courses_updated      = array(); // store updated course ids ( WordPress course ids ).
		$courses_created      = array(); // store newely created course ids ( WordPress course ids ).
		$eb_access_token      = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();
		$eb_access_url        = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();

		// checking if moodle connection is working properly.
		$connected = edwiser_bridge_instance()->connection_helper()->connection_test_helper( $eb_access_url, $eb_access_token );

		$response_array['connection_response'] = $connected['success']; // add connection response in response array.

		if ( 1 === $connected['success'] ) {
			/*
			 * Sync Moodle course categories to WordPress conditionally.
			 * Executes only if user chooses to sync categories.
			 */
			if ( isset( $sync_options['eb_synchronize_categories'] ) && '1' === $sync_options['eb_synchronize_categories'] ) {

				$moodle_category_resp = $this->get_moodle_course_categories(); // get categories from moodle.

				// creating categories based on recieved data.
				if ( 1 === $moodle_category_resp['success'] ) {

					$this->create_course_categories_on_wordpress( $moodle_category_resp['response_data'] );
				}

				// push category response in array.
				$response_array['category_success']          = $moodle_category_resp['success'];
				$response_array['category_response_message'] = $moodle_category_resp['response_message'];
			}

			/*
			 * sync moodle courses to WordPress.
			 */
			$moodle_course_resp = $this->get_moodle_courses(); // get courses from moodle.

			if ( ( isset( $sync_options['eb_synchronize_draft'] ) ) || ( isset( $sync_options['eb_synchronize_previous'] ) && '1' === $sync_options['eb_synchronize_previous'] ) ) {

				// creating courses based on recieved data.
				if ( 1 === $moodle_course_resp['success'] ) {
					foreach ( $moodle_course_resp['response_data'] as $course_data ) {
						/*
						 * moodle always returns moodle frontpage as first course,
						 * below step is to avoid the frontpage to be added as a course.
						 */
						if ( 1 === $course_data->id ) {
							continue;
						}

						// check if course is previously synced.
						$existing_course_id = $this->is_course_presynced( $course_data->id );

						// creates new course or updates previously synced course conditionally.
						if ( ! is_numeric( $existing_course_id ) ) {
							$course_id         = $this->create_course_on_wordpress( $course_data, $sync_options );
							$courses_created[] = $course_id; // push course id in courses created array.
						} elseif ( is_numeric( $existing_course_id ) &&
								isset( $sync_options['eb_synchronize_previous'] ) &&
								'1' === $sync_options['eb_synchronize_previous'] ) {
								$course_id     = $this->update_course_on_wordpress(
									$existing_course_id,
									$course_data,
									$sync_options
								);
							$courses_updated[] = $course_id; // push course id in courses updated array.

						}
					}
				}
				$response_array['course_success'] = $moodle_course_resp['success'];
				// push course response in array.
				$response_array['course_response_message'] = $moodle_course_resp['response_message'];
			}

			/*
			 * hook to be run on course completion
			 * we are passing all new created and updated courses as arg
			 */
			do_action( 'eb_course_synchronization_complete', $courses_created, $courses_updated, $sync_options );
		} else {
			edwiser_bridge_instance()->logger()->add(
				'course',
				'Connection problem in synchronization, Response:' . print_r( $connected, true ) // @codingStandardsIgnoreLine
			); // add connection log.
		}

		return $response_array;
	}




	/**
	 * DEPRECATED FUNCTION.
	 *
	 * Fetches the courses from moodle ( all courses or courses of a specfic user ).
	 *
	 * Uses connect_moodle_helper() and connect_moodle_with_args_helper()
	 *
	 * @deprecated since 2.0.1 use get_moodle_courses( $moodle_user_id ) insted.
	 * @param int $moodle_user_id  moodle user_id of a WordPress user passed to connection helper.
	 *
	 * @return array stores moodle web service response.
	 */
	public function getMoodleCourses( $moodle_user_id = null ) {
		return $this->get_moodle_courses( $moodle_user_id );
	}


	/**
	 * Fetches the courses from moodle ( all courses or courses of a specfic user ).
	 *
	 * Uses connect_moodle_helper() and connect_moodle_with_args_helper()
	 *
	 * @param int $moodle_user_id   moodle user_id of a WordPress user passed to connection helper.
	 *
	 * @return array stores moodle web service response.
	 */
	public function get_moodle_courses( $moodle_user_id = null ) {
		$response = '';

		if ( ! empty( $moodle_user_id ) ) {
			$webservice_function = 'core_enrol_get_users_courses'; // get a users enrolled courses from moodle.
			$request_data        = array( 'userid' => $moodle_user_id ); // prepare request data array.

			$response = edwiser_bridge_instance()->connection_helper()->connect_moodle_with_args_helper(
				$webservice_function,
				$request_data
			);

			// add course log.
			edwiser_bridge_instance()->logger()->add( 'course', 'User course response: ' . serialize( $response ) ); // @codingStandardsIgnoreLine
		} elseif ( empty( $moodle_user_id ) ) {
			$webservice_function = 'core_course_get_courses'; // get all courses from moodle.
			$response            = edwiser_bridge_instance()->connection_helper()->connect_moodle_helper( $webservice_function );
			// add course log.
			edwiser_bridge_instance()->logger()->add( 'course', 'Response: ' . serialize( $response ) ); // @codingStandardsIgnoreLine
		}

		return $response;
	}



	/**
	 * DEPRECATED FUNCTION
	 *
	 * Fetches the courses categories from moodle.
	 * uses connect_moodle_helper().
	 *
	 * @deprecated since 2.0.1 use get_moodle_course_categories( $webservice_function = null )  insted.
	 * @param string $webservice_function the webservice function passed to connection helper.
	 *
	 * @return array stores moodle web service response.
	 */
	public function getMoodleCourseCategories( $webservice_function = null ) {
		return $this->get_moodle_course_categories( $webservice_function );
	}


	/**
	 * Fetches the courses categories from moodle.
	 * uses connect_moodle_helper().
	 *
	 * @param string $webservice_function the webservice function passed to connection helper.
	 *
	 * @return array stores moodle web service response.
	 */
	public function get_moodle_course_categories( $webservice_function = null ) {
		if ( null === $webservice_function ) {
			$webservice_function = 'core_course_get_categories';
		}

		$response = edwiser_bridge_instance()->connection_helper()->connect_moodle_helper( $webservice_function );
		edwiser_bridge_instance()->logger()->add( 'course', serialize( $response ) ); // @codingStandardsIgnoreLine

		return $response;
	}


	/**
	 * DEPRECATED FUNCTION.
	 *
	 * Checks if a course is previously synced from moodle.
	 *
	 * @deprecated since 2.0.1 use is_course_presynced( $course_id_on_moodle ) insted.
	 * @param int $course_id_on_moodle the id of course as on moodle.
	 *
	 * @return bool returns respective course id on WordPress if exist else returns null
	 */
	public function isCoursePresynced( $course_id_on_moodle ) {
		return $this->is_course_presynced( $course_id_on_moodle );
	}


	/**
	 * Checks if a course is previously synced from moodle.
	 *
	 * @param int $course_id_on_moodle the id of course as on moodle.
	 *
	 * @return bool returns respective course id on WordPress if exist else returns null
	 */
	public function is_course_presynced( $course_id_on_moodle ) {
		global $wpdb;

		// get id of course on WordPress based on id on moodle $course_id =.
		$course_id = $wpdb->get_var( // @codingStandardsIgnoreLine
			$wpdb->prepare(
				"SELECT post_id
				FROM {$wpdb->prefix}postmeta
				WHERE meta_key = 'moodle_course_id'
				AND meta_value = %s",
				$course_id_on_moodle
			)
		);

		return $course_id;
	}


	/**
	 * Return the moodle id of a course using its WordPress id.
	 *
	 * @deprecated since 2.0.1 use get_moodle_course_id( $course_id_on_wp ) insted.
	 * @param int $course_id_on_wp the id of course synced on WordPress.
	 *
	 * @return int returns respective course id on moodle
	 */
	public function getMoodleCourseId( $course_id_on_wp ) {
		return $this->get_moodle_course_id( $course_id_on_wp );
	}

	/**
	 * Return the moodle id of a course using its WordPress id.
	 *
	 * @param int $course_id_on_wp the id of course synced on WordPress.
	 *
	 * @return int returns respective course id on moodle
	 */
	public function get_moodle_course_id( $course_id_on_wp ) {
		return get_post_meta( $course_id_on_wp, 'moodle_course_id', true );
	}

	/**
	 * Return the moodle id of a course using its WordPress id.
	 *
	 * @param int $course_id_on_wp the id of course synced on WordPress.
	 *
	 * @return int returns respective course id on moodle
	 */
	public function get_moodle_wp_course_id_pair( $course_id_on_wp ) {
		return array( $course_id_on_wp => get_post_meta( $course_id_on_wp, 'moodle_course_id', true ) );
	}

	/**
	 * Create course on WordPress.
	 *
	 * @deprecated since 2.0.1 use create_course_on_wordpress( $course_data, $sync_options = array() ) insted.
	 * @param array $course_data course data recieved from initiate_course_sync_process().
	 * @param array $sync_options course sync options.
	 *
	 * @return int returns id of course
	 */
	public function createCourseOnWordpress( $course_data, $sync_options = array() ) {
		return $this->create_course_on_wordpress( $course_data, $sync_options );
	}


	/**
	 * Create course on WordPress.
	 *
	 * @param array $course_data course data recieved from initiate_course_sync_process().
	 * @param text  $sync_options sync_options.
	 * @return int returns id of course
	 */
	public function create_course_on_wordpress( $course_data, $sync_options = array() ) {
		global $wpdb;

		$status = ( isset( $sync_options['eb_synchronize_draft'] ) &&
				'1' === $sync_options['eb_synchronize_draft'] ) ? 'draft' : 'publish'; // manage course status.

		$course_args = array(
			'post_title'   => $course_data->fullname,
			'post_content' => $course_data->summary,
			'post_status'  => $status,
			'post_type'    => 'eb_course',
		);

		$wp_course_id = wp_insert_post( $course_args ); // create a course on WordPress.

		$term_id = $wpdb->get_var( // @codingStandardsIgnoreLine
			$wpdb->prepare(
				"SELECT term_id
				FROM {$wpdb->prefix}termmeta
				WHERE meta_key = 'eb_moodle_cat_id'
				AND meta_value = %d",
				$course_data->categoryid
			)
		);

		// set course terms.
		if ( $term_id > 0 ) {
			wp_set_post_terms( $wp_course_id, $term_id, 'eb_course_cat' );
		}

		// add course id on moodle in corse meta on WP.
		$eb_course_options = array( 'moodle_course_id' => $course_data->id );
		add_post_meta( $wp_course_id, 'moodle_course_id', $course_data->id );
		add_post_meta( $wp_course_id, 'eb_course_options', $eb_course_options );

		/*
		 * execute your own action on course creation on WorPress
		 * we are passing newly created course id as well as its respective moodle id in arguments
		 *
		 * sync_options are also passed as it can be used in a custom action on hook.
		 */
		do_action( 'eb_course_created_wp', $wp_course_id, $course_data, $sync_options );

		return $wp_course_id;
	}


	/**
	 * DEPRECATED FUNCTION.
	 *
	 * Update previous synced course on WordPress.
	 *
	 * @deprecated since 2.0.1 use update_course_on_wordpress( $wp_course_id, $course_data, $sync_options ) insted.
	 * @param int   $wp_course_id existing id of course on WordPress.
	 * @param array $course_data  course data recieved from initiate_course_sync_process().
	 * @param array $sync_options  sync_options.
	 *
	 * @return int returns id of course
	 */
	public function updateCourseOnWordPress( $wp_course_id, $course_data, $sync_options ) {
		return $this->update_course_on_wordpress( $wp_course_id, $course_data, $sync_options );
	}


	/**
	 * Update previous synced course on WordPress.
	 *
	 * @param int   $wp_course_id existing id of course on WordPress.
	 * @param array $course_data  course data recieved from initiate_course_sync_process().
	 * @param array $sync_options  sync_options.
	 *
	 * @return int returns id of course
	 */
	public function update_course_on_wordpress( $wp_course_id, $course_data, $sync_options ) {
		global $wpdb;

		$course_args = array(
			'ID'           => $wp_course_id,
			'post_title'   => $course_data->fullname,
			'post_content' => $course_data->summary,
		);

		// updater course on WordPress.
		wp_update_post( $course_args );

		$term_id = $wpdb->get_var( // @codingStandardsIgnoreLine
			$wpdb->prepare(
				"SELECT term_id
				FROM {$wpdb->prefix}termmeta
				WHERE meta_key = 'eb_moodle_cat_id'
				AND meta_value = %d",
				$course_data->categoryid
			)
		);

		// set course terms.
		if ( $term_id > 0 ) {
			wp_set_post_terms( $wp_course_id, $term_id, 'eb_course_cat' );
		}

		/*
		 * execute your own action on course updation on WordPress
		 * we are passing newly created course id as well as its respective moodle id in arguments
		 *
		 * sync_options are also passed as it can be used in a custom action on hook.
		 */
		do_action( 'eb_course_updated_wp', $wp_course_id, $course_data, $sync_options );

		return $wp_course_id;
	}

	/**
	 * In case a course is permanentaly deleted from moodle course list,
	 * update course enrollment table appropriately by deleting records for course being deleted.
	 *
	 * @since  1.0.0
	 *
	 * @param int $course_id course_id.
	 */
	public function delete_enrollment_records_on_course_deletion( $course_id ) {
		global $wpdb;

		if ( 'eb_course' === get_post_type( $course_id ) ) {
			// removing course from enrollment table.
			$wpdb->delete( $wpdb->prefix . 'moodle_enrollment', array( 'course_id' => $course_id ), array( '%d' ) ); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * DEPRECATED FUNCTION.
	 *
	 * Uses the response recieved from get_eb_course_categories() function.
	 * craetes terms of eb_course_cat taxonomy.
	 *
	 * @deprecated since 2.0.1 use create_course_categories_on_wordpress( $category_response ) insted
	 * @param array $category_response accepts categories fetched from moodle.
	 */
	public function createCourseCategoriesOnWordpress( $category_response ) {
		$this->create_course_categories_on_wordpress( $category_response );
	}



	/**
	 * Uses the response recieved from get_eb_course_categories() function.
	 * craetes terms of eb_course_cat taxonomy.
	 *
	 * @param array $category_response accepts categories fetched from moodle.
	 */
	public function create_course_categories_on_wordpress( $category_response ) {
		global $wpdb;

		// sort category response by id in incremental order.
		usort( $category_response, '\app\wisdmlabs\edwiserBridge\wdm_eb_usort_numeric_callback' );

		foreach ( $category_response as $category ) {
			$cat_name_clean = preg_replace( '/\s*/', '', $category->name );
			$cat_name_lower = strtolower( $cat_name_clean );
			$parent         = ( 0 === $category->parent ? 0 : $category->parent );

			if ( $parent > 0 ) {
				// get parent term if exists.

				$parent_term = $wpdb->get_var( // @codingStandardsIgnoreLine
					$wpdb->prepare(
						"SELECT term_id
						FROM {$wpdb->prefix}termmeta
						WHERE meta_key = 'eb_moodle_cat_id'
						AND meta_value = %d",
						$category->parent
					)
				);

				if ( $parent_term && ! term_exists( $cat_name_lower, 'eb_course_cat', $parent_term ) ) {
					$created_term = wp_insert_term(
						$category->name,
						'eb_course_cat',
						array(
							'slug'        => $cat_name_lower,
							'parent'      => $parent_term,
							'description' => $category->description,
						)
					);

					if ( ! is_wp_error( $created_term ) && is_array( $created_term ) ) {
						update_term_meta( $created_term['term_id'], 'eb_moodle_cat_id', $category->id );
					}

					// Save the moodle id of category in options.
				}
			} else {
				if ( ! term_exists( $cat_name_lower, 'eb_course_cat' ) ) {
					$created_term = wp_insert_term(
						$category->name,
						'eb_course_cat',
						array(
							'slug'        => $cat_name_lower,
							'description' => $category->description,
						)
					);

					if ( ! is_wp_error( $created_term ) && is_array( $created_term ) ) {
						update_term_meta( $created_term['term_id'], 'eb_moodle_cat_id', $category->id );
					}

					// Save the moodle id of category in options.
				}
			}
		}
	}

	/**
	 * Add a new column price type to courses table in admin.
	 *
	 * @since  1.0.0
	 *
	 * @param array $columns default columns array.
	 *
	 * @return array $new_columns   updated columns array.
	 */
	public function add_course_price_type_column( $columns ) {
		$new_columns = array(); // new columns array.

		foreach ( $columns as $key => $value ) {
			if ( 'title' === $key ) {
				$new_columns[ $key ]          = esc_html__( 'Course Title', 'eb-textdomain' );
				$new_columns['mdl_course_id'] = esc_html__( 'Moodle Course Id', 'eb-textdomain' );
				$new_columns['course_type']   = esc_html__( 'Course Type', 'eb-textdomain' );
			} else {
				$new_columns[ $key ] = $value;
			}
			$new_columns = apply_filters( 'eb_course_each_table_header', $new_columns );
		}

		$new_columns = apply_filters( 'eb_course_table_headers', $new_columns );

		return $new_columns;
	}

	/**
	 * Add content to course price type column.
	 *
	 * @since  1.0.0
	 *
	 * @param array $column_name name of a column.
	 * @param array $post_id id of a column.
	 */
	public function add_course_price_type_column_content( $column_name, $post_id ) {

		if ( 'course_type' === $column_name ) {
			$status  = Eb_Post_Types::get_post_options( $post_id, 'course_price_type', 'eb_course' );
			$options = array(
				'free'   => esc_html__( 'Free', 'eb-textdomain' ),
				'paid'   => esc_html__( 'Paid', 'eb-textdomain' ),
				'closed' => esc_html__( 'Closed', 'eb-textdomain' ),
			);
			$status  = $status ? $status : 'free';
			echo esc_html( isset( $options[ $status ] ) ? $options[ $status ] : ucfirst( $status ) );
		} elseif ( 'mdl_course_id' === $column_name ) {
			$mdl_course_id      = Eb_Post_Types::get_post_options( $post_id, 'moodle_course_id', 'eb_course' );
			$mdl_course_deleted = Eb_Post_Types::get_post_options( $post_id, 'mdl_course_deleted', 'eb_course' );

			echo ! empty( $mdl_course_deleted ) ? '<span style="color:red;">' . esc_html__( 'Deleted', 'eb-textdomain' ) . '<span>' : esc_html( $mdl_course_id );
		}

		do_action( 'eb_course_table_content', $column_name, $post_id );
	}

	/**
	 * Adds the view moodle course link in courses list table for admin.
	 *
	 * @param array  $actions An array of row action links. .
	 * @param object $post post object.
	 */
	public function view_moodle_course_link( $actions, $post ) {
		if ( 'eb_course' === $post->post_type ) {
			$eb_access_url          = wdm_edwiser_bridge_plugin_get_access_url();
			$mdl_course_id          = $this->get_moodle_course_id( $post->ID );
			$course_url             = $eb_access_url . '/course/view.php?id=' . $mdl_course_id;
			$actions['moodle_link'] = "<a href='{$course_url}' title='' target='_blank' rel='permalink'>" . __( 'View on Moodle', 'eb-textdomain' ) . '</a>';
		}
		return $actions;
	}
}
