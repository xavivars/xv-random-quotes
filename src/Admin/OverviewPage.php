<?php
/**
 * Overview/Help Page for XV Random Quotes
 *
 * @package XVRandomQuotes
 * @subpackage Admin
 */

namespace XVRandomQuotes\Admin;

/**
 * Class OverviewPage
 *
 * Displays overview and quick start information with links to detailed documentation
 */
class OverviewPage {
    /**
     * Register the overview page
     */
    public function register() {
        add_action('admin_menu', array($this, 'add_overview_page'), 100);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_xv_reset_migration', array($this, 'ajax_reset_migration'));
    }

    /**
     * Add overview page to admin menu and reorder it to appear first
     */
    public function add_overview_page() {
        global $submenu;
        
        add_submenu_page(
            'edit.php?post_type=xv_quote',
            __('Overview', 'xv-random-quotes'),
            __('Overview', 'xv-random-quotes'),
            'edit_posts',
            'xv-quotes-overview',
            array($this, 'render_overview_page')
        );

        // Reorder submenu to place Overview first
        $parent_slug = 'edit.php?post_type=xv_quote';
        if (isset($submenu[$parent_slug])) {
            $overview_item = null;
            $other_items = array();
            
            // Find and separate the Overview item
            foreach ($submenu[$parent_slug] as $key => $item) {
                if (isset($item[2]) && $item[2] === 'xv-quotes-overview') {
                    $overview_item = $item;
                } else {
                    $other_items[$key] = $item;
                }
            }
            
            // Rebuild submenu with Overview first
            if ($overview_item) {
                $submenu[$parent_slug] = array_merge(
                    array(0 => $overview_item),
                    $other_items
                );
            }
        }
    }

    /**
     * Render the overview page
     */
    public function render_overview_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="card">
                <h2><?php _e('Welcome to XV Random Quotes', 'xv-random-quotes'); ?></h2>
                <p>
                    <?php _e('Display beautiful, rotating quotes anywhere on your WordPress site using Custom Post Types, Gutenberg blocks, widgets, shortcodes, and REST API.', 'xv-random-quotes'); ?>
                </p>
            </div>

            <div class="card">
                <h2><?php _e('Quick Start Guide', 'xv-random-quotes'); ?></h2>
                
                <h3><?php _e('1. Add Your First Quote', 'xv-random-quotes'); ?></h3>
                <ol>
                    <li><?php _e('Go to Quotes → Add New', 'xv-random-quotes'); ?></li>
                    <li><?php _e('Enter a title (used for admin reference)', 'xv-random-quotes'); ?></li>
                    <li><?php _e('Enter the quote text in the Quote Content box', 'xv-random-quotes'); ?></li>
                    <li><?php _e('Optionally add a source in the Quote Source box', 'xv-random-quotes'); ?></li>
                    <li><?php _e('Assign an author from the Quote Authors taxonomy', 'xv-random-quotes'); ?></li>
                    <li><?php _e('Publish the quote', 'xv-random-quotes'); ?></li>
                </ol>

                <h3><?php _e('2. Display Quotes on Your Site', 'xv-random-quotes'); ?></h3>
                
                <h4><?php _e('Using Gutenberg Blocks (Recommended)', 'xv-random-quotes'); ?></h4>
                <ul>
                    <li><strong><?php _e('Random Quote Block:', 'xv-random-quotes'); ?></strong> <?php _e('Displays a random quote each time the page loads', 'xv-random-quotes'); ?></li>
                    <li><strong><?php _e('Specific Quote Block:', 'xv-random-quotes'); ?></strong> <?php _e('Displays a specific quote by ID', 'xv-random-quotes'); ?></li>
                    <li><strong><?php _e('List Quotes Block:', 'xv-random-quotes'); ?></strong> <?php _e('Displays multiple quotes with pagination', 'xv-random-quotes'); ?></li>
                </ul>
                <p><em><?php _e('Search for "quote" in the block inserter to find these blocks.', 'xv-random-quotes'); ?></em></p>

                <h4><?php _e('Using Shortcodes', 'xv-random-quotes'); ?></h4>
                <ul>
                    <li><code>[stray-random]</code> - <?php _e('Display a random quote', 'xv-random-quotes'); ?></li>
                    <li><code>[stray-id id="123"]</code> - <?php _e('Display a specific quote', 'xv-random-quotes'); ?></li>
                    <li><code>[stray-all]</code> - <?php _e('Display all quotes with pagination', 'xv-random-quotes'); ?></li>
                </ul>

                <h4><?php _e('Using Widgets', 'xv-random-quotes'); ?></h4>
                <p><?php _e('Go to Appearance → Widgets and add the "Random Quote" legacy widget to any widget area. Configure categories, AJAX refresh, and display options.', 'xv-random-quotes'); ?></p>

