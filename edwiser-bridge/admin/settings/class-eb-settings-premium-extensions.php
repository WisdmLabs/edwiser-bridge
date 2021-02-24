<?php
/**
 * EDW Remui settings tab
 *
 * @link       https://edwiser.org
 * @since      1.3.1
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Settings_Premium_Extensions' ) ) :

	/**
	 * SettingsPremiumExtensions.
	 */
	class Eb_Settings_Premium_Extensions extends EBSettingsPage {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'remui';
			$this->label = __( 'Premium Extensions', 'eb-textdomain' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
		}

		/**
		 * Function to output conent on premium extensions page.
		 */
		public function output() {
			$GLOBALS['hide_save_button'] = 1;
			$extensions                  = array(
				array(
					'title' => 'Edwiser Bundle',
					'text'  => 'With Edwiser bundle, you get Edwiser Bridge extensions (WooCommerce Integration for $61, Single Sign On for $61, Selective Synchronization for $61, Bulk purchase for $61) adding up to $244 worth of extensions for only $108.',
					'img'   => plugins_url( 'edwiser-bridge/admin/assets/images/icon-bundle.png' ),
					'link'  => 'https://bit.ly/2NAJ7OW',
				),
				array(
					'title' => 'Edwiser WooCommerce Integration',
					'text'  => 'Use the power of WooCommerce to sell your Moodle courses from a WooCommerce store with the WooCommerce Integration Extension for Edwiser Bridge.',
					'img'   => plugins_url( 'edwiser-bridge/admin/assets/images/icon-woo-int.png' ),
					'link'  => 'https://bit.ly/2YWsjEj',
				),
				array(
					'title' => 'Edwiser Single Sign On',
					'text'  => 'The Single Sign On extension for Edwiser Bridge facilitates simultaneous login to your WordPress LMS and Moodle by entering login credentials only once.',
					'img'   => plugins_url( 'edwiser-bridge/admin/assets/images/icon-sso.png' ),
					'link'  => 'https://bit.ly/3tICgDx',
				),
				array(
					'title' => 'Edwiser Selective Synchronization',
					'text'  => 'Selectively synchronize Moodle courses or courses belonging to a particular category using the Selective Synchronization extension for Edwiser Bridge.',
					'img'   => plugins_url( 'edwiser-bridge/admin/assets/images/icon-sel-syn.png' ),
					'link'  => 'https://bit.ly/3tNRmrJ',
				),
				array(
					'title' => 'Edwiser Bulk Purchase',
					'text'  => 'The Bulk Purchase plugin lets you buy more than one Moodle course through WooCommerce at one go and enroll each student of your class.',
					'img'   => plugins_url( 'edwiser-bridge/admin/assets/images/icon-bp.png' ),
					'link'  => 'https://bit.ly/3pwnoF3',
				),
			);
			?>
			<style>
				.ebpe-wrap{
					font-family: 'Open Sans', sans-serif;
				}
				.eb-form-content-wrap{
					display: block !important;
				}
				.ebpe-wrap {
					display: flex;
					flex-wrap: wrap;
					list-style: none;
					margin: auto;
					padding: 0;
					max-width: 1020px;
				}
				.ebpe-card-item {
					display: flex;
					padding: 1rem;
					max-width: 300px;
				}
				.ebpe-card {
					background-color: white;
					border-radius: 0.25rem;
					box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
					display: flex;
					flex-direction: column;
					overflow: hidden;
					position: relative;
				}
				.ebpe-card:hover {
					box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
				}
				.ebpe-cc {
					padding: 1rem;
					display: flex;
					flex-direction: column;
					flex-grow: 1;
					justify-content: space-between;
				}
				.ebpe-cimage {
					background-color: #f7f7f7;
					min-height: 200px;
					display: flex;
				}
				.ebpe-cimage > img {
					height: auto;
					max-width: 100px;
					margin: auto;
					display: block;
				}
				.ebpe-ctitle {
					font-size: 18px;
					line-height: 1.2;
					font-weight: 400;
					margin: 0px;
				}
				.ebpe-ctext {
					line-height: 1.5;
					font-weight: 400;
				}
				.ebpe-cbtn {
					color: #f87140;
					padding: 7px 20px;
					text-decoration: none;
					background-color: white;
					border-radius: 3px;
					border: 1px solid #f87140;
					display: block;
					margin-left: auto;
					margin-right: auto;
					width: fit-content;
					font-weight: 500;
				}
				.ebpe-cbtn:hover {
					background-color: #f87140;
					color: white;
				}
				</style>
				<ul class='ebpe-wrap'>
				<?php foreach ( $extensions as $extension ) { ?>
					<li class='ebpe-card-item'>
					<div class="ebpe-card">
						<div class="ebpe-cimage">
							<img src="<?php echo esc_attr( $extension['img'] ); ?>">
						</div>
						<div class="ebpe-cc">
							<div class='ebpe-txt-wrap'>
								<h2 class="ebpe-ctitle"><?php echo esc_attr( $extension['title'] ); ?></h2>
								<p class="ebpe-ctext"><?php echo esc_attr( $extension['text'] ); ?></p>
							</div>
							<a href="<?php echo esc_attr( $extension['link'] ); ?>" target="_blank" class="btn ebpe-cbtn">View Detials</a>
						</div>
					</div>
					</li>
				<?php } ?>
				</ul>
			<?php
		}
	}


endif;

return new Eb_Settings_Premium_Extensions();
