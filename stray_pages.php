<?php

//options page
function stray_quotes_options () {

	global $wpdb;	

	//decode and intercept
	foreach($_POST as $key => $val) {
		$_POST[$key] = stripslashes($val);
	}
	
	if(!empty($_POST['update'])) {

		update_option('stray_quotes_regular_title', $_POST['regular_title']);		
		update_option('stray_quotes_widget_title', $_POST['widget_title']);	
		update_option('stray_quotes_before_all', $_POST['before_all']);		
		update_option('stray_quotes_after_all', $_POST['after_all']);
		update_option('stray_quotes_before_quote', $_POST['before_quote']);	
		update_option('stray_quotes_after_quote', $_POST['after_quote']);	
		update_option('stray_quotes_before_author', $_POST['before_author']);	
		update_option('stray_quotes_after_author', $_POST['after_author']);
		update_option('stray_quotes_before_source', $_POST['before_source']);	
		update_option('stray_quotes_after_source', $_POST['after_source']);
		update_option('stray_quotes_put_quotes_first', $_POST['put_quotes_first']);
		update_option('stray_quotes_use_google_links', $_POST['use_google_links']);
		update_option('stray_quotes_default_visible', $_POST['default_visible']);
				
		echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';	
	}

	//get options
	$regularTitle =  get_option('stray_quotes_regular_title');
	$widgetTitle = get_option('stray_quotes_widget_title');
	$beforeAll =  get_option ('stray_quotes_before_all');
	$afterAll = get_option ('stray_quotes_after_all');
	$beforeQuote = get_option ('stray_quotes_before_quote');
	$afterQuote = get_option ('stray_quotes_after_quote');
	$beforeAuthor = get_option ('stray_quotes_before_author');
	$afterAuthor = get_option ('stray_quotes_after_author');
	$beforeSource = get_option ('stray_quotes_before_source');
	$afterSource = get_option ('stray_quotes_after_source');
	$putQuotesFirst = get_option ('stray_quotes_put_quotes_first');
	$useGoogleLinks = get_option ('stray_quotes_use_google_links');
	$defaultVisible = get_option ('stray_quotes_default_visible');
		
	if ( $putQuotesFirst == 'Y' ) $putQuotesFirst_selected = 'checked';
	if ( $useGoogleLinks == 'Y' ) $useGoogleLinks_selected = 'checked';
	if ( $defaultVisible == 'Y' ) $defaultVisible_selected = 'checked';
	
	//build the option form
	?>    
    <div style="width:100%; margin:auto">	
	<div class="wrap"><br/><h2>Stray Quotes - Options</h2><div id="stray_quotes_options">
	These options can be used to customize the appearance of the quotes.<br/><br/>

	<form name="frm_options" method="post" action="<?php echo ($_SERVER['REQUEST_URI']); ?>">
	
	<fieldset><legend>Title</legend>
    	
        <p><strong>Widget</strong>
        <input type="text" size="50" name="widget_title" value="<?php echo ($widgetTitle); ?>" class="stray_text" /><br />
        <div class="stray_desc">This is valid for the widget functionality only. Leave empty for no title.
        It can also be changed from the <a href="<?php get_settings('siteurl'); ?>/wp-admin/widgets.php">widget page</a>. 
		Formatting of this element is pre-determined by 
        the template and shouldn't be inserted here. If you want to use a title with customized formatting elements, 
        leave this empty and use the option below instead.</div></p>
    	<p></p>
        
        <p><strong>Sidebar element</strong>
        <input type="text" size="50" name="regular_title" value="<?php echo (htmlentities($regularTitle)); ?>" class="stray_text" /><br />
        <div class="stray_desc">This is valid only when the widget functionality is not being used or when the widget title (option above) is left empty. 
		Leave empty for no title.<br/></div></p>
    
	</fieldset><p></p>
	
	<fieldset><legend>Author, Quote and Source</legend>
    
        <p><strong>Before</strong>
        <input type="text" size="50" name="before_all" value="<?php echo (htmlentities($beforeAll)); ?>" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or open tags that go before the group made by quote source and author. 
        It comes after the title. Won't be considered when spewing all the quotes onto a page.</div></p>
        <p></p>       
        <p><strong>After</strong>
        <input type="text" size="50" name="after_all" value="<?php echo (htmlentities($afterAll)); ?>" class="stray_text" /><br />
        <div class="stray_desc">Here you enter elements that go right after the group made by quote source and author,  
        or close the tags that you opened before it.
        Won't be considered when spewing all the quotes onto a page.<br/></div></p>
    
	</fieldset><p></p>
	
	<fieldset><legend>Quote only</legend>
    
        <p><strong>Before</strong>
        <input type="text" size="50" name="before_quote" value="<?php echo (htmlentities($beforeQuote)); ?>" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or open tags that go before the quote. 
        This will be considered when spewing all the quotes onto a page.</div></p>
        <p></p>        
        <p><strong>After</strong>
        <input type="text" size="50" name="after_quote" value="<?php echo (htmlentities($afterQuote)); ?>" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or close the tags that you opened before the quote.
        This will be considered when spewing all the quotes onto a page.<br/></div></p>
    
    </fieldset><p></p>
	
	<fieldset><legend>Author only</legend>
    
        <p><strong>Before</strong>
        <input type="text" size="50" name="before_author" value="<?php echo (htmlentities($beforeAuthor)); ?>" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or open tags that go before the author.
        This will be considered when spewing all the quotes onto a page.</div></p>
        <p></p>        
        <p><strong>After</strong>
        <input type="text" size="50" name="after_author" value="<?php echo (htmlentities($afterAuthor)); ?>" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or close the tags that you put before the author.
        This will be considered when spewing all the quotes onto a page.<br/></div></p>
        
	</fieldset><p></p>

	<fieldset><legend>Source only</legend>
    
        <p><strong>Before</strong>
        <input type="text" size="50" name="before_source" value="<?php echo (htmlentities($beforeSource)); ?>" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or open tags that go before the source of the quote.
        This will be considered when spewing all the quotes onto a page.</div></p>
        <p></p>        
        <p><strong>After</strong>
        <input type="text" size="50" name="after_source" value="<?php echo (htmlentities($afterSource)); ?>" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or close the tags that you put before the source of the quote.
        This will be considered when spewing all the quotes onto a page.<br/></div></p>
        
	</fieldset><p></p>

	<fieldset><legend>Other options</legend>
    
        <p><strong>Add google links:</strong> 
        <input type="checkbox" name="use_google_links" value="Y" <?php echo ($useGoogleLinks_selected); ?>>
        <br/><div class="stray_desc">Adds a google link to the Author element.
        This will be considered when spewing all the quotes onto a page.</div></p>
        <p></p>        
        <p><strong>Put quote before the author:</strong> 
        <input type="checkbox" name="put_quotes_first" value="Y" <?php echo ($putQuotesFirst_selected); ?>>
        <br/><div class="stray_desc">If checked, returns the quote before the author. Otherwise, the author comes first.
        This won't be considered when spewing all the quotes onto a page (quote will always come first).</div></p>
        <p></p>        
        <p><strong>Visible by default:</strong> 
        <input type="checkbox" name="default_visible" value="Y" <?php echo ($defaultVisible_selected); ?>>
        <br/><div class="stray_desc">If checked, will set "Visible" to "Yes" for all new quotes.<br/></div></p>
    
	</fieldset>
	
	<input type="hidden" name="update" value="yes" /><br/>
	<p class="submit" align="right"><input type="submit" value="Update Options &raquo;" />
	<br/></p>
	</form>
	</div><div class="wrap" style="margin-top:15px; padding:12px; margin:auto; text-align:center;"><ul>
    <li style="display:inline;list-style:none"><a href="http://www.italyisfalling.com/stray-quotes">Plugin's Homepage</a> | </li>
    <!--<li style="line-height:1em;display:inline;list-style:none;letter-spacing:0.5%;">Donate | </li>-->
    <li style="display:inline;list-style:none;"><a href="http://www.italyisfalling.com/coding">Other plugins</a></li>
    </ul>
    </div>
}

