<div class="eb-user-profile" >
    <?php
    $ebShortcodeObj = \app\wisdmlabs\edwiserBridge\EbShortcodeUserAccount::getInstance();
    if (isset($_GET['eb_action']) && $_GET['eb_action'] === 'edit-profile') {
        $template_loader->wpGetTemplate(
            'account/edit-user-profile.php',
            array(
            'user_avatar' => $user_avatar,
            'user' => $user,
            'user_meta' => $user_meta,
            'enrolled_courses' => $enrolled_courses,
            'template_loader' => $template_loader,
                )
        );
    } else {
        $labels = $ebShortcodeObj->getUserAccountNavigationItems();
        ?>
        <div class="eb-user-account-navigation">
            <div>
                <?php
                foreach ($labels as $label) {
                    $navItem = isset($label['label']) ? $label['label'] : '';
                    $navHref = isset($label['href']) ? $label['href'] : '';
                    $cssClass = 'eb-user-account-navigation-link';
                    if (isset($_GET['eb-active-link']) && $_GET['eb-active-link'] == $navHref) {
                        $cssClass .= ' eb-active-profile-nav';
                    }
                    ?>
                <nav class="<?php echo $cssClass;?>">
                        <a href="<?php echo esc_url(add_query_arg('eb-active-link', $navHref, get_permalink()));?>"><?php _e($navItem, 'eb-textdomain');?></a>
                    </nav>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="eb-user-account-content">
            <?php
            if (isset($_GET['eb-active-link'])) {
                $ebShortcodeObj->getUserAccountContent($_GET['eb-active-link'], $user_orders, $order_count, $user_avatar, $user, $user_meta, $enrolled_courses, $template_loader);
            } else {
                $template_loader->wpGetTemplate(
                    'account/user-data.php',
                    array(
                    'user' => $user,
                    'user_avatar' => $user_avatar,
                        )
                );
            }
            ?>
        </div>
        <?php
    }// end of else i.e content for logged in users?>
</div>
