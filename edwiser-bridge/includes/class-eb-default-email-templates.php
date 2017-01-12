<?php
namespace app\wisdmlabs\edwiserBridge;

if (!class_exists("EBDefaultEmailTemplate")) {
    class EBDefaultEmailTemplate
    {
        public function newUserAcoount($tmplId)
        {
            $data = get_option($tmplId);
            if ($data) {
                return $data;
            }
            $data = array(
                "subject" => __('New User Account Details', 'eb-textdomain'),
                "content" => $this->getNewUserAccountTemplate(),
            );
            return $data;
        }
        public function linkWPMoodleAccount($tmplId)
        {
            $data = get_option($tmplId);
            if ($data) {
                return $data;
            }
            $data = array(
                "subject" => __('Your Learning Account Credentials', 'eb-textdomain'),
                "content" => $this->getLinkWPMoodleAccountTemplate(),
            );
            return $data;
        }
        public function orderComplete($tmplId)
        {
            $data = get_option($tmplId);
            if ($data) {
                return $data;
            }
            $data = array(
                "subject" => __('Your order completed successfully.', 'eb-textdomain'),
                "content" => $this->getOrderCompleteTemplate(),
            );
            return $data;
        }
        public function courseAccessExpired($tmplId)
        {
            $data = get_option($tmplId);
            if ($data) {
                return $data;
            }
            $data = array(
                "subject" => __('Course access expired.', 'eb-textdomain'),
                "content" => $this->getCourseAccessExpitedTemplate(),
            );
            return $data;
        }
        private function getNewUserAccountTemplate()
        {
            ob_start();
            ?>
            <div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
                <table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;"><?php _e('Your Learning Account Credentials', 'eb-textdomain'); ?></h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                    printf(
                                        __('Hi %s', 'eb-textdomain'),
                                        '{FIRST_NAME}'
                                    );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Thanks for creating an account on %s. Your username is %s.', 'eb-textdomain'),
                                            '{SITE_NAME}',
                                            '<strong>{USER_NAME}</strong>'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Your password has been automatically generated: %s.', 'eb-textdomain'),
                                            '<strong>{USER_PASSWORD}</strong>'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('You can access your account here: %s.', 'eb-textdomain'),
                                            '<span style="color: #0000ff;">{USER_ACCOUNT_PAGE_LINK}</span>'
                                        );
                                    ?>
                                </div></td>
                        </tr>
                        <tr>
                            <td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>          
            <?php
            return ob_get_clean();
        }
        private function getLinkWPMoodleAccountTemplate()
        {
            ob_start();
            ?>
            <div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
                <table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
                                    <?php _e('Your Learning Account Credentials', 'eb-textdomain'); ?>
                                </h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Hi %s', 'eb-textdomain'),
                                            '{FIRST_NAME}'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        _e('A learning account is linked to your profile.Use credentials given below while accessing your courses.', 'eb-textdomain');
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Username: %s', 'eb-textdomain'),
                                            '<strong>{USER_NAME}</strong>'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                    printf(
                                        __('Password: %s', 'eb-textdomain'),
                                        '<strong>{USER_PASSWORD} </strong>'
                                    );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('You can purchase &amp; access courses here: %s.', 'eb-textdomain'),
                                            '<span style="color: #0000ff;">{COURSES_PAGE_LINK}</span>'
                                        );
                                    ?>
                                </div></td>
                        </tr>
                        <tr>
                            <td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>          
            <?php
            return ob_get_clean();
        }
        private function getOrderCompleteTemplate()
        {
            ob_start();
            ?>
            <div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
                <table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
                                    <?php _e('Your order completed successfully.', 'eb-textdomain'); ?>
                                </h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Hi %s', 'eb-textdomain'),
                                            '{FIRST_NAME}'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Thanks for purchasing %s course.', 'eb-textdomain'),
                                            '<strong>{COURSE_NAME}</strong>'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Your order with ID %s completed successfully.', 'eb-textdomain'),
                                            '<strong>{ORDER_ID}</strong>'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('You can access your account here: %s.', 'eb-textdomain'),
                                            '<span style="color: #0000ff;">{USER_ACCOUNT_PAGE_LINK}</span>'
                                        );
                                    ?>
                                </div></td>
                        </tr>
                        <tr>
                            <td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
            return ob_get_clean();
        }
        private function getCourseAccessExpitedTemplate()
        {
            ob_start();
            ?>
            <div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
                <table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">
                                    <?php
                                        printf(
                                            __('Your %s course access is expired.', 'eb-textdomain'),
                                            '{COURSE_NAME}'
                                        );
                                    ?>
                                </h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Hi %s', 'eb-textdomain'),
                                            '{FIRST_NAME}'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Your Subscription for %s course has expired.', 'eb-textdomain'),
                                            '{COURSE_NAME}'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php
                                        printf(
                                            __('Please purchase the course again to continue with it. %s to purchase now!', 'eb-textdomain'),
                                            '{WP_COURSE_PAGE_LINK}'
                                        );
                                    ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                    <?php _e('Thank you!', 'eb-textdomain'); ?>
                                </div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div></td>
                        </tr>
                        <tr>
                            <td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top"><span style="font-family: Arial; font-size: 12px;">{SITE_NAME}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
            return ob_get_clean();
        }
    }
}
