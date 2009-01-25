<?php

//this is all about the variables
$quotesoptions = array();
$quotesoptions = get_option('stray_quotes_options');
$regularTitle =  $quotesoptions['stray_quotes_regular_title'];
$widgetTitle = $quotesoptions['stray_quotes_widget_title'];
$beforeAll =  $quotesoptions['stray_quotes_before_all'];
$afterAll = $quotesoptions['stray_quotes_after_all'];
$beforeQuote = $quotesoptions['stray_quotes_before_quote'];
$afterQuote = $quotesoptions['stray_quotes_after_quote'];
$beforeAuthor = $quotesoptions['stray_quotes_before_author'];
$afterAuthor = $quotesoptions['stray_quotes_after_author'];
$beforeSource = $quotesoptions['stray_quotes_before_source'];
$afterSource = $quotesoptions['stray_quotes_after_source'];
$putQuotesFirst = $quotesoptions['stray_quotes_put_quotes_first'];
$defaultVisible = $quotesoptions['stray_quotes_default_visible'];
$linkto = $quotesoptions['stray_quotes_linkto'];
$sourcelinkto = $quotesoptions['stray_quotes_sourcelinkto'];
$sourcespaces = $quotesoptions['stray_quotes_sourcespaces'];	
$authorspaces = $quotesoptions['stray_quotes_authorspaces'];	

//this is called by other functions to output a given quote
function stray_output_one($get_one) {

	global $wpdb,$widgetTitle,$regularTitle,$beforeAll,$beforeAuthor,$afterAuthor,$beforeSource,$afterSource,$beforeQuote,$afterQuote,$afterAll,$putQuotesFirst,$linkto,$sourcelinkto,$sourcespaces,$authorspaces;
	$output = '';
	
	//make or not the author link
	if (!$linkto)$Author = $get_one->author;
	else {
		$Author = $get_one->author;
		if ($authorspaces)$Author =str_replace(" ",$authorspaces,$Author);
		
		$search = array('"', '&', '%AUTHOR%');
		$replace = array('%22','%26', $Author);
		$linkto = str_replace($search,$replace,$linkto);
		$Author = '<a href="'.$linkto.'">' . $get_one->author . '</a>';
	}
	
	//make or not the source link
	if (!$sourcelinkto)$Source = $get_one->source;
	else {
		$Source = $get_one->source;
		if ($sourcespaces)$Source =str_replace(" ",$sourcespaces,$Source);
		
		$search = array('"', '&', '%SOURCE%');
		$replace = array('%22','%26', $Source);
		$sourcelinkto = str_replace($search,$replace,$sourcelinkto);
		$Source = '<a href="'.$sourcelinkto.'">' . $get_one->source . '</a>';
	}
	
	//output the content
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
	
	//end of story
	return $output;		

}

//this prints a random quote from given groups
function stray_random_quote($groups=NULL) {

	global $wpdb;

	//handle the groups
	if ($groups) {
		$groupquery = ' AND `group`="';
		if (is_string($groups))$groups = explode(",", $groups);
		foreach ($groups as $group) {
			$group = trim($group);
			$groupquery .= $group.'" OR `group`="';
		}
		$groupquery = rtrim($groupquery,' OR `group`=""');
		$groupquery .='"';
	} else {
		$groupquery = '';
	}		
	
	//sql the thing
	$sql = "SELECT quoteID,quote,author,source,`group` FROM " . WP_STRAY_QUOTES_TABLE . " WHERE visible='yes'".$groupquery;
	$result = $wpdb->get_results($sql);	
	
	//if the sql has something to say, get to work
	if ( !empty($result) )	{
		
		//create random moment	
		list($usec, $sec) = explode(' ', microtime());
		srand( (double)(float) $sec + ((float) $usec * 100000) );		
		
		//end of it
		$get_one = $result[rand(0, count($result)-1)];
		echo stray_output_one($get_one);
		
	}
}

//this replaces "[random-quote groups=X]" inside a post with a random quote from a given group
function stray_rnd_shortcut($groups=NULL) {
		
	global $wpdb,$wp_version;
	
	//handle the groups
	if ($groups) {
		$groupquery = ' AND `group`="';
		if (is_string($groups)){$groups = explode(",", $groups);}
		foreach ($groups as $group) {
			$group = trim($group);
			$groupquery .= $group.'" OR `group`="';
		}
		$groupquery = rtrim($groupquery,' OR `group`=""');
		$groupquery .='"';
	} else {
		$groupquery = '';
	}		

	//shortcodes are only for WP-2.5+
	if ($wp_version >= 2.5) {
		
		//sql the thing
		$sql = "SELECT quoteID,quote,author,source,`group` FROM " . WP_STRAY_QUOTES_TABLE . " WHERE visible='yes'".$groupquery;
		$result = $wpdb->get_results($sql);	
		
		//if the sql has something to say, get to work
		if ( !empty($result) )	{
				
			//create random moment	
			list($usec, $sec) = explode(' ', microtime());
			srand( (double)(float) $sec + ((float) $usec * 100000) );		
			
			//end of it
			$get_one = $result[rand(0, count($result)-1)];
			echo stray_output_one($get_one);
			
		}
	}
}

//this prints a specific quote
function stray_a_quote($id ='1') {

	global $wpdb;
	
	//sql the thing
	$result = $wpdb->get_results("select quoteID,quote,author,source,`group` from " . WP_STRAY_QUOTES_TABLE . " where quoteID='{$id}'");				
	
	//if the sql has something to say, get to work
	if ( !empty($result) )	{
		
		//end of it
		$get_one = $result[0];	
		echo stray_output_one($get_one);
	}
}

//this replaces "[quote id=XX]" inside a post with a quote whose id corresponds to XX
function stray_id_shortcut($attr='1') {
	
	global $wpdb,$wp_version;

	//shortcodes are only for WP 2.5+
	if ($wp_version >= 2.5) {
		
		//sql the thing
		$result = $wpdb->get_results("select quoteID,quote,author,source,`group` from " . WP_STRAY_QUOTES_TABLE . " where quoteID=". $attr['id']);				
		
		if ( !empty($result) )	{
		
			//end of it
			$get_one = $result[0];					
			echo stray_output_one($get_one);
		}

	}	
}

//this replaces "[all-quotes]" in a post with all the quotes
function stray_page_shortcut($data) {

	global $wpdb,$wp_version;
	
	//sql the thing
	$result = $wpdb->get_results("select quoteID,quote,author,source,`group` from " . WP_STRAY_QUOTES_TABLE. " where visible='yes'");
	
	if ( !empty($result) ) {
	
		$contents = '<ul>';
		foreach ( $result as $get_one )$contents .= '<li>'.stray_output_one($get_one).'</li>';	
		$contents .= '</ul>';
		
		//if it is not WP 2.5 do the old thing
		if ($wp_version <= 2.3) {
			$start = strpos($data, "<!--wp_quotes_page-->");
			if ( $start !== false ) $data = substr_replace($data, $contents, $start, strlen("<!--wp_quotes_page-->"));		
		}		

		if ($wp_version <= 2.3) echo $data;
		else echo $contents;
	
	}	
}

//this is for compatibility with old function names
function wp_quotes_random() {return stray_random_quote('all');}
function wp_quotes($id) {return stray_a_quote($id);}
function wp_quotes_page($data) {return stray_page_shortcut($data);}

//this creates a list of unique groups
function make_groups() {
	global $wpdb;
	$allgroups = $wpdb->get_col("SELECT `group` FROM " . WP_STRAY_QUOTES_TABLE);
	$uniquegroups = array_unique($allgroups);
	return $uniquegroups;
}

?>