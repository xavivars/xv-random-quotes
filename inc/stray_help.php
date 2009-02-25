<?php

function stray_help() {
	
	$widgetpage = get_option('siteurl')."/wp-admin/widgets.php";
	$toolspage = get_option('siteurl')."/wp-admin/admin.php?page=stray_tools";
	
	// blah blah ?>
<div class="wrap" style="width:52%"><h2><?php _e('The help page','stray-quotes');?></h2>
   
    <?php // how do I display a random quote on this blog? ?>
    <h3><?php _e('So, how do I display a random quote on this blog?','stray-quotes');?></h3>
    <blockquote><p><?php echo str_replace("%s1",$widgetpage,__('Stray Random Quotes comes with <strong>Widgets</strong>. Depending on your preferences, a random quote in a widget could be coming from one or two categories of quotes, or from all the categories. You can use all the widgets you want. Each widget has its own set of options. Just enable one widget at a time on the <a href="%s1">widget page</a>, and change its settings accordingly.<br />Note: <em>Your template must be widget compatible.</em></p>','stray-quotes')); ?></p></blockquote>
 
    <?php // What if I don\'t use widgets?' ?>
    <h3><?php _e('What if I don\'t use widgets?','stray-quotes');?></h3>
    <blockquote><p><?php _e('If your template <strong>does not</strong> use widgets, or you want to display the quotes on your template <strong>elsewhere</strong> other than the sidebar, you can add this piece of code to your template (in the header, the footer etc):','stray-quotes');?></p>
    <p><?php echo str_replace("%s1",$options,__('<code>&lt;?php if (function_exists(\'stray_random_quote\')) stray_random_quote(\'category1,category2\', false, \'another quote &amp;raquo;\', false); ?&gt;</code><br/><br/>Note that <code>\'category1,category2\'</code> is where you add a comma separated list of the categories from which to extract the random quote. This setting is optional. If you don\'t indicate anything, a random quote from all the categories will be displayed. <code>true</code> or <code>false</code>, without brackets, indicates whether to load the quotes in order (true) or randomly (false). If you don\'t indicate anything the quotes will be loaded randomly. <code>\'another quote &amp;raquo;\'</code> allows to optionally indicate a special link phrase for the AJAX loader, or none at all. For this to work, the default link phrase in the <a href="%s1">settings page</a> must be left empty (and AJAX must be enabled). The last <code>false</code> indicates whether to optionally disable ajax entirely for this tag (if true). A default title, such as \'Random quote\', is set through the <a href="%s1">settings page</a>. If you want specific titles for different categories, you will have to put that directly in the HTML of the template. For example:<br/><br/><code>&lt;h2&gt;Random Mark Twain quote:&lt;/h2&gt;&lt;?php if (function_exists(\'stray_random_quote\')) stray_random_quote(\'mark twain\'); ?&gt;</code>, you got the idea.' , 'stray-quotes')); ?>
    </p></blockquote>
    
    <?php // What about posts and pages? ?>
    <h3><?php _e('What about posts and pages?','stray-quotes');?></h3>
    <blockquote><p><?php _e('To <strong>insert a random quote</strong> in a post or page, write in the post editor <code><strong>[random-quote]</strong></code>.<br/><br/>This shortcode accepts many variables: <code>[random-quote categories="category1,category2" sequence="false" linkphrase="another quote &amp;raquo;" noajax="false"]</code>. <code>categories</code> is where you add the categories separated by commas from which to extract the random quote. <code>sequence</code> loads the quotes in order if "true", randomly if "false". <code>linkphrase</code> displays a phrase to dynamically load another quote (overriding the one given in the settings). <code>noajax</code> if "true" optionally disables the ajax for this shortcode. All these settings are optional. If you don\'t indicate anything, a random quote from all the categories will be displayed, with ajax according to the general settings.<br/><br/>', 'stray-quotes');?></p>
    <p><?php _e('To <strong>insert a list of many or all the quotes</strong> in a post or page, just write in the editor <code><strong>[all-quotes]</strong></code><br/><br/>This shortcode accepts many variables: <code>[all-quotes rows=10 orderby="quoteID" sort="ASC" categories="all"]</code>. <code>rows</code> is how many quotes you want per page; <code>orderby</code> if you want to order the quotes by "quoteID", "author" "source" or "category"; <code>sort</code> is whether the quotes will be sorted ascending "ASC" or descending "DESC". Finally <code>categories</code> is from which category you want the quotes to be. Use "all" for all the categories, or use the names of the categories separated by comma. All these settings are optional. Without them, the values you see in this example are used as defaults.', 'stray-quotes');?></p></blockquote>
    
    <?php // What about other areas of the blog, such as post titles, or even the blog title? ?>
    <h3><?php _e('What about other areas of the blog, such as post titles, or even the blog title?','stray-quotes');?></h3>
    <blockquote><p><?php echo str_replace("%s1",$toolspage,__('Well, actually, on the <a href="%s1">tools page</a> you can enable shortcodes for a number of extra areas where shortcodes aren\'t normally allowed. This will entitle you to some pretty extraordinary things, such as random taglines or random category names. Note that this will enable all shortcodes and not only those of Stray Random Quotes. More examples can be found <a href="http://www.italyisfalling.com/cool-things-you-can-do-with-stray-random-quotes">here</a>.', 'stray-quotes'));?></p></blockquote>
    
    <?php // What else can I do? ?>
    <h3><?php _e('What else can I do?','stray-quotes');?></h3>
    <blockquote><p><?php _e('Well you could <strong>insert a given quote in a post or a page</strong>, in which case you should just write in the editor <code>[quote id=x]</code>, where <code>x</code> is the id number of the quote as it appears on the management page. This will outupt the quote and also a "next quote" link that will let the user browse between quotes of the same category as the first one indicated. I will enable more variables here in the future.', 'stray-quotes');?></p>
    <p><?php _e('To insert <strong>a given quote in your template</strong> use the following: <code>&lt;?php if (function_exists(\'stray_a_quote\')) stray_a_quote(x);?&gt;</code>, where <code>x</code> is the id number of the quote as it appears on the management page. This will outupt the quote and also a "next quote" link that will let the user browse between quotes of the same category as the first one indicated.', 'stray-quotes');?></p></blockquote>
        
    <?php // The IDs of the quotes are getting ridiculously high ?>
    <h3><?php _e('The IDs of the quotes are getting ridiculously high. Can I do something about it?','stray-quotes');?></h3>
    <blockquote><?php echo str_replace("%s1",$toolspage,__('It is the way MySQL works, gaps are not important in an index. Anyway, there is a workaround. On the <a href="%s1">tools page</a> there\'s a button to <strong>reset all quoteID numbers in your table</strong>, after which the numbering will look fine again. There is a downside, though: few, or even none, of the old ID numbers will still correspond to the same quotes, which means that if you called a quote in a post <strong>by its ID</strong> (as explained in the previous paragraph on this very page), there\'s a chance a different quote will be called afterwards. It is up to you to accept this and go and fix it later.', 'stray-quotes')); ?></blockquote>
    
    <?php // The bookmarklet ?>
    <h3><?php _e('I want a bookmarklet to quote things on the fly when I browse!','stray-quotes');?></h3>
    <blockquote><?php echo str_replace("%s1",$toolspage,__('You have it. It is on the <a href="%s1">tools page</a>. Place the link on your browser toolbar, select text in a page and click on the link. The "add new quote" page will open with the selected text in it.', 'stray-quotes')); ?></blockquote>

	<?php // Hey. There is a bug ?>
    <h3><?php _e('Hey. There is a bug!','stray-quotes');?></h3>
    <blockquote><?php echo str_replace("%s1","http://www.italyisfalling.com/stray-random-quotes/",__('I knew it! See, I am not a programmer or anything. There\'s always a bug. If you want to help me catch it, and for further help, please come and trash the comments <a href="%s1">on this post</a>. Thanks.<br /><br />', 'stray-quotes')); ?></blockquote>

</div><?php
}
?>
