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
use XVRandomQuotes\Admin\Settings;
use XVRandomQuotes\Admin\OverviewPage;
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

		// Register overview page
		$overview_page = new OverviewPage();
		$overview_page->register();

		// Register settings page
		$settings = new Settings();
		$settings->init();

		// Register widgets
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		// Register REST API endpoints
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Register Gutenberg blocks
		add_action( 'init', array( $this, 'register_blocks' ) );

		// Register shortcodes
		add_action( 'init', array( $this, 'register_shortcodes' ) );
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
		$plugin_dir = dirname( __DIR__ );
		
		// Register Random Quote block
		if ( ! \WP_Block_Type_Registry::get_instance()->is_registered( 'xv-random-quotes/random-quote' ) ) {
			register_block_type(
				$plugin_dir . '/src/Blocks/RandomQuote',
				array(
					'render_callback' => __NAMESPACE__ . '\\Blocks\\render_random_quote_block',
				)
			);
			wp_set_script_translations( 'xv-random-quotes-random-quote-editor-script', 'xv-random-quotes');
		}

		// Register Specific Quote block
		if ( ! \WP_Block_Type_Registry::get_instance()->is_registered( 'xv-random-quotes/specific-quote' ) ) {
			register_block_type(
				$plugin_dir . '/src/Blocks/SpecificQuote',
				array(
					'render_callback' => __NAMESPACE__ . '\\Blocks\\render_specific_quote_block',
				)
			);
			wp_set_script_translations( 'xv-random-quotes-specific-quote-editor-script', 'xv-random-quotes');
		}

		// Register List Quotes block
		if ( ! \WP_Block_Type_Registry::get_instance()->is_registered( 'xv-random-quotes/list-quotes' ) ) {
			register_block_type(
				$plugin_dir . '/src/Blocks/ListQuotes',
				array(
					'render_callback' => __NAMESPACE__ . '\\Blocks\\render_list_quotes_block',
				)
			);
			wp_set_script_translations( 'xv-random-quotes-list-quotes-editor-script', 'xv-random-quotes');
		}
	}

	/**
	 * Register shortcodes
	 */
	public function register_shortcodes() {
		// Register shortcodes - functions are in backward-compatibility.php
		add_shortcode( 'stray-random', 'stray_random_shortcode' );
		add_shortcode( 'stray-all', 'stray_all_shortcode' );
		add_shortcode( 'stray-id', 'stray_id_shortcode' );
	}
}
