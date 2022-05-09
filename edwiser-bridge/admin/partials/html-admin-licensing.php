<?php
/**
 * Partial: Page - Extensions.
 *
 * @package    Edwiser Bridge
 * @var object
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get Setting Messages.
 */
$setting_messages = '';
$setting_messages = apply_filters( 'eb_setting_messages', $setting_messages );
if ( ! empty( $setting_messages ) ) {
	echo wp_kses_post( $setting_messages );
}

$allowed_tags = \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags();

/**
 * Get Licensing Information.
*/

/*
 *
 * @var array License Information
 */
$licensing_info = array();
$licensing_info = apply_filters( 'eb_licensing_information', $licensing_info );
if ( ! empty( $licensing_info ) ) {
	?>
	<div class="eb_table">
		<div class="eb_table_body">
			<?php
			foreach ( $licensing_info as $single ) {
				?>
				<form name="<?php echo esc_attr( $single['plugin_slug'] ) . '_licensing_form'; ?>" method="post" id="mainform" >
					<div class="eb_table_row">

						<div class="eb_table_cell_1">
							<?php echo wp_kses_post( $single['plugin_name'] ); ?>
						</div>

						<div class="eb_table_cell_2">
							<?php echo wp_kses( $single['license_key'], $allowed_tags ); ?>
						</div>

						<div class="eb_table_cell_3">
							<?php echo wp_kses( $single['license_status'], $allowed_tags ); ?>
						</div>

						<div class="eb_table_cell_4">
							<?php echo wp_kses( $single['activate_license'], $allowed_tags ); ?>
						</div>

					</div>
					<?php wp_nonce_field( 'eb-settings' ); ?>
				</form>
				<?php
			}
			?>
		</div>
	</div>
	<?php
} else {
	/*
	 * translators: string plugin name.
	 */
	printf( esc_html( __( '%1$1s You do not have any extensions activated. %2$2s Please activate any installed extensions. If you do not have any extensions, you can take a look at the list %3$3s here%4$4s.%5$5s', 'edwiser-bridge' ) ), '<div class="update-nag"><strong>', '</strong>', '<a href="https://edwiser.org/bridge/extensions/" target="_blank">', '</a>', '</div>' );
}
