XV Random Quotes
======================

[![Build Status](https://api.travis-ci.org/xavivars/xv-random-quotes.png?branch=master)](https://travis-ci.org/xavivars/xv-random-quotes)

Display and rotate quotes anywhere on your WordPress site. Fully integrated with WordPress Custom Post Types, Gutenberg blocks, and REST API.

## Features

### 🎯 Modern WordPress Integration

* **Custom Post Type** - Quotes are native WordPress posts with full revision history
* **Gutenberg Blocks** - Three dedicated blocks: Random Quote, Specific Quote, and List Quotes
* **REST API** - Access quotes at `/wp-json/xv-random-quotes/v1/quote/random`
* **Taxonomy Support** - Organize with categories and authors (with URL support)
* **Block & Classic Editor** - Meta boxes for quote content and source

### ✨ Display Methods

* **Gutenberg Blocks** - Random, Specific, and List quote blocks
* **Widgets** - AJAX-powered sidebar widget with auto-refresh
* **Shortcodes** - `[stray-random]`, `[stray-id]`, `[stray-all]`
* **Template Tags** - `stray_random_quote()`, `stray_a_quote()`
* **REST API** - JSON endpoint for custom integrations

### 🔄 Migration from v1.x

Automatic migration system:
* **Small databases (≤500 quotes)**: Instant migration on activation
* **Large databases (>500 quotes)**: Batch processing with progress bar
* **100% Safe**: Original data preserved, migration can be resumed
* **Zero Breaking Changes**: All legacy shortcodes and functions still work

## Quick Start

### Installation

1. Install via WordPress Plugins > Add New or upload manually
2. Activate the plugin
3. If upgrading from v1.x, migration starts automatically
4. Go to Quotes > Add New to create your first quote

### Using Gutenberg Blocks

1. Edit any post/page in Block Editor
2. Click + and search for "quote"
3. Choose: Random Quote, Specific Quote, or List Quotes
4. Configure settings in the block sidebar

### Using Shortcodes

```
[stray-random]
[stray-random categories="inspiration,wisdom" multi="3"]
[stray-id id="123"]
[stray-all categories="all" rows="10"]
```

### Using Template Tags

```php
<?php
// Display a random quote
stray_random_quote();

// Display a specific quote
stray_a_quote(123);

// Random quote from specific categories
stray_random_quote('inspiration,wisdom');
?>
```

### Using REST API

```javascript
// Fetch a random quote
fetch('/wp-json/xv-random-quotes/v1/quote/random')
  .then(response => response.json())
  .then(data => console.log(data.html));

// With parameters
fetch('/wp-json/xv-random-quotes/v1/quote/random?categories=wisdom&multi=3')
  .then(response => response.json())
  .then(data => console.log(data));
```

## Documentation

* **Complete Guide**: See [RELEASE_NOTES.md](RELEASE_NOTES.md)
* **Migration Details**: See [NEW_ARCHITECTURE.md](NEW_ARCHITECTURE.md)
* **Changelog**: See [changelog.txt](changelog.txt)
* **Help**: Quotes > Help in WordPress admin

## Support

**Bug Reports**: [GitHub Issues](https://github.com/xavivars/xv-random-quotes/issues)  
**WordPress Forums**: [Support Forum](https://wordpress.org/support/plugin/xv-random-quotes)  
**Documentation**: Quotes > Help in WordPress admin

## Development

This plugin uses Docker with a single `docker-compose.yml` providing two isolated environments:

### Manual Development & Testing (web + db)

```bash
# Start WordPress site
docker-compose up -d

# Access at http://localhost:8080
# Database is persistent - data survives restarts

# Stop (keeps data)
docker-compose down

# Fresh start (wipes database)
docker-compose down -v
docker-compose up -d
```

### Automated Testing (cli + testdb)

```bash
# Run tests
docker-compose run --rm cli vendor/bin/phpunit

# Database resets automatically - clean slate for each test run
```

**Key Point:** Both environments can run simultaneously without interfering - they use separate databases (`db` vs `testdb`).

See [DOCKER_USAGE.md](DOCKER_USAGE.md) for detailed usage.

## Architecture

### Code Organization

```
src/
├── Admin/           # Settings, meta boxes, admin UI
├── Blocks/          # Gutenberg blocks (Random, Specific, List)
├── Migration/       # v1.x to v2.0 migration system
├── PostTypes/       # Custom Post Type registration
├── Queries/         # WP_Query helpers for quote retrieval
├── Rendering/       # HTML output and rendering
├── RestAPI/         # REST API endpoints
├── Taxonomies/      # Category and Author taxonomies
└── Widgets/         # Sidebar widget

tests/
├── admin/           # Admin interface tests
├── blocks/          # Gutenberg block tests
├── migration/       # Migration system tests
├── queries/         # Query system tests
├── rest-api/        # REST API tests
├── shortcodes/      # Shortcode tests
├── widgets/         # Widget tests
└── integration/     # End-to-end tests
```

### Key Concepts

* **Custom Post Type**: Quotes stored as `xv_quote` posts
* **Taxonomies**: `quote_category` (hierarchical) and `quote_author` (non-hierarchical)
* **Post Meta**: `_quote_source` (formatted source), `_quote_legacy_id` (migration)
* **Backward Compatibility**: All v1.x functions wrapped, zero breaking changes
* **Modern APIs**: Block Editor, REST API, WordPress Settings API

### Testing

```bash
# Run all tests (385 tests, 995 assertions)
docker-compose run --rm cli vendor/bin/phpunit

# Run specific test suite
docker-compose run --rm cli vendor/bin/phpunit tests/blocks/
docker-compose run --rm cli vendor/bin/phpunit tests/migration/
```

## Contributions
## Contributions

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for new functionality
4. Ensure all tests pass (`docker-compose run --rm cli vendor/bin/phpunit`)
5. Commit changes (`git commit -m 'Add amazing feature'`)
6. Push to branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Development Guidelines

* Follow WordPress Coding Standards
* Write PHPUnit tests for all new features
* Maintain backward compatibility
* Update documentation
* Use meaningful commit messages

## Credits

* **v2.0 Development**: Xavi Ivars ([@xavivars](https://github.com/xavivars))
* **Original Stray Quotes**: [Ico](http://unalignedcode.wordpress.com/)
* **Stray Quotes Z**: [Sergey Sirotkin](http://www.zeyalabs.ch/)
* **Multi-widget**: [Millian's tutorial](http://wp.gdragon.info/2008/07/06/create-multi-instances-widget/)
* **AJAX functionality**: [AgentSmith](http://www.matrixagents.org)

## License

GPL-2.0-or-later. See LICENSE file for details.