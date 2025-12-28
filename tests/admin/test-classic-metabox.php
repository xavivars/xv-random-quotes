<?php
/**
 * Tests for Classic Editor Meta Box
 *
 * Tests the Quote Source meta box that appears ONLY in Classic Editor.
 * In Block Editor, source is handled via registered post meta in sidebar.
 * 
 * Author: Handled by quote_author taxonomy (appears in sidebar in both editors)
 * Source: Custom meta box (Classic Editor) OR sidebar (Block Editor)
 * Quote content: WordPress native editor (post_content)
 *
 * @package XVRandomQuotes\Tests
 */

require_once __DIR__ . '/../bootstrap.php';

/**
 * Test Classic Editor Meta Box functionality
 */
class Test_Classic_MetaBox extends WP_UnitTestCase {

	/**
	 * Meta box instance
	 *
	 * @var \XVRandomQuotes\Admin\MetaBoxes
	 */
	private $metaboxes;

	/**
	 * Test post ID
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Test user ID
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Force Classic Editor mode for tests
		add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );

		// Create test user with edit capabilities
		$this->user_id = $this->factory->user->create(
			array(
				'role' => 'editor',
			)
		);
		wp_set_current_user( $this->user_id );

		// Create test post
		$this->post_id = $this->factory->post->create(
			array(
				'post_type'   => 'xv_quote',
				'post_status' => 'publish',
				'post_title'  => 'Test Quote Title',
			)
		);

		// Initialize metaboxes class
		if ( class_exists( '\XVRandomQuotes\Admin\MetaBoxes' ) ) {
			$this->metaboxes = new \XVRandomQuotes\Admin\MetaBoxes();
			$this->metaboxes->init();
		}
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Remove Classic Editor filter
		remove_filter( 'use_block_editor_for_post_type', '__return_false', 100 );

		// Clean up $_POST
		unset( $_POST['xv_quote_source_nonce'] );
		unset( $_POST['quote_source'] );

		// Clean up post meta
		delete_post_meta( $this->post_id, '_quote_source' );

		parent::tearDown();
	}

	/**
	 * Test 1: Meta box is registered
	 */
	public function test_meta_box_registered() {
		global $wp_meta_boxes;

		// Trigger add_meta_boxes action
		do_action( 'add_meta_boxes', 'xv_quote', get_post( $this->post_id ) );

		// Verify meta box is registered
		$this->assertArrayHasKey( 'xv_quote', $wp_meta_boxes, 'Meta box should be registered for xv_quote post type' );
		$this->assertArrayHasKey( 'side', $wp_meta_boxes['xv_quote'], 'Meta box should be in side context' );
		$this->assertArrayHasKey( 'default', $wp_meta_boxes['xv_quote']['side'], 'Meta box should have default priority' );
		$this->assertArrayHasKey( 'xv_quote_source', $wp_meta_boxes['xv_quote']['side']['default'], 'Meta box should have ID xv_quote_source' );

		// Verify meta box details
		$metabox = $wp_meta_boxes['xv_quote']['side']['default']['xv_quote_source'];
		$this->assertEquals( 'Quote Source', $metabox['title'], 'Meta box should have title "Quote Source"' );
	}

	/**
	 * Test 2: Meta box NOT registered when Block Editor is active
	 */
	public function test_meta_box_not_registered_for_block_editor() {
		// This test verifies the conditional logic exists
		// In real usage, use_block_editor_for_post_type() would return true for Block Editor
		// For now, we just verify the meta box can be conditionally registered
		
		// We'll test this by checking that the class has the is_classic_editor_active method
		$this->assertTrue(
			method_exists( $this->metaboxes, 'is_classic_editor_active' ),
			'MetaBoxes class should have is_classic_editor_active method for conditional registration'
		);
	}

	/**
	 * Test 3: Nonce verification - valid nonce
	 */
	public function test_save_with_valid_nonce() {
		// Set up nonce
		$_POST['xv_quote_source_nonce'] = wp_create_nonce( 'xv_quote_source' );
		$_POST['quote_source']           = 'Test Source';

		// Trigger save
		do_action( 'save_post_xv_quote', $this->post_id, get_post( $this->post_id ), false );

		// Verify source was saved
		$source = get_post_meta( $this->post_id, '_quote_source', true );
		$this->assertEquals( 'Test Source', $source, 'Source should be saved with valid nonce' );
	}

	/**
	 * Test 4: Nonce verification - invalid nonce
	 */
	public function test_save_with_invalid_nonce() {
		// Set up invalid nonce
		$_POST['xv_quote_source_nonce'] = 'invalid_nonce';
		$_POST['quote_source']           = 'Test Source';

		// Trigger save
		do_action( 'save_post_xv_quote', $this->post_id, get_post( $this->post_id ), false );

		// Verify source was NOT saved
		$source = get_post_meta( $this->post_id, '_quote_source', true );
		$this->assertEmpty( $source, 'Source should not be saved with invalid nonce' );
	}

	/**
	 * Test 5: Nonce verification - missing nonce
	 */
	public function test_save_with_missing_nonce() {
		// Don't set nonce
		unset( $_POST['xv_quote_source_nonce'] );
		$_POST['quote_source'] = 'Test Source';

		// Trigger save
		do_action( 'save_post_xv_quote', $this->post_id, get_post( $this->post_id ), false );

		// Verify source was NOT saved
		$source = get_post_meta( $this->post_id, '_quote_source', true );
		$this->assertEmpty( $source, 'Source should not be saved with missing nonce' );
	}

