<?php
/**
 * Tests for Widget Settings Migration
 *
 * Tests the automatic migration of legacy widget settings
 * from widget_stray_quotes option to modern WP_Widget format.
 *
 * @package XVRandomQuotes
 * @subpackage Tests
 */

/**
 * Test widget settings migration functionality
 */
class Test_Widget_Settings_Migration extends WP_UnitTestCase {

	/**
	 * Clean up after each test
	 */
	public function tearDown(): void {
		delete_option('widget_stray_quotes');
		delete_option('widget_xv_random_quotes_widget');
		delete_option('xv_quotes_widgets_migrated');
		parent::tearDown();
	}

	/**
	 * Test migration detects legacy widget option
	 */
	public function test_migration_detects_legacy_option() {
		// Set up legacy widget data
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Test Widget',
				'groups'       => 'Science',
				'sequence'     => 'N',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => 'New Quote',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		// Migration should detect the option exists
		$this->assertTrue(get_option('widget_stray_quotes') !== false);
	}

	/**
	 * Test migration converts single widget instance
	 */
	public function test_migration_converts_single_widget() {
		// Set up legacy widget
		$legacy_widgets = array(
			1 => array(
				'title'        => 'My Quotes',
				'groups'       => 'Science',
				'sequence'     => 'N',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		// Run migration
		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		// Check new format
		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertIsArray($new_widgets);
		$this->assertArrayHasKey(2, $new_widgets); // Widget instance key
		
		$widget = $new_widgets[2];
		$this->assertEquals('My Quotes', $widget['title']);
		$this->assertEquals('Science', $widget['categories']); // groups → categories
		$this->assertFalse($widget['sequence']); // 'N' → false
		$this->assertEquals(1, $widget['multi']);
		$this->assertFalse($widget['disableaspect']); // 'N' → false
		
		// AJAX fields should be migrated
		$this->assertArrayNotHasKey('noajax', $widget, 'Legacy noajax should be removed');
		$this->assertArrayNotHasKey('linkphrase', $widget, 'Legacy linkphrase should be removed');
		$this->assertFalse($widget['enable_ajax'], 'noajax=Y should become enable_ajax=false');
		$this->assertEquals(0, $widget['timer'], 'Timer should be migrated');
	}

	/**
	 * Test migration converts multiple widget instances
	 */
	public function test_migration_converts_multiple_widgets() {
		// Set up multiple legacy widgets
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Science Quotes',
				'groups'       => 'Science',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			),
			2 => array(
				'title'        => 'Philosophy Quotes',
				'groups'       => 'Philosophy',
				'sequence'     => 'N',
				'multi'        => 3,
				'noajax'       => 'N',
				'disableaspect' => 'Y',
				'linkphrase'   => 'Next Quote',
				'timer'        => 5,
			),
			3 => array(
				'title'        => 'All Quotes',
				'groups'       => 'all',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		// Run migration
		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		// Check all widgets converted
		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertIsArray($new_widgets);
		$this->assertCount(4, $new_widgets); // 3 widgets + _multiwidget flag

		// Check first widget
		$this->assertEquals('Science Quotes', $new_widgets[2]['title']);
		$this->assertEquals('Science', $new_widgets[2]['categories']);
		$this->assertTrue($new_widgets[2]['sequence']); // 'Y' → true

		// Check second widget
		$this->assertEquals('Philosophy Quotes', $new_widgets[3]['title']);
		$this->assertEquals('Philosophy', $new_widgets[3]['categories']);
		$this->assertFalse($new_widgets[3]['sequence']); // 'N' → false
		$this->assertEquals(3, $new_widgets[3]['multi']);
		$this->assertTrue($new_widgets[3]['disableaspect']); // 'Y' → true

		// Check third widget (all categories)
		$this->assertEquals('All Quotes', $new_widgets[4]['title']);
		$this->assertEquals('', $new_widgets[4]['categories']); // 'all' → empty string
	}

	/**
	 * Test field mapping: groups to categories
	 */
	public function test_field_mapping_groups_to_categories() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Test',
				'groups'       => 'Science,Philosophy',
				'sequence'     => 'N',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertEquals('Science,Philosophy', $new_widgets[2]['categories']);
	}

	/**
	 * Test field mapping: sequence Y/N to boolean
	 */
	public function test_field_mapping_sequence_to_boolean() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Random',
				'groups'       => 'all',
				'sequence'     => 'Y', // Random
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			),
			2 => array(
				'title'        => 'Sequential',
				'groups'       => 'all',
				'sequence'     => 'N', // Sequential
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertTrue($new_widgets[2]['sequence']); // Y → true
		$this->assertFalse($new_widgets[3]['sequence']); // N → false
	}

