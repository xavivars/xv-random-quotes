<?php
/**
 * Settings Migration from Legacy to New Settings Structure
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Migration;

use XVRandomQuotes\Admin\Settings;

/**
 * Class SettingsMigrator
 *
 * Handles migration of settings from legacy stray_quotes_options
 * to individual xv_quotes_* options.
 *
 * Uses version numbers for the _xv_quotes_migrated option:
 * - 0 or false: Not migrated
 * - 1: Settings migrated (v1 - initial migration)
 * - 2: Default category setup (v2 - added later)
 * - etc.
 */
class SettingsMigrator {

	/**
	 * Current migration version
	 *
	 * @var int
	 */
	const MIGRATION_VERSION = 2;

	/**
	 * Run the migration
	 */
	public static function migrate() {
		// Get current migration version (0 if never migrated, or false for backward compat)
		$current_version = (int) get_option( '_xv_quotes_migrated', 0 );

		// Run v1 migration if not yet done
		if ( $current_version < 1 ) {
			self::migrate_v200();
		}

		// Run v2 migration if not yet done
		if ( $current_version < 2 ) {
			self::migrate_v260();
		}

		// Update version to current
		update_option( '_xv_quotes_migrated', self::MIGRATION_VERSION );
	}

	/**
	 * V2.0.0 Migration: Settings migration from legacy format
	 */
	private static function migrate_v200() {
		// Get old settings
		$old_settings = get_option( 'stray_quotes_options', array() );

		// Check if this is a new installation (no old settings exist)
		$is_new_install = empty( $old_settings );

		if ( $is_new_install ) {
			// New installation - set defaults
			self::set_new_installation_defaults();
		} else {
			// Existing installation - migrate old settings
			self::migrate_from_legacy_settings( $old_settings );
		}
	}

	/**
	 * V2.6.0 Migration: Default category setup
	 */
	private static function migrate_v260() {
		// Check if there's a default category to migrate
		$old_settings = get_option( 'stray_quotes_options', array() );

		if ( isset( $old_settings['stray_default_category'] ) && ! empty( $old_settings['stray_default_category'] ) ) {
			self::create_default_category_term( $old_settings['stray_default_category'] );
		}
	}

	/**
	 * Set defaults for new installations
	 */
	private static function set_new_installation_defaults() {
		// Use native styling by default for new installations
		update_option( Settings::OPTION_USE_NATIVE_STYLING, true );

		// Set sensible default HTML wrappers for legacy mode (matching old plugin defaults)
		update_option( Settings::OPTION_BEFORE_ALL, '<div class="xv-quote-wrapper">' );
		update_option( Settings::OPTION_AFTER_ALL, '</div>' );
		update_option( Settings::OPTION_BEFORE_QUOTE, '<div class="xv-quote">' );
		update_option( Settings::OPTION_AFTER_QUOTE, '</div>' );
		update_option( Settings::OPTION_BEFORE_AUTHOR, '<div class="xv-quote-author">' );
		update_option( Settings::OPTION_AFTER_AUTHOR, '</div>' );
		update_option( Settings::OPTION_BEFORE_SOURCE, '<div class="xv-quote-source">' );
		update_option( Settings::OPTION_AFTER_SOURCE, '</div>' );
		update_option( Settings::OPTION_IF_NO_AUTHOR, '<div class="xv-quote-source">' );
		update_option( Settings::OPTION_PUT_QUOTES_FIRST, false );
		update_option( Settings::OPTION_LINKTO, '' );
		update_option( Settings::OPTION_SOURCELINKTO, '' );
		update_option( Settings::OPTION_AUTHORSPACES, '' );
		update_option( Settings::OPTION_SOURCESPACES, '' );

		// AJAX settings defaults
		update_option( Settings::OPTION_AJAX, false );
		update_option( Settings::OPTION_LOADER, '' );
		update_option( Settings::OPTION_BEFORE_LOADER, '' );
		update_option( Settings::OPTION_AFTER_LOADER, '' );
		update_option( Settings::OPTION_LOADING, '' );
	}

