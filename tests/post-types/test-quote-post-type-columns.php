<?php
/**
 * Tests for Quote Post Type Custom Columns
 *
 * @package XVRandomQuotes
 */

use XVRandomQuotes\PostTypes\QuotePostType;

/**
 * Test Quote Post Type custom column rendering
 */
class Test_Quote_Post_Type_Columns extends WP_UnitTestCase {

	/**
	 * QuotePostType instance
	 *
	 * @var QuotePostType
	 */
	protected $post_type;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();
		$this->post_type = new QuotePostType();
		$this->post_type->init();
	}

	/**
	 * Test custom columns are added
	 */
	public function test_custom_columns_added() {
		$columns = $this->post_type->add_custom_columns( array( 'cb' => '', 'title' => 'Title', 'taxonomy-quote_author' => 'Author' ) );
		
		$this->assertArrayHasKey( 'quote_preview', $columns, 'Should have quote_preview column' );
		$this->assertArrayHasKey( 'quote_source', $columns, 'Should have quote_source column' );
		$this->assertArrayNotHasKey( 'title', $columns, 'Should not have default title column' );
	}

	/**
	 * Test title column is removed
	 */
	public function test_title_column_removed() {
		$columns = $this->post_type->add_custom_columns( array( 'cb' => '', 'title' => 'Title' ) );
		
		$this->assertArrayNotHasKey( 'title', $columns, 'Title column should be removed' );
	}

	/**
	 * Test quote_preview column appears after checkbox
	 */
	public function test_quote_preview_column_position() {
		$columns = $this->post_type->add_custom_columns( array( 'cb' => '', 'title' => 'Title', 'date' => 'Date' ) );
		
		$keys = array_keys( $columns );
		$this->assertEquals( 'cb', $keys[0], 'First column should be checkbox' );
		$this->assertEquals( 'quote_preview', $keys[1], 'Second column should be quote_preview' );
	}

	/**
	 * Test rendering quote with title shows title
	 */
	public function test_render_quote_with_title() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'  => 'xv_quote',
				'post_title' => 'Test Quote Title',
				'post_content' => 'Test quote content',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Test Quote Title', $output, 'Should display title' );
		$this->assertStringContainsString( 'row-title', $output, 'Should have row-title class' );
		$this->assertStringContainsString( '<strong>', $output, 'Should wrap in strong tag' );
	}

	/**
	 * Test rendering quote without title shows content preview
	 */
	public function test_render_quote_without_title_shows_content() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'  => 'xv_quote',
				'post_title' => '',
				'post_content' => 'This is a long quote content that should be trimmed to show only the first few words as a preview',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'This is a long', $output, 'Should show content preview' );
		$this->assertStringNotContainsString( 'Test Quote Title', $output, 'Should not show title' );
	}

	/**
	 * Test rendering quote with featured image but no content
	 */
	public function test_render_quote_with_featured_image() {
		// Create an attachment
		$attachment_id = $this->factory->attachment->create_upload_object(
			DIR_TESTDATA . '/images/canola.jpg'
		);

		$post_id = $this->factory->post->create(
			array(
				'post_type'  => 'xv_quote',
				'post_title' => '',
				'post_content' => '',
			)
		);

		set_post_thumbnail( $post_id, $attachment_id );

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<img', $output, 'Should contain image tag' );
		$this->assertStringContainsString( 'attachment-', $output, 'Should be WordPress thumbnail' );
	}

	/**
	 * Test rendering quote with image in content but no featured image
	 */
	public function test_render_quote_with_content_image() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => '',
				'post_content' => '<img src="http://example.com/image.jpg" width="300" height="200" alt="Test">',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<img', $output, 'Should contain image tag' );
		$this->assertStringContainsString( 'width:50px', $output, 'Should resize to 50px' );
		$this->assertStringContainsString( 'height:50px', $output, 'Should resize to 50px' );
		$this->assertStringNotContainsString( 'width="300"', $output, 'Should remove original width attribute' );
	}

	/**
	 * Test rendering quote with no content shows fallback
	 */
	public function test_render_quote_with_no_content() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'  => 'xv_quote',
				'post_title' => '',
				'post_content' => '',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( '(no content)', $output, 'Should show no content message' );
	}

	/**
	 * Test rendering quote source column with source
	 */
	public function test_render_quote_source_with_value() {
		$post_id = $this->factory->post->create(
			array(
				'post_type' => 'xv_quote',
			)
		);

		update_post_meta( $post_id, '_quote_source', 'Test Source Book' );

		ob_start();
		$this->post_type->render_custom_column( 'quote_source', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Test Source Book', $output, 'Should display source' );
	}

	/**
	 * Test rendering quote source column without source
	 */
	public function test_render_quote_source_without_value() {
		$post_id = $this->factory->post->create(
			array(
				'post_type' => 'xv_quote',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_source', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( '—', $output, 'Should display em dash placeholder' );
	}

	/**
	 * Test quote source column is sortable
	 */
	public function test_quote_source_column_sortable() {
		$columns = $this->post_type->make_sortable_columns( array() );
		
		$this->assertArrayHasKey( 'quote_source', $columns, 'quote_source should be sortable' );
		$this->assertEquals( 'quote_source', $columns['quote_source'], 'Should map to quote_source' );
	}

	/**
	 * Test all column outputs are escaped/sanitized
	 */
	public function test_column_output_escaping() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'  => 'xv_quote',
				'post_title' => '<script>alert("xss")</script>Title',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( '<script>', $output, 'Should escape script tags in title' );
		$this->assertStringContainsString( 'Title', $output, 'Should still show safe content' );
	}

	/**
	 * Test edit links are present in column output
	 */
	public function test_column_contains_edit_link() {
		// Set current user to have edit capabilities
		wp_set_current_user( 1 );
		
		$post_id = $this->factory->post->create(
			array(
				'post_type'  => 'xv_quote',
				'post_title' => 'Test Quote',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		// Check that output contains a link (may be empty href in test environment)
		$this->assertStringContainsString( '<a', $output, 'Should contain a link' );
		$this->assertStringContainsString( 'Test Quote', $output, 'Should show quote title' );
		$this->assertStringContainsString( 'row-title', $output, 'Should have row-title class' );
	}

	/**
	 * Test content with HTML is stripped for preview
	 */
	public function test_content_html_stripped_for_preview() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => '',
				'post_content' => '<p>This is <strong>formatted</strong> text content</p>',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( '<p>', $output, 'Should strip paragraph tags' );
		$this->assertStringNotContainsString( '<strong>', $output, 'Should strip strong tags' );
		$this->assertStringContainsString( 'This is formatted text', $output, 'Should show text content' );
	}

	/**
	 * Test whitespace-only title is treated as empty
	 */
	public function test_whitespace_title_treated_as_empty() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => '   ',
				'post_content' => 'Actual content here',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Actual content here', $output, 'Should show content when title is whitespace' );
	}

	/**
	 * Test whitespace-only content is treated as empty
	 */
	public function test_whitespace_content_treated_as_empty() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'xv_quote',
				'post_title'   => '',
				'post_content' => '   ',
			)
		);

		ob_start();
		$this->post_type->render_custom_column( 'quote_preview', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( '(no content)', $output, 'Should show no content when content is whitespace' );
	}
}
