<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Eb_Public class.
 */
class Eb_Public {


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function public_enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

		// Loading dasicons.
		wp_enqueue_style( 'dashicons' );

		wp_enqueue_style(
			$this->plugin_name . '_font_awesome',
			$eb_plugin_url . 'public/assets/css/font-awesome-4.4.0/css/font-awesome.min.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_style(
			$this->plugin_name,
			$eb_plugin_url . 'public/assets/css/eb-public.css',
			array( $this->plugin_name . '_font_awesome' ),
			$this->version,
			'all'
		);
		wp_enqueue_style(
			'wdmdatatablecss',
			$eb_plugin_url . 'public/assets/css/datatable.css',
			array(),
			$this->version,
			'all'
		);
		wp_enqueue_style(
			'eb-public-jquery-ui-css',
			$eb_plugin_url . 'admin/assets/css/jquery-ui.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function public_enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();
		$nonce         = wp_create_nonce( 'public_js_nonce' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script(
			$this->plugin_name,
			$eb_plugin_url . 'public/assets/js/eb-public.js',
			array( 'jquery', 'jquery-ui-dialog' ),
			$this->version,
			false
		);
		wp_register_script(
			$this->plugin_name . '-edit-user-profile',
			$eb_plugin_url . 'public/assets/js/edit-user-profile.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_localize_script(
			$this->plugin_name . '-edit-user-profile',
			'ebEditProfile',
			array(
				'default' => __( '- Select Country -', 'edwiser-bridge' ),
			)
		);

		wp_enqueue_script(
			$this->plugin_name . '-ui-block',
			$eb_plugin_url . 'public/assets/js/jquery-blockui-min.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_localize_script(
			$this->plugin_name,
			'eb_public_js_object',
			array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => $nonce,
				'msg_val_fn'       => __( "The field 'First Name' cannot be left blank", 'edwiser-bridge' ),
				'msg_val_ln'       => __( "The field 'Last Name' cannot be left blank", 'edwiser-bridge' ),
				'msg_val_mail'     => __( "The field 'Email' cannot be left blank", 'edwiser-bridge' ),
				'msg_ordr_pro_err' => __( 'Problems in processing your order, Please try later.', 'edwiser-bridge' ),
				'msg_processing'   => __( 'Processing...', 'edwiser-bridge' ),
				'access_course'    => __( 'Access Course', 'edwiser-bridge' ),
			)
		);

		// datatable.
		wp_localize_script(
			$this->plugin_name,
			'ebDataTable',
			array(
				'search'          => __( 'Search:', 'edwiser-bridge' ),
				'all'             => __( 'All', 'edwiser-bridge' ),
				'sEmptyTable'     => __( 'No data available in table', 'edwiser-bridge' ),
				'sLoadingRecords' => __( 'Loading...', 'edwiser-bridge' ),
				'sSearch'         => __( 'Search', 'edwiser-bridge' ),
				'sZeroRecords'    => __( 'No matching records found', 'edwiser-bridge' ),
				'sProcessing'     => __( 'Processing...', 'edwiser-bridge' ),
				'sInfo'           => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'edwiser-bridge' ),
				'sInfoEmpty'      => __( 'Showing 0 to 0 of 0 entries', 'edwiser-bridge' ),
				'sInfoFiltered'   => __( 'filtered from _MAX_ total entries', 'edwiser-bridge' ),
				'sInfoPostFix'    => '',
				'sInfoThousands'  => __( ',', 'edwiser-bridge' ),
				'sLengthMenu'     => __( 'Show _MENU_ entries', 'edwiser-bridge' ),
				'sFirst'          => __( 'First', 'edwiser-bridge' ),
				'sLast'           => __( 'Last', 'edwiser-bridge' ),
				'sNext'           => __( 'Next', 'edwiser-bridge' ),
				'sPrevious'       => __( 'Previous', 'edwiser-bridge' ),
				'sSortAscending'  => __( ': activate to sort column ascending', 'edwiser-bridge' ),
				'sSortDescending' => __( ': activate to sort column descending', 'edwiser-bridge' ),
			)
		);

		// datatable js for user order table.
		wp_enqueue_script(
			'wdmdatatablejs',
			$eb_plugin_url . 'public/assets/js/datatable.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_register_script(
			'eb_paypal_js',
			$eb_plugin_url . 'public/assets/js/eb-paypal.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		if ( \app\wisdmlabs\edwiserBridge\wdm_eb_recaptcha_type() ) {
			wp_register_script(
				'eb_captcha',
				'https://www.google.com/recaptcha/api.js',
				array(),
				$this->version,
				true
			);
			wp_enqueue_script( 'eb_captcha' );
		}
	}


	/**
	 * Theme specific setup.
	 *
	 * @since    1.2.0
	 */
	public function after_setup_theme() {
		add_theme_support( 'post-thumbnails' );

		// Custom sized thumbnails - single course page.
		add_image_size( 'course_single', 600, 450, true );

		// Custom sized thumbnails - archive course page.
		add_image_size( 'course_archive', 200, 150, true );
	}
}
