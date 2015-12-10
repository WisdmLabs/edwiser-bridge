<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
class EB_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string  $plugin_name The name of this plugin.
	 * @param string  $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in EB_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The EB_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, EB_PLUGIN_URL . 'admin/assets/css/eb-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'jquery-tiptip-css', EB_PLUGIN_URL . 'admin/assets/css/tipTip.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in EB_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$nonce = wp_create_nonce( 'check_sync_action' );
		wp_enqueue_script( $this->plugin_name, EB_PLUGIN_URL . 'admin/assets/js/eb-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'eb_admin_js_object', array( 'unsaved_warning' => __( 'Please save the changes.', 'eb-textdomain' ), 'plugin_url' => EB_PLUGIN_URL, 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => $nonce ) );

		wp_enqueue_script( 'jquery-tiptip-js', EB_PLUGIN_URL . 'admin/assets/js/jquery.tipTip.minified.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( 'iris' );
	}
}
