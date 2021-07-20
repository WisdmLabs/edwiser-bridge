<?php
/**
 * This class works as a connection helper to connect with Moodle webservice API.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge.
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Connection helper.
 */
class EBConnectionHelper {

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
	 * @param text $plugin_name plugin_name.
	 * @param text $version version.
	 * @return EBConnectionHelper - Main instance
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
	 * Initialize the class and set its properties.
	 *
	 * @since     1.0.0
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Wp_remote_post() has default timeout set as 5 seconds
	 * increase it to 40 seconds to remove timeout problem.
	 *
	 * @since  1.0.2
	 *
	 * @param int $time seconds before timeout.
	 *
	 * @return int
	 */
	public function connection_timeout_extender( $time = 50 ) {
		return $time;
	}

	/**
	 * DEPRECATED FUNCTION
	 *
	 * Sends an API request to moodle server based on the credentials entered by user.
	 * returns response to ajax initiater.
	 *
	 * @since     1.0.0
	 *
	 * @deprecated since 2.0.1 use connection_test_helper( $url, $token ) insted.
	 * @param string $url   moodle URL.
	 * @param string $token moodle access token.
	 *
	 * @return array returns array containing the success & response message
	 */
	public function connectionTestHelper( $url, $token ) {
		return $this->connection_test_helper( $url, $token );
	}



	/**
	 * Sends an API request to moodle server based on the credentials entered by user.
	 * returns response to ajax initiater.
	 *
	 * @since     1.0.0
	 *
	 * @param string $url   moodle URL.
	 * @param string $token moodle access token.
	 *
	 * @return array returns array containing the success & response message
	 */
	public function connection_test_helper( $url, $token ) {
		$success          = 1;
		$response_message = 'success';

		// function to check if webservice token is properly set.

		$webservice_function = 'core_course_get_courses';

		$request_url  = $url . '/webservice/rest/server.php?wstoken=';
		$request_url .= $token . '&wsfunction=';
		$request_url .= $webservice_function . '&moodlewsrestformat=json';
		$request_args = array(
			'timeout' => 100,
		);
		$response     = wp_remote_post( $request_url, $request_args );
		if ( is_wp_error( $response ) ) {
			$success          = 0;
			$response_message = $response->get_error_message();
		} elseif ( wp_remote_retrieve_response_code( $response ) === 200 ||
				wp_remote_retrieve_response_code( $response ) === 303 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( null === $body ) {
				$url_link         = "<a href='$url/local/edwiserbridge/edwiserbridge.php?tab=summary'>here</a>";
				$success          = 0;
				$response_message = __( "Please check moodle web service configuration, Got invalid JSON,Check moodle web summary {$url_link}", 'eb-textdomain' );
			} elseif ( ! empty( $body->exception ) ) {
				$success          = 0;
				$response_message = $body->message;
			} else {
				// added else to check the other services access error.
				$access_control_result = $this->check_service_access( $url, $token );

				if ( ! $access_control_result['success'] ) {
					$success          = 0;
					$response_message = $access_control_result['response_message'];
				}
			}
		} else {
			$success          = 0;
			$response_message = esc_html__( 'Please check Moodle URL !', 'eb-textdomain' );
		}

		return array(
			'success'          => $success,
			'response_message' => $response_message,
		);
	}




	/**
	 * This is called on the test connection.
	 *
	 * @param text $url url.
	 * @param text $token token.
	 */
	public function check_service_access( $url, $token ) {
		$success          = 1;
		$response_message = '<div>';

		$response_message .= '<div>' . esc_html__( 'Below are the functions which don\'t have access to the web service you created. This is due to :', 'eb-textdomain' ) . '</div>
								<div>
									<div>
										<ol>
											<li>' . esc_html__( 'Function is not added to the web service', 'eb-textdomain' ) . '</li>
											<li>' . esc_html__( 'Authorised user don\'t have enough capabilities i.e he is not admin', 'eb-textdomain' ) . '</li>
											<li>' . esc_html__( 'Edwiser Moodle extensions are not installed or have the lower version', 'eb-textdomain' ) . '</li>
										</ol>
									</div>
								</div>
								<div>
									<div>' . esc_html__( 'Services:', 'eb-textdomain' ) . '</div>
									<div>
										';

