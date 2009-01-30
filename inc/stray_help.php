<?php

function stray_help() {

	$widgetpage = get_option('siteurl')."/wp-admin/widgets.php";
	
	?>
<div class="wrap" style="width:52%"><h2><?php echo __('The help page','stray-quotes');?></h2>
    <span class="setting-description"><?php echo __('"Quote me as saying I was misquoted." ~ Groucho Marx','stray-quotes') ?></span><br/><br/>
    <?php // help part 2 ?>
    <h3><?php echo __('So, how do I display a random quote on this blog?','stray-quotes');?></h3>
    <p><?php echo str_replace("%s1",$widgetpage,__('Stray Random Quotes comes with <strong>Widgets</strong>. Depending on your preferences, a random quote in a widget could be coming from one or two groups of quotes, or from all the groups. You can use all the widgets you want. Just enable one widget at a time on the <a href="%s1">widget page</a>, and change its settings accordingly.<br />Note: <em>Your template must be widget compatible.</em></p>','stray-quotes')); ?>
    
    <h3><?php echo __('What if I don\'t use widgets?','stray-quotes');?></h3>
    <p><ul><?php echo __('If your template <strong>does not</strong> use widgets, or you want to display the quotes on your template <strong>elsewhere</strong> other than the sidebar, you can add this piece of code to your template (in the header, the footer etc):','stray-quotes');?><br/><br/>
    <li><?php echo str_replace("%s1",$options,__('<code>&lt;?php if (function_exists(\'stray_random_quote\')) stray_random_quote(\'group1,group2,etc\'); ?&gt;</code><br/><br/>Note that <code>\'group1,group2,etc\'</code> is where you add a comma separated list of the groups from which to extract the random quote. This setting is optional. If you don\'t indicate anything, a random quote from all the groups will be displayed. A default title, such as \'Random quote\', is set through the <a href="%s1">settings page</a>. If you want specific titles for different groups, you will have to put that directly in the HTML of the template. For example:<br/><br/><code>&lt;h2&gt;Random Mark Twain quote:&lt;/h2&gt;&lt;?php if (function_exists(\'stray_random_quote\')) stray_random_quote(\'mark twain\'); ?&gt;</code>, you got the idea.' , 'stray-quotes')); ?></li>
    </ul></p> 
    </p>
                
    <?php // help part 2 ?>
    <h3><?php echo __('What about posts and pages?','stray-quotes');?></h3>
    <p><ul>
    <li><?php echo __('To insert a <strong>random quote in a post or page</strong>, just write in the editor <code>[random-quote \'group1\' \'group2\']</code>. Note that <code>\'group1\' \'group2\'</code> is where you add the groups from which to extract the random quote. This setting is optional. If you don\'t indicate anything, a random quote from all the groups will be displayed.', 'stray-quotes');?></li>
    <li><?php echo __('To insert a list of <strong>many or all the quotes in a post or page</strong>, just write in the editor <code>[all-quotes rows=10, orderby="quoteID", sort="ASC", group="all"]</code>. The variables here I think are pretty straightforward, but to clarify: <em>rows</em> is how many quotes you want per page; <em>orderby</em> if you want to order the quotes by "quoteID", "author" "source" or "group"; <em>sort</em> is whether the quotes will be sorted ascending "ASC" or descending "DESC". Finally <em>groups</em> is from which group you want the quotes to be. Use "all" for all the groups, or use the names of the groups separated by comma. All these settings are optional. Without them, the values you see in this example are used as defaults.', 'stray-quotes');?></li> 
    </ul></p>
    <h3><?php echo __('What else can I do?','stray-quotes');?></h3>
    <p><ul>
    <li><?php echo __('Well you could insert <strong>a given quote in a post or a page</strong>, in which case you should just write in the editor <code>[quote id=x]</code>, where <code>x</code> is the id number of the quote as it appears on the management page.', 'stray-quotes');?></li>
    <li><?php echo __('To insert <strong>a given quote in your template</strong> use the following: <code>&lt;?php if (function_exists(\'stray_a_quote\')) stray_a_quote(id);?&gt;</code>.', 'stray-quotes');?></li>
    </ul></p>
    
    <h3><?php echo __('Is that really all?','stray-quotes');?></h3>
    <p><?php echo str_replace("%s1","http://www.italyisfalling.com/stray-random-quotes/",__('Look, for further help, you\'re welcome to trash the comments <a href="%s1">on this post</a>. I am stopping here because this page proved well enough already that I am not having a life.<br /><br />', 'stray-quotes')); ?></p>

</div><?php

}
?>
