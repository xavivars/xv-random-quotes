=== XV Random Quotes ===
Contributors: xavivars
Old contributors: ico@italyisfalling.com, Sergey Sirotkin
Tags: quotes, random, gutenberg, blocks, widget, sidebar, rest-api, custom-post-type
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: http://xavi.ivars.me/codi/xv-random-quotes-wordpress-plugin-english/

Display and rotate quotes anywhere on your WordPress site. Fully integrated with WordPress Custom Post Types, Gutenberg blocks, and REST API.

== Description ==

XV Random Quotes helps you collect and display random quotes everywhere on your WordPress site. Built with modern WordPress standards using Custom Post Types, Gutenberg blocks, and the REST API.

**🎯 Modern WordPress Integration**

* **Custom Post Type** - Quotes are managed as native WordPress posts with full revision history
* **Gutenberg Blocks** - Three dedicated blocks: Random Quote, Specific Quote, and List Quotes
* **REST API** - Access quotes programmatically for headless WordPress and custom integrations
* **Taxonomy Support** - Organize quotes with categories and authors (with URL support)
* **Block Editor** - Full support with dedicated meta boxes for quote content and source

**✨ Key Features**

* **Multiple Display Methods** - Widgets, shortcodes, template tags, and Gutenberg blocks
* **AJAX-Powered Widgets** - Automatic quote rotation without page reload (configurable timer)
* **Category Filtering** - Display quotes from specific categories or all quotes
* **Author Management** - Track quote authors with optional URL links
* **Flexible Ordering** - Random or sequential quote display
* **Native Styling Toggle** - Use plugin's default styling or your theme's styles
* **Complete Backward Compatibility** - All legacy shortcodes and template tags still work

**📦 Display Options**

* **Gutenberg Blocks**: 
  - Random Quote Block - Display one or more random quotes
  - Specific Quote Block - Show a particular quote by ID
  - List Quotes Block - Paginated list of quotes with filtering
* **Widgets**: Sidebar widget with AJAX refresh and category filtering
* **Shortcodes**: `[stray-random]`, `[stray-id]`, `[stray-all]` for posts and pages
* **Template Tags**: `stray_random_quote()`, `stray_a_quote()` for theme integration
* **REST API**: `/wp-json/xv-random-quotes/v1/quote/random` for custom integrations

**🔄 Automatic Migration**

Upgrading from v1.x? The plugin automatically migrates your existing quotes to the new Custom Post Type system. Small databases (≤500 quotes) migrate instantly on activation. Large databases use a batch migration system with progress tracking.

**🎨 Customization**

* Customize HTML wrappers (before/after quote, author, source)
* Control author and source link formatting
* AJAX loading messages and animations
* Native WordPress styling or custom CSS

