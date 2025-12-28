# XV Random Quotes v2.0 - Development TODO List

This document tracks the complete roadmap for refactoring XV Random Quotes from v1.40 to v2.0, migrating from a custom database table to WordPress Custom Post Types.

**Progress:** 24/74 tasks completed (32.4%)

## Phase 1: Foundation & Setup

- [x] **Task 1:** Setup Development Environment & Testing Framework
  - Configure PHP 7.4+ environment, update PHPUnit to latest compatible version, set up WordPress test environment with WP 6.0+, configure test database, and establish TDD workflow. Update composer.json with required dependencies.

## Phase 2: Core Architecture (CPT, Taxonomies, Meta)

- [x] **Task 2:** Write Tests for CPT Registration
  - Create test cases for registering 'xv_quote' custom post type: verify post type exists, check labels, verify supports array (title, author, revisions), confirm capability_type, validate show_in_rest is true, test menu_icon, ensure public=false but show_ui=true.

- [x] **Task 3:** Implement Custom Post Type Registration
  - Create new file src/PostTypes/QuotePostType.php (or similar structure). Implement register_post_type() for 'xv_quote' with all specifications from NEW_ARCHITECTURE.md. Make tests pass.

- [x] **Task 4:** Write Tests for Taxonomy Registration
  - Create test cases for both taxonomies: quote_category (hierarchical) and quote_author (non-hierarchical). Verify taxonomy exists, check hierarchical property, validate show_in_rest, confirm association with xv_quote post type, test labels.

- [x] **Task 5:** Implement Taxonomy Registration
  - Create src/Taxonomies/QuoteTaxonomies.php. Implement register_taxonomy() for both quote_category and quote_author with all specifications. Add term meta registration for author URLs. Make tests pass.
  - ✅ **Status:** COMPLETED
    - Term meta registration already implemented in QuoteTaxonomies.php (line 124):
      * register_term_meta('quote_author', 'author_url') with type 'string', sanitize_callback 'esc_url_raw'
      * Optional URL link for author (Wikipedia, author website, etc.)
      * Stored per-author, not per-quote - update once, affects all quotes by that author
    - Migration updated to extract author URLs from legacy data:
      * Legacy format: `<a href="URL">Author Name</a>` or just `Author Name`
      * Modified assign_term() in QuoteMigrator.php to extract URLs from author field HTML
      * Regex pattern extracts URL from <a href="..."> tag if present
      * wp_strip_all_tags() creates clean author name for term
      * update_term_meta() saves URL to author_url term meta (only if not already set)
      * Handles authors without URLs (plain text)
      * Does NOT overwrite existing term meta (preserves manual edits)
    - Created 9 comprehensive tests in tests/migration/test-author-url-migration.php:
      * test_author_url_extracted_from_anchor_tag() - basic URL extraction
      * test_author_url_extraction_with_complex_attributes() - handles target, class attributes
      * test_author_without_url_plain_text() - authors without links work correctly
      * test_author_with_html_formatting_no_link() - strips HTML from author names
      * test_existing_author_url_not_overwritten() - preserves manual edits
      * test_author_url_sanitization() - esc_url_raw prevents XSS
      * test_multiple_quotes_same_author_with_url() - URL saved only once per author
      * test_category_terms_not_processed_for_urls() - only author taxonomy processed
      * test_author_url_with_single_quotes() - handles both quote styles in href
    - Updated shortcode rendering to display author URLs:
      * Modified stray_output_one_cpt() in src/legacy/stray_helpers.php
      * Priority 1: Use author_url from term meta (migrated data or manually set)
      * Priority 2: Fall back to settings-based link pattern (stray_quotes_linkto)
      * Security: esc_url() and esc_html() prevent XSS attacks
      * Works in all shortcodes: [stray-id], [stray-random], [stray-all]
    - Created 7 rendering tests in tests/shortcodes/test-author-url-rendering.php:
      * test_author_url_rendered_in_stray_id_shortcode() - link appears in output
      * test_author_url_rendered_in_stray_random_shortcode() - works in random quotes
      * test_author_url_rendered_in_stray_all_shortcode() - works in listings
      * test_author_without_url_renders_plain() - no link when URL not set
      * test_author_url_priority_over_settings() - term meta takes priority
      * test_author_url_xss_protection() - javascript: URLs sanitized
      * test_author_name_escaped() - special characters in names escaped
    - All 232 tests passing (9 migration + 7 rendering = 16 new tests)


- [x] **Task 6:** Write Tests for Post Meta Registration
  - Create tests for _quote_source, _quote_legacy_id, and _quote_display_order post meta fields. Verify meta is registered, check sanitization callbacks (wp_kses_post for source), validate show_in_rest, test single property.

