<?php
/**
 * REST API Quote Endpoint
 *
 * Provides REST API endpoint for retrieving random quotes.
 * Replaces legacy AJAX handlers with modern WordPress REST API.
 *
 * @package XVRandomQuotes
 * @subpackage RestAPI
 */

namespace XVRandomQuotes\RestAPI;

use XVRandomQuotes\Queries\QuoteQueries;
use XVRandomQuotes\Output\QuoteOutput;

/**
 * Quote REST API endpoint handler
 */
class QuoteEndpoint {

	/**
	 * API namespace
	 *
	 * @var string
	 */
	const NAMESPACE = 'xv-random-quotes/v1';

	/**
	 * Register REST API routes
	 */
	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/quote/random',
			array(
				'methods'             => \WP_REST_Server::READABLE, // GET method
				'callback'            => array( __CLASS__, 'get_random_quote' ),
				'permission_callback' => '__return_true', // Public endpoint
				'args'                => self::get_endpoint_args(),
			)
		);
	}

	/**
	 * Get endpoint parameter schema
	 *
	 * @return array Parameter definitions
	 */
	private static function get_endpoint_args() {
		return array(
			'categories'     => array(
				'description'       => 'Comma-separated list of category slugs to filter by',
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'sequence'       => array(
				'description' => 'Whether to use random ordering (false) or sequential (true)',
				'type'        => 'boolean',
				'default'     => false,
			),
			'multi'          => array(
				'description'       => 'Number of quotes to return',
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'maximum'           => 50,
				'sanitize_callback' => 'absint',
			),
			'disableaspect'  => array(
				'description' => 'Whether to disable aspect/styling',
				'type'        => 'boolean',
				'default'     => false,
			),
			'contributor'    => array(
				'description'       => 'Filter by contributor username',
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Get random quote(s) endpoint handler
	 *
	 * @param WP_REST_Request $request Full request data
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error on failure
	 */
	public static function get_random_quote( $request ) {
		// Get parameters
		$categories    = $request->get_param( 'categories' );
		$sequence      = $request->get_param( 'sequence' );
		$multi         = $request->get_param( 'multi' );
		$disableaspect = $request->get_param( 'disableaspect' );
		$contributor   = $request->get_param( 'contributor' );

		// Get quotes first (before rendering)
		$quote_queries = new QuoteQueries();
		
		// Convert categories string to array if needed
		$categories_array = ! empty( $categories ) ? explode( ',', $categories ) : array();
		
		// Build query args
		$query_args = array(
			'posts_per_page' => $multi,
		);
		
		// Add ordering
		if ( $sequence ) {
			$query_args['orderby'] = 'date';
			$query_args['order'] = 'ASC';
		} else {
			$query_args['orderby'] = 'rand';
		}
		
		// Get quotes
		if ( ! empty( $categories_array ) ) {
			$quotes = $quote_queries->get_quotes_by_categories( $categories_array, $query_args );
		} else {
			$quotes = $quote_queries->get_all_quotes( $query_args );
		}

		// Handle WP_Error
		if ( is_wp_error( $quotes ) ) {
			return new \WP_Error(
				'query_error',
				'Error retrieving quotes',
				array( 'status' => 500 )
			);
		}
		
		// Check if we got any quotes
		if ( empty( $quotes ) ) {
			return new \WP_Error(
				'no_quotes',
				'No quotes found matching the criteria',
				array( 'status' => 404 )
			);
		}

		// Render HTML from the same quotes we'll use for metadata
		$quote_output = new QuoteOutput();
		$renderer = $quote_output->get_renderer();
		
		if ( $multi > 1 ) {
			$html = $renderer->render_multiple_quotes( $quotes, $disableaspect );
		} else {
			$html = $renderer->render_quote( $quotes[0], false, $disableaspect );
		}

		// Build response data
		$response_data = array(
			'html' => $html,
		);

		// Add metadata for single quote
		if ( $multi === 1 && ! empty( $quotes ) && count( $quotes ) > 0 ) {
			$quote = $quotes[0];
			
			$response_data['quote_id']      = $quote->ID;
			$response_data['quote_text']    = wp_strip_all_tags( $quote->post_content );
			$response_data['quote_content'] = $quote->post_content;
			
			// Get author
			$authors = wp_get_post_terms( $quote->ID, 'quote_author' );
			$response_data['author'] = ! empty( $authors ) && ! is_wp_error( $authors ) 
				? $authors[0]->name 
				: '';
			
			// Get source
			$response_data['source'] = get_post_meta( $quote->ID, '_quote_source', true );
			
			// Get categories
			$categories_terms = wp_get_post_terms( $quote->ID, 'quote_category' );
			$response_data['categories'] = array();
			if ( ! empty( $categories_terms ) && ! is_wp_error( $categories_terms ) ) {
				foreach ( $categories_terms as $term ) {
					$response_data['categories'][] = $term->slug;
				}
			}
		} else {
			// For multi-quote, provide basic metadata
			$response_data['quote_id']      = 0;
			$response_data['quote_text']    = '';
			$response_data['quote_content'] = '';
			$response_data['author']        = '';
			$response_data['source']        = '';
			$response_data['categories']    = array();
		}

		return new \WP_REST_Response( $response_data, 200 );
	}
}
