<?php
/**
 * Quote Post Meta Fields Registration
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\PostMeta;

/**
 * Class QuoteMetaFields
 *
 * Handles registration of custom post meta fields for quotes.
 */
class QuoteMetaFields {

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	const POST_TYPE = 'xv_quote';

	/**
	 * Initialize the post meta fields
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_meta_fields' ) );
	}

	/**
	 * Register all quote post meta fields
	 */
	public function register_meta_fields() {
		// Quote source field
		register_post_meta(
			self::POST_TYPE,
			'_quote_source',
			array(
				'type'              => 'string',
				'description'       => __( 'Source or citation for the quote', 'xv-random-quotes' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'wp_kses_post',
				'auth_callback'     => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		// Legacy ID field for migration tracking
		register_post_meta(
			self::POST_TYPE,
			'_quote_legacy_id',
			array(
				'type'              => 'integer',
				'description'       => __( 'Original quote ID from pre-v2.0 database table', 'xv-random-quotes' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Display order field
		register_post_meta(
			self::POST_TYPE,
			'_quote_display_order',
			array(
				'type'              => 'integer',
				'description'       => __( 'Custom display order for the quote', 'xv-random-quotes' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
