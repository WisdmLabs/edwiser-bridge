jQuery(document).ready(function ($) {
  let modalHtml = `
    <div class="deactmodal" style="position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); display: none;">
        <div style="background-color: #fff; margin: 15% auto; border: 1px solid #888; width: 80%; max-width: 500px; color: #283B3C">
            <div style="background: #F8FBFC; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;     box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);">
                <h4 style="margin: 0; font-size: 16px; text-transform: uppercase;font-weight: 600; color: #4B5858;">Quick Feedback</h4>
                <button type="button" style="background: none; border: none; font-size: 20px; cursor: pointer;" aria-label="Close">&times;</button>
            </div>
            <div style="padding: 0 24px; margin-top: 24px;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 600; line-height: 24px; color: #283B3C;"><strong>If you have a moment, please let us know why you are deactivating:</strong></h3>
                    <ul style="list-style: none; padding: 0; margin: 16px 0;">
                        <li style="margin-bottom: 10px;">
                            <label style="font-size: 14px; color: #4B5858; line-height: 20px">
                                <input type="radio" name="selected-reason" value="The plugin didn't work" style="accent-color: #F75D25;">
                                <span>The plugin didn't work</span>
                            </label>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <label style="font-size: 14px; color: #4B5858; line-height: 20px">
                                <input type="radio" name="selected-reason" value="I found a better plugin" style="accent-color: #F75D25;">
                                <span>I found a better plugin</span>
                            </label>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <label style="font-size: 14px; color: #4B5858; line-height: 20px">
                                <input type="radio" name="selected-reason" value="I don't like to share my information with you" style="accent-color: #F75D25;">
                                <span>I don't like to share my information with you</span>
                            </label>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <label style="font-size: 14px; color: #4B5858; line-height: 20px">
                                <input type="radio" name="selected-reason" value="It's a temporary deactivation - I'm troubleshooting an issue" style="accent-color: #F75D25;">
                                <span>It's a temporary deactivation - I'm troubleshooting an issue</span>
                            </label>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <label style="font-size: 14px; color: #4B5858; line-height: 20px">
                                <input type="radio" name="selected-reason" value="Other" id="other-reason-radio" style="accent-color: #F75D25;">
                                <span>Other</span>
                            </label>
                            <textarea id="other-reason-text" rows="4" placeholder="Please specify..." style="display: none; margin-top: 8px; width: 100%; padding: 6px; resize: vertical; max-height: 64px; border: 1px solid #D6EAEB"></textarea>
                        </li>
                    </ul>
                </div>
            </div>
            <div style="padding: 0 24px 24px; display: flex; justify-content: space-between; margin-top: 24px;">
                <a href="#" class="button-deactivate" style="background-color: #F8FBFC; padding: 10px 20px; text-decoration: none; color: #000; border: 1px solid #D6D8E7; border-radius: 4px; cursor: pointer; font-weight: 500; font-size: 14px; line-height: 20px">Skip &amp; Deactivate</a>
                <a href="#" class="button-submit" style="background-color: #F75D25; padding: 10px 20px; text-decoration: none; color: #fff; border: 1px solid #F75D25; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px; line-height: 20px">Submit &amp; Deactivate</a>
            </div>
        </div>
    </div>`;

  $('body').append(modalHtml); // Add the modal to the page

  $('#deactivate-edwiser-bridge').click(function (e) {
    //replace with yor plugin ID of the deactivate button
    e.preventDefault();
    $('.deactmodal').show();
  });

  $(document).on('change', 'input[name="selected-reason"]', function () {
    if ($(this).val() === 'Other') {
      $('#other-reason-text').show();
    } else {
      $('#other-reason-text').hide();
    }
  });

  $('.button-submit').click(function (e) {
    e.preventDefault();
    let selectedReason = $('input[name="selected-reason"]:checked').val();

    if (selectedReason === 'Other') {
      const otherText = $('#other-reason-text').val().trim();
      if (!otherText) {
        alert('Please specify your reason in the textbox.');
        return;
      }

      selectedReason = otherText;
    }

    if (selectedReason) {
      $.ajax({
        url: modular_analytics_params.ajax_url,
        type: 'POST',
        data: {
          action: 'modular_analytics_deactivation_feedback',
          nonce: modular_analytics_params.nonce,
          reason: selectedReason,
        },
        success: function (response) {
          console.log(response);
          if (response.success) {
            // Deactivate the plugin (redirect or AJAX call)
            window.location.href = modular_analytics_params.deactivation_url; // Or use an AJAX call if needed
          } else {
            alert(response.data.message);
          }
        },
        error: function (error) {
          console.error('Error sending feedback:', error);
          alert('An error occurred while sending feedback.');
        },
      });
    } else {
      alert('Please select a reason.');
    }
  });

  $('.button-deactivate').click(function (e) {
    e.preventDefault();
    window.location.href = modular_analytics_params.deactivation_url; // Redirect to the deactivation URL
  });

  // Close button handler for the modal
  $('.deactmodal button[aria-label="Close"]').click(function (e) {
    $('.deactmodal').hide();
  });

  $('.fs-modal-close').click(function (e) {
    $('.fs-modal-deactivation-feedback').hide();
  });

  $('.button-close').click(function (e) {
    $('.fs-modal-deactivation-feedback').remove();
  });
});
