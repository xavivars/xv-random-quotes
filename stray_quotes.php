<?php
/*
Plugin Name: Stray Random Quotes
Plugin URI: http://www.italyisfalling.com/stray-random-quotes/
Description: Displays random quotes everywhere on your blog. Easy to custom and manage. Compatible with Wordpress 2.7.
Author: Corpodibacco
Author URI:http://www.italyisfalling.com/coding/
Version: 1.7.8
License: GPL compatible
*/

global $wpdb, $wp_version;

//few definitions
define("WP_QUOTES_TABLE", $wpdb->prefix . "quotes");
define("WP_STRAY_QUOTES_TABLE", $wpdb->prefix . "stray_quotes");
define ("DIR",basename(dirname(__FILE__)));
if (DIR == 'plugins') $dir = '';
define("WP_STRAY_QUOTES_PATH", get_option("siteurl") . "/wp-content/plugins/" . DIR);

// !!! remember to change this with every new version !!!
define ("WP_STRAY_VERSION", 178);

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
	
	?><script  type='text/javascript'>
	<!--
    
    function switchpage(select) {
        var index;
        for(index=0; index<select.options.length; index++) {
            if(select.options[index].selected){
                if(select.options[index].value!="")window.location.href=select.options[index].value;
                break;
            }
		}
     }
	-->	
	</script><?php /*wp_enqueue_script('jquery');*/
}

