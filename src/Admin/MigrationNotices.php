<?php
/**
 * Migration Admin Notices
 *
 * Handles admin notices for the migration system, including pending migration
 * alerts, progress tracking, success messages, and error display.
 *
 * @package XVRandomQuotes\Admin
 */

namespace XVRandomQuotes\Admin;

/**
 * Class MigrationNotices
 *
 * Displays admin notices for migration status and progress.
 */
class MigrationNotices {

	/**
	 * Initialize migration notices
	 *
	 * Hooks into admin_notices to display migration-related messages.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'display_migration_notices' ) );
	}

	/**
	 * Display migration notices based on current state
	 *
	 * Shows different notices depending on:
	 * - Migration pending (with start button)
	 * - Migration in progress (with progress bar)
	 * - Migration complete (success message)
	 * - Migration error (error message)
	 */
	public static function display_migration_notices() {
		// Check for error messages first
		$error = get_transient( 'xv_migration_error' );
		if ( $error ) {
			self::display_error_notice( $error );
			return;
		}

		// Check for success messages
		$success_count = get_transient( 'xv_migration_success' );
		if ( $success_count ) {
			self::display_success_notice( $success_count );
			return;
		}

		// Check if migration is pending or in progress
		$pending = get_option( 'xv_migration_pending', false );
		$migrated = get_option( 'xv_quotes_migrated_v2', false );

		// Don't show notice if already migrated
		if ( $migrated && ! $pending ) {
			return;
		}

		// Show pending/progress notice
		if ( $pending ) {
			self::display_pending_notice();
		}
	}

	/**
	 * Display pending migration notice with start button
	 */
	private static function display_pending_notice() {
		$total = get_option( 'xv_migration_total', 0 );
		$progress = get_transient( 'xv_migration_progress' );

		// If migration is in progress, show progress bar
		if ( $progress !== false ) {
			self::display_progress_notice( $total, $progress );
			return;
		}

		// Show initial pending notice with start button
		$nonce = wp_create_nonce( 'xv_migration_nonce' );
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'XV Random Quotes Migration Required', 'stray-quotes' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					/* translators: %d: number of quotes to migrate */
					esc_html__( 'XV Random Quotes needs to migrate %d quotes to the new system.', 'stray-quotes' ),
					(int) $total
				);
				?>
			</p>
			<p>
				<button type="button" class="button button-primary xv-start-migration" data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Start Migration', 'stray-quotes' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * Display migration progress notice with progress bar
	 *
	 * @param int $total Total number of quotes to migrate.
	 * @param int $progress Number of quotes migrated so far.
	 */
	private static function display_progress_notice( $total, $progress ) {
		$percentage = $total > 0 ? round( ( $progress / $total ) * 100 ) : 0;
		$remaining = $total - $progress;
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Migration in Progress', 'stray-quotes' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					/* translators: 1: migrated count, 2: total count, 3: percentage */
					esc_html__( 'Migrated %1$d of %2$d quotes (%3$d%%)', 'stray-quotes' ),
					(int) $progress,
					(int) $total,
					(int) $percentage
				);
				?>
			</p>
			<div class="xv-migration-progress-bar" style="background: #ddd; height: 20px; border-radius: 3px; overflow: hidden;">
				<div class="xv-migration-progress-fill" style="background: #2271b1; height: 100%; width: <?php echo esc_attr( $percentage ); ?>%; transition: width 0.3s;"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display success notice after migration completes
	 *
	 * @param int $count Number of quotes migrated.
	 */
	private static function display_success_notice( $count ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Migration Complete!', 'stray-quotes' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					/* translators: %d: number of quotes migrated */
					esc_html__( 'Successfully migrated %d quotes to the new system.', 'stray-quotes' ),
					(int) $count
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Display error notice when migration fails
	 *
	 * @param string $error Error message to display.
	 */
	private static function display_error_notice( $error ) {
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Migration Error', 'stray-quotes' ); ?></strong>
			</p>
			<p>
				<?php echo esc_html( $error ); ?>
			</p>
		</div>
		<?php
	}
}
