# XV Random Quotes - New Architecture Proposal

## Overview

This document outlines the proposed rewrite of XV Random Quotes to use WordPress native Custom Post Types (CPT) and taxonomies instead of a custom database table. This modernization will:

- Eliminate security vulnerabilities (XSS issues that led to WordPress.org repository removal)
- Reduce code complexity by ~60-80%
- Leverage WordPress core functionality
- Provide better integration with modern WordPress (Gutenberg, REST API)
- Maintain backward compatibility with existing installations

## Current Architecture Problems

1. **Custom database table** (`wp_stray_quotes`) requires manual SQL queries
2. **Security issues** from raw SQL and inadequate escaping
3. **Custom admin pages** duplicate WordPress core functionality
4. **No REST API** integration
5. **Legacy codebase** inherited from Stray Quotes → Stray Quotes Z → XV Random Quotes
6. **Manual permission checks** instead of WordPress capabilities system

## Proposed Data Model

### Custom Post Type: `xv_quote`

**Standard WordPress Fields:**
- `post_content` → Quote text (supports HTML formatting: `<em>`, `<strong>`, `<a>`, etc.)
- `post_title` → Auto-generated from first 10 words of quote (for admin list display, searchable)
- `post_author` → WordPress user who created the quote (maintains multiuser capability)
- `post_status` → Maps to current `visible` field:
  - `publish` = visible (yes)
  - `draft` = not visible (no)
- `post_date` → Automatic creation timestamp

**Benefits:**
- Automatic WordPress search integration (searches both title and content)
- Built-in revision system
- Standard WordPress permissions (edit_posts, publish_posts, etc.)
- REST API endpoints automatically created
- Native HTML support in quotes (emphasis, links, etc.)
- WordPress editor handles content sanitization automatically

### Custom Taxonomies

#### 1. `quote_category` (Hierarchical)
- Replaces current flat category system
- Allows nested categories (e.g., "Philosophy" → "Stoicism")
- Standard WordPress category UI
- Filterable and searchable

#### 2. `quote_author` (Non-hierarchical, like tags)
- For quote attribution (the person being quoted)
- Examples: "Mark Twain", "Albert Einstein", "Maya Angelou"
- Prevents duplicate/misspelled author names
- Enables "Show all quotes by X" functionality
- Supports autocomplete from existing authors

**Term Meta for Authors:**
```php
register_term_meta('quote_author', 'author_url', [
    'type' => 'string',
    'description' => 'Optional URL link for the author',
    'single' => true,
    'sanitize_callback' => 'esc_url_raw',
]);
```

- Optional link to Wikipedia, author website, etc.
- Stored per-author, not per-quote
- Update once, affects all quotes by that author

### Post Meta

#### `_quote_source` (TEXT)
- Stores the source of the quote
- **Allows HTML content** (unlike taxonomies)
- Examples:
  - `"Roughin' it"` (book title)
  - `"Interview on CBS, 2005"`
  - `"Visit <a href='http://example.com'>Website</a>"` (HTML links)
- Sanitized with `wp_kses_post()` for security

**Why post_meta not taxonomy:**
- Sources often contain HTML (links, formatting)
- Sources are unique per quote, not classification
- Not used for filtering/grouping quotes

#### `_quote_legacy_id` (INTEGER)
- Stores old `quoteID` from custom table
- Enables backward compatibility during migration period
- Can be removed in future major version

#### `_quote_display_order` (INTEGER, optional)
- For sequential display if needed
- Maintains compatibility with current "sequence" feature

## Current vs. New Schema Mapping

| Current Table Column | New Implementation |
|---------------------|-------------------|
| `quoteID` (INT) | `ID` (post ID) + `_quote_legacy_id` (meta) |
| `quote` (TEXT) | `post_content` + auto-generated `post_title` |
| `author` (VARCHAR) | `quote_author` taxonomy term (HTML stripped) |
| `source` (VARCHAR) | `_quote_source` post meta |
| `category` (VARCHAR) | `quote_category` taxonomy term |
| `visible` (ENUM) | `post_status` (publish/draft) |
| `user` (VARCHAR) | `post_author` (user ID) |