- [x] **Task 7:** Implement Post Meta Registration
  - Create src/PostMeta/QuoteMetaFields.php. Implement register_post_meta() for all three meta fields with proper sanitization. Make tests pass.

## Phase 3: Data Migration

- [x] **Task 8:** Write Tests for Migration - Single Quote
  - Create test cases for migrating a single quote from old table to CPT: verify post is created, check post_title matches quote text, validate post_status (yes→publish, no→draft), confirm taxonomy terms assigned, test meta fields populated, verify legacy_id stored.

- [x] **Task 9:** Implement Single Quote Migration Function
  - Create src/Migration/QuoteMigrator.php with xv_migrate_single_quote() function. Handle all field mappings, user nicename to ID conversion, taxonomy term creation/assignment, meta field population. Make tests pass.

- [x] **Task 10:** Write Tests for Migration - Duplicate Detection
  - Create tests to verify duplicate prevention: test that quotes with same legacy_id aren't migrated twice, verify idempotent migration (running twice produces same result), test partial migration resume capability.

- [x] **Task 11:** Implement Duplicate Detection in Migration
  - Add duplicate checking logic to QuoteMigrator. Query for existing posts with same _quote_legacy_id before creating new post. All 8 tests passing, original 19 migration tests still passing.

- [x] **Task 12:** Write Tests for Full Migration (Small Database)
  - Create tests for automatic migration of ≤500 quotes: verify all quotes migrated, check migration status flag set, test empty database handling, verify new installation detection (no old table).

- [x] **Task 13:** Implement Automatic Migration on Activation
  - Create activation hook function xv_quotes_activation_migration(). Implement logic for small database auto-migration, migration status checking, table existence validation. Make tests pass.

- [x] **Task 14:** Write Tests for Batch Migration (Large Database)
  - Create tests for AJAX batch migration: verify batch size handling (100 quotes/batch), test progress tracking, validate resumability after interruption, check completion detection.

- [x] **Task 15:** Implement AJAX Batch Migration System
  - Create AJAX endpoint for batch migration, implement progress tracking with transients, add batch processing logic (100 quotes at a time), create resumable migration state. Make tests pass.

- [x] **Task 15.2:** Write Tests for Activation Hook
  - Create tests for xv_quotes_activation_migration() hook: verify migration already completed check (xv_quotes_migrated_v2), test table existence validation, verify small database (≤500 quotes) calls migrate_all_quotes() and sets flag, test large database (>500 quotes) sets xv_migration_pending flag and stores total count, verify empty database handling.

- [x] **Task 15.3:** Implement Activation Hook
  - Create activation hook function xv_quotes_activation_migration(). Implement logic to check migration status flag, validate table existence, count total quotes, call migrate_all_quotes() for ≤500 quotes, or set pending flag for >500 quotes. Register hook with register_activation_hook(). Make tests pass.

- [x] **Task 16:** Write Tests for Migration Admin UI
  - Create tests for admin notices: verify notice appears when migration pending, test progress bar rendering, validate success/error message display, check migration button functionality. 12 tests created covering all scenarios.

- [x] **Task 17:** Implement Migration Admin Notice & UI
  - Create admin notice system for migration status, implement progress bar UI, add AJAX handlers for UI updates, create migration trigger button. All 12 tests passing.

## Phase 4: Admin Interface

- [x] **Task 18:** Write Tests for Classic Meta Box
  - Create tests for quote source meta box (Classic Editor only): verify box is registered for xv_quote post type with ID xv_quote_source, context side, priority default; test conditional registration (is_classic_editor_active method); validate nonce verification; check autosave protection; verify user capability check; test source meta save with wp_kses_post sanitization; validate empty source handling.

- [x] **Task 19:** Implement Classic Meta Box
  - Create src/Admin/MetaBoxes.php with quote source meta box for Classic Editor. Implement is_classic_editor_active() method checking use_block_editor_for_post_type('xv_quote'). Add add_meta_boxes hook with conditional registration. Implement save_post_xv_quote hook with nonce verification, autosave check, capability check, and wp_kses_post sanitization for _quote_source. Initialize in Plugin class.


- [x] **Task 20:** Register Post Meta for Block Editor
  - Add register_post_meta() call for _quote_source with show_in_rest => true (enables REST API access), type => 'string', single => true, sanitize_callback => 'wp_kses_post'. Register in QuoteMetaFields class.
  - **Note:** show_in_rest only exposes meta via REST API - it does NOT create automatic UI
  - ✅ **Status:** COMPLETED
    - _quote_source registered with show_in_rest => true in Task 7 (src/PostMeta/QuoteMetaFields.php)
    - Added 'custom-fields' support to xv_quote post type (REQUIRED for REST API meta exposure)
    - Created 12 REST API integration tests - all passing (tests/integration/test-post-meta-rest.php)
    - Block Editor UI requires either: Classic meta box (Task 19), custom meta block, or PluginDocumentSettingPanel (Tasks 21-22)

