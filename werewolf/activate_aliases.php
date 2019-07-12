<?php

// This page is called from the Moderator Controls to Control the Auto Vote Tally features.

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";

dbConnect();

// Check that the user requesting this page is a moderator of the game.
$sql = sprintf("select * from Moderators where user_id=%s and game_id=%s",quote_smart($uid),quote_smart($_GET['game_id']));
$result = mysql_query($sql);
if ( mysql_num_rows($result) == 1 ) {
  $game_id = $_GET['game_id'];
  $sql = sprintf("update Games set vote_by_alias='Yes' where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
}
?>
<html>
<head>
<script language='javascript'>
<!--
history.back();
//-->
</script>
</head>
<body>
Please return hit your back button.
</body>
</html>

