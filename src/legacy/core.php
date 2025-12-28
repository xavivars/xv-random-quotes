<?php
/**
 * Core implementation functions for quote display
 * These are the pure logic functions that both shortcodes and template tags use
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Legacy;

use XVRandomQuotes\Queries\QuoteQueries;

/**
 * Core implementation: Get random quote(s) output
 *
 * @param string $categories Category slugs (comma-separated or 'all')
 * @param bool $sequence Whether to use sequential ordering
 * @param int $multi Number of quotes to retrieve
 * @param int $offset Offset for pagination
 * @param bool $disableaspect Whether to disable formatting
 * @param string $user Filter by user/contributor
 * @return string HTML output
 */
function stray_get_random_quotes_output($categories = 'all', $sequence = false, $multi = 1, $offset = 0, $disableaspect = false, $user = '') {
	// Initialize QuoteQueries
	$quote_queries = new QuoteQueries();
	
	// Build WP_Query args
	$query_args = array(
		'posts_per_page' => $multi,
		'offset' => $offset,
	);

	// Add ordering (random by default, sequential if specified)
	$query_args = array_merge($query_args, stray_build_order_args($sequence));

	// Get quotes with optional category filtering
	$category_slugs = parse_category_slugs($categories);
	$quotes = stray_get_filtered_quotes($quote_queries, $category_slugs, $query_args);

	if (empty($quotes)) {
		return '';
	}

	// Handle multi-quote vs single quote output
	if ($multi > 1) {
		return stray_build_multi_quote_output($quotes, $disableaspect);
	} else {
		return stray_output_one_cpt($quotes[0], false, $disableaspect);
	}
}

/**
 * Core implementation: Get specific quote output by ID
 *
 * @param int $id Quote ID (post ID or legacy ID)
 * @param bool $disableaspect Whether to disable formatting
 * @return string HTML output
 */
function stray_get_quote_by_id_output($id = 1, $disableaspect = false) {
	// Initialize QuoteQueries
	$quote_queries = new QuoteQueries();

	// Try to get quote by legacy ID first, then by post ID
	$quote_post = $quote_queries->get_quote_by_legacy_id($id);
	
	if (!$quote_post) {
		$quote_post = $quote_queries->get_quote_by_id($id);
	}
	
	// If still not found and ID is default (1), get first available quote
	if (!$quote_post && $id === 1) {
		$quotes = $quote_queries->get_all_quotes(array('posts_per_page' => 1));
		if (!empty($quotes)) {
			$quote_post = $quotes[0];
		}
	}

	if (!$quote_post) {
		return '';
	}

	return stray_output_one_cpt($quote_post, false, $disableaspect);
}
