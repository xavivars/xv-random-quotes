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
/*
* IMPORTANT ACKNOWLEDGMENTS *
This plugin is a spin-off of version 1.3 of the wordpress plugin 'Random Quotes' by Dustin Barnes (http://www.zombierobot.com/wp-quotes/). 
I've been using 'Random Quotes' for a long time... I wanted to ask mr. Barnes permission to take his project in my hands but I couldn't find his email address on his blog. His blog hasn't been updated for over a year,  and his plugin hasn't been updated for over two years, and I really wanted to solve a couple of bugs and limitations of this great plugin. So, here it is, Stray Quotes. I am going to take this plugin to another level. (Ok, sorry, bragging again).
*/

//few definitions
define('WP_QUOTES_TABLE', $table_prefix . 'quotes');
define('WP_STRAY_QUOTES_TABLE', $table_prefix . 'stray_quotes');
define('WP_QUOTES_PAGE', '<!--wp_quotes_page-->');
$dir = basename(dirname(__FILE__));
if ($dir == 'plugins') $dir = '';
else $dir = $dir . '/';	
define('WP_STRAY_QUOTES_PATH', get_option('siteurl') . '/wp-content/plugins/' . $dir);
	
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

//check if table exists and alter it if necessary	
$tableExists = false;
$tables = $wpdb->get_results("SHOW TABLES");
foreach ( $tables as $table ){	
	foreach ( $table as $value ){
	
		if ( $value == WP_QUOTES_TABLE ){			
			$tableExists = true;	
			//if table exists it must be old -- must update and rename.
			$wpdb->query('ALTER TABLE ' . WP_QUOTES_TABLE . ' ADD COLUMN `source` VARCHAR( 255 ) NOT NULL AFTER `author`');
			$wpdb->query('RENAME TABLE ' . WP_QUOTES_TABLE . ' TO ' . WP_STRAY_QUOTES_TABLE);
			
			//1 = first time, 2 = altered table, 3 = everything is cool
			update_option('stray_quotes_first_time', 2);				
			break;
		}
		elseif ( $value == WP_STRAY_QUOTES_TABLE ){			
			$tableExists=true;
			break;
		}		
	}
}

//table does not exist, create one
if ( !$tableExists ) {
	
	$wpdb->query('
	CREATE TABLE IF NOT EXISTS `'. WP_STRAY_QUOTES_TABLE . '` (
	`quoteID` INT NOT NULL AUTO_INCREMENT ,
	`quote` TEXT NOT NULL ,
	`author` varchar( 255 ) NOT NULL ,
	`source` varchar( 255 ) NOT NULL ,
	`visible` ENUM( "yes", "no" ) NOT NULL ,
	PRIMARY KEY ( `quoteID` ) )
	');
	
	//1 = first time, 2 = altered table, 3 = everything is cool
	update_option('stray_quotes_first_time', 1);
}

//includes
include('stray_functions.php');
include('stray_pages.php');

//add headers
function stray_quotes_header() {

	echo "\n" . '<link rel="stylesheet" type="text/css" href="' . WP_STRAY_QUOTES_PATH . 'straystyle.css" />';	
}

//build sumenu entries
function stray_quotes_add_pages() {

	add_options_page('Stray Quotes', 'Stray Quotes', 9, basename(__FILE__), 'stray_quotes_options');
	add_management_page('Stray Quotes', 'Stray Quotes', 9, basename(__FILE__), 'stray_quotes_manage');		
}

//add actions to wordpress
add_action('widgets_init', 'stray_quotes_widget_init');
add_action('admin_menu', 'stray_quotes_add_pages');
add_action('admin_head', 'stray_quotes_header');
add_filter('the_content', 'wp_quotes_page', 10);

?>