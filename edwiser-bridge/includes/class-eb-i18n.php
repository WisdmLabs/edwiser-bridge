<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 * @package    Edwiser Bridge
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * I18n.
 */
class Eb_I18n {

	/**
	 * The domain specified for this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The domain identifier for this plugin.
	 */
	private $domain;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			$this->domain,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
	 * Set the domain equal to that of the specified domain.
	 *
	 * @since    1.0.0
	 *
	 * @param string $domain The domain that represents the locale of this plugin.
	 */
	public function set_domain( $domain ) {
		$this->domain = $domain;
	}

	/**
	 * Compatibility with eb-textdomain text domain.
	 *
	 * @since    2.1.6
	 *
	 * @param string $mofile mofile for the plugin.
	 * @param string $domain text domain for the plugin.
	 */
	public function load_edwiser_bridge_textdomain( $mofile, $domain ) {

		$notice_dismissed = get_option( 'eb_rename_file_notice_dismissed' );
		if ( 'false' === $notice_dismissed ) {
			$eb_renamed_lang_files = get_option( 'eb_renamed_lang_files' );
			if ( 'true' !== $eb_renamed_lang_files && 'edwiser-bridge' === $domain && is_admin() ) {
				$this->rename_langauge_files();
			}
		}

		if ( 'edwiser-bridge' === $domain && 0 === strpos( $mofile, WP_LANG_DIR . '/plugins/' ) && ! file_exists( $mofile ) ) {
			$mofile = dirname( $mofile ) . DIRECTORY_SEPARATOR . str_replace( $domain, 'eb-textdomain', basename( $mofile ) );
		}

		return $mofile;
	}

	/**
	 * Check if language files are renamed and if not rename them each time user login.
	 * hook : wp_login
	 *
	 * @since    2.1.6
	 */
	public function check_file_renaming() {
		$eb_renamed_lang_files = get_option( 'eb_renamed_lang_files' );
		// check if user is admin and if language files are renamed.
		if ( 'true' !== $eb_renamed_lang_files ) {
			$this->rename_langauge_files();
		}
	}

	/**
	 * Display admin notice if translation files are not renamed.
	 *
	 * @since    2.1.6
	 */
	public function eb_admin_notice_failed_rename_files() {

		// if notice is dismissed, do not show it again.
		if ( true === filter_input( INPUT_GET, 'eb-rename-lang-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) ) {
			update_option( 'eb_rename_file_notice_dismissed', 'true' );
			return;
		}

		$eb_renamed_lang_files = get_option( 'eb_renamed_lang_files' );
		if ( ! empty( $eb_renamed_lang_files ) && 'true' !== $eb_renamed_lang_files ) {
			$fileinfo = array();
			$msg      = '';
			foreach ( $eb_renamed_lang_files as $file ) {
				$fileinfo[ dirname( $file ) ][] = basename( $file );
			}
			foreach ( $fileinfo as $dir => $files ) {
				$msg .= '<li>Go to <strong>' . $dir . '</strong> directory and rename the following files : <br>';
				foreach ( $files as $file ) {
					$msg .= 'rename from ' . $file . ' to ' . str_replace( 'eb-textdomain', 'edwiser-bridge', $file ) . '<br>';
				}
				$msg .= '</li>';
			}
			$redirection = add_query_arg( 'eb-rename-lang-notice-dismissed', true );
			$class       = 'notice notice-error ';
			$message     = '<h3>Edwiser Bridge is unable to rename translation files. please rename files manually</h3>
			<p> Please rename the following files manually:</p>
			<ul>
				' . $msg . '
			</ul>
			<a href="' . esc_html( $redirection ) . '" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			';

			printf( '<div style="position:relative;" class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Scan langauge/loco directory and rename all files with eb-textdomain to edwiser-bridge
	 *
	 * @since    2.1.6
	 *
	 * @throws \Exception If language files are not renamed.
	 */
	public function rename_langauge_files() {

		$lang_dir   = WP_LANG_DIR . DIRECTORY_SEPARATOR . 'plugins';
		$loco_dir   = WP_LANG_DIR . DIRECTORY_SEPARATOR . 'loco' . DIRECTORY_SEPARATOR . 'language';
		$plugin_dir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'edwiser-bridge' . DIRECTORY_SEPARATOR . 'languages';

		$files = array();
		if ( file_exists( $lang_dir ) ) {
			$land_files = scandir( $lang_dir );
			foreach ( $land_files as $file ) {
				if ( strpos( $file, 'eb-textdomain' ) !== false ) {
					$files[] = $lang_dir . DIRECTORY_SEPARATOR . $file;
				}
			}
		}
		if ( file_exists( $loco_dir ) ) {
			$loco_files = scandir( $loco_dir );
			foreach ( $loco_files as $file ) {
				if ( strpos( $file, 'eb-textdomain' ) !== false ) {
					$files[] = $loco_dir . DIRECTORY_SEPARATOR . $file;
				}
			}
		}
		if ( $plugin_dir ) {
			$plugin_files = scandir( $plugin_dir );
			foreach ( $plugin_files as $file ) {
				if ( strpos( $file, 'eb-textdomain' ) !== false ) {
					$files[] = $plugin_dir . DIRECTORY_SEPARATOR . $file;
				}
			}
		}

		$failed_files = array();
		foreach ( $files as $file ) {
			if ( strpos( $file, 'eb-textdomain' ) !== false ) {
				$new_file = str_replace( 'eb-textdomain', 'edwiser-bridge', $file );
				try {
					if ( is_writable( $file ) ) {
						rename( $file, $new_file );
					} else {
						throw new \Exception( 'File is not writable' );
					}
				} catch ( \Exception $e ) {
					$failed_files[] = $file;
				}
			}
		}

		if ( empty( $failed_files ) ) {
			update_option( 'eb_renamed_lang_files', 'true' );
		} else {
			update_option( 'eb_renamed_lang_files', $failed_files );
			update_option( 'eb_rename_file_notice_dismissed', 'false' );
		}
	}

	/**
	 * Ajax function to dismiss admin notice
	 *
	 * @since    2.1.6
	 */
	public function eb_dismiss_lang_rename_admin_notice() {
		if ( true === filter_input( INPUT_GET, 'eb-rename-lang-notice-dismissed', FILTER_VALIDATE_BOOLEAN ) ) {
			update_option( 'eb_rename_file_notice_dismissed', 'true' );
		}
	}
}
