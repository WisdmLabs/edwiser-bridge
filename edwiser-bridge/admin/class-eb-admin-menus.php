<?php
/**
 * Setup plugin menus in WP admin.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * Eb_Admin_Menus Class
 */
class Eb_Admin_Menus {

	/**
	 * Hook in tabs.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add menus.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 10 );
		add_action( 'admin_menu', array( $this, 'email_template' ), 10 );
		add_action( 'admin_menu', array( $this, 'manage_enrollment_menu' ), 10 );
		add_action( 'admin_footer', array( $this, 'open_help_menu_new_tab' ) ); // open help menu in new tab.
	}

	/**
	 * Add menu items
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=eb_course',
			__( 'Orders', 'edwiser-bridge' ),
			__( 'Orders', 'edwiser-bridge' ),
			'manage_options',
			'edit.php?post_type=eb_order'
		);
	}

	/**
	 * Add settings submenu item
	 *
	 * @since 1.1.0
	 */
	public function settings_menu() {
		add_submenu_page(
			'edit.php?post_type=eb_course',
			__( 'Settings', 'edwiser-bridge' ),
			__( 'Settings', 'edwiser-bridge' ),
			'manage_options',
			'eb-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Add submenu `Manage Enrollment` to manage user enrollment under EB.
	 *
	 * @since 1.2.2
	 */
	public function manage_enrollment_menu() {
		add_submenu_page(
			'edit.php?post_type=eb_course',
			__( 'User Enrollment', 'edwiser-bridge' ),
			__( 'Manage Enrollment', 'edwiser-bridge' ),
			'manage_options',
			'mucp-manage-enrollment',
			array( $this, 'manage_enrollment_content' )
		);
	}

	/**
	 * Add extensions submenu item
	 *
	 * @since 1.0.0
	 */
	public function email_template() {
		add_submenu_page(
			'edit.php?post_type=eb_course',
			__( 'Manage Email Templates', 'edwiser-bridge' ),
			__( 'Manage Email Templates', 'edwiser-bridge' ),
			'manage_options',
			'eb-email-template',
			array( $this, 'email_template_page' )
		);
	}

	/**
	 * Open plugin help link in new tab.
	 *
	 * @since  1.0.0
	 */
	public function open_help_menu_new_tab() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery('#helpmenu').parent().attr('target', '_blank');
			});
		</script>
		<?php
	}

	/**
	 * Initialize the settings page.
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {
		Eb_Admin_Settings::output();
	}

	/**
	 * Render Enrollment  manager page content.
	 *
	 * @since 1.2.2
	 */
	public function manage_enrollment_content() {
		$edwiser            = EdwiserBridge::instance();
		$enrollment_manager = new Eb_Manage_Enrollment( $edwiser->get_plugin_name(), $edwiser->get_version() );
		$enrollment_manager->out_put();
	}

	/**
	 * Initialize the extensions page.
	 */
	public function extensions_page() {
		Eb_Extensions::output();
	}

	/**
	 * Email template page.
	 */
	public function email_template_page() {
		$email_tmpl = new EB_Email_Template();
		$email_tmpl->output();
	}
}
return new Eb_Admin_Menus();
