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
	class Eb_Settings_Other_Plugins extends EB_Settings_Page {

		/**
		 * Plugin data.
		 *
		 * @var array
		 */
		public $plugin_data = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'other-plugins';
			$this->label = __( 'Other Edwiser Plugins', 'edwiser-bridge' );

			$this->plugin_data = self::get_other_plugin_data();

			add_filter( 'eb_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );
		}

		/**
		 * Function to output conent on premium extensions page.
		 */
		public function output() {
			$GLOBALS['hide_save_button'] = 1;

			?>
			<div class="eb_table" >
				<h1><?php esc_attr_e( 'Edwiser Bridge Plugins', 'edwiser-bridge' ); ?></h1>
				<div class="eb-pro-plugins">  
					<?php
					// non licensed plugins.
					foreach ( $this->plugin_data as $plugin ) {
						// create card for each plugin with activation/deactivation button.
						?>
						<div class="eb-pro-upgrade-plugin-card">
							<div class="eb-pro-plugin-card-header">
								<?php echo true === $plugin['free'] ? '<span class="eb-free-plugin">Free</span>' : '<span class="eb-pro-plugin">Premium</span>'; ?>
								<img src="<?php echo esc_attr( $plugin['img'] ); ?>" alt="">
								<h2><?php echo esc_attr( $plugin['name'] ); ?></h2>
							</div>
							<div class="eb-pro-plugin-card-body">
								<?php echo esc_attr( $plugin['desc'] ); ?>
							</div>
							<div class="eb-pro-plugin-card-footer">
								<a class="eb-pro-other-plugin-btn" href="<?php echo esc_attr( $plugin['link'] ); ?>"><?php esc_attr_e( 'Explore now', 'edwiser-bridge' ); ?></a>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Get other plugin data
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function get_other_plugin_data() {
			$plugin_data = array(
				array(
					'name' => 'All-in-One Edwiser Bundle',
					'desc' => __( 'Get your budget-friendly All-in-One bundle of Edwiser products at an unbeatable price.', 'edwiser-bridge' ),
					'link' => 'https://bit.ly/2NAJ7OW',
					'img'  => plugins_url( 'edwiser-bridge/admin/assets/images/ed-bundle.svg' ),
					'free' => false,
				),
				array(
					'name' => 'Edwiser Bridge PRO',
					'desc' => __( 'The Edwiser Bridge PRO is the ideal eCommerce solution, for anyone looking to sell their Moodle™courses with ease.', 'edwiser-bridge' ),
					'link' => 'https://bit.ly/2NAJ7OW',
					'img'  => plugins_url( 'edwiser-bridge/admin/assets/images/ed-bridge.svg' ),
					'free' => false,
				),
				array(
					'name' => 'Edwiser RemUI',
					'desc' => __( 'Edwiser RemUI is a Moodle™ theme that completely transforms your Moodle™ into a beautiful, clean & easy-to-use LMS', 'edwiser-bridge' ),
					'link' => 'https://bit.ly/2NAJ7OW',
					'img'  => plugins_url( 'edwiser-bridge/admin/assets/images/ed-remui.svg' ),
					'free' => false,
				),
				array(
					'name' => 'Edwiser Forms',
					'desc' => __( 'Create versatile forms in Moodle™ using Edwiser Forms, featuring Conditional Logic and a user-friendly Drag & Drop Builder.', 'edwiser-bridge' ),
					'link' => 'https://bit.ly/2NAJ7OW',
					'img'  => plugins_url( 'edwiser-bridge/admin/assets/images/ed-forms.svg' ),
					'free' => false,
				),
				array(
					'name' => 'Edwiser RapidGrader',
					'desc' => __( 'Effortlessly evaluate students in Moodle™ with Edwiser RapidGrader, utilizing smart grading and a streamlined interface.', 'edwiser-bridge' ),
					'link' => 'https://bit.ly/2NAJ7OW',
					'img'  => plugins_url( 'edwiser-bridge/admin/assets/images/ed-rapid-grader.svg' ),
					'free' => false,
				),
				array(
					'name' => 'Edwiser Reports',
					'desc' => __( 'Easily monitor learning trends and student engagement with this visual reporting plugin for Moodle™ software using Edwiser Reports', 'edwiser-bridge' ),
					'link' => 'https://bit.ly/2NAJ7OW',
					'img'  => plugins_url( 'edwiser-bridge/admin/assets/images/ed-reports.svg' ),
					'free' => true,
				),
				array(
					'name' => 'Edwiser Site Monitor',
					'desc' => __( 'Monitor and analyze your Moodle™ LMS with Edwiser Site Monitor, ensuring continuous operation and staying informed on critical aspects.', 'edwiser-bridge' ),
					'link' => 'https://bit.ly/2NAJ7OW',
					'img'  => plugins_url( 'edwiser-bridge/admin/assets/images/ed-site-monitor.svg' ),
					'free' => true,
				),
				array(
					'name' => 'Edwiser Course Formats',
					'desc' => __( 'Enhance course content presentation in Moodle™ with Edwiser Course Formats, offering List and Card layout options.', 'edwiser-bridge' ),
					'link' => 'https://bit.ly/2NAJ7OW',
					'img'  => plugins_url( 'edwiser-bridge/admin/assets/images/ed-course-format.svg' ),
					'free' => true,
				),
			);

			return $plugin_data;
		}
	}
}

return new Eb_Settings_Other_Plugins();
