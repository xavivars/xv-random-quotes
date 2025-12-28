<?php
/**
 * Widget Settings Migration
 *
 * Handles migration of legacy widget settings from widget_stray_quotes
 * option to modern WP_Widget format (widget_xv_random_quotes_widget).
 *
 * @package XVRandomQuotes
 * @subpackage Migration
 */

namespace XVRandomQuotes\Migration;

/**
 * Widget settings migrator class
 */
class WidgetMigrator {

	/**
	 * Migration flag option name
	 *
	 * @var string
	 */
	const MIGRATION_FLAG = 'xv_quotes_widgets_migrated';

	/**
	 * Legacy widget option name
	 *
	 * @var string
	 */
	const LEGACY_OPTION = 'widget_stray_quotes';

	/**
	 * New widget option name
	 *
	 * @var string
	 */
	const NEW_OPTION = 'widget_xv_random_quotes_widget';

	/**
	 * Migrate legacy widget settings to new format
	 *
	 * Converts widget_stray_quotes option to widget_xv_random_quotes_widget
	 * with proper field mappings and structure for modern WP_Widget.
	 *
	 * @return bool True if migration completed, false if skipped
	 */
	public static function migrate_widgets() {
		// Check if already migrated
		if ( get_option( self::MIGRATION_FLAG ) ) {
			return false;
		}

		// Get legacy widgets
		$legacy_widgets = get_option( self::LEGACY_OPTION, array() );

		// Initialize new widgets array with _multiwidget flag
		$new_widgets = array(
			'_multiwidget' => 1,
		);

		// Convert each legacy widget instance
		if ( is_array( $legacy_widgets ) && ! empty( $legacy_widgets ) ) {
			// Widget IDs start at 2 in WP_Widget
			$widget_id = 2;
			
			foreach ( $legacy_widgets as $legacy_id => $legacy_settings ) {
				if ( ! is_array( $legacy_settings ) ) {
					continue;
				}

				$new_widgets[ $widget_id ] = self::convert_widget_instance( $legacy_settings );
				$widget_id++;
			}
		}

		// Save new widgets option
		update_option( self::NEW_OPTION, $new_widgets );

		// Set migration flag
		update_option( self::MIGRATION_FLAG, '1' );

		return true;
	}

	/**
	 * Convert a single widget instance from legacy to new format
	 *
	 * Field mappings:
	 * - title: preserved
	 * - groups → categories (renamed)
	 * - sequence: Y/N → true/false
	 * - multi: preserved
	 * - disableaspect: Y/N → true/false
	 * - contributor: preserved
	 * - noajax: removed (deferred to Tasks 35-36)
	 * - linkphrase: removed (deferred to Tasks 35-36)
	 * - timer: removed (deferred to Tasks 35-36)
	 *
	 * @param array $legacy Legacy widget settings
	 * @return array Converted widget settings
	 */
	private static function convert_widget_instance( $legacy ) {
		$new_instance = array();

		// Title - preserved
		$new_instance['title'] = isset( $legacy['title'] ) ? $legacy['title'] : '';

		// Groups → Categories (renamed)
		// Convert 'all' and 'default' to empty string
		$groups = isset( $legacy['groups'] ) ? $legacy['groups'] : '';
		if ( in_array( $groups, array( 'all', 'default' ), true ) ) {
			$new_instance['categories'] = '';
		} else {
			$new_instance['categories'] = $groups;
		}

		// Sequence - Y/N to boolean (Y = random = true, N = sequential = false)
		$sequence = isset( $legacy['sequence'] ) ? $legacy['sequence'] : 'Y';
		$new_instance['sequence'] = ( $sequence === 'Y' );

		// Multi - preserved
		$new_instance['multi'] = isset( $legacy['multi'] ) ? (int) $legacy['multi'] : 1;

		// Disableaspect - Y/N to boolean
		$disableaspect = isset( $legacy['disableaspect'] ) ? $legacy['disableaspect'] : 'N';
		$new_instance['disableaspect'] = ( $disableaspect === 'Y' );

		// Contributor - preserved if present
		if ( isset( $legacy['contributor'] ) ) {
			$new_instance['contributor'] = $legacy['contributor'];
		}

		// AJAX-related fields (noajax, linkphrase, timer) are intentionally
		// NOT migrated - they will be reimplemented in Tasks 35-36

		return $new_instance;
	}
}
