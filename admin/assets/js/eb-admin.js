(function( $ ) {
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
	
	$( window ).load(function() {

		// Color picker
	jQuery('.colorpick').iris( {
		change: function(event, ui){
			jQuery(this).css( { backgroundColor: ui.color.toString() } );
		},
		hide: true,
		border: true
	} ).each( function() {
		jQuery(this).css( { backgroundColor: jQuery(this).val() } );
	})
	.click(function(){
		jQuery('.iris-picker').hide();
		jQuery(this).closest('.color_box, td').find('.iris-picker').show();
	});

	jQuery('body').click(function() {
		jQuery('.iris-picker').hide();
	});

	jQuery('.color_box, .colorpick').click(function(event){
	    event.stopPropagation();
	});

	// Edit prompt
	jQuery(function(){
		var changed = false;

		jQuery('input, textarea, select, checkbox').change(function(){
			changed = true;
		});

		jQuery('.eb-nav-tab-wrapper a').click(function(){
			if (changed) {
				window.onbeforeunload = function() {
				    return eb_admin_js_object.unsaved_warning;
				}
			} else {
				window.onbeforeunload = '';
			}
		});

		jQuery('.submit input').click(function(){
			window.onbeforeunload = '';
		});
	});

	//help tip
	var tiptip_args = {
		'attribute' : 'data-tip',
		'fadeIn' : 50,
		'fadeOut' : 50,
		'delay' : 200
	};
	jQuery(".tips, .help_tip, .help-tip").tipTip( tiptip_args );

	// Add tiptip to parent element for widefat tables
	jQuery(".parent-tips").each(function(){
		jQuery(this).closest( 'a, th' ).attr( 'data-tip', jQuery(this).data( 'tip' ) ).tipTip( tiptip_args ).css( 'cursor', 'help' );
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
		  
		  if (typeof element !== "undefined" ) {
		    element.fadeOut();
		  } else {
		    jQuery('.alert').fadeOut();
		  }
		}

		// Remove the notification on click
		$('.alert').live('click', function() { 
		  ohSnapX(jQuery(this));
		});

	    /**
	     * creates ajax request to initiate test connection request
	     * display a response to user on process completion
	     */
	    $('#eb_test_connection_button').click(function(){
	    	//get selected options
	    	//
	    	$('.response-box').empty(); // empty the response
	    	var url 	= $('#eb_url').val();
			var token 	= $('#eb_access_token').val();

			var $this = $(this);
			
			//display loading animation
			$('.load-response').show();

			$.ajax({
				method		: "post",
				url: eb_admin_js_object.ajaxurl,
		        dataType	: "json",
		        data: {
		            'action'		:'handle_connection_test',
		            'url' 			: url,
		            'token'			: token,
		            '_wpnonce_field': eb_admin_js_object.nonce,
		        },
		        success:function(response) {
		        	$('.load-response').hide();
		        	//prepare response for user
					if( response.success == 1 )
						ohSnap('Connection successful, Please save your connection details.', 'success', 1);
					else 
						ohSnap(response.response_message, 'error', 0);
		    	}
	    	});
		});
		
		/**
	     * creates ajax request to initiate course synchronization
	     * display a response to user on process completion
	     */
	    $('#eb_synchronize_courses_button').click( function( ){

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
		            'action':'handle_course_synchronization',
		            //'sync_category' : sync_category,
		            //'make_courses_draft': make_courses_draft,
		            //'update_courses':update_courses,
		            'sync_options': JSON.stringify(sync_options),
		            '_wpnonce_field': eb_admin_js_object.nonce,
		        },
		        success:function( response ) {
		        	
		        	$('.load-response').hide();

		        	//prepare response for user
		        	if( response.connection_response == 1 ){
						if( response.course_success == 1 )
							ohSnap('Courses synchronized successfully.', 'success', 1);
						else 
							ohSnap(response.course_response_message, 'error', 0);

						if( sync_options['eb_synchronize_categories'] == 1 && response.category_success == 1 )
							ohSnap('Categories synchronized successfully.', 'success', 1);
						else if( sync_options['eb_synchronize_categories'] == 1 && response.category_success == 0 )
							ohSnap(response.category_response_message, 'error', 0);
					} else {
						ohSnap('There is a problem while connecting to moodle server.', 'error', 0);
					}
		    	}
	    	});
		});


/**
	     * creates ajax request to initiate user's courses synchronization
	     * display a response to user on process completion
	     */
	    $('#eb_synchronize_users_button').click( function( ){

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
		            'action':'handle_user_course_synchronization',
		            'sync_options'		: JSON.stringify(sync_options),
		            '_wpnonce_field': eb_admin_js_object.nonce
		        },
		        success:function( response ) {
		        	$('.load-response').hide();

		        	//prepare response for user
					if( response.connection_response == 1 ){

						if(response.user_with_error !== undefined ){
							$.each(response.user_with_error, function(index, value) {
						       user_id_error += this;
						   	});
						}
						
						if(response.user_with_error !== undefined ){
							ohSnap('<p>Error occured for following users: </p>'+user_id_error, 'red');
						} else {
							ohSnap('<p>User data synced successfully.', 'success', 1);
						}
					}
					else {
						ohSnap('There is a problem while connecting to moodle server.', 'error', 0);
					}
		    	}
	    	});
		});

		/**
		 * Handle course price dropdown toggle.
		 */
		$('#course_price_type').change(function(){
			var type = $('#course_price_type').val();
			if( type == 'free' ){
				$('#eb_course_course_price').hide();
				$('#eb_course_course_closed_url').hide();
				$('#course_price').val('');
				$('#course_closed_url').val('');
			} else if( type == 'paid' ){
				$('#eb_course_course_price').show();
				$('#eb_course_course_closed_url').hide();
				$('#course_closed_url').val('');
			} else if( type == 'closed' ){
				$('#eb_course_course_price').hide();
				$('#eb_course_course_closed_url').show();
				$('#course_price').val('');
			}
		});
		$('#course_price_type').change();

	});
})( jQuery );
