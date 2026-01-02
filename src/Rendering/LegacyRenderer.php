<?php
/**
 * Legacy Custom HTML Wrapper Renderer
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Rendering;

use XVRandomQuotes\Admin\Settings;

/**
 * Class LegacyRenderer
 *
 * Renders quotes using custom HTML wrappers for backward compatibility.
 */
class LegacyRenderer {

	/**
	 * Render a quote using custom HTML wrappers
	 *
	 * @param \WP_Post $post The quote post object.
	 * @param string $quote_text The quote text content.
	 * @param string $author The quote author name.
	 * @param string $source The quote source.
	 * @param string $author_url Optional author URL.
	 * @param bool $is_multi Whether this is part of a multi-quote display.
	 * @param bool $disable_aspect Whether to disable formatting.
	 * @return string Formatted quote HTML with custom wrappers.
	 */
	public function render( $post, $quote_text, $author, $source, $author_url = '', $is_multi = false, $disable_aspect = false ) {
		if ( empty( $quote_text ) ) {
			return '';
		}

		// Get wrapper settings
		$wrappers = $this->get_wrappers( $disable_aspect );

		// Format author with link if needed
		$formatted_author = $this->format_author( $author, $author_url, $wrappers );

		// Format source with link if needed
		$formatted_source = $this->format_source( $source, $wrappers );

		// Build output based on quote-first setting
		$put_quotes_first = get_option( Settings::OPTION_PUT_QUOTES_FIRST, false );

		$output = '';

		if ( ! $put_quotes_first ) {
			// Default order: author, source, then quote
			if ( ! empty( $formatted_author ) ) {
				$output .= wp_kses_post( $wrappers['before_author'] ) . $formatted_author . wp_kses_post( $wrappers['after_author'] );
			}

			if ( ! empty( $formatted_source ) ) {
				if ( ! empty( $formatted_author ) ) {
					$output .= wp_kses_post( $wrappers['before_source'] ) . $formatted_source . wp_kses_post( $wrappers['after_source'] );
				} else {
					$output .= wp_kses_post( $wrappers['if_no_author'] ) . $formatted_source . wp_kses_post( $wrappers['after_source'] );
				}
			}

			$output .= wp_kses_post( $wrappers['before_quote'] ) . wp_kses_post( $quote_text ) . wp_kses_post( $wrappers['after_quote'] );
		} else {
			// Alternate order: quote first, then author and source
			$output .= wp_kses_post( $wrappers['before_quote'] ) . wp_kses_post( $quote_text ) . wp_kses_post( $wrappers['after_quote'] );

			if ( ! empty( $formatted_author ) ) {
				$output .= wp_kses_post( $wrappers['before_author'] ) . $formatted_author . wp_kses_post( $wrappers['after_author'] );
			}

			if ( ! empty( $formatted_source ) ) {
				if ( ! empty( $formatted_author ) ) {
					$output .= wp_kses_post( $wrappers['before_source'] ) . $formatted_source . wp_kses_post( $wrappers['after_source'] );
				} else {
					$output .= wp_kses_post( $wrappers['if_no_author'] ) . $formatted_source . wp_kses_post( $wrappers['after_source'] );
				}
			}
		}

		// Only wrap with before_all/after_all when rendering a single quote
		// For multiple quotes, these wrappers are applied in render_multiple_quotes()
		if ( ! $disable_aspect && ! $is_multi ) {
			$output = wp_kses_post( $wrappers['before_all'] ) . $output . wp_kses_post( $wrappers['after_all'] );
		}

		return $output;
	}

	/**
	 * Get HTML wrapper settings
	 *
	 * @param bool $disable_aspect Whether wrappers are disabled.
	 * @return array Wrapper settings.
	 */
	public function get_wrappers( $disable_aspect ) {
		if ( $disable_aspect ) {
			return array(
				'before_all'    => '',
				'after_all'     => '',
				'before_quote'  => '',
				'after_quote'   => ' ',
				'before_author' => '',
				'after_author'  => ' ',
				'before_source' => '',
				'after_source'  => '',
				'if_no_author'  => '',
			);
		}

		return array(
			'before_all'    => get_option( Settings::OPTION_BEFORE_ALL, '' ),
			'after_all'     => get_option( Settings::OPTION_AFTER_ALL, '' ),
			'before_quote'  => get_option( Settings::OPTION_BEFORE_QUOTE, '' ),
			'after_quote'   => get_option( Settings::OPTION_AFTER_QUOTE, '' ),
			'before_author' => get_option( Settings::OPTION_BEFORE_AUTHOR, '' ),
			'after_author'  => get_option( Settings::OPTION_AFTER_AUTHOR, '' ),
			'before_source' => get_option( Settings::OPTION_BEFORE_SOURCE, '' ),
			'after_source'  => get_option( Settings::OPTION_AFTER_SOURCE, '' ),
			'if_no_author'  => get_option( Settings::OPTION_IF_NO_AUTHOR, '' ),
		);
	}

