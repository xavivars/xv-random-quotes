<?php
/*
Author: Dustin Barnes
Author URI: http://www.blogmotron.com
Description: Admin tool for the wp-quotes plugin.
*/

require_once('admin.php');
$title = __('Quotes');
$parent_file = 'edit.php';

// Global variable cleanup. 
$edit = $create = $save = $delete = false;

// How to control the app
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$quoteID = !empty($_REQUEST['quoteID']) ? $_REQUEST['quoteID'] : '';

// Messages for the user
$debugText = '';
$messages = '';


require_once('admin-header.php');
//get_currentuserinfo();
?>

<style type="text/css">
<!--
	.error
	{
		background: lightcoral;
		border: 1px solid #e64f69;
		margin: 1em 5% 10px;
		padding: 0 1em 0 1em;
	}

	.center		{ text-align: center;	}
	.right		{ text-align: right;	}
	.left		{ text-align: left;		}
	.top		{ vertical-align: top;	}
	.bold		{ font-weight: bold;	}
	.private	{ color: #e64f69;		}
//-->
</style>

<?php

//////////////    Start First Run Check&Run   /////////////////
$tableExists = false;
$tables = $wpdb->get_results("show tables;");

foreach ( $tables as $table )
{
	foreach ( $table as $value )
	{
		if ( $value == WP_QUOTES_TABLE )
		{
			$tableExists=true;
			break;
		}
	}
}

if ( !$tableExists )
{
	$sql = "CREATE TABLE `" . WP_QUOTES_TABLE . "` (
				`quoteID` INT(11) NOT NULL AUTO_INCREMENT ,
				`quote` TEXT NOT NULL ,
				`author` varchar( 255 ) NOT NULL ,
				`visible` ENUM( 'yes', 'no' ) NOT NULL ,
				PRIMARY KEY ( `quoteID` )
			)";
	$wpdb->get_results($sql);
	
	$sql = "INSERT INTO `" . WP_QUOTES_TABLE . "` (quote, author, visible) values "
	     . "('Each place has its own advantages - heaven for the climate, and hell for the society.', 'Mark Twain', 'yes'), "
	     . "('An invasion of armies can be resisted, but not an idea whose time has come.', '', 'yes'), "
	     . "('Love is a snowmobile racing across the tundra and then suddenly it flips over, pinning you underneath. At night, the ice weasels come.', 'Matt Groening', 'yes')";
	$wpdb->get_results($sql);

	echo "<div class=\"updated\"><p><strong>Hola, bitchola</strong><br /><br />This seems to be your first time visiting this page. I've created a database table for you (" . WP_QUOTES_TABLE . "), and put a few quotes in there to start you off. Feel free to delete them. If you want to remove the data, make sure to delete that table after deactivating the plugin. This plugin's website is at <a href=\"http://www.zombierobot.com/\">http://www.zombierobot.com/</a></p></div>";
}
///////////////      End first run      ///////////////


