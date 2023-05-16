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
if ( $is_legacy_pro ) {

	?>
	<div class="eb_table" <?php echo $bridge_pro ? 'style="opacity: 0.5;cursor: not-allowed;"' : ''; ?>>
		<div class="eb_table_body">
			<?php
			foreach ( $this->products_data as $single ) {
				if( $single['slug'] == 'edwiser_bridge_pro' ) {
					continue;
				}
				?>
				<div class="eb_table_row">
					<form method="post" id="mainform" >
						<div class="eb_table_cell_1">
							<?php echo esc_attr( $single['item_name'] ); ?>
						</div>

						<div class="eb_table_cell_2">
							<input class="wdm_key_in" type="text" name="<?php echo esc_attr( $single['key'] ); ?>" value="<?php echo esc_attr( $this->get_licence_key( $single['key'] ) ); ?>" <?php echo $bridge_pro ? esc_attr( $this->is_readonly_key( $single['slug'] ) ) : 'readonly'; ?> />
						</div>

						<div class="eb_table_cell_3">
							<?php
							if( ! $bridge_pro ) {
								$this->get_license_status( $single['slug'] );
							} else{
								?>
								<span class="eb_lic_status">Not active</span>
								<?php
							}
							?>
						</div>
						<?php
						if( ! $bridge_pro ) {
							?>
							<div class="eb_table_cell_4">
							<?php $this->get_license_buttons( $single['slug'] ); ?>
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
	if( $bridge_pro ) {
		?>
		<div class="eb-admin-license-page-notice">
			<p><?php _e( '<strong>Note: </strong>Starting from Edwiser Bridge version 3.0.0, you no longer need to activate or deactivate licenses for each add-on separately. All the Edwiser Bridge Pro Add-ons have been combined into a single plugin, and you just need to activate one license key to receive all the updates.', 'edwiser-bridge' ); ?></p>
			<p><?php _e( ' Even though all the add-ons have been consolidated, you can still choose to enable or disable these features individually from', 'edwiser-bridge' ); ?> <a href="<?php echo admin_url( 'admin.php?page=eb-settings&tab=pro_features' ); ?>"><?php _e( 'here.', 'edwiser-bridge' ); ?></a></p>
		</div>
		<?php
	} else {
		?>
		<div class="eb-admin-license-page-notice">
			<p><?php _e( '<strong>Note: </strong> Enter your new license key below to activate the Edwiser Bridge Pro versioon xxx.', 'edwiser-bridge' ); ?></p>
			<p><?php _e( 'Upon entering the license key we will automatically install and activate the Pro plugin. While activating the Pro version xxx we will deactivate any Bridge Pro add-on plugin (version xxx and below) if found active on the site.', 'edwiser-bridge' ); ?></p>
			<p><?php _e( 'Don\'t worry all you features and functionalities will work as earlier without any reconfiguration needed.', 'edwiser-bridge' ); ?></p>
		</div>
		<?php
	}
} else {
	?>
	<div class="eb-pro-upgrade-plugin-notice">
		<img class="eb-pro-upgrade-plugin-notice-img eb-pro-p-b-0" src="<?php echo \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url(); ?>/admin/assets/images/eb-pro-banner.svg" alt="">
		<div class="eb-pro-upgrade-plugin-notice-content">
			<h1><?php _e( 'It seems that you have not purchased Edwiser Bridge Add-on plugin(s).', 'edwiser-bridge-pro' ); ?></h1>
			<p><?php echo sprintf ( __( 'To get access to all the Edwiser Bridge Pro features consider %s', 'edwiser-bridge-pro' ), '<a href="#">' . __( 'Upgrading to Pro', 'edwiser-bridge-pro' ) . '</a>' ); ?></p>
			<p><a href="#"><?php _e( 'Refer here', 'edwiser-bridge-pro' ); ?></a><?php _e( ' if you like to learn more about the Edwiser Bridge Pro', 'edwiser-bridge-pro' ) ?></p>
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
		<h1><?php _e( 'Activate your Edwiser Bridge Pro', 'edwiser-bridge' ); ?></h1>
		<p><?php _e( 'Activating the License Key is essential to receive regular plugin updates as well as support from Edwiser.', 'edwiser-bridge' ); ?></p>
		<form method="post" id="mainform" >
			<table class="eb_pro_license_table">
				<thead class="eb_pro_license_table_head">
					<tr>
						<th><?php _e( 'Edwiser Bridge Pro', 'edwiser-bridge' ); ?></th>
						<th><?php _e( 'License status', 'edwiser-bridge' ); ?></th>
						<th><?php _e( 'Action', 'edwiser-bridge' ); ?></th>
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
					$status_option = get_option( 'edd_' . $single['slug'] . '_license_status' );
					$license_expiry = get_option( 'eb_' . $single['slug'] . '_license_key_expires' );
					$license_active = get_option( 'eb_' . $single['slug'] . '_license_key_site_count' );
					$license_left = get_option( 'eb_' . $single['slug'] . '_license_key_license_limit' );

					$license_expiry = date( 'd F Y', strtotime( $license_expiry ) );
					if ( false !== $status_option && 'valid' === $status_option ) {
						?>
						<tr>
							<td colspan="3" class="eb_pro_license_info_wrap">
								<span class="eb_pro_license_info"><?php _e( 'Licence Expires on: ') ?> <?php echo $license_expiry; ?></span>
								<span class="eb_pro_license_info"><?php _e( 'Active licenses: ') ?> <?php echo $license_active; ?> of <?php echo $license_left; ?></span>
							</td>
						</tr>
						<?php
					}
					?>
			</table>
		</form>
	</div>
</div>
<?php
	do_action( 'eb_check_mdl_plugin_update' );

	$plugin_update_data = get_option( 'eb_mdl_plugin_update_check' );
	if ( is_array( $plugin_update_data ) && ! empty( $plugin_update_data ) ) {
		foreach ( $plugin_update_data as $plugin ) {
			if ( 'moodle_edwiser_bridge' === $plugin['slug'] ) {
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
						<p class="eb-pro-update-notice-h1"><?php echo sprintf ( __( 'The Edwiser Bridge Moodle Plugin is not update to the latest version %s', 'edwiser-bridge' ), $plugin['new_version'] ); ?></p>
						<p style="margin-top:0px;"><?php echo sprintf ( __( 'To download the latest version %s or go to Edwiser %s', 'edwiser-bridge' ), '<a href="' . $plugin['url'] . '">' . __( 'Click here', 'edwiser-bridge' ) . '</a>', '<a href="' . $my_account_url . '">' . __( 'My account', 'edwiser-bridge' ) . '</a>' ); ?></p>
						<a href="#"><?php _e( 'How to update the Edwiser Bridge Moodle Pluign? ', 'edwiser-bridge' ); ?></a>
					</div>
				</div>
				<?php
			}
		}
	}
	?>
<?php
// Dialog content.
$this->eb_license_pop_up_data();
