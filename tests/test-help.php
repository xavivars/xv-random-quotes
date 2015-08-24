<?php

class HelpTests extends WP_UnitTestCase {

    function get_help_content() {
        ob_start();

        $help = new XV_RandomQuotes_Help();

        $help->show_help();

        $output = ob_get_clean();

        return $output;
    }

    function get_help_private_content($function_name) {
        ob_start();
        
        $help = new XV_RandomQuotes_Help();

        $reflectionOfUser = new ReflectionClass('XV_RandomQuotes_Help');
        $method = $reflectionOfUser->getMethod($function_name);
        $method->setAccessible(true);
        
        $method->invoke($help);
        
        $output = ob_get_clean();
        
        return $output;        
    }
    
	function test_help_returns_something() {
        
        $output = $this->get_help_content();
		
        $this->assertNotEmpty($output);
	}
    
    function test_help_does_not_contain_paypal() {
        $output = $this->get_help_content();
		
        $this->assertNotContains("paypal", $output, true);
    }

    function test_help_quotes_in_blog_when_cannot_manage_options_is_empty() {

        $user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
        wp_set_current_user( $user_id );
        $content = $this->get_help_private_content('random_quotes_in_blog');

        $this->assertEmpty(trim($content));
    }

    function test_help_quotes_in_blog_when_can_manage_options_has_content() {
        $user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $user_id );
        $content = $this->get_help_private_content('random_quotes_in_blog');

        $this->assertNotEmpty(trim($content));
    }
}