- [x] **Task 21:** Setup Block Editor Build Environment
  - Install @wordpress/scripts, configure package.json with build scripts, create src/blocks directory structure, setup webpack config (or use default from @wordpress/scripts), test build process works.
  - ✅ **Status:** COMPLETED
    - Created package.json with @wordpress/scripts v28.0.0
    - Configured build scripts: build, start (dev), format, lint:js, lint:pkg-json
    - Created src/blocks/quote-details/ directory with placeholder index.js
    - Created custom webpack.config.js extending @wordpress/scripts defaults
    - Installed dependencies including @babel/runtime
    - Verified build process works - outputs to src/generated/quote-details.js and src/generated/quote-details.asset.php
    - Created BUILD.md documentation for developers
    - .gitignore already configured for node_modules/ and build/

- [x] **Task 22:** Write Tests for Block Editor Asset Enqueuing
  - Create tests/admin/test-block-editor-assets.php to verify:
    - Asset enqueuing class exists (BlockEditorAssets)
    - Scripts enqueued only on Block Editor screens for xv_quote post type
    - Correct script handle, src path (src/generated/quote-details.js), dependencies loaded from .asset.php
    - Script localized with REST API data (nonce, post ID, endpoints)
    - Only enqueued when is_block_editor_active() returns true
    - Not enqueued in Classic Editor mode
  - **Note:** JavaScript/React code itself is tested via browser/E2E tests, not PHPUnit. This task tests the PHP integration.
  - ✅ **Status:** COMPLETED - 15 tests created, 12 failing as expected (TDD red phase), 3 passing (asset file validation)

- [x] **Task 22.1:** Implement Block Editor Asset Enqueuing
  - Create src/Admin/BlockEditorAssets.php class
  - Hook into enqueue_block_editor_assets action
  - Check post type is xv_quote before enqueuing
  - Load dependencies from src/generated/quote-details.asset.php
  - Enqueue src/generated/quote-details.js with proper dependencies
  - Localize script with xvQuoteData (nonce, postId, restUrl, etc.)
  - Initialize in Plugin class
  - Make all tests pass
  - ✅ **Status:** COMPLETED - All 15 tests passing, BlockEditorAssets class fully functional

- [x] **Task 22.2:** Implement Block Editor Sidebar Panel (React/JavaScript)
  - Create src/blocks/quote-details/index.js with PluginDocumentSettingPanel
  - Import @wordpress/plugins, @wordpress/edit-post, @wordpress/components, @wordpress/core-data
  - Implement TextControl for quote source using useEntityProp hook
  - Register plugin with registerPlugin
  - Build with npm run build
  - Test manually in Block Editor (no automated tests - requires browser)
  - **Note:** React/JSX code validation done via ESLint (npm run lint:js), not PHPUnit
  - ✅ **Status:** COMPLETED - All code written, ESLint passing, build successful (790 bytes minified)

## Phase 5: Query System Refactor

- [x] **Task 23:** Write Tests for WP_Query Helper Functions
  - Create tests for new query helper functions: test get_random_quote(), get_quote_by_id(), get_quotes_by_category(), verify proper WP_Query args, validate post type filtering, check taxonomy queries.
  - ✅ **Status:** COMPLETED - 17 tests created, all failing as expected (TDD red phase - QuoteQueries class not found)

- [x] **Task 24:** Implement WP_Query Helper Functions
  - Create src/Queries/QuoteQueries.php with helper functions wrapping WP_Query. Replace raw SQL patterns with WP_Query calls. Make tests pass.
  - ✅ **Status:** COMPLETED - QuoteQueries class implemented with 5 methods (get_random_quote, get_quote_by_id, get_quotes_by_author, get_quotes_by_category, get_all_quotes). All 17 tests passing, 135 total tests, 341 assertions.

## Phase 6: Shortcodes Refactor

