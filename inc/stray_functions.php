<?php

//this is all about the variables
$quotesoptions = array();
$quotesoptions = get_option('stray_quotes_options');
$regularTitle =  utf8_decode($quotesoptions['stray_quotes_regular_title']);
$widgetTitle = utf8_decode($quotesoptions['stray_quotes_widget_title']);
$beforeAll =  utf8_decode($quotesoptions['stray_quotes_before_all']);
$afterAll = utf8_decode($quotesoptions['stray_quotes_after_all']);
$beforeQuote = utf8_decode($quotesoptions['stray_quotes_before_quote']);
$afterQuote = utf8_decode($quotesoptions['stray_quotes_after_quote']);
$beforeAuthor = utf8_decode($quotesoptions['stray_quotes_before_author']);
$afterAuthor = utf8_decode($quotesoptions['stray_quotes_after_author']);
$beforeSource = utf8_decode($quotesoptions['stray_quotes_before_source']);
$afterSource = utf8_decode($quotesoptions['stray_quotes_after_source']);
$putQuotesFirst = utf8_decode($quotesoptions['stray_quotes_put_quotes_first']);
$defaultVisible = utf8_decode($quotesoptions['stray_quotes_default_visible']);
$linkto = utf8_decode($quotesoptions['stray_quotes_linkto']);
$sourcelinkto = utf8_decode($quotesoptions['stray_quotes_sourcelinkto']);
$sourcespaces = utf8_decode($quotesoptions['stray_quotes_sourcespaces']);	
$authorspaces = utf8_decode($quotesoptions['stray_quotes_authorspaces']);
$ifnoauthor = utf8_decode($quotesoptions['stray_if_no_author']);		

//this is called by other functions to output a given quote
function stray_output_one($get_one) {

	global $wpdb,$widgetTitle,$regularTitle,$beforeAll,$beforeAuthor,$afterAuthor,$beforeSource,$afterSource,$beforeQuote,$afterQuote,$afterAll,$putQuotesFirst,$linkto,$sourcelinkto,$sourcespaces,$authorspaces,$ifnoauthor;
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
		
		//if author
		if ( !empty($get_one->author) ) {
			$output .= $beforeAuthor . $Author . $afterAuthor;
			//source values if there is an author
			if ( !empty($get_one->source) ) {
				$output .= $beforeSource . $Source . $afterSource;
			}				
		//source values if there is no author	
		} else {
			if ( !empty($get_one->source) ) {
				$output .= $ifnoauthor . $Source . $afterSource;
			}				
		}

		$output .= $beforeQuote . nl2br($get_one->quote) . $afterQuote;			
		$output .= $afterAll;		
	}
	
	//quote first
	else {	
		
		$output .= $beforeAll;		
		$output .= $beforeQuote . nl2br($get_one->quote) . $afterQuote;
		//if author
		if ( !empty($get_one->author) ) {
			$output .= $beforeAuthor . $Author . $afterAuthor;
			//source values if there is an author
			if ( !empty($get_one->source) ) {
				$output .= $beforeSource . $Source . $afterSource;
			}				
		//source values if there is no author	
		} else {
			if ( !empty($get_one->source) ) {
				$output .= $ifnoauthor . $Source . $afterSource;
			}				
		}
		$output .= $afterAll;		
	}		
	
	//end of story
	return $output;		

}

//this prints a random quote from given categories
function stray_random_quote($categories=NULL) {

	global $wpdb;

	//handle the categories
	if ($categories) {
		$categoryquery = ' AND `category`="';
		if (is_string($categories))$categories = explode(",", $categories);
		foreach ($categories as $category) {
			$category = trim($category);
			$categoryquery .= $category.'" OR `category`="';
		}
		$categoryquery = rtrim($categoryquery,' OR `category`=""');
		$categoryquery .='"';
	} else {
		$categoryquery = '';
	}		
	
	//sql the thing
	$sql = "SELECT quoteID,quote,author,source,`category` FROM " . WP_STRAY_QUOTES_TABLE . " WHERE visible='yes'".$categoryquery;
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

//this replaces "[random-quote categories=X]" inside a post with a random quote from a given category
function stray_rnd_shortcut($categories=NULL) {
		
	global $wpdb,$wp_version;
	
	//handle the categories
	if ($categories) {
		$categoryquery = ' AND `category`="';
		if (is_string($categories)){$categories = explode(",", $categories);}
		foreach ($categories as $category) {
			$category = trim($category);
			$categoryquery .= $category.'" OR `category`="';
		}
		$categoryquery = rtrim($categoryquery,' OR `category`=""');
		$categoryquery .='"';
	} else {
		$categoryquery = '';
	}		

	//shortcodes are only for WP-2.5+
	if ($wp_version >= 2.5) {
		
		//sql the thing
		$sql = "SELECT quoteID,quote,author,source,`category` FROM " . WP_STRAY_QUOTES_TABLE . " WHERE visible='yes'".$categoryquery;
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
	$result = $wpdb->get_results("select quoteID,quote,author,source,`category` from " . WP_STRAY_QUOTES_TABLE . " where quoteID='{$id}'");				
	
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
		$result = $wpdb->get_results("select quoteID,quote,author,source,`category` from " . WP_STRAY_QUOTES_TABLE . " where quoteID=". $attr['id']);				
		
		if ( !empty($result) )	{
		
			//end of it
			$get_one = $result[0];					
			return stray_output_one($get_one);
		}

	}	
}

//this replaces "[all-quotes rows=10, orderby="quoteID", sort="ASC", category="all"]" in a post with all the quotes
function stray_page_shortcut($atts, $content = NULL) {

	global $wpdb,$wp_version;
	$quotesoptions = get_option('stray_quotes_options');
	
	//shortcodes are only for WP 2.5+
	if ($wp_version >= 2.5) {
	
		extract(shortcode_atts(array(
			"rows" => 10,
			"orderby" =>'quoteID',
			"sort" => 'ASC',
			"categories" => ''
		), $atts));
	
		// prepares category for sql
		$where = '';
		if ($categories == 'all' || $categories == '') $where = " WHERE visible='yes'";
		else $where = " WHERE `category`='" . $categories . "' AND visible='yes'";
		
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
		$baseurl = $_SERVER['REQUEST_URI'];
		if (strpos( $_SERVER['REQUEST_URI'],'?'))$urlpages = $baseurl.'&qp=';
		else $urlpages = $baseurl.'?qp=';
		
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
		$sql = "SELECT quoteID,quote,author,source,`category` FROM " 
		. WP_STRAY_QUOTES_TABLE. $where 
		. " ORDER BY `". $orderby ."`"
		. $sort 
		. " LIMIT " . $offset. ", ". $rows;
		$result = $wpdb->get_results($sql);
		
		$contents = '';		
		if ( !empty($result) ) {
			
			//uncomment this to have the navigation also above the list.
			/*$contents .= '<p>'.$first . $prev . $nav . $next . $last.'</p>';*/
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

//this creates a list of unique categories
function make_categories() {
	global $wpdb;
	$allcategories = $wpdb->get_col("SELECT `category` FROM " . WP_STRAY_QUOTES_TABLE);
	$uniquecategories = array_unique($allcategories);
	return $uniquecategories;
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
	$atleastthree = false;
	
	while(NULL !== key($array)) {
		if($val > $max)$max = $val;
		elseif($val < $min)$min = $val;
		if ($val > 3) $atleastthree = true;
		$val = next($array);
		
	}
	if ($atleastthree == true) {
		$keys = array_keys($array, $max);
		$maxvalue = $keys[0];
		return $maxvalue;	
	} else return false;
}

?>