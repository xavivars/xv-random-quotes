<?php
/**
 * Pagination Helper Utilities
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Utils;

/**
 * Class PaginationHelper
 *
 * Utilities for building pagination links.
 */
class PaginationHelper {

	/**
	 * Build pagination HTML
	 *
	 * @param int $current_page Current page number.
	 * @param int $max_pages Total number of pages.
	 * @param int $rows Number of rows per page.
	 * @param bool $fullpage Whether to show full pagination or simple prev/next.
	 * @return string Pagination HTML.
	 */
	public static function build_pagination( $current_page, $max_pages, $rows, $fullpage = true ) {
		$pagination = '';

		if ( $fullpage ) {
			// Full pagination with page numbers
			$baseurl = self::remove_querystring_var( $_SERVER['REQUEST_URI'], 'qp' );
			$urlpages = strpos( $baseurl, '?' ) !== false ? $baseurl . '&qp=' : $baseurl . '?qp=';

			// First and Previous links
			if ( $current_page > 1 ) {
				$prev_page = $current_page - 1;
				$pagination .= ' <a href="' . esc_url( $urlpages . '1' ) . '">First</a> | ';
				$pagination .= ' <a href="' . esc_url( $urlpages . $prev_page ) . '">Previous ' . intval( $rows ) . '</a> | ';
			} else {
				$pagination .= '&nbsp; ';
			}

			// Page numbers
			for ( $page = 1; $page <= $max_pages; $page++ ) {
				if ( $page == $current_page ) {
					$pagination .= $page . ' ';
				} else {
					$pagination .= ' <a href="' . esc_url( $urlpages . $page ) . '">' . intval( $page ) . '</a> ';
				}
			}

			// Next and Last links
			if ( $current_page < $max_pages ) {
				$next_page = $current_page + 1;
				$pagination .= ' | <a href="' . esc_url( $urlpages . $next_page ) . '">Next</a> ';
				$pagination .= ' | <a href="' . esc_url( $urlpages . $max_pages ) . '">Last</a> ';
			} else {
				$pagination .= '&nbsp;';
			}
		} else {
			// Simple pagination (Previous/Next only)
			$baseurl = self::remove_querystring_var( $_SERVER['REQUEST_URI'], 'qmp' );
			$urlpages = strpos( $baseurl, '?' ) !== false ? $baseurl . '&qmp=' : $baseurl . '?qmp=';

			if ( $current_page > 1 ) {
				$prev_page = $current_page - 1;
				$pagination .= '<a href="' . esc_url( $urlpages . $prev_page ) . '">&laquo; Previous ' . intval( $rows ) . '</a>&nbsp;|';
			} else {
				$pagination .= '&nbsp;';
			}

			if ( $current_page < $max_pages ) {
				$next_page = $current_page + 1;
				$pagination .= '&nbsp;<a href="' . esc_url( $urlpages . $next_page ) . '">Next ' . intval( $rows ) . ' &raquo;</a>';
			} else {
				$pagination .= '&nbsp;';
			}
		}

		return $pagination;
	}

	/**
	 * Add or replace a variable in a querystring
	 *
	 * @param string $url The URL to modify.
	 * @param string $key The parameter key.
	 * @param string $value The parameter value.
	 * @return string Modified URL.
	 */
	public static function add_querystring_var( $url, $key, $value ) {
		$url = preg_replace( '/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&' );
		$url = substr( $url, 0, -1 );

		if ( strpos( $url, '?' ) === false ) {
			return $url . '?' . $key . '=' . $value;
		} else {
			return $url . '&' . $key . '=' . $value;
		}
	}

	/**
	 * Remove a variable from a querystring
	 *
	 * @param string $url The URL to modify.
	 * @param string $key The parameter key to remove.
	 * @return string Modified URL.
	 */
	public static function remove_querystring_var( $url, $key ) {
		$url = preg_replace( '/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&' );
		$url = substr( $url, 0, -1 );
		return $url;
	}
}
