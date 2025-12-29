<?php
/**
 * Shortcode Helper Utilities
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Utils;

/**
 * Class ShortcodeHelpers
 *
 * Utilities for processing shortcode attributes.
 */
class ShortcodeHelpers {

	/**
	 * Sanitize shortcode attributes
	 *
	 * @param array $atts Raw shortcode attributes.
	 * @param array $defaults Default values for attributes.
	 * @return array Sanitized attributes.
	 */
	public static function sanitize_attributes( $atts, $defaults ) {
		// Extract attributes with defaults
		$atts = shortcode_atts( $defaults, $atts );
		$sanitized = array();

		// Handle numeric parameters
		if ( isset( $atts['rows'] ) ) {
			$sanitized['rows'] = absint( $atts['rows'] );
			if ( $sanitized['rows'] <= 0 ) {
				$sanitized['rows'] = 10;
			}
		}

		if ( isset( $atts['multi'] ) ) {
			$sanitized['multi'] = absint( $atts['multi'] );
			if ( $sanitized['multi'] <= 0 ) {
				$sanitized['multi'] = 1;
			}
		}

		if ( isset( $atts['offset'] ) ) {
			$sanitized['offset'] = absint( $atts['offset'] );
		}

		if ( isset( $atts['id'] ) ) {
			$sanitized['id'] = absint( $atts['id'] );
			if ( $sanitized['id'] <= 0 ) {
				$sanitized['id'] = 1;
			}
		}

		// Handle boolean parameters
		if ( isset( $atts['sequence'] ) ) {
			$sanitized['sequence'] = filter_var( $atts['sequence'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $atts['fullpage'] ) ) {
			$sanitized['fullpage'] = filter_var( $atts['fullpage'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $atts['disableaspect'] ) ) {
			$sanitized['disableaspect'] = filter_var( $atts['disableaspect'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $atts['noajax'] ) ) {
			$sanitized['noajax'] = filter_var( $atts['noajax'], FILTER_VALIDATE_BOOLEAN );
		}

		// Pass through other parameters unchanged
		$passthrough = array( 'categories', 'linkphrase', 'widgetid', 'timer', 'user', 'orderby', 'sort' );
		foreach ( $passthrough as $key ) {
			if ( isset( $atts[ $key ] ) ) {
				$sanitized[ $key ] = $atts[ $key ];
			}
		}

		// Ensure all default keys are present in the output
		foreach ( $defaults as $key => $default_value ) {
			if ( ! isset( $sanitized[ $key ] ) ) {
				$sanitized[ $key ] = $default_value;
			}
		}

		return $sanitized;
	}
}
