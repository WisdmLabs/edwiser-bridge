(function ($) {
    'use strict';
    /**
     * All of the code for your admin-specific JavaScript source
     * should reside in this file.
     *
     * Note that this assume you're going to use jQuery, so it prepares
     * the $ function reference to be used within the scope of this
     * function.
     *
     * From here, you're able to define handlers for when the DOM is
     * ready:
     *
     * $(function() {
     *
     * });
     *
     * Or when the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and so on.
     *
     * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
     * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
     * be doing this, we should try to minimize doing that in our own work.
     */

    $(window).load(function () {

// Color picker
        jQuery('.colorpick').iris({
            change: function (event, ui) {
                jQuery(this).css({backgroundColor: ui.color.toString()});
            },
            hide: true,
            border: true
        }).each(function () {
            jQuery(this).css({backgroundColor: jQuery(this).val()});
        })
                .click(function () {
                    jQuery('.iris-picker').hide();
                    jQuery(this).closest('.color_box, td').find('.iris-picker').show();
                });
        jQuery('body').click(function () {
            jQuery('.iris-picker').hide();
        });
        jQuery('.color_box, .colorpick').click(function (event) {
            event.stopPropagation();
        });
        // Edit prompt
        jQuery(function () {
            var changed = false;
            jQuery('input, textarea, select, checkbox').change(function () {
                changed = true;
            });
            jQuery('.eb-nav-tab-wrapper a').click(function () {
                if (changed) {
                    window.onbeforeunload = function () {
                        var flag = true;
                        var query = window.location.search.substring(1);
                        var vars = query.split("&");
                        for (var i = 0; i < vars.length; i++) {
                            var pair = vars[i].split("=");
                            // If first entry with this name
                            if (pair[0] == 'tab' && pair[1] == 'synchronization') {
                                flag = false;
                            }
                        }
                        if (flag) {
                            return eb_admin_js_object.unsaved_warning;
                        }
                    };
                } else {
                    window.onbeforeunload = '';
                }
            });
            jQuery('.submit input').click(function () {
                window.onbeforeunload = '';
            });
        });
        //help tip
        var tiptip_args = {
            'attribute': 'data-tip',
            'fadeIn': 50,
            'fadeOut': 50,
            'delay': 200
        };
        jQuery(".tips, .help_tip, .help-tip").tipTip(tiptip_args);
        // Add tiptip to parent element for widefat tables
        jQuery(".parent-tips").each(function () {
            jQuery(this).closest('a, th').attr('data-tip', jQuery(this).data('tip')).tipTip(tiptip_args).css('cursor', 'help');
        });
        /**
         * == OhSnap!.js ==
         * A simple notification jQuery/Zepto library designed to be used in mobile apps
         *
         * author: Justin Domingue
         * date: september 5, 2013
         * version: 0.1.2
         * copyright - nice copyright over here
         */

        function ohSnap(text, type, status)
        {
            // text : message to show (HTML tag allowed)
            // Available colors : red, green, blue, orange, yellow --- add your own!

            // Set some variables
            var time = '10000';
            var container = jQuery('.response-box');
            // Generate the HTML
            var html = '<div class="alert alert-' + type + '">' + text + '</div>';
            // Append the label to the container
            container.append(html);
            // after 'time' seconds, the animation fades out
            // setTimeout(function () {
            //   ohSnapX(container.children('.alert'));
            // }, time);
        }

        function ohSnapX(element)
        {
            // Called without argument, the function removes all alerts
            // element must be a jQuery object

            if (typeof element !== "undefined") {
                element.fadeOut();
            } else {
                jQuery('.alert').fadeOut();
            }
        }

        // Remove the notification on click
        $('.alert').live('click', function () {
            ohSnapX(jQuery(this));
        });
        /**
         * creates ajax request to initiate test connection request
         * display a response to user on process completion
         */
        $('#eb_test_connection_button').click(function () {
            //get selected options
            //
            $('.response-box').empty(); // empty the response
            var url = $('#eb_url').val();
            var token = $('#eb_access_token').val();
            var $this = $(this);
            //display loading animation
            $('.load-response').show();
            $.ajax({
                method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'handleConnectionTest',
                    'url': url,
                    'token': token,
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    $('.load-response').hide();
                    //prepare response for user
                    if (response.success == 1) {
                        ohSnap(eb_admin_js_object.msg_con_success, 'success', 1);
                    } else {
                        ohSnap(response.response_message, 'error', 0);
                    }
                }
            });
        });
        /**
         * creates ajax request to initiate course synchronization
         * display a response to user on process completion
         */
        $('#eb_synchronize_courses_button').click(function () {

            $('.response-box').empty(); // empty the response

            var sync_options = {};
            var $this = $(this);
            // prepare sync options array
            $('input:checkbox').each(function () {
                var cb_key = $(this).attr('id');
                var cb_value = (this.checked ? $(this).val() : 0);
                sync_options[cb_key] = cb_value;
            });
            //display loading animation
            $('.load-response').show();
            $.ajax({
                method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'handleCourseSynchronization',
                    //'sync_category' : sync_category,
                    //'make_courses_draft': make_courses_draft,
                    //'update_courses':update_courses,
                    'sync_options': JSON.stringify(sync_options),
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {

                    $('.load-response').hide();
                    //prepare response for user
                    if (response.connection_response == 1) {
                        if (response.course_success == 1) {
                            ohSnap(eb_admin_js_object.msg_courses_sync_success, 'success', 1);
                        } else {
                            ohSnap(response.course_response_message, 'error', 0);
                        }

                        if (sync_options['eb_synchronize_categories'] == 1 && response.category_success == 1) {
                            ohSnap(eb_admin_js_object.msg_cat_sync_success, 'success', 1);
                        } else if (sync_options['eb_synchronize_categories'] == 1 && response.category_success == 0) {
                            ohSnap(response.category_response_message, 'error', 0);
                        }
                    } else {
                        ohSnap(eb_admin_js_object.msg_con_prob, 'error', 0);
                    }
                }
            });
        });
        /**
         * creates ajax request to initiate user's courses synchronization
         * display a response to user on process completion
         */
        $('#eb_synchronize_users_button').click(function () {

            $('.response-box').empty(); // empty the response

            // get selected options
            // var sync_user_courses 	= ($('#eb_synchronize_user_courses').prop('checked'))?1:0;
            var response_message = '';
            var user_id_success = '';
            var user_id_error = '';
            var $this = $(this);
            var sync_options = {};
            // prepare sync options array
            $('input:checkbox').each(function () {
                var cb_key = $(this).attr('id');
                var cb_value = (this.checked ? $(this).val() : 0);
                sync_options[cb_key] = cb_value;
            });
            //display loading animation
            $('.load-response').show();
            $.ajax({
                method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'handleUserCourseSynchronization',
                    'sync_options': JSON.stringify(sync_options),
                    '_wpnonce_field': eb_admin_js_object.nonce
                },
                success: function (response) {
                    $('.load-response').hide();
                    if (response.connection_response == 1) {
                        if (response.user_with_error !== undefined) {
                            $.each(response.user_with_error, function (index, value) {
                                user_id_error += this;
                            });
                        }

                        if (response.user_with_error !== undefined) {
                            ohSnap('<p>' + eb_admin_js_object.msg_err_users + '</p>' + user_id_error, 'red');
                        } else {
                            ohSnap('<p>' + eb_admin_js_object.msg_user_sync_success + '</p>', 'success', 1);
                        }
                    } else {
                        ohSnap(eb_admin_js_object.msg_con_prob, 'error', 0);
                    }
                }
            });
        });
        /**
         * Handle course price dropdown toggle.
         */
        $('#course_price_type').change(function () {
            var type = $('#course_price_type').val();
            if (type == 'free') {
                $('#eb_course_course_price').hide();
                $('#eb_course_course_closed_url').hide();
                $('#course_price').val('');
                $('#course_closed_url').val('');
            } else if (type == 'paid') {
                $('#eb_course_course_price').show();
                $('#eb_course_course_closed_url').hide();
                $('#course_closed_url').val('');
            } else if (type == 'closed') {
                $('#eb_course_course_price').hide();
                $('#eb_course_course_closed_url').show();
                $('#course_price').val('');
            }
        });
        $("#course_expirey").change(function () {
            if ($(this).prop("checked") == true) {
                $('#eb_course_num_days_course_access').show();
            } else {
                $('#eb_course_num_days_course_access').hide();
                $('#num_days_course_access').val("");
            }
        });
        $('#course_price_type').change();
        $("#course_expirey").change();

    });
    function setGetParameter(paramName, paramValue)
    {
        var url = window.location.href;
        var hash = location.hash;
        url = url.replace(hash, '');
        if (url.indexOf("?") >= 0)
        {
            var params = url.substring(url.indexOf("?") + 1).split("&");
            var paramFound = false;
            params.forEach(function (param, index) {
                var p = param.split("=");
                if (p[0] == paramName) {
                    params[index] = paramName + "=" + paramValue;
                    paramFound = true;
                }
            });
            if (!paramFound)
                params.push(paramName + "=" + paramValue);
            url = url.substring(0, url.indexOf("?") + 1) + params.join("&");
        } else
            url += "?" + paramName + "=" + paramValue;
        window.history.pushState(null, null, url + hash);
    }
    /**
     * Email template ajax request handler.
     */
    $(document).ready(function () {
        $(".eb-emailtmpl-list-item").click(function (e) {
            e.preventDefault();
            var tmplId = this.id;
            var name = $(this).text();
            setGetParameter("curr_tmpl", tmplId);
            $.ajax({
                type: "post",
                url: ajaxurl,
                data: {action: "wdm_eb_get_email_template", tmpl_name: tmplId},
                error: function (error) {
                    alert(eb_admin_js_object.msg_tpl_not_found);
                },
                success: function (response) {
                    setTemplateData(response, name, tmplId);
                    $(".eb-emailtmpl-list-item").removeClass("eb-emailtmpl-active");
                    $("#" + tmplId).addClass("eb-emailtmpl-active");
                }
            });
        });
        $("#eb_send_test_email").click(function (e) {
            e.preventDefault();
            $('.response-box').empty();
            $('.load-response').show();
            var mailTo = $("#eb_test_email_add_txt").val();
            var subject = $("#eb_email_subject").val();
            var security = $("#eb_send_testmail_sec_filed").val();
            var message = tinyMCE.get("eb_emailtmpl_editor").getContent();
            $.ajax({
                type: "post",
                url: ajaxurl,
                data: {action: "wdm_eb_send_test_email", mail_to: mailTo, subject: subject, content: message, security: security},
                error: function (error) {
                    $('.load-response').hide();
                    ohSnap('<p>' + eb_admin_js_object.msg_mail_delivery_fail + '</p>', 'error');
                },
                success: function (response) {
                    response = $.parseJSON(response);
                    console.log(response["success"]);
                    $('.load-response').hide();
                    if (response["success"] == "1") {
                        ohSnap('<p>' + eb_admin_js_object.msg_test_mail_sent_to + mailTo + '</p>', 'success');
                    } else {
                        ohSnap('<p>' + eb_admin_js_object.msg_mail_delivery_fail + '</p>', 'error');
                    }
                }
            });
        });


         $(".link-unlink").click(function (e) {
            e.preventDefault();
            var userid=$(this).parent().attr("id");
            var linkuser=$(this).attr("for");
            linkuser=linkuser.substr(linkuser.indexOf("-")+1);
            var str=linkuser;
            linkuser=0;
            if(str == "link"){
                linkuser=1;
                var showText=eb_admin_js_object.msg_link_user;
            } else {
                var showText=eb_admin_js_object.msg_unlink_user;
            }
            $("#moodleLinkUnlinkUserNotices").css("display","none");
            $.ajax({
                type: "post",
                url: ajaxurl,
                data: {action: "moodleLinkUnlinkUser", user_id: userid, link_user:linkuser },
                error: function (error) {
                    $("#moodleLinkUnlinkUserNotices").css("display","block");
                    $("#moodleLinkUnlinkUserNotices").removeClass("updated");
                    $("#moodleLinkUnlinkUserNotices").addClass("notice notice-error");
                    if(str == "link")
                        {
                            $("#moodleLinkUnlinkUserNotices").children().html(eb_admin_js_object.msg_error_link_user);
                        } else {
                            $("#moodleLinkUnlinkUserNotices").children().html(eb_admin_js_object.msg_error_unlink_user);
                        }
                },
                success: function (response) {
                    if(response.includes("Success")){
                        var message=response.substr(response.indexOf("-")+1);
                        $("#moodleLinkUnlinkUserNotices").addClass("updated");
                        $("#moodleLinkUnlinkUserNotices").css("display","block");
                        $("#moodleLinkUnlinkUserNotices").children().html(showText+" "+message);
                        $("#"+userid+"-"+str).prop("checked",true);
                    } else {
                         var message=response.substr(response.indexOf("-")+1);
                        $("#moodleLinkUnlinkUserNotices").css("display","block");
                        $("#moodleLinkUnlinkUserNotices").removeClass("updated");
                        $("#moodleLinkUnlinkUserNotices").addClass("notice notice-error");
                        if(response.includes("LinkError")){
                            $("#moodleLinkUnlinkUserNotices").children().html(eb_admin_js_object.msg_error_moodle_link);
                        } else {
                            if(str == "link")
                            {
                                $("#moodleLinkUnlinkUserNotices").children().html(eb_admin_js_object.msg_error_link_user);
                            } else {
                                $("#moodleLinkUnlinkUserNotices").children().html(eb_admin_js_object.msg_error_unlink_user);
                            }
                        }
                    }

                }
            });
        });

    });

    function ohSnap(text, type)
    {
        var container = $('.response-box');
        var html = '<div class="alert alert-' + type + '">' + text + '</div>';
        container.empty();
        container.append(html);
    }

    /**
     * Provides the functionality to set the email template page content on
     * the ajax sucerssfull responce.
     *
     * @param {type} response responce sent by the ajax.
     * @param {type} name name of the template.
     */
    function setTemplateData(response, name, tmplId)
    {
        try {
            response = $.parseJSON(response);
            $("#eb-email-template-name").text(name);
            $("#eb_email_from").val(response['from_email']);
            $("#eb_email_from_name").val(response['from_name']);
            $("#eb_email_subject").val(response['subject']);
            $("#eb_emailtmpl_name").val(tmplId);
            if (tinyMCE.activeEditor == null) {
               jQuery("#eb_emailtmpl_editor").html(response['content']);
            } else {
               tinyMCE.get("eb_emailtmpl_editor").setContent(response['content']);
            }
        } catch (e) {
            alert(eb_admin_js_object.msg_err_parsing_res);
            console.log("EB Error : " + e);
        }
    }

    //Single course and archive page settings.
    $(document).ready(function () {

        // archive courses right sidebar
        $('input#archive_enable_right_sidebar').change(function () {
            if ($(this).is(':checked')) {
                $('#archive_right_sidebar').closest('tr').show();
            } else {
                $('#archive_right_sidebar').closest('tr').hide();
            }
        }).change();
        $('input#archive_enable_left_sidebar').change(function () {
            if ($(this).is(':checked')) {
                $('#archive_left_sidebar').closest('tr').show();
            } else {
                $('#archive_left_sidebar').closest('tr').hide();
            }
        }).change();

        // single course right sidebar
        $('input#single_enable_right_sidebar').change(function () {
            if ($(this).is(':checked')) {
                $('#single_right_sidebar').closest('tr').show();
            } else {
                $('#single_right_sidebar').closest('tr').hide();
            }
        }).change();
        $('input#single_enable_left_sidebar').change(function () {
            if ($(this).is(':checked')) {
                $('#single_left_sidebar').closest('tr').show();
            } else {
                $('#single_left_sidebar').closest('tr').hide();
            }
        }).change();

    });

})(jQuery);
