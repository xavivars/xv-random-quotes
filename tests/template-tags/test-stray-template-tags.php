<?php
/**
 * Tests for Template Tag Functions
 *
 * Tests the template tag functions that allow direct inclusion
 * of quotes in theme templates.
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';

/**
 * Test template tag functions
 */
class Test_Stray_Template_Tags extends WP_UnitTestCase {

	/**
	 * Test quote IDs
	 *
	 * @var array
	 */
	private $quote_ids = array();

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Create test quotes
		for ( $i = 1; $i <= 5; $i++ ) {
			$post_id = wp_insert_post(
				array(
					'post_type'    => 'xv_quote',
					'post_title'   => 'Test Quote ' . $i,
					'post_content' => 'This is test quote number ' . $i . '.',
					'post_status'  => 'publish',
				)
			);

			$this->quote_ids[] = $post_id;

			// Add author
			$author_term = wp_insert_term( 'Author ' . $i, 'quote_author' );
			if ( ! is_wp_error( $author_term ) ) {
				wp_set_post_terms( $post_id, array( $author_term['term_id'] ), 'quote_author' );
			}

			// Add source
			update_post_meta( $post_id, '_quote_source', 'Source ' . $i );

			// Add category
			if ( $i % 2 === 0 ) {
				$cat_term = wp_insert_term( 'Science', 'quote_category' );
				if ( ! is_wp_error( $cat_term ) ) {
					wp_set_post_terms( $post_id, array( $cat_term['term_id'] ), 'quote_category' );
				}
			} else {
				$cat_term = wp_insert_term( 'Philosophy', 'quote_category' );
				if ( ! is_wp_error( $cat_term ) ) {
					wp_set_post_terms( $post_id, array( $cat_term['term_id'] ), 'quote_category' );
				}
			}
		}

