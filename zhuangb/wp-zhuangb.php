<?php
/*
Plugin Name: ZhuangbComment.
Plugin URI: http://www.ll19.com/?p=113
Description: Insert comments in your post with [zhuangb event="mouseover" id="1" title="Leave a reply"]~
Version: 1.2.3
Author: LL19.com
Author URI: http://www.ll19.com/
*/

if (!isset ($wpdb)) {
	zhuangb_init();
}

class wp_zhuangb {
	function install() {
		global $wpdb;
		$result = mysql_query("CREATE TABLE `$wpdb->zhuangb_comments`(`commentid` INT( 20 ) NOT NULL ,`postid` INT( 20 ) NOT NULL ,`zhuangbid` INT( 5 ) NOT NULL ,PRIMARY KEY ( `commentid` ))", $wpdb->dbh) or die(mysql_error() . ' on line: ' . __LINE__);
		if (!$result) {
			return false;
		}
		return true;
	}
}

/*
creat table zhuangb_comments.
*/
function zhuangb_init() {
	global $wpdb, $wpzhuangb;

	$wpdb->zhuangb_comments = $wpdb->prefix . 'zhuangb_comments';
	$wpzhuangb = new wp_zhuangb;
	$result = mysql_list_tables(DB_NAME);
	$tables = array ();
	while ($row = mysql_fetch_row($result)) {
		$tables[] = $row[0];
	}
	if (!in_array($wpdb->zhuangb_comments, $tables)) {
		$wpzhuangb->install();
	}
}

/*
add js&&css
*/
add_action('wp_head', 'zhuangb_css_js');
function zhuangb_css_js() {
	echo "\n" . '<!-- Start Of Script Generated By wp-zhuangb-comment 1.2.2 -->' . "\n";
	if (@ file_exists(TEMPLATEPATH . '/jquery-1.2.6.pack.js')) {
		echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/zhuangb.css" type="text/css" media="screen" />' . "\n";
		wp_print_scripts('jquery');
		echo '<script src="' . get_stylesheet_directory_uri() . '/jquery.zhuangb.js" type="text/javascript"></script>' . "\n";
		echo '<script src="' . get_stylesheet_directory_uri() . '/jquery.form.js" type="text/javascript"></script>' . "\n";
	} else {
		echo '<link rel="stylesheet" href="' . WP_PLUGIN_URL . '/zhuangb/zhuangb.css" type="text/css" media="screen" />' . "\n";
		wp_print_scripts('jquery');
		echo '<script src="' . WP_PLUGIN_URL . '/zhuangb/jquery.zhuangb.js" type="text/javascript"></script>' . "\n";
		echo '<script src="' . WP_PLUGIN_URL . '/zhuangb/jquery.form.js" type="text/javascript"></script>' . "\n";
	}
	echo '<!-- End Of Script Generated By wp-zhuangb-comment 1.2.2 -->' . "\n";
}

