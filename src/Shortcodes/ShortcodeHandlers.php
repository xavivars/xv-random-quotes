<?php
/**
 * Shortcode handlers for displaying quotes
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Shortcodes;

use XVRandomQuotes\Queries\QuoteQueries;

// Import legacy helper functions until they're refactored
use function XVRandomQuotes\Legacy\stray_sanitize_shortcode_attributes;
use function XVRandomQuotes\Legacy\stray_build_order_args;
use function XVRandomQuotes\Legacy\parse_category_slugs;
use function XVRandomQuotes\Legacy\stray_get_filtered_quotes;
use function XVRandomQuotes\Legacy\stray_build_multi_quote_output;
use function XVRandomQuotes\Legacy\stray_build_pagination;
use function XVRandomQuotes\Legacy\stray_get_wrapper_html;
use function XVRandomQuotes\Legacy\stray_get_random_quotes_output;
use function XVRandomQuotes\Legacy\stray_get_quote_by_id_output;

/**
 * Shortcode [stray-all] - Display all quotes with pagination
 *
 * @param array $atts Shortcode attributes
 * @param string|null $content Shortcode content
 * @return string HTML output
 */
function stray_all_shortcode($atts, $content = NULL) {
	// Extract and sanitize attributes
	$atts = stray_sanitize_shortcode_attributes($atts, array(
		'categories' => 'all',
		'sequence' => true,
		'linkphrase' => '',
		'widgetid' => '',
		'noajax' => true,
		'rows' => 10,
		'timer' => '',
		'offset' => 0,
		'fullpage' => true,
		'orderby' => 'quoteID',
		'sort' => 'ASC',
		'disableaspect' => false,
		'user' => ''
	));

	// Initialize QuoteQueries
	$quote_queries = new QuoteQueries();
	
	// Build WP_Query args
	$query_args = array(
		'posts_per_page' => $atts['rows'],
		'offset' => $atts['offset'],
	);

	// Add ordering
	$query_args = array_merge($query_args, stray_build_order_args(
		$atts['sequence'],
		$atts['orderby'],
		$atts['sort']
	));

	// Get quotes with optional category filtering
	$category_slugs = parse_category_slugs($atts['categories']);
	$quotes = stray_get_filtered_quotes($quote_queries, $category_slugs, $query_args);

	if (empty($quotes)) {
		return '';
	}

	// Build multi-quote output
	$output = stray_build_multi_quote_output($quotes, $atts['disableaspect']);

	// Add pagination if needed
	if ($atts['fullpage'] || !$atts['noajax']) {
		// Calculate total quotes
		$total_query = new \WP_Query(array(
			'post_type' => 'xv_quote',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'no_found_rows' => false,
		));
		$total_quotes = $total_query->found_posts;
		
		if ($total_quotes > $atts['rows']) {
			$current_page = max(1, isset($_GET['qp']) ? absint($_GET['qp']) : 1);
			$max_pages = ceil($total_quotes / $atts['rows']);
			
			$pagination = stray_build_pagination($current_page, $max_pages, $atts['rows'], $atts['fullpage']);
			$wrapper = stray_get_wrapper_html($atts['disableaspect'], 'loader');
			
			$output .= $wrapper['before'] . $pagination . $wrapper['after'];
		}
	}

	return $output;
}

/**
 * Shortcode [stray-random] - Display random quote(s)
 *
 * @param array $atts Shortcode attributes
 * @param string|null $content Shortcode content
 * @return string HTML output
 */
function stray_random_shortcode($atts, $content = NULL) {
	// Extract and sanitize attributes
	$atts = stray_sanitize_shortcode_attributes($atts, array(
		'categories' => 'all',
		'sequence' => false,
		'linkphrase' => '',
		'widgetid' => '',
		'noajax' => '',
		'multi' => 1,
		'timer' => '',
		'offset' => 0,
		'fullpage' => '',
		'disableaspect' => false,
		'user' => ''
	));

	// Call core implementation
	return stray_get_random_quotes_output(
		$atts['categories'],
		$atts['sequence'],
		$atts['multi'],
		$atts['offset'],
		$atts['disableaspect'],
		$atts['user']
	);
}

/**
 * Shortcode [stray-id] - Display specific quote by ID
 *
 * @param array $atts Shortcode attributes
 * @param string|null $content Shortcode content
 * @return string HTML output
 */
function stray_id_shortcode($atts, $content = NULL) {
	// Extract and sanitize attributes
	$atts = stray_sanitize_shortcode_attributes($atts, array(
		'id' => '1',
		'linkphrase' => '',
		'noajax' => true,
		'disableaspect' => false
	));

	// Call core implementation
	return stray_get_quote_by_id_output($atts['id'], $atts['disableaspect']);
}
