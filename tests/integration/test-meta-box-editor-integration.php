<?php
/**
 * Integration tests for meta box editor functionality
 *
 * Tests the complete flow from quote creation to display,
 * verifying that meta boxes work correctly for both editors.
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';

/**
 * Test meta box editor integration
 */
class Test_Meta_Box_Editor_Integration extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Initialize v2.0 architecture
		do_action( 'init' );
	}

	/**
	 * Test quote creation saves correctly to post_content
	 */
	public function test_quote_creation_saves_to_post_content() {
		$quote_text = 'This is a <strong>test quote</strong> with <em>formatting</em>.';

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Auto-generated title',
				'post_content' => $quote_text,
				'post_status'  => 'publish',
			)
		);

		$this->assertNotWPError( $post_id );
		$this->assertGreaterThan( 0, $post_id );

		// Verify post_content is saved correctly
		$post = get_post( $post_id );
		$this->assertEquals( $quote_text, $post->post_content );

		wp_delete_post( $post_id, true );
	}

	/**
	 * Test migrated quote loads correctly in editor
	 */
	public function test_migrated_quote_loads_correctly() {
		// Create a quote that simulates migrated data
		$legacy_quote = 'Science is <strong>organized knowledge</strong>. Wisdom is organized life.';

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Science is organized knowledge',
				'post_content' => $legacy_quote,
				'post_status'  => 'publish',
			)
		);

		// Add legacy ID meta
		update_post_meta( $post_id, '_quote_legacy_id', 42 );

		// Verify quote can be retrieved
		$post = get_post( $post_id );
		$this->assertEquals( $legacy_quote, $post->post_content );

		// Verify legacy ID is preserved
		$legacy_id = get_post_meta( $post_id, '_quote_legacy_id', true );
		$this->assertEquals( 42, $legacy_id );

		wp_delete_post( $post_id, true );
	}

	/**
	 * Test HTML sanitization strips dangerous tags
	 */
	public function test_html_sanitization_strips_dangerous_tags() {
		$unsafe_quote = 'Test <script>alert("xss")</script> quote with <img src="x" onerror="alert(1)"> and <iframe src="evil.com"></iframe>.';

		// Simulate what wp_kses would do (this is what the meta box save does)
		$allowed_tags = array(
			'strong' => array(),
			'em'     => array(),
			'b'      => array(),
			'i'      => array(),
			'code'   => array(),
			'abbr'   => array( 'title' => true ),
			'cite'   => array(),
			'q'      => array(),
			'mark'   => array(),
			'sub'    => array(),
			'sup'    => array(),
			'a'      => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
				'rel'    => true,
			),
		);

		$sanitized = wp_kses( $unsafe_quote, $allowed_tags );

		// Verify dangerous tags are stripped
		$this->assertStringNotContainsString( '<script>', $sanitized );
		$this->assertStringNotContainsString( '<img', $sanitized );
		$this->assertStringNotContainsString( '<iframe>', $sanitized );
		$this->assertStringNotContainsString( 'onerror=', $sanitized );
		
		// Verify text content remains (wp_kses strips tags but keeps text)
		$this->assertStringContainsString( 'Test', $sanitized );
		$this->assertStringContainsString( 'quote', $sanitized );
	}

	/**
	 * Test HTML sanitization preserves allowed tags
	 */
	public function test_html_sanitization_preserves_allowed_tags() {
		$safe_quote = 'This has <strong>bold</strong>, <em>italic</em>, <code>code</code>, and <a href="http://example.com">a link</a>.';

		$allowed_tags = array(
			'strong' => array(),
			'em'     => array(),
			'b'      => array(),
			'i'      => array(),
			'code'   => array(),
			'abbr'   => array( 'title' => true ),
			'cite'   => array(),
			'q'      => array(),
			'mark'   => array(),
			'sub'    => array(),
			'sup'    => array(),
			'a'      => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
				'rel'    => true,
			),
		);

		$sanitized = wp_kses( $safe_quote, $allowed_tags );

		// Verify allowed tags are preserved
		$this->assertStringContainsString( '<strong>bold</strong>', $sanitized );
		$this->assertStringContainsString( '<em>italic</em>', $sanitized );
		$this->assertStringContainsString( '<code>code</code>', $sanitized );
		$this->assertStringContainsString( '<a href="http://example.com">a link</a>', $sanitized );
	}

	/**
	 * Test saved quote displays correctly in shortcode
	 */
	public function test_saved_quote_displays_in_shortcode() {
		// Create a quote with formatting
		$quote_text = 'Knowledge is <strong>power</strong>, wisdom is <em>knowing how to use it</em>.';

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Knowledge and Wisdom',
				'post_content' => $quote_text,
				'post_status'  => 'publish',
			)
		);

		// Add author and source
		$author_term = wp_insert_term( 'Test Author', 'quote_author' );
		wp_set_post_terms( $post_id, array( $author_term['term_id'] ), 'quote_author' );
		update_post_meta( $post_id, '_quote_source', 'Test Source' );

		// Get shortcode output
		$output = do_shortcode( '[stray-id id="' . $post_id . '"]' );

		// Verify quote content appears in output
		$this->assertStringContainsString( 'Knowledge is', $output );
		$this->assertStringContainsString( '<strong>power</strong>', $output );
		$this->assertStringContainsString( '<em>knowing how to use it</em>', $output );

		// Verify author and source appear
		$this->assertStringContainsString( 'Test Author', $output );
		$this->assertStringContainsString( 'Test Source', $output );

		wp_delete_post( $post_id, true );
		wp_delete_term( $author_term['term_id'], 'quote_author' );
	}

	/**
	 * Test quote with source HTML formatting displays correctly
	 */
	public function test_quote_with_formatted_source_displays_correctly() {
		$quote_text = 'Test quote text';
		$source_html = 'From <a href="http://example.com">Example Book</a>';

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Test Quote',
				'post_content' => $quote_text,
				'post_status'  => 'publish',
			)
		);

		// Add formatted source
		update_post_meta( $post_id, '_quote_source', $source_html );

		// Get shortcode output
		$output = do_shortcode( '[stray-id id="' . $post_id . '"]' );

		// Verify source HTML is preserved in output
		$this->assertStringContainsString( 'From', $output );
		$this->assertStringContainsString( '<a href="http://example.com">Example Book</a>', $output );

		wp_delete_post( $post_id, true );
	}

	/**
	 * Test complete quote lifecycle from creation to display
	 */
	public function test_complete_quote_lifecycle() {
		// 1. Create quote (simulating meta box save)
		$quote_content = 'The only true wisdom is in <strong>knowing</strong> you know <em>nothing</em>.';
		$source_content = 'From <cite>Socrates</cite>';

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Socratic Wisdom',
				'post_content' => $quote_content,
				'post_status'  => 'publish',
			)
		);

		// 2. Save source meta
		update_post_meta( $post_id, '_quote_source', $source_content );

		// 3. Assign author with URL
		$author_term = wp_insert_term( 'Socrates', 'quote_author' );
		update_term_meta( $author_term['term_id'], 'author_url', 'https://en.wikipedia.org/wiki/Socrates' );
		wp_set_post_terms( $post_id, array( $author_term['term_id'] ), 'quote_author' );

		// 4. Verify data is saved correctly
		$post = get_post( $post_id );
		$this->assertEquals( $quote_content, $post->post_content );
		$this->assertEquals( $source_content, get_post_meta( $post_id, '_quote_source', true ) );

		$terms = wp_get_post_terms( $post_id, 'quote_author' );
		$this->assertEquals( 'Socrates', $terms[0]->name );
		$this->assertEquals( 'https://en.wikipedia.org/wiki/Socrates', get_term_meta( $terms[0]->term_id, 'author_url', true ) );

		// 5. Verify display in shortcode
		$output = do_shortcode( '[stray-id id="' . $post_id . '"]' );

		$this->assertStringContainsString( 'knowing', $output );
		$this->assertStringContainsString( '<strong>knowing</strong>', $output );
		$this->assertStringContainsString( '<em>nothing</em>', $output );
		$this->assertStringContainsString( '<cite>Socrates</cite>', $output );
		$this->assertStringContainsString( '<a href="https://en.wikipedia.org/wiki/Socrates">Socrates</a>', $output );

		// 6. Verify display in stray-random
		$random_output = do_shortcode( '[stray-random]' );
		$this->assertNotEmpty( $random_output );
		$this->assertStringContainsString( 'Socrates', $random_output );

		// 7. Verify display in stray-all
		$all_output = do_shortcode( '[stray-all rows="10"]' );
		$this->assertNotEmpty( $all_output );
		$this->assertStringContainsString( 'Socrates', $all_output );

		wp_delete_post( $post_id, true );
		wp_delete_term( $author_term['term_id'], 'quote_author' );
	}

	/**
	 * Test block-level tags are stripped from quote content
	 */
	public function test_block_level_tags_stripped() {
		$quote_with_blocks = '<p>Paragraph 1</p><div>Div content</div><h1>Heading</h1>This should remain.';

		$allowed_tags = array(
			'strong' => array(),
			'em'     => array(),
			'b'      => array(),
			'i'      => array(),
			'code'   => array(),
			'abbr'   => array( 'title' => true ),
			'cite'   => array(),
			'q'      => array(),
			'mark'   => array(),
			'sub'    => array(),
			'sup'    => array(),
			'a'      => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
				'rel'    => true,
			),
		);

		$sanitized = wp_kses( $quote_with_blocks, $allowed_tags );

		// Block-level tags should be stripped
		$this->assertStringNotContainsString( '<p>', $sanitized );
		$this->assertStringNotContainsString( '<div>', $sanitized );
		$this->assertStringNotContainsString( '<h1>', $sanitized );

		// Text content should remain
		$this->assertStringContainsString( 'Paragraph 1', $sanitized );
		$this->assertStringContainsString( 'Div content', $sanitized );
		$this->assertStringContainsString( 'This should remain', $sanitized );
	}

	/**
	 * Test meta box save simulation with proper sanitization
	 */
	public function test_meta_box_save_simulation() {
		// Simulate POST data from meta box
		$_POST['xv_quote_content'] = 'Test <strong>quote</strong> with <script>alert("xss")</script>';
		$_POST['xv_quote_source'] = 'Source with <a href="http://example.com">link</a> and <img src="x">';

		$allowed_tags = array(
			'strong' => array(),
			'em'     => array(),
			'b'      => array(),
			'i'      => array(),
			'code'   => array(),
			'abbr'   => array( 'title' => true ),
			'cite'   => array(),
			'q'      => array(),
			'mark'   => array(),
			'sub'    => array(),
			'sup'    => array(),
			'a'      => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
				'rel'    => true,
			),
		);

		// Sanitize like the meta box does
		$sanitized_content = wp_kses( $_POST['xv_quote_content'], $allowed_tags );
		$sanitized_source = wp_kses( $_POST['xv_quote_source'], $allowed_tags );

		// Verify sanitization worked
		$this->assertStringContainsString( '<strong>quote</strong>', $sanitized_content );
		$this->assertStringNotContainsString( '<script>', $sanitized_content );
		$this->assertStringNotContainsString( '</script>', $sanitized_content );
		// Text content 'alert' remains, just the dangerous tags are removed

		$this->assertStringContainsString( '<a href="http://example.com">link</a>', $sanitized_source );
		$this->assertStringNotContainsString( '<img', $sanitized_source );

		// Clean up
		unset( $_POST['xv_quote_content'], $_POST['xv_quote_source'] );
	}

	/**
	 * Test quote retrieval maintains HTML formatting
	 */
	public function test_quote_retrieval_maintains_formatting() {
		$formatted_quote = 'E = mc<sup>2</sup> was discovered by <strong>Einstein</strong>.';

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Einstein Formula',
				'post_content' => $formatted_quote,
				'post_status'  => 'publish',
			)
		);

		// Retrieve and verify
		$post = get_post( $post_id );
		$this->assertEquals( $formatted_quote, $post->post_content );

		// Verify it renders correctly in shortcode
		$output = do_shortcode( '[stray-id id="' . $post_id . '"]' );
		$this->assertStringContainsString( 'mc<sup>2</sup>', $output );
		$this->assertStringContainsString( '<strong>Einstein</strong>', $output );

		wp_delete_post( $post_id, true );
	}
}
