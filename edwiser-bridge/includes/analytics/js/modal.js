jQuery(document).ready(function ($) {
    let modalHtml = `
    <div class="deactmodal" style="position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); display: none;">
        <div style="background-color: #fff; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h4>Quick Feedback</h4>
                <button type="button" style="background: none; border: none; font-size: 20px; cursor: pointer;" aria-label="Close">&times;</button>
            </div>
            <div>
                <div>
                    <h3><strong>If you have a moment, please let us know why you are deactivating:</strong></h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px;">
                            <label>
                                <input type="radio" name="selected-reason" value="The plugin didn't work">
                                <span>The plugin didn't work</span>
                            </label>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <label>
                                <input type="radio" name="selected-reason" value="I found a better plugin">
                                <span>I found a better plugin</span>
                            </label>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <label>
                                <input type="radio" name="selected-reason" value="I don't like to share my information with you">
                                <span>I don't like to share my information with you</span>
                            </label>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <label>
                                <input type="radio" name="selected-reason" value="It's a temporary deactivation - I'm troubleshooting an issue">
                                <span>It's a temporary deactivation - I'm troubleshooting an issue</span>
                            </label>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <label>
                                <input type="radio" name="selected-reason" value="Other">
                                <span>Other</span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <a href="#" class="button-deactivate" style="background-color: #f1f1f1; padding: 10px 20px; text-decoration: none; color: #000; border: 1px solid #ccc; border-radius: 4px; cursor: pointer;">Skip &amp; Deactivate</a>
                <a href="#" class="button-submit" style="background-color: #0073aa; padding: 10px 20px; text-decoration: none; color: #fff; border: 1px solid #0073aa; border-radius: 4px; cursor: pointer;">Submit &amp; Deactivate</a>
            </div>
        </div>
    </div>`;

    $('body').append(modalHtml); // Add the modal to the page

    $('#deactivate-edwiser-bridge').click(function (e) { //replace with yor plugin ID of the deactivate button
        e.preventDefault();
        $('.deactmodal').show();
    });

    $('.button-submit').click(function (e) {
        e.preventDefault();
        let selectedReason = $('input[name="selected-reason"]:checked').val();

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
                    console.error("Error sending feedback:", error);
                    alert("An error occurred while sending feedback.");
                }
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