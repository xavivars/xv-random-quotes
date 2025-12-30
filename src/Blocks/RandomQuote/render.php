<?php
/**
 * Random Quote Block Renderer
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Blocks;

use XVRandomQuotes\Queries\QuoteQueries;
use XVRandomQuotes\Rendering\QuoteRenderer;
use XVRandomQuotes\Utils\QueryHelpers;

/**
 * Render callback for Random Quote block
 *
 * @param array $attributes Block attributes
 * @return string HTML output
 */
function render_random_quote_block( $attributes ) {
	// Extract attributes with defaults
	$categories    = $attributes['categories'] ?? '';
	$multi         = $attributes['multi'] ?? 1;
	$sequence      = $attributes['sequence'] ?? false;
	$disableaspect = $attributes['disableaspect'] ?? false;
	$enableAjax    = $attributes['enableAjax'] ?? false;
	$timer         = $attributes['timer'] ?? 0;

	// Initialize QuoteQueries
	$quote_queries = new QuoteQueries();
	$renderer = new QuoteRenderer();

	// Build WP_Query args
	$query_args = array(
		'posts_per_page' => $multi,
	);

	// Add ordering using QueryHelpers
	$query_args = array_merge( $query_args, QueryHelpers::build_order_args( $sequence ) );

	// Get quotes with optional category filtering
	$category_slugs = QueryHelpers::parse_category_slugs( $categories );
	$quotes = QueryHelpers::get_filtered_quotes( $quote_queries, $category_slugs, $query_args );

	if ( empty( $quotes ) ) {
		return '';
	}

	// Build output with block-specific wrapper
	$output = '';
	
	// For single quote
	if ( 1 === $multi ) {
		$output .= $renderer->render_quote( $quotes[0], false, $disableaspect );
	} else {
		// Multiple quotes
		$output .= $renderer->render_multiple_quotes( $quotes, $disableaspect );
	}

	// Wrap in AJAX container if enabled
	if ( $enableAjax ) {
		$data_attrs = sprintf(
			'data-categories="%s" data-multi="%d" data-sequence="%s" data-timer="%d"',
			esc_attr( $categories ),
			esc_attr( $multi ),
			$sequence ? 'true' : 'false',
			esc_attr( $timer )
		);

		$ajax_output = sprintf(
			'<div class="xv-quote-ajax-wrapper" %s>%s',
			$data_attrs,
			$output
		);

		// Add refresh link if timer is 0 (manual refresh)
		if ( 0 === $timer ) {
			$ajax_output .= '<a href="#" class="xv-quote-refresh">Load another quote</a>';
		}

		$ajax_output .= '</div>';

		// Enqueue AJAX script
		wp_enqueue_script( 'xv-quote-refresh' );

		return $ajax_output;
	}

	return $output;
}
