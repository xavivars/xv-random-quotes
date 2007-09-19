<?php
/*
Plugin Name: Stray Quotes
Plugin URI: http://www.italyisfalling.com/stray-quotes/
Description: This plugin allows you to embed random quotes into your pages. It comes with a management tool and a option page in the administrative console - and it is widget compatible.
Author: Corpodibacco
Author URI: http://www.italyisfalling.com/category/wordpress-things
Version: 1.48
License: GPL compatible
*/
//embed a random quote
function wp_quotes_random() {

	global $wpdb;

	$widgetTitle = get_option('stray_quotes_widget_title');
	$regularTitle =  get_option('stray_quotes_regular_title');
	$beforeAll =  get_option ('stray_quotes_before_all');
	$beforeAuthor = get_option ('stray_quotes_before_author');
	$afterAuthor = get_option ('stray_quotes_after_author');
	$beforeSource = get_option ('stray_quotes_before_source');
	$afterSource = get_option ('stray_quotes_after_source');
	$beforeQuote = get_option ('stray_quotes_before_quote');
	$afterQuote = get_option ('stray_quotes_after_quote');
	$afterAll = get_option ('stray_quotes_after_all');
	$putQuotesFirst = get_option ('stray_quotes_put_quotes_first');
	$useGoogleLinks = get_option ('stray_quotes_use_google_links');	
	
	$result = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where visible='yes'");				
	
	if ( !empty($result) )	{
			
		// srand ((double) microtime() * 1000000);
		// srand (time());
		list($usec, $sec) = explode(' ', microtime());
		srand( (double)(float) $sec + ((float) $usec * 100000) );		
		$get_one = $result[rand(0, count($result)-1)];
		
		
		if ( !$widgetTitle) {			
			$output = $regularTitle;
		}
		else {
			$output = '';
		}
		
		if ( !$useGoogleLinks) {			
			$Author = $get_one->author;
			$Source = $get_one->source;
		}
		else {
		$Author = '<a href="http://www.google.com/search?q=%22' . str_replace('&', '%26',$get_one->author) . '%22">' . 
					$get_one->author . '</a>';		
		$Source = '<a href="http://www.google.com/search?q=%22' . str_replace('&', '%26',$get_one->source) . '%22">' . 
					$get_one->source . '</a>';		
		}
		
		if ( !$putQuotesFirst) {
			$output .= $beforeAll;
			if ( !empty($get_one->author) ) {
				$output .= $beforeAuthor . $Author . $afterAuthor;
			}
			if ( !empty($get_one->source) ) {
				$output .= $beforeSource . $Source . $afterSource;
			}		

			$output .= $beforeQuote . nl2br($get_one->quote) . $afterQuote;			
			$output .= $afterAll;		
		}
		else {		
			$output .= $beforeAll;		
			$output .= $beforeQuote . nl2br($get_one->quote) . $afterQuote;
			if ( !empty($get_one->author) ) {
				$output .= $beforeAuthor . $Author . $afterAuthor;
			}
			if ( !empty($get_one->source) ) {
				$output .= $beforeSource . $Source . $afterSource;
			}		
			$output .= $afterAll;		
		}		
		
		echo $output;		
	}
}

//print a specific quote
function wp_quotes($id) {

	global $wpdb;

	$widgetTitle = get_option('stray_quotes_widget_title');
	$regularTitle =  get_option('stray_quotes_regular_title');
	$beforeAll =  get_option ('stray_quotes_before_all');
	$beforeAuthor = get_option ('stray_quotes_before_author');
	$afterAuthor = get_option ('stray_quotes_after_author');
	$beforeSource = get_option ('stray_quotes_before_source');
	$afterSource = get_option ('stray_quotes_after_source');
	$beforeQuote = get_option ('stray_quotes_before_quote');
	$afterQuote = get_option ('stray_quotes_after_quote');
	$afterAll = get_option ('stray_quotes_after_all');
	$putQuotesFirst = get_option ('stray_quotes_put_quotes_first');
	$useGoogleLinks = get_option ('stray_quotes_use_google_links');	
	
	$result = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='{$id}'");				
	
	if ( !empty($result) )	{

		$get_one = $result[0];		
		
		if ( !$widgetTitle) {			
			$output = $regularTitle;
		}
		else {
			$output = '';
		}
		
		if ( !$useGoogleLinks) {			
			$Author = $get_one->author;
			$Source = $get_one->source;
		}
		else {
		$Author = '<a href="http://www.google.com/search?q=%22' . str_replace('&', '%26',$get_one->author) . '%22">' . 
					$get_one->author . '</a>';		
		$Source = '<a href="http://www.google.com/search?q=%22' . str_replace('&', '%26',$get_one->source) . '%22">' . 
					$get_one->source . '</a>';		
		}
		
		if ( !$putQuotesFirst) {
			$output .= $beforeAll;
			if ( !empty($get_one->author) ) {
				$output .= $beforeAuthor . $Author . $afterAuthor;
			}
			if ( !empty($get_one->source) ) {
				$output .= $beforeSource . $Source . $afterSource;
			}		

			$output .= $beforeQuote . nl2br($get_one->quote) . $afterQuote;			
			$output .= $afterAll;		
		}
		else {		
			$output .= $beforeAll;		
			$output .= $beforeQuote . nl2br($get_one->quote) . $afterQuote;
			if ( !empty($get_one->author) ) {
				$output .= $beforeAuthor . $Author . $afterAuthor;
			}
			if ( !empty($get_one->source) ) {
				$output .= $beforeSource . $Source . $afterSource;
			}		
			$output .= $afterAll;		
		}		
		
		echo $output;		
	}
}

