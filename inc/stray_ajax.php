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

        $categories = isset($_POST['categories'])?$_POST['categories']:'';
        $sequence = isset($_POST['sequence'])?$_POST['sequence']:'';
        $linkphrase = isset($_POST['linkphrase'])?$_POST['linkphrase']:'';
        $widgetid = isset($_POST['widgetid'])?$_POST['widgetid']:'';
        $multi = isset($_POST['multi'])?$_POST['multi']:'';
        $offset = isset($_POST['offset'])?$_POST['offset']:'';
        $timer = isset($_POST['timer'])?$_POST['timer']:'';
        $sort = isset($_POST['sort'])?$_POST['sort']:'';
        $orderby = isset($_POST['orderby'])?$_POST['orderby']:'';
        $disableaspect = isset($_POST['disableaspect'])?$_POST['disableaspect']:'';
        $contributor = isset($_POST['contributor'])?$_POST['contributor']:'';
        
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