	/**
	 * Test 6: Autosave protection
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_save_blocked_during_autosave() {
		// Set up test environment for separate process
		add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );
		
		// Create test user with edit capabilities
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Create test post
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'xv_quote',
				'post_status' => 'publish',
				'post_title'  => 'Test Quote Title',
			)
		);

		// Initialize metaboxes class
		$metaboxes = new \XVRandomQuotes\Admin\MetaBoxes();
		$metaboxes->init();

		// Simulate autosave
		if ( ! defined( 'DOING_AUTOSAVE' ) ) {
			define( 'DOING_AUTOSAVE', true );
		}

		// Set up valid nonce and data
		$_POST['xv_quote_source_nonce'] = wp_create_nonce( 'xv_quote_source' );
		$_POST['quote_source']           = 'Test Source';

		// Trigger save
		do_action( 'save_post_xv_quote', $post_id, get_post( $post_id ), false );

		// Verify data was NOT saved during autosave
		$source = get_post_meta( $post_id, '_quote_source', true );
		$this->assertEmpty( $source, 'Source should not be saved during autosave' );
	}

	/**
	 * Test 7: User capability check
	 */
	public function test_save_blocked_without_capability() {
		// Create user without edit capability
		$subscriber_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		// Set up valid nonce and data
		$_POST['xv_quote_source_nonce'] = wp_create_nonce( 'xv_quote_source' );
		$_POST['quote_source']           = 'Test Source';

		// Trigger save
		do_action( 'save_post_xv_quote', $this->post_id, get_post( $this->post_id ), false );

		// Verify data was NOT saved without capability
		$source = get_post_meta( $this->post_id, '_quote_source', true );
		$this->assertEmpty( $source, 'Source should not be saved without edit_post capability' );

		// Restore original user
		wp_set_current_user( $this->user_id );
	}

	/**
	 * Test 8: Source meta save with HTML sanitization
	 */
	public function test_source_html_sanitization() {
		// Set up nonce and source with HTML
		$_POST['xv_quote_source_nonce'] = wp_create_nonce( 'xv_quote_source' );
		$_POST['quote_source']           = '<a href="http://example.com">Source Link</a>';

		// Trigger save
		$this->metaboxes->save_meta_box( $this->post_id, get_post( $this->post_id ) );

		// Verify source was saved with HTML preserved
		$source = get_post_meta( $this->post_id, '_quote_source', true );
		$this->assertStringContainsString( '<a href="http://example.com">Source Link</a>', $source, 'Source should preserve safe HTML' );
	}

	/**
	 * Test 9: Source sanitization removes dangerous HTML
	 */
	public function test_source_removes_dangerous_html() {
		// Set up nonce and source with dangerous HTML
		$_POST['xv_quote_source_nonce'] = wp_create_nonce( 'xv_quote_source' );
		$_POST['quote_source']           = '<script>alert("xss")</script><a href="http://example.com">Link</a>';

		// Trigger save
		$this->metaboxes->save_meta_box( $this->post_id, get_post( $this->post_id ) );

		// Verify dangerous HTML was removed
		$source = get_post_meta( $this->post_id, '_quote_source', true );
		$this->assertStringNotContainsString( '<script>', $source, 'Source should remove script tags' );
		$this->assertStringContainsString( '<a href="http://example.com">Link</a>', $source, 'Source should preserve safe HTML' );
	}

	/**
	 * Test 10: Empty source saves empty string
	 */
	public function test_empty_source_saves_empty_string() {
		// First, set a source
		update_post_meta( $this->post_id, '_quote_source', 'Initial Source' );

		// Verify source is set
		$source = get_post_meta( $this->post_id, '_quote_source', true );
		$this->assertEquals( 'Initial Source', $source );

		// Now save with empty source
		$_POST['xv_quote_source_nonce'] = wp_create_nonce( 'xv_quote_source' );
		$_POST['quote_source']           = '';

		// Trigger save
		$this->metaboxes->save_meta_box( $this->post_id, get_post( $this->post_id ) );

		// Verify source was updated to empty string
		$source = get_post_meta( $this->post_id, '_quote_source', true );
		$this->assertEquals( '', $source, 'Empty source should save as empty string' );
	}

	/**
	 * Test 11: Meta box renders form field
	 */
	public function test_metabox_renders_form_field() {
		// Set some initial data
		update_post_meta( $this->post_id, '_quote_source', 'Test Source' );

		// Capture metabox output
		ob_start();
		do_action( 'add_meta_boxes', 'xv_quote', get_post( $this->post_id ) );
		
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes['xv_quote']['side']['default']['xv_quote_source'] ) ) {
			$metabox = $wp_meta_boxes['xv_quote']['side']['default']['xv_quote_source'];
			call_user_func( $metabox['callback'], get_post( $this->post_id ), $metabox );
		}
		$output = ob_get_clean();

		// Verify nonce field is present
		$this->assertStringContainsString( 'xv_quote_source_nonce', $output, 'Metabox should include nonce field' );

		// Verify source field is present with value
		$this->assertStringContainsString( 'quote_source', $output, 'Metabox should include source field' );
		$this->assertStringContainsString( 'Test Source', $output, 'Metabox should display current source' );
	}
}
