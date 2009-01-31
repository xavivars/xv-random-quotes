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
	$first_time = $quotesoptions['stray_quotes_first_time'];

	//if it is really the first time
	if ($first_time == 1) {
	
		$search = "%s1";
		$replace = WP_STRAY_QUOTES_TABLE;
		
		
		?><div id="message" class="updated fade"><p><?php echo str_replace($search,$replace,__(
		'Hey. Welcome to <strong>Stray Random Quotes.</strong><br />This seems to be your first time with this plugin. I\'ve just created the database table "%s1" to store your quotes, and added one to start you off.', 'stray-quotes')); ?>
        </div><?php
		
		$quotesoptions['stray_quotes_first_time'] = 4;
		update_option('stray_quotes_options', $quotesoptions);
	}
	
	//if you udpated the old table
	else if ($first_time == 2) {
	
		$search = array("%s1", "%s2");
		$replace = array(WP_QUOTES_TABLE, WP_STRAY_QUOTES_TABLE);
	
		?><div id="message" class="updated fade"><p><?php echo str_replace($search,$replace,__(
		'Hey. Welcome to <strong>Stray Random Quotes.</strong><br />I updated the old table "%s1" to "%s2" but don\'t worry, all your quotes are still there.','stray-quotes')); ?></div><?php
		
		$quotesoptions['stray_quotes_first_time'] = 4;
		update_option('stray_quotes_options', $quotesoptions);
        	
	}
	
	//if you updated the new table
	else if ($first_time == 3) {
	
		$search = array("%s1", "%s2");
		$replace = array(WP_STRAY_QUOTES_TABLE, $management);
	
		?><div id="message" class="updated fade"><p><?php echo str_replace($search,$replace,__(
		'Hey. Welcome to this new version of <strong>Stray Random Quotes</strong>.<br />This plugin now comes with "groups", which should make for a more intelligent way to organize, maintain and display quotes on your blog.<br />I updated the table "%s1" but all your quotes <a href="%s2">are still there</a>.','stray-quotes')); ?></div><?php
		
		$quotesoptions['stray_quotes_first_time'] = 4;
		update_option('stray_quotes_options', $quotesoptions);
        	
	}
	
	//the blah blah	
	$sql1 = "SELECT quoteID FROM " . WP_STRAY_QUOTES_TABLE; 
	$sql2 = "SELECT quoteID FROM " . WP_STRAY_QUOTES_TABLE. " WHERE visible='yes'"; 
	$howmanyquotes = count($wpdb->get_results($sql1));
    $howmanygroups = count(make_groups());
	if ($howmanygroups == 1)$howmanygroups = 'one group';
	else { 
		if ($howmanygroups)
			$howmanygroups = $howmanygroups . ' groups';
			$groupmost = mostused("group");	
	}
	
    $authormost = mostused("author");
	
	$visiblequotes = count($wpdb->get_results($sql2));
	if($visiblequotes == $howmanyquotes)$visiblequotes = 'all ';
	 
	?><div class="wrap"><h2>Stray Random Quotes, a wordpress plugin</h2>
    <!--<span class="setting-description"><?php /*echo __('"A witty saying proves nothing." ~ Voltaire','stray-quotes')*/ ?></span>
    <br/><br/>-->
    <p><?php if ($howmanyquotes) { 
        $search = array('%s1','%s2','%s3');
        $replace = array($howmanyquotes,$howmanygroups,$visiblequotes);
        echo str_replace ($search,$replace, __('Right now you have <strong>%s1 quotes</strong> in <strong>%s2</strong>.<br/><strong>%s3</strong> are visible.<br/> ','stray-quotes'));
		if ($groupmost) echo str_replace ('%s4',$groupmost, __('<br/>The group you are using the most is "<strong>%s4</strong>".<br/>','stray-quotes'));
		if ($authormost) {
			$sql = "SELECT quote FROM " . WP_STRAY_QUOTES_TABLE. " WHERE author='".$authormost."' ORDER BY RAND() LIMIT 1"; 
			$authorquote = $wpdb->get_var($sql);
			$search = array('%s5','%s6');
			$replace = array($authormost,$authorquote);
			echo str_replace ($search,$replace, __('<br/>Your most quoted author is <strong>%s5</strong>, who says: <br /><div style="width:35%">"<em>%s6</em>"</div>','stray-quotes'));
		}
    } else {echo__('You don\'t have any quote yet.','stray-quotes');}
    ?></p>

    <p><?php $search = array ("%s1", "%s2","%s3","%s4");
	$replace = array($new,$management,$options,$help);	
	echo str_replace($search,$replace,__('To start doing stuff, you can <a href="%s1"><strong>add new quotes</strong></a>;<br />use the <a href="%s2"><strong>manage</strong></a> page to edit or delete existing quotes;<br />change the <a href="%s3"><strong>settings</strong></a> to control how the quotes are displayed on your blog.<br/>If you\'re new to all this, there\'s a <a href="%s4"><strong>help page</strong></a> as well.','stray-quotes')); ?>
	</p></div>
    <p>This is all in a day's work for <a href="http://www.italyisfalling.com/coding">italyisfalling.com</a>, <?php echo date('Y'); ?>.<br/><?php echo __('Happy quoting.','stray-quotes'); ?></p>
	<?php
	
}
?>