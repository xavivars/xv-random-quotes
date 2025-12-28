<?php
/**
 * Quote Migration Class
 *
 * Handles migration from old database table to Custom Post Types.
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Migration;

/**
 * Class QuoteMigrator
 *
 * Migrates quotes from wp_stray_quotes table to xv_quote CPT.
 */
class QuoteMigrator {

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	const POST_TYPE = 'xv_quote';

	/**
	 * Category taxonomy slug
	 *
	 * @var string
	 */
	const CATEGORY_TAXONOMY = 'quote_category';

	/**
	 * Author taxonomy slug
	 *
	 * @var string
	 */
	const AUTHOR_TAXONOMY = 'quote_author';

	/**
	 * Threshold for automatic vs. AJAX batch migration
	 *
	 * @var int
	 */
	const MIGRATION_THRESHOLD = 500;

	/**
	 * Global database object
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * Old table name
	 *
	 * @var string
	 */
	private $old_table;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->old_table = $this->wpdb->prefix . 'stray_quotes';
	}

	/**
	 * Run the migration process
	 *
	 * Determines whether to migrate immediately (small DB) or defer to AJAX (large DB).
	 * Called on first init after plugin activation via the xv_quotes_needs_migration flag.
	 *
	 * @return void
	 */
	public static function run_migration() {
		global $wpdb;

		// Skip if migration already completed
		if ( get_option( 'xv_quotes_migrated_v2', false ) ) {
			return;
		}

		// Quick check: count quotes to determine migration strategy
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_quotes = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}stray_quotes"
		);

		// If NULL (table doesn't exist) or 0, migrate_all_quotes() will handle it
		// and set the flag appropriately
		if ( $total_quotes === null || (int) $total_quotes <= self::MIGRATION_THRESHOLD ) {
			// Small database or no table: migrate immediately
			// migrate_all_quotes() -> migrate_batch() will set xv_quotes_migrated_v2 flag
			$migrator = new self();
			$migrator->migrate_all_quotes();
			return;
		}

		// Large database: set pending flag for AJAX batch migration
		update_option( 'xv_migration_pending', true );
		update_option( 'xv_migration_total', (int) $total_quotes );
	}

	/**
	 * Migrate a single quote from old table to CPT
	 *
	 * @param int $quote_id Old quote ID from stray_quotes table.
	 * @return int|false New post ID on success, false on failure.
	 */
	public function migrate_single_quote( $quote_id ) {
		// Check if already migrated
		$existing_post_id = $this->find_migrated_quote( $quote_id );
		if ( $existing_post_id ) {
			return $existing_post_id;
		}

		// Fetch quote from old table
		$quote_data = $this->get_old_quote( $quote_id );

		if ( ! $quote_data ) {
			return false;
		}

		// Get total count for date calculation
		$total_quotes = (int) $this->wpdb->get_var(
			"SELECT COUNT(*) FROM {$this->old_table}"
		);

		// Prepare post data
		$post_data = $this->prepare_post_data( $quote_data, $total_quotes );

		// Insert the post
		$post_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		// Assign taxonomies
		$this->assign_taxonomies( $post_id, $quote_data );

		// Save post meta
		$this->save_post_meta( $post_id, $quote_data, $quote_id );

		return $post_id;
	}

	/**
	 * Migrate all quotes from old table to CPT
	 *
	 * This is a convenience wrapper around migrate_batch() that processes
	 * all quotes in a single operation. Uses a very large batch size to
	 * migrate everything at once.
	 *
	 * @return int Number of quotes migrated.
	 */
	public function migrate_all_quotes() {
		// Use migrate_batch with unlimited batch size (PHP_INT_MAX)
		$result = $this->migrate_batch( PHP_INT_MAX );
		
		return $result['migrated'];
	}

	/**
	 * Migrate a batch of quotes for AJAX processing
	 *
	 * Supports resumable migration with progress tracking via transients.
	 *
	 * @param int $batch_size Number of quotes to migrate in this batch (default 100).
	 * @return array Migration results with progress information.
	 */
	public function migrate_batch( $batch_size = 100 ) {
		// Check if already migrated
		if ( get_option( 'xv_quotes_migrated_v2', false ) ) {
			return array(
				'migrated'   => 0,
				'total'      => 0,
				'offset'     => 0,
				'complete'   => true,
				'percentage' => 100,
			);
		}

		// Get total count (this also checks if table exists)
		$total_quotes = get_transient( 'xv_migration_total' );
		
		if ( false === $total_quotes ) {
			// First batch - get total from database
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$total_quotes = $this->wpdb->get_var(
				"SELECT COUNT(*) FROM {$this->old_table}"
			);

			// If query returned NULL, table doesn't exist
			if ( $total_quotes === null ) {
				// New installation, no migration needed
				update_option( 'xv_quotes_migrated_v2', true );
				return array(
					'migrated'   => 0,
					'total'      => 0,
					'offset'     => 0,
					'complete'   => true,
					'percentage' => 100,
				);
			}

			$total_quotes = (int) $total_quotes;
			set_transient( 'xv_migration_total', $total_quotes, HOUR_IN_SECONDS );

			// Reset other transients for fresh start
			delete_transient( 'xv_migration_progress' );
			delete_transient( 'xv_migration_offset' );
		}

		$total_quotes = (int) $total_quotes;

		if ( $total_quotes === 0 ) {
			// Empty table, mark as migrated
			update_option( 'xv_quotes_migrated_v2', true );
			$this->cleanup_migration_transients();
			
			return array(
				'migrated'   => 0,
				'total'      => 0,
				'offset'     => 0,
				'complete'   => true,
				'percentage' => 100,
			);
		}

		// Get current offset (resume point)
		$offset = (int) get_transient( 'xv_migration_offset' );
		if ( false === $offset ) {
			$offset = 0;
		}

		// Calculate how many quotes to migrate in this batch
		$remaining = $total_quotes - $offset;
		$to_migrate = min( $batch_size, $remaining );

		// Get quotes for this batch
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$old_quotes = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT quoteID FROM {$this->old_table} ORDER BY quoteID LIMIT %d OFFSET %d",
				$to_migrate,
				$offset
			)
		);

		$migrated_count = 0;

		// Migrate each quote in this batch
		foreach ( $old_quotes as $quote ) {
			$result = $this->migrate_single_quote( $quote->quoteID );
			if ( $result ) {
				$migrated_count++;
			}
		}

		// Update progress
		$new_offset = $offset + $migrated_count;
		$progress = $new_offset;

		set_transient( 'xv_migration_progress', $progress, HOUR_IN_SECONDS );
		set_transient( 'xv_migration_offset', $new_offset, HOUR_IN_SECONDS );

		// Calculate percentage
		$percentage = $total_quotes > 0 ? (int) round( ( $new_offset / $total_quotes ) * 100 ) : 100;

		// Check if complete
		$complete = $new_offset >= $total_quotes;

		if ( $complete ) {
			// Set migration flag and cleanup
			update_option( 'xv_quotes_migrated_v2', true );
			$this->cleanup_migration_transients();
		}

		return array(
			'migrated'   => $migrated_count,
			'total'      => $total_quotes,
			'offset'     => $new_offset,
			'complete'   => $complete,
			'percentage' => $percentage,
		);
	}

	/**
	 * Clean up migration transients
	 */
	private function cleanup_migration_transients() {
		delete_transient( 'xv_migration_progress' );
		delete_transient( 'xv_migration_total' );
		delete_transient( 'xv_migration_offset' );
	}

	/**
	 * Find an already migrated quote by legacy ID
	 *
	 * @param int $legacy_id Old quote ID from stray_quotes table.
	 * @return int|false Post ID if found, false otherwise.
	 */
	private function find_migrated_quote( $legacy_id ) {
		$query = new \WP_Query( array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'trash', 'future' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_quote_legacy_id',
					'value'   => $legacy_id,
					'type'    => 'NUMERIC',
					'compare' => '=',
				),
			),
		) );

		if ( $query->have_posts() ) {
			return $query->posts[0];
		}

		return false;
	}

	/**
	 * Get quote data from old table
	 *
	 * @param int $quote_id Quote ID.
	 * @return object|false Quote data or false if not found.
	 */
	private function get_old_quote( $quote_id ) {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->old_table} WHERE quoteID = %d LIMIT 1",
			$quote_id
		);

		return $this->wpdb->get_row( $sql );
	}

	/**
	 * Prepare post data from old quote
	 *
	 * @param object $quote_data Old quote data.
	 * @param int    $total_quotes Total number of quotes (for date calculation).
	 * @return array Post data for wp_insert_post.
	 */
	private function prepare_post_data( $quote_data, $total_quotes = 0 ) {
		// Map visible field to post_status
		$post_status = ( 'yes' === $quote_data->visible ) ? 'publish' : 'draft';

		// Convert user nicename to user ID
		$post_author = $this->get_user_id_from_nicename( $quote_data->user );

		// Create timestamps based on legacy ID to preserve original order
		// Start from (now - total_quotes) and add 1 second per quote ID
		// This keeps dates recent while preserving order
		$base_timestamp = time() - $total_quotes;
		$post_timestamp = $base_timestamp + $quote_data->quoteID;
		$post_date      = gmdate( 'Y-m-d H:i:s', $post_timestamp );
		$post_date_gmt  = gmdate( 'Y-m-d H:i:s', $post_timestamp );

		return array(
			'post_type'     => self::POST_TYPE,
			'post_content'  => $quote_data->quote,
			'post_title'    => wp_trim_words( $quote_data->quote, 10, '...' ),
			'post_status'   => $post_status,
			'post_author'   => $post_author,
			'post_date'     => $post_date,
			'post_date_gmt' => $post_date_gmt,
		);
	}

	/**
	 * Get user ID from nicename
	 *
	 * @param string $nicename User nicename.
	 * @return int User ID, falls back to current user if not found.
	 */
	private function get_user_id_from_nicename( $nicename ) {
		$user = get_user_by( 'slug', $nicename );

		if ( $user ) {
			return $user->ID;
		}

		// Fallback to current user
		return get_current_user_id();
	}

	/**
	 * Assign taxonomies to migrated post
	 *
	 * @param int    $post_id Post ID.
	 * @param object $quote_data Old quote data.
	 */
	private function assign_taxonomies( $post_id, $quote_data ) {
		// Assign category taxonomy
		if ( ! empty( $quote_data->category ) ) {
			$this->assign_term( $post_id, $quote_data->category, self::CATEGORY_TAXONOMY );
		}

		// Assign author taxonomy
		if ( ! empty( $quote_data->author ) ) {
			$this->assign_term( $post_id, $quote_data->author, self::AUTHOR_TAXONOMY );
		}
	}

	/**
	 * Assign a taxonomy term to a post
	 *
	 * Creates the term if it doesn't exist, reuses if it does.
	 * For author taxonomy, extracts and saves author URL from HTML if present.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $term_name Term name (may contain HTML for author links).
	 * @param string $taxonomy Taxonomy slug.
	 */
	private function assign_term( $post_id, $term_name, $taxonomy ) {
		// For author taxonomy, extract URL and clean name
		$author_url = '';
		$clean_name = $term_name;

		if ( self::AUTHOR_TAXONOMY === $taxonomy ) {
			// Extract URL from <a href="URL">Author Name</a> format
			if ( preg_match( '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/i', $term_name, $matches ) ) {
				$author_url = esc_url_raw( $matches[1] );
				$clean_name = wp_strip_all_tags( $matches[2] );
			} else {
				// No link found, just strip any HTML tags
				$clean_name = wp_strip_all_tags( $term_name );
			}
		}
		// For non-author taxonomies, use the term name as-is (keep any HTML)

		// Check if term exists
		$term = term_exists( $clean_name, $taxonomy );

		if ( ! $term ) {
			// Create new term
			$term = wp_insert_term( $clean_name, $taxonomy );

			if ( is_wp_error( $term ) ) {
				return;
			}
		}

		// Get the term_id (term_exists returns int, wp_insert_term returns array)
		$term_id = is_array( $term ) ? $term['term_id'] : $term;

		// Save author URL as term meta if extracted and not already set
		if ( self::AUTHOR_TAXONOMY === $taxonomy && ! empty( $author_url ) ) {
			// Only save if term meta doesn't already exist (don't overwrite)
			$existing_url = get_term_meta( $term_id, 'author_url', true );
			if ( empty( $existing_url ) ) {
				update_term_meta( $term_id, 'author_url', $author_url );
			}
		}

		// Assign term to post - wp_set_post_terms expects array of term IDs
		wp_set_post_terms( $post_id, array( intval( $term_id ) ), $taxonomy, false );
	}

	/**
	 * Save post meta for migrated quote
	 *
	 * @param int    $post_id Post ID.
	 * @param object $quote_data Old quote data.
	 * @param int    $old_quote_id Original quote ID from old table.
	 */
	private function save_post_meta( $post_id, $quote_data, $old_quote_id ) {
		// Save source (with HTML sanitization)
		if ( ! empty( $quote_data->source ) ) {
			update_post_meta( $post_id, '_quote_source', wp_kses_post( $quote_data->source ) );
		} else {
			update_post_meta( $post_id, '_quote_source', '' );
		}

		// Save legacy ID
		update_post_meta( $post_id, '_quote_legacy_id', (int) $old_quote_id );
	}
}
