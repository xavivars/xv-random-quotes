<?php
/**
 * Quote Widget
 *
 * Modern widget implementation using WP_Widget base class and CPT architecture
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Widgets;

use XVRandomQuotes\Output\QuoteOutput;
use XVRandomQuotes\Admin\Settings;

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
			'description' => __( 'Display random quotes from your collection', 'xv-random-quotes' ),
		);
		
		$control_ops = array(
			'width'   => 650,
			'height'  => 100,
			'id_base' => 'xv_quote_widget',
		);
		
		parent::__construct(
			'xv_quote_widget',
			__( 'Random Quotes', 'xv-random-quotes' ),
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

		// Output widget
		$out = $before_widget;
		
		if ( ! empty( $title ) ) {
			$out .= $before_title . esc_html( $title ) . $after_title;
		}
		
		// Use QuoteOutput class for complete rendering (including AJAX if enabled)
		$quote_output = new QuoteOutput();
		$out .= $quote_output->get_random_quotes(
			array(
				'categories'    => $categories,
				'sequence'      => $sequence,
				'multi'         => $multi,
				'offset'        => 0,
				'disableaspect' => $disableaspect,
				'contributor'   => $contributor,
				'enable_ajax'   => $enable_ajax,
				'timer'         => $timer,
				'container_id'  => 'xv-quote-container-' . sanitize_html_class( $widget_id ),
			)
		);
		
		$out .= $after_widget;

        echo wp_kses_post( $out );
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
				<strong><?php esc_html_e( 'Title:', 'xv-random-quotes' ); ?></strong>
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
				<strong><?php esc_html_e( 'Categories:', 'xv-random-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Comma-separated category slugs, or "all" for all categories', 'xv-random-quotes' ); ?></small>
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
					<?php esc_html_e( 'Available categories:', 'xv-random-quotes' ); ?>
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
				<strong><?php esc_html_e( 'Number of quotes:', 'xv-random-quotes' ); ?></strong>
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
				<strong><?php esc_html_e( 'Sequential order', 'xv-random-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Leave unchecked for random order', 'xv-random-quotes' ); ?></small>
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
				<strong><?php esc_html_e( 'Disable aspect settings', 'xv-random-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Disable HTML formatting from settings page', 'xv-random-quotes' ); ?></small>
			</label>
		</p>

		<?php
		// Check if multiuser is enabled
		$quote_options = get_option( 'stray_quotes_options', array() );
		if ( isset( $quote_options['stray_multiuser'] ) && $quote_options['stray_multiuser'] === 'Y' ) :
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'contributor' ) ); ?>">
				<strong><?php esc_html_e( 'Only from this contributor:', 'xv-random-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Leave empty for all contributors', 'xv-random-quotes' ); ?></small>
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
				<strong><?php esc_html_e( 'Enable AJAX refresh', 'xv-random-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Allow users to get new quotes without reloading the page', 'xv-random-quotes' ); ?></small>
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'timer' ) ); ?>">
				<strong><?php esc_html_e( 'Auto-refresh timer (seconds):', 'xv-random-quotes' ); ?></strong><br/>
				<small><?php esc_html_e( 'Set to 0 for manual refresh only, or enter seconds for automatic refresh', 'xv-random-quotes' ); ?></small>
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
