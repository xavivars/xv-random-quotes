<?php
/**
 * Test AJAX batch migration for large databases
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/class-migration-test-base.php';

/**
 * Test AJAX batch migration functionality
 */
class Test_AJAX_Batch_Migration extends Migration_Test_Base {

	/**
	 * Test 1: Migrate batch with default size (100)
	 */
	public function test_migrate_batch_default_size() {
		// Insert 350 quotes
		$this->insert_old_quotes( 350 );

		// Migrate first batch
		$result = $this->migrator->migrate_batch();

		$this->assertIsArray( $result );
		$this->assertEquals( 100, $result['migrated'] );
		$this->assertEquals( 350, $result['total'] );
		$this->assertEquals( 100, $result['offset'] );
		$this->assertFalse( $result['complete'] );
	}

	/**
	 * Test 2: Migrate batch with custom size
	 */
	public function test_migrate_batch_custom_size() {
		$this->insert_old_quotes( 75 );

		// Migrate with batch size of 50
		$result = $this->migrator->migrate_batch( 50 );

		$this->assertEquals( 50, $result['migrated'] );
		$this->assertEquals( 75, $result['total'] );
		$this->assertEquals( 50, $result['offset'] );
		$this->assertFalse( $result['complete'] );
	}

	/**
	 * Test 3: Multiple batches complete full migration
	 */
	public function test_multiple_batches_complete_migration() {
		$this->insert_old_quotes( 250 );

		// First batch
		$result1 = $this->migrator->migrate_batch( 100 );
		$this->assertEquals( 100, $result1['migrated'] );
		$this->assertFalse( $result1['complete'] );

		// Second batch
		$result2 = $this->migrator->migrate_batch( 100 );
		$this->assertEquals( 100, $result2['migrated'] );
		$this->assertEquals( 200, $result2['offset'] );
		$this->assertFalse( $result2['complete'] );

		// Third batch (final)
		$result3 = $this->migrator->migrate_batch( 100 );
		$this->assertEquals( 50, $result3['migrated'] );
		$this->assertEquals( 250, $result3['offset'] );
		$this->assertTrue( $result3['complete'] );

		// Verify all migrated
		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );
		$this->assertCount( 250, $posts );
	}

	/**
	 * Test 4: Progress tracking with transients
	 */
	public function test_progress_tracking_transients() {
		$this->insert_old_quotes( 150 );

		// First batch
		$result1 = $this->migrator->migrate_batch( 100 );

		// Check transients after first batch
		$progress = get_transient( 'xv_migration_progress' );
		$total = get_transient( 'xv_migration_total' );
		$offset = get_transient( 'xv_migration_offset' );

		$this->assertEquals( 100, $progress );
		$this->assertEquals( 150, $total );
		$this->assertEquals( 100, $offset );
		$this->assertFalse( $result1['complete'] );

		// Second batch (completes migration)
		$result2 = $this->migrator->migrate_batch( 100 );

		// After completion, transients should be cleaned up
		$this->assertTrue( $result2['complete'] );
		$this->assertFalse( get_transient( 'xv_migration_progress' ) );
		$this->assertFalse( get_transient( 'xv_migration_offset' ) );
	}

	/**
	 * Test 5: Resumability after interruption
	 */
	public function test_resumability_after_interruption() {
		$this->insert_old_quotes( 200 );

		// First batch
		$this->migrator->migrate_batch( 100 );

		// Simulate interruption by creating new migrator instance
		$new_migrator = new \XVRandomQuotes\Migration\QuoteMigrator();

		// Resume migration
		$result = $new_migrator->migrate_batch( 100 );

		// Should continue from offset 100
		$this->assertEquals( 100, $result['migrated'] );
		$this->assertEquals( 200, $result['offset'] );
		$this->assertTrue( $result['complete'] );
	}

	/**
	 * Test 6: Completion detection and cleanup
	 */
	public function test_completion_detection_and_cleanup() {
		$this->insert_old_quotes( 50 );

		// Single batch completes migration
		$result = $this->migrator->migrate_batch( 100 );

		$this->assertEquals( 50, $result['migrated'] );
		$this->assertTrue( $result['complete'] );

		// Migration flag should be set
		$this->assertTrue( (bool) get_option( 'xv_quotes_migrated_v2' ) );

		// Transients should be cleaned up
		$this->assertFalse( get_transient( 'xv_migration_progress' ) );
		$this->assertFalse( get_transient( 'xv_migration_total' ) );
		$this->assertFalse( get_transient( 'xv_migration_offset' ) );
	}

	/**
	 * Test 7: Empty database handling
	 */
	public function test_empty_database_batch_migration() {
		// Don't insert any quotes

		$result = $this->migrator->migrate_batch();

		$this->assertIsArray( $result );
		$this->assertEquals( 0, $result['migrated'] );
		$this->assertEquals( 0, $result['total'] );
		$this->assertTrue( $result['complete'] );
		$this->assertTrue( (bool) get_option( 'xv_quotes_migrated_v2' ) );
	}

	/**
	 * Test 8: Already migrated flag prevents re-migration
	 */
	public function test_already_migrated_prevents_batch_migration() {
		$this->insert_old_quotes( 100 );

		// Set migration flag
		update_option( 'xv_quotes_migrated_v2', true );

		$result = $this->migrator->migrate_batch();

		$this->assertIsArray( $result );
		$this->assertEquals( 0, $result['migrated'] );
		$this->assertTrue( $result['complete'] );

		// No posts should be created
		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );
		$this->assertCount( 0, $posts );
	}

	/**
	 * Test 9: Progress percentage calculation
	 */
	public function test_progress_percentage() {
		$this->insert_old_quotes( 200 );

		// First batch (50%)
		$result1 = $this->migrator->migrate_batch( 100 );
		$this->assertEquals( 50, $result1['percentage'] );

		// Second batch (100%)
		$result2 = $this->migrator->migrate_batch( 100 );
		$this->assertEquals( 100, $result2['percentage'] );
	}

	/**
	 * Test 10: Batch respects existing migrated quotes (duplicate detection)
	 */
	public function test_batch_respects_duplicate_detection() {
		$this->insert_old_quotes( 150 );

		// Manually migrate first 50 quotes
		for ( $i = 1; $i <= 50; $i++ ) {
			$this->migrator->migrate_single_quote( $i );
		}

		// Verify 50 posts exist
		$posts_before = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );
		$this->assertCount( 50, $posts_before );

		// Now run batch migration - will process quotes 1-100 from database
		// But quotes 1-50 already exist (duplicate detection returns existing IDs)
		$result = $this->migrator->migrate_batch( 100 );

		// Should process 100 quotes (even though 50 are duplicates)
		$this->assertEquals( 100, $result['migrated'] );
		
		// Total posts should now be 100 (50 pre-existing + 50 new from batch)
		$posts_after = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );
		$this->assertCount( 100, $posts_after );

		// Run second batch to get remaining 50
		$result2 = $this->migrator->migrate_batch( 100 );
		$this->assertEquals( 50, $result2['migrated'] );

		// Now all 150 should exist
		$posts_final = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );
		$this->assertCount( 150, $posts_final );
	}

	/**
	 * Test 11: Batch size of 1 for stress testing
	 */
	public function test_batch_size_one() {
		$this->insert_old_quotes( 5 );

		$completed = false;
		$iterations = 0;
		$max_iterations = 10; // Safety limit

		while ( ! $completed && $iterations < $max_iterations ) {
			$result = $this->migrator->migrate_batch( 1 );
			$completed = $result['complete'];
			$iterations++;
		}

		$this->assertTrue( $completed );
		$this->assertEquals( 5, $iterations );

		$posts = get_posts( array(
			'post_type'      => 'xv_quote',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );
		$this->assertCount( 5, $posts );
	}

	/**
	 * Test 12: Reset transients on fresh start
	 */
	public function test_reset_transients_on_fresh_start() {
		$this->insert_old_quotes( 100 );

		// Set stale transients
		set_transient( 'xv_migration_progress', 999, HOUR_IN_SECONDS );
		set_transient( 'xv_migration_offset', 999, HOUR_IN_SECONDS );

		// First batch should reset and start from 0
		$result = $this->migrator->migrate_batch( 50 );

		$this->assertEquals( 50, $result['migrated'] );
		$this->assertEquals( 50, $result['offset'] );
		$this->assertEquals( 50, get_transient( 'xv_migration_progress' ) );
	}
}