	/**
	 * Migrate settings from legacy stray_quotes_options format
	 *
	 * @param array $old_settings Legacy settings from stray_quotes_options option.
	 */
	private static function migrate_from_legacy_settings( $old_settings ) {
		// Keep legacy mode for existing installations (for backward compatibility)
		update_option( Settings::OPTION_USE_NATIVE_STYLING, false );

		// Map old option keys to new option keys
		$mapping = array(
			// Display settings
			'stray_quotes_before_all'       => Settings::OPTION_BEFORE_ALL,
			'stray_quotes_after_all'        => Settings::OPTION_AFTER_ALL,
			'stray_quotes_before_quote'     => Settings::OPTION_BEFORE_QUOTE,
			'stray_quotes_after_quote'      => Settings::OPTION_AFTER_QUOTE,
			'stray_quotes_before_author'    => Settings::OPTION_BEFORE_AUTHOR,
			'stray_quotes_after_author'     => Settings::OPTION_AFTER_AUTHOR,
			'stray_quotes_before_source'    => Settings::OPTION_BEFORE_SOURCE,
			'stray_quotes_after_source'     => Settings::OPTION_AFTER_SOURCE,
			'stray_if_no_author'            => Settings::OPTION_IF_NO_AUTHOR,
			'stray_quotes_put_quotes_first' => Settings::OPTION_PUT_QUOTES_FIRST,
			'stray_quotes_linkto'           => Settings::OPTION_LINKTO,
			'stray_quotes_sourcelinkto'     => Settings::OPTION_SOURCELINKTO,
			'stray_quotes_authorspaces'     => Settings::OPTION_AUTHORSPACES,
			'stray_quotes_sourcespaces'     => Settings::OPTION_SOURCESPACES,

			// AJAX settings
			'stray_ajax'          => Settings::OPTION_AJAX,
			'stray_loader'        => Settings::OPTION_LOADER,
			'stray_before_loader' => Settings::OPTION_BEFORE_LOADER,
			'stray_after_loader'  => Settings::OPTION_AFTER_LOADER,
			'stray_loading'       => Settings::OPTION_LOADING,

			// Default category (for applying to migrated quotes without a category)
			'stray_default_category' => 'xv_quotes_default_category',
		);

		// Migrate each setting
		foreach ( $mapping as $old_key => $new_key ) {
			if ( isset( $old_settings[ $old_key ] ) ) {
				$value = $old_settings[ $old_key ];
				
				// Handle utf8_decode for HTML content
				if ( in_array( $old_key, array(
					'stray_quotes_before_all',
					'stray_quotes_after_all',
					'stray_quotes_before_quote',
					'stray_quotes_after_quote',
					'stray_quotes_before_author',
					'stray_quotes_after_author',
					'stray_quotes_before_source',
					'stray_quotes_after_source',
					'stray_if_no_author',
					'stray_before_loader',
					'stray_after_loader',
				), true ) ) {
					$value = utf8_decode( $value );
				}

				// Convert Y/N checkboxes to boolean
				if ( in_array( $old_key, array(
					'stray_quotes_put_quotes_first',
					'stray_ajax',
				), true ) ) {
					// Handle both 'Y' string and boolean true
					$value = ( $value === 'Y' || $value === true );
				}

				update_option( $new_key, $value );
			}
		}
	}

	/**
	 * Create the default category term in the quote_category taxonomy
	 *
	 * @param string $category_name The name of the default category.
	 */
	private static function create_default_category_term( $category_name ) {
		$taxonomy = 'quote_category';

		// Check if term already exists
		$term = term_exists( $category_name, $taxonomy );

		if ( ! $term ) {
			// Create the term
			$term = wp_insert_term( $category_name, $taxonomy );

			if ( is_wp_error( $term ) ) {
				return;
			}
		}

		// Get the term ID (term_exists returns int, wp_insert_term returns array)
		$term_id = is_array( $term ) ? $term['term_id'] : $term;

		// Store the term ID for use as default when creating new quotes
		update_option( 'xv_quotes_default_category_id', (int) $term_id );
	}
}
