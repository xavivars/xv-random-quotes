/**
 * Overview page admin functionality
 * Handles reset migration AJAX action
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const $resetButton = $('#xv-reset-migration');
        const $status = $('#xv-reset-status');

        $resetButton.on('click', function() {
            // Confirm action
            if (!confirm(xvOverview.confirmMessage)) {
                return;
            }

            // Disable button and show loading state
            $resetButton.prop('disabled', true);
            $status.html('<span class="spinner is-active" style="float: none; margin: 0;"></span>');

            // Send AJAX request
            $.ajax({
                url: xvOverview.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'xv_reset_migration',
                    nonce: xvOverview.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<span style="color: #00a32a;">✓ ' + response.data.message + '</span>');
                    } else {
                        $status.html('<span style="color: #d63638;">✗ ' + response.data.message + '</span>');
                        $resetButton.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #d63638;">✗ Error: ' + error + '</span>');
                    $resetButton.prop('disabled', false);
                }
            });
        });
    });

})(jQuery);