///////////////   Handle any manipulations   //////////////////
if ( $action == 'add' )
{
	$quote = !empty($_REQUEST['quote_quote']) ? $_REQUEST['quote_quote'] : '';
	$author = !empty($_REQUEST['quote_author']) ? $_REQUEST['quote_author'] : '';
	$visible = !empty($_REQUEST['quote_visible']) ? $_REQUEST['quote_visible'] : '';
	
	// why do people leave this crap on?! turn it OFF OFF OFF!
	if ( ini_get('magic_quotes_gpc') )
	{
		$quote = stripslashes($quote);
		$author = stripslashes($author);
		$visible = stripslashes($visible);	
	}	

	$sql = "insert into " . WP_QUOTES_TABLE . " set quote='" . mysql_escape_string($quote)
	     . "', author='" . mysql_escape_string($author) . "', visible='" . mysql_escape_string($visible) . "'";
	     
	$wpdb->get_results($sql);
	
	$sql = "select quoteID from " . WP_QUOTES_TABLE . " where quote='" . mysql_escape_string($quote) . "'"
	     . " and author='" . mysql_escape_string($author) . "' and visible='" . mysql_escape_string($visible) . "' limit 1";
	$result = $wpdb->get_results($sql);
	
	if ( empty($result) || empty($result[0]->quoteID) )
	{
		?>
		<div class="error"><p><strong>Failure:</strong> Holy crap you destroyed the internet! That, or something else went wrong when I tried to insert the quote. Try again? </p></div>
		<?php
	}
	else
	{
		?>
		<div class="updated"><p>Freaking sweet. You just added quote id <?php echo $result[0]->quoteID;?> to the database.</p></div>
		<?php
	}
}
elseif ( $action == 'edit_save' )
{
	$quote = !empty($_REQUEST['quote_quote']) ? $_REQUEST['quote_quote'] : '';
	$author = !empty($_REQUEST['quote_author']) ? $_REQUEST['quote_author'] : '';
	$visible = !empty($_REQUEST['quote_visible']) ? $_REQUEST['quote_visible'] : '';
	
	// why do people leave this crap on?! turn it OFF OFF OFF!
	if ( ini_get('magic_quotes_gpc') )
	{
		$quote = stripslashes($quote);
		$author = stripslashes($author);
		$visible = stripslashes($visible);	
	}
	
	if ( empty($quoteID) )
	{
		?>
		<div class="error"><p><strong>Failure:</strong> No quote ID given. Can't save nothing. Giving up...</p></div>
		<?php		
	}
	else
	{
		$sql = "update " . WP_QUOTES_TABLE . " set quote='" . mysql_escape_string($quote)
		     . "', author='" . mysql_escape_string($author) . "', visible='" . mysql_escape_string($visible) . "'"
		     . " where quoteID='" . mysql_escape_string($quoteID) . "'";
		     
		$wpdb->get_results($sql);
		
		$sql = "select quoteID from " . WP_QUOTES_TABLE . " where quote='" . mysql_escape_string($quote) . "'"
		     . " and author='" . mysql_escape_string($author) . "' and visible='" . mysql_escape_string($visible) . "' limit 1";
		$result = $wpdb->get_results($sql);
		
		if ( empty($result) || empty($result[0]->quoteID) )
		{
			?>
			<div class="error"><p><strong>Failure:</strong> The Evil Monkey wouldn't let me update your quote. Try again? </p></div>
			<?php
		}
		else
		{
			?>
			<div class="updated"><p>Quote <?php echo $quoteID;?> updated successfully</p></div>
			<?php
		}		
	}
}
elseif ( $action == 'delete' )
{
	if ( empty($quoteID) )
	{
		?>
		<div class="error"><p><strong>Failure:</strong> No quote ID given. I guess I deleted nothing successfully.</p></div>
		<?php			
	}
	else
	{
		$sql = "delete from " . WP_QUOTES_TABLE . " where quoteID='" . mysql_escape_string($quoteID) . "'";
		$wpdb->get_results($sql);
		
		$sql = "select quoteID from " . WP_QUOTES_TABLE . " where quoteID='" . mysql_escape_string($quoteID) . "'";
		$result = $wpdb->get_results($sql);
		
		if ( empty($result) || empty($result[0]->quoteID) )
		{
			?>
			<div class="updated"><p>Quote <?php echo $quoteID;?> deleted successfully</p></div>
			<?php
		}
		else
		{
			?>
			<div class="error"><p><strong>Failure:</strong> Ninjas proved my kung-fu to be too weak to delete that quote.</p></div>
			<?php

		}		
	}
}
//////////////   Heh.. I said manipulation ////////////////////

?>

<div class="wrap">
	<?php
	if ( $action == 'edit' )
	{
		?>
		<h2><?php _e('Edit Quote'); ?></h2>
		<?php
		if ( empty($quoteID) )
		{
			echo "<div class=\"error\"><p>I didn't get a quote identifier from the query string. Giving up...</p></div>";
		}
		else
		{
			wp_quotes_edit_form('edit_save', $quoteID);
		}	
	}
	else
	{
		?>
		<h2><?php _e('Add Quote'); ?></h2>
		<?php wp_quotes_edit_form(); ?>
	
		<h2><?php _e('Manage Quotes'); ?></h2>
		<?php
			wp_quotes_display_list();
	}
	?>
</div>

