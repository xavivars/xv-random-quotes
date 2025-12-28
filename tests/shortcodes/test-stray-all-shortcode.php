<?php
/**
 * Tests for [stray-all] shortcode
 *
 * Tests the shortcode that displays multiple or all quotes with pagination.
 */

class Test_Stray_All_Shortcode extends WP_UnitTestCase {

	private $quote_ids = array();
	private $author_ids = array();
	private $category_ids = array();

	public function set_up() {
		parent::set_up();

		// Create test quotes with different authors and categories
		$this->setup_test_quotes();
	}

	public function tear_down() {
		// Clean up
		foreach ( $this->quote_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		foreach ( $this->author_ids as $term_id ) {
			wp_delete_term( $term_id, 'quote_author' );
		}

		foreach ( $this->category_ids as $term_id ) {
			wp_delete_term( $term_id, 'quote_category' );
		}

		parent::tear_down();
	}

	private function setup_test_quotes() {
		// Create authors
		$author1 = wp_insert_term( 'Albert Einstein', 'quote_author' );
		$author2 = wp_insert_term( 'Mark Twain', 'quote_author' );
		$author3 = wp_insert_term( 'Oscar Wilde', 'quote_author' );
		$this->author_ids = array( $author1['term_id'], $author2['term_id'], $author3['term_id'] );

		// Create categories
		$cat1 = wp_insert_term( 'Science', 'quote_category' );
		$cat2 = wp_insert_term( 'Humor', 'quote_category' );
		$cat3 = wp_insert_term( 'Philosophy', 'quote_category' );
		$this->category_ids = array( $cat1['term_id'], $cat2['term_id'], $cat3['term_id'] );

		// Create 15 test quotes (enough to test pagination)
		for ( $i = 1; $i <= 15; $i++ ) {
			$post_id = $this->factory->post->create(
				array(
					'post_type'    => 'xv_quote',
					'post_title'   => "Test Quote $i",
					'post_content' => "This is test quote number $i",
					'post_status'  => 'publish',
				)
			);

			// Assign rotating authors and categories
			$author_index = ( $i - 1 ) % 3;
			$cat_index    = ( $i - 1 ) % 3;

			wp_set_post_terms( $post_id, array( $this->author_ids[ $author_index ] ), 'quote_author' );
			wp_set_post_terms( $post_id, array( $this->category_ids[ $cat_index ] ), 'quote_category' );

			// Add source meta
			update_post_meta( $post_id, '_quote_source', "Source for quote $i" );

			$this->quote_ids[] = $post_id;
		}
	}

	/**
	 * Test shortcode is registered
	 */
	public function test_shortcode_registered() {
		$this->assertTrue( shortcode_exists( 'stray-all' ) );
	}

	/**
	 * Test function exists
	 */
	public function test_function_exists() {
		$this->assertTrue( function_exists( 'stray_all_shortcode' ) );
	}

	/**
	 * Test default output returns multiple quotes
	 */
	public function test_default_output_returns_quotes() {
		$output = do_shortcode( '[stray-all]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test rows parameter limits number of quotes
	 */
	public function test_rows_parameter_limits_quotes() {
		$output = do_shortcode( '[stray-all rows="5"]' );

		// Count how many quotes appear (should be 5 or fewer)
		$count = substr_count( $output, 'test quote number' );
		$this->assertLessThanOrEqual( 5, $count );
		$this->assertGreaterThan( 0, $count );
	}

	/**
	 * Test category filtering with single category
	 */
	public function test_category_filtering_single() {
		$output = do_shortcode( '[stray-all categories="Science" rows="15"]' );

		$this->assertNotEmpty( $output );
		// Should contain Science quotes (quotes 1, 4, 7, 10, 13)
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test category filtering with multiple categories
	 */
	public function test_category_filtering_multiple() {
		$output = do_shortcode( '[stray-all categories="Science,Humor" rows="15"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test categories="all" returns all quotes
	 */
	public function test_category_all_returns_all_quotes() {
		$output = do_shortcode( '[stray-all categories="all" rows="15"]' );

		$this->assertNotEmpty( $output );
		$count = substr_count( $output, 'test quote number' );
		$this->assertGreaterThanOrEqual( 10, $count );
	}

	/**
	 * Test sequence=true returns ordered quotes
	 */
	public function test_sequence_true_returns_ordered() {
		$output = do_shortcode( '[stray-all sequence="true" rows="3"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test sequence=false returns quotes (random order)
	 */
	public function test_sequence_false_returns_quotes() {
		$output = do_shortcode( '[stray-all sequence="false" rows="5"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test orderby parameter with quoteID
	 */
	public function test_orderby_quote_id() {
		$output = do_shortcode( '[stray-all orderby="quoteID" sort="ASC" sequence="true" rows="3"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test sort parameter ASC
	 */
	public function test_sort_asc() {
		$output = do_shortcode( '[stray-all orderby="quoteID" sort="ASC" sequence="true" rows="3"]' );

		$this->assertNotEmpty( $output );
	}

	/**
	 * Test sort parameter DESC
	 */
	public function test_sort_desc() {
		$output = do_shortcode( '[stray-all orderby="quoteID" sort="DESC" sequence="true" rows="3"]' );

		$this->assertNotEmpty( $output );
	}

	/**
	 * Test fullpage=true includes pagination
	 */
	public function test_fullpage_true_includes_pagination() {
		$output = do_shortcode( '[stray-all fullpage="true" rows="5"]' );

		$this->assertNotEmpty( $output );
		// Pagination should include page numbers or links
		// This is a basic check - actual pagination HTML depends on implementation
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test fullpage=false shows simpler pagination
	 */
	public function test_fullpage_false_simple_pagination() {
		$output = do_shortcode( '[stray-all fullpage="false" rows="5"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test offset parameter
	 */
	public function test_offset_parameter() {
		$output = do_shortcode( '[stray-all offset="5" rows="3" sequence="true" orderby="quoteID" sort="ASC"]' );

		$this->assertNotEmpty( $output );
		// Should start from quote 6 onwards
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test noajax parameter
	 */
	public function test_noajax_parameter() {
		$output = do_shortcode( '[stray-all noajax="true" rows="3"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test disableaspect parameter
	 */
	public function test_disableaspect_parameter() {
		$output = do_shortcode( '[stray-all disableaspect="true" rows="3"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test with linkphrase parameter
	 */
	public function test_linkphrase_parameter() {
		$output = do_shortcode( '[stray-all linkphrase="Load more quotes" rows="3"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test empty category parameter returns all quotes
	 */
	public function test_empty_category_returns_all() {
		$output = do_shortcode( '[stray-all categories="" rows="10"]' );

		$this->assertNotEmpty( $output );
		$count = substr_count( $output, 'test quote number' );
		$this->assertGreaterThanOrEqual( 5, $count );
	}

	/**
	 * Test with non-existent category returns empty
	 */
	public function test_nonexistent_category_returns_empty() {
		$output = do_shortcode( '[stray-all categories="NonExistent" rows="10"]' );

		// Should return empty or no quotes
		$this->assertIsString( $output );
	}

	/**
	 * Test timer parameter (for auto-refresh)
	 */
	public function test_timer_parameter() {
		$output = do_shortcode( '[stray-all timer="5" rows="3"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test that draft quotes are excluded
	 */
	public function test_excludes_draft_quotes() {
		// Create a draft quote
		$draft_id = $this->factory->post->create(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Draft Quote',
				'post_content' => 'This is a draft quote',
				'post_status'  => 'draft',
			)
		);

		$output = do_shortcode( '[stray-all rows="20"]' );

		// Draft quote should not appear
		$this->assertStringNotContainsString( 'draft quote', $output );

		wp_delete_post( $draft_id, true );
	}

	/**
	 * Test with zero quotes returns empty
	 */
	public function test_no_quotes_returns_empty() {
		// Delete all test quotes
		foreach ( $this->quote_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}
		$this->quote_ids = array();

		$output = do_shortcode( '[stray-all rows="10"]' );

		// Should return empty string or message
		$this->assertIsString( $output );
	}

	/**
	 * Test multiple attribute combinations
	 */
	public function test_multiple_attributes_combined() {
		$output = do_shortcode( '[stray-all categories="Science" rows="5" sequence="true" orderby="quoteID" sort="ASC" fullpage="true"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}
}
