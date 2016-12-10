<?php

namespace app\wisdmlabs\edwiserBridge;

if (!class_exists("EBDefaultEmailTemplate")) {

    class EBDefaultEmailTemplate
    {

        public function newUserAcoount($tmplId)
        {
            $data = get_option($tmplId);
            if (!$data) {
                return $data;
            }
            $data = array(
                "tmpl_name" => __("New User Account Details", "eb-textdomain"),
                "tmpl_subject" => __('New User Account Details', 'eb-textdomain'),
                "content" => $this->getNewUserAccountTemplate(),
            );
            return $data;
        }

        public function linkWPMoodleAccount($tmplId)
        {
            $data = get_option($tmplId);
            if (!$data) {
                return $data;
            }
            $data = array(
                "tmpl_name" => __("Link existing Wordpress user account to moodle", "eb-textdomain"),
                "tmpl_subject" => __('Your Learning Account Credentials', 'eb-textdomain'),
                "content" => $this->getLinkWPMoodleAccountTemplate(),
            );
            return $data;
        }

        public function orderComplete($tmplId)
        {
            $data = get_option($tmplId);
            if (!$data) {
                return $data;
            }
            $data = array(
                "tmpl_name" => __("Order completed successfully", "eb-textdomain"),
                "tmpl_subject" => __('Your order completed successfully.', 'eb-textdomain'),
                "content" => $this->getOrderCompleteTemplate(),
            );
            return $data;
        }

        private function getNewUserAccountTemplate()
        {
            ob_start();
            ?>
            <div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 0 70px 0;">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td align="center" valign="top">
                                <table id="template_container" style="box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
                                    <tbody>
                                        <tr>
                                            <td align="center" valign="top"><!-- Header -->
                                                <table id="template_header" style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;" border="0" width="600" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">Your Learning Account Credentials</h1>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- End Header --></td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top"><!-- Body -->
                                                <table id="template_body" border="0" width="600" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="background-color: #dfdfdf; border-radius: 6px !important;" valign="top"><!-- Content -->
                                                                <table border="0" width="100%" cellspacing="0" cellpadding="20">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td valign="top">
                                                                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">
                                                                                    <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">

                                                                                        Hi {FIRST_FIRST}

                                                                                    </div>
                                                                                </div>
                                                                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">

                                                                                    A learning account is linked to your profile.
                                                                                    Use credentials given below while accessing your courses.

                                                                                    Username: {USER_NAME}

                                                                                    Password: {USER_PASSWORD}

                                                                                    You can purchase &amp; access courses here: {COURSE_NAME_LINK}.

                                                                                </div></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                                <!-- End Content --></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- End Body --></td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top"><!-- Footer -->
                                                <table id="template_footer" style="border-top: 0; -webkit-border-radius: 6px;" border="0" width="600" cellspacing="0" cellpadding="10">
                                                    <tbody>
                                                        <tr>
                                                            <td style="text-align: center;" valign="top"><span style="font-family: Arial;"><span style="font-size: 12px;">{SITE_NAME}</span></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- End Footer --></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>          
            <?php
            return ob_clean();
        }

        private function getLinkWPMoodleAccountTemplate()
        {
            ob_start();
            ?>
            <div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 0 70px 0;">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td align="center" valign="top">
                                <table id="template_container" style="box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
                                    <tbody>
                                        <tr>
                                            <td align="center" valign="top"><!-- Header -->
                                                <table id="template_header" style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;" border="0" width="600" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">Your Learning Account Credentials</h1>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- End Header --></td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top"><!-- Body -->
                                                <table id="template_body" border="0" width="600" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="background-color: #dfdfdf; border-radius: 6px !important;" valign="top"><!-- Content -->
                                                                <table border="0" width="100%" cellspacing="0" cellpadding="20">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td valign="top">
                                                                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">

                                                                                    Hi {FIRST_NAME}

                                                                                    A learning account is linked to your profile.
                                                                                    Use credentials given below while accessing your courses.

                                                                                    Username: <strong>{USER_NAME}</strong>

                                                                                    Password: <strong>{USER_PASSWORD}</strong>

                                                                                    You can purchase &amp; access courses here: {COURSE_NAME_LINK}.

                                                                                </div></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                                <!-- End Content --></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- End Body --></td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top"><!-- Footer -->
                                                <table id="template_footer" style="border-top: 0; -webkit-border-radius: 6px;" border="0" width="600" cellspacing="0" cellpadding="10">
                                                    <tbody>
                                                        <tr>
                                                            <td style="text-align: center;" valign="top"><span style="font-family: Arial;"><span style="font-size: 12px;">{SITE_NAME}</span></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- End Footer --></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            &nbsp;
            <?php
            return ob_clean();
        }

        private function getOrderCompleteTemplate()
        {
            ob_start();
            ?>
            <div style="background-color: #efefef; width: 100%; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 0 70px 0;">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td align="center" valign="top">
                                <table id="template_container" style="box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important; border-radius: 6px !important; background-color: #dfdfdf;" border="0" width="600" cellspacing="0" cellpadding="0">
                                    <tbody>
                                        <tr>
                                            <td align="center" valign="top"><!-- Header -->
                                                <table id="template_header" style="background-color: #465c94; border-top-left-radius: 6px !important; border-top-right-radius: 6px !important; border-bottom: 0; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;" border="0" width="600" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">Your order completed successfully.</h1>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- End Header --></td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top"><!-- Body -->
                                                <table id="template_body" border="0" width="600" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="background-color: #dfdfdf; border-radius: 6px !important;" valign="top"><!-- Content -->
                                                                <table border="0" width="100%" cellspacing="0" cellpadding="20">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td valign="top">
                                                                                <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">

                                                                                    Hi {FIRST_NAME}

                                                                                    Thanks for purchasing <b>{COURSE_NAME}</b> course.

                                                                                    Your order with {ORDER_ID} completed successfully.

                                                                                    You can access your account here: {WP_LOGIN_PAGE_LINK}.

                                                                                </div></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                                <!-- End Content --></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- End Body --></td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top"><!-- Footer -->
                                                <table id="template_footer" style="border-top: 0; -webkit-border-radius: 6px;" border="0" width="600" cellspacing="0" cellpadding="10">
                                                    <tbody>
                                                        <tr>
                                                            <td style="text-align: center;" valign="top"><span style="font-family: Arial;"><span style="font-size: 12px;">{SITE_NAME}</span></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- End Footer --></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            &nbsp;
            <?php
            return ob_clean();
        }
    }

}