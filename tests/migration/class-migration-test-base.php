<?php
/**
 * Base class for migration tests
 *
 * Provides common functionality for testing quote migration:
 * - Old table creation and cleanup
 * - Test quote insertion
 * - Migration state cleanup (options and transients)
 * - CPT post cleanup
 *
 * @package XVRandomQuotes\Tests
 */

use XVRandomQuotes\Migration\QuoteMigrator;

/**
 * Base test class for migration tests
 */
abstract class Migration_Test_Base extends WP_UnitTestCase {

	/**
	 * QuoteMigrator instance
	 *
	 * @var QuoteMigrator
	 */
	protected $migrator;

	/**
	 * Old table name
	 *
	 * @var string
	 */
	protected $old_table;

	/**
	 * Set up test environment
	 *
	 * Creates old table, initializes migrator, cleans migration state.
	 * Child classes can override and call parent::setUp() first.
	 */
	public function setUp(): void {
		parent::setUp();

		global $wpdb;
		$this->migrator = new QuoteMigrator();
		$this->old_table = $wpdb->prefix . 'stray_quotes';

		// Create old quotes table
		$this->create_old_table();

		// Clean up migration state
		$this->clean_migration_state();
	}

	/**
	 * Clean up test environment
	 *
	 * Drops old table, cleans migration state, deletes created posts.
	 * Child classes can override and call parent::tearDown() last.
	 */
	public function tearDown(): void {
		global $wpdb;

		// Drop old table
		$wpdb->query( "DROP TABLE IF EXISTS {$this->old_table}" );

		// Clean up migration state
		$this->clean_migration_state();

		// Delete all created quotes
		$this->delete_all_quotes();

		parent::tearDown();
	}

	/**
	 * Create old quotes table structure
	 *
	 * Creates the legacy wp_stray_quotes table with standard schema.
	 */
	protected function create_old_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$this->old_table} (
			quoteID int(11) NOT NULL AUTO_INCREMENT,
			quote text NOT NULL,
			author varchar(255) DEFAULT NULL,
			source varchar(255) DEFAULT NULL,
			category varchar(255) DEFAULT NULL,
			visible enum('yes','no') DEFAULT 'yes',
			user varchar(60) DEFAULT NULL,
			PRIMARY KEY (quoteID)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Insert a single quote into old table
	 *
	 * Convenience wrapper around insert_old_quotes() for single quote insertion.
	 *
	 * @param array $data Optional. Quote data to override defaults.
	 * @return int Quote ID.
	 */
	protected function insert_old_quote( $data = array() ) {
		$ids = $this->insert_old_quotes( 1, $data );
		return $ids[0];
	}

	/**
	 * Insert multiple quotes into old table
	 *
	 * @param int   $count Number of quotes to insert.
	 * @param array $base_data Optional. Base data for all quotes (will be augmented with index).
	 * @return array Array of inserted quote IDs.
	 */
	protected function insert_old_quotes( $count, $base_data = array() ) {
		global $wpdb;
		$ids = array();

		for ( $i = 1; $i <= $count; $i++ ) {
			$data = wp_parse_args(
				$base_data,
				array(
					'quote'    => "Test quote {$i}",
					'author'   => "Author {$i}",
					'source'   => "Source {$i}",
					'category' => 'test',
					'visible'  => 'yes',
					'user'     => 'admin',
				)
			);

			$wpdb->insert(
				$this->old_table,
				$data,
				array( '%s', '%s', '%s', '%s', '%s', '%s' )
			);

			$ids[] = $wpdb->insert_id;
		}

		return $ids;
	}

	/**
	 * Clean migration state (options and transients)
	 */
	protected function clean_migration_state() {
		delete_option( 'xv_quotes_migrated_v2' );
		delete_option( 'xv_migration_pending' );
		delete_option( 'xv_migration_total' );
		delete_transient( 'xv_migration_progress' );
		delete_transient( 'xv_migration_total' );
		delete_transient( 'xv_migration_offset' );
	}

	/**
	 * Delete all xv_quote posts
	 */
	protected function delete_all_quotes() {
		$posts = get_posts(
			array(
				'post_type'   => 'xv_quote',
				'numberposts' => -1,
				'post_status' => 'any',
			)
		);

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	/**
	 * Get migrated post by legacy ID
	 *
	 * @param int $legacy_id Legacy quote ID.
	 * @return WP_Post|null Post object or null if not found.
	 */
	protected function get_migrated_post( $legacy_id ) {
		$posts = get_posts(
			array(
				'post_type'   => 'xv_quote',
				'meta_key'    => '_quote_legacy_id',
				'meta_value'  => $legacy_id,
				'numberposts' => 1,
			)
		);

		return ! empty( $posts ) ? $posts[0] : null;
	}
}