- [x] **Task 25:** Write Tests for [stray-random] Shortcode
  - Create tests for random quote shortcode: verify shortcode registered, test category filtering, validate AJAX parameter handling, check output HTML structure, test all existing attributes work (show_author, show_source, etc).
  - ✅ **Status:** COMPLETED - 17 tests created (2 passing: shortcode registration, 15 failing: functional tests)
    - Created tests/shortcodes/test-stray-random-shortcode.php
    - Test Coverage:
      * Shortcode registration and function existence (2 tests - PASSING)
      * Basic output for single quote (1 test)
      * Category filtering: single, multiple, "all", empty, nonexistent (5 tests)
      * Multi parameter for multiple quotes (1 test)
      * Sequence parameter (sequential vs random) (1 test)
      * Other parameters: disableaspect, noajax, timer, offset (4 tests)
      * Edge cases: draft exclusion, empty database, combined attributes (3 tests)
    - TDD red phase confirmed: 15 tests failing (errors from legacy database table dependency)

- [x] **Task 26:** Refactor [stray-random] Shortcode to Use WP_Query
  - Update stray_random_shortcode() function to use new WP_Query helpers instead of raw SQL. Maintain all existing attributes and functionality. Make tests pass.
  - ✅ **Status:** COMPLETED - All 17 tests passing
    - Created stray_random_shortcode() in src/legacy/shortcodes.php using QuoteQueries class
    - Supports both single and multi-quote output
    - Handles category filtering via get_quotes_by_categories()
    - Random ordering by default, sequential if sequence=true
    - Thin wrapper maintained in inc/stray_functions.php for backward compatibility
    - All parameters functional: categories, sequence, multi, offset, disableaspect

- [x] **Task 27:** Write Tests for [stray-id] Shortcode
  - Create tests for specific quote shortcode: verify quote retrieval by ID, test legacy_id lookup support, validate output matches expected format, test non-existent ID handling.
  - ✅ **Status:** COMPLETED - 14 tests created (2 passing: shortcode registration, 12 failing: functional tests)
    - Created tests/shortcodes/test-stray-id-shortcode.php
    - Test Coverage:
      * Shortcode registration and function existence (2 tests - PASSING)
      * Retrieval by post ID and legacy ID (2 tests)
      * Default ID parameter handling (1 test)
      * Nonexistent ID handling (1 test)
      * Output format validation (1 test)
      * Parameters: disableaspect, noajax, linkphrase (3 tests)
      * Edge cases: draft quotes, numeric strings, legacy ID precedence, minimal quotes (4 tests)
    - TDD red phase confirmed: 12 tests failing (errors from legacy database table dependency)

- [x] **Task 28:** Refactor [stray-id] Shortcode to Use WP_Query
  - Update stray_id_shortcode() to query by post ID and support legacy_id meta lookup. Maintain backward compatibility. Make tests pass.
  - ✅ **Status:** COMPLETED - All 14 tests passing
    - Created stray_id_shortcode() in src/legacy/shortcodes.php using QuoteQueries class
    - Tries legacy ID first via get_quote_by_legacy_id(), then post ID via get_quote_by_id()
    - Falls back to first available quote when default ID=1 doesn't exist
    - Enhanced get_quote_by_id() to check post_status='publish' (filters out drafts)
    - Thin wrapper maintained in inc/stray_functions.php for backward compatibility
    - All parameters functional: id, disableaspect, noajax, linkphrase

- [x] **Task 29:** Write Tests for [stray-all] Shortcode
  - Create tests for all quotes shortcode: verify pagination works, test category filtering, validate sorting options, check fullpage parameter, test output structure with multiple quotes.
  - ✅ **Status:** COMPLETED - 24 tests created (2 passing: shortcode registration, 22 failing: functional tests)
    - Created tests/shortcodes/test-stray-all-shortcode.php
    - Test Coverage:
      * Shortcode registration and function existence (2 tests - PASSING)
      * Basic output and rows parameter (2 tests)
      * Category filtering: single, multiple, "all", empty, nonexistent (5 tests)
      * Ordering: sequence, orderby, sort parameters (5 tests)
      * Pagination: fullpage, offset parameters (3 tests)
      * Other parameters: noajax, disableaspect, linkphrase, timer (4 tests)
      * Edge cases: draft exclusion, no quotes, combined attributes (3 tests)
    - TDD red phase confirmed: 22 tests failing (errors from legacy database table dependency)

- [x] **Task 30:** Refactor [stray-all] Shortcode to Use WP_Query
  - Update stray_all_shortcode() to use WP_Query with pagination args. Maintain all filtering and sorting functionality. Make tests pass.
  - ✅ **Status:** COMPLETED - All 24 tests passing
    - Refactored stray_all_shortcode() to use QuoteQueries class
    - Added get_quotes_by_categories() method to QuoteQueries for multiple category filtering
    - Created stray_output_one_cpt() for CPT-compatible output formatting
    - Created stray_build_pagination() helper for pagination links
    - Implementation details:
      * Uses QuoteQueries::get_quotes_by_categories() or get_all_quotes()
      * Handles category filtering (single, multiple, all, empty)
      * Supports pagination with fullpage/simple modes
      * Maps legacy orderby values (quoteID→ID, author→title, etc.)
      * Random vs sequential ordering via sequence parameter
      * Extracts author from quote_author taxonomy, source from _quote_source meta
      * Respects all display settings from options (beforeAll, afterAll, etc.)
    - Test results: 24/24 tests passing, 166 total tests, 400 assertions

