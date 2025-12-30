<?php
/*
Plugin Name: XV Random Quotes
Description: Display and rotate quotes anywhere on your WordPress site. Fully integrated with WordPress Custom Post Types, Gutenberg blocks, and REST API.
Author: Xavi Ivars
Author URI: https://xavi.ivars.me/
Version: 2.0.5
Requires at least: 6.0
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://spdx.org/licenses/GPL-2.0-or-later.html
Text Domain: xv-random-quotes
*/

define( 'XV_RANDOM_QUOTES', true );

// Load Composer autoloader for v2.0 architecture
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
	
	// Load shortcode handlers
	require_once __DIR__ . '/src/Shortcodes/ShortcodeHandlers.php';
	
	// Load block render callbacks
	require_once __DIR__ . '/src/Blocks/RandomQuote/render.php';
	require_once __DIR__ . '/src/Blocks/SpecificQuote/render.php';
	require_once __DIR__ . '/src/Blocks/ListQuotes/render.php';
	
	// Load backward compatibility wrappers
	require_once __DIR__ . '/backward-compatibility.php';
	
	// Initialize v2.0 architecture (CPT, Taxonomies, Post Meta)
	add_action( 'plugins_loaded', function() {
		\XVRandomQuotes\Plugin::get_instance();
	}, 5 );
	
	// Initialize migration admin notices
	add_action( 'plugins_loaded', function() {
		if ( class_exists( '\XVRandomQuotes\Admin\MigrationNotices' ) ) {
			\XVRandomQuotes\Admin\MigrationNotices::init();
		}
	}, 10 );
	
	// Handle deferred migration after CPT and taxonomies are registered
	add_action( 'init', function() {
		if ( get_option( 'xv_quotes_needs_migration' ) ) {
			delete_option( 'xv_quotes_needs_migration' );
			
			if ( class_exists( '\XVRandomQuotes\Migration\QuoteMigrator' ) ) {
				\XVRandomQuotes\Migration\QuoteMigrator::run_migration();
			}
		}
		
		// Flush rewrite rules if needed (after CPT registration)
		if ( get_option( 'xv_quotes_flush_rewrite_rules' ) ) {
			delete_option( 'xv_quotes_flush_rewrite_rules' );
			flush_rewrite_rules();
		}
	}, 20 ); // Priority 20 to run after CPT/taxonomy registration at priority 10
}

/**
 * Activation hook for v2.0 migration
 * 
 * Sets a flag to trigger migration on next 'init' hook.
 * This ensures CPT and taxonomies are registered before migration runs.
 */
function xv_quotes_activation_migration() {
	// Migrate settings from legacy format to new structure
	if ( class_exists( '\XVRandomQuotes\Migration\SettingsMigrator' ) ) {
		\XVRandomQuotes\Migration\SettingsMigrator::migrate();
	}

	if ( class_exists( '\XVRandomQuotes\Migration\QuoteMigrator' ) ) {
		// Set flag to trigger migration on next init
		update_option( 'xv_quotes_needs_migration', true );
		
		// Set flag to flush rewrite rules on next init
		update_option( 'xv_quotes_flush_rewrite_rules', true );
	}
	
	// Migrate widget settings immediately (doesn't need CPT/taxonomies)
	if ( class_exists( '\XVRandomQuotes\Migration\WidgetMigrator' ) ) {
		\XVRandomQuotes\Migration\WidgetMigrator::migrate_widgets();
	}
}

/**
 * Check if migration is needed on admin dashboard load.
 */
function xv_quotes_check_migration() {
    // If the 'migrated' option doesn't exist (returns false), run the migration
    if ( ! get_option( 'xv_quotes_migrated_v2' ) ) {
        xv_quotes_activation_migration();
    }
}
add_action( 'admin_init', 'xv_quotes_check_migration' );
