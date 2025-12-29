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
 */
class SettingsMigrator {

	/**
	 * Run the migration
	 */
	public static function migrate() {
		// Check if migration has already been run
		if ( get_option( '_xv_quotes_migrated', false ) ) {
			return;
		}

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

		// Mark migration as complete
		update_option( '_xv_quotes_migrated', true );
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
}