## Migration Strategy

### Single Release Strategy (v2.0)

Since the current version cannot be published to WordPress.org due to security issues, **v2.0 must be a complete rewrite** that is immediately production-ready. There will be no beta period in the plugin repository.

**Key Principles:**
1. **Full feature parity** with v1.41 on day one
2. **Automatic migration** on plugin activation/update
3. **No user intervention required** for basic migration
4. **Keep old table** for fallback/safety (can be manually removed later)
5. **All functionality working** (shortcodes, widgets, blocks, template tags)

### v2.0 Complete Implementation

Register the custom post type with support for Gutenberg and REST API:

```php
function xv_register_quote_cpt() {
    register_post_type('xv_quote', [
        'labels' => [
            'name' => __('Quotes', 'stray-quotes'),
            'singular_name' => __('Quote', 'stray-quotes'),
            'add_new' => __('Add New Quote', 'stray-quotes'),
            'add_new_item' => __('Add New Quote', 'stray-quotes'),
            'edit_item' => __('Edit Quote', 'stray-quotes'),
            'view_item' => __('View Quote', 'stray-quotes'),
            'search_items' => __('Search Quotes', 'stray-quotes'),
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'capability_type' => 'post',
        'supports' => ['title', 'editor', 'author', 'revisions'],
        'menu_icon' => 'dashicons-format-quote',
        'taxonomies' => ['quote_category', 'quote_author'],
    ]);
    
    register_taxonomy('quote_category', 'xv_quote', [
        'hierarchical' => true,
        'show_in_rest' => true,
        'labels' => [],
    ]);
    
    register_taxonomy('quote_author', 'xv_quote', [
        'hierarchical' => false,
        'show_in_rest' => true,
        'labels' => [],
    ]);
    
    register_post_meta('xv_quote', '_quote_source', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'sanitize_callback' => 'wp_kses_post',
    ]);
}
add_action('init', 'xv_register_quote_cpt');
```

Key features enabled:
- `show_in_rest` enables both Gutenberg editor and REST API endpoints
- `supports` array includes `editor` for post_content field (quote text with HTML support)
- `title` is auto-generated from quote content for admin list display
- Post meta is registered for REST API access
- Both taxonomies are public and REST-enabled

### Manual Migration on Activation

The migration is always manual to prevent race conditions and duplicate imports. When the plugin is activated or updated, an admin notice prompts the user to start the migration via a button. The migration then processes quotes in batches via AJAX.

```php
function xv_quotes_activation_migration() {
    global $wpdb;
    
    if (get_option('xv_quotes_migrated_v2', false)) {
        return;
    }
    
    $table_exists = $wpdb->get_var(
        "SHOW TABLES LIKE '" . XV_RANDOMQUOTES_TABLE . "'"
    ) === XV_RANDOMQUOTES_TABLE;
    
    if (!$table_exists) {
        update_option('xv_quotes_migrated_v2', true);
        return;
    }
    
    $total_quotes = $wpdb->get_var(
        "SELECT COUNT(*) FROM " . XV_RANDOMQUOTES_TABLE
    );
    
    if ($total_quotes == 0) {
        update_option('xv_quotes_migrated_v2', true);
        return;
    }
    
    // Always set pending flag - require manual migration
    update_option('xv_migration_pending', true);
    update_option('xv_migration_total', $total_quotes);
}
register_activation_hook(__FILE__, 'xv_quotes_activation_migration');

function xv_migrate_all_quotes() {
    global $wpdb;
    
    $old_quotes = $wpdb->get_results(
        "SELECT * FROM " . XV_RANDOMQUOTES_TABLE . " ORDER BY quoteID"
    );
    
    foreach ($old_quotes as $old_quote) {
        xv_migrate_single_quote($old_quote);
    }
}
```

The activation hook checks for existing migrations, validates the old table exists, and either migrates immediately or sets up batch processing.

