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

        private function getNewUserAccountTemplate()
        {
            ob_start();
            ?>
            <div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;">
                <table id="template_container" style="padding-bottom: 20px; box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;">
                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">Your Learning Account Credentials</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Hi {FIRST_NAME}</div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Thanks for creating an account on {SITE_NAME}. Your username is <strong>{USER_NAME}</strong>.</div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Your password has been automatically generated: <strong>{USER_PASSWORD}</strong>.</div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">You can access your account here: <span style="color: #0000ff;">{USER_ACCOUNT_PAGE_LINK}</span>.</div></td>
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
                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">Your Learning Account Credentials</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Hi {FIRST_NAME}</div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">A learning account is linked to your profile.Use credentials given below while accessing your courses.</div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Username: <strong>{USER_NAME}</strong></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Password: <strong>{USER_PASSWORD} </strong></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">You can purchase &amp; access courses here: <span style="color: #0000ff;">{COURSES_PAGE_LINK}</span>.</div></td>
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
                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">Your order completed successfully.</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Hi {FIRST_NAME}</div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Thanks for purchasing <strong>{COURSE_NAME}</strong> course.</div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Your order with ID <strong>{ORDER_ID}</strong> completed successfully.</div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">You can access your account here: <span style="color: #0000ff;">{USER_ACCOUNT_PAGE_LINK}</span>.</div></td>
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