<?php

    if ( ! defined( 'XV_RANDOM_QUOTES' ) ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden' );
        exit();
    }

    require_once plugin_dir_path( __FILE__ ).'/class.adminbase.php' ;

    class XV_RandomQuotes_Help extends XV_RandomQuotes_AdminBase {

        private function help_header() {
             ?>
        <div class="wrap" style="width:52%"><h2><?php _e('The help page','stray-quotes');?></h2>
        <!--<span class="setting-description"><?php _e(' ~ where <strong>ico</strong> answers questions, as you click on them.','stray-quotes');?></span>-->
            <?php
        }

        private function random_quotes_in_blog() {

        if(current_user_can('manage_options')) {

            // how do I display random quotes on this blog? ?>
            <h3 style="cursor:pointer" onclick="expand('text1', this);"><?php _e('So, how do I display a random quote on this blog?','stray-quotes');?></h3>
            <div id="text1" style="display:none;"><blockquote><p><?php echo str_replace("%s1",$this->get_widgets_url(),__('Stray Random Quotes comes with <strong>Widgets</strong>. You can use all the widgets you want. Each widget has its own set of options. Just drag a "Quotes" widget to the sidebar on the <a href="%s1">widget page</a>, and change its settings accordingly.<br />Note: <em>Your template must be widget compatible.</em></p>','stray-quotes')); ?></p></blockquote></div>

            <?php // What if I don\'t use widgets?' ?>
            <h3 style="cursor:pointer" onclick="expand('text2', this);"><?php _e('Can I add quotes directly to the template?','stray-quotes');?></h3>
            <div id="text2" style="display:none;"><blockquote><p><?php _e('If your template <strong>does not</strong> use widgets, or you want to display random quotes on your template <strong>elsewhere</strong> other than the sidebar, you can use <strong>template tags</strong>. These are PHP snippets that can be added directly to your template pages (in the header, the footer etc). You can use as many snippets as you need. They accept variables, but these have to be inserted in order. For example, if you want to set only the second variable, you have to set the first one too, but you can skip all those that follow if you don\'t need them.','stray-quotes');?></p>

            <p><strong><?php _e('To get one or more random quotes: ', 'stray-quotes');?>

            <code>&lt;?php if (function_exists('stray_random_quote')) stray_random_quote('category1,category2',false,'another quote &amp;raquo;',false,1,0,'quoteID','ASC', false, ''); ?&gt;</code></strong><br/><br/>

            <?php _e('Variables :', 'stray-quotes');?><br/>

            <ul>

            <li>1. <strong><code><?php _e('category1,category2', 'stray-quotes');?></code></strong> : <?php _e('A comma-separated list of the categories from which to extract the random quote. <code>\'all\'</code> or <code>\'\'</code> uses all the categories. Default is \'all\'.', 'stray-quotes');?></li>

            <li>2. <strong><code>true/false</code></strong> : <?php _e('Loads the quotes in order if "true", randomly if "false". Default is "false".', 'stray-quotes');?></li>

            <li>3. <strong><code><?php _e('"another quote &amp;raquo;"', 'stray-quotes');?></code></strong> : <?php _e('The phrase to dynamically load another quote (overrides the one in the settings). If this value is empty, and the one in the settings is empty too, clicking on the quote area will reload the quote. Default is empty.', 'stray-quotes');?></li>

            <li>4. <strong><code>true/false</code></strong> <?php _e('Disables AJAX for the present template tag if "true". default is "false".', 'stray-quotes');?></li>

            <li>5. <strong><code>1</code></strong> :  <?php _e('How many quotes to retrieve at a time. Default is 1.', 'stray-quotes');?></li>

            <li>6. <strong><code>0</code></strong> : <?php _e('Reloads the quote automatically after the given interval (in seconds). Hides the linkphrase. Use 0 or \'\' to disable. Default is 0.', 'stray-quotes');?></li>

            <li>7. <strong><code>quoteID</code></strong> <?php _e('Orders the quotes according to "quoteID" or "Author" or "Source" or "Visible" or "Category". Default is "quoteID".', 'stray-quotes');?></li>

            <li>8. <strong><code>ASC</code></strong> <?php _e('Sorts the quotes ascending "ASC" or descending "DESC". Default is "ASC".', 'stray-quotes');?></li>

            <li>9. <strong><code>true/false</code></strong> <?php _e('Disables the aspect settings (how the quotes look) if "true". default is "false".', 'stray-quotes');?></li>

            <li>10. <strong><code>''</code></strong> <?php _e('Contributor whose quotes have to be used exclusively. A empty value by default takes quotes from all contributors.', 'stray-quotes');?></li>

            </ul></p>

            <p><strong><?php _e('To get a specific quote: ', 'stray-quotes');?>

            <code>&lt;?php if (function_exists('stray_a_quote')) stray_a_quote(31,'Next quote &amp;raquo;',false, false); ?&gt;</code></strong><br/><br/>

            <?php _e('Variables :', 'stray-quotes');?><br/>

            <ul>

            <li>1. <strong><code><?php _e('31', 'stray-quotes');?></code></strong> : <?php _e('The ID number of the quote to be retrieved. Default is 1.', 'stray-quotes');?></li>

            <li>2. <strong><code><?php _e('Next quote &amp;raquo;', 'stray-quotes');?></code></strong> : <?php _e('The phrase to dynamically load the next quote in sequence. If this value is empty, and the one in the settings is empty too, clicking on the quote area will reload the quote. Default is empty.', 'stray-quotes');?></li>

            <li>3. <strong><code>true/false</code></strong> <?php _e('Disables AJAX for the present template tag if "true". default is "false".', 'stray-quotes');?></li>

            <li>4. <strong><code>true/false</code></strong> <?php _e('Disables the aspect settings (how the quotes look) if "true". default is "false".', 'stray-quotes');?></li>


            </ul></p><p><?php _e('Unlike widgets, template tags do not generate a title for the quote area. If you want specific titles for different tags, enter them directly in the template HTML, before the tag.' , 'stray-quotes'); ?>
            </p></blockquote></div>

        <?php } ?>

        <?php 
        }

        private function quotes_in_posts() {

        // What about posts and pages?
            if(current_user_can('manage_options')) { ?>
            <h3 style="cursor:pointer" onclick="expand('text3', this);"><?php _e('What about posts and pages?','stray-quotes');?></h3>
            <?php }  else { ?>
            <h3 style="cursor:pointer" onclick="expand('text3', this);"><?php _e('How do I add quotes to posts and pages?','stray-quotes');?></h3>
            <?php } ?>
            <div id="text3" style="display:none;"><blockquote><p>

            <?php echo str_replace("%s1",'http://codex.wordpress.org/Shortcode_API',__('Posts and pages accept <strong>shortcodes</strong>. Wordpress shortcodes can have many variables, in no particular order. More informations about the use of shortcodes are on <a href="%s1">this page</a>.', 'stray-quotes'));?><br/><br/>

            <?php _e('To <strong>insert a random quote</strong> in a post or page use:', 'stray-quotes');?> <code><strong>[stray-random]</strong></code>

            <br/><br/><?php _e('Variables (use quotations if there are spaces in the value):', 'stray-quotes');?><br/>

            <ul><li><strong><code>categories=cat1,cat2,etc</code></strong> <?php _e('A comma-separated list of the categories from which to extract the random quote. If none is set, quotes will be taken from all categories.', 'stray-quotes');?></li>

            <li><strong><code>sequence=true</code></strong> <?php _e('Loads the quotes in order if "true", randomly if "false". Default is "false"', 'stray-quotes');?></li>

            <li><strong><code>linkphrase=<?php _e('"another quote &amp;raquo;"', 'stray-quotes');?></code></strong> <?php _e('The phrase to dynamically load another quote (overrides the one in the settings). Without this value, clicking on the quote area will reload the quote.', 'stray-quotes');?></li>

            <li><strong><code>noajax=true</code></strong> <?php _e('Disables AJAX for the present shortcode if "true".', 'stray-quotes');?></li>

            <li><strong><code>timer=5</code></strong> <?php _e('Reloads the quote automatically after the given interval (in seconds). Hides the linkphrase. ', 'stray-quotes');?></li>

            <li><strong><code>disableaspect=false</code></strong> <?php _e('Disable the aspect settings if "true". Default is "false"', 'stray-quotes');?></li>

            <li><strong><code>user=<em>user-name-goes-here</em></code></strong>  <?php _e('Contributor whose quotes have to be used exclusively. Without this setting, quotes are taken from all contributors.', 'stray-quotes');?></li>

            </ul>

            <br/><?php _e('All these settings are optional. If you don\'t indicate anything, a random quote from all the categories will be displayed, with ajax according to the general settings.<br/><br/>', 'stray-quotes');?></p>

            <p><?php _e('To <strong>insert more than one or all the quotes</strong> in a post or page use:', 'stray-quotes');?> <code><strong>[stray-all]</strong></code>

            <br/><br/><em><?php _e('Variables (use quotations if there are spaces in the value):', 'stray-quotes');?></em><br/>

            <ul><li><strong><code>categories="cat1,cat2,etc"</code></strong> <?php _e('A comma-separated list of the categories from which to extract the random quote. If none is set, quotes will be taken from all categories.', 'stray-quotes');?></li>

            <li><strong><code>sequence=true</code></strong> <?php _e('Loads the quotes in order if "true", randomly if "false". Default is "false"', 'stray-quotes');?></li>

            <li><strong><code>linkphrase=<?php _e('"another quote &amp;raquo;"', 'stray-quotes');?></code></strong> <?php _e('The phrase to dynamically load another quote (overrides the one in the settings). Without this value, clicking on the quote area will reload the quote.', 'stray-quotes');?></li>

            <li><strong><code>rows=10</code></strong> <?php _e('How many quotes to retrieve at a time.', 'stray-quotes');?></li>

            <li><strong><code>orderby="quoteID"</code></strong> <?php _e('Orders the quotes according to "quoteID" or "Author" or "Source" or "Visible" or "Category".', 'stray-quotes');?></li>

            <li><strong><code>sort="ASC"</code></strong> <?php _e('Sorts the quotes ascending "ASC" or descending "DESC".', 'stray-quotes');?></li>

            <li><strong><code>fullpage=true</code></strong> <?php _e('Generates complete pagination links (with page numbers) if "true", only next and previous links if "false". Not considered if AJAX is enabled.', 'stray-quotes');?></li>

            <li><strong><code>noajax=true</code></strong> <?php _e('Disables AJAX for the present shortcode if "true".', 'stray-quotes');?></li>

            <li><strong><code>timer=5</code></strong> <?php _e('Reloads the quotes automatically after the given interval (in seconds). Hides the linkphrase. ', 'stray-quotes');?></li>

            <li><strong><code>disableaspect=false</code></strong> <?php _e('Disable the aspect settings if "true". Default is "false"', 'stray-quotes');?></li>

            <li><strong><code>user=<em>user-name-goes-here</em></code></strong>  <?php _e('Contributor whose quotes have to be used exclusively. Without this setting, quotes are taken from all contributors.', 'stray-quotes');?></li>

            </ul>

            <br/><?php _e('All these settings are optional. Without them, AJAX is disabled in the shortcode and the values you see in this example are used as defaults.', 'stray-quotes');?><br/><br/></p>


            <p><?php _e('To <strong>insert a specific quote</strong> in a post or page (with AJAX, a sequence of quotes starting from a given one) use:', 'stray-quotes');?> <code><strong>[stray-id id=x]</strong></code>

            <br/><br/><em><?php _e('Variables (use quotations if there are spaces in the value):', 'stray-quotes');?></em><br/>

            <ul><li><strong><code>id=12</code></strong> <?php _e('The ID of the quote to be retrieved. This number appears in the first column on the management page.', 'stray-quotes');?></li>

            <li><strong><code>noajax=true</code></strong> <?php _e('Disables AJAX for the present shortcode if "true". Default is true.', 'stray-quotes');?></li>

            <li><strong><code>linkphrase=<?php _e('"another quote &amp;raquo;"', 'stray-quotes');?></code></strong> <?php _e('The phrase to dynamically load another quote (overrides the one in the settings). Without this value, clicking on the quote area will reload the quote.', 'stray-quotes');?></li>

            <li><strong><code>disableaspect=false</code></strong> <?php _e('Disable the aspect settings if "true". Default is "false"', 'stray-quotes');?></li>

            </ul>

            </p></blockquote></div><?php

        }

        private function other_questions() {

            if(current_user_can('manage_options')) {

                // What about other areas of the blog, such as post titles, or even the blog title? ?>
                <h3 style="cursor:pointer" onclick="expand('text4', this);"><?php _e('What about other areas of the blog, such as post titles, or even the blog title?','stray-quotes');?></h3>
                <div id="text4" style="display:none;"><blockquote><p><?php echo str_replace("%s1",$this->get_stray_tools_url(),__('Well, actually, on the <a href="%s1">tools page</a> you can enable shortcodes for a number of extra areas where shortcodes aren\'t normally allowed. This will entitle you to some quite extraordinary things, such as random taglines or random category names. Cool examples can be found <a href="http://www.italyisfalling.com/cool-things-you-can-do-with-stray-random-quotes">here</a>.<br/><br/><strong><em>Please Note:</em></strong> it is <strong>highly recommended to disable AJAX</strong> for all these unconventional uses. This will lessen a potential impact on your hosting server caused by a excessive refreshing of quotes, plus AJAX adds a DIV tag around the quotes that would mess with your layout in most cases where the random word is inline with the text.<br/><br/> <strong><em>Please Note:</em></strong> This trick will apply to all shortcodes, not just those of Stray Random Quotes: so pay attention before you enable it for areas where the user has access, such as the comments.', 'stray-quotes'));?></p></blockquote></div>

                <?php //How do I change the appearance of the quotes? ?>
                <h3 style="cursor:pointer" onclick="expand('text5', this);"><?php _e('How do I change the appearance of the quotes?','stray-quotes');?></h3>
                <div id="text5" style="display:none;"><blockquote><p><?php echo str_replace("%s1",$this->get_stray_options_url(),__('To change how the quotes look, you can use the <a href="%s1">settings page</a>. Here you will be able to set what comes before and after every part of the quote area (source, author, the quote etc). With little HTML, all sorts of customizations are possible, such as assigning styles, set the alignment of text, specify font or font color, adding images, quotation marks and many other entities. Just remember to close all the tags you opened before a part.<br/><br/><em><strong>Please note:</strong></em> the aspect options in the settings page normally apply to <em>ALL</em> quotes in every area of your blog, but they can be disabled within each widget, shortcode or template tag. When aspect settings are disabled, the only element added is a whitespace between the quote and the author or source.', 'stray-quotes'));?></p></blockquote></div>

                <?php // The IDs of the quotes are getting ridiculously high ?>
                <h3 style="cursor:pointer" onclick="expand('text6', this);"><?php _e('The IDs of the quotes are getting ridiculously high. Can I do something about it?','stray-quotes');?></h3>
                <div id="text6" style="display:none;"><blockquote><p><?php echo str_replace("%s1",$this->get_stray_tools_url(),__('Over time, deleting quotes and adding quotes, the IDs of new quotes can get to be much higher of the total of the quotes. Some folks are bothered by this. It is the way MySQL works, gaps are not important in an index. Anyway, there is a workaround. On the <a href="%s1">tools page</a> there\'s a button to <strong>reset all quoteID numbers in your table</strong>, after which the numbering will look fine again. There is a downside, though: few or even none of the old ID numbers will still correspond to the same quotes, which means that if you called a quote in a post <strong>by its ID</strong> (as explained above on this very page), there\'s a chance a different quote will be called afterwards. It is up to you to accept this and go and fix it later.', 'stray-quotes')); ?></p></blockquote></div>

                <?php // The bookmarklet ?>
                <h3 style="cursor:pointer" onclick="expand('text7', this);"><?php _e('I want a bookmarklet to quote things on the fly when I browse!','stray-quotes');?></h3>
                <div id="text7" style="display:none;"><blockquote><p><?php echo str_replace("%s1",$this->get_stray_tools_url(),__('You have it. It is on the <a href="%s1">tools page</a>. Place the link on your browser toolbar, select text in a page and click on the link. The "add new quote" page will open with the selected text in it.', 'stray-quotes')); ?></p></blockquote></div>

                <?php // upgrading ?>
                <h3 style="cursor:pointer" onclick="expand('text10', this);"><?php _e('I am about to upgrade and I am scared. Should I backup my quotes?','stray-quotes');?></h3>
                <div id="text10" style="display:none;"><blockquote><p><?php echo str_replace(array("%s1","%s2"),array('http://wordpress.org/extend/plugins/stray-quotes/other_notes/','http://code.italyisfalling.com/feed/'),__('It is normally not necessary to backup the quotes before a upgrade. Usually when a new version of Stray Random Quotes is activated the database table that contains the quotes is untouched. Exceptionally it may be necessary to manipulate it, in which case a backup is probably advisable. To know when this rare case applies you can check the changelog on <a href="%s1">this page</a>, or, more simply, you can follow the <a href="%s2">plugins feed</a> on my blog.<br/>How to backup, you ask? There are exceptional plugins out there to do that.', 'stray-quotes')); ?></p></blockquote></div>

                <?php } ?>

                <?php // HTTPS ?>
                <h3 style="cursor:pointer" onclick="expand('text11', this);"><?php _e('I am running Stray Random Quotes on a HTTPS server and some of the links in the menu redirect to regular HTTP.','stray-quotes');?></h3>
                <div id="text11" style="display:none;"><blockquote><p>
                <?php _e('It is not entirely clear why this can happen, anyway there is workaround. Add to your wp-config.php file this line: <code>define(\'FORCE_SSL_ADMIN\', true);</code>. This will force all admin links to stay within your https boundaries. Also consider looking in the Wordpress Repository for plugins dedicated to enforcing HTTPS on wordpress, there\'s more than one.'); ?></p></blockquote></div>

                <?php // Hey. Something isn\'t working! ?>
                <h3 style="cursor:pointer" onclick="expand('text8', this);"><?php _e('Hey. Something isn\'t working!','stray-quotes');?></h3>
                <div id="text8" style="display:none;"><blockquote><p><?php echo str_replace("%s1","http://www.italyisfalling.com/stray-random-quotes/",__('Well, that figures. See, there\'s always going to be a bug. If you want to help me catch it, and to get further help, you are welcome to trash the comments <a href="%s1">on this post</a>. Before you do, though, check if the problem you are experiencing isn\'t caused by a conflicting plugin or some other issue of which Stray Random Quotes itself might not be responsible. Thanks.', 'stray-quotes')); ?></p></blockquote></div>
            <?php
        }

        private function help_footer() {
            ?></div><?php
        }

        public function show_help() {
            $this->help_header();

            $this->random_quotes_in_blog();

            $this->quotes_in_posts();

            $this->other_questions();

            $this->help_footer();
        }
    }
