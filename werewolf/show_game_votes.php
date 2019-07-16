<?php
include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "menu.php";

dbConnect();

#checkLevel($level,1);

$thread_id = $_GET['thread_id'];
$here = "/";
$game_page = "${here}game/";
if ( $thread_id == "" ) {
?>
<html>
<head>
<script language='javascript'>
<!--
window.history.back();
//-->
</script>
</head>
<body>
Please hit your browsers back button.
</body>
</html>
<?php
exit;
}


$sql = sprintf("select id, title, auto_vt from Games where thread_id=%s",quote_smart($thread_id));
$result = mysql_query($sql);
if ( mysql_num_rows($result) == 1 ) {
  $game_id = mysql_result($result,0,0);
  $title = mysql_result($result,0,1);
  $tiebreaker = mysql_result($result,0,2);
} else {
  $game_id = 0;
  $title = "Invalid Game";
}
$sql = sprintf("select last_dumped from Post_collect_slots where game_id=%s",$game_id);
$result = mysql_query($sql);
if ( mysql_num_rows($result) == 1 ) {
  $last_dumped = mysql_result($result,0,0);
} 

?>
<html>
<head>
<title>Votes from <?=$title;?></title>
<link rel='stylesheet' type='text/css' href='<?=$here;?>assets/css/application.css'>
<script language='javascript'>
<!--

var game_id = '<?=$game_id;?>';

function show_tables() {
  name = document.getElementById('s_name').value
  agent.call('','vote_table','update_table',game_id,name)
}

function update_table(obj) {
  document.getElementById('table_div').innerHTML = obj
}
//-->
</script>
</head>
<body>
<?php display_menu();?>
<h1>Votes from <?=$title;?> as of <?=$last_dumped;?></h1>
<?php
if ( $tiebreaker == "lhv" ) {
  print "<p>Your Moderator has chosen to use the Longest Held Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.</p>\n";
} else {
  print "<p>Your Moderator has chosen to use the Longest Held Last Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.</p>\n";
}
$sql_players = sprintf("select name from Players, Players_all, Users where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Players_all.user_id=Users.id and Players_all.game_id=%s order by name",quote_smart($game_id));
$result_players = mysql_query($sql_players);
print "<form>";
print "Show Player: <select id='s_name' onChange='show_tables()'>";
print "<option value='All'>All</option>";
while ( $player = mysql_fetch_array($result_players) ) {
  print "<option value='".$player['name']."'>".$player['name']."</option>";
}
print "</select></form>";

print "<div id='table_div'>";
print vote_table($game_id,'All');
print "</div>";
?>
</body>
</html>
<?php

function vote_table($game_id,$player) {
  $bgg_article = "http://boardgamegeek.com/article/";

  $output = "";
  $sql_days = sprintf("select distinct day from Votes_log where game_id=%s order by day desc",$game_id);
  $result_days = mysql_query($sql_days);
  while ( $day = mysql_fetch_array($result_days) ) {
    if ( $player == "All" ) {
      $sql_votes = sprintf("select * from Votes_log where game_id=%s and day=%s order by time_stamp",$game_id,$day[0]);
    } else {
      $sql_votes = sprintf("select * from Votes_log where game_id=%s and day=%s and voter=%s order by time_stamp",$game_id,$day[0],quote_smart($player));
    }
    $result_votes = mysql_query($sql_votes);
	if ( mysql_num_rows($result_votes) == 0 ) {
	  $output .= "$player did not vote on day ".$day[0]."<br />";
	} else {
      $output .= "<table class='forum_table'>";
      $output .= "<tr><th colspan='6'>Day ".$day[0]."</th></tr>";
      $output .= "<tr><th>Voter</th><th>Type</th><th>Votee</th><th>Misc</th><th>Time Stamp</th><th>Valid</th><th>Edited</th></tr>";
      while ( $row = mysql_fetch_array($result_votes) ) {
        $style = "style='color:black;'";
        if ( $row['valid'] == "No" ) {
          $style = "style='color:red;'";
        }
        $output .= "<tr>";
        $output .= "<td $style>".$row['voter']."</td>";
        $output .= "<td $style>".$row['type']."</td>";
        $output .= "<td $style>".$row['votee']."</td>";
        $output .= "<td $style>".$row['misc']."</td>";
        $output .= "<td $style><a href='$bgg_article".$row['article_id']."#".$row['article_id']."'>".$row['time_stamp']."</a></td>";
        $output .= "<td $style>".$row['valid']."</td>";
        $output .= "<td $style>".$row['edited']."</td>";
        $output .= "</tr>\n";
      }
      $output .= "</table>\n";
	}
    if ( $player == "All" ) {
      $sql_nonvoters = sprintf("select get_non_voters(%d, %d);",$game_id, $day[0]);
      $res = mysql_query($sql_nonvoters);
      $nonvoters = mysql_result($res,0,0);
      $output .=  "Not voting: $nonvoters<br>\n";     
    }
	$output .= "<br />";
  }
  return $output;
}