## Phase 7: Template Tags & Widgets

- [ ] **Task 31:** Write Tests for Template Tags
  - Create tests for template tag functions: stray_random_quote(), stray_a_quote(), verify backward compatibility, test parameter passing, validate output format matches original.

- [ ] **Task 32:** Refactor Template Tags to Use WP_Query
  - Update all template tag functions in stray_functions.php to use new query helpers. Maintain exact same function signatures and behavior. Make tests pass.

- [ ] **Task 33:** Write Tests for Widget Data Retrieval
  - Create tests for widget quote retrieval: verify category filtering works with new taxonomies, test multi-quote display, validate AJAX refresh parameters, check contributor filtering.

- [ ] **Task 34:** Refactor Widget to Use WP_Query
  - Update stray_widgets class to use WP_Query for quote retrieval. Maintain all existing widget options and UI. Update category dropdown to use get_terms(). Make tests pass.

- [ ] **Task 35:** Write Tests for AJAX Quote Refresh
  - Create tests for AJAX endpoint: verify xv_random_quotes_new_quote action, test parameter sanitization, validate response format, check category filtering via AJAX works with taxonomies.

- [ ] **Task 36:** Refactor AJAX Handlers to Use WP_Query
  - Update stray_ajax.php AJAX handlers to use new query system. Ensure AJAX refresh works with CPT/taxonomies. Make tests pass.

## Phase 8: Gutenberg Blocks

- [ ] **Task 37:** Write Tests for Random Quote Gutenberg Block
  - Create tests for server-rendered random quote block: verify block registration, test block attributes (category, showAuthor, showSource, enableAjax), validate server-side render callback, check block preview.

- [ ] **Task 38:** Implement Random Quote Gutenberg Block
  - Create src/blocks/random-quote/ with block.json and render.php. Implement server-side rendering using query helpers. Add block editor controls for attributes. Make tests pass.

## Phase 9: Settings & Admin UI

- [ ] **Task 39:** Write Tests for Settings Page - Display Options
  - Create tests for settings persistence: verify HTML wrapper settings save correctly, test default category setting, validate sanitization with wp_kses_post, check option retrieval.

- [ ] **Task 40:** Update Settings Page for CPT Compatibility
  - Review and update stray_settings.php to work with new architecture. Ensure all display settings still apply to CPT-based quotes. Make tests pass.

- [ ] **Task 41:** Write Tests for Admin List Table Customization
  - Create tests for custom columns in admin: verify quote text column, test author taxonomy column, check source meta column, validate category filter dropdown.

- [ ] **Task 42:** Implement Admin List Table Customizations
  - Add filters for manage_{post_type}_posts_columns and manage_{post_type}_posts_custom_column. Customize admin list view to show quote, author, source, category. Make tests pass.

## Phase 10: Backward Compatibility

- [ ] **Task 43:** Write Tests for Backward Compatibility Layer
  - Create comprehensive integration tests: verify old shortcode syntax works, test old function calls still work, validate widget settings migrate automatically, check URLs and permalinks.

- [ ] **Task 44:** Implement Backward Compatibility Helpers
  - Create compatibility layer for any deprecated functions. Add function_exists checks and wrappers where needed. Ensure zero breaking changes for existing users. Make tests pass.

## Phase 11: Core Refactor

- [ ] **Task 45:** Write Tests for Quote Rendering/Display Logic
  - Create tests for quote output formatting: verify HTML wrappers applied correctly, test author/source display logic, validate link generation, check aspect disable functionality, test multiuser filtering.

- [ ] **Task 46:** Refactor Core Display Function (get_stray_quotes)
  - Refactor the main get_stray_quotes() function to use WP_Query while maintaining all parameters and display logic. This is the largest refactor. Make tests pass.

## Phase 12: Integration Testing

- [ ] **Task 47:** Write Integration Tests for Full User Workflows
  - Create end-to-end tests: add quote via admin → display on frontend, migrate old quotes → verify display, create quote → edit → publish → display, test AJAX refresh cycle, test all shortcodes on same page.

- [ ] **Task 48:** Run Integration Tests and Fix Issues
  - Execute all integration tests, identify failures, fix bugs, ensure all user workflows work correctly end-to-end.

## Phase 13: Security & Performance

