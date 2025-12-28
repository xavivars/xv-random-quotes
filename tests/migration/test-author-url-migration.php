<?php
/**
 * Class Test_Author_URL_Migration
 *
 * Tests author URL extraction and term meta migration from legacy data.
 *
 * @package XVRandomQuotes
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/class-migration-test-base.php';

use XVRandomQuotes\Migration\QuoteMigrator;

/**
 * Test author URL migration from legacy database
 */
class Test_Author_URL_Migration extends Migration_Test_Base {

	/**
	 * Test author URL extraction from anchor tag
	 */
	public function test_author_url_extracted_from_anchor_tag() {
		global $wpdb;

		// Insert legacy quote with author link
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Test quote',
				'author'   => '<a href="https://en.wikipedia.org/wiki/Albert_Einstein">Albert Einstein</a>',
				'source'   => 'Test source',
				'category' => 'Test',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);

		$quote_id = $wpdb->insert_id;

		// Migrate the quote
		$post_id = $this->migrator->migrate_single_quote( $quote_id );

		$this->assertNotFalse( $post_id );

		// Check that author term was created with clean name
		$terms = wp_get_post_terms( $post_id, 'quote_author' );
		$this->assertCount( 1, $terms );
		$this->assertEquals( 'Albert Einstein', $terms[0]->name );

