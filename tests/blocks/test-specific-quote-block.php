<?php
/**
 * Tests for Specific Quote Gutenberg Block
 *
 * @package XVRandomQuotes
 */

/**
 * Test class for Specific Quote block (displays a quote by ID)
 */
class Test_Specific_Quote_Block extends WP_UnitTestCase {

	/**
	 * Quote IDs for testing
	 *
	 * @var array
	 */
	private $quote_ids = array();

	/**
	 * Author term IDs
	 *
	 * @var array
	 */
	private $author_ids = array();

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Create test quotes
		$quotes_data = array(
			array(
				'text'   => 'The science of today is the technology of tomorrow.',
				'author' => 'Edward Teller',
				'source' => 'Speech at MIT',
			),
			array(
				'text'   => 'I think, therefore I am.',
				'author' => 'René Descartes',
				'source' => 'Discourse on the Method',
			),
			array(
				'text'   => 'To be is to do.',
				'author' => 'Immanuel Kant',
				'source' => 'Critique of Pure Reason',
			),
		);

		foreach ( $quotes_data as $quote_data ) {
			$post_id = wp_insert_post(
				array(
					'post_type'    => 'xv_quote',
					'post_title'   => $quote_data['text'],
					'post_content' => $quote_data['text'],
					'post_status'  => 'publish',
				)
			);

			$this->quote_ids[] = $post_id;

			// Set author
			$author_term = wp_insert_term( $quote_data['author'], 'quote_author' );
			if ( ! is_wp_error( $author_term ) ) {
				wp_set_post_terms( $post_id, array( $author_term['term_id'] ), 'quote_author' );
				$this->author_ids[ $quote_data['author'] ] = $author_term['term_id'];
			}

			// Set source
			update_post_meta( $post_id, '_quote_source', $quote_data['source'] );
		}

		// Configure settings with CSS classes expected by tests
		update_option(
			'stray_quotes_options',
			array(
				'stray_quotes_before_all'    => '<div class="xv-quote-wrapper">',
				'stray_quotes_after_all'     => '</div>',
				'stray_quotes_before_quote'  => '<div class="xv-quote">',
				'stray_quotes_after_quote'   => '</div>',
				'stray_quotes_before_author' => '<div class="xv-quote-author">',
				'stray_quotes_after_author'  => '</div>',
				'stray_quotes_before_source' => '<div class="xv-quote-source">',
				'stray_quotes_after_source'  => '</div>',
			)
		);
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		// Clean up posts
		foreach ( $this->quote_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		// Clean up terms
		foreach ( $this->author_ids as $term_id ) {
			wp_delete_term( $term_id, 'quote_author' );
		}

		parent::tearDown();
	}

	/**
	 * Test block is registered
	 */
	public function test_block_is_registered() {
		$registry = WP_Block_Type_Registry::get_instance();
		$this->assertTrue( $registry->is_registered( 'xv-random-quotes/specific-quote' ) );
	}

	/**
	 * Test block has correct attributes schema
	 */
	public function test_block_attributes_schema() {
		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( 'xv-random-quotes/specific-quote' );

		$this->assertIsObject( $block );
		$this->assertIsArray( $block->attributes );

		// Check for expected attributes
		$this->assertArrayHasKey( 'postId', $block->attributes );
		$this->assertArrayHasKey( 'disableaspect', $block->attributes );
	}

	/**
	 * Test block has render callback
	 */
	public function test_block_has_render_callback() {
		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( 'xv-random-quotes/specific-quote' );

		$this->assertIsObject( $block );
		$this->assertIsCallable( $block->render_callback );
	}

	/**
	 * Test renders specific quote by post ID
	 */
	public function test_renders_by_post_id() {
		$attributes = array(
			'postId' => $this->quote_ids[0],
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'The science of today is the technology of tomorrow.', $content );
		$this->assertStringContainsString( 'Edward Teller', $content );
	}

	/**
	 * Test renders specific quote by legacy ID
	 */
	public function test_renders_by_legacy_id() {
		// Add legacy ID to a quote
		update_post_meta( $this->quote_ids[1], '_quote_legacy_id', 42 );

		$attributes = array(
			'legacyId' => 42,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'I think, therefore I am.', $content );
		$this->assertStringContainsString( 'René Descartes', $content );
	}

	/**
	 * Test handles invalid/nonexistent ID
	 */
	public function test_invalid_id() {
		$attributes = array(
			'postId' => 999999,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		// Should return empty
		$this->assertEmpty( $content );
	}

	/**
	 * Test handles missing quoteId attribute
	 */
	public function test_missing_quote_id() {
		$attributes = array();

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		// Should return empty or first quote (depending on implementation)
		// We'll test that it at least doesn't error
		$this->assertIsString( $content );
	}

	/**
	 * Test renders with quote wrapper by default
	 */
	public function test_renders_with_wrapper_default() {
		$attributes = array(
			'postId' => $this->quote_ids[0],
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'xv-quote-wrapper', $content );
	}

	/**
	 * Test disableaspect removes wrapper
	 */
	public function test_disable_aspect() {
		$attributes = array(
			'postId'       => $this->quote_ids[0],
			'disableaspect' => true,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// When disableaspect=true, wrapper should not be present
		$this->assertStringNotContainsString( 'xv-quote-wrapper', $content );
	}

	/**
	 * Test renders quote content
	 */
	public function test_renders_quote_content() {
		$attributes = array(
			'postId' => $this->quote_ids[0],
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'xv-quote', $content );
		$this->assertStringContainsString( 'The science of today is the technology of tomorrow.', $content );
	}

	/**
	 * Test renders author information
	 */
	public function test_renders_author() {
		$attributes = array(
			'postId' => $this->quote_ids[0],
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'Edward Teller', $content );
	}

	/**
	 * Test renders source information
	 */
	public function test_renders_source() {
		$attributes = array(
			'postId' => $this->quote_ids[0],
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'Speech at MIT', $content );
	}

	/**
	 * Test does not render draft quotes
	 */
	public function test_draft_quote_not_rendered() {
		// Create a draft quote
		$draft_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Draft Quote',
				'post_content' => 'This is a draft quote.',
				'post_status'  => 'draft',
			)
		);

		$attributes = array(
			'postId' => $draft_id,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertEmpty( $content );

		wp_delete_post( $draft_id, true );
	}

	/**
	 * Test renders quote without author
	 */
	public function test_quote_without_author() {
		// Create quote without author
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Anonymous quote',
				'post_content' => 'A quote without attribution.',
				'post_status'  => 'publish',
			)
		);

		$attributes = array(
			'postId' => $post_id,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'A quote without attribution.', $content );

		wp_delete_post( $post_id, true );
	}

	/**
	 * Test numeric string ID is handled correctly
	 */
	public function test_numeric_string_id() {
		$attributes = array(
			'postId' => (string) $this->quote_ids[0],
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/specific-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'The science of today is the technology of tomorrow.', $content );
	}
}
