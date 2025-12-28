<?php
/**
 * Meta Boxes for Classic Editor
 *
 * Provides custom meta boxes for the Quote post type when using the Classic Editor.
 * The Block Editor uses the native sidebar for meta fields via register_post_meta.
 *
 * @package    XVRandomQuotes
 * @subpackage Admin
 * @since      2.0.0
 */

namespace XVRandomQuotes\Admin;

/**
 * Meta Boxes Class
 *
 * Handles registration and saving of meta boxes for the xv_quote post type
 * in the Classic Editor. Only registers when Classic Editor is active.
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
		add_action( 'save_post_xv_quote', array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Check if Classic Editor is active for the quote post type
	 *
	 * @since 2.0.0
	 * @return bool True if Classic Editor is active, false otherwise
	 */
	public function is_classic_editor_active() {
		// Check if the Block Editor is disabled for this post type
		if ( function_exists( 'use_block_editor_for_post_type' ) ) {
			return ! use_block_editor_for_post_type( 'xv_quote' );
		}

		// If function doesn't exist, assume Classic Editor
		return true;
	}

	/**
	 * Register meta boxes for the quote post type
	 *
	 * Only registers if Classic Editor is active. The Block Editor
	 * uses the native sidebar for meta fields.
	 *
	 * @since 2.0.0
	 */
	public function register_meta_boxes() {
		// Only register for Classic Editor
		if ( ! $this->is_classic_editor_active() ) {
			return;
		}

		add_meta_box(
			'xv_quote_source',
			__( 'Quote Source', 'stray-quotes' ),
			array( $this, 'render_meta_box' ),
			'xv_quote',
			'side',
			'default'
		);
	}

	/**
	 * Render the meta box content
	 *
	 * @since 2.0.0
	 * @param \WP_Post $post The post object.
	 */
	public function render_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'xv_quote_source', 'xv_quote_source_nonce' );

		// Get current source value
		$source = get_post_meta( $post->ID, '_quote_source', true );
		?>
		<p>
			<label for="quote_source"><?php esc_html_e( 'Source', 'stray-quotes' ); ?></label>
			<input type="text" id="quote_source" name="quote_source" value="<?php echo esc_attr( $source ); ?>" class="widefat" />
		</p>
		<?php
	}

	/**
	 * Save meta box data
	 *
	 * @since 2.0.0
	 * @param int      $post_id The post ID.
	 * @param \WP_Post $post    The post object.
	 */
	public function save_meta_box( $post_id, $post ) {
		// Verify nonce
		if ( ! isset( $_POST['xv_quote_source_nonce'] ) || ! wp_verify_nonce( $_POST['xv_quote_source_nonce'], 'xv_quote_source' ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user capability
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save source meta with HTML sanitization
		if ( isset( $_POST['quote_source'] ) ) {
			$source = wp_kses_post( $_POST['quote_source'] );
			update_post_meta( $post_id, '_quote_source', $source );
		}
	}
}
