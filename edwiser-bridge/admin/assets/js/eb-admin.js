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

    $(window).on("load", function () {
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
                $(this).css({ backgroundColor: ui.color.toString() });
            },
            hide: true,
            border: true
        }).each(function () {
            $(this).css({ backgroundColor: jQuery(this).val() });
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
                        'admin_nonce': eb_admin_js_object.admin_nonce,
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

        //error log manager
        $('.eb-error-log-view').click(function(){
            var id = jQuery(this).data('log-id');
            var row = jQuery(this).parents('tr');
            //hide .eb-view-eye and show .load-response 
            $('.eb-view-eye-'+id).hide();
            $('.load-response-'+id).show();
            
            $.ajax({
                method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'wdm_eb_get_log_data',
                    'key': id,
                    'admin_nonce': eb_admin_js_object.admin_nonce,
                },
                success: function (response) {
                    console.log(response);
                    var log = response.data;
                    var dialogBox = $(document.createElement('div'));
                    dialogBox.attr('id', 'eb-error-log-dialog');
                    dialogBox.attr('title', log.data.message);
                    
                    var heading = $(document.createElement('h3'));
                    heading.html('Status: '+log.status);

                    var time = $(document.createElement('p')).html('Time : '+log.time);
                    var user = $(document.createElement('p')).html('User : '+log.data.user);
                    var rcode = $(document.createElement('p')).html('Error Code : '+log.data.responsecode);
                    var rmsg = $(document.createElement('p')).html('Response Message : '+log.data.message);
                    var viewMore = $(document.createElement('a')).html('View More...');
                    viewMore.attr('href', '#');
                    viewMore.attr('id', 'eb-dialog-view-more'+id);
                    viewMore.attr('class', 'eb-dialog-view-more'+id);

                    var viewMoreDiv = $(document.createElement('div')).addClass('eb-view-more eb-view-more'+id);
                    var urlData = $(document.createElement('p')).html('URL : '+log.data.url);
                    // explode backtrace with , and then add <br> after each line
                    var backtrace = log.data.backtrace;
                    var backtraceHtml = '';
                    for(var i=0; i<backtrace.length; i++){
                        backtraceHtml += backtrace[i]+'<br>';
                    }
                    var backtrace = $(document.createElement('p')).html('Backtrace : '+backtraceHtml);
                    viewMoreDiv.append(urlData);
                    viewMoreDiv.append(backtrace);

                    dialogBox.append(heading);
                    dialogBox.append(time);
                    dialogBox.append(user);
                    dialogBox.append(rcode);
                    dialogBox.append(rmsg);
                    
                    
                    if(log.data.debuginfo){
                        var debug = $(document.createElement('p')).html('Debug Info: '+log.data.debuginfo);
                        dialogBox.append(debug);
                    }

                    dialogBox.append(viewMore);
                    dialogBox.append(viewMoreDiv);


                    dialogBox.dialog({
                        modal: true,
                        height: 'auto',
                        minWidth: 500,
                        buttons: [
                            {
                                text: "Mark Resolved",
                                click: function () {
                                    $.ajax({
                                        method: "post",
                                        url: eb_admin_js_object.ajaxurl,
                                        dataType: "json",
                                        data: {
                                            'action': 'wdm_eb_mark_log_resolved',
                                            'key': id,
                                            'admin_nonce': eb_admin_js_object.admin_nonce,
                                        },
                                        success: function (response) {
                                            $(row).find('.column-status').html('RESOLVED');
                                        }
                                    });
                                    $(this).dialog("close");
                                }
                            },
                            {
                                text: "Report Issue",
                                title: "Send this issue to Edwiser Support",
                                click: function () {
                                    //opew dialog box and ask for email
                                    var emailDialogBox = $(document.createElement('div'));
                                    emailDialogBox.attr('id', 'eb-error-log-dialog');
                                    emailDialogBox.attr('title', 'Send this issue to Edwiser Support');
                                    var email = $(document.createElement('input')).attr('type', 'email').attr('placeholder', 'Enter Admin Email');
                                    email.css('width', '100%');
                                    email.css('margin-top', '15px');
                                    var info = $(document.createElement('p')).html('*This email will be used for further communication from Edwiser Support');

                                    emailDialogBox.append(email);
                                    emailDialogBox.append(info);
                                    emailDialogBox.dialog({
                                        modal: true,
                                        height: 'auto',
                                        minWidth:400,
                                        buttons: [
                                            {
                                                text: "Cancel",
                                                click: function () {
                                                    $(this).dialog("close");
                                                }
                                            },
                                            {
                                                text: "Send",
                                                click: function () {
                                                    var email = $(this).find('input').val();
                                                    // check if this is an valid email address
                                                    if(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)){
                                                        // valid email
                                                    } else {
                                                        alert('Please enter a valid email address');
                                                        return;
                                                    }
                                                    $.ajax({
                                                        method: "post",
                                                        url: eb_admin_js_object.ajaxurl,
                                                        dataType: "json",
                                                        data: {
                                                            'action': 'send_log_to_support',
                                                            'key': id,
                                                            'email': email,
                                                            'admin_nonce': eb_admin_js_object.admin_nonce,
                                                        },
                                                        success: function (response) {
                                                            if(response.success){
                                                                $(row).find('.column-status').html('SENT TO SUPPORT');
                                                            }
                                                        }
                                                    });
                                                    $(this).dialog("close");
                                                }
                                            }
                                        ]
                                    });                                        
                                    $(this).dialog("close");
                                }
                            }
                        ]
                    });
                    $('.eb-view-eye-'+id).show();
                    $('.eb-dialog-view-more'+id).click(function(){
                        $('.eb-view-more'+id).toggle();
                    });
                    $('.load-response-'+id).hide();
                }
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

        function ohSnap(text, type, status) {
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

        function ohSnapX(element) {
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



        /* ---------------------------
         * Manage Enrollment page js
         * ------------------------------*/
        $(document).on('click', '#eb_manage_enroll_dt_search', function (event) {
            event.preventDefault();
            $('#eb_manage_enroll_export').val('');
            var parent = $(this).closest('form');
            parent.attr('action', '');
            parent.submit();
        });

        $(document).on('focusout', '#enrollment_from_date', function (event) {
            var value = $(this).val();

            if (is_valid_date(value)) {
                // $('#enrollment_to_date').prop('disabled ', false);
                document.getElementById("enrollment_to_date").disabled = false;
            } else {
                document.getElementById("enrollment_to_date").disabled = true;
            }

        });






        function is_valid_date(s) {
            var bits = s.split('-');

            // var d = new Date(bits[2], bits[1] - 1, bits[0]);
            var d = new Date(bits[0], bits[1] - 1, bits[2]);

            return d && (d.getMonth() + 1) == bits[1];
        }


        /*******  END  ******/


        $(document).on('click', '.eb_test_connection_log_open', function (event) {
            $('.eb_test_connection_log_open').addClass('eb_test_connection_log_close');
            $('.eb_test_connection_log_close').removeClass('eb_test_connection_log_open');
            $(".eb_test_connection_log").slideDown();
        });

        $(document).on('click', '.eb_test_connection_log_close', function (event) {
            $('.eb_test_connection_log_close').addClass('eb_test_connection_log_open');
            $('.eb_test_connection_log_open').removeClass('eb_test_connection_log_close');
            $(".eb_test_connection_log").slideUp();
        });


        /**
         * Reload the Moodle course enrollment.
         */
         $('.eb-enable-manual-enrolment').click(function(){

            // Create loader.
            var loader_html = '<span class="eb-load-response"><img src="' + eb_admin_js_object.plugin_url + 'images/loader.gif" height="20" width="20" /></span>';
            var current = $(this);
            var course_id = $(this).data('courseid');

            current.append(loader_html);

            $.ajax({
                method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'enable_course_enrollment_method',
                    'course_id': course_id,
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    current.find('.eb-load-response').remove();
                    //prepare response for user
                    if (response.success == 1) {
                        $("#moodleLinkUnlinkUserNotices").css("display", "none");
                        // Add succes icon and also remove.
                        current.parent().html('<span style="color:green;font-size:30px;" class="dashicons dashicons-yes"></span>');

                    } else {
                        $("#moodleLinkUnlinkUserNotices").css("display", "block");
                        $("#moodleLinkUnlinkUserNotices").removeClass("updated");
                        $("#moodleLinkUnlinkUserNotices").addClass("notice notice-error");
                        $("#moodleLinkUnlinkUserNotices").children().html(response.data["message"]);
                    }
                }
            });

         });


        /**
         * creates ajax request to initiate test connection request
         * display a response to user on process completion
         */
        $('#eb_test_enrollment_button').click(function () {
            // $('#eb_test_enrollment_button').prop('disabled', true);
            $('.enroll-progress').show();
            $('.response-box').empty();
            $('.test-enrollment-heading').empty();
            $('#eb_test_enrollment_button').attr('disabled', 'disabled');
            //remove all active classes
            $('.enroll-progress').find('.active').removeClass('active');
            $('.enroll-progress').find('.in-progress').removeClass('in-progress');
            $('#progress_settings').addClass('in-progress');
            $('.eb_test_enrollment_response').empty(); // empty the response
            $('.eb_test_enrollment_response').html('<div class="alert alert-loading">'+eb_admin_js_object.checking_mandatory_settings+'</div>');

            var course_id = $('#eb_test_enrollment_course').val();
            
            if(course_id == ''){
                $('.eb_test_enrollment_response').empty(); // empty the response
                ohSnap( eb_admin_js_object.please_select_course, 'error');
                $('.enroll-progress').hide();
                $('#eb_test_enrollment_button').removeAttr('disabled');
                return;
            }
            var course_name = $('#eb_test_enrollment_course option:selected').text();
            $('.test-enrollment-heading').html(eb_admin_js_object.testing_enrollment_process + course_name);
            var $this = $(this);
        
            $.ajax({
                method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'check_mandatory_settings',
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    $('.load-response').hide();
                    //prepare response for user
                    $('.alert-loading').remove();
                    if (response.status == 'success') {
                        $('.eb_test_enrollment_response').append(response.message);
                        setTimeout(function () {
                            check_manual_enrollment(course_id);
                        }, 1000);
                    } else {
                        $('#eb_test_enrollment_button').removeAttr('disabled');
                        if(response.html){
                            response.message = response.message + response.html;
                        }
                        $('.eb_test_enrollment_response').append(response.message);
                    }
                }
            });
        });

        function check_course_options( course_id ){
            $('.eb_test_enrollment_response').append('<div class="alert alert-loading">'+eb_admin_js_object.checking_course_options+'</div>');
            $.ajax({
               method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'check_course_options',
                    'course_id': course_id,
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    $('.load-response').hide();
                    $('.alert-loading').remove();
                    //prepare response for user
                    if (response.status == 'success') {
                        $('.eb_test_enrollment_response').append(response.message);
                        $('#progress_settings').removeClass('in-progress');
                        $('#progress_settings').addClass('active');
                        setTimeout(function () {
                            create_dummy_user(course_id);
                        }, 1000);
                    } else {
                        $('#eb_test_enrollment_button').removeAttr('disabled');
                        $('.eb_test_enrollment_response').append(response.message + response.html);
                    }
                }
            });
        }

        function check_manual_enrollment( course_id ){
            $('.eb_test_enrollment_response').append('<div class="alert alert-loading">'+eb_admin_js_object.checking_manual_enrollment+'</div>');
            $.ajax({
               method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'check_manual_enrollment',
                    'course_id': course_id,
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    $('.load-response').hide();
                    $('.alert-loading').remove();
                    //prepare response for user
                    if (response.status == 'success') {
                        $('.eb_test_enrollment_response').append(response.message);
                        setTimeout(function () {
                            check_course_options(course_id);
                        }, 1000);
                    } else {
                        $('#eb_test_enrollment_button').removeAttr('disabled');
                        $('.eb_test_enrollment_response').append(response.message + response.html);
                    }
                }
            });
        }

        function create_dummy_user( course_id ){
            $('#progress_user').addClass('in-progress');
            $('.eb_test_enrollment_response').append('<div class="alert alert-loading">'+eb_admin_js_object.creating_dummy_user+'</div>');
            $.ajax({
               method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'create_dummy_user',
                    'course_id': course_id,
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    $('.load-response').hide();
                    $('.alert-loading').remove();
                    //prepare response for user
                    if (response.status == 'success') {
                        $('.eb_test_enrollment_response').append(response.wp_message + response.moodle_message);
                        $('#progress_user').removeClass('in-progress');
                        $('#progress_user').addClass('active');
                        setTimeout(function () {
                            enroll_dummy_user(course_id);
                        }, 1000);
                    } else {
                        $('#eb_test_enrollment_button').removeAttr('disabled');
                        if(response.html){
                            response.moodle_message = response.moodle_message + response.html;
                        }
                        $('.eb_test_enrollment_response').append(response.wp_message + response.moodle_message);
                    }
                }
            });
        }

        function enroll_dummy_user( course_id ){
            $('#progress_enroll').addClass('in-progress');
            $('.eb_test_enrollment_response').append('<div class="alert alert-loading">' + eb_admin_js_object.enrolling_user + '</div>');
            $.ajax({
               method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'enroll_dummy_user',
                    'course_id': course_id,
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    $('.load-response').hide();
                    $('.alert-loading').remove();
                    //prepare response for user
                    if (response.status == 'success') {
                        $('#progress_enroll').removeClass('in-progress');
                        $('#progress_enroll').addClass('active');
                        $('.eb_test_enrollment_response').append(response.enroll_message);
                        $('#progress_finish').addClass('active');
                        $('#eb_test_enrollment_button').removeAttr('disabled');
                    } else {
                        $('#eb_test_enrollment_button').removeAttr('disabled');
                        if(response.html){
                            response.enroll_message = response.enroll_message + response.html;
                        }
                        $('.eb_test_enrollment_response').append(response.enroll_message);
                    }
                }
            });
        }

        $('.eb_test_enrollment_response').on('click', '#btn_set_mandatory', function () {
            $('.alert-error').remove();
            $('.eb_test_enrollment_response').find('#btn_set_mandatory').remove();
            $('#eb_test_enrollment_button').attr('disabled', 'disabled');
            $('.eb_test_enrollment_response').append('<div class="alert alert-loading">'+eb_admin_js_object.updating_mandatory_settings+'</div>');
            var course_id = $('#eb_test_enrollment_course').val();
            $.ajax({
               method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'enable_mandatory_settings',
                    'course_id': course_id,
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    $('.load-response').hide();
                    $('.alert-loading').remove();
                    $('.alert-error').remove();
                    $('#btn_set_mandatory').remove();
                    //prepare response for user
                    if (response.status == 'success') {
                        $('.eb_test_enrollment_response').append(response.message);
                        setTimeout(function () {
                            check_manual_enrollment(course_id);
                        }, 1000);
                    } else {
                        $('#eb_test_enrollment_button').removeAttr('disabled');
                        if(response.html){
                            response.message = response.message + response.html;
                        }
                        $('.eb_test_enrollment_response').append(response.message);
                    }
                }
            });
        });


        $('.eb_test_enrollment_response').on('click','#btn_set_manual_enrol', function () {
            $('.alert-error').remove();
            $('.eb_test_enrollment_response').find('#btn_set_manual_enrol').remove();
            $('#eb_test_enrollment_button').attr('disabled', 'disabled');
            $('.eb_test_enrollment_response').append('<div class="alert alert-loading">'+eb_admin_js_object.enabling_manual_enrollment+'</div>');
            var course_id = $('#eb_test_enrollment_course').val();
            
            $.ajax({
               method: "post",
                url: eb_admin_js_object.ajaxurl,
                dataType: "json",
                data: {
                    'action': 'enable_manual_enrollment',
                    'course_id': course_id,
                    '_wpnonce_field': eb_admin_js_object.nonce,
                },
                success: function (response) {
                    $('.load-response').hide();
                    $('.alert-loading').remove();
                    //prepare response for user
                    if (response.status == 'success') {
                        $('.eb_test_enrollment_response').append(response.message);
                        setTimeout(function () {
                            check_course_options(course_id);
                        }, 1000);
                    } else {
                        $('#eb_test_enrollment_button').removeAttr('disabled');
                        if(response.html){
                            response.message += response.html;
                        }
                        $('.eb_test_enrollment_response').append(response.message);
                    }
                }
            });
        });

        $('.eb_test_enrollment_response').on('click', '#btn_set_course_price_type', function () {
            $('.eb_test_enrollment_response').find('#btn_set_course_price_type').remove();
            $('#progress_settings').removeClass('in-progress');
            $('#progress_settings').addClass('active');
            $('#eb_test_enrollment_button').attr('disabled', 'disabled');
            var course_id = $('#eb_test_enrollment_course').val();
            create_dummy_user(course_id);
        });

        /**
         * creates ajax request to initiate test connection request
         * display a response to user on process completion
         */
        $('#eb_test_connection_button').click(function () {
            //get selected options
            //
            $('.response-box').empty(); // empty the response
            $('.eb_test_connection_response').empty(); // empty the response
            
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
                        if(response.warnings){
                            // add ohSnap warning message for each warning
                            $.each(response.warnings, function (index, value) {
                                ohSnap(value, 'warning', 0);
                            });
                        }
                    } else {
                        // ohSnap(response.response_message, 'error', 0);
                        $('.eb_test_connection_response').html(response.response_message);
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
                        if (sync_options['eb_synchronize_previous'] == 1 || sync_options['eb_synchronize_draft'] !== null) {
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
                var cb_value = (this.checked ? 1 : 0);
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
            if ($("#eb_synchronize_user_courses").prop('checked') == true) {
                userSyncAjax($this, sync_options, offset, progressWidth);
            }
            // new Ajax call function for user link to moodle synchronization.
            if ($("#eb_link_users_to_moodle").prop('checked') == true) {
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
                $('#eb_course_course_expiry_action').show();
            } else {
                $('#eb_course_course_expiry_action').hide();
                $('#eb_course_num_days_course_access').hide();
                $('#num_days_course_access').val("");
                $('#course_expiry_action').val("do-nothing");
            }
        });
        $('#course_price_type').change();
        $("#course_expirey").change();

        /* Profile page js */
        $(document).on('keyup', '#eb-search-all-courses', function (event) {
            event.preventDefault();
            var course = $(this).val();
            var all_courses = $('#eb-all-courses-list').children();
            var options = $('#eb-all-courses').children();
            // remove all otpions
            options.remove();
            // add options
            all_courses.each(function () {
                var course_name = $(this).text();
                if (course_name.toLowerCase().indexOf(course.toLowerCase()) >= 0) {
                    $('#eb-all-courses').append($(this).clone());
                }
            });
        });

        $(document).on('keyup', '#eb-search-enrolled-courses', function (event) {
            event.preventDefault();
            var course = $(this).val();
            var enrolled_courses = $('#eb-enrolled-courses-list').children();
            var options = $('#eb-enrolled-courses').children();
            // remove enrolled otpions
            options.remove();
            // add options
            enrolled_courses.each(function () {
                var course_name = $(this).text();
                if (course_name.toLowerCase().indexOf(course.toLowerCase()) >= 0) {
                    $('#eb-enrolled-courses').append($(this).clone());
                }
            });
        });

        $(document).on('click', '#eb-profile-course-add', function (event) {
            event.preventDefault();
            var selected = $('#eb-all-courses').children(':selected');
            // check if duplicate
            var duplicate = false;
            $('#eb-enrolled-courses').children().each(function () {
                if ($(this).val() == selected.val()) {
                    duplicate = true;
                }
            });
            if (duplicate) {
                return;
            }
            $('#eb-enrolled-courses').append(selected.clone());
            selected.remove();
            // get data
            // for each selected option
            
            var enrolled_courses = $('#eb_enroll_courses').val();
            enrolled_courses = JSON.parse(enrolled_courses);
            selected.each(function () {
                var course_id = $(this).val();
                var course_name = $(this).text();
                var option = '<option value="' + course_id + '">' + course_name + '</option>';
                $('#eb-enrolled-courses-list').append(option);
                // remove from datalist
                $('#eb-all-courses-list').find('option[value="' + course_id + '"]').remove();

                // check if array then add the course id
                if (Array.isArray(enrolled_courses)) {
                    // add int value 
                    enrolled_courses.push(parseInt(course_id));
                } else {
                    enrolled_courses = [];
                    enrolled_courses.push(parseInt(course_id));
                }
            });
            $('#eb_enroll_courses').val(JSON.stringify(enrolled_courses));
        });
        $(document).on('click', '#eb-profile-course-remove', function (event) {
            event.preventDefault();
            var selected = $('#eb-enrolled-courses').children(':selected');
            $('#eb-all-courses').append(selected.clone());
            selected.remove();
            // get data
            var enrolled_courses = $('#eb_enroll_courses').val();
            enrolled_courses = JSON.parse(enrolled_courses);
            selected.each(function () {
                var course_id = $(this).val();
                var course_name = $(this).text();
                var option = '<option value="' + course_id + '">' + course_name + '</option>';
                $('#eb-all-courses-list').append(option);
                // remove from datalist
                $('#eb-enrolled-courses-list').find('option[value="' + course_id + '"]').remove();

                // check if array then add the course id
                if (Array.isArray(enrolled_courses)) {
                    var index = enrolled_courses.indexOf(parseInt(course_id));
                    if (index > -1) {
                        enrolled_courses.splice(index, 1);
                    }
                }
            });
            $('#eb_enroll_courses').val(JSON.stringify(enrolled_courses));
        });
    });
    /* Function for user synchronization, this will have a ajax call which will run after completion of another(recursively) */
    function userSyncAjax($this, sync_options, offset, progressWidth) {
        $('.load-response').show();
        var response_message = '';
        var user_id_success = '';
        var user_id_error = '';
        if (!$('.response-box').is(":empty")) {
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
                            if (!$('.response-box').is(":empty")) {
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
        if (!$('.response-box').is(":empty")) {
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
                        if (!$('.response-box').is(":empty")) {
                            $('.linkresponse-box').css('margin-top', '3%');
                        }
                        $('.linkresponse-box').css('margin-left', '0px !important');
                        // linkUserResponseBox('<p class="linkerror">' + eb_admin_js_object.msg_user_sync_success + '</p>', 'success', 1);
                        if (typeof notLinkedusers !== 'undefined' && notLinkedusers.length > 0) {
                            var container = $('.linkresponse-box');
                            var html = '<span class="linkresponse-box-error">' + eb_admin_js_object.msg_unlink_users_list + '</span>';
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
    $(document).on('click', '.linkresponse-box a', function () {
        $("#unlinkerrorid-modal").show();
    });
    // Used to hide the response in popup for unlinked users to moodle functionality.
    $(document).on('click', '.unlinkerror-modal-close', function () {
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
        var html = '<div class="alert alert-' + type + '">' + users_count + ' / ' + wp_users_count + ' ' + eb_admin_js_object.msg_user_sync_success + '</div>';
        container.empty();
        container.append(html);
    }
    /* Function to show progress of link users to moodle functionality*/
    function showLinkedUsersProgress(linked_users_count = 0, unlinked_users_count = 0, type) {
        var container = $('.linkresponse-box');
        var html = '<div class="alert alert-' + type + '">' + linked_users_count + ' / ' + unlinked_users_count + ' ' + eb_admin_js_object.msg_user_link_to_moodle_success + '</div>';
        container.empty();
        container.append(html);
    }
    function setGetParameter(paramName, paramValue) {
        var url = window.location.href;
        var hash = location.hash;
        url = url.replace(hash, '');
        if (url.indexOf("?") >= 0) {
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

        // Usage tracking confirmation box.
        // $("#eb_usage_tracking").attr('readonly', true);
        $('#eb_usage_tracking').click(function (e) {
            if ($(this).is(':checked')) {
                $('#dialog-tnc').dialog({
                    minWidth: 500,
                    maxHeight: 450,
                    width: $(window).width() * 0.7,
                    modal: true,
                    closeOnEscape: true,
                    draggable: false,
                    title: "Edwiser Bridge Usage Tracking Terms and Conditions",
                    buttons: {
                        Accept: function () {
                            $(this).dialog("close");
                        },
                        Dicline: function () {
                            $(this).dialog("close");
                            $("#eb_usage_tracking").prop('checked', false);
                        }
                    },
                    create: function (event, ui) {
                        $(event.target).parent().css('position', 'fixed');
                    }
                });
            }
        });

        if (!$('#eb_enable_recaptcha').is(':checked')) {
            $('#eb_enable_recaptcha').closest('tbody').find('tr').not(':first').hide();
        }
        $('#eb_enable_recaptcha').click(function (e) {
            if ($(this).is(':checked')) {
                $('#eb_enable_recaptcha').closest('tbody').find('tr').show();
            } else {
                $('#eb_enable_recaptcha').closest('tbody').find('tr').not(':first').hide();
            }
        });
	    $('.wdm_eb_get_key_popup_btn').click(function (e) {
            e.preventDefault();

            $('#eb_get_license_key_dialog').dialog({
                minWidth: 500,
                maxHeight: 550,
                width: $(window).width() * 0.4,
                dialogClass: 'eb_get_license_key_dialog',
                modal: true,
                closeOnEscape: true,
                draggable: false,
                title: "Get License Key",
                buttons: [
                    {
                        text: 'Close',
                        class: "button",
                        click: function () {
                            $(this).dialog("close");
                        }
                    }
                ],
                create: function (event, ui) {
                    $(event.target).parent().css('position', 'fixed');
                }
            });
        });


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
            var header = $("#eb_bcc_email").val();
            var message = tinyMCE.get("eb_emailtmpl_editor").getContent();
            $("#eb-lading-parent").show();
            $.ajax({
                type: "post",
                url: ajaxurl,
                data: {
                    action: "wdm_eb_send_test_email",
                    mail_to: mailTo,
                    headers: "Bcc:" + header,
                    subject: subject,
                    content: message,
                    security: security
                },
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
                    admin_nonce: eb_admin_js_object.admin_nonce,
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
                data: {
                    action: "moodleLinkUnlinkUser",
                    user_id: userid,
                    link_user: linkuser,
                    admin_nonce: eb_admin_js_object.admin_nonce,
                },
                error: function (error) {
                    var result = $.parseJSON(response);
                    $("#moodleLinkUnlinkUserNotices").css("display", "block");
                    $("#moodleLinkUnlinkUserNotices").removeClass("updated");
                    $("#moodleLinkUnlinkUserNotices").addClass("notice notice-error");
                    if (str == "link") {
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
                            if (str == "link") {
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


        function dfaultRecommendedSectionGeneralSettings(dfaultSection, dropdownDiv, selectDropDown, displayType) {
            if ($(dfaultSection).prop("checked") == true) {
                $(selectDropDown).val([]);
                dropdownDiv.css("display", "none");
            } else {
                dropdownDiv.css("display", displayType);
            }
        }


        function recommendedCourseSectionGeneralSettings(enbleSection, dfaultSection, checkboxDiv, dropdownDiv, selectDropDown, displayType) {
            if ($(enbleSection).prop("checked") == true) {
                checkboxDiv.css("display", displayType);
                dfaultRecommendedSectionGeneralSettings(dfaultSection, dropdownDiv, selectDropDown, displayType);
            } else {
                checkboxDiv.css("display", "none");
                dropdownDiv.css("display", "none");
            }
        }


        $("#eb_enable_recmnd_courses").click(function () {
            recommendedCourseSectionGeneralSettings("#eb_enable_recmnd_courses", "#eb_show_default_recmnd_courses", $("#eb_show_default_recmnd_courses").closest("tr"), $("#eb_recmnd_courses").closest("tr"), "#eb_recmnd_courses", "table-row");
        });

        $("#eb_show_default_recmnd_courses").click(function () {
            dfaultRecommendedSectionGeneralSettings("#eb_show_default_recmnd_courses", $("#eb_recmnd_courses").closest("tr"), "#eb_recmnd_courses", "table-row");
        });

        if ($("#eb_show_default_recmnd_courses").length) {
            recommendedCourseSectionGeneralSettings("#eb_enable_recmnd_courses", "#eb_show_default_recmnd_courses", $("#eb_show_default_recmnd_courses").closest("tr"), $("#eb_recmnd_courses").closest("tr"), "#eb_recmnd_courses", "table-row");
        }

        $("#enable_recmnd_courses").click(function () {
            recommendedCourseSectionGeneralSettings("#enable_recmnd_courses", "#show_default_recmnd_course", $("#eb_course_show_default_recmnd_course"), $("#eb_course_enable_recmnd_courses_single_course"), "#enable_recmnd_courses_single_course", "block");
        });

        $("#show_default_recmnd_course").click(function () {
            dfaultRecommendedSectionGeneralSettings("#show_default_recmnd_course", $("#eb_course_enable_recmnd_courses_single_course"), "#enable_recmnd_courses_single_course", "block");
        });


        if ($("#show_default_recmnd_course").length) {
            recommendedCourseSectionGeneralSettings("#enable_recmnd_courses", "#show_default_recmnd_course", $("#eb_course_show_default_recmnd_course"), $("#eb_course_enable_recmnd_courses_single_course"), "#enable_recmnd_courses_single_course", "block");
        }


        $("#eb_recmnd_courses").select2({
            placeholder: "Select Course",
        });
        $("#enable_recmnd_courses_single_course").select2({
            placeholder: "Select Course",
            width: 'auto'
        });

        /**
         * Functionality to show hide the get license key button on licensing page start.
         */
        $('.wdm_key_in').bind("change paste keyup propertychange", function () {
            toggleLicenseButtons($(this));
        });
        $('.wdm_key_in').each(function () {
            toggleLicenseButtons($(this));
        });

        function toggleLicenseButtons(element) {
            if ($.trim(element.val()).length > 0) {
                element.parent().parent().find('.get_license_key').hide();
                element.parent().parent().find('.eb-activate-plugin').show();
                element.parent().parent().find('.activate_license').show();
                element.parent().parent().find('.install_plugin').show();
            } else {
                element.parent().parent().find('.get_license_key').show();
                element.parent().parent().find('.eb-activate-plugin').hide();
                element.parent().parent().find('.activate_license').hide();
                element.parent().parent().find('.install_plugin').hide();
            }
        }
        /**
         * Functionality to show hide the get license key button on licensing page end.
         */
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
                tmpl_name: tmplId,
                admin_nonce: eb_admin_js_object.admin_nonce
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
    function ohSnap(text, type) {
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
    function setTemplateData(response, name, tmplId) {
        try {
            response = $.parseJSON(response);
            $("#eb-email-template-name").text(name);
            $("#eb_email_from").val(response['from_email']);
            $("#eb_email_from_name").val(response['from_name']);
            $("#eb_email_subject").val(response['subject']);
            $("#eb_bcc_email").val(response['bcc_email']);


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
        }
    }

    function sendRefundRequest(orderId) {
        var refAmt = $("#eb_ord_refund_amt").val();
        var refNote = $("#eb_order_refund_note").val();
        var isUneroll = "";
        var nonce = $("#eb_order_refund_nons").val();
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
                $('html, body').animate({ scrollTop: 0 }, "fast");
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
                    $('html, body').animate({ scrollTop: 0 }, "fast");
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
        if ($(".eb-nav-tab-wrapper .nav-tab:last-child").hasClass("nav-tab-active")) {
            $(".eb-nav-tab-wrapper .nav-tab:last-child").css("color", "#2e9aa6");
        } else {
            $(".eb-nav-tab-wrapper .nav-tab:last-child").css("background-color", "#2e9aa6");
            $(".eb-nav-tab-wrapper .nav-tab:last-child").css("color", "white");
        }


        /****  TO hide eb update notice   ***/
        $(".eb_update_notice_hide").click(function () {
            var parent = $(this).parent().parent();
            parent.css("display", "none");
        });

        $(".eb_admin_pro_popup_hide").click(function () {
            var parent = $(this).parent().parent().parent();
            parent.css("display", "none");
        });

        $(".eb_admin_discount_notice_hide").click(function () {
            var parent = $(this).parent().parent();
            parent.css("display", "none");
        });


        $(".eb_admin_feedback_dismiss_notice_message").click(function () {
            var parent = $(this).parent();
            parent.css("display", "none");
        });

        $(".eb_admin_update_dismiss_notice_message").click(function () {
            var parent = $(this).parent();
            parent.css("display", "none");
        });




        // $( ".eb-setting-help-accordion" ).accordion();



        /*--------------------------------
         * Sidebar
         *---------------------------------*/
        $('.eb_settings_help_btn_wrap .eb_open_btn').click(function (event) {
            event.preventDefault();
            $(".eb_setting_help_pop_up").css('width', '250px');
            $(".eb_setting_help_pop_up").css('right', '0px');

            // $("main").css('margin-left', '250px');
        });


        $('.eb_setting_help_pop_up .closebtn').click(function (event) {
            $(".eb_setting_help_pop_up").css('width', "0");
            $(".eb_setting_help_pop_up").css('right', '-25px');
        });
    });
    /*JS for Order page end*/



})(jQuery);
