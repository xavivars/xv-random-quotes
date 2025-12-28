<?php
/**
 * List Quotes Block Renderer
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Blocks;

use XVRandomQuotes\Queries\QuoteQueries;

// Import legacy helper functions
use function XVRandomQuotes\Legacy\parse_category_slugs;
use function XVRandomQuotes\Legacy\stray_get_filtered_quotes;
use function XVRandomQuotes\Legacy\stray_build_pagination;
use function XVRandomQuotes\Legacy\stray_get_wrapper_html;
use function XVRandomQuotes\Legacy\stray_build_order_args;
use function XVRandomQuotes\Legacy\stray_output_one_cpt;

/**
 * Render callback for List Quotes block
 *
 * @param array $attributes Block attributes
 * @return string HTML output
 */
function render_list_quotes_block( $attributes ) {
	// Extract attributes with defaults
	$categories    = $attributes['categories'] ?? '';
	$rows          = $attributes['rows'] ?? 10;
	$orderby       = $attributes['orderby'] ?? 'date';
	$sort          = $attributes['sort'] ?? 'DESC';
	$disableaspect = $attributes['disableaspect'] ?? false;

	// Initialize QuoteQueries
	$quote_queries = new QuoteQueries();

	// Build WP_Query args
	$query_args = array(
		'posts_per_page' => $rows,
	);

	// Add ordering
	$query_args = array_merge(
		$query_args,
		stray_build_order_args( true, $orderby, $sort )
	);

	// Get quotes with optional category filtering
	$category_slugs = parse_category_slugs( $categories );
	$quotes         = stray_get_filtered_quotes( $quote_queries, $category_slugs, $query_args );

	if ( empty( $quotes ) ) {
		return '';
	}

	// Build output
	$output = '<ul>';
	foreach ( $quotes as $quote_post ) {
		$output .= '<li>' . stray_output_one_cpt( $quote_post, true, $disableaspect ) . '</li>';
	}
	$output .= '</ul>';

	// Add pagination if needed
	$total_query = new \WP_Query(
		array(
			'post_type'      => 'xv_quote',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
		)
	);

	$total_quotes = $total_query->found_posts;

	if ( $total_quotes > $rows ) {
		$current_page = max( 1, isset( $_GET['qp'] ) ? absint( $_GET['qp'] ) : 1 );
		$max_pages    = ceil( $total_quotes / $rows );

		$pagination = stray_build_pagination( $current_page, $max_pages, $rows, true );
		$wrapper    = stray_get_wrapper_html( $disableaspect, 'loader' );

		$output .= $wrapper['before'] . $pagination . $wrapper['after'];
	}

	return $output;
}
