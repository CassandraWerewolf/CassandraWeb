<?php

// This page is called from the Moderator Controls to Activate the Missing player Warning System.

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";

dbConnect();

if ( isset($_GET['action']) ) {
$action = $_GET['action'];
  // Check that the user requesting this page is a moderator of the game.
  $sql = sprintf("select * from Moderators where user_id=%s and game_id=%s",quote_smart($uid),quote_smart($_GET['game_id']));
  $result = mysql_query($sql);
  if ( mysql_num_rows($result) == 1 ) {
    $game_id = $_GET['game_id'];
    if ( $action == "activate" ) {
	  if ( $_GET['hr'] == "0" || $_GET['hr'] == "" ) {
        $sql = sprintf("update Games set missing_hr=NULL where id=%s",quote_smart($game_id));
	  } else {
        $sql = sprintf("update Games set missing_hr=%s where id=%s",quote_smart($_GET['hr']),quote_smart($game_id));
	  }
      $result = mysql_query($sql);
    }
  }
}

// "from" is the game page to go back to (mod_control.js sends REQUEST_URI, e.g. /game/1234).
// Only allow a path on this site: no other sites (//evil, /\evil) and no javascript: URLs.
$from = (string) ($_GET['from'] ?? '/');
if ( ! preg_match('#^/(?![/\\\\])#', $from) ) {
  $from = '/';
}
?>
<html>
<head>
<script language='javascript'>
<!--
alert("The warnings will only be as accurate as the last time Cassandra pulled the thread.")
location.href=<?=json_encode($from, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);?>

//-->
</script>
</head>
<body>
Please return to your <a href='<?=htmlspecialchars($from, ENT_QUOTES, 'UTF-8');?>'>game page.</a>
</body>
</html>