<?php
include('admin-footer.php');


/**
 * Display code for the listing
 */
function wp_quotes_display_list()
{
	global $wpdb;
	
	$quotes = $wpdb->get_results("SELECT * FROM " . WP_QUOTES_TABLE . " order by quoteID");
	
	if ( !empty($quotes) )
	{
		?>
		<table width="100%" cellpadding="3" cellspacing="3">
			<tr>
				<th scope="col"><?php _e('ID') ?></th>
				<th scope="col"><?php _e('Quote') ?></th>
				<th scope="col"><?php _e('Author') ?></th>
				<th scope="col"><?php _e('Visible') ?></th>
				<th scope="col"><?php _e('Edit') ?></th>
				<th scope="col"><?php _e('Delete') ?></th>
			</tr>
		<?php
		$class = '';
		foreach ( $quotes as $quote )
		{
			$class = ($class == 'alternate') ? '' : 'alternate';
			?>
			<tr class="<?php echo $class; ?>">
				<th scope="row"><?php echo $quote->quoteID; ?></th>
				<td><?php echo nl2br($quote->quote) ?></td>
				<td><?php echo $quote->author; ?></td>
				<td><?php echo $quote->visible=='yes' ? 'Yes' : 'No'; ?></td>
				<td><a href="edit-quotes.php?action=edit&amp;quoteID=<?php echo $quote->quoteID;?>" class='edit'><?php echo __('Edit'); ?></a></td>
				<td><a href="edit-quotes.php?action=delete&amp;quoteID=<?php echo $quote->quoteID;?>" class="delete" onclick="return confirm('Are you sure you want to delete this quote?')"><?php echo __('Delete'); ?></a></td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
	}
	else
	{
		?>
		<p><?php _e("You haven't entered any quotes yet.") ?></p>
		<?php	
	}
}


/**
 * Display code for the add/edit form
 */
function wp_quotes_edit_form($mode='add', $quoteID=false)
{
	global $wpdb;
	$data = false;
	
	if ( $quoteID !== false )
	{
		// this next line makes me about 200 times cooler than you.
		if ( intval($quoteID) != $quoteID )
		{
			echo "<div class=\"error\"><p>Bad Monkey! No banana!</p></div>";
			return;
		}
		else
		{
			$data = $wpdb->get_results("select * from " . WP_QUOTES_TABLE . " where quoteID='" . mysql_escape_string($quoteID) . "' limit 1");
			if ( empty($data) )
			{
				echo "<div class=\"error\"><p>I couldn't find a quote linked up with that identifier. Giving up...</p></div>";
				return;
			}
			$data = $data[0];
		}	
	}
	
	?>
	<form name="quoteform" id="quoteform" class="wrap" method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
		<input type="hidden" name="action" value="<?php echo $mode?>">
		<input type="hidden" name="quoteID" value="<?php echo $quoteID?>">
	
		<div id="item_manager">
			<div style="float: left; width: 98%; clear: both;" class="top">
				<!-- List URL -->
				<fieldset class="small"><legend><?php _e('Quote'); ?></legend>
					<textarea name="quote_quote" class="input" cols=45 rows=7
					><?php if ( !empty($data) ) echo htmlspecialchars($data->quote); ?></textarea>
				</fieldset>
				
				<fieldset class="small"><legend><?php _e('Author'); ?></legend>
					<input type="text" name="quote_author" class="input" size=45
					value="<?php if ( !empty($data) ) echo htmlspecialchars($data->author); ?>" />
				</fieldset>
				
				<fieldset class="small"><legend><?php _e('Visible'); ?></legend>
					<input type="radio" name="quote_visible" class="input" value="yes" 
					<?php if ( empty($data) || $data->visible=='yes' ) echo "checked" ?>/> Yes
					<br />
					<input type="radio" name="quote_visible" class="input" value="no" 
					<?php if ( !empty($data) && $data->visible=='no' ) echo "checked" ?>/> No
				</fieldset>
				<br />
				<input type="submit" name="save" class="button bold" value="Save &raquo;" />
			</div>
			<div style="clear:both; height:1px;">&nbsp;</div>
		</div>
	</form>
	<?php
}

?>