# XV Random Quotes v2.0.0 - Release Notes

**Release Date:** December 29, 2025  
**Major Version:** 2.0.0  
**Type:** Major Feature Release with Architecture Modernization

---

## 🎯 Overview

XV Random Quotes v2.0.0 is a complete modernization of the plugin, bringing it fully into the modern WordPress ecosystem. This release migrates from a legacy custom database table to WordPress Custom Post Types (CPT), adds full Gutenberg Block Editor support, implements a REST API, and modernizes the entire codebase with zero breaking changes for existing users.

**Key Highlights:**
- ✅ **Full backward compatibility** - All existing shortcodes, template tags, and widgets continue to work
- ✅ **Automatic migration** - Seamless upgrade from v1.x with data preservation
- ✅ **Modern WordPress integration** - CPT, Block Editor, REST API
- ✅ **Enhanced admin experience** - Simplified editing with custom meta boxes
- ✅ **AJAX refresh** - Modern JavaScript implementation with REST API
- ✅ **385 automated tests** - Comprehensive test coverage ensuring quality

---

## 🚀 What's New

### 1. Gutenberg Block Editor Support

Three new blocks are now available in the Block Editor:

#### **Random Quote Block**
Display one or more random quotes anywhere in your content.

**Features:**
- Category filtering (select specific categories or all)
- Multiple quotes display (set quantity)
- Native WordPress blockquote styling or custom HTML wrappers
- AJAX refresh with manual or automatic timer
- Full i18n support

**Usage:**
1. Click the (+) button in the Block Editor
2. Search for "Random Quote"
3. Configure categories, quantity, and AJAX settings
4. Publish!

#### **Specific Quote Block**
Display a particular quote by its ID.

**Features:**
- Select quote from dropdown
- Supports both new post IDs and legacy quote IDs
- Native or custom styling
- Perfect for highlighting specific quotes

**Usage:**
1. Add "Specific Quote" block
2. Select quote from dropdown or enter ID
3. Configure styling preferences

#### **List Quotes Block**
Display a paginated list of quotes with filtering and sorting.

**Features:**
- Category filtering
- Configurable page size
- Sort by ID, author, or random
- Ascending/descending order
- Optional pagination controls
- Sequential or random display

**Usage:**
1. Add "List Quotes" block
2. Configure filters and sorting
3. Set page size and pagination preferences

### 2. WordPress Custom Post Type Integration

Quotes are now first-class WordPress content.

**Benefits:**
- Native WordPress admin interface
- Search and filter in admin
- Bulk actions support
- Revision history
- Custom fields and meta data
- Integration with other plugins

**Post Type Details:**
- **Post Type:** `xv_quote`
- **Taxonomies:** 
  - `quote_category` (hierarchical, like WordPress categories)
  - `quote_author` (non-hierarchical, like tags)
- **Meta Fields:**
  - `_quote_source` - Source attribution with HTML support
  - `_quote_legacy_id` - Backward compatibility with v1.x IDs
  - `_quote_display_order` - Custom ordering
- **Supports:** Title (quote text), Author, Revisions, Custom Fields

**Author URLs:**
Author taxonomy terms can have URLs (e.g., Wikipedia links) stored as term meta. These are automatically extracted during migration from v1.x data and can be managed in the taxonomy interface.

### 3. REST API Integration

New REST endpoint for programmatic access and AJAX functionality.

**Endpoint:** `/wp-json/xv-quotes/v1/random`

**Parameters:**
- `categories` - Filter by category slugs (comma-separated)
- `multi` - Number of quotes to return (default: 1)
- `sequence` - Sequential ordering (default: false/random)

**Response Format:**
```json
{
  "quotes": [
    {
      "id": 123,
      "quote": "Quote text here",
      "author": "Author Name",
      "author_url": "https://en.wikipedia.org/wiki/Author_Name",
      "source": "Book Title",
      "categories": ["inspiration", "wisdom"]
    }
  ]
}
```

**Use Cases:**
- AJAX refresh in widgets and blocks
- External application integration
- Mobile app data source
- JavaScript-based quote displays

### 4. Modern Widget with AJAX

The Quote Widget has been completely rebuilt.

