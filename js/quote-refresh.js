/**
 * Quote Refresh Script
 * 
 * Handles AJAX quote refreshing using WordPress REST API
 * Modern vanilla JavaScript (no jQuery dependency)
 * 
 * @package XVRandomQuotes
 */

(function() {
	'use strict';

	/**
	 * Initialize quote refresh functionality
	 */
	function init() {
		// Find all refresh links
		const refreshLinks = document.querySelectorAll('.xv-quote-refresh');
		
		refreshLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				const containerId = this.getAttribute('data-container');
				const container = document.getElementById(containerId);
				
				if (container) {
					refreshQuote(container);
				}
			});
		});

		// Set up auto-refresh timers
		const containers = document.querySelectorAll('.xv-quote-container[data-timer]');
		
		containers.forEach(function(container) {
			const timer = parseInt(container.getAttribute('data-timer'), 10);
			
			if (timer > 0) {
				setInterval(function() {
					refreshQuote(container);
				}, timer * 1000); // Convert seconds to milliseconds
			}
		});
	}

	/**
	 * Refresh quote in container
	 * 
	 * @param {HTMLElement} container The quote container element
	 */
	function refreshQuote(container) {
		// Get parameters from data attributes
		const categories = container.getAttribute('data-categories') || '';
		const sequence = container.getAttribute('data-sequence') === '1';
		const multi = parseInt(container.getAttribute('data-multi'), 10) || 1;
		const disableaspect = container.getAttribute('data-disableaspect') === '1';
		const contributor = container.getAttribute('data-contributor') || '';

		// Build query string
		const params = new URLSearchParams();
		if (categories && categories !== 'all') {
			params.append('categories', categories);
		}
		if (sequence) {
			params.append('sequence', '1');
		}
		if (multi > 1) {
			params.append('multi', multi.toString());
		}
		if (disableaspect) {
			params.append('disableaspect', '1');
		}
		if (contributor) {
			params.append('contributor', contributor);
		}

		// Show loading state
		const originalHeight = container.offsetHeight;
		container.style.minHeight = originalHeight + 'px';
		container.classList.add('xv-quote-loading');
		
		// Fade out
		container.style.opacity = '0.5';

		// Build REST API URL
		const restUrl = xvQuoteRefresh.restUrl + '?' + params.toString();

		// Fetch new quote
		fetch(restUrl, {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': xvQuoteRefresh.restNonce
			},
			credentials: 'same-origin'
		})
		.then(function(response) {
			if (!response.ok) {
				throw new Error('Network response was not ok');
			}
			return response.json();
		})
		.then(function(data) {
			if (data.html) {
				// Get the refresh link wrapper to preserve it
				const refreshWrapper = container.querySelector('.xv-quote-refresh-wrapper');
				
				// Update the container content
				// We need to replace everything except the refresh link
				const tempDiv = document.createElement('div');
				tempDiv.innerHTML = data.html;
				
				// Clear container except for refresh wrapper
				while (container.firstChild && container.firstChild !== refreshWrapper) {
					container.removeChild(container.firstChild);
				}
				
				// Insert new content before refresh wrapper
				if (refreshWrapper) {
					while (tempDiv.firstChild) {
						container.insertBefore(tempDiv.firstChild, refreshWrapper);
					}
				} else {
					// No refresh wrapper (shouldn't happen), just replace all
					container.innerHTML = data.html;
				}
				
				// Fade in
				container.style.opacity = '1';
				container.style.minHeight = '';
				container.classList.remove('xv-quote-loading');
			}
		})
		.catch(function(error) {
			console.error('Error refreshing quote:', error);
			
			// Reset loading state
			container.style.opacity = '1';
			container.style.minHeight = '';
			container.classList.remove('xv-quote-loading');
		});
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
