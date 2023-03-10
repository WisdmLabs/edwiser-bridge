(function ($) {
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

	/**
	 * Provides the functionality to place the new order on click of the Take this course button.
	 */
	function placeOrder(formSubmit) {
		var course_id   = $("input[name='item_number']").val();
		var order_id    = '';
		var buyer_id    = $("input[name='custom']").val();
		var custom_data = {};

		$.ajax({
			method: "post",
			async: false,
			url: eb_public_js_object.ajaxurl,
			dataType: "json",
			data: {
				'action': 'createNewOrderAjaxWrapper',
				'buyer_id': buyer_id,
				'course_id': course_id,
				'_wpnonce_field': eb_public_js_object.nonce,
			},
			success: function (response) {
				//prepare response for user
				if (response.success == 1) {
					//create custom data encoded in json
					custom_data['buyer_id'] = parseInt(buyer_id);
					custom_data['order_id'] = parseInt(response.order_id);
					// custom_data['eb_nonce'] = response.nonce;


					$("input[name='custom']").val(JSON.stringify(custom_data));

					/*
					 *---------------------------------------
					 *Added code to solve payment pending issue
					 * -------------------------------------
					 */
					// submitting form if the submit form is on.
					// added button click event on class as there are 2 payment forms in the single course page with 2 submit buttons with same id.
					if (formSubmit) {
						$(".eb-paid-course").click();
					}
				} else {
					e.preventDefault();
					alert(eb_public_js_object.msg_ordr_pro_err);
				}
			}
		});
	}

	$(window).on("load", function () {


		function getUrlParameter(sParam)
		{
			var sPageURL          = decodeURIComponent(window.location.search.substring(1)),
					sURLVariables = sPageURL.split('&'),
					sParameterName,
					i;

			for (i = 0; i < sURLVariables.length; i++) {
				sParameterName = sURLVariables[i].split('=');

				if (sParameterName[0] === sParam) {
					return sParameterName[1] === undefined ? true : sParameterName[1];
				}
			}
		};




		if (getUrlParameter("auto_enroll") === "true") {

			$.blockUI({
				message: eb_public_js_object.msg_processing
			});
			var btn = document.getElementById('eb_course_payment_button');
			if (btn == null) {
				btn = document.getElementById('wdm-btn');
				if (btn.text != eb_public_js_object.access_course) {
					btn.click();
				} else {
					$.unblockUI();
				}
			} else {


				/*
				 * ---------------------------------
				 * Commented the btn.click() to solve pending payment issue.
				 * --------------------------------
				 */
				/*btn.click();*/
				placeOrder(1);
			}
		}







		/* Change required fields error messages for login / register page */
		var intputElements = document.getElementsByTagName("INPUT");
		for (var i = 0; i < intputElements.length; i++) {
			intputElements[i].oninvalid = function (e) {
				e.target.setCustomValidity("");
				if (!e.target.validity.valid) {
					if (e.target.name == "firstname") {
						e.target.setCustomValidity(eb_public_js_object.msg_val_fn);
					} else if (e.target.name == "lastname") {
						e.target.setCustomValidity(eb_public_js_object.msg_val_ln);
					} else if (e.target.name == "email") {
						e.target.setCustomValidity(eb_public_js_object.msg_val_mail);
					}
				}
			};
		}

		/**
		 * datatable js for user order history table
		 */
		$('#wdm_user_order_history').dataTable({
			"aLengthMenu": [[5, 10, 25, -1], [5, 10, 25, ebDataTable.all]],
			"iDisplayLength": 10,
			"order": [[1, "desc"]],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": true,
			}],
			language: {
				search: ebDataTable.search,
				sEmptyTable: ebDataTable.sEmptyTable,
				sLoadingRecords: ebDataTable.sLoadingRecords,
				sSearch: ebDataTable.sSearch,
				sZeroRecords: ebDataTable.sZeroRecords,
				sProcessing: ebDataTable.sProcessing,
				sInfo: ebDataTable.sInfo,
				sInfoEmpty: ebDataTable.sInfoEmpty,
				sInfoFiltered: ebDataTable.sInfoFiltered,
				sInfoPostFix: ebDataTable.sInfoPostFix,
				sInfoThousands: ebDataTable.sInfoThousands,
				sLengthMenu: ebDataTable.sLengthMenu,
				oPaginate: {
					sFirst: ebDataTable.sFirst,
					sLast: ebDataTable.sLast,
					sNext: ebDataTable.sNext,
					sPrevious: ebDataTable.sPrevious,
				},
				oAria: {
					sSortAscending: ebDataTable.sSortAscending,
					sSortDescending: ebDataTable.sSortDescending
				}
			}
		});

		/**
		 * jquery blockui js to block UI on clicking take course button for paid courses.
		 */



		/**
		 * called by 'take this course' button for paid courses.
		 * calls create_new_order_ajax_wrapper() function to create a new order
		 * add the newly created order it in form to be sent to paypal.
		 *
		 */
		$('.eb-paid-course').click(function (e) {
			placeOrder(0);
		});
	});



	$(document).ready(function () {

		if ($("#reg_terms_and_cond").length) {

			$("input[name='register']").prop('disabled', true);
			$("input[name='register']").css("cursor", "no-drop");
			$("#reg_terms_and_cond").change(function() {
				if (this.checked) {
					$("input[name='register']").prop('disabled', false);
					$("input[name='register']").css("cursor", "pointer");
				} else {
					$("input[name='register']").prop('disabled', true);
					$("input[name='register']").css("cursor", "no-drop");
				}
			});
		}



		$('#eb_terms_cond_check').click(function(){
			var checkbox = $(this).parent().parent();
			checkbox     = checkbox.find("input[name='reg_terms_and_cond']");
			$('#eb-user-account-terms-content').dialog({
				modal: true,
				resizable: true,
				width: 500,
				dialogClass: 'eb_admin_terms_dialog',
				buttons: [
					{
						text: "Agree",
						"class": 'eb_terms_button_agree',
						click: function() {
							checkbox.prop('checked', true);
							$("input[name='register']").prop('disabled', false);
							$("input[name='register']").css("cursor", "pointer");
							$(this).dialog("close");
						}
				},
					{
						text: "Disagree",
						"class": 'eb_terms_button_disagree',
						click: function() {
							checkbox.prop('checked', false);
							 $(this).dialog("close");
						}
				}
				],
			});

			// $('.eb-user-account-terms div').dialog();
		});



		/*Submit filters form on the selction of filter*/
		$('#eb_category_filter').on('change',function(event){
			var form = $(this).closest('form');
			$(form).trigger( 'submit' );
		});

		$('#eb_category_sort').on('change',function(event){
			$(this).closest('form').trigger( 'submit' );
		});

		/**
		 * Scroll left
		 */
		$(".eb-scroll-left").on("click", function (event) {
			event.preventDefault();
			var parent        = $(this).parents(".eb-cat-courses-cont");
			var newScrollLeft = parent.scrollLeft();
			var width         = parent.width();
			var scrollWidth   = parent.get(0).scrollWidth;
			var scrollOffcet  = width / 2;

			parent.animate({scrollLeft: parent.scrollLeft() - scrollOffcet}, "fast");
			if (newScrollLeft <= 0 + scrollOffcet) {
				$(this).hide();
			}
			if (scrollWidth >= newScrollLeft) {
				parent.children(".eb-scroll-right").show()
			}
		});

		/**
		 * Scroll right
		 */
		$(".eb-scroll-right").on("click", function (event) {
			event.preventDefault();
			var parent        = $(this).parents(".eb-cat-courses-cont");
			var newScrollLeft = parent.scrollLeft();
			var width         = parent.width();
			var scrollWidth   = parent.get(0).scrollWidth;
			var scrollOffcet  = width / 2;
			$(parent).children(".eb-scroll-left").show();
			parent.animate({scrollLeft: parent.scrollLeft() + scrollOffcet}, "fast");
			if (scrollWidth <= newScrollLeft + width + scrollOffcet) {
				$(this).hide();
			}
			if (newScrollLeft <= 0) {
				parent.children(".eb-scroll-left").show()
			}
		});

		$(".eb-cat-courses-cont").each(function () {
			var documentScrollLeft = jQuery(".eb-cat-courses-cont").scrollLeft();
			var lastScrollLeft     = 0;
			var width              = $(this).width();
			var scrollWidth        = $(this).get(0).scrollWidth;
			if (scrollWidth <= width) {
				lastScrollLeft = documentScrollLeft;
				$(this).children(".eb-scroll-right").hide();
			}
			$(this).children(".eb-scroll-left").hide();
		});

		if(typeof eb_user_email_verification != "undefined")
		{
			// Show the message in the dialog box
			$('<div></div>').html(eb_user_email_verification.message).dialog({
				modal: false,
				resizable: false,
				width: 500,
				dialogClass: 'eb_admin_terms_dialog eb_email_verify_dialog',
				buttons: [
					{
						text: "Ok",
						"class": 'eb_terms_button_agree',
						click: function() {
							$(this).dialog("close");
						}
					}
				],
			});
		}
	});
})(jQuery);

function ebSubmitCaptchaForm(token) {
	document.getElementById("eb-user-account-form").submit();
}
