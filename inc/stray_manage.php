<?php 

//manage page
function stray_manage() {

	?><div class="wrap"><h2><?php echo __('Manage quotes','stray-quotes') ?></h2><?php    
    
	global $wpdb;
	$quotesoptions = get_option('stray_quotes_options');
	//decode and intercept
	foreach($_POST as $key => $val)$_POST[$key] = stripslashes($val); 
	// Global variable cleanup. 
	$edit = $create = $save = $delete = false;	
		
	//defaults and gets
	$action = !empty($_REQUEST['qa']) ? $_REQUEST['qa'] : '';
	$quoteID = !empty($_REQUEST['qi']) ? $_REQUEST['qi'] : '';
	$orderby = $quotesoptions['stray_quotes_order'];
	$pages = 1;
	$rows = $quotesoptions['stray_quotes_rows']; 
	$groups = $quotesoptions['stray_quotes_groups']; 
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
		$groups = $_GET['qg'];
		$quotesoptions['stray_quotes_groups'] = $_GET['qg'];	
	}
	
	if(isset($_GET['qs'])){
		$sort = $_GET['qs'];
		$quotesoptions['stray_quotes_sort'] = $_GET['qs'];		
	}
	
	$offset = ($pages - 1) * $rows;
	update_option('stray_quotes_options', $quotesoptions);
	
	//urls for different use (primitive, I know)
	$baseurl = get_option("siteurl").'/wp-admin/admin.php?page=stray_manage';
	$urlorder = $baseurl.'&qp='.$pages.'&qr='.$rows.'&qg='.$groups.'&qs='.$sort.'&qa='.$action.'&qi='.$quoteID.'&qo=';	
	$urlpages = $baseurl.'&qo='.$orderby.'&qr='.$rows.'&qg='.$groups.'&qs='.$sort.'&qa='.$action.'&qi='.$quoteID.'&qp=';
	$urlrows = $baseurl.'&qo='.$orderby.'&qp='.$pages.'&qg='.$groups.'&qs='.$sort.'&qa='.$action.'&qi='.$quoteID.'&qr=';
	$urlgroup = $baseurl.'&qo='.$orderby.'&qp='.$pages.'&qr='.$rows.'&qs='.$sort.'&qa='.$action.'&qi='.$quoteID.'&qg=';
	$urlsort = $baseurl.'&qo='.$orderby.'&qp='.$pages.'&qr='.$rows.'&qg='.$groups.'&qa='.$action.'&qi='.$quoteID.'&qs=';
	$urlaction = $baseurl.'&qo='.$orderby.'&qp='.$pages.'&qr='.$rows.'&qg='.$groups.'&qs='.$sort; 
		
	//if the page is opened after a edit action
	if ( $action == 'edit' ) {
	
		//check if something went wrong with quote id
		if ( empty($quoteID) ) {
			?><div id="message" class="error"><p><?php echo __(
			'Something is wrong. No quote ID from the query string.','stray-quotes') ?></p></div><?php
		}
		
		else {			
			
			//query
			$data = $wpdb->get_results("select * from " . WP_STRAY_QUOTES_TABLE . " where quoteID='" 
			. mysql_real_escape_string($quoteID) . "' limit 1");
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
			$styleborder = 'style="border:1px solid #ccc"';
			$styletextarea = 'style="border:1px solid #ccc; font-family: Times New Roman, Times, serif; font-size: 1.4em;"'; ?>
            <div>
			<script src="<?php echo WP_STRAY_QUOTES_PATH ?>/inc/js_quicktags-mini.js" type="text/javascript"></script>
            <form name="quoteform" id="quoteform" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?> ">
				<input type="hidden" name="qa" value="edit_save">
				<input type="hidden" name="qi" value="<?php echo $quoteID; ?>">
			
				<p><label><?php echo __('Quote:','stray-quotes') ?></label><br />
                <script type="text/javascript">edToolbar();</script>
                <textarea id="qeditor" name="quote_quote" <?php echo $styletextarea ?> cols=68 rows=7><?php echo $quote; ?></textarea></p>
				<script type="text/javascript">var edCanvas = document.getElementById('qeditor');</script>

				<p><label><?php echo __('Author:','stray-quotes') ?></label>
                <input type="text" name="quote_author" size=65 value="<?php echo $author ?>" <?php echo $styleborder ?> /></p>
				
				<p><label><?php echo __('Source:','stray-quotes') ?></label>
                <input type="text" name="quote_source" size=65 value="<?php echo $source ?>" <?php echo $styleborder ?> /></p>
                
                <p><label><?php echo __('Group:&nbsp;','stray-quotes') ?></label>
                
                <select name="groups" style="vertical-align:middle; width:17em;"> 
                <?php $grouplist = make_groups(); 
                foreach($grouplist as $groupo){ ?>
                <option value="<?php echo $groupo; ?>" style=" padding-right:5px"
                <?php  if ( $groupo == $group) echo ' selected'; ?> >
                <?php echo $groupo;?></option>
                <?php } ?>   
                </select>
                  
                <label><?php echo __('new group:&nbsp;','stray-quotes') ?></label>
                <input type="text" name="quote_group" size=26 value=""  maxlength="25" <?php echo $styleborder ?> /></p>
                
				<p><label><?php echo __('Visible:','stray-quotes') ?></label>
					<input type="radio" name="quote_visible" class="input" value="yes"<?php echo $visible_yes ?> /> <?php echo __('Yes','stray-quotes') ?>					
					<input type="radio" name="quote_visible" class="input" value="no"<?php echo $visible_no ?> /> <?php echo __('No','stray-quotes') ?></div>
				</p><p>&nbsp;</p>
				<p> <a href=" <?php echo $urlaction ?>">Cancel</a>&nbsp;
            <input type="submit" name="save"  class="button-primary" value="<?php echo __('Save quote','stray-quotes') ?> &raquo;" /></p>
            
                				
			</form><p>&nbsp;</p></div><?php 
	
		}	
	}	
	
	//after having saved the quote
	else if ( $action == 'edit_save' ) {
	
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
		
		if ( empty($quoteID) )	{
			?><div id="message" class="error fade"><p><?php echo __(
			'<strong>Failure:</strong> No quote ID given.','stray-quotes') ?></p></div><?php
		}
		
		else {		
			$sql = "update " . WP_STRAY_QUOTES_TABLE 
			. " set quote='" . mysql_real_escape_string($quote)
			. "', author='" . mysql_real_escape_string($author) 
			. "', source='" . mysql_real_escape_string($source) 
			. "', `group`='" . mysql_real_escape_string($group)
			. "', visible='" . mysql_real_escape_string($visible) 
			. "' where quoteID='" . mysql_real_escape_string($quoteID) . "'";		     
			$wpdb->get_results($sql);
			
			$sql = "select quoteID from " . WP_STRAY_QUOTES_TABLE 
			. " where quote='" . mysql_real_escape_string($quote) 
			. "' and author='" . mysql_real_escape_string($author) 
			. "' and source='" . mysql_real_escape_string($source) 
			. "' and `group`='" . mysql_real_escape_string($group) 
			. "' and visible='" . mysql_real_escape_string($visible) . "' limit 1";
			$result = $wpdb->get_results($sql);
			
			if ( empty($result) || empty($result[0]->quoteID) )	{			
				?><div id="message" class="error fade"><p><?php echo __(
				'<strong>Failure:</strong> Something went wrong.','stray-quotes') ?></p></div><?php				
			}
			else {			
				?><div id="message" class="updated fade"><p><?php echo str_replace("%s",$quoteID,__(
				'Quote %s updated.','stray-quotes'));?></p></div><?php
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
				'Quote %s deleted.','stray-quotes')); ?></p></div><?php
			}			
			else {						
				?><div class="error fade"><p><?php echo __(
				'<strong>Failure:</strong> Nothing deleted.','stray-quotes'); ?></p></div><?php	
			}		
		}
	}
		
	// prepares group for sql
	$where = '';
	if (!$groups || $groups == 'all') $where = '';
	else $where = " WHERE `group`='" . $groups . "'";
	
	// how many rows we have in database
	$result = $wpdb->get_results("select quoteID from " . WP_STRAY_QUOTES_TABLE . $where);
	$numrows = count($result);
	
	//temporary workaround for the "division by zero" problem
	if (is_string($rows))$rows=intval($rows);
	settype($rows, "integer"); 
	
	// how many pages we have when using paging?
	if ($rows == NULL || $rows > 10) $rows = 10; 
	$maxPage = ceil($numrows/$rows);
		
	
	// print the link to access each page
	$nav  = '';
	
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
	   $quotepage = $pages + 1;
	   $next = ' | <a href="'.$urlpages.$quotepage.'"> Next '.$rows.'</a> ';
	
	   $last = ' | <a href="'.$urlpages.$maxPage.'"> Last</a> ';
	}
	else {
	   $next = '&nbsp;'; // we're on the last page, don't print next link
	   $last = '&nbsp;'; // nor the last page link
	}		

	//get all the quotes
	$sql = "SELECT * FROM " 
	. WP_STRAY_QUOTES_TABLE 
	. $where
	. " ORDER BY `". $orderby ."`"
	. $sort 
	. " LIMIT " . $offset. ", ". $rows;
	
	$quotes = $wpdb->get_results($sql);

	//HTML
	?><p class="subsubsub" style="float:left"> quotes per page: 
    <select name="lines" onchange="switchpage(this)"  style="vertical-align:middle">
    <option value=<?php echo $urlrows.'10'; if ( $rows == 10) echo ' selected';  ?> >10</option>
    <option value=<?php echo $urlrows.'15'; if ( $rows == 15) echo ' selected'; ?> >15</option>
    <option value=<?php echo $urlrows.'20'; if ( $rows == 20) echo ' selected'; ?> >20</option>
	<option value=<?php echo $urlrows.'30'; if ( $rows == 30) echo ' selected'; ?> >30</option>
    <option value=<?php echo $urlrows.'50'; if ( $rows == 50) echo ' selected'; ?> >50</option>
    <option value=<?php echo $urlrows.'100'; if ( $rows == 100) echo ' selected'; ?> >100</option>
	</select> | show group: 
    
    <select name="groups" onchange="switchpage(this)"  style="vertical-align:middle"> 
    <option value="<?php echo $urlgroup.'all'; ?>" 
	<?php  if ( $groups == '' || $groups == 'all' ) echo ' selected'; ?>>all</option>
    <?php $grouplist = make_groups(); 
	foreach($grouplist as $groupo){ ?>
    	<option value="<?php echo $urlgroup.$groupo; ?>" 
		<?php  if ( $groups) {if ( $groups == $groupo) echo ' selected';} ?> >
		<?php echo $groupo;?></option>
	<?php } ?>   
    </select>
    </p>
	<p class="subsubsub" style="float:right"><?php echo $first . $prev . $nav . $next . $last; ?></p><?php
	
	//build table
	if ( !empty($quotes) ) {
		$imgasc = WP_STRAY_QUOTES_PATH . 'img/s_asc.png';
		$imgdsc = WP_STRAY_QUOTES_PATH . 'img/s_desc.png';
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
            
            <th scope="col" style="white-space: nowrap;"> <?php if ($numrows != 1) { if ( $orderby != 'group') { ?>
            <a href="<?php echo $urlorder . 'group'; ?>"><?php echo __('Group','stray-quotes') ?></a>
			<?php } else { echo __('Group','stray-quotes');
				if ($sort == 'ASC') { ?><a href="<?php echo $urlsort . 'DESC'; ?>">
                <img src= <?php echo $imgasc ?> alt="Descending" title="Descending" /> <?php }
				else if ($sort == 'DESC') { ?><a href="<?php echo $urlsort . 'ASC'; ?>">
				<img src= <?php echo $imgdsc ?> alt="Ascending" title="Ascending" /> <?php } ?>
			</a>			
			<?php } }else{ echo __('Group','stray-quotes'); } ?>            
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
                <td><?php if ($quote->group == 'default')echo('<em>'.$quote->group.'</em>'); else echo $quote->group;?></td>
				<td align="center"><?php echo $quote->visible; ?></td>
									
				<td align="center">
				<a href="<?php echo $urlaction . '&qa=edit&qi='.$quote->quoteID ; ?>">
				<?php echo __('Edit','stray-quotes') ?></a></td>

				<td align="center">
				<a href="
				<?php echo $urlaction . '&qa=delete&qi='.$quote->quoteID; ?>"
				onclick="if ( confirm('<?php echo __(
				'You are about to delete this quote\\n\\\'Cancel\\\' to stop, \\\'OK\\\' to delete.\'',
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