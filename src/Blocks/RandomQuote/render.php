<?php
/**
 * Random Quote Block Renderer
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Blocks;

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

	// Use QuoteOutput class for complete rendering (including AJAX if enabled)
	$quote_output = new \XVRandomQuotes\Output\QuoteOutput();
	return $quote_output->get_random_quotes(
		array(
			'categories'    => $categories,
			'sequence'      => $sequence,
			'multi'         => $multi,
			'offset'        => 0,
			'disableaspect' => $disableaspect,
			'enable_ajax'   => $enableAjax,
			'timer'         => $timer,
		)
	);
}
