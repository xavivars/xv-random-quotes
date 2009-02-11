<?php 

//manage page
function stray_manage() {

	?><div class="wrap"><h2><?php echo __('Manage quotes','stray-quotes') ?></h2>
    	
	<?php global $wpdb;
	$quotesoptions = get_option('stray_quotes_options');
	
	//decode and intercept
	foreach($_POST as $key => $val)$_POST[$key] = stripslashes($val);
	 		
	//defaults and gets
	$action = !empty($_REQUEST['qa']) ? $_REQUEST['qa'] : '';
	$quoteID = !empty($_REQUEST['qi']) ? $_REQUEST['qi'] : '';
	$orderby = $quotesoptions['stray_quotes_order'];
	$pages = 1;
	$rows = $quotesoptions['stray_quotes_rows']; 
	$categories = $quotesoptions['stray_quotes_categories']; 
	$sort = $quotesoptions['stray_quotes_sort']; 
	
	if(isset($_GET['qo'])){
		$orderby = $_GET['qo'];
		$quotesoptions['stray_quotes_order'] = $_GET['qo'];
	}
	if(isset($_GET['qp']))$pages = $_GET['qp'];	
	
	if(isset($_GET['qr'])){
		$rows = $_GET['qr'];
		$quotesoptions['stray_quotes_rows'] = $_GET['qr'];	
	}
	
	if(isset($_GET['qg'])){
		$categories = $_GET['qg'];
		$quotesoptions['stray_quotes_categories'] = $_GET['qg'];	
	}
	
	if(isset($_GET['qs'])){
		$sort = $_GET['qs'];
		$quotesoptions['stray_quotes_sort'] = $_GET['qs'];		
	}
	
	$offset = ($pages - 1) * $rows;
	
	//update options now
	update_option('stray_quotes_options', $quotesoptions);
	
	//urls for different use (primitive, I know)
	$baseurl = get_option("siteurl").'/wp-admin/admin.php?page=stray_manage';
	$urlaction = $baseurl.'&qo='.$orderby.'&qp='.$pages.'&qr='.$rows.'&qg='.$categories.'&qs='.$sort; 
		
	//if the page is opened after a edit action
	if ( $action == 'edit' ) {
	
		//check if something went wrong with quote id
		if ( empty($quoteID) ) {
			?><div id="message" class="error"><p><?php echo __(
			'Something is wrong. No quote ID from the query string.','stray-quotes') ?></p></div><?php
		}
		
		else {			
			
			//query
			$data = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "' limit 1");
			
			//bad feedback
			if ( empty($data) ) {
				?><div id="message" class="error"><p><?php echo __(
				'Something is wrong. I can\'t find a quote linked up with that ID.','stray-quotes') ?></p></div><?php
				return;
			}
			$data = $data[0];
			
			//encode strings
			if ( !empty($data) ) $quote = htmlspecialchars($data->quote); 
			if ( !empty($data) ) $author = htmlspecialchars($data->author);
			if ( !empty($data) ) $source = htmlspecialchars($data->source);
			if ( !empty($data) ) $category = htmlspecialchars($data->category);
			
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
			$styleborder = 'style="border:1px solid #ccc"';
			$styletextarea = 'style="border:1px solid #ccc; font-family: Times New Roman, Times, serif; font-size: 1.4em;"'; ?>
            <div style="width:42em">
			<script src="<?php echo WP_STRAY_QUOTES_PATH ?>/inc/js_quicktags-mini.js" type="text/javascript"></script>
            <form name="quoteform" id="quoteform" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?> ">
				<input type="hidden" name="qa" value="edit_save">
				<input type="hidden" name="qi" value="<?php echo $quoteID; ?>">
			
				<p><!--<label><?php echo __('Quote:','stray-quotes') ?></label><br />-->
                <div style="float:left"><script type="text/javascript">edToolbar();</script></div>
                <div style="float:right; display:compact;margin-top:12px"><small>To insert this quote in a post use: <code>[quote id=<?php echo $quoteID ?>]</code></small></div>
                <textarea id="qeditor" name="quote_quote" <?php echo $styletextarea ?> cols=68 rows=7><?php echo $quote; ?></textarea></p>
				<script type="text/javascript">var edCanvas = document.getElementById('qeditor');</script>
                <p class="setting-description"><small><?php echo __('* Other than the few offered in the toolbar above, many HTML and non-HTML formatting elements can be used for the quote. Lines can be broken traditionally or using <code>&lt;br/&gt;</code>, etcetera.','stray-quotes'); ?></small></p></p>
                
				<p><label><?php echo __('Author:','stray-quotes') ?></label>
                <input type="text" name="quote_author" size=58 value="<?php echo $author ?>" <?php echo $styleborder ?> />
				<script type="text/javascript">edToolbar1();</script>
                <script type="text/javascript">var edCanvas1 = document.getElementById('aeditor');</script><br />
				
				<label><?php echo __('Source:','stray-quotes') ?></label>
                <input type="text" name="quote_source" size=58 value="<?php echo $source ?>" <?php echo $styleborder ?> />
				<script type="text/javascript">edToolbar2();</script>
                <script type="text/javascript">var edCanvas2 = document.getElementById('seditor');</script>
                <p class="setting-description"><small><?php echo __('* By adding a link to the author or the source, the default links specified on the settings page are ignored. Make sure the link is closed by a <code>&lt;/a&gt;</code> tag.','stray-quotes'); ?></small></p></p>
                
                <p><label><?php echo __('Category:&nbsp;','stray-quotes') ?></label>                
                <select name="categories" style="vertical-align:middle; width:14em;"> 
                <?php $categorylist = make_categories(); 
                foreach($categorylist as $categoryo){ ?>
                <option value="<?php echo $categoryo; ?>" style=" padding-right:5px"
                <?php  if ( $categoryo == $category) echo ' selected'; ?> >
                <?php echo $categoryo;?></option>
                <?php } ?>   
                </select>
                  
                <label><?php echo __('new category:&nbsp;','stray-quotes') ?></label>
                <input type="text" name="quote_category" size=24 value=""  maxlength="24" <?php echo $styleborder ?> /></p>
                
				<p><label><?php echo __('Visible:','stray-quotes') ?></label>
					<input type="radio" name="quote_visible" class="input" value="yes"<?php echo $visible_yes ?> /> <?php echo __('Yes','stray-quotes') ?>					
					<input type="radio" name="quote_visible" class="input" value="no"<?php echo $visible_no ?> /> <?php echo __('No','stray-quotes') ?></div>
				</p><p>&nbsp;</p>
				<p> <a href=" <?php echo $urlaction ?>">Cancel</a>&nbsp;
         	   <input type="submit" name="save"  class="button-primary" value="<?php echo __('Update quote','stray-quotes') ?> &raquo;" /></p>
			</form><p>&nbsp;</p></div><?php 
	
		}	
	}	
	
	//after having saved the quote
	else if ( $action == 'edit_save' ) {
	
		$quote = !empty($_REQUEST['quote_quote']) ? $_REQUEST['quote_quote'] : '';	
		$author = !empty($_REQUEST['quote_author']) ? $_REQUEST['quote_author'] : '';
		$source = !empty($_REQUEST['quote_source']) ? $_REQUEST['quote_source'] : '';
		$visible = !empty($_REQUEST['quote_visible']) ? $_REQUEST['quote_visible'] : '';
		
		if ($_REQUEST['quote_category'])$category = $_REQUEST['quote_category'];
		else $category = $_REQUEST['categories'];
		
		if (preg_match('/\s+/',$category)>0){
			$category=preg_replace('/\s+/','-',$category);
			$plusmessage = "<br/>Note: <strong>The name of the category you created contained spaces</strong>, which are not allowed. <strong>I replaced them with dashes</strong>. I hope it's okay.";
		} 

	
		if ( ini_get('magic_quotes_gpc') )	{
		
			$quote = stripslashes($quote);
			$author = stripslashes($author);
			$source = stripslashes($source);
			$category = stripslashes($category);
			$visible = stripslashes($visible);	
		}
		
		if ( empty($quoteID) )	{
			?><div id="message" class="error fade"><p><?php echo __(
			'<strong>Failure:</strong> No quote ID given.','stray-quotes') ?></p></div><?php
		}
		
		else {		
			$sql = "UPDATE " . WP_STRAY_QUOTES_TABLE 
			. " SET `quote`='" . mysql_real_escape_string($quote)
			. "', `author`='" . mysql_real_escape_string($author) 
			. "', `source`='" . mysql_real_escape_string($source) 
			. "', `category`='" . mysql_real_escape_string($category)
			. "', `visible`='" . mysql_real_escape_string($visible) 
			. "' WHERE `quoteID`='" . mysql_real_escape_string($quoteID) . "'";		     
			$wpdb->get_results($sql);
			
			$sql = "SELECT `quoteID` FROM " . WP_STRAY_QUOTES_TABLE 
			. " WHERE `quote`='" . mysql_real_escape_string($quote) 
			. "' AND `author`='" . mysql_real_escape_string($author) 
			. "' AND `source`='" . mysql_real_escape_string($source) 
			. "' AND `category`='" . mysql_real_escape_string($category) 
			. "' AND `visible`='" . mysql_real_escape_string($visible) . "' LIMIT 1";
			$result = $wpdb->get_results($sql);
			
			if ( empty($result) || empty($result[0]->quoteID) )	{			
				?><div id="message" class="error fade"><p><?php echo __(
				'<strong>Failure:</strong> Something went wrong.','stray-quotes') ?></p></div><?php				
			}
			else {			
				?><div id="message" class="updated fade"><p><?php echo str_replace("%s",$quoteID,__(
				'Quote <strong>%s</strong> updated.'.$plusmessage,'stray-quotes'));?></p></div><?php
			}		
		}
	}
	
	else if ( $action == 'delete' ) {
	
		if ( empty($quoteID) ) {
			
			
			?><div class="error fade"><p><?php echo __(
			'<strong>Failure:</strong> No quote ID given. Nothing deleted.','stray-quotes') ?></p></div><?php		
		}
			
		else {
		
			$sql = "delete from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "'";
			$wpdb->get_results($sql);
			
			$sql = "select quoteID from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" . mysql_real_escape_string($quoteID) . "'";
			$result = $wpdb->get_results($sql);
			
			if ( empty($result) || empty($result[0]->quoteID) )	{			
				?><div class="updated"><p><?php echo str_replace("%s",$quoteID,__(
				'Quote <strong>%s</strong> deleted.','stray-quotes')); ?></p></div><?php
			}			
			else {						
				?><div class="error fade"><p><?php echo __(
				'<strong>Failure:</strong> Nothing deleted.','stray-quotes'); ?></p></div><?php	
			}		
		}
	}
		
	// prepares category for sql
	$where = '';
	if (!$categories || $categories == 'all') $where = '';
	else $where = " WHERE `category`='" . $categories . "'";
	
	// how many rows we have in database
	$numrows = $wpdb->get_var("SELECT COUNT(`quoteID`) as rows FROM " . WP_STRAY_QUOTES_TABLE . $where);
	
	//temporary workaround for the "division by zero" problem
	if (is_string($rows))$rows=intval($rows);
	settype($rows, "integer"); 
	
	// how many pages we have when using paging?
	if ($rows == NULL || $rows < 10) $rows = 10; 
	$maxPage = ceil($numrows/$rows);		
	
	// print the link to access each page
	$nav  = '';
	
	$urlpages = $baseurl.'&qo='.$orderby.'&qr='.$rows.'&qg='.$categories.'&qs='.$sort.'&qp=';
	
	for($quotepage = 1; $quotepage <= $maxPage; $quotepage++) {
	   if ($quotepage == $pages)$nav .= $quotepage; // no need to create a link to current page
	   else $nav .= ' <a href="'.$urlpages.$quotepage.'">'.$quotepage.'</a> ';
	}
	
	if ($pages > 1) {
		
		$quotepage  = $pages - 1;		
		$prev  = ' <a href="'.$urlpages.$quotepage.'">Previous '.$rows.'</a> | ';		
		$first = ' <a href="'.$urlpages.'1">First</a> | ';
	}
	else {
	   $prev  = '&nbsp;'; // we're on page one, don't print previous link
	   $first = '&nbsp;'; // nor the first page link
	}
	
	if ($pages < $maxPage) {
	
		$missing = $numrows-($rows*$pages);		
		if ($missing > $rows) $missing = $rows;
		
		$quotepage = $pages + 1;
		$next = ' | <a href="'.$urlpages.$quotepage.'"> Next '.$missing.'</a> ';
		
		$last = ' | <a href="'.$urlpages.$maxPage.'"> Last</a> ';
	}
	else {
	   $next = '&nbsp;'; // we're on the last page, don't print next link
	   $last = '&nbsp;'; // nor the last page link
	}		

	//get all the quotes
	$sql = "SELECT `quoteID`,`quote`,`author`,`source`,`category`,`visible` FROM " 
	. WP_STRAY_QUOTES_TABLE 
	. $where
	. " ORDER BY `". $orderby ."`"
	. $sort 
	. " LIMIT " . $offset. ", ". $rows;
	
	$quotes = $wpdb->get_results($sql);
	
	//page number has to be reset to 1 otherwise it would look like you have no quotes left when you are on a page too high for so many quotes.
	$urlrows = $baseurl.'&qo='.$orderby.'&qp='.'1'/*$pages*/.'&qg='.$categories.'&qs='.$sort.'&qr=';
	
	$urlcategory = $baseurl.'&qo='.$orderby.'&qp='.$pages.'&qr='.$rows.'&qs='.$sort.'&qg='; 
	$urlorder = $baseurl.'&qp='.$pages.'&qr='.$rows.'&qg='.$categories.'&qs='.$sort.'&qo=';	
	$urlsort = $baseurl.'&qo='.$orderby.'&qp='.$pages.'&qr='.$rows.'&qg='.$categories.'&qs=';

	//HTML
	?><p class="subsubsub" style="float:left"> <?php echo __('quotes per page:','stray-quotes'); ?> 
    <select name="lines" onchange="switchpage(this)"  style="vertical-align:middle">
    <option value=<?php echo $urlrows.'10'; if ( $rows == 10) echo ' selected';  ?> >10</option>
    <option value=<?php echo $urlrows.'15'; if ( $rows == 15) echo ' selected'; ?> >15</option>
    <option value=<?php echo $urlrows.'20'; if ( $rows == 20) echo ' selected'; ?> >20</option>
	<option value=<?php echo $urlrows.'30'; if ( $rows == 30) echo ' selected'; ?> >30</option>
    <option value=<?php echo $urlrows.'50'; if ( $rows == 50) echo ' selected'; ?> >50</option>
    <option value=<?php echo $urlrows.'100'; if ( $rows == 100) echo ' selected'; ?> >100</option>
	</select> | <?php echo __('show category: ','stray-quotes'); ?> 
    
    <select name="categories" onchange="switchpage(this)"  style="vertical-align:middle"> 
    <option value="<?php echo $urlcategory.'all'; ?>" 
	<?php  if ( $categories == '' || $categories == 'all' ) echo ' selected'; ?>>all</option>
    <?php $categorylist = make_categories(); 
	foreach($categorylist as $categoryo){ 
    if (strpos(" ",$categoryo)) $categoryo = str_replace(" ","-",$categoryo);
    	?><option value="<?php echo $urlcategory.$categoryo; ?>" 
		<?php  if ( $categories) {if ( $categories == $categoryo) echo ' selected';} ?> >
		<?php echo $categoryo;?></option>
	<?php } ?>   
    </select>
    </p>
	<p class="subsubsub" style="float:right"><?php echo $first . $prev . $nav . $next . $last; ?></p><?php
	
	//build table
	if ( !empty($quotes) ) {
		$imgasc = WP_STRAY_QUOTES_PATH . '/img/s_asc.png';
		$imgdsc = WP_STRAY_QUOTES_PATH . '/img/s_desc.png';
		?><table class="widefat">
        <thead><tr>          
			
            <th scope="col" style="white-space: nowrap;"> <?php if ($numrows != 1) { if ( $orderby != 'quoteID') { ?>
            <a href="<?php echo $urlorder . 'quoteID'; ?>" title="Sort"><?php echo __('ID','stray-quotes') ?></a>
			<?php } else { echo __('ID','stray-quotes');
				if ($sort == 'ASC') { ?><a href="<?php echo $urlsort . 'DESC'; ?>">
                <img src= <?php echo $imgasc ?> alt="Descending" title="Descending" /> <?php }
				else if ($sort == 'DESC') { ?><a href="<?php echo $urlsort . 'ASC'; ?>">
				<img src= <?php echo $imgdsc ?> alt="Ascending" title="Ascending" /> <?php } ?>
			</a>			
			<?php } }else{ echo __('ID','stray-quotes'); }?>            
            </th>
            
			<th scope="col"> <?php echo __('Quote','stray-quotes') ?> </th>
            
            <th scope="col" style="white-space: nowrap;"> <?php if ($numrows != 1) { if ( $orderby != 'author') { ?>
            <a href="<?php echo $urlorder . 'author'; ?>"><?php echo __('Author','stray-quotes') ?></a>
			<?php } else { echo __('Author','stray-quotes');
				if ($sort == 'ASC') { ?><a href="<?php echo $urlsort . 'DESC'; ?>">
                <img src= <?php echo $imgasc ?> alt="Descending" title="Descending" /> <?php }
				else if ($sort == 'DESC') { ?><a href="<?php echo $urlsort . 'ASC'; ?>">
				<img src= <?php echo $imgdsc ?> alt="Ascending" title="Ascending" /> <?php } ?>
			</a>			
			<?php } }else{ echo __('Author','stray-quotes'); } ?>            
            </th>
            
            <th scope="col" style="white-space: nowrap;"> <?php if ($numrows != 1) { if ( $orderby != 'source') { ?>
            <a href="<?php echo $urlorder . 'source'; ?>"><?php echo __('Source','stray-quotes') ?></a>
			<?php } else { echo __('Source','stray-quotes');
				if ($sort == 'ASC') { ?><a href="<?php echo $urlsort . 'DESC'; ?>">
                <img src= <?php echo $imgasc ?> alt="Descending" title="Descending" /> <?php }
				else if ($sort == 'DESC') { ?><a href="<?php echo $urlsort . 'ASC'; ?>">
				<img src= <?php echo $imgdsc ?> alt="Ascending" title="Ascending" /> <?php } ?>
			</a>			
			<?php }}else{ echo __('Source','stray-quotes'); }  ?>            
            </th>
            
            <th scope="col" style="white-space: nowrap;"> <?php if ($numrows != 1) { if ( $orderby != 'category') { ?>
            <a href="<?php echo $urlorder . 'category'; ?>"><?php echo __('Category','stray-quotes') ?></a>
			<?php } else { echo __('Category','stray-quotes');
				if ($sort == 'ASC') { ?><a href="<?php echo $urlsort . 'DESC'; ?>">
                <img src= <?php echo $imgasc ?> alt="Descending" title="Descending" /> <?php }
				else if ($sort == 'DESC') { ?><a href="<?php echo $urlsort . 'ASC'; ?>">
				<img src= <?php echo $imgdsc ?> alt="Ascending" title="Ascending" /> <?php } ?>
			</a>			
			<?php } }else{ echo __('Category','stray-quotes'); } ?>            
            </th>
            
            <th scope="col" style="white-space: nowrap;"> <?php if ($numrows != 1) { if ( $orderby != 'visible') { ?>
            <a href="<?php echo $urlorder . 'visible'; ?>"><?php echo __('Visible','stray-quotes') ?></a>
			<?php } else { echo __('Visible','stray-quotes');
				if ($sort == 'ASC') { ?><a href="<?php echo $urlsort . 'DESC'; ?>">
                <img src= <?php echo $imgasc ?> alt="Descending" title="Descending" /> <?php }
				else if ($sort == 'DESC') { ?><a href="<?php echo $urlsort . 'ASC'; ?>">
				<img src= <?php echo $imgdsc ?> alt="Ascending" title="Ascending" /> <?php } ?>
			</a>			
			<?php }}else{ echo __('Visible','stray-quotes'); }  ?>            
            </th>
            
			<th scope="col">&nbsp;</th>
			<th scope="col">&nbsp;</th>
			
			</tr></thead><tbody><?php
		
		$i = 0;

		foreach ( $quotes as $quote ) {
		
			$alt = ($i % 2 == 0) ? ' class="alternate"' : '';
	
			?> <tr <?php echo($alt); ?>>
				
				<th scope="row"><?php echo ($quote->quoteID); ?></th>
				<td><?php echo(nl2br($quote->quote)); ?></td>
				<td><?php echo($quote->author); ?></td>
				<td><?php echo($quote->source); ?></td>
                <td><?php if ($quote->category == 'default')echo('<em>'.$quote->category.'</em>'); else echo $quote->category;?></td>
				<td align="center"><?php echo $quote->visible; ?></td>
									
				<td align="center">
				<a href="<?php echo $urlaction . '&qa=edit&qi='.$quote->quoteID ; ?>">
				<?php echo __('Edit','stray-quotes') ?></a></td>

				<td align="center">
				<a href="
				<?php echo $urlaction . '&qa=delete&qi='.$quote->quoteID; ?>"
				onclick="if ( confirm('<?php echo __(
				'You are about to delete quote '.$quote->quoteID.'.\\n\\\'Cancel\\\' to stop, \\\'OK\\\' to delete.\'',
				'stray-quotes'); ?>) ) { return true;}return false;"><?php echo __('Delete','stray-quotes') ?></a></td>			
			</tr>
			<?php $i++; 
		} ?>
		</tbody></table><p class="subsubsub" style="float:right"><?php
		echo $first . $prev . $nav . $next . $last; ?></p><?php
		
	} else { ?><p><div style="clear:both"> <?php echo __('<br/>No quotes yet.','stray-quotes') ?> </div></p>

	</div><?php	}	
	
?></div><?php

}


?>