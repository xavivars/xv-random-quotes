<?php

function stray_help() {
	
	//function reset id numbers
	if(!empty($_POST['do'])) {
		if(isset($_POST['submit'])){
		
			global $wpdb;
			$query1 = $wpdb->query("ALTER TABLE `".WP_STRAY_QUOTES_TABLE."` DROP `quoteID`");
			$query2 = $wpdb->query("ALTER TABLE `".WP_STRAY_QUOTES_TABLE."` ADD COLUMN `quoteID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
				
			if ($query1 && $query2) { ?>
                <div id="message" class="updated fade below-h2"><p>
                <?php echo str_replace("%s",get_option("siteurl").'/wp-admin/admin.php?page=stray_manage' ,__('<strong>Quote IDs have been reset.</strong> To review use the <a href="%s">Manage page</a>.','stray-quotes'));  ?></p></div><?php 
			} else { ?>
                <div id="message" class="error fade below-h2"><p>
                <?php echo __('<strong>Failure.</strong> It was not possible to reset the ID numbers.','stray-quotes');
                ?></p></div><?php 
			}			
		} 
	}
	
	// blah blah ?>
<div class="wrap" style="width:52%"><h2><?php echo __('The help page','stray-quotes');?></h2>
   
    <?php // how do I display a random quote on this blog? 
	$widgetpage = get_option('siteurl')."/wp-admin/widgets.php"; ?>
    <h3><?php echo __('So, how do I display a random quote on this blog?','stray-quotes');?></h3>
    <p><?php echo str_replace("%s1",$widgetpage,__('Stray Random Quotes comes with <strong>Widgets</strong>. Depending on your preferences, a random quote in a widget could be coming from one or two categories of quotes, or from all the categories. You can use all the widgets you want. Just enable one widget at a time on the <a href="%s1">widget page</a>, and change its settings accordingly.<br />Note: <em>Your template must be widget compatible.</em></p>','stray-quotes')); ?></p>
 
    <?php // What if I don\'t use widgets?' ?>
    <h3><?php echo __('What if I don\'t use widgets?','stray-quotes');?></h3>
    <p><ul><?php echo __('If your template <strong>does not</strong> use widgets, or you want to display the quotes on your template <strong>elsewhere</strong> other than the sidebar, you can add this piece of code to your template (in the header, the footer etc):','stray-quotes');?><br/><br/>
    <li><?php echo str_replace("%s1",$options,__('<code>&lt;?php if (function_exists(\'stray_random_quote\')) stray_random_quote(\'category1,category2,etc\'); ?&gt;</code><br/><br/>Note that <code>\'category1,category2,etc\'</code> is where you add a comma separated list of the categories from which to extract the random quote. This setting is optional. If you don\'t indicate anything, a random quote from all the categories will be displayed. A default title, such as \'Random quote\', is set through the <a href="%s1">settings page</a>. If you want specific titles for different categories, you will have to put that directly in the HTML of the template. For example:<br/><br/><code>&lt;h2&gt;Random Mark Twain quote:&lt;/h2&gt;&lt;?php if (function_exists(\'stray_random_quote\')) stray_random_quote(\'mark twain\'); ?&gt;</code>, you got the idea.' , 'stray-quotes')); ?></li>
    </ul></p> 
                
    <?php // What "categories" are for? ?>
    <h3><?php echo __('What "categories" are for?','stray-quotes');?></h3>
    <p><?php echo __('Categories are groups into which your quotes can be divided. They can have all sorts of use, from easily managing a large number of quotes, to displaying quotes of different categories in different areas of the blog.<br/>Note: <em>Categories were called originally "groups", then I decided "group" was lame. I do things like that.</em></p>','stray-quotes'); ?></p>
    
    <?php // What about posts and pages? ?>
    <h3><?php echo __('What about posts and pages?','stray-quotes');?></h3>
    <p><ul>
    <li><?php echo __('To insert a <strong>random quote in a post or page</strong>, just write in the editor <code>[random-quote \'category1\' \'category2\']</code>. Note that <code>\'category1\' \'category2\'</code> is where you add the categories from which to extract the random quote. This setting is optional. If you don\'t indicate anything, a random quote from all the categories will be displayed.', 'stray-quotes');?></li>
    <li><?php echo __('To insert a list of <strong>many or all the quotes in a post or page</strong>, just write in the editor <code>[all-quotes rows=10, orderby="quoteID", sort="ASC", category="all"]</code>. The variables here I think are pretty straightforward, but to clarify: <em>rows</em> is how many quotes you want per page; <em>orderby</em> if you want to order the quotes by "quoteID", "author" "source" or "category"; <em>sort</em> is whether the quotes will be sorted ascending "ASC" or descending "DESC". Finally <em>categories</em> is from which category you want the quotes to be. Use "all" for all the categories, or use the names of the categories separated by comma. All these settings are optional. Without them, the values you see in this example are used as defaults.', 'stray-quotes');?></li> 
    </ul></p>
    
    <?php // What else can I do? ?>
    <h3><?php echo __('What else can I do?','stray-quotes');?></h3>
    <p><ul>
    <li><?php echo __('Well you could insert <strong>a given quote in a post or a page</strong>, in which case you should just write in the editor <code>[quote id=x]</code>, where <code>x</code> is the id number of the quote as it appears on the management page.', 'stray-quotes');?></li>
    <li><?php echo __('To insert <strong>a given quote in your template</strong> use the following: <code>&lt;?php if (function_exists(\'stray_a_quote\')) stray_a_quote(x);?&gt;</code>, where <code>x</code> is the id number of the quote as it appears on the management page.', 'stray-quotes');?></li>
    </ul></p>
    
    <?php // The IDs of the quotes are getting ridiculously high ?>
    <form action="<?php echo ($_SERVER['REQUEST_URI']); ?>" method="post">
    <h3><?php echo __('The IDs of the quotes are getting ridiculously high. Can I do something about it?','stray-quotes');?></h3>
    <p><?php echo __('It is the way MySQL works, gaps are not important in an index. Anyway, there is a workaround. By clicking the button below you will <strong>reset all quoteID numbers in your table</strong>, and the numbering will look fine again. There is a downside, though: few, or even none, of the old ID numbers will still correspond to the same quotes, which means that if you called a quote in a post <strong>by its ID</strong> (as explained in the previous paragraph on this very page), there\'s a chance a different quote will be called afterwards. It is up to you to accept this and go and fix it later.', 'stray-quotes'); ?><br/><br/><span class="submit">&nbsp;<input type="hidden" name="do" value="Update" />
    <input type="submit" name="submit" value="<?php echo __('Reset', 'stray-quotes'); ?>"> <?php echo __('the numbering of the quotes.', 'stray-quotes'); ?>
    </span></p></form>    

	<?php // Hey. There is a bug ?>
    <h3><?php echo __('Hey. There is a bug!','stray-quotes');?></h3>
    <p><?php echo str_replace("%s1","http://www.italyisfalling.com/stray-random-quotes/",__('I knew it! See, I am not a programmer or anything. There\'s always a bug. If you want to help me catch it, and for further help, please come and trash the comments <a href="%s1">on this post</a>. Thanks.<br /><br />', 'stray-quotes')); ?></p>

</div><?php
}
?>
