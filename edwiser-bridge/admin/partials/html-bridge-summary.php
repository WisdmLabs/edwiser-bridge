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
<table class="wdm-table-status">
	<thead>
		<tr>
			<th  colspan='5'><h3><?php echo esc_attr( $title ); ?></h3></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $data as $row ) { ?>
		<tr>
			<td class='eb-stat-tbl-label'><?php echo esc_attr( $row['label'] ); ?></td>
			<td class='eb-stat-tbl-help'><?php echo wp_kses_post( $row['help'] ); ?></td>
			<td class='eb-stat-tbl-label'><?php echo wp_kses_post( $row['value'] ); ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
