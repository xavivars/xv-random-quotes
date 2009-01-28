<?php

//intro page
function stray_intro() {


	global $wpdb;
	$quotesoptions = get_option('stray_quotes_options');
	
	$widgetpage = get_option('siteurl')."/wp-admin/widgets.php";
	$management = get_option('siteurl')."/wp-admin/admin.php?page=stray_manage";
	$options =  get_option('siteurl')."/wp-admin/admin.php?page=stray_quotes_options";
	$new = get_option('siteurl')."/wp-admin/admin.php?page=stray_new";

	//if it is really the first time
	$first_time = $quotesoptions['stray_quotes_first_time'];
	if ($first_time == 1) {
	
		$search = "%s1";
		$replace = WP_STRAY_QUOTES_TABLE;
		
		
		?><div id="message" class="updated fade"><p><?php echo str_replace($search,$replace,__(
		'Hey. Welcome to <strong>Stray Random Quotes.</strong><br />This seems to be your first time with this plugin.I\'ve just created the database table "%s1" to store your quotes, and added one to start you off.', 'stray-quotes')); ?>
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
	$search = array ("%s1", "%s2","%s3");
	$replace = array($new,$management,$options);		
	
	?><div class="wrap"><h2>Stray Random Quotes</h2>
	<strong><?php echo __('is a Wordpress plugin by' , 'stray-quotes'); ?> <a href="http://www.italyisfalling.com/coding">italyisfalling.com</a></strong><p><?php echo str_replace($search,$replace,__('With it you can: <a href="%s1"><strong>add new quotes</strong></a> to be randomly displayed in your blog;<br />use the <a href="%s2"><strong>manage</strong></a> page to edit or delete existing quotes;<br />change the <a href="%s3"><strong>settings</strong></a> to control how the quotes are displayed on your blog.','stray-quotes')); ?>
	</p></div>
    
	<div class="wrap"><h2><?php echo __('So, how do I display a random quote on this blog?','stray-quotes');?></h2><p>
	<?php echo str_replace("%s1",$widgetpage,__('Stray Random Quotes comes with <strong>Widgets</strong>. Depending on your preferences, a random quote in a widget could be coming from one or two groups of quotes, or from all the groups. You can use all the widgets you want. Just enable one widget at a time on the <a href="%s1">widget page</a>, and change its settings accordingly.<br />Note: <em>Your template must be widget compatible.</em></p>','stray-quotes')); ?>

    <p><ul><?php echo __('If your template <strong>does not</strong> use widgets, or you want to display the quotes <strong>elsewhere</strong> other than the sidebar,<br />you can add this piece of code to your template (in the header, the footer etc):','stray-quotes');?><br/><br/>
    	
    	<li><?php echo str_replace("%s1",$options,__('<code>&lt;?php if (function_exists(\'stray_random_quote\')) stray_random_quote(\'group1,group2,etc\'); ?&gt;</code><br/><br/>Note that <code>\'group1,group2,etc\'</code> is where you add a comma separated list of the groups from which to extract the random quote. This setting is optional. If you don\'t indicate anything, a random quote from all the groups will be displayed. A default title, such as \'Random quote\', is set through the <a href="%s1">settings page</a>. If you want specific titles for different groups, you will have to put that directly in the HTML of the template. For example:<br/><code>&lt;h2&gt;Random Mark Twain quote:&lt;/h2&gt;&lt;?php if (function_exists(\'stray_random_quote\')) stray_random_quote(\'mark twain\'); ?&gt;</code>, you got the idea.' , 'stray-quotes')); ?></li>
    </ul>
</p> 
    
    </p></div>
<div class="wrap"><h2>What else can I do?</h2>
   
    <p><ul>
     	<li><?php echo __('To insert a <strong>random quote in a post or page</strong>, just write in the editor <code>[random-quote \'group1\' \'group2\']</code>.', 'stray-quotes');?></li>
        <li><?php echo __('To insert a list of <strong>many or all the quotes in a post or page</strong>, just write in the editor <code>[all-quotes rows=10, orderby="quoteID", sort="ASC", group="all"]</code>. The options here I think are pretty straightforward, but to clarify: <em>rows</em> is how many quotes you want per page; <em>orderby</em> if you want to order the quotes by "quoteID", "author" "source" or "group"; <em>sort</em> is whether the quotes will be sorted ascending "ASC" or descending "DESC". Finally <em>groups</em> is from which group you want the quotes to be. Use "all" for all the groups, or use the names of the groups separated by comma. All these settings are optional. Without them, the values you see in this example are used as defaults.', 'stray-quotes');?></li> 
		<li><?php echo __('To insert <strong>a given quote in a post or a page</strong>, just write in the editor <code>[quote id=1]</code>, where <code>id</code> is the id number of the quote as it appears on the management page.', 'stray-quotes');?></li>
        <li><?php echo __('To insert <strong>a given quote in your template</strong> use the following: <code>&lt;?php if (function_exists(\'stray_a_quote\')) stray_a_quote(id);?&gt;</code>.', 'stray-quotes');?></li>
    </ul></p>     
    <p><?php echo str_replace("%s1","http://www.italyisfalling.com/stray-random-quotes/",__('For further help, make use of the comments <a href="%s1">on this post</a>.<br />Happy quoting.', 'stray-quotes')); ?>
    </p></div><?php
	
}
?>