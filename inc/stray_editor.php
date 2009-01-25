<?php

//new and edit page
function stray_editor() {

	global $wpdb;
	

	//decode and intercept
	foreach($_POST as $key => $val) {
		$_POST[$key] = stripslashes($val);
	}	

	// Messages for the user
	$debugText = '';
	$messages = '';
	
	// Global variable cleanup. 
	$edit = $create = $save = $delete = false;
			
	// How to control the app
	$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
	$quoteID = !empty($_REQUEST['quoteID']) ? $_REQUEST['quoteID'] : '';
		
	//handle actions	
	if ( $action == 'add' ) {
	
		$quote = !empty($_REQUEST['quote_quote']) ? $_REQUEST['quote_quote'] : '';
		$author = !empty($_REQUEST['quote_author']) ? $_REQUEST['quote_author'] : '';
		$source = !empty($_REQUEST['quote_source']) ? $_REQUEST['quote_source'] : '';
		$group = !empty($_REQUEST['quote_group']) ? $_REQUEST['quote_group'] : '';
		$visible = !empty($_REQUEST['quote_visible']) ? $_REQUEST['quote_visible'] : '';
	
		if ( ini_get('magic_quotes_gpc') )	{
		
			$quote = stripslashes($quote);
			$author = stripslashes($author);
			$source = stripslashes($source);
			$group = stripslashes($group);
			$visible = stripslashes($visible);	
		}	
		
		$sql = "insert into " . WP_STRAY_QUOTES_TABLE
		. " set quote='" . mysql_real_escape_string($quote) 
		. "', author='" . mysql_real_escape_string($author) 
		. "', source='" . mysql_real_escape_string($source) 
		. "', group='" . mysql_real_escape_string($group) 
		. "', visible='" . mysql_real_escape_string($visible) . "'";	     
		$wpdb->get_results($sql);
		
		$sql2 = "select quoteID from " . WP_STRAY_QUOTES_TABLE
		. " where quote='" . mysql_real_escape_string($quote) 
		. "' and author='" . mysql_real_escape_string($author) 
		. "' and source='" . mysql_real_escape_string($source) 
		. "' and group='" . mysql_real_escape_string($group) 
		. "' and visible='" . mysql_real_escape_string($visible) . "' limit 1";
		$result = $wpdb->get_results($sql2);
		
		if ( empty($result) || empty($result[0]->quoteID) )	{
			?><div class="error"><p><?php echo __(
			'<strong>Failure:</strong> Something went wrong when trying to insert the quote. Try again?',
			'stray-quotes'); ?></p></div><?php				
		}
			
		else {
			?><div class="updated"><p><?php echo str_replace("%s",$result[0]->quoteID,__(
			'Quote id %s successfully added to the database.','stray-quotes')); ?></p></div><?php			
		}
	}
	
	else if ( $action == 'edit_save' ) {
	
		$quote = !empty($_REQUEST['quote_quote']) ? $_REQUEST['quote_quote'] : '';	
		$author = !empty($_REQUEST['quote_author']) ? $_REQUEST['quote_author'] : '';
		$source = !empty($_REQUEST['quote_source']) ? $_REQUEST['quote_source'] : '';
		$group = !empty($_REQUEST['quote_group']) ? $_REQUEST['quote_group'] : '';
		$visible = !empty($_REQUEST['quote_visible']) ? $_REQUEST['quote_visible'] : '';
		
		if ( ini_get('magic_quotes_gpc') )	{
		
			$quote = stripslashes($quote);
			$author = stripslashes($author);
			$source = stripslashes($source);
			$group = stripslashes($group);
			$visible = stripslashes($visible);	
		}
		
		if ( empty($quoteID) )	{
			?><div class="error"><p><?php echo __(
			'<strong>Failure:</strong> No quote ID given.','stray-quotes') ?></p></div><?php
		}
		
		else {		
			$sql = "update " . WP_STRAY_QUOTES_TABLE 
			. " set quote='" . mysql_real_escape_string($quote)
			. "', author='" . mysql_real_escape_string($author) 
			. "', source='" . mysql_real_escape_string($source) 
			. "', group='" . mysql_real_escape_string($group)
			. "', visible='" . mysql_real_escape_string($visible) 
			. "' where quoteID='" . mysql_real_escape_string($quoteID) . "'";		     
			$wpdb->get_results($sql);
			
			$sql = "select quoteID from " . WP_STRAY_QUOTES_TABLE 
			. " where quote='" . mysql_real_escape_string($quote) 
			. "' and author='" . mysql_real_escape_string($author) 
			. "' and source='" . mysql_real_escape_string($source) 
			. "' and group='" . mysql_real_escape_string($group) 
			. "' and visible='" . mysql_real_escape_string($visible) . "' limit 1";
			$result = $wpdb->get_results($sql);
			
			if ( empty($result) || empty($result[0]->quoteID) )	{			
				?><div class="error"><p><?php echo __(
				'<strong>Failure:</strong> Something went wrong. Try again?','stray-quotes') ?></p></div><?php				
			}
			else {			
				?><div class="updated"><p><?php echo str_replace("%s",$quoteID,__(
				'Quote %s updated successfully.','stray-quotes'));?></p></div><?php
			}		
		}
	}
	
	else if ( $action == 'delete' ) {
	
		if ( empty($quoteID) ) {
			
			
			?><div class="error"><p><?php echo __(
			'<strong>Failure:</strong> No quote ID given. Nothing deleted.','stray-quotes') ?></p></div><?php		
		}
			
		else {
		
			$sql = "delete from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "'";
			$wpdb->get_results($sql);
			
			$sql = "select quoteID from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "'";
			$result = $wpdb->get_results($sql);
			
			if ( empty($result) || empty($result[0]->quoteID) )	{			
				?><div class="updated"><p><?php echo str_replace("%s",$quoteID,__(
				'Quote %s deleted successfully','stray-quotes')); ?></p></div><?php
			}			
			else {						
				?><div class="error"><p><?php echo __(
				'<strong>Failure:</strong> Nothing deleted.','stray-quotes'); ?></p></div><?php	
			}		
		}
	}
	
	//edit form
	?><div class="wrap"><?php
		
	//if the page is opened after a edit action, shows only the form
	if ( $action == 'edit' ) {
	
		//edit form
		?><h2>Edit Quote</h2><?php		
		
		//check if something went wrong with quote id
		if ( empty($quoteID) ) {
			?><div class="error"><p><?php echo __(
			'Something is wrong. No quote ID from the query string.','stray-quotes') ?></p></div><?php
		}
		
		else {			
			
			//query
			$data = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" 
			. mysql_real_escape_string($quoteID) . "' limit 1");
			if ( empty($data) ) {
				?><div class="error"><p><?php echo __(
				'Something is wrong. I can\'t find a quote linked up with that ID.','stray-quotes') ?></p></div><?php
				return;
			}
			$data = $data[0];
			
			//encode strings
			if ( !empty($data) ) $quote = htmlspecialchars($data->quote); 
			if ( !empty($data) ) $author = htmlspecialchars($data->author);
			if ( !empty($data) ) $source = htmlspecialchars($data->source);
			if ( !empty($data) ) $group = htmlspecialchars($data->group);
			
			//set visibility
			$defaultVisible = get_option ('stray_quotes_default_visible');
			if ( empty($data)){				
				if  ($defaultVisible == 'Y') {			
					$visible_yes = "checked";
					$visible_no = "";
				}
				else {
					$visible_yes = "";
					$visible_no = "checked";				
				}				
			}
			else {			
				if ( $data->visible=='yes' ) {
					$visible_yes = "checked";
					$visible_no = "";
				}
				else {
					$visible_yes = "";
					$visible_no = "checked";				
				}		
			}			
			
			//make the edit form
			?>
            <div class="stray_form">
            <form name="quoteform" id="quoteform" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?> ">
				<input type="hidden" name="action" value="edit_save">
				<input type="hidden" name="quoteID" value="<?php echo $quoteID; ?>">
			
				<fieldset class="small"><legend><?php echo __('Quote:','stray-quotes') ?> </legend>
					<textarea name="quote_quote" class="stray_textarea" cols=78 rows=7><?php echo $quote; ?></textarea>
				</fieldset>
				
				<fieldset class="small"><legend><?php echo __('Author:','stray-quotes') ?> </legend>
					<input type="text" name="quote_author" size=80
					value="<?php echo $author ?>" />
				</fieldset>

				<fieldset class="small"><legend><?php echo __('Source:','stray-quotes') ?> </legend>
					<input type="text" name="quote_source" size=80
					value="<?php echo $source ?>" />
				</fieldset>
                
				<fieldset class="small"><legend><?php echo __('Group:','stray-quotes') ?> </legend>
					<input type="text" name="quote_group" size=80
					value="<?php echo $group ?>" />
				</fieldset>				
                
				<fieldset class="small"><legend><?php echo __('Visible:','stray-quotes') ?> 
					<input type="radio" name="quote_visible" class="input" value="yes"<?php echo $visible_yes ?> /> <?php echo __('Yes','stray-quotes') ?>					
					<input type="radio" name="quote_visible" class="input" value="no"<?php echo $visible_no ?> /> <?php echo __('No','stray-quotes') ?></legend>
				</fieldset>
				<p align="right"><a href="<?php echo (get_option('siteurl'))?>/wp-admin/edit.php?page=stray_quotes.php">
                &laquo; <?php echo __('go back to the list of quotes','stray-quotes') ?></a>&nbsp;
                <input type="submit" name="save" class="button bold" value="<?php echo __('Save quote','stray-quotes') ?> &raquo;" />
                </p>				
			</form></div></div><?php 
	
		}	
	}	
	
	//in all the other cases shows the form and the list
	//it is debatable whether this form should show empty fields or values from the last insert
	else {
	
		?><h2>Stray Random Quotes -  <?php echo __('Add new','stray-quotes') ?></h2><?php 	
		    
			$quoteID=false;
			$data = false;	
			if ( $quoteID !== false ) {
		
				if ( intval($quoteID) != $quoteID ) {		
					?><div class="error"><p><?php echo __('The Quote ID seems to be invalid.','stray-quotes') ?></p></div><?php
					return;
				}
				else {
					$data = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "' limit 1");
					if ( empty($data) ) {
						?><div class="error"><p><?php echo __(
						'Something is wrong. I can\'t find a quote connected to that ID.','stray-quotes') ?></p></div><?php
						return;
					}
					$data = $data[0];
				}	
			}		
			if ( !empty($data) ) $quote = htmlspecialchars($data->quote); 
			if ( !empty($data) ) $author = htmlspecialchars($data->author);
			if ( !empty($data) ) $source = htmlspecialchars($data->source);
			if ( !empty($data) ) $group = htmlspecialchars($data->group);
			
			$defaultVisible = get_option ('stray_quotes_default_visible');
			if ( empty($data)){				
				if  ($defaultVisible == 'Y') {			
					$visible_yes = "checked";
					$visible_no = "";
				}
				else {
					$visible_yes = "";
					$visible_no = "checked";				
				}				
			}
			else {			
				if ( $data->visible=='yes' ) {
					$visible_yes = "checked";
					$visible_no = "";
				}
				else {
					$visible_yes = "";
					$visible_no = "checked";				
				}		
			}			
			
			//make the edit form
			?><div class="stray_form">
            <form name="quoteform" id="quoteform" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<input type="hidden" name="action" value="add">
				<input type="hidden" name="quoteID" value="">
			
				<fieldset class="small"><legend><?php echo __('Quote:','stray-quotes') ?> </legend>
					<textarea name="quote_quote" class="stray_textarea" cols=78 rows=7></textarea>
				</fieldset>
				
				<fieldset class="small"><legend><?php echo __('Author:','stray-quotes') ?> </legend>
					<input type="text" name="quote_author" size=80
					value="" />
				</fieldset>

				<fieldset class="small"><legend><?php echo __('Source:','stray-quotes') ?> </legend>
					<input type="text" name="quote_source" size=80
					value="" />
				</fieldset>
                
				<fieldset class="small"><legend><?php echo __('Group:','stray-quotes') ?> </legend>
					<input type="text" name="quote_group" size=80
					value="" />
				</fieldset>				
				
				<fieldset class="small"><legend><?php echo __('Visible:','stray-quotes') ?> 
					<input type="radio" name="quote_visible" class="input" value="yes"<?php echo $visible_yes ?> /> 
					<?php echo __('Yes','stray-quotes') ?>					
					<input type="radio" name="quote_visible" class="input" value="no"<?php echo $visible_no ?> /> 
					<?php echo __('No','stray-quotes') ?></legend>
				</fieldset>
				<p align="right"><input type="submit" name="save" class="button bold" 
                value="<?php echo __('Save quote','stray-quotes') ?> &raquo;" /></p>				
			</form></div></div>
			
			
	</div>
    
    <?php }
}


?>
