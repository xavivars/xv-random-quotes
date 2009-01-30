<?php 

//options page
function stray_quotes_options () {
	
	
	global $wpdb;
	
	// Check Whether User Can Manage Options
	if(!current_user_can('manage_options'))die('Access Denied');	
	$mode = trim($_GET['mode']);
	
	//decode and intercept
	foreach($_POST as $key => $val) {
		$_POST[$key] = stripslashes($val);
	}
	
	//handle the post event
	if(!empty($_POST['do'])) {

		// check URLs
		if ($_POST['link_to'] == 'http://') unset($_POST['link_to']);				
		else if (false === strpos($_POST['link_to'],'%AUTHOR%')) {  
			unset($_POST['link_to']);
			$msgvar1 = 1;
		}				
		if ($_POST['source_link_to'] == 'http://') unset($_POST['source_link_to']);				
		else if (false === strpos($_POST['source_link_to'],'%SOURCE%')) {  
			unset($_POST['source_link_to']);
			$msgvar2 = 1;
		}
		
		//create array of values
		$quotesoptions = array(
		'stray_quotes_regular_title' => $_POST['regular_title'],	
		'stray_quotes_widget_title' => $_POST['widget_title'],	
		'stray_quotes_before_all' => $_POST['before_all'],		
		'stray_quotes_after_all' => $_POST['after_all'],
		'stray_quotes_before_quote' => $_POST['before_quote'],	
		'stray_quotes_after_quote' => $_POST['after_quote'],
		'stray_quotes_before_author' => $_POST['before_author'],	
		'stray_quotes_after_author' => $_POST['after_author'],
		'stray_quotes_before_source' => $_POST['before_source'],	
		'stray_quotes_after_source' => $_POST['after_source'],
		'stray_quotes_put_quotes_first' => $_POST['put_quotes_first'],
		'stray_quotes_default_visible' => $_POST['default_visible'],
		'stray_quotes_linkto' => $_POST['link_to'],	
		'stray_quotes_sourcelinkto' => $_POST['source_link_to'],	
		'stray_quotes_authorspaces' => $_POST['author_spaces'],	
		'stray_quotes_sourcespaces' => $_POST['source_spaces'],	
		'stray_quotes_order' => $_POST['order'],
		'stray_quotes_rows' => $_POST['rows'],
		'stray_quotes_groups' => $_POST['groups'],
		'stray_quotes_sort' => $_POST['sort'],
		'stray_default_group' => $_POST['default_group'],
		
		'stray_quotes_version' => WP_STRAY_VERSION,				
		'stray_quotes_first_time' => 4,
		'stray_quotes_uninstall' => '',
		);		
		
		//update options
		$update_quotes_options = update_option('stray_quotes_options', $quotesoptions);			
		
		//positive feedback
		if ($update_quotes_options) { ?>
		<div id="message" class="updated fade below-h2"><p>
		<?php echo __('<strong>Options saved...</strong> ','stray-quotes');
		if ($msgvar1 == 1 && $msgvar2 == 1) echo __('No problems. Well,  except that the links you provided for the author and source were invalid. I had to discard them.', 'stray-quotes');
		else if ($msgvar1 == 1) echo __('No problems. Well, except that there was no variable in the author link. I discared it.', 'stray-quotes');
		else if ($msgvar2 == 1) echo __('No problems. Well,  except that there was no variable in the source link. I discared it.', 'stray-quotes');	
		else echo __('No problems.', 'stray-quotes'); ?></p></div><?php } else {
		
		//negative feedback		
		?><div id="message" class="error fade below-h2"><p>
		<?php if ( $msgvar1 == 1 && $msgvar2 == 1) echo __('<strong>Something went wrong!</strong> The links you provided for the Author and Source had no variables.</strong> ','stray-quotes'); 
		else if ( $msgvar1 == 1 ) echo __('<strong>Something went wrong!</strong> There was no variable in the author link. I discared it. ','stray-quotes'); 
		else if ( $msgvar2 == 1 )  echo __('<strong>Something went wrong!</strong> There was no variable in the source link. I discared it. ','stray-quotes'); 
		else  echo __('<strong>Something went wrong!</strong> The options could not be saved.</strong> ','stray-quotes'); 
		?></p></div><?php }
		
	}	
	
	//get the options
	$quotesoptions = array();
	$quotesoptions = get_option('stray_quotes_options');
	$regularTitle =  $quotesoptions['stray_quotes_regular_title'];
	$widgetTitle = $quotesoptions['stray_quotes_widget_title'];
	$beforeAll =  $quotesoptions['stray_quotes_before_all'];
	$afterAll = $quotesoptions['stray_quotes_after_all'];
	$beforeQuote = $quotesoptions['stray_quotes_before_quote'];
	$afterQuote = $quotesoptions['stray_quotes_after_quote'];
	$beforeAuthor = $quotesoptions['stray_quotes_before_author'];
	$afterAuthor = $quotesoptions['stray_quotes_after_author'];
	$beforeSource = $quotesoptions['stray_quotes_before_source'];
	$afterSource = $quotesoptions['stray_quotes_after_source'];
	$putQuotesFirst = $quotesoptions['stray_quotes_put_quotes_first'];
	$defaultVisible = $quotesoptions['stray_quotes_default_visible'];
	$linkto = $quotesoptions['stray_quotes_linkto'];
	$sourcelinkto = $quotesoptions['stray_quotes_sourcelinkto'];
	$sourcespaces = $quotesoptions['stray_quotes_sourcespaces'];	
	$authorspaces = $quotesoptions['stray_quotes_authorspaces'];	
	$order = $quotesoptions['stray_quotes_order'];
	$rows = $quotesoptions['stray_quotes_rows'];
	$groups = $quotesoptions['stray_quotes_groups'];
	$sort = $quotesoptions['stray_quotes_sort'];
	$defaultgroup = $quotesoptions['stray_default_group'];
		
	if ( $putQuotesFirst == 'Y' ) $putQuotesFirst_selected = 'checked';	
	if ( $defaultVisible == 'Y' ) $defaultVisible_selected = 'checked';	
	
	//the options form	?>
	<form name="frm_options" method="post" action="<?php echo ($_SERVER['REQUEST_URI']); ?>">

    <?php //quote aspect ?>
    <div class="wrap"><h2><?php echo __('Settings','stray-quotes') ?></h2>
    <span class="setting-description"><?php echo __('"With just enough of learning to misquote." ~ Lord Byron ','stray-quotes') ?></span>
    <p>&nbsp;</p>
    <p><h3 style="line-height:.1em"><?php echo __('How the quotes look','stray-quotes') ?></h3>
    <span class="setting-description"><?php echo __('Default settings to change how the quotes appear in your blog.','stray-quotes') ?></span>
    </p>
	<table class="form-table"> 
	<tr valign="top"><th scope="row"><?php echo __('The Title','stray-quotes') ?></th>    	
        <td><input type="text" size="50" name="widget_title" value="<?php echo ($widgetTitle); ?>"class="regular-text" /><span class="setting-description">
    	<?php echo str_replace("%s",get_option('siteurl')."/wp-admin/widgets.php",__('<br/>The default title for all the quote widgets. Single settings can be changed on the <a href="%s">widget page</a>. HTML is not needed here.<br /><strong>Sample value:</strong> <code>Random Quote</code>','stray-quotes')); ?></span></td>
        <td><input type="text" size="50" name="regular_title" value="<?php echo (htmlentities($regularTitle)); ?>"class="regular-text" /><span class="setting-description">
		<?php echo str_replace("%s",get_option("siteurl").'/wp-admin/admin.php?page=stray_quotes/stray_quotes.php',__('<br/>The default title for when the widget functionality is not being used. On how to work with the code added to your template, refer to <a href="%s">this page</a>.<br/><strong>Sample value:</strong> <code>&lt;h2&gt;Random Quote&lt;/h2&gt;</code>','stray-quotes')) ?></span>   
	</td></tr>
	<tr valign="top" style="background:#F0F0F0"><th scope="row"><?php echo __('Author, Quote and Source','stray-quotes') ?></th>    
        <td><input type="text" size="50" name="before_all" value="<?php echo (htmlentities($beforeAll)); ?>"class="regular-text" /><span class="setting-description">
		<?php echo __('<br/>HTML or other elements before this whole group, which comes after the title.<br/><strong>Sample value:</strong>','stray-quotes') ?> <code>&lt;div align=&quot;right&quot;&gt;</code></span></td>
        <td><input type="text" size="50" name="after_all" value="<?php echo (htmlentities($afterAll)); ?>"class="regular-text" /><span class="setting-description">
		<?php echo __('<br/>HTML or other elements after this group.<br/><strong>Sample value:</strong>','stray-quotes') ?> <code>&lt;/div&gt;</code></span>   
	</td></tr>
	<tr valign="top"><th scope="row"><?php echo __('Quote','stray-quotes') ?></th>    
        <td><input type="text" size="50" name="before_quote" value="<?php echo (htmlentities($beforeQuote)); ?>"class="regular-text" /><span class="setting-description">
        <?php echo __('<br/>HTML or other elements before the quote.<br/><strong>Sample value:</strong>','stray-quotes') ?> <code>&amp;#8220;</code></span>
        <td><input type="text" size="50" name="after_quote" value="<?php echo (htmlentities($afterQuote)); ?>"class="regular-text" /><span class="setting-description">
		<?php echo __('<br/>HTML or other elements after the quote.<br/><strong>Sample value:</strong>','stray-quotes') ?> <code>&amp;#8221;</code></span>    
    </td></tr>
	<tr valign="top" style="background:#F0F0F0"><th scope="row"><?php echo __('Author','stray-quotes') ?></th><td>    
        <input type="text" size="50" name="before_author" value="<?php echo (htmlentities($beforeAuthor)); ?>" class="regular-text" /><span class="setting-description">
		<?php echo __('<br/>HTML or other elements before the author.<br/><strong>Sample value:</strong>','stray-quotes') ?> <code>&lt;br/&gt;by&amp;nbsp;</code></span>
        <br/>
        <input type="text" size="50" name="after_author" value="<?php echo (htmlentities($afterAuthor)); ?>" class="regular-text" /><span class="setting-description">
		<?php echo __('<br/>HTML or other elements after the author.','stray-quotes') ?></span></td>
        <td><input type="text" size="50" name="link_to" value="<?php if ($linkto) echo (htmlentities($linkto)); else echo 'http://'; ?>" class="regular-text code" /><span class="setting-description">
		<?php echo __('<br/>You can link the Author to a website of your choice.
		<br/>Use this variable in your link: <code>%AUTHOR%</code><br/>
		<strong>Sample values:</strong>','stray-quotes') ?> <code>http://www.google.com/search?q=&quot;%AUTHOR%&quot;</code><br/> 
		<code>http://en.wikipedia.org/wiki/%AUTHOR%</code><br />
        <?php echo __('Replace spaces within %AUTHOR% with ','stray-quotes') ?>
        <input type="text" size="1" maxlength="1" name="author_spaces" value="<?php echo (htmlentities($authorspaces)); ?>" /></span>
   	</td></tr>
	<tr valign="top"><th scope="row"><?php echo __('Source','stray-quotes') ?></th><td>    
        <input type="text" size="50" name="before_source" value="<?php echo (htmlentities($beforeSource)); ?>" class="regular-text" /><span class="setting-description">
		<?php echo __('<br/>HTML or other elements before the source.<br/><strong>Sample value:</strong>','stray-quotes') ?> <code>,&lt;em&gt;&amp;nbsp;</code></span>
        <br/>
        <input type="text" size="50" name="after_source" value="<?php echo (htmlentities($afterSource)); ?>" class="regular-text" /><span class="setting-description">
		<?php echo __('<br/>HTML or other elements after the source.<br/><strong>Sample value:</strong>','stray-quotes') ?> <code>&lt;/em&gt;</code></span></td>
        <td><input type="text" size="50" name="source_link_to" value="<?php if ($sourcelinkto) echo (htmlentities($sourcelinkto)); else echo 'http://'; ?>" class="regular-text code" /><span class="setting-description">
		<?php echo __('<br/>You can link the Source to a website of your choice.
		<br/>Use this variable in your link: <code>%SOURCE%</code><br/>
		<strong>Sample values:</strong>','stray-quotes') ?> <code>http://www.google.com/search?q=&quot;%SOURCE%&quot;</code><br/> 
		<code>http://en.wikipedia.org/wiki/%SOURCE%</code><br />
        <?php echo __('Replace spaces within %SOURCE% with ','stray-quotes') ?>
        <input type="text" size="1" maxlength="1" name="source_spaces" value="<?php echo (htmlentities($sourcespaces)); ?>" />
        </span>
   	</td></tr>
	<tr valign="top" style="background:#F0F0F0"><th scope="row"><?php echo __('Quote before Author and Source','stray-quotes') ?></th><td colspan="2">    
    	<input type="checkbox" name="put_quotes_first" value="Y" <?php echo ($putQuotesFirst_selected); ?> /><span class="setting-description">
        <?php echo __('If checked, returns the quote before author and source. This won\'t be considered when spewing all the quotes onto a page (quote will always come first).','stray-quotes') ?></span>
    </td></tr>
    </table>
    <br/>
   	<div class="submit">
    <input type="hidden" name="do" value="Update" />
    <input type="submit" value="<?php echo __('Update all Settings','stray-quotes') ?> &raquo;" />
    </div>
    
   <p>&nbsp;</p>
   
	<?php //new quotes ?>
    <p><h3 style="line-height:.1em"><?php echo __('New quotes','stray-quotes') ?></h3>
    <span class="setting-description"><?php echo __('Default settings when you create a new quote.','stray-quotes') ?></span>
    </p>
    <table class="form-table">
    <tr valign="top"><th scope="row"><?php echo __('Visibility','stray-quotes') ?></th>       
        <td colspan="2"><input type="checkbox" name="default_visible" value="Y" <?php echo ($defaultVisible_selected); ?> /><span class="setting-description">
        <?php echo __('If checked, will set "Visible" to "Yes" for all new quotes.','stray-quotes') ?></span>
    </td></tr> 
    <tr valign="top" style="background:#F0F0F0"><th scope="row"><?php echo __('Default group','stray-quotes') ?></th>       
    <td colspan="2"><select name="default_group" style="vertical-align:middle"> 
    <?php $grouplist = make_groups(); 
	foreach($grouplist as $groupo){ ?>
    	<option value="<?php echo $groupo; ?>" 
		<?php  if ( $groupo == $defaultgroup) echo ' selected '; ?> >
		<?php echo $groupo;?></option>
	<?php } ?>   
    </select><span class="setting-description"> 
	<?php echo __('This group will be the default for all new quotes.','stray-quotes') ?></span>
	</td></tr>
	</table>
    <br/>
	<div class="submit">
    <input type="hidden" name="do" value="Update" />
    <input type="submit" value="<?php echo __('Update all Settings','stray-quotes') ?> &raquo;" />
    </div>
    
    <p>&nbsp;</p>
     
	<?php //management of quotes ?>
    
    <p><h3 style="line-height:.1em"><?php echo __('Management of the quotes','stray-quotes') ?></h3>
    <span class="setting-description"><?php echo __('Default settings for the management page.  They can be changed on the management page as well.','stray-quotes') ?></span>
    </p>
    <table class="form-table">
     <tr valign="top"><th scope="row"><?php echo __('Order by','stray-quotes') ?></th>       
        <td><select name="order" style="vertical-align:middle" >
        <option value="quoteID" <?php if ($order == "quoteID") echo 'selected="selected"'; ?> >ID</option>
        <option value="author" <?php if ($order == "author") echo 'selected="selected"'; ?> >Author</option>
        <option value="source" <?php if ($order == "source") echo 'selected="selected"'; ?> >Source</option>
        <option value="group" <?php if ($order == "group") echo 'selected="selected"'; ?> >Group</option>
        <option value="visible" <?php if ($order == "visible") echo 'selected="selected"'; ?> >Visibility</option>
        </select><span class="setting-description">
        <?php echo __('<br/>The list of quotes in the management page will be ordered by this value.','stray-quotes') ?></span>
      </td>
      <td><select name="sort" style="vertical-align:middle" >
        <option value="ASC" <?php if ($sort == "ASC") echo 'selected="selected"'; ?> >Ascending</option>
        <option value="DESC" <?php if ($sort == "DESC") echo 'selected="selected"'; ?> >Descending</option>
        </select><span class="setting-description">
        <?php echo __('<br/>The sorting of quotes will take this direction.','stray-quotes') ?></span>
      </td></tr>
     <tr valign="top" style="background:#F0F0F0"><th scope="row"><?php echo __('Quotes per page','stray-quotes') ?></th>       
        <td colspan="2"><select name="rows" style="vertical-align:middle">
        <option value="10" <?php if ( $rows == 10) echo ' selected';  ?> >10</option>
        <option value="15" <?php if ( $rows == 15) echo ' selected'; ?> >15</option>
        <option value="20" <?php if ( $rows == 20) echo ' selected'; ?> >20</option>
        <option value="30" <?php if ( $rows == 30) echo ' selected'; ?> >30</option>
        <option value="50" <?php if ( $rows == 50) echo ' selected'; ?> >50</option>
        <option value="100" <?php if ( $rows == 100) echo ' selected'; ?> >100</option>
        </select><span class="setting-description">
        <?php echo __('The list of quotes in the management page will display this much quotes per page.','stray-quotes') ?></span>
      </td></tr>
    <tr valign="top"><th scope="row"><?php echo __('Show groups','stray-quotes') ?></th>       
    <td colspan="2"><select name="groups" style="vertical-align:middle"> 
    <option value="<?php echo $urlgroup.'all'; ?>" 
	<?php  if ( $groups == '' || $groups == 'all' ) echo ' selected'; ?>><?php echo __('All groups','stray-quotes') ?></option>
    <?php $grouplist = make_groups(); 
	foreach($grouplist as $groupo){ ?>
    	<option value="<?php echo $urlgroup.$groupo; ?>" 
		<?php  if ( $groups) {if ( $groups == $groupo) echo ' selected';} ?> >
		<?php echo $groupo;?></option>
	<?php } ?>   
    </select><span class="setting-description"> 
	<?php echo __('The list of quotes in the management page will present quotes from this group only.','stray-quotes') ?></span>
	</td></tr>
	</table>
    <br/>
	<div class="submit">
    <input type="hidden" name="do" value="Update" />
    <input type="submit" value="<?php echo __('Update all Settings','stray-quotes') ?> &raquo;" />
    </div>
    <p>&nbsp;</p>
    </div>
    </form><?php
	
}

?>