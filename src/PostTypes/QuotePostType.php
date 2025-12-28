<?php
/**
 * Quote Custom Post Type Registration
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\PostTypes;

/**
 * Class QuotePostType
 *
 * Handles registration of the xv_quote custom post type.
 */
class QuotePostType {

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	const POST_TYPE = 'xv_quote';

	/**
	 * Initialize the custom post type
	 */
	public function init() {
		add_action( 'init', array( $this, 'register' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_custom_column' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'make_sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'sort_by_custom_column' ) );
	}

	/**
	 * Register the custom post type
	 */
	public function register() {
		$labels = array(
			'name'                  => _x( 'Quotes', 'Post type general name', 'stray-quotes' ),
			'singular_name'         => _x( 'Quote', 'Post type singular name', 'stray-quotes' ),
			'menu_name'             => _x( 'Quotes', 'Admin Menu text', 'stray-quotes' ),
			'name_admin_bar'        => _x( 'Quote', 'Add New on Toolbar', 'stray-quotes' ),
			'add_new'               => __( 'Add New', 'stray-quotes' ),
			'add_new_item'          => __( 'Add New Quote', 'stray-quotes' ),
			'new_item'              => __( 'New Quote', 'stray-quotes' ),
			'edit_item'             => __( 'Edit Quote', 'stray-quotes' ),
			'view_item'             => __( 'View Quote', 'stray-quotes' ),
			'all_items'             => __( 'All Quotes', 'stray-quotes' ),
			'search_items'          => __( 'Search Quotes', 'stray-quotes' ),
			'parent_item_colon'     => __( 'Parent Quotes:', 'stray-quotes' ),
			'not_found'             => __( 'No quotes found.', 'stray-quotes' ),
			'not_found_in_trash'    => __( 'No quotes found in Trash.', 'stray-quotes' ),
			'featured_image'        => _x( 'Quote Cover Image', 'Overrides the "Featured Image" phrase', 'stray-quotes' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'stray-quotes' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'stray-quotes' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'stray-quotes' ),
			'archives'              => _x( 'Quote archives', 'The post type archive label used in nav menus', 'stray-quotes' ),
			'insert_into_item'      => _x( 'Insert into quote', 'Overrides the "Insert into post" phrase', 'stray-quotes' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this quote', 'Overrides the "Uploaded to this post" phrase', 'stray-quotes' ),
			'filter_items_list'     => _x( 'Filter quotes list', 'Screen reader text for the filter links', 'stray-quotes' ),
			'items_list_navigation' => _x( 'Quotes list navigation', 'Screen reader text for the pagination', 'stray-quotes' ),
			'items_list'            => _x( 'Quotes list', 'Screen reader text for the items list', 'stray-quotes' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-format-quote',
			'supports'           => array( 'title', 'author', 'revisions', 'custom-fields' ),
			'show_in_rest'       => true,
			'rest_base'          => 'quotes',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Add custom columns to the post type list table
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_custom_columns( $columns ) {
		// Insert source column after author (taxonomy)
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'taxonomy-quote_author' === $key ) {
				$new_columns['quote_source'] = __( 'Quote Source', 'stray-quotes' );
			}
		}
		return $new_columns;
	}

	/**
	 * Render custom column content
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_custom_column( $column, $post_id ) {
		if ( 'quote_source' === $column ) {
			$source = get_post_meta( $post_id, '_quote_source', true );
			if ( $source ) {
				// Display the source with HTML rendered
				echo wp_kses_post( $source );
			} else {
				echo '<span class="na">—</span>';
			}
		}
	}

	/**
	 * Make custom columns sortable
	 *
	 * @param array $columns Sortable columns.
	 * @return array Modified sortable columns.
	 */
	public function make_sortable_columns( $columns ) {
		$columns['quote_source'] = 'quote_source';
		return $columns;
	}

	/**
	 * Handle sorting for custom columns
	 *
	 * @param WP_Query $query The WordPress query.
	 */
	public function sort_by_custom_column( $query ) {
		// Only modify admin queries for our post type
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// Check if we're on the right post type
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}

		// Check if sorting by our custom column
		$orderby = $query->get( 'orderby' );
		if ( 'quote_source' === $orderby ) {
			$query->set( 'meta_key', '_quote_source' );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}
