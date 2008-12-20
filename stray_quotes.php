<?php
/*
Plugin Name: Stray Random Quotes
Plugin URI: http://www.italyisfalling.com/stray-random-quotes/
Description: Displays random quotes on your blog. Easy to custom and manage. Compatible with Wordpress 2.5.
Author: Corpodibacco
Author URI: http://www.italyisfalling.com/coding/
Version: 1.6.4
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
	$moFile = ABSPATH . 'wp-content/plugins/' . $dir . 'stray-quotes-' . $currentLocale . ".mo";
	//check if it is a window server and changes path accordingly
	if ( strpos($moFile, '\\')) $moFile = str_replace('/','\\',$moFile); 
	if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('stray-quotes', $moFile);
}
	
//add options and defaults if they do not exist
add_option('stray_quotes_before_all', '<div align="right">');
add_option('stray_quotes_before_quote', '&#8220;');
add_option('stray_quotes_after_quote', '&#8221;');
add_option('stray_quotes_before_author', '<br/>by&nbsp;');
add_option('stray_quotes_after_author', '');
add_option('stray_quotes_before_source', ',<em>&nbsp;');
add_option('stray_quotes_after_source', '</em>');
add_option('stray_quotes_after_all','</div>');
add_option('stray_quotes_widget_title', 'Random Quote');
add_option('stray_quotes_regular_title', '<h2>Random Quote</h2>');
add_option('stray_quotes_put_quotes_first','Y');
add_option('stray_quotes_use_google_links','');
add_option('stray_quotes_default_visible','');
add_option('stray_quotes_wiki_lan','en');

//check if table exists and alter it if necessary	
$straytableExists = false;
$straytables = $wpdb->get_results("SHOW TABLES");
foreach ( $straytables as $straytable ){	
	foreach ( $straytable as $value ){
	
		if ( $value == WP_QUOTES_TABLE ){			
			$straytableExists = true;	
			//if table exists it must be old -- must update and rename.
			$wpdb->query('ALTER TABLE ' . WP_QUOTES_TABLE . ' ADD COLUMN `source` VARCHAR( 255 ) NOT NULL AFTER `author`');
			$wpdb->query('RENAME TABLE ' . WP_QUOTES_TABLE . ' TO ' . WP_STRAY_QUOTES_TABLE);
			
			//1 = first time, 2 = altered table, 3 = everything is cool
			update_option('stray_quotes_first_time', 2);				
			break;
		}
		elseif ( $value == WP_STRAY_QUOTES_TABLE ){			
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
	`visible` ENUM( 'yes', 'no' ) NOT NULL ,
	PRIMARY KEY ( `quoteID` ) )
	");
	
	//1 = first time, 2 = altered table, 3 = everything is cool
	update_option('stray_quotes_first_time', 1);
}

//includes
include('stray_functions.php');
include('stray_pages.php');

//add headers
function stray_quotes_header() {

	?><link rel="stylesheet" type="text/css" href="<?php echo WP_STRAY_QUOTES_PATH ?>straystyle.css" />	
	<script type="text/javascript"> function toggleMe(a){ var e=document.getElementById(a); if(!e)return true; if(e.style.display=="block"){ e.style.display="none" } else{ e.style.display="block" } return true; } </script><?php 	
}

//build submenu entries
function stray_quotes_add_pages() {

	add_options_page('Stray Random Quotes', 'Stray Random Quotes', 9, basename(__FILE__), 'stray_quotes_options');
	add_management_page('Stray Random Quotes', __('Quotes', 'stray-quotes'), 9, basename(__FILE__), 'stray_quotes_manage');		
}

//add actions and shortcodes to wordpress
add_action('widgets_init', 'stray_quotes_widget_init');
add_action('admin_menu', 'stray_quotes_add_pages');
add_action('admin_head', 'stray_quotes_header');
if ($wp_version >= 2.5) {
	add_shortcode('quote', 'stray_id_shortcut');
	add_shortcode('random-quote', 'stray_rnd_shortcut');
	add_shortcode('all-quotes', 'stray_page_shortcut');
}

//for compatibility
if ($wp_version <= 2.3 ) add_filter('the_content', 'wp_quotes_page', 10);

?>