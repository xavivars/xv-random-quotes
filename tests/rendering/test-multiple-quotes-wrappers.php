<?php
/**
 * Tests for multiple quotes wrapper behavior
 *
 * Verifies that before_all/after_all wrappers appear only once
 * when rendering multiple quotes, while before_quote/after_quote
 * wrap each individual quote.
 *
 * @package XVRandomQuotes\Tests
 */

use XVRandomQuotes\Rendering\QuoteRenderer;
use XVRandomQuotes\Rendering\LegacyRenderer;
use XVRandomQuotes\Admin\Settings;

/**
 * Test multiple quotes wrapper behavior
 */
class Test_Multiple_Quotes_Wrappers extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Set custom wrapper settings
		update_option( Settings::OPTION_BEFORE_ALL, '<div class="all-quotes-start">' );
		update_option( Settings::OPTION_AFTER_ALL, '<div class="all-quotes-end">' );
		update_option( Settings::OPTION_BEFORE_QUOTE, '<div class="quote-start">' );
		update_option( Settings::OPTION_AFTER_QUOTE, '<div class="quote-end">' );
		update_option( Settings::OPTION_USE_NATIVE_STYLING, '0' ); // Use legacy mode
	}

	/**
	 * Clean up after tests
	 */
	public function tearDown(): void {
		delete_option( Settings::OPTION_BEFORE_ALL );
		delete_option( Settings::OPTION_AFTER_ALL );
		delete_option( Settings::OPTION_BEFORE_QUOTE );
		delete_option( Settings::OPTION_AFTER_QUOTE );
		delete_option( Settings::OPTION_USE_NATIVE_STYLING );

		parent::tearDown();
	}

	/**
	 * Test that before_all/after_all appear only once with multiple quotes
	 */
	public function test_before_after_all_appear_once_for_multiple_quotes() {
		// Create test quotes
		$quote1 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'First test quote',
			)
		);

		$quote2 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'Second test quote',
			)
		);

		$quote3 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'Third test quote',
			)
		);

		$renderer = new QuoteRenderer( new LegacyRenderer() );
		$output = $renderer->render_multiple_quotes( array( $quote1, $quote2, $quote3 ), false );

		// Count occurrences of before_all/after_all
		$before_all_count = substr_count( $output, '<div class="all-quotes-start">' );
		$after_all_count = substr_count( $output, '<div class="all-quotes-end">' );

		$this->assertEquals( 1, $before_all_count, 'before_all should appear exactly once' );
		$this->assertEquals( 1, $after_all_count, 'after_all should appear exactly once' );

		// Verify before_all/after_all wrap the entire output
		$this->assertStringStartsWith( '<div class="all-quotes-start">', $output );
		$this->assertStringEndsWith( '<div class="all-quotes-end">', $output );
	}

	/**
	 * Test that before_quote/after_quote appear for each quote
	 */
	public function test_before_after_quote_appear_for_each_quote() {
		// Create test quotes
		$quote1 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'First test quote',
			)
		);

		$quote2 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'Second test quote',
			)
		);

		$quote3 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'Third test quote',
			)
		);

		$renderer = new QuoteRenderer( new LegacyRenderer() );
		$output = $renderer->render_multiple_quotes( array( $quote1, $quote2, $quote3 ), false );

		// Count occurrences of before_quote/after_quote
		$before_quote_count = substr_count( $output, '<div class="quote-start">' );
		$after_quote_count = substr_count( $output, '<div class="quote-end">' );

		$this->assertEquals( 3, $before_quote_count, 'before_quote should appear once per quote' );
		$this->assertEquals( 3, $after_quote_count, 'after_quote should appear once per quote' );
	}

	/**
	 * Test single quote rendering still uses before_all/after_all
	 */
	public function test_single_quote_uses_before_after_all() {
		$quote = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'Single test quote',
			)
		);

		$renderer = new QuoteRenderer( new LegacyRenderer() );
		$output = $renderer->render_quote( $quote, false, false );

		// Single quote should have before_all/after_all
		$before_all_count = substr_count( $output, '<div class="all-quotes-start">' );
		$after_all_count = substr_count( $output, '<div class="all-quotes-end">' );

		$this->assertEquals( 1, $before_all_count, 'Single quote should have before_all' );
		$this->assertEquals( 1, $after_all_count, 'Single quote should have after_all' );
	}

	/**
	 * Test that wrappers are not applied when disable_aspect is true
	 */
	public function test_no_wrappers_when_disabled() {
		$quote1 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'First test quote',
			)
		);

		$quote2 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'Second test quote',
			)
		);

		$renderer = new QuoteRenderer( new LegacyRenderer() );
		$output = $renderer->render_multiple_quotes( array( $quote1, $quote2 ), true );

		// No wrappers should be present
		$this->assertStringNotContainsString( '<div class="all-quotes-start">', $output );
		$this->assertStringNotContainsString( '<div class="all-quotes-end">', $output );
		$this->assertStringNotContainsString( '<div class="quote-start">', $output );
		$this->assertStringNotContainsString( '<div class="quote-end">', $output );
	}

	/**
	 * Test native styling mode does not use before_all/after_all
	 */
	public function test_native_styling_mode_does_not_use_before_after_all() {
		// Enable native styling
		update_option( Settings::OPTION_USE_NATIVE_STYLING, '1' );

		$quote1 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'First test quote',
			)
		);

		$quote2 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'Second test quote',
			)
		);

		$renderer = new QuoteRenderer( new LegacyRenderer() );
		$output = $renderer->render_multiple_quotes( array( $quote1, $quote2 ), false );

		// Should have before_all/after_all exactly once
		$before_all_count = substr_count( $output, '<div class="all-quotes-start">' );
		$after_all_count = substr_count( $output, '<div class="all-quotes-end">' );

		$this->assertEquals( 0, $before_all_count, 'Native mode should not have before_all once' );
		$this->assertEquals( 0, $after_all_count, 'Native mode should not have after_all once' );
	}

	/**
	 * Test wrapper ordering: before_all, quotes, after_all
	 */
	public function test_wrapper_ordering_is_correct() {
		$quote1 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'First quote',
			)
		);

		$quote2 = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'stray_quotes',
				'post_content' => 'Second quote',
			)
		);

		$renderer = new QuoteRenderer( new LegacyRenderer() );
		$output = $renderer->render_multiple_quotes( array( $quote1, $quote2 ), false );

		// Find positions
		$before_all_pos = strpos( $output, '<div class="all-quotes-start">' );
		$first_quote_pos = strpos( $output, 'First quote' );
		$second_quote_pos = strpos( $output, 'Second quote' );
		$after_all_pos = strpos( $output, '<div class="all-quotes-end">' );

		// Verify order
		$this->assertLessThan( $first_quote_pos, $before_all_pos, 'before_all should come before first quote' );
		$this->assertLessThan( $second_quote_pos, $first_quote_pos, 'First quote should come before second quote' );
		$this->assertLessThan( $after_all_pos, $second_quote_pos, 'Second quote should come before after_all' );
	}
}
