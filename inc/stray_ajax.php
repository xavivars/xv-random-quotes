<?php

add_action( 'wp_enqueue_scripts', 'xv_random_quotes_add_js' );

//add ajax script
function xv_random_quotes_add_js() {
    $quotesoptions = get_option('stray_quotes_options');
    if ($quotesoptions['stray_ajax'] !='Y') {
        wp_enqueue_script('stray_ajax.js', plugins_url('stray_ajax.js', __FILE__), array('jquery'));
        
        wp_localize_script( 'stray_ajax.js', 
            'xv_random_quotes', 
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ))
        );
    }
}

add_action( 'wp_ajax_xv_random_quotes_new_quote', 'xv_random_quotes_new_quote' );
add_action( 'wp_ajax_nopriv_xv_random_quotes_new_quote', 'xv_random_quotes_new_quote' );
function xv_random_quotes_new_quote() {
	global $wpdb;
	
    if($_POST['xv_random_quote_action'] == 'newquote'){

        $categories = isset($_POST['categories'])?sanitize_text_field($_POST['categories']):'';
        $sequence = isset($_POST['sequence'])?sanitize_text_field($_POST['sequence']):'';
        $linkphrase = isset($_POST['linkphrase'])?sanitize_text_field($_POST['linkphrase']):'';
        $widgetid = isset($_POST['widgetid'])?sanitize_text_field($_POST['widgetid']):'';
        $multi = isset($_POST['multi'])?sanitize_text_field($_POST['multi']):'';
        $offset = isset($_POST['offset'])?sanitize_text_field($_POST['offset']):'';
        $timer = isset($_POST['timer'])?sanitize_text_field($_POST['timer']):'';
        $sort = isset($_POST['sort'])?sanitize_text_field($_POST['sort']):'';
        $orderby = isset($_POST['orderby'])?sanitize_text_field($_POST['orderby']):'';
        $disableaspect = isset($_POST['disableaspect'])?sanitize_text_field($_POST['disableaspect']):'';
        $contributor = isset($_POST['contributor'])?sanitize_text_field($_POST['contributor']):'';
        
        ob_clean();
        $output = get_stray_quotes($categories,$sequence,$linkphrase,$multi,$timer,false,$offset,$widgetid,false,$orderby,$sort,'',$disableaspect,$contributor);
        
        $response = array(
           'what'=>'quote',
           'action'=>'get-new-quote',
           'id'=>'1',
           'data'=> $output
        );
        $xmlResponse = new WP_Ajax_Response($response);
        $xmlResponse->send();
        
    }
	wp_die();
}