**Migration Features:**
- **Manual button-initiated** for all database sizes (prevents race conditions)
- **AJAX batch processing** (user-initiated from admin notice)
- **Duplicate detection** (checks for existing legacy_id before migrating)
- **User mapping** (nicename → user ID with fallback to current user)
- **HTML preservation** (quote text with HTML formatting migrated to post_content)
- **Progress tracking** with visual progress bar
- **Old table preservation** (kept for safety, can be manually removed via settings)
- **No data loss** (all quotes, categories, authors, sources preserved)

**Migration User Experience:**

**Scenario 1: Existing database (any size)**
1. User updates to v2.0
2. Admin notice appears: "📊 XV Random Quotes needs to migrate X quotes. Click to start migration."
3. User clicks button, AJAX migration begins
4. Progress bar shows real-time progress (with percentage)
5. Migration completes in background (can close tab and return)
6. Success notice when complete

**Scenario 2: Fresh install**
1. User installs v2.0 on new site
2. No old table exists, no migration needed
3. Starts using CPT immediately

**Migration Safety:**
## Authoring Experience

Both classic meta boxes and block editor sidebar panels will be included in v2.0, providing an optimal experience for all users regardless of their editor preference.

### Classic Meta Boxes

Traditional WordPress UI, works in both classic and block editor.
Serves as the primary interface for Classic Editor users and fallback for Block Editor:
**Migration Challenges & Solutions:**

| Challenge | Solution |
|-----------|----------|
| Large quote databases | AJAX batch processing (100 per batch) |
| User mapping | Map `user_nicename` to `user_id`, fallback to current user |
| Timeout issues | AJAX prevents timeouts, resumable on page refresh |
| Data validation | Per-quote error logging, continues on single failures |
| Old table cleanup | Manual option in settings (safety first) |

### Future Cleanup (v2.1+)

Once users have migrated successfully:
- Add tool in Settings → Tools to "Remove Old Quote Table" (with confirmation)
- Add option to export old table to JSON before removal
- Monitor support requests to ensure migration stability before adding removal tool

## Authoring Experience

Both classic meta boxes and block editor sidebar panels will be included in v2.0, providing an optimal experience for all users regardless of their editor preference.

### Classic Meta Boxes

Traditional WordPress UI, works in both classic and block editor.
Serves as the primary interface for Classic Editor users and fallback for Block Editor:

```php
function xv_quote_meta_boxes() {
    remove_meta_box('quote_authordiv', 'xv_quote', 'side');
    
    add_meta_box(
        'xv_quote_details',
        __('Quote Details', 'stray-quotes'),
        'xv_quote_details_callback',
        'xv_quote',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'xv_quote_meta_boxes');

function xv_quote_details_callback($post) {
    wp_nonce_field('xv_quote_details', 'xv_quote_details_nonce');
    
    $terms = get_the_terms($post->ID, 'quote_author');
    $author = $terms ? $terms[0]->name : '';
    $source = get_post_meta($post->ID, '_quote_source', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="quote_author"><?php _e('Author', 'stray-quotes'); ?></label></th>
            <td>
                <input type="text" id="quote_author" name="quote_author" 
                       value="<?php echo esc_attr($author); ?>" 
                       class="regular-text" 
                       placeholder="<?php esc_attr_e('e.g., Mark Twain', 'stray-quotes'); ?>">
                <p class="description">
                    <?php _e('The person being quoted', 'stray-quotes'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th><label for="quote_source"><?php _e('Source', 'stray-quotes'); ?></label></th>
            <td>
                <input type="text" id="quote_source" name="quote_source" 
                       value="<?php echo esc_attr($source); ?>" 
                       class="regular-text"
                       placeholder="<?php esc_attr_e('e.g., Roughin\' it', 'stray-quotes'); ?>">
                <p class="description">
                    <?php _e('Book, article, or URL. HTML allowed: &lt;a href="..."&gt;Title&lt;/a&gt;', 'stray-quotes'); ?>
                </p>
            </td>
        </tr>
    </table>
    <?php
}

function xv_save_quote_details($post_id) {
    if (!isset($_POST['xv_quote_details_nonce']) || 
        !wp_verify_nonce($_POST['xv_quote_details_nonce'], 'xv_quote_details')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['quote_author']) && !empty($_POST['quote_author'])) {
        wp_set_object_terms($post_id, sanitize_text_field($_POST['quote_author']), 'quote_author');
    } else {
        wp_delete_object_term_relationships($post_id, 'quote_author');
    }
    
    if (isset($_POST['quote_source'])) {
        update_post_meta($post_id, '_quote_source', wp_kses_post($_POST['quote_source']));
    }
}
add_action('save_post_xv_quote', 'xv_save_quote_details');
```

