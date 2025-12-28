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

/**
 * Parse and sanitize category slugs from shortcode attribute
 *
 * @param string $categories Comma-separated category slugs
 * @return array Array of sanitized category slugs, empty if 'all' or empty
 */
function parse_category_slugs($categories) {
	$category_slugs = array();
	
	if ($categories && $categories !== 'all' && $categories !== '') {
		if (is_string($categories)) {
			$category_slugs = array_map('trim', explode(',', $categories));
			$category_slugs = array_map('sanitize_title', $category_slugs);
		}
	}
	
	return $category_slugs;
}

/**
 * Extract and sanitize shortcode attributes for quote display
 *
 * @param array $atts Raw shortcode attributes from user
 * @param array $defaults Default attribute values
 * @return array Sanitized attributes with proper types
 */
function stray_sanitize_shortcode_attributes($atts, $defaults) {
	// Extract attributes with defaults
	$atts = shortcode_atts($defaults, $atts);
	$sanitized = array();
	
	// Handle numeric parameters
	if (isset($atts['rows'])) {
		$sanitized['rows'] = absint($atts['rows']);
		if ($sanitized['rows'] <= 0) $sanitized['rows'] = 10;
	}
	
	if (isset($atts['multi'])) {
		$sanitized['multi'] = absint($atts['multi']);
		if ($sanitized['multi'] <= 0) $sanitized['multi'] = 1;
	}
	
	if (isset($atts['offset'])) {
		$sanitized['offset'] = absint($atts['offset']);
	}
	
	if (isset($atts['id'])) {
		$sanitized['id'] = absint($atts['id']);
		if ($sanitized['id'] <= 0) $sanitized['id'] = 1;
	}
	
	// Handle boolean parameters
	if (isset($atts['sequence'])) {
		$sanitized['sequence'] = filter_var($atts['sequence'], FILTER_VALIDATE_BOOLEAN);
	}
	
	if (isset($atts['fullpage'])) {
		$sanitized['fullpage'] = filter_var($atts['fullpage'], FILTER_VALIDATE_BOOLEAN);
	}
	
	if (isset($atts['disableaspect'])) {
		$sanitized['disableaspect'] = filter_var($atts['disableaspect'], FILTER_VALIDATE_BOOLEAN);
	}
	
	if (isset($atts['noajax'])) {
		$sanitized['noajax'] = filter_var($atts['noajax'], FILTER_VALIDATE_BOOLEAN);
	}
	
	// Pass through other parameters unchanged
	$passthrough = array('categories', 'linkphrase', 'widgetid', 'timer', 'user', 'orderby', 'sort');
	foreach ($passthrough as $key) {
		if (isset($atts[$key])) {
			$sanitized[$key] = $atts[$key];
		}
	}
	
	return $sanitized;
}

/**
 * Build query args for ordering quotes
 *
 * @param bool $sequence Whether to use sequential ordering
 * @param string $orderby Legacy orderby value (quoteID, author, source, etc)
 * @param string $sort Sort direction (ASC or DESC)
 * @return array Query args with orderby and order set
 */
function stray_build_order_args($sequence, $orderby = 'quoteID', $sort = 'ASC') {
	$args = array();
	
	if (!$sequence) {
		// Random order
		$args['orderby'] = 'rand';
	} else {
		// Sequential order - map legacy orderby values to CPT fields
		switch ($orderby) {
			case 'quoteID':
				$args['orderby'] = 'ID';
				break;
			case 'author':
			case 'Author':
				$args['orderby'] = 'title'; // Best approximation
				break;
			case 'source':
			case 'Source':
				$args['orderby'] = 'title';
				break;
			default:
				$args['orderby'] = 'ID';
		}
		$args['order'] = strtoupper($sort) === 'DESC' ? 'DESC' : 'ASC';
	}
	
	return $args;
}

/**
 * Get quotes with optional category filtering
 *
 * @param QuoteQueries $quote_queries QuoteQueries instance
 * @param array $category_slugs Array of category slugs to filter by
 * @param array $query_args WP_Query arguments
 * @return array Array of quote post objects
 */
function stray_get_filtered_quotes($quote_queries, $category_slugs, $query_args) {
	if (!empty($category_slugs)) {
		return $quote_queries->get_quotes_by_categories($category_slugs, $query_args);
	} else {
		return $quote_queries->get_all_quotes($query_args);
	}
}

/**
 * Get before/after wrapper HTML from settings
 *
 * @param bool $disableaspect Whether to disable aspect/wrapper HTML
 * @param string $type Type of wrapper: 'all' or 'loader'
 * @return array Array with 'before' and 'after' keys
 */
function stray_get_wrapper_html($disableaspect, $type = 'all') {
	$result = array('before' => '', 'after' => '');
	
	if ($disableaspect) {
		return $result;
	}
	
	$quotesoptions = get_option('stray_quotes_options');
	if (!$quotesoptions) {
		return $result;
	}
	
	if ($type === 'all') {
		$result['before'] = isset($quotesoptions['stray_quotes_before_all']) ? utf8_decode($quotesoptions['stray_quotes_before_all']) : '';
		$result['after'] = isset($quotesoptions['stray_quotes_after_all']) ? utf8_decode($quotesoptions['stray_quotes_after_all']) : '';
	} elseif ($type === 'loader') {
		$result['before'] = isset($quotesoptions['stray_before_loader']) ? utf8_decode($quotesoptions['stray_before_loader']) : '';
		$result['after'] = isset($quotesoptions['stray_after_loader']) ? utf8_decode($quotesoptions['stray_after_loader']) : '';
	}
	
	return $result;
}

/**
 * Build HTML output for multiple quotes
 *
 * @param array $quotes Array of quote post objects
 * @param bool $disableaspect Whether to disable aspect/wrapper HTML
 * @return string HTML output
 */
function stray_build_multi_quote_output($quotes, $disableaspect = false) {
	$wrapper = stray_get_wrapper_html($disableaspect, 'all');
	
	$output = $wrapper['before'] . '<ul>';
	foreach ($quotes as $quote_post) {
		$output .= '<li>' . stray_output_one_cpt($quote_post, true, $disableaspect) . '</li>';
	}
	$output .= '</ul>' . $wrapper['after'];
	
	return $output;
}
