<?php
/**
 * Query Helper Utilities
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Utils;

use XVRandomQuotes\Queries\QuoteQueries;

/**
 * Class QueryHelpers
 *
 * Utilities for building and executing quote queries.
 */
class QueryHelpers {

	/**
	 * Build WP_Query order arguments
	 *
	 * @param bool $sequence Whether to use sequential ordering (true) or random (false).
	 * @param string $orderby Field to order by (legacy values supported).
	 * @param string $sort Sort direction: 'ASC' or 'DESC'.
	 * @return array WP_Query order arguments.
	 */
	public static function build_order_args( $sequence, $orderby = 'quoteID', $sort = 'ASC' ) {
		$args = array();

		if ( ! $sequence ) {
			// Random order
			$args['orderby'] = 'rand';
		} else {
			// Sequential order - map legacy orderby values to CPT fields
			switch ( $orderby ) {
				case 'quoteID':
					$args['orderby'] = 'ID';
					break;
				case 'author':
				case 'Author':
					$args['orderby'] = 'title'; // Best approximation
					break;
				case 'source':
				case 'Source':
					$args['orderby'] = 'title';
					break;
				default:
					$args['orderby'] = 'ID';
			}
			$args['order'] = strtoupper( $sort ) === 'DESC' ? 'DESC' : 'ASC';
		}

		return $args;
	}

	/**
	 * Get quotes with optional category filtering
	 *
	 * @param QuoteQueries $quote_queries QuoteQueries instance.
	 * @param array $category_slugs Array of category slugs to filter by.
	 * @param array $query_args WP_Query arguments.
	 * @return array Array of quote post objects.
	 */
	public static function get_filtered_quotes( $quote_queries, $category_slugs, $query_args ) {
		if ( ! empty( $category_slugs ) ) {
			return $quote_queries->get_quotes_by_categories( $category_slugs, $query_args );
		} else {
			return $quote_queries->get_all_quotes( $query_args );
		}
	}

	/**
	 * Parse category parameter into array of slugs
	 *
	 * @param string $categories Category slugs (comma-separated or 'all').
	 * @return array Array of category slugs, or empty array for 'all'.
	 */
	public static function parse_category_slugs( $categories ) {
		if ( empty( $categories ) || strtolower( $categories ) === 'all' ) {
			return array();
		}

		// Split by comma and trim whitespace
		$slugs = array_map( 'trim', explode( ',', $categories ) );

		// Remove empty values
		return array_filter( $slugs );
	}
}
