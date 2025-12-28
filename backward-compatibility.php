<?php
/**
 * Backward Compatibility Layer
 * 
 * This file contains thin wrappers for functions that have been refactored
 * to use the new CPT architecture. These wrappers maintain backward compatibility
 * for existing users while the actual implementation lives in the src/ directory.
 *
 * ORGANIZATION:
 * - Refactored code lives in: src/legacy/ (shortcodes.php, stray_helpers.php)
 * - Backward compatibility wrappers live here (this file)
 * - Legacy code still to be migrated lives in: inc/stray_functions.php
 *
 * WHY THIS FILE EXISTS:
 * - Separates migrated code (wrappers) from unmigrated code (stray_functions.php)
 * - Makes it clear which functions have been refactored vs still need work
 * - Maintains backward compatibility for users calling these functions directly
 * - Cleaner organization as migration progresses through Phase 7+ 
 *
 * @package XVRandomQuotes
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Shortcode [stray-random] - Display random quote(s)
 * Wrapper for backwards compatibility - actual implementation in src/legacy/shortcodes.php
 */
function stray_random_shortcode($atts, $content = NULL) {
	return \XVRandomQuotes\Legacy\stray_random_shortcode($atts, $content);
}

/**
 * Shortcode [stray-all] - Display all quotes with pagination
 * Wrapper for backwards compatibility - actual implementation in src/legacy/shortcodes.php
 */
function stray_all_shortcode($atts, $content = NULL) {
	return \XVRandomQuotes\Legacy\stray_all_shortcode($atts, $content);
}

/**
 * Shortcode [stray-id] - Display specific quote by ID
 * Wrapper for backwards compatibility - actual implementation in src/legacy/shortcodes.php
 */
function stray_id_shortcode($atts, $content = NULL) {
	return \XVRandomQuotes\Legacy\stray_id_shortcode($atts, $content);
}

// ==============================================================================
// HELPER FUNCTION WRAPPERS
// ==============================================================================

/**
 * Add or replace a variable in a querystring
 * Wrapper for backwards compatibility - actual implementation in src/legacy/stray_helpers.php
 * 
 * Thanks to http://www.addedbytes.com/php/querystring-functions/
 */
function querystrings($url, $key, $value) {
	return \XVRandomQuotes\Legacy\querystrings($url, $key, $value);
}

/**
 * Remove a variable from a querystring
 * Wrapper for backwards compatibility - actual implementation in src/legacy/stray_helpers.php
 * 
 * Thanks to http://www.addedbytes.com/php/querystring-functions/
 */
function remove_querystring_var($url, $key) {
	return \XVRandomQuotes\Legacy\remove_querystring_var($url, $key);
}

/**
 * Legacy function name from original plugin - calls stray_random_quote()
 */
function wp_quotes_random() {
	return stray_random_quote();
}

/**
 * Legacy function name from from original plugin - calls stray_a_quote()
 */
function wp_quotes($id) {
	return stray_a_quote($id);
}

/**
 * Legacy function name from from original plugin - calls stray_all_shortcode()
 */
function wp_quotes_page($data) {
	return stray_all_shortcode();
}
