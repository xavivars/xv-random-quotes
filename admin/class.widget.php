<?php

    if ( ! defined( 'XV_RANDOM_QUOTES' ) ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden' );
        exit();
    }

require_once plugin_dir_path( __FILE__ ).'/class.adminbase.php' ;

/**
 * Handles XV RandomQuotes widget
 *
 * @author xavi
 */
class XV_RandomQuotes_Widget extends WP_Widget {

	private $DEFAULT_WIDGET_TITLE;
	private $_repository;
	private $_renderer;
	
	public function __construct() {
		parent::__construct('xv_randomquotes', __('XV Random Quotes', 'xv-random-quotes') );
		$this->DEFAULT_WIDGET_TITLE = __( 'XV Random Quotes', 'xv-random-quotes');
		$this->_repository = new XV_RandomQuotes_Repository();
		$this->_renderer = new XV_RandomQuotes_QuoteRenderer();
	}

	public function widget( $args, $instance ) {
		extract($args);

        $title = apply_filters( 'widget_title', $instance['title'] );
		
		echo $before_widget;
		
		if( ! empty( $title ) ) {
			echo $before_title, $title, $after_title;
		}
		
		$args = array(
				'categories' => isset($instance["categories"]) ? explode(',', $instance["categories"]) : array(),
				'random' => $instance['random'],
				'reloadtext' => $instance['reloadtext'],
				'amount' => $instance['amount'],
				'ajax' => $instance['ajax'],
				'disableaspect' => $instance['disableaspect'],
				'timer' => $options["timer"],
			);
		
        $quote = $this->_repository->get_quote( $args );
		
		$this->_renderer->render( $quote );
		
        echo $after_widget;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
                $title = $instance[ 'title' ];
		}
		
		$random = isset ( $instance['random'] ) && $instance['random'];
		
		?>
			<p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
                <input class="widefat" 
                       id="<?php echo $this->get_field_id( 'title' ); ?>" 
                       name="<?php echo $this->get_field_name( 'title' ); ?>" 
                       type="text" value="<?php echo esc_attr( $title ); ?>" />
                
            </p>
			<p>
                <input type="checkbox" name="<?php echo $this->get_field_name('random'); ?>" 
					   id="<?php echo $this->get_field_id('random'); ?>" class="widefat" 
					   <?php if ($random) { ?> checked="checked" <?php } ?> />
				<label for="<?php echo $this->get_field_id('random'); ?>"><?php _e('Display a Random Quote', 'xv-random-quotes'); ?></label>
            </p>

		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['random'] = isset ( $new_instance['random'] );
		
		return $instance;
	}
}

add_action('widgets_init',
     create_function('', 'return register_widget("XV_RandomQuotes_Widget");')
);