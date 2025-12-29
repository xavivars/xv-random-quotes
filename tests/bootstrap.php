<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/xv-random-quotes.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

// Immediately after WordPress bootstrap, run migration and set legacy mode for tests
// The migration must run after WordPress DB is initialized
class XVRandomQuotes_Test_Setup {
	public static function setup() {
		// Run migration to initialize all xv_quotes_* options
		$migrator_class = 'XVRandomQuotes\Migration\SettingsMigrator';
		if ( class_exists( $migrator_class ) ) {
			call_user_func( array( $migrator_class, 'migrate' ) );
		}
		
		// Override to legacy mode for tests
		// Tests were written expecting legacy HTML wrapper output with xv-quote classes
		update_option( 'xv_quotes_use_native_styling', false );
	}
}

// Run setup before any tests execute
XVRandomQuotes_Test_Setup::setup();
