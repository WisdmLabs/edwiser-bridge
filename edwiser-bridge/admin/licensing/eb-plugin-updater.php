<?php
/**
 * Handles the plugin update functionality.
 *
 * @package    edwiserBridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Allows plugins to use their own update API.
 *
 * @author Pippin Williamson
 * @modified WisdmLabs
 *
 * @version 1.1
 */
if ( ! class_exists( 'EbPluginUpdater' ) ) {
	/**
	 * Class to update the plugin.
	 */
	class EbPluginUpdater {

		/**
		 * Plugin store url to get the plugin.
		 *
		 * @var string $api_url Store url to get the plugin.
		 */
		private $api_url = '';

		/**
		 * Plugin Api responce data.
		 *
		 * @var string $api_data Plugin Api responce data.
		 */
		private $api_data = array();

		/**
		 * Plugin name.
		 *
		 * @var string $name Plugin name.
		 */
		private $name = '';

		/**
		 * Plugin slug.
		 *
		 * @var string $slug Plugin slug.
		 */
		private $slug = '';

		/**
		 * Store responce data.
		 *
		 * @var string $resp_data Reponce data.
		 */
		private static $resp_data;

		/**
		 * Class constructor.
		 *
		 * @uses plugin_basename()
		 * @uses hook()
		 *
		 * @param string $api_url     The URL pointing to the custom API endpoint.
		 * @param string $plugin_file Path to the plugin file.
		 * @param array  $api_data    Optional data to send with API calls.
		 */
		public function __construct( $api_url, $plugin_file, $api_data = null ) {
			$this->api_url  = trailingslashit( $api_url );
			$this->api_data = urlencode_deep( $api_data );
			$this->name     = plugin_basename( $plugin_file );
			$this->slug     = basename( $plugin_file, '.php' );
			$this->version  = $api_data['version'];

			// Set up hooks.
			$this->hook();
		}

		/**
		 * Set up WordPress filters to hook into WP's update process.
		 *
		 * @uses add_filter()
		 */
		private function hook() {
			add_filter(
				'pre_set_site_transient_update_plugins',
				array(
					$this,
					'preSetSiteTransientUpdatePluginsFilter',
				)
			);
			add_filter(
				'pre_set_transient_update_plugins',
				array(
					$this,
					'preSetSiteTransientUpdatePluginsFilter',
				)
			);
			add_filter( 'plugins_api', array( $this, 'pluginsApiFilter' ), 10, 3 );
		}

		/**
		 * Check for Updates at the defined API endpoint and modify the update array.
		 *
		 * This function dives into the update api just when WordPress creates its update array,
		 * then adds a custom API call and injects the custom plugin data retrieved from the API.
		 * It is reassembled from parts of the native WordPress plugin update code.
		 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
		 *
		 * @uses api_request()
		 *
		 * @param array $_transient_data Update array build by WordPress.
		 *
		 * @return array Modified update array with custom plugin data.
		 */
		public function preSetSiteTransientUpdatePluginsFilter( $_transient_data ) {
			if ( empty( $_transient_data ) ) {
				return $_transient_data;
			}

			$to_send = array( 'slug' => $this->slug );

			$api_response = $this->apiRequest( $to_send );

			if ( false !== $api_response && is_object( $api_response ) && isset( $api_response->new_version ) ) {
				if ( version_compare( $this->version, $api_response->new_version, '<' ) ) {
					$_transient_data->response[ $this->name ] = $api_response;
				}
			}

			return $_transient_data;
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @param mixed  $data request data.
		 * @param string $action action name.
		 * @param object $args reuest args.
		 */
		public function pluginsApiFilter( $data, $action = '', $args = null ) {
			if ( ( 'plugin_information' !== $action ) || ! isset( $args->slug ) || ( $args->slug !== $this->slug ) ) {
				return $data;
			}

			$to_send = array( 'slug' => $this->slug );

			$api_resp = $this->apiRequest( $to_send );
			if ( false !== $api_resp ) {
				$data = $api_resp;
			}

			return $data;
		}

		/**
		 * Calls the API and, if successfull, returns the object delivered by the API.
		 *
		 * @param array $data Parameters for the API action.
		 */
		private function apiRequest( $data ) {
			if ( null !== self::$resp_data ) {
				return self::$resp_data;
			}
			$data = array_merge( $this->api_data, $data );

			if ( $data['slug'] !== $this->slug ) {
				return;
			}

			if ( empty( $data['license'] ) ) {
				return;
			}

			$api_params = array(
				'edd_action'      => 'get_version',
				'license'         => $data['license'],
				'name'            => $data['item_name'],
				'slug'            => $this->slug,
				'author'          => $data['author'],
				'current_version' => $this->version,
			);

			$request = wp_remote_get(
				add_query_arg( $api_params, $this->api_url ),
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'blocking'  => true,
				)
			);

			if ( ! is_wp_error( $request ) ) {
				$request = json_decode( wp_remote_retrieve_body( $request ) );
				if ( $request && isset( $request->sections ) ) {
					$request->sections = maybe_unserialize( $request->sections );
				}
				self::$resp_data = $request;
				return $request;
			} else {
				self::$resp_data = false;
				return false;
			}
		}
	}
}
