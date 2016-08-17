<?php

namespace wisdmalbs\newtowrk\marketing;

if (!class_exists("wisdmalbs\newtowrk\marketing\DashBoard")) {
    class DashBoard
    {
        public function getInstalledPlugins()
        {
            $installedPlugins = get_option('wdm_installed_plugins');
            if ($installedPlugins == false) {
                return array();
            } else {
                return get_option('wdm_installed_plugins');
            }
        }

        public function addInstalledPluginData($title, $desc, $menuSlug)
        {
            $installedPlugins = $this->getInstalledPlugins();
            if ($installedPlugins !== false && !is_array($installedPlugins)) {
                $installedPlugins = array();
            }
            $link = menu_page_url($menuSlug, false);
            $installedPlugins[$title] = array('title' => $title, 'desc' => $desc, 'url' => $link);
            update_option('wdm_installed_plugins', $installedPlugins);
        }

        public function getRecommendedPlugins()
        {
            $sync = new SyncronizeData($requestUrl, $parameter, 'nm_recom_plugins');
            if (isset($_POST['nm-refresh-recom']) && $_POST['nm-refresh-recom'] == 'Refresh' && $_GET['nm-plugins'] == 'recommended') {
                $sync->updateData();
            } else {
                $sync->prepareRequest();
            }
            $installedPlugins = $this->getInstalledPlugins();
            $recoPlugins = get_option('nm_recom_plugins');

            foreach ($recoPlugins as $key => $value) {
                if (array_key_exists($value['title'], $installedPlugins)) {
                    unset($recoPlugins[$key]);
                }
            }

            return $recoPlugins;
        }

        public static function dashBoardHeader()
        {
            ?>
            <div class="dash-bord-header">
                <div class="dash-bord-logo">
                    <img class="dash-bord-img" src="https://wisdmlabs.com/site/wp-content/uploads/2009/11/WisdmLabs-Logo-Final-transparent.png">
                    <label><?php _e('WisdmLabs', NetworkMarketing::$nmTextDomain) ?></label>
                </div><div class="dash-bord-link">
                    <label><?php _e('Thank you for downloading plugin. For more details please', NetworkMarketing::$nmTextDomain) ?> </label>
                    <a href="https://wisdmlabs.com/"> <?php _e('Click here', NetworkMarketing::$nmTextDomain) ?> </a>
                </div>
            </div>
            <?php

        }

        public function displayExtensions($recExtension)
        {
            echo "<ul class='recom-plugin'>";
            foreach ($recExtension as $exten) {
                $this->displayBox($exten['desc'], $exten['title'], $exten['url']);
            }
            echo '</ul>';
        }

        public function output()
        {
            ?>
            <div class="nm-dashbord-container wrap">
                <?php
                self::dashBoardHeader();

                if (!isset($_GET['nm-plugins']) || $_GET['nm-plugins'] == 'installed') {
                    $this->dashBoardSubMenu('active-sub-tab', '');
                    $this->displayExtensions($this->getInstalledPlugins());
                } else {
                    $this->dashBoardSubMenu('', 'active-sub-tab');
                    $this->displayExtensions($this->getRecommendedPlugins());
                    echo '<form method="post">';
                    echo'<input type="submit" id="nm-refresh-recom" class="button-primary" value="Refresh" name="nm-refresh-recom">';
                    echo'</form>';
                }
            ?>
            </div>
            <?php

        }

        private function dashBoardSubMenu($instClass, $recClass)
        {
            $pageUrl = menu_page_url('network-marketing', false);
            ?>
            <div>                    
                <div>
                    <h2><?php _e('WisdmLabs Add-ons/Extensions', NetworkMarketing::$nmTextDomain);
            ?>
                        <a href="https://wisdmlabs.com/" class="dash-board-submenu"><?php _e('Browse all extensions', NetworkMarketing::$nmTextDomain);
            ?></a>

                    </h2>
                </div>
                <ul class="nm-sub-section">
                    <li>
                        <a href="<?php echo "$pageUrl&nm-plugins=installed";
            ?>" class="<?php echo $instClass;
            ?>"><?php _e('Installed Plugins', NetworkMarketing::$nmTextDomain) ?></a>
                    </li>
                    <li>
                        <a href="<?php echo "$pageUrl&nm-plugins=recommended";
            ?>" class="<?php echo $recClass;
            ?>"><?php _e('Recommended Plugins', NetworkMarketing::$nmTextDomain) ?></a>
                    </li>
                </ul>
            </div>
            <?php

        }

        public function displayBox($desc, $pluginName, $link)
        {
            ?>
            <li class="plugin">
                <a href="<?php _e($link, NetworkMarketing::$nmTextDomain);
            ?>">
                    <h2><?php _e($pluginName, NetworkMarketing::$nmTextDomain);
            ?></h2>
                    <p><?php _e($desc, NetworkMarketing::$nmTextDomain);
            ?></p>
                </a>                
            </li>
            <?php

        }
    }
}
