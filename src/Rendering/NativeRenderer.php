<?php
/**
 * Native WordPress Quote Block Renderer
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Rendering;

use XVRandomQuotes\Admin\Settings;

/**
 * Class NativeRenderer
 *
 * Renders quotes using native WordPress quote block format.
 */
class NativeRenderer {

	/**
	 * Render a quote in native WordPress block format
	 *
	 * @param string $quote_text The quote text content.
	 * @param string $author The quote author name.
	 * @param string $source The quote source.
	 * @param string $author_url Optional author URL.
	 * @return string Formatted quote HTML in native block format.
	 */
	public function render( $quote_text, $author, $source, $author_url = '' ) {
		if ( empty( $quote_text ) ) {
			return '';
		}

		$output = '<blockquote class="wp-block-quote has-text-align-right is-style-default">' . "\n";
		$output .= '  <p class="has-text-align-left">' . wp_kses_post( $quote_text ) . '</p>' . "\n";

		// Build citation
		if ( ! empty( $author ) || ! empty( $source ) ) {
			$output .= '  <cite>';

			if ( ! empty( $author ) ) {
				if ( ! empty( $author_url ) ) {
					$output .= '<a href="' . esc_url( $author_url ) . '">' . esc_html( $author ) . '</a>';
				} else {
					$output .= esc_html( $author );
				}
			}

			if ( ! empty( $author ) && ! empty( $source ) ) {
				$output .= '<br>';
			}

			if ( ! empty( $source ) ) {
				// Check if source contains a link pattern for sourcelinkto
				$sourcelinkto = get_option( Settings::OPTION_SOURCELINKTO, '' );
				if ( $sourcelinkto && ! preg_match( "/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i", $source ) ) {
					$sourcespaces = get_option( Settings::OPTION_SOURCESPACES, '' );
					$processed_source = $source;
					if ( $sourcespaces ) {
						$processed_source = str_replace( ' ', $sourcespaces, $source );
					}
					$search = array( '"', '&', '%SOURCE%' );
					$replace = array( '%22', '&amp;', $processed_source );
					$source_linkto = str_replace( $search, $replace, $sourcelinkto );
					$output .= '<a href="' . esc_url( $source_linkto ) . '">' . wp_kses_post( $source ) . '</a>';
				} else {
					$output .= wp_kses_post( $source );
				}
			}

			$output .= '</cite>' . "\n";
		}

		$output .= '</blockquote>';

		return $output;
	}
}
