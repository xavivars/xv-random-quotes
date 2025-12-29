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
	 * Get random quote(s) output
	 *
	 * @param string $categories Category slugs (comma-separated or 'all').
	 * @param bool $sequence Whether to use sequential ordering.
	 * @param int $multi Number of quotes to retrieve.
	 * @param int $offset Offset for pagination.
	 * @param bool $disable_aspect Whether to disable formatting.
	 * @param string $user Filter by user/contributor.
	 * @return string HTML output.
	 */
	public function get_random_quotes( $categories = 'all', $sequence = false, $multi = 1, $offset = 0, $disable_aspect = false, $user = '' ) {
		// Build WP_Query args
		$query_args = array(
			'posts_per_page' => $multi,
			'offset' => $offset,
		);

		// Add ordering (random by default, sequential if specified)
		$query_args = array_merge( $query_args, QueryHelpers::build_order_args( $sequence ) );

		// Get quotes with optional category filtering
		$category_slugs = QueryHelpers::parse_category_slugs( $categories );
		$quotes = QueryHelpers::get_filtered_quotes( $this->quote_queries, $category_slugs, $query_args );

		if ( empty( $quotes ) ) {
			return '';
		}

		// Handle multi-quote vs single quote output
		if ( $multi > 1 ) {
			return $this->renderer->render_multiple_quotes( $quotes, $disable_aspect );
		} else {
			return $this->renderer->render_quote( $quotes[0], false, $disable_aspect );
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
