<?php

namespace wisdmalbs\newtowrk\marketing;

if (!class_exists("wisdmalbs\newtowrk\marketing\General")) {
    abstract class General
    {
        abstract public function nmDocumentation();

        abstract public function nmGetReadmeFile();

        private function nmChangeLog()
        {
            $file = $this->nmGetReadmeFile();
            $file_contents = @implode('', @file($file));
            include_once 'ReadmeParse.php';
            $redmeParser = new ReadmeParse();
            $changeLog = $redmeParser->parseReadmeContents($file_contents);
            echo $changeLog;
        }

        private function nmUpcomingFetures()
        {
            //            $arrUpcomFeth=  get_option($_GET['page']."upcoming_fetures");
            $arrUpcomFeth = array('Upcoming Feture 1', 'Upcoming Feture 2', 'Upcoming Feture 3', 'Upcoming Feture 4', 'Upcoming Feture 5', 'Upcoming Feture 6');
            echo '<ul>';
            foreach ($arrUpcomFeth as $fetcher) {
                echo "<li class='nm-genral-feth'>".__($fetcher, NetworkMarketing::$nmTextDomain).'</li>';
            }
            echo '</ul>';
        }

        public function outPut()
        {
            ?>
            <div class="nm-genral-wrap">
                <ul class="nm-sub-section">
                    <li id="nm-doc-id" class="nm-sub-menu nm-sub-menu-activ"><?php _e('Documentation', NetworkMarketing::$nmTextDomain)?></li>
                    <li id="nm-change-log-id" class="nm-sub-menu"><?php _e('Change Log', NetworkMarketing::$nmTextDomain)?></li>
                    <li id="nm-upcom-featu-id" class="nm-sub-menu"><?php _e('Upcoming Features', NetworkMarketing::$nmTextDomain)?></li>
                </ul>
                <div class="nm-content">
                    <div class="nm-genral-changelog-wrap nm-hide">
                        <!--<h2><?php // _e('Change Log',  NetworkMarketing::$nmTextDomain)?></h2>-->
                        <div class="nm-genral-changelog">
            <?php $this->nmChangeLog();
            ?>
                        </div>
                    </div>

                    <div class="nm-genral-doc-wrap">
                        <!--<h2><?php // _e('Documentation',  NetworkMarketing::$nmTextDomain)?></h2>-->
                        <div class="nm-genral-doc">
                            <?php $this->nmDocumentation();
            ?>
                        </div>
                    </div>

                    <div class="nm-genral-upcom-feth-wrap nm-hide">
                        <!--<h2><?php // _e('Upcoming Features',  NetworkMarketing::$nmTextDomain)?></h2>-->
                        <div class="nm-genral-upcom-feth">
                            <?php $this->nmUpcomingFetures();
            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php

        }
    }
}
