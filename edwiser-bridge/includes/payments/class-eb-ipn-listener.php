<?php
/**
 *  PayPal IPN Listener
 *
 *  A class to listen for and handle Instant Payment Notifications (IPN) from
 *  the PayPal server.
 *
 *  https://github.com/Quixotix/PHP-PayPal-IPN
 *
 *  @package    PHP-PayPal-IPN
 *  @author     Micah Carrick
 *  @copyright  (c) 2011 - Micah Carrick
 *  @version    2.0.5
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Ipn Listner.
 */
class Eb_Ipn_Listener {

	/**
	 *  If true, the recommended cURL PHP library is used to send the post back
	 *  to PayPal. If false then fsockopen() is used. Default true.
	 *
	 *  @var boolean
	 */
	public $use_curl = true;

	/**
	 *  If true, explicitly sets cURL to use SSL version 3. Use this if cURL
	 *  is compiled with GnuTLS SSL.
	 *
	 *  @var boolean
	 */
	public $force_ssl_v3 = false;

	/**
	 *  If true, cURL will use the CURLOPT_FOLLOWLOCATION to follow any
	 *  "Location: ..." headers in the response.
	 *
	 *  @var boolean
	 */
	public $follow_location = false;

	/**
	 *  If true, an SSL secure connection (port 443) is used for the post back
	 *  as recommended by PayPal. If false, a standard HTTP (port 80) connection
	 *  is used. Default true.
	 *
	 *  @var boolean
	 */
	public $use_ssl = true;

	/**
	 *  If true, the paypal sandbox URI www.sandbox.paypal.com is used for the
	 *  post back. If false, the live URI www.paypal.com is used. Default false.
	 *
	 *  @var boolean
	 */
	public $use_sandbox = false;

	/**
	 *  The amount of time, in seconds, to wait for the PayPal server to respond
	 *  before timing out. Default 30 seconds.
	 *
	 *  @var int
	 */
	public $timeout = 30;

	/**
	 * Post data.
	 *
	 * @since    1.0.0
	 *
	 * @var string post data.
	 */
	private $post_data = array();

	/**
	 * Post uri.
	 *
	 * @since    1.0.0
	 *
	 * @var string post uri.
	 */
	private $post_uri = '';

	/**
	 * Post uri.
	 *
	 * @since    1.0.0
	 *
	 * @var string post uri.
	 */
	private $response_status = '';

	/**
	 * Response.
	 *
	 * @since    1.0.0
	 *
	 * @var string response.
	 */
	private $response = '';

	/**
	 * PAYPAL HOST.
	 *
	 * @since    1.0.0
	 *
	 * @var string PAYPAL HOST.
	 */
	const PAYPAL_HOST = 'www.paypal.com';

	/**
	 * Plugin name.
	 *
	 * @since    1.0.0
	 *
	 * @var string plugin name.
	 */
	const SANDBOX_HOST = 'www.sandbox.paypal.com';

	/**
	 *  Post Back Using cURL
	 *
	 *  Sends the post back to PayPal using the cURL library. Called by
	 *  the process_ipn() method if the use_curl property is true. Throws an
	 *  exception if the post fails. Populates the response, response_status,
	 *  and post_uri properties on success.
	 *
	 *  @param  string $encoded_data The post data as a URL encoded string.
	 *  @throws \Exception Exception.
	 */
	protected function curl_post( $encoded_data ) {
		if ( $this->use_ssl ) {
			$uri            = 'https://' . $this->get_paypal_host() . '/cgi-bin/webscr';
			$this->post_uri = $uri;
		} else {
			$uri            = 'http://' . $this->get_paypal_host() . '/cgi-bin/webscr';
			$this->post_uri = $uri;
		}

		$encoded_data['cmd'] = '_notify-validate';

		// Send back post vars to paypal.
		$params = array(
			'body'        => $encoded_data,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => false,
			'decompress'  => false,
			'user-agent'  => 'eb',
		);

		// Post back to get a response.
		$resp = wp_safe_remote_post( $uri, $params );

		$this->response_status = strval( wp_remote_retrieve_response_code( $resp ) );
		$this->response        = $resp['body'];

		if ( is_wp_error( $resp ) ) {
			$errstr = $resp->get_error_message();
			throw new \Exception( 'cURL error: ' . esc_html( $errstr ) );
		} elseif ( 200 === wp_remote_retrieve_response_code( $resp ) ) {
			// Set responce here.
			$this->response = $resp['body'];

		} else {
			throw new \Exception( 'cURL error: Failed to retrieve response.' );
		}
	}

