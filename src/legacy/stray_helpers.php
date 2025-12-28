<?php
/**
 * Legacy helper functions for quote formatting and output
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Legacy;

/**
 * Formats a CPT quote (WP_Post object) according to the settings
 *
 * @param \WP_Post $post The quote post object
 * @param mixed $multi Whether this is part of a multi-quote display
 * @param bool $disableaspect Whether to disable aspect/formatting
 * @return string Formatted quote HTML
 */
function stray_output_one_cpt($post, $multi = NULL, $disableaspect = NULL) {
	if (!$post || !is_a($post, 'WP_Post')) {
		return '';
	}

	// Get quote data from CPT
	$quote_text = $post->post_content;
	$source = get_post_meta($post->ID, '_quote_source', true);
	
	// Get author from taxonomy
	$author_terms = wp_get_post_terms($post->ID, 'quote_author');
	$author = !empty($author_terms) && !is_wp_error($author_terms) ? $author_terms[0]->name : '';

	// Get settings
	$quotesoptions = get_option('stray_quotes_options');
	$beforeAll = '';
	$afterAll = '';
	
	if (!$disableaspect && $quotesoptions) {
		if ($multi == 1 || $multi == '' || $multi == false) {
			$beforeAll = isset($quotesoptions['stray_quotes_before_all']) ? utf8_decode($quotesoptions['stray_quotes_before_all']) : '';
			$afterAll = isset($quotesoptions['stray_quotes_after_all']) ? utf8_decode($quotesoptions['stray_quotes_after_all']) : '';
		}
		$beforeQuote = isset($quotesoptions['stray_quotes_before_quote']) ? utf8_decode($quotesoptions['stray_quotes_before_quote']) : '';
		$afterQuote = isset($quotesoptions['stray_quotes_after_quote']) ? utf8_decode($quotesoptions['stray_quotes_after_quote']) : '';
		$beforeAuthor = isset($quotesoptions['stray_quotes_before_author']) ? utf8_decode($quotesoptions['stray_quotes_before_author']) : '';
		$afterAuthor = isset($quotesoptions['stray_quotes_after_author']) ? utf8_decode($quotesoptions['stray_quotes_after_author']) : '';
		$beforeSource = isset($quotesoptions['stray_quotes_before_source']) ? utf8_decode($quotesoptions['stray_quotes_before_source']) : '';
		$afterSource = isset($quotesoptions['stray_quotes_after_source']) ? utf8_decode($quotesoptions['stray_quotes_after_source']) : '';
		$linkto = isset($quotesoptions['stray_quotes_linkto']) ? utf8_decode($quotesoptions['stray_quotes_linkto']) : '';
		$sourcelinkto = isset($quotesoptions['stray_quotes_sourcelinkto']) ? utf8_decode($quotesoptions['stray_quotes_sourcelinkto']) : '';
		$sourcespaces = isset($quotesoptions['stray_quotes_sourcespaces']) ? utf8_decode($quotesoptions['stray_quotes_sourcespaces']) : '';
		$authorspaces = isset($quotesoptions['stray_quotes_authorspaces']) ? utf8_decode($quotesoptions['stray_quotes_authorspaces']) : '';
		$ifnoauthor = isset($quotesoptions['stray_if_no_author']) ? utf8_decode($quotesoptions['stray_if_no_author']) : '';
		$putQuotesFirst = isset($quotesoptions['stray_quotes_put_quotes_first']) ? utf8_decode($quotesoptions['stray_quotes_put_quotes_first']) : false;
	} else {
		$beforeQuote = '';
		$afterQuote = '';
		$beforeAuthor = '';
		$afterAuthor = '';
		$beforeSource = '';
		$afterSource = '';
		$linkto = '';
		$sourcelinkto = '';
		$sourcespaces = '';
		$authorspaces = '';
		$ifnoauthor = '';
		$putQuotesFirst = false;
	}

	$output = '';

	// Format author with link if needed
	$Author = $author;
	if ($author && $linkto && !preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i", $author)) {
		if ($authorspaces) {
			$Author = str_replace(" ", $authorspaces, $author);
		}
		$search = array('"', '&', '%AUTHOR%');
		$replace = array('%22', '&amp;', $Author);
		$author_linkto = str_replace($search, $replace, $linkto);
		$Author = '<a href="' . $author_linkto . '">' . $author . '</a>';
	}

	// Format source with link if needed
	$Source = $source;
	if ($source && $sourcelinkto && !preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i", $source)) {
		if ($sourcespaces) {
			$Source = str_replace(" ", $sourcespaces, $source);
		}
		$search = array('"', '&', '%SOURCE%');
		$replace = array('%22', '&amp;', $Source);
		$source_linkto = str_replace($search, $replace, $sourcelinkto);
		$Source = '<a href="' . $source_linkto . '">' . $source . '</a>';
	}

	// Build output based on quote-first setting
	if (!$putQuotesFirst) {
		// Author first
		$output .= $beforeAll;
		
		if (!empty($author)) {
			$output .= $beforeAuthor . $Author . $afterAuthor;
			if (!empty($source)) {
				$output .= $beforeSource . $Source . $afterSource;
			}
		} else {
			if (!empty($source)) {
				$output .= $ifnoauthor . $Source . $afterSource;
			}
		}
		
		$output .= $beforeQuote . nl2br($quote_text) . $afterQuote;
		$output .= $afterAll;
	} else {
		// Quote first
		$output .= $beforeAll;
		$output .= $beforeQuote . nl2br($quote_text) . $afterQuote;
		
		if (!empty($author)) {
			$output .= $beforeAuthor . $Author . $afterAuthor;
			if (!empty($source)) {
				$output .= $beforeSource . $Source . $afterSource;
			}
		} else {
			if (!empty($source)) {
				$output .= $ifnoauthor . $Source . $afterSource;
			}
		}
		
		$output .= $afterAll;
	}

	return $output;
}

