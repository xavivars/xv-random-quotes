<?php
/**
 * Block Editor Asset Enqueuing
 *
 * Handles enqueuing JavaScript and CSS assets for the Block Editor
 * sidebar panel that allows editing quote metadata.
 *
 * @package XVRandomQuotes
 * @subpackage Admin
 */

namespace XVRandomQuotes\Admin;

/**
 * Class BlockEditorAssets
 *
 * Manages Block Editor asset enqueuing for quote post type.
 */
class BlockEditorAssets {

	/**
	 * Constructor
	 *
	 * Sets up action hooks for asset enqueuing.
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue Block Editor assets
	 *
	 * Loads JavaScript for the sidebar panel on xv_quote edit screens only.
	 * Assets are loaded from src/generated/ directory with dependencies
	 * automatically determined by @wordpress/scripts build process.
	 */
	public function enqueue_assets() {
		// Get current screen
		$screen = get_current_screen();
		
		// Only enqueue on xv_quote post type
		if ( ! $screen || 'xv_quote' !== $screen->post_type ) {
			return;
		}

		// Get current post ID
		global $post;
		$post_id = $post ? $post->ID : 0;

		// Load asset file with dependencies and version
		$asset_file = plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'src/generated/quote-details.asset.php';
		
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		// Enqueue the script
		wp_enqueue_script(
			'xv-quote-details',
			plugins_url( 'src/generated/quote-details.js', dirname( dirname( __FILE__ ) ) ),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		// Localize script with data needed by JavaScript
		wp_localize_script(
			'xv-quote-details',
			'xvQuoteData',
			array(
				'postId'  => $post_id,
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'restUrl' => rest_url(),
				'postType' => 'xv_quote',
			)
		);
	}
}
