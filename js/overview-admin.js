/**
 * Overview page admin functionality
 * Handles reset migration AJAX actions
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Reset all migrations button
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
                        // Auto-reload after 10 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 10000);
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

        // Reset quotes only button
        const $resetQuotesButton = $('#xv-reset-quotes-only');
        const $quotesStatus = $('#xv-reset-quotes-status');

        $resetQuotesButton.on('click', function() {
            // Confirm action
            if (!confirm(xvOverview.confirmQuotesMessage)) {
                return;
            }

            // Disable button and show loading state
            $resetQuotesButton.prop('disabled', true);
            $quotesStatus.html('<span class="spinner is-active" style="float: none; margin: 0;"></span>');

            // Send AJAX request
            $.ajax({
                url: xvOverview.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'xv_reset_quotes_only',
                    nonce: xvOverview.quotesNonce
                },
                success: function(response) {
                    if (response.success) {
                        $quotesStatus.html('<span style="color: #00a32a;">✓ ' + response.data.message + '</span>');
                        // Auto-reload after 10 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 10000);
                    } else {
                        $quotesStatus.html('<span style="color: #d63638;">✗ ' + response.data.message + '</span>');
                        $resetQuotesButton.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $quotesStatus.html('<span style="color: #d63638;">✗ Error: ' + error + '</span>');
                    $resetQuotesButton.prop('disabled', false);
                }
            });
        });
    });

})(jQuery);
