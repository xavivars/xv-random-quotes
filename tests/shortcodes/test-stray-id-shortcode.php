<?php
/**
 * Tests for [stray-id] shortcode
 *
 * @package XVRandomQuotes
 */

class Test_Stray_Id_Shortcode extends WP_UnitTestCase {

	protected $quote_id;
	protected $legacy_quote_id;

	public function setUp(): void {
		parent::setUp();
		
		// Create a test quote with known ID
		$this->quote_id = wp_insert_post(array(
			'post_type' => 'xv_quote',
			'post_title' => 'Specific Test Quote',
			'post_content' => 'This is a specific quote for testing',
			'post_status' => 'publish',
		));
		
		wp_set_object_terms($this->quote_id, 'Test Author', 'quote_author');
		update_post_meta($this->quote_id, '_quote_source', 'Test Source');
		
		// Create a quote with legacy ID
		$this->legacy_quote_id = wp_insert_post(array(
			'post_type' => 'xv_quote',
			'post_title' => 'Legacy Quote',
			'post_content' => 'This is a legacy quote from old database',
			'post_status' => 'publish',
		));
		
		wp_set_object_terms($this->legacy_quote_id, 'Legacy Author', 'quote_author');
		update_post_meta($this->legacy_quote_id, '_quote_source', 'Legacy Source');
		update_post_meta($this->legacy_quote_id, '_quote_legacy_id', 42);
	}

	public function tearDown(): void {
		wp_delete_post($this->quote_id, true);
		wp_delete_post($this->legacy_quote_id, true);
		parent::tearDown();
	}

	/**
	 * Test shortcode is registered
	 */
	public function test_shortcode_registered() {
		$this->assertTrue( shortcode_exists( 'stray-id' ) );
	}

	/**
	 * Test function exists
	 */
	public function test_function_exists() {
		$this->assertTrue( function_exists( 'stray_id_shortcode' ) );
	}

	/**
	 * Test retrieval by post ID
	 */
	public function test_retrieval_by_post_id() {
		$output = do_shortcode( '[stray-id id="' . $this->quote_id . '"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'specific quote for testing', $output );
		$this->assertStringContainsString( 'Test Author', $output );
	}

	/**
	 * Test retrieval by legacy ID
	 */
	public function test_retrieval_by_legacy_id() {
		$output = do_shortcode( '[stray-id id="42"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'legacy quote from old database', $output );
		$this->assertStringContainsString( 'Legacy Author', $output );
	}

	/**
	 * Test default ID parameter (should default to 1 or first available)
	 */
	public function test_default_id_parameter() {
		$output = do_shortcode( '[stray-id]' );
		
		// Should return something (either ID 1 if it exists, or first available quote)
		$this->assertNotEmpty( $output );
	}

	/**
	 * Test nonexistent ID returns empty
	 */
	public function test_nonexistent_id() {
		$output = do_shortcode( '[stray-id id="999999"]' );
		
		$this->assertEmpty( $output );
	}

	/**
	 * Test output format includes author and source
	 */
	public function test_output_format() {
		$output = do_shortcode( '[stray-id id="' . $this->quote_id . '"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'Test Author', $output );
		$this->assertStringContainsString( 'Test Source', $output );
		$this->assertStringContainsString( 'specific quote for testing', $output );
	}

	/**
	 * Test disableaspect parameter
	 */
	public function test_disableaspect_parameter() {
		$output = do_shortcode( '[stray-id id="' . $this->quote_id . '" disableaspect="true"]' );
		
		$this->assertNotEmpty( $output );
		// When aspect is disabled, minimal formatting should be applied
	}

	/**
	 * Test noajax parameter
	 */
	public function test_noajax_parameter() {
		$output = do_shortcode( '[stray-id id="' . $this->quote_id . '" noajax="true"]' );
		
		$this->assertNotEmpty( $output );
		// Should not contain AJAX-related code
		$this->assertStringNotContainsString( 'xv_random_quotes.newQuote', $output );
	}

	/**
	 * Test linkphrase parameter (legacy AJAX loader)
	 */
	public function test_linkphrase_parameter() {
		$output = do_shortcode( '[stray-id id="' . $this->quote_id . '" linkphrase="New Quote"]' );
		
		$this->assertNotEmpty( $output );
		// When linkphrase is set without noajax, it should appear somewhere
	}

	/**
	 * Test draft quote is not displayed
	 */
	public function test_draft_quote_not_displayed() {
		$draft_id = wp_insert_post(array(
			'post_type' => 'xv_quote',
			'post_title' => 'Draft Quote',
			'post_content' => 'This is a draft quote',
			'post_status' => 'draft',
		));
		
		$output = do_shortcode( '[stray-id id="' . $draft_id . '"]' );
		
		$this->assertEmpty( $output );
		
		wp_delete_post($draft_id, true);
	}

	/**
	 * Test numeric string ID is handled correctly
	 */
	public function test_numeric_string_id() {
		$output = do_shortcode( '[stray-id id="' . strval($this->quote_id) . '"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'specific quote for testing', $output );
	}

	/**
	 * Test legacy ID takes precedence when both exist
	 */
	public function test_legacy_id_precedence() {
		// If a quote has legacy_id=42, using id="42" should find it
		// even if there's also a post with ID=42
		$output = do_shortcode( '[stray-id id="42"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'legacy quote from old database', $output );
	}

	/**
	 * Test quote without author or source
	 */
	public function test_quote_without_author_or_source() {
		$minimal_id = wp_insert_post(array(
			'post_type' => 'xv_quote',
			'post_title' => 'Minimal Quote',
			'post_content' => 'Just the quote text, nothing else',
			'post_status' => 'publish',
		));
		
		$output = do_shortcode( '[stray-id id="' . $minimal_id . '"]' );
		
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'Just the quote text', $output );
		
		wp_delete_post($minimal_id, true);
	}
}
