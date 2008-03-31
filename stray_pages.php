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
	echo '    
    <div style="width:100%; margin:auto">	
	<div class="wrap"><br/><h2>Stray Quotes - Options</h2><div id="stray_quotes_options">
	These options can be used to customize the appearance of the quotes.<br/><br/>

	<form name="frm_options" method="post" action="'.$_SERVER['REQUEST_URI'].'">
	
	<fieldset><legend>Title</legend>
    	
        <p><strong>Widget</strong>
        <input type="text" size="50" name="widget_title" value="'. $widgetTitle .'" class="stray_text" /><br />
        <div class="stray_desc">This is valid for the widget functionality only. Leave empty for no title.
        It can also be changed from the <a href="' . get_settings('siteurl'). '/wp-admin/widgets.php' . '">widget page</a>. 
		Formatting of this element is pre-determined by 
        the template and shouldn\'t be inserted here. If you want to use a title with customized formatting elements, 
        leave this empty and use the option below instead.</div></p>
    	<p></p>
        
        <p><strong>Sidebar element</strong>
        <input type="text" size="50" name="regular_title" value="'. htmlentities($regularTitle) .'" class="stray_text" /><br />
        <div class="stray_desc">This is valid only when the widget functionality is not being used or when the widget title (option above) is left empty. 
		Leave empty for no title.<br/></div></p>
    
	</fieldset><p></p>
	
	<fieldset><legend>Author, Quote and Source</legend>
    
        <p><strong>Before</strong>
        <input type="text" size="50" name="before_all" value="'. htmlentities($beforeAll) .'" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or open tags that go before the group made by quote source and author. 
        It comes after the title. Won\'t be considered when spewing all the quotes onto a page.</div></p>
        <p></p>       
        <p><strong>After</strong>
        <input type="text" size="50" name="after_all" value="'. htmlentities($afterAll) .'" class="stray_text" /><br />
        <div class="stray_desc">Here you enter elements that go right after the group made by quote source and author,  
        or close the tags that you opened before it.
        Won\'t be considered when spewing all the quotes onto a page.<br/></div></p>
    
	</fieldset><p></p>
	
	<fieldset><legend>Quote only</legend>
    
        <p><strong>Before</strong>
        <input type="text" size="50" name="before_quote" value="'. htmlentities($beforeQuote) .'" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or open tags that go before the quote. 
        This will be considered when spewing all the quotes onto a page.</div></p>
        <p></p>        
        <p><strong>After</strong>
        <input type="text" size="50" name="after_quote" value="'. htmlentities($afterQuote) .'" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or close the tags that you opened before the quote.
        This will be considered when spewing all the quotes onto a page.<br/></div></p>
    
    </fieldset><p></p>
	
	<fieldset><legend>Author only</legend>
    
        <p><strong>Before</strong>
        <input type="text" size="50" name="before_author" value="'. htmlentities($beforeAuthor) .'" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or open tags that go before the author.
        This will be considered when spewing all the quotes onto a page.</div></p>
        <p></p>        
        <p><strong>After</strong>
        <input type="text" size="50" name="after_author" value="'. htmlentities($afterAuthor) .'" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or close the tags that you put before the author.
        This will be considered when spewing all the quotes onto a page.<br/></div></p>
        
	</fieldset><p></p>

	<fieldset><legend>Source only</legend>
    
        <p><strong>Before</strong>
        <input type="text" size="50" name="before_source" value="'. htmlentities($beforeSource) .'" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or open tags that go before the source of the quote.
        This will be considered when spewing all the quotes onto a page.</div></p>
        <p></p>        
        <p><strong>After</strong>
        <input type="text" size="50" name="after_source" value="'. htmlentities($afterSource) .'" class="stray_text" /><br />
        <div class="stray_desc">Here you can enter elements or close the tags that you put before the source of the quote.
        This will be considered when spewing all the quotes onto a page.<br/></div></p>
        
	</fieldset><p></p>

	<fieldset><legend>Other options</legend>
    
        <p><strong>Add google links:</strong> 
        <input type="checkbox" name="use_google_links" value="Y" '. $useGoogleLinks_selected .'>
        <br/><div class="stray_desc">Adds a google link to the Author element.
        This will be considered when spewing all the quotes onto a page.</div></p>
        <p></p>        
        <p><strong>Put quote before the author:</strong> 
        <input type="checkbox" name="put_quotes_first" value="Y" '. $putQuotesFirst_selected .'>
        <br/><div class="stray_desc">If checked, returns the quote before the author. Otherwise, the author comes first.
        This won\'t be considered when spewing all the quotes onto a page (quote will always come first).</div></p>
        <p></p>        
        <p><strong>Visible by default:</strong> 
        <input type="checkbox" name="default_visible" value="Y" '. $defaultVisible_selected .'>
        <br/><div class="stray_desc">If checked, will set "Visible" to "Yes" for all new quotes.<br/></div></p>
    
	</fieldset>
	
	<input type="hidden" name="update" value="yes" /><br/>
	<p class="submit" align="right"><input type="submit" value="Update Options &raquo;" />
	<br/></p>
	</form>
	</div></div>';
	
	//paypal and stuff
	donate_block();	
}

