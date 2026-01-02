/**
 * XV Random Quotes Migration Handler
 *
 * Handles AJAX batch migration with progress bar updates.
 */

(function() {
	'use strict';

	// Migration state
	let isMigrating = false;
	let $button = null;
	let $notice = null;
	let $progressNotice = null;

	/**
	 * Start migration process
	 */
	function startMigration() {
		if (isMigrating) {
			return;
		}

		isMigrating = true;
		$button = document.querySelector('.xv-start-migration');

		if (!$button) {
			return;
		}

		// Disable button
		$button.disabled = true;
		$button.textContent = 'Migration in progress...';

		// Start batch processing
		processBatch();
	}

	/**
	 * Process one batch of migration
	 */
	function processBatch() {
		const nonce = $button.getAttribute('data-nonce');

		fetch(xvMigration.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'xv_quote_migration_batch',
				nonce: nonce,
			}),
		})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					const result = data.data;

					// Update UI
					updateProgressBar(result);

					// Continue if not complete
					if (!result.complete) {
						// Small delay to prevent overwhelming the server
						setTimeout(processBatch, 100);
					} else {
						// Migration complete
						completeMigration();
					}
				} else {
					// Error occurred
					showError(data.data.message || 'Migration failed');
					isMigrating = false;
					$button.disabled = false;
					$button.textContent = 'Start Migration';
				}
			})
			.catch(error => {
				showError('AJAX error: ' + error.message);
				isMigrating = false;
				$button.disabled = false;
				$button.textContent = 'Start Migration';
			});
	}

	/**
	 * Update progress bar in the notice
	 *
	 * @param {Object} result Migration batch result
	 */
	function updateProgressBar(result) {
		if (!$progressNotice) {
			// Create progress notice if not exists
			const percentage = result.percentage || 0;
			const html = `
				<div class="notice notice-info xv-migration-progress-notice" style="margin-top: 20px;">
					<p>
						<strong>Migration in Progress</strong>
					</p>
					<p class="xv-migration-progress-text">
						Migrated ${result.offset} of ${result.total} quotes (${percentage}%)
					</p>
					<div class="xv-migration-progress-bar" style="background: #ddd; height: 20px; border-radius: 3px; overflow: hidden;">
						<div class="xv-migration-progress-fill" style="background: #2271b1; height: 100%; width: ${percentage}%; transition: width 0.3s;"></div>
					</div>
				</div>
			`;

			const $warningNotice = document.querySelector('.notice.notice-warning');
			if ($warningNotice) {
				$warningNotice.insertAdjacentHTML('afterend', html);
				$progressNotice = document.querySelector('.xv-migration-progress-notice');
			}
		} else {
			// Update existing progress bar
			const percentage = result.percentage || 0;
			const $fill = $progressNotice.querySelector('.xv-migration-progress-fill');
			if ($fill) {
				$fill.style.width = percentage + '%';
			}

			// Update text
			const $text = $progressNotice.querySelector('.xv-migration-progress-text');
			if ($text) {
				$text.textContent = `Migrated ${result.offset} of ${result.total} quotes (${percentage}%)`;
			}
		}
	}

	/**
	 * Handle migration completion
	 */
	function completeMigration() {
		isMigrating = false;
		$progressNotice = null;

		// Call AJAX to clear the pending flag on server
		fetch(xvMigration.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'xv_migration_complete',
				nonce: $button.getAttribute('data-nonce'),
			}),
		}).catch(() => {
			// Ignore errors, UI is already updated
		});

		// Remove old notices
		document.querySelectorAll('.notice.notice-warning').forEach(el => el.remove());
		document.querySelectorAll('.notice.notice-info').forEach(el => el.remove());

		// Show success notice
		const successHtml = `
			<div class="notice notice-success is-dismissible">
				<p>
					<strong>Migration Complete!</strong>
				</p>
				<p>
					All quotes have been successfully migrated to the new system.
				</p>
			</div>
		`;

		const $pageNotices = document.querySelector('.wp-header-end');
		if ($pageNotices) {
			$pageNotices.insertAdjacentHTML('beforebegin', successHtml);
		}

		// Make success dismissible
		document.querySelectorAll('.notice.is-dismissible').forEach(notice => {
			const $button = document.createElement('button');
			$button.type = 'button';
			$button.className = 'notice-dismiss';
			$button.innerHTML = '<span class="screen-reader-text">Dismiss this notice.</span>';

			$button.addEventListener('click', function() {
				notice.style.display = 'none';
			});

			notice.appendChild($button);
		});
	}

	/**
	 * Show error message
	 *
	 * @param {string} message Error message
	 */
	function showError(message) {
		const errorHtml = `
			<div class="notice notice-error">
				<p>
					<strong>Migration Error</strong>
				</p>
				<p>
					${message}
				</p>
			</div>
		`;

		const $pageNotices = document.querySelector('.wp-header-end');
		if ($pageNotices) {
			$pageNotices.insertAdjacentHTML('beforebegin', errorHtml);
		}
	}

	/**
	 * Initialize on document ready
	 */
	function init() {
		const $startButton = document.querySelector('.xv-start-migration');

		if ($startButton) {
			$startButton.addEventListener('click', startMigration);
		}
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