/*
add post
*/
add_filter('the_content', 'zhuangb_post_add', 0);
//add_filter('the_excerpt', 'wp_syntax_before_filter', 0);
function zhuangb_post_add($content) {
	global $zhuangb_post_out;
	preg_match_all("/\[zhuangb(.+)\]/siU", $content, $results, PREG_SET_ORDER);
	if ($results) {
		foreach ($results as $item) {
			$zhuangbtag = $item[0];
			//echo $item[0];
			preg_match_all("/\[zhuangb\s*event=\"(.*)\"\s*id=\"(.*)\"\s*title=\"(.*)\"\s*\]/siU", $zhuangbtag, $resultstags, PREG_SET_ORDER);
			if ($resultstags) {
				foreach ($resultstags as $itemtags) {
					$zhuangbevent = $itemtags[1];
					$zhuangbid = $itemtags[2];
					$zhuangbtitle = $itemtags[3];
					$out = "<br><a class=\"zhuangbtext\" href=\"javascript:void(0);\" id=\"post_" . get_the_ID() . "_" . $zhuangbid . "\">" . $zhuangbtitle . "<sup>[!!]</sup></a>";
					$out .= "<div id=\"post_" . get_the_ID() . "_" . $zhuangbid . "_zhuangb\" class=\"zhuangbWin\"></div>";
					$out .= "<div class=\"zhuangbdiv\">";
					if (@ file_exists(TEMPLATEPATH . '/wp-zhuangb.php')) {
						$out .= "<object id=\"zhuangb_flash_" . get_the_ID() . "_" . $zhuangbid . "\" type=\"application/x-shockwave-flash\" data=\"" . get_stylesheet_directory_uri() . "/zhuangb.swf\" width=\"515\" height=\"22\">";
						$out .= "<aram name=\"movie\" value=\"" . get_stylesheet_directory_uri() . "/zhuangb.swf\" />";
					} else {
						$out .= "<object id=\"zhuangb_flash_" . get_the_ID() . "_" . $zhuangbid . "\" type=\"application/x-shockwave-flash\" data=\"" . WP_PLUGIN_URL . "/zhuangb/zhuangb.swf\" width=\"515\" height=\"22\">";
						$out .= "<param name=\"movie\" value=\"" . WP_PLUGIN_URL . "/zhuangb/zhuangb.swf\" />";
					}
					$out .= "<param name=\"BGCOLOR\" value=\"\" />";
					$out .= "<param name=\"quality\" value=\"high\" />";
					$out .= "<param name=\"wmode\" value=\"transparent\" />";
					$out .= "</object><div class=\"zhuangblist\" id=\"postlist_" . get_the_ID() . "_" . $zhuangbid . "_zhuangb\">&nbsp;&nbsp;&nbsp;Loading......</div>";
					$out .= "<script type=\"text/javascript\">";
					$out .= "jQuery(document).ready(function() {";
					$out .= "ajaxZhuangBList(\"" . get_settings('siteurl') . "\",\"" . get_the_author_email() . "\"," . $zhuangbid . "," . get_the_ID() . ",\"" . $zhuangbevent . "\");";
					$out .= "});";
					$out .= "</script>";
					$out .= "</div>";
					$zhuangb_post_out = $out;
				}
			}
			$content = str_replace($item[0], $zhuangb_post_out, $content);
		}
	}
	return $content;
}

/*
add admin page
*/
add_action('admin_menu', 'wp_zhuangb_admin_page');
function wp_zhuangb_admin_page() {
	if (function_exists('add_options_page')) {
		add_options_page( __('Zhuangb Admin Page','zhuangb_admin_page'), __('Zhuangb Admin Page','zhuangb_admin_page'), 8, basename(__FILE__), 'wp_zhuangb_admin_page_form');
	}
}
function wp_zhuangb_admin_page_form() {
	$zhuangb_delete_form = "";
	if (@ file_exists(TEMPLATEPATH . '/comments-ajax-zhuangb.php')) {
		$zhuangb_delete_form =  get_stylesheet_directory_uri() . "/comments-ajax-zhuangb.php";
	} else {
		$zhuangb_delete_form =  WP_PLUGIN_URL . "/zhuangb/comments-ajax-zhuangb.php";
	}
?>
	<div style="padding:50px;">
	<h2>
	<script type="text/javascript">
	var tmp = "\u5982\u679c\u4f60\u5220\u9664\u4e86\u5728\u88c5B\u7559\u8a00\u4e2d\u7684\u7559\u8a00\uff0c\u53ef\u4ee5\u70b9\u51fb\u4e0b\u9762\u7684\u6309\u94ae\u6e05\u9664\u76f8\u5173\u7684zhuangb\u8868\u7684\u65e0\u7528\u4fe1\u606f\uff0c\u6bcf\u4e00\u6bb5\u65f6\u95f4\u6267\u884c\u4e00\u6b21\u5373\u53ef\u3002";
	document.writeln(tmp);
	</script>
	</h2>
	<div id="zhuangb_call_back" style="display:none; margin: 20px;"></div>
	<form action="<?php echo $zhuangb_delete_form;?>" method="post" id="zhuangb_delete">
	<input type="button" value="Click This Button." id="zhuangb_delete_button"/>
	</form>
	<script type="text/javascript">
	jQuery('#zhuangb_delete_button').click(function() {
		jQuery.ajax({
		url : "<?php echo $zhuangb_delete_form;?>",
		dataType : "html",
		type : "post",
		data : "zhuangbaction=delete",
		success : function(msg) {
			jQuery("#zhuangb_call_back").html(msg);
			jQuery("#zhuangb_call_back").slideDown(); 
		},
		error : function(msg) {
			jQuery("#zhuangb_call_back").html(msg);
			jQuery("#zhuangb_call_back").slideDown(); 
		}
		})
	})
	</script>
	</div>
<?php
}
?>
