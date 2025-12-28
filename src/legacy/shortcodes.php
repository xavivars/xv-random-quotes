<?php
/**
 * Legacy shortcode functions (refactored to use CPT architecture)
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Legacy;

use XVRandomQuotes\Queries\QuoteQueries;

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

	// Initialize QuoteQueries
	$quote_queries = new QuoteQueries();
	
	// Build WP_Query args
	$query_args = array(
		'posts_per_page' => $atts['multi'],
		'offset' => $atts['offset'],
	);

	// Add ordering (random by default, sequential if specified)
	$query_args = array_merge($query_args, stray_build_order_args($atts['sequence']));

	// Get quotes with optional category filtering
	$category_slugs = parse_category_slugs($atts['categories']);
	$quotes = stray_get_filtered_quotes($quote_queries, $category_slugs, $query_args);

	if (empty($quotes)) {
		return '';
	}

	// Handle multi-quote vs single quote output
	if ($atts['multi'] > 1) {
		return stray_build_multi_quote_output($quotes, $atts['disableaspect']);
	} else {
		return stray_output_one_cpt($quotes[0], false, $atts['disableaspect']);
	}
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

	// Initialize QuoteQueries
	$quote_queries = new QuoteQueries();

	// Try to get quote by legacy ID first, then by post ID
	$quote_post = $quote_queries->get_quote_by_legacy_id($atts['id']);
	
	if (!$quote_post) {
		$quote_post = $quote_queries->get_quote_by_id($atts['id']);
	}
	
	// If still not found and ID is default (1), get first available quote
	if (!$quote_post && $atts['id'] === 1) {
		$quotes = $quote_queries->get_all_quotes(array('posts_per_page' => 1));
		if (!empty($quotes)) {
			$quote_post = $quotes[0];
		}
	}

	if (!$quote_post) {
		return '';
	}

	return stray_output_one_cpt($quote_post, false, $atts['disableaspect']);
}
