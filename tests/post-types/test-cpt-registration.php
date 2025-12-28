<?php
/**
 * Tests for Quote Custom Post Type Registration
 *
 * @package XVRandomQuotes
 */

/**
 * Test case for Quote CPT registration
 */
class Test_CPT_Registration extends WP_UnitTestCase {

	/**
	 * Test that xv_quote post type is registered
	 */
	public function test_post_type_exists() {
		$this->assertTrue( post_type_exists( 'xv_quote' ), 'xv_quote post type should be registered' );
	}

	/**
	 * Test that xv_quote post type has correct configuration
	 */
	public function test_post_type_configuration() {
		$post_type_object = get_post_type_object( 'xv_quote' );

		$this->assertNotNull( $post_type_object, 'Post type object should exist' );
		$this->assertFalse( $post_type_object->public, 'Post type should not be public' );
		$this->assertTrue( $post_type_object->show_ui, 'Post type should show in admin UI' );
		$this->assertTrue( $post_type_object->show_in_rest, 'Post type should be available in REST API' );
		$this->assertEquals( 'dashicons-format-quote', $post_type_object->menu_icon, 'Post type should have quote icon' );
	}

	/**
	 * Test that xv_quote post type supports only title (editor removed for meta box approach)
	 */
	public function test_post_type_supports_only_title() {
		$this->assertTrue( post_type_supports( 'xv_quote', 'title' ), 'Post type should support title' );
		$this->assertFalse( post_type_supports( 'xv_quote', 'editor' ), 'Post type should NOT support editor (using meta boxes instead)' );
	}

	/**
	 * Test that xv_quote post type supports other necessary features
	 */
	public function test_post_type_supports_other_features() {
		$this->assertTrue( post_type_supports( 'xv_quote', 'author' ), 'Post type should support author' );
		$this->assertTrue( post_type_supports( 'xv_quote', 'revisions' ), 'Post type should support revisions' );
		$this->assertTrue( post_type_supports( 'xv_quote', 'custom-fields' ), 'Post type should support custom-fields for REST API meta exposure' );
	}

	/**
	 * Test that xv_quote post type has correct labels
	 */
	public function test_post_type_labels() {
		$post_type_object = get_post_type_object( 'xv_quote' );

		$this->assertEquals( 'Quotes', $post_type_object->labels->name, 'Post type should have correct plural name' );
		$this->assertEquals( 'Quote', $post_type_object->labels->singular_name, 'Post type should have correct singular name' );
		$this->assertEquals( 'Add New Quote', $post_type_object->labels->add_new_item, 'Post type should have correct add new label' );
	}

	/**
	 * Test that a quote post can be created
	 */
	public function test_can_create_quote_post() {
		$post_id = wp_insert_post( array(
			'post_type'    => 'xv_quote',
			'post_title'   => 'Test Quote Title',
			'post_content' => 'This is a test quote content.',
			'post_status'  => 'publish',
		) );

		$this->assertGreaterThan( 0, $post_id, 'Quote post should be created successfully' );
		$this->assertEquals( 'xv_quote', get_post_type( $post_id ), 'Created post should be xv_quote type' );
	}

	/**
	 * Test that quote post content can be saved and retrieved
	 */
	public function test_quote_content_saved_to_post_content() {
		$quote_text = 'To be or not to be, that is the question.';
		
		$post_id = wp_insert_post( array(
			'post_type'    => 'xv_quote',
			'post_title'   => 'Shakespeare Quote',
			'post_content' => $quote_text,
			'post_status'  => 'publish',
		) );

		$saved_post = get_post( $post_id );
		
		$this->assertEquals( $quote_text, $saved_post->post_content, 'Quote text should be saved to post_content' );
	}
}
