<?php
/**
 * Partial: Page - Pro Plugins.
 *
 * @package    Edwiser Bridge Pro
 * @var object
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'eb_before_pro_plugins_page', $this );
if ( count( $this->plugin_licensing_data ) > 0 ) {
	?>
	<div class="eb_table" >
		<h1 class="eb-pro-feature-heading"><?php esc_attr_e( 'Edwiser Bridge Pro features', 'edwiser-bridge' ); ?></h1>
		<p class="eb-pro-feature-sub-heading"><?php esc_attr_e( 'Activate/Deactivate Pro plugins ', 'edwiser-bridge' ); ?></p>
		<form action=""></form>
		<div class="eb-pro-plugins">
			<?php
			foreach ( $this->plugin_data as $key => $plugin_single ) {
				if ( 'edwiser_bridge_pro' === $plugin_single['slug'] ) {
					continue;
				}
				if ( ! in_array( $plugin_single['item_name'], $this->plugin_licensing_data ) ) { // @codingStandardsIgnoreLine
					continue;
				}
				// create card for each plugin with activation/deactivation button.
				?>
				<div class="eb-pro-plugin-card <?php echo $this->bridge_pro ? '' : 'eb-pro-plugin-not-active'; ?>">
					<?php
					if ( ! $this->bridge_pro ) {
						?>
						<div class="eb-pro-license-not-active-overlay">
							<div class="eb-pro-license-not-active-content">
								<?php echo sprintf( esc_html__( 'To access the Edwiser Bridge PRO features, please activate the license key from the %s tab', 'edwiser-bridge' ), '<a class="eb-pro-license-not-active-link" href="' . esc_url( admin_url( 'admin.php?page=eb-settings&tab=licensing' ) ) . '">“License”</a>' ); // @codingStandardsIgnoreLine ?>
							</div>
						</div>
						<?php
					}
					?>
					<div class="eb-pro-plugin-card-header">
						<h2><?php echo esc_attr( $plugin_single['item_name'] ); ?></h2>
					</div>
					<div class="eb-pro-plugin-card-body">
						<?php echo esc_attr( $plugin_single['description'] ); ?>
					</div>
					<div class="eb-pro-plugin-card-footer">
						<form method="post">
							<label class="eb-pro-switch">
								<input type="hidden" name="action" value="<?php echo esc_attr( $key ); ?>"/>
								<?php wp_nonce_field( 'eb-licence-nonce', $key ); ?>
								<input class="eb-pro-activate-plugin" name="activate_plugin" value="1" type="checkbox" <?php echo $this->is_plugin_active( $key ) ? 'checked' : ''; ?>>
								<span class="eb-pro-checkbox-slider round"></span>
							</label>
							<span class="eb-pro-license-status <?php echo $this->is_plugin_active( $key ) ? 'eb-pro-license-active' : 'eb-pro-license-not-active'; ?>"><?php echo $this->is_plugin_active( $key ) ? esc_html__( 'Active', 'edwiser-bridge' ) : esc_html__( 'Not active', 'edwiser-bridge' ); ?></span>
						</form>
						<?php
						if ( $this->is_plugin_active( $key ) ) {
							?>
							<a class="eb-pro-plugin-setting"  href="<?php echo esc_attr( $plugin_single['setting_url'] ); ?>">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<g clip-path="url(#clip0_1975_628)">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M7.75486 1.67861C8.41361 -0.558887 11.5861 -0.558887 12.2449 1.67861L12.3624 2.07736C12.4084 2.23355 12.4887 2.37747 12.5976 2.49856C12.7064 2.61965 12.841 2.71485 12.9914 2.77718C13.1419 2.83951 13.3043 2.86738 13.4669 2.85876C13.6295 2.85014 13.7881 2.80524 13.9311 2.72736L14.2961 2.52861C16.3449 1.41236 18.5886 3.65361 17.4724 5.70361L17.2724 6.06861C17.1945 6.2116 17.1496 6.37021 17.141 6.53281C17.1323 6.6954 17.1602 6.85787 17.2225 7.00829C17.2849 7.15871 17.3801 7.29328 17.5012 7.40213C17.6223 7.51099 17.7662 7.59136 17.9224 7.63736L18.3211 7.75486C20.5586 8.41361 20.5586 11.5861 18.3211 12.2449L17.9224 12.3624C17.7662 12.4084 17.6223 12.4887 17.5012 12.5976C17.3801 12.7064 17.2849 12.841 17.2225 12.9914C17.1602 13.1419 17.1323 13.3043 17.141 13.4669C17.1496 13.6295 17.1945 13.7881 17.2724 13.9311L17.4711 14.2961C18.5886 16.3449 16.3461 18.5886 14.2961 17.4724L13.9311 17.2724C13.7881 17.1945 13.6295 17.1496 13.4669 17.141C13.3043 17.1323 13.1419 17.1602 12.9914 17.2225C12.841 17.2849 12.7064 17.3801 12.5976 17.5012C12.4887 17.6223 12.4084 17.7662 12.3624 17.9224L12.2449 18.3211C11.5861 20.5586 8.41361 20.5586 7.75486 18.3211L7.63736 17.9224C7.59136 17.7662 7.51099 17.6223 7.40213 17.5012C7.29328 17.3801 7.15871 17.2849 7.00829 17.2225C6.85787 17.1602 6.6954 17.1323 6.53281 17.141C6.37021 17.1496 6.2116 17.1945 6.06861 17.2724L5.70361 17.4711C3.65361 18.5886 1.41236 16.3461 2.52736 14.2961L2.72736 13.9311C2.80524 13.7881 2.85014 13.6295 2.85876 13.4669C2.86738 13.3043 2.83951 13.1419 2.77718 12.9914C2.71485 12.841 2.61965 12.7064 2.49856 12.5976C2.37747 12.4887 2.23355 12.4084 2.07736 12.3624L1.67861 12.2449C-0.558887 11.5861 -0.558887 8.41361 1.67861 7.75486L2.07736 7.63736C2.23355 7.59136 2.37747 7.51099 2.49856 7.40213C2.61965 7.29328 2.71485 7.15871 2.77718 7.00829C2.83951 6.85787 2.86738 6.6954 2.85876 6.53281C2.85014 6.37021 2.80524 6.2116 2.72736 6.06861L2.52861 5.70361C1.41236 3.65486 3.65361 1.41236 5.70361 2.52736L6.06861 2.72736C6.2116 2.80524 6.37021 2.85014 6.53281 2.85876C6.6954 2.86738 6.85787 2.83951 7.00829 2.77718C7.15871 2.71485 7.29328 2.61965 7.40213 2.49856C7.51099 2.37747 7.59136 2.23355 7.63736 2.07736L7.75486 1.67861ZM11.0461 2.03236C10.7386 0.988613 9.26111 0.988613 8.95361 2.03236L8.83611 2.43111C8.73729 2.76605 8.5648 3.07463 8.33128 3.33426C8.09775 3.5939 7.8091 3.79801 7.48647 3.93163C7.16385 4.06526 6.81542 4.12503 6.4667 4.10655C6.11798 4.08808 5.77781 3.99183 5.47111 3.82486L5.10611 3.62611C4.14986 3.10611 3.10611 4.15111 3.62486 5.10611L3.82486 5.47111C3.9916 5.77777 4.08766 6.11782 4.10601 6.46639C4.12436 6.81496 4.06454 7.16322 3.93093 7.48568C3.79732 7.80815 3.59329 8.09666 3.33379 8.3301C3.07429 8.56354 2.76587 8.736 2.43111 8.83486L2.03236 8.95236C0.988613 9.25986 0.988613 10.7374 2.03236 11.0449L2.43111 11.1624C2.76639 11.2611 3.07532 11.4336 3.33521 11.6673C3.59511 11.901 3.79938 12.19 3.93304 12.5129C4.06671 12.8359 4.12637 13.1846 4.10765 13.5337C4.08893 13.8827 3.9923 14.2231 3.82486 14.5299L3.62611 14.8936C3.10611 15.8499 4.15111 16.8936 5.10611 16.3749L5.47111 16.1749C5.77781 16.0079 6.11798 15.9116 6.4667 15.8932C6.81542 15.8747 7.16385 15.9345 7.48647 16.0681C7.8091 16.2017 8.09775 16.4058 8.33128 16.6655C8.5648 16.9251 8.73729 17.2337 8.83611 17.5686L8.95361 17.9674C9.26111 19.0111 10.7386 19.0111 11.0461 17.9674L11.1636 17.5699C11.2622 17.2346 11.4347 16.9258 11.6682 16.6659C11.9018 16.406 12.1905 16.2016 12.5133 16.0678C12.8361 15.9341 13.1848 15.8743 13.5337 15.8928C13.8827 15.9113 14.223 16.0077 14.5299 16.1749L14.8936 16.3736C15.8499 16.8936 16.8936 15.8486 16.3749 14.8936L16.1749 14.5299C16.0077 14.2231 15.9112 13.8828 15.8926 13.534C15.874 13.1851 15.9337 12.8365 16.0674 12.5137C16.201 12.1909 16.4052 11.9021 16.665 11.6685C16.9248 11.4349 17.2335 11.2624 17.5686 11.1636L17.9674 11.0461C19.0111 10.7386 19.0111 9.26111 17.9674 8.95361L17.5699 8.83611C17.2348 8.73744 16.9261 8.56505 16.6663 8.33158C16.4066 8.09812 16.2023 7.80949 16.0686 7.48684C15.9348 7.1642 15.8749 6.81572 15.8933 6.46693C15.9117 6.11815 16.0079 5.7779 16.1749 5.47111L16.3736 5.10611C16.8936 4.14986 15.8486 3.10611 14.8936 3.62486L14.5299 3.82486C14.2231 3.99207 13.8828 4.0885 13.534 4.1071C13.1851 4.1257 12.8365 4.06598 12.5137 3.93234C12.1909 3.79869 11.9021 3.5945 11.6685 3.33474C11.4349 3.07497 11.2624 2.76622 11.1636 2.43111L11.0461 2.03236ZM8.44725 6.25124C8.93953 6.04733 9.46715 5.94238 9.99999 5.94238C11.0761 5.94238 12.1081 6.36987 12.8691 7.1308C13.63 7.89173 14.0575 8.92377 14.0575 9.99988C14.0575 11.076 13.63 12.108 12.8691 12.869C12.1081 13.6299 11.0761 14.0574 9.99999 14.0574C9.46715 14.0574 8.93953 13.9524 8.44725 13.7485C7.95498 13.5446 7.50768 13.2457 7.13091 12.869C6.75413 12.4922 6.45526 12.0449 6.25135 11.5526C6.04744 11.0603 5.94249 10.5327 5.94249 9.99988C5.94249 9.46704 6.04744 8.93942 6.25135 8.44714C6.45526 7.95487 6.75413 7.50757 7.13091 7.1308C7.50768 6.75402 7.95498 6.45515 8.44725 6.25124ZM8.01479 8.01468C7.48828 8.54119 7.19249 9.25529 7.19249 9.99988C7.19249 10.7445 7.48828 11.4586 8.01479 11.9851C8.5413 12.5116 9.2554 12.8074 9.99999 12.8074C10.7446 12.8074 11.4587 12.5116 11.9852 11.9851C12.5117 11.4586 12.8075 10.7445 12.8075 9.99988C12.8075 9.25529 12.5117 8.54119 11.9852 8.01468C11.4587 7.48817 10.7446 7.19238 9.99999 7.19238C9.2554 7.19238 8.5413 7.48817 8.01479 8.01468Z" fill="#444444"/>
									</g>
									<defs>
										<clipPath id="clip0_1975_628">
											<rect width="20" height="20" fill="white"/>
										</clipPath>
									</defs>
								</svg>
							</a>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}
if ( count( $this->plugin_licensing_data ) < 5 ) {
	?>
	<div class="eb_table" >
		<h1><?php esc_attr_e( 'Upgrade to Edwiser Bridge Pro', 'edwiser-bridge' ); ?></h1>
		<?php
		if ( ! is_plugin_active( 'edwiser-bridge-pro/edwiser-bridge-pro.php' ) ) {
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
		} else {
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
					<p><?php echo sprintf( esc_html__( 'Based on your %s it seems that you have purchased only the above mentioned Edwiser Bridge Add-on plugin(s). To get access to all the Edwiser Bridge Pro feature consider %s', 'edwiser-bridge' ), '<a href="#">' . esc_html__( 'license key', 'edwiser-bridge' ) . '</a>', '<a href="#">' . esc_html__( 'Upgrading to Pro', 'edwiser-bridge' ) . '</a>' ); // @codingStandardsIgnoreLine ?></p>
					<p><a href="#"><?php esc_html_e( 'Refer here', 'edwiser-bridge' ); ?></a><?php esc_html_e( ' if you like to learn more about the Edwiser Bridge Pro', 'edwiser-bridge' ); ?></p>
				</div>
			</div>
			<?php
		}
		?>
		<div class="eb-pro-plugins">  
			<?php
			// non licensed plugins.
			foreach ( $this->plugin_data as $key => $plugin_single ) {
				if ( 'edwiser_bridge_pro' === $plugin_single['slug'] ) {
					continue;
				}
				if( in_array( $plugin_single['item_name'], $this->plugin_licensing_data ) ) { // @codingStandardsIgnoreLine
					continue;
				}
				// create card for each plugin with activation/deactivation button.
				?>
				<div class="eb-pro-upgrade-plugin-card">
					<div class="eb-pro-plugin-card-header">
						<h2><?php echo esc_attr( $plugin_single['item_name'] ); ?></h2>
					</div>
					<div class="eb-pro-plugin-card-body">
						<?php echo esc_attr( $plugin_single['description'] ); ?>
					</div>
					<div class="eb-pro-plugin-card-footer">
						<a class="eb-pro-upgrade-to-pro-btn" href="#"><?php esc_attr_e( 'Upgrade to PRO', 'edwiser-bridge' ); ?></a>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}
?>
