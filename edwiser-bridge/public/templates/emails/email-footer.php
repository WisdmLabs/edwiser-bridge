<?php
/**
 * Email Footer.
 *
 * @package Edwiser Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/*
 * For gmail compatibility, including CSS styles in head/body are stripped out therefore styles need to be inline.
 * These variables contain rules which are added to the template inline.
 */

$template_footer = '
    border-top:0;
    -webkit-border-radius:6px;
';

$credit = '
    border:0;
    font-family: Arial;
    font-size:12px;
    line-height:125%;
    text-align:center;
';
?>
</div>
</td>
</tr>
</table>
<!-- End Content -->
</td>
</tr>
</table>
<!-- End Body -->
</td>
</tr>
<tr>
	<td align="center" valign="top">
		<!-- Footer -->
		<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer" style="<?php echo esc_html( $template_footer ); ?>">
			<tr>
				<td valign="top">
					<table border="0" cellpadding="10" cellspacing="0" width="100%">
						<tr>
							<td colspan="2" valign="middle" id="credit" style="<?php echo esc_html( $credit ); ?>">
								<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<!-- End Footer -->
	</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</body>
</html>
