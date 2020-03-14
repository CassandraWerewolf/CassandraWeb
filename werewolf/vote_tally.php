<?php

// This page is called from the Moderator Controls to Control the Auto Vote Tally features.

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
	  if ( $_GET['nf'] == "true" ) { $nf = "Yes"; } else { $nf = "No"; }
	  if ( $_GET['nl'] == "true" ) { $nl = "Yes"; } else { $nl = "No"; }
      $sql = sprintf("update Games set auto_vt=%s, allow_nightfall=%s, allow_nolynch=%s where id=%s",quote_smart($_GET['tieb']),quote_smart($nf),quote_smart($nl),quote_smart($game_id));
      $result = mysql_query($sql);
      $sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
      $result = mysql_query($sql);
      $game = mysql_fetch_array($result);
      $message = file_get_contents("cassy_vote_tally.txt");
	  $message .= "\n";
	  if ( $_GET['tieb'] == "lhv") {
	    $message .= "Your Moderator has chosen to use the Longest Held Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.\n";
	  } else {
	    $message .= "Your Moderator has chosen to use the Longest Held Last Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.\n";
	  }
	  $message .= "Your Moderator has chosen to [b]" . ($nf == "No" ?  "dis" : "") . "allow[/b] Nightfall votes.\n";
	  $message .= "Your Moderator has chosen to [b]" . ($nl == "No" ?  "dis" : "") . "allow[/b] No Lynch votes.\n\n";
	  
	  $message .= "Vote Log Page: http://cassandrawerewolf.com/game/".$game['thread_id']."/votes\n";
	  $message .= "Vote Tally Page: http://cassandrawerewolf.com/game/".$game['thread_id']."/tally\n";
      print "<!--\n";
      BGG::authAsCassy()->reply_thread($game['thread_id'],$message);
      print "-->\n";
    } elseif ($action == "retrieve" ) {
      $sql = sprintf("update Games set updated_tally=1 where id=%s",quote_smart($game_id));
      $result = mysql_query($sql);

      $sql = sprintf("update Post_collect_slots set last_dumped=NULL where game_id=%s",quote_smart($game_id));
      $result = mysql_query($sql);
    }
  }
}
?>
<html>
<head>
<script language='javascript'>
<!--
//location.href='<?=$_GET['from'];?>'
//-->
</script>
</head>
<body>
Please return to your <a href='<?=$_GET['from'];?>'>game page.</a>
</body>
</html>

