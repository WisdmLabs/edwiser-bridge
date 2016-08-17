<?php
/*
 * This file will initilise the newtwork marketting settings framework
 * @link    www.wisdmlabs.com
 * Version:           1.0.0
 * Author:            WisdmLabs, India
 * @since             1.0.0
 *
 */

namespace wisdmalbs\newtowrk\marketing;

if (!class_exists('wisdmalbs\newtowrk\marketing\NetworkMarketing')) {
    class NetworkMarketing
    {
        public static $nmTextDomain;

        public function __construct()
        {
            include_once 'SyncronizeData.php';
            $this->addMenuToAdminDashBord();
            add_filter('network_marketing_submenu', array($this, 'addSubMenues'), 10, 1);
            add_submenu_page('network-marketing', __('licensing', self::$nmTextDomain), __('Licensing', self::$nmTextDomain), 'manage_options', 'nm-licensing', array($this, 'wdmLicensingPage'));
            add_filter('custom_menu_order', array($this, 'reorderSubMenu')); // Activate custom_menu_order
            $this->enqueStyle();
            $this->enqueScript();
        }

        /**
         * provides the functioanlity to reorder the submenu of the network marketing framework.
         */
        public function reorderSubMenu($menuOrd)
        {
            global $submenu;
            $netMarketSubmenu = $submenu['network-marketing'];
            foreach ($netMarketSubmenu as $key => $value) {
                if ($value[0] == 'Licensing') {
                    $LicenKey = $key;
                    unset($netMarketSubmenu[$LicenKey]);
                    $netMarketSubmenu[] = $value;
                    $submenu['network-marketing'] = $netMarketSubmenu;
                    break;
                }
            }
            $menuOrd = $menuOrd;
        }

        public function addSubMenues($subMenu)
        {
            $this->addSubmenuPages($subMenu['plugin_name'], $subMenu['menu_slug'], $subMenu['call_back']);
        }

        private function addMenuToAdminDashBord()
        {
            add_menu_page('WisdmLabs_marketing', __('WisdmLabs', self::$nmTextDomain), 'manage_options', 'network-marketing', '', plugin_dir_url(__FILE__).'images/Wisdmlabs-Vector-Logo.png', '2');
            add_submenu_page('network-marketing', __('dash-board', self::$nmTextDomain), __('Dashboard', self::$nmTextDomain), 'manage_options', 'network-marketing', array($this, 'netMarkDashBoard'));
        }

        public function netMarkDashBoard()
        {
            include_once 'DashBoard.php';
            echo "<div class='container wrapper'>";
            $dashbord = new DashBoard();
            $dashbord->output();
            echo '</div>';
        }

        public function addSubmenuPages($menuTitle, $menuSlug, $callBack)
        {
            add_submenu_page('network-marketing', __($menuTitle.'-settings', self::$nmTextDomain), __($menuTitle, self::$nmTextDomain), 'manage_options', $menuSlug, $callBack);
        }

        public function wdmLicensingPage()
        {
            include_once 'Licensing.php';
            $licensing = new Licensing();
            $licensing->outPut();
        }

        public static function instance()
        {
            return new self();
        }

        public function enqueStyle()
        {
            wp_register_style('net-mark-style', plugin_dir_url(__DIR__).'NetMarketing/css/net_marketing_style.css');
            wp_enqueue_style('net-mark-style', 99);
        }

        public function enqueScript()
        {
            ?>
            <script type="text/javascript">
                var nmRecomPluginUrl = '<?php echo admin_url('admin-ajax.php');
            ?>';
            </script>
            <?php
            wp_register_script('net-mark-script', plugin_dir_url(__DIR__).'NetMarketing/js/nm-script.js');
            wp_enqueue_script('net-mark-script');
        }

        public static function dashBoardInstance()
        {
            include_once 'DashBoard.php';

            return new DashBoard();
        }
    }

    new NetworkMarketing();
}
