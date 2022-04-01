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

console.log('CLICKED ::: ');

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





    // ajax xall to save data and get new tab at the same time.







    });
    

})(jQuery);