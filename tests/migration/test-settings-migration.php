<?php
/**
 * Class Test_Settings_Migration
 *
 * Tests settings migration from legacy stray_quotes_options to new xv_quotes_* options.
 *
 * @package XVRandomQuotes
 */

require_once __DIR__ . '/../bootstrap.php';

use XVRandomQuotes\Migration\SettingsMigrator;
use XVRandomQuotes\Admin\Settings;

/**
 * Test settings migration
 */
class Test_Settings_Migration extends WP_UnitTestCase {

	/**
	 * Clean up before each test
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Clean up all settings
		delete_option( '_xv_quotes_migrated' );
		delete_option( 'stray_quotes_options' );
		
		// Delete all new settings - Display section
		delete_option( Settings::OPTION_USE_NATIVE_STYLING );
		delete_option( Settings::OPTION_BEFORE_ALL );
		delete_option( Settings::OPTION_AFTER_ALL );
		delete_option( Settings::OPTION_BEFORE_QUOTE );
		delete_option( Settings::OPTION_AFTER_QUOTE );
		delete_option( Settings::OPTION_BEFORE_AUTHOR );
		delete_option( Settings::OPTION_AFTER_AUTHOR );
		delete_option( Settings::OPTION_BEFORE_SOURCE );
		delete_option( Settings::OPTION_AFTER_SOURCE );
		delete_option( Settings::OPTION_IF_NO_AUTHOR );
		delete_option( Settings::OPTION_PUT_QUOTES_FIRST );
		delete_option( Settings::OPTION_LINKTO );
		delete_option( Settings::OPTION_SOURCELINKTO );
		delete_option( Settings::OPTION_AUTHORSPACES );
		delete_option( Settings::OPTION_SOURCESPACES );

		// Delete AJAX settings
		delete_option( Settings::OPTION_AJAX );
		delete_option( Settings::OPTION_LOADER );
		delete_option( Settings::OPTION_BEFORE_LOADER );
		delete_option( Settings::OPTION_AFTER_LOADER );
		delete_option( Settings::OPTION_LOADING );
	}

	/**
	 * Test new installation gets native styling enabled
	 */
	public function test_new_installation_gets_native_styling() {
		// Run migration with no old settings (new install)
		SettingsMigrator::migrate();

		// Check that native styling is enabled
		$this->assertTrue( (bool) get_option( Settings::OPTION_USE_NATIVE_STYLING ) );
	}

	/**
	 * Test new installation gets default wrapper settings
	 */
	public function test_new_installation_gets_default_wrappers() {
		// Run migration with no old settings (new install)
		SettingsMigrator::migrate();

		// Check default wrapper structure
		$this->assertEquals( '<div class="xv-quote-wrapper">', get_option( Settings::OPTION_BEFORE_ALL ) );
		$this->assertEquals( '</div>', get_option( Settings::OPTION_AFTER_ALL ) );
		$this->assertEquals( '<div class="xv-quote">', get_option( Settings::OPTION_BEFORE_QUOTE ) );
		$this->assertEquals( '</div>', get_option( Settings::OPTION_AFTER_QUOTE ) );
		$this->assertEquals( '<div class="xv-quote-author">', get_option( Settings::OPTION_BEFORE_AUTHOR ) );
		$this->assertEquals( '</div>', get_option( Settings::OPTION_AFTER_AUTHOR ) );
		$this->assertEquals( '<div class="xv-quote-source">', get_option( Settings::OPTION_BEFORE_SOURCE ) );
		$this->assertEquals( '</div>', get_option( Settings::OPTION_AFTER_SOURCE ) );
		$this->assertEquals( '<div class="xv-quote-source">', get_option( Settings::OPTION_IF_NO_AUTHOR ) );
	}

	/**
	 * Test new installation gets default link settings
	 */
	public function test_new_installation_gets_default_link_settings() {
		// Run migration with no old settings (new install)
		SettingsMigrator::migrate();

		// Check default link settings
		$this->assertFalse( (bool) get_option( Settings::OPTION_PUT_QUOTES_FIRST ) );
		$this->assertEquals( '', get_option( Settings::OPTION_LINKTO ) );
		$this->assertEquals( '', get_option( Settings::OPTION_SOURCELINKTO ) );
		$this->assertEquals( '', get_option( Settings::OPTION_AUTHORSPACES ) );
		$this->assertEquals( '', get_option( Settings::OPTION_SOURCESPACES ) );
	}

