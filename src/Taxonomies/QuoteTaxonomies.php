<?php
/**
 * Quote Taxonomies Registration
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Taxonomies;

/**
 * Class QuoteTaxonomies
 *
 * Handles registration of quote_category and quote_author taxonomies.
 */
class QuoteTaxonomies {

	/**
	 * Category taxonomy slug
	 *
	 * @var string
	 */
	const CATEGORY_TAXONOMY = 'quote_category';

	/**
	 * Author taxonomy slug
	 *
	 * @var string
	 */
	const AUTHOR_TAXONOMY = 'quote_author';

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	const POST_TYPE = 'xv_quote';

	/**
	 * Initialize the taxonomies
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_category_taxonomy' ) );
		add_action( 'init', array( $this, 'register_author_taxonomy' ) );
	}

	/**
	 * Register the quote_category taxonomy (hierarchical)
	 */
	public function register_category_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Quote Categories', 'taxonomy general name', 'xv-random-quotes' ),
			'singular_name'              => _x( 'Quote Category', 'taxonomy singular name', 'xv-random-quotes' ),
			'search_items'               => __( 'Search Quote Categories', 'xv-random-quotes' ),
			'popular_items'              => __( 'Popular Quote Categories', 'xv-random-quotes' ),
			'all_items'                  => __( 'All Quote Categories', 'xv-random-quotes' ),
			'parent_item'                => __( 'Parent Quote Category', 'xv-random-quotes' ),
			'parent_item_colon'          => __( 'Parent Quote Category:', 'xv-random-quotes' ),
			'edit_item'                  => __( 'Edit Quote Category', 'xv-random-quotes' ),
			'update_item'                => __( 'Update Quote Category', 'xv-random-quotes' ),
			'add_new_item'               => __( 'Add New Quote Category', 'xv-random-quotes' ),
			'new_item_name'              => __( 'New Quote Category Name', 'xv-random-quotes' ),
			'separate_items_with_commas' => __( 'Separate quote categories with commas', 'xv-random-quotes' ),
			'add_or_remove_items'        => __( 'Add or remove quote categories', 'xv-random-quotes' ),
			'choose_from_most_used'      => __( 'Choose from the most used quote categories', 'xv-random-quotes' ),
			'not_found'                  => __( 'No quote categories found.', 'xv-random-quotes' ),
			'menu_name'                  => __( 'Categories', 'xv-random-quotes' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rest_base'         => 'quote-categories',
			'rewrite'           => false,
		);

		register_taxonomy( self::CATEGORY_TAXONOMY, array( self::POST_TYPE ), $args );
	}

	/**
	 * Register the quote_author taxonomy (non-hierarchical)
	 */
	public function register_author_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Quote Authors', 'taxonomy general name', 'xv-random-quotes' ),
			'singular_name'              => _x( 'Quote Author', 'taxonomy singular name', 'xv-random-quotes' ),
			'search_items'               => __( 'Search Quote Authors', 'xv-random-quotes' ),
			'popular_items'              => __( 'Popular Quote Authors', 'xv-random-quotes' ),
			'all_items'                  => __( 'All Quote Authors', 'xv-random-quotes' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Quote Author', 'xv-random-quotes' ),
			'update_item'                => __( 'Update Quote Author', 'xv-random-quotes' ),
			'add_new_item'               => __( 'Add New Quote Author', 'xv-random-quotes' ),
			'new_item_name'              => __( 'New Quote Author Name', 'xv-random-quotes' ),
			'separate_items_with_commas' => __( 'Separate quote authors with commas', 'xv-random-quotes' ),
			'add_or_remove_items'        => __( 'Add or remove quote authors', 'xv-random-quotes' ),
			'choose_from_most_used'      => __( 'Choose from the most used quote authors', 'xv-random-quotes' ),
			'not_found'                  => __( 'No quote authors found.', 'xv-random-quotes' ),
			'menu_name'                  => __( 'Authors', 'xv-random-quotes' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => true,
			'show_in_rest'      => true,
			'rest_base'         => 'quote-authors',
			'rewrite'           => false,
		);

		register_taxonomy( self::AUTHOR_TAXONOMY, array( self::POST_TYPE ), $args );

		// Register term meta for author URLs
		register_term_meta(
			self::AUTHOR_TAXONOMY,
			'author_url',
			array(
				'type'              => 'string',
				'description'       => __( 'URL for the quote author', 'xv-random-quotes' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'esc_url_raw',
			)
		);
	}
}
