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

?>
<div class="eb_table">
	<div class="eb_table_body">
		<?php
		foreach ( $this->products_data as $single ) {
			$lice_status = $this->get_license_status( $single['key'] );
			?>
			<form method="post" id="mainform" >
				<div class="eb_table_row">
					<div class="eb_table_cell_1">
						<?php echo esc_attr( $single['item_name'] ); ?>
					</div>

					<div class="eb_table_cell_2">
						<input class="wdm_key_in" type="text" name="<?php echo esc_attr( $single['key'] ); ?>" value=<?php echo $this->get_licence_key( $single['key'] ); ?>>
					</div>

					<div class="eb_table_cell_3">
						<span class="eb_lic_status eb_lic_<?php echo esc_attr( $lice_status ); ?>"><?php echo esc_attr( $lice_status ); ?><span>
					</div>

					<div class="eb_table_cell_4">
					<?php $this->get_license_buttons( $single['key'], $single['path'] ); ?>
					</div>
					<input type="hidden" name="action" value="<?php echo esc_attr( $single['slug'] ); ?>">
				</div>
				<?php wp_nonce_field( 'eb-licence-nonce', $single['slug'] ); ?>
			</form>
			<?php
		}
		?>
	</div>
</div>