- [ ] **Task 49:** Write Tests for Security - XSS Prevention
  - Create security tests: test malicious input in quote text, verify author name XSS prevention, test source field HTML filtering with wp_kses_post, validate taxonomy term sanitization, test shortcode attribute sanitization.

- [ ] **Task 50:** Security Audit and Hardening
  - Review all output escaping (esc_html, esc_attr, wp_kses_post), verify all input sanitization, check nonce verification, validate capability checks, ensure no raw SQL vulnerabilities remain. Make tests pass.

- [ ] **Task 51:** Write Performance Tests
  - Create performance benchmark tests: test query performance with 1000+ quotes, verify AJAX response times, check page load impact, test widget rendering performance, validate caching works.

- [ ] **Task 52:** Performance Optimization
  - Optimize queries using WP_Query best practices, implement proper caching strategies, optimize AJAX endpoints, add query result caching where appropriate. Make performance tests pass.

## Phase 14: Plugin Metadata

- [ ] **Task 53:** Update Plugin Header and Version
  - Update xv-random-quotes.php header to version 2.0.0, update required WordPress version to 6.0+, update required PHP version to 7.4+, update description to mention modern WordPress integration.

- [ ] **Task 54:** Write Tests for Activation/Deactivation Hooks
  - Create tests for plugin lifecycle: verify activation triggers migration, test clean activation on new install, verify deactivation cleanup (if any), test reactivation doesn't duplicate data.

- [ ] **Task 55:** Update Activation/Deactivation Hooks
  - Update register_activation_hook to trigger migration system, ensure clean new installs work, update deactivation hook if needed. Make tests pass.

- [ ] **Task 56:** Update Plugin Documentation
  - Update README.md with new architecture info, update installation instructions, document migration process, add developer documentation for new CPT/taxonomy structure, update code examples.

- [ ] **Task 57:** Update WordPress.org readme.txt
  - Update readme.txt for WordPress.org: new version number, updated description highlighting modern features, update requirements (WP 6.0+, PHP 7.4+), add migration notes to upgrade notice, update screenshots if needed.

## Phase 15: Testing & QA

- [ ] **Task 58:** Create Migration Testing Database Fixtures
  - Create test fixtures with various database scenarios: small DB (10 quotes), medium DB (500 quotes), large DB (1000+ quotes), edge cases (special characters, HTML in fields, empty fields, orphaned categories).

- [ ] **Task 59:** Manual Testing - Fresh Install
  - Manually test fresh installation: install plugin on clean WordPress 6.0+, verify CPT appears in admin, create test quote, verify all fields work, test quote display on frontend, test all shortcodes.

- [ ] **Task 60:** Manual Testing - Migration from v1.40
  - Manually test migration: install v1.40 with sample data, upgrade to v2.0, verify automatic migration runs, check all quotes migrated correctly, verify categories and authors preserved, test display still works.

- [ ] **Task 61:** Manual Testing - Block Editor Integration
  - Manually test Gutenberg: create quote in block editor, verify sidebar panel works, test random quote block, check block preview, verify category selector, test block variations.

- [ ] **Task 62:** Manual Testing - Classic Editor Integration
  - Manually test Classic Editor plugin: verify meta boxes appear, test all fields save correctly, check quick edit functionality, test bulk edit if applicable.

- [ ] **Task 63:** Manual Testing - Widget Areas
  - Manually test widgets: add quote widget in classic widgets screen, test in block-based widget editor (WP 5.8+), verify all widget options work, test AJAX refresh, verify appearance on frontend.

- [ ] **Task 64:** Manual Testing - Multisite Compatibility
  - Test on WordPress multisite: verify plugin works per-site, test network activation if applicable, verify quotes don't leak between sites, test migration on multisite.

- [ ] **Task 65:** Browser Compatibility Testing
  - Test admin interface in Chrome, Firefox, Safari, Edge. Test AJAX functionality in all browsers. Verify block editor works in all browsers. Check mobile responsive admin.

- [ ] **Task 66:** Accessibility Audit (WCAG 2.1)
  - Run accessibility checks on admin UI, verify keyboard navigation works, check screen reader compatibility, validate ARIA labels, test color contrast, ensure form labels are proper.

## Phase 16: Release Preparation

- [ ] **Task 67:** Update Changelog
  - Update changelog.txt with comprehensive v2.0 changes: list all new features, document breaking changes (none expected), note migration process, credit contributors, add upgrade notes.

- [ ] **Task 68:** Create Release Notes
  - Write detailed release notes for v2.0: highlight major improvements, explain migration process for users, document new features (Gutenberg blocks, REST API), provide upgrade recommendations.

