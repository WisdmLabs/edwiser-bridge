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
        if ($("#eb_email_templates_list").length) {
            var container = $("#eb_email_templates_list");
            var scrollTo = $(".eb-emailtmpl-active");
            container.animate({
                scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
            });
        }
        /**
         * Add the ajax processing icon
         * @type String
         */
        var login = '<div id="eb-lading-parent" class="eb-lading-parent-wrap"><div class="eb-loader-progsessing-anim"></div></div>';
        $("body").append(login);

        $('.colorpick').iris({
            change: function (event, ui) {
                $(this).css({backgroundColor: ui.color.toString()});
            },
            hide: true,
            border: true
        }).each(function () {
            $(this).css({backgroundColor: jQuery(this).val()});
        })
                .click(function () {
                    $('.iris-picker').hide();
                    $(this).closest('.color_box, td').find('.iris-picker').show();
                });
        $('body').click(function () {
            $('.iris-picker').hide();
        });
        $('.color_box, .colorpick').click(function (event) {
            event.stopPropagation();
        });
        // Edit prompt
        $(function () {
            var changed = false;
            $('input, textarea, select, checkbox').change(function () {
                changed = true;
            });
            $('.eb-nav-tab-wrapper a').click(function () {
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
            $('.submit input').click(function () {
                window.onbeforeunload = '';
            });
            $('.eb-unenrol').click(function (e) {
                var userId = jQuery(this).data('user-id');
                var recordId = jQuery(this).data('record-id');
                var courseId = jQuery(this).data('course-id');
                var row = jQuery(this).parents('tr');
                $("#eb-lading-parent").show();
                $.ajax({
                    method: "post",
                    url: eb_admin_js_object.ajaxurl,
                    dataType: "json",
                    data: {
                        'action': 'wdm_eb_user_manage_unenroll_unenroll_user',
                        'user_id': userId,
                        'course_id': courseId,
                    },
                    success: function (response) {
                        $('.load-response').hide();
                        var message = "";
                        if (response['success'] == true) {
                            var msg = response['data'];
                            message = "<div class='notice notice-success is-dismissible'>"
                                    + "<p><strong>" + msg + "</strong></p>"
                                    + "<button type='button' class='notice-dismiss'>"
                                    + "<span class='screen-reader-text'>Dismiss this notice</span>"
                                    + "</button>"
                                    + "</div>";
                            jQuery(row).css('background-color', '#d7cad2');
                            jQuery(row).fadeOut(2000, function () { });

                        } else {
                            var msg = response['data'];
                            message = "<div class='notice notice-error is-dismissible'>"
                                    + "<p><strong>" + msg + "</strong></p>"
                                    + "<button type='button' class='notice-dismiss'>"
                                    + "<span class='screen-reader-text'>Dismiss this notice</span>"
                                    + "</button>"
                                    + "</div>";
                        }
                        $("#eb-notices").empty();
                        $("#eb-notices").append(message);
                        $("#eb-lading-parent").hide();
                    },
                    error: function (error) {
                        var html = "<div class='notice notice-error is-dismissible'>"
                                + "<p><strong>Error unenrolling student</strong></p>"
                                + "<button type='button' class='notice-dismiss'>"
                                + "<span class='screen-reader-text'>Dismiss this notice</span>"
                                + "</button>"
                                + "</div>";
                        $("#eb-lading-parent").hide();
                    }
                });
            });
        });
        //help tip
        var tiptip_args = {
            'attribute': 'data-tip',
            'fadeIn': 50,
            'fadeOut': 50,
            'delay': 200
        };
        $(".tips, .help_tip, .help-tip").tipTip(tiptip_args);
        // Add tiptip to parent element for widefat tables
        $(".parent-tips").each(function () {
            $(this).closest('a, th').attr('data-tip', jQuery(this).data('tip')).tipTip(tiptip_args).css('cursor', 'help');
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
        $('.alert').on('click', function () {
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
                    'url': url.trim(),
                    'token': token.trim(),
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    $('.load-response').hide();
                    //prepare response for user
                    if (response.success == 1) {
                        ohSnap(eb_admin_js_object.msg_con_success, 'success', 1);
                    } else {
                        ohSnap(response.response_message, 'error', 0);
                        // ohSnap(custom_response, 'error', 0);
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
                        if (sync_options['eb_synchronize_previous'] == 1 || sync_options['eb_synchronize_draft'] !== null ) {
                            if (response.course_success == 1) {
                                ohSnap(eb_admin_js_object.msg_courses_sync_success, 'success', 1);
                            } else {
                                ohSnap(response.course_response_message, 'error', 0);
                            }
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
            $('.linkresponse-box').empty(); // empty the response
            // get selected options
            // var sync_user_courses 	= ($('#eb_synchronize_user_courses').prop('checked'))?1:0;
            var $this = $(this);
            var sync_options = {};
            // prepare sync options array
            $('input:checkbox').each(function () {
                var cb_key = $(this).attr('id');
                var cb_value = (this.checked ? $(this).val() : 0);
                sync_options[cb_key] = cb_value;
            });
            var offset = 0;
            var progressWidth = 0;
            var linkedUsers = 0;
            var users_count = 0;
            var queryLimit = 0;
            var notLinkedusers = [];
            //display loading animation
            $('.load-response').show();
            // new Ajax call function for user course status synchronization.
            if($("#eb_synchronize_user_courses").prop('checked') == true){
                userSyncAjax($this, sync_options, offset, progressWidth);
            }
            // new Ajax call function for user link to moodle synchronization.
            if($("#eb_link_users_to_moodle").prop('checked') == true){
                $(".unlink-table tbody").empty();
                userLinkSyncAjax($this, sync_options, offset, linkedUsers, users_count, queryLimit, notLinkedusers);
            }
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
    /* Function for user synchronization, this will have a ajax call which will run after completion of another(recursively) */
    function userSyncAjax($this, sync_options, offset, progressWidth) {
        $('.load-response').show();
        var response_message = '';
        var user_id_success = '';
        var user_id_error = '';
        if(!$('.response-box').is(":empty"))
        {
            $('.linkresponse-box').css('margin-top', '3%');
        }
        $.ajax({
            method: "post",
            url: eb_admin_js_object.ajaxurl,
            dataType: "json",
            data: {
                'action': 'handleUserCourseSynchronization',
                'sync_options': JSON.stringify(sync_options),
                '_wpnonce_field': eb_admin_js_object.nonce,
                'offset': offset
            },
            success: function (response) {
                offset = offset + response.users_count;
                showUserCourseSynchProgress(offset, response.wp_users_count, 'success');
                if (response.connection_response == 1) {
                    if (response.user_with_error !== undefined) {
                        $.each(response.user_with_error, function (index, value) {
                            user_id_error += this;
                        });
                    }

                    if (response.user_with_error !== undefined) {
                        $('.load-response').hide();
                        ohSnap('<p>' + eb_admin_js_object.msg_err_users + '</p>' + user_id_error, 'red');
                    } else {
                        if (offset < response.wp_users_count) {
                            userSyncAjax($this, sync_options, offset, progressWidth);
                        } else {
                            $('.load-response').hide();
                            if(!$('.response-box').is(":empty"))
					        {
					            $('.linkresponse-box').css('margin-top', '3%');
					        }
                            ohSnap('<p>' + eb_admin_js_object.msg_user_sync_success + '</p>', 'success', 1);
                        }
                    }
                } else {
                    $('.load-response').hide();
                    ohSnap(eb_admin_js_object.msg_con_prob, 'error', 0);
                }
            }
        });
    }
     /* Function for link users to moodle, this will have a ajax call which will run after completion of another(recursively) */
    function userLinkSyncAjax($this, sync_options, offset, linkedUsers, users_count, queryLimit, notLinkedusers) {
        $('.load-response').show();
        var response_message = '';
        var user_id_success = '';
        var user_id_error = '';
        if(!$('.response-box').is(":empty"))
        {
            $('.linkresponse-box').css('margin-top', '3%');
        }
        $.ajax({
            method: "post",
            url: eb_admin_js_object.ajaxurl,
            dataType: "json",
            data: {
                'action': 'handleUserLinkToMoodle',
                'sync_options': JSON.stringify(sync_options),
                '_wpnonce_field': eb_admin_js_object.nonce,
                'offset': offset
            },
            success: function (response) {
                queryLimit = queryLimit + 20;
                offset = offset + Math.abs(parseInt(response.unlinked_users_count) - parseInt(response.linked_users_count));
                linkedUsers = parseInt(linkedUsers) + parseInt(response.linked_users_count);
                users_count = parseInt(linkedUsers) + parseInt(response.users_count);
                showLinkedUsersProgress(linkedUsers, users_count, 'success');
                if (response.connection_response == 1) {
                    if (response.user_with_error !== undefined) {
                        $.each(response.user_with_error, function (index, value) {
                            if (!notLinkedusers.includes(value)) {
                                notLinkedusers.push(value);
                                user_id_error += this;
                            }
                        });
                    }
                    if (queryLimit < users_count) {
                        userLinkSyncAjax($this, sync_options, offset, linkedUsers, users_count, queryLimit, notLinkedusers);
                    } else {
                        $('.load-response').hide();
                        if(!$('.response-box').is(":empty"))
				        {
				            $('.linkresponse-box').css('margin-top', '3%');
				        }
                        $('.linkresponse-box').css('margin-left', '0px !important');
                        // linkUserResponseBox('<p class="linkerror">' + eb_admin_js_object.msg_user_sync_success + '</p>', 'success', 1);
                        if (typeof notLinkedusers !== 'undefined' && notLinkedusers.length > 0) {
                            var container = $('.linkresponse-box');
                            var html = '<span class="linkresponse-box-error">'+eb_admin_js_object.msg_unlink_users_list+'</span>';
                            container.append(html);
                            $(".unlink-table tbody").append(notLinkedusers);
                        }
                    }
                } else {
                    $('.load-response').hide();
                    linkUserResponseBox(eb_admin_js_object.msg_con_prob, 'error', 0);
                }
            }
        });
    }
    // Used to show the response in popup for unlinked users to moodle functionality.
    $(document).on('click', '.linkresponse-box a', function(){
        $("#unlinkerrorid-modal").show();
    });
     // Used to hide the response in popup for unlinked users to moodle functionality.
    $(document).on('click', '.unlinkerror-modal-close', function(){
        $("#unlinkerrorid-modal").hide();
    });
    /**
     * This function is used to show the response for link users to moodle functionliaty.
     */
    function linkUserResponseBox(text, type) {
        var container = $('.linkresponse-box');
        var html = '<div class="alert alert-' + type + '">' + text + '</div>';
        container.empty();
        container.append(html);
    }
    /* Function to show user's course synch progress */
    function showUserCourseSynchProgress(users_count = 0, wp_users_count = 0, type) {
        var container = $('.response-box');
        var html = '<div class="alert alert-' + type + '">' + users_count+' / '+wp_users_count+' '+eb_admin_js_object.msg_user_sync_success+'</div>';
        container.empty();
        container.append(html);
    }
     /* Function to show progress of link users to moodle functionality*/
    function showLinkedUsersProgress(linked_users_count = 0, unlinked_users_count = 0, type) {
        var container = $('.linkresponse-box');
        var html = '<div class="alert alert-' + type + '">' + linked_users_count+' / '+unlinked_users_count+' '+eb_admin_js_object.msg_user_link_to_moodle_success+ '</div>';
        container.empty();
        container.append(html);
    }
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
            $("#current_selected_email_tmpl_key").val(tmplId);
            setGetParameter("curr_tmpl", tmplId);
            $("#eb-lading-parent").show();
            getTamplateContent(tmplId, name);
            $(document).on("click", ".notice-dismiss", function () {
                $("#eb-notices").empty();
            });
        });
        $(document).on("click", ".notice-dismiss", function () {
            $("#eb-notices").empty();
        });
        $("#eb_send_test_email").click(function (e) {
            e.preventDefault();
            $('.response-box').empty();
            $('.load-response').show();
            var mailTo = $("#eb_test_email_add_txt").val();
            var subject = $("#eb_email_subject").val();
            var security = $("#eb_send_testmail_sec_filed").val();
            var message = tinyMCE.get("eb_emailtmpl_editor").getContent();
            $("#eb-lading-parent").show();
            $.ajax({
                type: "post",
                url: ajaxurl,
                data: {action: "wdm_eb_send_test_email", mail_to: mailTo, subject: subject, content: message, security: security},
                error: function (error) {
                    $('.load-response').hide();
                    ohSnap('<p>' + eb_admin_js_object.msg_mail_delivery_fail + '</p>', 'error');
                    $("#eb-lading-parent").hide();
                },
                success: function (response) {
                    if (response['success']) {
                        if (response["data"] == "OK") {
                            ohSnap('<p>' + eb_admin_js_object.msg_test_mail_sent_to + mailTo + '</p>', 'success');
                        } else {
                            ohSnap('<p>' + eb_admin_js_object.msg_mail_delivery_fail + '</p>', 'error');
                        }
                    } else {
                        ohSnap('<p>' + eb_admin_js_object.msg_mail_delivery_fail + '</p>', 'error');
                    }
                    $('.load-response').hide();
                    $("#eb-lading-parent").hide();
                }
            });
        });

        $("#eb_email_reset_template").click(function (e) {
            e.preventDefault();
            var tmplName = $("#current_selected_email_tmpl_key").val();
            var tmplSub = $("#current-tmpl-name").val();
            $("#eb-lading-parent").show();
            $.ajax({
                type: "post",
                url: ajaxurl,
                data: {
                    action: "wdm_eb_email_tmpl_restore_content",
                    tmpl_name: tmplName,
                },
                error: function (error) {
                    $("#eb-lading-parent").hide();
                },
                success: function (response) {
                    if (response["success"] == true) {
                        getTamplateContent(tmplName, tmplSub);
                    } else {
                        alert("Template is identical, did not restore.");
                    }
                    $("#eb-lading-parent").hide();
                }
            });
        });



        $(".link-unlink").click(function (e) {
            e.preventDefault();
            var userid = $(this).parent().attr("id");
            var linkuser = $(this).attr("id");
            /*var currDiv=$(this);*/
            linkuser = linkuser.substr(linkuser.indexOf("-") + 1);
            var str = linkuser;
            linkuser = 0;
            if (str == "link") {
                linkuser = 1;
                var strCheck = "unlink";
            } else {
                var strCheck = "link";
            }
            $("#moodleLinkUnlinkUserNotices").css("display", "none");
            $("#eb-lading-parent").show();
            $.ajax({
                type: "post",
                url: ajaxurl,
                data: {action: "moodleLinkUnlinkUser", user_id: userid, link_user: linkuser},
                error: function (error) {
                    $("#moodleLinkUnlinkUserNotices").css("display", "block");
                    $("#moodleLinkUnlinkUserNotices").removeClass("updated");
                    $("#moodleLinkUnlinkUserNotices").addClass("notice notice-error");
                    if (str == "link")
                    {
                        $("#moodleLinkUnlinkUserNotices").children().html(result["msg"]);
                    } else {
                        $("#moodleLinkUnlinkUserNotices").children().html(result["msg"]);
                    }
                    $("#eb-lading-parent").hide();
                },
                success: function (response) {
                    var result = $.parseJSON(response);
                    if (result["code"] == ("success")) {
                        $("#moodleLinkUnlinkUserNotices").addClass("updated");
                        $("#moodleLinkUnlinkUserNotices").css("display", "block");
                        $("#moodleLinkUnlinkUserNotices").children().html(result['msg']);
                        $("#" + userid + "-" + str).css("display", "none");
                        $("#" + userid + "-" + strCheck).css("display", "block");
                    } else {
                        $("#moodleLinkUnlinkUserNotices").css("display", "block");
                        $("#moodleLinkUnlinkUserNotices").removeClass("updated");
                        $("#moodleLinkUnlinkUserNotices").addClass("notice notice-error");
                        if (response.includes("LinkError")) {
                            $("#moodleLinkUnlinkUserNotices").children().html(response["msg"]);
                        } else {
                            if (str == "link")
                            {

console.log(eb_admin_js_object.msg_error_link_user);

                                $("#moodleLinkUnlinkUserNotices").children().html(eb_admin_js_object.msg_error_link_user);
                            } else {
                                $("#moodleLinkUnlinkUserNotices").children().html(eb_admin_js_object.msg_error_unlink_user);
                            }
                        }
                    }
                    $("#eb-lading-parent").hide();
                }
            });

        });



/*************** from 1.2.4  ********************/
        /**
         * Order page JS
         *
         */
        $("#eb_ord_refund_amt").blur(function () {
            $(this).val(Number($(this).val()).toFixed(2));
            var rfndAmt = $(this).val();
            if (rfndAmt == "NaN") {
                rfndAmt = "0.00";
                $(this).val(rfndAmt);
            }
            $("#eb-ord-refund-amt-btn-txt").text(rfndAmt);
        });

        /**
         * Refund order click event handler
         */
        $("#eb_order_refund_btn").click(function () {
            var orderId = getUrlParameter("post");
            var resp = confirm(eb_admin_js_object.msg_confirm_refund + orderId);
            if (resp == true) {
                sendRefundRequest(orderId);
            }
        });

/********  recommended courses settings in general settings and course edit settings   ************/


        function dfaultRecommendedSectionGeneralSettings(dfaultSection, dropdownDiv, selectDropDown, displayType)
        {
            if ($(dfaultSection).prop("checked") == true) {
                $(selectDropDown).val([]);
                dropdownDiv.css("display", "none");
            } else {
                dropdownDiv.css("display", displayType);
            }
        }


        function recommendedCourseSectionGeneralSettings(enbleSection, dfaultSection, checkboxDiv, dropdownDiv, selectDropDown, displayType)
        {
            if ($(enbleSection).prop("checked") == true) {
                checkboxDiv.css("display", displayType);
                dfaultRecommendedSectionGeneralSettings(dfaultSection, dropdownDiv, selectDropDown, displayType);
            } else {
                checkboxDiv.css("display", "none");
                dropdownDiv.css("display", "none");
            }
        }


        $("#eb_enable_recmnd_courses").click(function(){
            recommendedCourseSectionGeneralSettings("#eb_enable_recmnd_courses", "#eb_show_default_recmnd_courses", $("#eb_show_default_recmnd_courses").closest("tr"),$("#eb_recmnd_courses").closest("tr"), "#eb_recmnd_courses", "table-row");
        });

        $("#eb_show_default_recmnd_courses").click(function(){
            dfaultRecommendedSectionGeneralSettings("#eb_show_default_recmnd_courses", $("#eb_recmnd_courses").closest("tr"), "#eb_recmnd_courses", "table-row");
        });

        if ($("#eb_show_default_recmnd_courses").length) {
            recommendedCourseSectionGeneralSettings("#eb_enable_recmnd_courses", "#eb_show_default_recmnd_courses", $("#eb_show_default_recmnd_courses").closest("tr"),$("#eb_recmnd_courses").closest("tr"), "#eb_recmnd_courses", "table-row") ;
        }

        $("#enable_recmnd_courses").click(function(){
            recommendedCourseSectionGeneralSettings("#enable_recmnd_courses", "#show_default_recmnd_course", $("#eb_course_show_default_recmnd_course"),$("#eb_course_enable_recmnd_courses_single_course"), "#enable_recmnd_courses_single_course", "block") ;
        });

        $("#show_default_recmnd_course").click(function(){
            dfaultRecommendedSectionGeneralSettings("#show_default_recmnd_course", $("#eb_course_enable_recmnd_courses_single_course"), "#enable_recmnd_courses_single_course", "block");
        });


        if ($("#show_default_recmnd_course").length) {
            recommendedCourseSectionGeneralSettings("#enable_recmnd_courses", "#show_default_recmnd_course", $("#eb_course_show_default_recmnd_course"),$("#eb_course_enable_recmnd_courses_single_course"), "#enable_recmnd_courses_single_course", "block") ;
        }


        $("#eb_recmnd_courses").select2({
            placeholder: "Select Course",
        });
        $("#enable_recmnd_courses_single_course").select2({
            placeholder: "Select Course",
            width: 'auto'
        });



/*******************  END   *********************/

    });

    $(document).one("click", ".notice-dismiss", function () {
        $("#eb-notices").empty();
    });

    function getTamplateContent(tmplId, name) {
        $.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                action: "wdm_eb_get_email_template",
                tmpl_name: tmplId
            },
            error: function (error) {
                alert(eb_admin_js_object.msg_tpl_not_found);
                $("#eb-lading-parent").hide();
            },
            success: function (response) {
                setTemplateData(response, name, tmplId);
                $(".eb-emailtmpl-list-item").removeClass("eb-emailtmpl-active");
                $("#" + tmplId).addClass("eb-emailtmpl-active");
                $("#eb-lading-parent").hide();
                $("#current-tmpl-name").val(response['subject']);
            }
        });
    }
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
            if (response['notify_allow'] == "ON") {
                $("#eb_email_notification_on").attr('checked', true)
            } else {
                $("#eb_email_notification_on").attr('checked', false)
            }
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

    function sendRefundRequest(orderId) {
        var refAmt = $("#eb_ord_refund_amt").val();
        var refNote = $("#eb_order_refund_note").val();
        var isUneroll = "";
        var nonce=$("#eb_order_refund_nons").val();
        if ($("#eb_order_meta_unenroll_user").prop("checked")) {
            isUneroll = "ON";
        }

        $("#eb-lading-parent").show();
        $.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                action: "wdm_eb_order_refund",
                eb_ord_refund_amt: refAmt,
                eb_order_refund_note: refNote,
                eb_order_meta_unenroll_user: isUneroll,
                eb_order_id: orderId,
                order_nonce: eb_admin_js_object.eb_order_refund_nonce
            },
            error: function (error) {
                $("#moodleLinkUnlinkUserNotices").css("display", "block");
                $("#moodleLinkUnlinkUserNotices").removeClass("updated");
                $("#moodleLinkUnlinkUserNotices").addClass("notice notice-error");
                $("#moodleLinkUnlinkUserNotices").children().html(eb_admin_js_object.msg_refund_failed);
                $('html, body').animate({scrollTop: 0}, "fast");
                $("#eb-lading-parent").hide();
            },
            success: function (response) {
                if (response.success == true) {
                    $("#eb-lading-parent").hide();
                    location.reload();
                } else {
                    $("#moodleLinkUnlinkUserNotices").css("display", "block");
                    $("#moodleLinkUnlinkUserNotices").removeClass("updated");
                    $("#moodleLinkUnlinkUserNotices").addClass("notice notice-error");
                    $("#moodleLinkUnlinkUserNotices").children().html(response['data']);
                    $('html, body').animate({scrollTop: 0}, "fast");
                }
                $("#eb-lading-parent").hide();
            }
        });

    }

    function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    }

