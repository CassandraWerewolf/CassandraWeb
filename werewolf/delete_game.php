<?php
include_once "php/accesscontrol.php";
include_once "php/common.php";
include_once "php/db.php";

$cache = init_cache();

dbConnect();

$game_id = $_REQUEST['game_id'];

// Make sure the player trying to delete is either the moderator or has level 1 clearance.

if ( $level != 1 ) {
  $sql = sprintf("select * from Moderators where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($uid));
  $result = mysql_query($sql);
  if ( mysql_num_rows($result) != 1 ) {
    error("You must be a Moderator to delete this game.");
  }
}

// Make sure the game is in sign-up mode.

$sql = sprintf("select status from Games where id=%s",quote_smart($game_id));
$result = mysql_query($sql);
$status = mysql_result($result,0,0);

if ( $status != "Sign-up" && $status != "Scheduled" && $status != "Unknown" ) {
  error("You can only delete a game Before it is in 'In Progress' mode.");
}

if ( isset($_POST['confirm']) ) {
  $sql = sprintf("delete from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $cache->remove('games-signup-fast-list', 'front');
  $cache->remove('games-signup-list', 'front');
?>
<html>
<head>
<script language='javascript'>
<!--
location.href='/'
//-->
</script>
</head>
<body>
Return to the <a href="/">front page.</a>
</body>
</html>
<?php
} else {
?>
<html>
<head>
<title>Are you sure?</title>
</head>
<body>
<form action='<?=$_SERVER['PHP_SELF'];?>' method='post'>
<p>Are you sure you want to delete this game?</p>
<input type='hidden' name='game_id' value='<?=$game_id;?>' />
<input type='submit' name='confirm' value='Yes' />
</form>
</body>
</html>
<?php
}

?>
