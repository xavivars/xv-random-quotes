<?php
/**
 * Tests for Migration Admin UI
 *
 * Tests admin notices, progress bar, success/error messages, and migration button.
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/class-migration-test-base.php';

/**
 * Test admin migration UI functionality
 */
class Test_Admin_Migration_UI extends Migration_Test_Base {

	/**
	 * Test 1: Admin notice appears when migration is pending
	 */
	public function test_admin_notice_appears_when_migration_pending() {
		// Set up pending migration state
		update_option( 'xv_migration_pending', true );
		update_option( 'xv_migration_total', 1000 );

		// Get admin notices
		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Verify notice contains migration message
		$this->assertStringContainsString( 'migrate', strtolower( $notices ), 'Notice should mention migration' );
		$this->assertStringContainsString( '1000', $notices, 'Notice should show total quote count' );
	}

	/**
	 * Test 2: No admin notice when migration not pending
	 */
	public function test_no_admin_notice_when_not_pending() {
		// Ensure no pending migration
		delete_option( 'xv_migration_pending' );

		// Get admin notices
		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Verify no migration notice
		$this->assertStringNotContainsString( 'migrate', strtolower( $notices ), 'Should not show notice when not pending' );
	}

	/**
	 * Test 3: No admin notice when already migrated
	 */
	public function test_no_admin_notice_when_already_migrated() {
		// Set migration completed
		update_option( 'xv_quotes_migrated_v2', true );
		update_option( 'xv_migration_pending', false );

		// Get admin notices
		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Verify no migration notice
		$this->assertStringNotContainsString( 'migrate', strtolower( $notices ), 'Should not show notice when already migrated' );
	}

	/**
	 * Test 4: Admin notice includes start migration button
	 */
	public function test_admin_notice_includes_migration_button() {
		// Set up pending migration
		update_option( 'xv_migration_pending', true );
		update_option( 'xv_migration_total', 500 );

		// Get admin notices
		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Verify button is present
		$this->assertStringContainsString( '<button', strtolower( $notices ), 'Notice should contain a button' );
		$this->assertStringContainsString( 'xv-start-migration', $notices, 'Button should have migration trigger class/id' );
	}

	/**
	 * Test 5: Progress bar is rendered during migration
	 */
	public function test_progress_bar_rendered_during_migration() {
		// Set up migration in progress
		update_option( 'xv_migration_pending', true );
		update_option( 'xv_migration_total', 1000 );
		set_transient( 'xv_migration_progress', 500, HOUR_IN_SECONDS );
		set_transient( 'xv_migration_offset', 500, HOUR_IN_SECONDS );

		// Get admin notices
		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Verify progress bar elements
		$this->assertStringContainsString( 'progress', strtolower( $notices ), 'Should contain progress indicator' );
		$this->assertStringContainsString( '50', $notices, 'Should show 50% progress (500/1000)' );
	}

	/**
	 * Test 6: Success message after migration completion
	 */
	public function test_success_message_after_migration_complete() {
		// Set up completed migration state
		update_option( 'xv_quotes_migrated_v2', true );
		delete_option( 'xv_migration_pending' );
		set_transient( 'xv_migration_success', 1000, MINUTE_IN_SECONDS );

		// Get admin notices
		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Verify success message
		$this->assertStringContainsString( 'success', strtolower( $notices ), 'Should show success notice' );
		$this->assertStringContainsString( '1000', $notices, 'Should show number of migrated quotes' );
	}

	/**
	 * Test 7: Error message display when migration fails
	 */
	public function test_error_message_on_migration_failure() {
		// Set up error state
		set_transient( 'xv_migration_error', 'Database connection failed', MINUTE_IN_SECONDS );

		// Get admin notices
		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Verify error message
		$this->assertStringContainsString( 'error', strtolower( $notices ), 'Should show error notice' );
		$this->assertStringContainsString( 'Database connection failed', $notices, 'Should show error message' );
	}

	/**
	 * Test 8: Admin notice only appears on admin pages
	 */
	public function test_admin_notice_only_on_admin_pages() {
		// This test verifies the notice hook is registered for admin_notices
		// not wp_footer or other front-end hooks

		update_option( 'xv_migration_pending', true );
		update_option( 'xv_migration_total', 500 );

		// The hook should already be registered via plugins_loaded in bootstrap
		// Check that admin_notices action has a callback (any callback is fine)
		$has_admin_notices = has_action( 'admin_notices' );
		$this->assertNotFalse( $has_admin_notices, 'Should register admin_notices hook' );

		// Verify notices are actually output when migration is pending
		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();
		
		$this->assertStringContainsString( 'migrate', strtolower( $notices ), 'Admin notices should output migration notice' );
	}

	/**
	 * Test 9: Migration button has proper nonce for security
	 */
	public function test_migration_button_includes_nonce() {
		update_option( 'xv_migration_pending', true );
		update_option( 'xv_migration_total', 500 );

		// Get admin notices
		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Verify nonce field is present
		$this->assertStringContainsString( 'nonce', strtolower( $notices ), 'Should include nonce for security' );
	}

	/**
	 * Test 10: Progress percentage is calculated correctly
	 */
	public function test_progress_percentage_calculation() {
		// Test various completion levels
		$test_cases = array(
			array( 'total' => 1000, 'progress' => 0, 'expected' => 0 ),
			array( 'total' => 1000, 'progress' => 250, 'expected' => 25 ),
			array( 'total' => 1000, 'progress' => 500, 'expected' => 50 ),
			array( 'total' => 1000, 'progress' => 750, 'expected' => 75 ),
			array( 'total' => 1000, 'progress' => 1000, 'expected' => 100 ),
		);

		foreach ( $test_cases as $case ) {
			update_option( 'xv_migration_pending', true );
			update_option( 'xv_migration_total', $case['total'] );
			set_transient( 'xv_migration_progress', $case['progress'], HOUR_IN_SECONDS );

			ob_start();
			do_action( 'admin_notices' );
			$notices = ob_get_clean();

			if ( $case['progress'] > 0 ) {
				$this->assertStringContainsString( (string) $case['expected'], $notices, "Should show {$case['expected']}% for {$case['progress']}/{$case['total']}" );
			}
		}
	}

	/**
	 * Test 11: Admin notice is dismissible for success messages
	 */
	public function test_success_notice_is_dismissible() {
		set_transient( 'xv_migration_success', 500, MINUTE_IN_SECONDS );

		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Verify dismissible class is present
		$this->assertStringContainsString( 'is-dismissible', $notices, 'Success notice should be dismissible' );
	}

	/**
	 * Test 12: Progress bar shows remaining quote count
	 */
	public function test_progress_bar_shows_remaining_count() {
		update_option( 'xv_migration_pending', true );
		update_option( 'xv_migration_total', 1000 );
		set_transient( 'xv_migration_progress', 600, HOUR_IN_SECONDS );

		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		// Should show either "600 of 1000" or "400 remaining"
		$has_progress_info = strpos( $notices, '600' ) !== false || strpos( $notices, '400' ) !== false;
		$this->assertTrue( $has_progress_info, 'Should show progress or remaining count' );
	}
}
