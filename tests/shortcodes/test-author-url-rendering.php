<?php
/**
 * Tests for author URL rendering in shortcodes
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';

use XVRandomQuotes\Queries\QuoteQueries;

/**
 * Test author URL rendering in quote output
 */
class Test_Author_URL_Rendering extends WP_UnitTestCase {

	/**
	 * Quote ID for testing
	 *
	 * @var int
	 */
	private $quote_id;

	/**
	 * Author term ID
	 *
	 * @var int
	 */
	private $author_term_id;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Create author term with URL
		$term = wp_insert_term(
			'Albert Einstein',
			'quote_author'
		);

		$this->author_term_id = $term['term_id'];

		// Add author URL to term meta
		update_term_meta(
			$this->author_term_id,
			'author_url',
			'https://en.wikipedia.org/wiki/Albert_Einstein'
		);

		// Create a test quote
		$this->quote_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Test Quote',
				'post_content' => 'Imagination is more important than knowledge.',
				'post_status'  => 'publish',
			)
		);

		// Assign author term
		wp_set_post_terms( $this->quote_id, array( $this->author_term_id ), 'quote_author' );

		// Add source
		update_post_meta( $this->quote_id, '_quote_source', 'On Science' );

		// Initialize v2.0 architecture
		do_action( 'init' );
	}

	/**
	 * Clean up after tests
	 */
	public function tearDown(): void {
		wp_delete_post( $this->quote_id, true );
		wp_delete_term( $this->author_term_id, 'quote_author' );
		parent::tearDown();
	}

	/**
	 * Test that author URL from term meta is rendered in shortcode output
	 */
	public function test_author_url_rendered_in_stray_id_shortcode() {
		$output = do_shortcode( '[stray-id id="' . $this->quote_id . '"]' );

		// Should contain the author name
		$this->assertStringContainsString( 'Albert Einstein', $output );

		// Should contain a link to the author URL
		$this->assertStringContainsString( 'href="https://en.wikipedia.org/wiki/Albert_Einstein"', $output );
		$this->assertStringContainsString( '<a href="https://en.wikipedia.org/wiki/Albert_Einstein">Albert Einstein</a>', $output );
	}

	/**
	 * Test that author URL is rendered in stray-random shortcode
	 */
	public function test_author_url_rendered_in_stray_random_shortcode() {
		$output = do_shortcode( '[stray-random]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'Albert Einstein', $output );
		$this->assertStringContainsString( 'href="https://en.wikipedia.org/wiki/Albert_Einstein"', $output );
	}

	/**
	 * Test that author URL is rendered in stray-all shortcode
	 */
	public function test_author_url_rendered_in_stray_all_shortcode() {
		$output = do_shortcode( '[stray-all rows="10"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'Albert Einstein', $output );
		$this->assertStringContainsString( 'href="https://en.wikipedia.org/wiki/Albert_Einstein"', $output );
	}

	/**
	 * Test author without URL renders as plain text
	 */
	public function test_author_without_url_renders_plain() {
		// Create author without URL
		$term2 = wp_insert_term( 'Unknown Author', 'quote_author' );
		$author_term_id2 = $term2['term_id'];

		$quote_id2 = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Test Quote 2',
				'post_content' => 'Another quote',
				'post_status'  => 'publish',
			)
		);

		wp_set_post_terms( $quote_id2, array( $author_term_id2 ), 'quote_author' );

		$output = do_shortcode( '[stray-id id="' . $quote_id2 . '"]' );

		// Should contain author name
		$this->assertStringContainsString( 'Unknown Author', $output );

		// Should NOT contain a link for this author
		$this->assertStringNotContainsString( '<a href=', $output );

		wp_delete_post( $quote_id2, true );
		wp_delete_term( $author_term_id2, 'quote_author' );
	}

	/**
	 * Test that author URL takes priority over settings-based link pattern
	 */
	public function test_author_url_priority_over_settings() {
		// Set a settings-based link pattern
		$options = get_option( 'stray_quotes_options', array() );
		$options['stray_quotes_linkto'] = 'http://www.google.com/search?q="%AUTHOR%"';
		update_option( 'stray_quotes_options', $options );

		$output = do_shortcode( '[stray-id id="' . $this->quote_id . '"]' );

		// Should use term meta URL, NOT the settings pattern
		$this->assertStringContainsString( 'href="https://en.wikipedia.org/wiki/Albert_Einstein"', $output );
		$this->assertStringNotContainsString( 'google.com/search', $output );

		// Clean up
		unset( $options['stray_quotes_linkto'] );
		update_option( 'stray_quotes_options', $options );
	}

	/**
	 * Test XSS protection in author URL
	 */
	public function test_author_url_xss_protection() {
		// Create author with malicious URL
		$term3 = wp_insert_term( 'Malicious Author', 'quote_author' );
		$author_term_id3 = $term3['term_id'];

		// Try to add XSS in term meta
		update_term_meta( $author_term_id3, 'author_url', 'javascript:alert("xss")' );

		$quote_id3 = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Test Quote 3',
				'post_content' => 'XSS test quote',
				'post_status'  => 'publish',
			)
		);

		wp_set_post_terms( $quote_id3, array( $author_term_id3 ), 'quote_author' );

		$output = do_shortcode( '[stray-id id="' . $quote_id3 . '"]' );

		// esc_url should have sanitized the javascript: URL
		// It should either be empty or the link should not contain javascript:
		$this->assertStringNotContainsString( 'javascript:', $output );

		wp_delete_post( $quote_id3, true );
		wp_delete_term( $author_term_id3, 'quote_author' );
	}

	/**
	 * Test author name is properly escaped
	 */
	public function test_author_name_escaped() {
		// Create author with special characters
		$term4 = wp_insert_term( 'Author <script>alert("xss")</script>', 'quote_author' );
		$author_term_id4 = $term4['term_id'];

		update_term_meta( $author_term_id4, 'author_url', 'https://example.com' );

		$quote_id4 = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Test Quote 4',
				'post_content' => 'Escape test quote',
				'post_status'  => 'publish',
			)
		);

		wp_set_post_terms( $quote_id4, array( $author_term_id4 ), 'quote_author' );

		$output = do_shortcode( '[stray-id id="' . $quote_id4 . '"]' );

		// Author name should be escaped (no raw <script> tag)
		$this->assertStringNotContainsString( '<script>alert', $output );
		// esc_html should convert < and > to entities
		$this->assertStringContainsString( 'Author', $output );

		wp_delete_post( $quote_id4, true );
		wp_delete_term( $author_term_id4, 'quote_author' );
	}
}
