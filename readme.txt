=== Stray Random Quotes ===
Contributors: ico@italyisfalling.com
Donate link: http://www.italyisfalling.com/lines-of-code#donate
Tags: quotes, random, widget, sidebar, AJAX, random quotes, random words, quotations, words, multiuser, randomness, shortcodes
Requires at least: 2.3
Tested up to: 2.8
Stable tag: 1.9.6

Display and rotate random quotes and words everywhere on your blog. Easy to custom and manage. Multiuser. Ajax enabled.

== Description ==

Stray Random Quotes helps you collect and display random quotes everywhere on your blog. The plugin is so flexible that it can be used to display random words of all sorts: taglines, "leave a response" messages, footer or header sections etc.
The main features:

* As many **widgets** as you need, each with its individual set of options, to display one or more quotes from all or some categories, randomly or in sequence, with or without AJAX, etc.
* **AJAX** automation so a reader of the blog can get another quote without reloading the page.
* Optional **automatic rotation** of the quotes within a given interval of seconds.
* **Multiuser** ready (contributors to the blog can access a limited version of the plugin, adding and managing their own sets of quotes)
* **Shortcodes** that can be used to add one quote or series of quotes to your posts and pages. The shortcodes come with a set of individual options as well and, if needed, they can be extended to apply everywhere on the blog, allowing random words for the tagline, the category names, the post titles etc.
* **Template tags** to add one or more quotes -- random words in general -- directly to the template pages. Template tags support many variables as well.
* A **Settings page** to customize the appearance of the quotes with little or no knowledge of HTML.
* A easy to use **management page** where even thousands of quotes can be handled easily, with bulk actions to change category, delete quotes and toggle visibility of many quotes at a time.
* A **bookmarklet** to create quotes on the fly as you browse the web and find text worth quoting.
* A **help page** where everything you need to know is explained.

See [more cool things you can do with Stray Random Quotes](http://www.italyisfalling.com/cool-things-you-can-do-with-stray-random-quotes/).

== Screenshots ==

1. How the management page works.
2. How to add a new quote.
3. A random quote in the sidebar.
4. Bulk editing in the Management page.
5. The bookmarklet in the Tools page.
6. The Settings page.
7. The widget options.

== Installation ==

1. Upload the content of stray-quotes.zip to a dedicated folder in your `/wp-content/plugins/` directory.
2. Activate the plugin on the 'Plugins' page in WordPress.
3. Stray Random Quotes has its own menu. Check the overview page in "Quotes" > "Overview". All the rest will come naturally.

_Note when upgrading: If you are not automatically upgrading via Wordpress, always **deactivate the older version** first and **delete the old 'stray-quotes' folder**. It is not normally necessary to backup the quotes in the database unless so advised in the changelog or on the [plugin feed](http://www.italyisfalling.com/category/wordpress-things/feed/)._

== Changelog - version 1.9.6 ==

* **Changed**: For compatibility with other plugins that use similar or too common names for their shortcodes (above all NextGen gallery), the shortcodes of Stray Random Quotes have changed names. Please take note before you complain about malfunctions: `random-quote` is now `stray-random`, `all-quotes` is now `stray-all` and `quote` is now `stray-id`. Please make the appropriate changes *everywhere in your blog* where Stray Random Quotes shortcodes are used, or they won't work anymore. Sorry about this, I hoped and tried to implement a more clever automatic check on the active shortcodes but unfortunatley many plugins don't register theirs correctly and it is not possible from inside a wordpress plugin to identify them with certainty. 
* **Fixed**: some of the settings got reset upon activation because of a comparison based on version number that for unkown reasons did not work. Possibly a PHP bug. I disabled the comparison and the problem should not present itself anymore, although it might cause issues to users who upgrade from very old versions of the plugin.
* **Fixed**: a compatibility issue with Wordpress 2.8 in conjunction with other plugins that loaded jquery (as experienced by many with Ozh Admin Drop Menu+Stray Random Quotes). I welcome anyone's feedback on this since I haven't tested the fix in all possible scenarios and other problems might be lurking around.

Read the complete changelog [here](http://www.italyisfalling.com/stray-random-quotes).

== Credits ==

* For Multi-widget functionality, [Millian's tutorial](http://wp.gdragon.info/2008/07/06/create-multi-instances-widget/)
* For help in developing user-end AJAX functionality, [AgentSmith](http://www.matrixagents.org)

== Localization ==

* German, thanks to Markus Griesbach
* Chinese, thanks to [WGMking](http://2say.hi.cn/)
* Croatian, thanks to [Rajic](http://www.atrium.hr/)
* Danish, thanks to [Georg](http://wordpress.blogos.dk/)

(Some of these translations might not be updated to the latest version, depending on the maintainer.)

The best way to **submit new or updated translations** is to include a direct link to the localization files in a comment to [this post](http://www.italyisfalling.com/stray-random-quotes#comments). This way the files are made available to the users sooner, and without waiting for a new release.

_Please note: there is a lot to translate with this plugin, expecially on account of the help page. If you want to create a localized copy of Stray Random Quotes, consider skipping the help page and translate the rest. This will save you some time._
