<?php

/**
 * This class handles common logic for all XV_RandomQuotes admin stuff
 *
 * @author xavivars
 */
class XV_RandomQuotes_AdminBase {

    public function get_widgets_url() {
        return admin_url('widgets.php');
    }
    
    public function get_stray_tools_url() {
        return admin_url('admin.php?page=stray_tools');
    }
    
    public function get_stray_options_url() {
        return admin_url('admin.php?page=stray_quotes_options');
    }
	
    public function get_stray_manage_url() {
        return admin_url('admin.php?page=stray_manage');
    }
}
