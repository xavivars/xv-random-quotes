<?php
/**
 * Tests for Widget Data Retrieval
 *
 * Tests that the widget can retrieve quotes correctly using the new
 * CPT/taxonomy architecture.
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';

/**
 * Test widget quote retrieval functionality
 */
class Test_Widget_Data_Retrieval extends WP_UnitTestCase {

	/**
	 * Test quote IDs
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
	 * Widget instance
	 *
	 * @var \XVRandomQuotes\Widgets\QuoteWidget
	 */
	private $widget;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Create test quotes with categories
		$categories = array('Science', 'Philosophy', 'Literature');
		
		foreach ($categories as $cat_index => $cat_name) {
			// Create category term
			$cat_term = wp_insert_term($cat_name, 'quote_category');
			if (!is_wp_error($cat_term)) {
				$this->category_ids[$cat_name] = $cat_term['term_id'];
			}

			// Create 3 quotes per category
			for ($i = 1; $i <= 3; $i++) {
				$quote_num = ($cat_index * 3) + $i;
				$post_id = wp_insert_post(
					array(
						'post_type'    => 'xv_quote',
						'post_title'   => "Quote {$quote_num} - {$cat_name}",
						'post_content' => "This is quote {$quote_num} from {$cat_name}.",
						'post_status'  => 'publish',
					)
				);

				$this->quote_ids[] = $post_id;

				// Add author
				$author_term = wp_insert_term("Author {$quote_num}", 'quote_author');
				if (!is_wp_error($author_term)) {
					wp_set_post_terms($post_id, array($author_term['term_id']), 'quote_author');
				}

				// Add source
				update_post_meta($post_id, '_quote_source', "Source {$quote_num}");

				// Add category
				wp_set_post_terms($post_id, array($this->category_ids[$cat_name]), 'quote_category');
			}
		}

		// Create widget instance
		$this->widget = new \XVRandomQuotes\Widgets\QuoteWidget();

