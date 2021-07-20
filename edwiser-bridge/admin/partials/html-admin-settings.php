<?php
/**
 * Admin View: Settings.
 *
 *  @package    Edwiser Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wrap edw">
	<?php
	do_action( 'eb_settings_header' );
	if ( 'licensing' !== $tabname ) {
		?>
		<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<?php
	}
	?>
		<div class="icon32 icon32-eb-settings" id="icon-edw"><br /></div>
		<h2 class="nav-tab-wrapper eb-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=eb-settings&tab=' . $name ) ) .
				'" class="nav-tab ' . ( $current_tab === $name ? 'nav-tab-active' : '' ) . '">'
				. esc_html( $label ) .
				'</a>';
			}
			do_action( 'eb_settings_tabs' );
			?>
		</h2>
		<div class="eb-form-content-wrap" style='display: flex;'>
			<div class="form-content">

				<?php
				do_action( 'eb_sections_' . $current_tab );
				do_action( 'eb_settings_' . $current_tab );
				?>
				<p class="submit">
					<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
						<input name="save" class="button-primary" type="submit"
							value="<?php esc_html_e( 'Save changes', 'eb-textdomain' ); ?>" />
					<?php endif; ?>
					<input type="hidden" name="subtab" id="last_tab" />
					<?php wp_nonce_field( 'eb-settings' ); ?>
				</p>
			</div>


			<?php
			$show_banner = true;
			$extensions  = array(
				'woocommerce-integration/bridge-woocommerce.php',
				'selective-synchronization/selective-synchronization.php',
				'edwiser-bridge-sso/sso.php',
				'edwiser-multiple-users-course-purchase/edwiser-multiple-users-course-purchase.php',
			);
			foreach ( $extensions as $plugin_path ) {
				if ( is_plugin_active( $plugin_path ) ) {
					$show_banner = false;
				} else {
					$show_banner = true;
					break;
				}
			}
			if ( in_array( $tabname, array( 'remui', 'pfetures' ), true ) ) {
				$show_banner = false;
			}
			$css_class = $show_banner ? 'eb_setting_right_sidebar_info' : '';
			?>
			<div class="eb_setting_right_sidebar_info">
				<?php
				require_once plugin_dir_path( __DIR__ ) . '../admin/partials/html-admin-settings-right-section.php';
				?>
			</div>
		</div>
		<?php
		if ( 'licensing' !== $tabname ) {
			?>
		</form>
			<hr/>
			<?php
			do_action( 'eb_settings_footer' );
		}
		?>
</div>
<?php
