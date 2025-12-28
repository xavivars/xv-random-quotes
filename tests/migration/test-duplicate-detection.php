<?php
/**
 * Test duplicate detection in quote migration
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/class-migration-test-base.php';

/**
 * Test duplicate detection during migration
 */
class Test_Duplicate_Detection extends Migration_Test_Base {

	/**
	 * Test 1: Migrating same quote twice returns existing post ID
	 */
	public function test_duplicate_migration_returns_existing_post_id() {
		$old_id = $this->insert_old_quote();

		// First migration
		$post_id_1 = $this->migrator->migrate_single_quote( $old_id );
		$this->assertIsInt( $post_id_1 );
		$this->assertGreaterThan( 0, $post_id_1 );

		// Second migration attempt should return same post ID
		$post_id_2 = $this->migrator->migrate_single_quote( $old_id );
		$this->assertEquals( $post_id_1, $post_id_2 );
	}

	/**
	 * Test 2: Duplicate migration does not create multiple posts
	 */
	public function test_duplicate_migration_does_not_create_duplicate_posts() {
		$old_id = $this->insert_old_quote();

		// First migration
		$this->migrator->migrate_single_quote( $old_id );

		// Count posts before second migration
		$posts_before = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );

		// Second migration attempt
		$this->migrator->migrate_single_quote( $old_id );

		// Count posts after second migration
		$posts_after = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );

		$this->assertCount( count( $posts_before ), $posts_after );
	}

	/**
	 * Test 3: Can find existing migrated quote by legacy ID
	 */
	public function test_can_find_existing_migrated_quote_by_legacy_id() {
		$old_id = $this->insert_old_quote( array( 'quote' => 'Original migration' ) );

		// Migrate the quote
		$post_id = $this->migrator->migrate_single_quote( $old_id );

		// Query for post with this legacy ID
		$query = new WP_Query( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => '_quote_legacy_id',
					'value' => $old_id,
					'type'  => 'NUMERIC',
				),
			),
		) );

		$this->assertTrue( $query->have_posts() );
		$this->assertEquals( $post_id, $query->posts[0]->ID );
	}

	/**
	 * Test 4: Duplicate detection works with multiple different quotes
	 */
	public function test_duplicate_detection_with_multiple_quotes() {
		// Insert three different quotes
		$old_id_1 = $this->insert_old_quote( array( 'quote' => 'Quote 1' ) );
		$old_id_2 = $this->insert_old_quote( array( 'quote' => 'Quote 2' ) );
		$old_id_3 = $this->insert_old_quote( array( 'quote' => 'Quote 3' ) );

		// First migration of all three
		$post_id_1a = $this->migrator->migrate_single_quote( $old_id_1 );
		$post_id_2a = $this->migrator->migrate_single_quote( $old_id_2 );
		$post_id_3a = $this->migrator->migrate_single_quote( $old_id_3 );

		// Second migration attempt
		$post_id_1b = $this->migrator->migrate_single_quote( $old_id_1 );
		$post_id_2b = $this->migrator->migrate_single_quote( $old_id_2 );
		$post_id_3b = $this->migrator->migrate_single_quote( $old_id_3 );

		// All should return the same IDs
		$this->assertEquals( $post_id_1a, $post_id_1b );
		$this->assertEquals( $post_id_2a, $post_id_2b );
		$this->assertEquals( $post_id_3a, $post_id_3b );

		// Total post count should be 3
		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );
		$this->assertCount( 3, $posts );
	}

	/**
	 * Test 5: Duplicate migration does not update existing post content
	 */
	public function test_duplicate_migration_does_not_update_post_content() {
		$old_id = $this->insert_old_quote( array( 'quote' => 'Original text' ) );

		// First migration
		$post_id = $this->migrator->migrate_single_quote( $old_id );
		$original_post = get_post( $post_id );

		// Manually update the post title
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => 'Manually updated text',
		) );

		// Attempt second migration
		$this->migrator->migrate_single_quote( $old_id );

		// Post title should still be manually updated (not reverted)
		$updated_post = get_post( $post_id );
		$this->assertEquals( 'Manually updated text', $updated_post->post_title );
	}

	/**
	 * Test 6: Duplicate detection works after post is trashed
	 */
	public function test_duplicate_detection_works_with_trashed_post() {
		$old_id = $this->insert_old_quote();

		// First migration
		$post_id = $this->migrator->migrate_single_quote( $old_id );

		// Trash the post
		wp_trash_post( $post_id );

		// Second migration attempt should still return same post ID
		$post_id_2 = $this->migrator->migrate_single_quote( $old_id );
		$this->assertEquals( $post_id, $post_id_2 );
	}

	/**
	 * Test 7: Legacy ID meta is preserved on duplicate migration
	 */
	public function test_legacy_id_preserved_on_duplicate_migration() {
		$old_id = $this->insert_old_quote();

		// First migration
		$post_id = $this->migrator->migrate_single_quote( $old_id );
		$legacy_id_1 = get_post_meta( $post_id, '_quote_legacy_id', true );

		// Second migration
		$this->migrator->migrate_single_quote( $old_id );
		$legacy_id_2 = get_post_meta( $post_id, '_quote_legacy_id', true );

		$this->assertEquals( $old_id, $legacy_id_1 );
		$this->assertEquals( $legacy_id_1, $legacy_id_2 );
	}

	/**
	 * Test 8: Duplicate detection fails gracefully with invalid legacy ID
	 */
	public function test_duplicate_detection_with_corrupted_meta() {
		$old_id = $this->insert_old_quote();

		// Create a post manually with corrupted legacy ID meta
		$corrupted_post_id = wp_insert_post( array(
			'post_type'   => 'xv_quote',
			'post_title'  => 'Corrupted',
			'post_status' => 'publish',
		) );
		update_post_meta( $corrupted_post_id, '_quote_legacy_id', 'not-a-number' );

		// Migration should still work and create a new post
		$post_id = $this->migrator->migrate_single_quote( $old_id );

		$this->assertIsInt( $post_id );
		$this->assertNotEquals( $corrupted_post_id, $post_id );
	}
}
