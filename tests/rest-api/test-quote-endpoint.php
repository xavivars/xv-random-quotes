<?php
/**
 * Tests for REST API Quote Endpoint
 *
 * Tests the REST API endpoint for retrieving random quotes.
 * Uses WordPress REST API instead of legacy AJAX handlers.
 *
 * @package XVRandomQuotes
 * @subpackage Tests
 */

/**
 * Test REST API quote endpoint functionality
 */
class Test_Quote_Endpoint extends WP_UnitTestCase {

	/**
	 * REST API server instance
	 *
	 * @var WP_REST_Server
	 */
	protected $server;

	/**
	 * Category IDs for testing
	 *
	 * @var array
	 */
	protected $category_ids = array();

	/**
	 * Quote post IDs for testing
	 *
	 * @var array
	 */
	protected $quote_ids = array();

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Initialize REST API server
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );

		// Create test categories
		$categories = array( 'Science', 'Philosophy', 'Literature', 'HTMLTest' );
		foreach ( $categories as $cat_name ) {
			$term = wp_insert_term( $cat_name, 'quote_category' );
			if ( ! is_wp_error( $term ) ) {
				$this->category_ids[ $cat_name ] = $term['term_id'];
			}
		}

		// Create test quotes
		$quotes_data = array(
			array( 'Science', 'The science of today is the technology of tomorrow.' ),
			array( 'Science', 'Science knows no country, because knowledge belongs to humanity.' ),
			array( 'Philosophy', 'I think, therefore I am.' ),
			array( 'Philosophy', 'The unexamined life is not worth living.' ),
			array( 'Literature', 'To be or not to be, that is the question.' ),
			array( 'HTMLTest', '<strong>Bold text</strong> and <em>italic text</em>.' ),
		);

		foreach ( $quotes_data as $index => $quote_data ) {
			list( $category, $quote_text ) = $quote_data;
			
			$post_id = wp_insert_post(
				array(
					'post_type'    => 'xv_quote',
					'post_title'   => $quote_text,
					'post_content' => $quote_text,
					'post_status'  => 'publish',
				)
			);

			$this->quote_ids[] = $post_id;

			// Add category
			wp_set_post_terms( $post_id, array( $this->category_ids[ $category ] ), 'quote_category' );

			// Add author
			$author_term = wp_insert_term( "Author $index", 'quote_author' );
			if ( ! is_wp_error( $author_term ) ) {
				wp_set_post_terms( $post_id, array( $author_term['term_id'] ), 'quote_author' );
			}

			// Add source
			update_post_meta( $post_id, '_quote_source', "Source $index" );
		}
	}

	/**
	 * Clean up after each test
	 */
	public function tearDown(): void {
		global $wp_rest_server;
		$wp_rest_server = null;

		// Delete quotes
		foreach ( $this->quote_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		// Delete terms
		$terms = get_terms(
			array(
				'taxonomy'   => array( 'quote_author', 'quote_category' ),
				'hide_empty' => false,
			)
		);
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $term->taxonomy );
		}

		parent::tearDown();
	}

	/**
	 * Test REST route is registered
	 */
	public function test_route_is_registered() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/xv-random-quotes/v1/quote/random', $routes );
	}

	/**
	 * Test route namespace
	 */
	public function test_route_has_correct_namespace() {
		$routes = $this->server->get_routes();
		$route  = $routes['/xv-random-quotes/v1/quote/random'];
		
		$this->assertNotEmpty( $route );
	}

	/**
	 * Test endpoint accepts GET requests
	 */
	public function test_endpoint_accepts_get_requests() {
		$request  = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$response = $this->server->dispatch( $request );
		
		// If GET is not allowed, we'd get a 404 or 405 error
		$this->assertNotEquals( 404, $response->get_status(), 'Endpoint should exist' );
		$this->assertNotEquals( 405, $response->get_status(), 'GET method should be allowed' );
		$this->assertEquals( 200, $response->get_status(), 'GET request should succeed' );
	}

	/**
	 * Test endpoint is publicly accessible (no auth required)
	 */
	public function test_endpoint_is_publicly_accessible() {
		$request  = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$response = $this->server->dispatch( $request );
		
		// Should not return 401 Unauthorized
		$this->assertNotEquals( 401, $response->get_status() );
	}

	/**
	 * Test endpoint returns random quote
	 */
	public function test_endpoint_returns_random_quote() {
		$request  = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$response = $this->server->dispatch( $request );
		
		$this->assertEquals( 200, $response->get_status() );
		
		$data = $response->get_data();
		$this->assertArrayHasKey( 'html', $data );
		$this->assertNotEmpty( $data['html'] );
	}

	/**
	 * Test endpoint response structure
	 */
	public function test_endpoint_response_structure() {
		$request  = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$response = $this->server->dispatch( $request );
		
		$data = $response->get_data();
		
		// Check required fields
		$this->assertArrayHasKey( 'html', $data );
		$this->assertArrayHasKey( 'quote_id', $data );
		$this->assertArrayHasKey( 'quote_text', $data );
		$this->assertArrayHasKey( 'quote_content', $data );
		$this->assertArrayHasKey( 'author', $data );
		$this->assertArrayHasKey( 'source', $data );
		$this->assertArrayHasKey( 'categories', $data );
	}

	/**
	 * Test endpoint filters by single category
	 */
	public function test_endpoint_filters_by_single_category() {
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'categories', 'science' ); // Use lowercase slug
		
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		
		$this->assertEquals( 200, $response->get_status() );
		$this->assertStringContainsString( 'science', strtolower( $data['html'] ) );
	}

	/**
	 * Test endpoint filters by multiple categories
	 */
	public function test_endpoint_filters_by_multiple_categories() {
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'categories', 'science,philosophy' ); // Use lowercase slugs
		
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		
		$this->assertEquals( 200, $response->get_status() );
		
		// Verify the returned quote is from one of the requested categories
		$this->assertIsArray( $data['categories'] );
		$has_matching_category = in_array( 'science', $data['categories'], true ) || 
		                         in_array( 'philosophy', $data['categories'], true );
		$this->assertTrue( $has_matching_category, 'Quote should be from Science or Philosophy category' );
	}

	/**
	 * Test endpoint with sequence parameter (random vs sequential)
	 */
	public function test_endpoint_with_sequence_parameter() {
		// Test random (sequence=true)
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'sequence', true );
		
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		
		// Test sequential (sequence=false)
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'sequence', false );
		
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	/**
	 * Test endpoint with multi parameter
	 */
	public function test_endpoint_with_multi_parameter() {
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'multi', 3 );
		$request->set_param( 'categories', 'Science,Philosophy' );
		
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		
		$this->assertEquals( 200, $response->get_status() );
		
		// Multi-quote should contain <ul> structure
		$this->assertStringContainsString( '<ul>', $data['html'] );
		$this->assertStringContainsString( '<li>', $data['html'] );
	}

	/**
	 * Test endpoint with disableaspect parameter
	 */
	public function test_endpoint_with_disableaspect_parameter() {
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'disableaspect', true );
		
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		
		$this->assertEquals( 200, $response->get_status() );
		$this->assertNotEmpty( $data['html'] );
	}

	/**
	 * Test endpoint with contributor parameter
	 */
	public function test_endpoint_with_contributor_parameter() {
		// Create a quote with specific contributor
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Contributor Quote',
				'post_content' => 'Quote from specific contributor.',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $post_id, '_quote_contributor', 'john_doe' );

		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'contributor', 'john_doe' );
		
		$response = $this->server->dispatch( $request );
		
		$this->assertEquals( 200, $response->get_status() );
		
		// Clean up
		wp_delete_post( $post_id, true );
	}

	/**
	 * Test endpoint handles invalid category gracefully
	 */
	public function test_endpoint_handles_invalid_category() {
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'categories', 'NonexistentCategory' );
		
		$response = $this->server->dispatch( $request );
		
		// Should return 404 or empty result, not error
		$this->assertTrue( 
			$response->get_status() === 404 || 
			$response->get_status() === 200 
		);
	}

	/**
	 * Test endpoint handles no quotes found
	 */
	public function test_endpoint_handles_no_quotes_found() {
		// Delete all quotes
		foreach ( $this->quote_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}
		$this->quote_ids = array();
		
		$request  = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$response = $this->server->dispatch( $request );
		
		// Should return 404 when no quotes exist
		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Test endpoint returns proper JSON
	 */
	public function test_endpoint_returns_json() {
		$request  = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$response = $this->server->dispatch( $request );
		
		// Response should be WP_REST_Response
		$this->assertInstanceOf( 'WP_REST_Response', $response );
		
		// Data should be an array
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'html', $data );
	}

	/**
	 * Test parameter validation for categories
	 */
	public function test_parameter_validation_categories() {
		$routes = $this->server->get_routes();
		$route  = $routes['/xv-random-quotes/v1/quote/random'];
		
		// Check if categories parameter has schema
		$endpoint = $route[0];
		$this->assertArrayHasKey( 'args', $endpoint );
		
		if ( isset( $endpoint['args']['categories'] ) ) {
			$this->assertArrayHasKey( 'type', $endpoint['args']['categories'] );
		}
	}

	/**
	 * Test parameter validation for sequence
	 */
	public function test_parameter_validation_sequence() {
		$routes = $this->server->get_routes();
		$route  = $routes['/xv-random-quotes/v1/quote/random'];
		
		$endpoint = $route[0];
		
		if ( isset( $endpoint['args']['sequence'] ) ) {
			$this->assertArrayHasKey( 'type', $endpoint['args']['sequence'] );
			$this->assertEquals( 'boolean', $endpoint['args']['sequence']['type'] );
		}
	}

	/**
	 * Test parameter validation for multi
	 */
	public function test_parameter_validation_multi() {
		$routes = $this->server->get_routes();
		$route  = $routes['/xv-random-quotes/v1/quote/random'];
		
		$endpoint = $route[0];
		
		if ( isset( $endpoint['args']['multi'] ) ) {
			$this->assertArrayHasKey( 'type', $endpoint['args']['multi'] );
			$this->assertEquals( 'integer', $endpoint['args']['multi']['type'] );
		}
	}

	/**
	 * Test endpoint with all parameters combined
	 */
	public function test_endpoint_with_all_parameters() {
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'categories', 'Science,Philosophy' );
		$request->set_param( 'sequence', true );
		$request->set_param( 'multi', 2 );
		$request->set_param( 'disableaspect', false );
		
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		
		$this->assertEquals( 200, $response->get_status() );
		$this->assertNotEmpty( $data['html'] );
	}

	/**
	 * Test endpoint response includes metadata
	 */
	public function test_endpoint_response_includes_metadata() {
		$request  = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		
		// Check metadata fields
		$this->assertIsInt( $data['quote_id'] );
		$this->assertIsString( $data['quote_text'] );
		$this->assertIsString( $data['quote_content'] );
		$this->assertIsString( $data['author'] );
		$this->assertIsArray( $data['categories'] );
	}

	/**
	 * Test multiple requests return different quotes (randomness)
	 */
	public function test_endpoint_randomness() {
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'sequence', false ); // Disable sequential (use random)
		
		$quote_ids = array();
		
		// Make 20 requests to increase probability of seeing different quotes
		for ( $i = 0; $i < 20; $i++ ) {
			$response = $this->server->dispatch( $request );
			$data     = $response->get_data();
			$quote_ids[] = $data['quote_id'];
		}
		
		// With 6 quotes and 20 requests using random selection,
		// we should see at least 3 different quotes
		$unique_quotes = array_unique( $quote_ids );
		$this->assertGreaterThanOrEqual( 3, count( $unique_quotes ), 
			'Should get at least 3 different quotes from 20 random requests' );
	}

	/**
	 * Test endpoint with empty categories parameter (all categories)
	 */
	public function test_endpoint_with_empty_categories() {
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'categories', '' );
		
		$response = $this->server->dispatch( $request );
		
		$this->assertEquals( 200, $response->get_status() );
	}

	/**
	 * Test quote_text vs quote_content distinction
	 */
	public function test_quote_text_vs_quote_content() {
		// Request quote from HTMLTest category (created in setUp with HTML content)
		$request = new WP_REST_Request( 'GET', '/xv-random-quotes/v1/quote/random' );
		$request->set_param( 'categories', 'htmltest' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		// quote_content should contain HTML tags
		$this->assertStringContainsString( '<strong>', $data['quote_content'] );
		$this->assertStringContainsString( '<em>', $data['quote_content'] );

		// quote_text should be plain text (no HTML tags)
		$this->assertStringNotContainsString( '<strong>', $data['quote_text'] );
		$this->assertStringNotContainsString( '<em>', $data['quote_text'] );
		$this->assertStringContainsString( 'Bold text', $data['quote_text'] );
		$this->assertStringContainsString( 'italic text', $data['quote_text'] );
	}
}

