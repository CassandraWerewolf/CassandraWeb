<?php

// This page is called from the Moderator Controls to Activate the Missing player Warning System.

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";

$mysql = dbConnect();

if ( isset($_GET['action']) ) {
$action = $_GET['action'];
  // Check that the user requesting this page is a moderator of the game.
  $sql = sprintf("select * from Moderators where user_id=%s and game_id=%s",quote_smart($uid),quote_smart($_GET['game_id']));
  $result = mysqli_query($mysql, $sql);
  if ( mysqli_num_rows($result) == 1 ) {
    $game_id = $_GET['game_id'];
    if ( $action == "activate" ) {
	  if ( $_GET['hr'] == "0" || $_GET['hr'] == "" ) {
        $sql = sprintf("update Games set missing_hr=NULL where id=%s",quote_smart($game_id));
	  } else {
        $sql = sprintf("update Games set missing_hr=%s where id=%s",quote_smart($_GET['hr']),quote_smart($game_id));
	  }
      $result = mysqli_query($mysql, $sql);
    }
  }
}
?>
<html>
<head>
<script language='javascript'>
<!--
alert("The warnings will only be as accurate as the last time Cassandra pulled the thread.")
location.href='<?=$_GET['from'];?>'
//-->
</script>
</head>
<body>
Please return to your <a href='<?=$_GET['from'];?>'>game page.</a>
</body>
</html>

