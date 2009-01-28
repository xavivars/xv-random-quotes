<?php
/*
Plugin Name: Stray Random Quotes
Plugin URI: http://www.italyisfalling.com/stray-random-quotes/
Description: Displays random quotes on your blog. Easy to custom and manage. Compatible with Wordpress 2.7.
Author: Corpodibacco
Author URI:http://www.italyisfalling.com/coding/
Version: 1.7.1
License: GPL compatible
*/

global $wpdb, $wp_version;

//few definitions
define("WP_QUOTES_TABLE", $wpdb->prefix . "quotes");
define("WP_STRAY_QUOTES_TABLE", $wpdb->prefix . "stray_quotes");
$dir = basename(dirname(__FILE__));
if ($dir == 'plugins') $dir = '';
else $dir = $dir . '/';	
define("WP_STRAY_QUOTES_PATH", get_option("siteurl") . "/wp-content/plugins/" . $dir);

//prepare for local
$currentLocale = get_locale();
if(!empty($currentLocale)) {
	$moFile = ABSPATH . 'wp-content/plugins/' . $dir . 'lang/stray-quotes-' . $currentLocale . ".mo";
	//check if it is a window server and changes path accordingly
	if ( strpos($moFile, '\\')) $moFile = str_replace('/','\\',$moFile); 
	if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('stray-quotes', $moFile);
}

//add header
function stray_quotes_header() {

	?><script type="text/javascript">function switchpage(select) {var index;for(index=0; index<select.options.length; index++)if(select.options[index].selected){if(select.options[index].value!="")window.location.href=select.options[index].value;break;}}</script><?php 	
	
}