	/**
	 * Test field mapping: disableaspect Y/N to boolean
	 */
	public function test_field_mapping_disableaspect_to_boolean() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Enabled',
				'groups'       => 'all',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N', // Aspect enabled
				'linkphrase'   => '',
				'timer'        => 0,
			),
			2 => array(
				'title'        => 'Disabled',
				'groups'       => 'all',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'Y', // Aspect disabled
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertFalse($new_widgets[2]['disableaspect']); // N → false
		$this->assertTrue($new_widgets[3]['disableaspect']); // Y → true
	}

	/**
	 * Test contributor field preserved
	 */
	public function test_field_mapping_contributor_preserved() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Test',
				'groups'       => 'all',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
				'contributor'  => 'john_doe',
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertEquals('john_doe', $new_widgets[2]['contributor']);
	}

	/**
	 * Test AJAX fields migrated correctly
	 */
	public function test_ajax_fields_migrated_correctly() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Test with AJAX enabled',
				'groups'       => 'all',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'N', // AJAX enabled
				'disableaspect' => 'N',
				'linkphrase'   => 'Click for new quote', // Will be removed
				'timer'        => 10,
			),
			2 => array(
				'title'        => 'Test with AJAX disabled',
				'groups'       => 'all',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y', // AJAX disabled
				'disableaspect' => 'N',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		
		// Widget 1: noajax='N' should become enable_ajax=true
		$widget1 = $new_widgets[2];
		$this->assertTrue($widget1['enable_ajax'], 'noajax=N should convert to enable_ajax=true');
		$this->assertEquals(10, $widget1['timer'], 'Timer should be preserved');
		$this->assertArrayNotHasKey('noajax', $widget1, 'Legacy noajax field should be removed');
		$this->assertArrayNotHasKey('linkphrase', $widget1, 'Legacy linkphrase field should be removed');
		
		// Widget 2: noajax='Y' should become enable_ajax=false
		$widget2 = $new_widgets[3];
		$this->assertFalse($widget2['enable_ajax'], 'noajax=Y should convert to enable_ajax=false');
		$this->assertEquals(0, $widget2['timer'], 'Timer should be preserved');
	}

	/**
	 * Test AJAX fields default values when not present
	 */
	public function test_ajax_fields_default_when_missing() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Test without AJAX fields',
				'groups'       => 'all',
				'sequence'     => 'N',
				'multi'        => 1,
				// No noajax field
				// No timer field
				'disableaspect' => 'N',
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$widget = $new_widgets[2];
		
		// When noajax is missing, default to AJAX disabled (enable_ajax=false)
		$this->assertFalse($widget['enable_ajax'], 'Missing noajax should default to enable_ajax=false');
		
		// When timer is missing, default to 0
		$this->assertEquals(0, $widget['timer'], 'Missing timer should default to 0');
	}

	/**
	 * Test migration sets flag after completion
	 */
	public function test_migration_sets_completion_flag() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Test',
				'groups'       => 'all',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		// Check flag is set
		$this->assertEquals('1', get_option('xv_quotes_widgets_migrated'));
	}

	/**
	 * Test migration runs only once (idempotent)
	 */
	public function test_migration_runs_only_once() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Original',
				'groups'       => 'Science',
				'sequence'     => 'N',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		// Run migration first time
		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		// Modify legacy data
		$legacy_widgets[1]['title'] = 'Modified';
		update_option('widget_stray_quotes', $legacy_widgets);

		// Run migration again
		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		// Check that migration didn't run again (title still 'Original')
		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertEquals('Original', $new_widgets[2]['title']);
	}

	/**
	 * Test migration handles empty legacy widgets
	 */
	public function test_migration_handles_empty_legacy_widgets() {
		update_option('widget_stray_quotes', array());

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		// Should still set flag and create empty widgets option
		$this->assertEquals('1', get_option('xv_quotes_widgets_migrated'));
		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertIsArray($new_widgets);
	}

	/**
	 * Test migration handles missing legacy option
	 */
	public function test_migration_handles_missing_legacy_option() {
		// No legacy widgets exist
		delete_option('widget_stray_quotes');

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		// Should still set flag
		$this->assertEquals('1', get_option('xv_quotes_widgets_migrated'));
	}

	/**
	 * Test migration skips if already migrated
	 */
	public function test_migration_skips_if_already_migrated() {
		// Set migration flag
		update_option('xv_quotes_widgets_migrated', '1');

		// Set up legacy widgets
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Should Not Migrate',
				'groups'       => 'Science',
				'sequence'     => 'N',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		// New widgets should not exist
		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertFalse($new_widgets);
	}

	/**
	 * Test special groups value 'all' converts to empty string
	 */
	public function test_groups_all_converts_to_empty_string() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Test',
				'groups'       => 'all',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertEquals('', $new_widgets[2]['categories']);
	}

	/**
	 * Test special groups value 'default' converts to empty string
	 */
	public function test_groups_default_converts_to_empty_string() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Test',
				'groups'       => 'default',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertEquals('', $new_widgets[2]['categories']);
	}

	/**
	 * Test _multiwidget flag is set
	 */
	public function test_multiwidget_flag_is_set() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Test',
				'groups'       => 'Science',
				'sequence'     => 'Y',
				'multi'        => 1,
				'noajax'       => 'Y',
				'disableaspect' => 'N',
				'linkphrase'   => '',
				'timer'        => 0,
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$this->assertArrayHasKey('_multiwidget', $new_widgets);
		$this->assertEquals(1, $new_widgets['_multiwidget']);
	}

	/**
	 * Test widget with missing optional fields
	 */
	public function test_migration_handles_missing_optional_fields() {
		$legacy_widgets = array(
			1 => array(
				'title'        => 'Minimal Widget',
				'groups'       => 'Science',
				// Missing: sequence, multi, noajax, disableaspect, linkphrase, timer, contributor
			)
		);
		update_option('widget_stray_quotes', $legacy_widgets);

		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();

		$new_widgets = get_option('widget_xv_random_quotes_widget');
		$widget = $new_widgets[2];
		
		$this->assertEquals('Minimal Widget', $widget['title']);
		$this->assertEquals('Science', $widget['categories']);
		
		// Should have sensible defaults
		$this->assertArrayHasKey('sequence', $widget);
		$this->assertArrayHasKey('multi', $widget);
		$this->assertArrayHasKey('disableaspect', $widget);
	}
}
