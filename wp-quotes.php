<?php
/*
Plugin Name: Random Quotes
Plugin URI: http://www.blogmotron.com/
Description: This plugin allows you to embed random quotes into your pages. It also has a spiffy management tool in the administrative console.
Author: Dustin Barnes
Author URI: http://www.blogmotron.com
Version: 1.0
*/

// Quotes table. I suppose you could change this.
define('WP_QUOTES_TABLE', $table_prefix . 'quotes');

/*
 * Puts the Quotes manager thingie under the "manage" tab. 
 */
function wp_quotes_admin_menu($content)
{
	global $submenu;
	$submenu['edit.php'][40] = array(__('Quotes'), 8, 'edit-quotes.php');
}
add_action('admin_menu', 'wp_quotes_admin_menu');


/*
 * This is the main function you call to embed a quote
 */
function wp_quotes_random()
{
	global $wpdb;
	
	$sql = "select * from " . WP_QUOTES_TABLE . " where visible='yes'";
	
	$result = $wpdb->get_results($sql);
	
	if ( !empty($result) )
		wp_quotes_spew($result[mt_rand(0, count($result)-1)]);
}


/*
 * You can use this to print a specific quote
 */
function wp_quotes($id)
{
	global $wpdb;
	
	$sql = "select * from " . WP_QUOTES_TABLE . " where quoteID='{$id}'";
	
	$result = $wpdb->get_results($sql);
	
	if ( !empty($result) )
		wp_quotes_spew($result[0]);
}

/*
 * Actually spews a quote
 */
function wp_quotes_spew($quote)
{
	?>
	<div id="wp_quotes">
		<div class="wp_quotes_quote"><?php echo nl2br($quote->quote); ?></div>
		<?php
		if ( !empty($quote->author) )
		{
			?>
			<div class="wp_quotes_author"><?php echo $quote->author;?></div>
			<?php
		}
		?>
	</div>
	<?php
}

?>