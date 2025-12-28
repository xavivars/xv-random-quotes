<?php
/**
 * Tests for [stray-random] shortcode
 *
 * @package XVRandomQuotes
 */

class Test_Stray_Random_Shortcode extends WP_UnitTestCase {

	protected $quotes = array();

	public function setUp(): void {
		parent::setUp();
		
		// Create test quotes with categories and authors
		$categories = array('inspiration', 'humor', 'wisdom');
		$authors = array('Albert Einstein', 'Mark Twain', 'Oscar Wilde');
		
		for ($i = 1; $i <= 10; $i++) {
			$quote_id = wp_insert_post(array(
				'post_type' => 'xv_quote',
				'post_title' => 'Test Quote ' . $i,
				'post_content' => 'This is test quote number ' . $i,
				'post_status' => 'publish',
			));
			
			// Assign category
			$category = $categories[$i % 3];
			wp_set_object_terms($quote_id, $category, 'quote_category');
			
			// Assign author
			$author = $authors[$i % 3];
			wp_set_object_terms($quote_id, $author, 'quote_author');
			
			// Add source
			update_post_meta($quote_id, '_quote_source', 'Source for quote ' . $i);
			
			$this->quotes[] = $quote_id;
		}
		
		// Create a draft quote (should not appear)
		$draft_id = wp_insert_post(array(
			'post_type' => 'xv_quote',
			'post_title' => 'Draft Quote',
			'post_content' => 'This is a draft quote',
			'post_status' => 'draft',
		));
		wp_set_object_terms($draft_id, 'inspiration', 'quote_category');
		wp_set_object_terms($draft_id, 'Albert Einstein', 'quote_author');
		update_post_meta($draft_id, '_quote_source', 'Draft Source');
	}

	public function tearDown(): void {
		// Clean up
		foreach ($this->quotes as $quote_id) {
			wp_delete_post($quote_id, true);
		}
		parent::tearDown();
	}

	/**
	 * Test shortcode is registered
	 */
	public function test_shortcode_registered() {
		$this->assertTrue( shortcode_exists( 'stray-random' ) );
	}

	/**
	 * Test function exists
	 */
	public function test_function_exists() {
		$this->assertTrue( function_exists( 'stray_random_shortcode' ) );
	}

	/**
	 * Test default output returns a single quote
	 */
	public function test_default_output_returns_single_quote() {
		$output = do_shortcode( '[stray-random]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test category filtering - single category
	 */
	public function test_category_filtering_single() {
		$output = do_shortcode( '[stray-random categories="inspiration"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test category filtering - multiple categories
	 */
	public function test_category_filtering_multiple() {
		$output = do_shortcode( '[stray-random categories="inspiration,humor"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test category filtering - all categories
	 */
	public function test_category_filtering_all() {
		$output = do_shortcode( '[stray-random categories="all"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test category filtering - empty categories
	 */
	public function test_category_filtering_empty() {
		$output = do_shortcode( '[stray-random categories=""]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test category filtering - nonexistent category
	 */
	public function test_category_filtering_nonexistent() {
		$output = do_shortcode( '[stray-random categories="nonexistent"]' );
		
		$this->assertEmpty( $output );
	}

	/**
	 * Test multi parameter returns multiple quotes
	 */
	public function test_multi_parameter() {
		$output = do_shortcode( '[stray-random multi="3"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( '<ul>', $output );
		$this->assertStringContainsString( '<li>', $output );
		// Should have at least 3 list items
		$this->assertGreaterThanOrEqual( 3, substr_count( $output, '<li>' ) );
	}

	/**
	 * Test sequence parameter (sequential vs random)
	 */
	public function test_sequence_parameter() {
		// Sequential - should get consistent results
		$output1 = do_shortcode( '[stray-random sequence="true"]' );
		$output2 = do_shortcode( '[stray-random sequence="true"]' );
		
		// With sequence=true, we get predictable ordering
		$this->assertNotEmpty( $output1 );
		$this->assertNotEmpty( $output2 );
	}

	/**
	 * Test disableaspect parameter
	 */
	public function test_disableaspect_parameter() {
		$output = do_shortcode( '[stray-random disableaspect="true"]' );
		
		$this->assertNotEmpty( $output );
		// When aspect is disabled, no formatting tags should be present
		// This test assumes default formatting adds specific tags
	}

	/**
	 * Test draft quotes are excluded
	 */
	public function test_draft_quotes_excluded() {
		// Run shortcode many times to ensure draft never appears
		for ($i = 0; $i < 20; $i++) {
			$output = do_shortcode( '[stray-random]' );
			$this->assertStringNotContainsString( 'draft quote', $output );
		}
	}

	/**
	 * Test empty database returns empty output
	 */
	public function test_empty_database() {
		// Delete all quotes
		foreach ($this->quotes as $quote_id) {
			wp_delete_post($quote_id, true);
		}
		$this->quotes = array();
		
		$output = do_shortcode( '[stray-random]' );
		
		$this->assertEmpty( $output );
	}

	/**
	 * Test noajax parameter is respected
	 */
	public function test_noajax_parameter() {
		$output = do_shortcode( '[stray-random noajax="true"]' );
		
		$this->assertNotEmpty( $output );
		// With noajax=true, no AJAX loader elements should be present
		$this->assertStringNotContainsString( 'xv_random_quotes.newQuote', $output );
	}

	/**
	 * Test timer parameter
	 */
	public function test_timer_parameter() {
		$output = do_shortcode( '[stray-random timer="5"]' );
		
		$this->assertNotEmpty( $output );
		// Timer should add JavaScript timeout code
		// This would normally be tested via AJAX, but we can check structure
	}

	/**
	 * Test combined attributes
	 */
	public function test_combined_attributes() {
		$output = do_shortcode( '[stray-random categories="wisdom" multi="2" disableaspect="true"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( '<ul>', $output );
		$this->assertGreaterThanOrEqual( 2, substr_count( $output, '<li>' ) );
	}

	/**
	 * Test offset parameter with multi
	 */
	public function test_offset_parameter() {
		$output = do_shortcode( '[stray-random multi="5" offset="2"]' );
		
		$this->assertNotEmpty( $output );
		// Should return up to 5 quotes starting from offset 2
		$this->assertStringContainsString( '<ul>', $output );
	}
}
