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
class Eb_Connection_Helper {

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
	 * @var Eb_Connection_Helper The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main Eb_Connection_Helper Instance.
	 *
	 * Ensures only one instance of Eb_Connection_Helper is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @see Eb_Connection_Helper()
	 * @param text $plugin_name plugin_name.
	 * @param text $version version.
	 * @return Eb_Connection_Helper - Main instance
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
	 * @param int    $text_response text response.
	 *
	 * @return array returns array containing the success & response message
	 */
	public function connection_test_helper( $url, $token, $text_response = 0 ) {
		$success          = 1;
		$response_message = 'success';
		$plain_txt_msg    = '';
		// function to check if webservice token is properly set.

		// new test connection api.
		$webservice_function = 'eb_test_connection';

		$request_url               = $url . '/webservice/rest/server.php?wstoken=';
		$request_url              .= $token . '&wsfunction=';
		$request_url              .= $webservice_function . '&moodlewsrestformat=json';
		$request_args              = array(
			'timeout' => 100,
		);
		$settings                  = get_option( 'eb_general' );
		$request_args['sslverify'] = false;
		if ( isset( $settings['eb_ignore_ssl'] ) && 'no' === $settings['eb_ignore_ssl'] ) {
			$request_args['sslverify'] = true;
		}

		$request_args['body'] = array(
			'test_connection' => 'wordpress',
			'wp_url'          => get_site_url(),
			'wp_token'        => $token,
		);
		$response             = wp_remote_post( $request_url, $request_args );

		if ( is_wp_error( $response ) ) {
			$success          = 0;
			$plain_txt_msg    = $response->get_error_message();
			$response_message = $this->create_response_message( $request_url, $response->get_error_message() );
			global $current_user;
			wp_get_current_user();
			$error_data = array(
				'url'          => $request_url,
				'arguments'    => $request_args,
				'user'         => isset( $current_user ) ? $current_user->user_login . '(' . $current_user->first_name . ' ' . $current_user->last_name . ')' : '',
				'responsecode' => '',
				'exception'    => '',
				'errorcode'    => '',
				'message'      => $plain_txt_msg,
				'backtrace'    => wp_debug_backtrace_summary( null, 0, false ), // @codingStandardsIgnoreLine
			);
			wdm_log_json( $error_data );
		} elseif ( wp_remote_retrieve_response_code( $response ) === 200 ||
			wp_remote_retrieve_response_code( $response ) === 303 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( null === $body ) {
				$url_link      = "<a href='$url/auth/edwiserbridge/edwiserbridge.php?tab=summary'>here</a>";
				$success       = 0;
				$plain_txt_msg = $response->get_error_message( __( 'Please check moodle web service configuration, Got invalid JSON,Check moodle web summary ', 'edwiser-bridge' ) );

				$response_message = $this->create_response_message(
					$request_url,
					__( 'Please check moodle web service configuration, Got invalid JSON,Check moodle web summary ', 'edwiser-bridge' ) . $url_link
				);
			} elseif ( ! empty( $body->exception ) ) {
				if ( 'invalid_parameter_exception' === $body->exception ) {
					$success          = 0;
					$msg              = __( 'Edwiser plugin is not updated on your Moodle Site. Please update it to avoid any malfunctioning.', 'edwiser-bridge' );
					$plain_txt_msg    = $msg;
					$response_message = $this->create_response_message( $request_url, $msg );
				} else {
					$success          = 0;
					$msg              = print_r( $body, 1 ); // @codingStandardsIgnoreLine
					$plain_txt_msg    = $msg;
					$response_message = $this->create_response_message( $request_url, $msg );
				}

				// register error log.
				global $current_user;
				wp_get_current_user();
				$error_data = array(
					'url'          => $request_url,
					'arguments'    => $request_args,
					'user'         => $current_user->user_login . '(' . $current_user->user_firstname . ' ' . $current_user->user_lastname . ')',
					'responsecode' => wp_remote_retrieve_response_code( $response ),
					'exception'    => $body->exception,
					'errorcode'    => $body->errorcode,
					'message'      => $body->message,
					'backtrace'    => wp_debug_backtrace_summary( null, 0, false ), // @codingStandardsIgnoreLine
				);
				if ( isset( $body->debuginfo ) ) {
					$error_data['debuginfo'] = $body->debuginfo;
				}

				wdm_log_json( $error_data );

			} else {
				if ( '0' === $body->status ) {
					$success          = 0;
					$plain_txt_msg    = esc_html__( 'Connection failed', 'edwiser-bridge' );
					$update_msg       = esc_html__( 'You can check added webservice here ', 'edwiser-bridge' ) . '<a href="' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/admin/settings.php?section=externalservices">' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/admin/settings.php?section=externalservices</a>' . esc_html__( ' or you can directly create new token and webservice in our Moodle edwiser settings here ', 'edwiser-bridge' ) . '<a href="' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=service">' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=service</a>';
					$response_message = $this->create_response_message( $request_url, esc_html( $body->msg ) . ' ' . $update_msg );
				}
			}
		} else {
			$success          = 0;
			$plain_txt_msg    = esc_html__( 'Please check Moodle URL or Moodle plugin configuration !', 'edwiser-bridge' );
			$response_message = $this->create_response_message( $request_url, esc_html__( 'Please check Moodle URL or Moodle plugin configuration !', 'edwiser-bridge' ) );
		}

		if ( $text_response && ! empty( $plain_txt_msg ) ) {
			$response_message = $plain_txt_msg;
		}

		$response = array(
			'success'          => $success,
			'response_message' => $response_message,
		);
		if ( isset( $body->warnings ) && is_array( $body->warnings ) ) {
			foreach ( $body->warnings as $warning ) {
				$response['warnings'][] = '<span class="dashicons dashicons-warning" style="padding: 2px 6px 2px 0px;font-size: 22px;margin-left: -2px;"></span>' . $warning;
			}
		}

		return $response;
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

		$response_message .= '<div>' . esc_html__( 'Below are the functions which don\'t have access to the web service you created. This is due to :', 'edwiser-bridge' ) . '</div>
								<div>
									<div>
										<ol>
											<li>' . esc_html__( 'Function is not added to the web service', 'edwiser-bridge' ) . '</li>
											<li>' . esc_html__( 'Authorised user don\'t have enough capabilities i.e he is not admin', 'edwiser-bridge' ) . '</li>
											<li>' . esc_html__( 'Edwiser Moodle extensions are not installed or have the lower version', 'edwiser-bridge' ) . '</li>
										</ol>
									</div>
								</div>
								<div>
									<div>' . esc_html__( 'Services:', 'edwiser-bridge' ) . '</div>
									<div>
										';

