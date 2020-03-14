<?php
// This is used to sign a player up for a game

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";
include_once "php/common.php";

$cache = init_cache();

if ( $_REQUEST['remember'] == "on" ) {
   setcookie('bgg_password', $_REQUEST['bggpwd'], time()+60*60*24*365, '/', '', true, true);
}

dbConnect();

$bggpwd = $_COOKIE['bgg_password'];

if ( $_GET['action'] == "add" ) {
  $sql = sprintf ("insert into Players ( user_id, game_id, update_time ) values ( %s, %s, now() )",quote_smart($uid),quote_smart($_GET['game_id']));
  $result = mysql_query($sql);
	$cache->clean('front-signup-' . quote_smart($_GET['game_id']));
	$cache->clean('front-signup-fast-' . quote_smart($_GET['game_id']));
	$cache->clean('front-signup-swf-' . quote_smart($_GET['game_id']));
	$cache->remove('games-signup-swf-list', 'front');
	$cache->remove('games-signup-fast-list', 'front');

  
  $sql = sprintf("select thread_id from Games where id=%s",quote_smart($_GET['game_id']));
  $result = mysql_query($sql);
  $thread_id = mysql_result($result,0,0);

  print "<!--\n";
  edit_playerlist_post($_GET['game_id']);
  notify_moderator($_GET['game_id'],"added",$username);
  print "-->\n";
?>
<html>
<head>
<title>Sign Me Up</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
<script language='javascript'>
<!--
function bgg_pwd_note() {
  alert("This will be stored as a cookie on your computer not in the Cassandra database.")
}
//-->
</script>
<body>
<h1>Post a message in the sign-up thread</h1>
<form name='post_message' method='post' action='<?=$_SERVER['PHP_SELF'];?>' >
<input type='hidden' name='thread_id' value='<?=$thread_id;?>' />
<table class='forum_table'>
<tr><td>BGG Password:</td><td><input type='password' name='bggpwd' value='<?=$bggpwd;?>' /></td></tr>
<tr><td></td><td><input type='checkbox' name='remember' />
<?php
if ($uid == 1525) {
 echo "Reme<b>M</b>ber BGG password. This is for you, Tim.";
} else {
 echo "Remember BGG password.";
}
?>
<a href='javascript:bgg_pwd_note()'>Read First</a></td></tr>
<tr><td>Message:</td><td><textarea name='message' rows='10' cols='50'>I just signed up on Cassandra.</textarea></td></tr>
<tr><td colspan='2' align='center'><input type='submit' name='submit' value='submit' /></td></tr>
</table></form>
<h3>or</h3>
<p>Return to <a href='/game/<?=$thread_id;?>'>Game Page</a></p>
</body>
</html>
<?php
}
if ( $_GET['action'] == "confirm") {
  $sql = sprintf("update Players set need_to_confirm = null where user_id=%s and game_id=%s",quote_smart($uid),quote_smart($_GET['game_id']));
  $result = mysql_query($sql);
  error("You have been confirmed");
}
if ( $_GET['action'] == "remove" ) {
  $sql = sprintf ("delete from Players where user_id=%s and game_id=%s",quote_smart($uid),quote_smart($_GET['game_id']));
  $result = mysql_query($sql);
	$cache->clean('front-signup-' . quote_smart($_GET['game_id']));
	$cache->clean('front-signup-fast-' . quote_smart($_GET['game_id']));
	$cache->clean('front-signup-swf-' . quote_smart($_GET['game_id']));
	$cache->remove('games-signup-swf-list', 'front');
	$cache->remove('games-signup-fast-list', 'front');
  
  $sql = sprintf("select thread_id from Games where id=%s",quote_smart($_GET['game_id']));
  $result = mysql_query($sql);
  $thread_id = mysql_result($result,0,0);

  edit_playerlist_post($_GET['game_id']);
  notify_moderator($_GET['game_id'],"removed",$username);
?>
<html>
<head>
<script language='javascript'>
<!--
location.href='/game/<?=$thread_id;?>'
//-->
</script>
<body>
Return to <a href='/game/<?=$thread_id;?>'>Game Page</a>
</body>
</html>
<?php
}
if ( isset($_REQUEST['submit']) ) {
$thread_id = $_REQUEST['thread_id'];
$message = $_REQUEST['message'];
print "<!--";
$bgg_user = BGG::auth($username, $_REQUEST['bggpwd']);
$bgg_user->reply_thread_quick($_REQUEST['thread_id'],$message);
print "-->";

?>
<html>
<head>
<script language='javascript'>
<!--
location.href='/game/<?=$thread_id;?>'
//-->
</script>
</head>
<body>
<?=$message;?><br />
Return to <a href='/game/<?=$thread_id;?>'>Game Page</a>
</body>
</html>
<?php
}
