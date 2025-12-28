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
	$atts = shortcode_atts(array(
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
	), $atts);

	// Initialize QuoteQueries
	$quote_queries = new QuoteQueries();

	// Sanitize and normalize parameters
	$rows = absint($atts['rows']);
	if ($rows <= 0) $rows = 10;
	
	$offset = absint($atts['offset']);
	$sequence = filter_var($atts['sequence'], FILTER_VALIDATE_BOOLEAN);
	$fullpage = filter_var($atts['fullpage'], FILTER_VALIDATE_BOOLEAN);
	$disableaspect = filter_var($atts['disableaspect'], FILTER_VALIDATE_BOOLEAN);
	
	// Build WP_Query args
	$query_args = array(
		'posts_per_page' => $rows,
		'offset' => $offset,
	);

	// Handle ordering
	if (!$sequence) {
		// Random order
		$query_args['orderby'] = 'rand';
	} else {
		// Sequential order - map legacy orderby values to CPT fields
		switch ($atts['orderby']) {
			case 'quoteID':
				$query_args['orderby'] = 'ID';
				break;
			case 'author':
			case 'Author':
				$query_args['orderby'] = 'title'; // Best approximation
				break;
			case 'source':
			case 'Source':
				$query_args['orderby'] = 'title';
				break;
			default:
				$query_args['orderby'] = 'ID';
		}
		$query_args['order'] = strtoupper($atts['sort']) === 'DESC' ? 'DESC' : 'ASC';
	}

	// Handle category filtering
	$categories = $atts['categories'];
	$category_slugs = array();
	
	if ($categories && $categories !== 'all' && $categories !== '') {
		if (is_string($categories)) {
			$category_slugs = array_map('trim', explode(',', $categories));
			$category_slugs = array_map('sanitize_title', $category_slugs);
		}
	}

	// Get quotes using QuoteQueries
	if (!empty($category_slugs)) {
		$quotes = $quote_queries->get_quotes_by_categories($category_slugs, $query_args);
	} else {
		$quotes = $quote_queries->get_all_quotes($query_args);
	}

	// If no quotes found, return empty
	if (empty($quotes)) {
		return '';
	}

	// Build output
	$output = '';
	
	// Get settings for output formatting
	$quotesoptions = get_option('stray_quotes_options');
	if (!$disableaspect && $quotesoptions) {
		$beforeAll = isset($quotesoptions['stray_quotes_before_all']) ? utf8_decode($quotesoptions['stray_quotes_before_all']) : '';
		$afterAll = isset($quotesoptions['stray_quotes_after_all']) ? utf8_decode($quotesoptions['stray_quotes_after_all']) : '';
	} else {
		$beforeAll = '';
		$afterAll = '';
	}

	// Output multiple quotes
	$output .= $beforeAll . '<ul>';
	foreach ($quotes as $quote_post) {
		$output .= '<li>' . stray_output_one_cpt($quote_post, true, $disableaspect) . '</li>';
	}
	$output .= '</ul>' . $afterAll;

	// Add pagination if needed
	if ($fullpage || !filter_var($atts['noajax'], FILTER_VALIDATE_BOOLEAN)) {
		// Calculate pagination
		$total_query = new \WP_Query(array(
			'post_type' => 'xv_quote',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'no_found_rows' => false,
		));
		$total_quotes = $total_query->found_posts;
		
		if ($total_quotes > $rows) {
			$current_page = max(1, isset($_GET['qp']) ? absint($_GET['qp']) : 1);
			$max_pages = ceil($total_quotes / $rows);
			
			$pagination = stray_build_pagination($current_page, $max_pages, $rows, $fullpage);
			
			if ($quotesoptions && !$disableaspect) {
				$beforeloader = isset($quotesoptions['stray_before_loader']) ? utf8_decode($quotesoptions['stray_before_loader']) : '';
				$afterloader = isset($quotesoptions['stray_after_loader']) ? utf8_decode($quotesoptions['stray_after_loader']) : '';
			} else {
				$beforeloader = '';
				$afterloader = '';
			}
			
			$output .= $beforeloader . $pagination . $afterloader;
		}
	}

	return $output;
}
