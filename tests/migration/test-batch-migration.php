<?php
/**
 * Test batch migration of all quotes
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/class-migration-test-base.php';

/**
 * Test batch migration functionality
 */
class Test_Batch_Migration extends Migration_Test_Base {

	/**
	 * Test 1: Migrate all quotes from small database
	 */
	public function test_migrate_all_quotes_small_database() {
		// Insert 10 test quotes
		$this->insert_old_quotes( 10 );

		// Migrate all
		$result = $this->migrator->migrate_all_quotes();

		$this->assertEquals( 10, $result );

		// Verify all 10 quotes were migrated
		$migrated_posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );

		$this->assertCount( 10, $migrated_posts );
	}

	/**
	 * Test 2: Migration sets completion flag
	 */
	public function test_migration_sets_completion_flag() {
		$this->insert_old_quotes( 5 );

		// Flag should not exist before migration
		$this->assertFalse( get_option( 'xv_quotes_migrated_v2' ) );

		$this->migrator->migrate_all_quotes();

		// Flag should be set after migration
		$this->assertTrue( (bool) get_option( 'xv_quotes_migrated_v2' ) );
	}

	/**
	 * Test 3: Empty database sets flag without creating posts
	 */
	public function test_empty_database_sets_flag() {
		// Don't insert any quotes

		$result = $this->migrator->migrate_all_quotes();

		$this->assertEquals( 0, $result );
		$this->assertTrue( (bool) get_option( 'xv_quotes_migrated_v2' ) );

		// No posts should be created
		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );

		$this->assertCount( 0, $posts );
	}

	/**
	 * Test 4: Old table doesn't exist - sets flag, no errors
	 */
	public function test_no_old_table_sets_flag() {
		global $wpdb;

		// Drop the old table
		$wpdb->query( "DROP TABLE IF EXISTS {$this->old_table}" );

		$result = $this->migrator->migrate_all_quotes();

		$this->assertEquals( 0, $result );
		$this->assertTrue( (bool) get_option( 'xv_quotes_migrated_v2' ) );
	}

	/**
	 * Test 5: Already migrated - doesn't duplicate
	 */
	public function test_already_migrated_flag_prevents_duplication() {
		$this->insert_old_quotes( 5 );

		// Set flag as if already migrated
		update_option( 'xv_quotes_migrated_v2', true );

		$this->migrator->migrate_all_quotes();

		// Should not create any posts because flag is set
		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );

		$this->assertCount( 0, $posts );
	}

	/**
	 * Test 6: All quote fields migrated correctly in batch
	 */
	public function test_batch_migration_preserves_all_fields() {
		global $wpdb;

		// Insert specific test data
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Batch test quote',
				'author'   => 'Einstein',
				'source'   => 'Theory of Relativity',
				'category' => 'Science',
				'visible'  => 'no',
				'user'     => 'admin',
			)
		);

		$this->migrator->migrate_all_quotes();

		// Find the migrated post
		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => 1,
			'post_status'    => 'any',
		) );

		$this->assertCount( 1, $posts );

		$post = $posts[0];
		$this->assertEquals( 'Batch test quote', $post->post_title );
		$this->assertEquals( 'draft', $post->post_status );

		// Check taxonomies
		$authors = wp_get_post_terms( $post->ID, 'quote_author' );
		$this->assertCount( 1, $authors );
		$this->assertEquals( 'Einstein', $authors[0]->name );

		$categories = wp_get_post_terms( $post->ID, 'quote_category' );
		$this->assertCount( 1, $categories );
		$this->assertEquals( 'Science', $categories[0]->name );

		// Check meta
		$this->assertEquals( 'Theory of Relativity', get_post_meta( $post->ID, '_quote_source', true ) );
	}

	/**
	 * Test 7: Batch migration returns count of migrated quotes
	 */
	public function test_migrate_all_returns_count() {
		$this->insert_old_quotes( 15 );

		$count = $this->migrator->migrate_all_quotes();

		$this->assertEquals( 15, $count );
	}

	/**
	 * Test 8: Partial failure doesn't prevent other migrations
	 */
	public function test_partial_failure_continues_migration() {
		global $wpdb;

		// Insert valid quote
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Valid quote 1',
				'author'   => 'Author 1',
				'visible'  => 'yes',
			)
		);

		// Insert quote with empty text (should fail)
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => '',
				'author'   => 'Author 2',
				'visible'  => 'yes',
			)
		);

		// Insert another valid quote
		$wpdb->insert(
			$this->old_table,
			array(
				'quote'    => 'Valid quote 3',
				'author'   => 'Author 3',
				'visible'  => 'yes',
			)
		);

		$count = $this->migrator->migrate_all_quotes();

		// Should migrate the 2 valid quotes
		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );

		$this->assertGreaterThanOrEqual( 2, count( $posts ) );
	}

	/**
	 * Test 9: Large batch preserves order
	 */
	public function test_migration_preserves_quote_order() {
		// Insert quotes with specific IDs
		$ids = $this->insert_old_quotes( 20 );

		$this->migrator->migrate_all_quotes();

		// Get all migrated posts ordered by legacy ID
		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'meta_key'       => '_quote_legacy_id',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		) );

		$this->assertCount( 20, $posts );

		// Verify order
		for ( $i = 0; $i < 20; $i++ ) {
			$legacy_id = get_post_meta( $posts[ $i ]->ID, '_quote_legacy_id', true );
			$this->assertEquals( $ids[ $i ], $legacy_id );
		}
	}

	/**
	 * Test 10: Re-running batch migration doesn't create duplicates
	 */
	public function test_rerunning_batch_migration_no_duplicates() {
		$this->insert_old_quotes( 5 );

		// First run
		$count1 = $this->migrator->migrate_all_quotes();
		$this->assertEquals( 5, $count1 );

		// Clear the flag to simulate re-running
		delete_option( 'xv_quotes_migrated_v2' );

		// Second run
		$count2 = $this->migrator->migrate_all_quotes();
		$this->assertEquals( 5, $count2 );

		// Should still only have 5 posts (no duplicates)
		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );

		$this->assertCount( 5, $posts );
	}
}