		$webservice_functions    = \app\wisdmlabs\edwiserBridge\wdm_eb_get_all_web_service_functions();
		$missing_web_service_fns = array();

		$request_args              = array( 'timeout' => 100 );
		$settings                  = get_option( 'eb_general' );
		$request_args['sslverify'] = false;
		if ( isset( $settings['eb_ignore_ssl'] ) && 'no' === $settings['eb_ignore_ssl'] ) {
			$request_args['sslverify'] = true;
		}

		foreach ( $webservice_functions as $webservice_function ) {
			$request_url  = $url . '/webservice/rest/server.php?wstoken=';
			$request_url .= $token . '&wsfunction=';
			$request_url .= $webservice_function . '&moodlewsrestformat=json';
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

			$response_message .= esc_html__( 'You can check added webservice here ', 'edwiser-bridge' ) . '<a href="' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/admin/settings.php?section=externalservices">' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/admin/settings.php?section=externalservices</a>' . esc_html__( ' or you can directly create new token and webservice in our Moodle edwiser settings here ', 'edwiser-bridge' ) . '<a href="' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=service">' . \app\wisdmlabs\edwiserBridge\wdm_eb_get_moodle_url() . '/auth/edwiserbridge/edwiserbridge.php?tab=service</a>';

			$response_message .= '</div>';
		}

