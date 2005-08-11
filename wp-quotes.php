<?php
/*
Plugin Name: Random Quotes
Plugin URI: http://www.zombierobot.com/wp-quotes/
Description: This plugin allows you to embed random quotes into your pages. It also has a spiffy management tool in the administrative console.
Author: Dustin Barnes
Author URI: http://www.zombierobot.com/
Version: 1.2
*/

// Quotes table. I suppose you could change this.
define('WP_QUOTES_TABLE', $table_prefix . 'quotes');

// Quotes Page Delimiter. I suppose you could change this too...
define('WP_QUOTES_PAGE', '<!--wp_quotes_page-->');


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
function wp_quotes_spew($quote, $encloseDiv='id="wp_quotes"', $quoteDiv='wp_quotes_quote', $authorDiv='wp_quotes_author')
{
	?>
	<div "<?php echo $encloseDiv?>">
		<div class="<?php echo $quoteDiv?>"><?php echo nl2br($quote->quote); ?></div>
		<?php
		if ( !empty($quote->author) )
		{
			?>
			<div class="<?php echo $authorDiv?>"><?php echo $quote->author;?></div>
			<?php
		}
		?>
	</div>
	<?php
}


/**
 * Spews all the quotes onto a page
 */
function wp_quotes_page($data)
{
	$start = strpos($data, WP_QUOTES_PAGE);
	
	if ( $start !== false )
	{
		ob_start();
		
		global $wpdb;
		
		$sql = "select * from " . WP_QUOTES_TABLE;
		
		$result = $wpdb->get_results($sql);
		
		if ( !empty($result) )
		{
			$count = 0;
			foreach ( $result as $row )
			{
				if ( $count++ > 0 )
					echo "<hr class=\"wp_quotepage_hr\">\n";
					
				wp_quotes_spew($row, 'class="wp_quotepage"', 'wp_quotepage_quote', 'wp_quotepage_author');
				
			}
		}

		echo "<div class=\"wpquotes_poweredby\">Powered by <a href=\"http://www.zombierobot.com/\">Zombie Robot wp-quotes plugin</a>.</div>\n";
		
		$contents = ob_get_contents();
		ob_end_clean();
		
		$data = substr_replace($data, $contents, $start, strlen(WP_QUOTES_PAGE));
	}
	
	return $data;
}
add_filter('the_content', 'wp_quotes_page', 10);
?>
