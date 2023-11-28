<?php
include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "menu.php";

$mysql = dbConnect();

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
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) == 1 ) {
  $game_id = mysqli_result($result,0,0);
  $title = mysqli_result($result,0,1);
  $tiebreaker = mysqli_result($result,0,2);
} else {
  $game_id = 0;
  $title = "Invalid Game";
}
$sql = sprintf("select last_dumped from Post_collect_slots where game_id=%s",$game_id);
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) == 1 ) {
  $last_dumped = mysqli_result($result,0,0);
}

?>
<html>
<head>
<title>Vote Tally for <?=$title;?></title>
<link rel='stylesheet' type='text/css' href='<?=$here;?>assets/css/application.css'>
</head>
<body>
<?php display_menu();?>
<h1>Vote Tally for <?=$title;?> as of <?=$last_dumped;?></h1>
<?php
if ( $tiebreaker == "lhv" ) {
  print "<p>Your Moderator has chosen to use the Longest Held Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.</p>\n";
} else {
  print "<p>Your Moderator has chosen to use the Longest Held Last Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.</p>\n";
}
print "<p>Nightfall votes are denoted with an '*' after the player's name.</p>\n";
$sql_game_day = sprintf("select day from Games where id=%s",$game_id);
$result = mysqli_query($mysql, $sql_game_day);
$game_day = mysqli_result($result,0,0);
if($game_day > 0)
{
$sql_days = sprintf("select distinct day from Tally_display_%s where game_id=%s order by day desc",$tiebreaker,$game_id);
$result_days = mysqli_query($mysql, $sql_days);
if ( mysqli_num_rows($result_days) > 0 ) {
while ( $day = mysqli_fetch_array($result_days) ) {
?>
<table class='forum_table' width='100%'>
<tr><th colspan='6'>Day <?=$day[0];?></th></tr>
<tr><th width='10%'>Player</th><th width='2%'>Count</th><th>By...</th></tr>
<?php
$sql_votes = sprintf("select votee, total, votes_html from Tally_display_%s where game_id=%s and day=%s ",$tiebreaker,$game_id, $day[0]);
$result = mysqli_query($mysql, $sql_votes);
while ( $row = mysqli_fetch_array($result) ) {
print "<tr>";
print "<td>".$row['votee']."</td>";
print "<td align='center'>".$row['total']."</td>";
print "<td>".$row['votes_html']."</td>";
print "</tr>\n";
}
print "</table>\n";
$sql_nonvoters = sprintf("select get_non_voters(%d, %d);",$game_id, $day[0]);
$res = mysqli_query($mysql, $sql_nonvoters);
$nonvoters = mysqli_result($res,0,0);
print "Not voting: $nonvoters<br><br>\n";  
}
}
}
?>
</body>
</head>