The meta box provides simple text fields that automatically create and assign taxonomy terms for authors while storing the source as post meta.

**User Experience:**
- Box appears below the editor titled "Quote Details"
- Author: Simple text field (creates/assigns taxonomy term automatically)
- Source: Text field with note about HTML support
- Categories: Standard WordPress category selector in sidebar
- Works identically in Classic Editor and Block Editor
- Familiar to anyone who has used WordPress

### Block Editor Sidebar Panel

Modern Gutenberg integration using `@wordpress/scripts`.
Provides a cleaner, more integrated experience for Block Editor users:

```javascript
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { TextControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';

function QuoteDetailsPanel() {
    const postType = useSelect((select) => 
        select('core/editor').getCurrentPostType()
    );
    
    const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
    const [author] = useEntityProp('postType', postType, 'quote_author');
    
    if (postType !== 'xv_quote') return null;
    
    return (
        <PluginDocumentSettingPanel
            name="quote-details"
            title="Quote Details"
        >
            <TextControl
                label="Author"
                value={author[0]?.name || ''}
                onChange={(value) => {}}
                help="The person being quoted"
            />
            <TextControl
                label="Source"
                value={meta._quote_source || ''}
                onChange={(value) => setMeta({ ...meta, _quote_source: value })}
                help="Book, article, or URL. HTML allowed."
            />
        </PluginDocumentSettingPanel>
    );
}

registerPlugin('xv-quote-details', {
    render: QuoteDetailsPanel,
});
```

This creates a sidebar panel that appears alongside featured images and categories in the Block Editor.

**Implementation:**
Both interfaces coexist harmoniously:
- Block Editor automatically uses sidebar panel (cleaner UI)
- Meta boxes remain available as fallback
- Classic Editor users get meta boxes
- No user confusion - each editor gets its optimal interface

**Build Process:**
Uses `@wordpress/scripts` for zero-config build:
```bash
npm install @wordpress/scripts --save-dev
npx wp-scripts build
```

Enqueue the built script for the block editor:
```php
function xv_quote_block_editor_assets() {
    $asset_file = include plugin_dir_path(__FILE__) . 'build/index.asset.php';
    
    wp_enqueue_script(
        'xv-quote-editor',
        plugins_url('build/index.js', __FILE__),
        $asset_file['dependencies'],
        $asset_file['version']
    );
}
add_action('enqueue_block_editor_assets', 'xv_quote_block_editor_assets');
```

Enqueue the built script for the block editor:
```php
function xv_quote_block_editor_assets() {
    $asset_file = include plugin_dir_path(__FILE__) . 'build/index.asset.php';
    
    wp_enqueue_script(
        'xv-quote-editor',
        plugins_url('build/index.js', __FILE__),
### Shortcodes

Existing shortcodes will be refactored to use `WP_Query` instead of raw SQL:

**Current implementation (custom SQL):**
```php
$sql = "SELECT * FROM " . XV_RANDOMQUOTES_TABLE . " WHERE visible='yes' ORDER BY RAND() LIMIT 1";
$quote = $wpdb->get_row($sql);
```

**New implementation (WP_Query):**
```php
$query = new WP_Query([
    'post_type'      => 'xv_quote',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'orderby'        => 'rand',
    'tax_query'      => [
        [
            'taxonomy' => 'quote_category',
            'field'    => 'slug',
            'terms'    => $category,
        ],
    ],
]);

if ($query->have_posts()) {
    $query->the_post();
    $quote = get_the_title();
    $authors = get_the_terms(get_the_ID(), 'quote_author');
    $author = $authors ? $authors[0]->name : '';
    $source = get_post_meta(get_the_ID(), '_quote_source', true);
    wp_reset_postdata();
}
```

All shortcode attributes and functionality will be preserved:
- `[stray-random]` - Random quote with optional category filter
- `[stray-id id="123"]` - Specific quote by ID
- `[stray-all]` - Display all quotes with filtering optionsray-random]` → Random quote
- `[stray-id id="123"]` → Specific quote by ID
- `[stray-all]` → All quotes

