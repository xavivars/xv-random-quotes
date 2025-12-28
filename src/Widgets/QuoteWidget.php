<?php
/**
 * Quote Widget
 *
 * Modern widget implementation using WP_Widget base class and CPT architecture
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Widgets;

use XVRandomQuotes\Legacy;

/**
 * Quote Widget Class
 */
class QuoteWidget extends \WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'widget_xv_quotes',
			'description' => __( 'Display random quotes from your collection', 'stray-quotes' ),
		);
		
		$control_ops = array(
			'width'   => 650,
			'height'  => 100,
			'id_base' => 'xv_quote_widget',
		);
		
		parent::__construct(
			'xv_quote_widget',
			__( 'Random Quotes', 'stray-quotes' ),
			$widget_ops,
			$control_ops
		);
	}

	/**
	 * Display widget output
	 *
	 * @param array $args     Widget arguments
	 * @param array $instance Widget instance settings
	 */
	public function widget( $args, $instance ) {
		// Extract widget args
		$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
		$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';
		$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : '';
		$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : '';
		$widget_id     = isset( $args['widget_id'] ) ? $args['widget_id'] : 'xv_quote_widget-' . uniqid();

		// Get widget settings with defaults
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$categories    = ! empty( $instance['categories'] ) ? $instance['categories'] : 'all';
		$sequence      = ! empty( $instance['sequence'] ) ? (bool) $instance['sequence'] : false;
		$multi         = ! empty( $instance['multi'] ) ? absint( $instance['multi'] ) : 1;
		$disableaspect = ! empty( $instance['disableaspect'] ) ? (bool) $instance['disableaspect'] : false;
		$contributor   = ! empty( $instance['contributor'] ) ? sanitize_text_field( $instance['contributor'] ) : '';
		$enable_ajax   = ! empty( $instance['enable_ajax'] ) ? (bool) $instance['enable_ajax'] : false;
		$timer         = ! empty( $instance['timer'] ) ? absint( $instance['timer'] ) : 0;

		// Enqueue AJAX script if enabled
		if ( $enable_ajax ) {
			$this->enqueue_refresh_script();
		}
		
		// Output widget
		echo $before_widget;
		
		if ( ! empty( $title ) ) {
			echo $before_title . esc_html( $title ) . $after_title;
		}
		
		// Generate unique container ID
		$container_id = 'xv-quote-container-' . sanitize_html_class( $widget_id );
		
		// Start quote container with data attributes if AJAX enabled
		if ( $enable_ajax ) {
			echo '<div id="' . esc_attr( $container_id ) . '" class="xv-quote-container"';
			echo ' data-categories="' . esc_attr( $categories ) . '"';
			echo ' data-sequence="' . esc_attr( $sequence ? '1' : '0' ) . '"';
			echo ' data-multi="' . esc_attr( $multi ) . '"';
			echo ' data-disableaspect="' . esc_attr( $disableaspect ? '1' : '0' ) . '"';
			if ( ! empty( $contributor ) ) {
				echo ' data-contributor="' . esc_attr( $contributor ) . '"';
			}
			echo ' data-timer="' . esc_attr( $timer ) . '"';
			echo '>';
		}
		
		// Use core implementation for quote retrieval and display
		echo Legacy\stray_get_random_quotes_output(
			$categories,
			$sequence,
			$multi,
			0, // offset
			$disableaspect,
			$contributor
		);
		
		// Add refresh link if AJAX enabled
		if ( $enable_ajax ) {
			echo '<div class="xv-quote-refresh-wrapper">';
			echo '<a href="#" class="xv-quote-refresh" data-container="' . esc_attr( $container_id ) . '">';
			echo esc_html__( 'Get another quote', 'stray-quotes' );
			echo '</a>';
			echo '</div>';
			echo '</div>'; // Close container
		}
		
		echo $after_widget;
	}

	/**
	 * Enqueue the quote refresh JavaScript
	 */
	private function enqueue_refresh_script() {
		// WordPress will handle deduplication if this is called multiple times
		// Get plugin root directory (go up from src/Widgets/)
		$plugin_dir = dirname( dirname( __DIR__ ) );
		$script_path = $plugin_dir . '/assets/js/quote-refresh.js';
		$script_url  = plugins_url( 'assets/js/quote-refresh.js', dirname( dirname( __FILE__ ) ) );
		
		// Use file modification time for cache busting, fall back to version if file doesn't exist
		$version = file_exists( $script_path ) ? filemtime( $script_path ) : '1.0.0';
		
		// Only enqueue if not already enqueued
		if ( ! wp_script_is( 'xv-quote-refresh', 'enqueued' ) ) {
			wp_enqueue_script(
				'xv-quote-refresh',
				$script_url,
				array(), // No dependencies - vanilla JavaScript
				$version,
				true // In footer
			);
			
			// Localize script with REST API URL
			wp_localize_script(
				'xv-quote-refresh',
				'xvQuoteRefresh',
				array(
					'restUrl'   => esc_url_raw( rest_url( 'xv-random-quotes/v1/quote/random' ) ),
					'restNonce' => wp_create_nonce( 'wp_rest' ),
				)
			);
		}
	}

	/**
	 * Display widget settings form
	 *
	 * @param array $instance Widget instance settings
	 * @return string
	 */
	public function form( $instance ) {
		// Get current settings
		$title         = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$categories    = isset( $instance['categories'] ) ? esc_attr( $instance['categories'] ) : 'all';
		$sequence      = isset( $instance['sequence'] ) ? (bool) $instance['sequence'] : false;
		$multi         = isset( $instance['multi'] ) ? absint( $instance['multi'] ) : 1;
		$disableaspect = isset( $instance['disableaspect'] ) ? (bool) $instance['disableaspect'] : false;
		$contributor   = isset( $instance['contributor'] ) ? esc_attr( $instance['contributor'] ) : '';
		$enable_ajax   = isset( $instance['enable_ajax'] ) ? (bool) $instance['enable_ajax'] : false;
		$timer         = isset( $instance['timer'] ) ? absint( $instance['timer'] ) : 0;

		// Get available categories from taxonomy
		$category_terms = get_terms(
			array(
				'taxonomy'   => 'quote_category',
				'hide_empty' => false,
			)
		);

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<strong><?php esc_html_e( 'Title:', 'stray-quotes' ); ?></strong>
			</label>
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
				type="text" 
				value="<?php echo esc_attr( $title ); ?>"
			/>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>">
				<strong><?php esc_html_e( 'Categories:', 'stray-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Comma-separated category slugs, or "all" for all categories', 'stray-quotes' ); ?></small>
			</label>
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'categories' ) ); ?>" 
				type="text" 
				value="<?php echo esc_attr( $categories ); ?>"
			/>
			<?php if ( ! empty( $category_terms ) && ! is_wp_error( $category_terms ) ) : ?>
				<small>
					<?php esc_html_e( 'Available categories:', 'stray-quotes' ); ?>
					<?php
					$slugs = array_map( function( $term ) {
						return $term->slug;
					}, $category_terms );
					echo esc_html( implode( ', ', $slugs ) );
					?>
				</small>
			<?php endif; ?>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'multi' ) ); ?>">
				<strong><?php esc_html_e( 'Number of quotes:', 'stray-quotes' ); ?></strong>
			</label>
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'multi' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'multi' ) ); ?>" 
				type="number" 
				min="1"
				value="<?php echo esc_attr( $multi ); ?>"
			/>
		</p>

		<p>
			<input 
				id="<?php echo esc_attr( $this->get_field_id( 'sequence' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'sequence' ) ); ?>" 
				type="checkbox" 
				value="1"
				<?php checked( $sequence, true ); ?>
			/>
			<label for="<?php echo esc_attr( $this->get_field_id( 'sequence' ) ); ?>">
				<strong><?php esc_html_e( 'Sequential order', 'stray-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Leave unchecked for random order', 'stray-quotes' ); ?></small>
			</label>
		</p>

		<p>
			<input 
				id="<?php echo esc_attr( $this->get_field_id( 'disableaspect' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'disableaspect' ) ); ?>" 
				type="checkbox" 
				value="1"
				<?php checked( $disableaspect, true ); ?>
			/>
			<label for="<?php echo esc_attr( $this->get_field_id( 'disableaspect' ) ); ?>">
				<strong><?php esc_html_e( 'Disable aspect settings', 'stray-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Disable HTML formatting from settings page', 'stray-quotes' ); ?></small>
			</label>
		</p>

		<?php
		// Check if multiuser is enabled
		$quote_options = get_option( 'stray_quotes_options', array() );
		if ( isset( $quote_options['stray_multiuser'] ) && $quote_options['stray_multiuser'] === 'Y' ) :
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'contributor' ) ); ?>">
				<strong><?php esc_html_e( 'Only from this contributor:', 'stray-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Leave empty for all contributors', 'stray-quotes' ); ?></small>
			</label>
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'contributor' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'contributor' ) ); ?>" 
				type="text" 
				value="<?php echo esc_attr( $contributor ); ?>"
			/>
		</p>
		<?php endif; ?>

		<p style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 15px;">
			<input 
				id="<?php echo esc_attr( $this->get_field_id( 'enable_ajax' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'enable_ajax' ) ); ?>" 
				type="checkbox" 
				value="1"
				<?php checked( $enable_ajax, true ); ?>
			/>
			<label for="<?php echo esc_attr( $this->get_field_id( 'enable_ajax' ) ); ?>">
				<strong><?php esc_html_e( 'Enable AJAX refresh', 'stray-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Allow users to get new quotes without reloading the page', 'stray-quotes' ); ?></small>
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'timer' ) ); ?>">
				<strong><?php esc_html_e( 'Auto-refresh timer (seconds):', 'stray-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Set to 0 for manual refresh only, or enter seconds for automatic refresh', 'stray-quotes' ); ?></small>
			</label>
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'timer' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'timer' ) ); ?>" 
				type="number" 
				min="0"
				step="1"
				value="<?php echo esc_attr( $timer ); ?>"
			/>
		</p>
		<?php
	}

	/**
	 * Update widget settings
	 *
	 * @param array $new_instance New widget settings
	 * @param array $old_instance Old widget settings
	 * @return array Updated settings
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		
		$instance['title']         = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['categories']    = ! empty( $new_instance['categories'] ) ? sanitize_text_field( $new_instance['categories'] ) : 'all';
		$instance['sequence']      = ! empty( $new_instance['sequence'] ) ? true : false;
		$instance['multi']         = ! empty( $new_instance['multi'] ) ? absint( $new_instance['multi'] ) : 1;
		$instance['disableaspect'] = ! empty( $new_instance['disableaspect'] ) ? true : false;
		$instance['contributor']   = ! empty( $new_instance['contributor'] ) ? sanitize_text_field( $new_instance['contributor'] ) : '';
		$instance['enable_ajax']   = ! empty( $new_instance['enable_ajax'] ) ? true : false;
		$instance['timer']         = ! empty( $new_instance['timer'] ) ? absint( $new_instance['timer'] ) : 0;
		
		return $instance;
	}
}
