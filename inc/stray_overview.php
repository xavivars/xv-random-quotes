<?php

//intro page
function stray_intro() {

	global $wpdb;
	$quotesoptions = get_option('stray_quotes_options');
	
	$widgetpage = get_option('siteurl')."/wp-admin/widgets.php";
	$management = get_option('siteurl')."/wp-admin/admin.php?page=stray_manage";
	$options =  get_option('siteurl')."/wp-admin/admin.php?page=stray_quotes_options";
	$new = get_option('siteurl')."/wp-admin/admin.php?page=stray_new";
	$help =  get_option('siteurl')."/wp-admin/admin.php?page=stray_help";
	$straymessage = $quotesoptions['stray_quotes_first_time'];
	$totalquotes = $wpdb->get_var("SELECT COUNT(`quoteID`) as rows FROM " . WP_STRAY_QUOTES_TABLE);


	//feedback following activation (see main file)
	if ($straymessage !="") {
		
		?><div id="message" class="updated fade"><ul><?php echo $straymessage; ?></ul></div><?php
		
		//empty message after feedback
		$quotesoptions['stray_quotes_first_time'] = "";
		update_option('stray_quotes_options', $quotesoptions);
	}	
	
	?><div class="wrap"><h2>Stray Random Quotes: <?php _e('Overview','stray-quotes'); ?></h2><?php 
	
    if ($totalquotes > 3) { 
	
		//quotes and categories
		$howmanycategories = count(make_categories());
		if ($howmanycategories == 1)$howmanycategories = __('one category','stray-quotes');
		else { 
			if ($howmanycategories)
				$howmanycategories = $howmanycategories . ' ' . __('categories','stray-quotes');
				$categorymost = mostused("category");	
		}		
		$sql = "SELECT COUNT( `category` ) AS `Rows` , `category` FROM `" . WP_STRAY_QUOTES_TABLE . "` GROUP BY `category` ORDER BY `Rows` DESC";
		$howmany = $wpdb->get_results($sql);
		if ( count($howmany) > 1) $as = __(', distributed as follows:','stray-quotes');
		else $as = '.';
        $search = array('%s1','%s2', '%s3');
        $replace = array($totalquotes, $howmanycategories, $as);
        echo str_replace ($search,$replace, __('<p>Right now you have <strong>%s1 quotes</strong> in <strong>%s2</strong>%s3</p>','stray-quotes'));
		if ($howmany && count($howmany) > 1) { ?>
		
			<table class="widefat" style="width:200px"><?php
				
			$i = 0;
			
			foreach ( $howmany as $many ) {
			
				$alt = ($i % 2 == 0) ? ' class="alternate"' : '';
				
				?><tr <?php echo($alt); ?>>
				<th scope="row"><?php echo $many->Rows; ?></th>
				<td><?php echo $many->category; ?></td>
				</tr><?php 
			} ?>
			</table><?php	
		}		
		
		//visible quotes
		$visiblequotes = $wpdb->get_var("SELECT COUNT(`quoteID`) as rows FROM " . WP_STRAY_QUOTES_TABLE . " WHERE visible='yes'"); 
		if($visiblequotes == $totalquotes)$visiblequotes = __('All your quotes ','stray-quotes');
		echo str_replace ('%s3',$visiblequotes, __('<p><strong>%s3</strong> are visible.</p>','stray-quotes'));
		
		//author
		$authormost = mostused("author");
		if ($authormost) echo str_replace ('%s5',$authormost, __('<p>Your most quoted author is <strong>%s5</strong>.</p>','stray-quotes'));
		
		//source
		$sourcemost = mostused("source");
		if ($sourcemost) str_replace ('%s5',$sourcemost, __('<p>Your most used source is <strong>%s5</strong>.</p>','stray-quotes'));
		
    } else _e('There is nothing to report.','stray-quotes');
    ?><p><?php
	
	//link pages
    $search = array ("%s1", "%s2","%s3","%s4");
	$replace = array($new,$management,$options,$help);	
	echo str_replace($search,$replace,__('To start doing stuff, you can <a href="%s1"><strong>add new quotes</strong></a>;<br />use the <a href="%s2"><strong>manage</strong></a> page to edit or delete existing quotes;<br />change the <a href="%s3"><strong>settings</strong></a> to control how the quotes are displayed on your blog.<br/>If you\'re new to all this, there\'s a <a href="%s4"><strong>help page</strong></a> as well.','stray-quotes')); ?>
	</p></div>
    <p>This is all in a day's work for <a href="http://www.italyisfalling.com/coding">italyisfalling.com</a>, <?php echo date('Y'); ?>.<br/><?php _e('Happy quoting.','stray-quotes'); ?></p><?php
	
}
?>