=== Stray Quotes ===
Plugin Name: Stray Quotes
Contributors: corpodibacco
Tags: quotes, random, widget
Requires at least: 2.0.2
Tested up to: 2.5
Stable tag: 1.50
url http://www.italyisfalling.com/stray-quotes/

Display random quotes on your blog. Easy to custom and manage. Compatible with Wordpress 2.5.

== Description ==

Stray Quotes is a plugin for Wordpress that collects and displays random quotes on your blog. They can be shown one by one or as a whole in a page. The plugin is widget compatible, and the appearance of the quotes can be highly customized. It comes with a easy to use management tool and a option page.

== Screenshots ==

1. Your quotes are displayed in a management page, where from they can be edited or deleted, and where new quotes can be added.

2. The option page offers many option to customize the apperance of the quotes.

3. A random quote appears in the sidebar as one of the widgets.

== Installation ==

1. Upload the content of stray-quotes.zip to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. If you use widgets in your theme, you can enable Stray Quotes' from the widget page. You will be given the option to add a title to the sidebar element. If you don't use the widget function, just add this to your sidebar: `&lt;?php if (function_exists('wp_quotes_random')) wp_quotes_random(); ?&gt;`. 
4. To add a given quote to the sidebar use the following: `&lt;?php if (function_exists('wp_quotes')) wp_quotes(id);?&gt;`, where `id` is the id number of the quote as it appears on the management page. More options on how to customize the sidebar appearance are on the Stray Quotes option page. 
5. To spew a list of all the quotes, just add the following to a page or post (you can past this directly in the post editor) `&lt;!--wp_quotes_page--&gt;`