                <h4><?php _e('Using Template Tags', 'xv-random-quotes'); ?></h4>
                <p><?php _e('For theme developers:', 'xv-random-quotes'); ?></p>
                <pre><code>&lt;?php stray_random_quote(); ?&gt;</code></pre>
            </div>

            <div class="card">
                <h2><?php _e('Common Customizations', 'xv-random-quotes'); ?></h2>
                <ul>
                    <li><strong><?php _e('Filter by category:', 'xv-random-quotes'); ?></strong> <code>[stray-random categories="inspiration,wisdom"]</code></li>
                    <li><strong><?php _e('Display multiple quotes:', 'xv-random-quotes'); ?></strong> <code>[stray-random multi="3"]</code></li>
                    <li><strong><?php _e('Enable AJAX refresh:', 'xv-random-quotes'); ?></strong> <?php _e('Use the Random Quote widget with "Enable AJAX" checked', 'xv-random-quotes'); ?></li>
                    <li><strong><?php _e('Customize HTML output:', 'xv-random-quotes'); ?></strong> <?php _e('Go to Quotes → Settings to configure wrappers and styling', 'xv-random-quotes'); ?></li>
                </ul>
            </div>

            <div class="card">
                <h2><?php _e('Frequently Asked Questions', 'xv-random-quotes'); ?></h2>
                
                <h3><?php _e('How do I migrate from v1.x?', 'xv-random-quotes'); ?></h3>
                <p><?php _e('Migration happens automatically when you upgrade. For databases with ≤500 quotes, migration runs during plugin activation. For larger databases, you\'ll see an admin notice with a migration button to process quotes in batches.', 'xv-random-quotes'); ?></p>

                <h3><?php _e('Are my old shortcodes still supported?', 'xv-random-quotes'); ?></h3>
                <p><?php _e('Yes! All shortcodes, template tags, and widgets from v1.x continue to work without any changes needed.', 'xv-random-quotes'); ?></p>

                <h3><?php _e('How do I use the REST API?', 'xv-random-quotes'); ?></h3>
                <p><?php _e('Access random quotes at:', 'xv-random-quotes'); ?></p>
                <pre><code>GET /wp-json/xv-random-quotes/v1/quote/random</code></pre>
                <p><?php _e('Parameters: categories, sequence, multi, disableaspect, contributor', 'xv-random-quotes'); ?></p>

                <h3><?php _e('Can I customize the HTML output?', 'xv-random-quotes'); ?></h3>
                <p><?php _e('Yes! Go to Quotes → Settings to configure HTML wrappers, author/source link templates, and AJAX loading messages.', 'xv-random-quotes'); ?></p>

                <h3><?php _e('Where can I find more help?', 'xv-random-quotes'); ?></h3>
                <p>
                    <?php _e('For detailed documentation, see:', 'xv-random-quotes'); ?>
                </p>
                <ul>
                    <li><a href="<?php echo esc_url('https://github.com/xavivars/xv-random-quotes/blob/main/README.md'); ?>" target="_blank"><?php _e('Developer Documentation (README.md)', 'xv-random-quotes'); ?></a></li>
                    <li><a href="<?php echo esc_url('https://github.com/xavivars/xv-random-quotes/blob/main/RELEASE_NOTES.md'); ?>" target="_blank"><?php _e('Release Notes', 'xv-random-quotes'); ?></a></li>
                    <li><a href="<?php echo esc_url('https://github.com/xavivars/xv-random-quotes/blob/main/NEW_ARCHITECTURE.md'); ?>" target="_blank"><?php _e('Architecture Documentation', 'xv-random-quotes'); ?></a></li>
                    <li><a href="<?php echo esc_url('https://wordpress.org/support/plugin/xv-random-quotes/'); ?>" target="_blank"><?php _e('WordPress.org Support Forums', 'xv-random-quotes'); ?></a></li>
                    <li><a href="<?php echo esc_url('https://github.com/xavivars/xv-random-quotes/issues'); ?>" target="_blank"><?php _e('GitHub Issues', 'xv-random-quotes'); ?></a></li>
                </ul>
            </div>

            <div class="card">
                <h2><?php _e('Need Help?', 'xv-random-quotes'); ?></h2>
                <p>
                    <?php _e('If you encounter any issues or have questions:', 'xv-random-quotes'); ?>
                </p>
                <ul>
                    <li><?php printf(
                        __('Visit the %s for community support', 'xv-random-quotes'),
                        '<a href="https://wordpress.org/support/plugin/xv-random-quotes/" target="_blank">' . __('WordPress.org support forums', 'xv-random-quotes') . '</a>'
                    ); ?></li>
                    <li><?php printf(
                        __('Report bugs or request features on %s', 'xv-random-quotes'),
                        '<a href="https://github.com/xavivars/xv-random-quotes/issues" target="_blank">GitHub</a>'
                    ); ?></li>
                    <li><?php printf(
                        __('Review the complete %s', 'xv-random-quotes'),
                        '<a href="https://wordpress.org/plugins/xv-random-quotes/" target="_blank">' . __('plugin documentation', 'xv-random-quotes') . '</a>'
                    ); ?></li>
                </ul>
            </div>

