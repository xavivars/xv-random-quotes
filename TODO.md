# XV Random Quotes v2.0 - Development TODO List

This document tracks the complete roadmap for refactoring XV Random Quotes from v1.40 to v2.0, migrating from a custom database table to WordPress Custom Post Types.

**Progress:** 9/72 tasks completed (12.5%)

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

- [ ] **Task 18:** Write Tests for Classic Meta Box
  - Create tests for quote details meta box: verify box is registered for xv_quote post type, test nonce verification, validate author taxonomy assignment, check source meta save with wp_kses_post sanitization.

- [ ] **Task 19:** Implement Classic Meta Box
  - Create src/Admin/MetaBoxes.php with quote details meta box. Implement add_meta_box callback, create save_post hook with proper sanitization, handle author taxonomy and source meta. Make tests pass.

- [ ] **Task 20:** Setup Block Editor Build Environment
  - Install @wordpress/scripts, configure package.json with build scripts, create src/blocks directory structure, setup webpack config (or use default from @wordpress/scripts), test build process works.

- [ ] **Task 21:** Write Tests for Block Editor Sidebar Panel
  - Create integration tests for Gutenberg sidebar panel: verify panel registration, test meta field updates via REST API, validate panel appears only for xv_quote post type, check field synchronization.

- [ ] **Task 22:** Implement Block Editor Sidebar Panel
  - Create src/blocks/quote-details/index.js with PluginDocumentSettingPanel. Implement TextControl components for author and source, add useEntityProp hooks, register plugin. Make tests pass.

## Phase 5: Query System Refactor

- [ ] **Task 23:** Write Tests for WP_Query Helper Functions
  - Create tests for new query helper functions: test get_random_quote(), get_quote_by_id(), get_quotes_by_category(), verify proper WP_Query args, validate post type filtering, check taxonomy queries.

- [ ] **Task 24:** Implement WP_Query Helper Functions
  - Create src/Queries/QuoteQueries.php with helper functions wrapping WP_Query. Replace raw SQL patterns with WP_Query calls. Make tests pass.

## Phase 6: Shortcodes Refactor

- [ ] **Task 25:** Write Tests for [stray-random] Shortcode
  - Create tests for random quote shortcode: verify shortcode registered, test category filtering, validate AJAX parameter handling, check output HTML structure, test all existing attributes work (show_author, show_source, etc).

- [ ] **Task 26:** Refactor [stray-random] Shortcode to Use WP_Query
  - Update stray_random_shortcode() function to use new WP_Query helpers instead of raw SQL. Maintain all existing attributes and functionality. Make tests pass.

- [ ] **Task 27:** Write Tests for [stray-id] Shortcode
  - Create tests for specific quote shortcode: verify quote retrieval by ID, test legacy_id lookup support, validate output matches expected format, test non-existent ID handling.

- [ ] **Task 28:** Refactor [stray-id] Shortcode to Use WP_Query
  - Update stray_id_shortcode() to query by post ID and support legacy_id meta lookup. Maintain backward compatibility. Make tests pass.

- [ ] **Task 29:** Write Tests for [stray-all] Shortcode
  - Create tests for all quotes shortcode: verify pagination works, test category filtering, validate sorting options, check fullpage parameter, test output structure with multiple quotes.

- [ ] **Task 30:** Refactor [stray-all] Shortcode to Use WP_Query
  - Update stray_all_shortcode() to use WP_Query with pagination args. Maintain all filtering and sorting functionality. Make tests pass.

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

**Last Updated:** December 27, 2025