//spew all the quotes onto a page
function wp_quotes_page($data) {

	$beforeAuthor = get_option ('stray_quotes_before_author');
	$afterAuthor = get_option ('stray_quotes_after_author');
	$beforeSource = get_option ('stray_quotes_before_source');
	$afterSource = get_option ('stray_quotes_after_source');
	$beforeQuote = get_option ('stray_quotes_before_quote');
	$afterQuote = get_option ('stray_quotes_after_quote');
	$useGoogleLinks = get_option ('stray_quotes_use_google_links');	
	
	$start = strpos($data, WP_QUOTES_PAGE);
	
	if ( $start !== false ) {
 
 		global $wpdb;
		
		$result = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE);
		
		if ( !empty($result) ) {
		
			$contents = '';
			foreach ( $result as $row )	{		
	
				$contents .= $beforeQuote . nl2br($row->quote) . $afterQuote;
				if ( !empty($row->author) ) {
									
					if ( !$useGoogleLinks) $author = $row->author;
					else $author = '<a href="http://www.google.com/search?q=%22' . str_replace('&', '%26', $row->author) . '%22">' . $row->author . '</a>';
					
					$contents .= $beforeAuthor . $author . $afterAuthor;
				}		
				if ( !empty($row->source) ) {
									
					if ( !$useGoogleLinks) $source = $row->source;
					else $source = '<a href="http://www.google.com/search?q=%22' . str_replace('&', '%26', $row->source) . '%22">' . $row->source . '</a>';
					
					$contents .= $beforeSource . $source . $afterSource;
				}		

				$contents .= '<br /><br />';
			}
			
		}

		$data = substr_replace($data, $contents, $start, strlen(WP_QUOTES_PAGE));
	}
	
	return $data;
}

//does the widget for the lazy
function stray_quotes_widget_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	function stray_quotes_widget($args) {
	
		extract($args);		
		echo $before_widget;		
		/*retrieve title*/
		$title = get_option('stray_quotes_widget_title');
		if ( $title != '') {
			echo $before_title . $title . $after_title;
		}
		//the actual function		
		if (function_exists('wp_quotes_random')) wp_quotes_random();				
		echo $after_widget;
	}
	
	
	function stray_quotes_widget_control() {

		//update options from the form
		if ( $_POST['stray_quotes_submit'] ) {
		
			$get_title = strip_tags(stripslashes($_POST['stray_quotes_widget_title']));
			update_option('stray_quotes_widget_title', $get_title );
		}
		else {
			$get_title = get_option('stray_quotes_widget_title');		
		}
		
		$title = htmlspecialchars($get_title, ENT_QUOTES);		
		echo '<p style="text-align:left;"><label for="stray_quotes_widget_title">' . __('Title of the widget:') . 
		' <input style="width: 200px;" id="stray_quotes_widget_title" name="stray_quotes_widget_title" type="text" value="'.$title.'" /></label><br/>
		<em>Enter here a optional title for the widget.</em></p>';
		echo '<input type="hidden" id="stray_quotes_submit" name="stray_quotes_submit" value="1" />';		

	}

	register_sidebar_widget(array('Stray Quotes', 'widgets'), 'stray_quotes_widget');
	register_widget_control(array('Stray Quotes', 'widgets'), 'stray_quotes_widget_control', 200, 100);	
}

//paypal and stuff
function donate_block() {
	
	echo ' 
    <div class="wrap">
    
        <div style="width:200px; float:left; margin-top:15px; padding:12px;">
            <strong>Plugin</strong>
            <ul style="list-style:none">
            <li><a href="http://www.italyisfalling.com/stray-quotes">Plugin\'s Homepage</a></li>
            <li><a href="http://wordpress.org/support/">WordPress Support</a></li>
            </ul><br />
        </div>
        
        <div style="width:250px; float:left; border-left:1px; border-left-style:solid; border-left-color:#CCCCCC; margin-top:15px; padding:12px;">
            <strong>Donation</strong>
            <ul style="list-style:none">
            <li><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="image"  style="margin-right:3px; padding:0px; border-width:0px; float:left;" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt=""><img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" style="margin-right:3px; padding:0px; border-width:0px; float:left;">Are you finding this plugin useful? I really hope so. Consider a tip then. I\'ll use it on <s>debaucheries</s> 
            to make it better. Thank you.
            <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
            ">
            </form></li><!--<li><br/><a href="http://www.amazon.com/gp/registry/wishlist/1HLR6WX1Y7F9W/ref=wl_web/">
            <img src="http://ec1.images-amazon.com/images/G/01/gifts/registries/wishlist/v2/web/wl-btn-75-c._V46776201_.gif" width="75" 
            alt="My Amazon.com Wish List" height="35" border="0" style="margin-right:3px; padding:0px; border-width:0px; float:left;" /></a>
            Or you can buy me something on the amazon. Nothing special, maybe a book?<br /></li>-->
            </ul>
        </div>
        
        <div style="width:150px; float:left; border-left:1px; border-left-style:solid; border-left-color:#CCCCCC; margin-top:15px; margin-left:10px;padding:12px;">
            <strong>Miscellaneous</strong>
            <ul style="list-style:none">
            <li><a class="lhome" href="http://www.italyisfalling.com/category/wordpress-things">...more plugins</a></li>
            </ul>
        </div>
    
	<div style="clear:both">&nbsp;</div>
	</div>';
}


?>