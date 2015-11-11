<?php

function stray_new() {

	global $wpdb, $wp_version, $current_user; /* zL: added: $wp_version */

	//load options
	$quotesoptions = array();
	$quotesoptions = get_option('stray_quotes_options');

	//security check
	if( $quotesoptions['stray_multiuser'] == false && !current_user_can('manage_options') )
		die('Access Denied');

	//decode and intercept
	foreach($_POST as $key => $val) {
		$_POST[$key] = stripslashes($val);
	}

	// control the requests
	$action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
	$quoteID = !empty($_REQUEST['quoteID']) ? sanitize_text_field($_REQUEST['quoteID']) : '';

	//this is for the bookmarklet
	if ( $action == 'bookmarklet' ) {

		$quotesoptions = array();
		$quotesoptions = get_option('stray_quotes_options');
		$quote = !empty($_REQUEST['quote_quote']) ? sanitize_text_field(trim($_REQUEST['quote_quote'])) : '';
		if ($quotesoptions['bookmarlet_source'] == 'Y' )$source = !empty($_REQUEST['quote_source']) ? sanitize_text_field(trim($_REQUEST['quote_source'])) : '';
		if ($quotesoptions['bookmarklet_cat']) $category = $quotesoptions['bookmarklet_cat'];
	}

	//after adding a new quote
	if ( $action == 'add' ) {

		//assign variables and trim them
		$quote = !empty($_REQUEST['quote_quote']) ? sanitize_text_field($_REQUEST['quote_quote']) : '';
		$author = !empty($_REQUEST['quote_author']) ? sanitize_text_field($_REQUEST['quote_author']) : '';
		$source = !empty($_REQUEST['quote_source']) ? sanitize_text_field($_REQUEST['quote_source']) : '';
		$visible = !empty($_REQUEST['quote_visible']) ? sanitize_text_field($_REQUEST['quote_visible']) : '';
		if ( $_REQUEST['quote_category'] )$category = sanitize_text_field($_REQUEST['quote_category']);
		else $category = sanitize_text_field($_REQUEST['categories']);

		//remove spaces from categories
		if (preg_match('/\s+/',$category)>0){
			$category=preg_replace('/\s+/','-',$category);
			$plusmessage = "<br/>Note: <strong>The name of the category you created contained spaces</strong>, which are not allowed. <strong>I replaced them with dashes</strong>. I hope it's okay.";
		}

		if ($category == false || $category == '') $category = 'default';

		//take care of stupid magic quotes
		if (ini_get('magic_quotes_gpc') || $wp_version > '2.8.5') /* zL: added: version check for handling unstripped slashes */
		{
			$quote = stripslashes($quote);
			$author = stripslashes($author);
			$source = stripslashes($source);
			$category = stripslashes($category);
			$visible = stripslashes($visible);
		}

		//insert the quote into the database!!
		$sql = "insert into " . XV_RANDOMQUOTES_TABLE
		. " set `quote`='" . esc_sql($quote)
		. "', `author`='" . esc_sql($author)
		. "', `source`='" . esc_sql($source)
		. "', `category`='" . esc_sql($category)
		. "', `visible`='" . esc_sql($visible)
		. "', `user`='" . esc_sql($current_user->user_nicename)
		. "'";
		$wpdb->get_results($sql);

		//check: go and get the quote just inserted
		$sql2 = "select `quoteID` from " . XV_RANDOMQUOTES_TABLE
		. " where `quote`='" . esc_sql($quote)
		. "' and `author`='" . esc_sql($author)
		. "' and `source`='" . esc_sql($source)
		. "' and `category`='" . esc_sql($category)
		. "' and `visible`='" . esc_sql($visible)
		. "' and `user`='" . esc_sql($current_user->user_nicename)
		. "' limit 1";
		$result = $wpdb->get_results($sql2);

		//failure message
		if ( empty($result) || empty($result[0]->quoteID) )	{
			?><div class="error fade"><p><?php _e('<strong>Failure:</strong> Something went wrong when trying to insert the quote. Try again?',
			'stray-quotes'); ?></p></div><?php
		}

		//success message
		else {
			?><div class="updated fade"><p><?php

			$search = array("%s1", "%s2");
			$replace = array($result[0]->quoteID, get_option("siteurl").'/wp-admin/admin.php?page=stray_manage');
			echo str_replace($search,$replace,__(
			'Quote no. <strong>%s1</strong> was added to the database. To insert it in a post use: <code>[stray-id id=%s1]</code>. To review use the <a href="%s2">Manage page</a>.'.$plusmessage,'stray-quotes')); ?></p></div><?php
		}

	}

	//making the "add new quote" page
	?><div class="wrap"><h2><?php _e('Add new quote','stray-quotes') ?></h2><?php

		//housecleaning
		$quoteID=false;
		$data = false;

		//get the last inserted quote
		if ( $quoteID !== false ) {

			if ( intval($quoteID) != $quoteID ) {
				?><div class="error fade"><p><?php _e('The Quote ID seems to be invalid.','stray-quotes') ?></p></div><?php
				return;
			}
			else {
				$data = $wpdb->get_results("select * from " . XV_RANDOMQUOTES_TABLE . " where quoteID='" . esc_sql($quoteID) . "' limit 1");
				if ( empty($data) ) {
					?><div class="error fade"><p><?php _e('Something is wrong. Sorry.','stray-quotes') ?></p></div><?php
					return;
				}
				$data = $data[0];
			}
		}

		//optionally assign the just inserted quote to vaiables
		if ($quotesoptions['stray_clear_form']!=='Y') {
			if ( !empty($data) ) {
				$quote = $data->quote;
				$author = $data->author;
				$source = $data->source;
				$category = $data->category;
			}
		} else if($action != 'bookmarklet')$quote = $author = $source = $category = false;

		//visibility
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

		//default category
		$defaultcategory = $quotesoptions['stray_default_category'];

		//make the "add new quote" form
		$styleborder = 'style="border:1px solid #ccc"';
		$styletextarea = 'style="border:1px solid #ccc; font-family: Times New Roman, Times, serif; font-size: 1.4em;"'; ?>

		<div style="width:42em">
		<script src="<?php echo WP_STRAY_QUOTES_PATH ?>inc/stray_quicktags.js" type="text/javascript"></script>
		<form name="quoteform" id="quoteform" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<input type="hidden" name="action" value="add">
			<input type="hidden" name="quoteID" value="<?php echo $quoteID; ?>">

			<p><!--<label><?php _e('Quote:','stray-quotes') ?></label>-->
			<script type="text/javascript">edToolbar();</script>
			<textarea id="qeditor" name="quote_quote" <?php echo $styletextarea ?> cols=68 rows=7><?php echo $quote; ?></textarea>
			<script type="text/javascript">var edCanvas = document.getElementById('qeditor');</script>
			<p class="setting-description"><small><?php _e('* Other than the few offered in the toolbar above, many HTML and non-HTML formatting elements can be used for the quote. Lines can be broken traditionally or using <code>&lt;br/&gt;</code>, etcetera.','stray-quotes'); ?></small></p></p>

			<p><label><?php _e('Author:','stray-quotes') ?></label>
			<input type="text" id="aeditor" name="quote_author" size=58 value="<?php echo htmlspecialchars($author); ?>" <?php echo $styleborder ?> />
			<script type="text/javascript">edToolbar1();</script>
			<script type="text/javascript">var edCanvas1 = document.getElementById('aeditor');</script><br />

			<label><?php _e('Source:','stray-quotes') ?></label>
			<input type="text" id="seditor" name="quote_source" size=58 value="<?php echo htmlspecialchars($source); ?>" <?php echo $styleborder ?> />
			<script type="text/javascript">edToolbar2();</script>
			<script type="text/javascript">var edCanvas2 = document.getElementById('seditor');</script>
			<p class="setting-description"><small><?php _e('* By adding a link to the author or the source, the default links specified on the settings page are ignored. Make sure the link is closed by a <code>&lt;/a&gt;</code> tag.','stray-quotes'); ?></small></p></p>

			<p><label><?php _e('Category:&nbsp;','stray-quotes') ?></label>
			<select name="categories" style="vertical-align:middle; width:14em;" >
			<?php $categorylist = make_categories($current_user->user_nicename);
			foreach($categorylist as $categoryo){ ?>
			<option value="<?php echo $categoryo; ?>" style=" padding-right:5px"
			<?php if ($categoryo == $category || $categoryo == $defaultcategory) echo ' selected'; ?> >
			<?php echo $categoryo;?></option>
			<?php } ?>
			</select>

			<label><?php _e('&nbsp;new category:&nbsp;','stray-quotes') ?></label>
			<input type="text" name="quote_category" size=24 value="" <?php echo $styleborder ?> /></p>

			<p><label><?php _e('Visible:','stray-quotes') ?></label>
				<input type="radio" name="quote_visible" class="input" value="yes"<?php echo $visible_yes ?> /> <?php _e('Yes','stray-quotes') ?>
				<input type="radio" name="quote_visible" class="input" value="no"<?php echo $visible_no ?> /> <?php _e('No','stray-quotes') ?>
			</p><p>&nbsp;</p>

			<p><input type="submit" name="save"  class="button-primary" value="<?php _e('Add quote','stray-quotes') ?> &raquo;" /></p>
		</form></div>

	</div><?php
}

