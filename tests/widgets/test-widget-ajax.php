<?php
/**
 * Tests for Widget AJAX Functionality
 *
 * @package XVRandomQuotes
 */

/**
 * Test class for widget AJAX functionality
 */
class Test_Widget_Ajax extends WP_UnitTestCase {

	/**
	 * Widget instance
	 *
	 * @var XVRandomQuotes\Widgets\QuoteWidget
	 */
	private $widget;

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
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		$this->widget = new \XVRandomQuotes\Widgets\QuoteWidget();

		// Create test categories
		$science = wp_insert_term( 'Science', 'quote_category' );
		$this->category_ids['science'] = $science['term_id'];

		$philosophy = wp_insert_term( 'Philosophy', 'quote_category' );
		$this->category_ids['philosophy'] = $philosophy['term_id'];

		// Create test quotes
		$quotes_data = array(
			array( 'science', 'The science of today is the technology of tomorrow.' ),
			array( 'science', 'Science knows no country.' ),
			array( 'philosophy', 'I think, therefore I am.' ),
		);

		foreach ( $quotes_data as $index => $quote_data ) {
			list( $category_slug, $quote_text ) = $quote_data;

			$post_id = wp_insert_post(
				array(
					'post_type'    => 'xv_quote',
					'post_title'   => $quote_text,
					'post_content' => $quote_text,
					'post_status'  => 'publish',
				)
			);

			$this->quote_ids[] = $post_id;

			wp_set_post_terms( $post_id, array( $this->category_ids[ $category_slug ] ), 'quote_category' );

			$author_term = wp_insert_term( "Author $index", 'quote_author' );
			if ( ! is_wp_error( $author_term ) ) {
				wp_set_post_terms( $post_id, array( $author_term['term_id'] ), 'quote_author' );
			}

			update_post_meta( $post_id, '_quote_source', "Source $index" );
		}
	}

	/**
	 * Clean up after each test
	 */
	public function tearDown(): void {
		foreach ( $this->quote_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		$terms = get_terms(
			array(
				'taxonomy'   => array( 'quote_author', 'quote_category' ),
				'hide_empty' => false,
			)
		);

		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $term->taxonomy );
		}

		parent::tearDown();
	}

	/**
	 * Test widget form includes enable_ajax field
	 */
	public function test_widget_form_includes_enable_ajax_field() {
		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => true,
		);

		ob_start();
		$this->widget->form( $instance );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'enable_ajax', $output, 'Form should include enable_ajax field' );
		$this->assertStringContainsString( 'checkbox', $output, 'enable_ajax should be a checkbox' );
	}

	/**
	 * Test widget form includes timer field
	 */
	public function test_widget_form_includes_timer_field() {
		$instance = array(
			'title' => 'Test Widget',
			'timer' => 30,
		);

		ob_start();
		$this->widget->form( $instance );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'timer', $output, 'Form should include timer field' );
		$this->assertMatchesRegularExpression( '/type=["\']number["\']/', $output, 'Timer should be a number input' );
	}

	/**
	 * Test widget update saves enable_ajax setting
	 */
	public function test_widget_update_saves_enable_ajax() {
		$old_instance = array();
		$new_instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => '1',
			'timer'       => '0',
		);

		$result = $this->widget->update( $new_instance, $old_instance );

		$this->assertArrayHasKey( 'enable_ajax', $result, 'Updated instance should have enable_ajax' );
		$this->assertTrue( $result['enable_ajax'], 'enable_ajax should be boolean true' );
	}

	/**
	 * Test widget update saves timer setting
	 */
	public function test_widget_update_saves_timer() {
		$old_instance = array();
		$new_instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => '0',
			'timer'       => '30',
		);

		$result = $this->widget->update( $new_instance, $old_instance );

		$this->assertArrayHasKey( 'timer', $result, 'Updated instance should have timer' );
		$this->assertSame( 30, $result['timer'], 'Timer should be integer 30' );
	}

	/**
	 * Test widget update sanitizes timer to integer
	 */
	public function test_widget_update_sanitizes_timer_to_integer() {
		$old_instance = array();
		$new_instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => '0',
			'timer'       => '45.7', // Float string
		);

		$result = $this->widget->update( $new_instance, $old_instance );

		$this->assertIsInt( $result['timer'], 'Timer should be integer' );
		$this->assertSame( 45, $result['timer'], 'Timer should be sanitized to 45' );
	}

	/**
	 * Test widget update defaults enable_ajax to false
	 */
	public function test_widget_update_defaults_enable_ajax_to_false() {
		$old_instance = array();
		$new_instance = array(
			'title' => 'Test Widget',
			// enable_ajax not set
		);

		$result = $this->widget->update( $new_instance, $old_instance );

		$this->assertArrayHasKey( 'enable_ajax', $result, 'Updated instance should have enable_ajax' );
		$this->assertFalse( $result['enable_ajax'], 'enable_ajax should default to false' );
	}

	/**
	 * Test widget update defaults timer to 0
	 */
	public function test_widget_update_defaults_timer_to_zero() {
		$old_instance = array();
		$new_instance = array(
			'title' => 'Test Widget',
			// timer not set
		);

		$result = $this->widget->update( $new_instance, $old_instance );

		$this->assertArrayHasKey( 'timer', $result, 'Updated instance should have timer' );
		$this->assertSame( 0, $result['timer'], 'Timer should default to 0' );
	}

	/**
	 * Test widget output includes refresh link when AJAX enabled
	 */
	public function test_widget_output_includes_refresh_link_when_ajax_enabled() {
		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		);

		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => true,
			'timer'       => 0,
			'categories'  => 'all',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'xv-quote-refresh', $output, 'Output should include refresh link class' );
		$this->assertMatchesRegularExpression( '/<a[^>]*class=["\'][^"\']*xv-quote-refresh[^"\']*["\']/', $output, 'Should have anchor with refresh class' );
	}

	/**
	 * Test widget output does NOT include refresh link when AJAX disabled
	 */
	public function test_widget_output_excludes_refresh_link_when_ajax_disabled() {
		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		);

		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => false,
			'timer'       => 0,
			'categories'  => 'all',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'xv-quote-refresh', $output, 'Output should NOT include refresh link when AJAX disabled' );
	}

	/**
	 * Test widget output includes wrapper div with unique ID
	 */
	public function test_widget_output_includes_wrapper_with_unique_id() {
		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
			'widget_id'     => 'xv_quote_widget-2',
		);

		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => true,
			'timer'       => 0,
			'categories'  => 'all',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertMatchesRegularExpression( '/id=["\']xv-quote-container-[^"\']+["\']/', $output, 'Should have wrapper div with unique ID' );
		$this->assertStringContainsString( 'xv-quote-container', $output, 'Should use xv-quote-container class' );
	}

	/**
	 * Test widget output includes data attributes for REST API
	 */
	public function test_widget_output_includes_data_attributes() {
		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
			'widget_id'     => 'xv_quote_widget-2',
		);

		$instance = array(
			'title'         => 'Test Widget',
			'enable_ajax'   => true,
			'timer'         => 30,
			'categories'    => 'science,philosophy',
			'sequence'      => true,
			'multi'         => 2,
			'disableaspect' => true,
			'contributor'   => 'john',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-categories=', $output, 'Should include data-categories' );
		$this->assertStringContainsString( 'data-sequence=', $output, 'Should include data-sequence' );
		$this->assertStringContainsString( 'data-multi=', $output, 'Should include data-multi' );
		$this->assertStringContainsString( 'data-disableaspect=', $output, 'Should include data-disableaspect' );
		$this->assertStringContainsString( 'data-contributor=', $output, 'Should include data-contributor' );
		$this->assertStringContainsString( 'data-timer=', $output, 'Should include data-timer' );
	}

	/**
	 * Test data attributes contain correct values
	 */
	public function test_data_attributes_contain_correct_values() {
		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
			'widget_id'     => 'xv_quote_widget-2',
		);

		$instance = array(
			'title'         => 'Test Widget',
			'enable_ajax'   => true,
			'timer'         => 30,
			'categories'    => 'science',
			'sequence'      => true,
			'multi'         => 2,
			'disableaspect' => false,
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-categories="science"', $output );
		$this->assertMatchesRegularExpression( '/data-sequence=["\']1["\']|data-sequence=["\']true["\']/', $output );
		$this->assertStringContainsString( 'data-multi="2"', $output );
		$this->assertStringContainsString( 'data-timer="30"', $output );
	}

	/**
	 * Test script enqueued when AJAX widget is rendered
	 */
	public function test_script_enqueued_when_ajax_widget_rendered() {
		global $wp_scripts;

		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		);

		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => true,
			'timer'       => 0,
			'categories'  => 'all',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		ob_get_clean();

		// Check if script is enqueued
		$this->assertTrue( wp_script_is( 'xv-quote-refresh', 'enqueued' ), 'Quote refresh script should be enqueued when AJAX enabled' );
	}

	/**
	 * Test script NOT enqueued when AJAX disabled
	 */
	public function test_script_not_enqueued_when_ajax_disabled() {
		global $wp_scripts;

		// Reset scripts
		$wp_scripts = new WP_Scripts();

		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		);

		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => false,
			'timer'       => 0,
			'categories'  => 'all',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		ob_get_clean();

		$this->assertFalse( wp_script_is( 'xv-quote-refresh', 'enqueued' ), 'Quote refresh script should NOT be enqueued when AJAX disabled' );
	}

	/**
	 * Test script has correct dependencies
	 */
	public function test_script_has_correct_dependencies() {
		global $wp_scripts;

		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		);

		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => true,
			'timer'       => 0,
			'categories'  => 'all',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		ob_get_clean();

		// Verify script is enqueued first
		$this->assertTrue( wp_script_is( 'xv-quote-refresh', 'enqueued' ), 'Script should be enqueued' );
		
		// Check registered scripts
		$this->assertArrayHasKey( 'xv-quote-refresh', $wp_scripts->registered, 'Script should be registered' );
		
		$script = $wp_scripts->registered['xv-quote-refresh'];
		
		// Script should NOT depend on jQuery (modern vanilla JS)
		$this->assertNotContains( 'jquery', $script->deps, 'Script should use vanilla JavaScript, not jQuery' );
	}

	/**
	 * Test localized script data includes REST URL
	 */
	public function test_localized_script_includes_rest_url() {
		global $wp_scripts;

		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		);

		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => true,
			'timer'       => 0,
			'categories'  => 'all',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		ob_get_clean();

		// Verify script is registered
		$this->assertTrue( wp_script_is( 'xv-quote-refresh', 'enqueued' ), 'Script should be enqueued' );
		$this->assertArrayHasKey( 'xv-quote-refresh', $wp_scripts->registered, 'Script should be registered' );
		
		// Get the extra data (localized script)
		$extra = $wp_scripts->get_data( 'xv-quote-refresh', 'data' );
		
		$this->assertNotEmpty( $extra, 'Script should have localized data' );
		$this->assertStringContainsString( 'xvQuoteRefresh', $extra, 'Should have xvQuoteRefresh object' );
		$this->assertStringContainsString( 'restUrl', $extra, 'Should include REST URL' );
	}

	/**
	 * Test widget with timer=0 only shows manual refresh
	 */
	public function test_timer_zero_shows_manual_refresh_only() {
		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		);

		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => true,
			'timer'       => 0, // Manual refresh only
			'categories'  => 'all',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-timer="0"', $output, 'Timer should be 0 for manual refresh' );
		$this->assertStringContainsString( 'xv-quote-refresh', $output, 'Should still have refresh link' );
	}

	/**
	 * Test widget with timer>0 enables auto-refresh
	 */
	public function test_timer_greater_than_zero_enables_auto_refresh() {
		$args = array(
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		);

		$instance = array(
			'title'       => 'Test Widget',
			'enable_ajax' => true,
			'timer'       => 60, // Auto-refresh every 60 seconds
			'categories'  => 'all',
		);

		ob_start();
		$this->widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-timer="60"', $output, 'Timer should be 60 for auto-refresh' );
	}
}