	/**
	 *  Post Back Using fsockopen()
	 *
	 *  Sends the post back to PayPal using the fsockopen() function. Called by
	 *  the process_ipn() method if the use_curl property is false. Throws an
	 *  exception if the post fails. Populates the response, response_status,
	 *  and post_uri properties on success.
	 *
	 *  @param  string $encoded_data The post data as a URL encoded string.
	 *  @throws \Exception Exception.
	 */
	protected function fsock_post( $encoded_data ) {
		if ( $this->use_ssl ) {
			$uri            = 'ssl://' . $this->get_paypal_host();
			$port           = '443';
			$this->post_uri = $uri . '/cgi-bin/webscr';
		} else {
			$uri            = $this->get_paypal_host(); // no "http://" in call to fsockopen().
			$port           = '80';
			$this->post_uri = 'http://' . $uri . '/cgi-bin/webscr';
		}

		$_fp = fsockopen( $uri, $port, $errno, $errstr, $this->timeout ); // @codingStandardsIgnoreLine

		if ( ! $_fp ) {
			// fsockopen error.
			throw new \Exception( 'fsockopen error: [' . esc_html( $errno ) . '] ' . esc_html( $errstr ) );
		}

		$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= 'Content-Length: ' . strlen( $encoded_data ) . "\r\n";
		$header .= "Connection: Close\r\n\r\n";

		fputs( $_fp, $header . $encoded_data . "\r\n\r\n" );

		while ( ! feof( $_fp ) ) {
			if ( empty( $this->response ) ) {
				// extract HTTP status from first line.
				$status                = fgets( $_fp, 1024 );
				$this->response       .= $status;
				$this->response_status = trim( substr( $status, 9, 4 ) );
			} else {
				$this->response .= fgets( $_fp, 1024 );
			}
		}

		fclose( $_fp ); // @codingStandardsIgnoreLine
	}

	/**
	 * Get paypal host.
	 */
	private function get_paypal_host() {
		if ( $this->use_sandbox ) {
			return self::SANDBOX_HOST;
		} else {
			return self::PAYPAL_HOST;
		}
	}

	/**
	 *  Get POST URI
	 *
	 *  Returns the URI that was used to send the post back to PayPal. This can
	 *  be useful for troubleshooting connection problems. The default URI
	 *  would be "ssl://www.sandbox.paypal.com:443/cgi-bin/webscr"
	 *
	 *  @return string
	 */
	public function get_post_uri() {
		return $this->post_uri;
	}

	/**
	 *  Get Response
	 *
	 *  Returns the entire response from PayPal as a string including all the
	 *  HTTP headers.
	 *
	 *  @return string
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 *  Get Response Status
	 *
	 *  Returns the HTTP response status code from PayPal. This should be "200"
	 *  if the post back was successful.
	 *
	 *  @return string
	 */
	public function get_response_status() {
		return $this->response_status;
	}

	/**
	 *  Get Text Report
	 *
	 *  Returns a report of the IPN transaction in plain text format. This is
	 *  useful in emails to order processors and system administrators. Override
	 *  this method in your own class to customize the report.
	 *
	 *  @return string
	 */
	public function get_text_report() {
		$text_report = '';

		// date and POST url.
		for ( $i = 0; $i < 80; $i++ ) {
			$text_report .= '-';
		}
		$text_report .= "\n[" . gmdate( 'm/d/Y g:i A' ) . '] - ' . $this->get_post_uri();
		if ( $this->use_curl ) {
			$text_report .= " (curl)\n";
		} else {
			$text_report .= " (fsockopen)\n";
		}

		// POST vars.
		for ( $i = 0; $i < 80; $i++ ) {
			$text_report .= '-';
		}
		$text_report .= "\n";

		$text_report .= "PAYMENT VERIFICATION EMAIL \n";

		// POST vars.
		for ( $i = 0; $i < 80; $i++ ) {
			$text_report .= '-';
		}
		$text_report .= "\n";

		foreach ( $this->post_data as $key => $value ) {
			$value        = maybe_serialize( $value );
			$text_report .= str_pad( $key, 25 ) . "$value\n";
		}
		$text_report .= "\n\n";

		return $text_report;
	}

	/**
	 *  Process IPN
	 *
	 *  Handles the IPN post back to PayPal and parsing the response. Call this
	 *  method from your IPN listener script. Returns true if the response came
	 *  back as "VERIFIED", false if the response came back "INVALID", and
	 *  throws an exception if there is an error.
	 *
	 *  @param array $post_data post_data.
	 *  @throws \Exception Exception.
	 *  @return boolean
	 */
	public function process_ipn( $post_data = null ) {
		$encoded_data = array( 'cmd' => '=_notify-validate' );

		if ( null === $post_data ) {
			// use raw POST data.
			throw new \Exception( 'No POST data found.' );
		} else {
			// use provided data array.
			$this->post_data = $post_data;
			$encoded_data    = wp_unslash( $_POST ); // @codingStandardsIgnoreLine

		}

		if ( $this->use_curl ) {

			$this->curl_post( $encoded_data );
		} else {

			$this->fsock_post( $encoded_data );
		}

		if ( false === $this->response_status ) {

			throw new \Exception( 'Invalid response status: ' . esc_html( $this->response_status ) );
		}

		if ( strpos( $this->response, 'VERIFIED' ) !== false ) {

			return true;
		} elseif ( strpos( $this->response, 'INVALID' ) !== false ) {

			return false;
		} else {

			throw new \Exception( 'Unexpected response from PayPal.' );
		}
	}

	/**
	 *  Require Post Method
	 *
	 *  Throws an exception and sets a HTTP 405 response header if the request
	 *  method was not POST.
	 *
	 *  @throws \Exception Exception.
	 */
	public function require_post_method() {
		// require POST requests.
		$post_request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

		if ( ! $post_request_method ) {
			header( 'Allow: POST', true, 405 );
			throw new \Exception( 'Invalid HTTP request method.' );
		}
	}
}
