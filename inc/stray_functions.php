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
	if ( $get_one->author ) {
		if (!$linkto || strpos('<a href=',$get_one->author))$Author = $get_one->author;
		else {
			$Author = $get_one->author;
			if ($authorspaces)$Author =str_replace(" ",$authorspaces,$Author);
			
			$search = array('"', '&', '%AUTHOR%');
			$replace = array('%22','%26', $Author);
			$linkto = str_replace($search,$replace,$linkto);
			$Author = '<a href="'.htmlentities($linkto).'">' . $get_one->author . '</a>';
		}
	}
	
	//make or not the source link
	if ( $get_one->source ) {
		if (!$sourcelinkto || strpos('<a href=',$get_one->source))$Source = $get_one->source;
		else {
			$Source = $get_one->source;
			if ($sourcespaces)$Source =str_replace(" ",$sourcespaces,$Source);
			
			$search = array('"', '&', '%SOURCE%');
			$replace = array('%22','%26', $Source);
			$sourcelinkto = str_replace($search,$replace,$sourcelinkto);
			$Source = '<a href="'.htmlentities($sourcelinkto).'">' . $get_one->source . '</a>';
		}
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
			return stray_output_one($get_one);
			
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
			return stray_output_one($get_one);
		}

	}	
}

//this replaces "[all-quotes rows=10, orderby="quoteID", sort="ASC", group="all"]" in a post with all the quotes
function stray_page_shortcut($atts, $content = NULL) {

	global $wpdb,$wp_version;
	$quotesoptions = get_option('stray_quotes_options');
	
	//shortcodes are only for WP 2.5+
	if ($wp_version >= 2.5) {
	
		extract(shortcode_atts(array(
			"rows" => 10,
			"orderby" =>'quoteID',
			"sort" => 'ASC',
			"groups" => ''
		), $atts));
	
		// prepares group for sql
		$where = '';
		if ($groups == 'all' || $groups == '') $where = " WHERE visible='yes'";
		else $where = " WHERE `group`='" . $groups . "' AND visible='yes'";
		
		//what page number?
		$pages = 1;
		if(isset($_GET['qp']))$pages = $_GET['qp'];	
		$offset = ($pages - 1) * $rows;
		
		// how many rows we have in database?
		$result = $wpdb->get_results("select quoteID from " . WP_STRAY_QUOTES_TABLE . $where);
		$numrows = count($result);
		
		//temporary workaround for the "division by zero" problem
		if (is_string($rows))$rows=intval($rows);
		settype($rows, "integer"); 
		
		// how many pages we have when using paging?
		if ($rows == NULL || $rows < 10) $rows = 10; 
		$maxPage = ceil($numrows/intval($rows));
		
		// print the link to access each page
		$nav  = '';
		
		$baseurl = $_SERVER['PHP_SELF'];
		$urlpages = $baseurl.'&qp=';
		
		for($quotepage = 1; $quotepage <= $maxPage; $quotepage++) {
		   if ($quotepage == $pages)$nav .= $quotepage; // no need to create a link to current page
		   else $nav .= ' <a href="'.$urlpages.$quotepage.'">'.$quotepage.'</a> ';
		}
		
		if ($pages > 1) {
		   $quotepage  = $pages - 1;
		   $prev  = ' <a href="'.$urlpages.$quotepage.'">Previous '.$rows.'</a> | ';		
		   $first = ' <a href="'.$urlpages.'1">First</a> | ';
		}
		else {
		   $prev  = '&nbsp;'; // we're on page one, don't print previous link
		   $first = '&nbsp;'; // nor the first page link
		}
		
		if ($pages < $maxPage) {
		
			$missing = $numrows-($rows*$pages);		
			if ($missing > $rows) $missing = $rows;
			
			$quotepage = $pages + 1;
			$next = ' | <a href="'.$urlpages.$quotepage.'"> Next '.$missing.'</a> ';
			
			$last = ' | <a href="'.$urlpages.$maxPage.'"> Last</a> ';
		}
		else {
		   $next = '&nbsp;'; // we're on the last page, don't print next link
		   $last = '&nbsp;'; // nor the last page link
		}		
		$sql = "SELECT quoteID,quote,author,source,`group` FROM " 
		. WP_STRAY_QUOTES_TABLE. $where 
		. " ORDER BY `". $orderby ."`"
		. $sort 
		. " LIMIT " . $offset. ", ". $rows;
		$result = $wpdb->get_results($sql);
		
		if ( !empty($result) ) {
			
			$contents = '<p>'.$first . $prev . $nav . $next . $last.'</p>';
			$contents .= '<ul>';
			foreach ( $result as $get_one )$contents .= '<li>'.stray_output_one($get_one).'</li>';	
			$contents .= '</ul><p>'.$first . $prev . $nav . $next . $last.'</p>';;
			return $contents;
		
		}
	}
}

//this is for compatibility with old function names
function wp_quotes_random() {return stray_random_quote();}
function wp_quotes($id) {return stray_a_quote($id);}
function wp_quotes_page($data) {return stray_page_shortcut();}

//this creates a list of unique groups
function make_groups() {
	global $wpdb;
	$allgroups = $wpdb->get_col("SELECT `group` FROM " . WP_STRAY_QUOTES_TABLE);
	$uniquegroups = array_unique($allgroups);
	return $uniquegroups;
}

//this finds the most used value in a column
function mostused($field) {

	global $wpdb;

	$sql = 'SELECT `'.$field.'` FROM ' . WP_STRAY_QUOTES_TABLE . ' WHERE `'.$field.'` IS NOT NULL AND `'.$field.'` !=""' ;
	$all = $wpdb->get_col($sql);
	$array = array_count_values($all);
	
	reset($array);
	if(FALSE === key($array)) {
		return array('min' => NULL, 'max' => NULL);
	}
	
	$min = $max = current($array);
	$val = next($array);
	$atleastonerepeat = false;
	
	while(NULL !== key($array)) {
		if($val > $max)$max = $val;
		elseif($val < $min)$min = $val;
		if ($val > 1) $atleastonerepeat = true;
		$val = next($array);
		
	}
	if ($atleastonerepeat == true) {
		$keys = array_keys($array, $max);
		$maxvalue = $keys[0];
		return $maxvalue;	
	} else return false;
}

?>