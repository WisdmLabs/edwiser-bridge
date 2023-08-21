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

// check if user is legacy pro user if not then do not show older license keys.
$is_legacy_pro = app\wisdmlabs\edwiserBridge\eb_is_legacy_pro( true );
if ( $is_legacy_pro && ! $bridge_pro ) {

	?>
	<div class="eb_table" style="opacity: 0.5;cursor: not-allowed;" >
		<div class="eb_table_body">
			<?php
			foreach ( $this->products_data as $single ) {
				if ( 'edwiser_bridge_pro' === $single['slug'] ) {
					continue;
				}
				?>
				<div class="eb_table_row">
					<form method="post" id="mainform" >
						<div class="eb_table_cell_1">
							<?php echo esc_attr( $single['item_name'] ); ?>
						</div>

						<div class="eb_table_cell_2">
							<input style="opacity: 0.5;cursor: not-allowed;" class="wdm_key_in" type="text" name="<?php echo esc_attr( $single['key'] ); ?>" value="<?php echo esc_attr( $this->get_licence_key( $single['key'] ) ); ?>" <?php echo $bridge_pro ? esc_attr( $this->is_readonly_key( $single['slug'] ) ) : 'readonly'; ?> />
						</div>

						<div class="eb_table_cell_3">
							<?php
							if ( ! $bridge_pro ) {
								$this->get_license_status( $single['slug'] );
							} else {
								?>
								<span class="eb_lic_status"><?php esc_html_e( 'Not active', 'edwiser-bridge' ); ?></span>
								<?php
							}
							?>
						</div>
						<?php
						if ( ! $bridge_pro ) {
							?>
							<div class="eb_table_cell_4">
							<?php // $this->get_license_buttons( $single['slug'] ); // @codingStandardsIgnoreLine ?>
							</div>
							<input type="hidden" name="action" value="<?php echo esc_attr( $single['slug'] ); ?>"/>
							<?php wp_nonce_field( 'eb-licence-nonce', $single['slug'] ); ?>
							<?php
						}
						?>
					</form>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
	if ( $bridge_pro ) { // if user have pro license active then show this notice.
		?>
		<div class="eb-admin-license-page-notice">
			<p><?php _e( '<strong>Note: </strong>Starting from Edwiser Bridge version 3.0.0, you no longer need to activate or deactivate licenses for each add-on separately. All the Edwiser Bridge Pro Add-ons have been combined into a single plugin, and you just need to activate one license key to receive all the updates.', 'edwiser-bridge' ); // @codingStandardsIgnoreLine ?></p>
			<p><?php esc_html_e( ' Even though all the add-ons have been consolidated, you can still choose to enable or disable these features individually from', 'edwiser-bridge' ); ?> <a href="<?php ecs_url( admin_url( 'admin.php?page=eb-settings&tab=pro_features' ) ); ?>"><?php esc_html_e( 'here.', 'edwiser-bridge' ); ?></a></p>
		</div>
		<?php
	} else {
		?>
		<div class="eb-admin-license-page-notice">
			<p><?php _e( '<strong>Note: </strong> Enter your new license key below to activate the Edwiser Bridge Pro versioon 3.0.0.', 'edwiser-bridge' ); // @codingStandardsIgnoreLine ?></p>
			<p><?php esc_html_e( 'Upon entering the license key we will automatically install and activate the Pro plugin. While activating the Pro version 3.0.0 we will deactivate any Bridge Pro add-on plugin (version 3.0.0 and below) if found active on the site.', 'edwiser-bridge' ); ?></p>
			<p><?php esc_html_e( 'Don\'t worry all you features and functionalities will work as earlier without any reconfiguration needed.', 'edwiser-bridge' ); ?></p>
		</div>
		<?php
	}
} elseif ( ! get_option( 'edd_edwiser_bridge_pro_license_status' ) ) { // if user does not have pro license.
	?>
	<div class="eb-pro-upgrade-plugin-notice">
		<img class="eb-pro-upgrade-plugin-notice-img eb-pro-p-b-0" src="<?php echo esc_url( \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url() ); ?>/admin/assets/images/eb-pro-banner.svg" alt="">
		<div class="eb-pro-upgrade-plugin-notice-content">
			<h1><?php esc_html_e( 'It seems that you have not purchased Edwiser Bridge Pro plugin.', 'edwiser-bridge' ); ?></h1>
			<p><?php echo sprintf( esc_html__( 'To get access to all the Edwiser Bridge Pro features consider %s', 'edwiser-bridge' ), '<a href="#">' . esc_html__( 'Upgrading to Pro', 'edwiser-bridge' ) . '</a>' ); // @codingStandardsIgnoreLine ?></p>
			<p><a href="#"><?php esc_html_e( 'Refer here', 'edwiser-bridge' ); ?></a><?php esc_html_e( ' if you like to learn more about the Edwiser Bridge Pro', 'edwiser-bridge' ); ?></p>
		</div>
	</div>
	<?php
}
?>
<div class="eb_license_table">
	<div class="eb_table_row">
		<?php
		$single = $this->products_data['edwiser_bridge_pro'];
		?>
		<h1><?php esc_html_e( 'Activate your Edwiser Bridge Pro', 'edwiser-bridge' ); ?></h1>
		<p><?php esc_html_e( 'Activating the License Key is essential to receive regular plugin updates as well as support from Edwiser.', 'edwiser-bridge' ); ?></p>
		<form method="post" id="mainform" >
			<table class="eb_pro_license_table">
				<thead class="eb_pro_license_table_head">
					<tr>
						<th><?php esc_html_e( 'Edwiser Bridge Pro', 'edwiser-bridge' ); ?></th>
						<th><?php esc_html_e( 'License status', 'edwiser-bridge' ); ?></th>
						<th><?php esc_html_e( 'Action', 'edwiser-bridge' ); ?></th>
					</tr>
				</thead>
				<tbody class="eb_pro_license_table_body">
					<tr>
						<td><input class="wdm_key_in" type="text" placeholder="Type here" name="<?php echo esc_attr( $single['key'] ); ?>" value="<?php echo esc_attr( $this->get_licence_key( $single['key'] ) ); ?>" <?php echo esc_attr( $this->is_readonly_key( $single['slug'] ) ); ?> /></td>
						<td><?php $this->get_license_status( $single['slug'] ); ?></td>
						<td>
							<?php $this->get_license_buttons( $single['slug'] ); ?>
							<input type="hidden" name="action" value="<?php echo esc_attr( $single['slug'] ); ?>"/>
							<?php wp_nonce_field( 'eb-licence-nonce', $single['slug'] ); ?>
						</td>
					</tr>
					<?php
					$status_option  = get_option( 'edd_' . $single['slug'] . '_license_status' );
					$license_expiry = get_option( 'eb_' . $single['slug'] . '_license_key_expires' );
					$license_active = get_option( 'eb_' . $single['slug'] . '_license_key_site_count' );
					$license_left   = get_option( 'eb_' . $single['slug'] . '_license_key_license_limit' );

					if ( 'lifetime' !== $license_expiry ) {
						$license_expiry = date( 'd F Y', strtotime( $license_expiry ) ); // @codingStandardsIgnoreLine
					} else {
						$license_expiry = __( 'Lifetime', 'edwiser-bridge' );
					}
					if ( false !== $status_option && 'valid' === $status_option ) {
						?>
						<tr>
							<td colspan="3" class="eb_pro_license_info_wrap">
								<span class="eb_pro_license_info"><?php esc_html_e( 'License Expires on: ', 'edwiser-bridge' ); ?> <?php echo esc_html( $license_expiry ); ?></span>
								<span class="eb_pro_license_info"><?php esc_html_e( 'Active licenses: ', 'edwiser-bridge' ); ?> <?php echo esc_html( $license_active ); ?> of <?php echo esc_html( $license_left ); ?></span>
							</td>
						</tr>
						<?php
					}
					?>
			</table>
		</form>
		<div class="eb-pro-feature-link">
			<?php esc_html_e( 'Enable / Disable Edwiser Bridge Pro features Individually from', 'edwiser-bridge' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=eb-settings&tab=pro_features' ) ); ?>"> <?php esc_html_e( 'here', 'edwiser-bridge' ); ?></a>
		</div>
	</div>
	<div class="eb-license-help">
		<div class="eb-help-tootip">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="12" cy="12" r="11.5" fill="white" stroke="#C4C4C4"/>
				<path d="M10.5332 14.1085V13.5708C10.5332 13.1058 10.6325 12.7013 10.8311 12.3574C11.0297 12.0135 11.393 11.6478 11.921 11.2603C12.4296 10.897 12.7638 10.6015 12.9237 10.3738C13.0884 10.1462 13.1707 9.89188 13.1707 9.61093C13.1707 9.29608 13.0545 9.05631 12.8219 8.89162C12.5894 8.72692 12.2649 8.64458 11.8483 8.64458C11.1217 8.64458 10.2934 8.88193 9.36341 9.35663L8.57143 7.76541C9.65162 7.15992 10.7972 6.85718 12.0082 6.85718C13.006 6.85718 13.798 7.09695 14.3841 7.5765C14.9751 8.05604 15.2705 8.69544 15.2705 9.49468C15.2705 10.0275 15.1494 10.4877 14.9072 10.8752C14.6651 11.2627 14.2049 11.6987 13.5267 12.183C13.0617 12.527 12.7662 12.7885 12.6403 12.9678C12.5192 13.147 12.4587 13.3819 12.4587 13.6725V14.1085H10.5332ZM10.3007 16.5934C10.3007 16.1865 10.4097 15.8789 10.6277 15.6707C10.8456 15.4624 11.1629 15.3582 11.5795 15.3582C11.9815 15.3582 12.2915 15.4648 12.5095 15.6779C12.7323 15.891 12.8437 16.1962 12.8437 16.5934C12.8437 16.9761 12.7323 17.2788 12.5095 17.5016C12.2867 17.7196 11.9767 17.8286 11.5795 17.8286C11.1726 17.8286 10.8577 17.722 10.6349 17.5089C10.4121 17.2909 10.3007 16.9858 10.3007 16.5934Z" fill="#F98012"/>
			</svg>
			<span class="eb-help-tootip-content"><?php esc_html_e( 'Looking for help?', 'edwiser-bridge' ); ?></span>
		</div>
		<ul>
			<li><a href="https://edwiser.org/documentation/edwiser-bridge-pro/edwiser-bridge-pro/how-to-obtain-the-license-key/"><?php esc_html_e( 'Where to find my license key?', 'edwiser-bridge' ); ?></a></li>
			<li><a href="https://edwiser.org/documentation/edwiser-bridge-pro/edwiser-bridge-pro/installation-and-user-guide-for-v3-0-0/"><?php esc_html_e( 'Installation and User Guide', 'edwiser-bridge' ); ?></a></li>
			<li><?php esc_html_e( 'Talk to us:', 'edwiser-bridge' ); ?> <a href="mailto:edwiser@wisdmlabs.com">edwiser@wisdmlabs.com</a></li>
		</ul>
	</div>
</div>
<?php
do_action( 'eb_check_mdl_plugin_update' );

$plugin_update_data = get_option( 'eb_mdl_plugin_update_check' );
if ( is_array( $plugin_update_data ) && ! empty( $plugin_update_data ) ) {
	foreach ( $plugin_update_data as $plugin_data ) {
		if ( 'moodle_edwiser_bridge' === $plugin_data['slug'] ) {
			$my_account_url = 'https://edwiser.org/my-account/';
			?>
			<div class="eb-pro-upgrade-plugin-notice">
				<svg class="eb-pro-upgrade-plugin-notice-img" width="87" height="60" viewBox="0 0 87 60" fill="none" xmlns="http://www.w3.org/2000/svg">
					<g clip-path="url(#clip0_1912_8988)">
						<path d="M4.78405 58.7893C2.79583 58.7893 1.19629 57.1701 1.19629 55.1575C1.19629 54.5219 1.36073 53.8864 1.67466 53.3416L30.3767 3.02631C31.3633 1.28608 33.5609 0.695919 35.28 1.69466C35.8331 2.01244 36.2816 2.46641 36.5955 3.02631L65.2826 53.3265C66.2692 55.0667 65.6862 57.2911 63.9671 58.2899C63.414 58.6077 62.8011 58.7741 62.1732 58.7741L4.78405 58.7893Z" fill="#FFD21E"/>
						<path d="M33.4859 2.42112C34.338 2.40599 35.1303 2.87509 35.5638 3.63171L64.2509 53.947C64.9087 55.1122 64.52 56.5951 63.3689 57.261C63.0102 57.4728 62.5916 57.5787 62.173 57.5787H4.78386C3.46835 57.5787 2.39202 56.4892 2.39202 55.1576C2.39202 54.7339 2.49667 54.3101 2.70595 53.947L31.408 3.63171C31.8266 2.87509 32.6189 2.40599 33.4859 2.42112ZM33.4859 -6.37957e-05C31.7668 -0.0151962 30.1822 0.923013 29.3451 2.42112L0.642991 52.7364C-0.67252 55.0516 0.104827 58.0176 2.39202 59.3492C3.12452 59.7729 3.94672 59.9999 4.78386 59.9999H62.173C64.819 59.9999 66.9567 57.836 66.9567 55.1576C66.9567 54.3101 66.7325 53.4779 66.3139 52.7364L37.6268 2.42112C36.7747 0.923013 35.1901 -0.0151962 33.4859 -6.37957e-05Z" fill="#373737"/>
						<path d="M33.4861 50.5724C34.807 50.5724 35.8779 49.4883 35.8779 48.1512C35.8779 46.814 34.807 45.73 33.4861 45.73C32.1651 45.73 31.0942 46.814 31.0942 48.1512C31.0942 49.4883 32.1651 50.5724 33.4861 50.5724Z" fill="#373737"/>
						<path d="M33.4861 16.6758C34.8016 16.6758 35.8779 17.7653 35.8779 19.097V38.4664C35.8779 39.7981 34.8016 40.8876 33.4861 40.8876C32.1706 40.8876 31.0942 39.7981 31.0942 38.4664V19.097C31.0942 17.7502 32.1556 16.6758 33.4861 16.6758Z" fill="#373737"/>
					</g>
					<defs>
						<clipPath id="clip0_1912_8988">
							<rect width="66.9565" height="60" fill="white"/>
						</clipPath>
					</defs>
				</svg>
				<div class="eb-pro-upgrade-plugin-notice-content">
					<p class="eb-pro-update-notice-h1"><?php echo sprintf( esc_html__( 'The Edwiser Bridge Moodle Plugin is not update to the latest version %s', 'edwiser-bridge' ), esc_html( $plugin_data['new_version'] ) ); // @codingStandardsIgnoreLine?></p>
					<p style="margin-top:0px;"><?php echo sprintf( esc_html__( 'To download the latest version %s or go to Edwiser %s', 'edwiser-bridge' ), '<a href="' . esc_url( $plugin_data['url'] ) . '">' . esc_html__( 'Click here', 'edwiser-bridge' ) . '</a>', '<a href="' . esc_url( $my_account_url ) . '">' . esc_html__( 'My account', 'edwiser-bridge' ) . '</a>' ); // @codingStandardsIgnoreLine?></p>
					<a href="#"><?php esc_html_e( 'How to update the Edwiser Bridge Moodle Pluign? ', 'edwiser-bridge' ); ?></a>
				</div>
			</div>
			<?php
		}
	}
}
// Dialog content.
$this->eb_license_pop_up_data();