		// Check that author URL was saved to term meta
		$author_url = get_term_meta( $terms[0]->term_id, 'author_url', true );
		$this->assertEquals( 'https://en.wikipedia.org/wiki/Albert_Einstein', $author_url );
	}

	/**
	 * Test author URL extraction with complex HTML attributes
	 */
	public function test_author_url_extraction_with_complex_attributes() {
		global $wpdb;

		// Insert legacy quote with author link containing multiple attributes
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Test quote',
				'author'   => '<a href="http://example.com/author" target="_blank" class="author-link">John Doe</a>',
				'source'   => 'Test source',
				'category' => 'Test',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);

		$quote_id = $wpdb->insert_id;
		$post_id = $this->migrator->migrate_single_quote( $quote_id );

		$this->assertNotFalse( $post_id );

		// Check author name is clean
		$terms = wp_get_post_terms( $post_id, 'quote_author' );
		$this->assertEquals( 'John Doe', $terms[0]->name );

		// Check URL was extracted correctly
		$author_url = get_term_meta( $terms[0]->term_id, 'author_url', true );
		$this->assertEquals( 'http://example.com/author', $author_url );
	}

	/**
	 * Test author without URL (plain text)
	 */
	public function test_author_without_url_plain_text() {
		global $wpdb;

		// Insert legacy quote with plain text author
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Test quote',
				'author'   => 'Jane Smith',
				'source'   => 'Test source',
				'category' => 'Test',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);

		$quote_id = $wpdb->insert_id;
		$post_id = $this->migrator->migrate_single_quote( $quote_id );

		$this->assertNotFalse( $post_id );

		// Check author name
		$terms = wp_get_post_terms( $post_id, 'quote_author' );
		$this->assertEquals( 'Jane Smith', $terms[0]->name );

		// Check that no URL was saved
		$author_url = get_term_meta( $terms[0]->term_id, 'author_url', true );
		$this->assertEmpty( $author_url );
	}

	/**
	 * Test author with HTML formatting but no link
	 */
	public function test_author_with_html_formatting_no_link() {
		global $wpdb;

		// Insert legacy quote with HTML formatting in author
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Test quote',
				'author'   => '<strong>Bob Johnson</strong>',
				'source'   => 'Test source',
				'category' => 'Test',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);

		$quote_id = $wpdb->insert_id;
		$post_id = $this->migrator->migrate_single_quote( $quote_id );

		$this->assertNotFalse( $post_id );

		// Check that HTML was stripped from author name
		$terms = wp_get_post_terms( $post_id, 'quote_author' );
		$this->assertEquals( 'Bob Johnson', $terms[0]->name );

		// Check that no URL was saved
		$author_url = get_term_meta( $terms[0]->term_id, 'author_url', true );
		$this->assertEmpty( $author_url );
	}

	/**
	 * Test that existing author URL is not overwritten
	 */
	public function test_existing_author_url_not_overwritten() {
		global $wpdb;

		// Create an author term with URL manually
		$term = wp_insert_term( 'Existing Author', 'quote_author' );
		$term_id = $term['term_id'];
		update_term_meta( $term_id, 'author_url', 'https://original-url.com' );

		// Insert legacy quote with same author but different URL
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Test quote',
				'author'   => '<a href="https://different-url.com">Existing Author</a>',
				'source'   => 'Test source',
				'category' => 'Test',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);

		$quote_id = $wpdb->insert_id;
		$post_id = $this->migrator->migrate_single_quote( $quote_id );

		$this->assertNotFalse( $post_id );

		// Check that original URL was NOT overwritten
		$author_url = get_term_meta( $term_id, 'author_url', true );
		$this->assertEquals( 'https://original-url.com', $author_url );
	}

	/**
	 * Test URL sanitization with esc_url_raw
	 */
	public function test_author_url_sanitization() {
		global $wpdb;

		// Insert legacy quote with potentially malicious URL
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Test quote',
				'author'   => '<a href="javascript:alert(\'xss\')">Malicious Author</a>',
				'source'   => 'Test source',
				'category' => 'Test',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);

		$quote_id = $wpdb->insert_id;
		$post_id = $this->migrator->migrate_single_quote( $quote_id );

		$this->assertNotFalse( $post_id );

		// Check that author name is clean
		$terms = wp_get_post_terms( $post_id, 'quote_author' );
		$this->assertEquals( 'Malicious Author', $terms[0]->name );

		// Check that malicious URL was sanitized (esc_url_raw removes javascript:)
		$author_url = get_term_meta( $terms[0]->term_id, 'author_url', true );
		$this->assertNotEquals( "javascript:alert('xss')", $author_url );
		// esc_url_raw should return empty string or sanitized version
		$this->assertStringNotContainsString( 'javascript:', $author_url );
	}

	/**
	 * Test multiple quotes by same author with URL
	 */
	public function test_multiple_quotes_same_author_with_url() {
		global $wpdb;

		// Insert first quote with author link
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'First quote',
				'author'   => '<a href="https://example.com/author">Same Author</a>',
				'source'   => 'Test source 1',
				'category' => 'Test',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);
		$quote_id1 = $wpdb->insert_id;

		// Insert second quote with same author
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Second quote',
				'author'   => '<a href="https://example.com/author">Same Author</a>',
				'source'   => 'Test source 2',
				'category' => 'Test',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);
		$quote_id2 = $wpdb->insert_id;

		// Migrate both quotes
		$post_id1 = $this->migrator->migrate_single_quote( $quote_id1 );
		$post_id2 = $this->migrator->migrate_single_quote( $quote_id2 );

		$this->assertNotFalse( $post_id1 );
		$this->assertNotFalse( $post_id2 );

		// Check that both posts have the same author term
		$terms1 = wp_get_post_terms( $post_id1, 'quote_author' );
		$terms2 = wp_get_post_terms( $post_id2, 'quote_author' );
		$this->assertEquals( $terms1[0]->term_id, $terms2[0]->term_id );

		// Check that URL is saved only once
		$author_url = get_term_meta( $terms1[0]->term_id, 'author_url', true );
		$this->assertEquals( 'https://example.com/author', $author_url );
	}

	/**
	 * Test category terms do not get author URL processing
	 * Note: WordPress wp_insert_term() strips HTML tags from term names
	 */
	public function test_category_terms_not_processed_for_urls() {
		global $wpdb;

		// Insert legacy quote with category that looks like a link
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Test quote',
				'author'   => 'Test Author',
				'source'   => 'Test source',
				'category' => '<a href="http://example.com">Test Category</a>',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);

		$quote_id = $wpdb->insert_id;
		$post_id = $this->migrator->migrate_single_quote( $quote_id );

		$this->assertNotFalse( $post_id );

		// Check that category term name is clean (WordPress strips HTML from term names)
		$terms = wp_get_post_terms( $post_id, 'quote_category' );
		// WordPress wp_insert_term() sanitizes term names and strips HTML tags
		$this->assertEquals( 'Test Category', $terms[0]->name );

		// Verify no author_url meta was saved for category (author-only feature)
		$category_url = get_term_meta( $terms[0]->term_id, 'author_url', true );
		$this->assertEmpty( $category_url );
	}

	/**
	 * Test author URL with single quotes in href
	 */
	public function test_author_url_with_single_quotes() {
		global $wpdb;

		// Insert legacy quote with single-quoted href
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Test quote',
				'author'   => "<a href='https://example.com/author'>Test Author</a>",
				'source'   => 'Test source',
				'category' => 'Test',
				'visible'  => 'yes',
				'user'     => 'admin',
			)
		);

		$quote_id = $wpdb->insert_id;
		$post_id = $this->migrator->migrate_single_quote( $quote_id );

		$this->assertNotFalse( $post_id );

		// Check URL was extracted correctly with single quotes
		$terms = wp_get_post_terms( $post_id, 'quote_author' );
		$author_url = get_term_meta( $terms[0]->term_id, 'author_url', true );
		$this->assertEquals( 'https://example.com/author', $author_url );
	}
}
