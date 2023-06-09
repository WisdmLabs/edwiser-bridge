<?php
/**
 * Template Loader
 *
 * Define template loading and overriding functionality
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/public
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Eb_Template_Loader.
 */
class Eb_Template_Loader {


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. edw looks for theme
	 * overrides in /theme/edw/ by default
	 *
	 * @since 1.0.0
	 * @param mixed $template temp.
	 * @return string
	 */
	public function template_loader( $template ) {
		$file          = '';
		$eb_templ_path = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_template_path();

		if ( is_single() && get_post_type() === 'eb_course' ) {
			$file   = 'single-eb_course.php';
			$find[] = $file;
			$find[] = $eb_templ_path . $file;
		} elseif ( is_post_type_archive( 'eb_course' ) ) {
			$file   = 'archive-eb_course.php';
			$find[] = $file;
			$find[] = $eb_templ_path . $file;
		}

		if ( $file ) {
			$template = locate_template( array_unique( $find ) );

			if ( ! $template ) {
				$template = plugin_dir_path( __DIR__ ) . 'public/templates/' . $file;
			}
		}
		return $template;
	}


	/**
	 * DEPRECATED FUNCTION.
	 *
	 * Get template part (for templates like the shop-loop).
	 *
	 * @deprecated since 2.0.1 use wp_get_template_part( $slug, $name )
	 * @since  1.0.0
	 * @param mixed  $slug slug.
	 * @param string $name (default: '').
	 * @return void
	 */
	public function wpGetTemplatePart( $slug, $name = '' ) {
		$this->wp_get_template_part( $slug, $name );
	}

	/**
	 * Get template part (for templates like the shop-loop).
	 *
	 * @since  1.0.0
	 * @param mixed  $slug slug.
	 * @param string $name (default: '').
	 * @return void
	 */
	public function wp_get_template_part( $slug, $name = '' ) {
		$template      = '';
		$eb_templ_path = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_template_path();
		$plugin_path   = plugin_dir_path( __DIR__ );
		// Look in yourtheme/edw/slug-name.php.
		if ( $name ) {
			$template = locate_template( array( "{$slug}-{$name}.php", $eb_templ_path . "{$slug}-{$name}.php" ) );
		}

		// Get default slug-name.php.
		if ( ! $template && $name && file_exists( $plugin_path . "public/templates/{$slug}-{$name}.php" ) ) {
			$template = $plugin_path . "public/templates/{$slug}-{$name}.php";
		}

		// If template file doesn't exist, look in yourtheme/edw/slug.php.
		if ( ! $template ) {
			$template = locate_template( array( "{$slug}.php", $eb_templ_path . "{$slug}.php" ) );
		}

		// Allow 3rd party plugin filter template file from their plugin.
		if ( ( ! $template ) || $template ) {
			$template = apply_filters( 'eb_get_template_part', $template, $slug, $name );
		}

		if ( $template ) {
			load_template( $template, false );
		}
	}


	/**
	 * DEPRECATED FUNCTION
	 *
	 * Get other templates.
	 *
	 * @deprecated since 2.0.1 use wp_get_template( $template_name, $args, $template_path, $default_path ) insted.
	 * @since  1.0.0
	 * @param string $template_name nsme.
	 * @param array  $args          (default: array()).
	 * @param string $template_path (default: '').
	 * @param string $default_path  (default: '').
	 * @return void
	 */
	public function wpGetTemplate( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		$this->wp_get_template( $template_name, $args, $template_path, $default_path );
	}

	/**
	 * Get other templates.
	 *
	 * @since  1.0.0
	 * @param string $template_name name.
	 * @param array  $args          (default: array()).
	 * @param string $template_path (default: '').
	 * @param string $default_path  (default: '').
	 * @return void
	 */
	public function wp_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		// Declare variables here.
		if ( $args && is_array( $args ) ) {
			extract( $args ); // @codingStandardsIgnoreLine
		}

		$located = $this->wp_locate_template( $template_name, $template_path, $default_path );

		if ( ! file_exists( $located ) ) {
			/* Translators 1: file path */
			_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( '%$1s', 'edwiser-bridge' ) . ' does not exist.', '<code>' . esc_html( $located ) . '</code>' ), '2.1' );
			return;
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters( 'eb_get_template', $located, $template_name, $args, $template_path, $default_path );

		do_action( 'eb_before_template_part', $template_name, $template_path, $located, $args );

		include $located;

		do_action( 'eb_after_template_part', $template_name, $template_path, $located, $args );
	}

	/**
	 * Function to get the template of the page.
	 *
	 * @param string $template_name Template file name.
	 * @param string $template_path Template file path.
	 * @param string $default_path Template file defualr path.
	 */
	public function eb_get_page_template( $template_name, $template_path = '', $default_path = '' ) {
		$located = $this->wp_locate_template( $template_name, $template_path, $default_path );

		if ( ! file_exists( $located ) ) {
			/* Translators 1: file path */
			_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( '%$1s', 'edwiser-bridge' ) . ' does not exist.', '<code>' . esc_html( $located ) . '</code>' ), '2.1' );
			return;
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters( 'eb_get_template_file', $located, $template_name, $template_path, $default_path );
		return $located;
	}

	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 *  yourtheme  / $template_path / $template_name
	 *  yourtheme  / $template_name
	 *  $default_path / $template_name
	 *
	 * @since  1.0.0
	 * @param string $template_name name.
	 * @param string $template_path (default: '').
	 * @param string $default_path  (default: '').
	 * @return string
	 */
	public function wp_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		$eb_plugin_dir        = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_dir();
		$eb_plugin_templ_path = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_template_path();

		if ( ! $template_path ) {
			$template_path = $eb_plugin_templ_path;
		}

		if ( ! $default_path ) {
			$default_path = $eb_plugin_dir . 'public/templates/';
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get default template.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Return what we found.
		return apply_filters( 'eb_locate_template', $template, $template_name, $template_path );
	}
}

/**
 * Depricated Class.
 *
 * @deprecated since 3.0.0
 */
class EbTemplateLoader extends EB_Template_Loader { // @codingStandardsIgnoreLine

}
