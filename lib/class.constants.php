<?php

/*
 * Constants used by the plugin
 * 
 */

global $wpdb;

if ( !defined ( 'WPDB_PREFIX' ) ) {
	define ( 'WPDB_PREFIX' , $wpdb->prefix );
}
define("XV_RANDOMQUOTES_TABLE", $wpdb->prefix . "stray_quotes");
define("XV_RANDOMQUOTES_TABLE", $wpdb->prefix . "stray_quotes");

class XV_RandomQuotes_Constants {

	const PLUGIN_OPTIONS = 'stray_quotes_options';
	const VERSION = '1.28';
	const DEFAULT_CATEGORY_OPTION	 = 'stray_default_category';
	const DEFAULT_RELOAD_TEXT_OPTION	 = 'stray_loader';
	
	const DB_TABLE = XV_RANDOMQUOTES_TABLE;
}
