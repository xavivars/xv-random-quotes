<?php
/**
 * Meta Boxes for Quote Post Type
 *
 * Provides custom meta boxes for the Quote post type.
 * Uses meta boxes with wp_editor() for strict HTML control instead of the standard editor.
 *
 * @package    XVRandomQuotes
 * @subpackage Admin
 * @since      2.0.0
 */

namespace XVRandomQuotes\Admin;

/**
 * Meta Boxes Class
 *
 * Handles registration and saving of meta boxes for the xv_quote post type.
 * Provides a custom editor for quote content with strict HTML restrictions.
 *
 * @since 2.0.0
 */
class MetaBoxes {

	/**
	 * Initialize meta boxes
	 *
	 * @since 2.0.0
	 */
	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_xv_quote', array( $this, 'save_all_meta_boxes' ), 10, 2 );
		add_action( 'save_post_xv_quote', array( $this, 'apply_default_category' ), 20, 1 );
	}

	/**
	 * Get allowed HTML tags for quote content sanitization
	 *
	 * Returns array of allowed inline formatting tags only.
	 *
	 * @since 2.0.0
	 * @return array Allowed HTML tags and their attributes
	 */
	private function get_allowed_html_tags() {
		return array(
			'strong' => array(),
			'em'     => array(),
			'b'      => array(),
			'i'      => array(),
			'code'   => array(),
			'abbr'   => array( 'title' => true ),
			'cite'   => array(),
			'q'      => array(),
			'mark'   => array(),
			'sub'    => array(),
			'sup'    => array(),
			'a'      => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
				'rel'    => true,
			),
		);
	}

	/**
	 * Get content editor settings
	 *
	 * Returns wp_editor settings for the quote content meta box.
	 *
	 * @since 2.0.0
	 * @return array Editor settings
	 */
	private function get_content_editor_settings() {
		return $this->get_editor_settings( array(
			'teeny'         => false,
			'textarea_rows' => 10,
			'media_buttons' => true,
			'quicktags'     => true,
		) );
	}

	/**
	 * Get wp_editor settings
	 *
	 * Returns standard editor settings with optional configuration.
	 *
	 * @since 2.0.0
	 * @param array $options Editor options to override defaults.
	 * @return array Editor settings
	 */
	private function get_editor_settings( $options = array() ) {
		$defaults = array(
			'media_buttons' => false,          // No "Add Media" button
			'quicktags'     => false,          // No HTML/Text tab
			'teeny'         => true,           // Minimal editor
			'textarea_rows' => 1,	 		   // Single line textarea
			'tinymce'       => array(
				'toolbar1' => 'bold,italic,link,unlink',  // Only these buttons
				'toolbar2' => '',                         // No second toolbar
				'toolbar3' => '',                         // No third toolbar
			),
		);
		
		return array_merge( $defaults, $options );
	}

	/**
	 * Register meta boxes for the quote post type
	 *
	 * Registers both the quote content meta box (main editor area) and
	 * the quote source meta box. Works with both Classic and Block editors.
	 *
	 * @since 2.0.0
	 */
	public function register_meta_boxes() {
		// Remove the default Custom Fields meta box
		remove_meta_box( 'postcustom', 'xv_quote', 'normal' );

		// Quote content meta box - main editor area (replaces standard editor)
		add_meta_box(
			'xv_quote_text',
			__( 'Quote Text', 'xv-random-quotes' ),
			array( $this, 'render_quote_content_meta_box' ),
			'xv_quote',
			'normal',
			'high'
		);

		// Quote source meta box - below quote content (both editors)
		add_meta_box(
			'xv_quote_source',
			__( 'Quote Source', 'xv-random-quotes' ),
			array( $this, 'render_quote_source_meta_box' ),
			'xv_quote',
			'normal',
			'default'
		);
	}

	/**
	 * Render the quote content meta box
	 *
	 * Provides a custom editor for quote text with strict HTML restrictions.
	 * Only allows inline formatting tags (bold, italic, link).
	 *
	 * @since 2.0.0
	 * @param \WP_Post $post The post object.
	 */
	public function render_quote_content_meta_box( $post ) {
		wp_nonce_field( 'xv_quote_content_save', 'xv_quote_content_nonce' );

		echo '<div class="xv-quote-content-editor">';
		wp_editor( $post->post_content, 'xv_quote_content', $this->get_content_editor_settings() );
		echo '</div>';
		echo '<p class="description">';
		echo esc_html__( 'Enter the quote text. Only basic formatting (bold, italic, links) is allowed.', 'xv-random-quotes' );
		echo '</p>';
	}

	/**
	 * Render the quote source meta box
	 *
	 * Provides a custom editor for quote source with strict HTML restrictions.
	 * Only allows inline formatting tags (bold, italic, link).
	 *
	 * @since 2.0.0
	 * @param \WP_Post $post The post object.
	 */
	public function render_quote_source_meta_box( $post ) {
		wp_nonce_field( 'xv_quote_source_save', 'xv_quote_source_nonce' );

		$source = get_post_meta( $post->ID, '_quote_source', true );

		echo '<div class="xv-quote-source-editor">';
		wp_editor( $source, 'quote_source', $this->get_editor_settings( ) );
		echo '</div>';
		echo '<p class="description">';
		echo esc_html__( 'Enter the quote source. Only basic formatting (bold, italic, links) is allowed.', 'xv-random-quotes' );
		echo '</p>';
	}

	/**
	 * Save all meta boxes
	 *
	 * Unified save handler for both quote content and source meta boxes.
	 *
	 * @since 2.0.0
	 * @param int      $post_id The post ID.
	 * @param \WP_Post $post    The post object.
	 */
	public function save_all_meta_boxes( $post_id, $post ) {
		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user capability
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save quote content to post_content
		if ( isset( $_POST['xv_quote_content_nonce'] ) && 
		     wp_verify_nonce( $_POST['xv_quote_content_nonce'], 'xv_quote_content_save' ) &&
		     isset( $_POST['xv_quote_content'] ) ) {
			
			$content = wp_kses_post( $_POST['xv_quote_content'] );
			
			// Unhook this function to prevent infinite loop
			remove_action( 'save_post_xv_quote', array( $this, 'save_all_meta_boxes' ), 10 );
			
			// Update post using wp_update_post
			wp_update_post( array(
				'ID'           => $post_id,
				'post_content' => $content,
			) );
			
			// Re-hook this function
			add_action( 'save_post_xv_quote', array( $this, 'save_all_meta_boxes' ), 10, 2 );
		}

		// Save quote source to post meta
		if ( isset( $_POST['xv_quote_source_nonce'] ) && 
		     wp_verify_nonce( $_POST['xv_quote_source_nonce'], 'xv_quote_source_save' ) &&
		     isset( $_POST['quote_source'] ) ) {
			
			$source = wp_kses( $_POST['quote_source'], $this->get_allowed_html_tags() );
			update_post_meta( $post_id, '_quote_source', $source );
		}
	}

	/**
	 * Apply default category to quotes without any category assigned
	 *
	 * Automatically assigns the default category term (from v1.x migration)
	 * to quotes that don't have any quote_category term assigned.
	 *
	 * @since 2.0.0
	 * @param int $post_id The post ID.
	 */
	public function apply_default_category( $post_id ) {
		// Only apply to xv_quote post type
		if ( get_post_type( $post_id ) !== 'xv_quote' ) {
			return;
		}

		// Check if post has any quote_category terms assigned
		$terms = wp_get_post_terms( $post_id, 'quote_category', array( 'fields' => 'ids' ) );

		// If no terms assigned, try to assign the default
		if ( empty( $terms ) ) {
			$default_term_id = (int) get_option( 'xv_quotes_default_category_id', 0 );

			if ( $default_term_id > 0 ) {
				// Verify the term exists
				if ( get_term( $default_term_id, 'quote_category' ) ) {
					wp_set_post_terms( $post_id, array( $default_term_id ), 'quote_category', false );
				}
			}
		}
	}
}