            <div class="card">
                <h2><?php _e('Developer Tools', 'xv-random-quotes'); ?></h2>
                
                <h3><?php _e('Reset Migration', 'xv-random-quotes'); ?></h3>
                <p>
                    <?php _e('This will delete all migrated Custom Post Type quotes and reset migration flags. Use this to re-run the migration from the legacy database table.', 'xv-random-quotes'); ?>
                </p>
                <p>
                    <strong style="color: #d63638;"><?php _e('Warning: This action cannot be undone! All Custom Post Type quotes will be permanently deleted.', 'xv-random-quotes'); ?></strong>
                </p>
                <p>
                    <button type="button" id="xv-reset-migration" class="button button-secondary">
                        <?php _e('Reset Migration', 'xv-random-quotes'); ?>
                    </button>
                    <span id="xv-reset-status" style="margin-left: 10px;"></span>
                </p>
            </div>

            <div class="card">
                <h2><?php _e('Contributing', 'xv-random-quotes'); ?></h2>
                <p>
                    <?php _e('XV Random Quotes is open source! Contributions are welcome:', 'xv-random-quotes'); ?>
                </p>
                <ul>
                    <li><?php printf(
                        __('Fork the project on %s', 'xv-random-quotes'),
                        '<a href="https://github.com/xavivars/xv-random-quotes" target="_blank">GitHub</a>'
                    ); ?></li>
                    <li><?php _e('Submit pull requests with bug fixes or new features', 'xv-random-quotes'); ?></li>
                    <li><?php _e('Help translate the plugin into your language', 'xv-random-quotes'); ?></li>
                    <li><?php _e('Rate and review the plugin on WordPress.org', 'xv-random-quotes'); ?></li>
                </ul>
            </div>

            <style>
                .wrap .card {
                    max-width: 100%;
                    margin-bottom: 20px;
                }
                .wrap .card h3 {
                    margin-top: 20px;
                    margin-bottom: 10px;
                }
                .wrap .card h4 {
                    margin-top: 15px;
                    margin-bottom: 8px;
                }
                .wrap .card pre {
                    background: #f5f5f5;
                    padding: 10px;
                    border-left: 4px solid #2271b1;
                    overflow-x: auto;
                }
                .wrap .card code {
                    background: #f5f5f5;
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-family: monospace;
                }
                .wrap .card ul,
                .wrap .card ol {
                    margin-left: 20px;
                }
                .wrap .card li {
                    margin-bottom: 8px;
                }
            </style>
        </div>
        <?php
    }

    /**
     * Enqueue scripts for the overview page
     */
    public function enqueue_scripts($hook) {
        // Only load on our overview page
        if ($hook !== 'xv_quote_page_xv-quotes-overview') {
            return;
        }

        wp_enqueue_script(
            'xv-overview-admin',
            plugins_url('js/overview-admin.js', dirname(dirname(__FILE__))),
            array('jquery'),
            '2.0.0',
            true
        );

        wp_localize_script('xv-overview-admin', 'xvOverview', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('xv_reset_migration'),
            'confirmMessage' => __('Are you sure you want to reset the migration? This will delete all Custom Post Type quotes and cannot be undone!', 'xv-random-quotes'),
        ));
    }

    /**
     * AJAX handler to reset migration
     */
    public function ajax_reset_migration() {
        check_ajax_referer('xv_reset_migration', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'xv-random-quotes')));
            return;
        }

        global $wpdb;

        // Delete all xv_quote posts
        $posts = get_posts(array(
            'post_type' => 'xv_quote',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids',
        ));

        $deleted_posts = 0;
        foreach ($posts as $post_id) {
            if (wp_delete_post($post_id, true)) {
                $deleted_posts++;
            }
        }

        // Delete all xv_quote% options
        $options_deleted = $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%xv_quote%'"
        );

        // Delete widget migration flag and settings
        delete_option('xv_quotes_widgets_migrated');
        delete_option('widget_xv_random_quotes_widget');
        
        // Delete settings migration flag
        delete_option('_xv_quotes_migrated');

        // Delete migration transients
        delete_transient('xv_migration_total');
        delete_transient('xv_migration_progress');
        delete_transient('xv_migration_offset');
        delete_transient('xv_migration_error');
        delete_transient('xv_migration_success');

        wp_send_json_success(array(
            'message' => sprintf(
                __('Migration reset complete. Deleted %d posts and %d options. Refresh the page to re-run all migrations.', 'xv-random-quotes'),
                $deleted_posts,
                $options_deleted
            ),
        ));
    }
}