/*JS for Order page*/
    $(document).ready(function () {
        $('#eb_order_username').select2();
        $('#eb_order_course').select2();


/*        $('.eb_table_row div input[name$=license_activate]').click(function(){
            event.preventDefault();
            var submitButton = $(this);

            // $(".eb_table_cell_1").dialog();
            $('<div />').html(eb_admin_js_object.edwiser_terms_content).dialog({
                title: eb_admin_js_object.edwiser_terms_title,
                modal: true,
                resizable: true,
                width: 500,
                dialogClass: 'eb_admin_terms_dialog',
                buttons: [
                    {
                        text: "Agree",
                        "class": 'eb_terms_button_agree',
                        click: function() {
                            $(this).dialog("close");
                            submitButton.click();
                        }
                    },
                    {
                        text: "Disagree",
                        "class": 'eb_terms_button_disagree',
                        click: function() {
                             $(this).dialog("close");
                        }
                    }
                ],
            });
        });
*/



        /*$(window).scroll(function(){
            // This is then function used to detect if the element is scrolled into view
            function elementScrolled(elem)
            {
                var docViewTop = $(window).scrollTop();
                var docViewBottom = docViewTop + $(window).height();

                var elemTop = $(elem).offset().top;
                var elemBottom = elemTop + $(elem).height();


                return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
            }




            // This is where we use the function to detect if ".box2" is scrolled into view, and when it is add the class ".animated" to the <p> child element
            var elementsArray = [".eb-premium-extension-woo-int", ".eb-premium-extension-sso", ".eb-premium-extension-selective-synch"];
            elementsArray.forEach(function(item) {
                if ($(".eb-premium-extension-woo-int").length && $(".eb-premium-extension-sso").length && $(".eb-premium-extension-selective-synch").length) {

                    if(elementScrolled(item)) {
                        $(item).animate(
                        {
                        opacity: 1
                        }, 1000);
                    }
                }
            });

        });*/



        if ($(".eb-nav-tab-wrapper .nav-tab:last-child").hasClass("nav-tab-active")) {
            $(".eb-nav-tab-wrapper .nav-tab:last-child").css("color", "#2e9aa6");
        } else {
            $(".eb-nav-tab-wrapper .nav-tab:last-child").css("background-color", "#2e9aa6");
            $(".eb-nav-tab-wrapper .nav-tab:last-child").css("color", "white");
        }


        /****  TO hide eb update notice   ***/
        $(".eb_update_notice_hide").click(function(){
            var parent = $(this).parent().parent();
            parent.css("display", "none");
        });

        $(".eb_admin_discount_notice_hide").click(function(){
            var parent = $(this).parent().parent();
            parent.css("display", "none");
        });


        $(".eb_admin_feedback_dismiss_notice_message").click(function(){
            var parent = $(this).parent();
            parent.css("display", "none");
        });




        // $( ".eb-setting-help-accordion" ).accordion();



        /*--------------------------------
         * Sidebar
         *---------------------------------*/
        $('.eb_settings_help_btn_wrap .eb_open_btn').click(function(event){
            event.preventDefault();
            $(".eb_setting_help_pop_up").css('width', '250px');
            // $("main").css('margin-left', '250px');
        });


        $('.eb_setting_help_pop_up .closebtn').click(function(event){
            $(".eb_setting_help_pop_up").css('width', "0");
            // document.getElementById("main").style.marginLeft= "0";
        });

        // $('.eb_settings_rate_btn_wrap .eb_open_btn').click(function(event){
        //     event.preventDefault();
        //     // $(".eb_setting_rate_pop_up").css('width', '250px');
        //     // $("main").css('margin-left', '250px');
        // });

        // $('.eb_setting_rate_pop_up .closebtn').click(function(event){
        //     $(".eb_setting_rate_pop_up").css('width', "0");
        //     // document.getElementById("main").style.marginLeft= "0";
        // });

    });
/*JS for Order page end*/



})(jQuery);
