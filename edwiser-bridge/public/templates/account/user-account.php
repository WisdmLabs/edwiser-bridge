<div class="eb-user-profile" >

    <?php
    $ebShortcodeObj=\app\wisdmlabs\edwiserBridge\EbShortcodeUserAccount::getInstance();
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
        $labels = apply_filters(
            'eb_user_account_labels',
            array(
                array(
                    'label'=> 'Dashboard',
                    'href' => ''
                    ),
                array(
                    'label' => 'My Profile',
                    'href' => 'eb-my-profile'
                    ),
                array(
                    'label' =>'Orders',
                    'href'=>'eb-orders'
                    ),
                array(
                    'label' => 'My Courses',
                    'href'=> 'eb-my-courses'
                    )
            )
        );

        ?>
        <nav class="eb-user-account-navigation">
            <ul>
        <?php
        foreach ($labels as $label) {
            $navItem=isset($label['label'])?$label['label']:'';
            $navHref=isset($label['href'])?$label['href']:'';
        ?>
            <li class="eb-user-account-navigation-link ">
                <a href="<?php echo esc_url(add_query_arg('eb-active-link', $navHref, get_permalink())); ?>"><?php echo $navItem; ?></a>
            </li>
            <?php
        }
            ?>
            </ul>
        </nav>

        <div class="eb-user-account-content">
        <?php
        if (isset($_GET['eb-active-link'])) {
            switch ($_GET['eb-active-link']) {
                case '':
                            $template_loader->wpGetTemplate(
                                'account/user-data.php',
                                array(
                                'user' => $user,
                                )
                            );

                    break;

                case 'eb-my-profile':
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

                    break;
                case 'eb-orders':
                            $user_orders=$ebShortcodeObj->getUserOrders(get_current_user_id());
                            $template_loader->wpGetTemplate(
                                'account/user-orders.php',
                                array(
                                'user' => $user,
                                'user_meta' => $user_meta,
                                'enrolled_courses' => $enrolled_courses,
                                'template_loader' => $template_loader,
                                'user_orders'=> $user_orders,
                                'user_count'=>15
                                )
                            );

                    break;
                case 'eb-my-courses':
                            $template_loader->wpGetTemplate(
                                'account/my-courses.php',
                                array(
                                'user' => $user,
                                'user_meta' => $user_meta,
                                'enrolled_courses' => $enrolled_courses,
                                'template_loader' => $template_loader,
                                )
                            );

                    break;
                default:
                    do_action('eb_user_account_label_content', $_GET['eb-active-link']);
                    break;
            }
        } else {
            $template_loader->wpGetTemplate(
                'account/user-data.php',
                array(
                'user' => $user,
                )
            );
        }
        
        ?>

        </div> 
        
        <?php
    }// end of else i.e content for logged in users
        ?>
</div>
