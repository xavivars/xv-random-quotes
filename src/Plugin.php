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
}
