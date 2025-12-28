<?php
/**
 * Tests for Quote Query Helper Functions
 *
 * @package XVRandomQuotes
 */

use XVRandomQuotes\Queries\QuoteQueries;

/**
 * Test Quote Query Helper Functions
 */
class Test_Quote_Queries extends WP_UnitTestCase {

	/**
	 * Quote Queries instance
	 *
	 * @var QuoteQueries
	 */
	private $queries;

	/**
	 * Test quote IDs
	 *
	 * @var array
	 */
	private $quote_ids = array();

	/**
	 * Set up test fixtures
	 */
	public function setUp(): void {
		parent::setUp();

		$this->queries = new QuoteQueries();

		// Create test quotes with different authors and categories
		$this->quote_ids[] = $this->factory->post->create(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Test Quote 1',
				'post_content' => 'This is test quote 1',
				'post_status'  => 'publish',
			)
		);

		$this->quote_ids[] = $this->factory->post->create(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Test Quote 2',
				'post_content' => 'This is test quote 2',
				'post_status'  => 'publish',
			)
		);

		$this->quote_ids[] = $this->factory->post->create(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Test Quote 3',
				'post_content' => 'This is test quote 3',
				'post_status'  => 'publish',
			)
		);

		// Create draft quote (should be excluded from most queries)
		$this->quote_ids[] = $this->factory->post->create(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Draft Quote',
				'post_content' => 'This is a draft quote',
				'post_status'  => 'draft',
			)
		);

		// Assign taxonomies
		wp_set_object_terms( $this->quote_ids[0], 'einstein', 'quote_author' );
		wp_set_object_terms( $this->quote_ids[1], 'einstein', 'quote_author' );
		wp_set_object_terms( $this->quote_ids[2], 'shakespeare', 'quote_author' );

		wp_set_object_terms( $this->quote_ids[0], 'science', 'quote_category' );
		wp_set_object_terms( $this->quote_ids[1], 'philosophy', 'quote_category' );
		wp_set_object_terms( $this->quote_ids[2], 'literature', 'quote_category' );
	}

	/**
	 * Test get_random_quote() returns a quote
	 */
	public function test_get_random_quote_returns_quote() {
		$quote = $this->queries->get_random_quote();

		$this->assertInstanceOf( 'WP_Post', $quote );
		$this->assertEquals( 'xv_quote', $quote->post_type );
		$this->assertEquals( 'publish', $quote->post_status );
		$this->assertContains( $quote->ID, $this->quote_ids );
	}

	/**
	 * Test get_random_quote() excludes drafts by default
	 */
	public function test_get_random_quote_excludes_drafts() {
		// Run multiple times since it's random
		for ( $i = 0; $i < 10; $i++ ) {
			$quote = $this->queries->get_random_quote();
			$this->assertNotEquals( $this->quote_ids[3], $quote->ID, 'Draft quote should not be returned' );
		}
	}

	/**
	 * Test get_random_quote() with custom args
	 */
	public function test_get_random_quote_with_custom_args() {
		$quote = $this->queries->get_random_quote( array( 'post_status' => 'draft' ) );

		$this->assertInstanceOf( 'WP_Post', $quote );
		$this->assertEquals( 'draft', $quote->post_status );
	}

	/**
	 * Test get_quote_by_id() returns specific quote
	 */
	public function test_get_quote_by_id_returns_specific_quote() {
		$quote = $this->queries->get_quote_by_id( $this->quote_ids[0] );

		$this->assertInstanceOf( 'WP_Post', $quote );
		$this->assertEquals( $this->quote_ids[0], $quote->ID );
		$this->assertEquals( 'xv_quote', $quote->post_type );
	}

	/**
	 * Test get_quote_by_id() returns null for non-existent ID
	 */
	public function test_get_quote_by_id_returns_null_for_invalid_id() {
		$quote = $this->queries->get_quote_by_id( 999999 );

		$this->assertNull( $quote );
	}

	/**
	 * Test get_quote_by_id() returns null for non-quote post type
	 */
	public function test_get_quote_by_id_returns_null_for_wrong_post_type() {
		// Create a regular post
		$post_id = $this->factory->post->create(
			array(
				'post_type' => 'post',
			)
		);

		$quote = $this->queries->get_quote_by_id( $post_id );

		$this->assertNull( $quote );
	}

	/**
	 * Test get_quote_by_legacy_id() returns quote by legacy ID
	 */
	public function test_get_quote_by_legacy_id_returns_quote() {
		// Add legacy ID to first quote
		update_post_meta( $this->quote_ids[0], '_quote_legacy_id', 123 );

		$quote = $this->queries->get_quote_by_legacy_id( 123 );

		$this->assertInstanceOf( 'WP_Post', $quote );
		$this->assertEquals( $this->quote_ids[0], $quote->ID );
		$this->assertEquals( 'xv_quote', $quote->post_type );
	}

	/**
	 * Test get_quote_by_legacy_id() returns null for non-existent legacy ID
	 */
	public function test_get_quote_by_legacy_id_returns_null_for_invalid_id() {
		$quote = $this->queries->get_quote_by_legacy_id( 999999 );

		$this->assertNull( $quote );
	}

	/**
	 * Test get_quote_by_legacy_id() with multiple quotes having legacy IDs
	 */
	public function test_get_quote_by_legacy_id_with_multiple_legacy_ids() {
		// Add legacy IDs to multiple quotes
		update_post_meta( $this->quote_ids[0], '_quote_legacy_id', 100 );
		update_post_meta( $this->quote_ids[1], '_quote_legacy_id', 200 );
		update_post_meta( $this->quote_ids[2], '_quote_legacy_id', 300 );

		$quote = $this->queries->get_quote_by_legacy_id( 200 );

		$this->assertInstanceOf( 'WP_Post', $quote );
		$this->assertEquals( $this->quote_ids[1], $quote->ID );
	}

	/**
	 * Test get_quotes_by_author() returns quotes by specific author
	 */
	public function test_get_quotes_by_author_returns_author_quotes() {
		$quotes = $this->queries->get_quotes_by_author( 'einstein' );

		$this->assertIsArray( $quotes );
		$this->assertCount( 2, $quotes );

		foreach ( $quotes as $quote ) {
			$this->assertInstanceOf( 'WP_Post', $quote );
			$this->assertEquals( 'xv_quote', $quote->post_type );
			$this->assertTrue( has_term( 'einstein', 'quote_author', $quote ) );
		}
	}

	/**
	 * Test get_quotes_by_author() returns empty array for non-existent author
	 */
	public function test_get_quotes_by_author_returns_empty_for_invalid_author() {
		$quotes = $this->queries->get_quotes_by_author( 'nonexistent' );

		$this->assertIsArray( $quotes );
		$this->assertEmpty( $quotes );
	}

	/**
	 * Test get_quotes_by_author() with custom args
	 */
	public function test_get_quotes_by_author_with_custom_args() {
		$quotes = $this->queries->get_quotes_by_author( 'einstein', array( 'posts_per_page' => 1 ) );

		$this->assertIsArray( $quotes );
		$this->assertCount( 1, $quotes );
	}

	/**
	 * Test get_quotes_by_category() returns quotes by category
	 */
	public function test_get_quotes_by_category_returns_category_quotes() {
		$quotes = $this->queries->get_quotes_by_category( 'science' );

		$this->assertIsArray( $quotes );
		$this->assertCount( 1, $quotes );

		foreach ( $quotes as $quote ) {
			$this->assertInstanceOf( 'WP_Post', $quote );
			$this->assertEquals( 'xv_quote', $quote->post_type );
			$this->assertTrue( has_term( 'science', 'quote_category', $quote ) );
		}
	}

	/**
	 * Test get_quotes_by_category() returns empty array for non-existent category
	 */
	public function test_get_quotes_by_category_returns_empty_for_invalid_category() {
		$quotes = $this->queries->get_quotes_by_category( 'nonexistent' );

		$this->assertIsArray( $quotes );
		$this->assertEmpty( $quotes );
	}

	/**
	 * Test get_quotes_by_category() with custom args
	 */
	public function test_get_quotes_by_category_with_custom_args() {
		$quotes = $this->queries->get_quotes_by_category( 'science', array( 'post_status' => 'any' ) );

		$this->assertIsArray( $quotes );
		$this->assertGreaterThanOrEqual( 1, count( $quotes ) );
	}

	/**
	 * Test get_all_quotes() returns all published quotes
	 */
	public function test_get_all_quotes_returns_all_quotes() {
		$quotes = $this->queries->get_all_quotes();

		$this->assertIsArray( $quotes );
		$this->assertCount( 3, $quotes, 'Should return 3 published quotes' );

		foreach ( $quotes as $quote ) {
			$this->assertInstanceOf( 'WP_Post', $quote );
			$this->assertEquals( 'xv_quote', $quote->post_type );
			$this->assertEquals( 'publish', $quote->post_status );
		}
	}

	/**
	 * Test get_all_quotes() with custom args
	 */
	public function test_get_all_quotes_with_custom_args() {
		$quotes = $this->queries->get_all_quotes( array( 'post_status' => 'any' ) );

		$this->assertIsArray( $quotes );
		$this->assertCount( 4, $quotes, 'Should return all 4 quotes including draft' );
	}

	/**
	 * Test get_all_quotes() with pagination
	 */
	public function test_get_all_quotes_with_pagination() {
		$quotes = $this->queries->get_all_quotes( array( 'posts_per_page' => 2 ) );

		$this->assertIsArray( $quotes );
		$this->assertCount( 2, $quotes );
	}

	/**
	 * Test get_all_quotes() with orderby
	 */
	public function test_get_all_quotes_with_orderby() {
		$quotes = $this->queries->get_all_quotes( array( 'orderby' => 'title', 'order' => 'ASC' ) );

		$this->assertIsArray( $quotes );
		$this->assertEquals( 'Test Quote 1', $quotes[0]->post_title );
		$this->assertEquals( 'Test Quote 2', $quotes[1]->post_title );
		$this->assertEquals( 'Test Quote 3', $quotes[2]->post_title );
	}

	/**
	 * Test that all methods use WP_Query and not raw SQL
	 */
	public function test_methods_use_wp_query() {
		// This is more of a code review test, but we can verify
		// that the results are WP_Post objects which indicates WP_Query usage
		$random_quote = $this->queries->get_random_quote();
		$quote_by_id = $this->queries->get_quote_by_id( $this->quote_ids[0] );
		$quotes_by_author = $this->queries->get_quotes_by_author( 'einstein' );

		$this->assertInstanceOf( 'WP_Post', $random_quote );
		$this->assertInstanceOf( 'WP_Post', $quote_by_id );
		$this->assertContainsOnlyInstancesOf( 'WP_Post', $quotes_by_author );
	}
}
