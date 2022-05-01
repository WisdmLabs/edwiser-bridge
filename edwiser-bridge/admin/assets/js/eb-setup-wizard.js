(function ($) {
    'use strict';

    $(document).ready(function () {


    // ----    ------
var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
    /* Toggle between adding and removing the "active" class,
    to highlight the button that controls the panel */
    this.classList.toggle("active");

    /* Toggle between hiding and showing the active panel */
    var panel = this.nextElementSibling;
    if (panel.style.display === "block") {
      panel.style.display = "none";
    } else {
      panel.style.display = "block";
    }
  });
}

    // ----   ------

    // ajax call to change the tab.
        /**
         * Reload the Moodle course enrollment.
         */
        $('.eb-setup-step-completed').click(function(){

        // Create loader.
        var current = $(this);
        var step = $(this).data('step');

        // current.append(loader_html);


        $.ajax({
            method: "post",
            url: eb_setup_wizard.ajax_url,
            dataType: "json",
            data: {
                'action': 'eb_setup_' + step,
                // 'course_id': course_id,
                // '_wpnonce_field': eb_admin_js_object.nonce,
            },
            success: function (response) {

                console.log('AAAAAAAAAA');
                console.log(response);


                current.find('.eb-load-response').remove();
                //prepare response for user
                if (response.success == 1) {
                    $('.eb-setup-content').html(response.data.content);

                } else {



                }
            }
        });

        });


        // // Clicking save continue
        // // 
        // // 
        // $(document).on('click', '.eb_set_up_save_and_continue', function (event) {


        //     // Create loader.
        //     var current = $(this);
        //     var current_step = $(this).data('step');
        //     var next_step = $(this).data('next-step');
        //     var is_next_sub_step = $(this).data('is-next-sub-step');
            
        //     // get current step.
        //     // get next step.
        //     // get data which will be saved.

        //     // Creating swicth case.
            
        //     var data = {};




        //     $.ajax({
        //         method: "post",
        //         url: eb_setup_wizard.ajax_url,
        //         dataType: "json",
        //         data: {
        //             'action': 'eb_setup_' + step,
        //             // 'course_id': course_id,
        //             // '_wpnonce_field': eb_admin_js_object.nonce,
        //         },
        //         success: function (response) {
    
        //             //prepare response for user
        //             if (response.success == 1) {
        //                 $('.eb-setup-content').html(response.data.content);

        //             } else {
    
        //             }
        //         }
        //     });
    
        // });




    // ajax xall to save data and get new tab at the same time.
        
            // Clicking save continue
        // 
        // 
        // $('.eb_setup_save_and_continue').click(function(){
        $(document).on('click', '.eb_setup_save_and_continue', function (event) {

            var $this = $(this);
            var current_step = $(this).data('step');
            var next_step = $(this).data('next-step');
            var is_next_sub_step = $(this).data('is-next-sub-step');



            // get current step.
            // get next step.
            // get data which will be saved.

            // Creating swicth case.
            
            var data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };

            switch ( current_step ) {
                case 'moodle_redirection':
                    // Get required data and create array
                    var mdl_url      = $('#eb_setup_test_conn_mdl_url').val();

                    data = { 'mdl_url' : mdl_url, 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };

                    break;

                case 'test_connection':
                    var mdl_url      = $('#eb_setup_test_conn_mdl_url').val();
                    var mdl_token    = $('#eb_setup_test_conn_token').val();
                    var mdl_lang_code = $('#eb_setup_test_conn_lang_code').val();

                    data = { 'mdl_url' : mdl_url, 'mdl_token' : mdl_token, 'mdl_lang_code': mdl_lang_code, 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };
                    
                    break;
            
                case 'course_sync':
                    // Course sync process.
                    // Call course sync callback and after completing the process, call this callback.

                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };



                    break;

                case 'user_sync':
                    // If user checkbox is clicked start user sync otherwise just procedd to next screen.
                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };
                    var sync_options = {};
                    // prepare sync options array
                    var sync_options = {eb_synchronize_user_courses: 1, eb_link_users_to_moodle: 1};
                    var offset = 0;
                    var progressWidth = 0;
                    var linkedUsers = 0;
                    var users_count = 0;
                    var queryLimit = 0;
                    var notLinkedusers = [];
                    userLinkSyncAjax($this, sync_options, offset, linkedUsers, users_count, queryLimit, notLinkedusers);

                    break;

                case 'free_recommended_settings':
                    // user account page selection and enable registration on user account
                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };

                    break;


                case 'pro_initialize':
                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };
                
                    break;


                case 'license':
                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };
                
                    break;


                case 'wp_plugins':
                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };
                
                    break;


                case 'mdl_plugins':
                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };
                
                    break;


                case 'sso':
                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };
                
                    break;


                case 'wi_products_sync':
                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };
                
                    break;


                case 'pro_settings':
                    data = { 'current_step' : current_step, 'next_step' : next_step, 'is_next_sub_step': is_next_sub_step };
                
                    break;


                default:
                    break;
            }



