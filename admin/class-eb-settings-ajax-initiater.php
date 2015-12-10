<?php

/**
 * This class contains functionality to handle actions of custom buttons implemented in settings page
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
class EB_Settings_Ajax_Initiater {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $__plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $__version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * initiate course synchronization process
     *
     * @since    1.0.0
     * @access   public
     *
     * @return
     */
    public function course_synchronization_initiater() {

        if ( !isset( $_POST['_wpnonce_field'] ) ) {
            die( 'Busted!' );
        }

        // verifying generated nonce we created earlier
        if ( !wp_verify_nonce( $_POST['_wpnonce_field'], 'check_sync_action' ) ) {
            die( 'Busted!' );
        }

        // get sync options
        $sync_options = json_decode( stripslashes( $_POST['sync_options'] ), true );

        // start working on request
        $response       = EB()->course_manager()->course_synchronization_handler( $sync_options );
        echo json_encode( $response );
        die();
    }

    /**
     * initiate user data synchronization process
     *
     * @since    1.0.0
     * @access   public
     *
     * @return
     */
    public function user_data_synchronization_initiater() {

        if ( !isset( $_POST['_wpnonce_field'] ) ) {
            die( 'Busted!' );
        }

        // verifying generated nonce we created earlier
        if ( !wp_verify_nonce( $_POST['_wpnonce_field'], 'check_sync_action' ) ) {
            die( 'Busted!' );
        }

        // get sync options
        $sync_options = json_decode( stripslashes( $_POST['sync_options'] ), true );

        //$response = EB()->user_manager()->user_course_synchronization_handler( $sync_user_courses );
        $response = EB()->user_manager()->user_course_synchronization_handler( $sync_options );

        echo json_encode( $response );
        die();
    }

    /**
     * Test connection between wordpress and moodle
     *
     * Calls connection_test_helper() from EB_Connection_Helper class
     *
     * @since    1.0.0
     * @access   public
     *
     * @return boolean true on success else false
     */
    public function connection_test_initiater() {
        if ( !isset( $_POST['_wpnonce_field'] ) ) {
            die( 'Busted!' );
        }

        // verifying generated nonce we created earlier
        if ( !wp_verify_nonce( $_POST['_wpnonce_field'], 'check_sync_action' ) ) {
            die( 'Busted!' );
        }

        //start working on request
        $url   = $_POST['url'];
        $token = $_POST['token'];

        $connection_helper = new EB_Connection_Helper( $this->plugin_name, $this->version );
        $response          = $connection_helper->connection_test_helper( $url, $token );

        echo json_encode( $response );
        die();
    }
}
