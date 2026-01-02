<?php
/**
 * Tests for Quote Content Meta Box
 *
 * Tests the meta box that provides a custom editor for quote text,
 * saving to post_content instead of using the standard WordPress editor.
 *
 * @package XVRandomQuotes
 */

class Test_Quote_Content_Meta_Box extends WP_UnitTestCase {

	private $metabox_instance;
	private $post_id;

	public function set_up() {
		parent::set_up();

		// Get the MetaBoxes instance
		$this->metabox_instance = new \XVRandomQuotes\Admin\MetaBoxes();
		$this->metabox_instance->init();

		// Create a test quote post
		$this->post_id = wp_insert_post( array(
			'post_type'   => 'xv_quote',
			'post_title'  => 'Test Quote',
			'post_status' => 'draft',
		) );
	}

	public function tear_down() {
		if ( $this->post_id ) {
			wp_delete_post( $this->post_id, true );
		}
		
		// Clean up $_POST to prevent state pollution
		$_POST = array();
		
		// Reset current user
		wp_set_current_user( 0 );
		
		parent::tear_down();
	}

	/**
	 * Test that quote content meta box is registered
	 */
	public function test_quote_content_metabox_is_registered() {
		global $wp_meta_boxes;

		// Trigger add_meta_boxes action
		do_action( 'add_meta_boxes', 'xv_quote', get_post( $this->post_id ) );

		$this->assertArrayHasKey( 'xv_quote', $wp_meta_boxes, 'Meta boxes should be registered for xv_quote post type' );
		$this->assertArrayHasKey( 'normal', $wp_meta_boxes['xv_quote'], 'Meta box should be in normal context' );
		$this->assertArrayHasKey( 'xv_quote_text', $wp_meta_boxes['xv_quote']['normal']['high'], 'Quote content meta box should be registered' );
	}

	/**
	 * Test that quote content meta box has correct title
	 */
	public function test_quote_content_metabox_title() {
		global $wp_meta_boxes;

		do_action( 'add_meta_boxes', 'xv_quote', get_post( $this->post_id ) );

		$metabox = $wp_meta_boxes['xv_quote']['normal']['high']['xv_quote_text'];
		$this->assertEquals( 'Quote Text', $metabox['title'], 'Meta box should have correct title' );
	}

	/**
	 * Test that nonce field is included in meta box output
	 */
	public function test_quote_content_metabox_includes_nonce() {
		ob_start();
		do_action( 'add_meta_boxes', 'xv_quote', get_post( $this->post_id ) );
		
		// Manually call the callback to check output
		global $wp_meta_boxes;
		$callback = $wp_meta_boxes['xv_quote']['normal']['high']['xv_quote_text']['callback'];
		call_user_func( $callback, get_post( $this->post_id ) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'xv_quote_content_nonce', $output, 'Meta box should include nonce field' );
	}

	/**
	 * Test that quote content saves to post_content when nonce is valid
	 */
	public function test_saves_quote_content_to_post_content_with_valid_nonce() {
		$quote_text = 'To be or not to be, that is the question.';

		// Set current user with edit capability FIRST
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Now create the nonce for this user
		$_POST['xv_quote_content_nonce'] = wp_create_nonce( 'xv_quote_content_save' );
		$_POST['xv_quote_content'] = $quote_text;

		// Call the save method directly
		$this->metabox_instance->save_all_meta_boxes( $this->post_id, get_post( $this->post_id ) );

		// Clear the cache and refetch the post
		clean_post_cache( $this->post_id );
		$post = get_post( $this->post_id );
		
		$this->assertEquals( $quote_text, $post->post_content, 'Quote text should be saved to post_content' );

		// Cleanup
		unset( $_POST['xv_quote_content_nonce'] );
		unset( $_POST['xv_quote_content'] );
	}

	/**
	 * Test that quote content does not save when nonce is invalid
	 */
	public function test_does_not_save_with_invalid_nonce() {
		$quote_text = 'This should not be saved.';
		$original_content = 'Original content';

		// Set original content
		wp_update_post( array(
			'ID'           => $this->post_id,
			'post_content' => $original_content,
		) );

		// Set up invalid nonce
		$_POST['xv_quote_content_nonce'] = 'invalid_nonce';
		$_POST['xv_quote_content'] = $quote_text;

		// Set current user with edit capability
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Trigger save
		do_action( 'save_post_xv_quote', $this->post_id, get_post( $this->post_id ), false );

		// Check that content was NOT changed
		$post = get_post( $this->post_id );
		$this->assertEquals( $original_content, $post->post_content, 'Quote text should not be saved with invalid nonce' );

		// Cleanup
		unset( $_POST['xv_quote_content_nonce'] );
		unset( $_POST['xv_quote_content'] );
	}

