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

namespace app\wisdmlabs\edwiserBridge;

class EBSettingsAjaxInitiater
{

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

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * initiate course synchronization process
     *
     * @since    1.0.0
     * @access   public
     *
     * @return
     */
    public function courseSynchronizationInitiater()
    {
        if (!isset($_POST['_wpnonce_field'])) {
            die('Busted!');
        }

        // verifying generated nonce we created earlier
        if (!wp_verify_nonce($_POST['_wpnonce_field'], 'check_sync_action')) {
            die('Busted!');
        }

        // get sync options
        $sync_options = json_decode(stripslashes($_POST['sync_options']), true);

        // start working on request
        $response = edwiserBridgeInstance()->courseManager()->courseSynchronizationHandler($sync_options);
        echo json_encode($response);
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
    public function userDataSynchronizationInitiater()
    {
        if (!isset($_POST['_wpnonce_field'])) {
            die('Busted!');
        }

        // verifying generated nonce we created earlier
        if (!wp_verify_nonce($_POST['_wpnonce_field'], 'check_sync_action')) {
            die('Busted!');
        }

        // get sync options
        $sync_options = json_decode(stripslashes($_POST['sync_options']), true);

        //$response = edwiserBridgeInstance()->userManager()->user_course_synchronization_handler( $sync_user_courses );
        $response = edwiserBridgeInstance()->userManager()->userCourseSynchronizationHandler($sync_options);

        echo json_encode($response);
        die();
    }

    /**
     * Test connection between wordpress and moodle
     *
     * Calls connection_test_helper() from EBConnectionHelper class
     *
     * @since    1.0.0
     * @access   public
     *
     * @return boolean true on success else false
     */
    public function connectionTestInitiater()
    {
        if (!isset($_POST['_wpnonce_field'])) {
            die('Busted!');
        }

        // verifying generated nonce we created earlier
        if (!wp_verify_nonce($_POST['_wpnonce_field'], 'check_sync_action')) {
            die('Busted!');
        }

        //start working on request
        $url = $_POST['url'];
        $token = $_POST['token'];

        $connection_helper = new EBConnectionHelper($this->plugin_name, $this->version);
        $response = $connection_helper->connectionTestHelper($url, $token);

        echo json_encode($response);
        die();
    }
}
