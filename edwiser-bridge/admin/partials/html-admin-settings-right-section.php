<?php
/**
 * Partial: Page - right section.
 *
 * @package    Edwiser Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div>
	<div class="eb_settings_pop_btn_wrap">	
	<?php
	if ( $show_banner ) {
		?>
			<div class='eb-set-as'>
			<h3>Edwiser Bridge PRO</h3>
			<div class="eb-set-as-desc">
				<p>Automate your course selling experience with Edwiser Bridge PRO.</p>
				<ul>
					<li>4 Noteworthy Course Selling Extensions.</li>
					<li>Power of WooCommerce</li>
					<li>165+ Payment Gateways Unlocked</li>
					<li>10x eLearning Profits</li>
				</ul>
				<a href="https://bit.ly/2NAJ7OW" target="_blank">Check out Edwiser Bridge PRO</a>
				<p>Rated <span class="dashicons dashicons-star-filled"></span>4.5</br>Trusted By <i>5000+</i> happy customers.</p>
			</div>
		</div>
		<?php } ?>
		<div class="eb_settings_rate_btn_wrap">
			<a class="eb_open_btn" target="_blank" href="https://wordpress.org/support/plugin/edwiser-bridge/reviews/">
				<?php echo esc_html__( 'Rate Us', 'edwiser-bridge' ); ?>
			</a>
		</div>
	</div>
</div>