	/**
	 * Test that quote content does not save when nonce is missing
	 */
	public function test_does_not_save_without_nonce() {
		$quote_text = 'This should not be saved.';
		$original_content = 'Original content';

		// Set original content
		wp_update_post( array(
			'ID'           => $this->post_id,
			'post_content' => $original_content,
		) );

		// Set up POST data without nonce
		$_POST['xv_quote_content'] = $quote_text;

		// Set current user with edit capability
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Trigger save
		do_action( 'save_post_xv_quote', $this->post_id, get_post( $this->post_id ), false );

		// Check that content was NOT changed
		$post = get_post( $this->post_id );
		$this->assertEquals( $original_content, $post->post_content, 'Quote text should not be saved without nonce' );

		// Cleanup
		unset( $_POST['xv_quote_content'] );
	}

	/**
	 * Test that autosave does not trigger quote content save
	 */
	public function test_does_not_save_on_autosave() {
		$quote_text = 'This should not be saved on autosave.';
		$original_content = 'Original content';

		// Set original content
		wp_update_post( array(
			'ID'           => $this->post_id,
			'post_content' => $original_content,
		) );

		// Set current user with edit capability FIRST
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Now create the nonce for this user
		$_POST['xv_quote_content_nonce'] = wp_create_nonce( 'xv_quote_content_save' );
		$_POST['xv_quote_content'] = $quote_text;

		// Simulate autosave (only define if not already defined)
		if ( ! defined( 'DOING_AUTOSAVE' ) ) {
			define( 'DOING_AUTOSAVE', true );
		}

		// Call save method directly
		$this->metabox_instance->save_all_meta_boxes( $this->post_id, get_post( $this->post_id ) );

		// Check that content was NOT changed (autosave should prevent save)
		clean_post_cache( $this->post_id );
		$post = get_post( $this->post_id );
		$this->assertEquals( $original_content, $post->post_content, 'Quote text should not be saved during autosave' );

		// Cleanup
		unset( $_POST['xv_quote_content_nonce'] );
		unset( $_POST['xv_quote_content'] );
	}