		return array(
			'success'          => $success,
			'response_message' => $response_message,
		);
	}


	/**
	 * Parsing HTML response message.
	 *
	 * @param text $url url.
	 * @param text $message message.
	 */
	public function create_response_message( $url, $message ) {
		$msg = '<div>
                        <div class="eb_connection_short_msg">
                            ' . esc_html__( 'Test Connection failed, To check more information about issue click', 'edwiser-bridge' ) . ' <span class="eb_test_connection_log_open"> ' . esc_html__( 'here', 'edwiser-bridge' ) . ' </span>.
                        </div>

                        <div class="eb_test_connection_log">
                        	<div style="display:flex;">
	                            <div class="eb_connection_err_response">
	                                <h4> ' . esc_html__( 'An issue is detected.', 'edwiser-bridge' ) . ' </h4>
	                                <div style="display:flex;">
	                                	<div> <b>' . esc_html__( 'Status : ', 'edwiser-bridge' ) . '</b></div>
	                                	<div>' . esc_html__( 'Connection Failed', 'edwiser-bridge' ) . ' </div>
	                                </div>
	                                <div>
	                                	<div><b>' . esc_html__( 'Url : ', 'edwiser-bridge' ) . '</b></div>
	                                	<div class="eb_test_conct_log_url">' . $url . '</div>
	                                </div>
	                                <div>
	                                	<div><b>' . esc_html__( 'Response : ', 'edwiser-bridge' ) . '</b></div>
	                                	<div>' . $message . '</div>
	                                </div>
	                            </div>

	                            <div class="eb_admin_templ_dismiss_notice_message">
									<span class="eb_test_connection_log_close dashicons dashicons-dismiss"></span> 
								</div>
							<div>
                        </div>
                    </div>';
		return $msg;
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

		$request_args              = array( 'timeout' => 100 );
		$settings                  = get_option( 'eb_general' );
		$request_args['sslverify'] = false;
		if ( isset( $settings['eb_ignore_ssl'] ) && 'no' === $settings['eb_ignore_ssl'] ) {
			$request_args['sslverify'] = true;
		}
		$response = wp_remote_post( $request_url, $request_args );

		if ( is_wp_error( $response ) ) {
			$success          = 0;
			$response_message = $response->get_error_message();
			global $current_user;
			wp_get_current_user();
			$error_data = array(
				'url'          => $request_url,
				'arguments'    => $request_args,
				'user'         => isset( $current_user ) ? $current_user->user_login . '(' . $current_user->first_name . ' ' . $current_user->last_name . ')' : '',
				'responsecode' => '',
				'exception'    => '',
				'errorcode'    => '',
				'message'      => $response_message,
				'backtrace'    => wp_debug_backtrace_summary( null, 0, false ), // @codingStandardsIgnoreLine
			);
			wdm_log_json( $error_data );
		} elseif ( wp_remote_retrieve_response_code( $response ) === 200 ||
				wp_remote_retrieve_response_code( $response ) === 303 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $body->exception ) ) {
				$success          = 0;
				$response_message = $body->message;

				// register error log.
				global $current_user;
				wp_get_current_user();
				$error_data = array(
					'url'          => $request_url,
					'arguments'    => $request_args,
					'user'         => $current_user->user_login . '(' . $current_user->first_name . ' ' . $current_user->last_name . ')',
					'responsecode' => wp_remote_retrieve_response_code( $response ),
					'exception'    => $body->exception,
					'errorcode'    => $body->errorcode,
					'message'      => $body->message,
					'backtrace'    => wp_debug_backtrace_summary( null, 0, false ), // @codingStandardsIgnoreLine
				);
				if ( isset( $body->debuginfo ) ) {
					$error_data['debuginfo'] = $body->debuginfo;
				}

				wdm_log_json( $error_data );
			} else {
				$success       = 1;
				$response_data = $body;
			}
		} else {
			$success          = 0;
			$response_message = esc_html__( 'Please check Moodle connection details.', 'edwiser-bridge' );
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
		$body             = '';
		$response_message = 'success';
		$response_data    = array();
		$eb_access_token  = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_token();
		$eb_access_url    = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_get_access_url();

		$request_url  = $eb_access_url . '/webservice/rest/server.php?wstoken=';
		$request_url .= $eb_access_token . '&wsfunction=' . $webservice_function . '&moodlewsrestformat=json';

		$request_args              = array(
			'body'    => $request_data,
			'timeout' => 100,
		);
		$settings                  = get_option( 'eb_general' );
		$request_args['sslverify'] = false;
		if ( isset( $settings['eb_ignore_ssl'] ) && 'no' === $settings['eb_ignore_ssl'] ) {
			$request_args['sslverify'] = true;
		}

		$response = wp_remote_post( $request_url, $request_args );

		if ( is_wp_error( $response ) ) {
			$success          = 0;
			$response_message = $response->get_error_message();
			global $current_user;
			wp_get_current_user();
			$error_data = array(
				'url'          => $request_url,
				'arguments'    => $request_args,
				'user'         => isset( $current_user ) ? $current_user->user_login . '(' . $current_user->first_name . ' ' . $current_user->last_name . ')' : '',
				'responsecode' => '',
				'exception'    => '',
				'errorcode'    => '',
				'message'      => $response_message,
				'backtrace'    => wp_debug_backtrace_summary( null, 0, false ), // @codingStandardsIgnoreLine
			);
			wdm_log_json( $error_data );
		} elseif ( wp_remote_retrieve_response_code( $response ) === 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $body->exception ) ) {
				$success = 0;
				if ( isset( $body->debuginfo ) ) {
					$response_message = $body->message . ' - ' . $body->debuginfo;
				} else {
					$response_message = $body->message;
				}

				// register error log.
				global $current_user;
				wp_get_current_user();
				$error_data = array(
					'url'          => $request_url,
					'arguments'    => $request_data,
					'user'         => $current_user->user_login . '(' . $current_user->first_name . ' ' . $current_user->last_name . ')',
					'responsecode' => wp_remote_retrieve_response_code( $response ),
					'exception'    => $body->exception,
					'errorcode'    => $body->errorcode,
					'message'      => $body->message,
					'backtrace'    => wp_debug_backtrace_summary( null, 0, false ), // @codingStandardsIgnoreLine
				);
				if ( isset( $body->debuginfo ) ) {
					$error_data['debuginfo'] = $body->debuginfo;
				}

				wdm_log_json( $error_data );
			} else {
				$success       = 1;
				$response_data = $body;
			}
		} else {
			$success          = 0;
			$response_message = esc_html__( 'Please check Moodle URL !', 'edwiser-bridge' );
		}

		return array(
			'success'          => $success,
			'response_message' => $response_message,
			'response_data'    => $response_data,
			'status_code'      => wp_remote_retrieve_response_code( $response ),
			'response_body'    => $body,
		);
	}
}

/**
 * Deprecated Class.
 *
 * @deprecated since 3.0.0
 */
class EBConnectionHelper extends EB_Connection_Helper { // @codingStandardsIgnoreLine

}
