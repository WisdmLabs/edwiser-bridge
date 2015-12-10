(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

		/* Change required fields error messages for login / register page */
		var intputElements = document.getElementsByTagName("INPUT");
		    for (var i = 0; i < intputElements.length; i++) {
		        intputElements[i].oninvalid = function (e) {
		            e.target.setCustomValidity("");
		            if (!e.target.validity.valid) {
		                if (e.target.name == "firstname") {
		                    e.target.setCustomValidity("The field 'First Name' cannot be left blank");
		                } else if( e.target.name == "lastname" ){
		                	e.target.setCustomValidity("The field 'Last Name' cannot be left blank");
		                } else if( e.target.name == "email" ){
		                	e.target.setCustomValidity("The field 'Email' cannot be left blank");
		                }
		            }
		        };
		    }

	 	/**
	 	 * datatable js for user order history table
	 	 */
	 	$('#wdm_user_order_history').dataTable( {
	        "aLengthMenu": [[5, 10, 25, -1], [5, 10, 25, "All"]],
	        "iDisplayLength": 10,
	          "order": [[ 1, "desc" ]],
	          "columnDefs": [ {
		      "targets"  : 'no-sort',
		      "orderable": true,
		    }]
		} );

		/**
		 * jquery blockui js to block UI on clicking take course button for paid courses.
		 */

		
		/**
		 * called by 'take this course' button for paid courses.
		 * calls create_new_order_ajax_wrapper() function to create a new order
		 * add the newly created order it in form to be sent to paypal.
		 * 
		 */
		$('#eb_course_payment_button').click(function(e){
			//$.blockUI();
			//get selected options
	  		var course_id 	= $("input[name='item_number']").val();
			var order_id 	= '';
			var buyer_id 	= $("input[name='custom']").val();
			var custom_data = {};
			
			$.ajax({
				method		: "post",
				async		: false,
				url: eb_public_js_object.ajaxurl,
		        dataType	: "json",
		        data: {
		            'action'		:'create_new_order_ajax_wrapper',
		            'buyer_id' 		: buyer_id,
		            'course_id' 	: course_id,
		            '_wpnonce_field': eb_public_js_object.nonce,
		        },
		        success:function(response) {

		        	//prepare response for user
					if( response.success == 1 ){

						//create custom data encoded in json
						custom_data['buyer_id'] = parseInt(buyer_id);
						custom_data['order_id'] = parseInt(response.order_id);
						
						$("input[name='custom']").val(JSON.stringify(custom_data));
					}
					else {
						e.preventDefault();
						alert('Problems in processing your order, Please try later.');
					}
		    	}
	    	});
		});
	});

})( jQuery );