**All existing attributes maintained:**
- `category="slug"`
- `show_author="yes"`
- `show_source="yes"`
- `ajax="yes"`
- etc.

### Template Tags

### Template Tags

Template tags maintain the same function signatures but use `WP_Query` internally:

```php
function stray_quote_random($category = '') {
    $args = [
        'post_type' => 'xv_quote',
        'posts_per_page' => 1,
        'orderby' => 'rand',
    ];
    
    if (!empty($category)) {
        $args['tax_query'] = [[
            'taxonomy' => 'quote_category',
            'field' => 'slug',
            'terms' => $category,
        ]];
    }
    
    $query = new WP_Query($args);
}
```

Theme developers can continue using the same function calls without any changes to their templates.

### Widgets

The existing widget will be updated internally to use `WP_Query` instead of direct SQL queries. All widget options and settings will remain unchanged:
- Category selection dropdown (populated from `quote_category` taxonomy)
- Number of quotes to display
- AJAX refresh button option
- Auto-rotation with configurable interval
- Display options (show/hide author, source, etc.)

The widget will work in both classic widget areas and the block-based widget editor introduced in WordPress 5.8.tes/random-quote",
  "title": "Random Quote",
  "category": "widgets",
  "icon": "format-quote",
  "attributes": {
    "category": {
      "type": "string",
      "default": ""
    },
    "showAuthor": {
      "type": "boolean",
      "default": true
    },
    "showSource": {
      "type": "boolean",
      "default": true
    },
    "enableAjax": {
      "type": "boolean",
      "default": false
    }
#### Core Architecture
- [ ] Register CPT and taxonomies
- [ ] Implement post meta for source field
- [ ] Add term meta for author URLs
- [ ] Create meta boxes for authoring
- [ ] Create block editor sidebar panel
- [ ] Set up `@wordpress/scripts` build process

#### Migration System
- [ ] Manual migration trigger on activation (any database size)
- [ ] AJAX batch migration UI with progress bar
- [ ] Admin notice system with progress bar
- [ ] Duplicate detection
- [ ] Error logging and handling
- [ ] Migration status tracking

#### Backward Compatibility
- [ ] Refactor all shortcodes to use WP_Query
  - [ ] `[stray-random]` - Random quote
  - [ ] `[stray-id]` - Specific quote by ID
  - [ ] `[stray-all]` - All quotes
- [ ] Update all template tags
- [ ] Migrate widget to use CPT (keep same UI)
- [ ] AJAX functionality for quote refresh
#### Gutenberg Integration
- [ ] Random Quote block (server-rendered)
- [ ] Block settings (category, display options)
- [ ] Block preview in editor
- [ ] Quote authoring sidebar panel (integrated with block editor)ver-rendered)
- [ ] Block settings (category, display options)
- [ ] Block preview in editor

#### Testing & Documentation
- [ ] Unit tests for CPT, taxonomies, migration
- [ ] Integration tests for shortcodes, widgets
- [ ] Migration testing with various database sizes
- [ ] Security audit (XSS prevention)
- [ ] Update all documentation
- [ ] WordPress.org readme update
- [ ] Screenshots for WordPress.org

#### Settings & Tools
- [ ] Display settings page (HTML wrappers, etc.)
- [ ] Migration status page
- [ ] Help page updates
- [ ] Admin notices system

**Estimated development time: 6-8 weeks**
### v2.1 - Polish & Enhancements (Post-launch)
After v2.0 is stable in production:
- [ ] Additional Gutenberg blocks (Quote List, Featured Quote, Quote Carousel)
- [ ] Block patterns library (pre-designed quote layouts)
- [ ] Tool to remove old quote table (with safety confirmation)
- [ ] Performance optimizations based on user feedback
- [ ] Additional REST API endpoints if needed
- [ ] Enhanced author taxonomy UI (with avatar support)

