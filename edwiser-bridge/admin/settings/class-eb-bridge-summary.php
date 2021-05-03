<?php
/**
 * EDW Licensing Management
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Bridge_Summary' ) ) :

	/**
	 * Eb_Settings_Licensing.
	 */
	class Eb_Bridge_Summary extends EBSettingsPage {

		/**
		 * Addon licensing.
		 *
		 * @var text $addon_licensing addon licensing
		 */
		public $addon_licensing;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'summary';
			$this->label = __( 'Stats', 'eb-textdomain' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0.0
		 */
		public function output() {
			// Hide the save button.
			$GLOBALS['hide_save_button'] = true;
			$porducts                    = array();
			$plugin_path                 = plugin_dir_path( __DIR__ );
			$this->get_edwiser_envirment( $plugin_path );
			$this->get_server_envirment_info( $plugin_path );

		}

		/**
		 * Function to get the edwiser bridge plugins list with the version numbers.
		 *
		 * @param string $plugin_path Plugin file path.
		 */
		private function get_edwiser_envirment( $plugin_path ) {
			$data = array(
				array(
					'label' => __( 'Edwiser Bridge :', 'eb-textdomain' ),
					'help'  => '',
					'value' => wdm_get_plugin_version( 'edwiser-bridge/edwiser-bridge.php' ),
				),
			);

			if ( ! class_exists( 'Eb_Licensing_Manger' ) ) {
				include_once $plugin_path . 'licensing/class-eb-licensing-manager.php';
			}
			$products_data = Eb_Licensing_Manger::get_plugin_data();
			foreach ( $products_data as $product ) {
				$data[] = array(
					'label' => $product['item_name'] . ' :',
					'help'  => '',
					'value' => $this->get_plugin_version_info( $product, $product['path'] ),
				);
			}
			$data = array_merge(
				$data,
				array(
					array(
						'label' => __( 'Moodle Edwiser Bridge :', 'eb-textdomain' ),
						'help'  => '',
						'value' => '',
					),
					array(
						'label' => __( 'Moodle Edwiser Single Sign On :', 'eb-textdomain' ),
						'help'  => '',
						'value' => '',
					),
					array(
						'label' => __( 'Moodle Edwiser Bulk Purchase :', 'eb-textdomain' ),
						'help'  => '',
						'value' => '',
					),
				)
			);

			$title = __( 'Edwiser Bridge Information', 'eb-textdomain' );
			include $plugin_path . 'partials/html-bridge-summary.php';
		}

		/**
		 * Function to get the edwiser bridge plugin configuration info.
		 *
		 * @param array  $product Array of the plugin data.
		 * @param string $plugin_path Plugin file path.
		 */
		private function get_plugin_version_info( $product, $plugin_path = false ) {
			$version_info = '';
			if ( $plugin_path ) {
				$version_info = wdm_get_plugin_version( $plugin_path );
			}
			// $this->get_plugin_remote_version($product);
			return $version_info;
		}

		/**
		 * Function to get the remote vesion of the product.
		 *
		 * @param array $data Array of the plugin information.
		 */
		private function get_plugin_remote_version( $data ) {
			$remote_data = get_transient( 'eb_stats_' . $data['slug'] );
			if ( ! $remote_data ) {
				$l_key       = get_option( $data['key'], '' );
				$remote_data = wdm_request_edwiser(
					array(
						'edd_action'      => 'get_version',
						'name'            => $data['item_name'],
						'slug'            => $data['slug'],
						'current_version' => $data['version'],
						'license'         => $l_key,
					)
				);
				if ( $remote_data['status'] && isset( $remote_data['data'] ) ) {
					$data        = $remote_data['data'];
					$remote_data = array(
						'version'  => isset( $data['new_version'] ) ? $data['new_version'] : '',
						'url'      => isset( $data['download_link'] ) ? $data['download_link'] : '',
						'homepage' => isset( $data['url'] ) ? $data['url'] : '',
					);
					set_transient( 'eb_stats_' . $data['slug'], $remote_data, 60 * 60 * 24 * 7 );
				}
			}
			$path = ! empty( $remote_data['url'] ) ? $remote_data['url'] : $remote_data['homepage'];
			return "(<a href='" . $remote_data['url'] . '>' . $remote_data['version'] . '</a>)';
		}
		/**
		 * Function to get the edwiser bridge plugin configuration information.
		 *
		 * @param string $plugin_path Plugin main file path to get the plugin information.
		 */
		private function get_server_envirment_info( $plugin_path ) {
			$course_count = \wp_count_posts( 'eb_course' );
			$data         = array(
				array(
					'label' => __( 'Wordpress Site URL:', 'eb-textdomain' ),
					'help'  => '',
					'value' => get_home_url(),
				),
				array(
					'label' => __( 'Moodle Site URL:', 'eb-textdomain' ),
					'help'  => '',
					'value' => wdm_edwiser_bridge_plugin_get_access_url(),
				),
				array(
					'label' => __( 'Access Token:', 'eb-textdomain' ),
					'help'  => '',
					'value' => wdm_edwiser_bridge_plugin_get_access_token(),
				),
				array(
					'label' => __( 'Permalink Structure:', 'eb-textdomain' ),
					'help'  => '',
					'value' => get_option( 'permalink_structure' ),
				),
				array(
					'label' => __( 'Number of Courses:', 'eb-textdomain' ),
					'help'  => '',
					'value' => sprintf( __( 'Publish (%1$d), Draft(%2$d), Trash (%3$d), Private(%4$d)', 'eb-textdomain' ), $course_count->publish, $course_count->draft, $course_count->trash, $course_count->private ),
				),
			);

			$title = __( 'Server Envirment Information', 'eb-textdomain' );
			include $plugin_path . 'partials/html-bridge-summary.php';
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.0.0
		 * @param text $current_section current section.
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			$settings = apply_filters(
				'eb_licensing',
				array(
					array(
						'title' => __( 'Licenses', 'eb-textdomain' ),
						'type'  => 'title',
						'id'    => 'licensing_management',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'licensing_management',
					),
				)
			);

			return apply_filters( 'eb_get_settings_' . $this->_id, $settings, $current_section );
		}
	}

endif;

return new Eb_Bridge_Summary();
