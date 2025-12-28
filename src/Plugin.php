<?php
/**
 * Plugin Bootstrap
 *
 * Handles autoloading and initialization of plugin components.
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes;

use XVRandomQuotes\PostTypes\QuotePostType;
use XVRandomQuotes\Taxonomies\QuoteTaxonomies;
use XVRandomQuotes\PostMeta\QuoteMetaFields;
use XVRandomQuotes\Admin\MetaBoxes;
use XVRandomQuotes\Admin\BlockEditorAssets;
use XVRandomQuotes\Widgets\QuoteWidget;
use XVRandomQuotes\RestAPI\QuoteEndpoint;

/**
 * Class Plugin
 *
 * Main plugin bootstrap class.
 */
class Plugin {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const VERSION = '2.0.0';

	/**
	 * Singleton instance
	 *
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize plugin components
	 */
	private function init() {
		// Register custom post type
		$post_type = new QuotePostType();
		$post_type->init();

		// Register taxonomies
		$taxonomies = new QuoteTaxonomies();
		$taxonomies->init();

		// Register post meta fields
		$meta_fields = new QuoteMetaFields();
		$meta_fields->init();

		// Register meta boxes for Classic Editor
		$meta_boxes = new MetaBoxes();
		$meta_boxes->init();

		// Enqueue Block Editor assets
		new BlockEditorAssets();

		// Register widgets
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		// Register REST API endpoints
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Register Gutenberg blocks
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register widgets
	 */
	public function register_widgets() {
		register_widget( QuoteWidget::class );
	}

	/**
	 * Register REST API routes
	 */
	public function register_rest_routes() {
		QuoteEndpoint::register_routes();
	}

	/**
	 * Register Gutenberg blocks
	 */
	public function register_blocks() {
		// Register Random Quote block
		if ( ! \WP_Block_Type_Registry::get_instance()->is_registered( 'xv-random-quotes/random-quote' ) ) {
			register_block_type(
				dirname( __DIR__ ) . '/src/Blocks/RandomQuote/block.json',
				array(
					'render_callback' => __NAMESPACE__ . '\\Blocks\\render_random_quote_block',
				)
			);
		}

		// Register Specific Quote block
		if ( ! \WP_Block_Type_Registry::get_instance()->is_registered( 'xv-random-quotes/specific-quote' ) ) {
			register_block_type(
				dirname( __DIR__ ) . '/src/Blocks/SpecificQuote/block.json',
				array(
					'render_callback' => __NAMESPACE__ . '\\Blocks\\render_specific_quote_block',
				)
			);
		}

		// Register List Quotes block
		if ( ! \WP_Block_Type_Registry::get_instance()->is_registered( 'xv-random-quotes/list-quotes' ) ) {
			register_block_type(
				dirname( __DIR__ ) . '/src/Blocks/ListQuotes/block.json',
				array(
					'render_callback' => __NAMESPACE__ . '\\Blocks\\render_list_quotes_block',
				)
			);
		}
	}
}
