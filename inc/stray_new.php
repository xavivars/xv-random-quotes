<?php

function stray_new() {	
	
	
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

	if ( $action == 'add' ) {
	
		$quote = !empty($_REQUEST['quote_quote']) ? $_REQUEST['quote_quote'] : '';
		$author = !empty($_REQUEST['quote_author']) ? $_REQUEST['quote_author'] : '';
		$source = !empty($_REQUEST['quote_source']) ? $_REQUEST['quote_source'] : '';
		$visible = !empty($_REQUEST['quote_visible']) ? $_REQUEST['quote_visible'] : '';
		
		if ($_REQUEST['quote_group'])$group = $_REQUEST['quote_group'];
		else $group = $_REQUEST['groups'];
	
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
		. "', `group`='" . mysql_real_escape_string($group)
		. "', visible='" . mysql_real_escape_string($visible) . "'";	     
		$wpdb->get_results($sql);
		
		$sql2 = "select quoteID from " . WP_STRAY_QUOTES_TABLE
		. " where quote='" . mysql_real_escape_string($quote) 
		. "' and author='" . mysql_real_escape_string($author) 
		. "' and source='" . mysql_real_escape_string($source) 
		. "' and `group`='" . mysql_real_escape_string($group) 
		. "' and visible='" . mysql_real_escape_string($visible) . "' limit 1";
		$result = $wpdb->get_results($sql2);
		
		//failure message
		if ( empty($result) || empty($result[0]->quoteID) )	{
			?><div class="error fade"><p><?php echo __(
			'<strong>Failure:</strong> Something went wrong when trying to insert the quote. Try again?',
			'stray-quotes'); ?></p></div><?php				
		}
			
		//success message
		else {
			?><div class="updated fade"><p><?php 
			
			$search = array("%s1", "%s2");
			$replace = array($result[0]->quoteID, get_option("siteurl").'/wp-admin/admin.php?page=stray_manage');
			echo str_replace($search,$replace,__(
			'Quote no. <strong>%s1</strong> was added to the database. To insert it in a post use: <code>[quote id=%s1]</code>. To review use the <a href="%s2">Manage page</a>.','stray-quotes')); ?></p></div><?php			
		}
	}	
	
	//edit form
	?><div class="wrap"><h2><?php echo __('Add new quote','stray-quotes') ?></h2>
    <!--<span class="setting-description"><?php /*echo __('"A minimum of sound to a maximum of sense." ~ Mark Twain','stray-quotes')*/ ?></span>
    <br/><br/>-->
	<?php 	
		    
			$quoteID=false;
			$data = false;	
			if ( $quoteID !== false ) {
		
				if ( intval($quoteID) != $quoteID ) {		
					?><div class="error fade"><p><?php echo __('The Quote ID seems to be invalid.','stray-quotes') ?></p></div><?php
					return;
				}
				else {
					$data = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "' limit 1");
					if ( empty($data) ) {
						?><div class="error fade"><p><?php echo __(
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
			
			//load defaults
			$quotesoptions = array();
			$quotesoptions = get_option('stray_quotes_options');
		
			//set visibility
			$defaultVisible = $quotesoptions['stray_quotes_default_visible'];
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
			
			//set default group
			$defaultgroup = $quotesoptions['stray_default_group'];
			
			
			//make the edit form
			$styleborder = 'style="border:1px solid #ccc"';
			$styletextarea = 'style="border:1px solid #ccc; font-family: Times New Roman, Times, serif; font-size: 1.4em;"';
            ?><div style="width:42em">
            
			<script src="<?php echo WP_STRAY_QUOTES_PATH ?>/inc/js_quicktags-mini.js" type="text/javascript"></script>
            <form name="quoteform" id="quoteform" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?> ">
				<input type="hidden" name="action" value="add">
				<input type="hidden" name="quoteID" value="<?php echo $quoteID; ?>">
			
				<p><!--<label><?php echo __('Quote:','stray-quotes') ?></label>-->
                <script type="text/javascript">edToolbar();</script>
                <textarea id="qeditor" name="quote_quote" <?php echo $styletextarea ?> cols=68 rows=7><?php echo $quote; ?></textarea>
				<script type="text/javascript">var edCanvas = document.getElementById('qeditor');</script>
                <p class="setting-description"><small><?php echo __('* Other than the few offered in the toolbar above, many HTML and non-HTML formatting elements can be used for the quote. Lines can be broken traditionally or using <code>&lt;br/&gt;</code>, etcetera.','stray-quotes'); ?></small></p></p>
                
				<p><label><?php echo __('Author:','stray-quotes') ?></label>
                <input type="text" id="aeditor" name="quote_author" size=58 value="" <?php echo $styleborder ?> />
				<script type="text/javascript">edToolbar1();</script>
                <script type="text/javascript">var edCanvas1 = document.getElementById('aeditor');</script><br />

				<label><?php echo __('Source:','stray-quotes') ?></label>
                <input type="text" id="seditor" name="quote_source" size=58 value="" <?php echo $styleborder ?> />
				<script type="text/javascript">edToolbar2();</script>
                <script type="text/javascript">var edCanvas2 = document.getElementById('seditor');</script>
                <p class="setting-description"><small><?php echo __('* By adding a link to the author or the source, the default links specified on the settings page are ignored. Make sure the link is closed by a <code>&lt;/a&gt;</code> tag.','stray-quotes'); ?></small></p></p>
				
                <p><label><?php echo __('Group:&nbsp;','stray-quotes') ?></label>
                
                <select name="groups" style="vertical-align:middle; width:17em;" > 
                <?php $grouplist = make_groups(); 
                foreach($grouplist as $groupo){ ?>
                <option value="<?php echo $groupo; ?>" style=" padding-right:5px" <?php  if ( $groupo == $defaultgroup) echo ' selected'; ?> >
                <?php echo $groupo;?></option>
                <?php } ?>   
                </select>
                  
                <label><?php echo __('new group:&nbsp;','stray-quotes') ?></label>
                <input type="text" name="quote_group" size=26 value="" maxlength="25" <?php echo $styleborder ?> /></p>
                
				<p><label><?php echo __('Visible:','stray-quotes') ?></label>
					<input type="radio" name="quote_visible" class="input" value="yes"<?php echo $visible_yes ?> /> <?php echo __('Yes','stray-quotes') ?>					
					<input type="radio" name="quote_visible" class="input" value="no"<?php echo $visible_no ?> /> <?php echo __('No','stray-quotes') ?></div>
				</p><p>&nbsp;</p>

				<p><input type="submit" name="save"  class="button-primary" value="<?php echo __('Save quote','stray-quotes') ?> &raquo;" /></p>
			</form></div>
	</div><?php	

}

?>