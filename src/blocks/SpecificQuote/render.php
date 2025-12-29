<?php
/**
 * Specific Quote Block Renderer
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Blocks;

use XVRandomQuotes\Rendering\QuoteRenderer;

/**
 * Render callback for Specific Quote block
 *
 * @param array $attributes Block attributes
 * @return string HTML output
 */
function render_specific_quote_block( $attributes ) {
	// Extract attributes with defaults
	$post_id       = $attributes['postId'] ?? 0;
	$legacy_id     = $attributes['legacyId'] ?? 0;
	$disableaspect = $attributes['disableaspect'] ?? false;

	$post = null;

	// Try to get quote by post ID first
	if ( ! empty( $post_id ) ) {
		$post = get_post( $post_id );
		if ( $post && 'xv_quote' !== $post->post_type ) {
			$post = null;
		}
	}

	// If not found by post ID, try legacy ID
	if ( ! $post && ! empty( $legacy_id ) ) {
		$query = new \WP_Query(
			array(
				'post_type'      => 'xv_quote',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => '_quote_legacy_id',
						'value' => $legacy_id,
						'type'  => 'NUMERIC',
					),
				),
			)
		);

		if ( $query->have_posts() ) {
			$post = $query->posts[0];
		}
	}

	// If no post found, return empty
	if ( ! $post ) {
		return '';
	}

	// In the editor context, show any status; on frontend, only published
	$is_editor = defined( 'REST_REQUEST' ) && REST_REQUEST;
	if ( ! $is_editor && 'publish' !== $post->post_status ) {
		return '';
	}

	// Build output using QuoteRenderer
	$renderer = new QuoteRenderer();
	$output = $renderer->render_quote( $post, false, $disableaspect );
	
	return $output;
}