//upon activation
function quotes_activation() {

	global $wpdb;
	
	//set the messages
	$straymessage = "";
	$newmessage = str_replace("%1","http://www.italyisfalling.com/stray-random-quotes/#changelog",__('<p>Hey. Welcome to a new version of <strong>Stray Random Quotes</strong>. All changes are addressed in the <a href="%1">changelog</a>, but you should know that: </p>','stray-quotes'));

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
				$wpdb->query('ALTER TABLE ' . WP_QUOTES_TABLE . ' ADD COLUMN `category` VARCHAR( 255 ) NOT NULL  DEFAULT "default" AFTER `source`');			
				
				//and fill in default values
				$wpdb->query('UPDATE '. WP_QUOTES_TABLE . ' SET `category`="default"');				
				$wpdb->query('RENAME TABLE ' . WP_QUOTES_TABLE . ' TO ' . WP_STRAY_QUOTES_TABLE);
				
				//message
				$search = array("%s1", "%s2");
				$replace = array(WP_QUOTES_TABLE, WP_STRAY_QUOTES_TABLE);
				if (!$straymessage) $straymessage = $newmessage;
				$straymessage .= str_replace($search,$replace,__('<li>* I changed the old table "%s1" into a new one called "%s2" but don\'t worry, all your quotes are still there.</li>','stray-quotes')); 
				
				break;
			}
			
			//takes care of the new table
			if ( $value == WP_STRAY_QUOTES_TABLE ){			
				
				$categoryCol = $wpdb->get_col('SELECT `category` FROM '.WP_STRAY_QUOTES_TABLE);
				$groupCol = $wpdb->get_col('SELECT `group` FROM '.WP_STRAY_QUOTES_TABLE);
				if (!$categoryCol && !$groupCol) {
				
					//add new field
					$wpdb->query('ALTER TABLE ' . WP_STRAY_QUOTES_TABLE . ' ADD COLUMN `category` VARCHAR( 255 ) NOT NULL DEFAULT "default" AFTER `source`');
					
					//and fill in default category values
					$wpdb->query('UPDATE '. WP_STRAY_QUOTES_TABLE . ' SET `category`="default"');
					
					//message
					$search = array("%s1", "%s2");
					$replace = array(WP_STRAY_QUOTES_TABLE,  get_option('siteurl')."/wp-admin/admin.php?page=stray_manage");
					if (!$straymessage) $straymessage = $newmessage;
					$straymessage .= str_replace($search,$replace,__('<li>* This plugin now comes with "categories", which should make for a more intelligent way to organize, maintain and display quotes on your blog. I updated the table "%s1" but all your quotes <a href="%s2">are still there</a>.</li>','stray-quotes')); 
	
				}
						
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
		`category` varchar( 255 ) NOT NULL  DEFAULT 'default',
		`visible` ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'yes',
		PRIMARY KEY ( `quoteID` ) )
		");
		
		//insert sample quote
		$wpdb->query("INSERT INTO " . WP_STRAY_QUOTES_TABLE . " (
		`quote`, `author`, `source`) values ('Always tell the truth. Then you don\'t have to remember anything.', 'Mark Twain', 'Roughin it') ");
		
		//message
		$straymessage = __('Hey. This seems to be your first time with this plugin. I\'ve just created the database table "%s1" to store your quotes, and added one to start you off.','stray-quotes');
	}
	
	$quotesoptions = get_option('stray_quotes_options');
		
	//convert old options into new shiny array ones	
	if (false === $quotesoptions || !is_array($quotesoptions) || $quotesoptions=='' ) {
		
		$quotesoptions = array();
		
		//conversion of old pre-1.7 options
		$var = 'stray_quotes_before_all';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '<div align="right">';
		else $quotesoptions[$var] = $temp;
		delete_option($var);
		unset($var);unset($temp);		
		$var = 'stray_quotes_before_quote';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '&#8220;';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_after_quote';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '&#8221;';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_before_author';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '<br/>by&nbsp;';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_after_author';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_before_source';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '<em>&nbsp;';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_after_source';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '</em>';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_after_all';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '</div>';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_widget_title';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  'Random Quote';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_regular_title';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '<h2>Random Quote</h2>';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_put_quotes_first';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  'Y';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_default_visible';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  'Y';
		else $quotesoptions[$var] = $temp;
		delete_option($var);
		unset($var);unset($temp);
		
		//special trasformation for how link options work now
		$var = 'stray_quotes_use_google_links';
		$temp = get_option($var);
		$varb = 'stray_quotes_wiki_lan';		
		$tempb = get_option($varb);
		if ($temp == 'Y') {
			$quotesoptions['stray_quotes_linkto'] = '<a href="http://www.google.com/search?q=&quot;%AUTHOR%&quot;">';
			$quotesoptions['stray_quotes_sourcelinkto'] = '<a href="http://www.google.com/search?q=&quot;%SOURCE%&quot;">';
			$quotesoptions['stray_quotes_sourcespaces'] = ' ';	
			$quotesoptions['stray_quotes_authorspaces'] = ' ';		
		} 
		
		else if ($temp == 'W') {
			$quotesoptions['stray_quotes_linkto'] = '<a href="http://'.$tempb.'.wikipedia.org/wiki/%AUTHOR%';
			$quotesoptions['stray_quotes_linkto'] = '<a href="http://'.$tempb.'.wikipedia.org/wiki/%SOURCE%';
			$quotesoptions['stray_quotes_sourcespaces'] = '_';	
			$quotesoptions['stray_quotes_authorspaces'] = '_';		
		}
		
		else {
			$quotesoptions['stray_quotes_linkto'] =  '';
			$quotesoptions['stray_quotes_sourcelinkto'] =  '';
			$quotesoptions['stray_quotes_sourcespaces'] = '-';	
			$quotesoptions['stray_quotes_authorspaces'] = '-';		
		}
		delete_option($var);
		delete_option($varb);
		
		//more new entries
		$quotesoptions['stray_if_no_author'] =  '<br/>source:&nbsp;';	
		$quotesoptions['stray_quotes_uninstall'] = '';
		$quotesoptions['stray_clear_form'] =  'Y';	
		$quotesoptions['stray_quotes_order'] = 'quoteID';
		$quotesoptions['stray_quotes_rows'] = 10; 
		$quotesoptions['stray_quotes_categories'] = 'all';
		$quotesoptions['stray_quotes_sort'] = 'DESC';
		$quotesoptions['stray_default_category'] =  'default';		
		$quotesoptions['stray_quotes_version'] = WP_STRAY_VERSION; 
				
		//the message
		delete_option('stray_quotes_first_time');		
		if (!$straymessage) $straymessage = $newmessage;
		$straymessage .=__('<li>* I converted your old settings into new ones that will take less room in your database and be faster to load.</li>','stray-quotes');
				
	}
				
	//reset the removal option for everyone
	$quotesoptions['stray_quotes_uninstall'] = "";
	
	// < 1.7.3 NOTE: to this version
	if( $quotesoptions['stray_quotes_version'] <= 172 )$quotesoptions['stray_default_category'] =  'default';
	
	// < 1.7.6
	if( $quotesoptions['stray_quotes_version'] <= 175 ){
		
		//add a new fields
		$quotesoptions['stray_if_no_author'] =  '';
		$quotesoptions['stray_clear_form'] =  'Y';	
		
		//remove all spaces from categories. NOTE: to this version, categories were still called group and they must be called so here
		$removal = $wpdb->query("UPDATE `".WP_STRAY_QUOTES_TABLE."` SET `group`= REPLACE(`group`, ' ', '-') WHERE `group` LIKE '% %'");
		if ($removal) {
			if (!$straymessage)$straymessage = $newmessage;
			$straymessage .=__('<li>* Spaces are not allowed within groups names anymore, because they created all sorts of problems. It so happens that you had spaces in your Category names, so I replaced them with dashes. I hope it\'s okay.</li>','stray-quotes');
		}
		
	}
		
	// < 1.7.8
	if ( $quotesoptions['stray_quotes_version'] <= 177 ){
	
		//change column name to 'category'
		$wpdb->query("ALTER TABLE `".WP_STRAY_QUOTES_TABLE."` CHANGE `group` `category` varchar( 255 ) NOT NULL  DEFAULT 'default'");
		if( $quotesoptions['stray_quotes_order'] == "group" )$quotesoptions['stray_quotes_order'] = "category";
		if (!$straymessage)$straymessage = $newmessage;
		$straymessage .=str_replace("%1",  get_option('siteurl')."/wp-admin/admin.php?page=stray_help", __('<li>* For not entirely clear reasons, groups are now named "Categories". The only case in which this will affect you is with the use of the shortcode <code>[all-quotes group="group"...]</code>, which must now be written as <code>[all-quotes category="category"...]</code>. More on the <a href="%1">help page</a>.</li>','stray-quotes'));
		
	}	

	//take care of version number
	if( $quotesoptions['stray_quotes_version'] != (WP_STRAY_VERSION) )$quotesoptions['stray_quotes_version'] = WP_STRAY_VERSION; 
	
	//insert the feedback message
	$quotesoptions['stray_quotes_first_time'] = $straymessage;

	//and finally we actually put the option thing in the database
	update_option('stray_quotes_options', $quotesoptions);		
	
}