**New Features:**
- **AJAX Refresh:** Manual click or automatic timer-based refresh
- **REST API Integration:** Uses the new REST endpoint
- **Modern JavaScript:** Vanilla JS, no jQuery dependency
- **Smooth Animations:** Fade-in/fade-out transitions
- **Better Performance:** Conditional script loading

**Widget Settings:**
- Title
- Categories to display
- Number of quotes (multi)
- Sequential ordering
- Disable styling
- **Enable AJAX** (new)
- **Auto-refresh timer** (new, in seconds)

**Timer Modes:**
- `0` - Manual refresh (click link to get new quote)
- `> 0` - Auto-refresh every N seconds

### 5. Simplified Admin Interface

Quote editing is now simpler and more focused.

**Custom Meta Boxes:**
- **Quote Text Editor:** Rich text with wp_editor() in teeny mode
- **Author Selection:** Choose from existing authors or create new
- **Source Editor:** Rich text for source attribution

**Safe HTML Support:**
Allowed formatting tags: `<strong>`, `<em>`, `<b>`, `<i>`, `<code>`, `<abbr>`, `<cite>`, `<q>`, `<mark>`, `<sub>`, `<sup>`, `<a>`

Dangerous tags automatically stripped: `<script>`, `<iframe>`, `<style>`, `<object>`, `<embed>`

**Benefits:**
- No Block Editor complexity
- Focused editing experience
- HTML formatting support
- Safe from XSS attacks
- Familiar WordPress interface

### 6. Settings Page Modernization

The settings page has been rebuilt using WordPress Settings API.

**New Settings:**
- **Native WordPress Quote Styling:** Use WordPress blockquote styles or custom HTML
- **Display Options:** Before/after wrappers for quote, author, source
- **Author Options:** Author spaces, linking patterns
- **Source Options:** Source spaces, linking patterns
- **AJAX Options:** Loader text, before/after loader HTML, loading indicator

**Improvements:**
- Individual options (better performance than option arrays)
- Proper sanitization with WordPress functions
- Field validation
- Settings organized by category
- Help text for each field

---

## 🔄 Migration Process

### How Migration Works

XV Random Quotes v2.0 includes a comprehensive migration system that automatically converts your v1.x data to the new CPT architecture.

#### Small Databases (≤500 quotes)

**Automatic Migration:**
1. **Activate the Plugin:** Migration starts automatically
2. **Wait for Completion:** Usually completes in seconds
3. **Verify:** Check your quotes in the WordPress admin

**What Gets Migrated:**
- All quote text → Post content
- Authors → `quote_author` taxonomy terms
- Categories → `quote_category` taxonomy terms  
- Sources → `_quote_source` post meta
- Publication status → Post status (published/draft)
- Author URLs → Term meta (extracted from HTML if present)
- Legacy IDs → `_quote_legacy_id` post meta
- Widget settings → New widget format

#### Large Databases (>500 quotes)

**AJAX Batch Migration:**
1. **Activate the Plugin:** Notice appears with migration button
2. **Click "Start Migration":** AJAX batch process begins
3. **Monitor Progress:** Progress bar shows real-time status
4. **Completion:** Success message when finished

**Features:**
- Processes 100 quotes per batch
- Progress bar with percentage
- Resumable (handles interruptions)
- Background processing
- Real-time status updates

#### Migration Safety

