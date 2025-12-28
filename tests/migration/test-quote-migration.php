<?php
/**
 * Tests for Quote Migration - Single Quote
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/class-migration-test-base.php';

/**
 * Test single quote migration from old database table to CPT
 */
class Test_Quote_Migration extends Migration_Test_Base {

	/**
	 * Test user ID
	 *
	 * @var int
	 */
	private $test_user_id;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Create a test user
		$this->test_user_id = $this->factory->user->create(
			array(
				'user_login' => 'testuser',
				'user_nicename' => 'testuser',
			)
		);

		// Set current user
		wp_set_current_user( $this->test_user_id );

		// Initialize v2.0 architecture
		do_action( 'init' );
	}

	/**
	 * Test 1: Basic quote migration creates a post
	 */
	public function test_migrate_basic_quote_creates_post() {
		$old_id = $this->insert_old_quote( array() );

		$post_id = $this->migrator->migrate_single_quote( $old_id );

		$this->assertIsInt( $post_id );
		$this->assertGreaterThan( 0, $post_id );

		$post = get_post( $post_id );
		$this->assertNotNull( $post );
		$this->assertEquals( 'xv_quote', $post->post_type );
	}

	/**
	 * Test 2: Quote text migrates to post_content
	 */
	public function test_migrate_quote_text_to_post_content() {
		$quote_text = 'This is a test quote';
		$old_id = $this->insert_old_quote( array( 'quote' => $quote_text ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$post = get_post( $post_id );
		$this->assertEquals( $quote_text, $post->post_content );
		// post_title should be auto-generated from content
		$this->assertNotEmpty( $post->post_title );
	}

	/**
	 * Test 3: visible='yes' migrates to post_status='publish'
	 */
	public function test_migrate_visible_yes_to_publish_status() {
		$old_id = $this->insert_old_quote( array( 'visible' => 'yes' ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$post = get_post( $post_id );
		$this->assertEquals( 'publish', $post->post_status );
	}

	/**
	 * Test 4: visible='no' migrates to post_status='draft'
	 */
	public function test_migrate_visible_no_to_draft_status() {
		$old_id = $this->insert_old_quote( array( 'visible' => 'no' ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$post = get_post( $post_id );
		$this->assertEquals( 'draft', $post->post_status );
	}

	/**
	 * Test 5: Author migrates to taxonomy term
	 */
	public function test_migrate_author_to_taxonomy_term() {
		$old_id = $this->insert_old_quote( array( 'author' => 'Mark Twain' ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$terms = wp_get_post_terms( $post_id, 'quote_author' );
		$this->assertCount( 1, $terms );
		$this->assertEquals( 'Mark Twain', $terms[0]->name );
	}

	/**
	 * Test 6: Category migrates to taxonomy term
	 */
	public function test_migrate_category_to_taxonomy_term() {
		$old_id = $this->insert_old_quote( array( 'category' => 'Philosophy' ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$terms = wp_get_post_terms( $post_id, 'quote_category' );
		$this->assertCount( 1, $terms );
		$this->assertEquals( 'Philosophy', $terms[0]->name );
	}

	/**
	 * Test 7: Default category migrates properly
	 */
	public function test_migrate_default_category_properly() {
		$old_id = $this->insert_old_quote( array( 'category' => 'default' ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$terms = wp_get_post_terms( $post_id, 'quote_category' );
		$this->assertCount( 1, $terms );
		$this->assertEquals( 'default', $terms[0]->name );
	}

	/**
	 * Test 8: Source migrates to post meta
	 */
	public function test_migrate_source_to_post_meta() {
		$source = "Roughin' it";
		$old_id = $this->insert_old_quote( array( 'source' => $source ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$saved_source = get_post_meta( $post_id, '_quote_source', true );
		$this->assertEquals( $source, $saved_source );
	}

	/**
	 * Test 9: HTML source is properly sanitized
	 */
	public function test_migrate_html_source_with_sanitization() {
		$source = '<a href="http://example.com">Link</a>';
		$old_id = $this->insert_old_quote( array( 'source' => $source ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$saved_source = get_post_meta( $post_id, '_quote_source', true );
		// wp_kses_post should preserve safe HTML
		$this->assertStringContainsString( '<a', $saved_source );
		$this->assertStringContainsString( 'href=', $saved_source );
	}

	/**
	 * Test 10: Legacy ID is stored in post meta
	 */
	public function test_migrate_legacy_id_stored() {
		$old_id = $this->insert_old_quote( array() );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$legacy_id = get_post_meta( $post_id, '_quote_legacy_id', true );
		$this->assertEquals( $old_id, (int) $legacy_id );
	}

	/**
	 * Test 11: User nicename converts to author ID
	 */
	public function test_migrate_user_nicename_to_author_id() {
		$old_id = $this->insert_old_quote( array( 'user' => 'testuser' ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$post = get_post( $post_id );
		$this->assertEquals( $this->test_user_id, $post->post_author );
	}

	/**
	 * Test 12: Nonexistent user falls back to current user
	 */
	public function test_migrate_user_nicename_fallback_to_current_user() {
		$old_id = $this->insert_old_quote( array( 'user' => 'nonexistent' ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$post = get_post( $post_id );
		$this->assertEquals( get_current_user_id(), $post->post_author );
	}

	/**
	 * Test 13: Empty author field handled correctly
	 */
	public function test_migrate_empty_author_field() {
		$old_id = $this->insert_old_quote( array( 'author' => '' ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$terms = wp_get_post_terms( $post_id, 'quote_author' );
		$this->assertCount( 0, $terms );
	}

	/**
	 * Test 14: Empty source field handled correctly
	 */
	public function test_migrate_empty_source_field() {
		$old_id = $this->insert_old_quote( array( 'source' => '' ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$saved_source = get_post_meta( $post_id, '_quote_source', true );
		$this->assertEquals( '', $saved_source );
	}

	/**
	 * Test 15: Special characters are preserved in post_content
	 */
	public function test_migration_preserves_special_characters() {
		$quote_text = 'Quote with "quotes" and \'apostrophes\' & ampersands';
		$old_id = $this->insert_old_quote( array( 'quote' => $quote_text ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$post = get_post( $post_id );
		// post_content preserves special characters
		$this->assertStringContainsString( 'quotes', $post->post_content );
		$this->assertStringContainsString( 'apostrophes', $post->post_content );
		$this->assertStringContainsString( '&', $post->post_content );
	}

	/**
	 * Test 16: Multiline quotes are preserved
	 */
	public function test_migrate_multiline_quote() {
		$quote_text = "Line one\nLine two\nLine three";
		$old_id = $this->insert_old_quote( array( 'quote' => $quote_text ) );

		$migrator = $this->migrator;
		$post_id = $migrator->migrate_single_quote( $old_id );

		$post = get_post( $post_id );
		$this->assertEquals( $quote_text, $post->post_content );
	}

	/**
	 * Test 17: Returns false for invalid quote ID
	 */
	public function test_migrate_invalid_quote_id_returns_false() {
		$migrator = $this->migrator;
		$result = $migrator->migrate_single_quote( 99999 );

		$this->assertFalse( $result );
	}

	/**
	 * Test 18: Existing author term is reused
	 */
	public function test_migrate_reuses_existing_author_term() {
		// Migrate two quotes with same author
		$old_id_1 = $this->insert_old_quote( array( 'author' => 'Mark Twain' ) );
		$old_id_2 = $this->insert_old_quote( array( 'author' => 'Mark Twain' ) );

		$migrator = $this->migrator;
		$post_id_1 = $migrator->migrate_single_quote( $old_id_1 );
		
		// Verify first migration created the term
		$terms_after_first = wp_get_post_terms( $post_id_1, 'quote_author' );
		$this->assertCount( 1, $terms_after_first );
		
		// Migrate second quote - should reuse the term
		$post_id_2 = $migrator->migrate_single_quote( $old_id_2 );

		// Both should use the same term
		$terms_1 = wp_get_post_terms( $post_id_1, 'quote_author' );
		$terms_2 = wp_get_post_terms( $post_id_2, 'quote_author' );

		$this->assertCount( 1, $terms_1 );
		$this->assertCount( 1, $terms_2 );
		$this->assertEquals( 'Mark Twain', $terms_1[0]->name );
		$this->assertEquals( 'Mark Twain', $terms_2[0]->name );
		$this->assertEquals( $terms_1[0]->term_id, $terms_2[0]->term_id, 'Both quotes should use the same term ID' );
	}

	/**
	 * Test 19: Existing category term is reused
	 */
	public function test_migrate_reuses_existing_category_term() {
		// Create category term first
		$existing_term = wp_insert_term( 'Philosophy', 'quote_category' );

		// Migrate two quotes with same category
		$old_id_1 = $this->insert_old_quote( array( 'category' => 'Philosophy' ) );
		$old_id_2 = $this->insert_old_quote( array( 'category' => 'Philosophy' ) );

		$migrator = $this->migrator;
		$post_id_1 = $migrator->migrate_single_quote( $old_id_1 );
		$post_id_2 = $migrator->migrate_single_quote( $old_id_2 );

		// Both should use the same term
		$terms_1 = wp_get_post_terms( $post_id_1, 'quote_category' );
		$terms_2 = wp_get_post_terms( $post_id_2, 'quote_category' );

		$this->assertEquals( $terms_1[0]->term_id, $terms_2[0]->term_id );
		$this->assertEquals( $existing_term['term_id'], $terms_1[0]->term_id );
	}
}
