<?php
/**
 * Tests for Activation Hook Migration Logic
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/class-migration-test-base.php';

/**
 * Test activation hook migration decision logic
 */
class Test_Activation_Hook extends Migration_Test_Base {

	/**
	 * Helper method to simulate the deferred migration flow
	 * 
	 * Simulates: activation sets flag → init hook triggers migration
	 */
	private function run_deferred_migration() {
		// Step 1: Run activation hook (sets flags)
		xv_quotes_activation_migration();

		// Step 2: Simulate init hook checking for migration flag
		if ( get_option( 'xv_quotes_needs_migration' ) ) {
			delete_option( 'xv_quotes_needs_migration' );
			\XVRandomQuotes\Migration\QuoteMigrator::run_migration();
		}
	}

	/**
	 * Test that hook skips migration if already completed
	 */
	public function test_skips_if_already_migrated() {
		// Set migration completed flag
		update_option('xv_quotes_migrated_v2', true);

		// Create table with quotes
		$this->create_old_table();
		$this->insert_old_quotes(5);

		// Run deferred migration flow
		$this->run_deferred_migration();

		// Verify no posts were created
		$posts = get_posts(array(
			'post_type' => 'xv_quote',
			'numberposts' => -1,
		));
		$this->assertCount(0, $posts, 'Should not migrate if already completed');

		// Verify flag is still set
		$this->assertTrue(get_option('xv_quotes_migrated_v2'));
	}

	/**
	 * Test that hook sets flag when no old table exists
	 */
	public function test_sets_flag_when_no_old_table() {
		// Ensure table doesn't exist
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$this->old_table}");

		// Run deferred migration flow
		$this->run_deferred_migration();

		// Verify migration flag is set
		$this->assertTrue(get_option('xv_quotes_migrated_v2'), 'Should set flag when no old table exists');

		// Verify no pending migration
		$this->assertFalse(get_option('xv_migration_pending'), 'Should not set pending flag');
	}

	/**
	 * Test that hook sets flag when table is empty
	 */
	public function test_sets_flag_when_table_empty() {
		// Create empty table
		$this->create_old_table();

		// Run deferred migration flow
		$this->run_deferred_migration();

		// Verify migration flag is set
		$this->assertTrue(get_option('xv_quotes_migrated_v2'), 'Should set flag when table is empty');

		// Verify no pending migration
		$this->assertFalse(get_option('xv_migration_pending'), 'Should not set pending flag');
	}

	/**
	 * Test that hook sets pending flag for any database with quotes
	 */
	public function test_sets_pending_flag_for_any_quotes() {
		// Create table with 50 quotes
		$this->create_old_table();
		$old_ids = $this->insert_old_quotes(50);

		// Run deferred migration flow
		$this->run_deferred_migration();

		// Verify NO quotes were auto-migrated
		$posts = get_posts(array(
			'post_type' => 'xv_quote',
			'numberposts' => -1,
		));
		$this->assertCount(0, $posts, 'Should not auto-migrate any database');

		// Verify pending flag is set
		$this->assertTrue(get_option('xv_migration_pending'), 'Should set pending flag for any quotes');

		// Verify total is stored
		$this->assertEquals(50, get_option('xv_migration_total'), 'Should store total quote count');

		// Verify completed flag is NOT set
		$this->assertFalse(get_option('xv_quotes_migrated_v2'), 'Should not set completed flag yet');
	}

	/**
	 * Test that hook sets pending flag for large database
	 */
	public function test_sets_pending_flag_for_large_database() {
		// Create table with 501 quotes (just over threshold)
		$this->create_old_table();
		$old_ids = $this->insert_old_quotes(501);

		// Run deferred migration flow
		$this->run_deferred_migration();

		// Verify NO quotes were migrated
		$posts = get_posts(array(
			'post_type' => 'xv_quote',
			'numberposts' => -1,
		));
		$this->assertCount(0, $posts, 'Should not auto-migrate large database');

		// Verify pending flag is set
		$this->assertTrue(get_option('xv_migration_pending'), 'Should set pending flag for large database');

		// Verify total is stored
		$this->assertEquals(501, get_option('xv_migration_total'), 'Should store total quote count');

		// Verify completed flag is NOT set
		$this->assertFalse(get_option('xv_quotes_migrated_v2'), 'Should not set completed flag yet');
	}
}
