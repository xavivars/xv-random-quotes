<?php

//embed a random quote
function wp_quotes_random() {

	global $wpdb;

	$widgetTitle = get_option("stray_quotes_widget_title");
	$regularTitle =  get_option("stray_quotes_regular_title");
	$beforeAll =  get_option ("stray_quotes_before_all");
	$beforeAuthor = get_option ("stray_quotes_before_author");
	$afterAuthor = get_option ("stray_quotes_after_author");
	$beforeSource = get_option ("stray_quotes_before_source");
	$afterSource = get_option ("stray_quotes_after_source");
	$beforeQuote = get_option ("stray_quotes_before_quote");
	$afterQuote = get_option ("stray_quotes_after_quote");
	$afterAll = get_option ("stray_quotes_after_all");
	$putQuotesFirst = get_option ("stray_quotes_put_quotes_first");
	$useGoogleLinks = get_option ("stray_quotes_use_google_links");	
	
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

	$beforeAuthor = get_option ("stray_quotes_before_author");
	$afterAuthor = get_option ("stray_quotes_after_author");
	$beforeSource = get_option ("stray_quotes_before_source");
	$afterSource = get_option ("stray_quotes_after_source");
	$beforeQuote = get_option ("stray_quotes_before_quote");
	$afterQuote = get_option ("stray_quotes_after_quote");
	$useGoogleLinks = get_option ("stray_quotes_use_google_links");	
	
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
		//retrieve title
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

?>