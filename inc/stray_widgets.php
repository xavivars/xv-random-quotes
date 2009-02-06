<?php

/****

Multi-Widget solution thanks to 
Millan http://wp.gdragon.info/
Tutorial on this page:
http://wp.gdragon.info/2008/07/06/create-multi-instances-widget/

I'll add comments to this section soon.

*****/

class stray_widgets {

    function init() {
		
        if (!$options = get_option('widget_stray_quotes'))
            $options = array();
            
        $widget_ops = array('classname' => 'widget_stray_quotes', 'description' => '');
        $control_ops = array('width' => 250, 'height' => 100, 'id_base' => 'stray_widgets');
        $name = 'Quotes';
        
        $registered = false;
        foreach (array_keys($options) as $o) {
            if (!isset($options[$o]['title']))
                continue;
                
            $id = "stray_widgets-$o";		
				
			//check if the widgets is active
			global $wpdb;		
			$sql = "SELECT option_value FROM $wpdb->options WHERE option_name = 'sidebars_widgets' AND option_value like '%".$id."%'";
			$var = $wpdb->get_var( $sql );
			/*$var = is_active_widget($id);*/
			if (!$var)unset($options[$o]);
			
			/*delete_option('widget_stray_quotes');*/			
			
            $registered = true;
            wp_register_sidebar_widget($id, $name, array(&$this, 'widget'), $widget_ops, array( 'number' => $o ) );
            wp_register_widget_control($id, $name, array(&$this, 'control'), $control_ops, array( 'number' => $o ) );
        }
        if (!$registered) {
            wp_register_sidebar_widget('stray_widgets-1', $name, array(&$this, 'widget'), $widget_ops, array( 'number' => -1 ) );
            wp_register_widget_control('stray_widgets-1', $name, array(&$this, 'control'), $control_ops, array( 'number' => -1 ) );
        }
		
		update_option('widget_stray_quotes', $options);
    }

    function widget($args, $widget_args = 1) {
        extract($args);

        if (is_numeric($widget_args))
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array( 'number' => -1 ));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_stray_quotes');
        if (!isset($options_all[$number]))
            return;

        $options = $options_all[$number];

        echo $before_widget.$before_title;
		echo $options["title"];
        echo $after_title;
		stray_random_quote(isset($options["categories"]) ? explode(',', $options["categories"]) : array(""));
        echo $after_widget;
    }

    function control($widget_args = 1) {
        global $wp_registered_widgets;
        static $updated = false;

        if ( is_numeric($widget_args) )
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array('number' => -1));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_stray_quotes');
        if (!is_array($options_all))
            $options_all = array();  
            
        if (!$updated && !empty($_POST['sidebar'])) {
            $sidebar = (string)$_POST['sidebar'];

            $sidebars_widgets = wp_get_sidebars_widgets();
            if (isset($sidebars_widgets[$sidebar]))
                $this_sidebar =& $sidebars_widgets[$sidebar];
            else
                $this_sidebar = array();

            foreach ($this_sidebar as $_widget_id) {
                if ('widget_stray_quotes' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
                    $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                    if (!in_array("stray_widgets-$widget_number", $_POST['widget-id']))
                        unset($options_all[$widget_number]);
                }
            }
            foreach ((array)$_POST['widget_stray_quotes'] as $widget_number => $posted) {
                if (!isset($posted['title']) && isset($options_all[$widget_number]))
                    continue;
                
                $options = array();
                
                $options['title'] = $posted['title'];
                $options['categories'] = isset($posted['categories']) ? implode(',', $posted['categories']) : ''; 
                
                $options_all[$widget_number] = $options;
            }
            update_option('widget_stray_quotes', $options_all);
            $updated = true;
        }
		
		$quotesoptions = get_option('stray_quotes_options');
		$widgetTitle = $quotesoptions['stray_quotes_widget_title'];
		$default_options = array(
				'title' => $widgetTitle, 
				'categories' => implode(",",make_categories()),
		);
	

        if (-1 == $number) {
            $number = '%i%';
            $values = $default_options;
        }
        else {
            $values = $options_all[$number];
        }
        
		?><p><label for="gdpnav-title">Pick a title:</label>
		<input class="widefat" id="widget_stray_quotes-<?php echo $number; ?>-title" 
        name="widget_stray_quotes[<?php echo $number; ?>][title]" type="text" 
        value="<?php echo htmlspecialchars($values['title'], ENT_QUOTES); ?>" />
        </p>
		<p><label for="gdpnav-title">Select categories (drag the mouse or ctrl-click to multi-select):</label>
		<select class="widefat" style="width: 100%; height: 70px;" name="widget_stray_quotes[<?php echo $number; ?>][categories][]" 
        id="widget_stray_quotes-<?php echo $number; ?>-categories" multiple="multiple"></p>
        
		<?php 
		$items = make_categories();
		$categories = explode(',', $values['categories']);
        if ($items) {
            foreach ($items as $item) {
                if (in_array($item, $categories))
                    $current = ' selected="selected"';
                else
                    $current = '';
    
                echo "\n\t<option value='$item'$current>$item</option>";        
            }
        }         
		?></select>
		<br /><?php

    }
    
    function render_quotes($categories = array()) {
 		
    }
}

$gdm = new stray_widgets();
add_action('widgets_init', array($gdm, 'init'));

?>