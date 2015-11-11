<?php

//intro page
function stray_intro() {

	global $wpdb,$current_user;

	//load options
	$quotesoptions = array();
	$quotesoptions = get_option('stray_quotes_options');

	//security check
	if( $quotesoptions['stray_multiuser'] == false && !current_user_can('manage_options') )
		die('Access Denied');

	$widgetpage = admin_url('widgets.php');
	$management = admin_url('admin.php?page=stray_manage');
	$options =  admin_url('admin.php?page=stray_quotes_options');
	$new = admin_url('admin.php?page=stray_new');
	$help =  admin_url('admin.php?page=xv_random_quotes_help');
	$toolspage = admin_url('admin.php?page=stray_tools');
	$straymessage = $quotesoptions['stray_quotes_first_time'];

	//get total quotes
	$totalsql = "SELECT COUNT(`quoteID`) AS `Rows` FROM `" . XV_RANDOMQUOTES_TABLE . "` WHERE `user`='".$current_user->user_nicename."'";
	$totalquotes = $wpdb->get_var($totalsql);

	//feedback following activation (see main file)
	if ($straymessage !="") {

		?><div id="message" class="updated fade"><ul><?php echo $straymessage; ?></ul></div><?php

		//empty message after feedback
		$quotesoptions['stray_quotes_first_time'] = "";
		update_option('stray_quotes_options', $quotesoptions);
	}

	?><div class="wrap"><h2>Stray Random Quotes: <?php _e('Overview','stray-quotes'); ?></h2><?php

	echo STRAY_DIR . '=stray_dir<br/>'. WP_STRAY_QUOTES_PATH . '=WP_STRAY_QUOTES_PATH<br/>';
	echo WP_CONTENT_URL . '=WP_CONTENT_URL<br/>'. WP_SITEURL . '=WP_SITEURL<br/>'
. WP_PLUGIN_URL . '=WP_PLUGIN_URL<br/>' . WP_PLUGIN_DIR . '=WP_PLUGIN_DIR<br/><br/>';
echo ABSPATH . 'wp-content/plugins/' . STRAY_DIR . 'lang<br/>';
echo WP_PLUGIN_DIR. '/'. STRAY_DIR . 'lang<br/>';



    if ($totalquotes > 0) {

		//quotes and categories
		$howmanycategories = count(make_categories($current_user->user_nicename));
		if ($howmanycategories == 1)$howmanycategories = __('one category','stray-quotes');
		else {
			if ($howmanycategories)
				$howmanycategories = $howmanycategories . ' ' . __('categories','stray-quotes');
				$categorymost = mostused("category");
		}
		$sql = "SELECT COUNT( `category` ) AS `Rows` , `category` FROM `" . XV_RANDOMQUOTES_TABLE . "` WHERE `user`='".$current_user->user_nicename."' GROUP BY `category` ORDER BY `Rows` DESC";
		$howmany = $wpdb->get_results($sql);
		if ( count($howmany) > 1) $as = __(', distributed as follows:','stray-quotes');
		else $as = '.';
        $search = array('%s1','%s2', '%s3');
        $replace = array($totalquotes, $howmanycategories, $as);
        echo str_replace ($search,$replace, __('<p>Right now you have <strong>%s1 quotes</strong> in <strong>%s2</strong>%s3</p>','stray-quotes'));
		if ($howmany && count($howmany) > 1) { ?>

			<table class="widefat" style="width:200px"><?php

			$i = 0;

			foreach ( $howmany as $many ) {

				$alt = ($i % 2 == 0) ? ' class="alternate"' : '';

				?><tr <?php echo($alt); ?>>
				<th scope="row"><?php echo $many->Rows; ?></th>
				<td><?php echo $many->category; ?></td>
				</tr><?php
			} ?>
			</table><?php
		}

		//visible quotes
		$visiblequotes = $wpdb->get_var("SELECT COUNT(`quoteID`) as rows FROM " . XV_RANDOMQUOTES_TABLE . " WHERE visible='yes' AND `user`='".$current_user->user_nicename."'");
		if($visiblequotes == $totalquotes)$visiblequotes = __('All your quotes ','stray-quotes');
		echo str_replace ('%s3',$visiblequotes, __('<p><strong>%s3</strong> are visible.</p>','stray-quotes'));

		//author
		$authormost = mostused("author");
		if ($authormost) echo str_replace ('%s5',$authormost, __('<p>Your most quoted author is <strong>%s5</strong>.</p>','stray-quotes'));

		//source
		$sourcemost = mostused("source");
		if ($sourcemost) str_replace ('%s5',$sourcemost, __('<p>Your most used source is <strong>%s5</strong>.</p>','stray-quotes'));

    } else _e('There is nothing to report.','stray-quotes');
    ?><p><?php

	//link pages
    $search = array ("%s1", "%s2");
	$replace = array($new,$management);
	echo str_replace($search,$replace,__('To start doing stuff, you can <a href="%s1"><strong>add new quotes</strong></a>;<br />use the <a href="%s2"><strong>manage</strong></a> page to edit or delete existing quotes;','stray-quotes'));

    if(current_user_can('manage_options')) echo str_replace("%s3",$options,__('<br />change the <a href="%s3"><strong>settings</strong></a> to control how the quotes are displayed on your blog;','stray-quotes'));

	$search2 = array ("%s4","%s5");
    $replace2 = array($help,$toolspage);
	echo str_replace($search2,$replace2,__('<br/>a <a href="%s5"><strong>tools page</strong></a> can help you do more;<br/>if you\'re new to all this, visit the <a href="%s4"><strong>help page</strong></a>.','stray-quotes')); ?>

	</p>

    <p><?php _e('Brought to you by <a href="http://xavi.ivars.me">Xavi Ivars</a>','stray-quotes'); ?>, <?php echo date('Y'); ?>.<br/><?php _e('Happy quoting.','stray-quotes'); ?></p>

    </div><?php

}

