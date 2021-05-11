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
			$response   = wp_remote_get( 'https://edwiser.org/edwiserdemoimporter/bridge-free-plugin-info.json' );
			$fetch_data = false;
			if ( isset( $_GET['fetch_data'] ) && 'true' === $_GET['fetch_data'] ) {
				$fetch_data = true;
			}
			$free_plugin_data = array();
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$responce = json_decode( wp_remote_retrieve_body( $response ) );
				foreach ( $responce as $key => $value ) {
					$free_plugin_data[ $key ] = array(
						'name'    => $value->name,
						'version' => $value->version,
						'url'     => $value->url,
					);
				}
			}
			$data = array(
				array(
					'label' => __( 'Edwiser Bridge :', 'eb-textdomain' ),
					'help'  => '',
					'value' => $this->show_plugin_version( $free_plugin_data['edwiser_bridge'], wdm_get_plugin_version( 'edwiser-bridge/edwiser-bridge.php' ) ),
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
					'value' => $this->get_plugin_version_info( $product, $product['path'], $fetch_data ),
				);
			}
			$data           = array_merge(
				$data,
				array(
					array(
						'label' => __( 'Moodle Edwiser Bridge :', 'eb-textdomain' ),
						'help'  => '',
						'value' => $this->show_plugin_version( $free_plugin_data['moodle_edwiser_bridge'] ),
					),
					array(
						'label' => __( 'Moodle Edwiser Single Sign On :', 'eb-textdomain' ),
						'help'  => '',
						'value' => $this->show_plugin_version( $free_plugin_data['moodle_edwiser_bridge_sso'] ),
					),
					array(
						'label' => __( 'Moodle Edwiser Bulk Purchase :', 'eb-textdomain' ),
						'help'  => '',
						'value' => $this->show_plugin_version( $free_plugin_data['moodle_edwiser_bridge_bp'] ),
					),
				)
			);
			$refresh_url    = admin_url( '/admin.php?page=eb-settings&tab=summary&fetch_data=true' );
			$refresh_button = '<a class="wdm-stat-reload" title="' . __( 'Check update again', 'eb-textdomain' ) . '" href="' . $refresh_url . '"><span class="dashicons dashicons-update-alt"></span></a>';
			$title          = __( 'Edwiser Bridge Information', 'eb-textdomain' ) . $refresh_button;
			include $plugin_path . 'partials/html-bridge-summary.php';
		}

		/**
		 * Function to get the edwiser bridge plugin configuration info.
		 *
		 * @param array  $product Array of the plugin data.
		 * @param string $plugin_path Plugin file path.
		 * @param bool   $fetch_data Should force to fetch the remote data.
		 */
		private function get_plugin_version_info( $product, $plugin_path = false, $fetch_data = false ) {
			$version_info = false;
			if ( $plugin_path ) {
				$version_info = wdm_get_plugin_version( $plugin_path );
			}
			$remote_data = $this->get_plugin_remote_version( $product, $fetch_data );
			return $this->show_plugin_version( $remote_data, $version_info );
		}

		/**
		 * Function to display the curent plugin version and compair it with remote version of plugin and show appropriate message.
		 *
		 * @param array  $remote_data Remote plugin version detials.
		 * @param string $version_info installed plugin version numbers.
		 */
		private function show_plugin_version( $remote_data, $version_info = false ) {
			ob_start();
			if ( ! $version_info ) {
				?>
				<?php esc_attr_e( 'Not Available', 'eb-textdomain' ); ?>
				<span style='padding-left:1rem;'>
				<?php echo esc_attr( $remote_data['version'] ); ?>
				<a target='_blank' href="<?php echo esc_url( $remote_data['url'] ); ?>" title='<?php esc_attr_e( 'Plugin is not installed, Click to download the plugin file.', 'eb-textdomain' ); ?>'><?php esc_attr_e( 'Download Plugin', 'eb-textdomain' ); ?></a>
				</span>
				<?php
			} elseif ( $remote_data['version'] && version_compare( $remote_data['version'], $version_info, '>' ) ) {
				echo esc_attr( $version_info );
				?>
				<span style='padding-left:1rem;'>
				<?php echo esc_attr( $remote_data['version'] ); ?>
				<a target='_blank' href="<?php echo esc_url( $remote_data['url'] ); ?>" title='<?php esc_attr_e( 'Click to download the plugin file. Or you can update the from plugin page.', 'eb-textdomain' ); ?>'><?php echo esc_attr_e( 'Download', 'eb-textdomain' ); ?></a>
				</span>
				<?php
			} else {
				echo esc_attr( $version_info );
				?>
				<span style='color:limegreen; padding-left:1rem;'><?php esc_attr_e( 'Latest version', 'eb-textdomain' ); ?></span>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * Function to get the remote vesion of the product.
		 *
		 * @param array $data Array of the plugin information.
		 * @param bool  $force_remote_data Should force to fetch the remote data.
		 */
		private function get_plugin_remote_version( $data, $force_remote_data = false ) {
			$remote_data = get_transient( 'eb_stats_' . $data['slug'] );
			if ( ! $remote_data || $force_remote_data ) {
				$l_key    = get_option( $data['key'], '' );
				$responce = wdm_request_edwiser(
					array(
						'edd_action'      => 'get_version',
						'name'            => $data['item_name'],
						'slug'            => $data['slug'],
						'current_version' => $data['current_version'],
						'license'         => $l_key,
					)
				);
				if ( 200 === $responce['status'] ) {
					$data        = $responce['data'];
					$remote_data = array(
						'version'  => isset( $data->new_version ) ? $data->new_version : '',
						'url'      => isset( $data->download_link ) ? $data->download_link : '',
						'homepage' => isset( $data->homepage ) ? $data->homepage : '',
					);
					set_transient( 'eb_stats_' . $data->slug, $remote_data, 60 * 60 * 24 * 7 );
				}
			}
			return array(
				'version' => isset( $remote_data['version'] ) ? $remote_data['version'] : false,
				'url'     => ! empty( $remote_data['url'] ) ? $remote_data['url'] : $remote_data['homepage'],
			);
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