	/**
	 * Format author with link if needed
	 *
	 * @param string $author Author name.
	 * @param string $author_url Author URL.
	 * @param array $wrappers Wrapper settings.
	 * @return string Formatted author.
	 */
	private function format_author( $author, $author_url, $wrappers ) {
		if ( empty( $author ) ) {
			return '';
		}

		$linkto = get_option( Settings::OPTION_LINKTO, '' );
		$authorspaces = get_option( Settings::OPTION_AUTHORSPACES, '' );

		// Priority 1: Use author URL from term meta (migrated from legacy data or manually set)
		if ( ! empty( $author_url ) ) {
			return '<a href="' . esc_url( $author_url ) . '">' . esc_html( $author ) . '</a>';
		}

		// Priority 2: Use settings-based link pattern (if author doesn't already contain a link)
		if ( $linkto && ! preg_match( "/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i", $author ) ) {
			$processed_author = $author;
			if ( $authorspaces ) {
				$processed_author = str_replace( ' ', $authorspaces, $author );
			}
			$search = array( '"', '&', '%AUTHOR%' );
			$replace = array( '%22', '&amp;', $processed_author );
			$author_linkto = str_replace( $search, $replace, $linkto );
			return '<a href="' . esc_url( $author_linkto ) . '">' . esc_html( $author ) . '</a>';
		}

		return esc_html( $author );
	}

	/**
	 * Format source with link if needed
	 *
	 * @param string $source Source text.
	 * @param array $wrappers Wrapper settings.
	 * @return string Formatted source.
	 */
	private function format_source( $source, $wrappers ) {
		if ( empty( $source ) ) {
			return '';
		}

		$sourcelinkto = get_option( Settings::OPTION_SOURCELINKTO, '' );
		$sourcespaces = get_option( Settings::OPTION_SOURCESPACES, '' );

		// Format source with link if needed
		if ( $sourcelinkto && ! preg_match( "/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i", $source ) ) {
			$processed_source = $source;
			if ( $sourcespaces ) {
				$processed_source = str_replace( ' ', $sourcespaces, $source );
			}
			$search = array( '"', '&', '%SOURCE%' );
			$replace = array( '%22', '&amp;', $processed_source );
			$source_linkto = str_replace( $search, $replace, $sourcelinkto );
			return '<a href="' . esc_url( $source_linkto ) . '">' . wp_kses_post( $source ) . '</a>';
		}

		return wp_kses_post( $source );
	}

	/**
	 * Render loader/pagination wrapper HTML
	 *
	 * @param bool $disable_aspect Whether wrappers are disabled.
	 * @return array Array with 'before' and 'after' keys.
	 */
	public function get_loader_wrapper( $disable_aspect ) {
		if ( $disable_aspect ) {
			return array( 'before' => '', 'after' => '' );
		}

		return array(
			'before' => get_option( Settings::OPTION_BEFORE_LOADER, '' ),
			'after'  => get_option( Settings::OPTION_AFTER_LOADER, '' ),
		);
	}

	/**
	 * Render loader link for AJAX refresh
	 *
	 * @param bool $disable_ajax Whether AJAX is disabled.
	 * @param string $link_text Custom link text (uses settings default if empty).
	 * @param bool $disable_aspect Whether wrappers are disabled.
	 * @return string Loader link HTML or empty string if AJAX disabled.
	 */
	public function render_loader_link( $disable_ajax = false, $link_text = '', $disable_aspect = false ) {
		// Don't render link if AJAX is disabled
		if ( $disable_ajax ) {
			return '';
		}

		// Use provided link text or fall back to settings
		if ( empty( $link_text ) ) {
			$link_text = get_option( Settings::OPTION_LOADER, '' );
		}

		// Don't render link if no text is available
		if ( empty( $link_text ) ) {
			return '';
		}

		$wrapper = $this->get_loader_wrapper( $disable_aspect );

		return wp_kses_post( $wrapper['before'] ) . '<a href="#" class="xv-quote-refresh">' . wp_kses_post( $link_text ) . '</a>' . wp_kses_post( $wrapper['after'] );
	}
}