**Data Preservation:**
- Original database table is **NOT** deleted
- Can roll back if needed
- Duplicate prevention (won't re-migrate)
- Idempotent (safe to run multiple times)

**Verification Steps:**
1. Check quote count matches
2. Verify a few quotes display correctly
3. Test shortcodes on existing pages
4. Check widget still works
5. Verify categories preserved

### Before You Upgrade

**⚠️ Important Pre-Upgrade Steps:**

1. **Backup Your Database**
   ```sql
   -- Backup your WordPress database
   mysqldump -u username -p database_name > backup.sql
   ```

2. **Test on Staging First**
   - Install v2.0 on staging environment
   - Run migration
   - Test all integrations
   - Verify data integrity

3. **Document Customizations**
   - Note any custom CSS
   - List custom template modifications
   - Record custom shortcode parameters
   - Check theme integrations

4. **Check Requirements**
   - WordPress 6.0 or higher
   - PHP 7.4 or higher
   - Modern browser for admin interface

### After Migration

**Recommended Actions:**

1. **Verify Data:**
   - Go to Quotes → All Quotes in admin
   - Check quote count matches old database
   - Spot-check several quotes for accuracy

2. **Test Display:**
   - Visit pages with `[stray-random]` shortcode
   - Check widget displays
   - Test `[stray-all]` pagination
   - Verify `[stray-id]` links still work

3. **Update Content (Optional):**
   - Try new Gutenberg blocks
   - Enable AJAX in widgets
   - Explore new REST API

4. **Monitor Performance:**
   - Check page load times
   - Monitor database queries
   - Watch for PHP errors
   - Test on different browsers

---

## 🔧 For Developers

### Architecture Changes

**Old Architecture (v1.x):**
```
Custom DB Table (stray_quotes)
  ↓
Raw SQL Queries
  ↓
Direct Output Generation
```

**New Architecture (v2.0):**
```
WordPress CPT (xv_quote)
  ↓
WP_Query Abstraction (QuoteQueries)
  ↓
Rendering Layer (QuoteRenderer)
  ↓
Output Formatting (QuoteOutput)
```

### Code Organization

**Namespace:** `XVRandomQuotes\`

**Directory Structure:**
```
src/
├── PostTypes/        # CPT registration
├── Taxonomies/       # Category & author taxonomies
├── PostMeta/         # Post meta registration
├── Admin/            # Meta boxes, settings, notices
├── Queries/          # WP_Query helpers
├── Rendering/        # Quote rendering logic
├── Output/           # Output formatting
├── Blocks/           # Gutenberg blocks
│   ├── RandomQuote/
│   ├── SpecificQuote/
│   └── ListQuotes/
├── RestAPI/          # REST endpoints
├── Widgets/          # Widget implementation
├── Migration/        # Database migration
└── Utils/            # Helper utilities
```

### Backward Compatibility

**Function Wrappers:**
All old functions are preserved in `backward-compatibility.php`:

```php
// Template Tags
function stray_random_quote($categories=NULL, $sequence=NULL, ...)
function stray_a_quote($id)
function stray_all_quotes($categories=NULL, $rows=10, ...)

// Shortcodes
function stray_random_shortcode($atts, $content = NULL)
function stray_all_shortcode($atts, $content = NULL)
function stray_id_shortcode($atts, $content = NULL)

// Legacy Function Names
function wp_quotes_random()
function wp_quotes($id)
function wp_quotes_page($data)
```

**Accessing CPT Data:**

```php
// Get quotes via WP_Query
$args = array(
    'post_type' => 'xv_quote',
    'post_status' => 'publish',
    'posts_per_page' => 10,
);
$quotes = new WP_Query($args);

// Get quote categories
$categories = get_terms('quote_category');

// Get quote authors
$authors = get_terms('quote_author');

// Get author URL
$author_url = get_term_meta($term_id, 'author_url', true);

// Get quote meta
$source = get_post_meta($post_id, '_quote_source', true);
$legacy_id = get_post_meta($post_id, '_quote_legacy_id', true);
```

### Custom Rendering

**Use the Rendering Classes:**

```php
use XVRandomQuotes\Rendering\QuoteRenderer;

$renderer = new QuoteRenderer();

// Render single quote
$html = $renderer->render_quote($post, $is_multi, $disable_aspect);

// Render multiple quotes
$html = $renderer->render_multiple_quotes($quotes, $disable_aspect);

// Choose rendering mode
$use_native = $renderer->should_use_native_blockquote($disable_aspect);
```

### REST API Integration

**JavaScript Example:**

```javascript
// Fetch random quote
fetch('/wp-json/xv-quotes/v1/random')
    .then(response => response.json())
    .then(data => {
        const quote = data.quotes[0];
        console.log(quote.quote, quote.author);
    });

// With parameters
const params = new URLSearchParams({
    categories: 'inspiration,wisdom',
    multi: 3,
    sequence: true
});

fetch(`/wp-json/xv-quotes/v1/random?${params}`)
    .then(response => response.json())
    .then(data => {
        data.quotes.forEach(quote => {
            console.log(quote);
        });
    });
```

### Extending the Plugin

**Add Custom Meta Fields:**

```php
add_action('init', function() {
    register_post_meta('xv_quote', 'my_custom_field', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
    ));
});
```

**Add Custom Taxonomy:**

```php
add_action('init', function() {
    register_taxonomy('quote_topic', 'xv_quote', array(
        'hierarchical' => false,
        'show_in_rest' => true,
        'labels' => array(
            'name' => 'Topics',
            'singular_name' => 'Topic',
        ),
    ));
});
```

**Filter Quote Output:**

```php
add_filter('xv_quote_output', function($html, $post, $disable_aspect) {
    // Modify quote HTML
    return $html;
}, 10, 3);
```

---

## 📊 Testing & Quality Assurance

### Test Coverage

**385 Automated Tests** covering:
- CPT registration and configuration
- Taxonomy registration and term meta
- Post meta field registration
- Data migration (single quote, batch, duplicates)
- Shortcode functionality (all parameters)
- Template tag compatibility
- Widget rendering and settings
- REST API endpoints and validation
- Gutenberg block rendering
- Settings page persistence
- Meta box functionality
- Integration workflows

**Test Statistics:**
- 995 assertions
- 100% pass rate
- Comprehensive edge case coverage
- Integration test scenarios

### Manual Testing Checklist

Before deploying to production:

- [ ] Fresh install on WordPress 6.0+
- [ ] Migration from v1.40 sample data
- [ ] All shortcodes display correctly
- [ ] Widget shows quotes
- [ ] Gutenberg blocks work in editor
- [ ] Settings save and apply
- [ ] AJAX refresh functions
- [ ] REST API responds correctly
- [ ] Admin meta boxes save data
- [ ] Categories and authors display
- [ ] Author URLs link properly
- [ ] Mobile responsive admin
- [ ] Cross-browser compatibility (Chrome, Firefox, Safari, Edge)
- [ ] Accessibility (keyboard navigation, screen readers)

---

## 🐛 Known Issues

None at this time. Please report any issues on [GitHub](https://github.com/xavivars/xv-random-quotes/issues).

---

## 🔮 Future Plans

Potential features for future releases:

- [ ] Import/Export functionality
- [ ] Quote scheduling (publish on specific dates)
- [ ] Quote voting/ratings
- [ ] Social media sharing buttons
- [ ] Quote of the Day widget
- [ ] Advanced filtering (by date, rating, etc.)
- [ ] Quote collections/albums
- [ ] Multi-language support (WPML/Polylang integration)
- [ ] Quote search shortcode
- [ ] CSV import/export

---

## 🙏 Acknowledgments

**Special Thanks To:**
- All XV Random Quotes users who provided feedback
- WordPress community for best practices guidance
- Contributors who reported bugs and suggested features
- Beta testers who helped ensure quality

**Built With:**
- WordPress 6.8
- PHP 7.4+
- React (for Block Editor)
- WordPress REST API
- WordPress Settings API
- PHPUnit for testing
- Composer for autoloading

---

## 📞 Support & Resources

- **Documentation:** [Plugin Help Page] (in WordPress admin)
- **Support Forum:** [WordPress.org Support](https://wordpress.org/support/plugin/xv-random-quotes/)
- **Bug Reports:** [GitHub Issues](https://github.com/xavivars/xv-random-quotes/issues)
- **Source Code:** [GitHub Repository](https://github.com/xavivars/xv-random-quotes)
- **Author:** Xavi Ivars - https://xavi.ivars.me/

---

## 📝 Upgrade Recommendations

**Who Should Upgrade:**
- ✅ All users on WordPress 6.0+
- ✅ Users wanting Gutenberg block support
- ✅ Users needing REST API access
- ✅ Users wanting modern admin interface
- ✅ Users with PHP 7.4+

**Who Should Wait:**
- ⏸️ Users on WordPress < 6.0 (upgrade WordPress first)
- ⏸️ Users on PHP < 7.4 (upgrade PHP first)
- ⏸️ Users with heavily customized v1.x code (test thoroughly)

**Recommended Upgrade Path:**
1. Update WordPress to 6.0+ if needed
2. Update PHP to 7.4+ if needed
3. Backup database
4. Test on staging environment
5. Upgrade on production
6. Verify migration successful
7. Test all integrations

---

**Thank you for using XV Random Quotes! Enjoy v2.0! 🎉**
