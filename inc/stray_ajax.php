<?php

// If your 'wp-content' directory is not in the default location you have to enter the path to your blog here.
// It has nothing to do with your URL, we're talking about your server here. Example: '/home/www/public_html/wp'
$changedDir = ''; 

if($_POST['action'] == 'newquote'){

	if (!$changedDir)$changedDir = preg_replace('|wp-content.*$|','', __FILE__);
	include_once($changedDir.'/wp-config.php');
	
	$categories = isset($_POST['categories'])?$_POST['categories']:'';
	$sequence = isset($_POST['sequence'])?$_POST['sequence']:'';
	$linkphrase = isset($_POST['linkphrase'])?$_POST['linkphrase']:'';
	$widgetid = isset($_POST['widgetid'])?$_POST['widgetid']:'';
	
	echo stray_random_quote($categories,$sequence,$linkphrase,$widgetid );
}

?>