/**
 * Builds pagination links for quote listings
 *
 * @param int $current_page Current page number
 * @param int $max_pages Maximum number of pages
 * @param int $rows Number of rows per page
 * @param bool $fullpage Whether to show full pagination with page numbers
 * @return string Pagination HTML
 */
function stray_build_pagination($current_page, $max_pages, $rows, $fullpage = true) {
	$pagination = '';
	
	if ($fullpage) {
		// Full pagination with page numbers
		$baseurl = remove_querystring_var($_SERVER['REQUEST_URI'], 'qp');
		$urlpages = strpos($baseurl, '?') !== false ? $baseurl . '&qp=' : $baseurl . '?qp=';
		
		// First and Previous links
		if ($current_page > 1) {
			$prev_page = $current_page - 1;
			$pagination .= ' <a href="' . $urlpages . '1">First</a> | ';
			$pagination .= ' <a href="' . $urlpages . $prev_page . '">Previous ' . $rows . '</a> | ';
		} else {
			$pagination .= '&nbsp; ';
		}
		
		// Page numbers
		for ($page = 1; $page <= $max_pages; $page++) {
			if ($page == $current_page) {
				$pagination .= $page . ' ';
			} else {
				$pagination .= ' <a href="' . $urlpages . $page . '">' . $page . '</a> ';
			}
		}
		
		// Next and Last links
		if ($current_page < $max_pages) {
			$next_page = $current_page + 1;
			$pagination .= ' | <a href="' . $urlpages . $next_page . '">Next</a> ';
			$pagination .= ' | <a href="' . $urlpages . $max_pages . '">Last</a> ';
		} else {
			$pagination .= '&nbsp;';
		}
	} else {
		// Simple pagination (Previous/Next only)
		$baseurl = remove_querystring_var($_SERVER['REQUEST_URI'], 'qmp');
		$urlpages = strpos($baseurl, '?') !== false ? $baseurl . '&qmp=' : $baseurl . '?qmp=';
		
		if ($current_page > 1) {
			$prev_page = $current_page - 1;
			$pagination .= '<a href="' . $urlpages . $prev_page . '">&laquo; Previous ' . $rows . '</a>&nbsp;|';
		} else {
			$pagination .= '&nbsp;';
		}
		
		if ($current_page < $max_pages) {
			$next_page = $current_page + 1;
			$pagination .= '&nbsp;<a href="' . $urlpages . $next_page . '">Next ' . $rows . ' &raquo;</a>';
		} else {
			$pagination .= '&nbsp;';
		}
	}
	
	return $pagination;
}

/**
 * Adds or replaces a variable in a querystring
 *
 * @param string $url The URL to modify
 * @param string $key The parameter key
 * @param string $value The parameter value
 * @return string Modified URL
 */
function querystrings($url, $key, $value) {
	$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
	$url = substr($url, 0, -1);
	if (strpos($url, '?') === false) {
		return ($url . '?' . $key . '=' . $value);
	} else {
		return ($url . '&' . $key . '=' . $value);
	}
}

/**
 * Removes a variable from a querystring
 *
 * @param string $url The URL to modify
 * @param string $key The parameter key to remove
 * @return string Modified URL
 */
function remove_querystring_var($url, $key) {
	$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
	$url = substr($url, 0, -1);
	return ($url);
}