	/**
	 * Test existing installation keeps legacy mode
	 */
	public function test_existing_installation_keeps_legacy_mode() {
		// Set up old settings (simulating existing install)
		update_option( 'stray_quotes_options', array(
			'stray_quotes_before_all' => '<div class="old-wrapper">',
			'stray_quotes_after_all'  => '</div>',
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Check that native styling is disabled for backward compatibility
		$this->assertFalse( (bool) get_option( Settings::OPTION_USE_NATIVE_STYLING ) );
	}

	/**
	 * Test migration of HTML wrapper settings
	 */
	public function test_migration_of_html_wrappers() {
		// Set up old settings with custom wrappers
		update_option( 'stray_quotes_options', array(
			'stray_quotes_before_all'    => '<div class="custom-wrapper">',
			'stray_quotes_after_all'     => '</div><!-- end wrapper -->',
			'stray_quotes_before_quote'  => '<blockquote>',
			'stray_quotes_after_quote'   => '</blockquote>',
			'stray_quotes_before_author' => '<cite class="author">',
			'stray_quotes_after_author'  => '</cite>',
			'stray_quotes_before_source' => '<span class="source">',
			'stray_quotes_after_source'  => '</span>',
			'stray_if_no_author'         => '<span class="no-author">',
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Check all wrappers were migrated
		$this->assertStringContainsString( 'custom-wrapper', get_option( Settings::OPTION_BEFORE_ALL ) );
		$this->assertStringContainsString( 'end wrapper', get_option( Settings::OPTION_AFTER_ALL ) );
		$this->assertEquals( '<blockquote>', get_option( Settings::OPTION_BEFORE_QUOTE ) );
		$this->assertEquals( '</blockquote>', get_option( Settings::OPTION_AFTER_QUOTE ) );
		$this->assertStringContainsString( 'author', get_option( Settings::OPTION_BEFORE_AUTHOR ) );
		$this->assertEquals( '</cite>', get_option( Settings::OPTION_AFTER_AUTHOR ) );
		$this->assertStringContainsString( 'source', get_option( Settings::OPTION_BEFORE_SOURCE ) );
		$this->assertEquals( '</span>', get_option( Settings::OPTION_AFTER_SOURCE ) );
		$this->assertStringContainsString( 'no-author', get_option( Settings::OPTION_IF_NO_AUTHOR ) );
	}

	/**
	 * Test migration of link settings
	 */
	public function test_migration_of_link_settings() {
		// Set up old settings with link configurations
		update_option( 'stray_quotes_options', array(
			'stray_quotes_put_quotes_first' => true,
			'stray_quotes_linkto'           => 'https://example.com/authors/%AUTHOR%',
			'stray_quotes_sourcelinkto'     => 'https://example.com/sources/%SOURCE%',
			'stray_quotes_authorspaces'     => '_',
			'stray_quotes_sourcespaces'     => '-',
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Check all link settings were migrated
		$this->assertTrue( (bool) get_option( Settings::OPTION_PUT_QUOTES_FIRST ) );
		$this->assertEquals( 'https://example.com/authors/%AUTHOR%', get_option( Settings::OPTION_LINKTO ) );
		$this->assertEquals( 'https://example.com/sources/%SOURCE%', get_option( Settings::OPTION_SOURCELINKTO ) );
		$this->assertEquals( '_', get_option( Settings::OPTION_AUTHORSPACES ) );
		$this->assertEquals( '-', get_option( Settings::OPTION_SOURCESPACES ) );
	}

	/**
	 * Test migration only runs once
	 */
	public function test_migration_only_runs_once() {
		// Set up old settings
		update_option( 'stray_quotes_options', array(
			'stray_quotes_before_all' => '<div class="first-run">',
		) );

		// Run migration first time
		SettingsMigrator::migrate();
		$this->assertStringContainsString( 'first-run', get_option( Settings::OPTION_BEFORE_ALL ) );

		// Change the old settings
		update_option( 'stray_quotes_options', array(
			'stray_quotes_before_all' => '<div class="second-run">',
		) );

		// Run migration again
		SettingsMigrator::migrate();

		// Check that settings were NOT updated (migration already ran)
		$this->assertStringContainsString( 'first-run', get_option( Settings::OPTION_BEFORE_ALL ) );
		$this->assertStringNotContainsString( 'second-run', get_option( Settings::OPTION_BEFORE_ALL ) );
	}

	/**
	 * Test migration flag is set
	 */
	public function test_migration_flag_is_set() {
		// Initially no flag
		$this->assertFalse( get_option( '_xv_quotes_migrated' ) );

		// Run migration
		SettingsMigrator::migrate();

		// Flag should be set
		$this->assertTrue( (bool) get_option( '_xv_quotes_migrated' ) );
	}

	/**
	 * Test partial settings migration (some settings missing)
	 */
	public function test_partial_settings_migration() {
		// Set up old settings with only some values
		update_option( 'stray_quotes_options', array(
			'stray_quotes_before_all'  => '<div>',
			'stray_quotes_linkto'      => 'https://example.com/%AUTHOR%',
			// Other settings intentionally missing
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Check that present settings were migrated
		$this->assertEquals( '<div>', get_option( Settings::OPTION_BEFORE_ALL ) );
		$this->assertEquals( 'https://example.com/%AUTHOR%', get_option( Settings::OPTION_LINKTO ) );

		// Check that missing settings are not set (should be false or empty)
		$this->assertFalse( get_option( Settings::OPTION_BEFORE_QUOTE ) );
		$this->assertFalse( get_option( Settings::OPTION_SOURCELINKTO ) );
	}

	/**
	 * Test migration handles empty string values
	 */
	public function test_migration_handles_empty_strings() {
		// Set up old settings with empty strings
		update_option( 'stray_quotes_options', array(
			'stray_quotes_before_all'    => '<div>',
			'stray_quotes_before_quote'  => '',
			'stray_quotes_linkto'        => '',
			'stray_quotes_authorspaces'  => '',
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Check that empty strings are preserved
		$this->assertEquals( '<div>', get_option( Settings::OPTION_BEFORE_ALL ) );
		$this->assertEquals( '', get_option( Settings::OPTION_BEFORE_QUOTE ) );
		$this->assertEquals( '', get_option( Settings::OPTION_LINKTO ) );
		$this->assertEquals( '', get_option( Settings::OPTION_AUTHORSPACES ) );
	}

	/**
	 * Test migration handles special characters in HTML
	 */
	public function test_migration_handles_special_characters() {
		// Set up old settings with special characters
		update_option( 'stray_quotes_options', array(
			'stray_quotes_before_all'   => '<div class="quote-wrapper" data-attr="test&amp;">',
			'stray_quotes_before_author' => '<span>« ',
			'stray_quotes_after_author'  => ' »</span>',
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Check that special characters are preserved
		$before_all = get_option( Settings::OPTION_BEFORE_ALL );
		$this->assertStringContainsString( 'quote-wrapper', $before_all );
		$this->assertStringContainsString( 'data-attr', $before_all );
	}

	/**
	 * Test that old settings option remains untouched
	 */
	public function test_old_settings_remain_untouched() {
		// Set up old settings
		$old_settings = array(
			'stray_quotes_before_all' => '<div class="old">',
			'stray_quotes_linkto'     => 'https://example.com',
		);
		update_option( 'stray_quotes_options', $old_settings );

		// Run migration
		SettingsMigrator::migrate();

		// Check that old settings are still there (not deleted)
		$this->assertEquals( $old_settings, get_option( 'stray_quotes_options' ) );
	}

	/**
	 * Test migration handles boolean values correctly
	 */
	public function test_migration_handles_boolean_values() {
		// Set up old settings with boolean
		update_option( 'stray_quotes_options', array(
			'stray_quotes_put_quotes_first' => true,
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Check boolean was migrated correctly
		$this->assertTrue( (bool) get_option( Settings::OPTION_PUT_QUOTES_FIRST ) );

		// Clean up and test false value
		delete_option( '_xv_quotes_migrated' );
		delete_option( Settings::OPTION_PUT_QUOTES_FIRST );
		
		update_option( 'stray_quotes_options', array(
			'stray_quotes_put_quotes_first' => false,
		) );

		SettingsMigrator::migrate();
		$this->assertFalse( (bool) get_option( Settings::OPTION_PUT_QUOTES_FIRST ) );
	}

	/**
	 * Test new installation gets default AJAX settings
	 */
	public function test_new_installation_gets_default_ajax_settings() {
		// Run migration with no old settings (new install)
		SettingsMigrator::migrate();

		// Check default AJAX settings
		$this->assertFalse( (bool) get_option( Settings::OPTION_AJAX ) );
		$this->assertEquals( '', get_option( Settings::OPTION_LOADER ) );
		$this->assertEquals( '', get_option( Settings::OPTION_BEFORE_LOADER ) );
		$this->assertEquals( '', get_option( Settings::OPTION_AFTER_LOADER ) );
		$this->assertEquals( '', get_option( Settings::OPTION_LOADING ) );
	}

	/**
	 * Test migration of AJAX settings
	 */
	public function test_migration_of_ajax_settings() {
		// Set up old settings with AJAX configurations
		update_option( 'stray_quotes_options', array(
			'stray_ajax'          => 'Y',
			'stray_loader'        => 'New quote &raquo;',
			'stray_before_loader' => '<p>',
			'stray_after_loader'  => '</p>',
			'stray_loading'       => 'Loading...',
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Check all AJAX settings were migrated
		$this->assertTrue( (bool) get_option( Settings::OPTION_AJAX ) );
		$this->assertEquals( 'New quote &raquo;', get_option( Settings::OPTION_LOADER ) );
		$this->assertEquals( '<p>', get_option( Settings::OPTION_BEFORE_LOADER ) );
		$this->assertEquals( '</p>', get_option( Settings::OPTION_AFTER_LOADER ) );
		$this->assertEquals( 'Loading...', get_option( Settings::OPTION_LOADING ) );
	}

	/**
	 * Test migration of Y/N checkboxes to boolean
	 */
	public function test_migration_handles_yn_checkboxes() {
		// Set up old settings with Y/N values
		update_option( 'stray_quotes_options', array(
			'stray_quotes_put_quotes_first' => 'Y',
			'stray_ajax'                    => 'Y',
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Check all Y values converted to true
		$this->assertTrue( (bool) get_option( Settings::OPTION_PUT_QUOTES_FIRST ) );
		$this->assertTrue( (bool) get_option( Settings::OPTION_AJAX ) );
	}

	/**
	 * Test comprehensive migration of all settings
	 */
	public function test_comprehensive_migration_all_settings() {
		// Set up old settings with all possible values
		update_option( 'stray_quotes_options', array(
			// Display settings
			'stray_quotes_before_all'       => '<div class="all">',
			'stray_quotes_after_all'        => '</div>',
			'stray_quotes_before_quote'     => '<div class="quote">',
			'stray_quotes_after_quote'      => '</div>',
			'stray_quotes_before_author'    => '<span class="author">',
			'stray_quotes_after_author'     => '</span>',
			'stray_quotes_before_source'    => '<span class="source">',
			'stray_quotes_after_source'     => '</span>',
			'stray_if_no_author'            => '<span class="no-author">',
			'stray_quotes_put_quotes_first' => 'Y',
			'stray_quotes_linkto'           => 'https://example.com/%AUTHOR%',
			'stray_quotes_sourcelinkto'     => 'https://example.com/%SOURCE%',
			'stray_quotes_authorspaces'     => '_',
			'stray_quotes_sourcespaces'     => '-',
			// AJAX settings
			'stray_ajax'                    => 'Y',
			'stray_loader'                  => 'New quote',
			'stray_before_loader'           => '<p class="loader">',
			'stray_after_loader'            => '</p>',
			'stray_loading'                 => 'Loading...',
		) );

		// Run migration
		SettingsMigrator::migrate();

		// Verify all settings were migrated correctly
		// Display settings
		$this->assertEquals( '<div class="all">', get_option( Settings::OPTION_BEFORE_ALL ) );
		$this->assertEquals( '</div>', get_option( Settings::OPTION_AFTER_ALL ) );
		$this->assertEquals( '<div class="quote">', get_option( Settings::OPTION_BEFORE_QUOTE ) );
		$this->assertEquals( '</div>', get_option( Settings::OPTION_AFTER_QUOTE ) );
		$this->assertEquals( '<span class="author">', get_option( Settings::OPTION_BEFORE_AUTHOR ) );
		$this->assertEquals( '</span>', get_option( Settings::OPTION_AFTER_AUTHOR ) );
		$this->assertEquals( '<span class="source">', get_option( Settings::OPTION_BEFORE_SOURCE ) );
		$this->assertEquals( '</span>', get_option( Settings::OPTION_AFTER_SOURCE ) );
		$this->assertEquals( '<span class="no-author">', get_option( Settings::OPTION_IF_NO_AUTHOR ) );
		$this->assertTrue( (bool) get_option( Settings::OPTION_PUT_QUOTES_FIRST ) );
		$this->assertEquals( 'https://example.com/%AUTHOR%', get_option( Settings::OPTION_LINKTO ) );
		$this->assertEquals( 'https://example.com/%SOURCE%', get_option( Settings::OPTION_SOURCELINKTO ) );
		$this->assertEquals( '_', get_option( Settings::OPTION_AUTHORSPACES ) );
		$this->assertEquals( '-', get_option( Settings::OPTION_SOURCESPACES ) );

		// AJAX settings
		$this->assertTrue( (bool) get_option( Settings::OPTION_AJAX ) );
		$this->assertEquals( 'New quote', get_option( Settings::OPTION_LOADER ) );
		$this->assertEquals( '<p class="loader">', get_option( Settings::OPTION_BEFORE_LOADER ) );
		$this->assertEquals( '</p>', get_option( Settings::OPTION_AFTER_LOADER ) );
		$this->assertEquals( 'Loading...', get_option( Settings::OPTION_LOADING ) );

		// Verify legacy mode is used for existing installation
		$this->assertFalse( (bool) get_option( Settings::OPTION_USE_NATIVE_STYLING ) );
	}
}
