<?php
/**
 * Random Quote Block Renderer
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Blocks;

use XVRandomQuotes\Queries\QuoteQueries;

// Import legacy helper functions
use function XVRandomQuotes\Legacy\parse_category_slugs;
use function XVRandomQuotes\Legacy\stray_get_filtered_quotes;
use function XVRandomQuotes\Legacy\stray_output_one_cpt;

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

	// Build WP_Query args
	$query_args = array(
		'posts_per_page' => $multi,
	);

	// Set ordering (random vs sequential)
	if ( $sequence ) {
		$query_args['orderby'] = 'ID';
		$query_args['order']   = 'ASC';
	} else {
		$query_args['orderby'] = 'rand';
	}

	// Get quotes with optional category filtering
	$category_slugs = parse_category_slugs( $categories );
	$quotes         = stray_get_filtered_quotes( $quote_queries, $category_slugs, $query_args );

	if ( empty( $quotes ) ) {
		return '';
	}

	// Build output with block-specific wrapper
	$output = '';
	
	// For single quote
	if ( 1 === $multi ) {
		$quote_post = $quotes[0];
		$output .= stray_output_one_cpt( $quote_post, false, $disableaspect );
	} else {
		// Multiple quotes
		$output .= '<ul>';
		foreach ( $quotes as $quote_post ) {
			$output .= '<li>' . stray_output_one_cpt( $quote_post, true, $disableaspect ) . '</li>';
		}
		$output .= '</ul>';
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
