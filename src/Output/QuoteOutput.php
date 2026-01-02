<?php
/**
 * Quote Output Orchestration
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Output;

use XVRandomQuotes\Queries\QuoteQueries;
use XVRandomQuotes\Rendering\QuoteRenderer;
use XVRandomQuotes\Utils\QueryHelpers;
use XVRandomQuotes\Admin\Settings;

/**
 * Class QuoteOutput
 *
 * Orchestrates quote retrieval and rendering.
 */
class QuoteOutput {

	/**
	 * Quote queries instance
	 *
	 * @var QuoteQueries
	 */
	private $quote_queries;

	/**
	 * Quote renderer instance
	 *
	 * @var QuoteRenderer
	 */
	private $renderer;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->quote_queries = new QuoteQueries();
		$this->renderer = new QuoteRenderer();
	}

	/**
	 * Get renderer instance
	 *
	 * @return QuoteRenderer
	 */
	public function get_renderer() {
		return $this->renderer;
	}

	/**
	 * Get random quote(s) output with optional AJAX wrapper
	 *
	 * @param array $args {
	 *     Optional. Array of arguments for quote retrieval and rendering.
	 *
	 *     @type string $categories      Category slugs (comma-separated or 'all'). Default 'all'.
	 *     @type bool   $sequence        Whether to use sequential ordering. Default false.
	 *     @type int    $multi           Number of quotes to retrieve. Default 1.
	 *     @type int    $offset          Offset for pagination. Default 0.
	 *     @type bool   $disableaspect   Whether to disable formatting. Default false.
	 *     @type string $contributor     Filter by user/contributor. Default ''.
	 *     @type bool   $enable_ajax     Whether to enable AJAX refresh functionality. Default false.
	 *     @type string $link_phrase     Custom text for refresh link. Default ''.
	 *     @type int    $timer           Auto-refresh interval in seconds (0 = manual only). Default 0.
	 *     @type string $container_id    Custom container ID. If empty, one will be generated. Default ''.
	 * }
	 * @return string HTML output (with AJAX wrapper if enabled).
	 */
	public function get_random_quotes( $args = array() ) {
		// Parse arguments with defaults
		$args = wp_parse_args(
			$args,
			array(
				'categories'    => 'all',
				'sequence'      => false,
				'multi'         => 1,
				'offset'        => 0,
				'disableaspect' => false,
				'contributor'   => '',
				'enable_ajax'   => false,
				'link_phrase'   => '',
				'timer'         => 0,
				'container_id'  => '',
			)
		);

		// Build WP_Query args
		$query_args = array(
			'posts_per_page' => $args['multi'],
			'offset'         => $args['offset'],
		);

		// Add ordering (random by default, sequential if specified)
		$query_args = array_merge( $query_args, QueryHelpers::build_order_args( $args['sequence'] ) );

		// Get quotes with optional category filtering
		$category_slugs = QueryHelpers::parse_category_slugs( $args['categories'] );
		$quotes = QueryHelpers::get_filtered_quotes( $this->quote_queries, $category_slugs, $query_args );

		if ( empty( $quotes ) ) {
			return '';
		}

		// Handle multi-quote vs single quote output
		if ( $args['multi'] > 1 ) {
			$quote_html = $this->renderer->render_multiple_quotes( $quotes, $args['disableaspect'] );
		} else {
			$quote_html = $this->renderer->render_quote( $quotes[0], false, $args['disableaspect'] );
		}

		if ( empty( $quote_html ) ) {
			return '';
		}

		// Wrap with AJAX functionality if enabled
		if ( $args['enable_ajax'] ) {
			return $this->wrap_with_ajax( $quote_html, $args );
		}

		return $quote_html;
	}

	/**
	 * Wrap quote HTML with AJAX container and refresh link
	 *
	 * @param string $quote_html The rendered quote HTML.
	 * @param array  $args       Arguments array with AJAX configuration.
	 * @return string HTML wrapped with AJAX functionality.
	 */
	private function wrap_with_ajax( $quote_html, $args ) {
		// Generate or use provided container ID
		$container_id = ! empty( $args['container_id'] ) ? $args['container_id'] : 'xv-quote-container-' . uniqid();

		// Build container with data attributes
		$output = '<div id="' . esc_attr( $container_id ) . '" class="xv-quote-container"';
		$output .= ' data-categories="' . esc_attr( $args['categories'] ) . '"';
		$output .= ' data-sequence="' . esc_attr( $args['sequence'] ? '1' : '0' ) . '"';
		$output .= ' data-multi="' . esc_attr( $args['multi'] ) . '"';
		$output .= ' data-disableaspect="' . esc_attr( $args['disableaspect'] ? '1' : '0' ) . '"';

		if ( ! empty( $args['contributor'] ) ) {
			$output .= ' data-contributor="' . esc_attr( $args['contributor'] ) . '"';
		}

		// Always include timer attribute (even if 0)
		$output .= ' data-timer="' . esc_attr( absint( $args['timer'] ) ) . '"';

		$output .= '>';
		$output .= $quote_html;

		// Add refresh link (only if not auto-refresh only)
		$loader_text = ! empty( $args['link_phrase'] ) ? $args['link_phrase'] : get_option( Settings::OPTION_LOADER, '' );
		if ( empty( $loader_text ) ) {
			$loader_text = __( 'Get another quote', 'xv-random-quotes' );
		}

		$output .= '<div class="xv-quote-refresh-wrapper">';
		$output .= '<a href="#" class="xv-quote-refresh" data-container="' . esc_attr( $container_id ) . '">';
		$output .= esc_html( $loader_text );
		$output .= '</a>';
		$output .= '</div>';
		$output .= '</div>';

		// Enqueue AJAX script
		$this->enqueue_refresh_script();

		return $output;
	}

	/**
	 * Enqueue the quote refresh JavaScript and localize with REST API data
	 */
	private function enqueue_refresh_script() {
		// Only enqueue once
		if ( wp_script_is( 'xv-quote-refresh', 'enqueued' ) ) {
			return;
		}

		$script_url = plugins_url( 'js/quote-refresh.js', dirname( dirname( __FILE__ ) ) );

		wp_enqueue_script(
			'xv-quote-refresh',
			$script_url,
			array(),
			'2.0.0',
			true
		);

		// Localize script with REST endpoint data (only once)
		if ( ! wp_script_is( 'xv-quote-refresh', 'localized' ) ) {
			wp_localize_script(
				'xv-quote-refresh',
				'xvQuoteRefresh',
				array(
					'restUrl'   => esc_url_raw( rest_url( 'xv-random-quotes/v1/quote/random' ) ),
					'restNonce' => wp_create_nonce( 'wp_rest' ),
				)
			);
		}
	}

	/**
	 * Get specific quote output by ID
	 *
	 * @param int $id Quote ID (post ID or legacy ID).
	 * @param bool $disable_aspect Whether to disable formatting.
	 * @return string HTML output.
	 */
	public function get_quote_by_id( $id = 1, $disable_aspect = false ) {
		// Try to get quote by legacy ID first, then by post ID
		$quote_post = $this->quote_queries->get_quote_by_legacy_id( $id );

		if ( ! $quote_post ) {
			$quote_post = $this->quote_queries->get_quote_by_id( $id );
		}

		// If still not found and ID is default (1), get first available quote
		if ( ! $quote_post && $id === 1 ) {
			$quotes = $this->quote_queries->get_all_quotes( array( 'posts_per_page' => 1 ) );
			if ( ! empty( $quotes ) ) {
				$quote_post = $quotes[0];
			}
		}

		if ( ! $quote_post ) {
			return '';
		}

		return $this->renderer->render_quote( $quote_post, false, $disable_aspect );
	}
}