//manage page
function stray_quotes_manage() {

	global $wpdb;
	
	$first_time = get_option('stray_quotes_first_time');
	if ($first_time == 1) {
	
		$wpdb->query( "INSERT INTO `" . WP_STRAY_QUOTES_TABLE . "` (quote, author, source, visible) values ('And strange it is / That nature must compel us to lament / Our most persisted deeds.', 'William Shakespeare', 'Antony and Cleopatra', 'yes') ");
		
		echo '<div class="updated"><p>Hey. Welcome to <strong>Stray Quotes.</strong><br />
		This seems to be your first time visiting this page. 
		I just created the database table "' . WP_STRAY_QUOTES_TABLE . '" to store your quotes, 
		and added one to start you off.<br />
		Check out the <a href="' . get_settings('siteurl') . 
		'/wp-admin/options-general.php?page=stray_quotes.php"> Options Page</a> too. Good quoting.</div>';
		
		update_option('stray_quotes_first_time', 3);
	}
	
	else if ($first_time == 2) {
	
		echo '<div class="updated"><p>Hey. Welcome to <strong>Stray Quotes.</strong><br />
		I just renamed the old tables of quotes "' . WP_QUOTES_TABLE . '" as "' . WP_STRAY_QUOTES_TABLE . '".<br />
		All your quotes are still there. As you can see the new table comes with all your old quotes and a new optional field, "source". <br />
		Check out the <a href="' . get_settings('siteurl') . 
		'/wp-admin/options-general.php?page=stray_quotes.php"> Options Page</a> too. Good quoting.</div>';	
	}

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
		
				echo '<div class="error"><p><strong>Failure:</strong> 
				Something went wrong when trying to insert the quote. Try again? </p></div>';				
		}
			
		else {
			echo '<div class="updated"><p>Quote id ' . $result[0]->quoteID . ' successfully added to the database.</p></div>';
			
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
			echo '<div class="error"><p><strong>Failure:</strong> No quote ID given.</p></div>';
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
				echo '<div class="error"><p><strong>Failure:</strong> Something went wrong. Try again? </p></div>';
				
			}
			else {			
				echo '<div class="updated"><p>Quote ' . $quoteID . ' updated successfully</p></div>';
			}		
		}
	}
	
	else if ( $action == 'delete' ) {
	
		if ( empty($quoteID) ) {
		
			echo '<div class="error"><p><strong>Failure:</strong> No quote ID given. Nothing deleted.</p></div>';			
		}
			
		else {
		
			$sql = "delete from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "'";
			$wpdb->get_results($sql);
			
			$sql = "select quoteID from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "'";
			$result = $wpdb->get_results($sql);
			
			if ( empty($result) || empty($result[0]->quoteID) )	{
			
				echo '<div class="updated"><p>Quote ' . $quoteID . ' deleted successfully</p></div>';
			}
			
			else {
						
				echo '<div class="error"><p><strong>Failure:</strong> Nothing deleted.</p></div>';
	
			}		
		}
	}
	
	//edit form
	echo '<div style="width:100%; margin:auto"><div class="wrap">';
		
	//if the page is opened after a edit action, shows only the form
	if ( $action == 'edit' ) {
	
		//edit form
		echo'<h2><br/>
		Edit Quote</h2>';		
		
		//chek if something went wrong with quote id
		if ( empty($quoteID) ) {
			echo "<div class=\"error\"><p>I didn't get a quote identifier from the query string.</p></div>";
		}
		
		else {			
			
			//query
			$data = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "' limit 1");
			if ( empty($data) ) {
				echo "<div class=\"error\"><p>I couldn't find a quote linked up with that identifier.</p></div>";
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
			echo '<div class="stray_form"><form name="quoteform" id="quoteform" method="post" action="' . $_SERVER['REQUEST_URI'] . '">
				<input type="hidden" name="action" value="edit_save">
				<input type="hidden" name="quoteID" value="' .  $quoteID .'">
			
				<fieldset class="small"><legend>Quote: </legend>
					<textarea name="quote_quote" class="stray_textarea" cols=78 rows=7>'
					 . $quote . '</textarea>
				</fieldset>
				
				<fieldset class="small"><legend>Author: </legend>
					<input type="text" name="quote_author" class="stray_textedit" size=80
					value="'  . $author . '" />
				</fieldset>

				<fieldset class="small"><legend>Source: </legend>
					<input type="text" name="quote_source" class="stray_textedit" size=80
					value="'  . $source . '" />
				</fieldset>
				
				<fieldset class="small"><legend>Visible: </legend>
					<input type="radio" name="quote_visible" class="input" value="yes"' . $visible_yes. ' /> Yes					
					<input type="radio" name="quote_visible" class="input" value="no"' . $visible_no. ' /> No
				</fieldset>
				<p align="right"><input type="submit" name="save" class="button bold" value="Save quote &raquo;" /></p>
				</div>
			</form>';
	
		}	
	}	
	
	//in all the other cases shows the form and the list
	//it is debatable whether this form should show empty fields or values from the last insert
	else {
	
		echo '<h2><br/>
		Stray Quotes -  Add new</h2>'; 		
		    
			$quoteID=false;
			$data = false;	
			if ( $quoteID !== false ) {
		
				if ( intval($quoteID) != $quoteID ) {		
					echo "<div class=\"error\"><p>The Quote ID seems to be invalid.</p></div>";
					return;
				}
				else {
					$data = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "' limit 1");
					if ( empty($data) ) {
						echo "<div class=\"error\"><p>I couldn't find a quote linked up with that identifier.</p></div>";
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
			echo '<div class="stray_form"><form name="quoteform" id="quoteform" method="post" action="' . $_SERVER['REQUEST_URI'] . '">
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
					<input type="radio" name="quote_visible" class="input" value="yes"' . $visible_yes. ' /> Yes					
					<input type="radio" name="quote_visible" class="input" value="no"' . $visible_no. ' /> No
				</fieldset>
				<p align="right"><input type="submit" name="save" class="button bold" value="Save quote &raquo;" /></p>
				</div>
			</form>';
			
			
			$orderBY = 'quoteID';
			//list of existing quotes	
			echo '<div class="wrap"><br/><h2>
			Stray Quotes - Manage</h2>';
			$quotes = $wpdb->get_results("SELECT * FROM " . WP_STRAY_QUOTES_TABLE . " order by ". $orderBY);
			if ( !empty($quotes) ) {
				echo '<script language="JavaScript"><!--
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
						<th scope="col"> Edit </th>
						<th scope="col"> Delete </th>
					</tr>';
				
				$i = 0;	
				foreach ( $quotes as $quote ) {
				
					$alt = ($i % 2 == 0) ? ' class="alternate"' : '';
			
					echo '<tr ' . $alt .'>
						
						<th scope="row">'.  $quote->quoteID .'</th>
						<td>' .  nl2br($quote->quote) . '</td>
						<td>' .  $quote->author . '</td>
						<td>' .  $quote->source . '</td>
						<td>' .  $quote->visible . '</td>
											
						<td align="center"><form name="quotes" method="post"><input type="hidden" name="page" value="'. WP_STRAY_QUOTES_PATH .'">
						<input type="hidden" name="quoteID" value="'.$quote->quoteID.'" />					
						<input type="hidden" name="action" value="edit"><input type="button" class="edit" value="Edit" 
						onClick="mTsetAction(this.form, \'edit\');" /></form></td>
		
						<td align="center"><form name="quotes" method="post"><input type="hidden" name="page" value="'. WP_STRAY_QUOTES_PATH .'">
						<input type="hidden" name="quoteID" value="'.$quote->quoteID.'" />
						<input type="hidden" name="action" value="delete"><input type="button" class="delete" value="Delete" 
						onClick="if( confirm(\'Are you sure you wish to delete this quote permanently?\')) mTsetAction(this.form, \'delete\');" />
						</form></td>			
					</tr>';
					$i++;
				}
				echo '</table></div>';			
			}
			else { 		
				echo '<p> You haven\'t entered any quotes yet. </p>';			
			}
	}
	echo '</div></div>';
	//paypal and stuff
	donate_block();	
}

?>