- [ ] **Task 69:** Prepare Assets for WordPress.org
  - Update/create banner images (1544x500, 772x250), update icon images (128x128, 256x256), create/update screenshots showing new features, optimize all images.

- [ ] **Task 70:** Final Code Review and Cleanup
  - Review all code for WordPress coding standards, remove debug statements, remove commented-out old code, verify all files have proper headers, check for TODOs/FIXMEs, run PHP_CodeSniffer.

- [ ] **Task 71:** Create GitHub Release
  - Tag v2.0.0 in git, create GitHub release with release notes, attach zip file for manual installation, update main branch protection if needed.

- [ ] **Task 72:** Submit to WordPress.org
  - Package plugin for WordPress.org, submit v2.0 to plugin repository, respond to any automated checks, wait for approval, monitor for issues after release.

---

## Phase Extra 1: Simplified Admin Editor (Meta Box Approach)

**Context:** Instead of maintaining separate implementations for Classic Editor (meta boxes) and Block Editor (React sidebar), use a unified meta box approach that works in both editors. This simplifies the codebase and provides better content control.

**Strategy:**
- Remove 'editor' support from CPT (keep only 'title')
- Create two meta boxes that work in both Classic and Block editors
- Use `wp_editor()` with strict toolbar configuration (bold, italic, link only)
- Save quote text to `post_content` and source to `_quote_source` meta
- Prevents users from adding unwanted HTML, images, or blocks

- [x] **Task Extra 1:** Update CPT Registration to Remove Editor Support
  - Modify src/PostTypes/QuotePostType.php to remove 'editor' from supports array (keep only 'title')
  - Update existing tests in tests/post-types/test-quote-post-type.php to verify editor support is NOT present
  - Verify CPT registration still works correctly without editor
  - Make tests pass
  - ✅ **Status:** COMPLETED
    - Modified src/PostTypes/QuotePostType.php line 81: removed 'editor' from supports array
    - Now supports: array('title', 'author', 'revisions', 'custom-fields')
    - Created tests/post-types/test-cpt-registration.php with 7 comprehensive tests:
      * test_post_type_exists() - verifies xv_quote registered
      * test_post_type_configuration() - checks public=false, show_ui=true, show_in_rest=true
      * test_post_type_supports_only_title() - KEY TEST: verifies editor NOT supported
      * test_post_type_supports_other_features() - verifies author, revisions, custom-fields
      * test_post_type_labels() - checks labels configuration
      * test_can_create_quote_post() - creates test post
      * test_quote_content_saved_to_post_content() - verifies post_content still writable
    - All 7 tests passing, total suite: 204 tests, 493 assertions

- [x] **Task Extra 2:** Write Tests for Quote Content Meta Box
  - Create tests/admin/test-quote-content-metabox.php for quote text meta box
  - Test Cases:
    * Verify meta box is registered for xv_quote post type
    * Check meta box appears in both Classic and Block editors
    * Validate nonce verification on save
    * Test autosave protection
    * Verify capability check (edit_post)
    * Test quote text sanitization (wp_kses_post - allows <strong>, <em>, <a>)
    * Verify quote text saves to post_content column (not post_meta)
    * Test empty quote text handling
    * Validate HTML stripping (no <img>, <script>, <div>, etc.)
    * Test that allowed tags are preserved (<strong>, <em>, <a>)
  - ✅ **Status:** COMPLETED
    - Created tests/admin/test-quote-content-metabox.php with 12 comprehensive tests:
      * test_quote_content_metabox_is_registered() - verifies registration
      * test_quote_content_metabox_title() - checks title "Quote Text"
      * test_quote_content_metabox_includes_nonce() - verifies nonce field
      * test_saves_quote_content_to_post_content_with_valid_nonce() - happy path
      * test_does_not_save_with_invalid_nonce() - security check
      * test_does_not_save_without_nonce() - security check
      * test_does_not_save_on_autosave() - DOING_AUTOSAVE protection
      * test_does_not_save_without_edit_capability() - capability check
      * test_sanitizes_quote_text_with_wp_kses_post() - sanitization verification
      * test_saves_empty_quote_text() - edge case handling
      * test_preserves_allowed_html_tags() - verifies 12 inline tags preserved: <strong>, <em>, <a>, <code>, <abbr>, <cite>, <q>, <mark>, <sub>, <sup>, <b>, <i>
      * test_strips_block_level_and_disallowed_tags() - verifies dangerous tags stripped: <script>, <iframe>, <style>, <object>
    - All 12 tests passing with @runInSeparateProcess for proper test isolation


