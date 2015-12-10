<?php

/**
 * Setup plugin menus in WP admin.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EB_Admin_Menus Class
 */
class EB_Admin_Menus {

	/**
	 * Hook in tabs.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add menus
		add_action( 'admin_menu',  array( $this, 'admin_menu' ), 9 );
		add_action( 'admin_menu',  array( $this, 'settings_menu' ), 10 );
		add_action( 'admin_menu',  array( $this, 'extensions_menu' ), 10 );
		add_action( 'admin_menu',  array( $this, 'help_menu' ), 10 );
		add_action( 'parent_file', array( $this, 'add_menu_page_taxonomy_fix' ), 10 );

		add_action( 'admin_footer', array( $this, 'open_help_menu_new_tab' ) ); // open help menu in new tab
	}

	/**
	 * Add menu items
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		global $menu;

		// add menu separator
		if ( current_user_can( 'manage_options' ) ) {
			$menu[53.5] = array( '', 'read', 'separator-edwiserbridge_lms', '', 'wp-menu-separator edwiserbridge_lms' );
		}
		
		add_menu_page( __( 'Edwiser Bridge', 'eb-textdomain' ), __( 'Edwiser Bridge', 'eb-textdomain' ), 'manage_options', 'edwiserbridge_lms', null, 'dashicons-book-alt', 54 );

		global $submenu;
		$location = 55;
		$add_submenu = array(
			array(
				"name"  =>  __( "Courses", 'eb-textdomain' ),
				"cap"   =>  "manage_options",
				"link"  => "edit.php?post_type=eb_course"
			),
			array(
				"name"  =>  __( "Course Categories", 'eb-textdomain' ),
				"cap"   =>  "manage_options",
				"link"  => "edit-tags.php?taxonomy=eb_course_cat&post_type=eb_course"
			),
			array(
				"name"  =>  __( "Orders", 'eb-textdomain' ),
				"cap"   =>  "manage_options",
				"link"  => "edit.php?post_type=eb_order"
			)
		);

		foreach ( $add_submenu as $key => $add_submenu_item ) {
			if ( current_user_can( $add_submenu_item["cap"] ) )
				$submenu['edwiserbridge_lms'][$location++] = array( $add_submenu_item['name'], $add_submenu_item['cap'], $add_submenu_item['link'] );
		}

		//echo '<pre>'; print_r($menu); echo '</pre>';
	}

	/**
	 * Taxonomy fix to display correct submenu selected when on moodle categories menu
	 *
	 * @since 1.0.0
	 * @param string  $parent_file slug of current main menu selected
	 */
	public function add_menu_page_taxonomy_fix( $parent_file ) {
		global $submenu_file, $current_screen, $pagenow;

		// Set the submenu as active/current while anywhere in Custom Post Type ( courses, orders )
		if ( $current_screen->post_type == 'eb_course' || $current_screen->post_type == 'eb_order' ) {

			if ( $pagenow == 'post.php' ) {
				$submenu_file = 'edit.php?post_type='.$current_screen->post_type;
			}

			if ( $pagenow == 'edit-tags.php' ) {
				$submenu_file = 'edit-tags.php?taxonomy=eb_course_cat&post_type='.$current_screen->post_type;
			}
			$parent_file = 'edwiserbridge_lms';
		}
		return $parent_file;
	}

	/**
	 * Add settings submenu item
	 *
	 * @since 1.0.0
	 */
	public function settings_menu() {
		$settings_page = add_submenu_page( 'edwiserbridge_lms', __( 'Settings', 'eb-textdomain' ),  __( 'Settings', 'eb-textdomain' ) , 'manage_options', 'eb-settings', array( $this, 'settings_page' ) );
	}

	/**
	 * Add extensions submenu item
	 *
	 * @since 1.0.0
	 */
	public function extensions_menu() {
		$extensions_page = add_submenu_page( 'edwiserbridge_lms', __( 'Extensions', 'eb-textdomain' ),  __( 'Extensions', 'eb-textdomain' ) , 'manage_options', 'eb-extensions', array( $this, 'extensions_page' ) );
	}

	/**
	 * Add help submenu item
	 *
	 * @since 1.0.0
	 */
	public function help_menu() {
		global $submenu;

		$submenu['edwiserbridge_lms'][] = array( '<div id="helpmenu">Help</div>', 'manage_options', 'https://edwiser.org/bridge/documentation/' );
	}


	/**
	 * open plugin help link in new tab
	 *
	 * @since  1.0.0
	 */
	function open_help_menu_new_tab() {
?>
		    <script type="text/javascript">
		        jQuery(document).ready( function() {
		            jQuery('#helpmenu').parent().attr('target','_blank');
		        });
		    </script>
	    <?php
	}

	/**
	 * Initialize the settings page
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {
		EB_Admin_Settings::output();
	}

	/**
	 * Initialize the extensions page
	 */
	public function extensions_page() {
		EB_Admin_Extensions::output();
	}
}

return new EB_Admin_Menus();