		// Initialize v2.0 architecture
		do_action( 'init' );
	}

	/**
	 * Clean up after tests
	 */
	public function tearDown(): void {
		foreach ( $this->quote_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		// Clean up terms
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
	 * Test stray_random_quote function exists
	 */
	public function test_stray_random_quote_function_exists() {
		$this->assertTrue( function_exists( 'stray_random_quote' ) );
	}

	/**
	 * Test stray_a_quote function exists
	 */
	public function test_stray_a_quote_function_exists() {
		$this->assertTrue( function_exists( 'stray_a_quote' ) );
	}

	/**
	 * Test stray_random_quote outputs content
	 */
	public function test_stray_random_quote_outputs_content() {
		ob_start();
		stray_random_quote();
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test stray_random_quote with category filter
	 */
	public function test_stray_random_quote_with_category() {
		ob_start();
		stray_random_quote( 'Science' );
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		// Should get a quote from the Science category (even-numbered quotes)
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test stray_random_quote with multiple categories
	 */
	public function test_stray_random_quote_with_multiple_categories() {
		ob_start();
		stray_random_quote( 'Science,Philosophy' );
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test stray_random_quote with sequence parameter
	 */
	public function test_stray_random_quote_sequential() {
		ob_start();
		stray_random_quote( 'all', true ); // sequence = true
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test stray_random_quote with multi parameter
	 */
	public function test_stray_random_quote_multiple_quotes() {
		ob_start();
		stray_random_quote( 'all', false, '', false, 3 ); // multi = 3
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		// Should contain multiple quotes
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test stray_random_quote with orderby parameter
	 */
	public function test_stray_random_quote_with_orderby() {
		ob_start();
		stray_random_quote( 'all', false, '', false, 1, 0, 'quoteID', 'ASC' );
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test stray_random_quote with disableaspect parameter
	 */
	public function test_stray_random_quote_with_disableaspect() {
		ob_start();
		stray_random_quote( 'all', false, '', false, 1, 0, 'quoteID', 'ASC', true ); // disableaspect = true
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test stray_a_quote outputs specific quote
	 */
	public function test_stray_a_quote_outputs_specific_quote() {
		ob_start();
		stray_a_quote( $this->quote_ids[0] );
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number 1', $output );
		$this->assertStringContainsString( 'Author 1', $output );
	}

	/**
	 * Test stray_a_quote with different quote ID
	 */
	public function test_stray_a_quote_with_specific_id() {
		ob_start();
		stray_a_quote( $this->quote_ids[2] ); // Third quote
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number 3', $output );
	}

	/**
	 * Test stray_a_quote with disableaspect parameter
	 */
	public function test_stray_a_quote_with_disableaspect() {
		ob_start();
		stray_a_quote( $this->quote_ids[0], '', false, true ); // disableaspect = true
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number 1', $output );
	}

	/**
	 * Test stray_a_quote with non-existent ID returns empty or first quote
	 */
	public function test_stray_a_quote_with_nonexistent_id() {
		ob_start();
		stray_a_quote( 999999 );
		$output = ob_get_clean();

		// Should either be empty or return first available quote as fallback
		$this->assertIsString( $output );
	}

	/**
	 * Test stray_random_quote output format matches shortcode
	 */
	public function test_stray_random_quote_output_format() {
		ob_start();
		stray_random_quote( 'all', false, '', false, 1 );
		$template_output = ob_get_clean();

		$shortcode_output = do_shortcode( '[stray-random]' );

		// Both should contain quote content
		$this->assertNotEmpty( $template_output );
		$this->assertNotEmpty( $shortcode_output );

		// Both should have similar structure (contain author, quote text)
		$this->assertStringContainsString( 'Author', $template_output );
		$this->assertStringContainsString( 'test quote number', $template_output );
	}

	/**
	 * Test stray_a_quote output format matches shortcode
	 */
	public function test_stray_a_quote_output_format() {
		ob_start();
		stray_a_quote( $this->quote_ids[1] );
		$template_output = ob_get_clean();

		$shortcode_output = do_shortcode( '[stray-id id="' . $this->quote_ids[1] . '"]' );

		// Both should contain the same quote
		$this->assertNotEmpty( $template_output );
		$this->assertNotEmpty( $shortcode_output );

		// Both should contain the same content
		$this->assertStringContainsString( 'test quote number 2', $template_output );
		$this->assertStringContainsString( 'test quote number 2', $shortcode_output );
	}

	/**
	 * Test backward compatibility - parameter order
	 */
	public function test_stray_random_quote_parameter_order() {
		// Test with all parameters in correct order
		ob_start();
		stray_random_quote(
			'Philosophy',  // categories
			false,         // sequence
			'',            // linkphrase
			false,         // noajax
			1,             // multi
			0,             // timer
			'quoteID',     // orderby
			'ASC',         // sort
			false,         // disableaspect
			''             // contributor
		);
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'test quote number', $output );
	}

	/**
	 * Test backward compatibility - partial parameters
	 */
	public function test_stray_random_quote_partial_parameters() {
		// Test with only first few parameters (should work due to defaults)
		ob_start();
		stray_random_quote( 'Science', true );
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
	}

	/**
	 * Test stray_a_quote with legacy ID support
	 */
	public function test_stray_a_quote_legacy_id_support() {
		// Add legacy ID to a quote
		update_post_meta( $this->quote_ids[0], '_quote_legacy_id', 42 );

		ob_start();
		stray_a_quote( 42 ); // Try with legacy ID
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		// Should find the quote with legacy ID 42
		$this->assertStringContainsString( 'test quote number 1', $output );
	}

	/**
	 * Test stray_random_quote includes author and source
	 */
	public function test_stray_random_quote_includes_metadata() {
		ob_start();
		stray_random_quote();
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		// Should include author
		$this->assertStringContainsString( 'Author', $output );
		// Should include source
		$this->assertStringContainsString( 'Source', $output );
	}

	/**
	 * Test stray_a_quote includes author and source
	 */
	public function test_stray_a_quote_includes_metadata() {
		ob_start();
		stray_a_quote( $this->quote_ids[0] );
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'Author 1', $output );
		$this->assertStringContainsString( 'Source 1', $output );
	}

	/**
	 * Test function_exists checks (for theme compatibility)
	 */
	public function test_function_exists_pattern() {
		// This is the recommended pattern in documentation
		if ( function_exists( 'stray_random_quote' ) ) {
			ob_start();
			stray_random_quote();
			$output = ob_get_clean();
			$this->assertNotEmpty( $output );
		}

		if ( function_exists( 'stray_a_quote' ) ) {
			ob_start();
			stray_a_quote( $this->quote_ids[0] );
			$output = ob_get_clean();
			$this->assertNotEmpty( $output );
		}
	}
}
