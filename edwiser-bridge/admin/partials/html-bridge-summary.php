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
			<?php foreach ( $headings as $heading ) { ?>
			<th><h3><?php echo wp_kses_post( $heading ); ?></h3></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $data as $row ) { ?>
		<tr>
		<?php foreach ( $row as $col ) { ?>
			<td class='eb-stat-tbl-label'><?php echo wp_kses_post( $col ); ?></td>
			<?php } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