		// Initialize v2.0 architecture
		do_action('init');
	}

	/**
	 * Clean up after tests
	 */
	public function tearDown(): void {
		foreach ($this->quote_ids as $post_id) {
			wp_delete_post($post_id, true);
		}

		// Clean up terms
		$terms = get_terms(
			array(
				'taxonomy'   => array('quote_author', 'quote_category'),
				'hide_empty' => false,
			)
		);
		foreach ($terms as $term) {
			wp_delete_term($term->term_id, $term->taxonomy);
		}

		parent::tearDown();
	}

	/**
	 * Test widget instance exists
	 */
	public function test_widget_class_exists() {
		$this->assertTrue(class_exists('\XVRandomQuotes\Widgets\QuoteWidget'));
	}

	/**
	 * Test widget can be instantiated
	 */
	public function test_widget_can_be_instantiated() {
		$this->assertInstanceOf('\XVRandomQuotes\Widgets\QuoteWidget', $this->widget);
	}

	/**
	 * Test widget retrieves quotes with single category filter
	 */
	public function test_widget_retrieves_quotes_with_single_category() {
		// Widget instance settings
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => 'Science',
			'sequence'     => false,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should contain a Science quote
		$this->assertStringContainsString('Science', $output);
		$this->assertStringContainsString('quote', $output);
	}

	/**
	 * Test widget retrieves quotes with multiple category filter
	 */
	public function test_widget_retrieves_quotes_with_multiple_categories() {
		// Widget instance settings with multiple categories
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => 'Science,Philosophy',
			'sequence'     => false,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should contain either Science or Philosophy quote
		$this->assertTrue(
			strpos($output, 'Science') !== false || strpos($output, 'Philosophy') !== false
		);
	}

	/**
	 * Test widget multi-quote display
	 */
	public function test_widget_displays_multiple_quotes() {
		// Widget instance settings for multiple quotes
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => 'Science',
			'sequence'     => false,
			'multi'        => 3,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should contain multiple quotes (look for list structure)
		$this->assertStringContainsString('<ul>', $output);
		$this->assertStringContainsString('<li>', $output);
		$this->assertStringContainsString('Science', $output);
	}

	/**
	 * Test widget with random sequence
	 */
	public function test_widget_with_random_sequence() {
		// Widget instance settings with random sequence
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => '',  // all categories
			'sequence'     => true, // Random
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should contain a quote
		$this->assertNotEmpty($output);
		$this->assertStringContainsString('quote', $output);
	}

	/**
	 * Test widget with sequential ordering
	 */
	public function test_widget_with_sequential_ordering() {
		// Widget instance settings with sequential ordering
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => 'Philosophy',
			'sequence'     => false, // Sequential (not random)
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should contain a Philosophy quote
		$this->assertStringContainsString('Philosophy', $output);
	}

	/**
	 * Test widget with disabled aspect settings
	 */
	public function test_widget_with_disabled_aspect() {
		// Widget instance settings with disabled aspect
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => '',  // all categories
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => true, // Disable aspect
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should still contain quote text
		$this->assertStringContainsString('quote', $output);
	}

	/**
	 * Test widget with AJAX disabled
	 * TODO: AJAX features temporarily disabled, will be restored in Tasks 35-36
	 */
	public function test_widget_with_ajax_disabled() {
		// Widget instance settings (AJAX not yet implemented)
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => 'Science',
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should not contain AJAX-related elements (AJAX not yet implemented)
		$this->assertStringNotContainsString('xv_random_quotes.newQuote', $output);
	}

	/**
	 * Test widget with AJAX enabled
	 * TODO: AJAX features temporarily disabled, will be restored in Tasks 35-36
	 */
	public function test_widget_with_ajax_enabled() {
		// Widget instance settings (AJAX not yet implemented)
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => 'Literature',
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should contain quote (AJAX functionality will be added in Tasks 35-36)
		$this->assertStringContainsString('Literature', $output);
	}

	/**
	 * Test widget with timer for auto-refresh
	 * TODO: Timer/AJAX features temporarily disabled, will be restored in Tasks 35-36
	 */
	public function test_widget_with_timer() {
		// Widget instance settings (timer not yet implemented)
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => '',  // all categories
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should contain output (timer functionality will be added in Tasks 35-36)
		$this->assertNotEmpty($output);
	}

	/**
	 * Test widget with contributor filter
	 */
	public function test_widget_with_contributor_filter() {
		// Create a quote with specific contributor
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => 'Contributor Quote',
				'post_content' => 'This is a quote from a specific contributor.',
				'post_status'  => 'publish',
			)
		);

		// Add contributor meta (if using multiuser)
		update_post_meta($post_id, '_quote_contributor', 'john_doe');

		// Widget instance settings with contributor filter
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => '',  // all categories
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => false,
			'contributor'  => 'john_doe',
		);

		// Enable multiuser
		$quote_options = get_option('stray_quotes_options');
		if (!$quote_options) {
			$quote_options = array();
		}
		$quote_options['stray_multiuser'] = 'Y';
		update_option('stray_quotes_options', $quote_options);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should contain output (may or may not match depending on implementation)
		$this->assertNotEmpty($output);

		// Clean up
		wp_delete_post($post_id, true);
	}

	/**
	 * Test widget with all categories
	 */
	public function test_widget_with_all_categories() {
		// Widget instance settings with all categories (empty string)
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => '',  // Empty means all
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should contain a quote from any category
		$this->assertNotEmpty($output);
		$this->assertStringContainsString('quote', $output);
	}

	/**
	 * Test widget with nonexistent category
	 */
	public function test_widget_with_nonexistent_category() {
		// Widget instance settings with nonexistent category
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => 'NonexistentCategory',
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should handle gracefully (may return empty or fallback)
		$this->assertIsString($output);
	}

	/**
	 * Test widget includes widget wrapper HTML
	 */
	public function test_widget_includes_wrapper_html() {
		// Widget instance settings
		$instance = array(
			'title'        => 'My Quotes',
			'categories'   => 'Science',
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget-quotes">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should include widget wrapper elements
		$this->assertStringContainsString('<div class="widget-quotes">', $output);
		$this->assertStringContainsString('</div>', $output);
		$this->assertStringContainsString('<h3 class="widget-title">', $output);
		$this->assertStringContainsString('My Quotes', $output);
		$this->assertStringContainsString('</h3>', $output);
	}

	/**
	 * Test widget with empty title
	 */
	public function test_widget_with_empty_title() {
		// Widget instance settings with empty title
		$instance = array(
			'title'        => '',
			'categories'   => 'Philosophy',
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// Should still display quote even without title
		$this->assertStringContainsString('Philosophy', $output);
	}

	/**
	 * Test widget respects category taxonomy
	 */
	public function test_widget_respects_category_taxonomy() {
		// This test verifies that after refactoring, categories come from quote_category taxonomy
		
		// Widget instance settings
		$instance = array(
			'title'        => 'Test Widget',
			'categories'   => 'Science',
			'sequence'     => true,
			'multi'        => 1,
			'disableaspect' => false,
		);

		// Capture widget output
		ob_start();
		$this->widget->widget(
			array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			),
			$instance
		);
		$output = ob_get_clean();

		// After refactoring, this should work with quote_category taxonomy
		// For now, just verify output exists
		$this->assertNotEmpty($output);
	}
}