</div> 
	
<?php }

//manage page
function stray_quotes_manage() {

	global $wpdb;
	
	$first_time = get_option('stray_quotes_first_time');
	if ($first_time == 1) {
	
		$wpdb->query( "INSERT INTO `" . WP_STRAY_QUOTES_TABLE . "` (quote, author, source, visible) values ('And strange it is / That nature must compel us to lament / Our most persisted deeds.', 'William Shakespeare', 'Antony and Cleopatra', 'yes') ");
		
		?><div class="updated fade"><p>Hey. Welcome to <strong>Stray Quotes.</strong><br />
		This seems to be your first time visiting this page. 
		I just created the database table "<?php echo WP_STRAY_QUOTES_TABLE; ?>" to store your quotes, 
		and added one to start you off.<br />
		Check out the 
        <a href="<?php get_settings('siteurl') ?>/wp-admin/options-general.php?page=stray_quotes.php"> Options Page</a> too. Happy quoting!</div><?php
		
		update_option('stray_quotes_first_time', 3);
	}
	
	else if ($first_time == 2) {
	
		?><div class="updated fade"><p>Hey. Welcome to <strong>Stray Quotes.</strong><br />
		I just renamed the old tables of quotes "<?php echo WP_QUOTES_TABLE; ?>" as "<?php echo WP_STRAY_QUOTES_TABLE; ?>".<br />
		All your quotes are still there. As you can see the new table comes with all your old quotes and a new optional field, "source". <br />
		Check out the <a href="<?php get_settings('siteurl')?>/wp-admin/options-general.php?page=stray_quotes.php"> Options Page</a> too. Happy quoting!</div>';	
	<?php }

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
		$visible = !empty($_REQUEST['quote_visible']) ? $_REQUEST['quote_visible'] : '';
	
		if ( ini_get('magic_quotes_gpc') )	{
		
			$quote = stripslashes($quote);
			$author = stripslashes($author);
			$source = stripslashes($source);
			$visible = stripslashes($visible);	
		}	
		
		$sql = "insert into " . WP_STRAY_QUOTES_TABLE . " set quote='" . mysql_real_escape_string($quote) 
			 . "', author='" . mysql_real_escape_string($author) . "', source='" . mysql_real_escape_string($source) 
			 . "', visible='" . mysql_real_escape_string($visible) . "'";	     
		$wpdb->get_results($sql);
		
		$sql = "select quoteID from " . WP_STRAY_QUOTES_TABLE . " where quote='" . mysql_real_escape_string($quote) . "'"
			 . " and author='" . mysql_real_escape_string($author) . "' and source='" . mysql_real_escape_string($source)
			 . "' and visible='" . mysql_real_escape_string($visible) . "' limit 1";
		$result = $wpdb->get_results($sql);
		
		if ( empty($result) || empty($result[0]->quoteID) )	{
		
				?><div class="error"><p><strong>Failure:</strong> 
				Something went wrong when trying to insert the quote. Try again? </p></div><?php				
		}
			
		else {
			?><div class="updated"><p>Quote id <?php echo ($result[0]->quoteID); ?> successfully added to the database.</p></div><?php
			
		}
	}
	
	else if ( $action == 'edit_save' ) {
	
		$quote = !empty($_REQUEST['quote_quote']) ? $_REQUEST['quote_quote'] : '';	
		$author = !empty($_REQUEST['quote_author']) ? $_REQUEST['quote_author'] : '';
		$source = !empty($_REQUEST['quote_source']) ? $_REQUEST['quote_source'] : '';
		$visible = !empty($_REQUEST['quote_visible']) ? $_REQUEST['quote_visible'] : '';
		
		if ( ini_get('magic_quotes_gpc') )	{
		
			$quote = stripslashes($quote);
			$author = stripslashes($author);
			$source = stripslashes($source);
			$visible = stripslashes($visible);	
		}
		
		if ( empty($quoteID) )	{
			?><div class="error"><p><strong>Failure:</strong> No quote ID given.</p></div><?php
		}
		
		else {		
			$sql = "update " . WP_STRAY_QUOTES_TABLE . " set quote='" . mysql_real_escape_string($quote)
				 . "', author='" . mysql_real_escape_string($author) . "', source='" . mysql_real_escape_string($source) 
				 . "', visible='" . mysql_real_escape_string($visible) . "'"
				 . " where quoteID='" . mysql_real_escape_string($quoteID) . "'";		     
			$wpdb->get_results($sql);
			
			$sql = "select quoteID from " . WP_STRAY_QUOTES_TABLE . " where quote='" . mysql_real_escape_string($quote) . "'"
				 . " and author='" . mysql_real_escape_string($author) . "' and source='" . mysql_real_escape_string($source) 
				 . "' and visible='" . mysql_real_escape_string($visible) . "' limit 1";
			$result = $wpdb->get_results($sql);
			
			if ( empty($result) || empty($result[0]->quoteID) )	{			
				?><div class="error"><p><strong>Failure:</strong> Something went wrong. Try again?<?php
				
			}
			else {			
				?><div class="updated"><p>Quote <?php echo $quoteID ?> updated successfully.<?php
			}		
		}
	}
	
	else if ( $action == 'delete' ) {
	
		if ( empty($quoteID) ) {
		
			?><div class="error"><p><strong>Failure:</strong> No quote ID given. Nothing deleted.</p></div><?php		
		}
			
		else {
		
			$sql = "delete from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "'";
			$wpdb->get_results($sql);
			
			$sql = "select quoteID from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "'";
			$result = $wpdb->get_results($sql);
			
			if ( empty($result) || empty($result[0]->quoteID) )	{
			
				echo '<div class="updated"><p>Quote <?php echo $quoteID ?> deleted successfully</p></div>';
			}
			
			else {
						
				echo '<div class="error"><p><strong>Failure:</strong> Nothing deleted.</p></div>';
	
			}		
		}
	}
	
	//edit form
	?><div style="width:100%; margin:auto"><div class="wrap"><?php
		
	//if the page is opened after a edit action, shows only the form
	if ( $action == 'edit' ) {
	
		//edit form
		?><h2><br/>Edit Quote</h2><?php		
		
		//check if something went wrong with quote id
		if ( empty($quoteID) ) {
			?><div class="error"><p>Something is wrong. I don't get a quote ID from the query string.</p></div><?php
		}
		
		else {			
			
			//query
			$data = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "' limit 1");
			if ( empty($data) ) {
				?><div class="error"><p>Something is wrong. I can't find a quote linked up with that ID.</p></div><?php
				return;
			}
			$data = $data[0];
			
			//encode strings
			if ( !empty($data) ) $quote = htmlspecialchars($data->quote); 
			if ( !empty($data) ) $author = htmlspecialchars($data->author);
			if ( !empty($data) ) $source = htmlspecialchars($data->source);
			
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
			?><div class="stray_form"><form name="quoteform" id="quoteform" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<input type="hidden" name="action" value="edit_save">
				<input type="hidden" name="quoteID" value="<?php echo $quoteID; ?>">
			
				<fieldset class="small"><legend>Quote: </legend>
					<textarea name="quote_quote" class="stray_textarea" cols=78 rows=7><?php echo $quote; ?></textarea>
				</fieldset>
				
				<fieldset class="small"><legend>Author: </legend>
					<input type="text" name="quote_author" class="stray_textedit" size=80
					value="<?php echo $author ?>" />
				</fieldset>

				<fieldset class="small"><legend>Source: </legend>
					<input type="text" name="quote_source" class="stray_textedit" size=80
					value="<?php echo $source ?>" />
				</fieldset>
				
				<fieldset class="small"><legend>Visible: </legend>
					<input type="radio" name="quote_visible" class="input" value="yes"<?php echo $visible_yes ?> /> Yes					
					<input type="radio" name="quote_visible" class="input" value="no"<?php echo $visible_no ?> /> No
				</fieldset>
				<p align="right"><a href="<?php echo (get_option('siteurl'))?>/wp-admin/edit.php?page=stray_quotes.php">&laquo; go back to the list of quotes</a>&nbsp;
                <input type="submit" name="save" class="button bold" value="Save quote &raquo;" />
                </p>
				</div>
			</form><?php 
	
		}	
	}	
	
	//in all the other cases shows the form and the list
	//it is debatable whether this form should show empty fields or values from the last insert
	else {
	
		?><h2><br/>Stray Quotes -  Add new</h2><?php 	
		    
			$quoteID=false;
			$data = false;	
			if ( $quoteID !== false ) {
		
				if ( intval($quoteID) != $quoteID ) {		
					?><div class="error"><p>The Quote ID seems to be invalid.</p></div><?php
					return;
				}
				else {
					$data = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "' limit 1");
					if ( empty($data) ) {
						?><div class="error"><p>Something is wrong. I can't find a quote linked up with that ID.</p></div><?php
						return;
					}
					$data = $data[0];
				}	
			}		
			if ( !empty($data) ) $quote = htmlspecialchars($data->quote); 
			if ( !empty($data) ) $author = htmlspecialchars($data->author);
			if ( !empty($data) ) $source = htmlspecialchars($data->source);
			
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
			?><div class="stray_form"><form name="quoteform" id="quoteform" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<input type="hidden" name="action" value="add">
				<input type="hidden" name="quoteID" value="">
			
				<fieldset class="small"><legend>Quote: </legend>
					<textarea name="quote_quote" class="stray_textarea" cols=78 rows=7></textarea>
				</fieldset>
				
				<fieldset class="small"><legend>Author: </legend>
					<input type="text" name="quote_author" class="stray_textedit" size=80
					value="" />
				</fieldset>

				<fieldset class="small"><legend>Source: </legend>
					<input type="text" name="quote_source" class="stray_textedit" size=80
					value="" />
				</fieldset>
				
				<fieldset class="small"><legend>Visible: </legend>
					<input type="radio" name="quote_visible" class="input" value="yes"<?php echo $visible_yes ?> /> Yes					
					<input type="radio" name="quote_visible" class="input" value="no"<?php echo $visible_no ?> /> No
				</fieldset>
				<p align="right"><input type="submit" name="save" class="button bold" value="Save quote &raquo;" /></p>
				</div>
			</form><?php
			
			
			
			//list of existing quotes			
			
			$orderBY = 'quoteID';			
			?><div class="wrap"><br/><h2>Stray Quotes - Manage</h2><?php
			$quotes = $wpdb->get_results("SELECT * FROM " . WP_STRAY_QUOTES_TABLE . " order by ". $orderBY);
			if ( !empty($quotes) ) {
				?><script language="JavaScript"><!--
					function mTsetAction(obj, action) {
					obj.action.value = action;
					obj.submit();
					}
					//--></script>
					
					<div class="stray_list">
                    <table width="100%" cellpadding="3" cellspacing="3">
                        <tr>
                            <th scope="col"> ID </th>
                            <th scope="col"> Quote </th>
                            <th scope="col"> Author </th>
                            <th scope="col"> Source </th>
                            <th scope="col"> Visible </th>
                            <th scope="col">&nbsp;</th>
                            <th scope="col">&nbsp;</th>
                        </tr><?php
				
				$i = 0;	
				foreach ( $quotes as $quote ) {
				
					$alt = ($i % 2 == 0) ? ' class="alternate"' : '';
			
					?> <tr <?php echo($alt); ?>>
						
						<th scope="row"><?php echo ($quote->quoteID); ?></th>
						<td><?php echo(nl2br($quote->quote)); ?></td>
						<td><?php echo($quote->author); ?></td>
						<td><?php echo($quote->source); ?></td>
						<td align="center"><?php echo($quote->visible); ?></td>
											
						<td align="center">
                        <a href="<?php echo (get_option('siteurl'))?>/wp-admin/edit.php?page=stray_quotes.php&action=edit&quoteID=<?php echo($quote->quoteID); ?>">
                        Edit</a></td>
		
						<td align="center">
                           <a href="<?php echo (get_option('siteurl'))?>/wp-admin/edit.php?page=stray_quotes.php&action=delete&quoteID=<?php echo($quote->quoteID); ?>"
                        onclick="if ( confirm('You are about to delete this quote\n  \'Cancel\' to stop, \'OK\' to delete.') ) { return true;}return false;">
                        Delete</a></td>			
					</tr>
					<?php $i++; 
				} ?>
				</table></div>			
			<?php } else { ?><p> You haven't entered any quotes yet. </p><?php	}
	} ?>
	<div style="margin-top:15px; padding:12px; border-top:#CCCCCC 1px solid; margin:auto; text-align:center;"><ul>
    <li style="display:inline;list-style:none"><a href="http://www.italyisfalling.com/stray-quotes">Plugin's Homepage</a> | </li>
    <!--<li style="line-height:1em;display:inline;list-style:none;letter-spacing:0.5%;">Donate | </li>-->
    <li style="display:inline;list-style:none;"><a href="http://www.italyisfalling.com/coding">Other plugins</a></li>
    </ul>
    </div></div></div> <?php
}

?>