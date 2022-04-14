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

if ( ! class_exists( 'Eb_Settings_Premium_Features' ) ) {

	/**
	 * SettingsPremiumExtensions.
	 */
	class Eb_Settings_Premium_Features extends EBSettingsPage {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'pfetures';
			$this->label = __( 'Upgrade to Premium', 'edwiser-bridge' );

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
		}

		/**
		 * Function to output conent on premium extensions page.
		 */
		public function output() {
			$GLOBALS['hide_save_button'] = 1;
			$fetures                     = array(
				array(
					'title'  => 'Sell to a wider audience',
					'desc'   => 'Sell your Moodle courses without any geographical barriers via 160+ payment gateways through one of the best e commerce platforms i.e WooCommerce. Easily set up a complete digital storefront with all e commerce functionalities. And transact in multiple currencies with learners around the world.',
					'img'    => plugins_url( 'edwiser-bridge/admin/assets/images/ebpf_1.png' ),
					'layout' => 'tr',
				),
				array(
					'title'  => 'Create better pricing plans OR Set yourself up for better profits',
					'desc'   => 'Sell courses the way you like. Run sales, set discounts and more. You can also set up recurring payments by selling courses as subscriptions. Create better pricing plans to maximise course sales.',
					'img'    => plugins_url( 'edwiser-bridge/admin/assets/images/ebpf_2.png' ),
					'layout' => 'tl',
				),
				array(
					'title'  => 'Simplify navigation',
					'desc'   => 'No need to maintain two passwords for both WordPress and Moodle. With the simultaneous login functionality, students get signed into Moodle when they log into WordPress. And clicking on the enrolled course in WordPress takes them directly to the relevant course in Moodle.',
					'img'    => plugins_url( 'edwiser-bridge/admin/assets/images/ebpf_3.png' ),
					'layout' => 'tr',
				),
				array(
					'title'  => 'Easily manage courses and enrollments',
					'desc'   => 'Sell all your Moodle courses or choose & sync a select few to sell them on WordPress. Flexibly set prices for the courses & configure WooCommerce products as per your needs. Allow multiple students to enroll into a course at once.',
					'img'    => plugins_url( 'edwiser-bridge/admin/assets/images/ebpf_4.png' ),
					'layout' => 'tl',
				),
			);
			?>
			<style>
				.ebpf-wrap{
					font-family: 'Open Sans', sans-serif;
				}
				.ebpf-header {
					background: white;
					padding: 3.5rem;
					text-align: center;
				}
				.ebpf-title {
					font-weight: 500;
					font-size: 34px;
					line-height: 1.2;
					color: #444444;
					margin: 1rem;
				}
				.ebpf-sub-title {
					font-size: 24px;
				}
				.ebpf-fbtn:hover,.ebpf-fbtn,.ebpf-hbtn,.ebpf-hbtn:hover {
					color: white;
					background-color: #f87140;
					text-decoration: none;
					padding: 20px 35px;
					border-radius: 5px;
					font-size: 24px;
					display: block;
					width: fit-content;
					margin: 2rem auto;
				}
				.ebpf-scroll-to {
					margin-top: 5rem;
				}
				.ebpf-scroll-to > p {
					font-size: 28px;
					margin: unset;
				}
				.ebpf-scroll-to > span {
					font-size: 16px;
				}
				.ebpf-st-arrow:hover,.ebpf-st-arrow {
					display: flex;
					flex-direction: column;
					align-items: center;
					margin-top: 2rem;
					text-decoration: none;
				}
				.ebpf-st-arrow > span {
					color: #f57040;
					height: auto;
					line-height: 0.6;
				}
				.ebpg-fw {
					display: flex;
					flex-direction: row;
					padding: 3rem 0.5rem;
				}
				.ebpg-fw.tl {
					flex-direction: row-reverse;
					background-color: white;
				}
				.ebpf-fdetials {
					margin: auto 2rem;
				}
				.ebpf-fdetials > h2 {
					font-size: 24px;
					font-weight: 400;
					line-height:1;
				}
				.ebpf-fdetials > p {
					font-size: 16px;
				}
				.ebpf-footer {
					background-color: #1b4a4b;
					overflow: hidden;
					position: relative;
				}
				.ebpf-fcontent > h3 {
					font-size: 20px;
					color: white;
					text-align: center;
					font-weight: 400;
					line-height: 1.3;
					margin: unset;
				}
				.ebpf-fbtn:hover,.ebpf-fbtn {
					font-size: 20px;
					padding: 13px 30px;
					margin: 1rem auto;
				}
				.ebpf-fimg1,
				.ebpf-fimg2 {
					position: absolute;
				}
				.ebpf-fimg1 {
					left: 0;
				}
				.ebpf-fimg2 {
					right: 0;
				}
				.ebpf-fcontent {
					padding: 1.5rem;
					position:relative;
				}
			</style>
			<div class='ebpf-wrap'>
				<div class='ebpf-header'>
					<div class='ebpf-title'>
					Unlock the power of<br/>WooCommerce, 165+ payment gateways,<br>10x eLearning profits, and much more 
					</div>
					<div class='ebpf-sub-title'>with the Edwiser Bridge PRO.</div>
					<a href="https://bit.ly/2NAJ7OW" target="_blank" class="ebpf-hbtn">Upgrade to Edwiser Bridge PRO</a>
					<div class="ebpf-scroll-to">
						<p>PREMIUM FEATURES</p>
						<span>Scroll Down to View Premium Features</span>
						<a href="#ebpf-fetures-wrap" class='ebpf-st-arrow'>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</a>
					</div>
				</div>
				<div id="ebpf-fetures-wrap">
				<?php
				foreach ( $fetures as $feture ) {
					$css_fwrap = $feture['layout'] . ' ebpg-fw';
					?>
					<div class="<?php echo esc_html( $css_fwrap ); ?>">                    
						<img alt='' src='<?php echo esc_html( $feture['img'] ); ?>' class='ebpf-fi'/>
						<div class="ebpf-fdetials">
							<h2><?php echo esc_html( $feture['title'] ); ?></h2>
							<p><?php echo esc_html( $feture['desc'] ); ?></p>
						</div>
					</div>
					<?php } ?>
				</div>
				<div class="ebpf-footer">
					<img class="ebpf-fimg1" src="<?php echo esc_url( plugins_url( 'edwiser-bridge/admin/assets/images/ebpf-fbg1.png' ) ); ?>"/>
					<img class="ebpf-fimg2" src="<?php echo esc_url( plugins_url( 'edwiser-bridge/admin/assets/images/ebpf-fbg2.png' ) ); ?>"/>
					<div class="ebpf-fcontent">
						<h3>Increase profits by automated course selling with<br/>Edwiser Bridge PRO</h3>
						<a href="https://bit.ly/2NAJ7OW" target="_blank" class="ebpf-fbtn">Buy Now</a>
					</div>
				</div>
			</div>
			<?php
		}
	}
}

return new Eb_Settings_Premium_Features();
