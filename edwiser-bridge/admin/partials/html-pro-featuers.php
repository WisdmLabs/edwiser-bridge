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

if ( count( $this->plugin_licensing_data ) < 5 ) {
    ?>
    <div class="eb_table" >
        <h1><?php esc_attr_e( 'Upgrade to Edwiser Bridge Pro', 'edwiser-bridge-pro' ); ?></h1>
        <?php
        if ( ! is_plugin_active( 'edwiser-bridge-pro/edwiser-bridge-pro.php' ) ) {
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
                    <p><?php echo sprintf ( __( 'Based on your %s it seems that you have purchased only the above mentioned Edwiser Bridge Add-on plugin(s). To get access to all the Edwiser Bridge Pro feature consider %s', 'edwiser-bridge-pro' ), '<a href="#">' . __( 'license key', 'edwiser-bridge-pro' ) . '</a>', '<a href="#">' . __( 'Upgrading to Pro', 'edwiser-bridge-pro' ) . '</a>' ); ?></p>
                    <p><a href="#"><?php _e( 'Refer here', 'edwiser-bridge-pro' ); ?></a><?php _e( ' if you like to learn more about the Edwiser Bridge Pro', 'edwiser-bridge-pro' ) ?></p>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="eb-pro-plugins">  
            <?php
            // non licensed plugins
            foreach ( $this->plugin_data as $key => $plugin ) {
                if( $plugin['slug'] == 'edwiser_bridge_pro' ) {
                    continue;
                }
                if( in_array( $plugin['item_name'], $this->plugin_licensing_data ) ) {
                    continue;
                }
                // create card for each plugin with activation/deactivation button
                ?>
                <div class="eb-pro-upgrade-plugin-card">
                    <div class="eb-pro-plugin-card-header">
                        <h2><?php echo esc_attr( $plugin['item_name'] ); ?></h2>
                    </div>
                    <div class="eb-pro-plugin-card-body">
                        <p><?php echo esc_attr( $plugin['description'] ); ?></p>
                    </div>
                    <div class="eb-pro-plugin-card-footer">
                        <a class="eb-pro-upgrade-to-pro-btn" href="#"><?php esc_attr_e( 'Upgrade to PRO', 'edwiser-bridge-pro' ); ?></a>
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