//upon activation
function quotes_activation() {

	global $wpdb;
	
	//convert old options into new shiny array ones
	$quotesoptions = get_option('stray_quotes_options');
	
	if (false === $quotesoptions || !is_array($quotesoptions) ) {
		
		$quotesoptions = array();
		
		$var_1 = 'stray_quotes_before_all';
		$temp_1 = get_option($var_1);
		if (false === $temp_1) $quotesoptions[$var_1] =  '<div align="right">';
		else $quotesoptions[$var_1] = $temp_1;
		delete_option($var_1);
		
		$var_2 = 'stray_quotes_before_quote';
		$temp_2 = get_option($var_2);
		if (false === $temp_2) $quotesoptions[$var_2] =  '&#8220;';
		else $quotesoptions[$var_2] = $temp_2;
		delete_option($var_2);
		
		$var_3 = 'stray_quotes_after_quote';
		$temp_3 = get_option($var_3);
		if (false === $temp_3) $quotesoptions[$var_3] =  '&#8221;';
		else $quotesoptions[$var_3] = $temp_3;
		delete_option($var_3);
		
		$var_4 = 'stray_quotes_before_author';
		$temp_4 = get_option($var_4);
		if (false === $temp_4) $quotesoptions[$var_4] =  '<br/>by&nbsp;';
		else $quotesoptions[$var_4] = $temp_4;
		delete_option($var_4);
		
		$var_5 = 'stray_quotes_after_author';
		$temp_5 = get_option($var_5);
		if (false === $temp_5) $quotesoptions[$var_5] =  '';
		else $quotesoptions[$var_5] = $temp_5;
		delete_option($var_5);
		
		$var_6 = 'stray_quotes_before_source';
		$temp_6 = get_option($var_6);
		if (false === $temp_6) $quotesoptions[$var_6] =  '<em>&nbsp;';
		else $quotesoptions[$var_6] = $temp_6;
		delete_option($var_6);
		
		$var_7 = 'stray_quotes_after_source';
		$temp_7 = get_option($var_7);
		if (false === $temp_7) $quotesoptions[$var_7] =  '</em>';
		else $quotesoptions[$var_7] = $temp_7;
		delete_option($var_7);
		
		$var_8 = 'stray_quotes_after_all';
		$temp_8 = get_option($var_8);
		if (false === $temp_8) $quotesoptions[$var_8] =  '</div>';
		else $quotesoptions[$var_8] = $temp_8;
		delete_option($var_8);
		
		$var_9 = 'stray_quotes_widget_title';
		$temp_9 = get_option($var_9);
		if (false === $temp_9) $quotesoptions[$var_9] =  'Random Quote';
		else $quotesoptions[$var_9] = $temp_9;
		delete_option($var_9);
		
		$var_10 = 'stray_quotes_regular_title';
		$temp_10 = get_option($var_10);
		if (false === $temp_10) $quotesoptions[$var_10] =  '<h2>Random Quote</h2>';
		else $quotesoptions[$var_10] = $temp_10;
		delete_option($var_10);
		
		$var_11 = 'stray_quotes_put_quotes_first';
		$temp_11 = get_option($var_11);
		if (false === $temp_11) $quotesoptions[$var_11] =  'Y';
		else $quotesoptions[$var_11] = $temp_11;
		delete_option($var_11);
		
		$var_13 = 'stray_quotes_default_visible';
		$temp_13 = get_option($var_13);
		if (false === $temp_13) $quotesoptions[$var_13] =  'Y';
		else $quotesoptions[$var_13] = $temp_13;
		delete_option($var_13);
		
		//new entries
		$quotesoptions['stray_quotes_uninstall'] = '';
		$quotesoptions['stray_quotes_order'] = 'quoteID';
		$quotesoptions['stray_quotes_rows'] = 10; 
		$quotesoptions['stray_quotes_groups'] = 'all';
		$quotesoptions['stray_quotes_sort'] = 'DESC';
		$quotesoptions['stray_quotes_version'] = 171; 
				
		//special trasformation for how link options work now
		$var_12 = 'stray_quotes_use_google_links';
		$temp_12 = get_option($var_12);
		$var_14 = 'stray_quotes_wiki_lan';		
		$temp_14 = get_option($var_14);
		if ($temp12 == 'Y') {
			$quotesoptions['stray_quotes_linkto'] = '<a href="http://www.google.com/search?q=&quot;%AUTHOR%&quot;">';
			$quotesoptions['stray_quotes_sourcelinkto'] = '<a href="http://www.google.com/search?q=&quot;%SOURCE%&quot;">';
			$quotesoptions['stray_quotes_sourcespaces'] = ' ';	
			$quotesoptions['stray_quotes_authorspaces'] = ' ';		
		} 
		
		else if ($temp12 == 'W') {
			$quotesoptions['stray_quotes_linkto'] = '<a href="http://'.$temp_14.'.wikipedia.org/wiki/%AUTHOR%';
			$quotesoptions['stray_quotes_linkto'] = '<a href="http://'.$temp_14.'.wikipedia.org/wiki/%SOURCE%';
			$quotesoptions['stray_quotes_sourcespaces'] = '_';	
			$quotesoptions['stray_quotes_authorspaces'] = '_';		
		}
		
		else {
			$quotesoptions['stray_quotes_linkto'] =  '';
			$quotesoptions['stray_quotes_sourcelinkto'] =  '';
			$quotesoptions['stray_quotes_sourcespaces'] = '-';	
			$quotesoptions['stray_quotes_authorspaces'] = '-';		
		}
		delete_option($var_12);
		delete_option($var_14);
				
		$quotesoptions['stray_quotes_first_time'] = 4;
		delete_option('stray_quotes_first_time');
				
	}
	//if the new post 1.7 array options already exist
	else {
		//take care of version number
		if($quotesoptions['stray_quotes_version'] <= 170 || $quotesoptions['stray_quotes_version'] != 171) {
			$quotesoptions['stray_quotes_version'] = 171; 
		}
	}

	//check if table exists and alter it if necessary	
	$straytableExists = false;
	$straytables = $wpdb->get_results("SHOW TABLES");
	foreach ( $straytables as $straytable ){	
		foreach ( $straytable as $value ){
			
			//takes care of the old wp_quotes table (probably useless)
			if ( $value == WP_QUOTES_TABLE ){
					
				$straytableExists = true;	
				//if table exists it must be old -- must update and rename.
				$wpdb->query('ALTER TABLE ' . WP_QUOTES_TABLE . ' ADD COLUMN `source` VARCHAR( 255 ) NOT NULL AFTER `author`');
				$wpdb->query('ALTER TABLE ' . WP_QUOTES_TABLE . ' ADD COLUMN `group` VARCHAR( 255 ) NOT NULL  DEFAULT "default" AFTER `source`');			
				
				//and fill in default values
				$wpdb->query('UPDATE '. WP_QUOTES_TABLE . ' SET `group`="default"');				
				$wpdb->query('RENAME TABLE ' . WP_QUOTES_TABLE . ' TO ' . WP_STRAY_QUOTES_TABLE);
				
				//1 = first time, 2 = altered old table, 3 = altered new table =, 4 = we're cool
				$quotesoptions['stray_quotes_first_time'] = 2;				
				break;
			}
			
			//takes care of the new table
			if ( $value == WP_STRAY_QUOTES_TABLE ){			
				
				$groupCol = $wpdb->get_col('SELECT `group` FROM '.WP_STRAY_QUOTES_TABLE);
				if (!$groupCol) {
					//add new field
					$wpdb->query('ALTER TABLE ' . WP_STRAY_QUOTES_TABLE . ' ADD COLUMN `group` VARCHAR( 255 ) NOT NULL DEFAULT "default" AFTER `source`');
					//and fill in default group values
					$wpdb->query('UPDATE '. WP_STRAY_QUOTES_TABLE . ' SET `group`="default"');
				}
						
				//1 = first time, 2 = altered old table, 3 = altered new table =, 4 = we're cool
				$quotesoptions['stray_quotes_first_time'] = 3;
				$straytableExists=true;			
				break;	
			}		
		}
	}
	
	//table does not exist, create one
	if ( !$straytableExists ) {
		
		$wpdb->query("
		CREATE TABLE IF NOT EXISTS `". WP_STRAY_QUOTES_TABLE . "` (
		`quoteID` INT NOT NULL AUTO_INCREMENT ,
		`quote` TEXT NOT NULL ,
		`author` varchar( 255 ) NOT NULL ,
		`source` varchar( 255 ) NOT NULL ,
		`group` varchar( 255 ) NOT NULL  DEFAULT 'default',
		`visible` ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'yes',
		PRIMARY KEY ( `quoteID` ) )
		");
		
		//insert sample quote
		$wpdb->query("INSERT INTO " . WP_STRAY_QUOTES_TABLE . " (
		`quote`, `author`, `source`) values ('Always tell the truth. Then you don\'t have to remember anything.', 'Mark Twain', 'Roughin it') ");
		
		//1 = first time, 2 = altered old table, 3 = altered new table =, 4 = we're cool
		$quotesoptions['stray_quotes_first_time'] = 1;		
	}
		
	//and finally we actually put the option thing in the database
	update_option('stray_quotes_options', $quotesoptions);		
	
}

//upon deactivation
function quotes_deactivation() {

	global $wpdb;

	$quotesoptions = get_option('stray_quotes_options');
	$widgetsoptions =  get_option('stray_quotes_widgets');
	$sql = "DROP TABLE IF EXISTS ".WP_STRAY_QUOTES_TABLE;
	
	//delete the options
	if($quotesoptions['uninstall'] == 'options') {
		delete_option('stray_quotes_options');
		delete_option('stray_quotes_widgets');
	}
	else if ($quotesoptions['uninstall'] == 'table')$wpdb->query($sql);
	else if ($quotesoptions['uninstall'] == 'both'){
		 delete_option('stray_quotes_options');
		 delete_option('stray_quotes_widgets');
		$wpdb->query($sql);
	}

}
	
//for compatibility
if ($wp_version <= 2.3 ) add_filter('the_content', 'wp_quotes_page', 10);

//build submenu entries
function stray_quotes_add_pages() {

	add_menu_page('Stray Random Quotes', 'Quotes', 8, __FILE__, 'stray_intro', WP_STRAY_QUOTES_PATH.'/img/lightbulb.png');
	add_submenu_page(__FILE__, __('Overview','stray-quotes'), __('Overview','stray-quotes'), 8, __FILE__, 'stray_intro');
	add_submenu_page(__FILE__, __('Manage','stray-quotes'), __('Manage','stray-quotes'), 8, 'stray_manage', 'stray_manage');
	add_submenu_page(__FILE__, __('Add New','stray-quotes'), __('Add New','stray-quotes'), 8, 'stray_new', 'stray_new');
	add_submenu_page(__FILE__, __('Settings','stray-quotes'), __('Settings','stray-quotes'), 8, 'stray_quotes_options', 'stray_quotes_options'); 	
	
}

//includes
include('inc/stray_functions.php');
include('inc/stray_overview.php');
include('inc/stray_settings.php');
include('inc/stray_manage.php');
include('inc/stray_editor.php');
include('inc/stray_new.php');
include('inc/stray_widgets.php');

//excuse me, I'm hooking into wordpress
add_action('admin_menu', 'stray_quotes_add_pages');
add_action('admin_head', 'stray_quotes_header');
if ($wp_version >= 2.5) {
	add_shortcode('quote', 'stray_id_shortcut');
	add_shortcode('random-quote', 'stray_rnd_shortcut');
	add_shortcode('all-quotes', 'stray_page_shortcut');
}
register_activation_hook(__FILE__, 'quotes_activation');
register_deactivation_hook(__FILE__, 'quotes_deactivation');

?>