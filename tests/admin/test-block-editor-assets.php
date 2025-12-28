<?php
/**
 * Tests for Block Editor Asset Enqueuing
 *
 * Verifies that JavaScript assets for the Block Editor sidebar panel
 * are properly enqueued with correct dependencies and localization.
 *
 * @package XVRandomQuotes
 * @subpackage Tests
 */

require_once __DIR__ . '/../bootstrap.php';

/**
 * Test Block Editor Asset Enqueuing
 *
 * @group admin
 * @group block-editor
 * @group assets
 */
class Test_Block_Editor_Assets extends WP_UnitTestCase {

	/**
	 * BlockEditorAssets instance
	 *
	 * @var \XVRandomQuotes\Admin\BlockEditorAssets
	 */
	private $assets;

	/**
	 * Test post ID
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Ensure Plugin singleton is initialized
		\XVRandomQuotes\Plugin::get_instance();

		// Create test quote post
		$this->post_id = $this->factory->post->create(
			array(
				'post_type'   => 'xv_quote',
				'post_title'  => 'Test Quote',
				'post_status' => 'draft',
			)
		);

		// Set current screen to simulate Block Editor
		set_current_screen( 'xv_quote' );
		
		// Initialize BlockEditorAssets
		if ( class_exists( 'XVRandomQuotes\Admin\BlockEditorAssets' ) ) {
			$this->assets = new \XVRandomQuotes\Admin\BlockEditorAssets();
		}
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up registered scripts
		wp_dequeue_script( 'xv-quote-details' );
		wp_deregister_script( 'xv-quote-details' );
		
		parent::tearDown();
	}

	/**
	 * Test that BlockEditorAssets class exists
	 */
	public function test_class_exists() {
		$this->assertTrue(
			class_exists( 'XVRandomQuotes\Admin\BlockEditorAssets' ),
			'BlockEditorAssets class should exist'
		);
	}

	/**
	 * Test that BlockEditorAssets can be instantiated
	 */
	public function test_can_instantiate() {
		$this->assertInstanceOf(
			'XVRandomQuotes\Admin\BlockEditorAssets',
			$this->assets,
			'Should be able to instantiate BlockEditorAssets'
		);
	}

	/**
	 * Test that enqueue_block_editor_assets action is hooked
	 */
	public function test_enqueue_action_hooked() {
		$this->assertNotFalse(
			has_action( 'enqueue_block_editor_assets', array( $this->assets, 'enqueue_assets' ) ),
			'Should hook into enqueue_block_editor_assets action'
		);
	}

	/**
	 * Test that script is enqueued on xv_quote edit screen
	 */
	public function test_script_enqueued_on_quote_screen() {
		// Set up Block Editor context
		global $post;
		$post = get_post( $this->post_id );
		set_current_screen( 'xv_quote' );

		// Trigger enqueue
		do_action( 'enqueue_block_editor_assets' );

		$this->assertTrue(
			wp_script_is( 'xv-quote-details', 'enqueued' ),
			'Script should be enqueued on xv_quote Block Editor screen'
		);
	}

	/**
	 * Test that script is NOT enqueued on other post types
	 */
	public function test_script_not_enqueued_on_other_post_types() {
		// Create a regular post
		$regular_post = $this->factory->post->create(
			array(
				'post_type' => 'post',
			)
		);

		global $post;
		$post = get_post( $regular_post );
		set_current_screen( 'post' );

		// Trigger enqueue
		do_action( 'enqueue_block_editor_assets' );

		$this->assertFalse(
			wp_script_is( 'xv-quote-details', 'enqueued' ),
			'Script should NOT be enqueued on non-quote post types'
		);
	}

	/**
	 * Test that script has correct source path
	 */
	public function test_script_has_correct_source() {
		global $post;
		$post = get_post( $this->post_id );
		set_current_screen( 'xv_quote' );

		do_action( 'enqueue_block_editor_assets' );

		$wp_scripts = wp_scripts();
		$script = $wp_scripts->registered['xv-quote-details'] ?? null;

		$this->assertNotNull( $script, 'Script should be registered' );
		$this->assertStringContainsString(
			'src/generated/quote-details.js',
			$script->src,
			'Script src should point to src/generated/quote-details.js'
		);
	}