//upon deactivation
function quotes_deactivation() {

	global $wpdb;

	$quotesoptions = get_option('stray_quotes_options');
	$sql = "DROP TABLE IF EXISTS ".WP_STRAY_QUOTES_TABLE;

	//delete the options
	if($quotesoptions['stray_quotes_uninstall'] == 'options') {
		delete_option('stray_quotes_options');
		delete_option('stray_quotes_widgets');
	}
	else if ($quotesoptions['stray_quotes_uninstall'] == 'table')$wpdb->query($sql);
	else if ($quotesoptions['stray_quotes_uninstall'] == 'both'){
		 delete_option('stray_quotes_options');
		 delete_option('stray_quotes_widgets');
		$wpdb->query($sql);
	}

}
	
//for compatibility
if ($wp_version <= 2.3 ) add_filter('the_content', 'wp_quotes_page', 10);

//includes
include('inc/stray_functions.php');
include('inc/stray_overview.php');
include('inc/stray_settings.php');
include('inc/stray_manage.php');
include('inc/stray_new.php');
include('inc/stray_widgets.php');
include('inc/stray_help.php');
include('inc/stray_remove.php');

//build submenu entries
function stray_quotes_add_pages() {

	add_menu_page('Stray Random Quotes', 'Quotes', 8, __FILE__, 'stray_intro', WP_STRAY_QUOTES_PATH.'/img/lightbulb.png');
	add_submenu_page(__FILE__, __('Overview for the Quotes','stray-quotes'), __('Overview','stray-quotes'), 8, __FILE__, 'stray_intro');
	add_submenu_page(__FILE__, __('Manage Quotes','stray-quotes'), __('Manage','stray-quotes'), 8, 'stray_manage', 'stray_manage');
	add_submenu_page(__FILE__, __('Add New Quote','stray-quotes'), __('Add New','stray-quotes'), 8, 'stray_new', 'stray_new');
	add_submenu_page(__FILE__, __('Settings of the Quotes','stray-quotes'), __('Settings','stray-quotes'), 8, 'stray_quotes_options', 'stray_quotes_options'); 
	add_submenu_page(__FILE__, __('Help with the Quotes','stray-quotes'), __('Help','stray-quotes'), 8, 'stray_help', 'stray_help');
	add_submenu_page(__FILE__, __('Remove Stray Random Quotes','stray-quotes'), __('Remove','stray-quotes'), 8, 'stray_remove', 'stray_remove'); 	
	
}

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