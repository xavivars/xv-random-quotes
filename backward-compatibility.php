<?php
/**
 * Backward Compatibility Layer
 * 
 * This file contains thin wrappers for functions that have been refactored
 * to use the new CPT architecture. These wrappers maintain backward compatibility
 * for existing users while the actual implementation lives in the src/ directory.
 *
 * ORGANIZATION:
 * - Refactored code lives in: src/Shortcodes/ and src/legacy/
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
 * Wrapper for backwards compatibility - actual implementation in src/Shortcodes/ShortcodeHandlers.php
 */
function stray_random_shortcode($atts, $content = NULL) {
	return \XVRandomQuotes\Shortcodes\stray_random_shortcode($atts, $content);
}

/**
 * Shortcode [stray-all] - Display all quotes with pagination
 * Wrapper for backwards compatibility - actual implementation in src/Shortcodes/ShortcodeHandlers.php
 */
function stray_all_shortcode($atts, $content = NULL) {
	return \XVRandomQuotes\Shortcodes\stray_all_shortcode($atts, $content);
}

/**
 * Shortcode [stray-id] - Display specific quote by ID
 * Wrapper for backwards compatibility - actual implementation in src/Shortcodes/ShortcodeHandlers.php
 */
function stray_id_shortcode($atts, $content = NULL) {
	return \XVRandomQuotes\Shortcodes\stray_id_shortcode($atts, $content);
}

// ==============================================================================
// TEMPLATE TAG WRAPPERS (Refactored to use CPT architecture)
// ==============================================================================

/**
 * Template tag: Display one or more random quotes
 * Echoes output - uses QuoteOutput class
 *
 * @param string $categories Category slugs (comma-separated or 'all')
 * @param bool $sequence Whether to use sequential ordering
 * @param string $linkphrase Legacy parameter (not used in refactored version)
 * @param bool $noajax Legacy parameter (not used in refactored version)
 * @param int $multi Number of quotes to retrieve
 * @param int $timer Legacy parameter (not used in refactored version)
 * @param string $orderby Legacy parameter (not used in refactored version)
 * @param string $sort Legacy parameter (not used in refactored version)
 * @param bool|null $disableaspect Whether to disable formatting
 * @param string|null $contributor Filter by user/contributor
 */
function stray_random_quote($categories='all',$sequence=false,$linkphrase='',$noajax=false,$multi=1,$timer=0,$orderby='quoteID',$sort='ASC',$disableaspect=NULL, $contributor=NULL) {
	// Note: linkphrase, noajax, timer, orderby, and sort parameters are not used in the refactored version
	// The new implementation uses WP_Query with random or sequential ordering
	
	// Normalize disableaspect to boolean
	if ($disableaspect === NULL) {
		$disableaspect = false;
	}
	
	// Use QuoteOutput class and echo result
	$quote_output = new \XVRandomQuotes\Output\QuoteOutput();
	$output = $quote_output->get_random_quotes($categories, $sequence, $multi, 0, $disableaspect, $contributor);
	echo wp_kses($output);
}

/**
 * Template tag: Display a specific quote by ID
 * Echoes output - uses QuoteOutput class
 *
 * @param int $id Quote ID (post ID or legacy ID)
 * @param string $linkphrase Legacy parameter (not used in refactored version)
 * @param bool $noajax Legacy parameter (not used in refactored version)
 * @param bool|null $disableaspect Whether to disable formatting
 */
function stray_a_quote($id=1,$linkphrase='',$noajax=false,$disableaspect=NULL) {
	// Note: linkphrase and noajax parameters are not used in the refactored version
	// The new implementation uses WP_Query to retrieve quotes
	
	// Normalize disableaspect to boolean
	if ($disableaspect === NULL) {
		$disableaspect = false;
	}
	
	// Use QuoteOutput class and echo result
	$quote_output = new \XVRandomQuotes\Output\QuoteOutput();
	$output = $quote_output->get_quote_by_id($id, $disableaspect);
	echo wp_kses($output);
}

// ==============================================================================
// HELPER FUNCTION WRAPPERS
// ==============================================================================

/**
 * Add or replace a variable in a querystring
 * Wrapper for backwards compatibility - uses PaginationHelper class
 * 
 * Thanks to http://www.addedbytes.com/php/querystring-functions/
 */
function querystrings($url, $key, $value) {
	return \XVRandomQuotes\Utils\PaginationHelper::add_querystring_var($url, $key, $value);
}

/**
 * Remove a variable from a querystring
 * Wrapper for backwards compatibility - uses PaginationHelper class
 * 
 * Thanks to http://www.addedbytes.com/php/querystring-functions/
 */
function remove_querystring_var($url, $key) {
	return \XVRandomQuotes\Utils\PaginationHelper::remove_querystring_var($url, $key);
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