- [x] **Task Extra 3:** Implement Quote Content Meta Box
  - Create or update src/Admin/MetaBoxes.php with quote content meta box
  - Register meta box for both 'normal' context (main editor area)
  - Implement wp_editor() with strict configuration:
    * media_buttons => false (no "Add Media")
    * quicktags => false (no HTML/Text tab)
    * toolbar1 => 'bold,italic,link,unlink' (only these buttons)
    * teeny => true (minimal editor)
  - Hook into save_post_xv_quote to save quote text to post_content
  - Use wpdb->update() directly to update post_content (avoids infinite loops)
  - Apply wp_kses() with custom allowed tags (only inline formatting tags)
  - Implement nonce verification, autosave check, capability check
  - Load existing post_content into editor on edit
  - Make all tests pass
  - ✅ **Status:** COMPLETED
    - Updated src/Admin/MetaBoxes.php with quote content meta box functionality
    - Registered meta box in 'normal' context (works in both Classic and Block editors)
    - Implemented wp_editor() with strict settings: media_buttons=false, quicktags=false, teeny=true, toolbar1='bold,italic,link,unlink'
    - Created save_quote_content() method with:
      * Nonce verification (xv_quote_content_nonce)
      * Autosave protection (DOING_AUTOSAVE check)
      * Capability check (edit_post)
      * wp_kses() sanitization with custom allowed tags array (12 inline formatting tags only)
      * wpdb->update() direct database update to post_content (prevents infinite loops)
      * clean_post_cache() to ensure fresh data retrieval
    - Allowed HTML tags: strong, em, b, i, code, abbr (with title), cite, q, mark, sub, sup, a (with href/title/target/rel)
    - Security: Strips all dangerous tags (<script>, <iframe>, <style>, <object>) and block-level tags
    - All 12 tests passing (216 total tests, 522 assertions in full suite)


- [x] **Task Extra 4:** Update Source Meta Box for Consistent UX
  - Update existing source meta box (from Task 19) for consistency
  - Replace simple input field with wp_editor() in 'teeny' mode for HTML formatting support
  - Ensure both meta boxes have similar styling and placement
  - Update tests for editor configuration changes
  - ✅ **Status:** COMPLETED
    - Updated src/Admin/MetaBoxes.php to use wp_editor() for source field
    - Renamed render_meta_box() to render_quote_source_meta_box() for clarity
    - Changed meta box context from 'side' to 'normal' (main editor area, below quote content)
    - Implemented same wp_editor() configuration as quote content but smaller (3 rows vs 8)
    - Editor settings: media_buttons=false, quicktags=false, teeny=true, toolbar1='bold,italic,link,unlink'
    - Updated save_meta_box() to use stricter wp_kses() sanitization (same allowed tags as quote content)
    - Allowed HTML tags: strong, em, b, i, code, abbr (with title), cite, q, mark, sub, sup, a (with href/title/target/rel)
    - Updated nonce action from 'xv_quote_source' to 'xv_quote_source_save' for consistency
    - Updated all 11 tests in tests/admin/test-classic-metabox.php:
      * Changed assertions from 'side' to 'normal' context
      * Updated all nonce actions to 'xv_quote_source_save'
      * Updated test_meta_box_not_registered_for_block_editor to test_meta_box_registered_for_both_editors
      * Updated rendering test to check for wp_editor wrapper class
    - All 11 tests passing (216 total tests, 525 assertions in full suite)
    - Supports formatted sources like: `Vist al <a href="http://twitter.com/eulez/status/220510283825823744">twitter d'Eulez</a>`


- [ ] **Task Extra 5:** Remove/Archive Block Editor React Code
  - Archive or remove src/blocks/quote-details/ directory (React sidebar code no longer needed)
  - Archive or remove src/Admin/BlockEditorAssets.php (asset enqueuing no longer needed)
  - Remove Block Editor asset enqueuing from src/Plugin.php initialization
  - Archive or remove tests/admin/test-block-editor-assets.php
  - Update package.json to mark @wordpress/scripts as devDependency (still useful for potential future blocks)
  - Document in comments why Block Editor approach was replaced with meta box approach
  - Verify all remaining tests still pass after removal

- [ ] **Task Extra 6:** Integration Testing - Meta Box Editor
  - Create integration tests verifying:
    * Quote creation in Classic Editor saves correctly to post_content
    * Quote creation in Block Editor saves correctly to post_content
    * Existing quotes (migrated data) load correctly in editor
    * HTML sanitization works as expected (strips <img>, preserves <strong>)
    * Both meta boxes are visible and functional in both editor types
    * Saved quotes display correctly on frontend using existing shortcodes/template tags
  - Test with WordPress 6.0+ to ensure compatibility

---

**Last Updated:** December 28, 2025
