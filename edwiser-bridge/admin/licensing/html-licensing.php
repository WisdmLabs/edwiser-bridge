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
			?>
			<div class="eb_table_row">
				<form method="post" id="mainform" >
					<div class="eb_table_cell_1">
						<?php echo esc_attr( $single['item_name'] ); ?>
					</div>

					<div class="eb_table_cell_2">
						<input class="wdm_key_in" type="text" name="<?php echo esc_attr( $single['key'] ); ?>" value="<?php echo esc_attr( $this->get_licence_key( $single['key'] ) ); ?>" <?php echo esc_attr( $this->is_readonly_key( $single['slug'] ) ); ?> />
					</div>

					<div class="eb_table_cell_3">
						<?php $this->get_license_status( $single['slug'] ); ?>
					</div>

					<div class="eb_table_cell_4">
					<?php $this->get_license_buttons( $single['slug'] ); ?>
					</div>
					<input type="hidden" name="action" value="<?php echo esc_attr( $single['slug'] ); ?>"/>
					<?php wp_nonce_field( 'eb-licence-nonce', $single['slug'] ); ?>
				</form>
			</div>
			<?php
		}
		?>
	</div>
</div>
<?php
// Dialog content.
$this->eb_license_pop_up_data();
