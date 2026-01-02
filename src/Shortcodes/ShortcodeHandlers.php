<?php
/**
 * Shortcode handlers for displaying quotes
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Shortcodes;

use XVRandomQuotes\Queries\QuoteQueries;
use XVRandomQuotes\Output\QuoteOutput;
use XVRandomQuotes\Rendering\QuoteRenderer;
use XVRandomQuotes\Utils\QueryHelpers;
use XVRandomQuotes\Utils\PaginationHelper;
use XVRandomQuotes\Utils\ShortcodeHelpers;

/**
 * Shortcode [stray-all] - Display all quotes with pagination
 *
 * @param array $atts Shortcode attributes
 * @param string|null $content Shortcode content
 * @return string HTML output
 */
function stray_all_shortcode($atts, $content = NULL) {
	// Extract and sanitize attributes
	$atts = ShortcodeHelpers::sanitize_attributes($atts, array(
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

	// Initialize dependencies
	$quote_queries = new QuoteQueries();
	$renderer = new QuoteRenderer();
	
	// Build WP_Query args
	$query_args = array(
		'posts_per_page' => $atts['rows'],
		'offset' => $atts['offset'],
	);

	// Add ordering
	$query_args = array_merge($query_args, QueryHelpers::build_order_args(
		$atts['sequence'],
		$atts['orderby'],
		$atts['sort']
	));

	// Get quotes with optional category filtering
	$category_slugs = QueryHelpers::parse_category_slugs($atts['categories']);
	$quotes = QueryHelpers::get_filtered_quotes($quote_queries, $category_slugs, $query_args);

	if (empty($quotes)) {
		return '';
	}

	// Build multi-quote output
	$output = $renderer->render_multiple_quotes($quotes, $atts['disableaspect']);

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
			
			$pagination = PaginationHelper::build_pagination($current_page, $max_pages, $atts['rows'], $atts['fullpage']);
			
			// Get loader wrapper from renderer
			$wrapper = $renderer->get_loader_wrapper($atts['disableaspect']);
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
	$atts = ShortcodeHelpers::sanitize_attributes($atts, array(
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

	// Determine if AJAX is enabled (noajax="" means enabled)
	$enable_ajax = empty( $atts['noajax'] ) && ! empty( $atts['linkphrase'] );

	// Use QuoteOutput class for complete rendering (including AJAX if enabled)
	$quote_output = new QuoteOutput();
	return $quote_output->get_random_quotes(
		array(
			'categories'    => $atts['categories'],
			'sequence'      => $atts['sequence'],
			'multi'         => $atts['multi'],
			'offset'        => $atts['offset'],
			'disableaspect' => $atts['disableaspect'],
			'contributor'   => $atts['user'],
			'enable_ajax'   => $enable_ajax,
			'link_phrase'   => $atts['linkphrase'],
			'timer'         => ! empty( $atts['timer'] ) ? absint( $atts['timer'] ) : 0,
		)
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
	$atts = ShortcodeHelpers::sanitize_attributes($atts, array(
		'id' => '1',
		'linkphrase' => '',
		'noajax' => true,
		'disableaspect' => false
	));

	// Use QuoteOutput class
	$quote_output = new QuoteOutput();
	return $quote_output->get_quote_by_id($atts['id'], $atts['disableaspect']);
}
