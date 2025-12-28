<?php
/**
 * Sample test to verify the testing environment is working
 *
 * @package XVRandomQuotes
 */

/**
 * Test case to verify testing framework is properly set up
 */
class Test_Environment_Setup extends WP_UnitTestCase {

	/**
	 * Test that WordPress test environment is loaded
	 */
	public function test_wordpress_is_loaded() {
		$this->assertTrue( function_exists( 'add_filter' ) );
		$this->assertTrue( function_exists( 'register_post_type' ) );
	}

	/**
	 * Test that we can create a post
	 */
	public function test_can_create_post() {
		$post_id = $this->factory->post->create( [
			'post_title'   => 'Test Post',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		$this->assertGreaterThan( 0, $post_id );
		
		$post = get_post( $post_id );
		$this->assertEquals( 'Test Post', $post->post_title );
	}

	/**
	 * Test that the plugin main file exists
	 */
	public function test_plugin_file_exists() {
		$plugin_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/xv-random-quotes.php';
		$this->assertFileExists( $plugin_file );
	}

	/**
	 * Test that required PHP version is met
	 */
	public function test_php_version() {
		$this->assertGreaterThanOrEqual( '7.4.0', PHP_VERSION );
	}
}