**Estimated: 2-3 weeks**zations based on user feedback
- [ ] Additional REST API endpoints if needed

**Estimated: 2-3 weeks**

### v3.0 - Future Major Update
Long-term enhancements:
- [ ] Quote collections/books (new taxonomy)
- [ ] Image support for quotes
- [ ] Import/export quotes (JSON, CSV)
- [ ] Advanced filtering UI
## Rollout Strategy

Since the current version cannot be published to WordPress.org, the rollout must be direct:

### Pre-Release (GitHub Only)
1. **Development Branch** - Create `v2-rewrite` branch
2. **Alpha Testing** - Internal testing with various database sizes
3. **Beta Testing** - Limited GitHub releases for brave users
4. **Release Candidate** - Final testing, bug fixes, documentation review

### Public Release
1. **v2.0 Release** - Direct to WordPress.org (no v1.x is available there)
2. **Automatic Migration** - Runs on activation for existing users
3. **Support Period** - Active support for migration issues
4. **Monitoring** - Track support forum for common issues

### Post-Release
- Monitor migration success rates
- Gather user feedback
- Plan v2.1 enhancements based on feedback
- Consider tool to remove old table once stable (v2.1+)

## Documentation Updates Required
- Live preview in editor
- Category selector (populated from taxonomy)
- Display toggles (author, source)
- AJAX refresh option
- Server-side rendering (reuses shortcode logic)

## Code Reduction Estimate

| File | Current Lines | New Lines | Reduction |
|------|--------------|-----------|-----------|
| `stray_manage.php` | ~690 | ~100 | 85% |
| `stray_new.php` | ~200 | ~0 | 100% |
| `stray_functions.php` | ~800 | ~200 | 75% |
| `stray_overview.php` | ~118 | ~0 | 100% |
| Custom SQL queries | ~50 | ~0 | 100% |

**Total estimated reduction: 60-80% of codebase**

## Security Improvements

### Automatic WordPress Security

1. **Escaping:** WordPress handles output escaping for post content
2. **Sanitization:** Built-in sanitization for post types, taxonomies, meta
3. **Nonces:** Standard WordPress nonce verification
4. **Capabilities:** Standard post capabilities (edit_posts, publish_posts)
5. **SQL Injection:** Eliminated (no raw SQL queries)
6. **XSS Prevention:** `wp_kses_post()` on source field, taxonomy terms are plain text

### Removed Vulnerabilities

- No more `esc_sql()` failures on user input
- No more raw `$wpdb->query()` with concatenated strings
- No more manual capability checks that can be bypassed
## REST API (Automatic)

WordPress automatically provides REST API endpoints for the custom post type and taxonomies:

```
GET  /wp-json/wp/v2/xv_quote          - List quotes
GET  /wp-json/wp/v2/xv_quote/{id}     - Get single quote
POST /wp-json/wp/v2/xv_quote          - Create quote (requires auth)
PUT  /wp-json/wp/v2/xv_quote/{id}     - Update quote (requires auth)
DELETE /wp-json/wp/v2/xv_quote/{id}   - Delete quote (requires auth)

GET  /wp-json/wp/v2/quote_category    - List categories
GET  /wp-json/wp/v2/quote_author      - List authors
```

These endpoints are enabled by the `show_in_rest` parameter in the CPT and taxonomy registration.

**Use Cases:**
- Mobile app integration
- Headless WordPress setups
- JavaScript-based quote display widgets
- Third-party integrations
- Custom admin interfaces

## Features You Get for Free

Moving to CPT provides these WordPress features automatically:

1. **Bulk Edit UI** - Edit multiple quotes at once
2. **Quick Edit** - Inline editing in admin list
3. **Search** - WordPress native search includes quotes
4. **Revisions** - Track changes to quotes over time
5. **Media Library** - Attach images to quotes if desired
6. **Custom Fields UI** - Advanced users can add extra fields
7. **Import/Export** - WordPress import/export includes quotes
8. **Multisite Support** - Works across WordPress multisite
9. **Caching** - WordPress object caching automatically applies
10. **Trash/Restore** - Soft delete with recovery option

## Implementation Timeline