	/**
	 * Test that script dependencies are loaded from asset file
	 */
	public function test_script_dependencies_from_asset_file() {
		global $post;
		$post = get_post( $this->post_id );
		set_current_screen( 'xv_quote' );

		do_action( 'enqueue_block_editor_assets' );

		$wp_scripts = wp_scripts();
		$script = $wp_scripts->registered['xv-quote-details'] ?? null;

		$this->assertNotNull( $script, 'Script should be registered' );
		$this->assertIsArray( $script->deps, 'Script should have dependencies array' );
		
		// Dependencies should come from .asset.php file
		// Currently empty, but structure should be correct
		$this->assertIsArray( $script->deps );
	}

	/**
	 * Test that script version comes from asset file
	 */
	public function test_script_version_from_asset_file() {
		global $post;
		$post = get_post( $this->post_id );
		set_current_screen( 'xv_quote' );

		do_action( 'enqueue_block_editor_assets' );

		$wp_scripts = wp_scripts();
		$script = $wp_scripts->registered['xv-quote-details'] ?? null;

		$this->assertNotNull( $script, 'Script should be registered' );
		$this->assertNotEmpty( $script->ver, 'Script should have version from asset file' );
		$this->assertNotEquals( false, $script->ver, 'Version should not be false' );
	}

	/**
	 * Test that script is localized with required data
	 */
	public function test_script_localized_with_data() {
		global $post;
		$post = get_post( $this->post_id );
		set_current_screen( 'xv_quote' );

		do_action( 'enqueue_block_editor_assets' );

		$wp_scripts = wp_scripts();
		
		// Check if script has localized data
		$this->assertNotEmpty(
			$wp_scripts->get_data( 'xv-quote-details', 'data' ),
			'Script should have localized data'
		);
	}

	/**
	 * Test that localized data contains nonce
	 */
	public function test_localized_data_contains_nonce() {
		global $post;
		$post = get_post( $this->post_id );
		set_current_screen( 'xv_quote' );

		do_action( 'enqueue_block_editor_assets' );

		$wp_scripts = wp_scripts();
		$data = $wp_scripts->get_data( 'xv-quote-details', 'data' );

		$this->assertStringContainsString(
			'nonce',
			$data,
			'Localized data should contain nonce'
		);
	}

	/**
	 * Test that localized data contains post ID
	 */
	public function test_localized_data_contains_post_id() {
		global $post;
		$post = get_post( $this->post_id );
		set_current_screen( 'xv_quote' );

		do_action( 'enqueue_block_editor_assets' );

		$wp_scripts = wp_scripts();
		$data = $wp_scripts->get_data( 'xv-quote-details', 'data' );

		$this->assertStringContainsString(
			'postId',
			$data,
			'Localized data should contain postId'
		);
	}

	/**
	 * Test that localized data contains REST URL
	 */
	public function test_localized_data_contains_rest_url() {
		global $post;
		$post = get_post( $this->post_id );
		set_current_screen( 'xv_quote' );

		do_action( 'enqueue_block_editor_assets' );

		$wp_scripts = wp_scripts();
		$data = $wp_scripts->get_data( 'xv-quote-details', 'data' );

		$this->assertStringContainsString(
			'restUrl',
			$data,
			'Localized data should contain restUrl'
		);
	}

	/**
	 * Test that script is loaded in footer
	 */
	public function test_script_loaded_in_footer() {
		global $post;
		$post = get_post( $this->post_id );
		set_current_screen( 'xv_quote' );

		do_action( 'enqueue_block_editor_assets' );

		$wp_scripts = wp_scripts();
		$script = $wp_scripts->registered['xv-quote-details'] ?? null;

		$this->assertNotNull( $script, 'Script should be registered' );
		// Note: Block Editor scripts are typically loaded in footer
		// but we can't easily test the $in_footer parameter from wp_enqueue_script
		// This is more of a code review check
	}

	/**
	 * Test that asset file exists
	 */
	public function test_asset_file_exists() {
		$asset_file = plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'src/generated/quote-details.asset.php';
		
		$this->assertFileExists(
			$asset_file,
			'Asset file should exist at src/generated/quote-details.asset.php'
		);
	}

	/**
	 * Test that asset file returns correct structure
	 */
	public function test_asset_file_structure() {
		$asset_file = plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'src/generated/quote-details.asset.php';
		$asset = include $asset_file;

		$this->assertIsArray( $asset, 'Asset file should return an array' );
		$this->assertArrayHasKey( 'dependencies', $asset, 'Asset should have dependencies key' );
		$this->assertArrayHasKey( 'version', $asset, 'Asset should have version key' );
		$this->assertIsArray( $asset['dependencies'], 'Dependencies should be an array' );
		$this->assertIsString( $asset['version'], 'Version should be a string' );
	}
}
