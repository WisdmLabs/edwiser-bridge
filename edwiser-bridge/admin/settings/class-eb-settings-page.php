<?php
/**
 * EDW Settings Page/Tab
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

if ( ! class_exists( 'ESettingsPage' ) ) :

	/**
	 * EB_Settings_Page
	 */
	abstract class EB_Settings_Page {
		/**
		 * Id.
		 *
		 * @var text $_id Id
		 */
		protected $_id = ''; // @codingStandardsIgnoreLine.

		/**
		 * Label.
		 *
		 * @var text $label label
		 */
		protected $label = '';

		/**
		 * Constructor
		 */
		public function __construct() {
			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_sections_' . $this->_id, array( $this, 'output_sections' ) );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
			add_action( 'eb_settings_save_' . $this->_id, array( $this, 'save' ) );
		}

		/**
		 * Add this page to settings
		 *
		 * @deprecated somce 2.0.0 use add_settings_page().
		 * @param text $pages pages.
		 * @since  1.0.0
		 */
		public function addSettingsPage( $pages ) {
			return $this->add_settings_page( $pages );
		}

		/**
		 * Add this page to settings
		 *
		 * @param text $pages pages.
		 * @since  1.0.0
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->_id ] = $this->label;
			return $pages;
		}

		/**
		 * Get settings array
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function get_settings() {
			apply_filters_deprecated( 'eb_getSettings_' . $this->_id, array(), '2.0.1', 'eb_get_settings_' . $this->_id );
			return apply_filters( 'eb_get_settings_' . $this->_id, array() );
		}

		/**
		 * Deprecated Function
		 *
		 * @deprecated since 2.0.0 use get_settings()
		 * Get settings array
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function getSettings() {
			return $this->get_settings();
		}

		/**
		 * Get sections
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function get_sections() {
			apply_filters( 'eb_get_sections_' . $this->_id, array() );
			return apply_filters_deprecated( 'eb_getSections_' . $this->_id, array( array() ), '2.0.1', 'eb_get_sections_' . $this->_id );
		}

		/**
		 * Deprecated Function
		 *
		 * @deprecated since 2.0.0 use get_sections()
		 * Get sections
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function getSections() {
			return $this->get_sections();
		}

		/**
		 * Output sections
		 *
		 * @since  1.0.0
		 */
		public function output_sections() {
			global $current_section;
			$sections = $this->getSections();
			if ( empty( $sections ) ) {
				return;
			}

			echo '<ul class="subsubsub">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li>';
				echo '<a href="' .
				esc_url(
					admin_url(
						'admin.php?page=eb-settings&tab=' . $this->_id . '&section=' . sanitize_title( $id )
					)
				) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ';
				echo esc_html( ( end( $array_keys ) === $id ? '' : '|' ) ) . ' </li>';
			}

			echo '</ul><br class="clear" />';
		}

		/**
		 * Deprecated Function.
		 *
		 * @deprecated 2.0.0 Use output_sections().
		 *
		 * Output sections
		 *
		 * @since  1.0.0
		 */
		public function outputSections() {
			$this->output_sections();
		}

		/**
		 * Output the settings
		 *
		 * @since  1.0.0
		 */
		public function output() {
			$settings = $this->getSettings();
			if ( empty( $settings ) ) {
				$settings = $this->get_settings();
			}

			Eb_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings
		 *
		 * @since  1.0.0
		 */
		public function save() {
			global $current_section;

			$settings = $this->get_settings();
			Eb_Admin_Settings::save_fields( $settings );

			if ( $current_section ) {
				do_action( 'eb_update_options_' . $this->_id . '_' . $current_section );
			}
		}
	}

endif;

/**
 * Deprecated Class.
 *
 * @deprecated 3.0.0
 */
class EBSettingsPage extends EB_Settings_Page { // @codingStandardsIgnoreLine.

}
