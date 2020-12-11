<?php
/**
 * EDW Eb_Admin_Marketing_Add Class.
 *
 * @since      1.2.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Admin_Marketing_Add' ) ) {

	/**
	 * EbAdminSettings.
	 */
	class Eb_Admin_Marketing_Add {
		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'eb_settings_footer', array( $this, 'output' ) );
		}

		/**
		 * Output marketing banner.
		 */
		public function output() {
			?>
			<div class='eb-marketing-add'>
				<a target="_blank" href='https://edwiser.org/bridge/extensions/edwiser-bundle/'>
					<img alt="<?php __( 'Sorry, Unable to load image', 'eb-textdomain' ); ?>" src="<?php echo esc_url( plugins_url( 'edwiser-bridge/admin/assets/images/rem-ui.png' ) ); ?>">
				</a>
			</div>
			<?php
		}
	}
}
new Eb_Admin_Marketing_Add();

