<?php
/**
 * This class works as a connection helper to connect with Moodle webservice API.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace app\wisdmlabs\edwiserBridge;

class EBConnectionHelper
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
     * @var EBConnectionHelper The single instance of the class
     *
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * Main EBConnectionHelper Instance.
     *
     * Ensures only one instance of EBConnectionHelper is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     *
     * @see EBConnectionHelper()
     *
     * @return EBConnectionHelper - Main instance
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

    /**
     * Initialize the class and set its properties.
     *
     * @since     1.0.0
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * wp_remote_post() has default timeout set as 5 seconds
     * increase it to 40 seconds to remove timeout problem.
     *
     * @since  1.0.2
     *
     * @param int $time seconds before timeout
     *
     * @return int
     */
    public function connectionTimeoutExtender($time = 50)
    {
        return $time;
    }

    /**
     * sends an API request to moodle server based on the credentials entered by user.
     * returns response to ajax initiater.
     *
     * @since     1.0.0
     *
     * @param string $url   moodle URL
     * @param string $token moodle access token
     *
     * @return array returns array containing the success & response message
     */
    public function connectionTestHelper($url, $token)
    {
        $success          = 1;
        $responseMessage = 'success';

        //function to check if webservice token is properly set.
        // $webservice_function = 'core_course_get_courses';

        $webservice_function = 'core_course_get_courses';

        $request_url  = $url . '/webservice/rest/server.php?wstoken=';
        $request_url  .= $token . '&wsfunction=';
        $request_url  .= $webservice_function . '&moodlewsrestformat=json';
        // $response = wp_remote_post( $request_url, $request_args );
        $request_args = array(
            /*'headers' => array(
                    'content-type'   => 'application/json',
                    'Content-Length' => 100
                ),*/
                "timeout" => 100
            );
        $response     = wp_remote_post($request_url, $request_args);

        if (is_wp_error($response)) {
            $success          = 0;
            $responseMessage = $response->get_error_message();
        } elseif (wp_remote_retrieve_response_code($response) == 200 ||
                wp_remote_retrieve_response_code($response) == 303) {
            $body = json_decode(wp_remote_retrieve_body($response));
            if (!empty($body->exception)) {
                $success         = 0;
                $responseMessage = $body->message;
            } else {
                //added else to check the other services access error
                $accessControlResult = $this->checkServiceAccess($url, $token);

                if (!$accessControlResult['success']) {
                    $success         = 0;
                    $responseMessage = $accessControlResult['response_message'];
                }
            }
        } else {
            $success         = 0;
            $responseMessage = __('Please check Moodle URL !', 'eb-textdomain');
        }

        //edwiserBridgeInstance()->logger()->add( 'user', "\n Moodle response: ".serialize($response_data) );

        return array('success' => $success, 'response_message' => $responseMessage);
    }



    /**
     * This is called on the test connection
     */
    public function checkServiceAccess($url, $token)
    {
        $success         = 1;
        $responseMessage = '<div>';

        $responseMessage .= '<div>'. __('Below are the functions which don\'t have access to the web service you created. This is due to :', 'eb-textdomain') .'</div>
                                <div>
                                    <div>
                                        <ol>
                                            <li>'.__('Function is not added to the web service', 'eb-textdomain').'</li>
                                            <li>'.__('Authorised user don\'t have enough capabilities i.e he is not admin', 'eb-textdomain').'</li>
                                            <li>'.__('Edwiser Moodle extensions are not installed or have the lower version', 'eb-textdomain').'</li>
                                        </ol>
                                    </div>
                                </div>
                                <div>
                                    <div>'. __('Services:', 'eb-textdomain') .'</div>
                                    <div>
                                        ';




        $webserviceFunctions = ebGetAllWebServiceFunctions();
        $missingWebServiceFns = array();
        foreach ($webserviceFunctions as $webserviceFunction) {
            $request_url   = $url . '/webservice/rest/server.php?wstoken=';
            $request_url  .= $token . '&wsfunction=';
            $request_url  .= $webserviceFunction . '&moodlewsrestformat=json';
            // $response = wp_remote_post( $request_url, $request_args );
            $request_args  = array("timeout" => 100);
            $response      = wp_remote_post($request_url, $request_args);



            /*if (is_wp_error($response)) {
                $success          = 0;
                $responseMessage = $response->get_error_message();
            } else*/
            if (wp_remote_retrieve_response_code($response) == 200 ||
                    wp_remote_retrieve_response_code($response) == 303) {
                $body = json_decode(wp_remote_retrieve_body($response));
                if (!empty($body->exception)) {
                    // $responseMessage = $body->message;

                    //check if the error response is moodle_access_exception
                    //reasons are function missing, not authorised user or pluginis not activated.
                    if (isset($body->errorcode) && 'accessexception' == $body->errorcode) {
                        $success = 0;
                        array_push($missingWebServiceFns, $webserviceFunction);
                        // $responseMessage .= '<span>'. $webserviceFunction .'</span> , ';
                    }
                }
            }
        }


        if (count($missingWebServiceFns) > 0) {
            $responseMessage .= implode(' , ', $missingWebServiceFns);

            $responseMessage .=  '
                                        </div>
                                    </div>';
            $responseMessage .=  '</div>';
        }
        return array('success' => $success, 'response_message' => $responseMessage);
    }














    /**
     * helper function, recieves request to fetch data from moodle
     * accepts a paramtere for webservice function to be called on moodle.
     *
     * fetches data from moodle and returns response.
     *
     * @since  1.0.0
     *
     * @param string $webservice_function accepts webservice function as an argument
     *
     * @return array returns response to caller function
     */
    public function connectMoodleHelper($webservice_function = null)
    {
        $success          = 1;
        $response_message = 'success';
        $response_data    = array();

        $request_url = EB_ACCESS_URL . '/webservice/rest/server.php?wstoken=';
        $request_url .= EB_ACCESS_TOKEN . '&wsfunction=' . $webservice_function . '&moodlewsrestformat=json';

        // $response = wp_remote_post( $request_url, $request_args );
        $request_args = array("timeout" => 100);
        $response     = wp_remote_post($request_url, $request_args);

        if (is_wp_error($response)) {
            $success          = 0;
            $response_message = $response->get_error_message();
        } elseif (wp_remote_retrieve_response_code($response) == 200 ||
                wp_remote_retrieve_response_code($response) == 303) {
            $body = json_decode(wp_remote_retrieve_body($response));
            if (!empty($body->exception)) {
                $success          = 0;
                //.' - '.isset( $body->debuginfo )?$body->debuginfo:''
                $response_message = $body->message;
            } else {
                $success       = 1;
                $response_data = $body;
            }
        } else {
            $success          = 0;
            $response_message = __('Please check Moodle connection details.', 'eb-textdomain');
        }

        return array(
            'success'          => $success,
            'response_message' => $response_message,
            'response_data'    => $response_data,
        );
    }

    public function connectMoodleWithArgsHelper($webservice_function, $request_data)
    {
        $success          = 1;
        $response_message = 'success';
        $response_data    = array();

        $request_url = EB_ACCESS_URL . '/webservice/rest/server.php?wstoken=';
        $request_url .= EB_ACCESS_TOKEN . '&wsfunction=' . $webservice_function . '&moodlewsrestformat=json';

        $request_args = array(
            'body'    => $request_data,
            "timeout" => 100,
        );

        $response = wp_remote_post($request_url, $request_args);

        if (is_wp_error($response)) {
            $success          = 0;
            $response_message = $response->get_error_message();
        } elseif (wp_remote_retrieve_response_code($response) == 200) {
            $body = json_decode(wp_remote_retrieve_body($response));
            if (!empty($body->exception)) {
                $success = 0;
                if (isset($body->debuginfo)) {
                    $response_message = $body->message . ' - ' . $body->debuginfo;
                } else {
                    $response_message = $body->message;
                }
            } else {
                $success       = 1;
                $response_data = $body;
            }
        } else {
            $success          = 0;
            $response_message = __('Please check Moodle URL !', 'eb-textdomain');
        }

        return array(
            'success'          => $success,
            'response_message' => $response_message,
            'response_data'    => $response_data,
        );
    }
}