	/**
	 * Test that user without edit capability cannot save quote content
	 */
	public function test_does_not_save_without_edit_capability() {
		$quote_text = 'This should not be saved.';
		$original_content = 'Original content';

		// Set original content
		wp_update_post( array(
			'ID'           => $this->post_id,
			'post_content' => $original_content,
		) );

		// Set current user WITHOUT edit capability FIRST
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Now create the nonce for this user (who doesn't have capability)
		$_POST['xv_quote_content_nonce'] = wp_create_nonce( 'xv_quote_content_save' );
		$_POST['xv_quote_content'] = $quote_text;

		// Call save method directly
		$this->metabox_instance->save_all_meta_boxes( $this->post_id, get_post( $this->post_id ) );

		// Check that content was NOT changed (capability check should prevent save)
		clean_post_cache( $this->post_id );
		$post = get_post( $this->post_id );
		$this->assertEquals( $original_content, $post->post_content, 'Quote text should not be saved without edit capability' );

		// Cleanup
		unset( $_POST['xv_quote_content_nonce'] );
		unset( $_POST['xv_quote_content'] );
	}

	/**
	 * Test that quote text is sanitized with wp_kses
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_sanitizes_quote_text_with_wp_kses() {
		// Quote text with allowed and disallowed HTML
		$quote_text = '<strong>Bold text</strong> and <em>italic</em> with <a href="http://example.com">link</a> but no <script>alert("xss")</script>';
		$expected = '<strong>Bold text</strong> and <em>italic</em> with <a href="http://example.com">link</a> but no alert("xss")';

		// Set current user with edit capability FIRST
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Now create the nonce for this user
		$_POST['xv_quote_content_nonce'] = wp_create_nonce( 'xv_quote_content_save' );
		$_POST['xv_quote_content'] = $quote_text;

		// Call save method directly
		$this->metabox_instance->save_all_meta_boxes( $this->post_id, get_post( $this->post_id ) );

		// Clear the cache and refetch the post
		clean_post_cache( $this->post_id );
		$post = get_post( $this->post_id );
		
		$this->assertEquals( $expected, $post->post_content, 'Quote text should be sanitized with wp_kses' );

		// Cleanup
		unset( $_POST['xv_quote_content_nonce'] );
		unset( $_POST['xv_quote_content'] );
	}

	/**
	 * Test that empty quote text is saved correctly
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_saves_empty_quote_text() {
		// Set original content
		wp_update_post( array(
			'ID'           => $this->post_id,
			'post_content' => 'Original content',
		) );

		// Set current user with edit capability FIRST
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Now create the nonce for this user
		$_POST['xv_quote_content_nonce'] = wp_create_nonce( 'xv_quote_content_save' );
		$_POST['xv_quote_content'] = '';

		// Call save method directly
		$this->metabox_instance->save_all_meta_boxes( $this->post_id, get_post( $this->post_id ) );

		// Clear the cache and refetch the post
		clean_post_cache( $this->post_id );
		$post = get_post( $this->post_id );
		
		$this->assertEquals( '', $post->post_content, 'Empty quote text should be saved' );

		// Cleanup
		unset( $_POST['xv_quote_content_nonce'] );
		unset( $_POST['xv_quote_content'] );
	}

	/**
	 * Test that allowed HTML tags are preserved
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_preserves_allowed_html_tags() {
		$quote_text = '<strong>Bold</strong>, <em>italic</em>, <a href="http://example.com">link</a>, <code>code</code>, <abbr title="abbreviation">abbr</abbr>, <cite>citation</cite>, <q>quote</q>, <mark>mark</mark>, sub<sub>script</sub>, super<sup>script</sup>, <b>bold</b>, and <i>italic</i> are allowed.';

		// Set current user with edit capability FIRST
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Now create the nonce for this user
		$_POST['xv_quote_content_nonce'] = wp_create_nonce( 'xv_quote_content_save' );
		$_POST['xv_quote_content'] = $quote_text;

		// Call save method directly
		$this->metabox_instance->save_all_meta_boxes( $this->post_id, get_post( $this->post_id ) );

		// Clear the cache and refetch the post
		clean_post_cache( $this->post_id );
		$post = get_post( $this->post_id );
		
		// Verify all inline formatting tags are preserved
		$this->assertStringContainsString( '<strong>', $post->post_content, 'Strong tag should be preserved' );
		$this->assertStringContainsString( '<em>', $post->post_content, 'Em tag should be preserved' );
		$this->assertStringContainsString( '<a href=', $post->post_content, 'Link tag should be preserved' );
		$this->assertStringContainsString( '<code>', $post->post_content, 'Code tag should be preserved' );
		$this->assertStringContainsString( '<abbr', $post->post_content, 'Abbr tag should be preserved' );
		$this->assertStringContainsString( '<cite>', $post->post_content, 'Cite tag should be preserved' );
		$this->assertStringContainsString( '<q>', $post->post_content, 'Q tag should be preserved' );
		$this->assertStringContainsString( '<mark>', $post->post_content, 'Mark tag should be preserved' );
		$this->assertStringContainsString( '<sub>', $post->post_content, 'Sub tag should be preserved' );
		$this->assertStringContainsString( '<sup>', $post->post_content, 'Sup tag should be preserved' );
		$this->assertStringContainsString( '<b>', $post->post_content, 'B tag should be preserved' );
		$this->assertStringContainsString( '<i>', $post->post_content, 'I tag should be preserved' );

		// Cleanup
		unset( $_POST['xv_quote_content_nonce'] );
		unset( $_POST['xv_quote_content'] );
	}

	/**
	 * Test that block-level and disallowed HTML tags are stripped
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_strips_block_level_and_disallowed_tags() {
		// Quote text with block-level and disallowed tags
		// Note: wp_kses() with custom allowed tags strips all dangerous/block tags
		$quote_text = '<script>alert("xss")</script><iframe src="evil.com"></iframe>Some text with <style>body{color:red;}</style> and <object data="evil.swf"></object>';
		$expected = 'alert("xss")Some text with body{color:red;} and <object></object>';

		// Set current user with edit capability FIRST
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		// Now create the nonce for this user
		$_POST['xv_quote_content_nonce'] = wp_create_nonce( 'xv_quote_content_save' );
		$_POST['xv_quote_content'] = $quote_text;

		// Call save method directly
		$this->metabox_instance->save_all_meta_boxes( $this->post_id, get_post( $this->post_id ) );

		// Clear the cache and refetch the post
		clean_post_cache( $this->post_id );
		$post = get_post( $this->post_id );
		
		// Check that dangerous tags are stripped
		$this->assertEquals( $expected, $post->post_content, 'Dangerous tags should be stripped' );
		$this->assertStringNotContainsString( '<script>', $post->post_content, 'Script tag should be stripped' );
		$this->assertStringNotContainsString( '<iframe', $post->post_content, 'Iframe tag should be stripped' );
		$this->assertStringNotContainsString( '<style>', $post->post_content, 'Style tag should be stripped' );

		// Cleanup
		unset( $_POST['xv_quote_content_nonce'] );
		unset( $_POST['xv_quote_content'] );
	}
}
