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

<div class="wrap edwiser eb_extensions_wrap">
	<h2>
		<?php esc_html_e( 'Our Extensions', 'eb-textdomain' ); ?>
		<a href="https://edwiser.org/bridge/extensions/" target="_blank" class="add-new-h2">
			<?php esc_html_e( 'Browse all extensions', 'eb-textdomain' ); ?>
		</a>
	</h2>
	<br />
	<?php
	if ( $extensions ) {
		?>
		<ul class="extensions">
			<?php
			$extensions = $extensions->popular;
			$i          = 0;
			foreach ( $extensions as $extension ) {
				if ( $i > 7 ) {
					break;
				}

				echo '<li class="product" title="' . esc_html__( 'Click here to know more', 'eb-textdomain' ) . '">';
				echo '<a href="' . esc_html( $extension->link ) . '" target="_blank">';
				if ( ! empty( $extension->image ) ) {
					echo '<img src="' . esc_html( $extension->image ) . '"/>';
				} else {
					echo '<h3>' . esc_html( $extension->title ) . '</h3>';
				}
					echo '<p>' . esc_html( $extension->excerpt ) . '</p>';
					echo '</a>';
					echo '</li>';
					++$i;
			}
			?>
		</ul>
		<br />
		<a href="https://edwiser.org/bridge/extensions/" target="_blank" class="browse-all">
			<?php esc_html_e( 'Browse all our extensions', 'eb-textdomain' ); ?>
		</a>
		<?php
	} else {
		?>
		<p>
			<?php

			/*
			 * Translators: Edwiser bridge extensions page link.
			 */
			printf( esc_html__( 'Our list of extensions for Edwiser Bridge can be found here: <a href="%s" target="_blank">Edwiser Bridge Extensions</a>', 'eb-textdomain' ), 'https://edwiser.org/bridge/extensions/' );
			?>
		</p>
		<?php
	}
	?>
</div>
