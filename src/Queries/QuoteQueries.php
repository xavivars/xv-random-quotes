<?php
/**
 * Quote Query Helper Functions
 *
 * Provides WordPress-native WP_Query-based methods for querying quotes.
 * Replaces legacy raw SQL queries with proper WordPress query methods.
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Queries;

use WP_Query;
use WP_Post;

/**
 * Class QuoteQueries
 *
 * Helper class for querying xv_quote custom post type using WP_Query.
 */
class QuoteQueries {

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	const POST_TYPE = 'xv_quote';

	/**
	 * Get a random quote
	 *
	 * @param array $args Optional WP_Query arguments to override defaults.
	 * @return WP_Post|null Random quote post object or null if none found.
	 */
	public function get_random_quote( $args = array() ) {
		$defaults = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => 1,
			'orderby'        => 'rand',
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		);

		$args = wp_parse_args( $args, $defaults );

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			return $query->posts[0];
		}

		return null;
	}

	/**
	 * Get a quote by post ID
	 *
	 * @param int $id Post ID.
	 * @return WP_Post|null Quote post object or null if not found or wrong post type.
	 */
	public function get_quote_by_id( $id ) {
		$post = get_post( $id );

		// Verify post exists and is the correct post type
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return null;
		}

		return $post;
	}

	/**
	 * Get a quote by legacy ID (from migration)
	 *
	 * @param int $legacy_id Legacy quote ID from old database table.
	 * @return WP_Post|null Quote post object or null if not found.
	 */
	public function get_quote_by_legacy_id( $legacy_id ) {
		$default_args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'     => '_quote_legacy_id',
					'value'   => $legacy_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
			),
		);

		$query = new WP_Query( $default_args );

		if ( ! $query->have_posts() ) {
			return null;
		}

		return $query->posts[0];
	}

	/**
	 * Get quotes by author taxonomy term
	 *
	 * @param string $author_slug Author term slug.
	 * @param array  $args Optional WP_Query arguments to override defaults.
	 * @return array Array of quote post objects.
	 */
	public function get_quotes_by_author( $author_slug, $args = array() ) {
		$defaults = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'quote_author',
					'field'    => 'slug',
					'terms'    => $author_slug,
				),
			),
		);

		$args = wp_parse_args( $args, $defaults );

		// Merge tax_query if custom args provided
		if ( isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
			$args['tax_query'] = array_merge( $defaults['tax_query'], $args['tax_query'] );
		}

		$query = new WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Get quotes by category taxonomy term
	 *
	 * @param string $category_slug Category term slug.
	 * @param array  $args Optional WP_Query arguments to override defaults.
	 * @return array Array of quote post objects.
	 */
	public function get_quotes_by_category( $category_slug, $args = array() ) {
		$defaults = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'quote_category',
					'field'    => 'slug',
					'terms'    => $category_slug,
				),
			),
		);

		$args = wp_parse_args( $args, $defaults );

		// Merge tax_query if custom args provided
		if ( isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
			$args['tax_query'] = array_merge( $defaults['tax_query'], $args['tax_query'] );
		}

		$query = new WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Get all quotes
	 *
	 * @param array $args Optional WP_Query arguments to override defaults.
	 * @return array Array of quote post objects.
	 */
	public function get_all_quotes( $args = array() ) {
		$defaults = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$query = new WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Get quotes filtered by multiple categories
	 *
	 * @param array $category_slugs Array of category term slugs.
	 * @param array $args Optional WP_Query arguments to override defaults.
	 * @return array Array of quote post objects.
	 */
	public function get_quotes_by_categories( $category_slugs, $args = array() ) {
		// If empty array or single category, use existing method
		if ( empty( $category_slugs ) ) {
			return $this->get_all_quotes( $args );
		}

		if ( count( $category_slugs ) === 1 ) {
			return $this->get_quotes_by_category( $category_slugs[0], $args );
		}

		$defaults = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'quote_category',
					'field'    => 'slug',
					'terms'    => $category_slugs,
					'operator' => 'IN',
				),
			),
		);

		$args = wp_parse_args( $args, $defaults );

		// Merge tax_query if custom args provided
		if ( isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
			$args['tax_query'] = array_merge( $defaults['tax_query'], $args['tax_query'] );
		}

		$query = new WP_Query( $args );

		return $query->posts;
	}
}