console.log( 'data ::: ' );
console.log( data );


                $.ajax({
                    method: "post",
                    url: eb_setup_wizard.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'eb_setup_save_and_continue',
                        // 'action': 'eb_setup_' + step,
                        'data': data,
                        // '_wpnonce_field': eb_admin_js_object.nonce,
                    },
                    success: function (response) {

                        //prepare response for user
                        if (response.success == 1) {
                            $('.eb-setup-content').html(response.data.content);

                        } else {
        
                        }
                    }
                });
    
            });



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
                    url: eb_setup_wizard.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'handleUserLinkToMoodle',
                        'sync_options': JSON.stringify(sync_options),
                        '_wpnonce_field': eb_setup_wizard.sync_nonce,
                        'offset': offset
                    },
                    success: function (response) {
                        queryLimit = queryLimit + 2;
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
                                    var html = '<span class="linkresponse-box-error">' + eb_setup_wizard.msg_unlink_users_list + '</span>';
                                    container.append(html);
                                    $(".unlink-table tbody").append(notLinkedusers);
                                }
                            }
                        } else {
                            $('.load-response').hide();
                            linkUserResponseBox(eb_setup_wizard.msg_con_prob, 'error', 0);
                        }
                    }
                });
            }

            
            $(document).on('click', '.eb_setup_users_sync_btn', function (event) {
                var $this = $(this);
                
                var sync_options = {};
                // prepare sync options array
                var sync_options = {eb_synchronize_user_courses: 1, eb_link_users_to_moodle: 1};
                var offset = 0;
                var progressWidth = 0;
                var linkedUsers = 0;
                var users_count = 0;
                var queryLimit = 0;
                var notLinkedusers = [];
                userLinkSyncAjax($this, sync_options, offset, linkedUsers, users_count, queryLimit, notLinkedusers);

                // Trigger save and continue button.
                $( ".eb_setup_save_and_continue" ).click();

            });
            
            /* Function to show progress of link users to moodle functionality*/
            function showLinkedUsersProgress(linked_users_count = 0, unlinked_users_count = 0, type) {
                var container = $('.linkresponse-box');
                var html = '<div class="alert alert-' + type + '">' + linked_users_count + ' / ' + unlinked_users_count + ' ' + eb_setup_wizard.msg_user_link_to_moodle_success + '</div>';
                container.empty();
                container.append(html);
            }




            /**
             * creates ajax request to initiate test connection request
             * display a response to user on process completion
             */
            $(document).on('click', '.eb_setup_test_connection_btn', function (event) {

                //get selected options
                //
                
                var url      = $('#eb_setup_test_conn_mdl_url').val();
                var token    = $('#eb_setup_test_conn_token').val();



                var $this = $(this);
                //display loading animation
                $.ajax({
                    method: "post",
                    url: eb_setup_wizard.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'eb_setup_test_connection',
                        'url': url.trim(),
                        'token': token.trim(),
                        '_wpnonce_field': eb_setup_wizard.nonce,
                    },
                    success: function (response) {
                        //prepare response for user
                        if (response.success == 1) {

                            $('.eb_setup_test_conn_success').css('display', 'initial');
                            $('.eb_setup_test_connection_btn').css('display', 'none');
                            $('.eb_setup_test_connection_cont_btn').css('display', 'initial');

                        } else {
                            // ohSnap(response.response_message, 'error', 0);
                            $('.eb_setup_test_conn_error').html(response.response_message);
                        }
                    }
                });
            });





    });
    

})(jQuery);