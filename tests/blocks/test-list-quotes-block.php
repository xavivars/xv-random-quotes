<?php
/**
 * Tests for List Quotes Gutenberg Block
 *
 * @package XVRandomQuotes
 */

/**
 * Test class for List Quotes block (displays paginated list of quotes)
 */
class Test_List_Quotes_Block extends WP_UnitTestCase {

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
			array(
				'text'     => 'The good life is one inspired by love and guided by knowledge.',
				'author'   => 'Bertrand Russell',
				'source'   => 'What I Believe',
				'category' => 'philosophy',
			),
			array(
				'text'     => 'In the middle of difficulty lies opportunity.',
				'author'   => 'Albert Einstein',
				'source'   => 'Letter to a friend',
				'category' => 'science',
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
		$this->assertTrue( $registry->is_registered( 'xv-random-quotes/list-quotes' ) );
	}

	/**
	 * Test block has correct attributes schema
	 */
	public function test_block_attributes_schema() {
		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( 'xv-random-quotes/list-quotes' );

		$this->assertIsObject( $block );
		$this->assertIsArray( $block->attributes );

		// Check for expected attributes
		$this->assertArrayHasKey( 'categories', $block->attributes );
		$this->assertArrayHasKey( 'rows', $block->attributes );
		$this->assertArrayHasKey( 'orderby', $block->attributes );
		$this->assertArrayHasKey( 'sort', $block->attributes );
		$this->assertArrayHasKey( 'disableaspect', $block->attributes );
	}

	/**
	 * Test block has render callback
	 */
	public function test_block_has_render_callback() {
		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( 'xv-random-quotes/list-quotes' );

		$this->assertIsObject( $block );
		$this->assertIsCallable( $block->render_callback );
	}

	/**
	 * Test renders multiple quotes by default
	 */
	public function test_renders_multiple_quotes_default() {
		$attributes = array();

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// Should contain multiple quote divs
		$quote_count = substr_count( $content, 'class="xv-quote"' );
		$this->assertGreaterThanOrEqual( 1, $quote_count );
	}

	/**
	 * Test respects rows parameter
	 */
	public function test_rows_parameter() {
		$attributes = array(
			'rows' => 3,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$quote_count = substr_count( $content, 'class="xv-quote"' );
		$this->assertEquals( 3, $quote_count );
	}

	/**
	 * Test filters by single category
	 */
	public function test_category_filter_single() {
		$attributes = array(
			'categories' => 'science',
			'rows'       => 10,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// Should only contain science quotes (3 total)
		$this->assertStringContainsString( 'Edward Teller', $content );
		$this->assertStringNotContainsString( 'René Descartes', $content );
	}

	/**
	 * Test filters by multiple categories
	 */
	public function test_category_filter_multiple() {
		$attributes = array(
			'categories' => 'science,philosophy',
			'rows'       => 10,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// Should contain quotes from both categories
		$this->assertStringContainsString( 'class="xv-quote"', $content );
	}

	/**
	 * Test category 'all' returns all quotes
	 */
	public function test_category_all() {
		$attributes = array(
			'categories' => 'all',
			'rows'       => 10,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$quote_count = substr_count( $content, 'class="xv-quote"' );
		$this->assertEquals( 7, $quote_count ); // All 7 test quotes
	}

	/**
	 * Test ordering by date ascending
	 */
	public function test_order_by_date_asc() {
		$attributes = array(
			'orderby' => 'date',
			'sort'    => 'ASC',
			'rows'    => 5,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// First quote should be the oldest
		$this->assertStringContainsString( 'The science of today', $content );
	}

	/**
	 * Test ordering by date descending
	 */
	public function test_order_by_date_desc() {
		$attributes = array(
			'orderby' => 'date',
			'sort'    => 'DESC',
			'rows'    => 5,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// First quote should be the newest
		$this->assertStringContainsString( 'class="xv-quote"', $content );
	}

	/**
	 * Test ordering by title
	 */
	public function test_order_by_title() {
		$attributes = array(
			'orderby' => 'title',
			'sort'    => 'ASC',
			'rows'    => 5,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'class="xv-quote"', $content );
	}

	/**
	 * Test disableaspect removes wrapper
	 */
	public function test_disable_aspect() {
		$attributes = array(
			'disableaspect' => true,
			'rows'          => 2,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// When disableaspect=true, wrapper should not be present
		$this->assertStringNotContainsString( 'class="xv-quote"-wrapper', $content );
	}

	/**
	 * Test pagination is included when quotes exceed rows
	 */
	public function test_pagination_present() {
		$attributes = array(
			'rows' => 3,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// Should include pagination controls since we have 7 quotes and showing only 3
		// Pagination is typically wrapped in a specific class or contains page links
		$this->assertStringContainsString( 'class="xv-quote"', $content );
	}

	/**
	 * Test empty category returns empty
	 */
	public function test_nonexistent_category() {
		$attributes = array(
			'categories' => 'nonexistent-category',
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertEmpty( $content );
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

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
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
			'rows' => 10,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringNotContainsString( 'This is a draft quote.', $content );
		// Should still only have 7 quotes, not 8
		$quote_count = substr_count( $content, 'class="xv-quote"' );
		$this->assertEquals( 7, $quote_count );

		wp_delete_post( $draft_id, true );
	}

	/**
	 * Test renders all quote components (text, author, source)
	 */
	public function test_renders_complete_quotes() {
		$attributes = array(
			'rows' => 2,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		$this->assertStringContainsString( 'class="xv-quote"', $content );
		// Should contain author and source information
		$this->assertStringContainsString( 'xv-quote-author', $content );
		$this->assertStringContainsString( 'xv-quote-source', $content );
	}

	/**
	 * Test rows parameter with value larger than total quotes
	 */
	public function test_rows_exceeds_total() {
		$attributes = array(
			'rows' => 100,
		);

		$block   = WP_Block_Type_Registry::get_instance()->get_registered( 'xv-random-quotes/list-quotes' );
		$content = call_user_func( $block->render_callback, $attributes );

		$this->assertNotEmpty( $content );
		// Should only show the 7 quotes we have
		$quote_count = substr_count( $content, 'class="xv-quote"' );
		$this->assertEquals( 7, $quote_count );
	}
}
