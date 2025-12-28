<?php
/**
 * Specific Quote Block Renderer
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Blocks;

// Import legacy helper function
use function XVRandomQuotes\Legacy\stray_output_one_cpt;

/**
 * Render callback for Specific Quote block
 *
 * @param array $attributes Block attributes
 * @return string HTML output
 */
function render_specific_quote_block( $attributes ) {
	// Extract attributes with defaults
	$quote_id      = $attributes['quoteId'] ?? 0;
	$disableaspect = $attributes['disableaspect'] ?? false;

	if ( empty( $quote_id ) ) {
		return '';
	}

	// Try to get quote by post ID first
	$post = get_post( $quote_id );

	// If not found by post ID, try legacy ID
	if ( ! $post || 'xv_quote' !== $post->post_type ) {
		$query = new \WP_Query(
			array(
				'post_type'      => 'xv_quote',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => '_legacy_quote_id',
						'value' => $quote_id,
						'type'  => 'NUMERIC',
					),
				),
			)
		);

		if ( ! $query->have_posts() ) {
			return '';
		}

		$post = $query->posts[0];
	}

	// Check if post is published
	if ( 'publish' !== $post->post_status ) {
		return '';
	}

	// Build output using settings-based rendering
	$output = stray_output_one_cpt( $post, false, $disableaspect );
	
	return $output;
}
