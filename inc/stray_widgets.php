<?php
/*I'll add comments to this section soon.*/
/* NOTE: whenever on this page you find "group" mentioned, it is meant "category". 
But it has to remain "group", to avoid having to change the 'widget_stray_quotes' array structure in the database. */

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
		
		if ($options["sequence"] == "Y")$sequence = false;
		else $sequence = true;
		
		$linkphrase = $options["linkphrase"];
		$widgetid = $number;

        echo $before_widget.$before_title;
		echo $options["title"];
        echo $after_title;
		stray_random_quote(isset($options["groups"]) ? explode(',', $options["groups"]) : array("default"),$sequence,$linkphrase,$widgetid);
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
                $options['groups'] = isset($posted['groups']) ? implode(',', $posted['groups']) : ''; 
				$options['sequence'] =  $posted['sequence'];
				$options['linkphrase'] =  $posted['linkphrase'];
                
                $options_all[$widget_number] = $options;
            }
            update_option('widget_stray_quotes', $options_all);
            $updated = true;
        }
		
		$quotesoptions = get_option('stray_quotes_options');
		$widgetTitle = $quotesoptions['stray_quotes_widget_title'];
		$linkphrase = $quotesoptions['stray_loader'];
		$default_options = array(
				'title' => $widgetTitle, 
				'groups' => implode(",",make_categories()),
				'sequence' => false,
				'linkphrase' => $linkphrase
		);
	

        if (-1 == $number) {
            $number = '%i%';
            $values = $default_options;
        }
        else {
            $values = $options_all[$number];
        }
		
		
		if ( $values['sequence'] == "Y" ) $random_selected = ' checked="checked"';	
        
		?><p><label><strong>Title</strong></label>
		<input class="widefat" id="widget_stray_quotes-<?php echo $number; ?>-title" 
        name="widget_stray_quotes[<?php echo $number; ?>][title]" type="text" 
        value="<?php echo htmlspecialchars($values['title'], ENT_QUOTES); ?>" />
        </p>
        
		<p><label><strong>Categories</strong><span class="setting-description"> <small>quotes are taken from these. drag the mouse or ctrl-click to multi-select</small></span></label>
		<select class="widefat" style="width: 100%; height: 70px;" name="widget_stray_quotes[<?php echo $number; ?>][groups][]" 
        id="widget_stray_quotes-<?php echo $number; ?>-groups" multiple="multiple"></p>
        
        
		<?php 
		$items = make_categories();
		$groups = explode(',', $values['groups']);
        if ($items) {
            foreach ($items as $item) {
                if (in_array($item, $groups))
                    $current = ' selected="selected"';
                else
                    $current = '';
    
                echo "\n\t<option value='$item'$current>$item</option>";        
            }
        }         
		?></select>
        
		<p><label><strong>Link phrase</strong><span class="setting-description"> <small>if left empty, reloading is done by clicking on the quote.</small></span></label>
		<input class="widefat" id="widget_stray_quotes-<?php echo $number; ?>-title" 
        name="widget_stray_quotes[<?php echo $number; ?>][linkphrase]" type="text" 
        value="<?php echo htmlspecialchars($values['linkphrase'], ENT_QUOTES); ?>" />
        </p>
        
		<p><input type="checkbox" name="widget_stray_quotes[<?php echo $number; ?>][sequence]" value="Y" <?php echo $random_selected; ?> /><label><strong>Random</strong><span class="setting-description"><small> leave unckecked to load the quotes in order beginning from a random one.</small></span></label></p>
<?php }
    
}

$gdm = new stray_widgets();
add_action('widgets_init', array($gdm, 'init'));

?>