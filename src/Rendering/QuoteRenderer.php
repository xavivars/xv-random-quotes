<?php
/**
 * Quote Renderer - Main Entry Point
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Rendering;

use XVRandomQuotes\Admin\Settings;

/**
 * Class QuoteRenderer
 *
 * Main rendering coordinator that routes to appropriate renderer based on settings.
 */
class QuoteRenderer {

	/**
	 * Native renderer instance
	 *
	 * @var NativeRenderer
	 */
	private $native_renderer;

	/**
	 * Legacy renderer instance
	 *
	 * @var LegacyRenderer
	 */
	private $legacy_renderer;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->native_renderer = new NativeRenderer();
		$this->legacy_renderer = new LegacyRenderer();
	}

	/**
	 * Render a single quote
	 *
	 * @param \WP_Post $post The quote post object.
	 * @param bool $is_multi Whether this is part of a multi-quote display.
	 * @param bool $disable_aspect Whether to disable formatting.
	 * @return string Formatted quote HTML.
	 */
	public function render_quote( $post, $is_multi = false, $disable_aspect = false ) {
		if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
			return '';
		}

		// Get quote data from CPT
		$quote_text = $post->post_content;
		$source = get_post_meta( $post->ID, '_quote_source', true );

		// Get author from taxonomy
		$author_terms = wp_get_post_terms( $post->ID, 'quote_author' );
		$author = ! empty( $author_terms ) && ! is_wp_error( $author_terms ) ? $author_terms[0]->name : '';

		// Get author URL from term meta (if author exists)
		$author_url = '';
		if ( ! empty( $author_terms ) && ! is_wp_error( $author_terms ) ) {
			$author_url = get_term_meta( $author_terms[0]->term_id, 'author_url', true );
		}

		// Check if native styling is enabled
		$use_native_styling = get_option( Settings::OPTION_USE_NATIVE_STYLING, true );

		// If native styling is enabled and formatting is not disabled, use native output
		if ( $use_native_styling && ! $disable_aspect ) {
			return $this->native_renderer->render( $quote_text, $author, $source, $author_url );
		}

		// Legacy output format
		return $this->legacy_renderer->render( $post, $quote_text, $author, $source, $author_url, $is_multi, $disable_aspect );
	}

	/**
	 * Render multiple quotes
	 *
	 * @param array $quotes Array of WP_Post quote objects.
	 * @param bool $disable_aspect Whether to disable formatting.
	 * @return string Formatted quotes HTML.
	 */
	public function render_multiple_quotes( $quotes, $disable_aspect = false ) {
		if ( empty( $quotes ) ) {
			return '';
		}

		$output = '<ul>';
		foreach ( $quotes as $quote_post ) {
			$output .= '<li>' . $this->render_quote( $quote_post, true, $disable_aspect ) . '</li>';
		}
		$output .= '</ul>';

		return $output;
	}

	/**
	 * Get loader/pagination wrapper HTML based on current rendering mode
	 *
	 * @param bool $disable_aspect Whether wrappers are disabled.
	 * @return array Array with 'before' and 'after' keys.
	 */
	public function get_loader_wrapper( $disable_aspect ) {
		$use_native = get_option( Settings::OPTION_USE_NATIVE_STYLING, true );

		if ( $use_native ) {
			// Native mode doesn't use custom wrappers for pagination
			return array( 'before' => '', 'after' => '' );
		}

		// Legacy mode - delegate to LegacyRenderer
		$legacy_renderer = new LegacyRenderer();
		return $legacy_renderer->get_loader_wrapper( $disable_aspect );
	}
}