		$webservice_functions    = \app\wisdmlabs\edwiserBridge\wdm_eb_get_all_web_service_functions();
		$missing_web_service_fns = array();
		foreach ( $webservice_functions as $webservice_function ) {
			$request_url  = $url . '/webservice/rest/server.php?wstoken=';
			$request_url .= $token . '&wsfunction=';
			$request_url .= $webservice_function . '&moodlewsrestformat=json';
			$request_args = array( 'timeout' => 100 );
			$response     = wp_remote_post( $request_url, $request_args );

			if ( 200 === wp_remote_retrieve_response_code( $response ) ||
			303 === wp_remote_retrieve_response_code( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ) );
				if ( ! empty( $body->exception ) && isset( $body->errorcode ) && 'accessexception' === $body->errorcode ) {
						$success = 0;
						array_push( $missing_web_service_fns, $webservice_function );
				}
			}
		}

		if ( count( $missing_web_service_fns ) > 0 ) {
			$response_message .= implode( ' , ', $missing_web_service_fns );

			$response_message .= '
										</div>
									</div>';
			// Add new message here.

			$response_message .= esc_html__( 'You can check added webservice here ', 'eb-textdomain' ) . '<a href="' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/admin/settings.php?section=externalservices">' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/admin/settings.php?section=externalservices</a>' . esc_html__( ' or you can directly create new token and webservice in our Moodle edwiser settings here ', 'eb-textdomain' ) . '<a href="' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . 'local/edwiserbridge/edwiserbridge.php?tab=service">' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . 'local/edwiserbridge/edwiserbridge.php?tab=service</a>';

			$response_message .= '</div>';
		}

		return array(
			'success'          => $success,
			'response_message' => $response_message,
		);
	}




	/**
	 * DEPRECATED FUNCTION.
	 *
	 * Helper function, recieves request to fetch data from moodle.
	 * accepts a paramtere for webservice function to be called on moodle.
	 *
	 * fetches data from moodle and returns response.
	 *
	 * @deprecated since 20.1 use connect_moodle_helper( $webservice_function ) insted.
	 * @since  1.0.0
	 *
	 * @param string $webservice_function accepts webservice function as an argument.
	 *
	 * @return array returns response to caller function
	 */
	public function connectMoodleHelper( $webservice_function = null ) {
		return $this->connect_moodle_helper( $webservice_function );
	}




	/**
	 * Helper function, recieves request to fetch data from moodle.
	 * accepts a paramtere for webservice function to be called on moodle.
	 *
	 * Fetches data from moodle and returns response.
	 *
	 * @since  1.0.0
	 *
	 * @param string $webservice_function accepts webservice function as an argument.
	 *
	 * @return array returns response to caller function
	 */
	public function connect_moodle_helper( $webservice_function = null ) {
		$success          = 1;
		$response_message = 'success';
		$response_data    = array();
		$eb_access_token  = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();
		$eb_access_url    = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();

		$request_url  = $eb_access_url . '/webservice/rest/server.php?wstoken=';
		$request_url .= $eb_access_token . '&wsfunction=' . $webservice_function . '&moodlewsrestformat=json';

		$request_args = array( 'timeout' => 100 );
		$response     = wp_remote_post( $request_url, $request_args );

		if ( is_wp_error( $response ) ) {
			$success          = 0;
			$response_message = $response->get_error_message();
		} elseif ( wp_remote_retrieve_response_code( $response ) === 200 ||
				wp_remote_retrieve_response_code( $response ) === 303 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $body->exception ) ) {
				$success          = 0;
				$response_message = $body->message;
			} else {
				$success       = 1;
				$response_data = $body;
			}
		} else {
			$success          = 0;
			$response_message = esc_html__( 'Please check Moodle connection details.', 'eb-textdomain' );
		}

		return array(
			'success'          => $success,
			'response_message' => $response_message,
			'response_data'    => $response_data,
		);
	}


	/**
	 * Helper function, recieves request to fetch data from moodle.
	 * accepts a paramtere for webservice function to be called on moodle.
	 *
	 * Fetches data from moodle and returns response.
	 *
	 * @deprecated since 2.0.1 use connect_moodle_with_args_helper( $webservice_function, $request_data ) insted.
	 * @since  1.0.0
	 *
	 * @param string $webservice_function accepts webservice function as an argument.
	 * @param string $request_data request_data.
	 *
	 * @return array returns response to caller function
	 */
	public function connectMoodleWithArgsHelper( $webservice_function, $request_data ) {
		return $this->connect_moodle_with_args_helper( $webservice_function, $request_data );
	}


	/**
	 * Helper function, recieves request to fetch data from moodle.
	 * accepts a paramtere for webservice function to be called on moodle.
	 *
	 * Fetches data from moodle and returns response.
	 *
	 * @since  1.0.0
	 *
	 * @param string $webservice_function accepts webservice function as an argument.
	 * @param string $request_data accepts webservice function as an argument.
	 *
	 * @return array returns response to caller function
	 */
	public function connect_moodle_with_args_helper( $webservice_function, $request_data ) {
		$success          = 1;
		$response_message = 'success';
		$response_data    = array();
		$eb_access_token  = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();
		$eb_access_url    = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();

		$request_url  = $eb_access_url . '/webservice/rest/server.php?wstoken=';
		$request_url .= $eb_access_token . '&wsfunction=' . $webservice_function . '&moodlewsrestformat=json';

		$request_args = array(
			'body'    => $request_data,
			'timeout' => 100,
		);

		$response = wp_remote_post( $request_url, $request_args );

		if ( is_wp_error( $response ) ) {
			$success          = 0;
			$response_message = $response->get_error_message();
		} elseif ( wp_remote_retrieve_response_code( $response ) === 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $body->exception ) ) {
				$success = 0;
				if ( isset( $body->debuginfo ) ) {
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
			$response_message = esc_html__( 'Please check Moodle URL !', 'eb-textdomain' );
		}

		return array(
			'success'          => $success,
			'response_message' => $response_message,
			'response_data'    => $response_data,
		);
	}



}
