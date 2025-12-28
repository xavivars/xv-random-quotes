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

		// Get widget settings with defaults
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$categories    = ! empty( $instance['categories'] ) ? $instance['categories'] : 'all';
		$sequence      = ! empty( $instance['sequence'] ) ? (bool) $instance['sequence'] : false;
		$multi         = ! empty( $instance['multi'] ) ? absint( $instance['multi'] ) : 1;
		$disableaspect = ! empty( $instance['disableaspect'] ) ? (bool) $instance['disableaspect'] : false;
		$contributor   = ! empty( $instance['contributor'] ) ? sanitize_text_field( $instance['contributor'] ) : '';

		// TODO: AJAX and timer features temporarily disabled
		// Will be reimplemented in Tasks 35-36 (AJAX refactoring)
		// Legacy parameters: linkphrase, noajax, timer, widgetid
		
		// Output widget
		echo $before_widget;
		
		if ( ! empty( $title ) ) {
			echo $before_title . esc_html( $title ) . $after_title;
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
		
		echo $after_widget;
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
			<small style="color: #666;">
				<strong><?php esc_html_e( 'Note:', 'stray-quotes' ); ?></strong>
				<?php esc_html_e( 'AJAX refresh and timer features are temporarily disabled and will be restored in a future update.', 'stray-quotes' ); ?>
			</small>
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
		
		return $instance;
	}
}