1. **Unit Tests** (PHPUnit)
   - CPT registration
   - Taxonomy registration
   - Meta field sanitization
   - Query functions
   - Shortcode output

2. **Integration Tests**
   - Migration accuracy
   - Backward compatibility
   - Widget functionality
   - AJAX endpoints

3. **User Acceptance Testing**
   - Beta program with current users
   - Migration on real databases (100s-1000s of quotes)
   - Cross-browser testing
   - Accessibility audit (WCAG 2.1)

4. **Performance Testing**
   - Large database queries (10k+ quotes)
   - AJAX response times
   - Block editor performance

## Rollout Strategy

1. **Beta Release** - GitHub only, limited audience
2. **Release Candidate** - WordPress.org beta channel
3. **Stable Release** - Full WordPress.org release
4. **Gradual Migration** - Users choose when to migrate
5. **Support Period** - 6 months dual support (old + new)
6. **Deprecation** - Remove old system in major version bump

## Documentation Updates Required

- [ ] Installation guide (new CPT structure)
- [ ] Migration guide (step-by-step with screenshots)
- [ ] Shortcode reference (same syntax, note improvements)
- [ ] Template tag reference
- [ ] Block editor usage guide
- [ ] Developer API documentation (filters, actions)
- [ ] Troubleshooting guide
- [ ] FAQ updates

## Backward Compatibility Guarantees

**Will NOT break:**
- Existing shortcodes (same syntax)
- Template tag functions (same names)
- Widget settings (automatically migrate)
- Display options (same settings format)
- Category structures (migrate automatically)

**May require manual adjustment:**
- Custom SQL queries in child themes (provide helper functions)
- Direct database access (discouraged, but provide compatibility layer)
- Hard-coded table names (provide constant for new queries)

## Requirements & Compatibility

### Minimum Versions
- **WordPress:** 6.0+ (supports both classic and block editor)
- **PHP:** 7.4+ (enables typed properties, arrow functions, null coalescing)
- **MySQL:** 5.6+ (or MariaDB 10.1+)

### Editor Support
- ✅ **Block Editor (Gutenberg)** - Full support with custom blocks
- ✅ **Classic Editor** - Full support with meta boxes
- The plugin will work seamlessly with both editors, allowing users to choose their preference

### Widget Support
- ✅ **Classic Widgets** - Maintain existing widget for backward compatibility and users who prefer it
- ✅ **Block Widgets** - Widget will work in both classic widget areas and block-based widget editor (WordPress 5.8+)
- The widget will be updated to use WP_Query internally but keep the same user-facing options and interface

## PHP 7.4+ Features We Can Now Use

With the PHP 7.4 minimum requirement, the codebase can leverage:

1. **Typed Properties**
```php
class QuoteRenderer {
    private string $quote;
    private ?string $author = null;
    private array $options = [];
}
```

2. **Arrow Functions**
```php
$categories = array_map(fn($cat) => $cat->slug, $categories);
```

3. **Null Coalescing Assignment**
```php
$options['show_author'] ??= true;
```

4. **Spread Operator in Arrays**
```php
$args = [...$default_args, ...$user_args];
```

These features will make the code more readable, type-safe, and maintainable.

## Conclusion

This architecture modernizes XV Random Quotes to use WordPress best practices while maintaining full backward compatibility. The migration path is clear, the security improvements are substantial, and the reduction in code complexity will make the plugin more maintainable long-term.

**Key Benefits:**
- ✅ Eliminates security vulnerabilities
- ✅ Reduces code by 60-80%
- ✅ Leverages WordPress core functionality
- ✅ Provides modern block editor integration (while supporting classic editor)
- ✅ Maintains full backward compatibility (including widgets)
- ✅ Enables REST API access
- ✅ Improves maintainability with PHP 7.4+ features
- ✅ Follows WordPress coding standards
- ✅ Requires WordPress 6.0+ (released May 2022, widely adopted)

**Next Steps:**
1. Review and approve this architecture
2. Create development branch
3. Begin Phase 1 implementation
4. Set up testing environment with WordPress 6.0 and PHP 7.4
5. Recruit beta testers
