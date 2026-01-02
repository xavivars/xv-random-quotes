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
			'name'                  => _x( 'Quotes', 'Post type general name', 'xv-random-quotes' ),
			'singular_name'         => _x( 'Quote', 'Post type singular name', 'xv-random-quotes' ),
			'menu_name'             => _x( 'Quotes', 'Admin Menu text', 'xv-random-quotes' ),
			'name_admin_bar'        => _x( 'Quote', 'Add New on Toolbar', 'xv-random-quotes' ),
			'add_new'               => __( 'Add New', 'xv-random-quotes' ),
			'add_new_item'          => __( 'Add New Quote', 'xv-random-quotes' ),
			'new_item'              => __( 'New Quote', 'xv-random-quotes' ),
			'edit_item'             => __( 'Edit Quote', 'xv-random-quotes' ),
			'view_item'             => __( 'View Quote', 'xv-random-quotes' ),
			'all_items'             => __( 'All Quotes', 'xv-random-quotes' ),
			'search_items'          => __( 'Search Quotes', 'xv-random-quotes' ),
			'parent_item_colon'     => __( 'Parent Quotes:', 'xv-random-quotes' ),
			'not_found'             => __( 'No quotes found.', 'xv-random-quotes' ),
			'not_found_in_trash'    => __( 'No quotes found in Trash.', 'xv-random-quotes' ),
			'featured_image'        => _x( 'Quote Cover Image', 'Overrides the "Featured Image" phrase', 'xv-random-quotes' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'xv-random-quotes' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'xv-random-quotes' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'xv-random-quotes' ),
			'archives'              => _x( 'Quote archives', 'The post type archive label used in nav menus', 'xv-random-quotes' ),
			'insert_into_item'      => _x( 'Insert into quote', 'Overrides the "Insert into post" phrase', 'xv-random-quotes' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this quote', 'Overrides the "Uploaded to this post" phrase', 'xv-random-quotes' ),
			'filter_items_list'     => _x( 'Filter quotes list', 'Screen reader text for the filter links', 'xv-random-quotes' ),
			'items_list_navigation' => _x( 'Quotes list navigation', 'Screen reader text for the pagination', 'xv-random-quotes' ),
			'items_list'            => _x( 'Quotes list', 'Screen reader text for the items list', 'xv-random-quotes' ),
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
		// Remove the default title column
		unset( $columns['title'] );
		
		// Insert custom quote column at the beginning (after checkbox)
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			if ( 'cb' === $key ) {
				$new_columns[ $key ] = $value;
				$new_columns['quote_preview'] = __( 'Quote', 'xv-random-quotes' );
			} else {
				$new_columns[ $key ] = $value;
			}
			
			// Insert source column after author (taxonomy)
			if ( 'taxonomy-quote_author' === $key ) {
				$new_columns['quote_source'] = __( 'Quote Source', 'xv-random-quotes' );
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
		if ( 'quote_preview' === $column ) {
			$post = get_post( $post_id );
			$title = $post->post_title;
			$content = wp_strip_all_tags( $post->post_content );
			
			$edit_link = get_edit_post_link( $post_id );
			
			// Start the preview link
			echo '<a class="row-title" href="' . esc_url( $edit_link ) . '" style="display: flex; align-items: center; gap: 10px;">';
			
			// Show image if available (featured image or first image in content)
			if ( has_post_thumbnail( $post_id ) ) {
				$first_image = get_the_post_thumbnail( $post_id, array( 50, 50 ), array( 'style' => 'display: block; flex-shrink: 0;' ) );
				echo wp_kses_post( $first_image );
			} elseif ( $first_image = $this->get_first_image_from_content( $post->post_content ) ) {
				$first_image = preg_replace( '/(width|height)="\d*"\s*/i', '', $first_image );
				$first_image = str_replace( '<img', '<img style="width:50px;height:50px;object-fit:cover;flex-shrink:0;"', $first_image );
				echo wp_kses_post( $first_image );
			}
			
			// Show text (title or content excerpt)
			if ( ! empty( trim( $title ) ) ) {
				echo '<strong>' . esc_html( $title ) . '</strong>';
			} elseif ( ! empty( trim( $content ) ) ) {
				echo '<span>' . esc_html( wp_trim_words( $content, 10 ) ) . '</span>';
			} elseif ( $this->get_alt_text( $first_image ) ) {
				echo '<span>' . esc_html( $this->get_alt_text( $first_image ) ) . '</span>';
			} else {
				echo '<span>' . esc_html__( '(no content)', 'xv-random-quotes' ) . '</span>';
			}
			
			echo '</a>';
		}
		
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
	 * Get alt text from an image HTML string
	 * 
	 * @param string $image_html Image HTML.
	 * @return string|null Alt text or null if not found.
	 */
	private function get_alt_text( $image_html ) {
		if ( preg_match( '/alt=["\']([^"\']*)["\']/', $image_html, $matches ) ) {
			return $matches[1];
		}
		return null;
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

	/**
	 * Get the first image tag from post content
	 *
	 * @param string $content Post content.
	 * @return string|null First image HTML or null if none found.
	 */
	private function get_first_image_from_content( $content ) {
		preg_match( '/<img[^>]+>/i', $content, $matches );
		return ! empty( $matches ) ? $matches[0] : null;
	}
}
