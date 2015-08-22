<?php

class HelpTests extends WP_UnitTestCase {

    function get_help_content() {
        ob_start();
        
        $help = new XV_RandomQuotes_Help();
        
        $help->show_help();
        
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
}

