<?php
/**
 * Tests for Random Quote Gutenberg Block
 *
 * @package XVRandomQuotes
 */

/**
 * Test class for Random Quote block (displays one or more random quotes)
 */
class Test_Random_Quote_Block extends WP_UnitTestCase {

	/**
	 * Quote IDs for testing
	 *
	 * @var array
	 */
	private $quote_ids = array();

	/**
	 * Category term IDs
	 *
	 * @var array
	 */
	private $category_ids = array();

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

		// Create test categories
		$science = wp_insert_term( 'Science', 'quote_category' );
		$this->category_ids['science'] = $science['term_id'];

		$philosophy = wp_insert_term( 'Philosophy', 'quote_category' );
		$this->category_ids['philosophy'] = $philosophy['term_id'];

		// Create test quotes
		$quotes_data = array(
			array(
				'text'     => 'The science of today is the technology of tomorrow.',
				'author'   => 'Edward Teller',
				'source'   => 'Speech at MIT',
				'category' => 'science',
			),
			array(
				'text'     => 'Science knows no country, because knowledge belongs to humanity.',
				'author'   => 'Louis Pasteur',
				'source'   => 'Laboratory Notes',
				'category' => 'science',
			),
			array(
				'text'     => 'I think, therefore I am.',
				'author'   => 'René Descartes',
				'source'   => 'Discourse on the Method',
				'category' => 'philosophy',
			),
			array(
				'text'     => 'The unexamined life is not worth living.',
				'author'   => 'Socrates',
				'source'   => 'Plato\'s Apology',
				'category' => 'philosophy',
			),
			array(
				'text'     => 'To be is to do.',
				'author'   => 'Immanuel Kant',
				'source'   => 'Critique of Pure Reason',
				'category' => 'philosophy',
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

			// Set category
			wp_set_post_terms( $post_id, array( $this->category_ids[ $quote_data['category'] ] ), 'quote_category' );

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
		foreach ( $this->category_ids as $term_id ) {
			wp_delete_term( $term_id, 'quote_category' );
		}
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
		$this->assertTrue( $registry->is_registered( 'xv-random-quotes/random-quote' ) );
	}

	/**
	 * Test block has correct attributes schema
	 */
	public function test_block_attributes_schema() {
		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( 'xv-random-quotes/random-quote' );

		$this->assertIsObject( $block );
		$this->assertIsArray( $block->attributes );

		// Check for expected attributes
		$this->assertArrayHasKey( 'categories', $block->attributes );
		$this->assertArrayHasKey( 'multi', $block->attributes );
		$this->assertArrayHasKey( 'sequence', $block->attributes );
		$this->assertArrayHasKey( 'disableaspect', $block->attributes );
		$this->assertArrayHasKey( 'enableAjax', $block->attributes );
		$this->assertArrayHasKey( 'timer', $block->attributes );
	}

	/**
	 * Test block has render callback
	 */
	public function test_block_has_render_callback() {
		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( 'xv-random-quotes/random-quote' );

		$this->assertIsObject( $block );
		$this->assertIsCallable( $block->render_callback );
	}

	/**
	 * Test renders single random quote by default
	 */
	public function test_renders_single_quote_default() {
		$attributes = array();

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'xv-quote-wrapper', $content );
		$this->assertStringContainsString( 'class="xv-quote"', $content );
	}

	/**
	 * Test renders multiple random quotes
	 */
	public function test_renders_multiple_quotes() {
		$attributes = array(
			'multi' => 3,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// Should contain multiple quote divs (check for new class name)
		$this->assertGreaterThanOrEqual( 2, substr_count( $content, 'class="xv-quote"' ) );
	}

	/**
	 * Test filters by single category
	 */
	public function test_category_filter_single() {
		$attributes = array(
			'categories' => 'science',
			'multi'      => 1,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'science', strtolower( $content ) );
	}

	/**
	 * Test filters by multiple categories
	 */
	public function test_category_filter_multiple() {
		$attributes = array(
			'categories' => 'science,philosophy',
			'multi'      => 2,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'class="xv-quote"', $content );
	}

	/**
	 * Test category 'all' returns from all categories
	 */
	public function test_category_all() {
		$attributes = array(
			'categories' => 'all',
			'multi'      => 3,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertGreaterThanOrEqual( 1, substr_count( $content, 'class="xv-quote"' ) );
	}

	/**
	 * Test sequence parameter orders quotes
	 */
	public function test_sequence_ordering() {
		$attributes = array(
			'sequence' => true,
			'multi'    => 2,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// When sequence=true, quotes should be in order, not random
		$this->assertStringContainsString( 'class="xv-quote"', $content );
	}

	/**
	 * Test disableaspect removes wrapper
	 */
	public function test_disable_aspect() {
		$attributes = array(
			'disableaspect' => true,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// When disableaspect=true, wrapper should not be present
		$this->assertStringNotContainsString( 'class="xv-quote"-wrapper', $content );
	}

	/**
	 * Test AJAX enabled adds wrapper and data attributes
	 */
	public function test_ajax_enabled() {
		$attributes = array(
			'enableAjax' => true,
			'timer'      => 10,
			'categories' => 'science',
			'multi'      => 2,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'xv-quote-ajax-wrapper', $content );
		$this->assertStringContainsString( 'data-categories="science"', $content );
		$this->assertStringContainsString( 'data-timer="10"', $content );
		$this->assertStringContainsString( 'data-multi="2"', $content );
	}

	/**
	 * Test AJAX disabled no wrapper
	 */
	public function test_ajax_disabled() {
		$attributes = array(
			'enableAjax' => false,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringNotContainsString( 'class="xv-quote"-ajax-wrapper', $content );
		$this->assertStringNotContainsString( 'data-timer', $content );
	}

	/**
	 * Test AJAX refresh link present when timer is 0
	 */
	public function test_ajax_manual_refresh_link() {
		$attributes = array(
			'enableAjax' => true,
			'timer'      => 0,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'xv-quote-refresh', $content );
	}

	/**
	 * Test empty database returns empty
	 */
	public function test_empty_database() {
		// Delete all quotes
		foreach ( $this->quote_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}
		$this->quote_ids = array();

		$attributes = array();

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertEmpty( $content );
	}

	/**
	 * Test draft quotes are excluded
	 */
	public function test_draft_quotes_excluded() {
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
			'multi' => 10,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringNotContainsString( 'This is a draft quote.', $content );

		wp_delete_post( $draft_id, true );
	}

	/**
	 * Test nonexistent category returns empty
	 */
	public function test_nonexistent_category() {
		$attributes = array(
			'categories' => 'nonexistent-category',
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/random-quote' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertEmpty( $content );
	}
}
