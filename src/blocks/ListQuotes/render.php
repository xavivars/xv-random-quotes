<?php
/**
 * List Quotes Block Renderer
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Blocks;

use XVRandomQuotes\Queries\QuoteQueries;
use XVRandomQuotes\Rendering\QuoteRenderer;
use XVRandomQuotes\Utils\QueryHelpers;
use XVRandomQuotes\Utils\PaginationHelper;

/**
 * Render callback for List Quotes block
 *
 * @param array $attributes Block attributes
 * @return string HTML output
 */
function render_list_quotes_block( $attributes ) {
	// Extract attributes with defaults
	$categories      = $attributes['categories'] ?? '';
	$rows            = $attributes['rows'] ?? 10;
	$orderby         = $attributes['orderby'] ?? 'date';
	$sort            = $attributes['sort'] ?? 'DESC';
	$disableaspect   = $attributes['disableaspect'] ?? false;
	$show_pagination = $attributes['showPagination'] ?? true;

	// Get current page for pagination
	$current_page = max( 1, isset( $_GET['qp'] ) ? absint( $_GET['qp'] ) : 1 );
	$offset = ( $current_page - 1 ) * $rows;

	// Initialize QuoteQueries
	$quote_queries = new QuoteQueries();
	$renderer = new QuoteRenderer();

	// Build WP_Query args
	$query_args = array(
		'posts_per_page' => $rows,
		'offset'         => $offset,
	);

	// Add ordering
	$query_args = array_merge(
		$query_args,
		QueryHelpers::build_order_args( true, $orderby, $sort )
	);

	// Get quotes with optional category filtering
	$category_slugs = QueryHelpers::parse_category_slugs( $categories );
	$quotes = QueryHelpers::get_filtered_quotes( $quote_queries, $category_slugs, $query_args );

	if ( empty( $quotes ) ) {
		return '';
	}

	// Build output
	$output = $renderer->render_multiple_quotes( $quotes, $disableaspect );

	// Add pagination if needed
	// Get total count (respecting category filter if present)
	$total_query_args = array(
		'post_type'      => 'xv_quote',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => false,
	);

	// Apply same category filter to total count
	if ( ! empty( $category_slugs ) ) {
		$total_query_args['tax_query'] = array(
			array(
				'taxonomy' => 'quote_category',
				'field'    => 'slug',
				'terms'    => $category_slugs,
			),
		);
	}

	$total_query = new \WP_Query( $total_query_args );
	$total_quotes = $total_query->found_posts;

	if ( $show_pagination && $total_quotes > $rows ) {
		$max_pages = ceil( $total_quotes / $rows );

		$pagination = PaginationHelper::build_pagination( $current_page, $max_pages, $rows, true );
		
		// Get loader wrapper from renderer
		$wrapper = $renderer->get_loader_wrapper( $disableaspect );
		$output .= $wrapper['before'] . $pagination . $wrapper['after'];
	}

	return $output;
}
