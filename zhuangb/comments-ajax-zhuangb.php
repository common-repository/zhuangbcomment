<?php

/*
for zhuangb~
http://www.ll19.com/
*/

require_once ('../../../wp-config.php');

$wp_zhuangb_action = (string) $_POST["zhuangbaction"];

if ($wp_zhuangb_action == "zhuangbsubmit") {
	wp_zhuangb_comment_submit($table_prefix);
}
if ($wp_zhuangb_action == "zhuangbform") {
	wp_zhuangb_form();
}
if ($wp_zhuangb_action == "zhuangblist") {
	wp_zhuangb_list($table_prefix);
}
if ($wp_zhuangb_action == "delete") {
	wp_zhuangb_delete($table_prefix);
}

/*
submit comment
*/
function wp_zhuangb_comment_submit($table_prefix) {

	global $comment, $comments, $post, $wpdb, $user_ID, $user_identity, $user_email, $user_url;

	foreach ($_POST as $k => $v) {
		$_POST[$k] = urldecode($v);
	}

	$comment_post_ID = (int) $_POST['comment_post_ID'];

	$zhuangb_ID = (int) $_POST['zhuangb_ID'];
	if (empty ($zhuangb_ID))
		wp_die('Sorry,you must be have a zhuangBID.');

	$post_status = $wpdb->get_var("SELECT comment_status FROM $wpdb->posts WHERE ID = '$comment_post_ID'");

	if (empty ($post_status)) {
		do_action('comment_id_not_found', $comment_post_ID);
		wp_die('The post you are trying to comment on does not curently exist in the database.');
	}
	elseif ('closed' == $post_status) {
		do_action('comment_closed', $comment_post_ID);
		wp_die(__('Sorry, comments are closed for this item.'));
	}

	$comment_author = trim($_POST['author']);
	$comment_author_email = trim($_POST['email']);
	$comment_author_url = trim($_POST['url']);
	$comment_content = trim($_POST['comment']);

	// If the user is logged in
	get_currentuserinfo();
	if ($user_ID)
		: $comment_author = addslashes($user_identity);
	$comment_author_email = addslashes($user_email);
	$comment_author_url = addslashes($user_url);
	else
		: if (get_option('comment_registration'))
			wp_die(__('Sorry, you must be logged in to post a comment.'));
	endif;

	$comment_type = '';

	if (get_settings('require_name_email') && !$user_ID) {
		if (6 > strlen($comment_author_email) || '' == $comment_author)
			wp_die(__('Error: please fill the required fields (name, email).'));
		elseif (!is_email($comment_author_email)) wp_die(__('Error: please enter a valid email address.'));
	}

	if ('' == $comment_content)
		wp_die(__('Error: please type a comment.'));
		
	$comment_content = "<blockquote><b><em>This comment in post-".$comment_post_ID."-".$zhuangb_ID."</em></b></blockquote>".$comment_content;

	$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID');

	//insert comment
	$zhuangb_new_comment_ID = wp_new_comment($commentdata);

	if (!$user_ID)
		: setcookie('comment_author_' . COOKIEHASH, stripslashes($comment_author), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie('comment_author_email_' . COOKIEHASH, stripslashes($comment_author_email), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie('comment_author_url_' . COOKIEHASH, stripslashes($comment_author_url), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
	endif;

	if (!empty ($zhuangb_new_comment_ID)) {
		wp_insert_zhuangb($zhuangb_new_comment_ID, $zhuangb_ID, $comment_post_ID, $table_prefix);
	}
}

/*
insert table zhuangb_comments
*/
function wp_insert_zhuangb($zhuangb_new_comment_ID, $zhuangb_ID, $comment_post_ID, $wptable) {
	global $wpdb;
	$query = "INSERT INTO " . $wptable . "zhuangb_comments (commentid, postid, zhuangbid) VALUES (" . $zhuangb_new_comment_ID . ", " . $comment_post_ID . ", " . $zhuangb_ID . ")";
	$wpdb->query($query);
}
?>

<?php

/*
build form
*/
function wp_zhuangb_form() {

	$wp_form_email = (string) $_POST["email"];
	$post_form_id = (int) $_POST["postid"];
	$zhuangb_form_id = (int) $_POST["zhuangbid"];
	$zhuangb_form_win = (string) $_POST["zhuangbwin"];
	$wp_zhuangb_url = (string) $_POST["wpurl"];

	global $user_ID;

	if (@ file_exists(TEMPLATEPATH . '/comments-ajax-zhuangb.php')) {
		echo "<form action=\"" . get_stylesheet_directory_uri() . "/comments-ajax-zhuangb.php\" method=\"post\" id=\"zhuangbform_";
		echo $post_form_id;
		echo "_";
		echo $zhuangb_form_id;
		echo "\" class=\"zhuangbform\">";
	} else {
		echo "<form action=\"" . WP_PLUGIN_URL . "/zhuangb/comments-ajax-zhuangb.php\" method=\"post\" id=\"zhuangbform_";
		echo $post_form_id;
		echo "_";
		echo $zhuangb_form_id;
		echo "\" class=\"zhuangbform\">";
	}
?>
<div class="responseText" id="zhuangbform_<?php echo $post_form_id; ?>_<?php echo $zhuangb_form_id; ?>_responseText"></div>
<?php
	if ($user_ID) :
?>
<label style="float:left">(Logged in)</label>
<?php else : ?>
<div class="formdiv"><input type="text" name="author" id="author" value="" tabindex="1"/>
<label for="author">&nbsp;(NAME)</label></div>
<div class="formdiv"><input type="text" name="email" id="email" value="" tabindex="2"/>
<label for="email">&nbsp;(MAIL)</label></div>
<div class="formdiv"><input type="text" name="url" id="url" value="" tabindex="3" />
<label for="url">&nbsp;(WEBSITE)</label></div>
<?php endif; ?>
<div class="formdiv"><textarea name="comment" id="comment" tabindex="4"></textarea></div>
<div class="formdiv"><input name="submit" type="submit" id="submit" tabindex="5" value="(Submit comment)" class="zhuangbsubmit"/>
<input type="hidden" name="comment_post_ID" value="<?php echo $post_form_id; ?>" />
<input type="hidden" name="zhuangb_ID" value="<?php echo $zhuangb_form_id; ?>" />
<input type="hidden" name="zhuangbaction" value="zhuangbsubmit" />
</div>
</form>
<script type="text/javascript">
jQuery("#zhuangbform_<?php echo $post_form_id; ?>_<?php echo $zhuangb_form_id; ?> #submit").click(function() {
ajaxZhuangBForm("<?php echo $zhuangb_form_id; ?>","<?php echo $post_form_id; ?>","<?php echo $zhuangb_form_win; ?>","<?php echo $wp_zhuangb_url; ?>","<?php echo $wp_form_email; ?>");
});
</script>
<?php
 }
/*
comment list
*/
function wp_zhuangb_list($table_prefix) {
	global $wpdb;

	$zhuangb_glll = $_POST["glll"];
	$zhuangb = $_POST["zhuangb"];
	$zhuangb_page = $_POST["page"];
	$zhuangb_list_email = $_POST["email"];

	if ($zhuangb_glll != "" && $zhuangb != "" && $zhuangb_page != "") {
		$sql = "SELECT comment_author_email, comment_author, comment_author_url, " . $table_prefix . "comments.comment_date, comment_ID, comment_post_ID, comment_content FROM " . $table_prefix . "comments LEFT OUTER JOIN " . $table_prefix . "posts ON (" . $table_prefix . "comments.comment_post_ID = " . $table_prefix . "posts.ID) LEFT OUTER JOIN " . $table_prefix . "zhuangb_comments ON (" . $table_prefix . "comments.comment_post_ID = " . $table_prefix . "zhuangb_comments.postid) WHERE " . $table_prefix . "comments.comment_post_ID = '" . $zhuangb_glll . "' AND " . $table_prefix . "zhuangb_comments.zhuangbid = '" . $zhuangb . "' AND comment_approved = '1' AND comment_type = '' AND post_password = '' AND " . $table_prefix . "zhuangb_comments.commentid in (" . $table_prefix . "comments.comment_ID) ORDER BY comment_date_gmt DESC LIMIT " . ($zhuangb_page -10) . ", 10";
		$sqlcount = "SELECT count(*) AS count FROM " . $table_prefix . "comments LEFT OUTER JOIN " . $table_prefix . "posts ON (" . $table_prefix . "comments.comment_post_ID = " . $table_prefix . "posts.ID) LEFT OUTER JOIN " . $table_prefix . "zhuangb_comments ON (" . $table_prefix . "comments.comment_post_ID = " . $table_prefix . "zhuangb_comments.postid) WHERE " . $table_prefix . "comments.comment_post_ID = '" . $zhuangb_glll . "' AND " . $table_prefix . "zhuangb_comments.zhuangbid = '" . $zhuangb . "' AND comment_approved = '1' AND comment_type = '' AND post_password = '' AND " . $table_prefix . "zhuangb_comments.commentid in (" . $table_prefix . "comments.comment_ID) ";
	} else {
		$sql = "SELECT comment_author_email, comment_author, comment_author_url, " . $table_prefix . "comments.comment_date, comment_ID, comment_post_ID, comment_content FROM " . $table_prefix . "comments LEFT OUTER JOIN " . $table_prefix . "posts ON (" . $table_prefix . "comments.comment_post_ID = " . $table_prefix . "posts.ID) LEFT OUTER JOIN " . $table_prefix . "zhuangb_comments ON (" . $table_prefix . "comments.comment_post_ID = " . $table_prefix . "zhuangb_comments.postid) WHERE " . $table_prefix . "comments.comment_post_ID = '1' AND " . $table_prefix . "zhuangb_comments.zhuangbid = '1' AND comment_approved = '1' AND comment_type = '' AND post_password = '' AND " . $table_prefix . "zhuangb_comments.commentid in (" . $table_prefix . "comments.comment_ID) ORDER BY comment_date_gmt DESC LIMIT 0 , 10"; //显示最近10条
		$sqlcount = "SELECT count(*) AS count FROM " . $table_prefix . "comments LEFT OUTER JOIN " . $table_prefix . "posts ON (" . $table_prefix . "comments.comment_post_ID = " . $table_prefix . "posts.ID) LEFT OUTER JOIN " . $table_prefix . "zhuangb_comments ON (" . $table_prefix . "comments.comment_post_ID = " . $table_prefix . "zhuangb_comments.postid) WHERE " . $table_prefix . "comments.comment_post_ID = '1' AND " . $table_prefix . "zhuangb_comments.zhuangbid = '1' AND comment_approved = '1' AND comment_type = '' AND post_password = '' AND " . $table_prefix . "zhuangb_comments.commentid in (" . $table_prefix . "comments.comment_ID) ";
	}

	$zhuangbcount = $wpdb->get_var($sqlcount);
	$relax_comment_count = 1;

	echo "<h3>&nbsp;&nbsp;Comment List:</h3>";
	echo "<ol>";
	echo "<input type=\"hidden\" id=\"zhuangbcount\" value=\"" . $zhuangbcount . "\" />";
	if ($zhuangbcount == 0) {
		echo "<li class=\"reply\" onMouseOver=\"jQuery(this).css({background:'#FFFFFF', border:'1px solid #ECEEF0'})\"  onMouseOut=\"jQuery(this).css({background:'#FAFAFA', border:'1px solid #ECEEF0'})\">";
		echo "<div class=\"admincommentcount\">0_0</div>";
		$randNum = rand(0, 14);
		if (@ file_exists(TEMPLATEPATH . '/comments-ajax-zhuangb.php')) {
			echo zhuangb_get_avatar($zhuangb_list_email, 40, $default = get_stylesheet_directory_uri() . '/nomail/nomail' . $randNum . '.gif');
		} else {
			echo zhuangb_get_avatar($zhuangb_list_email, 40, $default = WP_PLUGIN_URL . '/zhuangb/nomail/nomail' . $randNum . '.gif');
		}
		echo "<cite>Admin</cite>";
		echo "<small class=\"commentmetadata\">No Comment</small>";
		echo "<div>";
		echo "No comments at the moment~";
		echo "</div>";
		echo "</li>";
	} else {
		$content = $wpdb->get_results($sql);
		global $comment;
		foreach ($content as $comment)
			: $liclass = "<li class=\"reply\" onMouseOver=\"jQuery(this).css({background:'#FFFFFF', border:'1px solid #ECEEF0'})\"  onMouseOut=\"jQuery(this).css({background:'#FAFAFA', border:'1px solid #ECEEF0'})\"";
		if ($comment->comment_author_email == $zhuangb_list_email) {
			$liclass = "<li class=\"adminreply\"";
		}
		echo $liclass;
		$zhuangbPCId = $comment->comment_post_ID . "-" . $comment->comment_ID;
		echo " id=\"zhuangb-" . $zhuangbPCId . "\">";
		$countclass = "<div class=\"commentcount\">";
		if ($comment->comment_author_email == $zhuangb_list_email) {
			$countclass = "<div class=\"admincommentcount\">";
		}
		echo $countclass;
		echo $relax_comment_count;
		echo "</div>";
		$randNum = rand(0, 14);
		if (@ file_exists(TEMPLATEPATH . '/comments-ajax-zhuangb.php')) {
			echo zhuangb_get_avatar($comment->comment_author_email, 40, $default = get_stylesheet_directory_uri() . '/nomail/nomail' . $randNum . '.gif');
		} else {
			echo zhuangb_get_avatar($comment->comment_author_email, 40, $default = WP_PLUGIN_URL . '/zhuangb/nomail/nomail' . $randNum . '.gif');
		}
		echo "<cite>";
		if (empty ($comment->comment_author_url)) {
			echo $comment->comment_author;
		} else {
			echo "<a target=\"_blank\" href=\"" . $comment->comment_author_url . "\">" . $comment->comment_author . "</a>";
		}
		echo "</cite>";
		echo "<small class=\"commentmetadata\">";
		$zhuangbdata = ($comment->comment_date) . " : " . $zhuangbPCId;
		echo $zhuangbdata;
		echo "</small>";
		echo "<div>";
		$apply_filters_comment_text = apply_filters('comment_text', $comment->comment_content);
		if (empty ($apply_filters_comment_text)) $apply_filters_comment_text = $comment->comment_content;
		echo zhuangb_comment_callback($apply_filters_comment_text);
		echo "</div>";
		$relax_comment_count++;
		echo "</li>";
		endforeach;
	}
	echo "</ol>";
}
/*
get gravatar
*/
function zhuangb_get_avatar($email, $size = '40', $default = '') {
	if (!is_numeric($size))
		$size = '40';
	if (empty ($email))
		$default = "http://www.gravatar.com/avatar/?d=$default&amp;s={$size}";
	if (!empty ($email)) {
		$out = 'http://www.gravatar.com/avatar/';
		$out .= md5(strtolower($email));
		$out .= '?s=' . $size;
		$out .= '&amp;d=' . urlencode($default);
		$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
	} else {
		$avatar = "<img alt='' src='{$default}' class='avatar avatar-{$size} avatar-default' height='{$size}' width='{$size}' />";
	}
	return $avatar;
}
function fail($s) {
	header('HTTP/1.0 500 Internal Server Error');
	echo $s;
	exit;
}
function zhuangb_comment_callback($content) {
	preg_match_all("/<blockquote\><p\><b\><em\>(.+)<\/em\><\/b\><\/p\><\/blockquote\>/siU", $content, $results, PREG_SET_ORDER);
	if ($results) {
		foreach ($results as $item) {
			$content = str_replace($item[0], "", $content);
		}
	}
	return $content;
}
function wp_zhuangb_delete($table_prefix) {
	global $wpdb;
	$zhuangb_delete_sql = "DELETE FROM `".$table_prefix."zhuangb_comments`  WHERE `".$table_prefix."zhuangb_comments`.`commentid` not in (SELECT `comment_ID` FROM `".$table_prefix."comments`)";
	$zhuangb_delete_back = $wpdb->query($zhuangb_delete_sql);
	echo ("DELETE ROWS: <h2>".$zhuangb_delete_back."</h2>DELETE COMPLETE.");
}
?>