See [RELEASE_NOTES.md](https://github.com/xavivars/xv-random-quotes/blob/main/RELEASE_NOTES.md) for complete v2.0 features and migration guide.

== Screenshots ==

1. Gutenberg blocks in the block inserter
2. Random Quote block in the editor
3. Quote management using WordPress Custom Post Types
4. Quote editing with meta boxes for content and source
5. Widget settings with AJAX options
6. Settings page for display customization
7. Migration progress for large databases

== Installation ==

**Automatic Installation**

1. Go to Plugins > Add New in your WordPress admin
2. Search for "XV Random Quotes"
3. Click "Install Now" and then "Activate"
4. If upgrading from v1.x, migration will start automatically

**Manual Installation**

1. Download the plugin zip file
2. Upload to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Quotes > All Quotes to start adding quotes

**After Installation**

* **New Installation**: Go to Quotes > Add New to create your first quote
* **Upgrading from v1.x**: 
  - Small databases (≤500 quotes) migrate automatically on activation
  - Large databases show an admin notice with "Start Migration" button
  - All existing quotes, categories, and settings are preserved
  - See migration guide in RELEASE_NOTES.md for details

**Using Gutenberg Blocks**

1. Edit any post or page
2. Click the + button to add a block
3. Search for "quote" to find the three quote blocks:
   - Random Quote - Display random quotes with category filtering
   - Specific Quote - Show a particular quote by ID
   - List Quotes - Paginated list with ordering options

**Using Widgets**

1. Go to Appearance > Widgets
2. Add "XV Random Quotes" widget to your sidebar
3. Configure categories, display options, and AJAX settings
4. Save and view your site

**Using Shortcodes**

Add to any post or page content:
- `[stray-random]` - Display a random quote
- `[stray-random categories="inspiration,wisdom"]` - Random quote from specific categories
- `[stray-id id="123"]` - Display a specific quote
- `[stray-all]` - Display all quotes with pagination

See the Help page (Quotes > Help) for complete documentation.

== Frequently Asked Questions ==

= What's new in version 2.0? =

Version 2.0 is a complete modernization using WordPress Custom Post Types, Gutenberg blocks, and REST API. All legacy features remain fully compatible.

= Will my existing quotes be lost when upgrading? =

No! The plugin automatically migrates all existing quotes from the old database table to the new Custom Post Type system. Categories, authors, and all metadata are preserved.

= How does the migration work? =

* **Small databases (≤500 quotes)**: Automatic migration on plugin activation
* **Large databases (>500 quotes)**: Shows admin notice with "Start Migration" button for batch processing
* Migration is safe and can be resumed if interrupted
* Original data remains in the database for safety

= Can I still use my old shortcodes? =

Yes! All legacy shortcodes work exactly as before: `[stray-random]`, `[stray-id]`, `[stray-all]`, and template tags like `stray_random_quote()`.

= How do I use the Gutenberg blocks? =

1. Edit any post or page in the Block Editor
2. Click + to add a block
3. Search for "quote"
4. Choose from: Random Quote, Specific Quote, or List Quotes blocks
5. Configure block settings in the sidebar

= Can I use AJAX to refresh quotes without page reload? =

Yes! Enable AJAX in the widget settings. You can set a timer for automatic rotation or allow manual refresh with a click link.

= How do I add quotes? =

Go to Quotes > Add New in your WordPress admin. Add the quote title, content (with basic formatting), author, source, and categories just like creating a post.

= Can I organize quotes into categories? =

Yes! Use the Quote Categories taxonomy (similar to post categories). Assign multiple categories to each quote and filter by category in widgets, blocks, and shortcodes.

= How do I add author information with a link? =

1. Go to Quotes > Authors
2. Add or edit an author
3. In the "Author URL" field, enter the author's website
4. Author names will automatically link to their URLs when displayed

= Does this work with the Classic Editor? =

Yes! The plugin works with both the Block Editor and Classic Editor. Meta boxes are available for adding quote content and source in both editors.

= Is there a REST API endpoint? =

Yes! Access random quotes via `/wp-json/xv-random-quotes/v1/quote/random` with parameters for categories, sequence, and more. Perfect for headless WordPress.

= Can I customize the HTML output? =

Yes! Go to Quotes > Settings to customize HTML wrappers, link formats, and styling options. You can also toggle native styling to use your theme's styles.

= Where can I get support? =

* Documentation: Quotes > Help in your WordPress admin
* GitHub Issues: https://github.com/xavivars/xv-random-quotes/issues
* WordPress Forums: https://wordpress.org/support/plugin/xv-random-quotes

= How do I contribute? =

Contributions are welcome! Visit the GitHub repository at https://github.com/xavivars/xv-random-quotes and submit pull requests or report issues.

== Upgrade Notice ==

= 2.0.0 =
Major modernization release! Automatic migration from v1.x. Uses WordPress Custom Post Types, Gutenberg blocks, and REST API. 100% backward compatible - all old shortcodes and template tags still work. Recommended: Backup your database before upgrading (standard WordPress best practice).

== Changelog ==

See changelog.txt for complete version history.


== Credits ==

* For main development of Stray Quotes, [Ico](http://unalignedcode.wordpress.com/my-wordpress-plugins/stray-random-quotes/)
* For Multi-widget functionality, [Millian's tutorial](http://wp.gdragon.info/2008/07/06/create-multi-instances-widget/)
* For help in developing user-end AJAX functionality, [AgentSmith](http://www.matrixagents.org)
* For search functionality and bugfixing, [Sergey Sirotkin](http://www.zeyalabs.ch/posts/2010/stray-quotes-z/)


== Localization ==

* German, thanks to Markus Griesbach
* Chinese, thanks to WGMking
* Croatian, thanks to [Rajic](http://www.atrium.hr/)
* Danish, thanks to [Georg](http://wordpress.blogos.dk/)

Actually, these translations are not updated to the latest version.
I am looking for new localizers, all languages welcome!

_Please note:_ the best way to **submit new or updated translations** is to send me a direct link to the localization files in [the contact page](http://xavi.ivars.me/contacta/) of my website. This way the files are made available to the users sooner, and without waiting for a new release.

_Please note:_ If you want to create a localized copy of XV Random Quotes, consider skipping the help page and translate the rest. This will save you quite some time. The help page has a lot of text.


== Known Issues ==

None currently reported for v2.0. If you find an issue, please report it on [GitHub](https://github.com/xavivars/xv-random-quotes/issues).
